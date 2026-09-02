<?php

declare(strict_types=1);

namespace AppBundle\Controller\HumanResources;

use ApiBundle\Client;
use ApiBundle\Model\ApiData;
use AppBundle\Configuration\ApiValueResolverAttribute;
use AppBundle\DataTable\Query\ApiProxyQuery;
use AppBundle\DataTable\Type\HumanResources\EventDataTableType;
use Kreyu\Bundle\DataTableBundle\DataTableFactoryAwareTrait;
use Kreyu\Bundle\DataTableBundle\DataTableTurboResponseTrait;
use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpClient\Exception\ClientException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route(path: '/human-resources/events', defaults: ['alvest_module' => 'Event', 'moduleDomain' => 'event'])]
class EventController extends AbstractController
{
    use DataTableFactoryAwareTrait;
    use DataTableTurboResponseTrait;
    public const string DELETE_TOKEN = 'delete_event';

    public static function getSubscribedServices(): array
    {
        return [
            ...parent::getSubscribedServices(),
            Client::class,
            TranslatorInterface::class,
        ];
    }

    #[Route(path: '', name: 'event_home', methods: ['GET', 'POST'])]
    public function home(Request $request): Response
    {
        $isGrantedAdmin = $this->isGranted('FEATURE_HOLIDAYS_ADMIN');
        $datatable = $this->createDataTable(EventDataTableType::class, \sprintf('%s?startedAt[after]=%s', EventDataTableType::RESOURCE, (new \DateTime())->format('Y-m-d')), ['isGrantedAdmin' => $isGrantedAdmin]);
        $datatable->handleRequest($request);
        if ($datatable->isExporting() && $datatable->getQuery() instanceof ApiProxyQuery) {
            return $datatable->getQuery()->export();
        }
        if ($datatable->isRequestFromTurboFrame()) {
            return $this->createDataTableTurboResponse($datatable);
        }

        $client = $this->container->get(Client::class);
        $comingThisMonth = $client->findBy('events',
            [
                'startedAt' => ['after' => (new \DateTime())->format('Y-m-d')],
                'endedAt' => ['before' => (new \DateTime('last day of this month'))->format('Y-m-d')],
            ],
            ['startedAt' => 'ASC']
        );

        $comingNextMonth = $client->findBy('events',
            [
                'startedAt' => ['after' => (new \DateTime('first day of next month'))->format('Y-m-d')],
                'endedAt' => ['before' => (new \DateTime('last day of next month'))->format('Y-m-d')],
            ],
            ['startedAt' => 'ASC']
        );

        return $this->render('human_resources/event/home.html.twig', [
            'dataTable' => $datatable->createView(),
            'comingThisMonth' => $comingThisMonth,
            'comingNextMonth' => $comingNextMonth,
        ]);
    }

    #[Route(path: '/add', name: 'event_add', methods: 'GET')]
    #[IsGranted('FEATURE_HOLIDAYS_ADMIN')]
    #[Template('human_resources/event/add.html.twig')]
    public function addEvent(): array|Response
    {
        return [];
    }

    #[Route(path: '/{id}/edit', name: 'event_edit', methods: ['GET', 'POST'])]
    #[Template('human_resources/event/edit.html.twig')]
    #[IsGranted('FEATURE_HOLIDAYS_ADMIN')]
    public function editEvent(#[ApiValueResolverAttribute] ApiData $event): array
    {
        return [
            'initialState' => [
                'eventValues' => $event->toArray(),
                'formType' => 'edit',
            ],
        ];
    }

    #[Route(path: '/{id}/delete', name: 'event_delete', methods: 'GET')]
    #[IsGranted('FEATURE_HOLIDAYS_ADMIN')]
    public function deleteEvent(Request $request, #[ApiValueResolverAttribute] ApiData $event): Response
    {
        if (!$this->isCsrfTokenValid(self::DELETE_TOKEN, $request->query->get('_token'))) {
            $this->addFlash('error', $this->container->get(TranslatorInterface::class)->trans('events.error.remove_form', [], 'events'));

            return $this->redirectToRoute('event_home');
        }

        try {
            $this->container->get(Client::class)->remove('events', $event->getIriId());
            $this->addFlash('success', $this->container->get(TranslatorInterface::class)->trans('events.success.remove', [], 'events'));
        } catch (ClientException $e) {
            $this->addFlash('error', \sprintf('%s: %s', $this->container->get(TranslatorInterface::class)->trans('events.error.remove', [], 'events'), $e->getMessage()));
        }

        return $this->redirectToRoute('event_home');
    }
}
