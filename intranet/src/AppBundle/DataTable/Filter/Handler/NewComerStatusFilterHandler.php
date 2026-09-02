<?php

declare(strict_types=1);

namespace AppBundle\DataTable\Filter\Handler;

class NewComerStatusFilterHandler extends AbstractPeopleLifecycleStatusFilterHandler
{
    public const STATUS_PLANNED = 'planned';
    public const STATUS_ARRIVED = 'arrived';

    protected function getStatusToDisabledMap(): array
    {
        return [
            self::STATUS_PLANNED => 'true',
            self::STATUS_ARRIVED => 'false',
        ];
    }
}
