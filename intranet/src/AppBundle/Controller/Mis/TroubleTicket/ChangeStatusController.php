<?php

declare(strict_types=1);

namespace AppBundle\Controller\Mis\TroubleTicket;

use ApiBundle\Client;
use ApiBundle\Model\ApiData;
use AppBundle\Configuration\ApiValueResolverAttribute;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpClient\Exception\ClientException;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

#[AsController]
#[Route(path: '/mis/trouble-tickets', defaults: ['alvest_module' => 'TTS', 'moduleDomain' => 'trouble_ticket'])]
class ChangeStatusController extends AbstractController
{
    public function __construct(
        private readonly TranslatorInterface $translator,
        private readonly Client $client,
    ) {
    }

    #[Route(path: '/{id}/change-status', name: 'trouble_ticket_change_status', methods: ['GET', 'POST'])]
    public function __invoke(#[ApiValueResolverAttribute(parameters: ['resource' => ShowController::RESOURCE_URL])] ApiData $troubleTicket): RedirectResponse
    {
        try {
            $this->client->put(
                \sprintf('%s/%s/status', ShowController::RESOURCE_URL, $troubleTicket->getIriId()),
                [
                    'json' => [
                        'status' => 'PENDING',
                    ],
                ]
            );

            $this->addFlash('success', $this->translator->trans('trouble_ticket.button.status_update_success', [], 'trouble_ticket'));
        } catch (ClientException $e) {
            $this->addFlash('error', $this->translator->trans('trouble_ticket.button.status_update_error', [], 'trouble_ticket'));
        }

        return $this->redirectToRoute('trouble_ticket_show', ['id' => $troubleTicket->getIriId()]);
    }
}
