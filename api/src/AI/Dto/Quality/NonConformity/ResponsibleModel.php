<?php

declare(strict_types=1);

namespace App\AI\Dto\Quality\NonConformity;

final readonly class ResponsibleModel
{
    public function __construct(
        public string $name,
    ) {
    }
}
