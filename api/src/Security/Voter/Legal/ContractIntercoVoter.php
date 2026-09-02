<?php

declare(strict_types=1);

namespace App\Security\Voter\Legal;

use App\Entity\Directory\People;
use App\Entity\Legal\Category;
use App\Entity\Legal\Contract;
use App\Entity\Legal\SubCategory;
use App\Repository\Directory\PeopleRepository;

class ContractIntercoVoter extends AbstractContractVoter
{
    protected function supports(string $attribute, $subject): bool
    {
        return \in_array($attribute, [self::FILES, self::EDIT], true)
            && $subject instanceof Contract
            && Category::INTERCO === $subject->subCategory->category->name;
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

            foreach ($businessUnits as $bu) {
                if (null !== $bu->getId()) {
                    $businessUnitIds[] = $bu->getId();
                }
            }

            if ([] !== $businessUnitIds) {
                if (!empty(
                    $this->serviceLocator
                        ->get(PeopleRepository::class)
                        ->findSubordinatesWithAnyFeatureName(
                            $user,
                            ['FEATURE_CATEGORY_INTERCO_CONTRACT_BU_ACCESS'],
                            5,
                            $businessUnitIds
                        )
                )) {
                    return true;
                }

                if (\in_array($contract->subCategory->name, [
                    SubCategory::CASH_POOLING_CONTRACT,
                    SubCategory::MANAGEMENT_FEES_AGREEMENT,
                ], true)) {
                    if (!empty(
                        $this->serviceLocator
                            ->get(PeopleRepository::class)
                            ->findSubordinatesWithAnyFeatureName(
                                $user,
                                ['FEATURE_CATEGORY_INTERCO_CONTRACT_SUB_BU_ACCESS'],
                                5,
                                $businessUnitIds
                            )
                    )) {
                        return true;
                    }
                }
            }
        }

        if (\in_array($contract->subCategory->name, [
            SubCategory::CASH_POOLING_CONTRACT,
            SubCategory::MANAGEMENT_FEES_AGREEMENT,
        ], true)) {
            if (!empty(
                $this->serviceLocator
                    ->get(PeopleRepository::class)
                    ->findSubordinatesWithAnyFeatureName(
                        $user,
                        ['FEATURE_CATEGORY_INTERCO_CONTRACT_SUB_ACCESS']
                    )
            )) {
                return true;
            }
        }

        return
            $this->getSecurity()->isGranted('FEATURE_CATEGORY_INTERCO_CONTRACT_ACCESS')
            || !empty(
                $this->serviceLocator
                    ->get(PeopleRepository::class)
                    ->findSubordinatesWithAnyFeatureName(
                        $user,
                        ['FEATURE_CATEGORY_INTERCO_CONTRACT_ACCESS']
                    )
            );
    }

    protected function canEdit(Contract $contract, People $user): bool
    {
        if ($contract->owner === $user || $this->isUserSubscriber($contract, $user)) {
            return true;
        }

        if ($this->isDivisionRepresentative($contract, $user)) {
            return true;
        }

        foreach ($contract->getBusinessUnits() as $bu) {
            if (null === $bu->getId()) {
                continue;
            }

            if (null !== $bu->getRepresentative() && $user->getId() === $bu->getRepresentative()->getId()) {
                return true;
            }

            if ($this->getSecurity()->isGranted('FEATURE_CATEGORY_INTERCO_CONTRACT_BU_ACCESS_'.$bu->getLocation()->getId())) {
                return true;
            }

            if (\in_array($contract->subCategory->name, [
                SubCategory::CASH_POOLING_CONTRACT,
                SubCategory::MANAGEMENT_FEES_AGREEMENT,
            ], true)) {
                if ($this->getSecurity()->isGranted('FEATURE_CATEGORY_INTERCO_CONTRACT_SUB_BU_ACCESS_'.$bu->getLocation()->getId())) {
                    return true;
                }
            }
        }

        if (\in_array($contract->subCategory->name, [
            SubCategory::CASH_POOLING_CONTRACT,
            SubCategory::MANAGEMENT_FEES_AGREEMENT,
        ], true)) {
            if ($this->getSecurity()->isGranted('FEATURE_CATEGORY_INTERCO_CONTRACT_SUB_ACCESS')) {
                return true;
            }
        }

        return false;
    }
}
