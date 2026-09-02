<?php

declare(strict_types=1);

namespace App\Tests\Doctrine\ORM\Extension;

use ApiPlatform\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use App\Doctrine\ORM\Extension\PeopleNewComersAccessControlExtension;
use App\Entity\Directory\People;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query\Expr;
use Doctrine\ORM\QueryBuilder;
use PHPUnit\Framework\TestCase;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

final class PeopleNewComersAccessControlExtensionTest extends TestCase
{
    public function testDoesNothingWhenResourceIsNotPeople(): void
    {
        $extension = new PeopleNewComersAccessControlExtension(
            $this->createMock(RequestStack::class),
            $this->createMock(Security::class)
        );

        $qb = $this->createQueryBuilder();
        $beforeDql = $qb->getDQLParts();

        $extension->applyToCollection(
            $qb,
            $this->createMock(QueryNameGeneratorInterface::class),
            \stdClass::class
        );

        self::assertSame($beforeDql, $qb->getDQLParts());
    }

    public function testDoesNothingWhenNewComersQueryParamIsAbsent(): void
    {
        $requestStack = $this->createMock(RequestStack::class);
        $requestStack->method('getCurrentRequest')->willReturn(new Request());

        $extension = new PeopleNewComersAccessControlExtension(
            $requestStack,
            $this->createMock(Security::class)
        );

        $qb = $this->createQueryBuilder();
        $beforeDql = $qb->getDQLParts();

        $extension->applyToCollection(
            $qb,
            $this->createMock(QueryNameGeneratorInterface::class),
            People::class
        );

        self::assertSame($beforeDql, $qb->getDQLParts());
    }

    public function testDoesNothingWhenUserHasNewComersFeature(): void
    {
        $request = new Request();
        $request->query->set('peopleCurrentlyOrFutureEnabled', ['enableAt' => '2000-01-01']);

        $requestStack = $this->createMock(RequestStack::class);
        $requestStack->method('getCurrentRequest')->willReturn($request);

        $security = $this->createMock(Security::class);
        $security->method('isGranted')->with('FEATURE_FILTER_PEOPLE_INCOMING')->willReturn(true);

        $extension = new PeopleNewComersAccessControlExtension($requestStack, $security);

        $qb = $this->createQueryBuilder();
        $beforeDql = $qb->getDQLParts();

        $extension->applyToCollection(
            $qb,
            $this->createMock(QueryNameGeneratorInterface::class),
            People::class
        );

        self::assertSame($beforeDql, $qb->getDQLParts());
    }

    public function testAddsExistsClauseWhenUserLacksFeature(): void
    {
        $request = new Request();
        $request->query->set('peopleCurrentlyOrFutureEnabled', ['enableAt' => '2000-01-01']);

        $requestStack = $this->createMock(RequestStack::class);
        $requestStack->method('getCurrentRequest')->willReturn($request);

        $user = new People();

        $security = $this->createMock(Security::class);
        $security->method('isGranted')->with('FEATURE_FILTER_PEOPLE_INCOMING')->willReturn(false);
        $security->method('getUser')->willReturn($user);

        $extension = new PeopleNewComersAccessControlExtension($requestStack, $security);

        $qb = $this->createQueryBuilder();

        $extension->applyToCollection(
            $qb,
            $this->createMock(QueryNameGeneratorInterface::class),
            People::class
        );

        self::assertNotNull($qb->getDQLPart('where'));
        self::assertSame($user, $qb->getParameter('currentUser')?->getValue());

        // The injected DQL should reference the ongoing update tasks subquery
        $where = (string) $qb->getDQLPart('where');
        self::assertStringContainsString('EXISTS', $where);
        self::assertStringContainsString('UpdateTask', $where);
        self::assertStringContainsString('module.mainAdmin = :currentUser', $where);
        self::assertStringContainsString('module.operationalOwner = :currentUser', $where);
    }

    public function testForcesEmptyResultWhenUserIsNotPeople(): void
    {
        $request = new Request();
        $request->query->set('peopleCurrentlyOrFutureEnabled', ['enableAt' => '2000-01-01']);

        $requestStack = $this->createMock(RequestStack::class);
        $requestStack->method('getCurrentRequest')->willReturn($request);

        $security = $this->createMock(Security::class);
        $security->method('isGranted')->with('FEATURE_FILTER_PEOPLE_INCOMING')->willReturn(false);
        $security->method('getUser')->willReturn(null);

        $extension = new PeopleNewComersAccessControlExtension($requestStack, $security);

        $qb = $this->createQueryBuilder();

        $extension->applyToCollection(
            $qb,
            $this->createMock(QueryNameGeneratorInterface::class),
            People::class
        );

        $where = (string) $qb->getDQLPart('where');
        self::assertStringContainsString('1 = 0', $where);
    }

    private function createQueryBuilder(): QueryBuilder
    {
        $em = $this->createMock(EntityManagerInterface::class);
        // expr() delegates to $em->getExpressionBuilder() internally — must be a real Expr.
        $em->method('getExpressionBuilder')->willReturn(new Expr());
        // The extension builds an EXISTS subquery via $queryBuilder->getEntityManager()->createQueryBuilder().
        $em->method('createQueryBuilder')->willReturnCallback(static fn () => new QueryBuilder($em));

        $qb = new QueryBuilder($em);
        $qb->select('p')->from(People::class, 'p');

        return $qb;
    }
}
