<?php

declare(strict_types=1);

namespace App\Tests\Javelo\Factory;

use App\Entity\Directory\BusinessUnit;
use App\Entity\Directory\ContractType;
use App\Entity\Directory\Department;
use App\Entity\Directory\Division;
use App\Entity\Directory\People;
use App\Entity\Directory\Position;
use App\Entity\Directory\Region;
use App\Entity\Directory\SubDivision;
use App\Javelo\Factory\UserFactory;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;

class UserFactoryTest extends TestCase
{
    use ProphecyTrait;

    private const JAVELO_ID = 'javelo_id';
    private UserFactory $userFactory;

    protected function setUp(): void
    {
        $this->userFactory = new UserFactory();
    }

    /**
     * @dataProvider peopleProvider
     */
    public function testCreateFromPeople(People $people, array $synchronizedPeople, array $expected)
    {
        $javeloUser = $this->userFactory->createFromPeople($people, self::JAVELO_ID, $synchronizedPeople);
        $this->assertSame($expected['id'], $javeloUser->id);
        $this->assertSame($expected['givenName'], $javeloUser->givenName);
        $this->assertSame($expected['familyName'], $javeloUser->familyName);
        $this->assertSame($expected['userName'], $javeloUser->userName);
        $this->assertSame($expected['active'], $javeloUser->active);
        $this->assertSame($expected['locale'], $javeloUser->locale);
        $this->assertSame($expected['title'], $javeloUser->title);
        $this->assertSame($expected['externalId'], $javeloUser->externalId);
        $this->assertSame($expected['intranetId'], $javeloUser->intranetId);
        $this->assertSame($expected['department'], $javeloUser->department);
        $this->assertSame($expected['businessUnit'], $javeloUser->businessUnit);
        $this->assertSame($expected['region'], $javeloUser->region);
        $this->assertSame($expected['subdivision'], $javeloUser->subdivision);
        $this->assertSame($expected['division'], $javeloUser->division);
        $this->assertSame($expected['managerUserName'], $javeloUser->managerUserName);
        $this->assertSame($expected['gender'], $javeloUser->gender);
        $this->assertSame($expected['contractType'], $javeloUser->contractType);
        $this->assertSame($expected['lastExitDate'], $javeloUser->getLastExitDate());
        $this->assertSame($expected['position'], $javeloUser->position);
        $this->assertSame($expected['workingTime'], $javeloUser->workingTime);
    }

    public function peopleProvider()
    {
        $commonConfig = $this->getCommonConfig();

        $people = $this->createPeople($commonConfig);
        $expected = $this->createExpected($commonConfig);
        $peopleAndHisSupervisorAreSynchronized = [['id' => 123], ['id' => 456]];
        $peopleOnlySynchronized = [['id' => 123]];
        $supervisorOnlySynchronized = [['id' => 456]];
        $tests = [];

        $tests['active People'] = [$people, $peopleAndHisSupervisorAreSynchronized, $expected];

        $people2 = $this->createPeople($commonConfig);
        $people2->setDisabled(true);
        $expected['active'] = false;
        $expected['managerUserName'] = null;

        $tests['People disabled, we don\'t send his manager'] = [$people2, $peopleAndHisSupervisorAreSynchronized, $expected];

        $people3 = $this->createPeople($commonConfig);
        $people3->getSupervisor()->setDisabled(true);
        $expected = $this->createExpected($commonConfig);
        $expected['managerUserName'] = null;

        $tests['People active, his manager is disabled we don\'t send manager'] = [$people3, $peopleAndHisSupervisorAreSynchronized, $expected];

        $people4 = $this->createPeople($commonConfig);
        $people4->setSupervisor(null);
        $expected = $this->createExpected($commonConfig);
        $expected['managerUserName'] = null;

        $tests['People active, his manager is null we don\'t send manager'] = [$people4, $peopleAndHisSupervisorAreSynchronized, $expected];

        $people5 = $this->createPeople($commonConfig);
        $expected5 = $this->createExpected($commonConfig);
        $expected5['managerUserName'] = null;
        $tests['People active, his manager is not found in repository'] = [$people5, $peopleOnlySynchronized, $expected5];

        $people6 = $this->createPeople($commonConfig);
        $people6->setLocale('Zh-Zn');
        $expected6 = $this->createExpected($commonConfig);
        $expected6['locale'] = 'en';
        $tests['People with lang not in valid locale has locale=en'] = [$people6, $peopleAndHisSupervisorAreSynchronized, $expected6];

        $people7 = $this->createPeople($commonConfig);
        $people7->setLocale('fr');
        $expected7 = $this->createExpected($commonConfig);
        $expected7['locale'] = 'fr';
        $tests['People with lang fr locale has locale=fr'] = [$people7, $peopleAndHisSupervisorAreSynchronized, $expected7];

        $people10 = $this->createPeople($commonConfig);
        $people10->setDisabledAt(null);
        $expected10 = $this->createExpected($commonConfig);
        $expected10['lastExitDate'] = null;
        $tests['People with no lastExitDate'] = [$people10, $peopleAndHisSupervisorAreSynchronized, $expected10];

        $people11 = $this->createPeople($commonConfig);
        $people11->setDisabledAt(new \DateTime('last day'));
        $expected11 = $this->createExpected($commonConfig);
        $expected11['lastExitDate'] = (new \DateTime('last day'))->format('Y-m-d');
        $tests['People with lastExitDate'] = [$people11, $peopleAndHisSupervisorAreSynchronized, $expected11];

        $people12 = $this->createPeople($commonConfig);
        $expected12 = $this->createExpected($commonConfig);
        $expected12['active'] = false;
        $tests['People not in synchronization make Javelo User inactive'] = [$people12, $supervisorOnlySynchronized, $expected12];

        return $tests;
    }

