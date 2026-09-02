<?php

declare(strict_types=1);

namespace AppBundle\Twig\Extension;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class ArraySearchExtension extends AbstractExtension
{
    public function getFilters(): array
    {
        return [
            new TwigFilter('arrayKeySearchInChild', [$this, 'arrayKeySearchInChild']),
        ];
    }

    public function arrayKeySearchInChild($array, $value): bool|int|string
    {
        foreach ($array as $status => $linkStatuses) {
            if (\in_array($value, $linkStatuses, true)) {
                return array_search($status, array_keys($array), true);
            }
        }

        throw new \RuntimeException(\sprintf('Status %s not find on the statuses configuration', $value));
    }
}
