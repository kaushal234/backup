<?php

declare(strict_types=1);

namespace App\ApiPlatform\Inflector;

use ApiPlatform\Metadata\InflectorInterface;

class EnglishInflector implements InflectorInterface
{
    public const NOT_INFLECTED = [
        'people',
        'dms',
        'monthly_activities',
        'materials',
        'chapters',
        'manuals',
    ];

    public const PLURAL_ERROR = [
        'criteria',
        'aircraft',
    ];

    public function __construct(
        private readonly InflectorInterface $decorated,
    ) {
    }

    public function tableize(string $input): string
    {
        return $this->decorated->tableize($input);
    }

    public function pluralize(string $singular): string
    {
        $lowerSingular = mb_strtolower($singular);

        // Check if the word is one which is not inflected, return early if so
        foreach (self::NOT_INFLECTED as $notInflected) {
            if (str_ends_with($lowerSingular, $notInflected)) {
                return $singular;
            }
        }

        // To fix plural error in route names, return early if so
        foreach (self::PLURAL_ERROR as $pluralError) {
            if (str_ends_with($lowerSingular, $pluralError)) {
                return \sprintf('%ss', $singular);
            }
        }

        return $this->decorated->pluralize($singular);
    }
}
