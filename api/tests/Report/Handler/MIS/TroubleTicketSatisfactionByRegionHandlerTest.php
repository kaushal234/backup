<?php

declare(strict_types=1);

namespace App\Tests\Report\Handler\MIS;

use ApiPlatform\Metadata\IriConverterInterface;
use App\Entity\MIS\TroubleTicket\TroubleTicket;
use App\Report\Handler\MIS\TroubleTicketSatisfactionByRegionHandler;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Query\Expression\ExpressionBuilder;
use Doctrine\DBAL\Query\QueryBuilder;
use Doctrine\DBAL\Result;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;

class TroubleTicketSatisfactionByRegionHandlerTest extends TestCase
{
    use ProphecyTrait;

    private $iriConverter;
    private $connection;
    private $handler;

    protected function setUp(): void
    {
        $this->iriConverter = $this->prophesize(IriConverterInterface::class);
        $this->connection = $this->prophesize(Connection::class);
        $this->handler = new TroubleTicketSatisfactionByRegionHandler(
            $this->iriConverter->reveal(),
            $this->connection->reveal()
        );
    }

    public function testHandleReturnsNullOnInvalidArgs()
    {
        $this->assertNull($this->handler->handle('InvalidClass', 'x', 'y'));
        $this->assertNull($this->handler->handle(TroubleTicket::class, 'invalid.x', 'week'));
        $this->assertNull($this->handler->handle(TroubleTicket::class, 'satisfaction', 'invalid.y'));
    }

    public function testHandleWeekMode()
    {
        $queryBuilder = $this->prophesize(QueryBuilder::class);
        $expressionBuilder = $this->prophesize(ExpressionBuilder::class);
        $this->connection->createQueryBuilder()->willReturn($queryBuilder->reveal());

        $queryBuilder->select(Argument::any())->willReturn($queryBuilder->reveal());
        $queryBuilder->addSelect(Argument::any())->willReturn($queryBuilder->reveal());
        $queryBuilder->from('trouble_ticket', 't')->willReturn($queryBuilder->reveal());
        $queryBuilder->innerJoin(Argument::cetera())->willReturn($queryBuilder->reveal());
        $queryBuilder->where(Argument::any())->willReturn($queryBuilder->reveal());
        $queryBuilder->groupBy('x')->willReturn($queryBuilder->reveal());
        $queryBuilder->addGroupBy('y')->willReturn($queryBuilder->reveal());
        $queryBuilder->orderBy('bt.created_at')->willReturn($queryBuilder->reveal());

        $queryBuilder->expr()->willReturn($expressionBuilder->reveal());
        $expressionBuilder->isNotNull(Argument::any())->willReturn('isNotNull');
        $expressionBuilder->gt(Argument::any(), Argument::any())->willReturn('gt');
        $expressionBuilder->lt(Argument::any(), Argument::any())->willReturn('lt');
        $expressionBuilder->in(Argument::any(), Argument::any())->willReturn('in');

        $queryBuilder->andWhere(Argument::any())->willReturn($queryBuilder->reveal());
        $queryBuilder->setParameter(Argument::cetera())->willReturn($queryBuilder->reveal());

        $result = $this->prophesize(Result::class);
        $result->fetchAllAssociative()->willReturn([
            ['x' => 'W-1', 'y' => 'Bug', 'value' => 3.5],
            ['x' => 'W-0', 'y' => 'Bug', 'value' => 4.0],
        ]);
        $queryBuilder->executeQuery()->willReturn($result->reveal());

        $dataProvider = $this->handler->handle(TroubleTicket::class, 'satisfaction', 'week');

        $this->assertNotNull($dataProvider);
        $this->assertSame([['x' => 'W-0'], ['x' => 'W-1']], $dataProvider->provideXLabels());
        $this->assertSame([['y' => 'Bug']], $dataProvider->provideYLabels());
    }

    public function testHandleMonthMode()
    {
        $queryBuilder = $this->prophesize(QueryBuilder::class);
        $expressionBuilder = $this->prophesize(ExpressionBuilder::class);
        $this->connection->createQueryBuilder()->willReturn($queryBuilder->reveal());

        $queryBuilder->select(Argument::any())->willReturn($queryBuilder->reveal());
        $queryBuilder->addSelect(Argument::any())->willReturn($queryBuilder->reveal());
        $queryBuilder->from('trouble_ticket', 't')->willReturn($queryBuilder->reveal());
        $queryBuilder->innerJoin(Argument::cetera())->willReturn($queryBuilder->reveal());
        $queryBuilder->where(Argument::any())->willReturn($queryBuilder->reveal());
        $queryBuilder->groupBy('x')->willReturn($queryBuilder->reveal());
        $queryBuilder->addGroupBy('y')->willReturn($queryBuilder->reveal());
        $queryBuilder->orderBy('bt.created_at')->willReturn($queryBuilder->reveal());

        $queryBuilder->expr()->willReturn($expressionBuilder->reveal());
        $expressionBuilder->isNotNull(Argument::any())->willReturn('isNotNull');
        $expressionBuilder->gt(Argument::any(), Argument::any())->willReturn('gt');
        $expressionBuilder->lt(Argument::any(), Argument::any())->willReturn('lt');
        $expressionBuilder->in(Argument::any(), Argument::any())->willReturn('in');

        $queryBuilder->andWhere(Argument::any())->willReturn($queryBuilder->reveal());
        $queryBuilder->setParameter(Argument::cetera())->willReturn($queryBuilder->reveal());

        $result = $this->prophesize(Result::class);
        $result->fetchAllAssociative()->willReturn([
            ['x' => '2023-01', 'y' => 'Bug', 'value' => 3.5],
        ]);
        $queryBuilder->executeQuery()->willReturn($result->reveal());

        $dataProvider = $this->handler->handle(TroubleTicket::class, 'satisfaction', 'month');

        $this->assertNotNull($dataProvider);
        $this->assertSame([['x' => '2023-01']], $dataProvider->provideXLabels());
    }
}
