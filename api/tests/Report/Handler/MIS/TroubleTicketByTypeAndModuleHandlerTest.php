<?php

declare(strict_types=1);

namespace App\Tests\Report\Handler\MIS;

use ApiPlatform\Metadata\IriConverterInterface;
use App\Entity\MIS\TroubleTicket\TroubleTicket;
use App\Report\Handler\MIS\TroubleTicketByTypeAndModuleHandler;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Query\QueryBuilder;
use Doctrine\DBAL\Result;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;

class TroubleTicketByTypeAndModuleHandlerTest extends TestCase
{
    use ProphecyTrait;

    private $iriConverter;
    private $connection;
    private $handler;

    protected function setUp(): void
    {
        $this->iriConverter = $this->prophesize(IriConverterInterface::class);
        $this->connection = $this->prophesize(Connection::class);
        $this->handler = new TroubleTicketByTypeAndModuleHandler(
            $this->iriConverter->reveal(),
            $this->connection->reveal()
        );
    }

    public function testHandleReturnsNullOnInvalidArgs()
    {
        $this->assertNull($this->handler->handle('InvalidClass', 'x', 'y'));
        $this->assertNull($this->handler->handle(TroubleTicket::class, 'invalid.x', 'y'));
        $this->assertNull($this->handler->handle(TroubleTicket::class, TroubleTicketByTypeAndModuleHandler::X, 'invalid.y'));
    }

    public function testHandleBasicQuery()
    {
        $queryBuilder = $this->prophesize(QueryBuilder::class);
        $this->connection->createQueryBuilder()->willReturn($queryBuilder->reveal());

        $queryBuilder->select('COUNT(t.id) AS value')->willReturn($queryBuilder->reveal());
        $queryBuilder->addSelect('m.name AS x')->willReturn($queryBuilder->reveal());
        $queryBuilder->addSelect('ty.type AS y')->willReturn($queryBuilder->reveal());
        $queryBuilder->from('trouble_ticket', 't')->willReturn($queryBuilder->reveal());
        $queryBuilder->innerJoin('t', 'base_task', 'bt', 'bt.id = t.id')->willReturn($queryBuilder->reveal());
        $queryBuilder->innerJoin('bt', 'modules', 'm', 'bt.module_id = m.id')->willReturn($queryBuilder->reveal());
        $queryBuilder->innerJoin('t', 'type', 'ty', 't.type_id = ty.id')->willReturn($queryBuilder->reveal());
        $queryBuilder->groupBy('x')->willReturn($queryBuilder->reveal());
        $queryBuilder->addGroupBy('y')->willReturn($queryBuilder->reveal());
        $queryBuilder->orderBy('x', 'ASC')->willReturn($queryBuilder->reveal());
        $queryBuilder->addOrderBy('y', 'ASC')->willReturn($queryBuilder->reveal());

        $result = $this->prophesize(Result::class);
        $result->fetchAllAssociative()->willReturn([
            ['x' => 'Module A', 'y' => 'Bug', 'value' => 10],
            ['x' => 'Module B', 'y' => 'Feature', 'value' => 5],
        ]);
        $queryBuilder->executeQuery()->willReturn($result->reveal());

        $dataProvider = $this->handler->handle(
            TroubleTicket::class,
            TroubleTicketByTypeAndModuleHandler::X,
            TroubleTicketByTypeAndModuleHandler::Y
        );

        $this->assertNotNull($dataProvider);
        $this->assertSame([
            ['x' => 'Module A', 'y' => 'Bug', 'value' => 10],
            ['x' => 'Module B', 'y' => 'Feature', 'value' => 5],
        ], $dataProvider->provideData());
        $this->assertSame([['x' => 'Module A'], ['x' => 'Module B']], $dataProvider->provideXLabels());
        $this->assertSame([['y' => 'Bug'], ['y' => 'Feature']], $dataProvider->provideYLabels());
    }

