<?php

declare(strict_types=1);

namespace App\Controller;

use ApiPlatform\Metadata\Resource\Factory\ResourceMetadataCollectionFactoryInterface;
use App\Formatter\Snappy\FormatterInterface;
use App\ION\DataProvider\CachedIONItemDataProvider;
use App\ION\Dto\Procurement\Orders\PurchaseOrderPdfInput;
use App\ION\Resources\Procurement\Orders\PurchaseOrder;
use App\ION\Resources\Procurement\Orders\PurchaseOrderLine;
use Knp\Bundle\SnappyBundle\Snappy\Response\PdfResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class PdfController extends AbstractController
{
    public function __construct(
        private readonly FormatterInterface $pdfFormatter,
        private readonly ResourceMetadataCollectionFactoryInterface $resourceMetadataCollectionFactory,
        private readonly CachedIONItemDataProvider $itemDataProvider,
        private readonly Security $security
    ) {
    }

    public function __invoke(PurchaseOrderPdfInput $data, string $orderIdentifier, string $purpose, Request $request): Response
    {
        $metadata = $this->resourceMetadataCollectionFactory->create(PurchaseOrder::class);
        /** @var PurchaseOrder $purchaseOrder */
        $purchaseOrder = $this->itemDataProvider->provide($metadata->getOperation(), ['orderIdentifier' => $orderIdentifier]);

        if (!$this->security->isGranted('ACCESS_PEOPLE') && !$this->security->isGranted('BUSINESS_PARTNER_VOTER', $purchaseOrder)) {
            throw new AccessDeniedException();
        }

        // Set quantityLabel from input to purchase order line
        foreach ($data->getLines() as $purchaseOrderLineInput) {
            $line = $purchaseOrder->getLineByKey($purchaseOrderLineInput->lineIdentifier, $purchaseOrderLineInput->sequence);
            if (!$line instanceof PurchaseOrderLine) {
                throw new BadRequestException(\sprintf('Purchase Order Line with identifier `%s` and sequence `%s` is not found', $purchaseOrderLineInput->lineIdentifier, $purchaseOrderLineInput->sequence));
            }
            if (!$line->isConfirmable) {
                continue;
            }

            $line->quantityLabel = $purchaseOrderLineInput->quantityLabel ?? 0;
            $line->packingSlip = $purchaseOrderLineInput->packingSlip;
            $line->labelDeliveredQuantity = $purchaseOrderLineInput->labelDeliveredQuantity ?? 0;
        }

        // Remove line for each purchase order line which has no quantityLabel for printing label
        foreach ($purchaseOrder->getLines() as $line) {
            if (!$line->quantityLabel) {
                $purchaseOrder->removeLine($line);
            }
        }

        if (0 === \count($purchaseOrder->getLines())) {
            throw new BadRequestHttpException('No purchase order lines to print.');
        }

        $content = $this->pdfFormatter->convert($purchaseOrder, $purpose, 'pdf');

        return new PdfResponse($content, $purchaseOrder->getFileName(), 'application/pdf', 'inline');
    }
}
