<?php

declare(strict_types=1);

namespace App\AI\Dto\Quality\NonConformity;

final readonly class ProcessModel
{
    public function __construct(
        public string $category,
        public string $description,
    ) {
    }
}
