<?php

declare(strict_types=1);

namespace AppBundle\Controller\Service\Dashboard;

use ApiBundle\Client;
use ApiBundle\Model\User;
use AppBundle\DataPersister\Service\CustomerServiceRecordPersister;
use AppBundle\Manager\Service\CustomerServiceRecordManager;
use AppBundle\Manager\SettingsManager;
use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route(path: '/service/dashboard')]
class CustomerServiceRecordTeamPlannerController extends AbstractController
{
    public static function getSubscribedServices(): array
    {
        return array_merge(parent::getSubscribedServices(), [
            CustomerServiceRecordManager::class,
            TranslatorInterface::class,
            RequestStack::class,
            SettingsManager::class,
            Client::class,
        ]);
    }

    #[Route(path: '/customer-service-dashboard-team-planner', name: 'dashboard_csr_team_planner', methods: ['GET'])]
    #[Template('service/dashboard/csr_team_planer.html.twig')]
    public function home(#[CurrentUser] User $user)
    {
        /** @var TranslatorInterface $translator */
        $translator = $this->container->get(TranslatorInterface::class);

        if (!$this->isGranted('CSM_DASHBOARD')) {
            return $this->render('service/dashboard/widget_not_available.html.twig', [
                'title' => $translator->trans('csr.dashboard.title.planner', [], 'customer_service_record'),
            ]);
        }

        /** @var SettingsManager $settingsManager */
        $settingsManager = $this->container->get(SettingsManager::class);

        $technicians = $settingsManager->get('csr.interventions.leader') ?? [$user->getIriId()];

        /** @var Client $client */
        $client = $this->container->get(Client::class);
        $planned = $client->get(CustomerServiceRecordPersister::CUSTOMER_SERVICE_RECORD_URL, [
            'query' => [
                'interventions.status' => ['PENDING', 'STARTED'],
                'interventions.leader' => array_values($technicians),
                'order' => ['interventions.leader.lastname' => 'asc', 'airport.code' => 'asc'],
            ],
        ]);

        /** @var CustomerServiceRecordManager $customerServiceRecordManager */
        $customerServiceRecordManager = $this->container->get(CustomerServiceRecordManager::class);
        $customerServiceRecordByTechByWeek = $customerServiceRecordManager->getCustomerServiceRecordsByWeekByUserOrdered($planned['hydra:member'], $technicians);

        return [
            'teamMembers' => $technicians,
            'customerServiceRecordByTechByWeek' => $customerServiceRecordByTechByWeek,
        ];
    }
}
