<?php

declare(strict_types=1);

namespace AppBundle\Controller\Mis\TroubleTicket;

use ApiBundle\Client;
use ApiBundle\Form\ViolationMapper;
use ApiBundle\Iri\Iri;
use ApiBundle\Model\ApiData;
use ApiBundle\Model\User;
use AppBundle\Configuration\ApiValueResolverAttribute;
use AppBundle\Form\Type\Mis\TroubleTicket\TroubleTicketEditType;
use AppBundle\Trait\Mis\TroubleTicket\CiosNamesTrait;
use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpClient\Exception\ClientException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Contracts\Translation\TranslatorInterface;

#[AsController]
#[Route(path: '/mis/trouble-tickets', defaults: ['alvest_module' => 'TTS', 'moduleDomain' => 'trouble_ticket'])]
class EditController extends AbstractController
{
    use CiosNamesTrait;

    public function __construct(
        private readonly TranslatorInterface $translator,
        private readonly Client $client,
        private readonly FormFactoryInterface $formFactory,
        private readonly ViolationMapper $violationMapper,
    ) {
    }

    #[Route(path: '/{id}/edit', name: 'trouble_ticket_edit', methods: ['GET', 'POST'], requirements: ['id' => '\d+'])]
    #[Template('mis/trouble_ticket/edit.html.twig')]
    public function __invoke(
        #[ApiValueResolverAttribute(parameters: ['resource' => ShowController::RESOURCE_URL])] ApiData $troubleTicket,
        Request $request,
        #[CurrentUser] User $user,
    ) {
        $isGrantedOpenOnBehalf = $this->isGranted('FEATURE_TROUBLE_TICKET_OPEN_ON_BEHALF');
        $isGrantedAdmin = \in_array('ROLE_MISM', $user->getAcls(), true);

        $data = [
            '@id' => $troubleTicket['@id'],
            'id' => $troubleTicket['id'],
            'url' => $troubleTicket['url'] ?? null,
            'indiceFactor' => $troubleTicket['indiceFactor'] ?? null,
            'module' => $troubleTicket['module'] ?? null,
            'shortDescription' => $troubleTicket['shortDescription'] ?? null,
            'type' => $troubleTicket['type'] ?? null,
            'dueDate' => !empty($troubleTicket['dueDate']) ? new \DateTimeImmutable($troubleTicket['dueDate']) : null,
            'createdBy' => $this->iriOf($troubleTicket['createdBy'] ?? null),
            'status' => $troubleTicket['status'] ?? null,
            'assignee' => $this->iriOf($troubleTicket['assignee'] ?? null),
            'comment' => '',
        ];

        $form = $this->formFactory->createNamed('trouble_ticket', TroubleTicketEditType::class, $data, [
            'is_granted_open_on_behalf' => $isGrantedOpenOnBehalf,
            'is_granted_admin' => $isGrantedAdmin,
            // The application field is mapped=false, so loading the ticket won't populate it.
            // Derive it from the ticket's module (its application) so it shows on edit, exactly
            // like the preset-module flow on the add page.
            'default_application' => $this->applicationIriForModule($troubleTicket['module'] ?? null),
            // Status choices for the edit dropdown. Only admins can edit the status, so only fetch
            // the workflow view (the resolved ticket has no availableStatus) when granted; the form
            // skips the field for an empty list anyway.
            'statuses' => $isGrantedAdmin ? $this->availableStatuses($troubleTicket) : [],
        ]);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $payload = $form->getData();
                if ($payload['dueDate'] instanceof \DateTimeInterface) {
                    $payload['dueDate'] = $payload['dueDate']->format(\DateTimeInterface::ATOM);
                }

                $this->client->save(\sprintf('%s/%d', ShowController::RESOURCE_URL, $troubleTicket->getIriId()), $payload);
                $this->addFlash('success', $this->translator->trans('trouble_ticket.success.update', [], 'trouble_ticket'));

                return $this->redirectToRoute('trouble_ticket_show', ['id' => $troubleTicket->getIriId()]);
            } catch (ClientException $exception) {
                $this->violationMapper->mapToForm($exception, $form);
            }
        }

        return [
            'form' => $form->createView(),
            'troubleTicket' => $troubleTicket,
            'isGrantedOpenOnBehalf' => $isGrantedOpenOnBehalf,
            'isGrantedAdmin' => $isGrantedAdmin,
            'cios' => $this->getCiosNames(),
        ];
    }

    private function availableStatuses(ApiData $troubleTicket): array
    {
        $current = $troubleTicket['status'] ?? null;

        try {
            $workflow = $this->client->find(
                ShowController::RESOURCE_URL,
                $troubleTicket->getIriId(),
                ['raw_results' => true, 'query' => ['normalizationGroups' => ['workflow']]],
            );
            $available = $workflow['availableStatus'] ?? [];
        } catch (ClientException) {
            $available = [];
        }

        $statuses = [...$available, $current];

        // Drop nulls/blanks and de-duplicate while preserving order.
        return array_values(array_unique(array_filter(
            $statuses,
            static fn ($status): bool => \is_string($status) && '' !== $status,
        )));
    }

    private function iriOf(mixed $item): ?string
    {
        if (\is_array($item)) {
            return $item['@id'] ?? null;
        }

        return \is_string($item) ? $item : null;
    }

    private function applicationIriForModule(mixed $module): ?string
    {
        if (null === $module) {
            return null;
        }

        if (\is_array($module) && isset($module['application'])) {
            $application = $module['application'];

            return \is_array($application) ? ($application['@id'] ?? null) : (\is_string($application) ? $application : null);
        }

        $moduleIri = \is_array($module) ? ($module['@id'] ?? null) : $module;
        if (!\is_string($moduleIri) || '' === $moduleIri) {
            return null;
        }

        try {
            $fetched = $this->client->find('modules', Iri::id($moduleIri), ['raw_results' => true]);
        } catch (ClientException) {
            return null;
        }

        $application = $fetched['application'] ?? null;

        return \is_array($application) ? ($application['@id'] ?? null) : (\is_string($application) ? $application : null);
    }
}
