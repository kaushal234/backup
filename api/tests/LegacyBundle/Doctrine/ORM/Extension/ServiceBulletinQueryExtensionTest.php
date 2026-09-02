<?php

declare(strict_types=1);

namespace App\Tests\LegacyBundle\Doctrine\ORM\Extension;

use ApiPlatform\Doctrine\Orm\Util\QueryNameGenerator;
use App\Entity\Directory\People;
use App\Entity\Sales\ExtranetUser;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query\Expr;
use Doctrine\ORM\QueryBuilder;
use LegacyBundle\Doctrine\ORM\Extension\ServiceBulletinQueryExtension;
use LegacyBundle\Entity\ServiceBulletin;
use LegacyBundle\Entity\ServiceBulletinFile;
use LegacyBundle\Entity\ServiceBulletinLine;
use LegacyBundle\Security\ExtranetUserCustomerResolver;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use Symfony\Bundle\SecurityBundle\Security;

class ServiceBulletinQueryExtensionTest extends TestCase
{
    use ProphecyTrait;

    /** @var ObjectProphecy<Security> */
    private ObjectProphecy $security;

    /** @var ObjectProphecy<Connection> */
    private ObjectProphecy $connection;

    /** @var ObjectProphecy<QueryBuilder> */
    private ObjectProphecy $queryBuilder;

    private QueryNameGenerator $queryNameGenerator;

    private ServiceBulletinQueryExtension $extension;

    protected function setUp(): void
    {
        $this->security = $this->prophesize(Security::class);
        // ExtranetUserCustomerResolver is final, so it cannot be doubled: use a real instance backed by a mocked Connection.
        $this->connection = $this->prophesize(Connection::class);
        $this->queryBuilder = $this->prophesize(QueryBuilder::class);
        $this->queryNameGenerator = new QueryNameGenerator();

        $this->extension = new ServiceBulletinQueryExtension(
            $this->security->reveal(),
            new ExtranetUserCustomerResolver($this->connection->reveal())
        );
    }

    public function testApplyDoesNothingWithoutAttribute(): void
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

        $this->extension->applyToCollection($this->queryBuilder->reveal(), $this->queryNameGenerator, ServiceBulletin::class);
    }

    public function testApplyBlocksAccessIfNoCustomersFound(): void
    {
        $user = $this->prophesize(ExtranetUser::class);
        $user->getLegacyId()->willReturn(42);
        $this->security->getUser()->willReturn($user->reveal());

        $this->queryBuilder->getRootAliases()->willReturn(['o']);
        $this->connection->fetchFirstColumn(Argument::any(), ['legacyUserId' => 42])->willReturn([]);

        $this->queryBuilder->andWhere('1 = 0')->shouldBeCalled()->willReturn($this->queryBuilder->reveal());

        $this->extension->applyToCollection($this->queryBuilder->reveal(), $this->queryNameGenerator, ServiceBulletin::class);
    }

    public function testApplyFiltersForExtranetUserOnServiceBulletin(): void
    {
        $user = $this->prophesize(ExtranetUser::class);
        $user->getLegacyId()->willReturn(42);
        $this->security->getUser()->willReturn($user->reveal());

        $this->queryBuilder->getRootAliases()->willReturn(['o']);
        $this->connection->fetchFirstColumn(Argument::any(), ['legacyUserId' => 42])->willReturn([100, 101]);

        $em = $this->prophesize(EntityManagerInterface::class);
        $subQb = $this->prophesize(QueryBuilder::class);
        $subQbReveal = $subQb->reveal();

        $expr = new Expr();

        $this->queryBuilder->getEntityManager()->willReturn($em->reveal());
        $em->createQueryBuilder()->willReturn($subQbReveal);

        $subQb->select(Argument::cetera())->willReturn($subQbReveal);
        $subQb->from(Argument::cetera())->willReturn($subQbReveal);
        $subQb->innerJoin(Argument::cetera())->willReturn($subQbReveal);
        $subQb->where(Argument::cetera())->willReturn($subQbReveal);
        $subQb->andWhere(Argument::cetera())->willReturn($subQbReveal);

        $subQb->getDQL()->willReturn('SELECT 1 FROM service_bulletin_line');

        $this->queryBuilder->expr()->willReturn($expr);

        $this->queryBuilder->andWhere(Argument::any())->shouldBeCalled()->willReturn($this->queryBuilder->reveal());
        $this->queryBuilder->setParameter('customerLegacyIds', [100, 101])->shouldBeCalled()->willReturn($this->queryBuilder->reveal());
        $this->queryBuilder->setParameter('sbConfidential', 'N')->shouldBeCalled()->willReturn($this->queryBuilder->reveal());
        $this->queryBuilder->setParameter('sbVisibleStatuses', ['PARTIAL_IMPLEMENTATION', 'IMPLEMENTATION', 'CLOSED'])->shouldBeCalled()->willReturn($this->queryBuilder->reveal());
        $this->queryBuilder->setParameter('visibleLineStatuses', ServiceBulletinLine::VISIBLE_ON_EXTRANET_STATUSES)->shouldBeCalled()->willReturn($this->queryBuilder->reveal());

        $this->extension->applyToCollection(
            $this->queryBuilder->reveal(),
            $this->queryNameGenerator,
            ServiceBulletin::class
        );
    }

    public function testApplyJoinsParentForServiceBulletinFile(): void
    {
        $user = $this->prophesize(ExtranetUser::class);
        $user->getLegacyId()->willReturn(42);
        $this->security->getUser()->willReturn($user->reveal());

        $this->queryBuilder->getRootAliases()->willReturn(['o']);
        $this->connection->fetchFirstColumn(Argument::any(), ['legacyUserId' => 42])->willReturn([100]);

        $this->queryBuilder->innerJoin(
            ServiceBulletin::class,
            Argument::type('string'),
            'WITH',
            Argument::containingString('o.parentId =')
        )->shouldBeCalled()->willReturn($this->queryBuilder->reveal());

        $em = $this->prophesize(EntityManagerInterface::class);
        $subQb = $this->prophesize(QueryBuilder::class);
        $this->queryBuilder->getEntityManager()->willReturn($em->reveal());
        $em->createQueryBuilder()->willReturn($subQb->reveal());
        $subQb->select(Argument::cetera())->willReturn($subQb);
        $subQb->from(Argument::cetera())->willReturn($subQb);
        $subQb->innerJoin(Argument::cetera())->willReturn($subQb);
        $subQb->where(Argument::cetera())->willReturn($subQb);
        $subQb->andWhere(Argument::cetera())->willReturn($subQb);
        $subQb->getDQL()->willReturn('DQL');
        $this->queryBuilder->expr()->willReturn(new Expr());
        $this->queryBuilder->andWhere(Argument::any())->willReturn($this->queryBuilder->reveal());
        $this->queryBuilder->setParameter(Argument::cetera())->willReturn($this->queryBuilder->reveal());

        $this->extension->applyToCollection($this->queryBuilder->reveal(), $this->queryNameGenerator, ServiceBulletinFile::class);
    }
}
