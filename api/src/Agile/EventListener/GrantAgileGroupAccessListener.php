<?php

declare(strict_types=1);

namespace App\Agile\EventListener;

use App\Agile\Event\GrantAgileGroupAccessEvent;
use App\Agile\SynchronizationFilters;
use App\Entity\Acl;
use App\Entity\Group;
use App\Repository\Directory\PeopleRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

#[AsEventListener]
class GrantAgileGroupAccessListener
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly PeopleRepository $peopleRepository,
    ) {
    }

    public function __invoke(GrantAgileGroupAccessEvent $event): void
    {
        $people = $event->people;

        $isConcernedBySynchronization = $this->peopleRepository->searchPeopleForAgileSynchronization(
            SynchronizationFilters::EXCLUDE_DIVISIONS_ID,
            SynchronizationFilters::EXCLUDE_BUSINESS_UNITS_ID,
            SynchronizationFilters::EXCLUDE_POSITIONS_ID,
            SynchronizationFilters::EXCLUDE_PEOPLE_ID,
            $people->getId()
        );

        if (empty($isConcernedBySynchronization)) {
            return;
        }

        $alreadyInGroup = $people->getAcls()->exists(
            static fn ($key, Acl $acl) => 'ACL_AUTH_AGILE' === $acl->getGroup()?->getName()
        );

        if ($alreadyInGroup) {
            return;
        }

        $agileGroup = $this->entityManager->getRepository(Group::class)->findOneBy([
            'name' => 'ACL_AUTH_AGILE',
        ]);
        $acl = new Acl();
        $acl->setUser($people);
        $acl->setGroup($agileGroup);
        $people->addAcl($acl);
        $this->entityManager->persist($acl);
    }
}
