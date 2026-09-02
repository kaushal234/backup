<?php

declare(strict_types=1);

namespace AppBundle\Controller\Support;

use ApiBundle\Client;
use ApiBundle\Http\CsvStreamedResponseFactory;
use ApiBundle\Http\FileStreamedResponseFactory;
use ApiBundle\Model\User;
use AppBundle\Chart\ChartBuilderFactory;
use AppBundle\Filters\Type\Support\EquipmentRecordFilterType;
use AppBundle\Filters\Type\Support\PreDeliveryInspectionReportFilter;
use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Encoder\CsvEncoder;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route(path: '/support/on_time_delivery_planning', defaults: ['alvest_module' => 'ODP', 'moduleDomain' => 'on_time_delivery_planning'])]
class OnTimeDeliveryPlanningController extends AbstractController
{
    public const RESOURCE_URL = 'equipment_records';

    // Shared by the CSV and XLS downloads so the two exports can never drift
    // apart again - see TTS #9286, where the sales price field was added to
    // the XLS column list but never to CSV, because each export used to
    // define its own list independently.
    private const array DOWNLOAD_COLUMNS = [
        'id', 'serialNumber', 'model', 'type', 'endUser', 'buyer',
        'airport.code', 'deliveredCountry', 'salesOrganisation', 'customerAssetNumber',
        'order.customerPurchaseOrders', 'firstGreenTagDate', 'estimatedGreenTagDate',
        'greenTagDate', 'yellowTagDate', 'lastEquipmentShippingRecord.estimatedPickUpDate',
        'dateShipped', 'workOrder', 'emissionRating',
        'orderFactory.factoryPromisedDeliveryDate', 'orderFactory.requestedDeliveryDate',
        'orderFactory.orderLine.deliveredEarly', 'orderFactory.orderLine.factory',
        'orderFactory.orderLine.legacyId', 'orderFactory.orderLine.purchaseOrderAcceptedDate',
        'orderFactory.orderLine.inspection', 'orderFactory.orderLine.incoterm.code',
        'orderFactory.orderLine.incotermLocation', 'orderTransaction.invoice',
        'orderFactory.commissioning', 'length', 'width', 'height', 'weight', 'light',
        'orderFactory.orderLine.paymentTerms', 'negotiatedTransferPrice', 'currency',
        'orderFactory.orderLine.deliveryPenalties', 'orderFactory.orderLine.deliveryPenaltiesConditions',
        'unitGrossSellingPrice',
    ];

    public static function getSubscribedServices(): array
    {
        return array_merge(parent::getSubscribedServices(), [
            Client::class,
            TranslatorInterface::class,
            FileStreamedResponseFactory::class,
            CsvStreamedResponseFactory::class,
            ChartBuilderFactory::class,
        ]);
    }

    #[Route(path: '', name: 'on_time_delivery_planning_home', methods: ['GET'])]
    #[Template('support/on_time_delivery_planning/home.html.twig')]
    public function home()
    {
        return [
            'reportSSObyStatus' => $this->container->get(Client::class)->get('reports/resource=/equipment_records;x=manufacturerLocation.name;y=salesOrganisation.name'),
            'reportFactoryDashboard' => $this->container->get(Client::class)->get('reports/resource=/equipment_records;x=factory;y=dashboard'),
            'reportSsoDashboard' => $this->container->get(Client::class)->get('reports/resource=/equipment_records;x=salesOrganisation;y=dashboard'),
        ];
    }

