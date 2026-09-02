<?php

declare(strict_types=1);

namespace App\Tests\Command\TldGroupSync;

use App\Command\TLDGroupSync\TLDGroupPeopleSyncCommand;
use App\Entity\Directory\BusinessUnit;
use App\Entity\Directory\Department;
use App\Entity\Directory\Location;
use App\Entity\Directory\People;
use App\Entity\Directory\PeopleFile;
use App\Entity\Directory\Phone;
use App\Entity\Directory\Position;
use App\Entity\Directory\Region;
use App\Manager\Directory\PeopleManager;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Query\QueryBuilder;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder as ORMQueryBuilder;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Tester\CommandTester;

class TLDGroupPeopleSyncCommandTest extends KernelTestCase
{
    /**
     * @var string
     */
    final public const COMMAND = 'tld:group:sync:people';

    private Application $application;

    protected function setUp(): void
    {
        self::bootKernel();
        $this->application = new Application(self::$kernel);
    }

    public function testExecuteOnAMESPM()
    {
        $businessUnit = $this->getBusinessUnit(12, 'TLD AME');

        $people = (new People())
            ->setDepartment((new Department())->setName('indre-et-loire'))
            ->setLegacyId(45)
            ->setJobTitle('Spare Parts Manager')
            ->addPhone((new Phone())->setType(Phone::TYPE_RECEPTION)->setNumber('+33 123'))
            ->addPhone((new Phone())->setType(Phone::TYPE_PHONE)->setNumber('+33 456'))
            ->addPhone((new Phone())->setType(Phone::TYPE_MOBILE)->setNumber('+33 789'))
            ->setBusinessUnit($businessUnit)
            ->setPosition((new Position())->setDescription('SPARE PARTS MANAGER'))
            ->setPhoto($photo = new PeopleFile())
            ->setEmail('emile.letueur@skyblog.com')
            ->setFirstname('Emile')
            ->setLastname('Le Tueur');

        $reflFile = new \ReflectionClass($photo);
        $file = $reflFile->getParentClass();
        $reflectionPropertyFile = $file->getProperty('id');
        $reflectionPropertyFile->setAccessible(true);
        $reflectionPropertyFile->setValue($photo, 69);

        $refl = new \ReflectionClass($people);

        /** @var \ReflectionClass $user */
        $user = $refl->getParentClass();

        $reflectionProperty = $user->getProperty('id');
        $reflectionProperty->setAccessible(true);
        $reflectionProperty->setValue($people, 999);

        $entityManagerMock = $this->getEntityManagerMock([$people]);

        /** @var MockObject|PeopleManager $peopleManagerMock */
        $peopleManagerMock = $this->getMockBuilder(PeopleManager::class)
            ->disableOriginalConstructor()
            ->getMock();

        $peopleManagerMock
            ->expects(self::once())
            ->method('hasPublicPicture')
            ->with($people)
            ->willReturn(false);

        $connectionMock = $this->getConnectionMock([
            'id' => 45,
            'firstname' => 'Emile',
            'lastname' => 'LE TUEUR',
            'div_id' => 42,
            'bu_id' => 42,
            'dpt' => 'indre-et-loire',
            'fct' => 'SPARE PARTS MANAGER',
            'title' => 'Spare Parts Manager',
            'phone' => '+33 123',
            'direct_phone' => '+33 456',
            'mobile' => '+33 789',
            'email' => 'emile.letueur@skyblog.com',
            'img_url' => 'https://api.tld-group.com/public/people/999/photo/69',
        ], 2);

        $this->application->addCommand(new TLDGroupPeopleSyncCommand(
            $entityManagerMock,
            $connectionMock,
            $peopleManagerMock
        ));

        $command = $this->application->find(self::COMMAND);

        $tester = new CommandTester($command);

        $tester->execute([
            'command' => self::COMMAND,
        ]);
    }

