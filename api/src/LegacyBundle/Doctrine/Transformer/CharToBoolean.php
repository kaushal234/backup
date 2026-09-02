<?php

declare(strict_types=1);

namespace LegacyBundle\Doctrine\Transformer;

class CharToBoolean
{
    public function __invoke($flag, array $options)
    {
        return $options['values'][$flag];
    }
}
