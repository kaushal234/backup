<?php

declare(strict_types=1);

namespace AppBundle\Controller\Sales;

use ApiBundle\Client;
use ApiBundle\Form\ViolationMapper;
use ApiBundle\Http\CsvStreamedResponseFactory;
use ApiBundle\Http\FileStreamedResponseFactory;
use ApiBundle\Http\ZipStreamedResponseFactory;
use ApiBundle\Iri\Iri;
use ApiBundle\Model\ApiData;
use AppBundle\Configuration\ApiValueResolverAttribute;
use AppBundle\Controller\Sales\MarketIntelligence\MarketIntelligenceController;
use AppBundle\DataTable\Query\ApiProxyQuery;
use AppBundle\DataTable\Type\Sales\CustomerFileDataTableType;
use AppBundle\Filters\Type\Sales\CustomerFilterType;
use AppBundle\Form\Type\Directory\People\ASMChoiceType;
use AppBundle\Form\Type\IdSearchType;
use AppBundle\Form\Type\LegacyIdSearchType;
use AppBundle\Form\Type\Sales\Customer\CustomerCreditLimitType;
use AppBundle\Form\Type\Sales\Customer\CustomerExportType;
use AppBundle\Form\Type\Sales\Customer\CustomerFileEditType;
use AppBundle\Form\Type\Sales\Customer\CustomerFileType;
use AppBundle\Form\Type\Sales\Customer\CustomerTransferType;
use AppBundle\Form\Type\Sales\Customer\CustomerType;
use AppBundle\Form\Type\Sales\Customer\CustomerWatchListType;
use AppBundle\Form\Type\SimpleSearchType;
use AppBundle\Manager\FileManager;
use Kreyu\Bundle\DataTableBundle\DataTableFactoryAwareTrait;
use Psr\Log\LoggerInterface;
use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\ExpressionLanguage\Expression;
use Symfony\Component\HttpClient\Exception\ClientException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Mime\Part\DataPart;
use Symfony\Component\Mime\Part\Multipart\FormDataPart;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route(path: '/sales/customers', defaults: ['alvest_module' => 'ECUST', 'moduleDomain' => 'sales_customers'])]
class CustomerController extends AbstractController
{
    use DataTableFactoryAwareTrait;
    final public const RESOURCE_URL = 'sales/customers';
    final public const ERP_REF_RESOURCE_URL = 'sales/customer_erp_references';
    final public const TRANSLATION_DOMAIN = 'sales_customers';
    final public const ION_BUSINESS_PARTNER_ITEM_ROUTE_PREFIX = 'ion/business_partners/';
    public const string DELETE_TOKEN_FILE = 'delete_customer_file_id';

    public static function getSubscribedServices(): array
    {
        return array_merge(parent::getSubscribedServices(), [Client::class, ViolationMapper::class, TranslatorInterface::class, FileManager::class, CsvStreamedResponseFactory::class, FileStreamedResponseFactory::class, ZipStreamedResponseFactory::class, LoggerInterface::class]);
    }

