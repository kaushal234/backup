<?php

declare(strict_types=1);

namespace App\Tests\Doctrine\ORM\Extension;

use ApiPlatform\Doctrine\Orm\Util\QueryNameGenerator;
use ApiPlatform\Metadata\GetCollection;
use App\Doctrine\ORM\Extension\ExtranetUserExtension;
use App\Entity\Directory\People;
use App\Entity\Sales\ExtranetUser;
use Doctrine\ORM\QueryBuilder;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Psr\Container\ContainerInterface;
use Symfony\Bundle\SecurityBundle\Security;

class ExtranetUserExtensionTest extends TestCase
{
    use ProphecyTrait;

    public function testFilterAsNoEffectWithPeople()
    {
        $queryBuilderProphecy = $this->prophesize(QueryBuilder::class);
        $queryBuilderProphecy->getRootAliases()->shouldNotBeCalled();

        $securityProphecy = $this->prophesize(Security::class);
        $securityProphecy->getUser()->shouldBeCalledTimes(1)->willReturn(new People());

        $containerProphecy = $this->prophesize(ContainerInterface::class);
        $containerProphecy->get(Security::class)->shouldBeCalledTimes(1)->willReturn($securityProphecy->reveal());

        $extranetUserExtension = new ExtranetUserExtension($containerProphecy->reveal());
        $extranetUserExtension->applyToCollection($queryBuilderProphecy->reveal(), new QueryNameGenerator(), ExtranetUser::class, new GetCollection());
    }

    public function testExtranetUsersCollectionAreFiltered()
    {
        $extranetUserProphecy = $this->prophesize(ExtranetUser::class);
        $extranetUserProphecy->getId()->shouldBeCalledTimes(1)->willReturn(42);

        $securityProphecy = $this->prophesize(Security::class);
        $securityProphecy->getUser()->shouldBeCalledTimes(1)->willReturn($extranetUserProphecy->reveal());

        $containerProphecy = $this->prophesize(ContainerInterface::class);
        $containerProphecy->get(Security::class)->shouldBeCalledTimes(1)->willReturn($securityProphecy->reveal());

        $queryBuilderMock = $this->createMock(QueryBuilder::class);
        $queryBuilderMock->expects($this->once())->method('getRootAliases')->willReturn(['o']);
        $queryBuilderMock->expects($this->once())->method('andWhere')->with('o.id = :current_extranet_user')->willReturn($queryBuilderMock);
        $queryBuilderMock->expects($this->once())->method('setParameter')->with('current_extranet_user', 42)->willReturn($queryBuilderMock);

        $extranetUserExtension = new ExtranetUserExtension($containerProphecy->reveal());
        $extranetUserExtension->applyToCollection($queryBuilderMock, new QueryNameGenerator(), ExtranetUser::class, new GetCollection());
    }
}
