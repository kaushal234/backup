<?php

declare(strict_types=1);

namespace App\Manager\Service;

use ApiPlatform\Metadata\IriConverterInterface;
use App\Entity\Common\Subscription;
use App\Entity\Service\TechnicianOnCall;
use App\Entity\Service\TechnicianOnCallTag;
use App\Repository\Common\SubscriptionRepository;
use App\Repository\Directory\PeopleRepository;
use Doctrine\ORM\EntityManagerInterface;

class TechnicianOnCallTagFollowerPolicy
{
    public function __construct(
        private readonly IriConverterInterface $iriConverter,
        private readonly EntityManagerInterface $entityManager,
        private readonly SubscriptionRepository $subscriptionRepository,
        private readonly PeopleRepository $peopleRepository,
    ) {
    }

    public function applyRule(TechnicianOnCall $technicianOnCall): void
    {
        $groups = array_values(array_unique(array_filter(array_map(
            static fn (TechnicianOnCallTag $tag): ?string => match ($tag->getName()) {
                TechnicianOnCallTag::IBS => 'SRME_IBS',
                TechnicianOnCallTag::LINK => 'SRME_LINK',
                default => null,
            },
            $technicianOnCall->getTags()->toArray(),
        ))));

        if ([] === $groups) {
            return;
        }

        $users = $this->peopleRepository->findGroupsMembers($groups);
        $resourceIri = $this->iriConverter->getIriFromResource($technicianOnCall);

        foreach ($users as $user) {
            if ($this->subscriptionRepository->isFollowingResource($user, $technicianOnCall)) {
                continue;
            }

            $this->entityManager->persist(
                (new Subscription())->setUser($user)->setResource($resourceIri)
            );
        }

        $this->entityManager->flush();
    }
}
