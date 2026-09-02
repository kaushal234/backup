<?php

declare(strict_types=1);

namespace App\Tests\LegacyBundle\Doctrine\ORM\Extension;

use ApiPlatform\Doctrine\Orm\Util\QueryNameGenerator;
use App\Entity\Directory\People;
use App\Entity\Sales\ExtranetUser;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\Query\Expr;
use Doctrine\ORM\QueryBuilder;
use LegacyBundle\Doctrine\ORM\Extension\EquipmentRecordFileQueryExtension;
use LegacyBundle\Entity\EquipmentRecord;
use LegacyBundle\Entity\EquipmentRecordFile;
use LegacyBundle\Security\ExtranetUserCustomerResolver;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use Symfony\Bundle\SecurityBundle\Security;

class EquipmentRecordFileQueryExtensionTest extends TestCase
{
    use ProphecyTrait;

    /** @var ObjectProphecy<Security> */
    private ObjectProphecy $security;

    /** @var ObjectProphecy<Connection> */
    private ObjectProphecy $connection;

    /** @var ObjectProphecy<QueryBuilder> */
    private ObjectProphecy $queryBuilder;

    private QueryNameGenerator $queryNameGenerator;

    private EquipmentRecordFileQueryExtension $extension;

    protected function setUp(): void
    {
        $this->security = $this->prophesize(Security::class);
        // ExtranetUserCustomerResolver is final, so it cannot be doubled: use a real instance backed by a mocked Connection.
        $this->connection = $this->prophesize(Connection::class);
        $this->queryBuilder = $this->prophesize(QueryBuilder::class);
        $this->queryNameGenerator = new QueryNameGenerator();

        $this->extension = new EquipmentRecordFileQueryExtension(
            $this->security->reveal(),
            new ExtranetUserCustomerResolver($this->connection->reveal())
        );
    }

    public function testApplyDoesNothingForOtherResource(): void
    {
        $this->security->getUser()->shouldNotBeCalled();
        $this->extension->applyToCollection($this->queryBuilder->reveal(), $this->queryNameGenerator, \stdClass::class);
    }

    public function testApplyDoesNothingForPeople(): void
    {
        $user = $this->prophesize(People::class);
        $this->security->getUser()->willReturn($user->reveal());

        $this->queryBuilder->getRootAliases()->willReturn(['o']);
        $this->queryBuilder->andWhere(Argument::any())->shouldNotBeCalled();

        $this->extension->applyToCollection($this->queryBuilder->reveal(), $this->queryNameGenerator, EquipmentRecordFile::class);
    }

    public function testApplyBlocksAccessIfNoCustomersFound(): void
    {
        $user = $this->prophesize(ExtranetUser::class);
        $user->getLegacyId()->willReturn(42);
        $this->security->getUser()->willReturn($user->reveal());

        $this->queryBuilder->getRootAliases()->willReturn(['o']);
        $this->connection->fetchFirstColumn(Argument::any(), ['legacyUserId' => 42])->willReturn([]);

        $this->queryBuilder->andWhere('1 = 0')->shouldBeCalled()->willReturn($this->queryBuilder->reveal());

        $this->extension->applyToCollection($this->queryBuilder->reveal(), $this->queryNameGenerator, EquipmentRecordFile::class);
    }

    public function testApplyBlocksAccessForUnhandledUser(): void
    {
        $this->security->getUser()->willReturn(null);

        $this->queryBuilder->getRootAliases()->willReturn(['o']);
        $this->queryBuilder->andWhere('1 = 0')->shouldBeCalled()->willReturn($this->queryBuilder->reveal());

        $this->extension->applyToCollection($this->queryBuilder->reveal(), $this->queryNameGenerator, EquipmentRecordFile::class);
    }

    public function testApplyJoinsEquipmentRecordForExtranetUser(): void
    {
        $user = $this->prophesize(ExtranetUser::class);
        $user->getLegacyId()->willReturn(42);
        $this->security->getUser()->willReturn($user->reveal());

        $this->queryBuilder->getRootAliases()->willReturn(['o']);
        $this->connection->fetchFirstColumn(Argument::any(), ['legacyUserId' => 42])->willReturn([100, 101]);

        $this->queryBuilder->innerJoin(
            EquipmentRecord::class,
            Argument::type('string'),
            'WITH',
            Argument::containingString('o.parentId =')
        )->shouldBeCalled()->willReturn($this->queryBuilder->reveal());

        $this->queryBuilder->expr()->willReturn(new Expr());
        $this->queryBuilder->andWhere(Argument::any())->shouldBeCalled()->willReturn($this->queryBuilder->reveal());
        $this->queryBuilder->setParameter('customerLegacyIds', [100, 101])->shouldBeCalled()->willReturn($this->queryBuilder->reveal());

        $this->extension->applyToCollection($this->queryBuilder->reveal(), $this->queryNameGenerator, EquipmentRecordFile::class);
    }
}
