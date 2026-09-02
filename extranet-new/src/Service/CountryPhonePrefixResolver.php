<?php

declare(strict_types=1);

namespace App\Service;

use libphonenumber\PhoneNumberUtil;

/**
 * Resolves the international dialing prefix (e.g. "+33") for a country,
 * given its ISO 3166-1 alpha-2 code, using libphonenumber.
 */
final class CountryPhonePrefixResolver
{
    public function resolve(?string $isoCode2): ?string
    {
        if (null === $isoCode2 || '' === $isoCode2) {
            return null;
        }

        $countryCode = PhoneNumberUtil::getInstance()->getCountryCodeForRegion(mb_strtoupper($isoCode2));

        if (0 === $countryCode) {
            return null;
        }

        return \sprintf('+%d', $countryCode);
    }
}
