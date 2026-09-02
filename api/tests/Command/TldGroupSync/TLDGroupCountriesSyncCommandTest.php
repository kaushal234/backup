<?php

declare(strict_types=1);

namespace App\Tests\Command\TldGroupSync;

use App\Command\TLDGroupSync\TLDGroupCountriesSyncCommand;
use App\Entity\Country;
use App\Repository\CountryRepository;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Query\QueryBuilder;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Tester\CommandTester;

class TLDGroupCountriesSyncCommandTest extends KernelTestCase
{
    /**
     * @var string
     */
    final public const COMMAND = 'tld:group:sync:countries';

    private Application $application;

    protected function setUp(): void
    {
        self::bootKernel();
        $this->application = new Application(self::$kernel);
    }

    public function testExecuteWillCreateRightQueriesToWordpress()
    {
        /** @var MockObject|CountryRepository $countryRepositoryMock */
        $countryRepositoryMock = $this->getMockBuilder(CountryRepository::class)
            ->disableOriginalConstructor()
            ->getMock()
        ;

        $countryRepositoryMock
            ->expects(self::once())
            ->method('findPublic')
            ->willReturn([
                (new Country())
                    ->setName('kinder'),
            ])
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
            ->with('tld_extranet_country');

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
                self::callback(static fn (string $query) => 'INSERT INTO tld_extranet_country (id, name) VALUES(:id, :name)' === $query),
                self::callback(static fn (array $parameters) => $parameters === [
                    'id' => 1,
                    'name' => 'kinder',
                ])
            );

        $this->application->addCommand(new TLDGroupCountriesSyncCommand(
            $countryRepositoryMock,
            $connectionMock
        ));

        $command = $this->application->find(self::COMMAND);

        $tester = new CommandTester($command);

        $tester->execute([
            'command' => self::COMMAND,
        ]);
    }
}
