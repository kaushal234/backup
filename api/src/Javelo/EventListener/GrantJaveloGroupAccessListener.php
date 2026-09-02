<?php

declare(strict_types=1);

namespace App\Javelo\EventListener;

use App\Entity\Acl;
use App\Entity\Group;
use App\Javelo\Event\GrantJaveloGroupAccessEvent;
use App\Javelo\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

#[AsEventListener]
class GrantJaveloGroupAccessListener
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly UserRepository $userRepository,
    ) {
    }

    public function __invoke(GrantJaveloGroupAccessEvent $event): void
    {
        $people = $event->people;

        $isConcernedBySynchronization = $this->userRepository->searchPeopleConcernedBySynchronization($people->getId());

        if (empty($isConcernedBySynchronization)) {
            return;
        }

        $alreadyInGroup = $people->getAcls()->exists(
            static fn ($key, Acl $acl) => 'ACL_AUTH_JAVELO' === $acl->getGroup()?->getName()
        );

        if (!$alreadyInGroup) {
            $javeloGroup = $this->entityManager->getRepository(Group::class)->findOneBy([
                'name' => 'ACL_AUTH_JAVELO',
            ]);
            $acl = new Acl();
            $acl->setUser($people);
            $acl->setGroup($javeloGroup);
            $people->addAcl($acl);
            $this->entityManager->persist($acl);
        }
    }
}
