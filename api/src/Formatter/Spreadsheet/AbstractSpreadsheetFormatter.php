<?php

declare(strict_types=1);

namespace App\Formatter\Spreadsheet;

abstract class AbstractSpreadsheetFormatter implements SpreadsheetFormatterInterface
{
    public function formatDate(\DateTimeInterface $dateTime): string
    {
        return $dateTime->format('Y-m-d');
    }

    public function getComputedColumns(): array
    {
        return [];
    }

    public function computeColumn(object $item, string $column): mixed
    {
        return null;
    }

    public function formatColumnName(string $columnName): string
    {
        $renamedColumnName = \array_key_exists($columnName, $this->getColumnToRename()) ? $this->getColumnToRename()[$columnName] : $columnName;
        $formattedColumnName = preg_replace('/([a-z])([A-Z])/', '$1 $2', $renamedColumnName);
        $formattedColumnName = str_replace(['_', '-'], ' ', $formattedColumnName);

        return ucwords(mb_trim($formattedColumnName));
    }

    protected function getColumnToRename(): array
    {
        return [];
    }
}
