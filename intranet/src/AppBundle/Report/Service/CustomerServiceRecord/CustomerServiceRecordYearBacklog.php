<?php

declare(strict_types=1);

namespace AppBundle\Report\Service\CustomerServiceRecord;

use ApiBundle\Client;
use AppBundle\Chart\ChartBuilderFactory;
use Symfony\Contracts\Translation\TranslatorInterface;

class CustomerServiceRecordYearBacklog
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
            '/reports/resource=/service/customer_service_records;x=year;y=backlog',
            ['query' => ['options' => $parameters]]
        );

        $chartMonthBuilder = $this->chartBuilderFactory
            ->getColumnChartBuilder()
            ->setTitle($this->translator->trans('csr.report.year_backlog.title', [], 'customer_service_record'))
            ->setStacked('normal', true)
        ;

        foreach ($report['rows'] as $data) {
            foreach ($data as $coordinates) {
                $chartMonthBuilder->addPlot(
                    $coordinates['y'],
                    $coordinates['x'],
                    $coordinates['value']
                );
            }
        }

        return $chartMonthBuilder->buildConfig(false);
    }
}
