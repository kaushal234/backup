<?php

declare(strict_types=1);

namespace App\Formatter\Spreadsheet\Sales;

use App\Entity\Sales\SalesForecast;
use App\Formatter\Spreadsheet\AbstractSpreadsheetFormatter;

class SalesForecastSpreadsheetFormatter extends AbstractSpreadsheetFormatter
{
    public function getComputedColumns(): array
    {
        return ['totalSuccessPercentage', 'lastComment'];
    }

    /**
     * @param SalesForecast $item
     */
    public function computeColumn(object $item, string $column): mixed
    {
        return match ($column) {
            'totalSuccessPercentage' => $item->getCustomerSuccessPercentage() * $item->getSuccessPercentage() / 100,
            'lastComment' => preg_replace('#\s\s+#', ' ', $item->getLastComment() ?? ''),
            default => null,
        };
    }

    public function supports(string $class, string $operationName): bool
    {
        return SalesForecast::class === $class;
    }

    protected function getColumnToRename(): array
    {
        return [
            'sso.currency' => 'sso currency',
        ];
    }
}
