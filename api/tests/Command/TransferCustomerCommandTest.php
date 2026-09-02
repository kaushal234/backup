<?php

declare(strict_types=1);

namespace App\Tests\Command;

use ApiPlatform\Metadata\IriConverterInterface;
use App\Command\TransferCustomerCommand;
use App\Entity\Directory\People;
use App\Entity\Sales\Customer;
use App\Repository\Sales\CustomerRepository;
use PHPUnit\Framework\MockObject\MockObject;
use Prophecy\PhpUnit\ProphecyTrait;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Tester\CommandTester;

class TransferCustomerCommandTest extends KernelTestCase
{
    use ProphecyTrait;
    /**
     * @var string
     */
    final public const COMMAND = 'api:transfer:customer';

    public function testExecuteWithWrongArgument()
    {
        /** @var CustomerRepository|MockObject $customerRepository */
        $customerRepository = $this
            ->getMockBuilder(CustomerRepository::class)
            ->disableOriginalConstructor()
            ->getMock();

        $customerRepository
            ->expects(self::never())
            ->method('getCustomersByMainRepresentativeCountry');

        self::bootKernel();
        /** @var IriConverterInterface $iriConverter */
        $iriConverter = static::getContainer()->get(IriConverterInterface::class);

        $application = new Application(self::$kernel);
        $application->addCommand(new TransferCustomerCommand($iriConverter, $customerRepository));

        $command = $application->find(self::COMMAND);
        $commandTester = new CommandTester($command);

        $commandTester->execute([
            'command' => self::COMMAND,
            'from' => '/pouet/12',
            'to' => '/people/10',
        ]);

        $output = $commandTester->getDisplay();
        self::assertStringContainsString('Invalid People submitted (No route matches "/pouet/12".)', $output);
    }

    public function testExecuteWithWrongPeople()
    {
        /** @var CustomerRepository|MockObject $customerRepository */
        $customerRepository = $this
            ->getMockBuilder(CustomerRepository::class)
            ->disableOriginalConstructor()
            ->getMock();

        $customerRepository
            ->expects(self::never())
            ->method('getCustomersByMainRepresentativeCountry');

        self::bootKernel();
        /** @var IriConverterInterface $iriConverter */
        $iriConverter = static::getContainer()->get(IriConverterInterface::class);

        $application = new Application(self::$kernel);
        $application->addCommand(new TransferCustomerCommand($iriConverter, $customerRepository));

        $command = $application->find(self::COMMAND);
        $commandTester = new CommandTester($command);

        $commandTester->execute([
            'command' => self::COMMAND,
            'from' => '/people/999999',
            'to' => '/people/10',
        ]);

        $output = $commandTester->getDisplay();
        self::assertStringContainsString('Invalid People submitted (Item not found for "/people/999999".)', $output);
    }

    public function testExecuteWithWrongCountry()
    {
        /** @var CustomerRepository|MockObject $customerRepository */
        $customerRepository = $this
            ->getMockBuilder(CustomerRepository::class)
            ->disableOriginalConstructor()
            ->getMock();

        $customerRepository
            ->expects(self::never())
            ->method('findBy');

        self::bootKernel();
        /** @var IriConverterInterface $iriConverter */
        $iriConverter = static::getContainer()->get(IriConverterInterface::class);

        $application = new Application(self::$kernel);
        $application->addCommand(new TransferCustomerCommand($iriConverter, $customerRepository));

        $command = $application->find(self::COMMAND);
        $commandTester = new CommandTester($command);

        $commandTester->execute([
            'command' => self::COMMAND,
            'from' => '/people/12',
            'to' => '/people/10',
            '--country' => '/countries/999999',
        ]);

        $output = $commandTester->getDisplay();
        self::assertStringContainsString('Invalid Country submitted (Item not found for "/countries/999999".)', $output);
    }

