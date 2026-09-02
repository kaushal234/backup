<?php

declare(strict_types=1);

namespace App\CQRS\CommandHandler\TechnicianOnCall;

use App\CQRS\Command\TechnicianOnCall\AddTechnicianOnCallCommentCommand;
use App\CQRS\CommandHandler\CommandHandlerInterface;
use App\Sdk\Client;
use App\Sdk\Resource\Comment;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler(bus: 'command.bus')]
readonly class AddTechnicianOnCallCommentCommandHandler implements CommandHandlerInterface
{
    public function __construct(
        private Client $client
    ) {
    }

    public function __invoke(AddTechnicianOnCallCommentCommand $command): void
    {
        $this->client->create(Comment::class, ['resource' => $command->iri, 'comment' => $command->message, 'file' => $command->file]);
    }
}
