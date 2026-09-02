<?php

declare(strict_types=1);

namespace App\Tests\Javelo\MessageHandler;

use App\Javelo\Event\GroupManagementEvent;
use App\Javelo\Event\GroupUpdateEvent;
use App\Javelo\Event\UserUpdatedEvent;
use App\Javelo\Message\UpdateUserMessage;
use App\Javelo\MessageHandler\UpdateUserMessageHandler;
use App\Javelo\Resources\User;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use Psr\EventDispatcher\EventDispatcherInterface;

class UpdateUserMessageHandlerTest extends TestCase
{
    use ProphecyTrait;

    private ObjectProphecy $eventDispatcherProphecy;
    private UpdateUserMessageHandler $handler;

    protected function setUp(): void
    {
        $this->eventDispatcherProphecy = $this->prophesize(EventDispatcherInterface::class);

        $this->handler = new UpdateUserMessageHandler(
            $this->eventDispatcherProphecy->reveal()
        );
    }

    public function testInvokeDispatchesEventsWithPoster(): void
    {
        $posterIri = 'some/poster_iri';
        $changes = ['field' => 'value_changed'];
        $javeloUser = new User();

        $this->eventDispatcherProphecy
            ->dispatch(new UserUpdatedEvent($javeloUser, $changes, $posterIri))
            ->shouldBeCalled();

        $this->eventDispatcherProphecy
            ->dispatch(new GroupManagementEvent($javeloUser))
            ->shouldBeCalled();

        $this->eventDispatcherProphecy
            ->dispatch(new GroupUpdateEvent())
            ->shouldBeCalled();

        $message = new UpdateUserMessage($javeloUser, $changes, $posterIri);

        $this->handler->__invoke($message);
    }

    public function testInvokeDispatchesEventsWithoutPoster(): void
    {
        $changes = ['field' => 'value_changed'];
        $javeloUser = new User();

        $this->eventDispatcherProphecy
            ->dispatch(new UserUpdatedEvent($javeloUser, $changes, null))
            ->shouldBeCalled();

        $this->eventDispatcherProphecy
            ->dispatch(new GroupManagementEvent($javeloUser))
            ->shouldBeCalled();

        $this->eventDispatcherProphecy
            ->dispatch(new GroupUpdateEvent())
            ->shouldBeCalled();

        $message = new UpdateUserMessage($javeloUser, $changes, null);

        $this->handler->__invoke($message);
    }
}
