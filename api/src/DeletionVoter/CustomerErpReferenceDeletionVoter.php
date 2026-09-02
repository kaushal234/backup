<?php

declare(strict_types=1);

namespace App\DeletionVoter;

use App\DeletionVoter\Reason\RejectedDeletionDetailedReason;
use App\DeletionVoter\Reason\RejectedDeletionReason;
use App\Entity\Sales\CustomerErpReference;
use App\Repository\Finance\AccountReceivableRepository;

class CustomerErpReferenceDeletionVoter implements DeletionVoterInterface
{
    private readonly AccountReceivableRepository $accountReceivableRepository;

    public function __construct(AccountReceivableRepository $accountReceivableRepository)
    {
        $this->accountReceivableRepository = $accountReceivableRepository;
    }

    public function supports($entity): bool
    {
        return $entity instanceof CustomerErpReference;
    }

    /**
     * {@inheritdoc}
     *
     * @param CustomerErpReference $entity
     */
    public function abstainToDeletion($entity): ?RejectedDeletionReason
    {
        $reason = new RejectedDeletionDetailedReason();
        $reason->setType('customerErpReference')->setLabel($entity->getCustomerNumber());

        if ((bool) ($ids = $this->accountReceivableRepository->getCustomerErpReferenceIdentifiersForAccountReceivables($entity))) {
            return $reason
                ->setIdentifiers(array_column($ids, 'id'))
                ->setCountedType('AR')
            ;
        }

        return null;
    }
}
