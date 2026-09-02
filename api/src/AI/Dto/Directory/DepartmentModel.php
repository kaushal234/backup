<?php

declare(strict_types=1);

namespace App\AI\Dto\Directory;

final readonly class DepartmentModel
{
    public function __construct(
        public string $name,
        public bool $sso,
        public bool $factory,
    ) {
    }
}
