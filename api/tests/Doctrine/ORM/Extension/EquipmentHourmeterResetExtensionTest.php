<?php

declare(strict_types=1);

namespace App\Tests\Doctrine\ORM\Extension;

use ApiPlatform\Doctrine\Orm\Util\QueryNameGenerator;
use ApiPlatform\Metadata\GetCollection;
use App\Doctrine\ORM\Extension\EquipmentHourmeterResetExtension;
use App\Entity\Sales\ExtranetUser;
use App\Entity\Support\EquipmentHourmeterReset;
use Doctrine\ORM\QueryBuilder;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Psr\Container\ContainerInterface;
use Symfony\Bundle\SecurityBundle\Security;

class EquipmentHourmeterResetExtensionTest extends TestCase
{
    use ProphecyTrait;

    public function testClassesWithEquipmentRecordPropertyCollectionAreFiltered()
    {
        $extranetUserProphecy = $this->prophesize(ExtranetUser::class);
        $extranetuser = $extranetUserProphecy->reveal();

        $queryBuilderMock = $this->createMock(QueryBuilder::class);
        $queryBuilderMock->expects($this->once())->method('innerJoin')->with('o.equipmentRecord', 'equipmentRecord_a1')->willReturn($queryBuilderMock);
        $queryBuilderMock->expects($this->exactly(3))->method('leftJoin')->withConsecutive(
            ['equipmentRecord_a1.contracts', 'contracts_a2'],
            ['contracts_a2.endUserRepresentatives', 'endUserRepresentatives_a3'],
            ['contracts_a2.buyerRepresentatives', 'buyerRepresentatives_a4'],
        )->willReturn($queryBuilderMock);
        $queryBuilderMock->expects($this->once())->method('andWhere')->with('((o.createdBy = :current_extranet_user AND (contracts_a2.id IS NULL OR :today < contracts_a2.startDate OR  contracts_a2.expirationDate < :today)) OR ((endUserRepresentatives_a3.id = :current_extranet_user OR buyerRepresentatives_a4.id = :current_extranet_user) AND contracts_a2.startDate <= :today AND :today <= contracts_a2.expirationDate))')->willReturn($queryBuilderMock);

        $queryBuilderMock->expects($this->exactly(2))->method('setParameter')->withConsecutive(
            ['current_extranet_user', $extranetuser],
            ['today', $this->callback(static fn ($date) => $date instanceof \DateTime)],
        )->willReturn($queryBuilderMock);

        $securityProphecy = $this->prophesize(Security::class);
        $securityProphecy->getUser()->shouldBeCalledTimes(1)->willReturn($extranetuser);

        $containerProphecy = $this->prophesize(ContainerInterface::class);
        $containerProphecy->get(Security::class)->shouldBeCalledTimes(1)->willReturn($securityProphecy->reveal());

        $queryBuilderMock->expects($this->once())->method('getRootAliases')->willReturn(['o']);

        $extranetUserExtension = new EquipmentHourmeterResetExtension($containerProphecy->reveal());
        $extranetUserExtension->applyToCollection($queryBuilderMock, new QueryNameGenerator(), EquipmentHourmeterReset::class, new GetCollection());
    }
}
