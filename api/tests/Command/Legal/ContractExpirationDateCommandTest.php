<?php

declare(strict_types=1);

namespace App\Tests\Command\Legal;

use App\Command\Legal\ContractExpirationDateCommand;
use App\Entity\Legal\Contract;
use App\Notifier\Legal\ContractExpirationNotifier;
use App\Repository\Legal\ContractRepository;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;

class ContractExpirationDateCommandTest extends TestCase
{
    private EntityManagerInterface&MockObject $entityManager;
    private ContractExpirationNotifier&MockObject $notifier;
    private ContractRepository&MockObject $contractRepository;

    protected function setUp(): void
    {
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->notifier = $this->createMock(ContractExpirationNotifier::class);
        $this->contractRepository = $this->createMock(ContractRepository::class);
    }

    public function testExecuteWithNoExpiredContracts(): void
    {
        $this->contractRepository
            ->expects($this->once())
            ->method('findExpiredContracts')
            ->willReturn([]);

        $this->entityManager
            ->expects($this->never())
            ->method('flush');

        $commandTester = $this->createCommandTester();
        $statusCode = $commandTester->execute([]);

        $this->assertSame(Command::SUCCESS, $statusCode);
        $this->assertStringContainsString(
            'No contracts to expire',
            $commandTester->getDisplay()
        );
    }

    public function testExecuteWithExpiredContracts(): void
    {
        $contract1 = $this->createContract(1, 'Contract A');
        $contract2 = $this->createContract(2, 'Contract B');

        $this->contractRepository
            ->expects($this->once())
            ->method('findExpiredContracts')
            ->willReturn([$contract1, $contract2]);

        $this->notifier
            ->expects($this->exactly(2))
            ->method('notifyExpiration');

        $this->entityManager
            ->expects($this->once())
            ->method('flush');

        $commandTester = $this->createCommandTester();
        $statusCode = $commandTester->execute([]);

        $this->assertSame(Command::SUCCESS, $statusCode);

        $this->assertSame(Contract::EXPIRED, $contract1->status);
        $this->assertSame(Contract::EXPIRED, $contract2->status);

        $this->assertStringContainsString(
            '2 contracts expired',
            $commandTester->getDisplay()
        );
    }

    public function testExecuteWithNotificationFailure(): void
    {
        $contract = $this->createContract(42, 'Failing contract');

        $this->contractRepository
            ->expects($this->once())
            ->method('findExpiredContracts')
            ->willReturn([$contract]);

        $this->notifier
            ->expects($this->once())
            ->method('notifyExpiration')
            ->willThrowException(
                $this->createMock(TransportExceptionInterface::class)
            );

        $this->entityManager
            ->expects($this->once())
            ->method('flush');

        $commandTester = $this->createCommandTester();
        $statusCode = $commandTester->execute([]);

        $this->assertSame(Command::SUCCESS, $statusCode);
        $this->assertSame(Contract::EXPIRED, $contract->status);

        $display = $commandTester->getDisplay();
        $this->assertStringContainsString('Failed to send email for expired contract', $display);
        $this->assertStringContainsString('#42', $display);
        $this->assertStringContainsString('Failing contract', $display);
    }

    private function createCommandTester(): CommandTester
    {
        $command = new ContractExpirationDateCommand(
            $this->entityManager,
            $this->notifier,
            $this->contractRepository
        );

        return new CommandTester($command);
    }

    private function createContract(int $id, string $shortDescription): Contract
    {
        $contract = new Contract();
        $contract->shortDescription = $shortDescription;

        $reflection = new \ReflectionProperty(Contract::class, 'id');
        $reflection->setValue($contract, $id);

        return $contract;
    }
}
