<?php

declare(strict_types=1);

namespace App\Tests\Unit\CQRS\QueryHandler\TechnicianOnCall;

use App\CQRS\Query\TechnicianOnCall\FindAllUnitOperationalStatusQuery;
use App\CQRS\QueryHandler\TechnicianOnCall\FindAllUnitOperationalStatusQueryHandler;
use App\Sdk\Client;
use App\Sdk\Resource\UnitOperationalStatus;
use PHPUnit\Framework\TestCase;
use Psl\Collection\AccessibleCollectionInterface;

/**
 * @group unit
 */
class FindAllUnitOperationalStatusQueryHandlerTest extends TestCase
{
    public function test(): void
    {
        $client = $this->createMock(Client::class);
        $collection = $this->createMock(AccessibleCollectionInterface::class);
        $client
            ->expects($this->once())
            ->method('findAll')
            ->with(UnitOperationalStatus::class)
            ->willReturn($collection)
        ;

        $queryHandler = new FindAllUnitOperationalStatusQueryHandler($client);
        $queryHandler->__invoke(new FindAllUnitOperationalStatusQuery());
    }
}
