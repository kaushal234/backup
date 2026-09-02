<?php

declare(strict_types=1);

namespace App\Tests\Unit\CQRS\QueryHandler\TechnicianOnCall;

use App\CQRS\Query\TechnicianOnCall\FindTechnicianOnCallQuery;
use App\CQRS\QueryHandler\TechnicianOnCall\FindTechnicianOnCallQueryHandler;
use App\Sdk\Client;
use App\Sdk\Resource\TechnicianOnCall;
use PHPUnit\Framework\TestCase;

/**
 * @group unit
 */
class FindTechnicianOnCallQueryHandlerTest extends TestCase
{
    public function test(): void
    {
        $client = $this->createMock(Client::class);
        $client->expects($this->once())->method('find')->with(TechnicianOnCall::class, [
            'resource_id' => 42,
        ]);

        $queryHandler = new FindTechnicianOnCallQueryHandler($client);
        $queryHandler->__invoke(new FindTechnicianOnCallQuery(42));
    }
}
