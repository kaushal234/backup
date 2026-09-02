<?php

declare(strict_types=1);

namespace App\Formatter\Spreadsheet\Quality;

use App\Entity\Quality\SupplierCorrectiveActionRequest;
use App\Formatter\Spreadsheet\AbstractSpreadsheetFormatter;

class SupplierCorrectiveActionRequestSpreadsheetFormatter extends AbstractSpreadsheetFormatter
{
    public function getColumnToRename(): array
    {
        return [
            'factory.erp' => 'factory erp',
            'factory.name' => 'factory name',
            'iFactor' => 'indice factor',
        ];
    }

    public function supports(string $class, string $operationName): bool
    {
        return SupplierCorrectiveActionRequest::class === $class;
    }
}
