<?php

declare(strict_types=1);

namespace ApiBundle\Iri;

use ApiBundle\Model\ApiData;

class Iri
{
    public static function id($item)
    {
        return self::getParts($item)['id'] ?: null;
    }

    public static function type($item)
    {
        return self::getParts($item)['type'] ?: null;
    }

    protected static function getParts($item)
    {
        if ($item instanceof ApiData) {
            return ['id' => $item->getIriId(), 'type' => $item->getIriType()];
        }
        $iri = \is_array($item) ? $item['@id'] : $item;
        $parts = explode('/', (string) $iri);
        $id = array_pop($parts);

        return ['id' => $id, 'type' => implode('/', $parts)];
    }
}
