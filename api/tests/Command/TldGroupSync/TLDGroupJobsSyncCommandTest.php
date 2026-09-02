<?php

declare(strict_types=1);

namespace App\Tests\Command\TldGroupSync;

use App\Command\TLDGroupSync\TLDGroupJobsSyncCommand;
use App\Entity\Directory\BusinessUnit;
use App\Entity\HumanResources\Job;
use App\Repository\HumanResources\JobRepository;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Query\QueryBuilder;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Tester\CommandTester;

class TLDGroupJobsSyncCommandTest extends KernelTestCase
{
    final public const COMMAND = 'tld:group:sync:jobs';

    private Application $application;

    protected function setUp(): void
    {
        self::bootKernel();
        $this->application = new Application(self::$kernel);
    }

    public function testExecuteWillCreateRightQueriesToWordpress()
    {
        /** @var MockObject|JobRepository $jobRepository */
        $jobRepository = $this->getMockBuilder(JobRepository::class)
            ->disableOriginalConstructor()
            ->getMock()
        ;
        $job = (new Job())
            ->setTitle('title')
            ->setExperience('experience')
            ->setDiploma('diploma')
            ->setDescription('desc')
            ->setCreatedAt(new \DateTime())
            ->setBusinessUnit((new BusinessUnit())->setName('TLD SHE'))
        ;

        $refl = new \ReflectionClass($job);
        $reflectionProperty = $refl->getProperty('id');
        $reflectionProperty->setAccessible(true);
        $reflectionProperty->setValue($job, 1);

        $jobRepository
            ->expects(self::exactly(1))
            ->method('findPublic')
            ->willReturn([$job])
        ;

        /** @var MockObject|Connection $connectionMock */
        $connectionMock = $this->getMockBuilder(Connection::class)
            ->disableOriginalConstructor()
            ->getMock()
        ;

        /** @var MockObject|AbstractPlatform $platformMock */
        $platformMock = $this->getMockBuilder(AbstractPlatform::class)
            ->disableOriginalConstructor()
            ->getMock()
        ;

        $platformMock
            ->expects(self::once())
            ->method('getTruncateTableSQL')
            ->with('tld_job');

        $connectionMock
            ->expects(self::once())
            ->method('getDatabasePlatform')
            ->willReturn($platformMock);

        $connectionMock
            ->expects(self::once())
            ->method('createQueryBuilder')
            ->willReturn(new QueryBuilder($connectionMock));

        $connectionMock
            ->expects(self::once())
            ->method('executeStatement');

        $connectionMock
            ->expects(self::exactly(1))
            ->method('executeQuery')
            ->with(
                'INSERT INTO tld_job (id, dt, title, diploma, experience, description, country, city, state) VALUES(:id, :dt, :title, :diploma, :experience, :description, :country, :city, :state)',
                self::callback(static fn (array $parameters) => $parameters === [
                    'id' => 1,
                    'dt' => (new \DateTime())->format('Y-m-d'),
                    'title' => 'title',
                    'diploma' => 'diploma',
                    'experience' => 'experience',
                    'description' => 'desc',
                    'country' => 'Canada',
                    'city' => 'Sherbrooke',
                    'state' => 'QC',
                ])
            );

        $this->application->addCommand(new TLDGroupJobsSyncCommand(
            $jobRepository,
            $connectionMock
        ));

        $command = $this->application->find(self::COMMAND);

        $tester = new CommandTester($command);

        $tester->execute([
            'command' => self::COMMAND,
        ]);
    }
}
