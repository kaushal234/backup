<?php

declare(strict_types=1);

namespace AppBundle\Controller\Quality\CalibratedTools;

use ApiBundle\Client;
use ApiBundle\Form\ViolationMapper;
use ApiBundle\Http\FileStreamedResponseFactory;
use ApiBundle\Iri\Iri;
use ApiBundle\Model\ApiData;
use AppBundle\Configuration\ApiValueResolverAttribute;
use AppBundle\Filters\Type\Quality\CalibratedTools\OutOfToleranceFormFilters;
use AppBundle\Form\Type\Quality\CalibratedTools\OutOfToleranceFormType;
use AppBundle\Manager\FileManager;
use AppBundle\Manager\Quality\CalibratedTools\Statuses\OutOfToleranceFormStatus;
use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpClient\Exception\ClientException;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route(path: '/quality/calibrated-tools/out-of-tolerance-forms', defaults: ['alvest_module' => 'CT', 'breadcrumb_label' => 'menu.out_of_tolerance_forms.title', 'moduleDomain' => 'quality_calibrated_otf'])]
class OutOfToleranceFormsController extends AbstractController
{
    final public const itemsPerPage = 10;
    final public const RESOURCE_URL = 'quality/calibrated_tools/out_of_tolerance_forms';

    private readonly Client $client;

    private readonly FormFactoryInterface $formFactory;

    private readonly ViolationMapper $violationMapper;

    private readonly TranslatorInterface $translator;

    private readonly FileStreamedResponseFactory $fileStreamedResponseFactory;

    private readonly FileManager $fileManager;

    public function __construct(Client $client, FormFactoryInterface $formFactory, ViolationMapper $violationMapper, TranslatorInterface $translator, FileStreamedResponseFactory $fileStreamedResponseFactory, FileManager $fileManager)
    {
        $this->client = $client;
        $this->formFactory = $formFactory;
        $this->violationMapper = $violationMapper;
        $this->translator = $translator;
        $this->fileStreamedResponseFactory = $fileStreamedResponseFactory;
        $this->fileManager = $fileManager;
    }

    /**
     * @return array
     */
    #[Route(path: '', name: 'quality_calibrated_otf_home', methods: 'GET')]
    #[Template('quality\calibrated_tools\out_of_tolerance_forms\index.html.twig')]
    public function index(Request $request)
    {
        $parameters = [];
        $parameters['page'] = $request->query->getInt('page', 1);
        $parameters['order'] = $request->query->all('order');
        $parameters['itemsPerPage'] = $request->query->get('itemsPerPage', self::itemsPerPage);

        $formFilters = $this->formFactory->createNamed('out_of_tolerance_forms', OutOfToleranceFormFilters::class, [], [
            'action' => $this->generateUrl('quality_calibrated_otf_home'),
            'method' => 'GET',
        ]);

        $formFilters->handleRequest($request);
        if ($formFilters->isSubmitted() && $formFilters->isValid()) {
            $parameters = array_merge($parameters, $formFilters->getData());
        }

        try {
            $otfs = $this->client->findBy(self::RESOURCE_URL, $parameters);
        } catch (ClientException $e) {
            $this->violationMapper->mapToForm($e, $formFilters);
            $otfs = [];
        }

        return [
            'otfs' => $otfs,
            'formFilters' => $formFilters->createView(),
            'itemsPerPage' => $parameters['itemsPerPage'],
            'currentPage' => $parameters['page'],
            'page' => $parameters['page'],
            'paginationUrl' => [
                'route' => $request->attributes->get('_route'),
                'parameters' => $request->query->all(),
            ],
        ];
    }

