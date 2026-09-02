<?php

declare(strict_types=1);

namespace App\CQRS\CommandHandler\Service;

use App\CQRS\Command\Service\TechnicianOnCallSatisfactionCommand;
use App\CQRS\CommandHandler\CommandHandlerInterface;
use App\Sdk\Client;
use App\Sdk\Resource\TechnicianOnCallSurvey;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler(bus: 'command.bus')]
class TechnicianOnCallSatisfactionCommandHandler implements CommandHandlerInterface
{
    public function __construct(
        private readonly Client $client
    ) {
    }

    public function __invoke(TechnicianOnCallSatisfactionCommand $command): void
    {
        $this->client->create(TechnicianOnCallSurvey::class, [
            'execution' => $command->execution,
            'responsiveness' => $command->responsiveness,
            'communication' => $command->communication,
            'attitude' => $command->attitude,
            'comment' => $command->comment,
            'technicianOnCall' => $command->technicianOnCall,
            'token' => $command->token,
        ]);
    }
}
