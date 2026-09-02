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
class ShowHelpController
{
    #[Route(path: '/{id}/show/help', name: 'trouble_ticket_show_help', methods: ['GET'])]
    #[Template('mis/trouble_ticket/show_help.html.twig')]
    public function __invoke(#[ApiValueResolverAttribute(parameters: ['resource' => ShowController::RESOURCE_URL, 'filters' => ['normalizationGroups' => ['workflow']]])] ApiData $troubleTicket)
    {
        $nextActions = [];
        $statuses = [
            'PENDING' => 'Send TTS to MIS.',
            'PENDING MOO/GKU' => 'Send TTS to MOO / GKU / LKU.',
            'AWAITING USER' => 'Request information to user.',
            'MOO/GKU AWAITING USER' => 'Request information to user.',
            'IN PROGRESS' => 'TTS taken in charge by MIS.',
            'SOLUTION PROPOSED' => 'Propose a solution to user.',
            'MOO/GKU SOLUTION PROPOSED' => 'Propose a solution to user.',
            'NOT AN ISSUE' => 'This TTS is not an issue anymore, it can be closed.',
            'ALREADY RAISED' => 'This TTS has been already raised, it can be closed.',
            'SOLVED' => 'This TTS has been solved.',
            'NOT APPROVED' => 'This request is not approved.',
        ];

        foreach ($troubleTicket['availableStatus'] as $availableStatus) {
            $nextActions[$availableStatus] = $statuses[$availableStatus];
        }

        return [
            'troubleTicket' => $troubleTicket,
            'nextActions' => $nextActions,
        ];
    }
}
