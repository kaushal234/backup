<?php

declare(strict_types=1);

namespace App\Tests\Filter\Purchasing;

use ApiPlatform\Doctrine\Orm\Util\QueryNameGenerator;
use App\Entity\Purchasing\NCRVendorWarrantyClaim;
use App\Entity\Purchasing\VendorWarrantyClaim;
use App\Filter\Purchasing\NCRVendorWarrantyClaimIdFilter;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query\Expr;
use Doctrine\ORM\Query\Expr\Andx;
use Doctrine\ORM\QueryBuilder;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

final class NCRVendorWarrantyClaimIdFilterTest extends TestCase
{
    use ProphecyTrait;

    public function testFilterIsAppliedWhenValueIsValid(): void
    {
        $requestStackProphecy = $this->prophesize(RequestStack::class);
        $requestStackProphecy
            ->getCurrentRequest()
            ->shouldBeCalledTimes(1)
            ->willReturn(new Request([NCRVendorWarrantyClaimIdFilter::FILTER_PROPERTY => '16426']));

        $filter = new NCRVendorWarrantyClaimIdFilter($requestStackProphecy->reveal());

        $queryBuilder = $this->getQueryBuilder();
        $queryBuilder->from(VendorWarrantyClaim::class, 'o');

        $filter->apply($queryBuilder, new QueryNameGenerator(), VendorWarrantyClaim::class);

        /** @var Andx|null $where */
        $where = $queryBuilder->getDQLPart('where');
        self::assertInstanceOf(Andx::class, $where);

        $andParts = $where->getParts();
        self::assertNotEmpty($andParts);

        $all = implode(' ', array_map('strval', $andParts));

        self::assertStringContainsString('INSTANCE OF', $all);
        self::assertStringContainsString(NCRVendorWarrantyClaim::class, $all);

        self::assertStringContainsString('EXISTS', $all);

        // subquery content we expect
        self::assertStringContainsString('nonConformity = :nonConformityId', $all);
        self::assertStringContainsString('id = o.id', $all);

        // parameter is set on the main QB
        $parameter = $queryBuilder->getParameter('nonConformityId');
        self::assertNotNull($parameter);
        self::assertSame(16426, (int) $parameter->getValue());
    }

    public function testFilterIsNotAppliedWhenMissing(): void
    {
        $requestStackProphecy = $this->prophesize(RequestStack::class);
        $requestStackProphecy
            ->getCurrentRequest()
            ->shouldBeCalledTimes(1)
            ->willReturn(new Request([]));

        $filter = new NCRVendorWarrantyClaimIdFilter($requestStackProphecy->reveal());

        $queryBuilder = $this->getQueryBuilder();
        $queryBuilder->from(VendorWarrantyClaim::class, 'o');

        $filter->apply($queryBuilder, new QueryNameGenerator(), VendorWarrantyClaim::class);

        self::assertNull($queryBuilder->getDQLPart('where'));
        self::assertNull($queryBuilder->getParameter('nonConformityId'));
    }

    public function testFilterIsNotAppliedWhenInvalidValue(): void
    {
        $requestStackProphecy = $this->prophesize(RequestStack::class);
        $requestStackProphecy
            ->getCurrentRequest()
            ->shouldBeCalledTimes(1)
            ->willReturn(new Request([NCRVendorWarrantyClaimIdFilter::FILTER_PROPERTY => 'abc']));

        $filter = new NCRVendorWarrantyClaimIdFilter($requestStackProphecy->reveal());

        $queryBuilder = $this->getQueryBuilder();
        $queryBuilder->from(VendorWarrantyClaim::class, 'o');

        $filter->apply($queryBuilder, new QueryNameGenerator(), VendorWarrantyClaim::class);

        self::assertNull($queryBuilder->getDQLPart('where'));
        self::assertNull($queryBuilder->getParameter('nonConformityId'));
    }

    public function testFilterIsNotAppliedWhenResourceClassIsNotVendorWarrantyClaim(): void
    {
        $requestStackProphecy = $this->prophesize(RequestStack::class);
        $requestStackProphecy
            ->getCurrentRequest()
            ->shouldBeCalledTimes(0);

        $filter = new NCRVendorWarrantyClaimIdFilter($requestStackProphecy->reveal());

        $queryBuilder = $this->getQueryBuilder();
        $queryBuilder->from(VendorWarrantyClaim::class, 'o');

        $filter->apply($queryBuilder, new QueryNameGenerator(), \stdClass::class);

        self::assertNull($queryBuilder->getDQLPart('where'));
        self::assertNull($queryBuilder->getParameter('nonConformityId'));
    }

    private function getQueryBuilder(): QueryBuilder
    {
        $emProphecy = $this->prophesize(EntityManagerInterface::class);
        $emProphecy->getExpressionBuilder()->willReturn(new Expr());

        $subEmProphecy = $this->prophesize(EntityManagerInterface::class);
        $subEmProphecy->getExpressionBuilder()->willReturn(new Expr());

        $subQueryBuilder = new QueryBuilder($subEmProphecy->reveal());

        $emProphecy->createQueryBuilder()->willReturn($subQueryBuilder);

        return new QueryBuilder($emProphecy->reveal());
    }
}
