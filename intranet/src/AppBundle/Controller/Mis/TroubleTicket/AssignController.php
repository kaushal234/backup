<?php

declare(strict_types=1);

namespace AppBundle\Controller\Mis\TroubleTicket;

use ApiBundle\Client;
use ApiBundle\Model\ApiData;
use ApiBundle\Model\User;
use AppBundle\Configuration\ApiValueResolverAttribute;
use AppBundle\Controller\Directory\PeopleController;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpClient\Exception\ClientException;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Contracts\Translation\TranslatorInterface;

#[AsController]
#[Route(path: '/mis/trouble-tickets', defaults: ['alvest_module' => 'TTS', 'moduleDomain' => 'trouble_ticket'])]
class AssignController extends AbstractController
{
    public function __construct(
        private readonly TranslatorInterface $translator,
        private readonly Client $client,
    ) {
    }

    #[Route(path: '/{id}/assign', name: 'trouble_ticket_assign', methods: 'GET|POST')]
    #[Route(path: '/{id}/assign/{assigneeId}', name: 'trouble_ticket_assign_to_team', methods: 'GET|POST', options: ['expose' => true])]
    #[IsGranted('ACL_GG_MIS')]
    public function __invoke(
        #[ApiValueResolverAttribute(parameters: ['resource' => ShowController::RESOURCE_URL])] ApiData $troubleTicket,
        #[ApiValueResolverAttribute(parameters: ['resource' => PeopleController::RESOURCE_URL, 'id' => 'assigneeId'])] ?ApiData $people = null,
    ): RedirectResponse {
        /** @var User|ApiData|null $user */
        $user = $people ?? $this->getUser();
        if (null === $user) {
            $this->addFlash('error', $this->translator->trans('trouble_ticket.errors.user_not_found', [], 'trouble_ticket'));

            return $this->redirectToRoute('trouble_ticket_show', ['id' => $troubleTicket->getIriId()]);
        }

        $payload = $user instanceof User ? $user->getIriId() : $user['@id'];

        try {
            $this->client->save(\sprintf('%s/%s', ShowController::RESOURCE_URL, $troubleTicket->getIriId()), ['@id' => $troubleTicket->getIri(), 'misAssignee' => $payload]);
            $this->addFlash('success', $this->translator->trans('trouble_ticket.success.assignee', [], 'trouble_ticket'));
        } catch (ClientException $exception) {
            $this->addFlash('error', \sprintf('%s. %s', $this->translator->trans('trouble_ticket.errors.update', [], 'trouble_ticket'), $exception->getMessage()));
        }

        return $this->redirectToRoute('trouble_ticket_show', ['id' => $troubleTicket->getIriId()]);
    }
}
