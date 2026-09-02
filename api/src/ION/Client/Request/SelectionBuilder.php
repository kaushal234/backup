<?php

declare(strict_types=1);

namespace App\ION\Client\Request;

final class SelectionBuilder
{
    public const SELECTION_NODE = 'Selection';
    public const SELECTION_ATTRIBUTE_NODE = 'selectionAttribute';

    public function build(string $ionResource, array $fieldList = []): array
    {
        $nodes = [];
        foreach ($fieldList as $field) {
            $nodes[self::SELECTION_ATTRIBUTE_NODE][] = \sprintf('%s.%s', $ionResource, $field);
        }

        return $nodes;
    }
}
