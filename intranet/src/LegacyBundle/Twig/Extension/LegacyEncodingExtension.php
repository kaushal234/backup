<?php

declare(strict_types=1);

namespace LegacyBundle\Twig\Extension;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class LegacyEncodingExtension extends AbstractExtension
{
    /**
     * {@inheritdoc}
     */
    public function getFilters(): array
    {
        return [
            new TwigFilter('legacy_encode', fn ($input) => $this->legacyEncode($input)),
        ];
    }

    public function legacyEncode($input): string
    {
        return mb_convert_encoding((string) $input, 'HTML-ENTITIES', 'UTF-8');
    }
}
