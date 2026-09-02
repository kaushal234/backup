<?php

declare(strict_types=1);

namespace App\AI\Dto;

final class ProductListOutput
{
    /**
     * @var array<array{name: string, family: string, description: string|null}>
     */
    public array $products = [];
}