    #[Route(path: '/{toolId}/add', name: 'quality_calibrated_otf_add', methods: 'GET|POST', requirements: ['toolId' => '\d+'])]
    #[Template('quality\calibrated_tools\out_of_tolerance_forms\add.html.twig')]
    #[IsGranted('FEATURE_TOOL_WRITE')]
    public function add(Request $request, $toolId)
    {
        $form = $this->formFactory->createNamed('out_of_tolerance_forms', OutOfToleranceFormType::class, [], [
            'action' => $this->generateUrl('quality_calibrated_otf_add', ['toolId' => $toolId]),
        ]);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $data = $form->getData();

                $files = $form->get('files')->getData();

                $data = $this->cleanDataForApi($data);
                $data['tool'] = $toolId;

                $returnData = $this->client->save(\sprintf('quality/calibrated_tools/tools/%d/out_of_tolerance_forms', $toolId), $data);

                $this->uploadFiles($returnData, $files);

                $this->addFlash(
                    'success',
                    $this->translator->trans('out_of_tolerance_form.messages.success.add', [], 'out_of_tolerance_form')
                );

                return $this->redirectToRoute('quality_calibrated_otf_show', ['id' => Iri::id($returnData)]);
            } catch (ClientException $e) {
                $this->violationMapper->mapToForm($e, $form);
            }
        }

        return [
            'form' => $form->createView(),
            'toolId' => $toolId,
        ];
    }

    #[Route(path: '/{id}/edit', name: 'quality_calibrated_otf_edit', methods: 'GET|POST', requirements: ['id' => '\d+'])]
    #[Template('quality\calibrated_tools\out_of_tolerance_forms\edit.html.twig')]
    #[IsGranted('FEATURE_TOOL_WRITE')]
    public function edit(Request $request, $id)
    {
        $otf = $this->client->find(self::RESOURCE_URL, $id);
        $form = $this->formFactory->createNamed('out_of_tolerance_form', OutOfToleranceFormType::class, $otf, [
            'action' => $this->generateUrl('quality_calibrated_otf_edit', ['id' => $id]),
            'no_status' => false,
            'available_statuses' => array_combine($otf['availableStatuses'], $otf['availableStatuses']),
        ]);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $files = $form->get('files')->getData();
                $data = $form->getData();
                $data = $this->cleanDataForApi($data);
                $this->client->save(self::RESOURCE_URL, $data);

                $this->uploadFiles($otf, $files);

                $this->addFlash(
                    'success',
                    $this->translator->trans('out_of_tolerance_form.messages.success.edit', [], 'out_of_tolerance_form')
                );

                return $this->redirectToRoute('quality_calibrated_otf_show', ['id' => $id]);
            } catch (ClientException $e) {
                $this->violationMapper->mapToForm($e, $form);
            }
        }

        return [
            'otf' => $otf,
            'otfIndex' => $id,
            'form' => $form->createView(),
        ];
    }

    #[Route(path: '/{id}/show', name: 'quality_calibrated_otf_show', methods: 'GET', requirements: ['id' => '\d+'])]
    #[Template('quality\calibrated_tools\out_of_tolerance_forms\show.html.twig')]
    public function show($id)
    {
        $otf = $this->client->find(self::RESOURCE_URL, $id);

        $tool = null;
        try {
            $tool = $this->client->findOneBy('quality/calibrated_tools/tools', ['calibrationLogs' => $otf['calibrationLog']]);
        } catch (\RangeException $e) {
            // do nothing
        }

        return [
            'otf' => $otf,
            'otfIndex' => $id,
            'tool' => $tool,
        ];
    }

    #[Route(path: '/{id}/show-file/{fileId}', name: 'quality_calibrated_otf_show_file', methods: 'GET', requirements: ['id' => '\d+'])]
    public function showFile($id, $fileId)
    {
        return $this->fileStreamedResponseFactory->create(\sprintf('%s/%s/files/%s', self::RESOURCE_URL, $id, $fileId));
    }

    #[Route(path: '/{id}/delete-file/{fileId}', name: 'quality_calibrated_otf_delete_file', methods: 'GET', requirements: ['id' => '\d+'])]
    public function deleteFile(#[ApiValueResolverAttribute(parameters: ['resource' => 'quality/calibrated_tools/out_of_tolerance_forms'])] ApiData $outOfToleranceForm, $fileId): RedirectResponse
    {
        $this->fileManager->deleteFile($outOfToleranceForm, self::RESOURCE_URL, \sprintf('files/%s', $fileId));

        return $this->redirectToRoute('quality_calibrated_otf_show', ['id' => $outOfToleranceForm->getIriId()]);
    }

    private function uploadFiles($object, $files)
    {
        if (\is_array($files) && [] !== $files) {
            foreach ($files as $file) {
                $this->fileManager->uploadFile($object, $file, self::RESOURCE_URL, null, 'files');
            }
        }
    }

    private function cleanDataForApi($data)
    {
        if (isset($data['files'])) {
            unset($data['files']);
        }

        if (isset($data['availableStatuses'])) {
            unset($data['availableStatuses']);
        }

        if (!isset($data['status'])) {
            $data['status'] = OutOfToleranceFormStatus::IN_PROGRESS;
        }

        return $data;
    }
}
