<?php

declare(strict_types=1);

namespace App\Tests\EventListener\Directory;

use ApiPlatform\Metadata\IriConverterInterface;
use App\Entity\Directory\Division;
use App\Entity\Directory\DivisionGroup;
use App\Entity\Directory\Position;
use App\Entity\Group;
use App\EventListener\Directory\PositionListener;
use App\Repository\Directory\PositionRepository;
use Doctrine\ORM\EntityManagerInterface;
use Prophecy\PhpUnit\ProphecyTrait;
use Psr\Container\ContainerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;

class PositionListenerTest extends KernelTestCase
{
    use ProphecyTrait;

    protected function setUp(): void
    {
        self::bootKernel();
    }

    public function testOnGroupPositionUpdatePreWrite()
    {
        $serviceLocatorProphecy = $this->prophesize(ContainerInterface::class);
        $positionRepositoryMock = $this->getMockBuilder(PositionRepository::class)->disableOriginalConstructor()->onlyMethods(['getPositionGroupsForDivision'])->getMock();
        $IriConverterProphecy = $this->prophesize(IriConverterInterface::class);
        $entityManagerProphecy = $this->prophesize(EntityManagerInterface::class);

        $groupPatron = (new Group())->setName('SUPERPATRON');
        $groupNew = (new Group())->setName('NEW');
        $groupDeleted = (new Group())->setName('DELETED');

        $division = new Division();
        $division->name = 'ALVEST';

        $divisionGroup = new DivisionGroup();
        $divisionGroup->division = $division;
        $divisionGroup->addGroup($groupPatron)->addGroup($groupNew);

        $reflectionProperty = (new \ReflectionClass(new Group()))->getProperty('id');
        $reflectionProperty->setAccessible(true);
        $reflectionProperty->setValue($groupPatron, 1);
        $reflectionProperty->setValue($groupNew, 2);
        $reflectionProperty->setValue($groupDeleted, 3);

        $position = (new Position())
            ->setCode('AK')
            ->setDescription('RUSSIAN WEAPON')
            ->addDivisionGroup($divisionGroup)
        ;

        $listener = new PositionListener($serviceLocatorProphecy->reveal());

        $request = new Request();
        $request->setMethod(Request::METHOD_PUT);

        $serviceLocatorProphecy->get(EntityManagerInterface::class)->shouldBeCalledTimes(1)->willReturn($entityManagerProphecy->reveal());
        $serviceLocatorProphecy->get(IriConverterInterface::class)->shouldBeCalledTimes(1)->willReturn($IriConverterProphecy->reveal());

        $entityManagerProphecy->getRepository(Position::class)->shouldBeCalledOnce()->willReturn($positionRepositoryMock);

        $positionRepositoryMock->expects($this->once())->method('getPositionGroupsForDivision')->with($position, $division)->willReturn([['id' => '1'], ['id' => '3']]);

        $IriConverterProphecy->getIriFromResource($position)->shouldBeCalledTimes(1)->willReturn('position/1');
        $IriConverterProphecy->getIriFromResource($division)->shouldBeCalledTimes(1)->willReturn('divisions/2');

        $listener->onGroupPositionUpdatePreWrite(new ViewEvent(static::$kernel, $request, HttpKernelInterface::MAIN_REQUEST, $position));
        self::assertSame(['1' => '2'], $listener->messages->first()->getGroupToAdd());
        self::assertSame(['1' => '3'], $listener->messages->first()->getGroupToDelete());
        self::assertCount(1, $listener->messages);
    }
}
