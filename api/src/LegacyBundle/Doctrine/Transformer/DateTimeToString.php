<?php

declare(strict_types=1);

namespace LegacyBundle\Doctrine\Transformer;

class DateTimeToString
{
    /**
     * @var string
     */
    final public const DEFAULT_FORMAT = 'Y-m-d H:i:s';

    public function __invoke($datetime, array $options)
    {
        if (!$datetime instanceof \DateTime) {
            return '';
        }

        if ($options['integer'] ?? null) {
            return (int) $datetime->format($options['format'] ?? self::DEFAULT_FORMAT);
        }

        return $datetime->format($options['format'] ?? self::DEFAULT_FORMAT);
    }
}
