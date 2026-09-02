<?php

declare(strict_types=1);

namespace App\Tests\Doctrine\ORM\Extension;

use ApiPlatform\Doctrine\Orm\Util\QueryNameGenerator;
use ApiPlatform\Metadata\GetCollection;
use App\Doctrine\ORM\Extension\ExtranetUserFavoriteExtension;
use App\Entity\Sales\ExtranetUser;
use App\Entity\Sales\ExtranetUserFavorite;
use Doctrine\ORM\QueryBuilder;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Psr\Container\ContainerInterface;
use Symfony\Bundle\SecurityBundle\Security;

class ExtranetUserFavoriteExtensionTest extends TestCase
{
    use ProphecyTrait;

    public function testClassesWithExtranetUsersPropertyCollectionAreFiltered()
    {
        $extranetUserProphecy = $this->prophesize(ExtranetUser::class);
        $extranetuser = $extranetUserProphecy->reveal();

        $queryBuilderMock = $this->createMock(QueryBuilder::class);
        $queryBuilderMock->expects($this->once())->method('andWhere')->with('o.extranetUser = :current_extranet_user')->willReturn($queryBuilderMock);
        $queryBuilderMock->expects($this->once())->method('setParameter')->with('current_extranet_user', $extranetuser)->willReturn($queryBuilderMock);

        $securityProphecy = $this->prophesize(Security::class);
        $securityProphecy->getUser()->shouldBeCalledTimes(1)->willReturn($extranetuser);

        $containerProphecy = $this->prophesize(ContainerInterface::class);
        $containerProphecy->get(Security::class)->shouldBeCalledTimes(1)->willReturn($securityProphecy->reveal());

        $queryBuilderMock->expects($this->once())->method('getRootAliases')->willReturn(['o']);

        $extranetUserExtension = new ExtranetUserFavoriteExtension($containerProphecy->reveal());
        $extranetUserExtension->applyToCollection($queryBuilderMock, new QueryNameGenerator(), ExtranetUserFavorite::class, new GetCollection());
    }
}
