<?php

declare(strict_types=1);

namespace LegacyBundle\Doctrine\Transformer;

class ArrayToString
{
    public function __invoke($array, array $options): string
    {
        if (!\is_array($array) || !$array) {
            return '';
        }

        return implode($options['glue'] ?? ',', $array);
    }
}
