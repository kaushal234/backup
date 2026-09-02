<?php

declare(strict_types=1);

namespace AppBundle\DataTable\Filter\Type\Directory;

use AppBundle\DataTable\Filter\Handler\LeaverStatusFilterHandler;
use Kreyu\Bundle\DataTableBundle\Filter\FilterHandlerInterface;

class LeaverStatusFilterType extends AbstractPeopleLifecycleStatusFilterType
{
    protected function createHandler(): FilterHandlerInterface
    {
        return new LeaverStatusFilterHandler();
    }

    protected function getChoices(): array
    {
        return [
            'directory.people.fields.leaver_status.planned' => LeaverStatusFilterHandler::STATUS_PLANNED,
            'directory.people.fields.leaver_status.left' => LeaverStatusFilterHandler::STATUS_LEFT,
        ];
    }
}
