<?php

declare(strict_types=1);

namespace App\Tests\Command\MIS;

use App\Command\MIS\DisableGuestUserCommand;
use App\Entity\Directory\People;
use App\Entity\MIS\GuestUser\GuestUser;
use App\Notifier\MIS\GuestUser\GuestUserNotifier;
use App\Repository\MIS\GuestUserRepository;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

class DisableGuestUserCommandTest extends TestCase
{
    private MockObject&EntityManagerInterface $entityManager;
    private MockObject&GuestUserRepository $repository;
    private MockObject&MailerInterface $mailer;

    protected function setUp(): void
    {
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->repository = $this->createMock(GuestUserRepository::class);
        $this->mailer = $this->createMock(MailerInterface::class);

        $this->entityManager
            ->method('getRepository')
            ->with(GuestUser::class)
            ->willReturn($this->repository);
    }

    public function testExecuteDisablesGuestAndNotifiesSupervisor(): void
    {
        $guest = $this->createGuest('supervisor@example.com');

        $this->repository->method('findUsersToDisable')->willReturn([$guest]);
        $this->repository->expects($this->once())->method('disableAndHideGuest')->with($guest);

        $this->mailer
            ->expects($this->once())
            ->method('send')
            ->with($this->callback(static function (Email $email): bool {
                return 'supervisor@example.com' === $email->getTo()[0]->getAddress()
                    && 'guest_user.disabled.subject' === $email->getSubject();
            }));

        $this->assertSame(Command::SUCCESS, $this->runCommand());
    }

    public function testExecuteDoesNotNotifyWhenSupervisorHasNoEmail(): void
    {
        $guest = $this->createGuest('');

        $this->repository->method('findUsersToDisable')->willReturn([$guest]);
        $this->repository->expects($this->once())->method('disableAndHideGuest')->with($guest);

        $this->mailer->expects($this->never())->method('send');

        $this->assertSame(Command::SUCCESS, $this->runCommand());
    }

    private function createGuest(string $supervisorEmail): GuestUser&MockObject
    {
        $supervisor = $this->createMock(People::class);
        $supervisor->method('getEmail')->willReturn($supervisorEmail);
        $supervisor->method('getFirstname')->willReturn('John');
        $supervisor->method('getLastname')->willReturn('Doe');

        $guest = $this->createMock(GuestUser::class);
        $guest->method('getSupervisor')->willReturn($supervisor);
        $guest->method('getFirstname')->willReturn('Jane');
        $guest->method('getLastname')->willReturn('Guest');
        $guest->method('__toString')->willReturn('Guest, Jane');

        return $guest;
    }

    private function runCommand(): int
    {
        $command = new DisableGuestUserCommand(
            $this->entityManager,
            new GuestUserNotifier($this->mailer),
        );

        return (new CommandTester($command))->execute([]);
    }
}
