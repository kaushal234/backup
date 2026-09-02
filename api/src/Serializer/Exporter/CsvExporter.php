<?php

declare(strict_types=1);

namespace App\Serializer\Exporter;

use Alvest\FeatureDoc\Attribute\FeatureDoc;
use App\Formatter\Spreadsheet\SpreadsheetFormatterInterface;
use Symfony\Component\DependencyInjection\Attribute\AutowireIterator;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;

#[FeatureDoc(path: 'export.md')]
final readonly class CsvExporter extends AbstractSpreadsheetExporter
{
    private const CSV_DELIMITER = ',';
    private const CSV_ENCLOSURE = '"';
    private const CSV_ESCAPE = '\\';

    /**
     * @param iterable<SpreadsheetFormatterInterface> $formatters
     */
    public function __construct(
        PropertyAccessorInterface $propertyAccessor,
        #[AutowireIterator(tag: 'spreadsheet.formatter')]
        iterable $formatters,
    ) {
        parent::__construct($propertyAccessor, $formatters);
    }

    public function export(string $class, iterable $data, array $columns, string $operationName = '', ?string $filename = null): StreamedResponse
    {
        return new StreamedResponse(function () use ($data, $columns, $class, $operationName) {
            $formatter = $this->getFormatter($class, $operationName);

            $handle = fopen('php://output', 'w');

            // UTF-8 BOM for Excel to correctly detect encoding
            fwrite($handle, "\xEF\xBB\xBF");

            fputcsv($handle, $this->buildHeader($columns, $formatter), self::CSV_DELIMITER, self::CSV_ENCLOSURE, self::CSV_ESCAPE);

            foreach ($data as $item) {
                fputcsv($handle, $this->buildRow($item, $columns, $formatter), self::CSV_DELIMITER, self::CSV_ENCLOSURE, self::CSV_ESCAPE);
            }

            fclose($handle);
        }, 200, [
            'Content-Type' => 'text/csv; charset=utf-8',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
        ]);
    }
}
