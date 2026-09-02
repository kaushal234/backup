<?php

declare(strict_types=1);

namespace AppBundle\Controller\Service\Dashboard;

use ApiBundle\Client;
use ApiBundle\Model\User;
use AppBundle\DataPersister\Service\TechnicianOnCallPersister;
use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

#[Route(path: '/service/dashboard')]
class TechnicianOnCallInSuspendedController extends AbstractController
{
    public static function getSubscribedServices(): array
    {
        return array_merge(parent::getSubscribedServices(), [
            Client::class,
        ]);
    }

    #[Route(path: '/technician-on-call-in-progress', name: 'dashboard_toc_in_progress', methods: ['GET'])]
    #[Template('service/dashboard/toc_suspended.html.twig')]
    public function home(#[CurrentUser] User $user, ?array $users = null)
    {
        /** @var Client $client */
        $client = $this->container->get(Client::class);
        $users = $users ?? [$user->iriId];

        $technicianOnCalls = $client->findBy(
            TechnicianOnCallPersister::RESOURCE_URL,
            [
                'actor' => $users,
                'status' => ['SUSPENDED'],
                'pagination' => false,
            ],
        );

        return [
            'technicianOnCalls' => $technicianOnCalls->all(),
            'users' => $users,
        ];
    }
}
