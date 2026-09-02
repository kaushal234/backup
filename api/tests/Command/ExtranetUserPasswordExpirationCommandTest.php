<?php

declare(strict_types=1);

namespace App\Tests\Command;

use App\Command\ExtranetUserPasswordExpirationCommand;
use App\Entity\Sales\ExtranetUser;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\Persistence\ObjectRepository;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

class ExtranetUserPasswordExpirationCommandTest extends TestCase
{
    private MockObject|EntityManagerInterface $entityManager;
    private MockObject|MailerInterface $mailer;
    /**
     * @var MockObject&ObjectRepository
     */
    private MockObject $repository;

    protected function setUp(): void
    {
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->mailer = $this->createMock(MailerInterface::class);
        $this->repository = $this->createMock(EntityRepository::class);

        $this->entityManager
            ->method('getRepository')
            ->with(ExtranetUser::class)
            ->willReturn($this->repository);
    }

    /**
     * @dataProvider userProvider
     */
    public function testExecute(ExtranetUser $user, bool $shouldSendMail): void
    {
        $this->repository->method('findAll')->willReturn([$user]);

        if ($shouldSendMail) {
            $this->mailer
                ->expects($this->once())
                ->method('send')
                ->with($this->callback(static function (Email $email) use ($user) {
                    return $email->getTo()[0]->getAddress() === $user->getEmail()
                        && 'Expiration Password Reminder' === $email->getSubject();
                }));
        } else {
            $this->mailer->expects($this->never())->method('send');
        }

        $command = new ExtranetUserPasswordExpirationCommand(
            $this->entityManager,
            $this->mailer
        );

        $commandTester = new CommandTester($command);
        $statusCode = $commandTester->execute([]);

        $this->assertSame(0, $statusCode);
    }

    public function userProvider(): \Generator
    {
        $today = new \DateTimeImmutable('today');

        $user = $this->createMock(ExtranetUser::class);
        $user->method('isDisabled')->willReturn(true);
        $user->method('isHidden')->willReturn(false);
        $user->method('getEmail')->willReturn('disabled@example.com');
        $user->method('isPasswordExpired')->willReturn(false);
        yield 'disabled user' => [$user, false];

        $user = $this->createMock(ExtranetUser::class);
        $user->method('isDisabled')->willReturn(false);
        $user->method('isHidden')->willReturn(true);
        $user->method('getEmail')->willReturn('hidden@example.com');
        $user->method('isPasswordExpired')->willReturn(false);
        yield 'hidden user' => [$user, false];

        $user = $this->createMock(ExtranetUser::class);
        $user->method('isDisabled')->willReturn(false);
        $user->method('isHidden')->willReturn(false);
        $user->method('getEmail')->willReturn('expired@example.com');
        $user->method('isPasswordExpired')->willReturn(true);
        yield 'expired password user' => [$user, false];

        $user = $this->createMock(ExtranetUser::class);
        $user->method('isDisabled')->willReturn(false);
        $user->method('isHidden')->willReturn(false);
        $user->method('getEmail')->willReturn('30days@example.com');
        $user->method('isPasswordExpired')->willReturn(false);
        $user->method('getPasswordExpirationDate')->willReturn($today->add(new \DateInterval('P30D')));
        yield 'password expires in 30 days' => [$user, true];

        $user = $this->createMock(ExtranetUser::class);
        $user->method('isDisabled')->willReturn(false);
        $user->method('isHidden')->willReturn(false);
        $user->method('getEmail')->willReturn('7days@example.com');
        $user->method('isPasswordExpired')->willReturn(false);
        $user->method('getPasswordExpirationDate')->willReturn($today->add(new \DateInterval('P7D')));
        yield 'password expires in 7 days' => [$user, true];

        $user = $this->createMock(ExtranetUser::class);
        $user->method('isDisabled')->willReturn(false);
        $user->method('isHidden')->willReturn(false);
        $user->method('getEmail')->willReturn('1day@example.com');
        $user->method('isPasswordExpired')->willReturn(false);
        $user->method('getPasswordExpirationDate')->willReturn($today->add(new \DateInterval('P1D')));
        yield 'password expires in 1 day' => [$user, true];

        $user = $this->createMock(ExtranetUser::class);
        $user->method('isDisabled')->willReturn(false);
        $user->method('isHidden')->willReturn(false);
        $user->method('getEmail')->willReturn('5days@example.com');
        $user->method('isPasswordExpired')->willReturn(false);
        $user->method('getPasswordExpirationDate')->willReturn($today->add(new \DateInterval('P5D')));
        yield 'password expires in 5 days' => [$user, false];
    }
}
