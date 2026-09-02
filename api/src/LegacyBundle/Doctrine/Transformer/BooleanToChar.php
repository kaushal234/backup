<?php

declare(strict_types=1);

namespace LegacyBundle\Doctrine\Transformer;

class BooleanToChar
{
    public function __invoke($flag, array $options)
    {
        return $flag ?
            ($options['trueValue'] ?? 'Y') :
            ($options['falseValue'] ?? 'N');
    }
}
