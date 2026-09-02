<?php

declare(strict_types=1);

namespace App\CQRS\CommandHandler\TechnicianOnCall;

use App\CQRS\Command\TechnicianOnCall\AddFileTechnicianOnCallCommand;
use App\CQRS\CommandHandler\CommandHandlerInterface;
use App\Sdk\Client;
use App\Sdk\Resource\TechnicianOnCall;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler(bus: 'command.bus')]
class AddFileTechnicianOnCallCommandHandler implements CommandHandlerInterface
{
    public function __construct(
        private readonly Client $client
    ) {
    }

    public function __invoke(AddFileTechnicianOnCallCommand $command): void
    {
        $this->client->upload(TechnicianOnCall::class, $command);
    }
}