    public function testExecuteOnEURSPM()
    {
        $businessUnit = $this->getBusinessUnit(7, 'TLD EUR');

        $people = (new People())
            ->setLegacyId(45)
            ->setDepartment((new Department())->setName('indre-et-loire'))
            ->setJobTitle('Blow')
            ->addPhone((new Phone())->setType(Phone::TYPE_RECEPTION)->setNumber('+33 123'))
            ->addPhone((new Phone())->setType(Phone::TYPE_PHONE)->setNumber('+33 456'))
            ->addPhone((new Phone())->setType(Phone::TYPE_MOBILE)->setNumber('+33 789'))
            ->setBusinessUnit($businessUnit)
            ->setPosition((new Position())->setDescription('SPARE PARTS MANAGER'))
            ->setEmail('emile.letueur@skyblog.com')
            ->setFirstname('Emile')
            ->setLastname('Le Tueur');

        $refl = new \ReflectionClass($people);

        /** @var \ReflectionClass $user */
        $user = $refl->getParentClass();

        $reflectionProperty = $user->getProperty('id');
        $reflectionProperty->setAccessible(true);
        $reflectionProperty->setValue($people, 999);

        $entityManagerMock = $this->getEntityManagerMock([$people]);

        /** @var MockObject|PeopleManager $peopleManagerMock */
        $peopleManagerMock = $this->getMockBuilder(PeopleManager::class)
            ->disableOriginalConstructor()
            ->getMock();

        $peopleManagerMock
            ->expects(self::once())
            ->method('hasPublicPicture')
            ->with($people)
            ->willReturn(false);

        $connectionMock = $this->getConnectionMock([
            'id' => 45,
            'firstname' => 'Emile',
            'lastname' => 'LE TUEUR',
            'div_id' => 40,
            'bu_id' => 40,
            'dpt' => 'indre-et-loire',
            'fct' => 'SPARE PARTS MANAGER',
            'title' => 'Blow',
            'phone' => '+33 123',
            'direct_phone' => '+33 456',
            'mobile' => '+33 789',
            'email' => 'emile.letueur@skyblog.com',
            'img_url' => '',
        ], 3);

        $this->application->addCommand(new TLDGroupPeopleSyncCommand(
            $entityManagerMock,
            $connectionMock,
            $peopleManagerMock
        ));

        $command = $this->application->find(self::COMMAND);

        $tester = new CommandTester($command);

        $tester->execute([
            'command' => self::COMMAND,
        ]);
    }

    public function testExecuteOnPublicPeople()
    {
        $businessUnit = $this->getBusinessUnit(7, 'TLD EUR');

        $people = (new People())
            ->setLegacyId(45)
            ->setDepartment((new Department())->setName('indre-et-loire'))
            ->setJobTitle('Blow')
            ->addPhone((new Phone())->setType(Phone::TYPE_RECEPTION)->setNumber('+33 123'))
            ->addPhone((new Phone())->setType(Phone::TYPE_PHONE)->setNumber('+33 456'))
            ->addPhone((new Phone())->setType(Phone::TYPE_MOBILE)->setNumber('+33 789'))
            ->setBusinessUnit($businessUnit)
            ->setPosition((new Position())->setDescription('Doggy'))->setPhoto($photo = new PeopleFile())
            ->setEmail('emile.letueur@skyblog.com')
            ->setFirstname('Emile')
            ->setLastname('Le Tueur');

        $reflFile = new \ReflectionClass($photo);
        $file = $reflFile->getParentClass();
        $reflectionPropertyFile = $file->getProperty('id');
        $reflectionPropertyFile->setAccessible(true);
        $reflectionPropertyFile->setValue($photo, 69);

        $refl = new \ReflectionClass($people);

        /** @var \ReflectionClass $user */
        $user = $refl->getParentClass();

        $reflectionProperty = $user->getProperty('id');
        $reflectionProperty->setAccessible(true);
        $reflectionProperty->setValue($people, 999);

        $entityManagerMock = $this->getEntityManagerMock([$people]);

        /** @var MockObject|PeopleManager $peopleManagerMock */
        $peopleManagerMock = $this->getMockBuilder(PeopleManager::class)
            ->disableOriginalConstructor()
            ->getMock();

        $peopleManagerMock
            ->expects(self::once())
            ->method('hasPublicPicture')
            ->with($people)
            ->willReturn(true)
        ;

        $connectionMock = $this->getConnectionMock([
            'id' => 45,
            'firstname' => 'Emile',
            'lastname' => 'LE TUEUR',
            'div_id' => 7,
            'bu_id' => 7,
            'dpt' => 'indre-et-loire',
            'fct' => 'Doggy',
            'title' => 'Blow',
            'phone' => '+33 123',
            'direct_phone' => '+33 456',
            'mobile' => '+33 789',
            'email' => 'emile.letueur@skyblog.com',
            'img_url' => 'https://api.tld-group.com/public/people/999/photo/69',
        ], 1);

        $this->application->addCommand(new TLDGroupPeopleSyncCommand(
            $entityManagerMock,
            $connectionMock,
            $peopleManagerMock
        ));

        $command = $this->application->find(self::COMMAND);

        $tester = new CommandTester($command);

        $tester->execute([
            'command' => self::COMMAND,
        ]);
    }

