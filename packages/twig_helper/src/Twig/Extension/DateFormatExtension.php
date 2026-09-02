<?php

declare(strict_types=1);

namespace Alvest\TwigHelper\Twig\Extension;

use DateTimeInterface;
use DateTimeZone;
use Twig\Environment;
use Twig\Extension\AbstractExtension;
use Twig\Extra\Intl\IntlExtension;
use Twig\TwigFilter;

final class DateFormatExtension extends AbstractExtension
{
    public function getFilters(): array
    {
        return [
            new TwigFilter('localizeddate', [$this, 'dateFormat'], ['needs_environment' => true]),
        ];
    }

    /**
     * @param DateTimeInterface|string|null  $date
     * @param DateTimeZone|string|false|null $timezone
     *
     * @throws \Twig\Error\RuntimeError
     */
    public function dateFormat(Environment $env, $date, ?string $dateFormat = 'medium', string $pattern = '', $timezone = null, string $calendar = 'gregorian', ?string $locale = null): string
    {
        $test = new IntlExtension();

        return $test->formatDateTime($env, $date, $dateFormat, 'medium', $pattern, $timezone, $calendar, $locale);
    }
}
