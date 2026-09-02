<?php

declare(strict_types=1);

namespace App\Link\Manager\Support;

use App\Entity\Directory\Location;
use App\Entity\EmissionRating;
use App\Entity\EquipmentRecord;
use App\Entity\Sales\Product;
use App\Http\Link\LinkChineseClient;
use App\Http\Link\LinkClient;
use App\Link\Formatter\FormatterLinkEmissionRatingName;
use App\Link\Formatter\FormatterLinkLocationName;
use Psr\Log\LoggerInterface;

class EquipmentRecordManager
{
    public function __construct(
        private readonly LinkClient $client,
        private readonly LinkChineseClient $chineseClient,
        private readonly LoggerInterface $linkRequestLogger
    ) {
    }

    /**
     * @param array<EquipmentRecord> $equipmentRecords
     */
    public function synchronizeEquipmentRecord(array $equipmentRecords)
    {
        // Foreach with only false value for now as chinese client os not ready for production yet
        foreach ([false] as $isChineseResource) {
            $client = $isChineseResource ? $this->chineseClient : $this->client;
            $linkProducts = $client->getCollection(Product::class);
            $linkLocations = $client->getCollection(Location::class);
            $linkEnergySources = $client->getCollection(EmissionRating::class);
            $linkEquipmentRecords = $client->getCollection(EquipmentRecord::class, 'plateNumber');
            $extraProperties = [];

            foreach ($equipmentRecords as $equipmentRecord) {
                if (isset($linkEquipmentRecords[$equipmentRecord->getSerialNumber()])) {
                    $equipmentRecord->setLinkId($linkEquipmentRecords[$equipmentRecord->getSerialNumber()]['id']);
                }

                if (
                    // SSO exists and is NOT TLD CHI, or no SSO but Factory exists and is NOT TLD SHA or TLD WUX, and we check chinese domain
                    ((
                        (null !== $equipmentRecord->getSalesOrganisation() && 'TLD CHI' !== $equipmentRecord->getSalesOrganisation()->getName())
                        || null === $equipmentRecord->getSalesOrganisation() && null !== $equipmentRecord->getManufacturerLocation() && !\in_array($equipmentRecord->getManufacturerLocation()->getName(), ['TLD SHA', 'TLD WUX'], true)
                    ) && $isChineseResource)
                    // SSO exists and is TLD CHI, or no SSO but Factory exists and is TLD SHA or TLD WUX, and we check worldwide domain
                    || (
                        (null !== $equipmentRecord->getSalesOrganisation() && 'TLD CHI' === $equipmentRecord->getSalesOrganisation()->getName()
                        || null === $equipmentRecord->getSalesOrganisation() && null !== $equipmentRecord->getManufacturerLocation() && \in_array($equipmentRecord->getManufacturerLocation()->getName(), ['TLD SHA', 'TLD WUX'], true))
                     && !$isChineseResource)
                ) {
                    if (isset($linkEquipmentRecords[$equipmentRecord->getSerialNumber()])) {
                        $this->linkRequestLogger->debug(\sprintf('Equipment %s archived into Link.', $equipmentRecord->getSerialNumber()));
                        $client->archive($equipmentRecord);
                    }

                    continue;
                }

                if ($this->skipFilter($equipmentRecord, $linkEquipmentRecords)) {
                    $this->linkRequestLogger->debug(\sprintf('Equipment %s has been skipped because no changes was found.', $equipmentRecord->getSerialNumber()));
                    continue;
                }

                if (null !== $equipmentRecord->getManufacturerLocation() && isset($linkLocations[FormatterLinkLocationName::formatValue($equipmentRecord->getManufacturerLocation()->getName())])) {
                    $equipmentRecord->getManufacturerLocation()->setLinkId($linkLocations[FormatterLinkLocationName::formatValue($equipmentRecord->getManufacturerLocation()->getName())]['id']);
                }

                if (null !== $equipmentRecord->getEmissionRating() && isset($linkEnergySources[FormatterLinkEmissionRatingName::formatValue($equipmentRecord->getEmissionRating()->getName())])) {
                    $equipmentRecord->getEmissionRating()->setLinkId($linkEnergySources[FormatterLinkEmissionRatingName::formatValue($equipmentRecord->getEmissionRating()->getName())]['id']);
                }

                if (null !== $equipmentRecord->getProduct() && isset($linkProducts[$equipmentRecord->getProduct()->getName()]['id'])) {
                    $equipmentRecord->getProduct()->setLinkId($linkProducts[$equipmentRecord->getProduct()->getName()]['id']);
                }

                try {
                    $client->mutate($equipmentRecord, ['equipment_record_detail', 'product_list', 'location_public', 'emission_rating:detail'], $extraProperties);
                    $this->linkRequestLogger->debug(\sprintf('Equipment %s has been synchronized on Link', $equipmentRecord->getSerialNumber()));
                } catch (\Exception $exception) {
                    $this->linkRequestLogger->debug(\sprintf('Equipment %s could not be synchronized on Link. Reason: %s', $equipmentRecord->getSerialNumber(), $exception->getMessage()));
                    throw new \Exception($exception->getMessage());
                }
            }
        }
    }

    private function skipFilter(EquipmentRecord $equipmentRecord, array $existingEquipmentRecords): bool
    {
        $serialNumber = $equipmentRecord->getSerialNumber();
        if (!\in_array($serialNumber, array_keys($existingEquipmentRecords), true)) {
            return false;
        }

        $existingEquipmentRecord = $existingEquipmentRecords[$serialNumber];

        if ((null !== $existingEquipmentRecord['energySource'] && $existingEquipmentRecord['energySource'] !== FormatterLinkEmissionRatingName::formatValue($equipmentRecord->getEmissionRating()?->getName()))
            || (null === $existingEquipmentRecord['energySource'] & null !== $equipmentRecord->getEmissionRating())
        ) {
            return false;
        }

        if ((null !== $existingEquipmentRecord['equipmentModel'] && $existingEquipmentRecord['equipmentModel']['name'] !== $equipmentRecord->getProduct()?->getName())
            || (null === $existingEquipmentRecord['equipmentModel'] && null !== $equipmentRecord->getProduct()?->getName())
        ) {
            return false;
        }

        if ((null !== $existingEquipmentRecord['organization'] && $existingEquipmentRecord['organization']['name'] !== FormatterLinkLocationName::formatValue($equipmentRecord->getManufacturerLocation()?->getName()))
            || (null === $existingEquipmentRecord['organization'] && null !== $equipmentRecord->getManufacturerLocation()?->getName())
        ) {
            return false;
        }

        if ($existingEquipmentRecord['plateNumber'] !== $equipmentRecord->getSerialNumber()) {
            return false;
        }

        if ($existingEquipmentRecord['astusId'] !== $equipmentRecord->getSerialNumber()) {
            return false;
        }

        return true;
    }
}
