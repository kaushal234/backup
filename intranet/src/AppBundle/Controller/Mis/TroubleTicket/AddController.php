<?php

declare(strict_types=1);

namespace AppBundle\Controller\Mis\TroubleTicket;

use ApiBundle\Client;
use ApiBundle\Form\ViolationMapper;
use ApiBundle\Iri\Iri;
use ApiBundle\Model\User;
use AppBundle\Form\Type\Mis\TroubleTicket\TroubleTicketType;
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
class AddController extends AbstractController
{
    use CiosNamesTrait;

    public function __construct(
        private readonly TranslatorInterface $translator,
        private readonly Client $client,
        private readonly FormFactoryInterface $formFactory,
        private readonly ViolationMapper $violationMapper,
    ) {
    }

    #[Route(path: '/add', name: 'trouble_ticket_add', methods: ['GET', 'POST'])]
    #[Route(path: '/add-from-ln', name: 'trouble_ticket_add_from_ln', methods: ['GET', 'POST'])]
    #[Template('mis/trouble_ticket/add.html.twig')]
    public function __invoke(Request $request, #[CurrentUser] User $user)
    {
        $isGrantedOpenOnBehalf = $this->isGranted('FEATURE_TROUBLE_TICKET_OPEN_ON_BEHALF');

        [$defaults, $defaultApplication] = $this->buildDefaults($request, $user);

        $form = $this->formFactory->createNamed(
            'trouble_ticket',
            TroubleTicketType::class,
            $defaults,
            [
                'is_granted_open_on_behalf' => $isGrantedOpenOnBehalf,
                'default_application' => $defaultApplication,
            ],
        );

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $troubleTicket = $this->client->save(ShowController::RESOURCE_URL, $form->getData());

                $this->addFlash('success', $this->translator->trans('trouble_ticket.success.add', [], 'trouble_ticket'));

                // POST-redirect-GET: come back to the add page with the new id so the
                // file uploader (React) opens in the side column for that ticket.
                return $this->redirectToRoute('trouble_ticket_add', ['created' => Iri::id($troubleTicket)]);
            } catch (ClientException $exception) {
                $this->violationMapper->mapToForm($exception, $form);
            }
        }

        $created = $request->query->get('created');

        return [
            'form' => $form->createView(),
            'isGrantedOpenOnBehalf' => $isGrantedOpenOnBehalf,
            'created' => $created,
            'cios' => null === $created ? $this->getCiosNames() : '',
        ];
    }

    private function buildDefaults(Request $request, User $user): array
    {
        $metadata = $request->request->all('metadata');

        $defaults = [
            'createdBy' => \sprintf('/people/%s', $user->getId()),
            'ccs' => [],
            'url' => $metadata['url'] ?? null,
        ];

        $defaultApplication = null;

        if (!empty($metadata['module'])) {
            try {
                // exact[name]: the plain "name" filter is a partial match. \RangeException = no module
                // with that exact (unique) name; the preset is best-effort, so skip it silently.
                $module = $this->client->findOneBy('/modules', ['exact' => ['name' => $metadata['module']]]);
                $defaults['module'] = $module['@id'] ?? null;
                $defaultApplication = $this->applicationIri($module);
            } catch (ClientException|\RangeException) {
            }
        }

        if (!empty($metadata['type'])) {
            $defaults['type'] = \sprintf('/mis/types/%s', (int) $metadata['type']);
        }

        if ('trouble_ticket_add_from_ln' === $request->attributes->get('_route')) {
            try {
                $module = $this->client->findOneBy('/modules', ['exact' => ['name' => 'Permission Connexion']]);
                $defaults['module'] = $module['@id'] ?? null;
                $defaultApplication = $this->applicationIri($module);
            } catch (ClientException|\RangeException) {
            }

            try {
                $defaults['type'] = $this->client->findOneBy('/mis/types', ['description' => 'i_cannot_proceed'])['@id'] ?? null;
            } catch (ClientException|\RangeException) {
            }

            $defaults['url'] = 'TLD_PRD';
        }

        return [$defaults, $defaultApplication];
    }

    /**
     * The application IRI from a module resource, whether embedded as an object or a bare IRI.
     */
    private function applicationIri(mixed $module): ?string
    {
        $application = $module['application'] ?? null;

        if (\is_array($application)) {
            return $application['@id'] ?? null;
        }

        return \is_string($application) ? $application : null;
    }
}
