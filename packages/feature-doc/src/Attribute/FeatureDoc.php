<?php

declare(strict_types=1);

namespace Alvest\FeatureDoc\Attribute;

#[\Attribute(\Attribute::TARGET_CLASS)]
final class FeatureDoc
{
    public function __construct(
        /**
         * Relative path to the markdown documentation file
         * Example: documentation/user_registration.md.
         */
        public readonly string $path,
    ) {
    }
}
