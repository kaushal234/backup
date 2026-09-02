<?php

declare(strict_types=1);

namespace App\Tests\Doctrine\ORM\Extension;

use ApiPlatform\Doctrine\Orm\Util\QueryNameGenerator;
use ApiPlatform\Metadata\GetCollection;
use App\Doctrine\ORM\Extension\EquipmentRecordExtension;
use App\Entity\EquipmentRecord;
use App\Entity\EquipmentRecordStatus;
use App\Entity\Sales\ExtranetUser;
use Doctrine\ORM\QueryBuilder;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Psr\Container\ContainerInterface;
use Symfony\Bundle\SecurityBundle\Security;

class EquipmentRecordExtensionTest extends TestCase
{
    use ProphecyTrait;

    public function testEquipmentRecordCollectionAreFiltered()
    {
        $extranetUserProphecy = $this->prophesize(ExtranetUser::class);
        $extranetuser = $extranetUserProphecy->reveal();

        $queryBuilderMock = $this->createMock(QueryBuilder::class);

        $securityProphecy = $this->prophesize(Security::class);
        $securityProphecy->getUser()->shouldBeCalledTimes(1)->willReturn($extranetuser);

        $containerProphecy = $this->prophesize(ContainerInterface::class);
        $containerProphecy->get(Security::class)->shouldBeCalledTimes(1)->willReturn($securityProphecy->reveal());

        $extranetUserExtension = new EquipmentRecordExtension($containerProphecy->reveal());
        $extranetUserExtension->applyToCollection($queryBuilderMock, new QueryNameGenerator(), EquipmentRecord::class, new GetCollection());
    }

    public function testEquipmentRecordByEndUserCollectionAreFiltered()
    {
        $extranetUserProphecy = $this->prophesize(ExtranetUser::class);
        $extranetuser = $extranetUserProphecy->reveal();

        $queryBuilderMock = $this->createMock(QueryBuilder::class);

        $queryBuilderMock->expects($this->exactly(3))->method('innerJoin')->withConsecutive(
            ['o.endUser', 'endUser_a1'],
            ['endUser_a1.crt', 'crt_a2'],
            ['crt_a2.acls', 'acls_a3'],
        )->willReturn($queryBuilderMock);
        // andWhere is called twice: once for the extranet user ACL filter, once for the
        // "visible to extranet" filter (ship/commissioning/actual delivery date not null).
        $queryBuilderMock->expects($this->exactly(2))->method('andWhere')->withConsecutive(
            ['acls_a3.extranetUser = :current_extranet_user'],
            [$this->anything()],
        )->willReturn($queryBuilderMock);
        $queryBuilderMock->expects($this->exactly(2))->method('setParameter')->withConsecutive(
            ['current_extranet_user', $extranetuser],
            ['inProductionStatus', EquipmentRecordStatus::IN_PRODUCTION->value],
        )->willReturn($queryBuilderMock);

        $securityProphecy = $this->prophesize(Security::class);
        $securityProphecy->getUser()->shouldBeCalledTimes(1)->willReturn($extranetuser);

        $containerProphecy = $this->prophesize(ContainerInterface::class);
        $containerProphecy->get(Security::class)->shouldBeCalledTimes(1)->willReturn($securityProphecy->reveal());
        // getRootAliases is called twice: in addExtranetUserFilterOnEndUser and in addVisibleToExtranetFilter.
        $queryBuilderMock->expects($this->exactly(2))->method('getRootAliases')->willReturn(['o']);

        $extranetUserExtension = new EquipmentRecordExtension($containerProphecy->reveal());
        $extranetUserExtension->applyToCollection($queryBuilderMock, new QueryNameGenerator(), EquipmentRecord::class, new GetCollection(name: 'get_by_enduser'));
    }
}
