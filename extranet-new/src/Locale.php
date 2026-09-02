<?php

declare(strict_types=1);

namespace App;

enum Locale: string
{
    case French = 'fr';
    case English = 'en';
    case Chinese = 'zh-CN';

    public function toApiLanguage(): string
    {
        return match ($this) {
            self::Chinese => 'zh',
            default => $this->value,
        };
    }

    public static function fromApiLanguage(?string $Language): self
    {
        return match ($Language) {
            'zh' => self::Chinese,
            default => self::tryFrom($Language ?? '') ?? self::English,
        };
    }

    public static function isAvailable(?string $locale): bool
    {
        if (null === $locale) {
            return false;
        }

        return null !== self::tryFrom($locale);
    }
}
