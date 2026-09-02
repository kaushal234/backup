<?php

declare(strict_types=1);

namespace App\Formatter\Spreadsheet\Sales;

use App\Entity\Sales\Order;
use App\Formatter\Spreadsheet\AbstractSpreadsheetFormatter;

class OrderSpreadsheetFormatter extends AbstractSpreadsheetFormatter
{
    public function getComputedColumns(): array
    {
        return ['baanOrderNumbers', 'customerPurchaseOrders'];
    }

    /**
     * @param Order $item
     */
    public function computeColumn(object $item, string $column): mixed
    {
        return match ($column) {
            'baanOrderNumbers' => implode(', ', $item->getBaanOrderNumbers()),
            'customerPurchaseOrders' => implode(', ', $item->getCustomerPurchaseOrders()),
            default => null,
        };
    }

    public function supports(string $class, string $operationName): bool
    {
        return Order::class === $class;
    }

    protected function getColumnToRename(): array
    {
        return [
            'sso.name' => 'SSO',
            'juridicalLocation.name' => 'Juridical location',
            'buyer.name' => 'Buyer',
            'endUser.name' => 'End user',
            'salesAgent.name' => 'Sales agent',
            'baanCustomerNumber' => 'CUNO',
            'equoteId' => 'eQuote#',
            'asm' => 'ASM',
        ];
    }
}
