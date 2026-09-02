<?php

declare(strict_types=1);

namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class StringExtensions extends AbstractExtension
{
    public function getFilters(): array
    {
        return [
            new TwigFilter('normalizeId', [$this, 'normalizeId']),
        ];
    }

    public function normalizeId(string $id): string
    {
        return str_replace(['/', ':', '.'], '-', $id);
    }
}
