<?php

declare(strict_types=1);

namespace App\Tests\Javelo\MessageHandler;

use App\Javelo\Event\GroupManagementEvent;
use App\Javelo\Event\GroupUpdateEvent;
use App\Javelo\Event\UserCreatedEvent;
use App\Javelo\Message\CreateUserMessage;
use App\Javelo\MessageHandler\CreateUserMessageHandler;
use App\Javelo\Resources\User;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use Psr\EventDispatcher\EventDispatcherInterface;

class CreateUserMessageHandlerTest extends TestCase
{
    use ProphecyTrait;

    private ObjectProphecy $eventDispatcherProphecy;
    private CreateUserMessageHandler $handler;

    protected function setUp(): void
    {
        $this->eventDispatcherProphecy = $this->prophesize(EventDispatcherInterface::class);

        $this->handler = new CreateUserMessageHandler(
            $this->eventDispatcherProphecy->reveal()
        );
    }

    public function testInvokeDispatchesEventsWithPoster(): void
    {
        $posterIri = 'some/poster_iri';
        $changes = ['field' => 'value_changed'];
        $javeloUser = new User();

        $this->eventDispatcherProphecy->dispatch(new UserCreatedEvent($javeloUser, $changes, $posterIri))->shouldBeCalled();

        $this->eventDispatcherProphecy->dispatch(new GroupManagementEvent($javeloUser))->shouldBeCalled();

        $this->eventDispatcherProphecy->dispatch(new GroupUpdateEvent())->shouldBeCalled();

        $message = new CreateUserMessage($javeloUser, $changes, $posterIri);

        $this->handler->__invoke($message);
    }

    public function testInvokeDispatchesEventsWithoutPoster(): void
    {
        $peopleIri = 'some/iri';
        $changes = ['field' => 'value_changed'];
        $javeloUser = new User();

        $this->eventDispatcherProphecy
            ->dispatch(new UserCreatedEvent($javeloUser, $changes, null))
            ->shouldBeCalled();

        $this->eventDispatcherProphecy
            ->dispatch(new GroupManagementEvent($javeloUser))
            ->shouldBeCalled();

        $this->eventDispatcherProphecy
            ->dispatch(new GroupUpdateEvent())
            ->shouldBeCalled();

        $message = new CreateUserMessage($javeloUser, $changes, null);

        $this->handler->__invoke($message);
    }
}
