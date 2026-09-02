<?php

declare(strict_types=1);

namespace App\Security\Voter\Legal;

use App\Entity\Directory\People;
use App\Entity\Legal\Category;
use App\Entity\Legal\Contract;
use App\Repository\Directory\PeopleRepository;

class ContractMaVoter extends AbstractContractVoter
{
    protected function supports(string $attribute, $subject): bool
    {
        return \in_array($attribute, [self::FILES, self::EDIT], true)
            && $subject instanceof Contract
            && Category::MA === $subject->subCategory->category->name;
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

        return !empty(
            $this->serviceLocator
                ->get(PeopleRepository::class)
                ->findSubordinatesWithAnyFeatureName(
                    $user,
                    ['FEATURE_CATEGORY_MA_CONTRACT_EDIT_ACCESS']
                )
        );
    }

    protected function canEdit(Contract $contract, People $user): bool
    {
        return
            $user === $contract->owner
            || $this->isUserSubscriber($contract, $user)
            || $this->getSecurity()->isGranted('FEATURE_CATEGORY_MA_CONTRACT_EDIT_ACCESS');
    }
}
