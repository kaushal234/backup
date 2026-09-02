<?php

declare(strict_types=1);

namespace AppBundle\Controller\Mis\TroubleTicket;

use ApiBundle\Model\ApiData;
use AppBundle\Configuration\ApiValueResolverAttribute;
use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;

#[AsController]
#[Route(path: '/mis/trouble-tickets', defaults: ['alvest_module' => 'TTS', 'moduleDomain' => 'trouble_ticket'])]
class CloseController extends AbstractController
{
    #[Route(path: '/{id}/close', name: 'trouble_ticket_close', requirements: ['id' => '\d+'], defaults: ['label' => 'trouble_ticket.button.close'], methods: ['GET', 'POST'])]
    #[Template('mis/trouble_ticket/close.html.twig')]
    public function __invoke(
        #[ApiValueResolverAttribute(parameters: ['resource' => ShowController::RESOURCE_URL, 'filters' => ['normalizationGroups' => ['workflow']]])] ApiData $troubleTicket,
        Request $request,
    ): array {
        return [
            'troubleTicket' => $troubleTicket,
            'tasks' => !empty($request->getSession()->get('tasks')),
        ];
    }
}
