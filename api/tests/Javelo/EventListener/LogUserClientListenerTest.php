<?php

declare(strict_types=1);

namespace App\Tests\Javelo\EventListener;

use App\Doctrine\Change;
use App\Entity\Activity\Log;
use App\Entity\Directory\People;
use App\Javelo\Event\LogUserClientEvent;
use App\Javelo\EventListener\LogUserClientListener;
use App\Javelo\Resources\User as JaveloUser;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;

class LogUserClientListenerTest extends TestCase
{
    use ProphecyTrait;

    private EntityManagerInterface|ObjectProphecy $entityManagerProphecy;
    private LogUserClientListener $listener;

    protected function setUp(): void
    {
        $this->entityManagerProphecy = $this->prophesize(EntityManagerInterface::class);

        $this->listener = new LogUserClientListener(
            $this->entityManagerProphecy->reveal()
        );
    }

    public function testInvokeCreatesLogAndPersistsIt(): void
    {
        $javeloUser = new JaveloUser();
        $javeloUser->intranetId = '123';
        $javeloUser->id = 'javelo-id';

        $changes = ['field' => 'value_changed'];

        $event = $this->prophesize(LogUserClientEvent::class);
        $event->getJaveloUser()->willReturn($javeloUser)->shouldBeCalled();
        $event->getChanges()->willReturn($changes)->shouldBeCalled();
        $event->getPoster()->willReturn(null)->shouldBeCalled();

        $this->entityManagerProphecy->persist(
            Argument::that(static function (Log $log) use ($javeloUser, $changes) {
                return $log->getResource() === '/people/'.$javeloUser->intranetId
                    && 'javelo' === $log->discriminator
                    && Change::ACTION_UPDATE === $log->getAction()
                    && $log->getChangeSet() === $changes;
            })
        )->shouldBeCalled();

        $this->entityManagerProphecy->flush()->shouldBeCalled();

        $this->listener->__invoke($event->reveal());
    }

    public function testInvokeWithPoster(): void
    {
        $javeloUser = new JaveloUser();
        $javeloUser->intranetId = '123';
        $javeloUser->id = 'javelo-id';
        $poster = new People();

        $changes = ['field' => 'value_changed'];

        $event = $this->prophesize(LogUserClientEvent::class);
        $event->getJaveloUser()->willReturn($javeloUser)->shouldBeCalled();
        $event->getChanges()->willReturn($changes)->shouldBeCalled();
        $event->getPoster()->willReturn($poster)->shouldBeCalled();

        $this->entityManagerProphecy->persist(
            Argument::that(static function (Log $log) use ($javeloUser, $changes, $poster) {
                return $log->getResource() === '/people/'.$javeloUser->intranetId
                    && 'javelo' === $log->discriminator
                    && Change::ACTION_UPDATE === $log->getAction()
                    && $log->getChangeSet() === $changes
                    && $log->getUser() === $poster;
            })
        )->shouldBeCalled();

        $this->entityManagerProphecy->flush()->shouldBeCalled();

        $this->listener->__invoke($event->reveal());
    }
}
