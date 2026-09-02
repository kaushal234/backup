<?php

declare(strict_types=1);

namespace App\Tests\Command\AI;

use App\Command\AI\CleanAILogsCommand;
use App\Entity\AI\AILog;
use App\Entity\Directory\People;
use App\Repository\AI\AILogRepository;
use App\Repository\Directory\PeopleRepository;
use Doctrine\ORM\EntityManagerInterface;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Tester\CommandTester;

class CleanAILogsCommandTest extends KernelTestCase
{
    use ProphecyTrait;

    final public const COMMAND = 'api:ai:clean-logs';

    public function testDoesNothingWhenNoActiveUsers(): void
    {
        $em = $this->prophesize(EntityManagerInterface::class);

        $peopleRepo = $this->createMock(PeopleRepository::class);
        $logRepo = $this->createMock(AILogRepository::class);

        $em->getRepository(People::class)->willReturn($peopleRepo);
        $em->getRepository(AILog::class)->willReturn($logRepo);

        $peopleRepo->method('findBy')->with(['hidden' => false, 'disabled' => false])->willReturn([]);

        $logRepo->expects($this->never())->method('findIdsOlderThan30DaysForPeople');
        $logRepo->expects($this->never())->method('findIdsExceedingLimitForPeople');
        $em->flush()->shouldNotBeCalled();

        $tester = $this->runCommand($em->reveal());

        $this->assertStringContainsString('Deleted 0 AI log(s).', $tester->getDisplay());
        $this->assertSame(0, $tester->getStatusCode());
    }

    public function testDeletesLogsOlderThan30Days(): void
    {
        $em = $this->prophesize(EntityManagerInterface::class);

        $peopleRepo = $this->createMock(PeopleRepository::class);
        $logRepo = $this->createMock(AILogRepository::class);

        $em->getRepository(People::class)->willReturn($peopleRepo);
        $em->getRepository(AILog::class)->willReturn($logRepo);

        $people = $this->buildPeople(1);
        $log = $this->buildLog();

        $peopleRepo->method('findBy')->willReturn([$people]);
        $logRepo->method('findIdsOlderThan30DaysForPeople')->with(1)->willReturn([42]);
        $logRepo->method('findIdsExceedingLimitForPeople')->with(1)->willReturn([]);
        $logRepo->method('find')->with(42)->willReturn($log);

        $em->remove($log)->shouldBeCalledOnce();
        $em->flush()->shouldBeCalledOnce();
        $em->clear()->shouldBeCalledOnce();

        $tester = $this->runCommand($em->reveal());

        $this->assertStringContainsString('Deleted 1 AI log(s).', $tester->getDisplay());
    }

    public function testDeletesLogsExceedingLimit(): void
    {
        $em = $this->prophesize(EntityManagerInterface::class);

        $peopleRepo = $this->createMock(PeopleRepository::class);
        $logRepo = $this->createMock(AILogRepository::class);

        $em->getRepository(People::class)->willReturn($peopleRepo);
        $em->getRepository(AILog::class)->willReturn($logRepo);

        $people = $this->buildPeople(2);
        $log1 = $this->buildLog();
        $log2 = $this->buildLog();

        $peopleRepo->method('findBy')->willReturn([$people]);
        $logRepo->method('findIdsOlderThan30DaysForPeople')->with(2)->willReturn([]);
        $logRepo->method('findIdsExceedingLimitForPeople')->with(2)->willReturn([10, 11]);
        $logRepo->method('find')->willReturnCallback(static fn (int $id) => match ($id) {
            10 => $log1,
            11 => $log2,
            default => null,
        });

        $em->remove(Argument::type(AILog::class))->shouldBeCalledTimes(2);
        $em->flush()->shouldBeCalledOnce();
        $em->clear()->shouldBeCalledOnce();

        $tester = $this->runCommand($em->reveal());

        $this->assertStringContainsString('Deleted 2 AI log(s).', $tester->getDisplay());
    }

