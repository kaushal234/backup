<?php

declare(strict_types=1);

namespace AppBundle\Controller\Survey;

use ApiBundle\Client;
use ApiBundle\Form\ViolationMapper;
use AppBundle\Form\Type\Survey\RatingType;
use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpClient\Exception\ClientException;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route(path: '/surveys/{id}/rating-types', defaults: ['alvest_module' => 'SRV'])]
class RatingController extends AbstractController
{
    final public const itemsPerPage = 10;
    final public const RESOURCE_URL = 'surveys/rating_types';

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

    #[Route(path: '', name: 'survey_rating_types_index', methods: 'GET', defaults: ['page' => 1, 'order' => [], 'itemsPerPage' => 10])]
    #[Template('surveys\rating_types\index.html.twig')]
    public function index(Request $request, $id)
    {
        $survey = $this->client->find(SurveyController::RESOURCE_URL, $id);

        $parameters = [];
        $parameters['page'] = $request->query->getInt('page', 1);
        $parameters['order'] = $request->query->all('order');
        $parameters['itemsPerPage'] = $request->query->get('itemsPerPage', static::itemsPerPage);
        $parameters['survey'] = $survey['@id'];

        try {
            $ratingTypes = $this->client->findBy(self::RESOURCE_URL, $parameters);
        } catch (ClientException $e) {
            $ratingTypes = [];
        }

        return [
            'survey' => $survey,
            'ratingTypes' => $ratingTypes,
            'itemsPerPage' => $parameters['itemsPerPage'],
            'currentPage' => $parameters['page'],
        ];
    }

    #[Route(path: '/add', name: 'survey_rating_types_add', methods: 'GET|POST')]
    #[Template('surveys\rating_types\add.html.twig')]
    #[IsGranted('FEATURE_SURVEY_WRITE')]
    public function add(Request $request, $id)
    {
        $survey = $this->client->find(SurveyController::RESOURCE_URL, $id);

        $form = $this->formFactory->createNamed('rating_type', RatingType::class);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $data = $form->getData();
                $data['survey'] = $survey['@id'];

                $this->client->save(self::RESOURCE_URL, $data);

                $this->addFlash(
                    'success',
                    $this->translator->trans('messages.success.rating.add', ['%name%' => $data['description']], 'surveys')
                );

                return $this->redirectToRoute('survey_rating_types_index', ['id' => $id]);
            } catch (ClientException $e) {
                $this->violationMapper->mapToForm($e, $form);
            }
        }

        return [
            'survey' => $survey,
            'form' => $form->createView(),
        ];
    }

    #[Route(path: '/{ratingId}/edit', name: 'survey_rating_types_edit', requirements: ['id' => '\d+'], methods: 'GET|POST')]
    #[Template('surveys\rating_types\edit.html.twig')]
    #[IsGranted('FEATURE_SURVEY_WRITE')]
    public function edit(Request $request, $id, $ratingId)
    {
        $ratingType = $this->client->find(self::RESOURCE_URL, $ratingId);
        $survey = $this->client->find(SurveyController::RESOURCE_URL, $id);

        $form = $this->formFactory->createNamed('ratingType', RatingType::class, $ratingType);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $data = $form->getData();

                $data['survey'] = $survey['@id'];

                $this->client->save(self::RESOURCE_URL, $data);
                $this->addFlash(
                    'success',
                    $this->translator->trans('messages.success.rating.edit', ['%name%' => $ratingType['description']], 'surveys')
                );

                return $this->redirectToRoute('survey_rating_types_index', ['id' => $id]);
            } catch (ClientException $e) {
                $this->violationMapper->mapToForm($e, $form);
            }
        }

        return [
            'form' => $form->createView(),
            'survey' => $survey,
            'ratingType' => $ratingType,
        ];
    }

    #[Route(path: '/{ratingId}/show', name: 'survey_rating_types_show', methods: 'GET', requirements: ['id' => '\d+'])]
    #[Template('surveys\rating_types\show.html.twig')]
    public function show($id, $ratingId)
    {
        $survey = $this->client->find(SurveyController::RESOURCE_URL, $id);

        $ratingType = $this->client->find(static::RESOURCE_URL, $ratingId);

        return [
            'survey' => $survey,
            'ratingType' => $ratingType,
        ];
    }

    #[Route(path: '/{ratingId}/delete', name: 'survey_rating_types_delete', methods: 'GET', requirements: ['id' => '\d+'])]
    #[IsGranted('FEATURE_SURVEY_DELETE')]
    public function delete($id, $ratingId): RedirectResponse
    {
        try {
            $this->client->remove(static::RESOURCE_URL, $ratingId);

            $this->addFlash(
                'success',
                $this->translator->trans('messages.success.rating.delete', [], 'surveys')
            );
        } catch (ClientException $e) {
            $this->addFlash(
                'error',
                $this->translator->trans('messages.error.rating.delete', [], 'surveys')
            );
        }

        return $this->redirectToRoute('survey_rating_types_index', ['id' => $id]);
    }
}