    public function testHandleWithOptions()
    {
        $queryBuilder = $this->prophesize(QueryBuilder::class);
        $this->connection->createQueryBuilder()->willReturn($queryBuilder->reveal());

        // Basic setup
        $queryBuilder->select(Argument::any())->willReturn($queryBuilder->reveal());
        $queryBuilder->addSelect(Argument::any())->willReturn($queryBuilder->reveal());
        $queryBuilder->from(Argument::any(), Argument::any())->willReturn($queryBuilder->reveal());
        $queryBuilder->innerJoin(Argument::cetera())->willReturn($queryBuilder->reveal());
        $queryBuilder->groupBy(Argument::any())->willReturn($queryBuilder->reveal());
        $queryBuilder->addGroupBy(Argument::any())->willReturn($queryBuilder->reveal());
        $queryBuilder->orderBy(Argument::cetera())->willReturn($queryBuilder->reveal());
        $queryBuilder->addOrderBy(Argument::cetera())->willReturn($queryBuilder->reveal());

        // Options filters
        $queryBuilder->andWhere('bt.created_at >= :after')->shouldBeCalled()->willReturn($queryBuilder->reveal());
        $queryBuilder->setParameter('after', '2023-01-01')->shouldBeCalled()->willReturn($queryBuilder->reveal());

        $queryBuilder->andWhere('bt.created_at <= :before')->shouldBeCalled()->willReturn($queryBuilder->reveal());
        $queryBuilder->setParameter('before', '2023-12-31')->shouldBeCalled()->willReturn($queryBuilder->reveal());

        $appMock = new class {
            public function getId()
            {
                return 123;
            }
        };
        $this->iriConverter->getResourceFromIri('/api/applications/123')->willReturn($appMock);
        $queryBuilder->innerJoin('m', 'application', 'a', 'm.application_id = a.id')->shouldBeCalled()->willReturn($queryBuilder->reveal());
        $queryBuilder->andWhere('a.id IN (:applicationIds)')->shouldBeCalled()->willReturn($queryBuilder->reveal());
        $queryBuilder->setParameter('applicationIds', [123], Argument::any())->shouldBeCalled()->willReturn($queryBuilder->reveal());

        $moduleMock = new class {
            public function getId()
            {
                return 456;
            }
        };
        $this->iriConverter->getResourceFromIri('/api/modules/456')->willReturn($moduleMock);
        $queryBuilder->andWhere('m.id IN (:moduleIds)')->shouldBeCalled()->willReturn($queryBuilder->reveal());
        $queryBuilder->setParameter('moduleIds', [456], Argument::any())->shouldBeCalled()->willReturn($queryBuilder->reveal());

        $queryBuilder->andWhere('ty.type IN (:types)')->shouldBeCalled()->willReturn($queryBuilder->reveal());
        $queryBuilder->setParameter('types', ['Bug'], Argument::any())->shouldBeCalled()->willReturn($queryBuilder->reveal());

        $assigneeMock = new class {
            public function getId()
            {
                return 789;
            }
        };
        $this->iriConverter->getResourceFromIri('/api/people/789')->willReturn($assigneeMock);
        $queryBuilder->andWhere('t.mis_assignee_id IN (:misAssigneeIds)')->shouldBeCalled()->willReturn($queryBuilder->reveal());
        $queryBuilder->setParameter('misAssigneeIds', [789], Argument::any())->shouldBeCalled()->willReturn($queryBuilder->reveal());

        $result = $this->prophesize(Result::class);
        $result->fetchAllAssociative()->willReturn([]);
        $queryBuilder->executeQuery()->willReturn($result->reveal());

        $this->handler->handle(
            TroubleTicket::class,
            TroubleTicketByTypeAndModuleHandler::X,
            TroubleTicketByTypeAndModuleHandler::Y,
            [
                'createdAt' => ['after' => '2023-01-01', 'before' => '2023-12-31'],
                'application' => ['/api/applications/123'],
                'module' => ['/api/modules/456'],
                'type.type' => 'Bug',
                'misAssignee' => ['/api/people/789'],
            ]
        );
    }
}
