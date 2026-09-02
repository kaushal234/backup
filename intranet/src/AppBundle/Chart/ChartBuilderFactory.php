<?php

declare(strict_types=1);

namespace AppBundle\Chart;

class ChartBuilderFactory
{
    /**
     * @return ChartBuilder
     */
    public function getLineChartBuilder()
    {
        return new ChartBuilder('line');
    }

    /**
     * @return ChartBuilder
     */
    public function getColumnChartBuilder()
    {
        return new ChartBuilder('column');
    }

    /**
     * @return ChartBuilder
     */
    public function getPieChartBuilder()
    {
        return new ChartBuilder('pie');
    }
}