    public function testExecuteWithNoCustomers()
    {
        /** @var CustomerRepository|MockObject $customerRepository */
        $customerRepository = $this
            ->getMockBuilder(CustomerRepository::class)
            ->disableOriginalConstructor()
            ->getMock();

        $customerRepository
            ->expects(self::once())
            ->method('getCustomersByMainRepresentativeCountry')
            ->willReturn([]);

        self::bootKernel();
        /** @var IriConverterInterface $iriConverter */
        $iriConverter = static::getContainer()->get(IriConverterInterface::class);

        $application = new Application(self::$kernel);
        $application->addCommand(new TransferCustomerCommand($iriConverter, $customerRepository));

        $command = $application->find(self::COMMAND);
        $commandTester = new CommandTester($command);

        $commandTester->execute([
            'command' => self::COMMAND,
            'from' => '/people/12',
            'to' => '/people/11',
            '--country' => '/countries/11',
        ]);

        $output = $commandTester->getDisplay();
        self::assertStringContainsString('No customer to transfer', $output);
    }

    public function testExecuteWithFoundCustomersAndCountry()
    {
        /** @var Customer[] $fakeCustomers */
        $fakeCustomers = [];

        for ($i = 0; $i < 5; ++$i) {
            $fakeCustomers[] = new Customer();
        }

        /** @var CustomerRepository|MockObject $customerRepository */
        $customerRepository = $this
            ->getMockBuilder(CustomerRepository::class)
            ->disableOriginalConstructor()
            ->getMock();

        $customerRepository
            ->expects(self::once())
            ->method('getCustomersByMainRepresentativeCountry')
            ->willReturn($fakeCustomers);

        $customerRepository
            ->expects(self::once())
            ->method('changeCustomerMainRepresentative')
            ->with(
                self::callback(static fn ($customers) => $customers === $fakeCustomers),
                self::callback(static fn (People $asm) => 11 === $asm->getId())
            );

        self::bootKernel();
        /** @var IriConverterInterface $iriConverter */
        $iriConverter = static::getContainer()->get(IriConverterInterface::class);

        $application = new Application(self::$kernel);
        $application->addCommand(new TransferCustomerCommand($iriConverter, $customerRepository));

        $command = $application->find(self::COMMAND);
        $commandTester = new CommandTester($command);

        $commandTester->execute([
            'command' => self::COMMAND,
            'from' => '/people/12',
            'to' => '/people/11',
            '--country' => '/countries/11',
        ]);

        $output = $commandTester->getDisplay();
        self::assertStringContainsString('5 customers transferred', $output);
    }

    public function testExecuteWithFoundCustomers()
    {
        /** @var Customer[] $fakeCustomers */
        $fakeCustomers = [];

        for ($i = 0; $i < 5; ++$i) {
            $fakeCustomers[] = new Customer();
        }

        /** @var CustomerRepository|MockObject $customerRepository */
        $customerRepository = $this
            ->getMockBuilder(CustomerRepository::class)
            ->disableOriginalConstructor()
            ->getMock();

        $customerRepository
            ->expects(self::once())
            ->method('getCustomersByMainRepresentativeCountry')
            ->willReturn($fakeCustomers);

        $customerRepository
            ->expects(self::once())
            ->method('changeCustomerMainRepresentative')
            ->with(
                self::callback(static fn ($customers) => $customers === $fakeCustomers),
                self::callback(static fn (People $asm) => 11 === $asm->getId())
            );

        self::bootKernel();
        /** @var IriConverterInterface $iriConverter */
        $iriConverter = static::getContainer()->get(IriConverterInterface::class);

        $application = new Application(self::$kernel);
        $application->addCommand(new TransferCustomerCommand($iriConverter, $customerRepository));

        $command = $application->find(self::COMMAND);
        $commandTester = new CommandTester($command);

        $commandTester->execute([
            'command' => self::COMMAND,
            'from' => '/people/12',
            'to' => '/people/11',
        ]);

        $output = $commandTester->getDisplay();
        self::assertStringContainsString('5 customers transferred', $output);
    }
}
