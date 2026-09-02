<?php

declare(strict_types=1);

namespace App\Manager;

use ApiPlatform\Metadata\Resource\Factory\ResourceMetadataCollectionFactoryInterface;
use App\Entity\EquipmentRecord;
use App\Entity\Sales\Customer;
use App\ION\DataProvider\CachedIONItemDataProvider;
use App\ION\Resources\Manufacturing\JobShop\CustomizedBillOfMaterials\MultiLevelView;
use App\ION\Resources\Manufacturing\JobShop\CustomizedBillOfMaterials\MultiLevelViewItem;
use Doctrine\ORM\EntityManagerInterface;

class EquipmentRecordManager
{
    public function __construct(
        private readonly CachedIONItemDataProvider $itemProvider,
        private readonly ResourceMetadataCollectionFactoryInterface $resourceMetadataCollectionFactory,
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    public function updatedEstimatedGreenTagDateNeedsToSendGapAlert(EquipmentRecord $equipmentRecord, ?\DateTimeInterface $previousEstimatedGreenTagDate): bool
    {
        $currentEstimatedGreenTagDate = $equipmentRecord->getEstimatedGreenTagDate();

        if ((null !== $equipmentRecord->getBuyer() && \in_array($equipmentRecord->getBuyer()->getName(), [Customer::CUSTOMER_DEMO, Customer::CUSTOMER_PROTO, Customer::CUSTOMER_STOCK], true))
            || (null === $equipmentRecord->orderFactory?->orderLine)
        ) {
            return false;
        }

        if (null === $previousEstimatedGreenTagDate || null === $currentEstimatedGreenTagDate || $previousEstimatedGreenTagDate->format('Y-m-d') === $currentEstimatedGreenTagDate->format('Y-m-d') || null !== $equipmentRecord->getFirstGreenTagDate()) {
            return false;
        }

        if (null !== ($factoryPromisedDeliveryDate = $equipmentRecord->orderFactory->factoryPromisedDeliveryDate)
            && ($factoryPromisedDeliveryDate->format('Y-m-d') === $currentEstimatedGreenTagDate->format('Y-m-d') || $factoryPromisedDeliveryDate > $currentEstimatedGreenTagDate)) {
            return false;
        }

        $gap = (int) $previousEstimatedGreenTagDate->diff($currentEstimatedGreenTagDate)->format('%r%a');

        if ($gap > 30) {
            return true;
        }

        if ($gap > 20 && $previousEstimatedGreenTagDate <= new \DateTime('+30 days')) {
            return true;
        }

        if ($gap > 10 && $previousEstimatedGreenTagDate <= new \DateTime('+7 days')) {
            return true;
        }

        return false;
    }

    public function setLastCBOMUpdateDate(EquipmentRecord $equipmentRecord): void
    {
        if (($erp = $equipmentRecord->getManufacturerLocation()?->getErp()) === null) {
            return;
        }

        $operation = $this->resourceMetadataCollectionFactory->create(MultiLevelView::class)->getOperation();
        /** @var MultiLevelView|null $cbom */
        $cbom = $this->itemProvider->provide(
            $operation,
            [
                'site' => $erp,
                'project' => $equipmentRecord->getSerialNumber(),
                'depth' => '20',
            ]
        );

        if (null === $cbom) {
            return;
        }

        $engineeringRevisionEffectiveDates = [];
        /** @var MultiLevelViewItem $item */
        foreach ($cbom->getItems() as $item) {
            $engineeringRevisionEffectiveDates[] = $item->engineeringRevisionEffectiveDate;
        }

        $maxDate = max(array_map('strtotime', $engineeringRevisionEffectiveDates));
        $equipmentRecord->setLastCBOMUpdateDate(new \DateTime(date('Y-m-d', $maxDate)));
        $this->entityManager->persist($equipmentRecord);
        $this->entityManager->flush();
    }
}
