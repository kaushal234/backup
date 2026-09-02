<?php

declare(strict_types=1);

namespace AppBundle\Twig\Extension;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class CountryToFlagExtension extends AbstractExtension
{
    public function countryToFlag(string $countryCode): string
    {
        return (string) preg_replace_callback(
            '/./',
            static fn (array $letter) => mb_chr(\ord($letter[0]) % 32 + 0x1F1E5),
            $countryCode
        );
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('country_to_flag', [$this, 'countryToFlag']),
        ];
    }
}
