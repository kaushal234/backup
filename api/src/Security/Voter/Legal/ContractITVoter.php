<?php

declare(strict_types=1);

namespace App\Security\Voter\Legal;

use App\Entity\Directory\People;
use App\Entity\Legal\Category;
use App\Entity\Legal\Contract;
use App\Repository\Directory\PeopleRepository;

class ContractITVoter extends AbstractContractVoter
{
    protected function supports(string $attribute, $subject): bool
    {
        return \in_array($attribute, [self::FILES, self::EDIT], true)
            && $subject instanceof Contract
            && Category::IP_IT === $subject->subCategory->category->name;
    }

    protected function canUploadAndDownloadFiles(Contract $contract, People $user): bool
    {
        if ($this->canEdit($contract, $user)) {
            return true;
        }

        foreach ($this->getContractAccessUsers($contract) as $accessUser) {
            if ($this->isSupervisorOf($user, $accessUser)) {
                return true;
            }
        }

        $businessUnits = $contract->getBusinessUnits();
        if (!$businessUnits->isEmpty()) {
            $businessUnitIds = [];

            foreach ($businessUnits as $businessUnit) {
                if (null !== $businessUnit->getId()) {
                    $businessUnitIds[] = $businessUnit->getId();
                }
            }

            if ([] !== $businessUnitIds) {
                if (!empty(
                    $this->serviceLocator
                        ->get(PeopleRepository::class)
                        ->findSubordinatesWithAnyFeatureName(
                            $user,
                            ['FEATURE_CATEGORY_IT_CONTRACT_BU_ACCESS'],
                            5,
                            $businessUnitIds
                        )
                )) {
                    return true;
                }
            }
        }

        return
            $this->getSecurity()->isGranted('FEATURE_CATEGORY_IT_CONTRACT_READ_ACCESS')
            || !empty(
                $this->serviceLocator
                    ->get(PeopleRepository::class)
                    ->findSubordinatesWithAnyFeatureName(
                        $user,
                        [
                            'FEATURE_CATEGORY_IT_CONTRACT_ACCESS',
                            'FEATURE_CATEGORY_IT_CONTRACT_READ_ACCESS',
                        ]
                    )
            );
    }

    protected function canEdit(Contract $contract, People $user): bool
    {
        foreach ($contract->getBusinessUnits() as $businessUnit) {
            if (null === $businessUnit->getId()) {
                continue;
            }

            if ($this->getSecurity()->isGranted(
                'FEATURE_CATEGORY_IT_CONTRACT_BU_ACCESS_'.$businessUnit->getLocation()->getId()
            )) {
                return true;
            }
        }

        return
            $user === $contract->owner
            || $this->isUserSubscriber($contract, $user)
            || $this->getSecurity()->isGranted('FEATURE_CATEGORY_IT_CONTRACT_ACCESS');
    }
}
