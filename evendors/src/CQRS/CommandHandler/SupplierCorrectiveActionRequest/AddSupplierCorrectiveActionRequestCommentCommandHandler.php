<?php

declare(strict_types=1);

namespace App\CQRS\CommandHandler\SupplierCorrectiveActionRequest;

use App\CQRS\Command\SupplierCorrectiveActionRequest\AddSupplierCorrectiveActionRequestCommentCommand;
use App\CQRS\CommandHandler\CommandHandlerInterface;
use App\Sdk\ClientInterface;
use App\Sdk\Resource\SupplierCorrectiveActionRequest;

class AddSupplierCorrectiveActionRequestCommentCommandHandler implements CommandHandlerInterface
{
    public function __construct(
        private readonly ClientInterface $client,
    ) {
    }

    public function __invoke(AddSupplierCorrectiveActionRequestCommentCommand $command): void
    {
        $this->client->update(SupplierCorrectiveActionRequest::class, identifier: ['iri' => $command->iri], update: [
            'comment' => $command->message,
            'file' => $command->file,
        ]);
    }
}
