<?php

declare(strict_types=1);

namespace AppBundle\Controller\Survey;

use ApiBundle\Client;
use ApiBundle\Form\ViolationMapper;
use AppBundle\Filters\Type\Survey\ItemsFilters;
use AppBundle\Form\Type\Survey\ItemType;
use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpClient\Exception\ClientException;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route(path: '/surveys/{id}/items', defaults: ['alvest_module' => 'SRV'])]
class ItemController extends AbstractController
{
    final public const itemsPerPage = 10;
    final public const RESOURCE_URL = 'surveys/items';

    private readonly Client $client;

    private readonly FormFactoryInterface $formFactory;

    private readonly ViolationMapper $violationMapper;

    private readonly TranslatorInterface $translator;

    public function __construct(Client $client, FormFactoryInterface $formFactory, ViolationMapper $violationMapper, TranslatorInterface $translator)
    {
        $this->client = $client;
        $this->formFactory = $formFactory;
        $this->violationMapper = $violationMapper;
        $this->translator = $translator;
    }

    #[Route(path: '', name: 'survey_items_index', methods: 'GET', defaults: ['page' => 1, 'order' => [], 'itemsPerPage' => 10])]
    #[Template('surveys\items\index.html.twig')]
    public function index(Request $request, $id)
    {
        $survey = $this->client->find(SurveyController::RESOURCE_URL, $id);

        $parameters = [];
        $parameters['page'] = $request->query->getInt('page', 1);
        $parameters['order'] = $request->query->all('order');
        $parameters['itemsPerPage'] = $request->query->get('itemsPerPage', static::itemsPerPage);
        $parameters['survey'] = $survey['@id'];

        $formFilters = $this->formFactory->createNamed('survey_filters', ItemsFilters::class, [], [
            'action' => $this->generateUrl('survey_items_index', ['id' => $id]),
            'method' => 'GET',
        ]);

        $formFilters->handleRequest($request);
        if ($formFilters->isSubmitted() && $formFilters->isValid()) {
            $parameters = array_merge($parameters, $formFilters->getData());
        }

        try {
            $items = $this->client->findBy(self::RESOURCE_URL, $parameters);
        } catch (ClientException $e) {
            $this->violationMapper->mapToForm($e, $formFilters);
            $items = [];
        }

        return [
            'survey' => $survey,
            'items' => $items,
            'formFilters' => $formFilters->createView(),
            'itemsPerPage' => $parameters['itemsPerPage'],
            'currentPage' => $parameters['page'],
            'paginationUrl' => [
                'route' => $request->attributes->get('_route'),
                'parameters' => ['id' => $id],
            ],
        ];
    }

    #[Route(path: '/add', name: 'survey_items_add', methods: 'GET|POST')]
    #[Template('surveys\items\add.html.twig')]
    #[IsGranted('FEATURE_SURVEY_WRITE')]
    public function add(Request $request, $id)
    {
        $survey = $this->client->find(SurveyController::RESOURCE_URL, $id);

        $form = $this->formFactory->createNamed('survey_item', ItemType::class);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $data = $form->getData();
                $data['survey'] = $survey['@id'];
                $this->client->save(self::RESOURCE_URL, $data);
                $this->addFlash(
                    'success',
                    $this->translator->trans('messages.success.item.add', ['%name%' => $data['description']], 'surveys')
                );

                return $this->redirectToRoute('survey_items_index', ['id' => $id]);
            } catch (ClientException $e) {
                $this->violationMapper->mapToForm($e, $form);
            }
        }

        return [
            'survey' => $survey,
            'form' => $form->createView(),
        ];
    }

    #[Route(path: '/{itemId}/edit', name: 'survey_items_edit', requirements: ['id' => '\d+'], methods: 'GET|POST')]
    #[Template('surveys\items\edit.html.twig')]
    #[IsGranted('FEATURE_SURVEY_WRITE')]
    public function edit(Request $request, $id, $itemId)
    {
        $item = $this->client->find(self::RESOURCE_URL, $itemId);

        $survey = $this->client->find(SurveyController::RESOURCE_URL, $id);

        $form = $this->formFactory->createNamed('survey_item', ItemType::class, $item);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $data = $form->getData();
                unset($data['survey']);
                $this->client->save(self::RESOURCE_URL, $data);
                $this->addFlash(
                    'success',
                    $this->translator->trans('messages.success.item.edit', ['%name%' => $item['description']], 'surveys')
                );

                return $this->redirectToRoute('survey_items_index', ['id' => $id]);
            } catch (ClientException $e) {
                $this->violationMapper->mapToForm($e, $form);
            }
        }

        return [
            'form' => $form->createView(),
            'item' => $item,
            'survey' => $survey,
        ];
    }

    #[Route(path: '/{itemId}/delete', name: 'survey_items_delete', methods: 'GET', requirements: ['id' => '\d+'])]
    #[IsGranted('FEATURE_SURVEY_DELETE')]
    public function delete($id, $itemId): RedirectResponse
    {
        try {
            $this->client->remove(static::RESOURCE_URL, $itemId);

            $this->addFlash(
                'success',
                $this->translator->trans('messages.success.item.delete', [], 'surveys')
            );
        } catch (ClientException $e) {
            $this->addFlash(
                'error',
                $this->translator->trans('messages.error.item.delete', [], 'surveys')
            );
        }

        return $this->redirectToRoute('survey_items_index', ['id' => $id]);
    }
}
