<?php

declare(strict_types=1);

namespace App\AI\Dto;

final class Result
{
    public function __construct(
        public int $id,
        public ?string $module = null,
        public ?string $description = null,
        public ?string $link = null,
    ) {
    }
}
