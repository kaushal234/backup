<?php

declare(strict_types=1);

namespace App;

enum Locale: string
{
    case French = 'fr';
    case English = 'en';
    case Chinese = 'zh-CN';

    public static function isAvailable(?string $locale): bool
    {
        if (null === $locale) {
            return false;
        }

        return null !== self::tryFrom($locale);
    }
}