    #[Route(path: '', name: 'sales_customers_home', defaults: ['page' => 1, 'order' => [], 'itemsPerPage' => 10])]
    #[Template('sales/customers/list.html.twig')]
    public function list(Request $request)
    {
        $client = $this->container->get(Client::class);
        $user = $client->get('/me');
        $reportTitle = 'customers.last10';
        $searchTable = false;
        $pagination = false;

        $parameters = [];
        $parameters['page'] = $request->query->getInt('page', 1);
        $parameters['order'] = [] !== $request->query->all('order') ? $request->query->all('order') : ['id' => 'desc'];
        $parameters['itemsPerPage'] = $request->query->get('itemsPerPage', 10);

        $formFilter = $this
            ->container
            ->get('form.factory')
            ->createNamed(
                '',
                CustomerFilterType::class, [],
                [
                    'action' => $this->generateUrl('sales_customers_home'),
                    'method' => 'GET',
                ]);

        $formFilter->handleRequest($request);
        if ($formFilter->isSubmitted() && $formFilter->isValid()) {
            $parameters = array_merge($parameters, $formFilter->getData());
            $parameters['itemsPerPage'] = 20000;
            $parameters['order'] = [] !== $request->query->all('order') ? $request->query->all('order') : ['name' => 'asc'];
            $parameters['has_crt'] = !(bool) $parameters['has_crt'];

            if ($parameters['has_crt']) {
                unset($parameters['has_crt']);
            }

            if ((bool) $parameters['main_representative_exists']) {
                $parameters['exists'] = ['mainSalesRepresentative' => false];
            }
            $reportTitle = 'customers.title';
            $searchTable = true;
            $pagination = 20;
            unset($parameters['main_representative_exists']);
        }

        try {
            $customers = $client->findBy(self::RESOURCE_URL, $parameters);
        } catch (ClientException $e) {
            $this->container->get(ViolationMapper::class)->mapToForm($e, $formFilter);
            $customers = [];
        }

        // Search action
        $simpleSearchForm = $this->createForm(SimpleSearchType::class, [], [
            'method' => 'GET',
            'csrf_protection' => false,
            'search_label' => false,
            'search_placeholder' => 'Search for...',
        ]);

        $idSearchForm = $this->createForm(IdSearchType::class, null, [
            'id_label' => false,
            'id_placeholder' => 'By ID',
        ]);

        $legacyIdSearchForm = $this->createForm(LegacyIdSearchType::class, null, [
            'legacy_id_label' => false,
            'legacy_id_placeholder' => 'By Legacy ID',
        ]);

        $idSearchForm->handleRequest($request);
        if ($idSearchForm->isSubmitted() && $idSearchForm->isValid()) {
            $id = $idSearchForm->get('id')->getData();
            try {
                $client->get(\sprintf('%s/%s', self::RESOURCE_URL, $id));

                return $this->redirectToRoute('sales_customers_show', ['id' => $id]);
            } catch (ClientException $e) {
                $this->addFlash('error', \sprintf('Customer #%s does not exist', $id));
            }
        }

        $legacyIdSearchForm->handleRequest($request);
        if ($legacyIdSearchForm->isSubmitted() && $legacyIdSearchForm->isValid()) {
            $legacyId = $legacyIdSearchForm->get('legacyId')->getData();
            try {
                $customer = $client->findOneBy(self::RESOURCE_URL, ['legacyId' => $legacyId]);

                return $this->redirectToRoute('sales_customers_show', ['id' => Iri::id($customer)]);
            } catch (\RangeException $e) {
                $this->addFlash('error', \sprintf('Customer #%s does not exist', $legacyId));
            }
        }

        $simpleSearchForm->handleRequest($request);
        if ($simpleSearchForm->isSubmitted() && $simpleSearchForm->isValid()) {
            $page = $request->query->getInt('page', 1);
            $reportTitle = 'customers.title';
            $searchTable = true;
            $pagination = 20;
            $parameters = [
                'q' => $simpleSearchForm->get('q')->getData(),
                'itemsPerPage' => 2000,
            ];

            if (1 !== $page) {
                $parameters['page'] = $page;
            }

            $customers = $client->findBy(self::RESOURCE_URL, $parameters, ['name']);
        }

        return [
            'user' => $user,
            'customers' => $customers,
            'formFilter' => $formFilter->createView(),
            'search_form' => $simpleSearchForm->createView(),
            'idSearchForm' => $idSearchForm->createView(),
            'legacyIdSearchForm' => $legacyIdSearchForm->createView(),
            'itemsPerPage' => $parameters['itemsPerPage'],
            'report_title' => $reportTitle,
            'search_table' => $searchTable,
            'pagination' => $pagination,
            'paginationUrl' => [
                'route' => $request->attributes->get('_route'),
                'parameters' => $request->query->all(),
            ],
        ];
    }

