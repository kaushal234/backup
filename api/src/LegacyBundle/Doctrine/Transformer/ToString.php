<?php

declare(strict_types=1);

namespace LegacyBundle\Doctrine\Transformer;

class ToString
{
    public function __invoke($input): string
    {
        return (string) $input;
    }
}
