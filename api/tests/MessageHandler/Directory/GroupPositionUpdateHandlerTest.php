<?php

declare(strict_types=1);

namespace App\Tests\MessageHandler\Directory;

use ApiPlatform\Metadata\IriConverterInterface;
use App\Entity\Acl;
use App\Entity\Directory\BusinessUnit;
use App\Entity\Directory\Division;
use App\Entity\Directory\Location;
use App\Entity\Directory\People;
use App\Entity\Directory\Position;
use App\Entity\Group;
use App\Message\Directory\GroupPositionUpdate;
use App\MessageHandler\Directory\GroupPositionUpdateHandler;
use App\Repository\Directory\PeopleRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Prophecy\PhpUnit\ProphecyTrait;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class GroupPositionUpdateHandlerTest extends KernelTestCase
{
    use ProphecyTrait;

    public function testHandler(): void
    {
        $iriConverterProphecy = $this->prophesize(IriConverterInterface::class);
        $entityManagerProphecy = $this->prophesize(EntityManagerInterface::class);

        $peopleRepositoryMock = $this->getMockBuilder(PeopleRepository::class)->disableOriginalConstructor()->onlyMethods(['findByPositionByDivision'])->getMock();
        $groupRepositoryMock = $this->createMock(EntityRepository::class);

        $position = (new Position())
            ->setCode('AK')
            ->setDescription('RUSSIAN WEAPON')
        ;

        $location = new Location();
        $division = new Division();
        $group = (new Group())->setName('USCULE');
        $newGroup = (new Group())->setName('PEUMENT');
        $acl = (new Acl())->setGroup($group)->setLocation($location);
        $people = (new People())
            ->setBusinessUnit((new BusinessUnit())->setLocation($location))
            ->addAcl($acl)
        ;

        $newAcl = (new Acl())
            ->setGroup($newGroup)
            ->setLocation($location)
            ->setUser($people)
        ;

        $reflectionProperty = (new \ReflectionClass(new Position()))->getProperty('id');
        $reflectionProperty->setAccessible(true);
        $reflectionProperty->setValue($position, 1);

        $reflectionProperty = (new \ReflectionClass(new Group()))->getProperty('id');
        $reflectionProperty->setAccessible(true);
        $reflectionProperty->setValue($group, 2);

        $reflectionProperty = (new \ReflectionClass(new Location()))->getProperty('id');
        $reflectionProperty->setAccessible(true);
        $reflectionProperty->setValue($location, 3);

        $messageProphecy = $this->prophesize(GroupPositionUpdate::class);
        $messageProphecy->getPosition()->shouldBeCalledTimes(1)->willReturn('/position/1');
        $messageProphecy->getDivision()->shouldBeCalledTimes(1)->willReturn('/division/1');
        $messageProphecy->getGroupToDelete()->shouldBeCalledTimes(1)->willReturn(['2']);
        $messageProphecy->getGroupToAdd()->shouldBeCalledTimes(1)->willReturn(['3']);

        $entityManagerProphecy->getRepository(Group::class)->shouldBeCalledTimes(1)->willReturn($groupRepositoryMock);
        $entityManagerProphecy->getRepository(People::class)->shouldBeCalledTimes(1)->willReturn($peopleRepositoryMock);
        $iriConverterProphecy->getResourceFromIri('/position/1')->shouldBeCalledTimes(1)->willReturn($position);
        $iriConverterProphecy->getResourceFromIri('/division/1')->shouldBeCalledTimes(1)->willReturn($division);
        $peopleRepositoryMock->expects($this->once())->method('findByPositionByDivision')->with($position, $division)->willReturn([$people]);
        $groupRepositoryMock->expects($this->once())->method('find')->with('3')->willReturn($newGroup);

        $entityManagerProphecy->remove($acl)->shouldBeCalledTimes(1);
        $entityManagerProphecy->persist($newAcl)->shouldBeCalledTimes(1);
        $entityManagerProphecy->flush()->shouldBeCalledTimes(1);

        $handler = new GroupPositionUpdateHandler($iriConverterProphecy->reveal(), $entityManagerProphecy->reveal());
        $handler($messageProphecy->reveal());
    }
}
