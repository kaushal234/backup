<?php

declare(strict_types=1);

namespace App\Tests\Filter;

use ApiPlatform\Doctrine\Orm\Util\QueryNameGenerator;
use App\Entity\EquipmentRecord;
use App\Filter\NotBetweenFilter;
use Doctrine\ORM\Query\Expr\Andx;
use Doctrine\ORM\Query\Expr\Orx;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Prophecy\PhpUnit\ProphecyTrait;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class NotBetweenFilterTest extends KernelTestCase
{
    use ProphecyTrait;

    private ManagerRegistry $managerRegistry;

    protected function setUp(): void
    {
        self::bootKernel();
        /** @var ManagerRegistry $managerRegistry */
        $managerRegistry = static::getContainer()->get('doctrine');
        $this->managerRegistry = $managerRegistry;
    }

    public function testTheQueryBuilderClauseIsAdded()
    {
        $filter = new NotBetweenFilter(
            $this->managerRegistry,
            null,
            ['contracts.startDate;contracts.expirationDate' => null, 'contracts.expirationDate;contracts.startDate' => null]
        );

        $em = $this->managerRegistry->getManagerForClass(EquipmentRecord::class);

        $queryBuilderMock = $this->getMockBuilder(QueryBuilder::class)->setConstructorArgs([$em])->onlyMethods(['getRootAliases', 'andWhere', 'setParameter'])->getMock();
        $queryBuilderMock->expects($this->atLeastOnce())->method('getRootAliases')->willReturn(['o']);
        $queryBuilderMock->expects(self::once())
            ->method('andWhere')
            ->with(self::callback(static fn (Orx $expr) => $expr->getParts() === ['contracts_a1.startDate > :startDate_p1', 'contracts_a1.expirationDate < :expirationDate_p2']))
            ->willReturn($queryBuilderMock);
        $queryBuilderMock->expects(self::exactly(2))
            ->method('setParameter')
            ->withConsecutive(
                ['startDate_p1', new \DateTime('2018-03-09')],
                ['expirationDate_p2', new \DateTime('2018-03-09')]
            );

        $filter->apply($queryBuilderMock, new QueryNameGenerator(), EquipmentRecord::class, null, ['filters' => ['not_between' => ['contracts.startDate;contracts.expirationDate' => '2018-03-09']]]);
    }

    public function testTheQueryBuilderClauseIsAddedWithNullInclusion()
    {
        $filter = new NotBetweenFilter(
            $this->managerRegistry,
            null,
            ['contracts.startDate;contracts.expirationDate' => NotBetweenFilter::INCLUDE_NULL]
        );

        $em = $this->managerRegistry->getManagerForClass(EquipmentRecord::class);

        $queryBuilderMock = $this->getMockBuilder(QueryBuilder::class)->setConstructorArgs([$em])->onlyMethods(['getRootAliases', 'andWhere', 'setParameter'])->getMock();
        $queryBuilderMock->expects($this->atLeastOnce())->method('getRootAliases')->willReturn(['o']);
        $queryBuilderMock->expects(self::once())
            ->method('andWhere')
            ->with(self::callback(static function (Orx $expr) {
                $parts = $expr->getParts();
                $expectedOr = ['contracts_a1.startDate > :startDate_p1', 'contracts_a1.expirationDate < :expirationDate_p2'];
                $innerExpr = array_pop($parts);
                $expectedAnd = ['contracts_a1.startDate IS NULL', 'contracts_a1.expirationDate IS NULL'];

                return $parts === $expectedOr && $innerExpr instanceof Andx && $innerExpr->getParts() === $expectedAnd;
            }))
            ->willReturn($queryBuilderMock);
        $queryBuilderMock->expects(self::exactly(2))
            ->method('setParameter')
            ->withConsecutive(
                ['startDate_p1', new \DateTime('2018-03-09')],
                ['expirationDate_p2', new \DateTime('2018-03-09')]
            );

        $filter->apply($queryBuilderMock, new QueryNameGenerator(), EquipmentRecord::class, null, ['filters' => ['not_between' => ['contracts.startDate;contracts.expirationDate' => '2018-03-09']]]);
    }

    public function testTheQueryBuilderClauseIsAddedMultipleTimes()
    {
        $filter = new NotBetweenFilter(
            $this->managerRegistry,
            null,
            ['contracts.startDate;contracts.expirationDate' => null, 'contracts.expirationDate;contracts.startDate' => null]
        );

        $em = $this->managerRegistry->getManagerForClass(EquipmentRecord::class);

        $queryBuilderMock = $this->getMockBuilder(QueryBuilder::class)->setConstructorArgs([$em])->onlyMethods(['getRootAliases', 'andWhere', 'setParameter'])->getMock();
        $queryBuilderMock->expects($this->atLeastOnce())->method('getRootAliases')->willReturn(['o']);
        $queryBuilderMock->expects(self::exactly(2))
            ->method('andWhere')
            ->withConsecutive(
                [self::callback(static fn (Orx $expr) => $expr->getParts() === ['contracts_a1.startDate > :startDate_p1', 'contracts_a1.expirationDate < :expirationDate_p2'])],
                [self::callback(static fn (Orx $expr) => $expr->getParts() === ['contracts_a1.expirationDate > :expirationDate_p3', 'contracts_a1.startDate < :startDate_p4'])]
            )
            ->willReturn($queryBuilderMock);
        $queryBuilderMock->expects(self::exactly(4))

            ->method('setParameter')
            ->withConsecutive(
                ['startDate_p1', new \DateTime('2018-03-09')],
                ['expirationDate_p2', new \DateTime('2018-03-09')],
                ['expirationDate_p3', new \DateTime('2018-03-10')],
                ['startDate_p4', new \DateTime('2018-03-10')]
            );
        $filter->apply($queryBuilderMock, new QueryNameGenerator(), EquipmentRecord::class, null, ['filters' => ['not_between' => ['contracts.startDate;contracts.expirationDate' => '2018-03-09', 'contracts.expirationDate;contracts.startDate' => '2018-03-10']]]);
    }

    /**
     * @dataProvider getNotImpactingContext
     */
    public function testQueryBuilderIsNotUpdated($context, $requireAlias = false)
    {
        $filter = new NotBetweenFilter(
            $this->managerRegistry,
            null,
            [
                'contracts.startDate;contracts.expirationDate' => null,
                'notmapped;contracts.expirationDate' => null,
                'serialNumber;contracts.expirationDate' => null, // Not a Date
            ],
            null,
        );

        if (!$requireAlias) {
            $queryBuilderProphecy = $this->prophesize(QueryBuilder::class);
            $queryBuilderProphecy->getRootAliases()->shouldNotBeCalled();
            $queryBuilderProphecy->andWhere()->shouldNotBeCalled();
            $queryBuilderMock = $queryBuilderProphecy->reveal();
        } else {
            $queryBuilderMock = $this->getMockBuilder(QueryBuilder::class)->disableOriginalConstructor()->onlyMethods(['getRootAliases', 'andWhere'])->getMock();
            $queryBuilderMock->expects($this->atLeastOnce())->method('getRootAliases')->willReturn(['o']);
            $queryBuilderMock->expects(self::never())->method('andWhere');
        }

        $filter->apply($queryBuilderMock, new QueryNameGenerator(), EquipmentRecord::class, null, $context);
    }

    public function getNotImpactingContext()
    {
        return [
            'Trying to apply on not mapped field' => [['filters' => ['not_between' => ['notmapped;contracts.expirationDate' => '2018-03-09']]]],
            'Trying to apply on not date field' => [['filters' => ['not_between' => ['serialNumber;contracts.expirationDate' => '2018-03-09']]]],
            'Trying to apply with bad date value' => [['filters' => ['not_between' => ['contracts.startDate;contracts.expirationDate' => 'dummy']]], true],
        ];
    }

    public function testGetDescription()
    {
        $filter = new NotBetweenFilter(
            $this->managerRegistry
        );
        self::assertSame([], $filter->getDescription(EquipmentRecord::class));

        $filter = new NotBetweenFilter(
            $this->managerRegistry,
            null,
            [
                'contracts.startDate;contracts.expirationDate' => null,
                'notmapped;contracts.expirationDate' => null,
                'serialNumber;contracts.expirationDate' => null, // Not a Date
            ]
        );
        self::assertSame([
            'not_between[contracts.startDate;contracts.expirationDate]' => [
                'property' => 'contracts.startDate;contracts.expirationDate',
                'type' => 'string',
                'required' => false,
            ],
        ], $filter->getDescription(EquipmentRecord::class));
    }
}