    #[Route(path: '/show', name: 'on_time_delivery_planning_show', methods: ['GET', 'POST'], defaults: ['label' => 'menu.search', 'domain' => 'messages'])]
    #[Template('support/on_time_delivery_planning/show.html.twig')]
    public function index(Request $request)
    {
        $client = $this->container->get(Client::class);
        $formFactoryInterface = $this->container->get('form.factory');
        $filtered = false;
        $equipmentRecords = [];
        $parameters = [];

        $formFilter = $formFactoryInterface->createNamed('', EquipmentRecordFilterType::class, $parameters);
        foreach (['salesOrganisation', 'manufacturerLocation'] as $parameter) {
            if ('ALL' === $request->query->get($parameter)) {
                $request->query->remove($parameter);
            }
        }
        /** @var User $user */
        $user = $this->getUser();
        $isAsm = null !== $user && \in_array('ROLE_ASM', $user->getAcls(), true);
        if ($isAsm && empty($request->query->all())) {
            $request->query->add([
                'asm' => $user->getIriId(),
            ]);
        }

        $formFilter->handleRequest($request);
        if ($formFilter->isSubmitted() && $formFilter->isValid()) {
            $filtered = true;
            $data = $formFilter->getData();
            $parameters = [
                'normalization_groups' => ['odp:view', 'expose_legacy', 'order_line', 'order_transaction', 'order_factory'],
                'itemsPerPage' => 2000,
                'order' => ['id' => 'DESC'],
            ];
            unset($data['isPreAssembly']);
            if (false === $data['light']) {
                unset($data['light']);
            }

            if (true === $data['excludeLight']) {
                $data['light'] = false;
            }
            unset($data['excludeLight']);

            if (false === $data['orderFactory.orderLine.inspection']) {
                unset($data['orderFactory.orderLine.inspection']);
            }
            if ($request->query->has('odpFilter')) {
                $parameters['odpFilter'] = $request->query->get('odpFilter');
            } else {
                $parameters['notShipped'] = !$data['isShipped'];
            }
            unset($data['isShipped']);

            $parameters = array_merge($parameters, $data);
            if (null !== ($parameters['notShipped'] ?? null) && !$parameters['notShipped']) {
                unset($parameters['notShipped']);
            }

            if ($formFilter->getClickedButton() && 'downloadCsv' === $formFilter->getClickedButton()->getName()) {
                unset($parameters['itemsPerPage']);
                $parameters['context'] = [CsvEncoder::DELIMITER_KEY => ';'];
                $parameters['columns'] = implode(',', self::DOWNLOAD_COLUMNS);

                return $this->container->get(CsvStreamedResponseFactory::class)->create(self::RESOURCE_URL, $parameters, 'odp.csv');
            }
            if ($formFilter->getClickedButton() && 'downloadXls' === $formFilter->getClickedButton()->getName()) {
                $parameters['itemsPerPage'] = 40000;
                $parameters['columns'] = implode(',', self::DOWNLOAD_COLUMNS);

                return $this->container->get(FileStreamedResponseFactory::class)->create(
                    self::RESOURCE_URL,
                    ['query' => $parameters, 'headers' => ['Accept' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet']],
                    'odp.xlsx'
                );
            }
            $equipmentRecords = $client->findBy(self::RESOURCE_URL, $parameters);
        }

        return [
            'equipmentRecords' => $equipmentRecords,
            'formFilter' => $formFilter->createView(),
            'filtered' => $filtered,
        ];
    }

    #[Route(path: '/edit', name: 'on_time_delivery_planning_edit', methods: ['GET', 'POST'], defaults: ['label' => 'menu.edit', 'domain' => 'messages'])]
    #[Template('support/on_time_delivery_planning/edit.html.twig')]
    public function edit(Request $request)
    {
        $client = $this->container->get(Client::class);
        $formFactoryInterface = $this->container->get('form.factory');
        $formFilter = $formFactoryInterface->createNamed('', EquipmentRecordFilterType::class);
        $formFilter->submit($request->query->all());
        $parameters = [
            'normalization_groups' => ['odp:view', 'expose_legacy', 'order_line', 'order_transaction', 'order_factory'],
            'order' => ['id' => 'DESC'],
        ];

        $parameters = array_merge($parameters, $formFilter->getData(), ['notShipped' => true]);
        unset($parameters['isPreAssembly']);
        if (false === $parameters['light']) {
            unset($parameters['light']);
        }

        if (true === $parameters['excludeLight']) {
            $parameters['light'] = false;
        }
        unset($parameters['excludeLight']);

        if ($request->query->has('odpFilter')) {
            $parameters['odpFilter'] = $request->query->get('odpFilter');
        } else {
            $parameters['notShipped'] = !$formFilter->getData()['isShipped'];
        }
        if (false === $parameters['orderFactory.orderLine.inspection']) {
            unset($parameters['orderFactory.orderLine.inspection']);
        }
        unset($parameters['isShipped']);
        $equipmentRecords = $client->findBy(self::RESOURCE_URL, $parameters, [], ['raw_results' => true]);

        $equipmentRecordsArray = [];
        foreach ($equipmentRecords['hydra:member'] as $equipmentRecord) {
            $equipmentRecordsArray[$equipmentRecord['@id']] = [
                ...$equipmentRecord,
                'userCanAdminPdi' => $this->isGranted('ODP_PDI_VOTER', $equipmentRecord['@id']),
            ];
        }

        return [
            'equipmentRecords' => $equipmentRecords,
            'initialState' => [
                'equipmentRecord' => [
                    'equipmentRecords' => $equipmentRecordsArray,
                ],
                'user' => [
                    'currentUser' => [
                        'isFromSupportTeam' => $this->isGranted('FEATURE_ODP_EDIT_SUPPORT'),
                        'isFromQualityTeam' => $this->isGranted('FEATURE_ODP_EDIT_QUALITY'),
                        'isOdpAdmin' => $this->isGranted('FEATURE_ODP_EDIT_ADMIN'),
                        'isMooEr' => $this->isGranted('MOO_ER'),
                    ],
                ],
            ],
            'props' => [
                'module' => 'ODP',
            ],
        ];
    }

    #[Route(path: '/reports/pdi/{period}', name: 'pre_delivery_inspection_report', methods: ['GET'], requirements: ['period' => 'month'])]
    #[Template('support/on_time_delivery_planning/reports/pre_delivery_inspection.html.twig')]
    public function preDeliveryInspectionReports(Request $request, string $period): array
    {
        $formFactoryInterface = $this->container->get('form.factory');
        $client = $this->container->get(Client::class);
        /** @var ChartBuilderFactory $chartBuilder */
        $chartBuilder = $this->container->get(ChartBuilderFactory::class);
        $form = $formFactoryInterface->createNamed('',
            PreDeliveryInspectionReportFilter::class,
            [],
            ['action' => $this->generateUrl('pre_delivery_inspection_report', ['period' => $period])]
        );
        $form->handleRequest($request);
        $parameters = $form->getData();
        $pastPreDeliveryInspectionReport = $client->get(\sprintf('reports/resource=/equipment_records;x=%s;y=inspection_rate_per_sso?options[totalsAsAverages]=1', $period), ['query' => $parameters]);

        if (empty($pastPreDeliveryInspectionReport['rows'])) {
            return [
                'form' => $form->createView(),
            ];
        }

        $chart = $chartBuilder
            ->getLineChartBuilder()
            ->setTitle('Pre-delivery Inspections Rate')
            ->addYAxis('Rate (%)', ['min' => 0])
        ;
        foreach ($pastPreDeliveryInspectionReport['rows'] as $row => $ssos) {
            foreach ($ssos as $sso => $values) {
                $chart->addPlot(
                    $sso,
                    $row,
                    (int) $values['value'],
                    [],
                    ['extraData' => $values['extraData'] ?? []]
                );
            }
        }

        foreach ($pastPreDeliveryInspectionReport['xTotals'] as $row => $totalQuantity) {
            $chart->addPlot(
                'AVERAGE',
                $row,
                (int) $totalQuantity,
                ['options' => ['visible' => false]]
            );
        }

        $nextThirtyDaysInspectionsReport = $this->container->get(Client::class)->get(
            'reports/resource=/equipment_records;x=manufacturerLocation.name;y=salesOrganisation.name?options[preDeliveryInspections]=+30'
        );

        return [
            'period' => $period,
            'form' => $form->createView(),
            'chart' => $chart->buildConfig(),
            'nextThirtyDaysInspectionsReport' => $nextThirtyDaysInspectionsReport,
        ];
    }

    #[Route(path: '/reports/stability', name: 'stability_report', methods: ['GET'])]
    #[Template('support/on_time_delivery_planning/reports/stability.html.twig')]
    public function stabilityReports(Request $request): array
    {
        $columnsOrder = [];
        for ($i = 4; $i > 0; --$i) {
            $columnsOrder[] = 'W-'.$i;
        }
        $columnsOrder[] = 'LATE';
        for ($i = 0; $i < 27; ++$i) {
            $columnsOrder[] = 'W+'.$i;
        }

        $mondayThisWeek = new \DateTime('monday this week');
        $columnFilters = [];
        foreach ($columnsOrder as $col) {
            if ('LATE' === $col) {
                $columnFilters[$col] = ['odpFilter' => 'late'];
            } elseif (str_starts_with($col, 'W-')) {
                $n = (int) mb_substr($col, 2);
                $after = (clone $mondayThisWeek)->modify("-{$n} weeks");
                $before = (clone $mondayThisWeek)->modify('-'.($n - 1).' weeks');
                $columnFilters[$col] = [
                    'greenTagDate[after]' => $after->format('Y-m-d'),
                    'greenTagDate[before]' => $before->format('Y-m-d'),
                ];
            } elseif (str_starts_with($col, 'W+')) {
                $n = (int) mb_substr($col, 2);
                $after = (clone $mondayThisWeek)->modify("+{$n} weeks");
                $before = (clone $mondayThisWeek)->modify('+'.($n + 1).' weeks');
                $columnFilters[$col] = [
                    'estimatedGreenTagDate[after]' => $after->format('Y-m-d'),
                    'estimatedGreenTagDate[before]' => $before->format('Y-m-d'),
                ];
            }
        }

        $client = $this->container->get(Client::class);
        $location = $request->query->all()['manufacturerLocation'][0];
        $lateEquipmentRecord = $client->findBy('equipment_records',
            [
                'normalization_groups' => ['odp:view'],
                'late' => true,
                'manufacturerLocation' => $location,
            ]
        );

        $excludedBuyers = ['**STOCK**', '**AVAILABLE FOR SALE**', '**PROTO**'];
        $lateEquipmentRecord = array_filter($lateEquipmentRecord->all(), static function ($record) use ($excludedBuyers) {
            return !isset($record['buyer']['name']) || !\in_array($record['buyer']['name'], $excludedBuyers, true);
        });
        $stabilityReport = $client->get(\sprintf('reports/resource=/equipment_records;x=stability;y=%s', $location), ['cache' => true]);
        $overviewReport = $client->get(\sprintf('reports/resource=/equipment_records;x=odp_overview;y=%s', $location));

        $topLateSols = $client->findBy('top_late_sols', ['manufacturerLocation' => $location]);

        return [
            'stabilityReport' => $stabilityReport,
            'overviewReport' => $overviewReport,
            'columnsOrder' => $columnsOrder,
            'columnFilters' => $columnFilters,
            'location' => $location,
            'lateEquipmentRecord' => $lateEquipmentRecord,
            'topLateSols' => $topLateSols,
        ];
    }
}
