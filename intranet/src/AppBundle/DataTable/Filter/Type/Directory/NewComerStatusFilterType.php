<?php

declare(strict_types=1);

namespace AppBundle\DataTable\Filter\Type\Directory;

use AppBundle\DataTable\Filter\Handler\NewComerStatusFilterHandler;
use Kreyu\Bundle\DataTableBundle\Filter\FilterHandlerInterface;

class NewComerStatusFilterType extends AbstractPeopleLifecycleStatusFilterType
{
    protected function createHandler(): FilterHandlerInterface
    {
        return new NewComerStatusFilterHandler();
    }

    protected function getChoices(): array
    {
        return [
            'directory.people.fields.new_comer_status.planned' => NewComerStatusFilterHandler::STATUS_PLANNED,
            'directory.people.fields.new_comer_status.arrived' => NewComerStatusFilterHandler::STATUS_ARRIVED,
        ];
    }
}
