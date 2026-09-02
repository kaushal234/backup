<?php

declare(strict_types=1);

namespace AppBundle\Controller\Mis\TroubleTicket;

use ApiBundle\Client;
use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;

#[AsController]
#[Route(path: '/mis/trouble-tickets', defaults: ['alvest_module' => 'TTS', 'moduleDomain' => 'trouble_ticket'])]
readonly class MatrixController
{
    public function __construct(
        private Client $client,
    ) {
    }

    #[Route(path: '/matrix', name: 'mis_matrix', defaults: ['label' => 'trouble_ticket.button.matrix'], methods: 'GET')]
    #[Template('mis/trouble_ticket/matrix.html.twig')]
    public function __invoke(Request $request): array
    {
        $xParam = true !== ($byMisAssignee = (bool) $request->query->get('byMisAssignee')) ? 'createdBy.businessUnit.location.name' : 'misAssignee';
        $xParamMatrix = true !== $byMisAssignee ? 'location' : 'misAssignee';
        $translationKey = true !== $byMisAssignee ? 'by_business_unit' : 'by_mis_assignee';

        $report = $this->client->get(\sprintf('/reports/resource=/mis/trouble_tickets;y=status;x=%s', $xParam));

        $report['yTotals'] = [
            'AWAITING USER' => $report['yTotals']['AWAITING USER'] ?? 0,
            'MOO/GKU AWAITING USER' => $report['yTotals']['MOO/GKU AWAITING USER'] ?? 0,
            'PENDING' => $report['yTotals']['PENDING'] ?? 0,
            'PENDING MOO/GKU' => $report['yTotals']['PENDING MOO/GKU'] ?? 0,
            'IN PROGRESS' => $report['yTotals']['IN PROGRESS'] ?? 0,
            'SOLUTION PROPOSED' => $report['yTotals']['SOLUTION PROPOSED'] ?? 0,
        ];

        foreach ($report['rows'] as $key => &$row) {
            $row = [
                'AWAITING USER' => $row['AWAITING USER'] ?? ['@type' => 'ReportCell', 'x' => $key, 'y' => 'AWAITING USER', 'value' => 0],
                'MOO/GKU AWAITING USER' => $row['MOO/GKU AWAITING USER'] ?? ['@type' => 'ReportCell', 'x' => $key, 'y' => 'MOO/GKU AWAITING USER', 'value' => 0],
                'PENDING' => $row['PENDING'] ?? ['@type' => 'ReportCell', 'x' => $key, 'y' => 'PENDING', 'value' => 0],
                'PENDING MOO/GKU' => $row['PENDING MOO/GKU'] ?? ['@type' => 'ReportCell', 'x' => $key, 'y' => 'PENDING MOO/GKU', 'value' => 0],
                'IN PROGRESS' => $row['IN PROGRESS'] ?? ['@type' => 'ReportCell', 'x' => $key, 'y' => 'IN PROGRESS', 'value' => 0],
                'SOLUTION PROPOSED' => $row['SOLUTION PROPOSED'] ?? ['@type' => 'ReportCell', 'x' => $key, 'y' => 'SOLUTION PROPOSED', 'value' => 0],
            ];
        }

        return [
            'report' => $report,
            'xParamMatrix' => $xParamMatrix,
            'translationKey' => $translationKey,
        ];
    }
}
