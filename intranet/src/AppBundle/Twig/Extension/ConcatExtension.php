<?php

declare(strict_types=1);

namespace AppBundle\Twig\Extension;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class ConcatExtension extends AbstractExtension
{
    public function getFilters(): array
    {
        return [
            new TwigFilter('concat', [$this, 'concat']),
        ];
    }

    public function concat($value, $string): string
    {
        return $value.$string;
    }

    /**
     * {@inheritdoc}
     */
    public function getName(): string
    {
        return 'concat';
    }
}
