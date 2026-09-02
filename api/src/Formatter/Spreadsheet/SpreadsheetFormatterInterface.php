<?php

declare(strict_types=1);

namespace App\Formatter\Spreadsheet;

use Alvest\FeatureDoc\Attribute\FeatureDoc;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[FeatureDoc(path: 'export.md')]
#[AutoconfigureTag('spreadsheet.formatter')]
interface SpreadsheetFormatterInterface
{
    public function formatDate(\DateTimeInterface $dateTime): string;

    public function formatColumnName(string $columnName): string;

    public function getComputedColumns(): array;

    public function computeColumn(object $item, string $column): mixed;

    public function supports(string $class, string $operationName): bool;
}
