<?php

declare(strict_types=1);

namespace App\DataProcessor\Support;

use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\IriConverterInterface;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\Metadata\Resource\Factory\ResourceMetadataCollectionFactoryInterface;
use ApiPlatform\State\ProcessorInterface;
use ApiPlatform\Validator\ValidatorInterface;
use App\Dto\Support\ManualInput;
use App\Entity\Support\Component;
use App\Entity\Support\EquipmentSerial;
use App\Entity\Support\Manual;
use App\Entity\Support\ManualDocument;
use App\Factory\Support\ManualFactory;
use App\Factory\VaultFileFactory;
use App\ION\DataProvider\CachedIONItemDataProvider;
use App\ION\Filter\DataAreaFilter;
use App\ION\Resources\Manufacturing\JobShop\CustomizedBillOfMaterials\Manuals;
use App\Message\Support\ManualDuplication;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;

/**
 * @template T
 */
final class ManualDataProcessor implements ProcessorInterface
{
    private bool $resume = false;

    public function __construct(
        private readonly ValidatorInterface $validator,
        private readonly ManualFactory $manualFactory,
        private readonly EntityManagerInterface $entityManager,
        private readonly VaultFileFactory $vaultFileFactory,
        private readonly CachedIONItemDataProvider $itemDataProvider,
        private readonly MessageBusInterface $messageBus,
        private readonly IriConverterInterface $iriConverter,
        private readonly Security $security,
        private readonly ResourceMetadataCollectionFactoryInterface $resourceMetadataCollectionFactory,
    ) {
    }

    /**
     * {@inheritdoc}
     *
     * @param ManualInput $data
     *
     * @return T
     */
    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = [])
    {
        $this->validator->validate($data);
        $manual = $this->manualFactory->create($data->mainEquipmentRecord, $data->language, $data->force, $data->status);
        $manual->status = $data->status;

        $this->validator->validate($manual, ['groups' => $data->force ? [Manual::PUBLISHABLE_VALIDATION_GROUP] : [Manual::CRITICAL_VALIDATION_GROUP]]);

        if (!$data->force) {
            // dry run
            $equipmentRecord = $data->mainEquipmentRecord;
            $equipmentRecord->setPublishable(true);

            $this->entityManager->persist($equipmentRecord);
            $this->entityManager->flush();

            $this->validator->validate($manual, ['groups' => Manual::NONCRITICAL_VALIDATION_GROUP]);

            return new Response('', Response::HTTP_NO_CONTENT);
        }

        $equipmentRecord = $manual->equipmentRecord;
        $site = $equipmentRecord->getManufacturerLocation()->getErp();
        $date = $equipmentRecord->getGreenTagDate() ?? new \DateTime();

        $metadata = $this->resourceMetadataCollectionFactory->create(Manuals::class);
        /** @var Manuals|null $schematics */
        $schematics = $this->itemDataProvider->provide(
            $metadata->getOperation(),
            ['site' => $site, 'project' => $equipmentRecord->getProjectNumber()],
            [
                'operation_type' => Get::class,
                DataAreaFilter::CONTEXT_DATA_AREA_KEY => [
                    'date' => $date->format(\DateTimeInterface::ATOM),
                    'signalCodeFilter' => ManualFactory::getFormattedGroupSignaCode('schematics'),
                    'signalCodeFilterMethod' => 'Equals',
                    'signalCodeAttribute' => 'engineeringSignalCode',
                    'otherLanguage' => $manual->language,
                ],
            ]);

        $schematicsSerials = new ArrayCollection();
        $cachedComponent = [];
        $processedSerialKeys = [];
        $schematicsCustomizedBillOfMaterialsItems = null !== $schematics ? $schematics->getItems() : [];

        foreach ($schematicsCustomizedBillOfMaterialsItems as $item) {
            if (\array_key_exists($item->engineeringSignalCode, $cachedComponent)) {
                $component = $cachedComponent[$item->engineeringSignalCode];
            } else {
                $component = $this->entityManager->getRepository(Component::class)->findOneBy(['signalCode' => $item->engineeringSignalCode]);
                $cachedComponent[$item->engineeringSignalCode] = $component;
            }

            $equipmentSerial = new EquipmentSerial();
            $equipmentSerial->serial = mb_trim((string) $item->partNumber);
            $equipmentSerial->component = $component;
            $equipmentSerial->model = $item->itemDescription;
            $equipmentSerial->brand = (string) $equipmentRecord->getManufacturerLocation()->getErp();
            $equipmentSerial->equipmentRecord = $equipmentRecord;

            $serialKey = implode('|', [
                $item->engineeringSignalCode,
                $equipmentSerial->model,
                $equipmentSerial->serial,
                $equipmentSerial->brand,
            ]);

            if (isset($processedSerialKeys[$serialKey])) {
                continue;
            }
            $processedSerialKeys[$serialKey] = true;

            $equipmentSerial = $this->entityManager->getRepository(EquipmentSerial::class)->createOrFindExistingSerial($equipmentSerial);
            $schematicsSerials->add($equipmentSerial);
        }

        foreach ($manual->getDocuments() as $manualDocument) {
            $this->vaultFileFactory->store($manualDocument, $site, (ManualDocument::MANUAL_SECTION === $manualDocument->type) ? 'pdf' : 'jpg', false);
        }

        $this->entityManager->persist($manual);
        //         Need to flush to have the manual legacy ID
        $this->entityManager->flush();

        $equipmentSerial = new EquipmentSerial();
        $equipmentSerial->serial = (string) $manual->getLegacyId();
        $equipmentSerial->component = $this->entityManager->getRepository(Component::class)->findOneBy(['name' => Component::MANUAL]);
        $equipmentSerial->equipmentRecord = $manual->equipmentRecord;
        $manual->equipmentSerial = $equipmentSerial;

        $this->entityManager->persist($equipmentSerial);
        $this->entityManager->flush();

        if ([] !== $data->getSecondaryEquipmentRecords()) {
            $secondaryEquipmentRecordsIri = $schematicsSerialsIri = [];

            foreach ($data->getSecondaryEquipmentRecords() as $equipmentRecord) {
                $secondaryEquipmentRecordsIri[] = $this->iriConverter->getIriFromResource($equipmentRecord);
            }

            foreach ($schematicsSerials as $schematicsSerial) {
                $schematicsSerialsIri[] = $this->iriConverter->getIriFromResource($schematicsSerial);
            }

            $this->messageBus->dispatch(new ManualDuplication(
                $this->iriConverter->getIriFromResource($manual),
                $secondaryEquipmentRecordsIri,
                $schematicsSerialsIri,
                $this->iriConverter->getIriFromResource($this->security->getUser())
            ));
        }

        return $manual;
    }
}
