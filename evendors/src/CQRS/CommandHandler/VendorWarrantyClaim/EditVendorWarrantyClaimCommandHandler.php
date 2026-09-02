<?php

declare(strict_types=1);

namespace App\CQRS\CommandHandler\VendorWarrantyClaim;

use App\CQRS\Command\VendorWarrantyClaim\EditVendorWarrantyClaimCommand;
use App\CQRS\CommandHandler\CommandHandlerInterface;
use App\Sdk\ClientInterface;
use App\Sdk\Resource\VendorWarrantyClaimInterface;

class EditVendorWarrantyClaimCommandHandler implements CommandHandlerInterface
{
    public function __construct(
        private readonly ClientInterface $client,
    ) {
    }

    public function __invoke(EditVendorWarrantyClaimCommand $command): void
    {
        $this->client->update(VendorWarrantyClaimInterface::class, identifier: ['iri' => $command->iri], update: [
            'supplierCreditAmount' => $command->supplierCreditAmount,
            'supplierShippingInstruction' => $command->supplierShippingInstruction,
            'accepted' => $command->accepted,
            'shipBackDefectivePart' => $command->shipBackDefectivePart,
            'supplierReturnMerchandiseAuthorization' => $command->supplierReturnMerchandiseAuthorization,
        ]);

        if (null !== $command->file) {
            $this->client->update(VendorWarrantyClaimInterface::class, identifier: ['iri' => $command->iri], update: [
                'file' => $command->file,
                'description' => $command->description,
            ]);
        }

        $this->client->update(VendorWarrantyClaimInterface::class, identifier: ['iri' => $command->iri], update: [
            'status' => '/purchasing/vendor_warranty_claim_statuses/4',
        ]);
    }
}
