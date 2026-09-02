<?php

declare(strict_types=1);

namespace App\AI\Filterable\Definition;

use App\AI\Filterable\Definition\Spec\BoolSpec;
use App\AI\Filterable\Definition\Spec\ConcatLikeSpec;
use App\AI\Filterable\Definition\Spec\CustomSpec;
use App\AI\Filterable\Definition\Spec\EqSpec;
use App\AI\Filterable\Definition\Spec\ExistsSpec;
use App\AI\Filterable\Definition\Spec\HasManySpec;
use App\AI\Filterable\Definition\Spec\InAnySpec;
use App\AI\Filterable\Definition\Spec\InSpec;
use App\AI\Filterable\Definition\Spec\LikeSpec;
use App\AI\Filterable\Definition\Spec\RangeSpec;
use App\AI\Filterable\FilterableField;
use Doctrine\ORM\QueryBuilder;

/**
 * Compact façade to declare filter specs from an {@see AbstractFilterableDefinition}.
 *
 * Each method returns a {@see FilterSpec} ready to be put into the array returned by
 * {@see AbstractFilterableDefinition::fields()}. The vocabulary mirrors the operators
 * actually used across the legacy FilterQuery classes (IN, =, LIKE %x%, range, bool,
 * IS [NOT] NULL, multi-column OR, CONCAT LIKE).
 */
final readonly class Filter
{
    private function __construct()
    {
    }

    /**
     * `path IN (:values)` — or `IDENTITY(rootAlias.relation) IN (:values)` if $path is a bare to-one
     * relation on the root entity (no join, matches the pre-refactor `IDENTITY(...) IN` shortcut).
     *
     * @param string[]|null $enum
     */
    public static function in(string $name, string $path, ?array $enum = null, string $desc = ''): InSpec
    {
        return new InSpec($name, $path, FilterableField::TYPE_STRING, $enum, $desc);
    }

    /**
     * Same as {@see in()} but the values are coerced to int (e.g. people ids).
     *
     * @param string[]|null $enum
     */
    public static function inInt(string $name, string $path, ?array $enum = null, string $desc = ''): InSpec
    {
        return new InSpec($name, $path, FilterableField::TYPE_INT, $enum, $desc);
    }

    /**
     * `path = :value` (scalar equality).
     */
    public static function eq(string $name, string $path, string $desc = ''): EqSpec
    {
        return new EqSpec($name, $path, FilterableField::TYPE_STRING, $desc);
    }

    /**
     * `path = :value` with int coercion.
     */
    public static function eqInt(string $name, string $path, string $desc = ''): EqSpec
    {
        return new EqSpec($name, $path, FilterableField::TYPE_INT, $desc);
    }

    /**
     * `path LIKE %:value%`.
     */
    public static function like(string $name, string $path, string $desc = ''): LikeSpec
    {
        return new LikeSpec($name, $path, $desc);
    }

    /**
     * `(path1 IN (:values) OR path2 IN (:values) …)` — one filter, OR'd across N columns.
     *
     * @param string[] $paths
     */
    public static function inAny(string $name, array $paths, string $desc = ''): InAnySpec
    {
        return new InAnySpec($name, $paths, $desc);
    }

    /**
     * Two filters on the same date column. $desc is prepended to a generic "on/after/before" boilerplate;
     * pass $afterDesc/$beforeDesc to override each bound's description entirely (for strict legacy parity).
     */
    public static function dateRange(string $path, string $afterName, string $beforeName, string $desc = '', ?string $afterDesc = null, ?string $beforeDesc = null): RangeSpec
    {
        return new RangeSpec($path, $afterName, $beforeName, FilterableField::TYPE_DATE, $desc, $afterDesc, $beforeDesc);
    }

    /**
     * Two filters on the same numeric column. Same desc/override behavior as {@see dateRange()}.
     */
    public static function intRange(string $path, string $minName, string $maxName, string $desc = '', ?string $minDesc = null, ?string $maxDesc = null): RangeSpec
    {
        return new RangeSpec($path, $minName, $maxName, FilterableField::TYPE_INT, $desc, $minDesc, $maxDesc);
    }

    public static function floatRange(string $path, string $minName, string $maxName, string $desc = '', ?string $minDesc = null, ?string $maxDesc = null): RangeSpec
    {
        return new RangeSpec($path, $minName, $maxName, FilterableField::TYPE_FLOAT, $desc, $minDesc, $maxDesc);
    }

    /**
     * `path = :value`. Use $trueValue/$falseValue when the column stores a Y/N flag instead of a boolean.
     */
    public static function bool(string $name, string $path, mixed $trueValue = true, mixed $falseValue = false, string $desc = ''): BoolSpec
    {
        return new BoolSpec($name, $path, $trueValue, $falseValue, $desc);
    }

    /**
     * `path IS [NOT] NULL` toggle. Bool true = IS NOT NULL, false = IS NULL.
     */
    public static function exists(string $name, string $path, string $desc = ''): ExistsSpec
    {
        return new ExistsSpec($name, $path, $desc);
    }

    /**
     * `path IS [NOT] EMPTY` toggle on a to-many collection (Doctrine DQL).
     * $collection must be the name of a collection-valued association on the root entity.
     */
    public static function hasMany(string $name, string $collection, string $desc = ''): HasManySpec
    {
        return new HasManySpec($name, $collection, $desc);
    }

    /**
     * `CONCAT(path1, ' ', path2, …) LIKE %:value%` — typical for "firstname lastname" search.
     *
     * @param string[] $paths
     */
    public static function concatLike(string $name, array $paths, string $desc = ''): ConcatLikeSpec
    {
        return new ConcatLikeSpec($name, $paths, $desc);
    }

    /**
     * Escape hatch for shapes the DSL does not cover. The $apply callback receives the QueryBuilder,
     * the PathResolver (so it can call ->resolve()/->resolveIdentity()) and the raw user value.
     * Use sparingly — most queries should fit the typed factories above.
     *
     * @param FilterableField[]                                                $fields
     * @param callable(QueryBuilder, PathResolver, array<string, mixed>): void $apply
     */
    public static function custom(array $fields, callable $apply): CustomSpec
    {
        return new CustomSpec($fields, $apply);
    }
}
