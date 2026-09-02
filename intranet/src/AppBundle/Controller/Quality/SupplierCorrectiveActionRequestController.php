<?php

declare(strict_types=1);

namespace AppBundle\Controller\Quality;

use ApiBundle\Client;
use ApiBundle\ClientExceptionMapper;
use ApiBundle\Form\ViolationMapper;
use ApiBundle\Http\FileStreamedResponseFactory;
use ApiBundle\Iri\Iri;
use ApiBundle\Model\ApiData;
use AppBundle\Configuration\ApiValueResolverAttribute;
use AppBundle\DataTable\Query\ApiProxyQuery;
use AppBundle\DataTable\Type\Quality\SupplierCorrectiveActionRequest\SupplierCorrectiveActionRequestDataTableType;
use AppBundle\Filters\Type\FactorySupplierReportFilter;
use AppBundle\Form\Type\Quality\SupplierCorrectiveActionRequest\SupplierCorrectiveActionRequestCommentFileType;
use AppBundle\Form\Type\SimpleFileType;
use AppBundle\Form\Type\StatusChoiceType;
use AppBundle\Manager\FileManager;
use Kreyu\Bundle\DataTableBundle\DataTableFactoryAwareTrait;
use Kreyu\Bundle\DataTableBundle\DataTableTurboResponseTrait;
use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\ExpressionLanguage\Expression;
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

#[Route(path: '/quality/supplier-corrective-action-requests', defaults: ['alvest_module' => 'SCAR', 'moduleDomain' => 'supplier_corrective_action_request'])]
class SupplierCorrectiveActionRequestController extends AbstractController
{
    use DataTableFactoryAwareTrait;
    use DataTableTurboResponseTrait;

    final public const RESOURCE_URL = 'quality/supplier_corrective_action_requests';
    final public const RESOURCE_URL_COMMENT = 'comments';
    final public const RESOURCE_URL_SUPPLIER = 'ion/business_partners';

    private readonly Client $client;
    private readonly FormFactoryInterface $formFactory;
    private readonly FileManager $fileManager;
    private readonly TranslatorInterface $translator;
    private readonly ViolationMapper $violationMapper;
    private readonly ClientExceptionMapper $clientExceptionMapper;
    private readonly FileStreamedResponseFactory $fileStreamedResponseFactory;

    public function __construct(Client $client, FormFactoryInterface $formFactory, FileManager $fileManager, TranslatorInterface $translator, ViolationMapper $violationMapper, ClientExceptionMapper $clientExceptionMapper, FileStreamedResponseFactory $fileStreamedResponseFactory)
    {
        $this->client = $client;
        $this->formFactory = $formFactory;
        $this->fileManager = $fileManager;
        $this->translator = $translator;
        $this->violationMapper = $violationMapper;
        $this->clientExceptionMapper = $clientExceptionMapper;
        $this->fileStreamedResponseFactory = $fileStreamedResponseFactory;
    }

    #[Route(name: 'supplier_corrective_action_request_home', methods: ['GET', 'POST'])]
    #[Template('quality/supplier_corrective_action_request/home.html.twig')]
    public function home(Request $request)
    {
        $dataTable = $this->createDataTable(SupplierCorrectiveActionRequestDataTableType::class, SupplierCorrectiveActionRequestDataTableType::RESOURCE);
        $dataTable->handleRequest($request);
        if ($dataTable->isExporting() && $dataTable->getQuery() instanceof ApiProxyQuery) {
            return $dataTable->getQuery()->export();
        }
        if ($dataTable->isRequestFromTurboFrame()) {
            return $this->createDataTableTurboResponse($dataTable);
        }

        // Remove filters with ALL value from the matrix datatable
        // In order to not send ALL value of status/factury in DataTable that it does not understand.
        $filters = $request->query->all('filter_supplier_corrective_action_request');
        if (isset($filters['factory']['value']) && 'ALL' === $filters['factory']['value']) {
            unset($filters['factory']);
        }
        if (isset($filters['status']['value']) && 'ALL' === $filters['status']['value']) {
            unset($filters['status']);
        }
        $request->query->set('filter_supplier_corrective_action_request', $filters);

        return [
            'supplierCorrectiveActionRequestsByFactoryStatus' => $this->client->get('reports/resource=/quality/supplier_corrective_action_requests;x=factory.name;y=status'),
            'dataTable' => $dataTable->createView(),
        ];
    }

