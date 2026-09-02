<?php

declare(strict_types=1);

namespace App\Manager\Quality;

use ApiPlatform\Metadata\Resource\Factory\ResourceMetadataCollectionFactoryInterface;
use App\Entity\EquipmentRecord;
use App\Entity\Quality\Crab;
use App\ION\DataProcessor\IONDataProcessor;
use App\ION\Resources\Manufacturing\ProductionOrder\ProductionOrder;
use App\ION\Resources\Manufacturing\ProductionOrder\ProductionOrderProject;
use App\Repository\Quality\Crab\CrabRepository;

class CrabManager
{
    public function __construct(
        private readonly CrabRepository $crabRepository,
        private readonly IONDataProcessor $dataProcessor,
        private readonly ResourceMetadataCollectionFactoryInterface $resourceMetadataCollectionFactory,
    ) {
    }

    public function updateIONCrabData(EquipmentRecord $equipmentRecord, ?bool $isCrabDeletedClosed = null): void
    {
        if (($site = $equipmentRecord->getManufacturerLocation()?->getErp()) === null) {
            return;
        }

        $crabs = $this->crabRepository->findBy(['equipmentRecord' => $equipmentRecord]);

        $countCrabOpen = 0;
        $countTotalCrab = 0;
        foreach ($crabs as $crab) {
            if (Crab::CLOSED !== $crab->status) {
                ++$countCrabOpen;
            }
            ++$countTotalCrab;
        }
        $productionOrder = new ProductionOrder();
        $productionOrder->site = $site;
        $project = new ProductionOrderProject();
        $project->code = $equipmentRecord->getSerialNumber();
        $project->openCrabs = $countCrabOpen - (null === $isCrabDeletedClosed || true === $isCrabDeletedClosed ? 0 : 1);
        $project->totalCrabs = $countTotalCrab - (int) (null !== $isCrabDeletedClosed);
        $productionOrder->addProject($project);

        $operation = $this->resourceMetadataCollectionFactory->create(ProductionOrder::class)->getOperation('post_production_order');
        $this->dataProcessor->process($productionOrder, $operation, [], ['resource_class' => ProductionOrder::class]);
    }
}
