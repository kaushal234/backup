<?php

declare(strict_types=1);

namespace AppBundle\Chart\Materials\Warehouse;

use AppBundle\Chart\ChartBuilder;
use AppBundle\Chart\ChartBuilderFactory;
use Symfony\Contracts\Translation\TranslatorInterface;

class MonthlyActivitiesChartsProvider
{
    final public const TARGETS = [
        'inboundAverageRate' => 10,
        'outboundAverageRate' => 35,
        'productiveAverageRatio' => 85,
    ];

    private const COLORS = [
        'improductive' => '#7cb5ec', // blue
        'excluded' => '#434348', // black
        'inbound' => '#90ed7d', // green
        'outbound' => '#f7a35c', // orange
    ];

    private ?ChartBuilder $activityChartBuilder = null;

    private ?ChartBuilder $productivityChartBuilder = null;

    private ?ChartBuilder $fullTimeEquivalentChartBuilder = null;

    private array $aggregates = [];

    private readonly ChartBuilderFactory $chartBuilderFactory;

    private readonly TranslatorInterface $translator;

    public function __construct(ChartBuilderFactory $chartBuilderFactory, TranslatorInterface $translator)
    {
        $this->chartBuilderFactory = $chartBuilderFactory;
        $this->translator = $translator;
    }

    public function initialize(array $data, array $parameters, ?int $fullTimeEquivalentMonthlyHours = null): self
    {
        if ([] === $data) {
            return $this;
        }

        $singleLocation = (bool) ($parameters['location'] ?? null);

        $this->initializeActivityChartBuilder($singleLocation);
        $this->initializeProductivityChartBuilder($singleLocation);

        if ($singleLocation) {
            $this->initializeFullTimeEquivalentChartBuilder();
        }

        foreach ($data as $activity) {
            if (!\array_key_exists($activity['location']['name'], $this->aggregates)) {
                $this->aggregates[$activity['location']['name']] = [
                    'inboundLines' => 0,
                    'outboundLines' => 0,
                    'inboundHours' => 0,
                    'outboundHours' => 0,
                    'improductiveHours' => 0,
                    'inboundAverageRate' => null,
                    'outboundAverageRate' => null,
                    'productiveAverageRatio' => null,
                ];
            }
            $this->activityChartBuilder
                ->addPlot(
                    \sprintf('%s (inbound)', $activity['location']['name']),
                    $activity['applicatedOn'],
                    $activity['inboundLines'],
                    ['options' => ['stack' => $activity['location']['name'], 'color' => $singleLocation ? self::COLORS['inbound'] : null]],
                )->addPlot(
                    \sprintf('%s (outbound)', $activity['location']['name']),
                    $activity['applicatedOn'],
                    $activity['outboundLines'],
                    ['options' => ['stack' => $activity['location']['name'], 'color' => $singleLocation ? self::COLORS['outbound'] : null]],
                );
            $this->productivityChartBuilder
                ->addPlot(
                    \sprintf('%s (inbound rate)', $activity['location']['name']),
                    $activity['applicatedOn'],
                    $activity['inboundRate'],
                    ['options' => ['color' => $singleLocation ? self::COLORS['inbound'] : null]],
                )->addPlot(
                    \sprintf('%s (outbound rate)', $activity['location']['name']),
                    $activity['applicatedOn'],
                    $activity['outboundRate'],
                    ['options' => ['color' => $singleLocation ? self::COLORS['outbound'] : null]],
                )->addPlot(
                    \sprintf('%s (productive ratio)', $activity['location']['name']),
                    $activity['applicatedOn'],
                    $activity['productiveRatio'],
                    ['options' => ['yAxis' => 1, 'color' => $singleLocation ? self::COLORS['improductive'] : null]],
                );

            if (null !== $this->fullTimeEquivalentChartBuilder) {
                $this->fullTimeEquivalentChartBuilder
                    ->addPlot(
                        \sprintf('%s (excluded)', $activity['location']['name']),
                        $activity['applicatedOn'],
                        round($activity['excludedHours'] / ($fullTimeEquivalentMonthlyHours ?? 1), 2),
                        ['options' => ['stack' => $activity['location']['name'], 'color' => self::COLORS['excluded']]]
                    )
                    ->addPlot(
                        \sprintf('%s (admin)', $activity['location']['name']),
                        $activity['applicatedOn'],
                        round($activity['improductiveHours'] / ($fullTimeEquivalentMonthlyHours ?? 1), 2),
                        ['options' => ['stack' => $activity['location']['name'], 'color' => self::COLORS['improductive']]]
                    )
                    ->addPlot(
                        \sprintf('%s (inbound)', $activity['location']['name']),
                        $activity['applicatedOn'],
                        round($activity['inboundHours'] / ($fullTimeEquivalentMonthlyHours ?? 1), 2),
                        ['options' => ['stack' => $activity['location']['name'], 'color' => self::COLORS['inbound']]],
                    )
                    ->addPlot(
                        \sprintf('%s (outbound)', $activity['location']['name']),
                        $activity['applicatedOn'],
                        round($activity['outboundHours'] / ($fullTimeEquivalentMonthlyHours ?? 1), 2),
                        ['options' => ['stack' => $activity['location']['name'], 'color' => self::COLORS['outbound']]]
                    );
            }

            $this->aggregates[$activity['location']['name']]['inboundLines'] += $activity['inboundLines'];
            $this->aggregates[$activity['location']['name']]['outboundLines'] += $activity['outboundLines'];
            $this->aggregates[$activity['location']['name']]['inboundHours'] += $activity['inboundHours'];
            $this->aggregates[$activity['location']['name']]['outboundHours'] += $activity['outboundHours'];
            $this->aggregates[$activity['location']['name']]['improductiveHours'] += $activity['improductiveHours'];
        }

        $period = new \DatePeriod(new \DateTime($parameters['applicatedOn']['after'].'-01'), new \DateInterval('P1M'), new \DateTime($parameters['applicatedOn']['before'].'-01'));
        $map = array_map([ChartBuilder::class, 'formatMonth'], iterator_to_array($period));

        foreach ($this->aggregates as $location => &$aggregate) {
            $aggregate['inboundAverageRate'] = $aggregate['inboundHours'] > 0 ? round($aggregate['inboundLines'] / $aggregate['inboundHours'], 1) : null;
            $aggregate['outboundAverageRate'] = $aggregate['outboundHours'] > 0 ? round($aggregate['outboundLines'] / $aggregate['outboundHours'], 1) : null;
            $aggregate['productiveAverageRatio'] = (0 < (int) $sum = ($aggregate['inboundHours'] + $aggregate['outboundHours'] + $aggregate['improductiveHours'])) ? (int) (($aggregate['inboundHours'] + $aggregate['outboundHours']) * 100 / $sum) : null;
        }

        $this->activityChartBuilder->reMapXValues($map);
        $this->productivityChartBuilder->reMapXValues($map);

        if (null !== $this->fullTimeEquivalentChartBuilder) {
            $this->fullTimeEquivalentChartBuilder->reMapXValues($map);
        }

        return $this;
    }

