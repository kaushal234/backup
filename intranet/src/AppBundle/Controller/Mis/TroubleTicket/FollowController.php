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
use Symfony\Contracts\Translation\TranslatorInterface;

#[AsController]
#[Route(path: '/mis/trouble-tickets', defaults: ['alvest_module' => 'TTS', 'moduleDomain' => 'trouble_ticket'])]
class FollowController extends AbstractController
{
    public function __construct(
        private readonly Client $client,
        private readonly TranslatorInterface $translator,
    ) {
    }

    #[Route(path: '/{id}/follow', name: 'trouble_ticket_follow', methods: 'GET|POST')]
    public function __invoke(#[ApiValueResolverAttribute(parameters: ['resource' => ShowController::RESOURCE_URL])] ApiData $troubleTicket, Request $request): Response
    {
        /** @var ?User $user */
        $user = $this->getUser();
        if (null === $user) {
            if ($request->isXmlHttpRequest()) {
                return new Response('', Response::HTTP_UNAUTHORIZED);
            }

            $this->addFlash('error', $this->translator->trans('trouble_ticket.errors.user_not_found', [], 'trouble_ticket'));

            return $this->redirectToRoute('trouble_ticket_show', ['id' => $troubleTicket->getIriId()]);
        }

        if ($request->isXmlHttpRequest()) {
            $this->subscribe($troubleTicket, $user);

            return new Response('', Response::HTTP_NO_CONTENT);
        }

        try {
            $this->subscribe($troubleTicket, $user);
            $this->addFlash('success', $this->translator->trans('trouble_ticket.success.follow', [], 'trouble_ticket'));
        } catch (ClientException $exception) {
            $this->addFlash('error', \sprintf('%s. %s', $this->translator->trans('trouble_ticket.errors.follow', [], 'trouble_ticket'), $exception->getMessage()));
        }

        return $this->redirectToRoute('trouble_ticket_show', ['id' => $troubleTicket->getIriId()]);
    }

    /**
     * Create the subscription unless it already exists (same lookup ShowController
     * uses for its "canSubscribe" flag).
     */
    private function subscribe(ApiData $troubleTicket, User $user): void
    {
        $existing = $this->client->findBy('subscriptions', [
            'resource' => $troubleTicket->getIri(),
            'user' => $user->getIriId(),
        ], []);

        if (0 === $existing->getIterator()->count()) {
            $this->client->save('subscriptions', [
                'resource' => $troubleTicket->getIri(),
                'user' => $user->getIriId(),
            ]);
        }
    }
}
