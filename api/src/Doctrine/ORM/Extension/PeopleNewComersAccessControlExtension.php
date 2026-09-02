<?php

declare(strict_types=1);

namespace App\Doctrine\ORM\Extension;

/**
 * Restricts the new comers collection to people linked to update tasks owned by
 * modules the current user administrates, unless they have
 * FEATURE_FILTER_PEOPLE_INCOMING (granted to HR & MIS).
 *
 * Only active for the new comers query (identified by the
 * peopleCurrentlyOrFutureEnabled query parameter).
 */
final class PeopleNewComersAccessControlExtension extends AbstractPeopleLifecycleAccessControlExtension
{
    protected function getQueryParameter(): string
    {
        return 'peopleCurrentlyOrFutureEnabled';
    }

    protected function getRequiredFeature(): string
    {
        return 'FEATURE_FILTER_PEOPLE_INCOMING';
    }
}
