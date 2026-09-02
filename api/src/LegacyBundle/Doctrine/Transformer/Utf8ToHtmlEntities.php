<?php

declare(strict_types=1);

namespace LegacyBundle\Doctrine\Transformer;

class Utf8ToHtmlEntities
{
    public function __invoke($string, array $options)
    {
        // convert multibytes strings to html entities
        return mb_encode_numericentity(
            htmlspecialchars_decode(
                htmlentities((string) $string, \ENT_NOQUOTES, 'UTF-8', false), \ENT_NOQUOTES
            ), [0x80, 0x10FFFF, 0, ~0],
            'UTF-8'
        );
    }
}
