<?php

declare(strict_types=1);

namespace App\Tests\Command\Legal;

use App\Command\Legal\ContractExpiringInOneMonthCommand;
use App\Entity\Legal\Contract;
use App\Notifier\Legal\ContractExpirationNotifier;
use App\Repository\Legal\ContractRepository;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;

class ContractExpiringInOneMonthCommandTest extends TestCase
{
    private ContractExpirationNotifier&MockObject $notifier;
    private ContractRepository&MockObject $contractRepository;

    protected function setUp(): void
    {
        $this->notifier = $this->createMock(ContractExpirationNotifier::class);
        $this->contractRepository = $this->createMock(ContractRepository::class);
    }

    public function testExecuteWithNoExpiringContracts(): void
    {
        $this->contractRepository
            ->expects($this->once())
            ->method('findContractsExpiringInOneMonth')
            ->willReturn([]);

        $this->notifier
            ->expects($this->never())
            ->method('notifyExpiringSoon');

        $commandTester = $this->createCommandTester();
        $statusCode = $commandTester->execute([]);

        $this->assertSame(Command::SUCCESS, $statusCode);
        $this->assertStringContainsString(
            'No contracts expiring in one month',
            $commandTester->getDisplay()
        );
    }

    public function testExecuteWithExpiringContracts(): void
    {
        $contract1 = $this->createContract(10, 'Contract A');
        $contract2 = $this->createContract(11, 'Contract B');

        $this->contractRepository
            ->expects($this->once())
            ->method('findContractsExpiringInOneMonth')
            ->willReturn([$contract1, $contract2]);

        $this->notifier
            ->expects($this->exactly(2))
            ->method('notifyExpiringSoon');

        $commandTester = $this->createCommandTester();
        $statusCode = $commandTester->execute([]);

        $this->assertSame(Command::SUCCESS, $statusCode);

        $display = $commandTester->getDisplay();
        $this->assertStringContainsString('2 contracts expiring in one month notified', $display);
        $this->assertStringContainsString('#10', $display);
        $this->assertStringContainsString('Contract A', $display);
        $this->assertStringContainsString('#11', $display);
        $this->assertStringContainsString('Contract B', $display);
    }

    public function testExecuteWithNotificationFailure(): void
    {
        $contract = $this->createContract(99, 'Failing contract');

        $this->contractRepository
            ->expects($this->once())
            ->method('findContractsExpiringInOneMonth')
            ->willReturn([$contract]);

        $this->notifier
            ->expects($this->once())
            ->method('notifyExpiringSoon')
            ->willThrowException(
                $this->createMock(TransportExceptionInterface::class)
            );

        $commandTester = $this->createCommandTester();
        $statusCode = $commandTester->execute([]);

        $this->assertSame(Command::SUCCESS, $statusCode);

        $display = $commandTester->getDisplay();
        $this->assertStringContainsString('Failed to send email for contract', $display);
        $this->assertStringContainsString('#99', $display);
        $this->assertStringContainsString('Failing contract', $display);
    }

    private function createCommandTester(): CommandTester
    {
        $command = new ContractExpiringInOneMonthCommand(
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
