<?php

declare(strict_types=1);

namespace AppBundle\Controller\Mis\TroubleTicket;

use ApiBundle\Client;
use ApiBundle\Model\User;
use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

#[AsController]
#[Route(path: '/mis/trouble-tickets', defaults: ['alvest_module' => 'TTS', 'moduleDomain' => 'trouble_ticket'])]
readonly class RelativeController
{
    public function __construct(
        private Client $client,
    ) {
    }

    /**
     * AJAX: returns the "relative TTS opened in the last 2 days" panel for the chosen module + option.
     * Mirrors the React fetchRelativeTroubleTickets call:
     *   GET /mis/trouble_tickets?type={option}&module={module}&open=1&createdAt[after]={today-2d}.
     */
    #[Route(path: '/relative', name: 'trouble_ticket_relative', methods: ['GET'])]
    #[Template('mis/trouble_ticket/partial/_relative.html.twig')]
    public function __invoke(Request $request, #[CurrentUser] User $user): array
    {
        $type = (string) $request->query->get('type', '');
        $module = (string) $request->query->get('module', '');

        if ('' === $type || '' === $module) {
            return ['rows' => []];
        }

        // Accept either a raw id or a full IRI from the client.
        $type = str_starts_with($type, '/') ? $type : '/mis/types/'.$type;
        $module = str_starts_with($module, '/') ? $module : '/modules/'.$module;

        $after = (new \DateTimeImmutable('-2 days'))->format('Y-m-d');

        $troubleTickets = $this->client->findBy(
            ShowController::RESOURCE_URL,
            [
                'type' => $type,
                'module' => $module,
                'open' => 1,
                'createdAt' => ['after' => $after],
            ],
            ['createdAt' => 'DESC'],
        );

        $userIri = $user->getIriId();
        $rows = [];

        foreach ($troubleTickets as $troubleTicket) {
            // createdBy may come back as an embedded object or as a bare IRI string.
            $createdBy = $troubleTicket['createdBy'] ?? null;
            $createdByIri = \is_array($createdBy) ? ($createdBy['@id'] ?? null) : $createdBy;

            // additionalOwners may come back as IRIs or as embedded objects.
            $ownerIris = array_map(
                static fn ($owner) => \is_array($owner) ? ($owner['@id'] ?? null) : $owner,
                $troubleTicket['additionalOwners'] ?? [],
            );

            // Subscriptions for this ticket (same lookup ShowController uses).
            $subscriptions = $this->client->findBy('subscriptions', ['resource' => $troubleTicket['@id']], []);
            $subscriberIris = array_map(
                static fn ($subscription) => \is_array($subscription['user'] ?? null) ? ($subscription['user']['@id'] ?? null) : ($subscription['user'] ?? null),
                iterator_to_array($subscriptions->getIterator()),
            );

            $rows[] = [
                'ticket' => $troubleTicket,
                'ownersCount' => \count($ownerIris),
                'subscribersCount' => \count($subscriberIris),
                'isMine' => $userIri === $createdByIri,
                'isOwner' => $userIri === $createdByIri || \in_array($userIri, $ownerIris, true),
                'isSubscriber' => $userIri === $createdByIri || \in_array($userIri, $subscriberIris, true),
            ];
        }

        return ['rows' => $rows];
    }
}
