<?php

declare(strict_types=1);

namespace AppBundle\Controller\Service;

use ApiBundle\Client;
use ApiBundle\Form\ViolationMapper;
use ApiBundle\Http\FileStreamedResponseFactory;
use ApiBundle\Model\ApiData;
use AppBundle\Configuration\ApiValueResolverAttribute;
use AppBundle\Controller\Service\CustomerServiceRecord\CustomerServiceRecordController;
use AppBundle\DataPersister\Service\TechnicianOnCallPersister;
use AppBundle\DataTable\Query\ApiProxyQuery;
use AppBundle\DataTable\Type\Service\TechnicianOnCallDataTableType;
use AppBundle\Form\Type\Service\TechnicianOnCallEmailType;
use AppBundle\Form\Type\Service\TechnicianOnCallNestedCustomerServiceRecordType;
use AppBundle\Form\Type\Service\TechnicianOnCallStatusType;
use AppBundle\Manager\FileManager;
use AppBundle\Manager\Service\TechnicianOnCallAuditManager;
use Kreyu\Bundle\DataTableBundle\DataTableFactoryAwareTrait;
use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpClient\Exception\ClientException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route(path: '/service/technician-on-calls', defaults: ['alvest_module' => 'TOC', 'moduleDomain' => 'technician_on_calls'])]
class TechnicianOnCallController extends AbstractController
{
    use DataTableFactoryAwareTrait;

    public static function getSubscribedServices(): array
    {
        return array_merge(parent::getSubscribedServices(), [
            Client::class,
            TranslatorInterface::class,
            TechnicianOnCallPersister::class,
            FileStreamedResponseFactory::class,
            ViolationMapper::class,
            TechnicianOnCallAuditManager::class,
            FileManager::class,
        ]);
    }

    #[Route(path: '', name: 'technician_on_calls_home', methods: ['GET', 'POST'])]
    #[Template('service/technician_on_call/home.html.twig')]
    public function home(Request $request)
    {
        $user = $this->container->get(Client::class)->get('/me');
        $positionCode = $user['position']['code'];
        $dashboardCSMCodes = ['CSM', 'CSTL', 'CSS'];

        if (\in_array($positionCode, $dashboardCSMCodes, true)) {
            return $this->redirectToRoute('csm_dashboard');
        }

        if ('AST' === $positionCode) {
            return $this->redirectToRoute('ast_dashboard');
        }

        return $this->redirectToRoute('technician_on_call_search');
    }

    #[Route(path: '/search', name: 'technician_on_call_search', methods: ['GET|POST'])]
    #[Template('service/technician_on_call/home.html.twig')]
    public function search(Request $request)
    {
        $filters = $request->query->all('filter_technician_on_call');
        if (isset($filters['salesOrganisationService']['value'][0]) && 'ALL' === $filters['salesOrganisationService']['value'][0]) {
            unset($filters['salesOrganisationService']);
        }
        if (isset($filters['factory']['value'][0]) && 'ALL' === $filters['factory']['value'][0]) {
            unset($filters['factory']);
        }
        if (isset($filters['indiceFactor']['value'][0]) && 'ALL' === $filters['indiceFactor']['value'][0]) {
            unset($filters['indiceFactor']);
        }
        $request->query->set('filter_technician_on_call', $filters);

        $datatable = $this->createDataTable(TechnicianOnCallDataTableType::class, TechnicianOnCallDataTableType::RESOURCE);
        $datatable->handleRequest($request);
        if ($datatable->isExporting() && $datatable->getQuery() instanceof ApiProxyQuery) {
            return $datatable->getQuery()->export();
        }

        return [
            'technicianOnCallDatatable' => $datatable->createView(),
        ];
    }

