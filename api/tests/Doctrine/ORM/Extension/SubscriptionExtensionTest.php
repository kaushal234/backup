<?php

declare(strict_types=1);

namespace App\Tests\Doctrine\ORM\Extension;

use ApiPlatform\Doctrine\Orm\Util\QueryNameGenerator;
use ApiPlatform\Metadata\GetCollection;
use App\Doctrine\ORM\Extension\SubscriptionExtension;
use App\Entity\Common\Subscription;
use App\Entity\Sales\ExtranetUser;
use Doctrine\ORM\QueryBuilder;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Psr\Container\ContainerInterface;
use Symfony\Bundle\SecurityBundle\Security;

class SubscriptionExtensionTest extends TestCase
{
    use ProphecyTrait;

    public function testSubscriptionCollectionsAreFiltered()
    {
        $extranetUser = new ExtranetUser();

        $queryBuilderMock = $this->createMock(QueryBuilder::class);
        $queryBuilderMock->expects($this->once())->method('andWhere')->with('o.user = :current_extranet_user')->willReturn($queryBuilderMock);
        $queryBuilderMock->expects($this->once())->method('setParameter')->with('current_extranet_user', $extranetUser)->willReturn($queryBuilderMock);

        $securityProphecy = $this->prophesize(Security::class);
        $securityProphecy->getUser()->shouldBeCalledTimes(1)->willReturn($extranetUser);

        $containerProphecy = $this->prophesize(ContainerInterface::class);
        $containerProphecy->get(Security::class)->shouldBeCalledTimes(1)->willReturn($securityProphecy->reveal());

        $queryBuilderMock->expects($this->once())->method('getRootAliases')->willReturn(['o']);

        $extranetUserExtension = new SubscriptionExtension($containerProphecy->reveal());
        $extranetUserExtension->applyToCollection($queryBuilderMock, new QueryNameGenerator(), Subscription::class, new GetCollection());
    }
}
