<?php

declare(strict_types=1);

namespace App\Tests\Serializer\Exporter;

use App\Formatter\Spreadsheet\SpreadsheetFormatterInterface;
use App\Serializer\Exporter\AbstractSpreadsheetExporter;
use Symfony\Component\HttpFoundation\StreamedResponse;

final readonly class StubSpreadsheetExporter extends AbstractSpreadsheetExporter
{
    public function export(string $class, iterable $data, array $columns, string $operationName = '', ?string $filename = null): StreamedResponse
    {
        return new StreamedResponse();
    }

    public function callBuildHeader(array $columns, ?SpreadsheetFormatterInterface $formatter): array
    {
        return $this->buildHeader($columns, $formatter);
    }

    public function callBuildRow(object $item, array $columns, ?SpreadsheetFormatterInterface $formatter): array
    {
        return $this->buildRow($item, $columns, $formatter);
    }
}
