<?php

declare(strict_types=1);

namespace App\DataProvider\Snowflake;

use App\Dto\Snowflake\VendorItemCost;
use App\Snowflake\AbstractCollectionProvider;
use App\Snowflake\QueryBuilder as SnowflakeQueryBuilder;

/**
 * @extends AbstractCollectionProvider<VendorItemCost>
 */
class VendorItemCostProvider extends AbstractCollectionProvider
{
    protected function getResourceClass(): string
    {
        return VendorItemCost::class;
    }

    protected function configureQuery(SnowflakeQueryBuilder $queryBuilder): void
    {
        $queryBuilder
            ->useConnection(database: 'LN_PRD', schema: 'SILVER', role: 'READONLY')
            ->from('V_VENDOR_XREF_COSTS', 'c')
            ->leftJoin('V_VENDOR_XREF_ITEMS', 'i', 'i.ITEM = c.ITEM');
    }

    protected function mapRow(array $row): VendorItemCost
    {
        return new VendorItemCost(
            site: (string) $this->columnValue($row, 'site'),
            item: (string) $this->columnValue($row, 'item'),
            description: null !== ($value = $this->columnValue($row, 'description')) ? (string) $value : null,
            currency: null !== ($value = $this->columnValue($row, 'currency')) ? (string) $value : null,
            unit: null !== ($value = $this->columnValue($row, 'unit')) ? (string) $value : null,
            price: null !== ($value = $this->columnValue($row, 'price')) ? (float) $value : null,
        );
    }
}
