<?php

declare(strict_types=1);

namespace App\CQRS;

use App\CQRS\Command\CommandInterface;
use Symfony\Component\Messenger\MessageBusInterface;

final class CommandBus implements CommandBusInterface
{
    public function __construct(
        private readonly MessageBusInterface $commandBus
    ) {
    }

    public function dispatch(CommandInterface $command): mixed
    {
        return $this->commandBus->dispatch($command);
    }
}
