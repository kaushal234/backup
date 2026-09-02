<?php

declare(strict_types=1);

namespace LegacyBundle\Doctrine\ORM\Extension;

/**
 * Marks an entity as subject to Service Bulletin access control.
 * It can be applied directly to ServiceBulletin or to related resources (e.g., ServiceBulletinFile).
 */
#[\Attribute(\Attribute::TARGET_CLASS)]
final readonly class ServiceBulletinSecurityAware
{
    public function __construct(
        /**
         * The property name in the current entity that links to the parent ServiceBulletin.
         * Leave null if applied directly to the ServiceBulletin entity.
         */
        public ?string $parentIdProperty = null,
    ) {
    }
}
