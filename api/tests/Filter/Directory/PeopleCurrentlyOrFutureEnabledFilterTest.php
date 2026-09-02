<?php

declare(strict_types=1);

namespace App\Tests\Filter\Directory;

use ApiPlatform\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use App\Entity\Directory\People;
use App\Filter\Directory\PeopleCurrentlyOrFutureEnabledFilter;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

final class PeopleCurrentlyOrFutureEnabledFilterTest extends TestCase
{
    public function testItDoesNothingWhenQueryParamIsMissing(): void
    {
        $requestStack = $this->createMock(RequestStack::class);
        $requestStack->method('getCurrentRequest')->willReturn(new Request());

        $filter = new PeopleCurrentlyOrFutureEnabledFilter($requestStack);

        $qb = $this->createQueryBuilder();
        $beforeDql = $qb->getDQLParts();

        $filter->apply(
            $qb,
            $this->createMock(QueryNameGeneratorInterface::class),
            People::class
        );

        self::assertSame(
            $beforeDql,
            $qb->getDQLParts(),
            'QueryBuilder should not be modified when filter param is absent.'
        );
    }

    public function testItSetsParametersAndAddsBothBranches(): void
    {
        $request = new Request();
        $request->query->set('peopleCurrentlyOrFutureEnabled', [
            'enableAt' => '2030-01-05',
            'plannedEnableAt' => '2030-04-05',
        ]);

        $requestStack = $this->createMock(RequestStack::class);
        $requestStack->method('getCurrentRequest')->willReturn($request);

        $filter = new PeopleCurrentlyOrFutureEnabledFilter($requestStack);

        $qb = $this->createQueryBuilder();

        $filter->apply(
            $qb,
            $this->createMock(QueryNameGeneratorInterface::class),
            People::class
        );

        // Parameters exist
        self::assertNotNull($qb->getParameter('now'));
        self::assertNotNull($qb->getParameter('enableAtStart'));
        self::assertNotNull($qb->getParameter('plannedEnableAtEnd'));

        // enableAtStart
        $enableAtStart = $qb->getParameter('enableAtStart')->getValue();
        self::assertInstanceOf(\DateTimeInterface::class, $enableAtStart);
        self::assertSame('2030-01-05', $enableAtStart->format('Y-m-d'));

        // plannedEnableAtEnd
        $plannedEnableAtEnd = $qb->getParameter('plannedEnableAtEnd')->getValue();
        self::assertInstanceOf(\DateTimeInterface::class, $plannedEnableAtEnd);
        self::assertSame('2030-04-05', $plannedEnableAtEnd->format('Y-m-d'));

        // now
        $now = $qb->getParameter('now')->getValue();
        self::assertInstanceOf(\DateTimeInterface::class, $now);

        // The WHERE clause exists (already enabled + planned branches).
        self::assertNotNull($qb->getDQLPart('where'));
    }

    /**
     * Regression: the planned (future) branch must be added unconditionally,
     * with no feature gate. Scoping for users without the unrestricted-access
     * feature is delegated to the access-control extension. The
     * `plannedEnableAtEnd` parameter is bound only inside the planned branch,
     * so its presence proves the branch ran without any security check.
     */
    public function testItAlwaysAddsThePlannedBranch(): void
    {
        $request = new Request();
        $request->query->set('peopleCurrentlyOrFutureEnabled', [
            'enableAt' => '2030-01-05',
            'plannedEnableAt' => '2030-04-05',
        ]);

        $requestStack = $this->createMock(RequestStack::class);
        $requestStack->method('getCurrentRequest')->willReturn($request);

        $filter = new PeopleCurrentlyOrFutureEnabledFilter($requestStack);

        $qb = $this->createQueryBuilder();

        $filter->apply(
            $qb,
            $this->createMock(QueryNameGeneratorInterface::class),
            People::class
        );

        self::assertNotNull($qb->getParameter('plannedEnableAtEnd'));
    }

    public function testItThrowsWhenValueIsEmpty(): void
    {
        $request = new Request();
        $request->query->set('peopleCurrentlyOrFutureEnabled', []);

        $requestStack = $this->createMock(RequestStack::class);
        $requestStack->method('getCurrentRequest')->willReturn($request);

        $filter = new PeopleCurrentlyOrFutureEnabledFilter($requestStack);

        $qb = $this->createQueryBuilder();

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage(
            'This filter requires peopleCurrentlyOrFutureEnabled[enableAt]'
        );

        $filter->apply(
            $qb,
            $this->createMock(QueryNameGeneratorInterface::class),
            People::class
        );
    }

    public function testItDoesNotBindPlannedEnableAtEndWhenParamIsMissing(): void
    {
        $request = new Request();
        $request->query->set('peopleCurrentlyOrFutureEnabled', [
            'enableAt' => '2030-01-05',
            // plannedEnableAt intentionally omitted (no upper bound on future arrivals)
        ]);

        $requestStack = $this->createMock(RequestStack::class);
        $requestStack->method('getCurrentRequest')->willReturn($request);

        $filter = new PeopleCurrentlyOrFutureEnabledFilter($requestStack);

        $qb = $this->createQueryBuilder();

        $filter->apply(
            $qb,
            $this->createMock(QueryNameGeneratorInterface::class),
            People::class
        );

        // Required parameters are still bound
        self::assertNotNull($qb->getParameter('now'));
        self::assertNotNull($qb->getParameter('enableAtStart'));

        // No upper bound was provided, so plannedEnableAtEnd must not be bound
        self::assertNull($qb->getParameter('plannedEnableAtEnd'));

        // WHERE clause still exists (both branches added, the planned branch is open-ended)
        self::assertNotNull($qb->getDQLPart('where'));
    }

    public function testItThrowsWhenDatesAreInvalid(): void
    {
        $request = new Request();
        $request->query->set('peopleCurrentlyOrFutureEnabled', [
            'enableAt' => 'not-a-date',
            'plannedEnableAt' => '2030-04-05',
        ]);

        $requestStack = $this->createMock(RequestStack::class);
        $requestStack->method('getCurrentRequest')->willReturn($request);

        $filter = new PeopleCurrentlyOrFutureEnabledFilter($requestStack);

        $qb = $this->createQueryBuilder();

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage(
            'Invalid date value for peopleCurrentlyOrFutureEnabled filter.'
        );

        $filter->apply(
            $qb,
            $this->createMock(QueryNameGeneratorInterface::class),
            People::class
        );
    }

    public function testItThrowsWhenResourceIsNotPeople(): void
    {
        $request = new Request();
        $request->query->set('peopleCurrentlyOrFutureEnabled', [
            'enableAt' => '2030-01-05',
            'plannedEnableAt' => '2030-04-05',
        ]);

        $requestStack = $this->createMock(RequestStack::class);
        $requestStack->method('getCurrentRequest')->willReturn($request);

        $filter = new PeopleCurrentlyOrFutureEnabledFilter($requestStack);

        $qb = $this->createQueryBuilder();

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('This filter is restricted to the People resource');

        $filter->apply(
            $qb,
            $this->createMock(QueryNameGeneratorInterface::class),
            \stdClass::class
        );
    }

    private function createQueryBuilder(): QueryBuilder
    {
        $em = $this->createMock(EntityManagerInterface::class);

        $qb = new QueryBuilder($em);
        $qb->select('p')->from(People::class, 'p');

        return $qb;
    }
}
