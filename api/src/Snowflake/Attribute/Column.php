<?php

declare(strict_types=1);

namespace App\Snowflake\Attribute;

/**
 * Declares, once, which Snowflake column (with its table alias, e.g. "c.SITE")
 * a resource property maps to.
 *
 * Read by ColumnMapper and reused both to build the SELECT list in
 * a Provider and to resolve columns in Snowflake filters
 * (SearchFilter, OrderFilter), so the column name/alias is
 * written exactly once per resource.
 */
#[\Attribute(\Attribute::TARGET_PROPERTY | \Attribute::TARGET_PARAMETER)]
class Column
{
    public function __construct(
        public readonly string $name,
    ) {
    }
}
