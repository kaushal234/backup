<?php

declare(strict_types=1);

namespace AppBundle\Controller\Mis\TroubleTicket;

use ApiBundle\Client;
use ApiBundle\Model\ApiData;
use ApiBundle\Model\User;
use AppBundle\Configuration\ApiValueResolverAttribute;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpClient\Exception\ClientException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Contracts\Translation\TranslatorInterface;

#[AsController]
#[Route(path: '/mis/trouble-tickets', defaults: ['alvest_module' => 'TTS', 'moduleDomain' => 'trouble_ticket'])]
class AdditionalOwnerController extends AbstractController
{
    public function __construct(
        private readonly Client $client,
        private readonly TranslatorInterface $translator,
    ) {
    }

    /**
     * Add or remove the current user in the ticket's additionalOwners. Two callers:
     *  - the buttons on the show page: flash + redirect back to the ticket;
     *  - the "I have the same issue" AJAX reaction button on the "Similar tickets" panel
     *    (detected via X-Requested-With, POSTing to add-additional-owner): 204 No Content,
     *    the panel then re-fetches itself so counts/states are re-rendered server-side.
     */
    #[Route(path: '/{id}/add-additional-owner', name: 'trouble_ticket_add_additional_owner', methods: 'GET|POST')]
    #[Route(path: '/{id}/remove-additional-owner', name: 'trouble_ticket_remove_additional_owner', methods: 'GET|POST')]
    public function __invoke(#[ApiValueResolverAttribute(parameters: ['resource' => ShowController::RESOURCE_URL])] ApiData $troubleTicket, Request $request, #[CurrentUser] ?User $user = null): Response
    {
        $isAjax = $request->isXmlHttpRequest();

        if (null === $user) {
            if ($isAjax) {
                return new Response('', Response::HTTP_UNAUTHORIZED);
            }

            $this->addFlash('error', $this->translator->trans('trouble_ticket.errors.user_not_found', [], 'trouble_ticket'));

            return $this->redirectToRoute('trouble_ticket_show', ['id' => $troubleTicket->getIriId()]);
        }

        // Owners may come back as embedded objects or as bare IRI strings; normalize to IRIs.
        $ownerIris = array_map(
            static fn ($owner) => \is_array($owner) ? ($owner['@id'] ?? null) : $owner,
            $troubleTicket['additionalOwners'] ?? [],
        );

        if ('trouble_ticket_add_additional_owner' === $request->attributes->get('_route')) {
            // Current user first, then the existing owners.
            $payload = [$user->getIriId(), ...$ownerIris];
        } else {
            $payload = array_filter($ownerIris, static fn ($iri) => $iri !== $user->getIriId());
        }

        // array_values: keep it a JSON list even after filtering/deduplicating.
        $payload = array_values(array_unique(array_filter($payload)));

        if ($isAjax) {
            // No flash, no redirect; a ClientException bubbles up like any AJAX failure.
            $this->client->save(\sprintf('%s/%s', ShowController::RESOURCE_URL, $troubleTicket->getIriId()), ['@id' => $troubleTicket->getIri(), 'additionalOwners' => $payload]);

            return new Response('', Response::HTTP_NO_CONTENT);
        }

        try {
            $this->client->save(\sprintf('%s/%s', ShowController::RESOURCE_URL, $troubleTicket->getIriId()), ['@id' => $troubleTicket->getIri(), 'additionalOwners' => $payload]);
            $this->addFlash('success', $this->translator->trans('trouble_ticket.success.update', [], 'trouble_ticket'));
        } catch (ClientException $exception) {
            $this->addFlash('error', \sprintf('%s. %s', $this->translator->trans('trouble_ticket.errors.update', [], 'trouble_ticket'), $exception->getMessage()));
        }

        return $this->redirectToRoute('trouble_ticket_show', ['id' => $troubleTicket->getIriId()]);
    }
}
