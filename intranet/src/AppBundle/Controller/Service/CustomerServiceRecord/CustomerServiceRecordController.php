<?php

declare(strict_types=1);

namespace AppBundle\Controller\Service\CustomerServiceRecord;

use ApiBundle\Client;
use ApiBundle\Form\ViolationMapper;
use ApiBundle\Http\FileStreamedResponseFactory;
use ApiBundle\Model\ApiData;
use AppBundle\Chart\ChartBuilderFactory;
use AppBundle\Configuration\ApiValueResolverAttribute;
use AppBundle\DataPersister\CommentPersister;
use AppBundle\DataPersister\Service\CustomerServiceRecordPersister;
use AppBundle\Enum\Service\CustomerServiceRecord\CustomerServiceRecordStatus;
use AppBundle\Factory\Service\CustomerServiceRecordFactory;
use AppBundle\Factory\Service\SurveyCommissioningFactory;
use AppBundle\Filters\Type\Service\CommissioningCustomerServiceRecordFilterType;
use AppBundle\Filters\Type\Service\CustomerServiceRecordFilterType;
use AppBundle\Form\Type\Common\DatePickerType;
use AppBundle\Form\Type\IdSearchType;
use AppBundle\Form\Type\Service\CustomerServiceRecord\CustomerServiceRecordCompletedBatchType;
use AppBundle\Form\Type\Service\CustomerServiceRecord\CustomerServiceRecordCompletedType;
use AppBundle\Form\Type\Service\CustomerServiceRecord\CustomerServiceRecordType;
use AppBundle\Form\Type\Service\CustomerServiceRecord\InterventionLiveType;
use AppBundle\Form\Type\Service\CustomerServiceRecord\OperatorEditType;
use AppBundle\Form\Type\Service\CustomerServiceRecord\SurveyCustomerServiceRecordType;
use AppBundle\Form\Type\Service\CustomerServiceRecord\TechnicianOnCallFactoryFlagType;
use AppBundle\Manager\FileManager;
use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpClient\Exception\ClientException;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route(path: '/service/customer-service-records', defaults: ['alvest_module' => 'CSR', 'moduleDomain' => 'customer_service_record'])]
class CustomerServiceRecordController extends AbstractController
{
    public const CUSTOMER_SERVICE_RECORD_URL = 'service/customer_service_records';
    public const CUSTOMER_SERVICE_RECORD_FILE_URL = 'customer_service_record_files';
    public const INTERVENTION_URL = 'service/interventions';
    public const LEADER_DEPARTMENTS = ['/departments/1', '/departments/2', '/departments/9'];

    public static function getSubscribedServices(): array
    {
        return array_merge(parent::getSubscribedServices(), [
            Client::class,
            ChartBuilderFactory::class,
            TranslatorInterface::class,
            FormFactoryInterface::class,
            ViolationMapper::class,
            CustomerServiceRecordFactory::class,
            CustomerServiceRecordPersister::class,
            FileManager::class,
            FileStreamedResponseFactory::class,
            SurveyCommissioningFactory::class,
            CommentPersister::class,
        ]);
    }

    #[Route(path: '', name: 'customer_service_record_home', methods: ['GET', 'POST'])]
    public function home()
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

