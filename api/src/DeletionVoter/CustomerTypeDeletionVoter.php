<?php

declare(strict_types=1);

namespace App\DeletionVoter;

use App\DeletionVoter\Reason\RejectedDeletionDetailedReason;
use App\DeletionVoter\Reason\RejectedDeletionReason;
use App\Entity\Sales\Customer;
use App\Entity\Sales\CustomerType;

class CustomerTypeDeletionVoter implements DeletionVoterInterface
{
    public function supports($entity): bool
    {
        return $entity instanceof CustomerType;
    }

    /**
     * {@inheritdoc}
     *
     * @param CustomerType $entity
     */
    public function abstainToDeletion($entity): ?RejectedDeletionReason
    {
        if (!($customers = $entity->getCustomers())->isEmpty()) {
            $reason = new RejectedDeletionDetailedReason();
            $reason->setType('Customer Type')->setLabel($entity->getName());
            $names = array_map(static fn (Customer $customer) => ['name' => $customer->getName()], $customers->toArray());

            return $reason
                ->setIdentifiers(array_column($names, 'name'))
                ->setCountedType('customer')
            ;
        }

        return null;
    }
}
