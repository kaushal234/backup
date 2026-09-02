<?php

declare(strict_types=1);

namespace App\Controller\PurchaseOrder;

use App\CQRS\Query\PurchaseOrder\FindOnePurchaseOrderQuery;
use App\CQRS\QueryBusInterface;
use App\Http\Responder;
use App\Sdk\Downloader;
use App\Sdk\Resource\PurchaseOrder;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\Exception\HandlerFailedException;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/purchase-order')]
final class DownloadDrawingsAndDocumentsController
{
    public function __construct(
        private readonly QueryBusInterface $bus,
        private readonly Downloader $downloader,
        private readonly Responder $responder,
    ) {
    }

    #[Route('/download_drawings_and_documents/{id}/{erp}', name: 'purchase-order:download_drawings_and_documents', methods: [Request::METHOD_GET])]
    public function __invoke(int|string $id, ?int $erp = null): Response
    {
        $order = $this->bus->dispatch(new FindOnePurchaseOrderQuery($id, $erp));
        try {
            $response = $this->downloader->download(PurchaseOrder::class, ['iri' => $order->iri]);
            if (0 === $response->getFile()->getSize()) {
                $this->responder->flash('danger', 'file.empty.purchase_order');

                return $this->responder->route('purchase-order:show', [
                    'id' => $order->id,
                    'erp' => $order->erp,
                ]);
            }

            return $response;
        } catch (HandlerFailedException $exception) {
            $this->responder->flash('danger', array_values($exception->getWrappedExceptions())[0]->getMessage());

            return $this->responder->route('purchase-order:show', [
                'id' => $order->id,
                'erp' => $order->erp,
            ]);
        }
    }
}
