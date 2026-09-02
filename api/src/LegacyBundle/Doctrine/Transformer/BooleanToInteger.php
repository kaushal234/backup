<?php

declare(strict_types=1);

namespace LegacyBundle\Doctrine\Transformer;

class BooleanToInteger
{
    public function __invoke($flag, array $options)
    {
        return (int) $flag;
    }
}
