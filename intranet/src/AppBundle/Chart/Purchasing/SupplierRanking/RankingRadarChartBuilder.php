<?php

declare(strict_types=1);

namespace AppBundle\Chart\Purchasing\SupplierRanking;

use AppBundle\Chart\ChartBuilder;

class RankingRadarChartBuilder extends ChartBuilder
{
    protected array $notations;

    protected array $thresholds;

    public function __construct(array $notations, array $thresholds)
    {
        parent::__construct('line');

        $this->notations = $notations;
        $this->thresholds = $thresholds;

        $this->addChartOptions([
            'polar' => true,
        ]);
        $this->addYAxis(null, [
            'gridLineInterpolation' => 'polygon',
            'min' => 0,
            'max' => 5,
        ]);
        $this->createThresholds();
        $this->createNotation();
    }

    /**
     * Create thresholds limit area.
     */
    protected function createThresholds(): void
    {
        foreach ($this->thresholds as $threshold) {
            foreach ($threshold->thresholdsCriterias as $thresholdsCriteria) {
                foreach ($this->notations as $notation) {
                    if ($notation['criteria']['id'] === $thresholdsCriteria['criteria']['id'] && null !== $notation['notation']) {
                        $this->addPlot(
                            $threshold->name,
                            $thresholdsCriteria['criteria']['name'],
                            $thresholdsCriteria['rankLimit'], [
                                'options' => [
                                    'type' => 'area',
                                    'color' => '#'.$threshold->classification['color'],
                                    'opacity' => '0.4',
                                ],
                            ]);
                    }
                }
            }
        }
    }

    protected function createNotation(): void
    {
        foreach ($this->notations as $notation) {
            if (null !== $notation['notation']) {
                $this->addPlot('Notation', $notation['criteria']['name'], $notation['notation'], ['options' => ['color' => '#1A3AA5']]);
            }
        }
    }
}
