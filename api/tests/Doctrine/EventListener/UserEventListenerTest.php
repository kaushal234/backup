<?php

declare(strict_types=1);

namespace App\Tests\Doctrine\EventListener;

use ApiPlatform\Metadata\IriConverterInterface;
use App\Agile\Event\GrantAgileGroupAccessEvent;
use App\Agile\Message\UpdateUserMessage;
use App\Doctrine\EventListener\UserEventListener;
use App\Entity\Directory\People;
use App\Javelo\Event\GrantJaveloGroupAccessEvent;
use App\Javelo\Event\UpdateJaveloUserEvent;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Event\PostUpdateEventArgs;
use Doctrine\ORM\UnitOfWork;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;

final class UserEventListenerTest extends TestCase
{
    /**
     * @dataProvider provideScenarios
     */
    public function testPostUpdate(
        People $people,
        array $changeSet,
        array $expectedEvents,
        array $expectedMessages
    ): void {
        $uow = $this->createMock(UnitOfWork::class);
        $uow->expects(self::once())
            ->method('getEntityChangeSet')
            ->with($people)
            ->willReturn($changeSet);

        $em = $this->createMock(EntityManagerInterface::class);
        $em->expects(self::once())
            ->method('getUnitOfWork')
            ->willReturn($uow);

        $args = new PostUpdateEventArgs($people, $em);

        $dispatchedEvents = [];
        $dispatcher = $this->createMock(EventDispatcherInterface::class);
        $dispatcher->expects(self::exactly(\count($expectedEvents)))
            ->method('dispatch')
            ->willReturnCallback(static function ($event) use (&$dispatchedEvents) {
                $dispatchedEvents[] = $event;

                return $event;
            });

        $dispatchedMessages = [];
        $bus = $this->createMock(MessageBusInterface::class);
        $bus->expects(self::exactly(\count($expectedMessages)))
            ->method('dispatch')
            ->willReturnCallback(static function ($message) use (&$dispatchedMessages) {
                $dispatchedMessages[] = $message;

                return new Envelope($message);
            });

        $iri = $this->createMock(IriConverterInterface::class);
        if (\count($expectedMessages) > 0) {
            $iri->expects(self::once())
                ->method('getIriFromResource')
                ->with($people)
                ->willReturn('/api/people/123');
        }

        $listener = new UserEventListener($dispatcher, $bus, $iri);

        $listener->postUpdate($args);

        $eventClasses = array_map(static fn ($e) => $e::class, $dispatchedEvents);
        foreach ($expectedEvents as $cls) {
            self::assertContains($cls, $eventClasses, "Expected event $cls not dispatched");
        }
        if (empty($expectedEvents)) {
            self::assertEmpty($dispatchedEvents, 'No events should have been dispatched.');
        }

        $msgClasses = array_map(static fn ($m) => $m::class, $dispatchedMessages);
        foreach ($expectedMessages as $cls) {
            self::assertContains($cls, $msgClasses, "Expected message $cls not dispatched");
        }
        if (empty($expectedMessages)) {
            self::assertEmpty($dispatchedMessages, 'No messages should have been dispatched.');
        }
    }

    public static function provideScenarios(): iterable
    {
        $enabled = (new People())->setDisabled(false);
        $disabled = (new People())->setDisabled(true);

        yield 'reactivation → verify Agile/Javelo access, then update profile' => [
            $enabled,
            ['disabled' => [true, false]],
            [GrantJaveloGroupAccessEvent::class, GrantAgileGroupAccessEvent::class, UpdateJaveloUserEvent::class], [UpdateUserMessage::class],
        ];

        yield 'contract type changes (enabled) → verify Agile/Javelo access, then update profile' => [
            $enabled,
            ['contractType' => ['CDD', 'CDI']],
            [GrantJaveloGroupAccessEvent::class, GrantAgileGroupAccessEvent::class, UpdateJaveloUserEvent::class], [UpdateUserMessage::class],
        ];

        yield 'contract type changes (still disabled) → only update profile' => [
            $disabled,
            ['contractType' => ['CDD', 'CDI']],
            [UpdateJaveloUserEvent::class], [UpdateUserMessage::class],
        ];

        yield 'change other field → only update profile' => [
            $enabled,
            ['email' => ['a@a.com', 'b@b.com']],
            [UpdateJaveloUserEvent::class], [UpdateUserMessage::class],
        ];

        yield 'only lastLogin → no dispatch' => [
            $enabled,
            ['lastLogin' => [null, new \DateTimeImmutable()]],
            [],
            [],
        ];
    }
}
