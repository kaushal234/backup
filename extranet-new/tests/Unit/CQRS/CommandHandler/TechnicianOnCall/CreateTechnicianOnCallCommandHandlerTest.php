<?php

declare(strict_types=1);

namespace App\Tests\Unit\CQRS\CommandHandler\TechnicianOnCall;

use App\CQRS\Command\TechnicianOnCall\CreateTechnicianOnCallCommand;
use App\CQRS\CommandHandler\TechnicianOnCall\CreateTechnicianOnCallCommandHandler;
use App\Sdk\Client;
use App\Sdk\Resource\TechnicianOnCall;
use PHPUnit\Framework\TestCase;

/**
 * @group unit
 */
final class CreateTechnicianOnCallCommandHandlerTest extends TestCase
{
    public function testInvocation(): void
    {
        $client = $this->createMock(Client::class);
        $client->expects($this->once())->method('create')->with(TechnicianOnCall::class, [
            'equipmentRecord' => '/equipment_record/42',
            'originalTitle' => 'title test',
            'originalDescription' => 'description test',
            'errorCodes' => '123',
            'serviceActivity' => '/service_activity/42',
            'unitOperationalStatus' => '/unit_operational_status/42',
            'mainContact' => '/user/42',
            'technicianOnCallType' => '/technician_on_call_type/42',
            'hourMeter' => 42,
            'airport' => '/airports/12',
        ]);

        $command = new CreateTechnicianOnCallCommand(
            originalTitle: 'title test',
            originalDescription: 'description test',
            serviceActivity: '/service_activity/42',
            unitOperationalStatus: '/unit_operational_status/42',
            mainContact: '/user/42',
            hourMeter: 42,
            airport: '/airports/12',
            equipmentRecord: '/equipment_record/42',
            errorCodes: '123',
            technicianOnCallType: '/technician_on_call_type/42',
        );
        $handler = new CreateTechnicianOnCallCommandHandler($client);
        $handler->__invoke($command);
    }
}
