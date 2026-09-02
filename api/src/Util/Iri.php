<?php

declare(strict_types=1);

namespace App\Util;

class Iri
{
    public static function id($item)
    {
        $iri = \is_array($item) ? $item['@id'] : $item;
        $parts = explode('/', (string) $iri);

        return array_pop($parts);
    }
}