    public function testDoesNotDeletePinnedLogsOlderThan30Days(): void
    {
        $em = $this->prophesize(EntityManagerInterface::class);

        $peopleRepo = $this->createMock(PeopleRepository::class);
        $logRepo = $this->createMock(AILogRepository::class);

        $em->getRepository(People::class)->willReturn($peopleRepo);
        $em->getRepository(AILog::class)->willReturn($logRepo);

        $people = $this->buildPeople(6);

        $peopleRepo->method('findBy')->willReturn([$people]);
        // Pinned logs older than 30 days are excluded by the repository — returns empty
        $logRepo->method('findIdsOlderThan30DaysForPeople')->with(6)->willReturn([]);
        $logRepo->method('findIdsExceedingLimitForPeople')->with(6)->willReturn([]);

        $em->flush()->shouldNotBeCalled();
        $em->clear()->shouldNotBeCalled();

        $tester = $this->runCommand($em->reveal());

        $this->assertStringContainsString('Deleted 0 AI log(s).', $tester->getDisplay());
    }

    public function testDoesNotDeletePinnedLogsExceedingLimit(): void
    {
        $em = $this->prophesize(EntityManagerInterface::class);

        $peopleRepo = $this->createMock(PeopleRepository::class);
        $logRepo = $this->createMock(AILogRepository::class);

        $em->getRepository(People::class)->willReturn($peopleRepo);
        $em->getRepository(AILog::class)->willReturn($logRepo);

        $people = $this->buildPeople(7);
        $log = $this->buildLog();

        $peopleRepo->method('findBy')->willReturn([$people]);
        // User has 32 logs total: 31st is non-pinned (deleted), 32nd is pinned (excluded by repo)
        $logRepo->method('findIdsOlderThan30DaysForPeople')->with(7)->willReturn([]);
        $logRepo->method('findIdsExceedingLimitForPeople')->with(7)->willReturn([301]);
        $logRepo->method('find')->with(301)->willReturn($log);

        $em->remove($log)->shouldBeCalledOnce();
        $em->flush()->shouldBeCalledOnce();
        $em->clear()->shouldBeCalledOnce();

        $tester = $this->runCommand($em->reveal());

        $this->assertStringContainsString('Deleted 1 AI log(s).', $tester->getDisplay());
    }

    public function testDeduplicatesOverlappingIds(): void
    {
        $em = $this->prophesize(EntityManagerInterface::class);

        $peopleRepo = $this->createMock(PeopleRepository::class);
        $logRepo = $this->createMock(AILogRepository::class);

        $em->getRepository(People::class)->willReturn($peopleRepo);
        $em->getRepository(AILog::class)->willReturn($logRepo);

        $people = $this->buildPeople(3);
        $log = $this->buildLog();

        $peopleRepo->method('findBy')->willReturn([$people]);
        // Same ID returned by both rules → should only be deleted once
        $logRepo->method('findIdsOlderThan30DaysForPeople')->with(3)->willReturn([99]);
        $logRepo->method('findIdsExceedingLimitForPeople')->with(3)->willReturn([99]);
        $logRepo->method('find')->with(99)->willReturn($log);

        $em->remove($log)->shouldBeCalledOnce();
        $em->flush()->shouldBeCalledOnce();
        $em->clear()->shouldBeCalledOnce();

        $tester = $this->runCommand($em->reveal());

        $this->assertStringContainsString('Deleted 1 AI log(s).', $tester->getDisplay());
    }

