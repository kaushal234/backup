<?php

declare(strict_types=1);

namespace App\Tests\Unit\CQRS\CommandHandler\Service;

use App\CQRS\Command\Service\TechnicianOnCallSatisfactionCommand;
use App\CQRS\CommandHandler\Service\TechnicianOnCallSatisfactionCommandHandler;
use App\Sdk\Client;
use App\Sdk\Resource\TechnicianOnCallSurvey;
use PHPUnit\Framework\TestCase;

class TechnicianOnCallSatisfactionCommandHandlerTest extends TestCase
{
    public function testInvocation(): void
    {
        $client = $this->createMock(Client::class);

        $client->expects($this->once())->method('create')->with(TechnicianOnCallSurvey::class, [
            'execution' => 2,
            'responsiveness' => 2,
            'communication' => 2,
            'attitude' => 2,
            'comment' => 'My comment',
            'technicianOnCall' => '/technician_on_calls/1',
            'token' => 'my_token',
        ]);

        $command = new TechnicianOnCallSatisfactionCommand(
            execution: 2,
            responsiveness: 2,
            communication: 2,
            attitude: 2,
            comment: 'My comment',
            technicianOnCall: '/technician_on_calls/1',
            token: 'my_token',
        );

        $handler = new TechnicianOnCallSatisfactionCommandHandler($client);
        $handler($command);
    }
}
