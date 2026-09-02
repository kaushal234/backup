<?php

declare(strict_types=1);

namespace App\Tests\Command\TldGroupSync;

use App\Command\TLDGroupSync\TLDGroupSalesAreasSyncCommand;
use App\Entity\Continent;
use App\Entity\Country;
use App\Entity\Directory\Location;
use App\Entity\Directory\Network;
use App\Entity\Directory\People;
use App\Entity\Sales\SalesArea;
use App\Repository\Sales\SalesAreaRepository;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Query\QueryBuilder;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Tester\CommandTester;

class TLDGroupSalesAreasSyncCommandTest extends KernelTestCase
{
    /**
     * @var string
     */
    final public const COMMAND = 'tld:group:sync:sales_areas';

    private Application $application;

    protected function setUp(): void
    {
        self::bootKernel();
        $this->application = new Application(self::$kernel);
    }

    public function testExecuteWillCreateRightQueriesToWordpress()
    {
        /** @var MockObject|SalesAreaRepository $salesAreaRepositoryMock */
        $salesAreaRepositoryMock = $this->getMockBuilder(SalesAreaRepository::class)
            ->disableOriginalConstructor()
            ->getMock()
        ;

        $ssoTLD = (new Location())->setNetwork((new Network())->setName(Network::NETWORK_TLD));
        $ssoSAS = (new Location())->setNetwork((new Network())->setName(Network::NETWORK_SAS));

        $salesAreaRepositoryMock
            ->expects(self::exactly(1))
            ->method('findWithPublicCountry')
            ->willReturn([
                (new SalesArea())
                ->setCountry(
                    (new Country())
                        ->setContinent((new Continent())->setName('incontinent'))
                        ->setName('kinder')
                )
                ->setAsm((new People())->setLegacyId(69))
                ->setSso($ssoTLD),
                (new SalesArea())
                    ->setCountry(
                        (new Country())
                            ->setContinent((new Continent())->setName('incontinent'))
                            ->setName('kinder')
                    )
                    ->setAsm((new People())->setLegacyId(70))
                    ->setSso($ssoSAS),
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
            ->with('tld_sales_areas');

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
            ->expects(self::once())
            ->method('executeQuery')
            ->with(
                self::callback(static fn (string $query) => 'INSERT INTO tld_sales_areas (id, continent, country, rep_id) VALUES(:id, :continent, :country, :asm)' === $query),
                self::callback(static fn (array $parameters) => $parameters === [
                    'id' => 1,
                    'continent' => 'incontinent',
                    'country' => 'kinder',
                    'asm' => 69,
                ])
            );

        $this->application->addCommand(new TLDGroupSalesAreasSyncCommand(
            $salesAreaRepositoryMock,
            $connectionMock
        ));

        $command = $this->application->find(self::COMMAND);

        $tester = new CommandTester($command);

        $tester->execute([
            'command' => self::COMMAND,
        ]);
    }
}
