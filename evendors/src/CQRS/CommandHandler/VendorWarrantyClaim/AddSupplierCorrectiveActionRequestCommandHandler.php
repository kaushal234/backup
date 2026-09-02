<?php

declare(strict_types=1);

namespace App\CQRS\CommandHandler\VendorWarrantyClaim;

use App\CQRS\Command\VendorWarrantyClaim\AddSupplierCorrectiveActionRequestCommand;
use App\CQRS\CommandHandler\CommandHandlerInterface;
use App\Sdk\ClientInterface;
use App\Sdk\Resource\SupplierCorrectiveActionRequest;
use App\Security\Security;

use const JSON_THROW_ON_ERROR;

final class AddSupplierCorrectiveActionRequestCommandHandler implements CommandHandlerInterface
{
    public function __construct(
        private readonly ClientInterface $client,
        private readonly Security $security,
    ) {
    }

    public function __invoke(AddSupplierCorrectiveActionRequestCommand $command): void
    {
        $response = $this->client->update(SupplierCorrectiveActionRequest::class, identifier: ['iri' => $command->iri], update: [
            'issueOrigin' => $command->issueOrigin,
            'correctiveAction' => $command->correctiveAction,
            'description' => $command->description,
            'shortDescription' => $command->shortDescription,
            'vendorWarrantyClaim' => $command->vendorWarrantyClaim,
        ]);

        $user = $this->security->getAuthenticatedUser();

        if (null !== $command->comment) {
            $this->client->update(SupplierCorrectiveActionRequest::class, identifier: ['iri' => $response['@id']], update: [
                'comment' => $command->comment,
                'file' => $command->file,
                'description' => $command->fileDescription,
                'metadata' => json_encode(['lastname' => $user->getLastname(), 'firstname' => $user->getFirstname()], JSON_THROW_ON_ERROR),
            ]);
        }
    }
}
