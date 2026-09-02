<?php

declare(strict_types=1);

namespace App\Doctrine\Type;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\ConversionException;
use Doctrine\DBAL\Types\Type;

final class NullableZeroDateType extends Type
{
    public const NAME = 'nullable_zero_date';

    public function getSQLDeclaration(array $column, AbstractPlatform $platform): string
    {
        return $platform->getDateTimeTypeDeclarationSQL($column);
    }

    public function getName(): string
    {
        return self::NAME;
    }

    public function convertToPHPValue(mixed $value, AbstractPlatform $platform): ?\DateTime
    {
        if (
            null === $value || '' === $value || '0000-00-00' === $value || '0000-00-00 00:00:00' === $value
        ) {
            return null;
        }

        if ($value instanceof \DateTimeInterface) {
            return new \DateTime($value->format('Y-m-d H:i:s'));
        }

        $date = \DateTime::createFromFormat('Y-m-d H:i:s', $value);
        if (false !== $date) {
            return $date;
        }

        $date = \DateTime::createFromFormat('Y-m-d', $value);
        if (false !== $date) {
            return $date;
        }

        throw new ConversionException(\sprintf('Could not convert database value "%s" to nullable date.', $value));
    }
}
