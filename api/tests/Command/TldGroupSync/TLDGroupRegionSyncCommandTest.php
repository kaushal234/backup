<?php

declare(strict_types=1);

namespace App\Tests\Command\TldGroupSync;

use App\Command\TLDGroupSync\TLDGroupRegionSyncCommand;
use App\Entity\Directory\Region;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Query\QueryBuilder;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Prophecy\PhpUnit\ProphecyTrait;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Tester\CommandTester;

class TLDGroupRegionSyncCommandTest extends KernelTestCase
{
    use ProphecyTrait;

    /**
     * @var string
     */
    final public const COMMAND = 'tld:group:sync:regions';

    private Application $application;

    protected function setUp(): void
    {
        self::bootKernel();
        $this->application = new Application(self::$kernel);
    }

    public function testExecuteWillCreateRightQueriesToWordpress()
    {
        $entityManagerProphecy = $this->prophesize(EntityManagerInterface::class);
        $regionRepositoryMock = $this->createMock(EntityRepository::class);
        $connectionProphecy = $this->prophesize(Connection::class);
        $platformProphecy = $this->prophesize(AbstractPlatform::class);

        $entityManagerProphecy->getRepository(Region::class)->shouldBeCalledOnce()->willReturn($regionRepositoryMock);
        $regionRepositoryMock->expects($this->once())->method('findAll')->willReturn([(new Region())->setLegacyId(1)->setName('name'), (new Region())->setLegacyId(2)->setName('name2')]);
        $connectionProphecy->getDatabasePlatform()->shouldBeCalledOnce()->willReturn($platformProphecy->reveal());
        $platformProphecy->getTruncateTableSQL('tld_division')->shouldBeCalledOnce()->willReturn('TRUNCATE tld_division');

        $connectionProphecy->createQueryBuilder()->shouldBeCalledTimes(2)->willReturn(new QueryBuilder($connectionProphecy->reveal()));
        $connectionProphecy->executeStatement('TRUNCATE tld_division')->shouldBeCalledOnce();
        $connectionProphecy->executeQuery('INSERT INTO tld_division (id, division) VALUES(:id, :division)', ['id' => 1, 'division' => 'name'])->shouldBeCalledOnce();
        $connectionProphecy->executeQuery('INSERT INTO tld_division (id, division) VALUES(:id, :division)', ['id' => 2, 'division' => 'name2'])->shouldBeCalledOnce();

        $this->application->addCommand(new TLDGroupRegionSyncCommand($entityManagerProphecy->reveal(), $connectionProphecy->reveal()));
        $command = $this->application->find(self::COMMAND);
        $tester = new CommandTester($command);

        $tester->execute([
            'command' => self::COMMAND,
        ]);
    }
}
