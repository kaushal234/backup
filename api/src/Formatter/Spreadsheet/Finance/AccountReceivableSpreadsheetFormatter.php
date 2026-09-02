<?php

declare(strict_types=1);

namespace App\Formatter\Spreadsheet\Finance;

use App\Entity\Finance\AccountReceivable;
use App\Formatter\Spreadsheet\AbstractSpreadsheetFormatter;

class AccountReceivableSpreadsheetFormatter extends AbstractSpreadsheetFormatter
{
    public function getColumnToRename(): array
    {
        return [
            'customerErpReference.sso.erp' => 'erp',
            'customerErpReference.customerNumber' => 'pcust',
            'customerErpReference.customer' => 'customer',
            'customerErpReference.customer.mainSalesRepresentative.asm' => 'asm',
            'transactionTypeReference.transactionType' => 'transaction type',
            'invoiceRecord.expectedPaymentDate' => 'expected payment date',
            'invoiceRecord.revisedDueDate' => 'revised due date',
            'invoiceRecord.lastComment' => 'last comment',
        ];
    }

    public function supports(string $class, string $operationName): bool
    {
        return AccountReceivable::class === $class;
    }
}
