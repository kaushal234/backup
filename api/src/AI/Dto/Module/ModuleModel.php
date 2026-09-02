<?php

declare(strict_types=1);

namespace App\AI\Dto\Module;

final readonly class ModuleModel
{
    public function __construct(
        public string $name,
    ) {
    }
}
