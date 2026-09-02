<?php

declare(strict_types=1);

namespace Alvest\TwigHelper\Twig\Extension;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

use function array_key_exists;

class MatrixExtension extends AbstractExtension
{
    public function getFunctions(): array
    {
        return [
            new TwigFunction('matrix', [$this, 'matrix']),
        ];
    }

    /**
     * @param array<string, mixed> $data
     * @param array<string, mixed> $columnHeaders
     * @param array<string, mixed> $lineHeaders
     *
     * @return array<string, mixed>
     */
    public function matrix(array $data, array $columnHeaders, array $lineHeaders): array
    {
        if (!$lineHeaders && !$columnHeaders) {
            return $data;
        }

        $newData = [
            'xTotals' => [],
            'yTotals' => [],
            'total' => 0,
            'rows' => null,
        ];

        if (!$lineHeaders) {
            $lineHeaders = array_keys($data['xTotals']);
        }
        if (!$columnHeaders) {
            $columnHeaders = array_keys($data['yTotals']);
        }

        $newData['xTotals'] = array_fill_keys($lineHeaders, 0);
        $newData['yTotals'] = array_fill_keys($columnHeaders, 0);

        $newData['rows'] = [];
        foreach ($lineHeaders as $xLabel) {
            if (!array_key_exists($xLabel, $data['rows'])) {
                continue;
            }

            foreach ($columnHeaders as $yLabel) {
                $newData['rows'][$xLabel][$yLabel] = $data['rows'][$xLabel][$yLabel] ?? ['x' => $xLabel, 'y' => $yLabel, 'value' => 0];
                if (isset($data['rows'][$xLabel][$yLabel]['value'])) {
                    $newData['xTotals'][$xLabel] += $data['rows'][$xLabel][$yLabel]['value'];
                    $newData['yTotals'][$yLabel] += $data['rows'][$xLabel][$yLabel]['value'];
                    $newData['total'] += $data['rows'][$xLabel][$yLabel]['value'];
                }
            }
        }

        return $newData;
    }

    /**
     * {@inheritdoc}
     */
    public function getName(): string
    {
        return 'matrix';
    }
}
