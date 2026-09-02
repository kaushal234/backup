<?php

declare(strict_types=1);

namespace AppBundle\Controller\Quality\CalibratedTools;

use ApiBundle\Client;
use ApiBundle\Form\ViolationMapper;
use ApiBundle\Iri\Iri;
use AppBundle\Filters\Type\Quality\CalibratedTools\ToolTypeFilters;
use AppBundle\Form\Type\Quality\CalibratedTools\ToolTypeType as ToolTypeForm;
use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpClient\Exception\ClientException;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route(path: '/quality/calibrated-tools/tool-types', defaults: ['alvest_module' => 'CT', 'breadcrumb_label' => 'menu.tool_types.title', 'moduleDomain' => 'quality_calibrated_tool_types'])]
class ToolTypesController extends AbstractController
{
    final public const RESOURCE_URL = 'quality/calibrated_tools/tool_types';

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

    #[Route(path: '', name: 'quality_calibrated_tool_types_home')]
    #[Template('quality\calibrated_tools\tool_types\index.html.twig')]
    public function index(Request $request)
    {
        $parameters = [];

        $formFilters = $this->formFactory->createNamed('tool_type_filters', ToolTypeFilters::class, [], [
            'action' => $this->generateUrl('quality_calibrated_tool_types_home'),
            'method' => 'GET',
        ]);

        $formFilters->handleRequest($request);
        if ($formFilters->isSubmitted() && $formFilters->isValid()) {
            $parameters = array_merge($parameters, $formFilters->getData());
        }

        try {
            $toolTypes = $this->client->findBy(self::RESOURCE_URL, $parameters);
        } catch (ClientException $e) {
            $this->violationMapper->mapToForm($e, $formFilters);
            $toolTypes = [];
        }

        return [
            'toolTypes' => $toolTypes,
            'form' => $formFilters->createView(),
            'paginationUrl' => [
                'route' => $request->attributes->get('_route'),
                'parameters' => $request->query->all(),
            ],
        ];
    }

    #[Route(path: '/{id}/show', name: 'quality_calibrated_tool_types_show', methods: 'GET', requirements: ['id' => '\d+'])]
    #[Template('quality\calibrated_tools\tool_types\show.html.twig')]
    public function show($id)
    {
        $toolType = $this->client->find(static::RESOURCE_URL, $id);

        $tools = $this->client->findBy(ToolsController::RESOURCE_URL, [
            'toolType' => $toolType['@id'],
        ]);

        return [
            'toolType' => $toolType,
            'tools' => $tools,
        ];
    }

    #[Route(path: '/{id}/delete', name: 'quality_calibrated_tool_types_delete', methods: 'GET|DELETE', requirements: ['id' => '\d+'])]
    #[IsGranted('FEATURE_TOOL_WRITE')]
    public function delete($id): RedirectResponse
    {
        try {
            $this->client->remove(static::RESOURCE_URL, $id);

            $this->addFlash(
                'success',
                $this->translator->trans('tool_types.messages.success.delete', [], 'tool_types')
            );
        } catch (ClientException $e) {
            $this->addFlash(
                'error',
                $this->translator->trans('tool_types.messages.error.delete', [], 'tool_types')
            );
        }

        return $this->redirectToRoute('quality_calibrated_tool_types_home');
    }

    #[Route(path: '/add', name: 'quality_calibrated_tool_types_add', methods: 'GET|POST')]
    #[Template('quality\calibrated_tools\tool_types\add.html.twig')]
    #[IsGranted('FEATURE_TOOL_WRITE')]
    public function add(Request $request)
    {
        $form = $this->formFactory->createNamed('tool_type', ToolTypeForm::class);
        if ($request->isMethod('post') && $form->handleRequest($request)->isSubmitted() && $form->isValid()) {
            try {
                $data = $form->getData();
                $data = $this->client->save(self::RESOURCE_URL, $data);

                $this->addFlash(
                    'success',
                    $this->translator->trans('tool_types.messages.success.add', [], 'tool_types')
                );

                return $this->redirectToRoute('quality_calibrated_tool_types_show', ['id' => Iri::id($data)]);
            } catch (ClientException $e) {
                $this->violationMapper->mapToForm($e, $form);
            }
        }

        return [
            'form' => $form->createView(),
        ];
    }

    #[Route(path: '/{id}/edit', name: 'quality_calibrated_tool_types_edit', methods: 'GET|POST', requirements: ['id' => '\d+'])]
    #[Template('quality\calibrated_tools\tool_types\edit.html.twig')]
    #[IsGranted('FEATURE_TOOL_WRITE')]
    public function edit(Request $request, $id)
    {
        $toolType = $this->client->find(self::RESOURCE_URL, $id);

        $form = $this->formFactory->createNamed('tool_type', ToolTypeForm::class, $toolType);

        if ($form->handleRequest($request)->isSubmitted() && $form->isValid()) {
            try {
                $data = $form->getData();
                $this->client->save(self::RESOURCE_URL.'/'.$id, $data);

                $this->addFlash(
                    'success',
                    $this->translator->trans('tool_types.messages.success.edit', [], 'tool_types')
                );

                return $this->redirectToRoute('quality_calibrated_tool_types_show', ['id' => $id]);
            } catch (ClientException $e) {
                $this->violationMapper->mapToForm($e, $form);
            }
        }

        return [
            'toolType' => $toolType,
            'form' => $form->createView(),
        ];
    }
}
