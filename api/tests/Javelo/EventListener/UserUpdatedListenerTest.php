<?php

declare(strict_types=1);

namespace App\Tests\Javelo\EventListener;

use ApiPlatform\Metadata\IriConverterInterface;
use App\Javelo\Event\UserUpdatedEvent;
use App\Javelo\EventListener\UserUpdatedListener;
use App\Javelo\Repository\UserClientRepository;
use App\Javelo\Resources\User as JaveloUser;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use Psr\Log\LoggerInterface;

class UserUpdatedListenerTest extends TestCase
{
    use ProphecyTrait;

    private ObjectProphecy $userClientRepositoryProphecy;
    private ObjectProphecy $loggerProphecy;
    private UserUpdatedListener $listener;
    private ObjectProphecy $iriConverter;

    protected function setUp(): void
    {
        $this->iriConverter = $this->prophesize(IriConverterInterface::class);
        $this->userClientRepositoryProphecy = $this->prophesize(UserClientRepository::class);
        $this->loggerProphecy = $this->prophesize(LoggerInterface::class);

        $this->listener = new UserUpdatedListener(
            $this->iriConverter->reveal(),
            $this->userClientRepositoryProphecy->reveal(),
            $this->loggerProphecy->reveal()
        );
    }

    public function testInvokeSuccessfullyUpdatesUser(): void
    {
        $javeloUser = new JaveloUser();
        $changes = ['field' => 'value_changed'];
        $posterIri = null;

        $event = $this->prophesize(UserUpdatedEvent::class);
        $event->getJaveloUser()->willReturn($javeloUser)->shouldBeCalled();
        $event->getChanges()->willReturn($changes)->shouldBeCalled();
        $event->getPosterIri()->willReturn($posterIri)->shouldBeCalled();

        $this->userClientRepositoryProphecy->updateUser($javeloUser, $changes, $posterIri)->shouldBeCalled();

        $this->loggerProphecy->error()->shouldNotBeCalled();

        $this->listener->__invoke($event->reveal());
    }

    public function testInvokeLogsErrorOnException(): void
    {
        $javeloUser = new JaveloUser();
        $changes = ['field' => 'value_changed'];
        $posterIri = null;

        $event = $this->prophesize(UserUpdatedEvent::class);
        $event->getJaveloUser()->willReturn($javeloUser)->shouldBeCalled();
        $event->getChanges()->willReturn($changes)->shouldBeCalled();
        $event->getPosterIri()->willReturn($posterIri)->shouldBeCalled();

        $this->userClientRepositoryProphecy
            ->updateUser($javeloUser, $changes, $posterIri)
            ->willThrow(new \Exception('User update failed'))
            ->shouldBeCalled();

        $this->loggerProphecy->error('Something went wrong when updating {userName} on Javelo: {error}', [
            'userName' => (string) $javeloUser->userName,
            'error' => 'User update failed',
        ])->shouldBeCalled();

        $this->listener->__invoke($event->reveal());
    }
}
