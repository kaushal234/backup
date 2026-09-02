<?php

declare(strict_types=1);

namespace AppBundle\Controller\Service;

use ApiBundle\Client;
use AppBundle\Form\Type\EquipmentRecordSearchType;
use AppBundle\Form\Type\Parts\PartsSearchType;
use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route(path: '/service/dashboard')]
class ASTDashboardController extends AbstractController
{
    public function __construct(
        private readonly Client $client,
    ) {
    }

    #[Route(path: '/dashboard-ast', name: 'ast_dashboard', methods: ['GET|POST'])]
    #[Template('service/ast_dashboard.html.twig')]
    public function home(Request $request)
    {
        if (!$this->isGranted('AST_DASHBOARD')) {
            return $this->redirectToRoute('technician_on_call_search');
        }

        $equipmentRecordSearchForm = $this->createForm(EquipmentRecordSearchType::class);
        $equipmentRecordSearchForm->handleRequest($request);

        if ($equipmentRecordSearchForm->isSubmitted() && $equipmentRecordSearchForm->isValid()) {
            $data = $equipmentRecordSearchForm->get('equipmentRecord')->getData();

            $equipmentRecord = $this->client->get($data);

            return $this->redirectToRoute('legacy_product_support', [
                'id' => $equipmentRecord['legacyId'],
                'm' => [
                    'equipment',
                    'view',
                ],
            ]);
        }

        $partsForm = $this->createForm(PartsSearchType::class);
        $partsForm->handleRequest($request);

        if ($partsForm->isSubmitted() && $partsForm->isValid()) {
            $part = $partsForm->get('part_number')->getData();

            return $this->redirectToRoute('parts_dashboard_view', ['partNumber' => $part]);
        }

        return [
            'equipmentRecordSearchForm' => $equipmentRecordSearchForm->createView(),
            'partsForm' => $partsForm->createView(),
        ];
    }
}
