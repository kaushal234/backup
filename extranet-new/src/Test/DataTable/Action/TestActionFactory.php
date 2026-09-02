<?php

declare(strict_types=1);

namespace App\Test\DataTable\Action;

use Kreyu\Bundle\DataTableBundle\Action\ActionFactory;
use Kreyu\Bundle\DataTableBundle\Action\ActionInterface;
use Kreyu\Bundle\DataTableBundle\Action\Type\ActionType;
use Kreyu\Bundle\DataTableBundle\DataTableInterface;

class TestActionFactory extends ActionFactory
{
    private DataTableInterface $dataTable;

    /**
     * @param array<string, mixed> $options
     */
    public function create(string $type = ActionType::class, array $options = []): ActionInterface
    {
        $action = parent::create($type, $options);
        $action->setDataTable($this->dataTable);

        return $action;
    }

    /**
     * @param array<string, mixed> $options
     */
    public function createNamed(string $name, string $type = ActionType::class, array $options = []): ActionInterface
    {
        $action = parent::createNamed($name, $type, $options);
        $action->setDataTable($this->dataTable);

        return $action;
    }

    public function setDataTable(DataTableInterface $dataTable): self
    {
        $this->dataTable = $dataTable;

        return $this;
    }
}
