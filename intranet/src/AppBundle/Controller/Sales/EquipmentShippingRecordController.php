<?php

declare(strict_types=1);

namespace AppBundle\Controller\Sales;

use ApiBundle\Client;
use ApiBundle\Form\ViolationMapper;
use ApiBundle\Http\FileStreamedResponseFactory;
use ApiBundle\Iri\Iri;
use ApiBundle\Model\ApiData;
use AppBundle\Configuration\ApiValueResolverAttribute;
use AppBundle\Controller\Support\EquipmentSerialsController;
use AppBundle\Filters\Type\Quality\SmwFilterType;
use AppBundle\Filters\Type\Sales\EquipmentShippingRecordFilterType;
use AppBundle\Form\Type\IdSearchType;
use AppBundle\Form\Type\LegacyIdSearchType;
use AppBundle\Form\Type\Sales\EquipmentShippingRecord\EquipmentShippingRecordCostType;
use AppBundle\Form\Type\Sales\EquipmentShippingRecord\EquipmentShippingRecordEditType;
use AppBundle\Form\Type\Sales\EquipmentShippingRecord\EquipmentShippingRecordFromEquipmentRecordType;
use AppBundle\Form\Type\Sales\EquipmentShippingRecord\EquipmentShippingRecordFromSolType;
use AppBundle\Form\Type\Sales\EquipmentShippingRecord\EquipmentShippingRecordType;
use AppBundle\Form\Type\Sales\EquipmentShippingRecord\PlanningDailyExceptionType;
use AppBundle\Form\Type\SimpleFileType;
use AppBundle\Manager\FileManager;
use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpClient\Exception\ClientException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route(path: '/sales/equipment_shipping_records', defaults: ['alvest_module' => 'ESR', 'moduleDomain' => 'equipment_shipping_record'])]
class EquipmentShippingRecordController extends AbstractController
{
    public const string RESOURCE_URL = 'sales/equipment_shipping_records';
    public const string RESOURCE_URL_ESRL = 'sales/equipment_shipping_record_lines';
    public const string RESOURCE_URL_ESRC = 'sales/equipment_shipping_record_costs';

    public static function getSubscribedServices(): array
    {
        return array_merge(parent::getSubscribedServices(), [
            Client::class,
            TranslatorInterface::class,
            FileManager::class,
            ViolationMapper::class,
            FileStreamedResponseFactory::class,
        ]);
    }

