<?php

declare(strict_types=1);

namespace App\Tests\Javelo\EventListener;

use ApiPlatform\Metadata\IriConverterInterface;
use App\Javelo\Event\UserCreatedEvent;
use App\Javelo\EventListener\UserCreatedListener;
use App\Javelo\Repository\UserClientRepository;
use App\Javelo\Resources\User as JaveloUser;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use Psr\Log\LoggerInterface;

class UserCreatedListenerTest extends TestCase
{
    use ProphecyTrait;

    private ObjectProphecy $userClientRepositoryProphecy;
    private ObjectProphecy $loggerProphecy;
    private UserCreatedListener $listener;
    private ObjectProphecy $iriConverter;

    protected function setUp(): void
    {
        $this->iriConverter = $this->prophesize(IriConverterInterface::class);
        $this->userClientRepositoryProphecy = $this->prophesize(UserClientRepository::class);
        $this->loggerProphecy = $this->prophesize(LoggerInterface::class);

        $this->listener = new UserCreatedListener(
            $this->iriConverter->reveal(),
            $this->userClientRepositoryProphecy->reveal(),
            $this->loggerProphecy->reveal()
        );
    }

    public function testOnUserCreationSuccessfullyCreatesUser(): void
    {
        $javeloUser = new JaveloUser();
        $changes = ['field' => 'value_changed'];
        $posterIri = null;

        $event = $this->prophesize(UserCreatedEvent::class);
        $event->getJaveloUser()->willReturn($javeloUser)->shouldBeCalled();
        $event->getChanges()->willReturn($changes)->shouldBeCalled();
        $event->getPosterIri()->willReturn($posterIri)->shouldBeCalled();

        $this->userClientRepositoryProphecy->createUser($javeloUser, $changes, $posterIri)->shouldBeCalled();

        $this->loggerProphecy->error()->shouldNotBeCalled();

        $this->listener->__invoke($event->reveal());
    }

    public function testOnUserCreationLogsErrorOnException(): void
    {
        $javeloUser = new JaveloUser();
        $changes = ['field' => 'value_changed'];
        $posterIri = null;

        $event = $this->prophesize(UserCreatedEvent::class);
        $event->getJaveloUser()->willReturn($javeloUser)->shouldBeCalled();
        $event->getChanges()->willReturn($changes)->shouldBeCalled();
        $event->getPosterIri()->willReturn($posterIri)->shouldBeCalled();

        $this->userClientRepositoryProphecy
            ->createUser($javeloUser, $changes, $posterIri)
            ->willThrow(new \Exception('User creation failed'))
            ->shouldBeCalled();

        $this->loggerProphecy->error('Something went wrong when creating {userName} on Javelo: {error}', [
            'userName' => (string) $javeloUser->userName,
            'error' => 'User creation failed',
        ])->shouldBeCalled();

        $this->listener->__invoke($event->reveal());
    }
}
