<?php

declare(strict_types=1);

namespace App\Report;

use App\Report\DataProvider\ReportDataProvider;

class ReportDataExtractor
{
    public function extract(ReportDataProvider $provider): array
    {
        $results = $provider->provideData();
        $xLabels = $provider->provideXLabels() ?? array_map(static fn ($value) => ['x' => $value], array_unique(array_column($results, 'x')));
        $yLabels = $provider->provideYLabels() ?? array_map(static fn ($value) => ['y' => $value], array_unique(array_column($results, 'y')));

        $orderedExpectedPairs = [];

        foreach ($xLabels as $xLabel) {
            foreach ($yLabels as $yLabel) {
                $orderedExpectedPairs[] = [
                    'x' => $xLabel['x'],
                    'y' => $yLabel['y'],
                ];
            }
        }

        $i = 0;
        $total = \count($orderedExpectedPairs);
        $fullResults = [];

        foreach ($results as $result) {
            while (
                $i < $total
                && ($orderedExpectedPairs[$i]['x'] !== $result['x'] || $orderedExpectedPairs[$i]['y'] !== $result['y'])
            ) {
                ['x' => $x, 'y' => $y] = $orderedExpectedPairs[$i];
                $fullResults[] = [
                    'x' => $x,
                    'y' => $y,
                    'value' => 0,
                ];
                ++$i;
            }
            $fullResults[] = $result;
            ++$i;
        }

        // Finish to fill after the last result from the DB
        while ($i < $total) {
            ['x' => $x, 'y' => $y] = $orderedExpectedPairs[$i];
            $fullResults[] = [
                'x' => $x,
                'y' => $y,
                'value' => 0,
            ];
            ++$i;
        }

        return $fullResults;
    }
}