    #[Route(path: '', name: 'equipment_shipping_record_home', methods: ['GET', 'POST'])]
    #[Template('sales/equipment_shipping_records/home.html.twig')]
    public function index(Request $request)
    {
        $client = $this->container->get(Client::class);
        $formFactoryInterface = $this->container->get('form.factory');

        $reportSSObyStatus = $client->get('reports/resource=/sales/equipment_shipping_records;x=sso.name;y=status');

        $options['options']['status'] = ['PENDING', 'BOOKED'];
        $reportFactoryByStatus = $client->get('reports/resource=/sales/equipment_shipping_records;x=equipmentShippingRecordLines.equipmentRecord.manufacturerLocation.name;y=status',
            [
                'query' => $options,
            ]
        );

        $idSearchForm = $formFactoryInterface->createNamed('by_id', IdSearchType::class, null, [
            'id_label' => false,
            'id_placeholder' => 'By ID',
        ]);
        $idSearchForm->handleRequest($request);
        if ($idSearchForm->isSubmitted() && $idSearchForm->isValid()) {
            $id = $idSearchForm->get('id')->getData();
            try {
                $client->get(\sprintf('%s/%s', self::RESOURCE_URL, $id));

                return $this->redirectToRoute('equipment_shipping_record_show', ['id' => $id]);
            } catch (ClientException $e) {
                $this->addFlash('error', \sprintf('ESR #%s does not exist', $id));
            }
        }

        $legacyIdSearchForm = $this->createForm(LegacyIdSearchType::class, null, [
            'legacy_id_label' => false,
            'legacy_id_placeholder' => 'By Legacy ID',
        ]);

        $legacyIdSearchForm->handleRequest($request);
        if ($legacyIdSearchForm->isSubmitted() && $legacyIdSearchForm->isValid()) {
            $legacyId = $legacyIdSearchForm->get('legacyId')->getData();
            try {
                $equipmentShippingRecord = $client->findOneBy(self::RESOURCE_URL, ['legacyId' => $legacyId]);

                return $this->redirectToRoute('equipment_shipping_record_show', ['id' => Iri::id($equipmentShippingRecord)]);
            } catch (\RangeException $e) {
                $this->addFlash('error', \sprintf('ESR #%s does not exist', $legacyId));
            }
        }

        $formFilter = $formFactoryInterface->createNamed('', EquipmentShippingRecordFilterType::class);

        $formFilter->handleRequest($request);
        if ($formFilter->isSubmitted() && $formFilter->isValid()) {
            $data = $formFilter->getData();
            if ('' === $data['shipAuthorization']) {
                unset($data['shipAuthorization']);
            }
            $parameters = [
                'itemsPerPage' => 2000,
                'order' => ['id' => 'DESC'],
            ];
            $title = 'equipment_shipping_record.filtered_esr';
            $parameters = array_merge($parameters, $data);
            $equipmentShippingRecords = $client->findBy(self::RESOURCE_URL, $parameters);

            return [
                'reportSSObyStatus' => $reportSSObyStatus,
                'equipmentShippingRecords' => $equipmentShippingRecords,
                'formFilter' => $formFilter->createView(),
                'legacyIdSearchForm' => $legacyIdSearchForm->createView(),
                'idSearchForm' => $idSearchForm->createView(),
                'title' => $title,
                'filtered' => true,
                'reportFactoryByStatus' => $reportFactoryByStatus,
            ];
        }

        $title = 'equipment_shipping_record.last_esr_table';
        $parameters = ['itemsPerPage' => 10, 'order' => ['id' => 'DESC']];
        $equipmentShippingRecords = $client->findBy(self::RESOURCE_URL, $parameters);

        return [
            'reportSSObyStatus' => $reportSSObyStatus,
            'equipmentShippingRecords' => $equipmentShippingRecords,
            'formFilter' => $formFilter->createView(),
            'idSearchForm' => $idSearchForm->createView(),
            'legacyIdSearchForm' => $legacyIdSearchForm->createView(),
            'title' => $title,
            'filtered' => false,
            'reportFactoryByStatus' => $reportFactoryByStatus,
        ];
    }

