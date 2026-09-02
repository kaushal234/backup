<?php

declare(strict_types=1);

namespace AppBundle\Controller\Quality\CalibratedTools;

use ActivityBundle\Form\Type\CommentType;
use ApiBundle\Client;
use ApiBundle\Form\ViolationMapper;
use ApiBundle\Http\CsvStreamedResponseFactory;
use ApiBundle\Http\FileStreamedResponseFactory;
use ApiBundle\Iri\Iri;
use ApiBundle\Model\ApiData;
use AppBundle\Configuration\ApiValueResolverAttribute;
use AppBundle\Filters\Type\Quality\CalibratedTools\ToolFilters;
use AppBundle\Form\Type\Quality\CalibratedTools\ToolCertificateFormType;
use AppBundle\Form\Type\Quality\CalibratedTools\ToolType;
use AppBundle\Manager\Quality\CalibratedTools\Statuses\ToolStatus;
use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpClient\Exception\ClientException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Mime\Part\DataPart;
use Symfony\Component\Mime\Part\Multipart\FormDataPart;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route(path: '/quality/calibrated-tools/tools', defaults: ['alvest_module' => 'CT', 'moduleDomain' => 'quality_calibrated_tools'])]
class ToolsController extends AbstractController
{
    final public const itemsPerPage = 10;
    final public const RESOURCE_URL = 'quality/calibrated_tools/tools';
    final public const CALIBRATION_LOGS_RESOURCE_URL = 'quality/calibrated_tools/calibration_logs';

    private readonly Client $client;
    private readonly FormFactoryInterface $formFactory;
    private readonly ViolationMapper $violationMapper;
    private readonly TranslatorInterface $translator;
    private readonly FileStreamedResponseFactory $fileStreamedResponseFactory;
    private readonly CsvStreamedResponseFactory $csvStreamedResponseFactory;

    public function __construct(Client $client, FormFactoryInterface $formFactory, ViolationMapper $violationMapper, TranslatorInterface $translator, FileStreamedResponseFactory $fileStreamedResponseFactory, CsvStreamedResponseFactory $csvStreamedResponseFactory)
    {
        $this->client = $client;
        $this->formFactory = $formFactory;
        $this->violationMapper = $violationMapper;
        $this->translator = $translator;
        $this->fileStreamedResponseFactory = $fileStreamedResponseFactory;
        $this->csvStreamedResponseFactory = $csvStreamedResponseFactory;
    }

    #[Route(path: '', name: 'quality_calibrated_tools_home', defaults: ['page' => 1, 'order' => [], 'itemsPerPage' => 10])]
    #[Template('quality\calibrated_tools\tools\index.html.twig')]
    public function index(Request $request)
    {
        $parameters = [];
        $parameters['page'] = $request->query->getInt('page', 1);
        $parameters['order'] = $request->query->all('order');
        $parameters['itemsPerPage'] = $request->query->get('itemsPerPage', static::itemsPerPage);

        $formFilters = $this->formFactory->createNamed('filter', ToolFilters::class, [], [
            'action' => $this->generateUrl('quality_calibrated_tools_home'),
            'method' => 'GET',
        ]);

        $formFilters->handleRequest($request);
        if ($formFilters->isSubmitted() && $formFilters->isValid()) {
            $parameters = array_merge($parameters, $formFilters->getData());
            if ($formFilters->getClickedButton() && 'csv' === $formFilters->getClickedButton()->getName()) {
                $parameters['normalizationGroupsOverride'] = ['tool_export'];

                return $this->csvStreamedResponseFactory->create(self::RESOURCE_URL, $parameters);
            }
        }

        try {
            $tools = $this->client->findBy(self::RESOURCE_URL, $parameters);
        } catch (ClientException $e) {
            $this->violationMapper->mapToForm($e, $formFilters);
            $tools = [];
        }

        return [
            'tools' => $tools,
            'formFilters' => $formFilters->createView(),
            'itemsPerPage' => $parameters['itemsPerPage'],
            'currentPage' => $parameters['page'],
            'paginationUrl' => [
                'route' => $request->attributes->get('_route'),
                'parameters' => $request->query->all(),
            ],
        ];
    }

    #[Route(path: '/{id}/show', name: 'quality_calibrated_tools_show', methods: 'GET', requirements: ['id' => '\d+'])]
    #[Template('quality\calibrated_tools\tools\show.html.twig')]
    public function show($id)
    {
        $tool = $this->client->find(static::RESOURCE_URL, $id);

        return [
            'tool' => $tool,
            'under_calibration' => ToolStatus::UNDER_CALIBRATION,
        ];
    }

