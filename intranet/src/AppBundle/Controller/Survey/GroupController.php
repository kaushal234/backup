<?php

declare(strict_types=1);

namespace AppBundle\Controller\Survey;

use ApiBundle\Client;
use ApiBundle\Form\ViolationMapper;
use AppBundle\Filters\Type\Survey\GroupsFilters;
use AppBundle\Form\Type\Survey\GroupType;
use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpClient\Exception\ClientException;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route(path: '/surveys/{id}/groups', defaults: ['alvest_module' => 'SRV'])]
class GroupController extends AbstractController
{
    final public const itemsPerPage = 10;
    final public const RESOURCE_URL = 'surveys/groups';

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

    /**
     * @return array
     */
    #[Route(path: '', name: 'survey_groups_index', methods: 'GET', defaults: ['page' => 1, 'order' => [], 'itemsPerPage' => 10])]
    #[Template('surveys\groups\index.html.twig')]
    public function index(Request $request, $id)
    {
        $survey = $this->client->find(SurveyController::RESOURCE_URL, $id);

        $parameters = [];
        $parameters['page'] = $request->query->getInt('page', 1);
        $parameters['order'] = $request->query->all('order');
        $parameters['itemsPerPage'] = $request->query->get('itemsPerPage', static::itemsPerPage);
        $parameters['survey'] = $survey['@id'];

        $formFilters = $this->formFactory->createNamed('group_filter', GroupsFilters::class, [], [
            'action' => $this->generateUrl('survey_groups_index', ['id' => $id]),
            'method' => 'GET',
        ]);

        $formFilters->handleRequest($request);
        if ($formFilters->isSubmitted() && $formFilters->isValid()) {
            $parameters = array_merge($parameters, $formFilters->getData());
        }

        try {
            $groups = $this->client->findBy(self::RESOURCE_URL, $parameters);
        } catch (ClientException $e) {
            $this->violationMapper->mapToForm($e, $formFilters);
            $groups = [];
        }

        return [
            'survey' => $survey,
            'groups' => $groups,
            'formFilters' => $formFilters->createView(),
            'itemsPerPage' => $parameters['itemsPerPage'],
            'currentPage' => $parameters['page'],
            'paginationUrl' => [
                'route' => $request->attributes->get('_route'),
                'parameters' => ['id' => $id],
            ],
        ];
    }

    /**
     * @return array|RedirectResponse
     */
    #[Route(path: '/add', name: 'survey_groups_add', methods: 'GET|POST')]
    #[Template('surveys\groups\add.html.twig')]
    #[IsGranted('FEATURE_SURVEY_WRITE')]
    public function add(Request $request, $id)
    {
        $survey = $this->client->find(SurveyController::RESOURCE_URL, $id);

        $form = $this->formFactory->createNamed('survey_group', GroupType::class);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $data = $form->getData();
                $data['sorting'] = 0;
                $data['survey'] = $survey['@id'];
                $this->client->save(self::RESOURCE_URL, $data);
                $this->addFlash(
                    'success',
                    $this->translator->trans('messages.success.group.add', ['%name%' => $data['name']], 'surveys')
                );

                return $this->redirectToRoute('survey_groups_index', ['id' => $id]);
            } catch (ClientException $e) {
                $this->violationMapper->mapToForm($e, $form);
            }
        }

        return [
            'survey' => $survey,
            'form' => $form->createView(),
        ];
    }

    #[Route(path: '/{groupId}/edit', name: 'survey_groups_edit', requirements: ['id' => '\d+'], methods: 'GET|POST')]
    #[Template('surveys\groups\edit.html.twig')]
    #[IsGranted('FEATURE_SURVEY_WRITE')]
    public function edit(Request $request, $id, $groupId)
    {
        $survey = $this->client->find(SurveyController::RESOURCE_URL, $id);
        $group = $this->client->find(self::RESOURCE_URL, $groupId);

        $form = $this->formFactory->createNamed('survey_group', GroupType::class, $group);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $data = $form->getData();
                $data['survey'] = $survey['@id'];
                $this->client->save(self::RESOURCE_URL, $data);
                $this->addFlash(
                    'success',
                    $this->translator->trans('messages.success.group.edit', ['%name%' => $group['name']], 'surveys')
                );

                return $this->redirectToRoute('survey_groups_index', ['id' => $id]);
            } catch (ClientException $e) {
                $this->violationMapper->mapToForm($e, $form);
            }
        }

        return [
            'survey' => $survey,
            'form' => $form->createView(),
            'group' => $group,
        ];
    }

    #[Route(path: '/{groupId}/show', name: 'survey_groups_show', methods: 'GET', requirements: ['id' => '\d+'])]
    #[Template('surveys\groups\show.html.twig')]
    public function show($id, $groupId)
    {
        $survey = $this->client->find(SurveyController::RESOURCE_URL, $id);

        $group = $this->client->find(static::RESOURCE_URL, $groupId);

        return [
            'survey' => $survey,
            'group' => $group,
        ];
    }

    #[Route(path: '/{groupId}/delete', name: 'survey_groups_delete', methods: 'GET', requirements: ['id' => '\d+'])]
    #[IsGranted('FEATURE_SURVEY_DELETE')]
    public function delete($id, $groupId): RedirectResponse
    {
        try {
            $this->client->remove(static::RESOURCE_URL, $groupId);

            $this->addFlash(
                'success',
                $this->translator->trans('messages.success.group.delete', [], 'surveys')
            );
        } catch (ClientException $e) {
            $this->addFlash(
                'error',
                $this->translator->trans('messages.error.group.delete', [], 'surveys')
            );
        }

        return $this->redirectToRoute('survey_groups_index', ['id' => $id]);
    }
}
