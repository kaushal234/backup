<?php

declare(strict_types=1);

namespace AppBundle\Controller\Quality;

use ApiBundle\Client;
use ApiBundle\Form\ViolationMapper;
use ApiBundle\Iri\Iri;
use AppBundle\Controller\Quality\CalibratedTools\ToolsController;
use AppBundle\Filters\Type\Quality\LocationAreaType as LocationAreaFilterType;
use AppBundle\Form\Type\Quality\LocationAreaType as LocationAreaFormType;
use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpClient\Exception\ClientException;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route(path: '/quality/location_areas', defaults: ['alvest_module' => 'CT', 'breadcrumb_label' => 'calibration_tools.titles.location_areas', 'moduleDomain' => 'quality_location_areas'])]
class LocationAreasController extends AbstractController
{
    final public const itemsPerPage = 8;
    final public const RESOURCE_URL = 'location_areas';

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

    #[Route(path: '', name: 'quality_location_areas_home', defaults: ['page' => 1, 'order' => [], 'itemsPerPage' => 8])]
    #[Template('quality\location_areas\index.html.twig')]
    public function index(Request $request)
    {
        $parameters = [];
        $parameters['page'] = $request->query->getInt('page', 1);
        $parameters['order'] = $request->query->all('order');
        $parameters['itemsPerPage'] = $request->query->get('itemsPerPage', static::itemsPerPage);

        $formFilters = $this->formFactory->createNamed('location_area', LocationAreaFilterType::class, [], [
            'action' => $this->generateUrl('quality_calibrated_tools_home'),
            'method' => 'GET',
        ]);

        $formFilters->handleRequest($request);
        if ($formFilters->isSubmitted() && $formFilters->isValid()) {
            $parameters = array_merge($parameters, $formFilters->getData());
        }

        try {
            $locationsAreas = $this->client->findBy(self::RESOURCE_URL, $parameters);
        } catch (ClientException $e) {
            $this->violationMapper->mapToForm($e, $formFilters);
            $locationsAreas = [];
        }

        return [
            'locationsAreas' => $locationsAreas,
            'form' => $formFilters->createView(),
            'itemsPerPage' => $parameters['itemsPerPage'],
            'currentPage' => $parameters['page'],
            'paginationUrl' => [
                'route' => $request->attributes->get('_route'),
                'parameters' => $request->query->all(),
            ],
        ];
    }

    #[Route(path: '/add', name: 'quality_location_areas_add', methods: 'GET|POST')]
    #[Template('quality\location_areas\add.html.twig')]
    #[IsGranted('FEATURE_TOOL_WRITE')]
    public function add(Request $request)
    {
        $form = $this->formFactory->createNamed('location_area', LocationAreaFormType::class);
        if ($request->isMethod('post') && $form->handleRequest($request)->isSubmitted() && $form->isValid()) {
            try {
                $data = $form->getData();
                $data = $this->client->save(self::RESOURCE_URL, $data);

                $this->addFlash(
                    'success',
                    $this->translator->trans('location_areas.messages.success.add', [], 'location_areas')
                );

                return $this->redirectToRoute('quality_location_areas_show', ['id' => Iri::id($data)]);
            } catch (ClientException $e) {
                $this->violationMapper->mapToForm($e, $form);
            }
        }

        return [
            'form' => $form->createView(),
        ];
    }

    #[Route(path: '/{id}/show', name: 'quality_location_areas_show', methods: 'GET', requirements: ['id' => '\d+'])]
    #[Template('quality\location_areas\show.html.twig')]
    public function show($id)
    {
        try {
            $locationArea = $this->client->find(static::RESOURCE_URL, $id);
        } catch (ClientException $e) {
            throw $this->createNotFoundException('Location Area not found');
        }

        $tools = $this->client->findBy(ToolsController::RESOURCE_URL, [
            'locationArea' => $locationArea['@id'],
        ]);

        return [
            'locationArea' => $locationArea,
            'tools' => $tools,
        ];
    }

    #[Route(path: '/{id}/edit', name: 'quality_location_areas_edit', methods: 'GET|POST', requirements: ['id' => '\d+'])]
    #[Template('quality\location_areas\edit.html.twig')]
    #[IsGranted('FEATURE_TOOL_WRITE')]
    public function edit(Request $request, $id)
    {
        $locationArea = $this->client->find(self::RESOURCE_URL, $id);

        $form = $this->formFactory->createNamed('location_area', LocationAreaFormType::class, $locationArea);

        if ($form->handleRequest($request)->isSubmitted() && $form->isValid()) {
            try {
                $data = $form->getData();
                $this->client->save(self::RESOURCE_URL, $data);

                $this->addFlash(
                    'success',
                    $this->translator->trans('location_areas.messages.success.edit', [], 'location_areas')
                );

                return $this->redirectToRoute('quality_location_areas_show', ['id' => $id]);
            } catch (ClientException $e) {
                $this->violationMapper->mapToForm($e, $form);
            }
        }

        return [
            'locationArea' => $locationArea,
            'form' => $form->createView(),
        ];
    }

    #[Route(path: '/{id}/delete', name: 'quality_location_areas_delete', methods: 'GET|DELETE', requirements: ['id' => '\d+'])]
    #[IsGranted('FEATURE_TOOL_WRITE')]
    public function delete($id): RedirectResponse
    {
        try {
            $this->client->remove(static::RESOURCE_URL, $id);

            $this->addFlash(
                'success',
                $this->translator->trans('location_areas.messages.success.delete', [], 'location_areas')
            );
        } catch (ClientException $e) {
            $this->addFlash(
                'error',
                $this->translator->trans('location_areas.messages.error.delete', [], 'location_areas')
            );
        }

        return $this->redirectToRoute('quality_location_areas_home');
    }
}
