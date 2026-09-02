<?php

declare(strict_types=1);

namespace App\Tests\Filter\MIS\Module;

use ApiPlatform\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use App\Entity\Module\ThirdPartyApp\UpdateTask;
use App\Filter\MIS\Module\UpdateTaskSupportTeamFilter;
use App\Util\IriToId;
use Doctrine\ORM\QueryBuilder;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\InputBag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

class UpdateTaskSupportTeamFilterTest extends TestCase
{
    private RequestStack&MockObject $requestStack;
    private IriToId&MockObject $iriToId;
    private QueryBuilder&MockObject $queryBuilder;
    private QueryNameGeneratorInterface&MockObject $queryNameGenerator;
    private UpdateTaskSupportTeamFilter $filter;

    protected function setUp(): void
    {
        $this->requestStack = $this->createMock(RequestStack::class);
        $this->iriToId = $this->createMock(IriToId::class);
        $this->queryBuilder = $this->createMock(QueryBuilder::class);
        $this->queryNameGenerator = $this->createMock(QueryNameGeneratorInterface::class);

        $this->filter = new UpdateTaskSupportTeamFilter(
            $this->requestStack,
            $this->iriToId,
        );
    }

    public function testApplyDoesNothingWhenNoRequest(): void
    {
        $this->requestStack
            ->method('getCurrentRequest')
            ->willReturn(null);

        $this->queryBuilder->expects($this->never())->method('innerJoin');

        $this->filter->apply(
            $this->queryBuilder,
            $this->queryNameGenerator,
            UpdateTask::class,
        );
    }

    public function testApplyDoesNothingWhenNoFilterValue(): void
    {
        $this->mockRequest([]);

        $this->queryBuilder->expects($this->never())->method('innerJoin');

        $this->filter->apply(
            $this->queryBuilder,
            $this->queryNameGenerator,
            UpdateTask::class,
        );
    }

    public function testApplyThrowsExceptionForWrongResourceClass(): void
    {
        $this->mockRequest([
            UpdateTaskSupportTeamFilter::FILTER_USED_PROPERTY => '/mis/support_teams/1',
        ]);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('This filter is restricted to the UpdateTask resource');

        $this->filter->apply(
            $this->queryBuilder,
            $this->queryNameGenerator,
            \stdClass::class,
        );
    }

    public function testApplyWithSingleIri(): void
    {
        $this->mockRequest([
            UpdateTaskSupportTeamFilter::FILTER_USED_PROPERTY => '/mis/support_teams/1',
        ]);
        $this->mockQueryBuilder();

        $this->iriToId
            ->expects($this->once())
            ->method('getId')
            ->with('/mis/support_teams/1')
            ->willReturn(1);

        $this->queryBuilder
            ->expects($this->exactly(3))
            ->method('innerJoin')
            ->willReturnSelf();

        $this->queryBuilder
            ->expects($this->once())
            ->method('andWhere')
            ->with('filter_support_team.id IN (:supportTeamIds)')
            ->willReturnSelf();

        $this->queryBuilder
            ->expects($this->once())
            ->method('setParameter')
            ->with('supportTeamIds', [1])
            ->willReturnSelf();

        $this->filter->apply(
            $this->queryBuilder,
            $this->queryNameGenerator,
            UpdateTask::class,
        );
    }

    public function testApplyWithMultipleIris(): void
    {
        $this->mockRequest([
            UpdateTaskSupportTeamFilter::FILTER_USED_PROPERTY => [
                '/mis/support_teams/1',
                '/mis/support_teams/2',
            ],
        ]);
        $this->mockQueryBuilder();

        $this->iriToId
            ->expects($this->exactly(2))
            ->method('getId')
            ->willReturnMap([
                ['/mis/support_teams/1', 1],
                ['/mis/support_teams/2', 2],
            ]);

        $this->queryBuilder
            ->expects($this->once())
            ->method('setParameter')
            ->with('supportTeamIds', [1, 2])
            ->willReturnSelf();

        $this->filter->apply(
            $this->queryBuilder,
            $this->queryNameGenerator,
            UpdateTask::class,
        );
    }

    public function testApplyWithNumericId(): void
    {
        $this->mockRequest([
            UpdateTaskSupportTeamFilter::FILTER_USED_PROPERTY => '42',
        ]);
        $this->mockQueryBuilder();

        $this->iriToId->expects($this->never())->method('getId');

        $this->queryBuilder
            ->expects($this->once())
            ->method('setParameter')
            ->with('supportTeamIds', ['42'])
            ->willReturnSelf();

        $this->filter->apply(
            $this->queryBuilder,
            $this->queryNameGenerator,
            UpdateTask::class,
        );
    }

    public function testGetDescription(): void
    {
        $description = $this->filter->getDescription(UpdateTask::class);

        $this->assertArrayHasKey(UpdateTaskSupportTeamFilter::FILTER_USED_PROPERTY, $description);
        $this->assertSame('string', $description[UpdateTaskSupportTeamFilter::FILTER_USED_PROPERTY]['type']);
        $this->assertFalse($description[UpdateTaskSupportTeamFilter::FILTER_USED_PROPERTY]['required']);
    }

    private function mockRequest(array $queryParams): void
    {
        $request = $this->createMock(Request::class);
        $request->query = new InputBag($queryParams);

        $this->requestStack
            ->method('getCurrentRequest')
            ->willReturn($request);
    }

    private function mockQueryBuilder(): void
    {
        $this->queryBuilder
            ->method('getRootAliases')
            ->willReturn(['o']);

        $this->queryBuilder
            ->method('innerJoin')
            ->willReturnSelf();

        $this->queryBuilder
            ->method('andWhere')
            ->willReturnSelf();

        $this->queryBuilder
            ->method('setParameter')
            ->willReturnSelf();
    }
}
