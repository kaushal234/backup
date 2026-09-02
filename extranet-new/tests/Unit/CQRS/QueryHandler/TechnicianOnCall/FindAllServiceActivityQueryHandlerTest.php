<?php

declare(strict_types=1);

namespace App\Tests\Unit\CQRS\QueryHandler\TechnicianOnCall;

use App\CQRS\Query\TechnicianOnCall\FindAllServiceActivityQuery;
use App\CQRS\QueryHandler\TechnicianOnCall\FindAllServiceActivityQueryHandler;
use App\Sdk\Client;
use App\Sdk\Resource\ServiceActivity;
use PHPUnit\Framework\TestCase;
use Psl\Collection\AccessibleCollectionInterface;

/**
 * @group unit
 */
class FindAllServiceActivityQueryHandlerTest extends TestCase
{
    public function test(): void
    {
        $client = $this->createMock(Client::class);
        $collection = $this->createMock(AccessibleCollectionInterface::class);
        $client
            ->expects($this->once())
            ->method('findAll')
            ->with(ServiceActivity::class)
            ->willReturn($collection)
        ;

        $queryHandler = new FindAllServiceActivityQueryHandler($client);
        $queryHandler->__invoke(new FindAllServiceActivityQuery());
    }
}
