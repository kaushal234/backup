<?php

declare(strict_types=1);

namespace App\Tests\AppBundle\DataTable\Query;

use App\CQRS\QueryBusInterface;
use App\DataTable\Query\ApiProxyQuery;
use App\DataTable\Query\ApiProxyQueryFactory;
use App\Tests\Unit\DummyQuery;
use PHPUnit\Framework\TestCase;

/**
 * @group unit
 */
class ApiProxyQueryFactoryTest extends TestCase
{
    private ApiProxyQueryFactory $factory;

    protected function setUp(): void
    {
        $this->factory = new ApiProxyQueryFactory(
            $this->createStub(QueryBusInterface::class),
        );
    }

    public function testCreate(): void
    {
        $this->assertInstanceOf(ApiProxyQuery::class, $this->factory->create(new DummyQuery()));
    }

    public function testSupports(): void
    {
        $this->assertTrue($this->factory->supports(new DummyQuery()));
        $this->assertFalse($this->factory->supports([]));
    }
}
