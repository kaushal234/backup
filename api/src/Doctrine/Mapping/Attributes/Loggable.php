<?php

declare(strict_types=1);

namespace App\Doctrine\Mapping\Attributes;

use App\Doctrine\Change;

#[\Attribute(\Attribute::TARGET_CLASS)]
final class Loggable
{
    public function __construct(
        public readonly array $on = [Change::ACTION_CREATE, Change::ACTION_UPDATE, Change::ACTION_DELETE],
        public readonly ?string $owner = null,
        public readonly string $ownerRelation = 'relation',
        public readonly bool $showIri = false,
    ) {
    }
}