    #[Route(path: '/{id}/delete', name: 'quality_calibrated_tools_delete', methods: 'GET|DELETE', requirements: ['id' => '\d+'])]
    #[IsGranted('FEATURE_TOOL_WRITE')]
    public function delete($id): RedirectResponse
    {
        try {
            $this->client->remove(static::RESOURCE_URL, $id);

            $this->addFlash(
                'success',
                $this->translator->trans('calibration_tool.messages.success.delete', [], 'calibration_tool')
            );
        } catch (ClientException $e) {
            $this->addFlash(
                'error',
                $this->translator->trans('calibration_tool.messages.error.delete', [], 'calibration_tool')
            );
        }

        return $this->redirectToRoute('quality_calibrated_tools_home');
    }

    #[Route(path: '/add', name: 'quality_calibrated_tools_add', methods: 'GET|POST')]
    #[Template('quality\calibrated_tools\tools\add.html.twig')]
    #[IsGranted('FEATURE_TOOL_WRITE')]
    public function add(Request $request)
    {
        $form = $this->formFactory->createNamed('calibration_tool', ToolType::class);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $data = $form->getData();
                $intervalUnit = $form->get('calibrationIntervalUnit')->getData();
                switch ($intervalUnit) {
                    case 'months':
                        $data['calibrationInterval'] *= 30;
                        break;
                    case 'weeks':
                        $data['calibrationInterval'] *= 7;
                        break;
                }

                $data = $this->cleanDataForApi($data);
                $data['purchasingDate'] = $form->get('purchasingDate')->getData();
                $data['nextCalibrationDate'] = $form->get('nextCalibrationDate')->getData();

                $data = $this->client->save(self::RESOURCE_URL, $data);

                $this->addFlash(
                    'success',
                    $this->translator->trans('calibration_tool.messages.success.add', [], 'calibration_tool')
                );

                return $this->redirectToRoute('quality_calibrated_tools_show', ['id' => Iri::id($data)]);
            } catch (ClientException $e) {
                $this->violationMapper->mapToForm($e, $form);
            }
        }

        return [
            'form' => $form->createView(),
        ];
    }

    #[Route(path: '/{toolId}/logs/{id}/delete', name: 'quality_calibrated_tools_delete_log', methods: 'GET', requirements: ['toolId' => '\d+', 'id' => '\d+'])]
    public function removeCalibrationLog($toolId, #[ApiValueResolverAttribute(parameters: ['resource' => 'quality/calibrated_tools/calibration_logs'])] ApiData $calibrationLog): RedirectResponse
    {
        if (!$this->isGranted('FEATURE_TOOL_WRITE')) {
            $this->addFlash(
                'error',
                $this->translator->trans('security.warning.access_denied', [], 'messages')
            );

            return $this->redirectToRoute('quality_calibrated_tools_show', ['id' => $toolId]);
        }

        $this->client->remove(static::CALIBRATION_LOGS_RESOURCE_URL, $calibrationLog->getIriId());

        $this->addFlash(
            'success',
            $this->translator->trans('calibration_log.messages.success.deleted', [], 'calibration_log')
        );

        return $this->redirectToRoute('quality_calibrated_tools_show', ['id' => $toolId]);
    }

    #[Route(path: '/{id}/certificate/{fileId}', name: 'quality_calibrated_tools_showFile', methods: 'GET', requirements: ['id' => '\d+', 'fileId' => '\d+'])]
    public function showFile($id, $fileId)
    {
        return $this->fileStreamedResponseFactory->create(\sprintf('%s/%s/file/%s', static::CALIBRATION_LOGS_RESOURCE_URL, $id, $fileId));
    }

    #[Route(path: '/{id}/edit', name: 'quality_calibrated_tools_edit', requirements: ['id' => '\d+'], methods: 'GET|POST')]
    #[Template('quality\calibrated_tools\tools\edit.html.twig')]
    #[IsGranted('FEATURE_TOOL_WRITE')]
    public function edit(Request $request, #[ApiValueResolverAttribute(parameters: ['resource' => self::RESOURCE_URL])] ApiData $tool)
    {
        $form = $this->formFactory->createNamed('calibration_tool', ToolType::class, $tool);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $data = $form->getData();

                $intervalUnit = $form->get('calibrationIntervalUnit')->getData();
                switch ($intervalUnit) {
                    case 'months':
                        $data['calibrationInterval'] *= 30;
                        break;
                    case 'weeks':
                        $data['calibrationInterval'] *= 7;
                        break;
                }

                $data = $this->cleanDataForApi($data);
                $this->client->save(self::RESOURCE_URL, $data);

                $this->addFlash(
                    'success',
                    $this->translator->trans('calibration_tool.messages.success.edit', [], 'calibration_tool')
                );

                return $this->redirectToRoute('quality_calibrated_tools_show', ['id' => $tool->getIriId()]);
            } catch (ClientException $e) {
                $this->violationMapper->mapToForm($e, $form);
            }
        }

        return [
            'tool' => $tool,
            'form' => $form->createView(),
        ];
    }

    #[Route(path: '/{id}/status/{status}', name: 'quality_calibrated_tools_status', requirements: ['id' => '\d+'], methods: ['GET', 'POST'])]
    #[IsGranted('FEATURE_TOOL_WRITE')]
    public function changeToolStatus($id, $status, #[ApiValueResolverAttribute(parameters: ['resource' => self::RESOURCE_URL])] ApiData $tool, Request $request)
    {
        if (ToolStatus::ACTIVE === $status) {
            return $this->redirectToRoute('quality_calibrated_tools_activate', ['id' => $id]);
        }

        if (ToolStatus::OUT_OF_SERVICE === $status) {
            $form = $this
                ->formFactory
                ->createNamed(
                    'tool_comment',
                    CommentType::class
                )
            ;

            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                $data = $form->getData();
                $data['resource'] = $tool->getIri();
                $this->client->save('comments', $data);
            } else {
                return $this->render('quality/calibrated_tools/tools/comment.html.twig', [
                    'form' => $form->createView(),
                    'formTitle' => $this->translator->trans('calibration_tool.title.comment', [], 'calibration_tool'),
                ]);
            }
        }

        try {
            $this->client->put(\sprintf('%s/%d/status', self::RESOURCE_URL, $id), ['json' => ['status' => $status]]);
        } catch (ClientException $e) {
            $errorDescription = json_decode($e->getResponse()->getContent(false), true);
            $this->addFlash(
                'error',
                \sprintf('%s. %s', $this->translator->trans('security.warning.access_denied', [], 'messages'), $errorDescription['hydra:description'])
            );
        }

        return $this->redirectToRoute('quality_calibrated_tools_show', ['id' => $id]);
    }

    #[Route(path: '/{id}/activate', name: 'quality_calibrated_tools_activate', methods: 'GET|POST', requirements: ['id' => '\d+'])]
    #[Template('quality\calibrated_tools\tools\activate.html.twig')]
    #[IsGranted('FEATURE_TOOL_WRITE')]
    public function activateTool($id, Request $request)
    {
        $tool = $this->client->find(self::RESOURCE_URL, $id);

        // If the tool has never been sent to calibration, we first need to create a log to attach this file
        if (0 === \count($tool['calibrationLogs'])) {
            $this->client->put(\sprintf('%s/%d/status', self::RESOURCE_URL, $id), ['json' => ['status' => ToolStatus::UNDER_CALIBRATION]]);
        }

        if (!\in_array($tool['status'], [ToolStatus::UNDER_CALIBRATION, ToolStatus::OUT_OF_SERVICE], true)) {
            $this->addFlash(
                'error',
                $this->translator->trans('security.warning.access_denied', [], 'messages')
            );

            return $this->redirectToRoute('quality_calibrated_tools_show', ['id' => $id]);
        }

        $form = $this->formFactory->createNamed('calibration_tool_activation', ToolCertificateFormType::class);

        $calibrationLogs = $tool['calibrationLogs'];
        $lastCalibrationLog = end($calibrationLogs);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $this->client->save(self::RESOURCE_URL, [
                    '@id' => $tool['@id'],
                    'nextCalibrationDate' => $form->get('nextCalibrationDate')->getData(),
                ]);

                /** @var UploadedFile $certFile */
                $certFile = $form->get('certificationFile')->getData();
                $requestUrl = \sprintf('%s/%s/file', static::CALIBRATION_LOGS_RESOURCE_URL, Iri::id($lastCalibrationLog));
                $multiPart['file'] = DataPart::fromPath($certFile->getPathname(), $certFile->getClientOriginalName());
                $multiPart['metadata[calibration_date]'] = $form->get('calibrationDate')->getData();

                $formData = new FormDataPart($multiPart);

                $this->client->request($requestUrl, null, null, 'POST', [
                    'headers' => $formData->getPreparedHeaders()->toArray(),
                    'body' => $formData->bodyToIterable(),
                ]);

                $this->client->put(\sprintf('%s/%d/status', self::RESOURCE_URL, $id), ['json' => ['status' => ToolStatus::ACTIVE]]);

                $this->addFlash(
                    'success',
                    $this->translator->trans('calibration_log.messages.success.cert_file', [], 'calibration_log')
                );

                if (true === $form->get('otf')->getData()) {
                    return $this->redirectToRoute('quality_calibrated_otf_add', ['toolId' => $id]);
                }

                return $this->redirectToRoute('quality_calibrated_tools_show', ['id' => $id]);
            } catch (ClientException $e) {
                $this->violationMapper->mapToForm($e, $form);
            }
        }

        return [
            'form' => $form->createView(),
            'tool' => $tool,
        ];
    }

    private function cleanDataForApi($data)
    {
        foreach (['availableStatuses', 'createdBy', 'calibrationLogs'] as $forbiddenKeys) {
            unset($data[$forbiddenKeys]);
        }

        return $data;
    }
}
