<?php

declare(strict_types=1);

namespace App\Serializer\Exporter;

use Alvest\FeatureDoc\Attribute\FeatureDoc;
use App\Formatter\Spreadsheet\SpreadsheetFormatterInterface;
use Doctrine\ORM\EntityNotFoundException;
use Doctrine\Persistence\Proxy;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;

/**
 * @param iterable<SpreadsheetFormatterInterface> $formatters
 */
#[FeatureDoc(path: 'export.md')]
abstract readonly class AbstractSpreadsheetExporter implements ExporterInterface
{
    public function __construct(
        protected PropertyAccessorInterface $propertyAccessor,
        protected iterable $formatters,
    ) {
    }

    abstract public function export(string $class, iterable $data, array $columns, string $operationName = '', ?string $filename = null): StreamedResponse;

    public function getFormatter(string $class, string $operationName = ''): ?SpreadsheetFormatterInterface
    {
        foreach ($this->formatters as $formatter) {
            if ($formatter->supports($class, $operationName)) {
                return $formatter;
            }
        }

        return null;
    }

    protected function buildHeader(array $columns, ?SpreadsheetFormatterInterface $formatter): array
    {
        return array_map(
            static fn ($column) => $formatter?->formatColumnName($column) ?? $column,
            $columns
        );
    }

    protected function buildRow(object $item, array $columns, ?SpreadsheetFormatterInterface $formatter): array
    {
        $line = [];
        foreach ($columns as $propertyPath) {
            $line[] = $this->formatValue(
                $this->getColumnValue($item, $propertyPath, $formatter),
                $formatter
            );
        }

        return $line;
    }

    protected function escapeQuotes(string $value): string
    {
        return $value;
    }

    protected function normalizeLineBreaks(string $value): string
    {
        return str_replace(["\r\n", "\r", "\n"], ' ', $value);
    }

    private function getColumnValue(object $item, string $propertyPath, ?SpreadsheetFormatterInterface $formatter = null): mixed
    {
        if (\in_array($propertyPath, $formatter?->getComputedColumns() ?? [], true)) {
            return $formatter->computeColumn($item, $propertyPath);
        }

        try {
            $value = $this->propertyAccessor->getValue($item, $propertyPath);

            // Force lazy-loading safely
            if ($value instanceof Proxy) {
                $value->__load();
            }

            return $value;
        } catch (EntityNotFoundException) {
            return null; // ignore soft-deleted entities
        } catch (\Throwable) {
            return null;
        }
    }

    private function formatValue(mixed $value, ?SpreadsheetFormatterInterface $formatter = null): ?string
    {
        if ($value instanceof \DateTimeInterface) {
            return $formatter?->formatDate($value) ?? $value->format('Y-m-d H:i:s');
        }
        if (\is_object($value)) {
            return method_exists($value, '__toString') ? (string) $value : null;
        }
        if (\is_bool($value)) {
            return $value ? 'Yes' : 'No';
        }
        if (\is_string($value)) {
            return $this->sanitize($value);
        }

        return null !== $value ? (string) $value : null;
    }

    private function sanitize(string $value): string
    {
        $value = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F]/', '', $value);
        $value = preg_replace('/[\x{00A0}\x{2000}-\x{200F}\x{2028}\x{2029}\x{FEFF}]/u', '', $value);
        $value = $this->normalizeLineBreaks($value);

        if (preg_match('/^[=+\-@]/', $value)) {
            $value = "'".$value;
        }

        $value = $this->escapeQuotes($value);

        if (mb_strlen($value) > 32767) {
            $value = mb_substr($value, 0, 32767);
        }

        return $value;
    }
}
