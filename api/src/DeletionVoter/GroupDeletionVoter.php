<?php

declare(strict_types=1);

namespace App\DeletionVoter;

use App\DeletionVoter\Reason\RejectedDeletionDetailedReason;
use App\DeletionVoter\Reason\RejectedDeletionReason;
use App\Entity\Group;
use App\Repository\AclRepository;

class GroupDeletionVoter implements DeletionVoterInterface
{
    private readonly AclRepository $aclRepository;

    public function __construct(AclRepository $aclRepository)
    {
        $this->aclRepository = $aclRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function supports($entity): bool
    {
        return $entity instanceof Group;
    }

    /**
     * {@inheritdoc}
     *
     * @param Group $entity
     */
    public function abstainToDeletion($entity): ?RejectedDeletionReason
    {
        $reason = new RejectedDeletionDetailedReason();
        $reason->setType('group')->setLabel((string) $entity->getName());

        if ((bool) ($ids = $this->aclRepository->getIdentifiersForGroup($entity))) {
            return $reason
                ->setIdentifiers(array_column($ids, 'id'))
                ->setCountedType('ACL')
            ;
        }

        return null;
    }
}
