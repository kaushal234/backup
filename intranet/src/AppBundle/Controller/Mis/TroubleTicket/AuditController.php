<?php

declare(strict_types=1);

namespace AppBundle\Controller\Mis\TroubleTicket;

use ApiBundle\Client;
use AppBundle\Chart\ChartBuilderFactory;
use AppBundle\Filters\Type\Mis\TroubleTicketAuditFilterType;
use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpClient\Exception\ClientException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

#[AsController]
#[Route(path: '/mis/trouble-tickets', defaults: ['alvest_module' => 'TTS', 'moduleDomain' => 'trouble_ticket'])]
class AuditController extends AbstractController
{
    public function __construct(
        private readonly Client $client,
        private readonly TranslatorInterface $translator,
        private readonly ChartBuilderFactory $chartBuilderFactory,
    ) {
    }

    #[Route(path: '/audit', name: 'mis_audit', defaults: ['label' => 'trouble_ticket.button.audit'], methods: 'GET|POST')]
    #[Template('mis/trouble_ticket/audit.html.twig')]
    public function __invoke(Request $request): array
    {
        $form = $this->createForm(TroubleTicketAuditFilterType::class, null, [
            'action' => $this->generateUrl('mis_audit'),
            'method' => Request::METHOD_GET,
        ]);

        $form->handleRequest($request);

        $data = null;
        $chart = null;
        $payload = null;
        $chartTroubleTicketsByMonth = null;
        $chartTroubleTicketsClosed = null;
        if ($form->isSubmitted() && $form->isValid()) {
            $formData = $form->getData();
            $filters = [...$formData, 'auditType' => 'trouble_ticket', 'property' => 'status'];

            $fieldMap = [
                'indiceFactor' => 'indiceFactor',
                'module' => 'module',
                'type.type' => 'type',
                'module.application' => 'application',
                'createdBy.businessUnit.location' => 'location',
                'createdBy.premise.supportTeam' => 'supportTeam',
                'createdBy.businessUnit.region' => 'region',
                'createdBy.businessUnit.region.subDivision' => 'subDivision',
                'createdBy.businessUnit.region.subDivision.division' => 'division',
            ];

            foreach ($fieldMap as $formKey => $payloadKey) {
                if (null !== $formData[$formKey]) {
                    $payload[$payloadKey] = $formData[$formKey];
                }
            }

            if (null !== $formData['createdAt']['after']) {
                $payload['after'] = $formData['createdAt']['after'];
            }

            if (null !== $formData['createdAt']['before']) {
                $payload['before'] = $formData['createdAt']['before'];
            }

            if (null === $filters['createdAt']['after'] && null === $filters['createdAt']['before']) {
                $this->addFlash('error', $this->translator->trans('trouble_ticket.errors.filter_missing', [], 'trouble_ticket'));

                return [
                    'chart' => null,
                    'form' => $form->createView(),
                    'data' => $data,
                ];
            }

            try {
                $results = $this->client->get('audit_logs', [
                    'query' => $filters,
                ]);
                $resultsByMonth = $this->client->get('audit_logs/by_month', [
                    'query' => $filters,
                ]);
            } catch (ClientException $exception) {
                $this->addFlash('error', $exception->getMessage());

                return [
                    'form' => $form->createView(),
                    'data' => $data,
                    'chart' => null,
                ];
            }

            $troubleTicketsByMonthData = $this->client->get('/reports/resource=/mis/trouble_tickets;x=status;y=createdMonth',
                ['query' => ['options' => $payload]]
            );

            $chartTroubleTicketsByMonth = $this->chartBuilderFactory
                ->getColumnChartBuilder()
                ->setTitle($this->translator->trans('trouble_ticket.audit.by_month', [], 'trouble_ticket'))
            ;
            foreach ($troubleTicketsByMonthData['rows'] as $data) {
                foreach ($data as $coordinates) {
                    $chartTroubleTicketsByMonth->addPlot(
                        $coordinates['y'],
                        $coordinates['x'],
                        $coordinates['value']
                    );
                }
            }

            $troubleTicketsClosedData = $this->client->get('/reports/resource=/mis/trouble_tickets;x=closedStatus;y=closedMonth',
                ['query' => ['options' => $payload]]
            );

            $chartTroubleTicketsClosed = $this->chartBuilderFactory
                ->getColumnChartBuilder()
                ->setTitle($this->translator->trans('trouble_ticket.audit.closed', [], 'trouble_ticket'))
            ;

            foreach ($troubleTicketsClosedData['rows'] as $data) {
                foreach ($data as $coordinates) {
                    $chartTroubleTicketsClosed->addPlot(
                        $coordinates['y'],
                        $coordinates['x'],
                        $coordinates['value']
                    );
                }
            }

            $closedStatuses = ['SOLVED', 'NOT AN ISSUE', 'ALREADY RAISED', 'NOT APPROVED'];
            $statusMap = [
                'PENDING' => 'pending',
                'IN PROGRESS' => 'implementation',
                'AWAITING USER' => 'awaiting_user',
                'MOO/GKU AWAITING USER' => 'awaiting_user',
                'SOLUTION PROPOSED' => 'solution_proposed',
                'MOO/GKU SOLUTION PROPOSED' => 'solution_proposed',
                'PENDING MOO/GKU' => 'validate',
            ];

            $data = ['pending' => 0, 'implementation' => 0, 'awaiting_user' => 0, 'solution_proposed' => 0, 'validate' => 0, 'total' => 0];
            foreach ($results['hydra:member'] as $result) {
                if (\in_array($result['value'], $closedStatuses, true)) {
                    continue;
                }

                $days = $result['time'] / 3600 / 24;

                if (isset($statusMap[$result['value']])) {
                    $data[$statusMap[$result['value']]] += $days;
                }

                $data['total'] += $days;
            }

            $chart = $this->chartBuilderFactory
                ->getColumnChartBuilder()
                ->addYAxis('Time')
                ->addPlotOptions(['stacking' => 'normal', 'states' => ['inactive' => ['enabled' => false]]])
                ->setTitle($this->translator->trans('trouble_ticket.audit.kpi', [], 'trouble_ticket'))
            ;

            $dataFormattedByMonth = [];
            foreach ($resultsByMonth['hydra:member'] as $resultByMonth) {
                if (\in_array($resultByMonth['value'], $closedStatuses, true)) {
                    continue;
                }

                $translationKey = $statusMap[$resultByMonth['value']] ?? null;
                $days = $resultByMonth['time'] / 3600 / 24;
                $key = \sprintf('%s-%s', $translationKey, $resultByMonth['month']);

                if (!isset($dataFormattedByMonth[$key])) {
                    $dataFormattedByMonth[$key] = [
                        'time' => $days,
                        'month' => $resultByMonth['month'],
                        'value' => $translationKey,
                    ];

                    continue;
                }

                $dataFormattedByMonth[$key]['time'] += $days;
            }

            foreach ($dataFormattedByMonth as $chartData) {
                $chart->addPlot($this->translator->trans(\sprintf('trouble_ticket.audit.%s', $chartData['value']), [], 'trouble_ticket'), $chartData['month'], round($chartData['time'], 2));
            }
        }

        return [
            'form' => $form->createView(),
            'data' => $data,
            'chart' => $chart?->buildConfig() ?? null,
            'chartTroubleTicketsByMonth' => $chartTroubleTicketsByMonth?->buildConfig(false),
            'chartTroubleTicketsClosed' => $chartTroubleTicketsClosed?->buildConfig(false),
        ];
    }
}
