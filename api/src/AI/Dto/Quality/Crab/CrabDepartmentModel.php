<?php

declare(strict_types=1);

namespace App\AI\Dto\Quality\Crab;

final readonly class CrabDepartmentModel
{
    public function __construct(
        public string $name,
    ) {
    }
}
