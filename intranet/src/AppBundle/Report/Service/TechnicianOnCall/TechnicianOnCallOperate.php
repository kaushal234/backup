<?php

declare(strict_types=1);

namespace AppBundle\Report\Service\TechnicianOnCall;

use ApiBundle\Client;
use AppBundle\Chart\ChartBuilderFactory;
use Symfony\Contracts\Translation\TranslatorInterface;

class TechnicianOnCallOperate
{
    public function __construct(
        private readonly Client $client,
        private readonly ChartBuilderFactory $chartBuilderFactory,
        private readonly TranslatorInterface $translator,
    ) {
    }

    public function build(array $parameters): array
    {
        $report = $this->client->get(
            'reports/resource=/service/technician_on_calls;x=date;y=operate',
            ['query' => ['options' => $parameters]]
        );

        if (empty($parameters['salesServiceOrganisation'])) {
            $title = 'toc.report.operate.title';
        } else {
            $location = $this->client->get($parameters['salesServiceOrganisation']);
            $title = $location['name'];
        }

        $chartBuilder = $this->chartBuilderFactory
            ->getPieChartBuilder()
            ->setTitle($this->translator->trans($title, [], 'technician_on_call'))
            ->addYAxis('Number')
            ->disableLegend()
            ->setFormatter('{point.y} {point.name} ({point.percentage:.1f}%)')
        ;

        foreach ($report['xTotals'] as $key => $value) {
            $key = $this->translator->trans(\sprintf('toc.report.operate.%s', mb_strtolower($key)), [], 'technician_on_call');
            $chartBuilder->addPlot(
                $this->translator->trans('toc.report.operate.y', [], 'technician_on_call'),
                $key,
                $value,
                [],
                ['name' => $key]
            );
        }

        return $chartBuilder->buildConfig();
    }
}
