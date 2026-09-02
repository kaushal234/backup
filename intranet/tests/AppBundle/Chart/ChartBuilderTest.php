<?php

declare(strict_types=1);

namespace Tests\AppBundle\Chart;

use AppBundle\Chart\ChartBuilder;
use PHPUnit\Framework\TestCase;

class ChartBuilderTest extends TestCase
{
    public function testTitleTheChartTypeCanBeSet()
    {
        $config = (new ChartBuilder('column'))->buildConfig();

        self::assertArrayHasKey('chart', $config);
        self::assertArrayHasKey('type', $config['chart']);

        self::assertSame('column', $config['chart']['type'], 'The chart type was not found into the config');
    }

    public function testTitleIsIntoTheConfig()
    {
        $title = 'This is a test';
        $config = (new ChartBuilder('line'))
            ->setTitle($title)
            ->buildConfig();

        self::assertArrayHasKey('title', $config);
        self::assertArrayHasKey('text', $config['title']);

        self::assertSame($title, $config['title']['text'], 'The title was not found into the config');
    }

    public function testMethodAddPlotCreateASerieAndACategory()
    {
        $config = (new ChartBuilder('line'))
            ->addPlot('B', 'Season 1', 12)
            ->buildConfig();

        self::assertCount(1, $config['series']);

        self::assertContains(
            [
                'name' => 'B',
                'data' => [['y' => 12]],
            ],
            $config['series']
        );

        self::assertSame(['Season 1'], $config['xAxis']['categories']);
    }

    public function testMethodAddPlotCanAddOption()
    {
        $config = (new ChartBuilder('line'))
            ->addPlot('B', 'Season 1', 12, ['options' => ['color' => '#7cb5ec']])
            ->buildConfig();

        self::assertCount(1, $config['series']);

        self::assertContains(
            [
                'name' => 'B',
                'data' => [['y' => 12]],
                'color' => '#7cb5ec',
            ],
            $config['series']
        );

        self::assertSame(['Season 1'], $config['xAxis']['categories']);
    }

    public function testMethodAddPlotMergePointMetadata()
    {
        $config = (new ChartBuilder('line'))
            ->addPlot('B', 'Season 1', 12, [], ['foo' => 'bar', 'baz' => ['buz' => true, 'fuz' => 17]])
            ->buildConfig();

        self::assertCount(1, $config['series']);

        self::assertContains(
            [
                'name' => 'B',
                'data' => [['y' => 12, 'foo' => 'bar', 'baz' => ['buz' => true, 'fuz' => 17]]],
            ],
            $config['series']
        );

        self::assertSame(['Season 1'], $config['xAxis']['categories']);
    }

    public function testMethodAddPlotDoesNotCreateDuplicates()
    {
        $config = (new ChartBuilder('line'))
            ->addPlot('B', 'Season 1', 12)
            ->addPlot('B', 'Season 1', 18)
            ->buildConfig();

        self::assertCount(1, $config['series']);

        self::assertContains(
            [
                'name' => 'B',
                'data' => [['y' => 18]],
            ],
            $config['series']
        );

        self::assertSame(['Season 1'], $config['xAxis']['categories']);
    }

    public function testMethodAddPlotCreateMultipleSeriesAndACategory()
    {
        $config = (new ChartBuilder('line'))
            ->addPlot('A', 'Season 1', 12)
            ->addPlot('B', 'Season 2', 42)
            ->buildConfig();

        self::assertCount(2, $config['series']);

        self::assertContains(
            [
                'name' => 'A',
                'data' => [['y' => 12]],
            ],
            $config['series']
        );

        self::assertContains(
            [
                'name' => 'B',
                'data' => [['y' => 42]],
            ],
            $config['series']
        );

        self::assertContains('Season 1', $config['xAxis']['categories']);
        self::assertContains('Season 2', $config['xAxis']['categories']);
    }

    public function testMethodRemapXValuesDoesRemapWell()
    {
        $config = (new ChartBuilder('line'))->addPlot('X', 'A', 1)
            ->addPlot('X', 'B', 2)
            ->addPlot('Y', 'E', 5)
            ->addPlot('Y', 'G', 12)
            ->reMapXValues(range('A', 'H'))
            ->buildConfig()
        ;

        self::assertCount(2, $config['series']);
        self::assertContains(
            [
                'name' => 'X',
                'data' => [['y' => 1], ['y' => 2], ['y' => null], ['y' => null], ['y' => null], ['y' => null], ['y' => null], ['y' => null]],
            ], $config['series']
        );
        self::assertContains(
            [
                'name' => 'Y',
                'data' => [['y' => null], ['y' => null], ['y' => null], ['y' => null], ['y' => 5], ['y' => null], ['y' => 12], ['y' => null]],
            ],
            $config['series']
        );

        self::assertSame(range('A', 'H'), $config['xAxis']['categories']);
    }

