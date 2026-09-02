<?php

declare(strict_types=1);

namespace App\Tests\Agile\EventListener;

use App\Agile\Event\LogUserUpdateOnClientEvent;
use App\Agile\EventListener\LogUserUpdateOnClientListener;
use App\Agile\Resources\User;
use App\Doctrine\Change;
use App\Entity\Activity\Log;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;

class ClientUpdatedListenerTest extends TestCase
{
    use ProphecyTrait;
    private $entityManager;

    protected function setUp(): void
    {
        $this->entityManager = $this->prophesize(EntityManagerInterface::class);
    }

    public function testInvokeCreatesLogForNewUser(): void
    {
        $agileUser = new User();
        $agileUser->peopleId = '123';
        $agileUser->isNew = true;

        $changes = ['some' => [0 => 'nada', 1 => 'nada change']];

        $event = new LogUserUpdateOnClientEvent($agileUser, $changes);
        $listener = new LogUserUpdateOnClientListener($this->entityManager->reveal());
        $listener->__invoke($event);

        $this->entityManager->persist(Argument::that(static function ($log) use ($agileUser, $changes) {
            if (!$log instanceof Log) {
                return false;
            }

            foreach ($changes as $field => $values) {
                if ($log->getChangeSet()[$field] !== $values) {
                    return false;
                }
            }

            if (null !== $log->getUser()) {
                return false;
            }

            if (Change::ACTION_CREATE !== $log->getAction()) {
                return false;
            }

            if (!str_contains($log->getResource(), '/people/'.$agileUser->peopleId)) {
                return false;
            }

            if ('agile' !== $log->discriminator) {
                return false;
            }

            return true;
        }))->shouldBeCalledOnce();

        $this->entityManager->flush()->shouldBeCalledOnce();
    }

    public function testInvokeCreatesLogForExistingUser(): void
    {
        $agileUser = new User();
        $agileUser->peopleId = '456';
        $agileUser->isNew = false;

        $changes = ['field' => [
            0 => 'previous',
            1 => 'updatedValue'],
        ];

        $event = new LogUserUpdateOnClientEvent($agileUser, $changes);
        $listener = new LogUserUpdateOnClientListener($this->entityManager->reveal());
        $listener->__invoke($event);

        $this->entityManager->persist(Argument::that(static function ($log) use ($agileUser, $changes) {
            if (!$log instanceof Log) {
                return false;
            }

            foreach ($changes as $field => $values) {
                if ($log->getChangeSet()[$field] !== $values) {
                    return false;
                }
            }

            if (null !== $log->getUser()) {
                return false;
            }

            if (Change::ACTION_UPDATE !== $log->getAction()) {
                return false;
            }

            if (!str_contains($log->getResource(), '/people/'.$agileUser->peopleId)) {
                return false;
            }

            if ('agile' !== $log->discriminator) {
                return false;
            }

            return true;
        }))->shouldBeCalled();
        $this->entityManager->flush()->shouldBeCalledOnce();

        $this->entityManager->flush()->shouldBeCalled();
    }
}
