<?php

declare(strict_types=1);

namespace App\DeletionVoter;

use App\DeletionVoter\Reason\RejectedDeletionDetailedReason;
use App\DeletionVoter\Reason\RejectedDeletionReason;
use App\Entity\Sales\CustomerRelationshipTeam;
use App\Repository\Sales\ExtranetUserAclRepository;

class CustomerRelationshipTeamDeletionVoter implements DeletionVoterInterface
{
    private readonly ExtranetUserAclRepository $extranetUserAclRepository;

    public function __construct(ExtranetUserAclRepository $extranetUserAclRepository)
    {
        $this->extranetUserAclRepository = $extranetUserAclRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function supports($entity): bool
    {
        return $entity instanceof CustomerRelationshipTeam;
    }

    /**
     * {@inheritdoc}
     *
     * @param CustomerRelationshipTeam $entity
     */
    public function abstainToDeletion($entity): ?RejectedDeletionReason
    {
        $reason = new RejectedDeletionDetailedReason();
        $reason->setType('customer relationship team')->setLabel((string) $entity->getId());

        if ((bool) ($ids = $this->extranetUserAclRepository->getIdentifiersForCustomerRelationshipTeam($entity))) {
            return $reason
                ->setIdentifiers(array_column($ids, 'id'))
                ->setCountedType('XU ACL')
            ;
        }

        return null;
    }
}
