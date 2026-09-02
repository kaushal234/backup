<?php

declare(strict_types=1);

namespace AppBundle\Controller\Survey;

use ApiBundle\Client;
use ApiBundle\Form\ViolationMapper;
use AppBundle\Filters\Type\Survey\SurveyFilters;
use AppBundle\Form\Type\Survey\SurveyEditType;
use AppBundle\Form\Type\Survey\SurveyType;
use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpClient\Exception\ClientException;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route(path: '/surveys', defaults: ['alvest_module' => 'SRV'])]
class SurveyController extends AbstractController
{
    final public const itemsPerPage = 10;
    final public const RESOURCE_URL = 'surveys/models';

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

    #[Route(path: '', name: 'surveys_index', methods: 'GET', defaults: ['page' => 1, 'order' => [], 'itemsPerPage' => 10])]
    #[Template('surveys\survey\index.html.twig')]
    public function index(Request $request)
    {
        $parameters = [];
        $parameters['page'] = $request->query->getInt('page', 1);
        $parameters['order'] = $request->query->all('order');
        $parameters['itemsPerPage'] = $request->query->get('itemsPerPage', static::itemsPerPage);

        $formFilters = $this->formFactory->createNamed('survey_filters', SurveyFilters::class, [], [
            'action' => $this->generateUrl('surveys_index'),
            'method' => 'GET',
        ]);

        $formFilters->handleRequest($request);
        if ($formFilters->isSubmitted() && $formFilters->isValid()) {
            $parameters = array_merge($parameters, $formFilters->getData());
        }

        try {
            $surveys = $this->client->findBy(self::RESOURCE_URL, $parameters);
        } catch (ClientException $e) {
            $this->violationMapper->mapToForm($e, $formFilters);
            $surveys = [];
        }

        return [
            'surveys' => $surveys,
            'formFilters' => $formFilters->createView(),
            'itemsPerPage' => $parameters['itemsPerPage'],
            'currentPage' => $parameters['page'],
            'paginationUrl' => [
                'route' => $request->attributes->get('_route'),
                'parameters' => $request->query->all(),
            ],
        ];
    }

    #[Route(path: '/{id}/show', name: 'surveys_show', methods: 'GET', requirements: ['id' => '\d+'])]
    #[Template('surveys\survey\show.html.twig')]
    public function show($id)
    {
        $survey = $this->client->find(static::RESOURCE_URL, $id);

        $campaigns = $this->client->findBy(CampaignController::CAMPAIGN_URL, ['model' => $survey->getIriId()]);

        return [
            'survey' => $survey,
            'campaigns' => $campaigns,
        ];
    }

    #[Route(path: '/add', name: 'surveys_add', methods: 'GET|POST')]
    #[Template('surveys\survey\add.html.twig')]
    #[IsGranted('FEATURE_SURVEY_WRITE')]
    public function add(Request $request)
    {
        $form = $this->formFactory->createNamed('survey', SurveyType::class);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $data = $form->getData();
                $this->client->save(self::RESOURCE_URL, $data);
                $this->addFlash(
                    'success',
                    $this->translator->trans('messages.success.survey.add', [], 'surveys')
                );

                return $this->redirectToRoute('surveys_index');
            } catch (ClientException $e) {
                $this->violationMapper->mapToForm($e, $form);
            }
        }

        return [
            'form' => $form->createView(),
        ];
    }

    #[Route(path: '/{id}/edit', name: 'surveys_edit', requirements: ['id' => '\d+'], methods: 'GET|POST')]
    #[Template('surveys\survey\edit.html.twig')]
    #[IsGranted('FEATURE_SURVEY_WRITE')]
    public function edit(Request $request, $id)
    {
        $survey = $this->client->find(self::RESOURCE_URL, $id);

        if (!empty($survey['expirationDate'])) {
            $date = new \DateTime($survey['expirationDate']);
            $survey['expirationDate'] = $date->format('d-m-Y');
        }

        $form = $this->formFactory->createNamed('survey', SurveyEditType::class, $survey);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $data = $form->getData();
                $data['id'] = (int) $data['id'];
                $this->client->save(self::RESOURCE_URL, $data);
                $this->addFlash(
                    'success',
                    $this->translator->trans('messages.success.survey.edit', [], 'surveys')
                );

                return $this->redirectToRoute('surveys_index', []);
            } catch (ClientException $e) {
                $this->violationMapper->mapToForm($e, $form);
            }
        }

        return [
            'form' => $form->createView(),
            'survey' => $survey,
        ];
    }

    #[Route(path: '/{id}/delete', name: 'surveys_delete', methods: 'GET|DELETE', requirements: ['id' => '\d+'])]
    #[IsGranted('FEATURE_SURVEY_DELETE')]
    public function delete($id): RedirectResponse
    {
        try {
            $this->client->remove(static::RESOURCE_URL, $id);

            $this->addFlash(
                'success',
                $this->translator->trans('messages.success.survey.delete', [], 'surveys')
            );
        } catch (ClientException $e) {
            $this->addFlash(
                'error',
                $this->translator->trans('messages.error.survey.delete', [], 'surveys')
            );
        }

        return $this->redirectToRoute('surveys_index');
    }
}
