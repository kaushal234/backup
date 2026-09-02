<?php

declare(strict_types=1);

namespace App\Sdk\Internal;

use Psl\Dict;
use Psl\Iter;
use Psl\Str;
use Psl\Type;
use Psl\Vec;

use function is_array;

/**
 * @internal
 */
final class Utility
{
    /**
     * @param non-empty-string             $template
     * @param string|array<string, string> $identifier
     *
     * @return non-empty-string
     */
    public static function buildIri(string $template, string|array $identifier): string
    {
        if (is_array($identifier) && Iter\contains_key($identifier, 'iri')) {
            return Type\non_empty_string()->assert($identifier['iri']);
        }

        /** @var non-empty-string */
        return Str\format($template, self::getSimplifiedIdentifier($identifier));
    }

    /**
     * @param string|array<string, string|null> $identifier
     */
    public static function getSimplifiedIdentifier(string|array $identifier): string
    {
        return is_array($identifier) ? Str\join(
            Vec\map_with_key(Dict\filter_nulls($identifier), static fn ($k, $v): string => Str\format('%s=%s', $k, $v)),
            ';',
        ) : $identifier;
    }
}
