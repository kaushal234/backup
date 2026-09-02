<?php

declare(strict_types=1);

namespace App\Test\DataTable\Action\Type;

use App\Test\DataTable\Action\TestActionFactory;
use Kreyu\Bundle\DataTableBundle\Action\ActionFactoryInterface;
use Kreyu\Bundle\DataTableBundle\Action\ActionInterface;
use Kreyu\Bundle\DataTableBundle\Action\ActionRegistry;
use Kreyu\Bundle\DataTableBundle\Action\ActionRegistryInterface;
use Kreyu\Bundle\DataTableBundle\Action\ActionView;
use Kreyu\Bundle\DataTableBundle\Action\Type\ActionType;
use Kreyu\Bundle\DataTableBundle\Action\Type\ActionTypeInterface;
use Kreyu\Bundle\DataTableBundle\Action\Type\ResolvedActionTypeFactory;
use Kreyu\Bundle\DataTableBundle\Action\Type\ResolvedActionTypeFactoryInterface;
use Kreyu\Bundle\DataTableBundle\Column\ColumnFactoryInterface;
use Kreyu\Bundle\DataTableBundle\Column\ColumnInterface;
use Kreyu\Bundle\DataTableBundle\Column\ColumnRegistry;
use Kreyu\Bundle\DataTableBundle\Column\ColumnRegistryInterface;
use Kreyu\Bundle\DataTableBundle\Column\ColumnValueView;
use Kreyu\Bundle\DataTableBundle\Column\Type\ColumnType;
use Kreyu\Bundle\DataTableBundle\Column\Type\ResolvedColumnTypeFactory;
use Kreyu\Bundle\DataTableBundle\Column\Type\ResolvedColumnTypeFactoryInterface;
use Kreyu\Bundle\DataTableBundle\DataTableFactory;
use Kreyu\Bundle\DataTableBundle\DataTableFactoryInterface;
use Kreyu\Bundle\DataTableBundle\DataTableInterface;
use Kreyu\Bundle\DataTableBundle\DataTableRegistry;
use Kreyu\Bundle\DataTableBundle\DataTableRegistryInterface;
use Kreyu\Bundle\DataTableBundle\DataTableView;
use Kreyu\Bundle\DataTableBundle\Query\ArrayProxyQuery;
use Kreyu\Bundle\DataTableBundle\Tests\Fixtures\Column\TestColumnFactory;
use Kreyu\Bundle\DataTableBundle\Type\DataTableType;
use Kreyu\Bundle\DataTableBundle\Type\ResolvedDataTableTypeFactory;
use Kreyu\Bundle\DataTableBundle\Type\ResolvedDataTableTypeFactoryInterface;
use Kreyu\Bundle\DataTableBundle\ValueRowView;
use PHPUnit\Framework\TestCase;

abstract class ActionTypeTestCase extends TestCase
{
    protected ActionFactoryInterface $actionFactory;
    protected ActionRegistryInterface $actionRegistry;
    protected ResolvedActionTypeFactoryInterface $resolvedActionTypeFactory;
    protected DataTableFactoryInterface $dataTableFactory;
    protected DataTableRegistryInterface $dataTableRegistry;
    protected ResolvedDataTableTypeFactoryInterface $resolvedDataTableTypeFactory;
    protected DataTableInterface $dataTable;
    protected ColumnFactoryInterface $columnFactory;
    protected ColumnRegistryInterface $columnRegistry;
    protected ResolvedColumnTypeFactoryInterface $resolvedColumnTypeFactory;

    /**
     * @return array<int, ActionType>
     */
    protected function getAdditionalActionTypes(): array
    {
        return [
            new ActionType(),
        ];
    }

    abstract protected function getTestedActionType(): ActionTypeInterface;

    /**
     * @param array<string, mixed> $options
     */
    protected function createAction(array $options = []): ActionInterface
    {
        return $this->getActionFactory()->create($this->getTestedActionType()::class, $options);
    }

    protected function getActionFactory(): ActionFactoryInterface
    {
        return $this->actionFactory ??= $this->createActionFactory();
    }

    protected function createActionFactory(): ActionFactoryInterface
    {
        $factory = new TestActionFactory($this->getActionRegistry());
        $factory->setDataTable($this->getDataTable());

        return $factory;
    }

    protected function getActionRegistry(): ActionRegistryInterface
    {
        return $this->actionRegistry ??= $this->createActionRegistry();
    }

