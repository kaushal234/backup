<?php

declare(strict_types=1);

namespace App\DeletionVoter;

use App\DeletionVoter\Reason\RejectedDeletionDetailedReason;
use App\DeletionVoter\Reason\RejectedDeletionReason;
use App\Entity\Sales\Product;
use App\Repository\EquipmentRecordRepository;
use App\Repository\Sales\DemoRepository;
use App\Repository\Sales\SalesForecastRepository;

class ProductDeletionVoter implements DeletionVoterInterface
{
    private readonly SalesForecastRepository $salesForecastRepository;
    private readonly DemoRepository $demoRepository;
    private readonly EquipmentRecordRepository $equipmentRecordRepository;

    public function __construct(
        SalesForecastRepository $salesForecastRepository,
        DemoRepository $demoRepository,
        EquipmentRecordRepository $equipmentRecordRepository)
    {
        $this->salesForecastRepository = $salesForecastRepository;
        $this->demoRepository = $demoRepository;
        $this->equipmentRecordRepository = $equipmentRecordRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function supports($entity): bool
    {
        return $entity instanceof Product;
    }

    /**
     * {@inheritdoc}
     *
     * @param Product $entity
     */
    public function abstainToDeletion($entity): ?RejectedDeletionReason
    {
        $reason = new RejectedDeletionDetailedReason();
        $reason->setType('Product')->setLabel($entity->getName());

        if ((bool) ($ids = $this->salesForecastRepository->getIdentifiersForProduct($entity))) {
            return $reason
                ->setIdentifiers(array_column($ids, 'id'))
                ->setCountedType('SFR')
            ;
        }

        if ((bool) ($ids = $this->demoRepository->getIdentifiersForProduct($entity))) {
            $type = \count($ids) > 1 ? 'Demos' : 'Demo';

            return $reason
                ->setIdentifiers(array_column($ids, 'id'))
                ->setCountedType($type)
            ;
        }

        if ((bool) ($ids = $this->equipmentRecordRepository->getIdentifiersForProduct($entity))) {
            return $reason
                ->setIdentifiers(array_column($ids, 'id'))
                ->setCountedType('ER')
            ;
        }

        return null;
    }
}
