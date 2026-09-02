<?php

declare(strict_types=1);

namespace App\Javelo\MessageHandler;

use App\Javelo\Event\GroupManagementEvent;
use App\Javelo\Event\GroupUpdateEvent;
use App\Javelo\Event\UserUpdatedEvent;
use App\Javelo\Message\UpdateUserMessage;
use Psr\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final class UpdateUserMessageHandler
{
    public function __construct(
        private readonly EventDispatcherInterface $eventDispatcher,
    ) {
    }

    public function __invoke(UpdateUserMessage $message): void
    {
        $this->eventDispatcher->dispatch(new UserUpdatedEvent($message->getJaveloUserToUpdate(), $message->getChanges(), $message->getPosterIri()));
        $this->eventDispatcher->dispatch(new GroupManagementEvent($message->getJaveloUserToUpdate()));
        $this->eventDispatcher->dispatch(new GroupUpdateEvent());
    }
}