    #[Route(path: '/{id}/show', name: 'sales_customers_show', requirements: ['id' => '\d+'], methods: ['GET', 'POST'])]
    #[Template('sales/customers/show.html.twig')]
    public function show(#[ApiValueResolverAttribute(parameters: ['resource' => self::RESOURCE_URL, 'filters' => ['normalization_groups' => ['customer_crt', 'location_public']]])] ApiData $customer, Request $request)
    {
        $client = $this->container->get(Client::class);
        $watchListForm = $this
            ->container
            ->get('form.factory')
            ->createNamed(
                'customer_watch_list_form',
                CustomerWatchListType::class,
                ['watchList' => $customer['watchList']]
            )
        ;

        $watchListForm->handleRequest($request);

        $granted = $this->isGranted('FEATURE_CUSTOMER_WATCH_LIST');
        if ($granted && $watchListForm->isSubmitted() && $watchListForm->isValid()) {
            $data = $watchListForm->getData();
            $parameters = ['watchList' => !$data['watchList']];

            if (!$data['watchList']) {
                $parameters = [...$parameters, ...['watchListReason' => $data['watchListReason']]];
            }

            try {
                $client->put(\sprintf('%s/%d/watch_list', self::RESOURCE_URL, $customer->getIriId()), ['json' => $parameters]);
            } catch (ClientException $e) {
                $errorDescription = json_decode($e->getResponse()->getContent(false), true);
                $this->addFlash(
                    'error',
                    \sprintf('%s. %s', $this->container->get(TranslatorInterface::class)->trans('errors.something_went_wrong', [], 'messages'), $errorDescription['hydra:description'])
                );
            }

            return $this->redirectToRoute('sales_customers_show', ['id' => $customer->getIriId()]);
        }

        return [
            'watchListForm' => $granted ? $watchListForm->createView() : null,
            'customer' => $customer,
            'marketIntelligences' => $client->findBy(MarketIntelligenceController::RESOURCE_URL,
                [
                    'customers' => $customer['@id'],
                    'itemsPerPage' => 5,
                    'order' => ['id' => 'desc'],
                ]),
            'demos' => $client->findBy(DemoController::RESOURCE_URL,
                [
                    'customer' => $customer['@id'],
                    'itemsPerPage' => 5,
                    'order' => ['id' => 'desc'],
                ]),
            'forecastClosures' => $client->findBy(ForecastClosureController::RESOURCE_URL,
                [
                    'salesForecast.buyer' => $customer['@id'],
                    'itemsPerPage' => 5,
                    'order' => ['id' => 'desc'],
                ]),
        ];
    }

    #[Route(path: '/{id}/edit', name: 'sales_customers_edit', requirements: ['id' => '\d+'], methods: ['GET|POST'])]
    #[Template('sales/customers/edit.html.twig')]
    #[IsGranted('FEATURE_CUSTOMER_EDIT')]
    public function edit($id, Request $request)
    {
        $client = $this->container->get(Client::class);
        $customer = $client->find(self::RESOURCE_URL, $id, ['query' => ['normalization_groups' => ['customer_crt', 'location_public']]]);
        $customerForm = $this
            ->container
            ->get('form.factory')
            ->createNamed(
                'customer_form',
                CustomerType::class,
                $customer->toArray()
            )
        ;

        $contactPointsSubDivisions = [];
        $contactPoints = $client->findBy('people', ['acls.group.name' => ASMChoiceType::GROUPS, 'normalization_groups' => ['people:division']]);
        foreach ($contactPoints as $contactPoint) {
            $contactPointsSubDivisions[$contactPoint['@id']] = $contactPoint['businessUnit']['region']['subDivision']['@id'] ?? null;
        }

        $customerForm->handleRequest($request);
        if ($customerForm->isSubmitted() && $customerForm->isValid()) {
            try {
                $editedCustomer = $customerForm->getData();
                $chosenBpCode = $editedCustomer['inforLNBpCode'] ?? null;

                unset($editedCustomer['inforLNBpCode'], $editedCustomer['mainSalesRepresentative']['@id']);

                if ($chosenBpCode && !\in_array($chosenBpCode, $customer['inforLnBusinessPartnerCodes'], true)) {
                    $editedCustomer['inforLnBusinessPartnerCodes'] = array_merge($customer['inforLnBusinessPartnerCodes'], [$chosenBpCode]);
                }
                $logo = $customerForm->get('logo')->getData();

                $client->save(self::RESOURCE_URL, $editedCustomer);

                $fileId = $customer['logo']['id'] ?? null;
                $this->container->get(FileManager::class)->updateImage($editedCustomer, self::RESOURCE_URL, $logo, 'logo', $fileId);

                if ($editedCustomer['status'] !== $customer['status']) {
                    try {
                        $client->put(\sprintf('%s/%d/status', self::RESOURCE_URL, $customer->getIriId()), [
                            'json' => [
                                '@id' => $editedCustomer['@id'],
                                'status' => $editedCustomer['status'],
                            ],
                        ]);
                    } catch (ClientException $e) {
                        $this->addFlash('error', $e->getMessage());

                        return [
                            'contactPointsSubDivisions' => $contactPointsSubDivisions,
                            'form' => $customerForm->createView(),
                            'customer' => $customer,
                        ];
                    }
                }

                $this->addFlash(
                    'success',
                    $this->container->get(TranslatorInterface::class)->trans('customers.messages.success.edit', ['%name%' => $editedCustomer['name']], self::TRANSLATION_DOMAIN)
                );

                return $this->redirectToRoute('sales_customers_show', ['id' => Iri::id($editedCustomer)]);
            } catch (ClientException $e) {
                $this->container->get(ViolationMapper::class)->mapToForm($e, $customerForm);
            }
        }

        return [
            'form' => $customerForm->createView(),
            'customer' => $customer,
            'contactPointsSubDivisions' => $contactPointsSubDivisions,
        ];
    }

    #[Route(path: '/{id}/validate', name: 'sales_customers_validate', requirements: ['id' => '\d+'], methods: ['GET'])]
    #[IsGranted('FEATURE_CUSTOMER_EDIT')]
    public function validate(#[ApiValueResolverAttribute(parameters: ['resource' => self::RESOURCE_URL])] ApiData $customer): RedirectResponse
    {
        try {
            $this->container->get(Client::class)->put(\sprintf('%s/%d/status', self::RESOURCE_URL, $customer->getIriId()), [
                'json' => [
                    '@id' => $customer->getIri(),
                    'status' => 'PENDING',
                ],
            ]);
            $this->addFlash('success', $this->container->get(TranslatorInterface::class)->trans('customers.messages.success.validate', [], self::TRANSLATION_DOMAIN));
        } catch (ClientException $e) {
            $errorDescription = json_decode($e->getResponse()->getContent(false), true);
            $this->addFlash('error', $errorDescription['hydra:description']);
        }

        return $this->redirectToRoute('sales_customers_show', ['id' => $customer->getIriId()]);
    }

    #[Route(path: '/add', name: 'sales_customers_add', methods: ['GET|POST'])]
    #[Template('sales/customers/add.html.twig')]
    #[IsGranted('FEATURE_CUSTOMER_CREATE')]
    public function add(Request $request)
    {
        $customerForm = $this->container->get('form.factory')->createNamed('customer_form', CustomerType::class);
        $client = $this->container->get(Client::class);

        $contactPointsSubDivisions = [];
        $contactPoints = $client->findBy('people', ['acls.group.name' => ASMChoiceType::GROUPS, 'normalization_groups' => ['people:division']]);
        foreach ($contactPoints as $contactPoint) {
            $contactPointsSubDivisions[$contactPoint['@id']] = $contactPoint['businessUnit']['region']['subDivision']['@id'] ?? null;
        }

        $customerForm->handleRequest($request);
        if ($customerForm->isSubmitted() && $customerForm->isValid()) {
            try {
                $data = $customerForm->getData();
                $logo = $customerForm->get('logo')->getData();

                $customer = $client->save(self::RESOURCE_URL, $data);

                if (null !== $logo['file']) {
                    $this->container->get(FileManager::class)->uploadFile($customer, $logo['file'], self::RESOURCE_URL, null, 'logo');
                }

                $this->addFlash(
                    'success',
                    $this->container->get(TranslatorInterface::class)->trans('customers.messages.success.add', ['%name%' => $customer['name']], self::TRANSLATION_DOMAIN)
                );

                return $this->redirectToRoute('sales_customers_home');
            } catch (ClientException $e) {
                $errorDescription = json_decode($e->getResponse()->getContent(false), true);
                $this->addFlash('error', $errorDescription['hydra:description']);
                $this->container->get(ViolationMapper::class)->mapToForm($e, $customerForm);

                return [
                    'form' => $customerForm->createView(),
                    'contactPointsSubDivisions' => $contactPointsSubDivisions,
                ];
            }
        }

        return [
            'form' => $customerForm->createView(),
            'contactPointsSubDivisions' => $contactPointsSubDivisions,
        ];
    }

    #[Route(path: '/{id}/delete', name: 'sales_customers_delete', requirements: ['id' => '\d+'], methods: ['GET|DELETE'])]
    #[IsGranted('FEATURE_CUSTOMER_EDIT')]
    public function delete($id): RedirectResponse
    {
        try {
            $this->container->get(Client::class)->remove(self::RESOURCE_URL, $id);

            $this->addFlash(
                'success',
                $this->container->get(TranslatorInterface::class)->trans('customers.messages.success.delete', [], self::TRANSLATION_DOMAIN)
            );
        } catch (ClientException $e) {
            $errorDescription = json_decode($e->getResponse()->getContent(false), true);
            $this->addFlash(
                'error',
                \sprintf('%s. %s', $this->container->get(TranslatorInterface::class)->trans('customers.messages.error.delete', [], self::TRANSLATION_DOMAIN), $errorDescription['hydra:description'])
            );
        }

        return $this->redirectToRoute('sales_customers_home');
    }

    #[Route(path: '/{id}/transfer', name: 'sales_customers_transfer', requirements: ['id' => '\d+'], methods: ['GET|POST'])]
    #[Template('sales/customers/transfer.html.twig')]
    #[IsGranted('FEATURE_CUSTOMER_EDIT')]
    public function transfer(Request $request, $id)
    {
        $client = $this->container->get(Client::class);
        $customer = $client->find(self::RESOURCE_URL, $id);
        $customerForm = $this->container->get('form.factory')->createNamed('customer_form', CustomerTransferType::class);

        $customerForm->handleRequest($request);
        if ($customerForm->isSubmitted() && $customerForm->isValid()) {
            try {
                $targetCustomer = $customerForm->getData();

                $client->request(self::RESOURCE_URL, $customer->getIriId(), 'transfer', 'PUT',
                    [
                        'json' => [
                            'target' => $targetCustomer['id'],
                        ],
                    ]);

                $this->addFlash(
                    'success',
                    $this->container->get(TranslatorInterface::class)->trans('customers.messages.success.transfer', [], self::TRANSLATION_DOMAIN)
                );

                return $this->redirectToRoute('sales_customers_home');
            } catch (ClientException $e) {
                $this->container->get(ViolationMapper::class)->mapToForm($e, $customerForm);
            }
        }

        return [
            'customer' => $customer,
            'form' => $customerForm->createView(),
        ];
    }

    #[Route(path: '/{id}/hierarchy', name: 'sales_customers_hierarchy', requirements: ['id' => '\d+'], methods: ['GET'])]
    #[Template('sales/customers/hierarchy.html.twig')]
    public function hierarchy($id)
    {
        $client = $this->container->get(Client::class);
        $customer = $client->find(self::RESOURCE_URL, $id, ['query' => ['normalization_groups' => ['customer_crt', 'location_public']]]);
        $customerTopParent = $client->get(\sprintf('%s/%s/hierarchy', self::RESOURCE_URL, $id));

        return ['customerTopParent' => $customerTopParent, 'id' => $id, 'customer' => $customer];
    }

    #[Route(path: '/{id}/files', name: 'sales_customers_files', requirements: ['id' => '\d+'], methods: ['GET|POST'])]
    #[Template('sales/customers/files.html.twig')]
    public function files(#[ApiValueResolverAttribute(parameters: ['resource' => self::RESOURCE_URL, 'filters' => ['normalization_groups' => ['customer_crt', 'location_public', 'subdivision', 'division']]])] ApiData $customer, Request $request)
    {
        $form = $this->container->get('form.factory')->createNamed('customer', CustomerFileType::class, null, ['description_required' => true]);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            try {
                /** @var UploadedFile $file */
                $file = $form->get('file')->getData();
                $isContract = (bool) $form->get('isContract')->getData();
                $uploadedFile = null;

                if ($file instanceof UploadedFile) {
                    $objectId = Iri::id($customer['@id']);
                    $filename = $file->getClientOriginalName();

                    $multiPart = ['file' => DataPart::fromPath($file->getPathname(), $filename)];

                    $fieldsToMap = ['description', 'subDivision'];

                    foreach ($fieldsToMap as $fieldName) {
                        $value = $form->get($fieldName)->getData();
                        if (null !== $value) {
                            $multiPart[$fieldName] = (string) $value;
                        }
                    }

                    $multiPart['public'] = '1';
                    $multiPart['isContract'] = $isContract ? '1' : '0';

                    $formData = new FormDataPart($multiPart);

                    $response = $this->container->get(Client::class)->request(
                        self::RESOURCE_URL,
                        $objectId,
                        'files',
                        'POST',
                        [
                            'headers' => $formData->getPreparedHeaders()->toArray(),
                            'body' => $formData->bodyToIterable(),
                        ]
                    );

                    $uploadedFile = json_decode($response->getContent(), true);
                }

                // If the file is flagged as a contract, read it with AI (same extraction used by the
                // Legal/Contract "ai-analyze" flow) and redirect to the contract creation page, keeping
                // track of the source CustomerFile so it can be linked back to the contract once created.
                if ($isContract && null !== $uploadedFile && $file instanceof UploadedFile) {
                    try {
                        $extractFormData = new FormDataPart(['file' => DataPart::fromPath($file->getPathname(), $filename)]);

                        $extractResponse = $this->container->get(Client::class)->request(
                            'contracts/extract',
                            null,
                            null,
                            Request::METHOD_POST,
                            [
                                'headers' => $extractFormData->getPreparedHeaders()->toArray(),
                                'body' => $extractFormData->bodyToIterable(),
                            ]
                        );

                        $aiData = json_decode($extractResponse->getContent(), true);
                        $base64File = base64_encode(file_get_contents($file->getPathname()));

                        $session = $request->getSession();
                        $session->set('contract_ai_data', $aiData);
                        $session->set('contract_ai_filename', $filename);
                        $session->set('contract_ai_file', [
                            'filename' => $filename,
                            'mimeType' => $file->getMimeType(),
                            'size' => $file->getSize(),
                            'base64' => $base64File,
                        ]);
                        $session->set('contract_ai_source_customer_file_id', Client::extractId($uploadedFile));

                        $this->addFlash(
                            'success',
                            $this->container->get(TranslatorInterface::class)->trans('customers.messages.success.file', [], self::TRANSLATION_DOMAIN)
                        );

                        return $this->redirectToRoute('contract_add');
                    } catch (\Throwable $e) {
                        $this->container->get(LoggerInterface::class)->error('Contract AI analyze failed after ECUST file upload: {message}', [
                            'message' => $e->getMessage(),
                            'exception' => $e,
                        ]);

                        $this->addFlash(
                            'error',
                            $this->container->get(TranslatorInterface::class)->trans('customers.messages.error.contract_ai_analyze', [], self::TRANSLATION_DOMAIN)
                        );
                    }
                }

                $this->addFlash(
                    'success',
                    $this->container->get(TranslatorInterface::class)->trans('customers.messages.success.file', [], self::TRANSLATION_DOMAIN)
                );

                return $this->redirectToRoute('sales_customers_files', ['id' => $customer->getIriId()]);
            } catch (ClientException $e) {
                $this->container->get(ViolationMapper::class)->mapToForm($e, $form);
            }
        }

        $datatable = $this->createDataTable(
            CustomerFileDataTableType::class,
            \sprintf(CustomerFileDataTableType::RESOURCE, $customer['id']),
            [
                'canEditCustomerFiles' => $this->isGranted('CUSTOMER_FILES_UPLOAD_VOTER', $customer->getIri()),
            ]
        );
        $datatable->handleRequest($request);

        if ($datatable->isExporting() && $datatable->getQuery() instanceof ApiProxyQuery) {
            return $datatable->getQuery()->export();
        }

        return [
            'customer' => $customer,
            'filesDataTable' => $datatable->createView(),
            'form' => $form->createView(),
        ];
    }

    #[Route(path: '/{customerId}/files/{id}/edit', name: 'sales_customers_edit_file', requirements: ['customerId' => '\\d+', 'id' => '\\d+'], methods: ['GET', 'POST'])]
    #[Template('sales/customers/file_edit.html.twig')]
    #[IsGranted(attribute: 'CUSTOMER_FILES_UPLOAD_VOTER', subject: new Expression('args["customer"].getIri()'))]
    public function editFile(
        Request $request,
        #[ApiValueResolverAttribute(parameters: ['resource' => self::RESOURCE_URL, 'id' => 'customerId', 'filters' => ['normalization_groups' => ['customer_crt', 'location_public', 'subdivision', 'division']]])]
        ApiData $customer,
        int $id,
    ) {
        $client = $this->container->get(Client::class);
        $file = $client->find('customer_files', $id, ['query' => ['normalization_groups' => ['file', 'customer_file', 'subdivision']]]);

        if (($file['customer'] ?? null) !== $customer['@id']) {
            throw $this->createNotFoundException();
        }

        $form = $this->container->get('form.factory')->createNamed('editFile', CustomerFileEditType::class, $file->toArray());

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();

            try {
                $client->save('files', [
                    '@id' => \sprintf('/files/%d', $id),
                    'description' => $data['description'],
                    'subDivision' => $data['subDivision'],
                ]);

                $this->addFlash(
                    'success',
                    $this->container->get(TranslatorInterface::class)->trans('customers.messages.success.file_description', [], self::TRANSLATION_DOMAIN)
                );

                return $this->redirectToRoute('sales_customers_files', ['id' => $customer->getIriId()]);
            } catch (ClientException $e) {
                $this->container->get(ViolationMapper::class)->mapToForm($e, $form);
            }
        }

        return [
            'customer' => $customer,
            'file' => $file,
            'form' => $form->createView(),
        ];
    }

    #[Route(path: '/{customerId}/files/{id}/delete', name: 'sales_customers_delete_file', requirements: ['customerId' => '\d+', 'id' => '\d+'], methods: ['GET'])]
    #[IsGranted(attribute: 'CUSTOMER_FILES_DELETE_VOTER', subject: new Expression('args["customer"].getIri()'))]
    public function deleteFile(
        Request $request,
        #[ApiValueResolverAttribute(parameters: ['resource' => self::RESOURCE_URL, 'id' => 'customerId'])]
        ApiData $customer,
        int $id,
    ): RedirectResponse {
        if (!$this->isCsrfTokenValid(self::DELETE_TOKEN_FILE, $request->query->get('_token'))) {
            $this->addFlash('error', $this->container->get(TranslatorInterface::class)->trans('delete.errors', [], 'file_type'));

            return $this->redirectToRoute('sales_customers_files', ['id' => $customer['id']]);
        }

        $this->container->get(FileManager::class)->deleteFile(
            $customer,
            self::RESOURCE_URL,
            \sprintf('files/%s', $id)
        );

        $this->addFlash('success', $this->container->get(TranslatorInterface::class)->trans('delete.success', [], 'file_type'));

        return $this->redirectToRoute('sales_customers_files', ['id' => $customer['id']]);
    }

    #[Route(path: '/{customerId}/files/{id}', name: 'sales_customers_files_show', requirements: ['id' => '\d+', 'customerId' => '\d+'], methods: 'GET')]
    #[IsGranted(attribute: 'CUSTOMER_FILES_READ_VOTER', subject: new Expression('args["customer"].getIri()'))]
    public function showFile(#[ApiValueResolverAttribute(parameters: ['resource' => self::RESOURCE_URL, 'id' => 'customerId'])] ApiData $customer, $id)
    {
        return $this->container->get(FileStreamedResponseFactory::class)->create(\sprintf('%s/%s/files/%s', self::RESOURCE_URL, $customer->getIriId(), $id), [], null, ResponseHeaderBag::DISPOSITION_INLINE, true);
    }

    #[Route(path: '/{id}/{link}', name: 'sales_customers_crt_linked', requirements: ['link' => 'sales_orders|packing_slips|invoices|email|zip'], methods: ['GET'])]
    #[Template('sales/customers/crt_linked.html.twig')]
    public function crtLinked($id, $link)
    {
        switch ($link) {
            case 'sales_orders':
                $target = 'so';
                $translation = 'customers.menu.sales_orders';
                break;
            case 'packing_slips':
                $target = 'ps';
                $translation = 'customers.menu.packing_slips';
                break;
            case 'invoices':
                $target = 'inv';
                $translation = 'customers.menu.invoices';
                break;
            case 'email':
                $target = $link;
                $translation = 'customers.email.target';
                break;
            case 'zip':
                $target = $link;
                $translation = 'customers.zip.target';
                break;
            default:
                $target = '';
                $translation = '';
        }

        return [
            'translation' => $translation,
            'target' => $target,
            'customer' => $this->container->get(Client::class)->find(self::RESOURCE_URL, $id, ['query' => ['normalization_groups' => ['customer_crt', 'location_public']]]),
        ];
    }

    #[Route(path: '/{id}/xu_linked', name: 'sales_xu_for_customer', requirements: ['id' => '\d+'], methods: ['GET'])]
    #[Template('sales/customers/extranet_users_linked.html.twig')]
    public function showContactForCustomer($id)
    {
        $extranetUsers = $this->container->get(Client::class)->findBy(ExtranetUserController::RESOURCE_URL, ['extranetUserAcls.crt.customer' => $id, 'normalization_groups' => ['extranet_user_acls']]);

        return [
            'extranetUsers' => $extranetUsers,
        ];
    }

    #[Route(path: '/export', name: 'sales_customer_export', methods: ['GET|POST'])]
    #[Template('sales/customers/export.html.twig')]
    public function exportCustomers(Request $request)
    {
        $form = $this->container->get('form.factory')->createNamed('customerType', CustomerExportType::class);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $parameters = [
                'customerTypes' => [$data['customerType']],
                'itemsPerPage' => 1000,
            ];

            return $this->container->get(CsvStreamedResponseFactory::class)->create('sales/customers_export', $parameters);
        }

        return [
            'form' => $form->createView(),
        ];
    }

    #[Route(path: '/{customerId}/files/download-all', name: 'sales_customers_files_show_all', requirements: ['customerId' => '\d+'], methods: 'GET')]
    #[IsGranted(attribute: 'CUSTOMER_FILES_READ_VOTER', subject: new Expression('args["customer"].getIri()'))]
    public function showAllFile(#[ApiValueResolverAttribute(parameters: ['resource' => self::RESOURCE_URL, 'id' => 'customerId'])] ApiData $customer)
    {
        return $this->container->get(ZipStreamedResponseFactory::class)->create(\sprintf('%s/%d/files', self::RESOURCE_URL, $customer['id']), $customer['name']);
    }

    #[Route(path: '/{id}/deactivate', name: 'sales_customers_deactivate', requirements: ['id' => '\d+'], methods: 'GET')]
    #[IsGranted('FEATURE_CUSTOMER_EDIT')]
    public function deactivate(#[ApiValueResolverAttribute(parameters: ['resource' => self::RESOURCE_URL])] ApiData $customer): RedirectResponse
    {
        try {
            $this->container->get(Client::class)->put(\sprintf('%s/%d/status', self::RESOURCE_URL, $customer->getIriId()), [
                'json' => [
                    '@id' => $customer->getIri(),
                    'status' => 'NOT ACTIVE',
                ],
            ]);
            $this->addFlash(
                'success',
                $this->container->get(TranslatorInterface::class)->trans('customers.messages.success.deactivate', [], self::TRANSLATION_DOMAIN)
            );
        } catch (ClientException $e) {
            $this->addFlash(
                'error',
                $this->container->get(TranslatorInterface::class)->trans('customers.messages.error.deactivate', [], self::TRANSLATION_DOMAIN)
            );
        }

        return $this->redirectToRoute('sales_customers_show', ['id' => $customer->getIriId()]);
    }

    #[Route(path: '/{id}/credit-limits', name: 'sales_customers_admin_finance', requirements: ['id' => '\d+'], methods: ['GET', 'POST'])]
    #[Template('sales/customers/credit_limits.html.twig')]
    #[IsGranted('FEATURE_CUSTOMER_FINANCE_ADMIN')]
    public function creditLimitsAdmin(#[ApiValueResolverAttribute(parameters: ['resource' => self::RESOURCE_URL])] ApiData $customer, Request $request)
    {
        $client = $this->container->get(Client::class);
        $form = $this->container->get('form.factory')->createNamed('', CustomerCreditLimitType::class, $customer);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();

            try {
                $client->put(\sprintf('%s/%d', self::RESOURCE_URL, $customer->getIriId()),
                    [
                        'json' => [
                            '@id' => $customer['@id'],
                            'creditLimits' => $data['creditLimits'],
                        ],
                    ]);
            } catch (ClientException $e) {
                $errorDescription = json_decode($e->getResponse()->getContent(false), true);
                $this->addFlash(
                    'error',
                    \sprintf('%s. %s', $this->container->get(TranslatorInterface::class)->trans('errors.something_went_wrong', [], 'messages'), $errorDescription['hydra:description'])
                );
            }

            return $this->redirectToRoute('sales_customers_show', ['id' => $customer->getIriId()]);
        }

        return [
            'form' => $form->createView(),
            'customer' => $customer,
        ];
    }
}
