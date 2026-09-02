<?php

declare(strict_types=1);

namespace AppBundle\Twig\Components\Mis\TroubleTicket;

use ApiBundle\Client;
use ApiBundle\Form\ViolationMapper;
use AppBundle\Controller\Mis\TroubleTicket\ShowController;
use AppBundle\Form\Type\Mis\TroubleTicket\TroubleTicketCloseType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpClient\Exception\ClientException;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveAction;
use Symfony\UX\LiveComponent\Attribute\LiveArg;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\ComponentWithFormTrait;
use Symfony\UX\LiveComponent\DefaultActionTrait;

#[AsLiveComponent(name: 'TroubleTicketCloseDynamicForm', template: 'components/DynamicForm.html.twig')]
class TroubleTicketCloseDynamicForm extends AbstractController
{
    use ComponentWithFormTrait;
    use DefaultActionTrait;

    private const STATUSES = ['SOLVED', 'NOT AN ISSUE', 'ALREADY RAISED', 'NOT APPROVED'];

    #[LiveProp]
    public array $troubleTicket = [];

    #[LiveProp]
    public array $statuses = [];

    #[LiveProp]
    public bool $tasks = false;

    public function __construct(
        private readonly Client $client,
        private readonly TranslatorInterface $translator,
        private readonly ViolationMapper $violationMapper,
        private readonly RequestStack $requestStack,
    ) {
    }

    public function mount(int $troubleTicketId): void
    {
        $troubleTicket = $this->client
            ->find(ShowController::RESOURCE_URL, $troubleTicketId, ['query' => ['normalizationGroups' => ['workflow']]])
            ->toArray();

        $this->troubleTicket = $troubleTicket;
        $this->statuses = array_values(array_intersect($troubleTicket['availableStatus'] ?? [], self::STATUSES));
        $this->tasks = !empty($this->requestStack->getSession()->get('tasks'));
    }

    #[LiveAction]
    public function save(#[LiveArg] ?string $next = null): ?RedirectResponse
    {
        $this->submitForm();

        try {
            $payload = $this->form->getData();
            $this->client->save(\sprintf('%s/%d', ShowController::RESOURCE_URL, $this->troubleTicket['id']), $payload);
            $this->addFlash('success', $this->translator->trans('trouble_ticket.success.close', [], 'trouble_ticket'));

            $route = null !== $next ? 'next_trouble_ticket' : 'trouble_ticket_show';
            $this->requestStack->getSession()->save();

            return $this->redirectToRoute($route, ['id' => $this->troubleTicket['id']]);
        } catch (ClientException $exception) {
            $this->violationMapper->mapToForm($exception, $this->form);

            throw new UnprocessableEntityHttpException();
        }
    }

    protected function instantiateForm(): FormInterface
    {
        $data = [
            '@id' => $this->troubleTicket['@id'],
            'id' => $this->troubleTicket['id'],
            'ccs' => array_values(array_filter(array_map(
                static fn (array $cc): ?string => $cc['@id'] ?? null,
                array_values($this->troubleTicket['ccs'] ?? []),
            ))),
        ];

        return $this->createForm(TroubleTicketCloseType::class, $data, [
            'statuses' => $this->statuses,
            'tasks' => $this->tasks,
        ]);
    }
}
