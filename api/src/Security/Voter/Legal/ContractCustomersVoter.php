<?php

declare(strict_types=1);

namespace App\Security\Voter\Legal;

use App\Entity\Directory\People;
use App\Entity\Legal\Category;
use App\Entity\Legal\Contract;
use App\Repository\Directory\PeopleRepository;

class ContractCustomersVoter extends AbstractContractVoter
{
    protected function supports(string $attribute, $subject): bool
    {
        return \in_array($attribute, [self::FILES, self::EDIT], true)
            && $subject instanceof Contract
            && Category::CUSTOMERS === $subject->subCategory->category->name;
    }

    protected function canUploadAndDownloadFiles(Contract $contract, People $user): bool
    {
        if ($this->canEdit($contract, $user)) {
            return true;
        }

        foreach ($contract->getBusinessUnits() as $businessUnit) {
            if ($businessUnit->getRepresentative() instanceof People
                && $user === $businessUnit->getRepresentative()
            ) {
                return true;
            }
        }

        if ($this->isDivisionRepresentative($contract, $user)) {
            return true;
        }

        foreach ($this->getContractAccessUsers($contract) as $accessUser) {
            if ($this->isSupervisorOf($user, $accessUser)) {
                return true;
            }
        }

        return
            $this->getSecurity()->isGranted('FEATURE_CATEGORY_CUSTOMER_CONTRACT_READ_ACCESS')
            || !empty(
                $this->serviceLocator
                    ->get(PeopleRepository::class)
                    ->findSubordinatesWithAnyFeatureName(
                        $user,
                        ['FEATURE_CATEGORY_CUSTOMER_CONTRACT_READ_ACCESS']
                    )
            );
    }

    protected function canEdit(Contract $contract, People $user): bool
    {
        foreach ($contract->getCustomers() as $customer) {
            if ($customer->getMainSalesRepresentative()->asm === $user) {
                return true;
            }
        }

        return
            $user === $contract->owner
            || $this->isUserSubscriber($contract, $user);
    }

    protected function getContractAccessUsers(Contract $contract): array
    {
        $users = parent::getContractAccessUsers($contract);

        foreach ($contract->getBusinessUnits() as $businessUnit) {
            if ($businessUnit->getRepresentative() instanceof People) {
                $users[] = $businessUnit->getRepresentative();
            }
        }

        foreach ($contract->getCustomers() as $customer) {
            if ($customer->getMainSalesRepresentative()->asm instanceof People) {
                $users[] = $customer->getMainSalesRepresentative()->asm;
            }
        }

        return array_unique($users, \SORT_REGULAR);
    }
}
