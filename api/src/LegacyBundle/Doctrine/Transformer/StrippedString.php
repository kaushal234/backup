<?php

declare(strict_types=1);

namespace LegacyBundle\Doctrine\Transformer;

class StrippedString
{
    public function __invoke($string, array $options)
    {
        if (!isset($options['search'])) {
            throw new \Exception('You should set the "search" option to use the stripped string transformer.');
        }
        $replacement = $options['replace'] ?? '';

        return str_replace($options['search'], $replacement, (string) $string);
    }
}
