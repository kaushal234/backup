<?php

declare(strict_types=1);

namespace App\AI\Dto\Activity;

final readonly class RelatedEntityModel
{
    public function __construct(
        public string $type,
        public int $item,
    ) {
    }
}