    private function getCommonConfig(): array
    {
        return [
            'id' => 1234,
            'firstname' => 'John',
            'lastname' => 'DOE',
            'username' => 'johndoe',
            'disabled' => false,
            'locale' => 'en',
            'jobTitle' => 'Developer',
            'department' => 'IT',
            'businessUnit' => 'Business Unit Name',
            'region' => 'Region Name',
            'subdivision' => 'Subdivision Name',
            'division' => 'Division Name',
            'gender' => 'male',
            'contractType' => 'extern',
            'lastExitDate' => '2025-12-21',
            'position' => 'Développeur',
            'workingTime' => 75,
            'supervisorUsername' => 'toto@toto',
        ];
    }

    private function createPeople(array $config): People
    {
        $people = new People();
        $people->setFirstname($config['firstname']);
        $people->setLastname($config['lastname']);
        $people->setUsername($config['username']);
        $people->setDisabled($config['disabled']);
        $people->setLocale($config['locale']);
        $people->setJobTitle($config['jobTitle']);
        $people->setGender('male' === $config['gender'] ? 'Mr' : ('Mrs' === $config['gender'] ? 'female' : null));
        $people->setDisabledAt(new \DateTime($config['lastExitDate']));
        $people->setCoefficient($config['workingTime']);

        if ($config['position']) {
            $position = new Position();
            $position->setDescription($config['position']);
            $people->setPosition($position);
        }

        if ($config['contractType']) {
            $contractType = new ContractType();
            $contractType->name = $config['contractType'];
            $people->setContractType($contractType);
        }

        if ($config['department']) {
            $department = new Department();
            $department->setName($config['department']);
            $people->setDepartment($department);
        }

        if ($config['businessUnit']) {
            $subDivision = new SubDivision();
            $subDivision->name = $config['subdivision'];

            $division = new Division();
            $division->name = $config['division'];
            $subDivision->division = $division;

            $region = new Region();
            $region->setName($config['region']);
            $region->setSubDivision($subDivision);

            $businessUnit = new BusinessUnit();
            $businessUnit->setName($config['businessUnit']);
            $businessUnit->setRegion($region);
            $people->setBusinessUnit($businessUnit);
        }

        if ($config['supervisorUsername']) {
            $supervisor = new People();
            $supervisor->setUsername($config['supervisorUsername']);
            $supervisor->setDisabled(false);

            $reflectionClass = new \ReflectionClass(\App\Entity\User::class);
            $property = $reflectionClass->getProperty('id');
            $property->setAccessible(true);
            $property->setValue($supervisor, 456);

            $people->setSupervisor($supervisor);
        }

        $reflectionClass = new \ReflectionClass(\App\Entity\User::class);
        $property = $reflectionClass->getProperty('id');
        $property->setAccessible(true);
        $property->setValue($people, 123);

        return $people;
    }

    private function createExpected(array $config): array
    {
        return [
            'id' => self::JAVELO_ID,
            'givenName' => $config['firstname'],
            'familyName' => $config['lastname'],
            'userName' => $config['username'],
            'active' => !$config['disabled'],
            'locale' => $config['locale'],
            'title' => $config['jobTitle'],
            'externalId' => '123',
            'intranetId' => '123',
            'department' => $config['department'],
            'businessUnit' => $config['businessUnit'],
            'region' => $config['region'],
            'subdivision' => $config['subdivision'],
            'division' => $config['division'],
            'managerUserName' => 'toto@toto',
            'gender' => $config['gender'],
            'contractType' => $config['contractType'],
            'lastExitDate' => $config['lastExitDate'],
            'position' => $config['position'],
            'workingTime' => (string) $config['workingTime'],
        ];
    }
}
