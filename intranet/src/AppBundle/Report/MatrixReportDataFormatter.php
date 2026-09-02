<?php

declare(strict_types=1);

namespace AppBundle\Report;

use Symfony\Component\PropertyAccess\PropertyAccess;

class MatrixReportDataFormatter
{
    public static function transform(array $inputRows, string $xProperty, string $yProperty, callable $valueGenerator): array
    {
        $propertyAccessor = PropertyAccess::createPropertyAccessor();

        $data = ['rows' => [], 'xTotals' => [], 'yTotals' => []];
        $columns = [];
        $rows = [];
        foreach ($inputRows as $inputRow) {
            $rowName = $propertyAccessor->getValue($inputRow, $xProperty);
            $columnName = $propertyAccessor->getValue($inputRow, $yProperty);

            $data['rows'][$rowName][$columnName] = [
                'x' => $rowName,
                'y' => $columnName,
                'value' => $valueGenerator($inputRow),
            ];
            $data['yTotals'][$columnName] = 1;

            $rows[] = $rowName;
            $columns[] = $columnName;
        }

        foreach (array_unique($rows) as $rowName) {
            foreach (array_unique($columns) as $columnName) {
                if (null !== ($data['rows'][$rowName][$columnName] ?? null)) {
                    continue;
                }
                $data['rows'][$rowName][$columnName] = [
                    'x' => $rowName,
                    'y' => $columnName,
                    'value' => null,
                ];
            }
        }

        ksort($data['rows']);
        ksort($data['xTotals']);
        ksort($data['yTotals']);
        foreach ($data['rows'] as $rowKey => $row) {
            ksort($data['rows'][$rowKey]);
            foreach (array_keys($row) as $columnKey) {
                ksort($data['rows'][$rowKey][$columnKey]);
            }
        }

        return $data;
    }
}
