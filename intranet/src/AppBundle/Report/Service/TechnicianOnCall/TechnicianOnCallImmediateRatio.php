<?php

declare(strict_types=1);

namespace AppBundle\Report\Service\TechnicianOnCall;

use ApiBundle\Client;
use AppBundle\Chart\ChartBuilderFactory;
use Symfony\Contracts\Translation\TranslatorInterface;

class TechnicianOnCallImmediateRatio
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
            '/reports/resource=/service/technician_on_calls;x=month;y=tir',
            ['query' => ['options' => $parameters]]
        );

        $chartMonthBuilder = $this->chartBuilderFactory
            ->getLineChartBuilder()
            ->setTitle($this->translator->trans('toc.report.tir.title', [], 'technician_on_call'))
            ->setSubTitle($this->translator->trans('toc.report.tir.subtitle', [], 'technician_on_call'))
            ->addYAxis($this->translator->trans('toc.report.tir.y', [], 'technician_on_call'), [
                'min' => 0,
                'max' => 100,
                'plotLines' => [
                    ['value' => 60, 'color' => 'green', 'width' => 3],
                    ['value' => 40, 'color' => 'red', 'width' => 3],
                ],
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
