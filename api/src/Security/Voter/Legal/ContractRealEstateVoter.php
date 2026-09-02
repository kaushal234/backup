<?php

declare(strict_types=1);

namespace App\Security\Voter\Legal;

use App\Entity\Directory\People;
use App\Entity\Legal\Category;
use App\Entity\Legal\Contract;
use App\Repository\Directory\PeopleRepository;

class ContractRealEstateVoter extends AbstractContractVoter
{
    protected function supports(string $attribute, $subject): bool
    {
        return \in_array($attribute, [self::FILES, self::EDIT], true)
            && $subject instanceof Contract
            && Category::REAL_ESTATE === $subject->subCategory->category->name;
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

                if ($this->getSecurity()->isGranted('FEATURE_CATEGORY_REAL_ESTATE_CONTRACT_BU_READ_ACCESS_'.$bu->getLocation()->getId())) {
                    return true;
                }
            }

            if ([] !== $businessUnitIds) {
                if ($this->serviceLocator
                    ->get(PeopleRepository::class)
                    ->findSubordinatesWithAnyFeatureName(
                        $user,
                        ['FEATURE_CATEGORY_REAL_ESTATE_CONTRACT_ACCESS', 'FEATURE_CATEGORY_REAL_ESTATE_CONTRACT_BU_READ_ACCESS'],
                        5,
                        $businessUnitIds
                    )
                ) {
                    return true;
                }
            }
        }

        return $this->getSecurity()->isGranted('FEATURE_CATEGORY_REAL_ESTATE_CONTRACT_READ_ACCESS');
    }

    protected function canEdit(Contract $contract, People $user): bool
    {
        if ($user === $contract->owner || $this->isUserSubscriber($contract, $user)) {
            return true;
        }

        if ($this->isDivisionRepresentative($contract, $user)) {
            return true;
        }

        foreach ($contract->getBusinessUnits() as $bu) {
            if (null === $bu->getId()) {
                continue;
            }

            if ($user === $bu->getRepresentative()) {
                return true;
            }

            if ($this->getSecurity()->isGranted('FEATURE_CATEGORY_REAL_ESTATE_CONTRACT_ACCESS_'.$bu->getLocation()->getId())) {
                return true;
            }
        }

        return false;
    }
}