    #[Route(path: '/{id}/show', name: 'equipment_shipping_record_show', requirements: ['id' => '\d+'], methods: ['GET', 'POST'])]
    #[Template('sales/equipment_shipping_records/show.html.twig')]
    public function show(Request $request, #[ApiValueResolverAttribute(parameters: ['resource' => self::RESOURCE_URL])] ApiData $equipmentShippingRecord)
    {
        $equipmentShippingRecord = $equipmentShippingRecord->toArray();
        foreach ($equipmentShippingRecord['equipmentShippingRecordLines'] as &$line) {
            $line['esrId'] = $equipmentShippingRecord['id'];
        }
        foreach ($equipmentShippingRecord['equipmentShippingRecordCosts'] as &$cost) {
            $cost['esrId'] = $equipmentShippingRecord['id'];
        }

        $formFiles = $this->createForm(SimpleFileType::class);
        $formFiles->handleRequest($request);
        if ($formFiles->isSubmitted() && $formFiles->isValid()) {
            try {
                /** @var UploadedFile $file */
                $file = $formFiles->get('file')->getData();

                if ($file instanceof UploadedFile) {
                    $this->container->get(FileManager::class)->uploadFile(
                        $equipmentShippingRecord,
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
                    $this->container->get(TranslatorInterface::class)->trans('files.upload_success', [], 'messages')
                );

                return $this->redirectToRoute('equipment_shipping_record_show', ['id' => $equipmentShippingRecord['id']]);
            } catch (ClientException $e) {
                $this->container->get(ViolationMapper::class)->mapToForm($e, $formFiles);
            }
        }

        return [
            'form_files' => $formFiles->createView(),
            'equipmentShippingRecord' => $equipmentShippingRecord,
        ];
    }

    #[Route(path: '/add', name: 'equipment_shipping_record_add', methods: ['GET', 'POST'])]
    #[Template('sales/equipment_shipping_records/create.html.twig')]
    #[IsGranted('FEATURE_ESR_WRITE')]
    public function create(Request $request)
    {
        $client = $this->container->get(Client::class);

        $esrForm = $this->container->get('form.factory')->createNamed('esrForm', EquipmentShippingRecordType::class);

        $esrForm->handleRequest($request);

        if ($response = $this->submitEquipmentShippingRecordForm($client, $esrForm)) {
            return $response;
        }

        return [
            'esrForm' => $esrForm->createView(),
        ];
    }

    #[Route(path: '/add/from-equipment-record/{equipmentRecord}', name: 'equipment_shipping_record_add_from_equipment_record', methods: ['GET', 'POST'])]
    #[Template('sales/equipment_shipping_records/create.html.twig')]
    #[IsGranted('FEATURE_ESR_WRITE')]
    public function createFromEquipmentRecord(Request $request, #[ApiValueResolverAttribute(parameters: ['resource' => EquipmentSerialsController::EQUIPMENT_RECORD_URL, 'id' => 'equipmentRecord'])] ApiData $equipmentRecord)
    {
        $client = $this->container->get(Client::class);

        $esrForm = $this->container->get('form.factory')->createNamed('esrForm', EquipmentShippingRecordFromEquipmentRecordType::class, null,
            [
                'equipment_record' => $equipmentRecord,
            ]
        );

        $esrForm->handleRequest($request);

        if ($response = $this->submitEquipmentShippingRecordForm($client, $esrForm)) {
            return $response;
        }

        return [
            'esrForm' => $esrForm->createView(),
        ];
    }

    #[Route(path: '/add/from-sol', name: 'equipment_shipping_record_add_from_sol', methods: ['GET', 'POST'])]
    #[Template('sales/equipment_shipping_records/create.html.twig')]
    #[IsGranted('FEATURE_ESR_WRITE')]
    public function createFromSol(Request $request)
    {
        $client = $this->container->get(Client::class);

        $customerIds = array_values(array_unique(array_filter($request->query->all('customerIds'))));
        $apiSso = $request->query->get('sso');
        $equipmentRecordIds = array_values(array_unique(array_filter($request->query->all('equipmentRecordIds'))));
        $apiIncoterm = $request->query->get('incoterm');
        $shipAuthorization = $request->query->get('shipAuthorization');

        $customerChoices = [];
        foreach ($customerIds as $customerId) {
            $customer = $client->get($customerId);
            $customerChoices[$customer['name']] = $customer['@id'];
        }

        $esrForm = $this->container->get('form.factory')->createNamed('esrForm', EquipmentShippingRecordFromSolType::class, null,
            [
                'customer_choices' => $customerChoices,
                'sso' => $apiSso,
                'equipment_record_ids' => $equipmentRecordIds,
                'incoterm' => $apiIncoterm,
                'ship_authorization' => 'Y' === $shipAuthorization,
            ]
        );

        $esrForm->handleRequest($request);

        if ($response = $this->submitEquipmentShippingRecordForm($client, $esrForm)) {
            return $response;
        }

        return [
            'esrForm' => $esrForm->createView(),
            'showSolInformationMessage' => true,
        ];
    }

    #[Route(path: '/{id}/edit', name: 'equipment_shipping_record_edit', methods: ['GET', 'POST'], requirements: ['id' => '\d+'])]
    #[Template('sales/equipment_shipping_records/edit.html.twig')]
    #[IsGranted('FEATURE_ESR_WRITE')]
    public function edit(Request $request, #[ApiValueResolverAttribute(parameters: ['resource' => self::RESOURCE_URL])] ApiData $equipmentShippingRecord)
    {
        $client = $this->container->get(Client::class);
        $formData = $equipmentShippingRecord->toArray();

        $customerChoices = [];

        foreach ($formData['equipmentShippingRecordLines'] ?? [] as $line) {
            $equipmentRecord = $line['equipmentRecord'];

            $customers = [$equipmentRecord['buyer'] ?? null, $equipmentRecord['endUser'] ?? null];

            foreach ($customers as $customer) {
                if (null === $customer) {
                    continue;
                }

                $customerChoices[$customer['name']] = $customer['@id'];
            }
        }

        if ([] !== $customerChoices && isset($formData['customer']['@id'])) {
            $formData['customer'] = $formData['customer']['@id'];
        }

        $esrForm = $this->container->get('form.factory')->createNamed('esrForm', EquipmentShippingRecordEditType::class, $formData, ['customer_choices' => $customerChoices]);

        $esrForm->handleRequest($request);

        if ($response = $this->submitEquipmentShippingRecordForm($client, $esrForm)) {
            return $response;
        }

        return [
            'esrForm' => $esrForm->createView(),
        ];
    }

    #[Route(path: '/{id}/costs/add', name: 'equipment_shipping_record_costs_add', defaults: ['label' => 'equipment_shipping_record.cost.add', 'domain' => 'equipment_shipping_record'], methods: ['GET|POST'], requirements: ['id' => '\d+'])]
    #[Route(path: '/{id}/costs/{costId}/edit', name: 'equipment_shipping_record_costs_edit', defaults: ['label' => 'equipment_shipping_record.cost.edit', 'domain' => 'equipment_shipping_record'], methods: ['GET|POST'], requirements: ['id' => '\d+', 'costId' => '\d+'])]
    #[Template('sales/equipment_shipping_records/add_cost.html.twig')]
    #[IsGranted('FEATURE_ESR_WRITE')]
    public function writeCost(
        #[ApiValueResolverAttribute(parameters: ['resource' => self::RESOURCE_URL])] ApiData $equipmentShippingRecord,
        Request $request,
        #[ApiValueResolverAttribute(parameters: ['resource' => self::RESOURCE_URL_ESRC, 'id' => 'costId'])] ?ApiData $equipmentShippingRecordCost = null,
    ) {
        $client = $this->container->get(Client::class);

        $costForm = $this->container->get('form.factory')->createNamed('esrcForm', EquipmentShippingRecordCostType::class, $equipmentShippingRecordCost);
        $costForm->handleRequest($request);
        if ($costForm->isSubmitted() && $costForm->isValid()) {
            try {
                $data = $costForm->getData();
                $data['equipmentShippingRecord'] = $equipmentShippingRecord->getIri();

                $client->save(self::RESOURCE_URL_ESRC, $data);
                $this->addFlash(
                    'success',
                    $this->container->get(TranslatorInterface::class)->trans('equipment_shipping_record.cost.create.success', [], 'equipment_shipping_record')
                );

                return $this->redirectToRoute('equipment_shipping_record_show', ['id' => Iri::id($equipmentShippingRecord)]);
            } catch (ClientException $e) {
                $this->addFlash(
                    'error',
                    \sprintf(
                        '%s : %s',
                        $this->container->get(TranslatorInterface::class)->trans('equipment_shipping_record.cost.create.error', [], 'equipment_shipping_record'),
                        $e->getMessage()
                    )
                );
            }
        }

        return [
            'costForm' => $costForm->createView(),
        ];
    }

    #[Route(path: '/{id}/costs/{costId}/delete', name: 'equipment_shipping_record_costs_delete', methods: 'GET|DELETE', requirements: ['id' => '\d+', 'costId' => '\d+'])]
    #[IsGranted('FEATURE_ESR_WRITE')]
    public function deleteCost(#[ApiValueResolverAttribute(parameters: ['resource' => self::RESOURCE_URL_ESRC, 'id' => 'costId'])] ApiData $equipmentShippingRecordCost, int $id): RedirectResponse
    {
        $client = $this->container->get(Client::class);
        try {
            $client->remove(self::RESOURCE_URL_ESRC, $equipmentShippingRecordCost->getIriId());

            $this->addFlash(
                'success',
                $this->container->get(TranslatorInterface::class)->trans('equipment_shipping_record.cost.delete.success', [], 'equipment_shipping_record')
            );
        } catch (ClientException $e) {
            $this->addFlash(
                'error',
                $this->container->get(TranslatorInterface::class)->trans('equipment_shipping_record.cost.delete.error', [], 'equipment_shipping_record')
            );
        }

        return $this->redirectToRoute('equipment_shipping_record_show', ['id' => $id]);
    }

    #[Route(path: '/{id}/lines/{lineId}/delete', name: 'equipment_shipping_record_lines_delete', methods: 'GET|DELETE')]
    #[IsGranted('FEATURE_ESR_WRITE')]
    public function deleteLine(int $id, int $lineId): RedirectResponse
    {
        $client = $this->container->get(Client::class);
        try {
            $client->remove(self::RESOURCE_URL_ESRL, $lineId);

            $this->addFlash(
                'success',
                $this->container->get(TranslatorInterface::class)->trans('equipment_shipping_record.line.delete.success', [], 'equipment_shipping_record')
            );
        } catch (ClientException $e) {
            $this->addFlash(
                'error',
                \sprintf(
                    '%s : %s',
                    $this->container->get(TranslatorInterface::class)->trans('equipment_shipping_record.line.delete.error', [], 'equipment_shipping_record'),
                    $e->getMessage()
                ));
        }

        return $this->redirectToRoute('equipment_shipping_record_show', ['id' => $id]);
    }

    #[Route(path: '/{id}/delete', name: 'equipment_shipping_record_delete', methods: 'GET|DELETE')]
    #[IsGranted('FEATURE_ESR_WRITE')]
    public function deleteEquipmentShippingRecord(#[ApiValueResolverAttribute(parameters: ['resource' => self::RESOURCE_URL])] ApiData $equipmentShippingRecord): RedirectResponse
    {
        $client = $this->container->get(Client::class);
        try {
            $client->remove(self::RESOURCE_URL, $equipmentShippingRecord->getIriId());

            $this->addFlash(
                'success',
                $this->container->get(TranslatorInterface::class)->trans('equipment_shipping_record.delete.success', [], 'equipment_shipping_record')
            );
        } catch (ClientException $e) {
            $this->addFlash(
                'error',
                \sprintf(
                    '%s : %s',
                    $this->container->get(TranslatorInterface::class)->trans('equipment_shipping_record.delete.error', [], 'equipment_shipping_record'),
                    '<br>'.$e->getMessage()
                ));

            return $this->redirectToRoute('equipment_shipping_record_show', ['id' => $equipmentShippingRecord['id']]);
        }

        return $this->redirectToRoute('equipment_shipping_record_home');
    }

    #[Route(path: '/{id}/status/{status}', name: 'equipment_shipping_record_status', requirements: ['id' => '\d+', 'status' => 'BOOKED|SHIPPED|CLOSED'], methods: 'GET')]
    #[IsGranted('FEATURE_ESR_WRITE')]
    public function status(#[ApiValueResolverAttribute(parameters: ['resource' => self::RESOURCE_URL])] ApiData $equipmentShippingRecord, $status)
    {
        try {
            $client = $this->container->get(Client::class);
            $client->put(\sprintf(self::RESOURCE_URL.'/%d/status', $equipmentShippingRecord->getIriId()), ['json' => ['status' => $status]]);
        } catch (ClientException $e) {
            $this->addFlash('error', $e->getMessage());
        }

        return $this->redirectToRoute('equipment_shipping_record_show', ['id' => $equipmentShippingRecord->getIriId()]);
    }

    #[Route(path: '/{id}/show_ajax', name: 'equipment_shipping_record_files_ajax', methods: 'GET')]
    #[Template('sales/equipment_shipping_records/partial/tabs/files_ajax.html.twig')]
    public function equipmentShippingRecordFilesAjax(#[ApiValueResolverAttribute(parameters: ['resource' => self::RESOURCE_URL])] ApiData $equipmentShippingRecord): array
    {
        return compact('equipmentShippingRecord');
    }

    #[Route(path: '/{equipmentShippingRecordId}/files/{id}', name: 'equipment_shipping_record_files_show', methods: 'GET')]
    public function showFile($equipmentShippingRecordId, $id)
    {
        return $this->container->get(FileStreamedResponseFactory::class)->create(\sprintf('%s/%s/files/%s', self::RESOURCE_URL, $equipmentShippingRecordId, $id));
    }

    #[Route(path: '/{equipmentShippingRecordId}/show/{id}/delete', name: 'delete_equipment_shipping_record_file', methods: ['GET'])]
    public function deleteFile(Request $request, $equipmentShippingRecordId, $id)
    {
        if (!$this->isCsrfTokenValid('delete_equipment_shipping_record_file', $request->query->get('_token'))) {
            $this->addFlash('error', 'Cannot delete file: please refresh your form.');

            return $this->redirectToRoute('equipment_shipping_record_files', ['id' => $equipmentShippingRecordId]);
        }
        $operation = \sprintf('files/%s', $id);
        try {
            $this->container->get(Client::class)->request(self::RESOURCE_URL, $equipmentShippingRecordId, $operation, Request::METHOD_DELETE);
        } catch (ClientException $e) {
            $this->addFlash(
                'error',
                $this->container->get(TranslatorInterface::class)->trans('first_article_qualification.messages.error.delete_file', [], 'first_article_qualification')
            );
        }

        return $this->redirectToRoute('equipment_shipping_record_show', ['id' => $equipmentShippingRecordId]);
    }

    #[Route(path: '/planning', name: 'equipment_shipping_record_planning', methods: ['GET', 'POST'])]
    #[Template('sales/equipment_shipping_records/planning.html.twig')]
    public function planning(Request $request)
    {
        $filterForm = $this->createForm(SmwFilterType::class, [], [
            'method' => 'GET',
        ]);
        $filterForm->handleRequest($request);

        $selectedFactory = $request->query->all('smw_filter')['location'] ?? null;

        if ($filterForm->isSubmitted() && $filterForm->isValid()) {
            $data = $filterForm->getData();
            $selectedFactory = $data['location'] ?? null;
        }

        $planningDailyExceptionForm = $this->createForm(PlanningDailyExceptionType::class, null, [
            'factory' => $selectedFactory,
            'method' => 'POST',
        ]);
        $planningDailyExceptionForm->handleRequest($request);

        if ($planningDailyExceptionForm->isSubmitted() && $planningDailyExceptionForm->isValid()) {
            try {
                $client = $this->container->get(Client::class);
                $client->save(PlanningDailyExceptionController::RESOURCE_URL, $planningDailyExceptionForm->getData());

                $this->addFlash('success', $this->container->get(TranslatorInterface::class)->trans('planning_daily_exception.edit.success', [], 'equipment_shipping_record')
                );

                return $this->redirectToRoute('equipment_shipping_record_planning', [
                    'smw_filter' => ['location' => $selectedFactory],
                ]);
            } catch (ClientException $e) {
                $this->container->get(ViolationMapper::class)->mapToForm($e, $planningDailyExceptionForm);
                $this->addFlash('error', $this->container->get(TranslatorInterface::class)->trans('planning_daily_exception.edit.error', [], 'equipment_shipping_record'));
            }
        }

        $planningDailyLimit = null;
        $planningByDate = [];

        if ($selectedFactory) {
            $client = $this->container->get(Client::class);
            $after = (new \DateTime('-7 days'))->format('Y-m-d');

            $equipmentShippingRecordLines = $client->findBy(self::RESOURCE_URL_ESRL, [
                'exists[equipmentRecord.dateShipped]' => false,
                'equipmentRecord.manufacturerLocation' => $selectedFactory,
                'estimatedPickUpDate' => ['after' => $after],
                'order[estimatedPickUpDate]' => 'asc',
                'order[equipmentShippingRecord.id]' => 'asc',
            ]);

            $planningDailyLimit = $client->findBy(PlanningDailyLimitController::RESOURCE_URL, ['factory' => $selectedFactory])[0] ?? null;

            $planningDailyExceptions = $client->findBy(PlanningDailyExceptionController::RESOURCE_URL, ['factory' => $selectedFactory, 'date[after]' => $after]);

            foreach ($equipmentShippingRecordLines as $line) {
                $date = mb_substr($line['estimatedPickUpDate'], 0, 10);

                $planningByDate[$date]['lines'][] = $line;
                $planningByDate[$date]['exception'] ??= null;
            }

            foreach ($planningDailyExceptions as $exception) {
                $date = mb_substr($exception['date'], 0, 10);

                $planningByDate[$date]['exception'] = $exception;
                $planningByDate[$date]['lines'] ??= [];
            }

            ksort($planningByDate);
        }

        return [
            'planningDailyLimit' => $planningDailyLimit,
            'form' => $filterForm->createView(),
            'planningDailyExceptionForm' => $planningDailyExceptionForm->createView(),
            'planningByDate' => $planningByDate,
            'selectedFactory' => $selectedFactory,
        ];
    }

    #[Route(path: '/dashboard', name: 'equipment_shipping_record_dashboard', methods: ['GET', 'POST'])]
    #[Template('sales/equipment_shipping_records/dashboard.html.twig')]
    public function dashboard(Request $request)
    {
        $form = $this->createForm(SmwFilterType::class, [], ['method' => 'GET']);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $client = $this->container->get(Client::class);
                $data = $form->getData();
                $payload = [
                    'equipmentRecord.manufacturerLocation' => $data['location'],
                    'exists[equipmentRecord.dateShipped]' => false,
                    'normalization_groups' => ['odp:view', 'expose_legacy'],
                ];
                $equipmentShippingRecordLines = $client->findBy(self::RESOURCE_URL_ESRL, $payload);
                foreach ($equipmentShippingRecordLines as $line) {
                    $line['yellowTag3month'] = false;
                    $line['yellowTag1month'] = false;
                    $line['yellowTagCrab'] = false;
                    if (null !== $line['equipmentRecord']['yellowTagDate']) {
                        if (null !== $line['equipmentRecord']['greenTagDate']) {
                            $today = new \DateTime();
                            $difference = $today->diff(new \DateTime($line['equipmentRecord']['greenTagDate']));
                            if ($difference->m >= 3 || $difference->y > 1) {
                                $line['yellowTag3month'] = true;
                                continue;
                            }
                            if ($difference->m >= 1) {
                                $line['yellowTag1month'] = true;
                                continue;
                            }
                        }
                        $line['yellowTagCrab'] = true;
                    }
                }

                return [
                    'equipmentShippingRecordLines' => $equipmentShippingRecordLines,
                    'form' => $form->createView(),
                ];
            } catch (ClientException $e) {
                $this->container->get(ViolationMapper::class)->mapToForm($e, $form);
            }
        }

        return [
            'form' => $form->createView(),
        ];
    }

    private function submitEquipmentShippingRecordForm(Client $client, $esrForm): ?RedirectResponse
    {
        if (!$esrForm->isSubmitted() || !$esrForm->isValid()) {
            return null;
        }

        try {
            $data = $esrForm->getData();
            $esr = $client->save(self::RESOURCE_URL, $data);
            $this->addFlash('success', $this->container->get(TranslatorInterface::class)->trans('equipment_shipping_record.add.success', [], 'equipment_shipping_record'));

            return $this->redirectToRoute('equipment_shipping_record_show', ['id' => Iri::id($esr)]);
        } catch (ClientException $e) {
            $this->addFlash('error', \sprintf('%s : <br> %s', $this->container->get(TranslatorInterface::class)->trans('equipment_shipping_record.add.error', [], 'equipment_shipping_record'), $e->getMessage()));

            return null;
        }
    }
}
