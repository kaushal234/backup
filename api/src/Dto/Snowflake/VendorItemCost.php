<?php

declare(strict_types=1);

namespace App\Dto\Snowflake;

use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GetCollection;
use App\DataProvider\Snowflake\VendorItemCostProvider;
use App\Snowflake\Attribute\Column as SnowflakeColumn;
use App\Snowflake\Filter\OrderFilter as SnowflakeOrderFilter;
use App\Snowflake\Filter\SearchFilter as SnowflakeSearchFilter;
use Symfony\Component\Serializer\Attribute\Groups;

/**
 * POC resource: exposes LN_PRD.SILVER.V_VENDOR_XREF_COSTS joined with
 * V_VENDOR_XREF_ITEMS, fetched live from Snowflake through the SQL API.
 *
 * Each property declares its Snowflake column exactly once, via
 * #[SnowflakeColumn]. VendorItemCostProvider reads that mapping
 * (ColumnMapper) to build the SELECT list, and the filters below
 * resolve their column the same way -- the column name/alias is never
 * repeated across files.
 *
 * Security is set to ACCESS_PEOPLE (basic authenticated-user access, same
 * baseline used elsewhere in the app) for this connectivity POC. Revisit
 * once the real access control for this Snowflake-backed data is defined.
 */
#[ApiFilter(SnowflakeSearchFilter::class, properties: ['site', 'item', 'description' => 'partial'])]
#[ApiFilter(SnowflakeOrderFilter::class, properties: ['site', 'item', 'price'])]
#[ApiResource(
    operations: [
        new GetCollection(
            uriTemplate: '/snowflake/vendor_item_costs',
            normalizationContext: ['groups' => ['vendor_item_cost']],
            provider: VendorItemCostProvider::class,
        ),
    ],
)]
class VendorItemCost
{
    public function __construct(
        #[SnowflakeColumn('c.SITE')]
        #[Groups(['vendor_item_cost'])]
        public readonly string $site,

        #[SnowflakeColumn('c.ITEM')]
        #[Groups(['vendor_item_cost'])]
        public readonly string $item,

        #[SnowflakeColumn('i.DSCA')]
        #[Groups(['vendor_item_cost'])]
        public readonly ?string $description,

        #[SnowflakeColumn('c.CCUR')]
        #[Groups(['vendor_item_cost'])]
        public readonly ?string $currency,

        #[SnowflakeColumn('c.CUPP')]
        #[Groups(['vendor_item_cost'])]
        public readonly ?string $unit,

        #[SnowflakeColumn('c.PRIP')]
        #[Groups(['vendor_item_cost'])]
        public readonly ?float $price,
    ) {
    }
}
