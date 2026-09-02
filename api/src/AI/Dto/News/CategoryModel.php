<?php

declare(strict_types=1);

namespace App\AI\Dto\News;

final readonly class CategoryModel
{
    public function __construct(
        public string $name,
    ) {
    }
}
