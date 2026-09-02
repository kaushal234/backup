<?php

declare(strict_types=1);

namespace App\MessageHandler\Directory;

use ApiPlatform\Metadata\IriConverterInterface;
use App\Entity\Acl;
use App\Entity\Directory\Division;
use App\Entity\Directory\People;
use App\Entity\Directory\Position;
use App\Entity\Group;
use App\Message\Directory\GroupPositionUpdate;
use App\Repository\Directory\PeopleRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class GroupPositionUpdateHandler
{
    private IriConverterInterface $iriConverter;
    private EntityManagerInterface $entityManager;

    public function __construct(IriConverterInterface $iriConverter, EntityManagerInterface $entityManager)
    {
        $this->iriConverter = $iriConverter;
        $this->entityManager = $entityManager;
    }

    public function __invoke(GroupPositionUpdate $message)
    {
        /** @var Position $position */
        $position = $this->iriConverter->getResourceFromIri($message->getPosition());

        /** @var Division $division */
        $division = $this->iriConverter->getResourceFromIri($message->getDivision());

        $groupRepository = $this->entityManager->getRepository(Group::class);
        /** @var PeopleRepository $peopleRepository */
        $peopleRepository = $this->entityManager->getRepository(People::class);
        $existingAcls = [];
        /** @var People $people */
        foreach ($peopleRepository->findByPositionByDivision($position, $division) as $people) {
            foreach ($people->getAcls() as $acl) {
                foreach ($message->getGroupToDelete() as $groupToDelete) {
                    if (null !== $acl->getGroup() && $acl->getGroup()->getId() === (int) $groupToDelete) {
                        $this->entityManager->remove($acl);
                        continue 2;
                    }
                }

                if (null !== $acl->getLocation()) {
                    $existingAcls[] = \sprintf('%s-%s', $acl->getGroup()->getId(), $acl->getLocation()->getId());
                }
            }

            foreach ($message->getGroupToAdd() as $groupToAdd) {
                if (!\in_array(\sprintf('%s-%s', $groupToAdd, $people->getBusinessUnit()->getLocation()->getId()), $existingAcls, true)) {
                    $aclToAdd = (new Acl())
                        ->setGroup($groupRepository->find($groupToAdd))
                        ->setUser($people)
                        ->setLocation($people->getBusinessUnit()->getLocation());
                    $this->entityManager->persist($aclToAdd);
                }
            }
        }
        $this->entityManager->flush();
    }
}
