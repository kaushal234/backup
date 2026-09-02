<?php

declare(strict_types=1);

namespace AppBundle\Report\Service\TechnicianOnCall;

use ApiBundle\Client;
use AppBundle\Chart\ChartBuilderFactory;
use Symfony\Contracts\Translation\TranslatorInterface;

class NumberOpenAndClosedByMonth
{
    public function __construct(
        private readonly Client $client,
        private readonly ChartBuilderFactory $chartBuilderFactory,
        private readonly TranslatorInterface $translator,
    ) {
    }

    public function build(array $parameters): array
    {
        $monthReport = $this->client->get(
            '/reports/resource=/service/technician_on_calls;x=month;y=quantity',
            ['query' => ['options' => $parameters]]
        );

        $chartMonthBuilder = $this->chartBuilderFactory
            ->getColumnChartBuilder()
            ->setTitle($this->translator->trans('toc.report.quantity_by_month', [], 'technician_on_call'))
        ;
        foreach ($monthReport['rows'] as $data) {
            foreach ($data as $coordinates) {
                $chartMonthBuilder->addPlot(
                    $coordinates['y'],
                    $coordinates['x'],
                    $coordinates['value']
                );
            }
        }

        return $chartMonthBuilder->buildConfig();
    }
}
