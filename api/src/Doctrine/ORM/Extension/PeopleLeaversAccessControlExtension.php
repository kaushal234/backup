<?php

declare(strict_types=1);

namespace App\Doctrine\ORM\Extension;

/**
 * Restricts the leavers collection to people linked to update tasks owned by
 * modules the current user administrates, unless they have
 * FEATURE_FILTER_PEOPLE_LEAVING (granted to HR & MIS).
 *
 * Only active for the leavers query (identified by the
 * peopleCurrentlyOrFutureDisabled query parameter).
 */
final class PeopleLeaversAccessControlExtension extends AbstractPeopleLifecycleAccessControlExtension
{
    protected function getQueryParameter(): string
    {
        return 'peopleCurrentlyOrFutureDisabled';
    }

    protected function getRequiredFeature(): string
    {
        return 'FEATURE_FILTER_PEOPLE_LEAVING';
    }
}
