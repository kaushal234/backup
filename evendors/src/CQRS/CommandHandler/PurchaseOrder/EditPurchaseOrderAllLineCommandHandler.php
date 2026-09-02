<?php

declare(strict_types=1);

namespace App\CQRS\CommandHandler\PurchaseOrder;

use App\CQRS\Command\PurchaseOrder\EditPurchaseOrderAllLineCommand;
use App\CQRS\CommandHandler\CommandHandlerInterface;
use App\Sdk\ClientInterface;
use App\Sdk\Resource\PurchaseOrder;
use DateTimeInterface;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

final class EditPurchaseOrderAllLineCommandHandler implements CommandHandlerInterface
{
    public function __construct(
        private readonly ClientInterface $client,
    ) {
    }

    /**
     * @throws TransportExceptionInterface
     * @throws ServerExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws ClientExceptionInterface
     */
    public function __invoke(EditPurchaseOrderAllLineCommand $message): void
    {
        $data = [];
        foreach ($message->editAllPurchaseOrderLine->getEditLines() as $line) {
            if ($line->isConfirmable && $line->update) {
                $data['lines'][] = [
                    'confirmedSupplierDate' => $line->getConfirmedSupplierDate()->format(DateTimeInterface::ATOM),
                    'sequence' => $line->sequence,
                    'lineIdentifier' => $line->lineIdentifier,
                ];
            }
        }
        $data['editMessage'] = $message->editAllPurchaseOrderLine->message;
        $this->client->update(PurchaseOrder::class, identifier: ['iri' => $message->editAllPurchaseOrderLine->iri], update: $data);
    }
}
