<?php

declare(strict_types=1);

namespace App\Chart\Purchasing\SupplierRanking;

use App\Chart\ChartBuilder;
use App\Sdk\Resource\SupplierRanking\Notation;

class RankingRadarChartBuilder extends ChartBuilder
{
    /**
     * @var array<Notation>
     */
    protected array $notations;

    /**
     * @param array<Notation> $notations
     */
    public function __construct(array $notations)
    {
        parent::__construct('line');

        $this->notations = $notations;

        $this->addChartOptions([
            'polar' => true,
        ]);
        $this->addYAxis(null, [
            'gridLineInterpolation' => 'polygon',
            'min' => 0,
            'max' => 5,
        ]);
        $this->createNotation();
    }

    protected function createNotation(): void
    {
        foreach ($this->notations as $notation) {
            if (null !== $notation->notation && $notation->criteria->public) {
                $this->addPlot('Notation', $notation->criteria->name, $notation->notation, ['options' => ['color' => '#1A3AA5']]);
            }
        }
    }
}
