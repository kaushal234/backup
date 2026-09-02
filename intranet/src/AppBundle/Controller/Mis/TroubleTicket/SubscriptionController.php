<?php

declare(strict_types=1);

namespace AppBundle\Controller\Mis\TroubleTicket;

use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;

#[AsController]
#[Route(path: '/mis/trouble-tickets', defaults: ['alvest_module' => 'TTS', 'moduleDomain' => 'trouble_ticket'])]
class SubscriptionController extends AbstractController
{
    #[Route(path: '/subscriptions', name: 'trouble_ticket_subscriptions', defaults: ['label' => 'trouble_ticket.button.subscriptions'], methods: 'GET|POST')]
    #[Template('mis/trouble_ticket/subscription.html.twig')]
    public function subscriptions(): array
    {
        return [];
    }
}
