<?php

declare(strict_types=1);

namespace Alvest\TwigHelper\Twig\Extension;

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

    /**
     * @param string $value
     * @param string $string
     *
     * @return string
     */
    public function concat($value, $string)
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
