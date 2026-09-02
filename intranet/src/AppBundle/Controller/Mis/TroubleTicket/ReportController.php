<?php

declare(strict_types=1);

namespace AppBundle\Controller\Mis\TroubleTicket;

use ApiBundle\Client;
use AppBundle\Chart\ChartBuilderFactory;
use AppBundle\Form\Type\Mis\TroubleTicket\MISReportType;
use AppBundle\Manager\Mis\TroubleTicketReportManager;
use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

#[AsController]
#[Route(path: '/mis/trouble-tickets', defaults: ['alvest_module' => 'TTS', 'moduleDomain' => 'trouble_ticket'])]
class ReportController extends AbstractController
{
    public function __construct(
        private readonly TroubleTicketReportManager $troubleTicketReportManager,
        private readonly TranslatorInterface $translator,
        private readonly Client $client,
        private readonly ChartBuilderFactory $chartBuilderFactory,
    ) {
    }

    #[Route(path: '/reports', name: 'mis_reports', defaults: ['label' => 'KPI'], methods: 'GET|POST')]
    #[Template('mis/trouble_ticket/reports.html.twig')]
    public function __invoke(Request $request): array
    {
        $form = $this->createForm(MISReportType::class);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $options = $form->getData();

            if ($options['createdAt']['after'] instanceof \DateTimeInterface) {
                $options['createdAt']['after'] = $options['createdAt']['after']->format('Y-m-d');
            }
            if ($options['createdAt']['before'] instanceof \DateTimeInterface) {
                $options['createdAt']['before'] = $options['createdAt']['before']->format('Y-m-d');
            }

            // Create month report
            $monthReport = $this->client->get(
                '/reports/resource=/mis/trouble_tickets;x=satisfaction;y=month',
                ['query' => ['options' => array_merge($options, [
                    'after' => null,
                    'before' => null,
                ])]]
            );
            $chartMonthBuilder = $this->chartBuilderFactory->getColumnChartBuilder()
                ->setTitle($this->translator->trans('mis.reports.satisfaction_by_month', [], 'mis'))
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

            // Create Week report
            $weekReport = $this->client->get(
                '/reports/resource=/mis/trouble_tickets;x=satisfaction;y=week',
                ['query' => ['options' => array_merge($options, [
                    'after' => null,
                    'before' => null,
                ])]]
            );
            $chartWeekBuilder = $this->chartBuilderFactory->getColumnChartBuilder()
                ->setTitle($this->translator->trans('mis.reports.satisfaction_by_week', [], 'mis'))
            ;
            $columns = [];
            for ($i = 11; $i > -1; --$i) {
                $columns[] = 'W-'.$i;
            }
            foreach ($columns as $column) {
                if (null !== ($weekReport['rows'][$column] ?? null)) {
                    foreach ($weekReport['rows'][$column] as $data) {
                        $chartWeekBuilder->addPlot(
                            $data['y'],
                            $data['x'],
                            $data['value']
                        );
                    }
                } else {
                    foreach (['Request', 'Incident'] as $y) {
                        $chartWeekBuilder->addPlot(
                            $y,
                            $column,
                            0
                        );
                    }
                }
            }

            $chartStatusByWeekBuilder = $this->troubleTicketReportManager->generateReport(
                $options,
                $form,
                '3 months ago',
                'mis.reports.status_by_week.title',
            );

            $chartStatusByMonthBuilder = $this->troubleTicketReportManager->generateReport(
                $options,
                $form,
                '12 months ago',
                'mis.reports.status_by_month.title',
                'month',
                true
            );

            // Create Type by Module report
            $typeByModuleReport = $this->client->get(
                '/reports/resource=/mis/trouble_tickets;x=module.name;y=type.type',
                ['query' => ['options' => array_merge($options, [
                    'after' => $options['createdAt']['after'] ?? null,
                    'before' => $options['createdAt']['before'] ?? null,
                ])]]
            );

            $chartIncidentByModuleBuilder = $this->chartBuilderFactory->getPieChartBuilder()
                ->setTitle($this->translator->trans('mis.reports.incident_by_module', [], 'mis'))
            ;
            if (isset($typeByModuleReport['rows'])) {
                foreach ($typeByModuleReport['rows'] as $moduleName => $types) {
                    if (isset($types['Incident'])) {
                        $chartIncidentByModuleBuilder->addPlot(
                            'Value',
                            $moduleName,
                            $types['Incident']['value'],
                            [],
                            ['name' => $moduleName]
                        );
                    }
                }
            }

            $chartRequestByModuleBuilder = $this->chartBuilderFactory->getPieChartBuilder()
                ->setTitle($this->translator->trans('mis.reports.request_by_module', [], 'mis'))
            ;
            if (isset($typeByModuleReport['rows'])) {
                foreach ($typeByModuleReport['rows'] as $moduleName => $types) {
                    if (isset($types['Request'])) {
                        $chartRequestByModuleBuilder->addPlot(
                            'Value',
                            $moduleName,
                            $types['Request']['value'],
                            [],
                            ['name' => $moduleName]
                        );
                    }
                }
            }

            // Create Top 10 modules stacked column chart
            $chartTop10ModulesBuilder = $this->chartBuilderFactory->getColumnChartBuilder()
                ->setTitle($this->translator->trans('mis.reports.top10_modules_by_type', [], 'mis'))
                ->setStacked('normal', true)
            ;
            if (isset($typeByModuleReport['rows'])) {
                $moduleTotals = [];
                foreach ($typeByModuleReport['rows'] as $moduleName => $types) {
                    $moduleTotals[$moduleName] = array_sum(array_column($types, 'value'));
                }
                arsort($moduleTotals);
                $top10Modules = \array_slice(array_keys($moduleTotals), 0, 10);

                foreach ($top10Modules as $moduleName) {
                    $types = $typeByModuleReport['rows'][$moduleName];
                    $chartTop10ModulesBuilder->addPlot('Incident', $moduleName, $types['Incident']['value'] ?? 0);
                    $chartTop10ModulesBuilder->addPlot('Request', $moduleName, $types['Request']['value'] ?? 0);
                }
            }

            return [
                'form' => $form->createView(),
                'chartMonth' => $chartMonthBuilder->buildConfig(),
                'chartWeek' => $chartWeekBuilder->buildConfig(false),
                'chartStatusByWeek' => $chartStatusByWeekBuilder->buildConfig(),
                'chartStatusByMonth' => $chartStatusByMonthBuilder->buildConfig(),
                'chartIncidentByModule' => $chartIncidentByModuleBuilder->buildConfig(),
                'chartRequestByModule' => $chartRequestByModuleBuilder->buildConfig(),
                'chartTop10Modules' => $chartTop10ModulesBuilder->buildConfig(false),
            ];
        }

        return [
            'form' => $form->createView(),
        ];
    }
}
