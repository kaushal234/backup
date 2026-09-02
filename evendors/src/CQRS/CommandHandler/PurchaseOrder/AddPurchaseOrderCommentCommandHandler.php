<?php

declare(strict_types=1);

namespace App\CQRS\CommandHandler\PurchaseOrder;

use App\CQRS\Command\PurchaseOrder\AddPurchaseOrderCommentCommand;
use App\CQRS\CommandHandler\CommandHandlerInterface;
use App\Sdk\ClientInterface;
use App\Sdk\Resource\PurchaseOrder;

final class AddPurchaseOrderCommentCommandHandler implements CommandHandlerInterface
{
    public function __construct(
        private readonly ClientInterface $client,
    ) {
    }

    public function __invoke(AddPurchaseOrderCommentCommand $command): void
    {
        $this->client->update(PurchaseOrder::class, identifier: ['iri' => $command->iri], update: [
            'message' => $command->message,
        ]);
    }
}
