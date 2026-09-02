<?php

declare(strict_types=1);

namespace App\Snowflake;

use App\Snowflake\Attribute\Column;

/**
 * Reads #[Column] attributes off a resource's constructor
 * properties, so the property => Snowflake column mapping is declared once
 * on the resource (see App\Dto\Snowflake\VendorItemCost) and reused by both
 * the Provider (SELECT list) and the Snowflake filters (WHERE/ORDER BY
 * column resolution), instead of being repeated in each place.
 */
final class ColumnMapper
{
    /** @var array<class-string, array<string, string>> */
    private static array $cache = [];

    /**
     * @param class-string $resourceClass
     *
     * @return array<string, string> property name => Snowflake column expression, in declaration order
     */
    public static function getColumns(string $resourceClass): array
    {
        if (isset(self::$cache[$resourceClass])) {
            return self::$cache[$resourceClass];
        }

        $columns = [];
        $constructor = (new \ReflectionClass($resourceClass))->getConstructor();

        foreach ($constructor?->getParameters() ?? [] as $parameter) {
            $attributes = $parameter->getAttributes(Column::class);

            if ([] === $attributes) {
                continue;
            }

            $columns[$parameter->getName()] = $attributes[0]->newInstance()->name;
        }

        return self::$cache[$resourceClass] = $columns;
    }

    /**
     * @param class-string $resourceClass
     */
    public static function getColumn(string $resourceClass, string $property): string
    {
        $columns = self::getColumns($resourceClass);

        if (!isset($columns[$property])) {
            throw new \LogicException(\sprintf('Property "%s" of "%s" has no #[Column] attribute.', $property, $resourceClass));
        }

        return $columns[$property];
    }
}
