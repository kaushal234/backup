<?php

declare(strict_types=1);

namespace App\Tests\Command\TldGroupSync;

use App\Command\TLDGroupSync\TLDGroupLocationsSyncCommand;
use App\Entity\AddressWithCountry;
use App\Entity\Directory\Location;
use App\Entity\Directory\LocationCapability;
use App\Entity\Directory\LocationContact;
use App\Repository\Directory\LocationRepository;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Query\QueryBuilder;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Tester\CommandTester;

class TLDGroupLocationsSyncCommandTest extends KernelTestCase
{
    /**
     * @var string
     */
    final public const COMMAND = 'tld:group:sync:locations';

    private Application $application;

    protected function setUp(): void
    {
        self::bootKernel();
        $this->application = new Application(self::$kernel);
    }

    public function testExecuteWillCreateRightQueriesToWordpress()
    {
        /** @var MockObject|LocationRepository $locationRepositoryMock */
        $locationRepositoryMock = $this->getMockBuilder(LocationRepository::class)
            ->disableOriginalConstructor()
            ->getMock()
        ;

        $locationRepositoryMock
            ->expects(self::once())
            ->method('findPublic')
            ->willReturn([
                (new Location())
                    ->setLegacyId(1)
                    ->setName('name')
                    ->setCompany('company')
                    ->setAddress(
                        (new AddressWithCountry())
                            ->setStreet1('street1')
                            ->setStreet2('street2')
                            ->setCity('city')
                            ->setPostalCode('postal_code')
                            ->setCountry('FR')
                    )
                    ->setContact(
                        (new LocationContact())
                            ->setTelephone('tel')
                            ->setFax('fax')
                    )
                    ->setCapability((new LocationCapability())->setSso(true)),
                (new Location())
                    ->setLegacyId(2)
                    ->setName('name2')
                    ->setCompany('company2')
                    ->setAddress(
                        (new AddressWithCountry())
                            ->setStreet1('street1')
                            ->setStreet2('street2')
                            ->setCity('city')
                            ->setPostalCode('postal_code')
                            ->setCountry('FR')
                    )
                    ->setContact(
                        (new LocationContact())
                            ->setTelephone('tel')
                            ->setFax('fax')
                    )
                    ->setCapability((new LocationCapability())->setSso(false)),
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
            ->with('tld_location');

        $connectionMock
            ->expects(self::once())
            ->method('getDatabasePlatform')
            ->willReturn($platformMock);

        $connectionMock
            ->expects(self::exactly(2))
            ->method('createQueryBuilder')
            ->willReturn(new QueryBuilder($connectionMock));

        $connectionMock
            ->expects(self::exactly(1))
            ->method('executeStatement');

        $connectionMock
            ->expects(self::exactly(2))
            ->method('executeQuery')
            ->withConsecutive(
                [
                    'INSERT INTO tld_location (id, location, company_name, region, street1, street2, city, postal_code, country, tel, fax, role) VALUES(:id, :location, :company_name, :region, :street1, :street2, :city, :postal_code, :country, :tel, :fax, :role)',
                    [
                        'id' => 1,
                        'location' => 'name',
                        'company_name' => 'company',
                        'region' => '',
                        'street1' => 'street1',
                        'street2' => 'street2',
                        'city' => 'city',
                        'postal_code' => 'postal_code',
                        'country' => 'France',
                        'tel' => 'tel',
                        'fax' => 'fax',
                        'role' => 'SSO',
                    ],
                ],
                [
                    'INSERT INTO tld_location (id, location, company_name, region, street1, street2, city, postal_code, country, tel, fax, role) VALUES(:id, :location, :company_name, :region, :street1, :street2, :city, :postal_code, :country, :tel, :fax, :role)',
                    [
                        'id' => 2,
                        'location' => 'name2',
                        'company_name' => 'company2',
                        'region' => '',
                        'street1' => 'street1',
                        'street2' => 'street2',
                        'city' => 'city',
                        'postal_code' => 'postal_code',
                        'country' => 'France',
                        'tel' => 'tel',
                        'fax' => 'fax',
                        'role' => 'ERP',
                    ],
                ]
            );

        $this->application->addCommand(new TLDGroupLocationsSyncCommand(
            $locationRepositoryMock,
            $connectionMock
        ));

        $command = $this->application->find(self::COMMAND);

        $tester = new CommandTester($command);

        $tester->execute([
            'command' => self::COMMAND,
        ]);
    }
}
