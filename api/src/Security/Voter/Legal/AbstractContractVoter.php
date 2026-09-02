<?php

declare(strict_types=1);

namespace App\Security\Voter\Legal;

use App\Entity\Directory\People;
use App\Entity\Legal\Contract;
use App\Repository\Common\SubscriptionRepository;
use App\Repository\Directory\PeopleRepository;
use App\Security\Voter\AbstractVoter;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Vote;

abstract class AbstractContractVoter extends AbstractVoter
{
    protected const string FILES = 'CONTRACT_UPLOAD_DOWNLOAD_FILES';
    protected const string EDIT = 'CONTRACT_EDIT_VOTER';

    public static function getSubscribedServices(): array
    {
        return [
            ...parent::getSubscribedServices(),
            SubscriptionRepository::class,
            PeopleRepository::class,
        ];
    }

    final protected function voteOnAttribute(string $attribute, $subject, TokenInterface $token, ?Vote $vote = null): bool
    {
        $user = $token->getUser();

        if (!$user instanceof People) {
            return false;
        }

        if ($this->getSecurity()->isGranted('MOO_CRS')
            || $this->getSecurity()->isGranted('MKU_CRS')
            || $this->getSecurity()->isGranted('FEATURE_FULL_CONTRACT_ACCESS')
        ) {
            return true;
        }

        return match ($attribute) {
            self::FILES => $this->canUploadAndDownloadFiles($subject, $user),
            self::EDIT => $this->canEdit($subject, $user),
            default => false,
        };
    }

    abstract protected function canUploadAndDownloadFiles(Contract $contract, People $user): bool;

    abstract protected function canEdit(Contract $contract, People $user): bool;

    protected function isUserSubscriber(Contract $contract, People $user): bool
    {
        $subscriptions = $this->serviceLocator
            ->get(SubscriptionRepository::class)
            ->findByResource($contract);

        foreach ($subscriptions as $subscription) {
            if ($subscription->getUser() === $user) {
                return true;
            }
        }

        return false;
    }

    protected function isSupervisorOf(People $manager, People $employee): bool
    {
        return \in_array($manager, $employee->getHierarchy(), true);
    }

    protected function isDivisionRepresentative(Contract $contract, People $user): bool
    {
        foreach ($contract->getDivisions() as $division) {
            if ($division->getRepresentatives()->contains($user)) {
                return true;
            }
        }

        return false;
    }

    protected function getContractAccessUsers(Contract $contract): array
    {
        $users = [$contract->owner];

        $subscriptions = $this->serviceLocator
            ->get(SubscriptionRepository::class)
            ->findByResource($contract);

        foreach ($subscriptions as $subscription) {
            if ($subscription->getUser() instanceof People) {
                $users[] = $subscription->getUser();
            }
        }

        return array_unique($users, \SORT_REGULAR);
    }
}