    /**
     * @return EntityManager|MockObject
     */
    private function getEntityManagerMock(array $results)
    {
        /** @var MockObject|EntityRepository $businessUnitRepositoryMock */
        $businessUnitRepositoryMock = $this->getMockBuilder(EntityRepository::class)
            ->disableOriginalConstructor()
            ->getMock();

        $businessUnitRepositoryMock
            ->expects(self::exactly(5))
            ->method('findOneBy')
            ->withConsecutive(
                [['name' => 'TLD LAC']],
                [['name' => 'TLD MEAI']],
                [['name' => 'TLD EUR']],
                [['name' => 'TLD STL']],
                [['name' => 'TLD AME']]
            )
            ->willReturnOnConsecutiveCalls(
                $this->getBusinessUnit(42, 'TLD 42'),
                $this->getBusinessUnit(40, 'TLD 40'),
                $this->getBusinessUnit(7, 'TLD 7'),
                $this->getBusinessUnit(9, 'TLD 9'),
                $this->getBusinessUnit(11, 'TLD 11')
            );

        /** @var MockObject|EntityRepository $peopleRepositoryMock */
        $peopleRepositoryMock = $this->getMockBuilder(EntityRepository::class)
            ->disableOriginalConstructor()
            ->getMock();

        /** @var MockObject|ORMQueryBuilder $qbMock */
        $qbMock = $this->getMockBuilder(ORMQueryBuilder::class)
            ->disableOriginalConstructor()
            ->getMock();

        $qbMock->expects(self::exactly(7))
            ->method('addSelect')
            ->withConsecutive(['businessUnit'], ['bu_location'], ['position'], ['acls'], ['department'], ['phones'], ['gr'])
            ->willReturnSelf();

        $qbMock->expects(self::exactly(7))
            ->method('join')
            ->withConsecutive(
                ['p.businessUnit', 'businessUnit'],
                ['businessUnit.location', 'bu_location'],
                ['p.position', 'position'],
                ['p.acls', 'acls'],
                ['p.department', 'department'],
                ['p.phones', 'phones'],
                ['acls.group', 'gr']
            )
            ->willReturnSelf();

        $qbMock->expects(self::once())
            ->method('andWhere')
            ->with('p.disabled = :disabled')
            ->willReturnSelf();

        $qbMock->expects(self::once())
            ->method('setParameter')
            ->with('disabled', false)
            ->willReturnSelf();

        $queryMock = $this->getMockBuilder(Query::class)
            ->disableOriginalConstructor()
            ->getMock();

        $queryMock->expects(self::once())->method('getResult')->willReturn($results);

        $qbMock->expects(self::once())->method('getQuery')->willReturn($queryMock);

        $peopleRepositoryMock->expects(self::once())
            ->method('createQueryBuilder')
            ->with('p')
            ->willReturn($qbMock);

        $entityManagerMock = $this->getMockBuilder(EntityManager::class)
            ->disableOriginalConstructor()
            ->getMock();

        $entityManagerMock
            ->expects(self::exactly(2))
            ->method('getRepository')
            ->withConsecutive([BusinessUnit::class], [People::class])
            ->willReturnOnConsecutiveCalls($businessUnitRepositoryMock, $peopleRepositoryMock);

        return $entityManagerMock;
    }

    /**
     * @return Connection|MockObject
     */
    private function getConnectionMock(array $parameters, int $calls)
    {
        /** @var MockObject|AbstractPlatform $platformMock */
        $platformMock = $this->getMockBuilder(AbstractPlatform::class)
            ->disableOriginalConstructor()
            ->getMock();

        $platformMock
            ->expects(self::once())
            ->method('getTruncateTableSQL')
            ->with('tld_people');

        /** @var MockObject|Connection $connectionMock */
        $connectionMock = $this->getMockBuilder(Connection::class)
            ->disableOriginalConstructor()
            ->getMock();

        $connectionMock
            ->expects(self::once())
            ->method('getDatabasePlatform')
            ->willReturn($platformMock);

        $connectionMock
            ->expects(self::exactly($calls))
            ->method('createQueryBuilder')
            ->willReturn(new QueryBuilder($connectionMock));

        $connectionMock
            ->expects(self::once())
            ->method('executeStatement');

        $connectionMock
            ->expects(self::exactly($calls))
            ->method('executeQuery')
            ->with(
                self::callback(static fn (string $query) => 'INSERT INTO tld_people (id, firstname, lastname, div_id, bu_id, dpt, fct, title, phone, direct_phone, mobile, email, img_url) VALUES(:id, :firstname, :lastname, :div_id, :bu_id, :dpt, :fct, :title, :phone, :direct_phone, :mobile, :email, :img_url)' === $query),
                self::callback(static function (array $params) use ($parameters) {
                    $bu = $params['bu_id'];
                    unset($params['bu_id'], $parameters['bu_id'], $params['div_id'], $parameters['div_id']);

                    return $params === $parameters && \in_array($bu, [42, 40, 7, 9, 11], true);
                })
            );

        return $connectionMock;
    }

    private function getBusinessUnit(int $legacyId, string $name = 'name')
    {
        $businessUnit = (new BusinessUnit())->setName($name);
        $location = (new Location())->setLegacyId($legacyId);
        $businessUnit->setLocation($location);
        $region = (new Region())->setLegacyId($legacyId);
        $businessUnit->setRegion($region);

        return $businessUnit;
    }
}
