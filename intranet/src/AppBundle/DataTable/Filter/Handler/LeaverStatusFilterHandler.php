<?php

declare(strict_types=1);

namespace AppBundle\DataTable\Filter\Handler;

class LeaverStatusFilterHandler extends AbstractPeopleLifecycleStatusFilterHandler
{
    public const STATUS_PLANNED = 'planned';
    public const STATUS_LEFT = 'left';

    protected function getStatusToDisabledMap(): array
    {
        return [
            self::STATUS_PLANNED => 'false',
            self::STATUS_LEFT => 'true',
        ];
    }
}
