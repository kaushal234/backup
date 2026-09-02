<?php

declare(strict_types=1);

namespace App\CQRS\CommandHandler\Equipment;

use App\CQRS\Command\Equipment\UpdateEquipmentCommand;
use App\CQRS\CommandHandler\CommandHandlerInterface;
use App\Sdk\Client;
use App\Sdk\Resource\EquipmentRecord;
use App\Sdk\Utils\IriToId;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler(bus: 'command.bus')]
class UpdateEquipmentCommandHandler implements CommandHandlerInterface
{
    public function __construct(
        private readonly Client $client
    ) {
    }

    public function __invoke(UpdateEquipmentCommand $command): void
    {
        $this->client->update(EquipmentRecord::class, ['resource_id' => IriToId::iriToId($command->updateEquipmentRecord->iri)], [
            '@id' => $command->updateEquipmentRecord->iri,
            'customerSerialNumber' => $command->updateEquipmentRecord->customerSerialNumber,
            'airport' => $command->updateEquipmentRecord->airport->iri,
        ]);
    }
}
