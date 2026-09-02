<?php

declare(strict_types=1);

namespace AppBundle\Report\Service\TechnicianOnCall;

use ApiBundle\Client;
use AppBundle\Chart\ChartBuilderFactory;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class TechnicianOnCallOldest
{
    public function __construct(
        private readonly Client $client,
        private readonly ChartBuilderFactory $chartBuilderFactory,
        private readonly TranslatorInterface $translator,
        private readonly RouterInterface $router,
    ) {
    }

    public function build(array $parameters): array
    {
        $monthReport = $this->client->get(
            '/reports/resource=/service/technician_on_calls_oldest_reports;x=month;y=oldest',
            ['query' => ['options' => $parameters]]
        );

        $chartMonthBuilder = $this->chartBuilderFactory
            ->getLineChartBuilder()
            ->setTitle($this->translator->trans('toc.report.oldest.title', [], 'technician_on_call'))
            ->setSubTitle($this->translator->trans('toc.report.oldest.subtitle', [], 'technician_on_call'))
            ->addYAxis($this->translator->trans('toc.report.oldest.y', [], 'technician_on_call'), [
                'min' => 0,
            ])
        ;

        foreach ($monthReport['rows'] as $data) {
            foreach ($data as $coordinates) {
                $yKey = $coordinates['y'];
                $yId = $monthReport['metadata']['yIris'][$yKey] ?? null;

                $chartMonthBuilder->addPlot(
                    $coordinates['y'],
                    $coordinates['x'],
                    $coordinates['value'],
                    [],
                    [
                        'url' => $this->router->generate('technician_on_calls_show', ['id' => $yId]),
                        'id' => $yId,
                    ],
                );
            }
        }

        return $chartMonthBuilder->buildConfig();
    }
}