    #[Route(path: '/{id}/show', name: 'supplier_corrective_action_request_show', methods: ['GET', 'POST'])]
    #[Template('quality/supplier_corrective_action_request/show.html.twig')]
    public function show(#[ApiValueResolverAttribute(parameters: ['resource' => self::RESOURCE_URL, 'filters' => ['normalizationGroups' => ['workflow']]])] ApiData $supplierCorrectiveActionRequest, Request $request)
    {
        try {
            $supplier = $this->client->get(\sprintf('%s/%s', self::RESOURCE_URL_SUPPLIER, $supplierCorrectiveActionRequest['supplierNumber']));
        } catch (\Exception $e) {
            $supplier = [];
        }

        $contacts = [];
        $supplier['contacts'] ??= [];
        foreach ($supplier['contacts'] as $contact) {
            $emails = explode(',', (string) $contact['emailAddress']);
            foreach ($emails as $email) {
                $contacts[\sprintf('%s, (%s)', $contact['fullName'], $email)] = $email;
            }
        }

        $supplierContacts = array_filter(
            $supplier['contacts'],
            static fn ($contact) => $contact['grantedQualityCategory'] ?? false
        );

        $formComment = $this->createForm(
            SupplierCorrectiveActionRequestCommentFileType::class,
            null,
            ['contacts' => $contacts]
        );

        $formComment->handleRequest($request);
        if ($formComment->isSubmitted() && $formComment->isValid()) {
            try {
                $formData = $formComment->getData();

                $data = [
                    'message' => $formData['message'],
                    'resource' => $supplierCorrectiveActionRequest->getIri(),
                    'discriminator' => 'scar_conversation',
                    'metadata' => json_encode(['tos' => $formData['contacts']], \JSON_THROW_ON_ERROR),
                ];

                if ($formData['file'] instanceof UploadedFile) {
                    $data[] = ['file' => DataPart::fromPath($formData['file']->getPathname())];
                }

                $formData = new FormDataPart($data);

                $this->client->post(self::RESOURCE_URL_COMMENT, [
                    'headers' => $formData->getPreparedHeaders()->toArray(),
                    'body' => $formData->bodyToIterable(),
                ]);

                $this->addFlash(
                    'success',
                    $this->translator->trans('supplier_corrective_action_request.comment.success', [], 'supplier_corrective_action_request')
                );

                return $this->redirectToRoute('supplier_corrective_action_request_show', ['id' => $supplierCorrectiveActionRequest['id'], 'tab' => 'conversation']);
            } catch (ClientException $e) {
                $this->addFlash(
                    'error',
                    $this->translator->trans('supplier_corrective_action_request.comment.fail', [], 'supplier_corrective_action_request')
                );
                $this->violationMapper->mapToForm($e, $formComment);
            }
        }

        $formFiles = $this->formFactory->createNamed('supplier_corrective_action_request_file', SimpleFileType::class);

        $formFiles->handleRequest($request);
        if ($formFiles->isSubmitted() && $formFiles->isValid()) {
            try {
                /** @var UploadedFile $file */
                $file = $formFiles->get('file')->getData();

                if ($file instanceof UploadedFile) {
                    $this->fileManager->uploadFile(
                        $supplierCorrectiveActionRequest,
                        $file,
                        self::RESOURCE_URL,
                        $formFiles->get('description')->getData(),
                        'files',
                        true,
                        true
                    );
                }

                $this->addFlash(
                    'success',
                    $this->translator->trans('customers.messages.success.file', [], 'sales_customers')
                );

                return $this->redirectToRoute('supplier_corrective_action_request_show', ['id' => $supplierCorrectiveActionRequest->getIriId(), 'tab' => 'files']);
            } catch (ClientException $e) {
                $this->violationMapper->mapToForm($e, $formFiles);
            }
        }

        $comments = $this->client->findBy(self::RESOURCE_URL_COMMENT, ['discriminator' => 'scar_conversation', 'resource' => $supplierCorrectiveActionRequest->getIri()], ['createdAt' => 'desc']);

        return [
            'formFiles' => $formFiles->createView(),
            'comments' => $comments,
            'supplierCorrectiveActionRequest' => $supplierCorrectiveActionRequest,
            'form' => $formComment->createView(),
            'supplierContacts' => $supplierContacts,
            'status_form' => $this->createForm(StatusChoiceType::class, null, ['choices' => $supplierCorrectiveActionRequest['availableStatus'], 'id' => $supplierCorrectiveActionRequest->getIriId(), 'route' => 'supplier_corrective_action_request_status'])->createView(),
        ];
    }

    #[Route(path: '/add', name: 'supplier_corrective_action_request_add', methods: ['GET', 'POST'])]
    #[Route(path: '/{id}/edit', name: 'supplier_corrective_action_request_edit', methods: ['GET', 'POST'])]
    #[Template('quality/supplier_corrective_action_request/write.html.twig')]
    public function write(Request $request, #[ApiValueResolverAttribute(parameters: ['resource' => self::RESOURCE_URL])] ?ApiData $supplierCorrectiveActionRequest = null)
    {
        $prefilledData = null;
        if (null === $supplierCorrectiveActionRequest) {
            if (null !== ($nonConformityId = $request->query->get('nonConformity'))) {
                $nonConformity = $this->client->find(NonConformityController::NON_CONFORMITY_URL, $nonConformityId);
                if (null !== $nonConformity) {
                    $prefilledData = [
                        'factory' => $nonConformity['factory'],
                        'description' => $nonConformity['problem'],
                        'shortDescription' => $nonConformity['shortDescription'],
                    ];
                }
            }
        }

        return [
            'supplierCorrectiveActionRequest' => $supplierCorrectiveActionRequest,
            'initialState' => [
                'supplierCorrectiveActionRequest' => $supplierCorrectiveActionRequest?->toArray(),
                'prefilledDataFromNonConformityRecord' => $prefilledData,
            ],
        ];
    }

    #[Route(path: '/{scarId}/files/{id}', name: 'supplier_corrective_action_request_download_file', methods: 'GET')]
    public function showFile($scarId, $id)
    {
        return $this->fileStreamedResponseFactory->create(\sprintf('%s/%s/files/%s', self::RESOURCE_URL, $scarId, $id));
    }

    #[Route(path: '/{scarId}/main-file/{id}', requirements: ['id' => '\d+'], name: 'supplier_corrective_action_request_download_main_file', methods: 'GET')]
    public function showMainFile($scarId, $id)
    {
        return $this->fileStreamedResponseFactory->create(\sprintf('%s/%s/main_file/%s', self::RESOURCE_URL, $scarId, $id));
    }

    #[Route(path: '/{scarId}/files/{id}/delete', name: 'supplier_corrective_action_request_delete_file', methods: ['GET'], requirements: ['scarId' => '\d+'])]
    #[IsGranted('FEATURE_SCAR_FILE_DELETE')]
    public function deleteFile(Request $request, #[ApiValueResolverAttribute(parameters: ['resource' => self::RESOURCE_URL, 'id' => 'scarId'])] ApiData $supplierCorrectiveActionRequest, $id): RedirectResponse
    {
        if (!$this->isCsrfTokenValid('supplier_corrective_action_request_delete_file', $request->query->get('_token'))) {
            $this->addFlash('error', $this->translator->trans('files.delete_error', [], 'messages'));

            return $this->redirectToRoute('supplier_corrective_action_request_show', ['id' => $supplierCorrectiveActionRequest->getIriId()]);
        }

        try {
            $this->fileManager->deleteFile($supplierCorrectiveActionRequest, self::RESOURCE_URL, \sprintf('files/%s', $id));
        } catch (ClientException $e) {
            $this->addFlash(
                'error',
                $this->translator->trans('first_article_qualification.messages.error.delete_file', [], 'first_article_qualification')
            );
        }

        return $this->redirectToRoute('supplier_corrective_action_request_show', ['id' => $supplierCorrectiveActionRequest->getIriId(), 'tab' => 'files']);
    }

    #[Route(path: '/{id}/delete', name: 'supplier_corrective_action_request_delete', methods: ['GET', 'DELETE'])]
    #[IsGranted(attribute: 'SUPPLIER_CORRECTIVE_ACTION_REQUEST_DELETE_VOTER', subject: new Expression('args["supplierCorrectiveActionRequest"].getIri()'))]
    public function delete(#[ApiValueResolverAttribute(parameters: ['resource' => self::RESOURCE_URL])] ApiData $supplierCorrectiveActionRequest): RedirectResponse
    {
        try {
            $this->client->remove(self::RESOURCE_URL, $supplierCorrectiveActionRequest['id']);
            $this->addFlash('success', $this->translator->trans('supplier_corrective_action_request.delete.success', [], 'supplier_corrective_action_request'));
        } catch (ClientException $e) {
            $this->addFlash('error', $this->translator->trans('supplier_corrective_action_request.delete.fail', [], 'supplier_corrective_action_request'));
        }

        return $this->redirectToRoute('supplier_corrective_action_request_home');
    }

    #[Route(path: '/{id}/status/{status}', name: 'supplier_corrective_action_request_status', methods: ['GET'])]
    public function status(#[ApiValueResolverAttribute(parameters: ['resource' => self::RESOURCE_URL])] ApiData $supplierCorrectiveActionRequest, $status): RedirectResponse
    {
        try {
            $this->client->put(\sprintf(self::RESOURCE_URL.'/%d/status', $supplierCorrectiveActionRequest->getIriId()), ['json' => ['status' => $status]]);
            $this->addFlash('success', $this->translator->trans('supplier_corrective_action_request.status.success', [], 'supplier_corrective_action_request'));
        } catch (ClientException $e) {
            $this->addFlash('error', nl2br((string) $this->clientExceptionMapper->mapToString($e)));
        }

        return $this->redirectToRoute('supplier_corrective_action_request_show', ['id' => $supplierCorrectiveActionRequest->getIriId()]);
    }

    #[Route(path: '/{id}/admin-parts', name: 'supplier_corrective_action_request_admin_parts', requirements: ['id' => '\d+'], methods: ['GET'], defaults: ['label' => 'non_conformity.title.edit_parts', 'domain' => 'non_conformity'])]
    #[Template('quality/supplier_corrective_action_request/parts.html.twig')]
    public function adminParts(#[ApiValueResolverAttribute(parameters: ['resource' => self::RESOURCE_URL])] ApiData $supplierCorrectiveActionRequest)
    {
        return [
            'supplierCorrectiveActionRequest' => $supplierCorrectiveActionRequest,
            'props' => [
                'object' => $supplierCorrectiveActionRequest->toArray(),
                'module' => 'SCAR',
                'redirectUrl' => $this->generateUrl('supplier_corrective_action_request_show', ['id' => Iri::id($supplierCorrectiveActionRequest)]),
            ],
        ];
    }

    #[Route(path: '/report', name: 'supplier_corrective_action_request_report', defaults: ['label' => 'sidebar.common.reports', 'domain' => 'sidebar'], methods: ['GET|POST'])]
    #[Template('quality/supplier_corrective_action_request/report.html.twig')]
    public function report(Request $request)
    {
        $formSupplierFactory = $this->formFactory->createNamed('', FactorySupplierReportFilter::class, [],
            [
                'action' => $this->generateUrl('supplier_corrective_action_request_report'),
                'method' => Request::METHOD_GET,
            ]
        );

        $formSupplierFactory->handleRequest($request);

        $parameters = [];
        $reportSupplierHistory = [];

        if ($formSupplierFactory->isSubmitted() && $formSupplierFactory->isValid()) {
            $parameters = $formSupplierFactory->getData();
            $options['factory'] = $parameters['factory'];
            if (null !== $parameters['supplierNumber']) {
                $options['supplierNumber'] = $parameters['supplierNumber'];
                $reportSupplierHistory = $this->client->get(
                    'reports/resource=/quality/supplier_corrective_action_requests;x=supplier_history;y=created_at',
                    ['query' => ['options' => $options]]
                );
            }
        }

        return [
            'formSupplierFactory' => $formSupplierFactory->createView(),
            'reportSupplierHistory' => $reportSupplierHistory,
            'parameters' => $parameters,
        ];
    }

    #[Route('/review', name: 'supplier_corrective_action_request_review', methods: ['GET', 'POST'])]
    #[Template('quality/supplier_corrective_action_request/review.html.twig')]
    public function review(Request $request)
    {
        $allScarDataTable = $this->createNamedDataTable(
            'all_scar_data_table',
            SupplierCorrectiveActionRequestDataTableType::class,
            SupplierCorrectiveActionRequestDataTableType::RESOURCE,
            [
                'title' => 'supplier_corrective_action_request.data_table.all_scar',
            ]
        );
        $allScarDataTable->handleRequest($request);
        if ($allScarDataTable->isExporting() && $allScarDataTable->getQuery() instanceof ApiProxyQuery) {
            return $allScarDataTable->getQuery()->export();
        }
        if ($allScarDataTable->isRequestFromTurboFrame()) {
            return $this->createDataTableTurboResponse($allScarDataTable);
        }

        $openedScarDataTable = $this->createNamedDataTable(
            'opened_scar_data_table',
            SupplierCorrectiveActionRequestDataTableType::class,
            SupplierCorrectiveActionRequestDataTableType::RESOURCE,
            [
                'title' => 'supplier_corrective_action_request.data_table.opened_scar',
            ]
        );
        $openedScarDataTable->handleRequest($request);
        if ($openedScarDataTable->isExporting() && $openedScarDataTable->getQuery() instanceof ApiProxyQuery) {
            return $openedScarDataTable->getQuery()->export();
        }
        if ($openedScarDataTable->isRequestFromTurboFrame()) {
            return $this->createDataTableTurboResponse($openedScarDataTable);
        }

        return [
            'supplierCorrectiveActionRequestsByFactoryStatus' => $this->client->get('reports/resource=/quality/supplier_corrective_action_requests;x=factory.name;y=status'),
            'allScarDataTable' => $allScarDataTable->createView(),
            'openedScarDataTable' => $openedScarDataTable->createView(),
        ];
    }
}
