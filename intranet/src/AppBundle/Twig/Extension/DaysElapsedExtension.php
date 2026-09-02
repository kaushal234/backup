<?php

declare(strict_types=1);

namespace AppBundle\Twig\Extension;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class DaysElapsedExtension extends AbstractExtension
{
    public function getFunctions(): array
    {
        return [
            new TwigFunction('daysElapsed', [$this, 'getDaysElapsed']),
        ];
    }

    /**
     * @throws \DateMalformedStringException
     */
    public function getDaysElapsed(?string $startDate = null, ?string $endDate = null): int|string
    {
        if (null === $startDate) {
            return 'N/A';
        }
        $diff = (new \DateTime($endDate ?? ''))->diff(new \DateTime($startDate))->days;

        return 0 === $diff ? 1 : $diff;
    }
}
