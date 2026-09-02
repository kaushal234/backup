<?php

declare(strict_types=1);

namespace AppBundle\Twig\Extension;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class SortExtension extends AbstractExtension
{
    public function getFilters(): array
    {
        return [
            new TwigFilter('sortBy', [$this, 'sortBy']),
        ];
    }

    public function sortBy($array, $property)
    {
        usort($array, static function ($item1, $item2) use ($property) {
            if ($item1[$property] === $item2[$property]) {
                return 0;
            }

            return $item1[$property] < $item2[$property] ? -1 : 1;
        });

        return $array;
    }
}
