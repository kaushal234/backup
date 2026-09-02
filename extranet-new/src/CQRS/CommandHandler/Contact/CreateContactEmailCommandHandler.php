<?php

declare(strict_types=1);

namespace App\CQRS\CommandHandler\Contact;

use App\CQRS\Command\Contact\ContactEmailCommand;
use App\CQRS\CommandHandler\CommandHandlerInterface;
use App\DataTransferObject\Contact\ContactEmail;
use App\Sdk\Client;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler(bus: 'command.bus')]
class CreateContactEmailCommandHandler implements CommandHandlerInterface
{
    public function __construct(
        private readonly Client $client
    ) {
    }

    public function __invoke(ContactEmailCommand $command): void
    {
        $this->client->create(ContactEmail::class, [
            'to' => $command->receiver,
            'message' => $command->message,
        ]);
    }
}
