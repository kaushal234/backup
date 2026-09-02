<?php

declare(strict_types=1);

namespace AppBundle\DataTable\Persistence;

use AppBundle\Manager\SettingsManager;
use Kreyu\Bundle\DataTableBundle\DataTableFactory;
use Kreyu\Bundle\DataTableBundle\DataTableInterface;
use Kreyu\Bundle\DataTableBundle\DataTableRegistry;
use Kreyu\Bundle\DataTableBundle\Filter\FilterData;
use Kreyu\Bundle\DataTableBundle\Filter\FiltrationData;
use Kreyu\Bundle\DataTableBundle\Pagination\PaginationData;
use Kreyu\Bundle\DataTableBundle\Personalization\PersonalizationData;
use Kreyu\Bundle\DataTableBundle\Sorting\SortingColumnData;
use Kreyu\Bundle\DataTableBundle\Sorting\SortingData;
use Kreyu\Bundle\DataTableBundle\Tests\Fixtures\DataTable\Query\CustomProxyQuery;
use Kreyu\Bundle\DataTableBundle\Tests\Fixtures\DataTable\Type\ConfigurableDataTableType;
use Kreyu\Bundle\DataTableBundle\Tests\Fixtures\DataTable\Type\SimpleDataTableType;
use Kreyu\Bundle\DataTableBundle\Tests\Fixtures\PersistenceSubjectUser;
use Kreyu\Bundle\DataTableBundle\Type\DataTableType;
use Kreyu\Bundle\DataTableBundle\Type\ResolvedDataTableTypeFactory;
use PHPUnit\Framework\TestCase;

class UserSettingsPersistenceAdapterTest extends TestCase
{
    public function testReadSubjectException(): void
    {
        $dataTable = $this->createDataTable('foo');
        $adapter = $this->createAdapter('unknownn');

        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('Invalid persistence subject type.');

        $adapter->read($dataTable, new PersistenceSubjectUser());
    }

    public function testWriteSubjectException(): void
    {
        $dataTable = $this->createDataTable('foo');
        $adapter = $this->createAdapter('unknown');

        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('Invalid persistence subject type.');

        $adapter->write($dataTable, new PersistenceSubjectUser(), []);
    }

    public function testReturnNullWhenNoUserSettings(): void
    {
        $dataTable = $this->createDataTable('bar');
        $adapter = $this->createAdapter('unknown', 'get', 'foo.datatable.bar.unknown');

        $result = $adapter->read($dataTable, $this->getPersistenceUser());

        $this->assertNull($result);
    }

    public function testExceptionOnInvalidPrefixRead(): void
    {
        $dataTable = $this->createDataTable('bar');
        $adapter = $this->createAdapter('unknown', 'get', 'foo.datatable.bar.unknown', '42');

        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('Invalid prefix.');

        $adapter->read($dataTable, $this->getPersistenceUser());
    }

    public function testExceptionOnInvalidPrefixWrite(): void
    {
        $dataTable = $this->createDataTable('bar');
        $adapter = $this->createAdapter('unknown');

        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('Invalid prefix.');

        $adapter->write($dataTable, $this->getPersistenceUser(), null);
    }

    /**
     * @dataProvider handleRead
     */
    public function testReadSettings(string $prefix, string $key, mixed $data, mixed $result): void
    {
        $dataTable = $this->createDataTable('bar');
        $adapter = $this->createAdapter($prefix, 'get', $key, $data);

        $adapterResult = $adapter->read($dataTable, $this->getPersistenceUser());

        $this->assertSame(json_encode($result), json_encode($adapterResult));
    }

    public function handleRead(): \Generator
    {
        yield [
            'personalization',
            'foo.datatable.bar.personalization',
            ['id' => ['name' => 'id', 'priority' => 0, 'visible' => true]],
            PersonalizationData::fromArray(['columns' => [['name' => 'id']]]),
        ];
        yield [
            'pagination',
            'foo.datatable.bar.pagination',
            ['page' => 42, 'perPage' => 10],
            PaginationData::fromArray(['page' => 42, 'perPage' => 10]),
        ];
        yield [
            'sorting',
            'foo.datatable.bar.sorting',
            ['id' => SortingColumnData::fromArray(['name' => 'id', 'direction' => 'asc', 'property_path' => null])],
            SortingData::fromArray(['id' => 'asc']),
        ];
        yield [
            'filtration',
            'foo.datatable.bar.filtration',
            ['id' => FilterData::fromArray(['value' => '42'])],
            FiltrationData::fromArray(['id' => '42']),
        ];
    }

    /**
     * @dataProvider handleRead
     */
    public function testWriteSettings(string $prefix, string $key, mixed $data, mixed $result): void
    {
        $dataTable = $this->createDataTable('bar');
        $adapter = $this->createAdapter($prefix, 'set', $key, $data);

        $adapter->write($dataTable, $this->getPersistenceUser(), $result);
    }

    protected function createAdapter(
        string $prefix,
        ?string $settingMethod = null,
        ?string $keySetting = null,
        mixed $data = null,
    ): UserSettingsPersistenceAdapter {
        $userSettings = $this->createMock(SettingsManager::class);

        match ($settingMethod) {
            'get' => $userSettings->expects($this->once())->method('get')->with($keySetting)->willReturn($data),
            'set' => $userSettings->expects($this->once())->method('set')->with($keySetting, $data),
            default => null,
        };

        return new UserSettingsPersistenceAdapter($userSettings, $prefix);
    }

    private function createDataTable(string $name): DataTableInterface
    {
        $registry = new DataTableRegistry(
            types: [
                new DataTableType(),
                new SimpleDataTableType(),
                new ConfigurableDataTableType(),
            ],
            typeExtensions: [],
            proxyQueryFactories: [],
            resolvedTypeFactory: new ResolvedDataTableTypeFactory(),
        );

        $factory = new DataTableFactory($registry);

        return $factory->createNamed(
            name: $name,
            type: ConfigurableDataTableType::class,
            data: new CustomProxyQuery(),
        );
    }

    private function getPersistenceUser(): UserModulePersistenceSubjectAggregate
    {
        return new UserModulePersistenceSubjectAggregate('42', 'FOO');
    }
}
