<?php

declare(strict_types=1);

namespace App\Javelo\MessageHandler;

use App\Javelo\Event\GroupManagementEvent;
use App\Javelo\Event\GroupUpdateEvent;
use App\Javelo\Event\UserCreatedEvent;
use App\Javelo\Message\CreateUserMessage;
use Psr\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final class CreateUserMessageHandler
{
    public function __construct(
        private readonly EventDispatcherInterface $eventDispatcher,
    ) {
    }

    public function __invoke(CreateUserMessage $message): void
    {
        $this->eventDispatcher->dispatch(new UserCreatedEvent($message->getJaveloUserToUpdate(), $message->getChanges(), $message->getPosterIri()));
        $this->eventDispatcher->dispatch(new GroupManagementEvent($message->getJaveloUserToUpdate()));
        $this->eventDispatcher->dispatch(new GroupUpdateEvent());
    }
}
