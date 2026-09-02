<?php

declare(strict_types=1);

namespace AppBundle\Report\Service\TechnicianOnCall;

use ApiBundle\Client;
use AppBundle\Chart\ChartBuilderFactory;
use Symfony\Contracts\Translation\TranslatorInterface;

class TechnicianOnCallBacklog
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
            '/reports/resource=/service/technician_on_call/backlog_reports;x=month;y=backlog',
            ['query' => ['options' => $parameters]]
        );

        $chartMonthBuilder = $this->chartBuilderFactory
            ->getLineChartBuilder()
            ->setTitle($this->translator->trans('toc.report.backlog.title', [], 'technician_on_call'))
            ->addYAxis($this->translator->trans('toc.report.backlog.y', [], 'technician_on_call'), [
                'min' => 0,
            ])
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
