<?php

declare(strict_types=1);

namespace AppBundle\Chart;

class ChartBuilder
{
    private array $chart;

    private array $series = [];

    private string $type;

    public function __construct(string $type)
    {
        $this->chart = [
            'chart' => [
                'type' => $type,
            ],
            'plotOptions' => [
                $type => [
                    'dataLabels' => [
                        'enabled' => true,
                    ],
                    'enableMouseTracking' => true,
                ],
            ],
            'series' => [],
            'credits' => [
                'enabled' => false,
                'text' => 'tld-group.com',
                'href' => 'https://www.tld-group.com',
            ],
        ];

        $this->type = $type;
    }

    public function disableLegend()
    {
        $this->chart['legend']['enabled'] = false;

        return $this;
    }

    public function setStacked(string $stackingType = 'normal', bool $withStackedLabels = false): self
    {
        $this->chart['yAxis']['stackLabels']['enabled'] = $withStackedLabels;
        $this->addPlotOptions(['stacking' => $stackingType]);

        return $this;
    }

    public function addYAxis(?string $title = null, array $options = []): self
    {
        $this->chart['yAxis'][] = $options + [
            'title' => [
                'text' => $title,
            ],
        ];

        return $this;
    }

    public function addPlot(string $serie, $xValue, $yValue, array $serieOptions = [], array $pointMetadata = []): self
    {
        if (!\array_key_exists($serie, $this->series)) {
            $this->series[$serie] = $serieOptions;
        }

        $this->series[$serie][$xValue] = array_merge(['y' => $yValue], $pointMetadata);

        return $this;
    }

    public function reMapXValues(array $xValues, $defaultValue = null): self
    {
        foreach ($this->series as $serie => &$series) {
            foreach ($xValues as $xValue) {
                if (!\array_key_exists($xValue, $series)) {
                    $series[$xValue] = ['y' => $defaultValue];
                }
            }
        }

        return $this;
    }

    public function shareTooltip(): self
    {
        $this->addToolTipOption(['shared' => true]);

        return $this;
    }

    /**
     * @see https://api.highcharts.com/highcharts/chart
     */
    public function addToolTipOption(array $options): self
    {
        $this->chart['tooltip'] = array_replace(
            $this->chart['tooltip'] ?? [],
            $options
        );

        return $this;
    }

    /**
     * @see https://api.highcharts.com/highcharts/chart
     */
    public function addChartOptions(array $options): self
    {
        $this->chart['chart'] = array_replace(
            $this->chart['chart'] ?? [],
            $options
        );

        return $this;
    }

    /**
     * @see https://api.highcharts.com/highcharts/plotOptions
     */
    public function addPlotOptions(array $options): self
    {
        $this->chart['plotOptions'][$this->type] = array_replace(
            $this->chart['plotOptions'][$this->type] ?? [],
            $options
        );

        return $this;
    }

    public function addXAxisOptions(string $serie, array $options): self
    {
        $this->series[$serie]['options'] = $options;

        return $this;
    }

    public function buildConfig(bool $sort = true): array
    {
        $series = [];
        foreach ($this->series as $serie => $plots) {
            if ($sort) {
                ksort($plots);
            }

            $series[$serie] = [
                'name' => $serie,
                'data' => [],
            ];

            if (isset($plots['options'])) {
                $series[$serie] = array_merge($series[$serie], $plots['options']);
                unset($plots['options']);
            }

            foreach ($plots as $xValue => $yValue) {
                $this->chart['xAxis']['categories'][] = (string) $xValue;
                $series[$serie]['data'][] = $yValue;
            }

            $this->chart['xAxis']['categories'] = array_unique($this->chart['xAxis']['categories'] ?? []);
        }

        $this->chart['series'] = array_values($series);

        return $this->chart;
    }

    public function setTitle($title): self
    {
        $this->chart['title']['text'] = $title;

        return $this;
    }

    public function setFormatter(string $formatter): self
    {
        $this->chart['plotOptions']['pie']['dataLabels'] = [
            ...$this->chart['plotOptions']['pie']['dataLabels'],
            'format' => $formatter,
        ];

        return $this;
    }

    public function setSubTitle($subtitle): self
    {
        $this->chart['subtitle']['text'] = $subtitle;

        return $this;
    }

    public static function formatMonth(\DateTimeInterface $dateTime): string
    {
        return $dateTime->format('Y-m');
    }
}
