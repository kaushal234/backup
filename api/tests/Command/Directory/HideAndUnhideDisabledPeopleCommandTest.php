<?php

declare(strict_types=1);

namespace App\Tests\Command\Directory;

use App\Command\Directory\HideAndUnhideDisabledPeopleCommand;
use App\Entity\Directory\People;
use App\Repository\Directory\PeopleRepository;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Tester\CommandTester;

class HideAndUnhideDisabledPeopleCommandTest extends KernelTestCase
{
    final public const COMMAND = 'api:people:hide-and-unhide';

    private PeopleRepository|MockObject $peopleRepositoryMock;

    protected function setUp(): void
    {
        parent::setUp();
        $this->peopleRepositoryMock = $this->createMock(PeopleRepository::class);
    }

    public function testExecute()
    {
        $people1 = new People();
        $people1ArrivalDate = new \DateTime('+3 weeks');
        $people1->setLastname('Doe')->setFirstname('John')->setDisabled(true)->setHidden(false);
        $people1->setEnableAt($people1ArrivalDate);

        $people2 = new People();
        $people2DisabledDate = new \DateTime('-5 weeks');
        $people2->setLastname('Smith')->setFirstname('Jane')->setDisabled(true)->setHidden(true)->setDisabledAt($people2DisabledDate);

        $this->peopleRepositoryMock
            ->expects($this->once())
            ->method('findDisabledPeopleToUnhide')
            ->willReturn([$people1]);

        $this->peopleRepositoryMock
            ->expects($this->once())
            ->method('findDisabledPeopleToHide')
            ->willReturn([$people2]);

        $this->peopleRepositoryMock
            ->expects($this->once())
            ->method('unhidePeople')
            ->with($people1);

        $this->peopleRepositoryMock
            ->expects($this->once())
            ->method('hidePeople')
            ->with($people2);

        self::bootKernel();
        $application = new Application(self::$kernel);
        $application->addCommand(new HideAndUnhideDisabledPeopleCommand($this->peopleRepositoryMock));

        $command = $application->find(self::COMMAND);
        $commandTester = new CommandTester($command);
        $commandTester->execute(['command' => self::COMMAND]);
        $output = $commandTester->getDisplay();

        $this->assertStringContainsString(\sprintf('Arrival %s, unhidden %s, %s.', $people1ArrivalDate->format('Y-m-d'), $people1->getLastname(), $people1->getFirstname()), $output);
        $this->assertStringContainsString(\sprintf('Departure %s, hidden %s, %s.', $people2DisabledDate->format('Y-m-d'), $people2->getLastname(), $people2->getFirstname()), $output);
        $this->assertStringContainsString('unhidden 1 people', $output);
        $this->assertStringContainsString('hidden 1 people', $output);
    }
}
