<?php

declare(strict_types=1);

namespace App\DeletionVoter;

use App\DeletionVoter\Reason\RejectedDeletionReason;

interface DeletionVoterInterface
{
    /**
     * Retrieves whether or not the voter can process the $entity.
     */
    public function supports($entity): bool;

    /**
     * Retrieves if the $entity is deletable.
     */
    public function abstainToDeletion($entity): ?RejectedDeletionReason;
}