        return $this->redirectToRoute('customer_service_record_search');
    }

    #[Route(path: '/search', name: 'customer_service_record_search', methods: ['GET|POST'])]
    #[Template('service/customer_service_record/search.html.twig')]
    public function search(Request $request)
    {
        $client = $this->container->get(Client::class);
        $parameters = ['itemsPerPage' => 10];
        $filtered = false;

        $idSearchForm = $this->createForm(IdSearchType::class, null, ['id_label' => false, 'id_placeholder' => 'By ID'])->handleRequest($request);
        if ($idSearchForm->isSubmitted() && $idSearchForm->isValid()) {
            $id = $idSearchForm->get('id')->getData();
            try {
                $client->get(\sprintf('%s/%s', self::CUSTOMER_SERVICE_RECORD_URL, $id));

                return $this->redirectToRoute('customer_service_record_show', ['id' => $id]);
            } catch (ClientException $e) {
                $this->addFlash('error', $this->container->get(TranslatorInterface::class)->trans('csr.errors.not_exist', ['%id%' => $id], 'customer_service_record'));
            }
        }

        $formFilter = $this->container->get(FormFactoryInterface::class)->createNamed('', CustomerServiceRecordFilterType::class, [], [
            'method' => Request::METHOD_GET,
            'action' => $this->generateUrl('customer_service_record_search'),
        ]);

        $formFilter->handleRequest($request);
        if ($formFilter->isSubmitted() && $formFilter->isValid()) {
            $data = $formFilter->getData();
            $filtered = true;
            $parameters = array_merge($parameters, $data);
            $parameters['itemsPerPage'] = 500;
            $parameters['order'] = [] !== $request->query->all('order') ? $request->query->all('order') : ['id' => 'asc'];

            if ($formFilter->getClickedButton() && 'download' === $formFilter->getClickedButton()->getName()) {
                $parameters = array_merge($parameters, $data);
                $parameters['itemsPerPage'] = 40000;
                $parameters['order'] = [] !== $request->query->all('order') ? $request->query->all('order') : ['id' => 'desc'];
                $parameters['columns'] = 'id,status,createdAt,completedAt,createdBy,plannedAt,title, description,legacyModuleName,legacyModuleId,interventionLeader,airport.code,equipmentRecord.serialNumber,equipmentRecord.model,equipmentRecord.manufacturerLocation.name,equipmentRecord.salesOrganisation.name,equipmentRecord.buyer.name,equipmentRecord.endUser.name,equipmentRecord.customerSerialNumber,interventionStatus,interventionPlannedAt,type';

                return $this->container->get(FileStreamedResponseFactory::class)->create(
                    self::CUSTOMER_SERVICE_RECORD_URL,
                    ['query' => $parameters, 'headers' => ['Accept' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet']],
                    'customer_service_records.xlsx'
                );
            }
        }

        return [
            'filtered' => $filtered,
            'customerServiceRecordsReportTitle' => $filtered ? 'csr.title.filtered' : 'csr.title.last',
            'formId' => $idSearchForm->createView(),
            'formFilter' => $formFilter->createView(),
            'customerServiceRecords' => $client->findBy(self::CUSTOMER_SERVICE_RECORD_URL, $parameters, ['id' => 'desc']),
        ];
    }

    #[Route(path: '/reports', name: 'customer_service_record_report', methods: ['GET'])]
    #[Template('service/customer_service_record/report.html.twig')]
    public function report(Request $request)
    {
        $client = $this->container->get(Client::class);
        $report = $client->get('reports/resource=/service/customer_service_records;x=equipmentRecord.salesOrganisationService.name;y=status');

        $report['yTotals'] = [
            'PENDING' => $report['yTotals']['PENDING'] ?? 0,
            'PLANNED' => $report['yTotals']['PLANNED'] ?? 0,
            'ASSIGNED' => $report['yTotals']['ASSIGNED'] ?? 0,
            'IN-PROGRESS' => $report['yTotals']['IN-PROGRESS'] ?? 0,
            'COMPLETED' => $report['yTotals']['COMPLETED'] ?? 0,
            'CLOSED' => $report['yTotals']['CLOSED'] ?? 0,
        ];

        foreach ($report['rows'] as $key => &$row) {
            $row = [
                'PENDING' => $row['PENDING'] ?? ['@type' => 'ReportCell', 'x' => $key, 'y' => 'PENDING', 'value' => 0],
                'PLANNED' => $row['PLANNED'] ?? ['@type' => 'ReportCell', 'x' => $key, 'y' => 'PLANNED', 'value' => 0],
                'ASSIGNED' => $row['ASSIGNED'] ?? ['@type' => 'ReportCell', 'x' => $key, 'y' => 'ASSIGNED', 'value' => 0],
                'IN-PROGRESS' => $row['IN-PROGRESS'] ?? ['@type' => 'ReportCell', 'x' => $key, 'y' => 'IN-PROGRESS', 'value' => 0],
                'COMPLETED' => $row['COMPLETED'] ?? ['@type' => 'ReportCell', 'x' => $key, 'y' => 'COMPLETED', 'value' => 0],
                'CLOSED' => $row['CLOSED'] ?? ['@type' => 'ReportCell', 'x' => $key, 'y' => 'CLOSED', 'value' => 0],
            ];
        }

        foreach (['status', 'equipmentRecord.salesOrganisationService'] as $parameter) {
            if ($request->query->has($parameter) && 'ALL' === $request->query->get($parameter)) {
                $request->query->remove($parameter);
            }
        }

        $reportPie = $this->container->get(Client::class)->get('reports/resource=/service/answer_survey_customer_service_records;x=answer;y=');

        $chartBuilder = $this->container->get(ChartBuilderFactory::class)
            ->getPieChartBuilder()
            ->addPlotOptions([
                'startAngle' => -90,
                'endAngle' => 90,
                'center' => ['50%', '75%'],
            ])
            ->setTitle($this->container->get(TranslatorInterface::class)->trans('csr.chart.shipping', [], 'customer_service_record'))
            ->disableLegend()
        ;

        foreach ($reportPie['xTotals'] as $key => $value) {
            $chartBuilder->addPlot(
                'Value',
                $this->container->get(TranslatorInterface::class)->trans('csr.questions.shipping_choices.'.$key, [], 'customer_service_record'),
                $value,
                [],
                ['name' => $this->container->get(TranslatorInterface::class)->trans('csr.questions.shipping_choices.'.$key, [], 'customer_service_record')]
            );
        }

        $surveyCommissioningReport = $this->forward('AppBundle\Controller\Service\CustomerServiceRecord\CustomerServiceRecordController::partialSurveyRating', [
            'request' => $request,
        ]);

        if ($surveyCommissioningReport instanceof StreamedResponse) {
            return $surveyCommissioningReport;
        }

        return [
            'bySSOServiceByStatus' => $report,
            'chart' => $chartBuilder->buildConfig(),
            'surveyCommissioningReport' => $surveyCommissioningReport->getContent(),
        ];
    }

    #[Route(path: '/reports/partial-survey-rating', name: 'customer_service_record_report_partial_survey_rating', methods: ['GET'])]
    #[Template('service/customer_service_record/report/_partial_survey_rating.html.twig')]
    public function partialSurveyRating(Request $request)
    {
        $location = $request->query->get('location');

        $form = $this->createForm(CommissioningCustomerServiceRecordFilterType::class, [
            'equipmentRecord.manufacturerLocation' => $location,
            'completedAt' => [
                'after' => (new \DateTime('first day of this month'))->format(DatePickerType::DEFAULT_INPUT_FORMAT),
                'before' => (new \DateTime('last day of this month'))->format(DatePickerType::DEFAULT_INPUT_FORMAT),
            ],
        ], [
            'method' => Request::METHOD_GET,
        ]);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid() && 'download' === $form->getClickedButton()?->getName()) {
            return $this->container->get(FileStreamedResponseFactory::class)->create(
                'service/commissioning_customer_service_records',
                [
                    'query' => [
                        ...$form->getData(),
                        'pagination' => 0,
                        'columns' => 'id,createdAt,completedAt,equipmentRecord.salesOrganisation.name,equipmentRecord.salesOrganisationService.name,equipmentRecord.manufacturerLocation.name,technicians,equipmentRecord.endUser,equipmentRecord.serialNumber,equipmentRecord.model,airport,equipmentRecord.greenTagDate,equipmentRecord.dateCommissioned,aspect,aspect_comment,conformity,conformity_comment,operational,operational_comment,shipping,shipping_comment,is_link_working,is_link_working_comment',
                    ],
                    'headers' => ['Accept' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'],
                ],
                'commissioning_customer_service_records_survey_result.xlsx'
            );
        }

        $customerServiceRecords = iterator_to_array($this->container->get(Client::class)->findAll(
            'service/commissioning_customer_service_records',
            $form->getData()
        ));

        return [
            'surveyFilterForm' => $form->createView(),
            'customerServiceRecords' => $customerServiceRecords,
        ];
    }

    #[Route(path: '/{id}/show', requirements: ['id' => '\d+'], name: 'customer_service_record_show', methods: 'GET|POST|PUT')]
    #[Template('service/customer_service_record/show.html.twig')]
    public function show(#[ApiValueResolverAttribute(parameters: [
        'resource' => self::CUSTOMER_SERVICE_RECORD_URL,
        'allowedTypes' => ['default_customer_service_record', 'serviceBulletinCustomerServiceRecord', 'technicianOnCallCustomerServiceRecord', 'commissioningCustomerServiceRecord'],
    ])] ApiData $customerServiceRecord, Request $request)
    {
        $closedForm = $factoryFlagForm = null;
        $client = $this->container->get(Client::class);

        if ('TechnicianOnCallCustomerServiceRecord' === $customerServiceRecord['@type']
            && !$customerServiceRecord['technicianOnCall']['factoryFlag']
            && !\in_array($customerServiceRecord['technicianOnCall']['status'], ['SOLVED', 'CLOSED'], true)
        ) {
            $factoryFlagForm = $this->createForm(TechnicianOnCallFactoryFlagType::class);
            $factoryFlagForm->handleRequest($request);

            if ($factoryFlagForm->isSubmitted() && $factoryFlagForm->isValid()) {
                try {
                    /** @var CommentPersister $commentPersister */
                    $commentPersister = $this->container->get(CommentPersister::class);
                    $commentPersister->save($factoryFlagForm->get('comment')->getData(), $customerServiceRecord['technicianOnCall']['@id'], 'OPEN_FACTORY_FLAG');

                    $this->addFlash('success', $this->container->get(TranslatorInterface::class)->trans('csr.success.factory_flag', [], 'customer_service_record'));

                    return $this->redirectToRoute('customer_service_record_show', ['id' => $customerServiceRecord['id']]);
                } catch (ClientException $e) {
                    /** @var ViolationMapper $violationMapper */
                    $violationMapper = $this->container->get(ViolationMapper::class);
                    $violationMapper->mapToForm($e, $factoryFlagForm);

                    $this->addFlash('error', $this->container->get(TranslatorInterface::class)->trans('errors.something_went_wrong'));
                }
            }
        }

        if (!empty($intervention = $customerServiceRecord['openIntervention'])) {
            $form = $this->createForm(InterventionLiveType::class, $intervention, [
                'customerServiceRecord' => $customerServiceRecord->toArray(),
            ]);

            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                try {
                    $data = $form->getData();

                    $client->put($data['@id'], ['json' => array_filter([
                        'status' => $data['status'],
                        'startedAt' => $data['startedDate'] ?? null,
                        'endedAt' => $data['endedDate'] ?? null,
                    ])]);

                    if ($form->has('answerSurveyCustomerServiceRecords')) {
                        $customerServiceRecord = $customerServiceRecord->toArray();
                        $answerData = $form->get('answerSurveyCustomerServiceRecords')->getData();

                        // Transform element on IRI
                        foreach ($answerData as $key => $answer) {
                            $answerData[$key]['questionSurveyCustomerServiceRecord'] = $answer['questionSurveyCustomerServiceRecord']['@id'];
                        }

                        $this->container->get(CustomerServiceRecordPersister::class)->save([
                            '@id' => $customerServiceRecord['@id'],
                            'type' => $customerServiceRecord['type'],
                            'answerSurveyCustomerServiceRecords' => [...$answerData],
                        ]);
                    }

                    if ($form->has('solveToc') && $form->get('solveToc')->getData()) {
                        try {
                            $tocId = $customerServiceRecord['technicianOnCall']['id'];

                            $client->put($customerServiceRecord['technicianOnCall']['@id'].'/status', ['json' => [
                                'status' => 'SOLVED',
                                'originalSymptoms' => $data['symptoms'],
                                'originalRootCause' => $data['rootCause'],
                                'originalSolution' => $data['solution'],
                            ]]);
                            $this->addFlash('success', $this->container->get(TranslatorInterface::class)->trans('intervention.success.toc', ['%toc%' => $tocId], 'customer_service_record'));
                        } catch (ClientException $e) {
                            $this->addFlash('error', $this->container->get(TranslatorInterface::class)->trans('intervention.error.toc', ['%toc%' => $tocId], 'customer_service_record'));
                        }
                    }

                    $this->addFlash('success', $this->container->get(TranslatorInterface::class)->trans('intervention.success.update', [], 'customer_service_record'));

                    if (null !== ($hourmeter = $form->get('hourmeter')->getData())) {
                        try {
                            $this->container->get(Client::class)->save('/support/equipment_record/customer_service_record_hour_meter_transactions', [
                                'hourMeter' => $hourmeter,
                                'equipmentRecord' => $customerServiceRecord['equipmentRecord']['@id'],
                                'customerServiceRecord' => $customerServiceRecord['@id'],
                            ]);
                            $this->addFlash('success', $this->container->get(TranslatorInterface::class)->trans('csr.success.hourmeter', ['%hourmeter%' => $hourmeter], 'customer_service_record'));
                        } catch (\Exception $e) {
                            $this->addFlash('error', $e->getMessage());
                        }
                    }

                    return $this->redirectToRoute('customer_service_record_show', ['id' => $customerServiceRecord['id']]);
                } catch (ClientException $e) {
                    $this->container->get(ViolationMapper::class)->mapToForm($e, $form);
                }
            }
        }

        if ('COMPLETED' === $customerServiceRecord['status']) {
            $closedForm = $this->createForm(CustomerServiceRecordCompletedType::class);
            $closedForm->handleRequest($request);

            if ($closedForm->isSubmitted() && $closedForm->isValid()) {
                try {
                    $this->container->get(Client::class)->put($customerServiceRecord['@id'], [
                        'json' => ['status' => 'CLOSED'],
                    ]);
                    $this->addFlash('success', $this->container->get(TranslatorInterface::class)->trans('csr.success.closed', [], 'customer_service_record'));

                    return $this->redirectToRoute('customer_service_record_show', ['id' => $customerServiceRecord['id']]);
                } catch (ClientException $e) {
                    $this->container->get(ViolationMapper::class)->mapToForm($e, $closedForm);
                }
            }
        }

        return [
            'customerServiceRecord' => $customerServiceRecord,
            'closedForm' => $closedForm?->createView(),
            'interventionsCount' => \count($customerServiceRecord['interventions']),
            'factoryFlagForm' => $factoryFlagForm?->createView(),
        ];
    }

    #[Route(path: '/add', name: 'customer_service_record_add', methods: ['GET', 'POST'])]
    #[Template('service/customer_service_record/add.html.twig')]
    #[IsGranted('FEATURE_CUSTOMER_SERVICE_RECORD_CREATE')]
    public function add(Request $request)
    {
        $sessionData = $request->getSession()->get('customer_service_record_data') ?? [];
        $customerServiceRecord = $this->container->get(CustomerServiceRecordFactory::class)->createFromSessionData($sessionData);

        $form = $this->createForm(CustomerServiceRecordType::class, $customerServiceRecord);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $customerServiceRecord = $this->container->get(CustomerServiceRecordPersister::class)->save($form->getData());
                $this->addFlash('success', $this->container->get(TranslatorInterface::class)->trans('csr.success.add', [], 'customer_service_record'));

                if (null !== ($hourmeter = $form->get('hourmeter')->getData())) {
                    try {
                        $this->container->get(Client::class)->save('/support/equipment_record/customer_service_record_hour_meter_transactions', [
                            'hourMeter' => $hourmeter,
                            'equipmentRecord' => $customerServiceRecord['equipmentRecord']['@id'],
                            'customerServiceRecord' => $customerServiceRecord['@id'],
                        ]);
                        $this->addFlash('success', $this->container->get(TranslatorInterface::class)->trans('csr.success.hourmeter', ['%hourmeter%' => $hourmeter], 'customer_service_record'));
                    } catch (\Exception $e) {
                        $this->addFlash('error', $e->getMessage());
                    }
                }

                return $this->redirectToRoute('customer_service_record_show', ['id' => $customerServiceRecord['id']]);
            } catch (ClientException $e) {
                $this->container->get(ViolationMapper::class)->mapToForm($e, $form);
            }
        }

        return [
            'form' => $form->createView(),
        ];
    }

    #[Route(path: '/{id}/edit', name: 'customer_service_record_edit', methods: ['GET', 'POST', 'PUT'])]
    #[Template('service/customer_service_record/edit.html.twig')]
    #[IsGranted('FEATURE_CUSTOMER_SERVICE_RECORD_EDIT')]
    public function edit(#[ApiValueResolverAttribute(parameters: [
        'resource' => self::CUSTOMER_SERVICE_RECORD_URL,
        'filters' => ['normalizationGroups' => ['workflow']],
        'allowedTypes' => ['default_customer_service_record', 'serviceBulletinCustomerServiceRecord', 'technicianOnCallCustomerServiceRecord', 'commissioningCustomerServiceRecord'],
    ])] ApiData $customerServiceRecord, Request $request)
    {
        $form = $this->createForm(CustomerServiceRecordType::class, $customerServiceRecord->toArray());
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $formData = $form->getData();
                $customerServiceRecord = $this->container->get(CustomerServiceRecordPersister::class)->save(
                    array_intersect_key($formData, array_flip(array_keys($form->all())) + ['@id' => $customerServiceRecord->getIri(), 'type' => 'type'])
                );

                $this->addFlash('success', $this->container->get(TranslatorInterface::class)->trans('csr.success.update', [], 'customer_service_record'));

                return $this->redirectToRoute('customer_service_record_show', ['id' => $customerServiceRecord['id']]);
            } catch (ClientException $e) {
                $this->container->get(ViolationMapper::class)->mapToForm($e, $form);
            }
        }

        $openIntervention = $customerServiceRecord['openIntervention'] ?? null;
        $operatorsForm = $this->createForm(OperatorEditType::class, $openIntervention);

        $operatorsForm->handleRequest($request);
        if ($operatorsForm->isSubmitted() && $operatorsForm->isValid()) {
            try {
                $this->container->get(Client::class)->save(self::INTERVENTION_URL, ['@id' => $operatorsForm->getData()['@id'], 'operators' => $operatorsForm->get('operators')->getData()]);

                $this->addFlash('success', $this->container->get(TranslatorInterface::class)->trans('csr.success.update', [], 'customer_service_record'));

                return $this->redirectToRoute('customer_service_record_show', ['id' => $customerServiceRecord['id']]);
            } catch (ClientException $e) {
                $this->container->get(ViolationMapper::class)->mapToForm($e, $form);
            }
        }

        $surveyForm = null;
        if ('commissioning' === $customerServiceRecord['type'] && \in_array($customerServiceRecord['status'], [CustomerServiceRecordStatus::COMPLETED->value, CustomerServiceRecordStatus::CLOSED->value], true)) {
            $answerList = $this->container->get(SurveyCommissioningFactory::class)->createAnswersCollection($customerServiceRecord['answerSurveyCustomerServiceRecords']);
            $fullAnswers['answerSurveyCustomerServiceRecords'] = $answerList;

            $surveyForm = $this->createForm(SurveyCustomerServiceRecordType::class, $fullAnswers);
            $surveyForm->handleRequest($request);
            if ($surveyForm->isSubmitted() && $surveyForm->isValid()) {
                $customerServiceRecord = $customerServiceRecord->toArray();

                $data = array_intersect_key($customerServiceRecord, array_flip(array_keys($surveyForm->all())) + ['@id' => $customerServiceRecord['@id'], 'type' => $customerServiceRecord['type']]);
                $data['answerSurveyCustomerServiceRecords'] = $surveyForm->getData()['answerSurveyCustomerServiceRecords'];

                foreach ($data['answerSurveyCustomerServiceRecords'] as $key => $answer) {
                    $data['answerSurveyCustomerServiceRecords'][$key]['questionSurveyCustomerServiceRecord'] = $answer['questionSurveyCustomerServiceRecord']['@id'];
                }

                $this->container->get(CustomerServiceRecordPersister::class)->save($data);

                return $this->redirectToRoute('customer_service_record_show', ['id' => $customerServiceRecord['id']]);
            }
        }

        return [
            'form' => $form->createView(),
            'operatorForm' => $operatorsForm->createView(),
            'surveyForm' => $surveyForm?->createView(),
            'customerServiceRecord' => $customerServiceRecord,
        ];
    }

    #[Route(path: '/{id}/delete', requirements: ['id' => '\d+'], name: 'customer_service_record_delete', methods: ['GET'])]
    #[IsGranted('FEATURE_CUSTOMER_SERVICE_RECORD_DELETE')]
    public function delete(#[ApiValueResolverAttribute(parameters: [
        'resource' => self::CUSTOMER_SERVICE_RECORD_URL,
        'allowedTypes' => ['default_customer_service_record', 'serviceBulletinCustomerServiceRecord', 'technicianOnCallCustomerServiceRecord', 'commissioningCustomerServiceRecord'],
    ])] ApiData $customerServiceRecord, Request $request)
    {
        if (!$this->isCsrfTokenValid('customer_service_record_delete', $request->query->get('_token'))) {
            $this->addFlash('error', $this->container->get(TranslatorInterface::class)->trans('csr.errors.delete', [], 'customer_service_record'));

            return $this->redirectToRoute('customer_service_record_edit', ['id' => $customerServiceRecord['id']]);
        }

        try {
            $this->container->get(Client::class)->remove(self::CUSTOMER_SERVICE_RECORD_URL, $customerServiceRecord->getIriId());
            $this->addFlash('success', $this->container->get(TranslatorInterface::class)->trans('csr.success.delete', [], 'customer_service_record'));
        } catch (ClientException $e) {
            $errorDescription = json_decode($e->getResponse()->getContent(), true);
            $this->addFlash(
                'error',
                \sprintf('%s. %s', $this->container->get(TranslatorInterface::class)->trans('csr.errors.delete', [], 'customer_service_record'), $errorDescription['hydra:description'])
            );
        }

        return $this->redirectToRoute('customer_service_record_search');
    }

    #[Route(path: '/multiple_closure', name: 'customer_service_record_multiple_closure', methods: ['GET', 'POST'])]
    #[Template('service/customer_service_record/csr_to_close.html.twig')]
    #[IsGranted('FEATURE_CUSTOMER_SERVICE_RECORD_CLOSE')]
    public function multiplecustomerServiceRecordClosure(Request $request)
    {
        $client = $this->container->get(Client::class);
        $user = $this->container->get(Client::class)->get('/me');

        $parameters = [
            'status' => 'COMPLETED',
            'equipmentRecord.salesOrganisation' => $user['businessUnit']['location']['@id'],
            'normalization_groups_override' => ['customer_service_record_light'],
        ];
        $completedCustomerServiceRecords = $client->findBy(self::CUSTOMER_SERVICE_RECORD_URL, $parameters)->getIndexedCollection('id');
        $form = $this->createForm(CustomerServiceRecordCompletedBatchType::class, ['completedCustomerServiceRecords' => $completedCustomerServiceRecords]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $customerServiceRecordToClose = [];
            foreach ($data['completedCustomerServiceRecords'] as $completedCustomerServiceRecord) {
                if (false === $completedCustomerServiceRecord['closed']) {
                    continue;
                }
                $customerServiceRecordToClose[] = $completedCustomerServiceRecord['@id'];
            }

            try {
                if (!empty($customerServiceRecordToClose)) {
                    $client->save('/service/customer_service_records/multiple_closure', ['customerServiceRecords' => $customerServiceRecordToClose]);
                    $this->addFlash('success', $this->container->get(TranslatorInterface::class)->trans('csr.success.update', [], 'customer_service_record'));
                }

                return $this->redirectToRoute('customer_service_record_multiple_closure');
            } catch (ClientException $e) {
                $this->container->get(ViolationMapper::class)->mapToForm($e, $form);
            }
        }

        return [
            'completedCustomerServiceRecords' => $completedCustomerServiceRecords,
            'form' => $form->createView(),
        ];
    }

    #[Route(path: '/{id}/edit_ajax', name: 'files_ajax_customer_service_record', methods: 'GET')]
    #[Template('service/customer_service_record/partial/files_ajax.html.twig')]
    public function customerServiceRecordFilesAjax(#[ApiValueResolverAttribute(parameters: [
        'resource' => self::CUSTOMER_SERVICE_RECORD_URL,
        'allowedTypes' => ['default_customer_service_record', 'serviceBulletinCustomerServiceRecord', 'technicianOnCallCustomerServiceRecord', 'commissioningCustomerServiceRecord'],
    ])] ApiData $customerServiceRecord): array
    {
        return compact('customerServiceRecord');
    }

    #[Route(path: '/{customerServiceRecordId}/files/{id}', requirements: ['id' => '\d+'], name: 'customer_service_record_files_show', methods: 'GET')]
    public function showFile(
        #[ApiValueResolverAttribute(parameters: [
            'id' => 'customerServiceRecordId',
            'resource' => self::CUSTOMER_SERVICE_RECORD_URL,
            'allowedTypes' => ['default_customer_service_record', 'serviceBulletinCustomerServiceRecord', 'technicianOnCallCustomerServiceRecord', 'commissioningCustomerServiceRecord'],
        ])] ApiData $customerServiceRecord, $id,
    ) {
        return $this->container->get(FileStreamedResponseFactory::class)->create(\sprintf('%s/%s/files/%s', self::CUSTOMER_SERVICE_RECORD_URL, $customerServiceRecord['id'], $id));
    }

    #[Route(path: '/{customerServiceRecordId}/files/{id}/delete', name: 'customer_service_record_files_delete', methods: ['GET'])]
    #[IsGranted('FEATURE_CUSTOMER_SERVICE_RECORD_EDIT')]
    public function deleteFile(
        Request $request,
        #[ApiValueResolverAttribute(parameters: [
            'id' => 'customerServiceRecordId',
            'resource' => self::CUSTOMER_SERVICE_RECORD_URL,
            'allowedTypes' => ['default_customer_service_record', 'serviceBulletinCustomerServiceRecord', 'technicianOnCallCustomerServiceRecord', 'commissioningCustomerServiceRecord'],
        ])] ApiData $customerServiceRecord, $id)
    {
        if (!$this->isCsrfTokenValid('delete_customer_service_record_file', $request->query->get('_token'))) {
            $this->addFlash('error', $this->container->get(TranslatorInterface::class)->trans('delete.errors', [], 'file_type'));

            return $this->redirectToRoute('customer_service_record_edit', ['id' => $customerServiceRecord->getIriId()]);
        }
        $this->container->get(FileManager::class)->deleteFile($customerServiceRecord, self::CUSTOMER_SERVICE_RECORD_URL, \sprintf('files/%s', $id));
        $this->addFlash('success', $this->container->get(TranslatorInterface::class)->trans('delete.success', [], 'file_type'));

        return $this->redirectToRoute('customer_service_record_edit', ['id' => $customerServiceRecord->getIriId()]);
    }

    #[\Symfony\Component\Routing\Annotation\Route(path: '/{customerServiceRecordId}/files/{fileId}/change_visibility', name: 'customer_service_record_file_change_visibility', methods: 'GET')]
    public function changeFileVisibility(#[ApiValueResolverAttribute(parameters: ['resource' => self::CUSTOMER_SERVICE_RECORD_FILE_URL, 'id' => 'fileId'])] ApiData $customerServiceRecordFile, int $fileId, int $customerServiceRecordId): RedirectResponse
    {
        try {
            $this->container->get(Client::class)->put(
                \sprintf('files/%d', $fileId),
                ['json' => ['fileId' => $fileId, 'public' => true !== $customerServiceRecordFile['public']]]
            );

            $this->addFlash('success', $this->container->get(TranslatorInterface::class)->trans('visibility.success', [], 'file_type'));
        } catch (ClientException $e) {
            $errorDescription = json_decode($e->getResponse()->getContent(false), true);
            $this->addFlash('error', \sprintf('%s %s', $this->container->get(TranslatorInterface::class)->trans('visibility.errors', [], 'file_type'), $errorDescription['hydra:description']));
        }

        return $this->redirectToRoute('customer_service_record_edit', ['id' => $customerServiceRecordId]);
    }
}
