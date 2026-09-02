<?php

declare(strict_types=1);

namespace App\Security\Voter\Legal;

use App\Entity\Directory\People;
use App\Entity\Legal\Category;
use App\Entity\Legal\Contract;
use App\Entity\Legal\SubCategory;
use App\Repository\Directory\PeopleRepository;

class ContractInsuranceVoter extends AbstractContractVoter
{
    protected function supports(string $attribute, $subject): bool
    {
        return \in_array($attribute, [self::FILES, self::EDIT], true)
            && $subject instanceof Contract
            && Category::INSURANCES === $subject->subCategory->category->name;
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

        return match ($contract->subCategory->name) {
            SubCategory::BUILDING_PROPERTY_DAMAGES_MASTER_POLICY => $this->checkBuAccess(
                $contract,
                $user,
                'FEATURE_SUB_CATEGORY_BUILDING_CONTRACT_BU_ACCESS',
                'FEATURE_SUB_CATEGORY_BUILDING_CONTRACT_ACCESS'
            ),

            SubCategory::CAR_LOCAL_POLICIES => $this->checkBuAccess(
                $contract,
                $user,
                'FEATURE_SUB_CATEGORY_CAR_CONTRACT_BU_ACCESS',
                'FEATURE_SUB_CATEGORY_CAR_CONTRACT_ACCESS'
            ),

            SubCategory::DO_POLICIES => !empty(
                $this->serviceLocator
                    ->get(PeopleRepository::class)
                    ->findSubordinatesWithAnyFeatureName(
                        $user,
                        ['FEATURE_SUB_CATEGORY_DO_CONTRACT_ACCESS']
                    )
            ),

            SubCategory::GENERAL_LIABILITY => $this->checkBuAccess(
                $contract,
                $user,
                'FEATURE_SUB_CATEGORY_GENERAL_LIABILITY_CONTRACT_BU_ACCESS'
            ),

            SubCategory::AERO_LIABILITY => $this->checkBuAccess(
                $contract,
                $user,
                'FEATURE_SUB_CATEGORY_AERO_LIABILITY_CONTRACT_BU_ACCESS',
            ),

            SubCategory::WORKER_COMPENSATION_LOCAL_POLICIES => $this->checkBuAccess(
                $contract,
                $user,
                'FEATURE_SUB_CATEGORY_WORKER_COMPENSATION_CONTRACT_BU_ACCESS',
                'FEATURE_SUB_CATEGORY_WORKER_COMPENSATION_CONTRACT_ACCESS'
            ),

            default => false,
        };
    }

    protected function canEdit(Contract $contract, People $user): bool
    {
        if ($user === $contract->owner || $this->isUserSubscriber($contract, $user)) {
            return true;
        }

        return match ($contract->subCategory->name) {
            SubCategory::BUILDING_PROPERTY_DAMAGES_MASTER_POLICY => $this->checkBuEditAccess(
                $contract,
                $user,
                'FEATURE_SUB_CATEGORY_BUILDING_CONTRACT_BU_EDIT_ACCESS'
            ),

            SubCategory::CAR_LOCAL_POLICIES => $this->checkBuEditAccess(
                $contract,
                $user,
                'FEATURE_SUB_CATEGORY_CAR_CONTRACT_BU_EDIT_ACCESS'
            ),

            SubCategory::DO_POLICIES => $this->getSecurity()->isGranted('FEATURE_SUB_CATEGORY_DO_CONTRACT_ACCESS'),

            SubCategory::GENERAL_LIABILITY => $this->getSecurity()->isGranted('FEATURE_SUB_CATEGORY_GENERAL_LIABILITY_CONTRACT_ACCESS'),

            SubCategory::AERO_LIABILITY => $this->getSecurity()->isGranted('FEATURE_SUB_CATEGORY_AERO_LIABILITY_CONTRACT_ACCESS'),

            SubCategory::WORKER_COMPENSATION_LOCAL_POLICIES => $this->checkBuEditAccess(
                $contract,
                $user,
                'FEATURE_SUB_CATEGORY_WORKER_COMPENSATION_CONTRACT_BU_EDIT_ACCESS'
            ),

            default => false,
        };
    }

    protected function getContractAccessUsers(Contract $contract): array
    {
        $users = parent::getContractAccessUsers($contract);

        if (\in_array(
            $contract->subCategory->name,
            [
                SubCategory::CAR_LOCAL_POLICIES,
                SubCategory::BUILDING_PROPERTY_DAMAGES_MASTER_POLICY,
            ],
            true
        )) {
            foreach ($contract->getBusinessUnits() as $businessUnit) {
                if ($businessUnit->getRepresentative() instanceof People) {
                    $users[] = $businessUnit->getRepresentative();
                }
            }
        }

        return $users;
    }

    private function checkBuAccess(
        Contract $contract,
        People $user,
        ?string $buFeature = null,
        ?string $globalFeature = null
    ): bool {
        $businessUnitIds = [];

        if (null !== $buFeature) {
            foreach ($contract->getBusinessUnits() as $bu) {
                if (null === $bu->getId()) {
                    continue;
                }

                $businessUnitIds[] = $bu->getId();

                if ($this->getSecurity()->isGranted($buFeature.'_'.$bu->getLocation()->getId())) {
                    return true;
                }
            }

            if ([] !== $businessUnitIds) {
                if (!empty(
                    $this->serviceLocator
                        ->get(PeopleRepository::class)
                        ->findSubordinatesWithAnyFeatureName(
                            $user,
                            [$buFeature],
                            5,
                            $businessUnitIds
                        )
                )) {
                    return true;
                }
            }
        }

        return null !== $globalFeature
            && $this->getSecurity()->isGranted($globalFeature);
    }

    private function checkBuEditAccess(
        Contract $contract,
        People $user,
        string $buEditFeature
    ): bool {
        if ($this->isDivisionRepresentative($contract, $user)) {
            return true;
        }

        foreach ($contract->getBusinessUnits() as $bu) {
            if (null === $bu->getId()) {
                continue;
            }

            if ($bu->getRepresentative() === $user) {
                return true;
            }

            if ($this->getSecurity()->isGranted($buEditFeature.'_'.$bu->getLocation()->getId())) {
                return true;
            }
        }

        return false;
    }
}
