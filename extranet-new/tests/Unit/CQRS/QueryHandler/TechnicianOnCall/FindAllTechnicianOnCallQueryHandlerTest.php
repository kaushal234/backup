<?php

declare(strict_types=1);

namespace App\Tests\Unit\CQRS\QueryHandler\TechnicianOnCall;

use App\CQRS\Query\TechnicianOnCall\FindAllTechnicianOnCallQuery;
use App\CQRS\QueryHandler\TechnicianOnCall\FindAllTechnicianOnCallQueryHandler;
use App\Sdk\Client;
use App\Sdk\PageInterface;
use App\Sdk\Resource\TechnicianOnCall;
use PHPUnit\Framework\TestCase;

/**
 * @group unit
 */
class FindAllTechnicianOnCallQueryHandlerTest extends TestCase
{
    public function test(): void
    {
        $client = $this->createMock(Client::class);
        $page = $this->createMock(PageInterface::class);
        $client
            ->expects($this->once())
            ->method('paginate')
            ->with(TechnicianOnCall::class, 7, 42, ['foo' => 'bar'])
            ->willReturn($page)
        ;

        $queryHandler = new FindAllTechnicianOnCallQueryHandler($client);
        $queryHandler->__invoke(new FindAllTechnicianOnCallQuery(page: 7, itemsPerPage: 42, options: ['foo' => 'bar']));
    }
}
