<?php

declare(strict_types=1);

namespace LegacyBundle\Doctrine\Transformer;

use Symfony\Component\Intl\Countries;
use Symfony\Component\Intl\Exception\MissingResourceException;

class CountryToString
{
    public function __invoke($countryCode, array $options): ?string
    {
        try {
            $country = Countries::getName((string) $countryCode);
        } catch (MissingResourceException $missingResourceException) {
            $country = '';
        }

        return $country;
    }
}
