<?php

declare(strict_types=1);

namespace App\Twig\Locale;

use App\Locale;
use Symfony\Component\Translation\LocaleSwitcher;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

final class LocaleExtension extends AbstractExtension
{
    public function __construct(
        private readonly LocaleSwitcher $localeSwitcher,
    ) {
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('get_available_locales', Locale::cases(...)),
            new TwigFunction('get_current_locale', $this->localeSwitcher->getLocale(...)),
        ];
    }
}
