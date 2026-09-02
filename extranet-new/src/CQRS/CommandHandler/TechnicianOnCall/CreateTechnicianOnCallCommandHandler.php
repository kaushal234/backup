<?php

declare(strict_types=1);

namespace App\CQRS\CommandHandler\TechnicianOnCall;

use App\CQRS\Command\TechnicianOnCall\CreateTechnicianOnCallCommand;
use App\CQRS\CommandHandler\CommandHandlerInterface;
use App\Sdk\Client;
use App\Sdk\Resource\TechnicianOnCall;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler(bus: 'command.bus')]
readonly class CreateTechnicianOnCallCommandHandler implements CommandHandlerInterface
{
    public function __construct(
        private Client $client
    ) {
    }

    /**
     * @return array<array-key, mixed>
     */
    public function __invoke(CreateTechnicianOnCallCommand $command): array
    {
        return $this->client->create(TechnicianOnCall::class, [
            'equipmentRecord' => $command->equipmentRecord,
            'originalTitle' => $command->originalTitle,
            'originalDescription' => $command->originalDescription,
            'serviceActivity' => $command->serviceActivity,
            'unitOperationalStatus' => $command->unitOperationalStatus,
            'mainContact' => $command->mainContact,
            'technicianOnCallType' => $command->technicianOnCallType,
            'hourMeter' => $command->hourMeter,
            'airport' => $command->airport,
            'errorCodes' => $command->errorCodes,
        ]);
    }
}