    public function testSkipsLogWhenFindReturnsNull(): void
    {
        $em = $this->prophesize(EntityManagerInterface::class);

        $peopleRepo = $this->createMock(PeopleRepository::class);
        $logRepo = $this->createMock(AILogRepository::class);

        $em->getRepository(People::class)->willReturn($peopleRepo);
        $em->getRepository(AILog::class)->willReturn($logRepo);

        $people = $this->buildPeople(4);

        $peopleRepo->method('findBy')->willReturn([$people]);
        $logRepo->method('findIdsOlderThan30DaysForPeople')->with(4)->willReturn([55]);
        $logRepo->method('findIdsExceedingLimitForPeople')->with(4)->willReturn([]);
        $logRepo->method('find')->with(55)->willReturn(null);

        $em->remove(Argument::any())->shouldNotBeCalled();
        $em->flush()->shouldBeCalledOnce();
        $em->clear()->shouldBeCalledOnce();

        $tester = $this->runCommand($em->reveal());

        $this->assertStringContainsString('Deleted 0 AI log(s).', $tester->getDisplay());
    }

    public function testHandlesMultipleUsers(): void
    {
        $em = $this->prophesize(EntityManagerInterface::class);

        $peopleRepo = $this->createMock(PeopleRepository::class);
        $logRepo = $this->createMock(AILogRepository::class);

        $em->getRepository(People::class)->willReturn($peopleRepo);
        $em->getRepository(AILog::class)->willReturn($logRepo);

        $people1 = $this->buildPeople(10);
        $people2 = $this->buildPeople(20);
        $log1 = $this->buildLog();
        $log2 = $this->buildLog();

        $peopleRepo->method('findBy')->willReturn([$people1, $people2]);

        $logRepo->method('findIdsOlderThan30DaysForPeople')->willReturnCallback(
            static fn (int $id) => match ($id) {
                10 => [100],
                default => [],
            }
        );
        $logRepo->method('findIdsExceedingLimitForPeople')->willReturnCallback(
            static fn (int $id) => match ($id) {
                20 => [200],
                default => [],
            }
        );
        $logRepo->method('find')->willReturnCallback(static fn (int $id) => match ($id) {
            100 => $log1,
            200 => $log2,
            default => null,
        });

        $em->remove(Argument::type(AILog::class))->shouldBeCalledTimes(2);
        $em->flush()->shouldBeCalledTimes(2);
        $em->clear()->shouldBeCalledTimes(2);

        $tester = $this->runCommand($em->reveal());

        $this->assertStringContainsString('Deleted 2 AI log(s).', $tester->getDisplay());
    }

    public function testSkipsUserWithNoLogsToDelete(): void
    {
        $em = $this->prophesize(EntityManagerInterface::class);

        $peopleRepo = $this->createMock(PeopleRepository::class);
        $logRepo = $this->createMock(AILogRepository::class);

        $em->getRepository(People::class)->willReturn($peopleRepo);
        $em->getRepository(AILog::class)->willReturn($logRepo);

        $people = $this->buildPeople(5);

        $peopleRepo->method('findBy')->willReturn([$people]);
        $logRepo->method('findIdsOlderThan30DaysForPeople')->with(5)->willReturn([]);
        $logRepo->method('findIdsExceedingLimitForPeople')->with(5)->willReturn([]);

        $em->flush()->shouldNotBeCalled();
        $em->clear()->shouldNotBeCalled();

        $tester = $this->runCommand($em->reveal());

        $this->assertStringContainsString('Deleted 0 AI log(s).', $tester->getDisplay());
        $this->assertSame(0, $tester->getStatusCode());
    }

    private function buildPeople(int $id): People
    {
        $people = $this->createMock(People::class);
        $people->method('getId')->willReturn($id);

        return $people;
    }

    private function buildLog(): AILog
    {
        return $this->createMock(AILog::class);
    }

    private function runCommand(EntityManagerInterface $em): CommandTester
    {
        self::bootKernel();
        $application = new Application(self::$kernel);
        $application->addCommand(new CleanAILogsCommand($em, new \Symfony\Component\Filesystem\Filesystem(), sys_get_temp_dir()));

        $tester = new CommandTester($application->find(self::COMMAND));
        $tester->execute(['command' => self::COMMAND]);

        return $tester;
    }
}
