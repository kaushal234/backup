<?php

declare(strict_types=1);

namespace App\Tests\EventListener\Directory;

use App\Entity\Directory\Department;
use App\Entity\Directory\People;
use App\EventListener\Directory\PeopleListener;
use App\Manager\Directory\PeopleManager;
use Prophecy\PhpUnit\ProphecyTrait;
use Psr\Container\ContainerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;

class PeopleListenerTest extends KernelTestCase
{
    use ProphecyTrait;

    protected function setUp(): void
    {
        self::bootKernel();
    }

    public function testOnMisPeopleCreate()
    {
        $department = new Department();
        $department->setName('Management of Information System');
        $people = new People();
        $people->setDepartment($department);
        $request = new Request();
        $request->setMethod(Request::METHOD_POST);
        $event = new ViewEvent(static::$kernel, $request, HttpKernelInterface::MAIN_REQUEST, $people);

        $peopleManagerProphecy = $this->prophesize(PeopleManager::class);
        $peopleManagerProphecy->createTaskForMISUserAndNotify($people)->shouldBeCalledOnce();
        $containerInterfaceProphecy = $this->prophesize(ContainerInterface::class);
        $containerInterfaceProphecy->get(PeopleManager::class)->shouldBeCalledOnce()->willReturn($peopleManagerProphecy->reveal());

        $listener = new PeopleListener($containerInterfaceProphecy->reveal());
        $listener->onMisPeopleCreate($event);
    }

    /** @dataProvider dataProvider */
    public function testNoMisPeopleCreate($object, $method, $department, $name)
    {
        $people = new $object();
        $request = new Request();
        $request->setMethod($method);

        if ($department) {
            $departmentEntity = new Department();
            $departmentEntity->setName($name);
            if ($people instanceof People) {
                $people->setDepartment($departmentEntity);
            }
        }

        $event = new ViewEvent(static::$kernel, $request, HttpKernelInterface::MAIN_REQUEST, $people);

        $peopleManagerProphecy = $this->prophesize(PeopleManager::class);
        $peopleManagerProphecy->createTaskForMISUserAndNotify($people)->shouldNotBeCalled();
        $containerInterfaceProphecy = $this->prophesize(ContainerInterface::class);
        $containerInterfaceProphecy->get(PeopleManager::class)->shouldNotBeCalled();

        $listener = new PeopleListener($containerInterfaceProphecy->reveal());
        $listener->onMisPeopleCreate($event);
    }

    public function dataProvider()
    {
        yield 'test with not people as object' => [\stdClass::class, 'POST', true, 'Management of Information System'];
        yield 'test with not post as method' => [People::class, 'PUT', true, 'Management of Information System'];
        yield 'test with not department' => [People::class, 'POST', false, ''];
        yield 'test with department but no MIS' => [People::class, 'POST', true, 'Cantine'];
    }
}
