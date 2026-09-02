<?php

declare(strict_types=1);

namespace AppBundle\Controller\Service\CustomerServiceRecord;

use ApiBundle\Client;
use AppBundle\Filters\Type\Service\PlannerFilterType;
use AppBundle\Manager\Service\CustomerServiceRecordManager;
use AppBundle\Manager\SettingsManager;
use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route(path: '/service/customer-service-records', defaults: ['alvest_module' => 'CSR', 'breadcrumb_label' => 'menu.planner.title', 'moduleDomain' => 'customer_service_record_planner'])]
class PlannerController extends AbstractController
{
    public static function getSubscribedServices(): array
    {
        return array_merge(parent::getSubscribedServices(), [
            Client::class,
            CustomerServiceRecordManager::class,
            SettingsManager::class,
        ]);
    }

    #[Route(path: '/planner', name: 'customer_service_record_planner_home', methods: ['GET', 'POST'])]
    #[Template('service/customer_service_record/team_planner.html.twig')]
    public function planner(Request $request)
    {
        /** @var SettingsManager $settingsManager */
        $settingsManager = $this->container->get(SettingsManager::class);

        $client = $this->container->get(Client::class);
        $me = $client->get('/me');
        $leaders = $settingsManager->get('csr.interventions.leader') ?? [];

        $businessUnit = $me['businessUnit']['location']['capability']['sso'] ? $me['businessUnit']['location']['@id'] : null;
        $data = ['interventions.leader' => $leaders, 'equipmentRecord.salesOrganisationService' => $businessUnit];

        $techniciansForm = $this->createForm(PlannerFilterType::class,
            $data,
            ['method' => Request::METHOD_GET, 'csrf_protection' => false]
        );

        $techniciansForm->handleRequest($request);
        if ($techniciansForm->isSubmitted() && $techniciansForm->isValid()) {
            $settingsManager->set('csr.interventions.leader', array_filter($techniciansForm->get('technicians')->getData()));
            $data = $techniciansForm->getData();
        }

        // Custom filter ?
        $selectedTechnicians = $data['interventions.leader'] ?? [];
        $planned = $this->container->get(Client::class)->get(CustomerServiceRecordController::CUSTOMER_SERVICE_RECORD_URL, [
            'query' => ['status' => ['ASSIGNED', 'IN-PROGRESS'], 'technicianPlanned' => $selectedTechnicians],
        ]);

        unset($data['interventions.leader']);

        // The "additional" technicians widget (TomSelect) only resolves option labels on the JS side,
        // so the server-rendered planner tabs would show blank buttons. Resolve the labels here.
        $technicianLabels = [];
        if ($selectedTechnicians) {
            $people = $client->get('/people', [
                'query' => [
                    'pagination' => false,
                    'id' => array_map(static fn ($iri) => basename((string) $iri), $selectedTechnicians),
                    'normalization_groups_override' => ['people_list'],
                ],
            ]);
            foreach ($people['hydra:member'] as $person) {
                $technicianLabels[$person['@id']] = \sprintf('%s %s', $person['lastname'], $person['firstname']);
            }
        }

        $pool = $this->container->get(Client::class)->get(CustomerServiceRecordController::CUSTOMER_SERVICE_RECORD_URL, [
            'query' => [
                'pagination' => false,
                'status' => ['PENDING', 'PLANNED'],
                ...$data,
            ],
        ]);

        $customerServiceRecordManager = $this->container->get(CustomerServiceRecordManager::class);

        return [
            'pool' => json_encode($pool['hydra:member'], \JSON_THROW_ON_ERROR | \JSON_HEX_QUOT),
            'techniciansForm' => $techniciansForm->createView(),
            'technicianLabels' => $technicianLabels,
            ...$customerServiceRecordManager->orderCustomerServicesRecords(array_merge($pool['hydra:member'], $planned['hydra:member'])),
        ];
    }
}
