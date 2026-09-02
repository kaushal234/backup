<?php

declare(strict_types=1);

namespace AppBundle\Controller\Service\Dashboard;

use ApiBundle\Client;
use ApiBundle\Model\User;
use AppBundle\DataPersister\Service\CustomerServiceRecordPersister;
use AppBundle\Manager\Service\CustomerServiceRecordManager;
use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

#[Route(path: '/service/dashboard')]
class CustomerServiceRecordPlannerController extends AbstractController
{
    public static function getSubscribedServices(): array
    {
        return array_merge(parent::getSubscribedServices(), [
            Client::class,
            CustomerServiceRecordManager::class,
        ]);
    }

    #[Route(path: '/customer-service-dashboard-planner', name: 'dashboard_csr_planner', methods: ['GET'])]
    #[Template('service/dashboard/csr_planner.html.twig')]
    public function home(#[CurrentUser] User $user)
    {
        $client = $this->container->get(Client::class);

        if (!$this->isGranted('AST_DASHBOARD') && !$this->isGranted('ACL_ROLE_AST')) {
            return $this->render('service/dashboard/widget_not_available.html.twig', [
                'title' => 'CSR Planner',
            ]);
        }

        $userCustomerServiceRecords = $client->findBy(CustomerServiceRecordPersister::CUSTOMER_SERVICE_RECORD_URL, [
            'technicianPlanned' => [$user->iriId],
            'status' => ['ASSIGNED', 'IN-PROGRESS'],
            'pagination' => false,
        ]);
        $allCustomerServiceRecords = $userCustomerServiceRecords->all();

        if (empty($allCustomerServiceRecords)) {
            $this->addFlash('danger', 'No data Available (To be managed with security)');

            return $this->render('service/dashboard/widget_not_available.html.twig', [
                'title' => 'CSR Planner',
            ]);
        }

        $customerServiceRecordsOrdered = $this->container->get(CustomerServiceRecordManager::class)->getCustomerServiceRecordsByUserOrdered($allCustomerServiceRecords);

        return [
            'customerServiceRecordOrdered' => $customerServiceRecordsOrdered,
        ];
    }
}
