<?php

declare(strict_types=1);

namespace App\Formatter\Spreadsheet\Support;

use App\Entity\EquipmentRecord;
use App\Formatter\Spreadsheet\AbstractSpreadsheetFormatter;
use LegacyBundle\Manager\SalesOrderLineManager;
use LegacyBundle\Manager\SalesOrderOptionManager;
use Symfony\Bundle\SecurityBundle\Security;

class EquipmentRecordSpreadsheetFormatter extends AbstractSpreadsheetFormatter
{
    private ?bool $isGranted = null;

    public function __construct(
        private readonly Security $security,
        private readonly SalesOrderOptionManager $salesOrderOptionManager,
        private readonly SalesOrderLineManager $salesOrderLineManager,
    ) {
    }

    public function getColumnToRename(): array
    {
        return [
            'order.customerPurchaseOrders' => 'customer po',
            'lastEquipmentShippingRecord.estimatedPickUpDate' => 'estimated pick up date',
            'orderFactory.requestedDeliveryDate' => 'requested delivery date',
            'orderFactory.factoryPromisedDeliveryDate' => 'factory promised delivery date',
            'orderFactory.orderLine.factory' => 'factory bu',
            'orderFactory.orderLine.legacyId' => 'sol',
            'orderFactory.orderLine.purchaseOrderAcceptedDate' => 'purchase order accepted date',
            'orderFactory.orderLine.inspection' => 'pre delivery inspection',
            'orderFactory.orderLine.incoterm.code' => 'incoterm',
            'orderFactory.orderLine.deliveredEarly' => 'early delivery',
            'orderFactory.orderLine.incotermLocation' => 'incoterm location',
            'orderTransaction.invoice' => 'invoice number',
            'orderFactory.commissioning' => 'commissioning',
            'orderFactory.orderLine.paymentTerms' => 'payment terms',
            'negotiatedTransferPrice' => 'negotiated transfer price',
            'currency' => 'negotiated tp currency',
            'airport.code' => 'airport code',
            'length' => 'length (in mm)',
            'height' => 'height (in mm)',
            'weight' => 'weight (in kg)',
            'width' => 'width (in mm)',
            'orderFactory.orderLine.deliveryPenalties' => 'delivery penalties',
            'orderFactory.orderLine.deliveryPenaltiesConditions' => 'delivery penalties conditions',
            'unitGrossSellingPrice' => 'unit gross selling price',
        ];
    }

    public function getComputedColumns(): array
    {
        return ['order.customerPurchaseOrders', 'negotiatedTransferPrice', 'currency', 'unitGrossSellingPrice'];
    }

    /**
     * @param EquipmentRecord $item
     */
    public function computeColumn(object $item, string $column): mixed
    {
        if (null === $this->isGranted) {
            $this->isGranted = $this->security->isGranted('FEATURE_NEGOTIATED_TRANSFER_PRICE_ODP_XLS');
        }
        $options = [];
        $unitGrossSellingPrice = null;
        if (null !== ($solLegacyId = $item->orderFactory?->orderLine->getLegacyId())) {
            $unitGrossSellingPrice = $this->salesOrderLineManager->getUnitGrossSellingPrice($solLegacyId);
            if ($this->isGranted) {
                $options = $this->salesOrderOptionManager->getTotalNegotiatedTransferPrices($solLegacyId);
            }
        }

        return match ($column) {
            'order.customerPurchaseOrders' => $item->getOrder() ? implode(', ', $item->getOrder()->getCustomerPurchaseOrders()) : null,
            'negotiatedTransferPrice' => $options['negotiatedTransferPrice'] ?? null,
            'currency' => $options['currency'] ?? null,
            'unitGrossSellingPrice' => $unitGrossSellingPrice,
            default => null,
        };
    }

    public function supports(string $class, string $operationName): bool
    {
        return EquipmentRecord::class === $class;
    }
}
