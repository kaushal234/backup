<?php

declare(strict_types=1);

namespace App\Serializer\Exporter;

use Alvest\FeatureDoc\Attribute\FeatureDoc;
use App\Event\SpreadsheetGeneratedEvent;
use App\Formatter\Spreadsheet\SpreadsheetFormatterInterface;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\DependencyInjection\Attribute\AutowireIterator;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;

#[FeatureDoc(path: 'export.md')]
final readonly class ExcelExporter extends AbstractSpreadsheetExporter
{
    /**
     * @param iterable<SpreadsheetFormatterInterface> $formatters
     */
    public function __construct(
        PropertyAccessorInterface $propertyAccessor,
        #[AutowireIterator(tag: 'spreadsheet.formatter')]
        iterable $formatters,
        private EventDispatcherInterface $dispatcher,
    ) {
        parent::__construct($propertyAccessor, $formatters);
    }

    public function export(string $class, iterable $data, array $columns, string $operationName = '', ?string $filename = null): StreamedResponse
    {
        return new StreamedResponse(function () use ($data, $columns, $class, $operationName) {
            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();

            $formatter = $this->getFormatter($class, $operationName);

            $rows = [$this->buildHeader($columns, $formatter)];
            foreach ($data as $item) {
                $rows[] = $this->buildRow($item, $columns, $formatter);
            }

            $sheet->fromArray($rows);

            $this->dispatcher->dispatch(new SpreadsheetGeneratedEvent($spreadsheet, ['resource_class' => $class]));

            (new Xlsx($spreadsheet))->save('php://output');
        }, 200, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet; charset=utf-8',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
        ]);
    }

    protected function escapeQuotes(string $value): string
    {
        return str_replace('"', "''", $value);
    }

    protected function normalizeLineBreaks(string $value): string
    {
        return str_replace(["\r\n", "\r"], "\n", $value);
    }
}
