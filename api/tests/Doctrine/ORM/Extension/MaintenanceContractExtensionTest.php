<?php

declare(strict_types=1);

namespace App\Tests\Doctrine\ORM\Extension;

use ApiPlatform\Doctrine\Orm\Util\QueryNameGenerator;
use ApiPlatform\Metadata\GetCollection;
use App\Doctrine\ORM\Extension\MaintenanceContractExtension;
use App\Entity\Sales\ExtranetUser;
use App\Entity\Support\MaintenanceContract;
use Doctrine\ORM\QueryBuilder;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Psr\Container\ContainerInterface;
use Symfony\Bundle\SecurityBundle\Security;

class MaintenanceContractExtensionTest extends TestCase
{
    use ProphecyTrait;

    public function testLeasingContractCollectionAreFiltered()
    {
        $extranetUserProphecy = $this->prophesize(ExtranetUser::class);
        $extranetUserProphecy->getId()->shouldBeCalledTimes(1)->willReturn(42);
        $extranetuser = $extranetUserProphecy->reveal();

        $queryBuilderMock = $this->createMock(QueryBuilder::class);
        $queryBuilderMock->expects($this->exactly(2))->method('leftJoin')->withConsecutive(
            ['o.endUserRepresentatives', 'endUserRepresentatives_a1'],
            ['o.buyerRepresentatives', 'buyerRepresentatives_a2'],
        )->willReturn($queryBuilderMock);
        $queryBuilderMock->expects($this->once())->method('andWhere')->with('endUserRepresentatives_a1.id = :current_extranet_user OR buyerRepresentatives_a2.id = :current_extranet_user')->willReturn($queryBuilderMock);
        $queryBuilderMock->expects($this->once())->method('setParameter')->with('current_extranet_user', 42)->willReturn($queryBuilderMock);

        $securityProphecy = $this->prophesize(Security::class);
        $securityProphecy->getUser()->shouldBeCalledTimes(1)->willReturn($extranetuser);

        $containerProphecy = $this->prophesize(ContainerInterface::class);
        $containerProphecy->get(Security::class)->shouldBeCalledTimes(1)->willReturn($securityProphecy->reveal());
        $queryBuilderMock->expects($this->once())->method('getRootAliases')->willReturn(['o']);

        $extranetUserExtension = new MaintenanceContractExtension($containerProphecy->reveal());
        $extranetUserExtension->applyToCollection($queryBuilderMock, new QueryNameGenerator(), MaintenanceContract::class, new GetCollection());
    }
}
