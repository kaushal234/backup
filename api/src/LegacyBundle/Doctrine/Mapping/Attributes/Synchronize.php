<?php

declare(strict_types=1);

namespace LegacyBundle\Doctrine\Mapping\Attributes;

#[\Attribute(\Attribute::TARGET_CLASS)]

class Synchronize
{
    /**
     * Setting $forceUpdate to true will never trigger an INSERT when persisting the entity but will UPDATE the legacy table
     * To be used in combination with overriding of getLegacyId() to return the identifier.
     */
    public function __construct(
        public readonly string $table,
        public readonly bool $isNestedEntity = false,
        public readonly bool $forceUpdate = false,
    ) {
    }
}
