<?php

declare(strict_types=1);

namespace App\Tests\Doctrine\ORM\Extension;

use ApiPlatform\Doctrine\Orm\Util\QueryNameGenerator;
use ApiPlatform\Metadata\GetCollection;
use App\Doctrine\ORM\Extension\EquipmentSerialExtension;
use App\Entity\Sales\ExtranetUser;
use App\Entity\Sales\ExtranetUserAcl;
use App\Entity\Support\EquipmentSerial;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query\Expr;
use Doctrine\ORM\QueryBuilder;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Psr\Container\ContainerInterface;
use Symfony\Bundle\SecurityBundle\Security;

class EquipmentSerialExtensionTest extends TestCase
{
    use ProphecyTrait;

    public function testEquipmentSerialCollectionIsFilteredForExtranetUser(): void
    {
        $extranetUserProphecy = $this->prophesize(ExtranetUser::class);
        $extranetUser = $extranetUserProphecy->reveal();

        // Subquery for buyer
        $subQbBuyer = $this->createMock(QueryBuilder::class);
        $subQbBuyer->method('select')->willReturnSelf();
        $subQbBuyer->method('from')->with(ExtranetUserAcl::class, 'aclBuyer')->willReturnSelf();
        $subQbBuyer->method('innerJoin')->willReturnSelf();
        $subQbBuyer->method('where')->willReturnSelf();
        $subQbBuyer->method('andWhere')->willReturnSelf();
        $subQbBuyer->method('getDQL')->willReturn('buyer_dql');

        // Subquery for maintainer
        $subQbMaintainer = $this->createMock(QueryBuilder::class);
        $subQbMaintainer->method('select')->willReturnSelf();
        $subQbMaintainer->method('from')->with(ExtranetUserAcl::class, 'aclMaintainer')->willReturnSelf();
        $subQbMaintainer->method('innerJoin')->willReturnSelf();
        $subQbMaintainer->method('where')->willReturnSelf();
        $subQbMaintainer->method('andWhere')->willReturnSelf();
        $subQbMaintainer->method('getDQL')->willReturn('maintainer_dql');

        // Subquery for end user
        $subQbEndUser = $this->createMock(QueryBuilder::class);
        $subQbEndUser->method('select')->willReturnSelf();
        $subQbEndUser->method('from')->with(ExtranetUserAcl::class, 'aclEndUser')->willReturnSelf();
        $subQbEndUser->method('innerJoin')->willReturnSelf();
        $subQbEndUser->method('where')->willReturnSelf();
        $subQbEndUser->method('andWhere')->willReturnSelf();
        $subQbEndUser->method('getDQL')->willReturn('enduser_dql');

        $entityManagerMock = $this->createMock(EntityManagerInterface::class);
        $entityManagerMock
            ->expects($this->exactly(3))
            ->method('createQueryBuilder')
            ->willReturnOnConsecutiveCalls(
                $subQbBuyer,
                $subQbMaintainer,
                $subQbEndUser
            );

        $expr = new Expr();

        $queryBuilderMock = $this->createMock(QueryBuilder::class);

        $queryBuilderMock
            ->expects($this->once())
            ->method('getRootAliases')
            ->willReturn(['o']);

        $queryBuilderMock
            ->method('expr')
            ->willReturn($expr);

        $queryBuilderMock
            ->method('getEntityManager')
            ->willReturn($entityManagerMock);

        $queryBuilderMock
            ->expects($this->exactly(4))
            ->method('leftJoin')
            ->willReturnSelf();

        $queryBuilderMock
            ->expects($this->atLeastOnce())
            ->method('andWhere')
            ->willReturnSelf();

        $queryBuilderMock
            ->expects($this->once())
            ->method('setParameter')
            ->with('current_extranet_user', $extranetUser)
            ->willReturnSelf();

        $securityProphecy = $this->prophesize(Security::class);
        $securityProphecy->getUser()->shouldBeCalledTimes(1)->willReturn($extranetUser);

        $containerProphecy = $this->prophesize(ContainerInterface::class);
        $containerProphecy->get(Security::class)->shouldBeCalledTimes(1)->willReturn($securityProphecy->reveal());

        $extension = new EquipmentSerialExtension($containerProphecy->reveal());

        $extension->applyToCollection(
            $queryBuilderMock,
            new QueryNameGenerator(),
            EquipmentSerial::class,
            new GetCollection()
        );
    }

    public function testEquipmentSerialCollectionIsNotFilteredForNonExtranetUser(): void
    {
        $queryBuilderMock = $this->createMock(QueryBuilder::class);

        $queryBuilderMock->expects($this->never())->method('leftJoin');
        $queryBuilderMock->expects($this->never())->method('andWhere');
        $queryBuilderMock->expects($this->never())->method('setParameter');

        $securityProphecy = $this->prophesize(Security::class);
        $securityProphecy->getUser()->shouldBeCalledTimes(1)->willReturn(null);

        $containerProphecy = $this->prophesize(ContainerInterface::class);
        $containerProphecy->get(Security::class)->shouldBeCalledTimes(1)->willReturn($securityProphecy->reveal());

        $extension = new EquipmentSerialExtension($containerProphecy->reveal());

        $extension->applyToCollection(
            $queryBuilderMock,
            new QueryNameGenerator(),
            EquipmentSerial::class,
            new GetCollection()
        );
    }
}
