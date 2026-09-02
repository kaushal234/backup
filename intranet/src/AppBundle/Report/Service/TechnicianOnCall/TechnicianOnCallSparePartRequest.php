<?php

declare(strict_types=1);

namespace AppBundle\Report\Service\TechnicianOnCall;

use ApiBundle\Client;
use AppBundle\Chart\ChartBuilderFactory;
use Symfony\Contracts\Translation\TranslatorInterface;

class TechnicianOnCallSparePartRequest
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
            'reports/resource=/service/technician_on_calls;x=date;y=sparePartRequest',
            ['query' => ['options' => $parameters]]
        );

        if (empty($parameters['salesServiceOrganisation'])) {
            $title = 'toc.report.spare_part_request.title';
        } else {
            $location = $this->client->get($parameters['salesServiceOrganisation']);
            $title = $location['name'];
        }

        $chartBuilder = $this->chartBuilderFactory
            ->getPieChartBuilder()
            ->setTitle($this->translator->trans($title, [], 'technician_on_call'))
            ->addYAxis('Number')
            ->disableLegend()
        ;

        foreach ($report['xTotals'] as $key => $value) {
            $key = $this->translator->trans(\sprintf('toc.report.spare_part_request.%s', mb_strtolower($key)), [], 'technician_on_call');
            $chartBuilder->addPlot(
                $this->translator->trans('toc.report.spare_part_request.y', [], 'technician_on_call'),
                $key,
                $value,
                [],
                ['name' => $key]
            );
        }

        return $chartBuilder->buildConfig();
    }
}
