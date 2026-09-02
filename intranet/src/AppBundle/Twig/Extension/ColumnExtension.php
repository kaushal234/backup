<?php

declare(strict_types=1);

namespace AppBundle\Twig\Extension;

use Twig\Error\RuntimeError;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

/**
 * To be dropped when upgrading to Twig 2.0.
 */
class ColumnExtension extends AbstractExtension
{
    public function getFilters(): array
    {
        return [
            new TwigFilter('column', [$this, 'column']),
        ];
    }

    public function column($array, $name, $index = null): array
    {
        if ($array instanceof \Traversable) {
            $array = iterator_to_array($array);
        } elseif (!\is_array($array)) {
            throw new RuntimeError(\sprintf('The column filter only works with arrays or "Traversable", got "%s" as first argument.', \gettype($array)));
        }

        return array_column($array, $name, $index);
    }
}