    protected function createActionRegistry(): ActionRegistryInterface
    {
        return new ActionRegistry(
            types: [
                $this->getTestedActionType(),
                ...$this->getAdditionalActionTypes(),
            ],
            typeExtensions: [],
            resolvedTypeFactory: $this->getResolvedActionTypeFactory(),
        );
    }

    protected function getResolvedActionTypeFactory(): ResolvedActionTypeFactoryInterface
    {
        return $this->resolvedActionTypeFactory ??= $this->createResolvedActionTypeFactory();
    }

    protected function createResolvedActionTypeFactory(): ResolvedActionTypeFactoryInterface
    {
        return new ResolvedActionTypeFactory();
    }

    protected function getDataTableRegistry(): DataTableRegistryInterface
    {
        return $this->dataTableRegistry ??= $this->createDataTableRegistry();
    }

    protected function createDataTableRegistry(): DataTableRegistryInterface
    {
        return new DataTableRegistry(
            types: [new DataTableType()],
            typeExtensions: [],
            proxyQueryFactories: [],
            resolvedTypeFactory: $this->getResolvedDataTableTypeFactory(),
        );
    }

    protected function getResolvedDataTableTypeFactory(): ResolvedDataTableTypeFactoryInterface
    {
        return $this->resolvedDataTableTypeFactory ??= $this->createResolvedDataTableTypeFactory();
    }

    protected function createResolvedDataTableTypeFactory(): ResolvedDataTableTypeFactoryInterface
    {
        return new ResolvedDataTableTypeFactory();
    }

    protected function getDataTableFactory(): DataTableFactoryInterface
    {
        return $this->dataTableFactory ??= $this->createDataTableFactory();
    }

    protected function createDataTableFactory(): DataTableFactoryInterface
    {
        return new DataTableFactory($this->createDataTableRegistry());
    }

    protected function getDataTable(): DataTableInterface
    {
        return $this->dataTable ??= $this->createDataTable();
    }

    protected function createDataTable(): DataTableInterface
    {
        return $this->getDataTableFactory()->create(DataTableType::class, new ArrayProxyQuery([]));
    }

    protected function createValueRowView(?DataTableView $dataTableView = null, int $index = 0, mixed $data = null): ValueRowView
    {
        return new ValueRowView($dataTableView ?? new DataTableView(), $index, $data);
    }

    protected function createColumnValueView(ColumnInterface $column, ?ValueRowView $valueRowView = null, mixed $data = null, mixed $rowData = null): ColumnValueView
    {
        $columnValueView = $column->createValueView($valueRowView ?? $this->createValueRowView(data: $rowData));
        $columnValueView->data = $data;

        return $columnValueView;
    }

    protected function createActionView(ActionInterface $action, ColumnValueView|DataTableView|null $view = null, mixed $data = null, mixed $rowData = null): ActionView
    {
        return $action->createView($view ?? $this->createColumnValueView(
            column: $this->createColumn(),
            data: $data,
            rowData: $rowData,
        ));
    }

    /**
     * @param array<string, mixed> $options
     */
    protected function createColumn(array $options = []): ColumnInterface
    {
        return $this->getColumnFactory()->create(ColumnType::class, $options);
    }

    /**
     * @param array<string, mixed> $options
     */
    protected function createNamedColumn(string $name, array $options = []): ColumnInterface
    {
        return $this->getColumnFactory()->createNamed($name, ColumnType::class, $options);
    }

    protected function getColumnFactory(): ColumnFactoryInterface
    {
        return $this->columnFactory ??= $this->createColumnFactory();
    }

    protected function createColumnFactory(): ColumnFactoryInterface
    {
        $factory = new TestColumnFactory($this->getColumnRegistry());
        $factory->setDataTable($this->getDataTable());

        return $factory;
    }

    protected function getColumnRegistry(): ColumnRegistryInterface
    {
        return $this->columnRegistry ??= $this->createColumnRegistry();
    }

    protected function createColumnRegistry(): ColumnRegistryInterface
    {
        return new ColumnRegistry(
            types: [
                new ColumnType(),
            ],
            typeExtensions: [],
            resolvedTypeFactory: $this->getResolvedColumnTypeFactory(),
        );
    }

    protected function getResolvedColumnTypeFactory(): ResolvedColumnTypeFactoryInterface
    {
        return $this->resolvedColumnTypeFactory ??= $this->createResolvedColumnTypeFactory();
    }

    protected function createResolvedColumnTypeFactory(): ResolvedColumnTypeFactoryInterface
    {
        return new ResolvedColumnTypeFactory();
    }
}
