<?php

declare(strict_types=1);

namespace App\Serializer\Normalizer;

use App\Report\Report;
use Symfony\Component\Serializer\Exception\ExceptionInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class ReportNormalizer implements NormalizerInterface
{
    public function getSupportedTypes(?string $format): array
    {
        return [
            Report::class => true,
        ];
    }

    public function supportsNormalization($data, ?string $format = null, array $context = []): bool
    {
        return $data instanceof Report && 'csv' === $format;
    }

    /**
     * @param Report $object
     *
     * @throws ExceptionInterface
     */
    public function normalize($object, ?string $format = null, array $context = []): array
    {
        $normalizedData = [];
        $showXTotals = !($context['hideTotals'] ?? null) || !\in_array($context['hideTotals'], ['x', 'both'], true);
        $showYTotals = !($context['hideTotals'] ?? null) || !\in_array($context['hideTotals'], ['y', 'both'], true);

        $rows = $object->getRows();
        $metadata = $object->getMetadata();

        $lineHeaders = $context['headers']['lines'] ?? array_keys($rows);
        $columnHeaders = $context['headers']['columns'] ?? [];

        if (!\is_array($columnHeaders) || !\is_array($lineHeaders)) {
            throw new \InvalidArgumentException('CSV headers must be passed as arrays.');
        }

        if (!$columnHeaders && !($lineHeaders[0] ?? [])) {
            return $normalizedData;
        }

        $columnHeaders = $columnHeaders ?: array_keys($rows[$lineHeaders[0]] ?? []);
        $columnsTotal = array_fill_keys($columnHeaders, 0.0);

        foreach ($lineHeaders as $lineHeader) {
            $lineTotal = 0.0;
            $line = ['' => $lineHeader];
            foreach ($columnHeaders as $columnHeader) {
                $cell = $rows[$lineHeader][$columnHeader] ?? null;
                $line += [$columnHeader => ($value = null !== $cell ? $cell->getValue() : ($metadata[$lineHeader][$columnHeader] ?? 0.0))];
                $lineTotal += $value = null !== $cell ? $cell->getValue() : 0.0;
                $columnsTotal[$columnHeader] += $value;
            }
            if ($showXTotals) {
                $line += ['Totals' => $lineTotal];
            }
            $normalizedData[] = $line;
        }

        if ($showYTotals) {
            $line = ['' => 'Totals'] + $columnsTotal;
            if ($showXTotals) {
                $line += ['Totals' => (float) array_sum($columnsTotal)];
            }
            $normalizedData[] = $line;
        }

        return $normalizedData;
    }
}
