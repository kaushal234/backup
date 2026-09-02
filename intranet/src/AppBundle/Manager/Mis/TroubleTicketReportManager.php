<?php

declare(strict_types=1);

namespace AppBundle\Manager\Mis;

use ApiBundle\Client;
use AppBundle\Chart\ChartBuilderFactory;
use Symfony\Contracts\Translation\TranslatorInterface;

class TroubleTicketReportManager
{
    public function __construct(
        private readonly Client $client,
        private readonly TranslatorInterface $translator,
        private readonly ChartBuilderFactory $chartBuilderFactory,
    ) {
    }

    public function generateReport(
        array $options,
        $form,
        string $dateRange,
        string $titleKey,
        string $groupBy = 'day',
        bool $lastWeekOfMonth = false,
    ) {
        $params = [
            'resource' => '/mis/trouble_tickets',
            'x' => 'status',
            'y' => 'module.application.name',
            'createdAt' => ['after' => (new \DateTime($dateRange))->format('Y-m-d')],
        ];

        if (!empty($options['createdAt']['after'])) {
            $params['createdAt']['after'] = $options['createdAt']['after'];
        }
        if (!empty($options['createdAt']['before'])) {
            $params['createdAt']['before'] = $options['createdAt']['before'];
        }

        $snapshotReports = $this->client->findBy('report_snapshots', $params);

        $applicationsFiltered = [];
        foreach ($options['application'] as $applicationIri) {
            $application = $this->client->get($applicationIri);
            $applicationsFiltered[] = $application['name'];
        }

        $results = [];

        if ($lastWeekOfMonth && 'month' === $groupBy) {
            $groupedByMonth = [];
            foreach ($snapshotReports as $report) {
                $month = mb_substr($report['createdAt'], 0, 7);
                $week = mb_substr($report['createdAt'], 0, 10);

                if (!isset($groupedByMonth[$month]['lastWeek']) || $groupedByMonth[$month]['lastWeek'] < $week) {
                    $groupedByMonth[$month]['lastWeek'] = $week;
                }

                if (!isset($groupedByMonth[$month]['reports'])) {
                    $groupedByMonth[$month]['reports'] = [];
                }

                $groupedByMonth[$month]['reports'][] = $report;
            }

            foreach ($groupedByMonth as $month => $data) {
                $lastWeek = $data['lastWeek'];
                foreach ($data['reports'] as $report) {
                    if (!str_starts_with($report['createdAt'], $lastWeek)) {
                        continue;
                    }
                    $this->processReportRows($report['rows'], $applicationsFiltered, $results, $month);
                }
            }
        } else {
            foreach ($snapshotReports as $report) {
                $groupKey = 'month' === $groupBy ? mb_substr($report['createdAt'], 0, 7) : mb_substr($report['createdAt'], 0, 10);
                $this->processReportRows($report['rows'], $applicationsFiltered, $results, $groupKey);
            }
        }

        $chartBuilder = $this->chartBuilderFactory->getColumnChartBuilder()
            ->addPlotOptions(['stacking' => 'normal', 'states' => ['inactive' => ['enabled' => false]]])
            ->setTitle($this->translator->trans($titleKey, [], 'mis'));

        foreach ($results as $groupKey => $statuses) {
            foreach ($statuses as $status => $total) {
                $chartBuilder->addPlot(
                    $status,
                    $groupKey,
                    (int) $total
                );
            }
        }

        return $chartBuilder;
    }

    private function processReportRows(
        array $rows,
        array $applicationsFiltered,
        array &$results,
        string $groupKey,
    ): void {
        foreach ($rows as $status => $applications) {
            foreach ($applications as $application => $value) {
                if (!empty($applicationsFiltered) && !\in_array($application, $applicationsFiltered, true)) {
                    continue;
                }

                if (!isset($results[$groupKey][$status])) {
                    $results[$groupKey][$status] = 0;
                }

                $results[$groupKey][$status] += $value['value'];
            }
        }
    }
}
