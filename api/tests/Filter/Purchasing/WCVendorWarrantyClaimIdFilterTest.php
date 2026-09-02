<?php

declare(strict_types=1);

namespace App\Tests\Filter\Purchasing;

use ApiPlatform\Doctrine\Orm\Util\QueryNameGenerator;
use App\Entity\Purchasing\VendorWarrantyClaim;
use App\Entity\Purchasing\WCVendorWarrantyClaim;
use App\Filter\Purchasing\WCVendorWarrantyClaimIdFilter;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query\Expr;
use Doctrine\ORM\Query\Expr\Andx;
use Doctrine\ORM\QueryBuilder;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

final class WCVendorWarrantyClaimIdFilterTest extends TestCase
{
    use ProphecyTrait;

    public function testFilterIsAppliedWhenValueIsValid(): void
    {
        $requestStackProphecy = $this->prophesize(RequestStack::class);
        $requestStackProphecy
            ->getCurrentRequest()
            ->shouldBeCalledTimes(1)
            ->willReturn(new Request([WCVendorWarrantyClaimIdFilter::FILTER_PROPERTY => '16426']));

        $filter = new WCVendorWarrantyClaimIdFilter($requestStackProphecy->reveal());

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
        self::assertStringContainsString(WCVendorWarrantyClaim::class, $all);

        self::assertStringContainsString('EXISTS', $all);

        // Subquery content we expect
        self::assertStringContainsString('warrantyClaimId = :warrantyClaimId', $all);
        self::assertStringContainsString('id = o.id', $all);

        // Parameter is set on the main QB
        $parameter = $queryBuilder->getParameter('warrantyClaimId');
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

        $filter = new WCVendorWarrantyClaimIdFilter($requestStackProphecy->reveal());

        $queryBuilder = $this->getQueryBuilder();
        $queryBuilder->from(VendorWarrantyClaim::class, 'o');

        $filter->apply($queryBuilder, new QueryNameGenerator(), VendorWarrantyClaim::class);

        self::assertNull($queryBuilder->getDQLPart('where'));
        self::assertNull($queryBuilder->getParameter('warrantyClaimId'));
    }

    public function testFilterIsNotAppliedWhenInvalidValue(): void
    {
        $requestStackProphecy = $this->prophesize(RequestStack::class);
        $requestStackProphecy
            ->getCurrentRequest()
            ->shouldBeCalledTimes(1)
            ->willReturn(new Request([WCVendorWarrantyClaimIdFilter::FILTER_PROPERTY => 'abc']));

        $filter = new WCVendorWarrantyClaimIdFilter($requestStackProphecy->reveal());

        $queryBuilder = $this->getQueryBuilder();
        $queryBuilder->from(VendorWarrantyClaim::class, 'o');

        $filter->apply($queryBuilder, new QueryNameGenerator(), VendorWarrantyClaim::class);

        self::assertNull($queryBuilder->getDQLPart('where'));
        self::assertNull($queryBuilder->getParameter('warrantyClaimId'));
    }

    public function testFilterIsNotAppliedWhenResourceClassIsNotVendorWarrantyClaim(): void
    {
        $requestStackProphecy = $this->prophesize(RequestStack::class);
        $requestStackProphecy
            ->getCurrentRequest()
            ->shouldBeCalledTimes(0);

        $filter = new WCVendorWarrantyClaimIdFilter($requestStackProphecy->reveal());

        $queryBuilder = $this->getQueryBuilder();
        $queryBuilder->from(VendorWarrantyClaim::class, 'o');

        $filter->apply($queryBuilder, new QueryNameGenerator(), \stdClass::class);

        self::assertNull($queryBuilder->getDQLPart('where'));
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
