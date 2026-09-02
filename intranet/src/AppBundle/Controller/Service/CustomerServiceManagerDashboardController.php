<?php

declare(strict_types=1);

namespace AppBundle\Controller\Service;

use ApiBundle\Client;
use AppBundle\Form\Type\EquipmentRecordSearchType;
use AppBundle\Form\Type\Parts\PartsSearchType;
use AppBundle\Form\Type\Service\CustomerServiceRecord\TechniciansType;
use AppBundle\Manager\SettingsManager;
use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route(path: '/service/dashboard')]
class CustomerServiceManagerDashboardController extends AbstractController
{
    public static function getSubscribedServices(): array
    {
        return array_merge(parent::getSubscribedServices(), [
            SettingsManager::class,
            Client::class,
        ]);
    }

    #[Route(path: '/dashboard-csm', name: 'csm_dashboard', methods: ['GET|POST'])]
    #[Template('service/csm_dashboard.html.twig')]
    public function home(Request $request)
    {
        if (!$this->isGranted('CSM_DASHBOARD')) {
            return $this->redirectToRoute('technician_on_call_search');
        }

        /** @var SettingsManager $settingsManager */
        $settingsManager = $this->container->get(SettingsManager::class);
        $users = $settingsManager->get('csr.interventions.leader');

        $techniciansForm = $this->createForm(TechniciansType::class, $users);
        $techniciansForm->handleRequest($request);

        if ($techniciansForm->isSubmitted() && $techniciansForm->isValid()) {
            $settingsManager->set('csr.interventions.leader', $techniciansForm->getData()['interventions.leader']);
            $users = $techniciansForm->getData()['interventions.leader'];
        }

        $equipmentRecordSearchForm = $this->createForm(EquipmentRecordSearchType::class);
        $equipmentRecordSearchForm->handleRequest($request);

        if ($equipmentRecordSearchForm->isSubmitted() && $equipmentRecordSearchForm->isValid()) {
            $data = $equipmentRecordSearchForm->get('equipmentRecord')->getData();

            /** @var Client $client */
            $client = $this->container->get(Client::class);
            $equipmentRecord = $client->get($data);

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
            'form' => $techniciansForm->createView(),
            'users' => $users,
            'equipmentRecordSearchForm' => $equipmentRecordSearchForm->createView(),
            'partsForm' => $partsForm->createView(),
        ];
    }
}
