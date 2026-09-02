<?php

declare(strict_types=1);

namespace App\DeletionVoter;

use App\DeletionVoter\Reason\RejectedDeletionDetailedReason;
use App\DeletionVoter\Reason\RejectedDeletionReason;
use App\Entity\Directory\JuridicalLocation;
use App\Repository\Directory\LocationRepository;

class JuridicalLocationDeletionVoter implements DeletionVoterInterface
{
    private readonly LocationRepository $locationRepository;

    public function __construct(LocationRepository $locationRepository)
    {
        $this->locationRepository = $locationRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function supports($entity): bool
    {
        return $entity instanceof JuridicalLocation;
    }

    /**
     * {@inheritdoc}
     *
     * @param JuridicalLocation $entity
     */
    public function abstainToDeletion($entity): ?RejectedDeletionReason
    {
        $reason = new RejectedDeletionDetailedReason();
        $reason->setType('juridical location')->setLabel((string) $entity->getName());

        if ((bool) ($ids = $this->locationRepository->getIdentifiersForJuridicalLocation($entity))) {
            $type = \count($ids) > 1 ? 'Locations' : 'Location';

            return $reason
                ->setIdentifiers(array_column($ids, 'id'))
                ->setCountedType($type)
            ;
        }

        return null;
    }
}