    public function getActivityChartConfig(): ?array
    {
        return null !== $this->activityChartBuilder ? $this->activityChartBuilder->buildConfig() : null;
    }

    public function getProductivityChartConfig(): ?array
    {
        return null !== $this->productivityChartBuilder ? $this->productivityChartBuilder->buildConfig() : null;
    }

    public function getFullTimeEquivalentChartConfig(): ?array
    {
        return null !== $this->fullTimeEquivalentChartBuilder ? $this->fullTimeEquivalentChartBuilder->buildConfig() : null;
    }

    public function getAggregates(): array
    {
        return $this->aggregates;
    }

    private function initializeActivityChartBuilder(bool $singleLocation): void
    {
        $this->activityChartBuilder = $this->chartBuilderFactory->getColumnChartBuilder()
            ->setTitle(ucwords($this->translator->trans('materials.monthly_activities.titles.activity', [], 'materials')))
            ->addYAxis($this->translator->trans('materials.monthly_activities.fields.lines', [], 'materials'))
            ->addPlotOptions(['stacking' => 'normal', 'states' => ['inactive' => ['enabled' => false]]])
            ->addChartOptions(['zoomType' => 'x'])
        ;
        if (!$singleLocation) {
            $this->activityChartBuilder->addPlotOptions(['dataLabels' => ['enabled' => false]]);
        }
    }

    private function initializeProductivityChartBuilder(bool $singleLocation): void
    {
        $this->productivityChartBuilder = $this->chartBuilderFactory->getLineChartBuilder()
            ->setTitle(ucwords($this->translator->trans('materials.monthly_activities.titles.productivity', [], 'materials')))
            ->addChartOptions(['zoomType' => 'x'])
            ->addYAxis($this->translator->trans('materials.monthly_activities.fields.rates_line_per_hour', [], 'materials'), ['min' => 0, 'plotLines' => [
                ['color' => $singleLocation ? self::COLORS['inbound'] : '#FF0000', 'width' => 1, 'value' => self::TARGETS['inboundAverageRate'], 'dashStyle' => 'dash'],
                ['color' => $singleLocation ? self::COLORS['outbound'] : '#FF0000', 'width' => 1, 'value' => self::TARGETS['outboundAverageRate'], 'dashStyle' => 'dash'],
            ]])
            ->addYAxis($this->translator->trans('materials.monthly_activities.fields.ratio_productive_hours_per_hours', [], 'materials'), ['min' => 0, 'opposite' => true, 'plotLines' => [
                ['color' => $singleLocation ? self::COLORS['improductive'] : '#FF0000', 'width' => 1, 'value' => self::TARGETS['productiveAverageRatio'], 'dashStyle' => 'dash'],
            ]])
        ;
    }

    private function initializeFullTimeEquivalentChartBuilder(): void
    {
        $this->fullTimeEquivalentChartBuilder = $this->chartBuilderFactory->getColumnChartBuilder()
            ->setTitle(ucwords($this->translator->trans('materials.monthly_activities.titles.full_time_equivalent', [], 'materials')))
            ->setStacked('normal', true)
            ->addPlotOptions(['states' => ['inactive' => ['enabled' => false]]])
            ->addChartOptions(['zoomType' => 'x']);
    }
}
