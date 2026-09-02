<?php

declare(strict_types=1);

namespace AppBundle\Controller\Mis\TroubleTicket;

use ApiBundle\Model\ApiData;
use AppBundle\Configuration\ApiValueResolverAttribute;
use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;

#[AsController]
#[Route(path: '/mis/trouble-tickets', defaults: ['alvest_module' => 'TTS', 'moduleDomain' => 'trouble_ticket'])]
class ShowSubscriptionController
{
    #[Route(path: '/{id}/show/subscriptions', name: 'trouble_ticket_show_subscriptions', methods: ['GET|POST'])]
    #[Template('mis/trouble_ticket/show_subscription.html.twig')]
    public function __invoke(#[ApiValueResolverAttribute(parameters: ['resource' => ShowController::RESOURCE_URL])] ApiData $troubleTicket)
    {
        return [
            'troubleTicket' => $troubleTicket,
        ];
    }
}
