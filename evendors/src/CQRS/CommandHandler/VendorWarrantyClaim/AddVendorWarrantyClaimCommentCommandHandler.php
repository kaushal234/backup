<?php

declare(strict_types=1);

namespace App\CQRS\CommandHandler\VendorWarrantyClaim;

use App\CQRS\Command\VendorWarrantyClaim\AddVendorWarrantyClaimCommentCommand;
use App\CQRS\CommandHandler\CommandHandlerInterface;
use App\Sdk\ClientInterface;
use App\Sdk\Resource\VendorWarrantyClaimInterface;
use App\Security\Security;

use const JSON_THROW_ON_ERROR;

final class AddVendorWarrantyClaimCommentCommandHandler implements CommandHandlerInterface
{
    public function __construct(
        private readonly ClientInterface $client,
        private readonly Security $security,
    ) {
    }

    public function __invoke(AddVendorWarrantyClaimCommentCommand $command): void
    {
        $user = $this->security->getAuthenticatedUser();
        $this->client->update(VendorWarrantyClaimInterface::class, identifier: ['iri' => $command->iri], update: [
            'comment' => $command->message,
            'file' => $command->file,
            'metadata' => json_encode(['email' => $user->getEmail(), 'lastname' => $user->getLastname(), 'firstname' => $user->getFirstname()], JSON_THROW_ON_ERROR),
        ]);
    }
}
