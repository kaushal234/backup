<?php

declare(strict_types=1);

namespace App\DeletionVoter;

use App\DeletionVoter\Reason\RejectedDeletionDetailedReason;
use App\DeletionVoter\Reason\RejectedDeletionReason;
use App\Entity\Sales\ProductFamily;
use App\Repository\EquipmentRecordRepository;
use App\Repository\Manufacturing\LeadTimeRepository;
use App\Repository\Sales\DemoRepository;
use App\Repository\Sales\SalesForecastRepository;

class ProductFamilyDeletionVoter implements DeletionVoterInterface
{
    public function __construct(
        private readonly SalesForecastRepository $salesForecastRepository,
        private readonly DemoRepository $demoRepository,
        private readonly EquipmentRecordRepository $equipmentRecordRepository,
        private readonly LeadTimeRepository $leadTimeRepository
    ) {
    }

    /**
     * {@inheritdoc}
     */
    public function supports($entity): bool
    {
        return $entity instanceof ProductFamily;
    }

    /**
     * {@inheritdoc}
     *
     * @param ProductFamily $entity
     */
    public function abstainToDeletion($entity): ?RejectedDeletionReason
    {
        $reason = new RejectedDeletionDetailedReason();
        $reason->setType('Product family')->setLabel($entity->getName());

        if ((bool) ($ids = $this->salesForecastRepository->getIdentifiersForProductFamily($entity))) {
            return $reason
                ->setIdentifiers(array_column($ids, 'id'))
                ->setCountedType('SFR')
            ;
        }

        if ((bool) ($ids = $this->demoRepository->getIdentifiersForProductFamily($entity))) {
            $type = \count($ids) > 1 ? 'Demos' : 'Demo';

            return $reason
                ->setIdentifiers(array_column($ids, 'id'))
                ->setCountedType($type)
            ;
        }

        if ((bool) ($ids = $this->equipmentRecordRepository->getIdentifiersForProductFamily($entity))) {
            return $reason
                ->setIdentifiers(array_column($ids, 'id'))
                ->setCountedType('ER')
            ;
        }

        if ((bool) ($ids = $this->leadTimeRepository->getIdentifiersForProductFamily($entity))) {
            return $reason
                ->setIdentifiers(array_column($ids, 'id'))
                ->setCountedType('Lead Time')
            ;
        }

        return null;
    }
}
