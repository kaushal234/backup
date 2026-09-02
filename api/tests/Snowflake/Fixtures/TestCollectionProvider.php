<?php

declare(strict_types=1);

namespace App\Tests\Snowflake\Fixtures;

use App\Snowflake\AbstractCollectionProvider;
use App\Snowflake\QueryBuilder;

/**
 * Concrete AbstractCollectionProvider used to unit test the base class.
 *
 * Named (rather than anonymous) so that PHPStan can see readColumnValue()
 * through a typed return value in tests/Snowflake/AbstractCollectionProviderTest.php.
 *
 * @extends AbstractCollectionProvider<DummyResource>
 */
class TestCollectionProvider extends AbstractCollectionProvider
{
    /**
     * Exposes the protected columnValue() helper for testing.
     */
    public function readColumnValue(array $row, string $property): mixed
    {
        return $this->columnValue($row, $property);
    }

    protected function getResourceClass(): string
    {
        return DummyResource::class;
    }

    protected function configureQuery(QueryBuilder $queryBuilder): void
    {
        $queryBuilder->from('A', 'a')->useConnection('DB', 'SCHEMA', 'ROLE');
    }

    protected function mapRow(array $row): DummyResource
    {
        return new DummyResource(
            site: (string) $this->columnValue($row, 'site'),
            item: (string) $this->columnValue($row, 'item'),
        );
    }
}
