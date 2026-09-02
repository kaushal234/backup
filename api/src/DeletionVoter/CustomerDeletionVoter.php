<?php

declare(strict_types=1);

namespace App\DeletionVoter;

use App\DeletionVoter\Reason\RejectedDeletionDetailedReason;
use App\DeletionVoter\Reason\RejectedDeletionReason;
use App\Entity\Sales\Customer;
use App\Repository\EquipmentRecordRepository;
use App\Repository\Finance\AccountReceivableRepository;
use App\Repository\Sales\CustomerRelationshipTeamRepository;
use App\Repository\Sales\DemoRepository;
use App\Repository\Sales\OrderRepository;
use App\Repository\Sales\SalesForecastRepository;

class CustomerDeletionVoter implements DeletionVoterInterface
{
    private readonly CustomerRelationshipTeamRepository $customerRelationshipTeamRepository;
    private readonly DemoRepository $demoRepository;
    private readonly EquipmentRecordRepository $equipmentRecordRepository;
    private readonly SalesForecastRepository $salesForecastRepository;
    private readonly OrderRepository $orderRepository;
    private readonly AccountReceivableRepository $accountReceivableRepository;

    public function __construct(
        CustomerRelationshipTeamRepository $customerRelationshipTeamRepository,
        DemoRepository $demoRepository,
        EquipmentRecordRepository $equipmentRecordRepository,
        SalesForecastRepository $salesForecastRepository,
        OrderRepository $orderRepository,
        AccountReceivableRepository $accountReceivableRepository
    ) {
        $this->customerRelationshipTeamRepository = $customerRelationshipTeamRepository;
        $this->demoRepository = $demoRepository;
        $this->equipmentRecordRepository = $equipmentRecordRepository;
        $this->salesForecastRepository = $salesForecastRepository;
        $this->orderRepository = $orderRepository;
        $this->accountReceivableRepository = $accountReceivableRepository;
    }

    public function supports($entity): bool
    {
        return $entity instanceof Customer;
    }

    /**
     * {@inheritdoc}
     *
     * @param Customer $entity
     */
    public function abstainToDeletion($entity): ?RejectedDeletionReason
    {
        $reason = new RejectedDeletionDetailedReason();
        $reason->setType('customer')->setLabel($entity->getName());

        if ((bool) ($ids = $this->customerRelationshipTeamRepository->getIdentifiersForCustomer($entity))) {
            return $reason
                ->setIdentifiers(array_column($ids, 'id'))
                ->setCountedType('CRT')
            ;
        }

        if ((bool) ($ids = $this->demoRepository->getIdentifiersForCustomer($entity))) {
            $type = \count($ids) > 1 ? 'Demos' : 'Demo';

            return $reason
                ->setIdentifiers(array_column($ids, 'id'))
                ->setCountedType($type)
            ;
        }

        if ((bool) ($ids = $this->equipmentRecordRepository->getSerialNumbersForCustomer($entity))) {
            return $reason
                ->setIdentifiers(array_column($ids, 'id'))
                ->setCountedType('ER')
            ;
        }

        if ((bool) ($ids = $this->salesForecastRepository->getIdentifiersForCustomer($entity))) {
            return $reason
                ->setIdentifiers(array_column($ids, 'id'))
                ->setCountedType('SFR')
            ;
        }

        if ((bool) ($ids = $this->orderRepository->getIdentifiersForCustomer($entity))) {
            return $reason
                ->setIdentifiers(array_column($ids, 'id'))
                ->setCountedType('SOR')
            ;
        }

        if ((bool) ($ids = $this->accountReceivableRepository->getCustomerIdentifiersForAccountReceivables($entity))) {
            $ids = array_column($ids, 'id');
            sort($ids);

            return $reason
                ->setIdentifiers($ids)
                ->setCountedType('AR')
            ;
        }

        return null;
    }
}
