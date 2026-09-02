<?php

declare(strict_types=1);

namespace App\Formatter\Spreadsheet\Sales;

use App\Entity\Sales\Competitor;
use App\Entity\Sales\ProductType;
use App\Formatter\Spreadsheet\AbstractSpreadsheetFormatter;

class CompetitorSpreadsheetFormatter extends AbstractSpreadsheetFormatter
{
    public function getComputedColumns(): array
    {
        return ['productTypes'];
    }

    /**
     * @param Competitor $item
     */
    public function computeColumn(object $item, string $column): mixed
    {
        return match ($column) {
            'productTypes' => implode(', ', array_map(static fn (ProductType $productType) => $productType->getEnglishName(), $item->getProductTypes()->toArray())),
            default => null,
        };
    }

    public function supports(string $class, string $operationName): bool
    {
        return Competitor::class === $class;
    }
}
