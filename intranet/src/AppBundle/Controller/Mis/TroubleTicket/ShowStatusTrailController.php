<?php

declare(strict_types=1);

namespace AppBundle\Controller\Mis\TroubleTicket;

use ApiBundle\Client;
use ApiBundle\Model\ApiData;
use AppBundle\Configuration\ApiValueResolverAttribute;
use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;

#[AsController]
#[Route(path: '/mis/trouble-tickets', defaults: ['alvest_module' => 'TTS', 'moduleDomain' => 'trouble_ticket'])]
class ShowStatusTrailController
{
    public function __construct(
        private readonly Client $client,
    ) {
    }

    #[Route(path: '/{id}/show/status-trail', name: 'trouble_ticket_show_status_trail', methods: ['GET'])]
    #[Template('mis/trouble_ticket/show_status_trail.html.twig')]
    public function __invoke(#[ApiValueResolverAttribute(parameters: ['resource' => ShowController::RESOURCE_URL])] ApiData $troubleTicket)
    {
        return [
            'troubleTicket' => $troubleTicket,
            'statusTrail' => $this->client->findBy('audit_logs/by_reference', ['referenceId' => $troubleTicket->getIriId(), 'auditType' => 'trouble_ticket', 'property' => 'status']),
        ];
    }
}