    #[Route(path: '/{id}/show', requirements: ['id' => '\d+'], name: 'technician_on_calls_show', methods: ['GET', 'POST'])]
    #[Template('service/technician_on_call/show.html.twig')]
    public function show(Request $request, #[ApiValueResolverAttribute(parameters: [
        'resource' => TechnicianOnCallPersister::RESOURCE_URL,
        'filters' => ['normalizationGroups' => ['workflow']],
    ])] ApiData $technicianOnCall)
    {
        $auditFactoryFlag = $this->container->get(TechnicianOnCallAuditManager::class)->factoryFlagAudit($technicianOnCall);
        $auditUnitOperationalStatus = $this->container->get(TechnicianOnCallAuditManager::class)->unitOperationalStatusAudit($technicianOnCall);
        $grantedFactoryFlag = $technicianOnCall['factoryFlag'] ? $this->isGranted('FEATURE_TECHNICIAN_ON_CALL_CLOSE_FACTORY_FLAG') : $this->isGranted('FEATURE_TECHNICIAN_ON_CALL_OPEN_FACTORY_FLAG');
        $cachedStatus = $technicianOnCall['status'];

        if ('CLOSED' === $technicianOnCall['status'] && !$technicianOnCall['openCustomerServiceRecords']) {
            return [
                'technicianOnCall' => $technicianOnCall,
                'auditFactoryFlag' => $auditFactoryFlag,
                'auditUnitOperationalStatus' => $auditUnitOperationalStatus,
                'grantedFactoryFlag' => $grantedFactoryFlag,
            ];
        }
        $statusForm = null;
        if (!empty($technicianOnCall['availableStatus'])) {
            $statusForm = $this->createForm(TechnicianOnCallStatusType::class, $technicianOnCall);
            $statusForm->handleRequest($request);
            $persister = $this->container->get(TechnicianOnCallPersister::class);
            if ($statusForm->isSubmitted() && $statusForm->isValid() && $persister->changeStatus($statusForm)) {
                $this->addFlash('success', $this->container->get(TranslatorInterface::class)->trans(
                    'toc.messages.success.status',
                    ['%status%' => $technicianOnCall['status']],
                    'technician_on_call'));

                return $this->redirectToRoute('technician_on_calls_show', ['id' => $technicianOnCall->getIriId()]);
            }
        }

        $customerServiceRecordForm = null;
        if ($this->isGranted('FEATURE_CUSTOMER_SERVICE_RECORD_CREATE')
            && $technicianOnCall['isOpen']
            && null === $technicianOnCall['currentCustomerServiceRecord']
            && $technicianOnCall['equipmentRecord']
        ) {
            $customerServiceRecordForm = $this->createForm(TechnicianOnCallNestedCustomerServiceRecordType::class, $technicianOnCall);
            $customerServiceRecordForm->handleRequest($request);

            if ($customerServiceRecordForm->isSubmitted() && $customerServiceRecordForm->isValid()) {
                $persister = $this->container->get(TechnicianOnCallPersister::class);
                $technicianOnCallPut = $persister->requestTechnician($customerServiceRecordForm);

                if ($technicianOnCallPut) {
                    if ('hydra:Error' === ($technicianOnCallPut['@sub_resources']['customerServiceRecord']['@type'] ?? null)) {
                        $customerServiceResponse = $technicianOnCallPut['@sub_resources']['customerServiceRecord'];
                        if (Response::HTTP_FORBIDDEN === ($customerServiceResponse['status'] ?? null)) {
                            $this->addFlash('error', 'You are not allowed to create a CSR');
                        } else {
                            $this->addFlash('error', 'Something went wrong with Customer Service Record creation');
                        }
                    }

                    return $this->redirectToRoute('technician_on_calls_show', ['id' => $technicianOnCall['id']]);
                }
            }
        }

        $technicianOnCall['status'] = $cachedStatus;

        return [
            'technicianOnCall' => $technicianOnCall,
            'auditFactoryFlag' => $auditFactoryFlag,
            'auditUnitOperationalStatus' => $auditUnitOperationalStatus,
            'statusForm' => $statusForm?->createView(),
            'customerServiceRecordForm' => $customerServiceRecordForm?->createView(),
            'grantedFactoryFlag' => $grantedFactoryFlag,
        ];
    }

    #[Route(path: '/{id}/files', requirements: ['id' => '\d+'], name: 'technician_on_call_files', methods: ['GET'])]
    #[Template('service/technician_on_call/files.html.twig')]
    public function files(#[ApiValueResolverAttribute(parameters: [
        'resource' => TechnicianOnCallPersister::RESOURCE_URL,
    ])] ApiData $technicianOnCall)
    {
        return ['technicianOnCall' => $technicianOnCall];
    }

    #[Route(path: '/{technicianOnCallId}/main-file-download/{id}', requirements: ['id' => '\d+'], name: 'technician_on_call_main_file_download', methods: 'GET')]
    public function downloadMainFile(
        #[ApiValueResolverAttribute(parameters: [
            'id' => 'technicianOnCallId',
            'resource' => TechnicianOnCallPersister::RESOURCE_URL,
        ])] ApiData $technicianOnCall, $id,
    ) {
        return $this->container->get(FileStreamedResponseFactory::class)->create(
            \sprintf('%s/%s/main_file/%s', TechnicianOnCallPersister::RESOURCE_URL, $technicianOnCall['id'], $id),
            [],
            null,
            ResponseHeaderBag::DISPOSITION_ATTACHMENT
        );
    }

    #[Route(path: '/{technicianOnCallId}/files-download/{id}', requirements: ['id' => '\d+'], name: 'technician_on_call_file_download', methods: 'GET')]
    public function downloadFile(
        #[ApiValueResolverAttribute(parameters: [
            'id' => 'technicianOnCallId',
            'resource' => TechnicianOnCallPersister::RESOURCE_URL,
        ])] ApiData $technicianOnCall, $id,
    ) {
        return $this->container->get(FileStreamedResponseFactory::class)->create(
            \sprintf('%s/%s/files/%s', TechnicianOnCallPersister::RESOURCE_URL, $technicianOnCall['id'], $id),
            [],
            null,
            ResponseHeaderBag::DISPOSITION_ATTACHMENT
        );
    }

    #[Route(path: '/{technicianOnCallId}/files-delete/{id}', requirements: ['id' => '\d+'], name: 'technician_on_call_file_delete', methods: 'GET')]
    public function deleteFile(
        Request $request,
        #[ApiValueResolverAttribute(parameters: [
            'id' => 'technicianOnCallId',
            'resource' => TechnicianOnCallPersister::RESOURCE_URL,
        ])] ApiData $technicianOnCall, $id,
    ) {
        $this->container->get(FileManager::class)->deleteFile(
            $technicianOnCall,
            TechnicianOnCallPersister::RESOURCE_URL,
            \sprintf('files/%s', $id));
        $this->addFlash('success', $this->container->get(TranslatorInterface::class)->trans('delete.success', [], 'file_type'));

        return $this->redirect($request->headers->get('referer'));
    }

    #[Route(path: '/{id}/audit-log', requirements: ['id' => '\d+'], name: 'technician_on_call_audit_log', methods: 'GET')]
    #[Template('service/technician_on_call/audit.html.twig')]
    public function auditLog(
        #[ApiValueResolverAttribute(parameters: [
            'resource' => TechnicianOnCallPersister::RESOURCE_URL,
        ])] ApiData $technicianOnCall,
    ) {
        /** @var Client $client */
        $client = $this->container->get(Client::class);

        $audit['status'] = $client->findBy('audit_logs/by_reference', ['referenceId' => $technicianOnCall->getIriId(), 'auditType' => 'technician_on_call', 'property' => 'status']);
        $auditFactoryFlag = $client->findBy('audit_logs/by_reference', ['referenceId' => $technicianOnCall->getIriId(), 'auditType' => 'technician_on_call', 'property' => 'factoryFlag']);

        foreach ($auditFactoryFlag as &$auditFactory) {
            if ('' === $auditFactory['value']) {
                $auditFactory['value'] = false;
            }
            if ('1' === $auditFactory['value']) {
                $auditFactory['value'] = true;
            }
        }

        $audit['factoryFlag'] = $auditFactoryFlag;
        $audit['originalSymptoms'] = $client->findBy('audit_logs/by_reference', ['referenceId' => $technicianOnCall->getIriId(), 'auditType' => 'technician_on_call', 'property' => 'originalSymptoms']);
        $audit['originalRootCause'] = $client->findBy('audit_logs/by_reference', ['referenceId' => $technicianOnCall->getIriId(), 'auditType' => 'technician_on_call', 'property' => 'originalRootCause']);
        $audit['originalSolution'] = $client->findBy('audit_logs/by_reference', ['referenceId' => $technicianOnCall->getIriId(), 'auditType' => 'technician_on_call', 'property' => 'originalSolution']);

        return [
            'technicianOnCall' => $technicianOnCall,
            'audits' => $audit,
        ];
    }

    #[Route(path: '/add', name: 'technician_on_calls_write', methods: ['GET', 'POST'])]
    #[Route(path: '/{id}/edit', name: 'technician_on_calls_edit', methods: ['GET|POST'])]
    #[Route(path: '/{id}/duplicate', name: 'technician_on_calls_duplicate', methods: ['GET|POST'])]
    #[Template('service/technician_on_call/write.html.twig')]
    public function write(Request $request, #[ApiValueResolverAttribute(parameters: [
        'resource' => TechnicianOnCallPersister::RESOURCE_URL,
        'filters' => ['normalizationGroups' => ['workflow']],
    ])] ?ApiData $technicianOnCall)
    {
        if ('technician_on_calls_duplicate' === $request->attributes->get('_route')) {
            $this->denyAccessUnlessGranted('MOO_TOC');
        }

        $client = $this->container->get(Client::class);

        $selectableCsrLeaders = $client->get('/people', [
            'query' => ['department' => CustomerServiceRecordController::LEADER_DEPARTMENTS],
        ])['hydra:member'];

        return [
            'initialState' => [
                'user' => [
                    'details' => $client->get('me'),
                    'canCreateCsr' => $this->isGranted('FEATURE_CUSTOMER_SERVICE_RECORD_CREATE'),
                    'canCreateExtranetUser' => $this->isGranted('FEATURE_EXTRANET_USER_CREATE'),
                    'users' => $selectableCsrLeaders,
                ],
                'technicianOnCall' => [
                    'technicianOnCalls' => $technicianOnCall ? [$technicianOnCall->toArray()] : [],
                ],
                'location' => [
                    'locations' => $client->findBy(
                        'locations', ['capability.sso' => 1], ['name' => 'asc'], ['raw_results' => true])['hydra:member'] ??
                        [],
                ],
            ],
            'props' => [
                'module' => 'TOC',
                'formType' => null !== $technicianOnCall ? 'edit' : 'add',
                'technicianOnCall' => $technicianOnCall ? $technicianOnCall->toArray() : [],
            ],
            'technicianOnCall' => $technicianOnCall,
        ];
    }

    #[Route(path: '/{id}/delete', requirements: ['id' => '\d+'], name: 'technician_on_calls_delete', methods: ['GET'])]
    public function delete(Request $request, #[ApiValueResolverAttribute(parameters: ['resource' => TechnicianOnCallPersister::RESOURCE_URL])] ApiData $technicianOnCall)
    {
        $translator = $this->container->get(TranslatorInterface::class);

        if (!$this->isCsrfTokenValid('technician_on_call_delete', $request->query->get('_token'))) {
            $this->addFlash('error', $translator->trans('toc.messages.errors.delete', [], 'technician_on_call'));

            return $this->redirectToRoute('technician_on_calls_show', ['id' => $technicianOnCall['id']]);
        }

        try {
            $this->container->get(Client::class)->remove(TechnicianOnCallPersister::RESOURCE_URL, $technicianOnCall->getIriId());
            $this->addFlash('success', $translator->trans('toc.messages.success.delete', [], 'technician_on_call'));
        } catch (ClientException $e) {
            $errorDescription = json_decode($e->getResponse()->getContent(), true);
            $this->addFlash(
                'error',
                \sprintf('%s. %s', $translator->trans('toc.messages.errors.delete', [], 'technician_on_call'), $errorDescription['hydra:description'])
            );
        }

        return $this->redirectToRoute('technician_on_calls_home');
    }

    #[Route(path: '/{id}/links', requirements: ['id' => '\d+'], name: 'technician_on_calls_links', methods: ['GET'])]
    #[Template('service/technician_on_call/links.html.twig')]
    public function links(#[ApiValueResolverAttribute(parameters: ['resource' => TechnicianOnCallPersister::RESOURCE_URL])] ApiData $technicianOnCall)
    {
        return ['technicianOnCall' => $technicianOnCall];
    }

    #[Route(path: '/{id}/tasks', requirements: ['id' => '\d+'], name: 'technician_on_calls_tasks', methods: ['GET'])]
    #[Template('service/technician_on_call/tasks.html.twig')]
    public function tasks(#[ApiValueResolverAttribute(parameters: ['resource' => TechnicianOnCallPersister::RESOURCE_URL])] ApiData $technicianOnCall)
    {
        /** @var Client $client */
        $client = $this->container->get(Client::class);

        return [
            'technicianOnCall' => $technicianOnCall,
            'tasks' => $client->findBy('tasks', ['module.name' => 'TOC', 'referenceId' => $technicianOnCall['id']]),
        ];
    }

    #[Route(path: '/{id}/follow', requirements: ['id' => '\d+'], name: 'technician_on_calls_follow', methods: ['GET'])]
    #[Template('service/technician_on_call/follow.html.twig')]
    public function follow(#[ApiValueResolverAttribute(parameters: ['resource' => TechnicianOnCallPersister::RESOURCE_URL])] ApiData $technicianOnCall): array
    {
        return ['technicianOnCall' => $technicianOnCall];
    }

    #[Route(path: '/{id}/email', name: 'technician_on_calls_email', methods: ['GET|POST'])]
    #[Template('service/technician_on_call/email.html.twig')]
    public function email(Request $request, #[ApiValueResolverAttribute(parameters: ['resource' => TechnicianOnCallPersister::RESOURCE_URL])] ApiData $technicianOnCall)
    {
        $emailForm = $this->createForm(TechnicianOnCallEmailType::class);

        $emailForm->handleRequest($request);
        if ($emailForm->isSubmitted() && $emailForm->isValid()) {
            try {
                $data = $emailForm->getData();
                $client = $this->container->get(Client::class);

                $client->post($technicianOnCall->getIri().'/email', ['json' => $data]);
                $this->addFlash(
                    'success',
                    $this->container->get(TranslatorInterface::class)->trans('contacts.messages.success.mail', [], 'contacts')
                );
            } catch (\Exception $e) {
                $this->addFlash(
                    'error',
                    $this->container->get(TranslatorInterface::class)->trans('contacts.messages.error.mail', [], 'contacts')
                );
            }

            return $this->redirectToRoute('technician_on_calls_show', ['id' => $technicianOnCall['id']]);
        }

        return [
            'form' => $emailForm,
            'technicianOnCall' => $technicianOnCall,
        ];
    }

    #[Route(path: '/{id}/activity', name: 'technician_on_calls_activity', methods: ['GET'])]
    #[Template('service/technician_on_call/activity.html.twig')]
    public function activity(Request $request, #[ApiValueResolverAttribute(parameters: ['resource' => TechnicianOnCallPersister::RESOURCE_URL])] ApiData $technicianOnCall)
    {
        return [
            'technicianOnCall' => $technicianOnCall,
        ];
    }

    #[Route(path: '/subscriptions', name: 'technician_on_calls_subscriptions', defaults: ['label' => 'toc.button.subscriptions', 'domain' => 'technician_on_call'], methods: 'GET|POST')]
    #[Template('service/technician_on_call/subscription.html.twig')]
    public function subscriptions(): array
    {
        return [];
    }
}
