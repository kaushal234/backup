<?php

declare(strict_types=1);

namespace App\Tests\Unit\CQRS\QueryHandler\TechnicianOnCall;

use App\CQRS\Query\TechnicianOnCall\FindAllTechnicianOnCallTypeQuery;
use App\CQRS\QueryHandler\TechnicianOnCall\FindAllTechnicianOnCallTypeQueryHandler;
use App\Sdk\Client;
use App\Sdk\Resource\TechnicianOnCallType;
use PHPUnit\Framework\TestCase;
use Psl\Collection\AccessibleCollectionInterface;

/**
 * @group unit
 */
class FindAllTechnicianOnCallTypeQueryHandlerTest extends TestCase
{
    public function test(): void
    {
        $client = $this->createMock(Client::class);
        $collection = $this->createMock(AccessibleCollectionInterface::class);
        $client
            ->expects($this->once())
            ->method('findAll')
            ->with(TechnicianOnCallType::class)
            ->willReturn($collection)
        ;

        $queryHandler = new FindAllTechnicianOnCallTypeQueryHandler($client);
        $queryHandler->__invoke(new FindAllTechnicianOnCallTypeQuery());
    }
}
