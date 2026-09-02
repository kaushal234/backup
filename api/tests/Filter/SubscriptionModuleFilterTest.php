<?php

declare(strict_types=1);

namespace App\Tests\Filter;

use ApiPlatform\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use ApiPlatform\Metadata\IriConverterInterface;
use App\Entity\Common\Subscription;
use App\Entity\Module\Module;
use App\Filter\SubscriptionModuleFilter;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query\Expr;
use Doctrine\ORM\Query\Expr\Andx;
use Doctrine\ORM\Query\Expr\Orx;
use Doctrine\ORM\QueryBuilder;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

class SubscriptionModuleFilterTest extends TestCase
{
    use ProphecyTrait;

    public function testFilterDoesNothingWhenNoModuleQueryParam(): void
    {
        $filter = $this->createFilter(request: new Request());
        $queryBuilder = $this->createQueryBuilder();

        $filter->apply($queryBuilder, $this->createQueryNameGenerator(), Subscription::class);

        self::assertNull($queryBuilder->getDQLPart('where'));
    }

    public function testFilterAppliesLikeClauseForKnownModule(): void
    {
        $module = $this->createModule('DEMO');

        $filter = $this->createFilter(
            request: new Request(['module' => ['/api/modules/15']]),
            iriResolutions: ['/api/modules/15' => $module],
        );
        $queryBuilder = $this->createQueryBuilder();

        $filter->apply($queryBuilder, $this->createQueryNameGenerator(), Subscription::class);

        /** @var Andx $where */
        $where = $queryBuilder->getDQLPart('where');
        self::assertInstanceOf(Andx::class, $where);
        $orParts = $where->getParts();
        self::assertCount(1, $orParts);
        self::assertInstanceOf(Orx::class, $orParts[0]);
        self::assertSame(
            ['o.resource LIKE :module_p1'],
            array_map('strval', $orParts[0]->getParts()),
        );
        self::assertSame('/sales/demos/%', $queryBuilder->getParameter('module_p1')->getValue());
    }

    public function testFilterExpandsMultiplePrefixesForVendorWarrantyClaim(): void
    {
        $module = $this->createModule('VWC');

        $filter = $this->createFilter(
            request: new Request(['module' => ['/api/modules/8']]),
            iriResolutions: ['/api/modules/8' => $module],
        );
        $queryBuilder = $this->createQueryBuilder();

        $filter->apply($queryBuilder, $this->createQueryNameGenerator(), Subscription::class);

        /** @var Andx $where */
        $where = $queryBuilder->getDQLPart('where');
        $orx = $where->getParts()[0];
        self::assertInstanceOf(Orx::class, $orx);
        self::assertCount(3, $orx->getParts());
        self::assertSame('/purchasing/vendor_warranty_claims/%', $queryBuilder->getParameter('module_p1')->getValue());
        self::assertSame('/purchasing/ncr_vendor_warranty_claims/%', $queryBuilder->getParameter('module_p2')->getValue());
        self::assertSame('/purchasing/wc_vendor_warranty_claims/%', $queryBuilder->getParameter('module_p3')->getValue());
    }

    public function testFilterIgnoresModuleWithUnknownName(): void
    {
        $module = $this->createModule('UNKNOWN');

        $filter = $this->createFilter(
            request: new Request(['module' => ['/api/modules/99']]),
            iriResolutions: ['/api/modules/99' => $module],
        );
        $queryBuilder = $this->createQueryBuilder();

        $filter->apply($queryBuilder, $this->createQueryNameGenerator(), Subscription::class);

        self::assertNull($queryBuilder->getDQLPart('where'));
    }

    public function testFilterIgnoresUnresolvableIri(): void
    {
        $iriConverter = $this->prophesize(IriConverterInterface::class);
        $iriConverter->getResourceFromIri('/api/modules/404')->willThrow(new \RuntimeException('not found'));

        $requestStack = $this->prophesize(RequestStack::class);
        $requestStack->getCurrentRequest()->willReturn(new Request(['module' => ['/api/modules/404']]));

        $filter = new SubscriptionModuleFilter($iriConverter->reveal(), $requestStack->reveal());
        $queryBuilder = $this->createQueryBuilder();

        $filter->apply($queryBuilder, $this->createQueryNameGenerator(), Subscription::class);

        self::assertNull($queryBuilder->getDQLPart('where'));
    }

    public function testGetDescription(): void
    {
        $filter = new SubscriptionModuleFilter(
            $this->prophesize(IriConverterInterface::class)->reveal(),
            $this->prophesize(RequestStack::class)->reveal(),
        );

        self::assertSame([
            'module[]' => [
                'property' => 'module',
                'type' => 'string',
                'required' => false,
                'is_collection' => true,
            ],
        ], $filter->getDescription(Subscription::class));
    }

    /**
     * @param array<string, Module> $iriResolutions
     */
    private function createFilter(Request $request, array $iriResolutions = []): SubscriptionModuleFilter
    {
        $iriConverter = $this->prophesize(IriConverterInterface::class);
        foreach ($iriResolutions as $iri => $module) {
            $iriConverter->getResourceFromIri($iri)->willReturn($module);
        }

        $requestStack = $this->prophesize(RequestStack::class);
        $requestStack->getCurrentRequest()->willReturn($request);

        return new SubscriptionModuleFilter($iriConverter->reveal(), $requestStack->reveal());
    }

    private function createModule(string $name): Module
    {
        $module = new Module();
        $module->setName($name);

        return $module;
    }

    private function createQueryBuilder(): QueryBuilder
    {
        $em = $this->prophesize(EntityManagerInterface::class);
        $em->getExpressionBuilder()->willReturn(new Expr());

        $qb = new QueryBuilder($em->reveal());
        $qb->from(Subscription::class, 'o');

        return $qb;
    }

    private function createQueryNameGenerator(): QueryNameGeneratorInterface
    {
        $generator = $this->createMock(QueryNameGeneratorInterface::class);
        $counter = 0;
        $generator->method('generateParameterName')->willReturnCallback(static function () use (&$counter) {
            return 'module_p'.++$counter;
        });

        return $generator;
    }
}
