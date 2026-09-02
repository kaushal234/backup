<?php

declare(strict_types=1);

namespace App\CQRS;

use App\CQRS\Command\CommandInterface;

interface CommandBusInterface
{
    public function dispatch(CommandInterface $command): void;
}