    public function testDisableLegend()
    {
        $config = (new ChartBuilder('line'))->disableLegend()->buildConfig();
        self::assertFalse($config['legend']['enabled']);
    }

    public function testSetStackedDefaultValue()
    {
        $builder = (new ChartBuilder('column'))
            ->addPlotOptions(['states' => ['inactive' => ['enabled' => false]]]);
        $config = $builder->buildConfig();
        self::assertSame(['inactive' => ['enabled' => false]], $config['plotOptions']['column']['states']);
        $builder->setStacked();
        $config = $builder->buildConfig();
        self::assertFalse($config['yAxis']['stackLabels']['enabled']);
        self::assertSame('normal', $config['plotOptions']['column']['stacking']);

        $builder->setStacked('percent', true);
        $config = $builder->buildConfig();
        self::assertSame(['inactive' => ['enabled' => false]], $config['plotOptions']['column']['states']);
        self::assertTrue($config['yAxis']['stackLabels']['enabled']);
        self::assertSame('percent', $config['plotOptions']['column']['stacking']);
    }

    public function testSetStackedWithOption()
    {
        $config = (new ChartBuilder('column'))
            ->setStacked('percent', true)
            ->buildConfig();

        self::assertTrue($config['yAxis']['stackLabels']['enabled']);
        self::assertSame('percent', $config['plotOptions']['column']['stacking']);
    }

    public function testAddToolTipOption()
    {
        $builder = new ChartBuilder('column');
        $config = $builder->addToolTipOption(['foo' => ['enable' => true]])->buildConfig();

        self::assertSame(['foo' => ['enable' => true]], $config['tooltip']);

        $config = $builder->addToolTipOption(['formatter' => true])->buildConfig();
        self::assertSame(['foo' => ['enable' => true], 'formatter' => true], $config['tooltip']);

        $config = $builder->shareTooltip()->buildConfig();
        self::assertSame(['foo' => ['enable' => true], 'formatter' => true, 'shared' => true], $config['tooltip']);
    }

    public function testAddChartOptions()
    {
        $config = (new ChartBuilder('column'))
            ->addChartOptions(['zoomType' => 'x'])
            ->addChartOptions(['showAxes' => true])
            ->addChartOptions(['zoomType' => 'xy'])
            ->buildConfig();

        self::assertSame(['type' => 'column', 'zoomType' => 'xy', 'showAxes' => true], $config['chart']);
    }

    public function testAddYAxis()
    {
        $config = (new ChartBuilder('column'))
            ->addYAxis()
            ->buildConfig();

        self::assertCount(1, $config['yAxis']);
        self::assertSame(['title' => ['text' => null]], $config['yAxis'][0]);

        $config = (new ChartBuilder('column'))
            ->addYAxis('My Title', ['min' => 0, 'max' => 100])
            ->buildConfig();

        self::assertCount(1, $config['yAxis']);
        self::assertSame(['min' => 0, 'max' => 100, 'title' => ['text' => 'My Title']], $config['yAxis'][0]);

        $config = (new ChartBuilder('column'))
            ->addYAxis('first')
            ->addYAxis('second', ['opposite' => true])
            ->buildConfig();

        self::assertCount(2, $config['yAxis']);
        self::assertSame(['opposite' => true, 'title' => ['text' => 'second']], $config['yAxis'][1]);
    }

    public function testPieChart()
    {
        $config = (new ChartBuilder('pie'))
            ->addPlot('Value', 'Season 1', 12, [], ['name' => 'Season 1'])
            ->addPlot('Value', 'Season 2', 42, [], ['name' => 'Season 2'])
            ->buildConfig();

        self::assertCount(1, $config['series']);
        self::assertSame([
            'name' => 'Value',
            'data' => [['y' => 12, 'name' => 'Season 1'], ['y' => 42, 'name' => 'Season 2']],
        ],
            $config['series'][0]
        );
    }
}
