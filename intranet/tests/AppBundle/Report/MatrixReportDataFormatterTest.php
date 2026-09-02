<?php

declare(strict_types=1);

namespace App\Tests\AppBundle\Report;

use AppBundle\Report\MatrixReportDataFormatter;
use PHPUnit\Framework\TestCase;

class MatrixReportDataFormatterTest extends TestCase
{
    public function testMatrixDataTransformerCreateConformMatrix()
    {
        $inputData = [
            ['showValue' => 'value1', 'xSortProperty' => 'xSortProperty1', 'ySortProperty' => 'ySortProperty1'],
            ['showValue' => 'value2', 'xSortProperty' => 'xSortProperty2', 'ySortProperty' => 'ySortProperty2'],
        ];

        $matrix = MatrixReportDataFormatter::transform(
            $inputData,
            '[xSortProperty]',
            '[ySortProperty]',
            static function (array $value): array {
                return [$value['showValue']];
            });

        $expected = [
            'rows' => [
                'xSortProperty1' => [
                    'ySortProperty1' => ['value' => ['value1'], 'x' => 'xSortProperty1', 'y' => 'ySortProperty1'],
                    'ySortProperty2' => ['value' => null, 'x' => 'xSortProperty1', 'y' => 'ySortProperty2'],
                ],
                'xSortProperty2' => [
                    'ySortProperty1' => ['value' => null, 'x' => 'xSortProperty2', 'y' => 'ySortProperty1'],
                    'ySortProperty2' => ['value' => ['value2'], 'x' => 'xSortProperty2', 'y' => 'ySortProperty2'],
                ],
            ],
            'xTotals' => [],
            'yTotals' => [
                'ySortProperty1' => 1,
                'ySortProperty2' => 1,
            ],
        ];

        $this->assertSame($expected, $matrix);
    }
}
