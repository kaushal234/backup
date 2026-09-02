<?php

declare(strict_types=1);

namespace App\Tests\AppBundle\DataTable\Query;

use ApiBundle\Client;
use ApiBundle\Http\CsvStreamedResponseFactory;
use ApiBundle\Http\FileStreamedResponseFactory;
use AppBundle\DataTable\Query\ApiProxyQuery;
use AppBundle\DataTable\Query\ApiProxyQueryFactory;
use PHPUnit\Framework\TestCase;

class ApiProxyQueryFactoryTest extends TestCase
{
    private ApiProxyQueryFactory $factory;

    protected function setUp(): void
    {
        $this->factory = new ApiProxyQueryFactory(
            $this->createStub(Client::class),
            $this->createStub(FileStreamedResponseFactory::class),
            $this->createStub(CsvStreamedResponseFactory::class)
        );
    }

    public function testCreate()
    {
        $this->assertInstanceOf(ApiProxyQuery::class, $this->factory->create(''));
    }

    public function testSupports()
    {
        $this->assertTrue($this->factory->supports('test'));
        $this->assertFalse($this->factory->supports([]));
    }
}
