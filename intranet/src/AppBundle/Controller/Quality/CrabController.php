<?php

declare(strict_types=1);

namespace AppBundle\Controller\Quality;

use ApiBundle\Client;
use ApiBundle\Form\ViolationMapper;
use ApiBundle\Http\FileStreamedResponseFactory;
use ApiBundle\Model\ApiData;
use AppBundle\Chart\ChartBuilder;
use AppBundle\Chart\ChartBuilderFactory;
use AppBundle\Configuration\ApiValueResolverAttribute;
use AppBundle\DataTable\Query\ApiProxyQuery;
use AppBundle\DataTable\Type\Quality\Crab\CrabDataTableType;
use AppBundle\Filters\Type\Quality\Crab\CrabChartProductFilterType;
use AppBundle\Form\Type\Quality\Crab\CrabAddType;
use AppBundle\Form\Type\Quality\Crab\CrabDuplicateType;
use AppBundle\Form\Type\Quality\Crab\CrabEditType;
use AppBundle\Form\Type\Quality\Crab\CrabFixType;
use AppBundle\Form\Type\Quality\Crab\CrabInspectType;
use AppBundle\Form\Type\Quality\Crab\DerogationLinkType;
use AppBundle\Form\Type\SimpleFileType;
use AppBundle\Manager\FileManager;
use Kreyu\Bundle\DataTableBundle\DataTableFactoryAwareTrait;
use Kreyu\Bundle\DataTableBundle\Filter\FilterData;
use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\ExpressionLanguage\Expression;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpClient\Exception\ClientException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Mime\Part\DataPart;
use Symfony\Component\Mime\Part\Multipart\FormDataPart;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route(path: '/quality/crabs', defaults: ['alvest_module' => 'CRAB', 'moduleDomain' => 'crab'])]
class CrabController extends AbstractController
{
    use DataTableFactoryAwareTrait;

    public const string CRAB_URL = 'quality/crabs';
    public const string CRAB_FILE_URL = 'crab_files';

    public static function getSubscribedServices(): array
    {
        return array_merge(parent::getSubscribedServices(), [FileStreamedResponseFactory::class, Client::class, TranslatorInterface::class, FormFactoryInterface::class, ChartBuilderFactory::class, ViolationMapper::class, FileManager::class]);
    }

    #[Route(path: '', name: 'crab_home', methods: ['GET|POST'])]
    #[Template('quality/crab/home.html.twig')]
    public function home(Request $request)
    {
        $datatable = $this->createDataTable(CrabDataTableType::class, CrabDataTableType::RESOURCE);
        $datatable->handleRequest($request);

        if ($datatable->isExporting() && $datatable->getQuery() instanceof ApiProxyQuery) {
            return $datatable->getQuery()->export();
        }

        // createView() initializes the data table, which loads the persisted filtration from cache.
        // This must happen before reading getFiltrationData(), otherwise the charts would ignore
        // filters applied on a previous request (e.g. restored from persistence on a fresh load).
        $view = $datatable->createView();

        $filtration = $datatable->getFiltrationData();
        $factory = $this->firstActiveFilterValue($filtration?->getFilterData('factory'));
        $category = $this->firstActiveFilterValue($filtration?->getFilterData('category'));

        $chartBuilderType = null;
        $chartBuilderDepartment = null;
        $chartBuilderCode = null;
        $chartBuilderFamily = null;

        if (null !== $factory || null !== $category) {
            $payload = [
                'options' => [
                    'factory' => $factory,
                    'category' => $category,
                ],
            ];
            $factoryName = $this->resolveFactoryName($factory);

            $chartBuilderType = $this->buildKpiChart('byType', $payload, $factoryName, $category);
            $chartBuilderDepartment = $this->buildKpiChart('byDepartment', $payload, $factoryName, $category);
            $chartBuilderCode = $this->buildKpiChart('byCode', $payload, $factoryName, $category);
            $chartBuilderFamily = $this->buildKpiChart('byFamily', $payload, $factoryName, $category, true);
        }

        $parameters = [
            'crabDatatable' => $view,
            'chartType' => $chartBuilderType?->buildConfig(false),
            'chartDepartment' => $chartBuilderDepartment?->buildConfig(false),
            'chartCode' => $chartBuilderCode?->buildConfig(false),
            'chartFamily' => $chartBuilderFamily?->buildConfig(false),
        ];

        // On a Turbo Frame request (filter/sort/paginate inside the data table frame), render the
        // data table frame together with the charts container. Turbo swaps the frame, and the
        // inline "turbo:before-fetch-response" listener in home.html.twig swaps the #anchor charts.
        if ($datatable->isRequestFromTurboFrame()) {
            return $this->render('quality/crab/partial/_turbo_response.html.twig', $parameters);
        }

        return $parameters;
    }

    #[Route(path: '/dashboard', name: 'crab_dashboard', methods: ['GET', 'POST'], defaults: ['label' => 'crab.title.dashboard'])]
    #[Template('quality/crab/dashboard.html.twig')]
    public function dashboard(Request $request)
    {
        $client = $this->container->get(Client::class);
        $report = $client->get('reports/resource=/quality/crabs;x=equipmentRecord.manufacturerLocation.name;y=category');

        $yKeys = array_keys($report['yTotals'] ?? []);

        foreach ($report['rows'] as &$row) {
            $newRow = [];
            foreach ($yKeys as $yKey) {
                $newRow[$yKey] = $row[$yKey];
            }
            $row = $newRow;
        }

        foreach (['category', 'factory'] as $parameter) {
            if ($request->query->has($parameter) && 'ALL' === $request->query->get($parameter)) {
                $request->query->remove($parameter);
            }
        }

        return ['byCategoryByFactory' => $report];
    }

    #[Route(path: '/reports', name: 'crab_report', methods: ['GET', 'POST'], defaults: ['label' => 'sidebar.common.reports', 'domain' => 'sidebar'])]
    #[Template('quality/crab/report.html.twig')]
    public function report(Request $request)
    {
        $formProductFilter = $this->container->get(FormFactoryInterface::class)->createNamed('', CrabChartProductFilterType::class, [],
            [
                'action' => $this->generateUrl('crab_report'),
                'method' => Request::METHOD_GET,
            ]
        );

        $chartBuilderProductFilterReportLastYear = null;
        $chartBuilderFamilyFilterReportByCode = null;
        $chartBuilderFamilyFilterReportLastYear = null;
        $chartBuilderProductFilterReportByCode = null;
        $chartBuilderAvgPerCategoryByPeriod = null;

        $formProductFilter->handleRequest($request);
        if ($formProductFilter->isSubmitted() && $formProductFilter->isValid()) {
            $data = $formProductFilter->getData();
            $dataFactoryArray = $formProductFilter->get('factory')->getConfig()->getOption('choices');
            $dataProductArray = $formProductFilter->get('model')->getConfig()->getOption('choices');
            $dataFamilyArray = $formProductFilter->get('family')->getConfig()->getOption('choices');

            if (null !== $data['equipmentRecord.manufacturerLocation'] && null !== $data['createdBefore'] && null !== $data['createdAfter']) {
                $payload = [
                    'options' => [
                        'from' => $data['createdAfter'],
                        'to' => $data['createdBefore'],
                        'factory' => $data['equipmentRecord.manufacturerLocation'],
                        'product' => $data['equipmentRecord.product'],
                    ],
                ];
                $reportByCodeByProduct = $this->container->get(Client::class)->get('reports/resource=/quality/crabs;x=reportByPeriodByCodeByProduct;y=', ['query' => $payload]);
                $productNames = [];
                foreach ($data['equipmentRecord.product'] as $iri) {
                    if (\in_array($iri, $dataProductArray, true)) {
                        $productNames[] = array_search($iri, $dataProductArray, true);
                    }
                }
                $chartBuilderProductFilterReportByCode = $this->container->get(ChartBuilderFactory::class)
                    ->getColumnChartBuilder()
                    ->addYAxis($this->container
                        ->get(TranslatorInterface::class)
                        ->trans('crab.report.crab_title_count', [], 'crab')
                    )
                    ->addPlotOptions(['stacking' => 'normal', 'states' => ['inactive' => ['enabled' => false]]])
                    ->addChartOptions(['zoomType' => 'x'])
                    ->setTitle($this->container
                        ->get(TranslatorInterface::class)
                        ->trans('crab.report.crab_count_by_code_product',
                            [
                                '%factory%' => array_search($data['equipmentRecord.manufacturerLocation'], $dataFactoryArray, true),
                                '%product%' => [] !== $productNames ? implode(', ', $productNames) : 'all products',
                                '%from%' => mb_substr($data['createdAfter'], 0, 10),
                                '%to%' => mb_substr($data['createdBefore'], 0, 10),
                            ],
                            'crab'))
                ;

                foreach ($reportByCodeByProduct['rows'] as $key => $dataCodeProduct) {
                    foreach ($dataCodeProduct as $category => $value) {
                        $chartBuilderProductFilterReportByCode
                            ->addPlot(
                                $category,
                                $key,
                                $value['value'],
                            );
                    }
                }
            }

            if (null !== $data['equipmentRecord.manufacturerLocation'] && null !== $data['createdBefore'] && null !== $data['createdAfter']) {
                $payload = [
                    'options' => [
                        'from' => $data['createdAfter'],
                        'to' => $data['createdBefore'],
                        'factory' => $data['equipmentRecord.manufacturerLocation'],
                        'family' => $data['equipmentRecord.product.family'],
                    ],
                ];
                $reportByCodeByFamily = $this->container->get(Client::class)->get('reports/resource=/quality/crabs;x=reportByPeriodByCodeByFamily;y=', ['query' => $payload]);
                $familiesNames = [];
                foreach ($data['equipmentRecord.product.family'] as $iri) {
                    if (\in_array($iri, $dataFamilyArray, true)) {
                        $familiesNames[] = array_search($iri, $dataFamilyArray, true);
                    }
                }
                $chartBuilderFamilyFilterReportByCode = $this->container->get(ChartBuilderFactory::class)
                    ->getColumnChartBuilder()
                    ->addYAxis($this->container
                        ->get(TranslatorInterface::class)
                        ->trans('crab.report.crab_title_count', [], 'crab')
                    )
                    ->addPlotOptions(['stacking' => 'normal', 'states' => ['inactive' => ['enabled' => false]]])
                    ->addChartOptions(['zoomType' => 'x'])
                    ->setTitle($this->container
                        ->get(TranslatorInterface::class)
                        ->trans('crab.report.crab_count_by_code_family',
                            [
                                '%factory%' => array_search($data['equipmentRecord.manufacturerLocation'], $dataFactoryArray, true),
                                '%family%' => [] !== $familiesNames ? implode(', ', $familiesNames) : 'all families',
                                '%from%' => mb_substr($data['createdAfter'], 0, 10),
                                '%to%' => mb_substr($data['createdBefore'], 0, 10),
                            ],
                            'crab'));

                foreach ($reportByCodeByFamily['rows'] as $key => $dataCodeFamily) {
                    foreach ($dataCodeFamily as $category => $value) {
                        $chartBuilderFamilyFilterReportByCode
                            ->addPlot(
                                $category,
                                $key,
                                $value['value'],
                            );
                    }
                }
            }

            if (null !== $data['equipmentRecord.manufacturerLocation'] && !empty($data['equipmentRecord.product'])) {
                $payload = [
                    'options' => [
                        'factory' => $data['equipmentRecord.manufacturerLocation'],
                        'product' => $data['equipmentRecord.product'],
                    ],
                ];
                $reportLastYear = $this->container->get(Client::class)->get('/reports/resource=/quality/crabs;x=reportLastYearByProduct;y=', ['query' => $payload]);

                $productNames = [];
                foreach ($data['equipmentRecord.product'] as $iri) {
                    if (\in_array($iri, $dataProductArray, true)) {
                        $productNames[] = array_search($iri, $dataProductArray, true);
                    }
                }

                $chartBuilderProductFilterReportLastYear = $this->container->get(ChartBuilderFactory::class)
                    ->getColumnChartBuilder()
                    ->addYAxis($this->container
                        ->get(TranslatorInterface::class)
                        ->trans('crab.report.crab_title_average', [], 'crab')
                    )
                    ->addPlotOptions(['stacking' => 'normal', 'states' => ['inactive' => ['enabled' => false]]])
                    ->addChartOptions(['zoomType' => 'x'])
                    ->setTitle($this->container
                        ->get(TranslatorInterface::class)
                        ->trans('crab.report.average_crab_count_product',
                            [
                                '%product%' => implode(', ', $productNames),
                            ],
                            'crab'));

                foreach ($reportLastYear['rows'] as $key => $dataYearProduct) {
                    foreach ($dataYearProduct as $category => $value) {
                        $month = (new \DateTime($key))->format('Y-m');
                        $chartBuilderProductFilterReportLastYear
                            ->addPlot(
                                $category,
                                $month,
                                $value['value'],
                            );
                    }
                }
            }

            if (null !== $data['equipmentRecord.manufacturerLocation'] && !empty($data['equipmentRecord.product.family'])) {
                $payload = [
                    'options' => [
                        'factory' => $data['equipmentRecord.manufacturerLocation'],
                        'family' => $data['equipmentRecord.product.family'],
                    ],
                ];
                $reportLastYear = $this->container->get(Client::class)->get('/reports/resource=/quality/crabs;x=reportLastYearByFamily;y=', ['query' => $payload]);

                $familiesNames = [];
                foreach ($data['equipmentRecord.product.family'] as $iri) {
                    if (\in_array($iri, $dataFamilyArray, true)) {
                        $familiesNames[] = array_search($iri, $dataFamilyArray, true);
                    }
                }
                $chartBuilderFamilyFilterReportLastYear = $this->container->get(ChartBuilderFactory::class)
                    ->getColumnChartBuilder()
                    ->setTitle($this->container
                        ->get(TranslatorInterface::class)
                        ->trans('crab.report.average_crab_count_type',
                            [
                                '%type%' => implode(', ', $familiesNames),
                            ],
                            'crab'))
                    ->addYAxis($this->container
                        ->get(TranslatorInterface::class)
                        ->trans('crab.report.crab_title_average', [], 'crab')
                    )
                    ->addPlotOptions(['stacking' => 'normal', 'states' => ['inactive' => ['enabled' => false]]])
                    ->addChartOptions(['zoomType' => 'x']);

                foreach ($reportLastYear['rows'] as $key => $dataYearFamily) {
                    foreach ($dataYearFamily as $category => $value) {
                        $month = (new \DateTime($key))->format('Y-m');
                        $chartBuilderFamilyFilterReportLastYear
                            ->addPlot(
                                $category,
                                $month,
                                $value['value'],
                            );
                    }
                }
            }

            // Average CRAB count per category by period — factory + date range only
            if (null !== $data['equipmentRecord.manufacturerLocation'] && null !== $data['createdBefore'] && null !== $data['createdAfter']) {
                $payload = [
                    'options' => [
                        'from' => $data['createdAfter'],
                        'to' => $data['createdBefore'],
                        'factory' => $data['equipmentRecord.manufacturerLocation'],
                    ],
                ];
                $reportAvgPerCategory = $this->container->get(Client::class)->get('reports/resource=/quality/crabs;x=reportAvgPerCategoryByPeriod;y=', ['query' => $payload]);

                $chartBuilderAvgPerCategoryByPeriod = $this->container->get(ChartBuilderFactory::class)
                    ->getColumnChartBuilder()
                    ->addYAxis($this->container
                        ->get(TranslatorInterface::class)
                        ->trans('crab.report.crab_title_average', [], 'crab')
                    )
                    ->addPlotOptions(['stacking' => 'normal', 'states' => ['inactive' => ['enabled' => false]]])
                    ->addChartOptions(['zoomType' => 'x'])
                    ->setTitle($this->container
                        ->get(TranslatorInterface::class)
                        ->trans('crab.report.crab_average_per_category_by_period',
                            [
                                '%factory%' => array_search($data['equipmentRecord.manufacturerLocation'], $dataFactoryArray, true),
                                '%from%' => mb_substr($data['createdAfter'], 0, 10),
                                '%to%' => mb_substr($data['createdBefore'], 0, 10),
                            ],
                            'crab'))
                ;

                foreach ($reportAvgPerCategory['rows'] as $key => $dataAvgCategory) {
                    foreach ($dataAvgCategory as $category => $value) {
                        $month = (new \DateTime($key))->format('Y-m');
                        $chartBuilderAvgPerCategoryByPeriod
                            ->addPlot(
                                $category,
                                $month,
                                $value['value'],
                            );
                    }
                }
            }
        }

        return [
            'formProductFilter' => $formProductFilter->createView(),
            'chartProductReportByCode' => null !== $chartBuilderProductFilterReportByCode ? $chartBuilderProductFilterReportByCode->buildConfig(false) : null,
            'chartFamilyReportByCode' => null !== $chartBuilderFamilyFilterReportByCode ? $chartBuilderFamilyFilterReportByCode->buildConfig(false) : null,
            'chartProductReportLastYear' => null !== $chartBuilderProductFilterReportLastYear ? $chartBuilderProductFilterReportLastYear->buildConfig() : null,
            'chartFamilyReportLastYear' => null !== $chartBuilderFamilyFilterReportLastYear ? $chartBuilderFamilyFilterReportLastYear->buildConfig() : null,
            'chartAvgPerCategoryByPeriod' => null !== $chartBuilderAvgPerCategoryByPeriod ? $chartBuilderAvgPerCategoryByPeriod->buildConfig() : null,
        ];
    }

    #[Route(path: '/{id}/show', requirements: ['id' => '\d+'], name: 'crab_show', methods: 'GET|POST')]
    #[Template('quality/crab/show.html.twig')]
    public function show(Request $request, #[ApiValueResolverAttribute(parameters: ['resource' => self::CRAB_URL])] ApiData $crab)
    {
        $formFiles = $this->container->get('form.factory')->createNamed('form_files', SimpleFileType::class, [], ['display_public' => true]);

        $formFiles->handleRequest($request);
        if ($formFiles->isSubmitted() && $formFiles->isValid()) {
            try {
                /** @var UploadedFile $file */
                $file = $formFiles->get('file')->getData();

                if ($file instanceof UploadedFile) {
                    $this->container->get(FileManager::class)->uploadFile(
                        $crab,
                        $file,
                        self::CRAB_URL,
                        $formFiles->get('description')->getData(),
                        'files',
                        true,
                        $formFiles->get('public')->getData(),
                    );
                }

                $this->addFlash('success', $this->container->get(TranslatorInterface::class)->trans('files.upload_success', [], 'messages'));

                return $this->redirectToRoute('crab_show', ['id' => $crab->getIriId(), 'tab' => 'files']);
            } catch (ClientException $e) {
                $this->container->get(ViolationMapper::class)->mapToForm($e, $formFiles);
            }
        }

        $formFix = $this->createForm(CrabFixType::class, $crab, []);
        $formFix->handleRequest($request);
        if ($formFix->isSubmitted() && $formFix->isValid()) {
            try {
                $data = $formFix->getData();
                $payload = [
                    'fixingComments' => $data['fixingComments'],
                    'status' => 'TO-INSPECT',
                    '@id' => $crab['@id'],
                ];

                $this->container->get(Client::class)->save(self::CRAB_URL, $payload);
                $this->addFlash('success', $this->container->get(TranslatorInterface::class)->trans('crab.success.fixed', [], 'crab'));

                return $this->redirectToRoute('crab_show', ['id' => $crab['id']]);
            } catch (ClientException $e) {
                $this->container->get(ViolationMapper::class)->mapToForm($e, $formFix);
                $this->addFlash('error', $e->getMessage());
            }
        }

        $formInspect = $this->createForm(CrabInspectType::class, $crab, []);
        $formInspect->handleRequest($request);
        if ($formInspect->isSubmitted() && $formInspect->isValid()) {
            try {
                $data = $formInspect->getData();
                $payload = [
                    'inspectingComments' => $data['inspectingComments'],
                    'status' => 'CLOSED',
                    '@id' => $crab['@id'],
                ];
                $this->container->get(Client::class)->save(self::CRAB_URL, $payload);
                $this->addFlash('success', $this->container->get(TranslatorInterface::class)->trans('crab.success.inspected', [], 'crab'));

                return $this->redirectToRoute('crab_show', ['id' => $crab['id']]);
            } catch (ClientException $e) {
                $this->container->get(ViolationMapper::class)->mapToForm($e, $formFix);
                $this->addFlash('error', $e->getMessage());
            }
        }

        return [
            'crab' => $crab,
            'form_fix' => $formFix->createView(),
            'form_inspect' => $formInspect->createView(),
            'form_files' => $formFiles->createView(),
        ];
    }

    #[Route(path: '/add', name: 'crab_add', methods: ['GET', 'POST'])]
    #[Route(path: '/add_from_order_line', name: 'crab_add_from_order_line', methods: ['GET', 'POST'])]
    #[Template('quality/crab/add.html.twig')]
    public function add(Request $request)
    {
        $formPayload = [];
        $formOptions = [];
        $client = $this->container->get(Client::class);
        $nonConformity = null;
        if (null !== ($nonConformityId = $request->query->get('nonConformity'))) {
            try {
                $nonConformity = $client->find(NonConformityController::NON_CONFORMITY_URL, $nonConformityId);
                $formPayload = [
                    'description' => $nonConformity['problem'],
                    'nonConformity' => $nonConformity['@id'],
                ];
            } catch (ClientException $e) {
                // do nothing
            }
        }

        if (null !== ($equipmentRecord = $request->query->get('equipmentRecord'))) {
            try {
                $equipmentRecord = $client->findOneBy('/equipment_records', ['legacyId' => $equipmentRecord]);
                $formOptions['equipmentRecords'][\sprintf('%s / %s - %s', $equipmentRecord['type'], $equipmentRecord['model'], $equipmentRecord['serialNumber'])] = $equipmentRecord['@id'];
            } catch (ClientException $e) {
                // do nothing
            }
        }

        if ('crab_add_from_order_line' === $request->attributes->get('_route')) {
            $formOptions['addFromSalesOrderLine'] = true;
        }

        $formOptions['user'] = null !== ($createdBy = $request->query->get('createdBy')) ? $client->findOneBy('/people', ['legacyId' => $createdBy]) : (null !== $nonConformity ? $nonConformity['reportedBy']['@id'] : $this->getUser());
        if (null !== $nonConformity) {
            $formOptions['addFromNonConformity'] = true;
        }

        if (!empty($nonConformity['equipmentRecords'])) {
            foreach ($nonConformity['parts'] ?? [] as $part) {
                $formOptions['parts'][\sprintf('%s - %s', $part['partNumber'], $part['description'])] = $part['partNumber'];
            }

            foreach ($nonConformity['equipmentRecords'] as $equipmentRecord) {
                $formOptions['equipmentRecords'][\sprintf('%s / %s - %s', $equipmentRecord['type'], $equipmentRecord['model'], $equipmentRecord['serialNumber'])] = $equipmentRecord['@id'];
            }
        }

        $form = $this->createForm(CrabAddType::class, $formPayload, $formOptions)->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $data = $form->getData();
                $isAddFromSalesOrderLine = isset($formOptions['addFromSalesOrderLine']);
                if (isset($data['part'])) {
                    $part = $client->get(\sprintf('%s?selection[]=description&selection[]=itemCode&selection[]=baseUOM', $data['part']));
                    if (null === $part) {
                        $this->addFlash('error', $this->container->get(TranslatorInterface::class)->trans('crab.errors.part_not_found', ['%part%' => $data['part']], 'crab'));

                        return $this->redirectToRoute('crab_add', ['nonConformity' => $nonConformityId]);
                    }

                    if (!$isAddFromSalesOrderLine) {
                        $data['part'] = [
                            'partNumber' => $part['itemCode'],
                            'description' => $part['description'],
                        ];
                    } else {
                        $data['partNumber'] = $part['itemCode'];
                        $data['partDescription'] = $part['description'];
                    }
                }

                $mainFile = $form->get('mainFile')->getData();
                unset($data['mainFile']);

                $route = $isAddFromSalesOrderLine ? '/quality/crabs/add_from_order_line' : self::CRAB_URL;

                if ($isAddFromSalesOrderLine) {
                    foreach (['eapId', 'orderLine'] as $propertyToStringify) {
                        $data[$propertyToStringify] = (string) $data[$propertyToStringify];
                    }

                    foreach ($data as $key => $property) {
                        if (null === $property) {
                            unset($data[$key]);
                        }
                    }

                    if (null !== $mainFile) {
                        $data['file'] = DataPart::fromPath($mainFile->getPathname());
                    }

                    $formData = new FormDataPart($data);
                    $client->post($route, [
                        'headers' => $formData->getPreparedHeaders()->toArray(),
                        'body' => $formData->bodyToIterable(),
                    ]);

                    $this->addFlash('success', $this->container->get(TranslatorInterface::class)->trans('crab.success.add_from_sol', [], 'crab'));

                    return $this->redirectToRoute('crab_home');
                }

                $crab = $this->container->get(Client::class)->save($route, $data);
                if (null !== $mainFile) {
                    $this->container->get(FileManager::class)->uploadFile(
                        $crab,
                        $mainFile,
                        self::CRAB_URL,
                        null,
                        'main_file',
                        false,
                        true
                    );
                }

                $this->addFlash('success', $this->container->get(TranslatorInterface::class)->trans('crab.success.add', [], 'crab'));

                return $this->redirectToRoute('crab_show', ['id' => $crab['id']]);
            } catch (ClientException $e) {
                $this->container->get(ViolationMapper::class)->mapToForm($e, $form);
            }
        }

        return [
            'form' => $form->createView(),
        ];
    }

    #[Route(path: '/{id}/edit', name: 'crab_edit', methods: ['GET', 'POST', 'PUT'])]
    #[Template('quality/crab/edit.html.twig')]
    #[IsGranted(attribute: 'FEATURE_CRAB_EDIT_VOTER', subject: new Expression('args["crab"].getIri()'))]
    public function edit(#[ApiValueResolverAttribute(parameters: ['resource' => self::CRAB_URL])] ApiData $crab, Request $request)
    {
        $crabPart = $crab['part'];
        $form = $this->createForm(CrabEditType::class, $crab);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $data = $form->getData();
                $derogation = $data['derogation'];
                unset($data['files'], $data['derogation']);

                $client = $this->container->get(Client::class);
                $newCrabPart = empty($crabPart) && !empty($data['part']);
                $updateCrabPart = isset($data['part'], $crabPart['@id']) && $data['part'] !== $crabPart['@id'];

                if ($newCrabPart || $updateCrabPart) {
                    $part = $client->get(\sprintf('%s?selection[]=description&selection[]=itemCode&selection[]=baseUOM', $data['part']));
                    if (null === $part) {
                        $this->addFlash('error', $this->container->get(TranslatorInterface::class)->trans('crab.errors.part_not_found', ['%part%' => $data['part']], 'crab'));

                        return $this->redirectToRoute('crab_edit', ['id' => $crab->getIriId()]);
                    }

                    $data['part'] = [
                        'partNumber' => $part['itemCode'],
                        'description' => $part['description'],
                    ];
                }

                $crabSaved = $client->save(self::CRAB_URL, $form->getData());
                $mainFile = $form->get('mainFile')->getData();
                if (null !== $mainFile) {
                    $this->container->get(FileManager::class)->uploadFile($crabSaved, $mainFile, self::CRAB_URL, null, 'main_file', false, true);
                }

                $this->addFlash('success', $this->container->get(TranslatorInterface::class)->trans('crab.success.edition', [], 'crab'));

                return $this->redirectToRoute('crab_show', ['id' => $crab['id']]);
            } catch (ClientException $e) {
                $data['derogation'] = $derogation;
                $this->container->get(ViolationMapper::class)->mapToForm($e, $form);
            }
        }

        return [
            'form' => $form->createView(),
            'crab' => $crab,
        ];
    }

    #[Route(path: '/{id}/delete', requirements: ['id' => '\d+'], name: 'crab_delete', methods: ['GET|DELETE'])]
    #[IsGranted(attribute: new Expression("is_granted('FEATURE_CRAB_DELETE') or is_granted('FEATURE_CRAB_ASSY_DELETE') or is_granted('FEATURE_CRAB_TEST_DELETE')"))]
    public function delete(#[ApiValueResolverAttribute(parameters: ['resource' => self::CRAB_URL])] ApiData $crab, Request $request)
    {
        if (!$this->isCsrfTokenValid('delete_crab', $request->query->get('_token'))) {
            $this->addFlash('error', $this->container->get(TranslatorInterface::class)->trans('crab.errors.delete', [], 'crab'));

            return $this->redirectToRoute('crab_show', ['id' => $crab->getIriId()]);
        }

        try {
            $this->container->get(Client::class)->remove(self::CRAB_URL, $crab->getIriId());
            $this->addFlash('success', $this->container->get(TranslatorInterface::class)->trans('crab.success.delete', [], 'crab'));
        } catch (ClientException $e) {
            $errorDescription = json_decode($e->getResponse()->getContent(), true);
            $this->addFlash(
                'error',
                \sprintf('%s. %s', $this->container->get(TranslatorInterface::class)->trans('crab.errors.delete', [], 'crab'), $errorDescription['hydra:description'])
            );
        }

        return $this->redirectToRoute('crab_home');
    }

    #[Route(path: '/{id}/duplicate', requirements: ['id' => '\d+'], name: 'crab_duplicate', methods: ['GET', 'POST'], defaults: ['label' => 'crab.title.duplicate'])]
    #[Template('quality/crab/duplicate.html.twig')]
    public function duplicate(#[ApiValueResolverAttribute(parameters: ['resource' => self::CRAB_URL])] ApiData $crab, Request $request)
    {
        $form = $this->container->get(FormFactoryInterface::class)->createNamed('duplicate', CrabDuplicateType::class, [], ['productType' => $crab['equipmentRecord']['product']['family']['productType']['@id']]);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $duplicatedCrabs = $this->container->get(Client::class)->save(
                    '/quality/crabs/duplicate',
                    array_merge($form->getData(), ['crab' => $crab['@id']])
                );

                if (isset($duplicatedCrabs['errorForEquipment']) && \is_array($duplicatedCrabs['errorForEquipment'])) {
                    foreach ($duplicatedCrabs['errorForEquipment'] as $serialNumber => $errorForEquipment) {
                        $this->addFlash('error', \sprintf('%s - %s', $serialNumber, $errorForEquipment));
                    }
                }

                $links = [];
                if (isset($duplicatedCrabs['duplicateCrabId']) && \is_array($duplicatedCrabs['duplicateCrabId'])) {
                    foreach ($duplicatedCrabs['duplicateCrabId'] as $duplicatedCrab) {
                        $links[] = \sprintf(
                            '<a href="%s">%s</a>',
                            $this->generateUrl('crab_show', ['id' => $duplicatedCrab]),
                            $duplicatedCrab
                        );
                    }
                }

                if ($links) {
                    $translator = $this->container->get(TranslatorInterface::class);
                    $this->addFlash('success', $translator->trans('crab.success.duplicate', ['%links%' => implode(' ', $links)], 'crab'));
                }

                return $this->redirectToRoute('crab_show', ['id' => $crab['id']]);
            } catch (ClientException $e) {
                $this->container->get(ViolationMapper::class)->mapToForm($e, $form);
            }
        }

        return [
            'form' => $form->createView(),
            'crab' => $crab,
        ];
    }

    #[Route(path: '/{crabId}/files/{id}', requirements: ['id' => '\d+'], name: 'crab_files_show', methods: 'GET')]
    public function showFile(
        #[ApiValueResolverAttribute(parameters: ['id' => 'crabId', 'resource' => self::CRAB_URL])] ApiData $crab,
        $id)
    {
        return $this->container->get(FileStreamedResponseFactory::class)->create(\sprintf('%s/%s/files/%s', self::CRAB_URL, $crab['id'], $id));
    }

    #[Route(path: '/{id}/main-file/{fileId}', requirements: ['id' => '\d+'], name: 'crab_download_main_file', methods: 'GET')]
    public function showMainFile($id, $fileId)
    {
        return $this->container->get(FileStreamedResponseFactory::class)->create(\sprintf('%s/%s/main_file/%s', self::CRAB_URL, $id, $fileId));
    }

    #[Route(path: '/{crabId}/files/{id}/delete', name: 'crab_file_delete', methods: ['GET'])]
    #[IsGranted('FEATURE_CRAB_FILE_DELETE')]
    public function deleteFile(Request $request, #[ApiValueResolverAttribute(parameters: ['resource' => self::CRAB_URL, 'id' => 'crabId'])] ApiData $crab, $id)
    {
        if (!$this->isCsrfTokenValid('delete_crab_file', $request->query->get('_token'))) {
            $this->addFlash('error', $this->container->get(TranslatorInterface::class)->trans('delete.errors', [], 'file_type'));

            return $this->redirectToRoute('crab_show', ['id' => $crab->getIriId()]);
        }
        $this->container->get(FileManager::class)->deleteFile($crab, self::CRAB_URL, \sprintf('files/%s', $id));
        $this->addFlash('success', $this->container->get(TranslatorInterface::class)->trans('delete.success', [], 'file_type'));

        return $this->redirectToRoute('crab_show', ['id' => $crab->getIriId()]);
    }

    #[Route(path: '/{crabId}/files/{fileId}/change_visibility', name: 'crab_file_change_visibility', methods: 'GET')]
    #[IsGranted('FEATURE_CRAB_FILE_CHANGE_VISIBILITY')]
    public function changeFileVisibility(#[ApiValueResolverAttribute(parameters: ['resource' => self::CRAB_FILE_URL, 'id' => 'fileId'])] ApiData $crabFile, int $fileId, int $crabId)
    {
        try {
            $this->container->get(Client::class)->put(
                \sprintf('files/%d', $fileId),
                ['json' => ['fileId' => $fileId, 'public' => !(true === $crabFile['public'])]]
            );

            $this->addFlash('success', $this->container->get(TranslatorInterface::class)->trans('visibility.success', [], 'file_type'));
        } catch (ClientException $e) {
            $errorDescription = json_decode($e->getResponse()->getContent(false), true);
            $this->addFlash('error', \sprintf('%s %s', $this->container->get(TranslatorInterface::class)->trans('visibility.errors', [], 'file_type'), $errorDescription['hydra:description']));
        }

        return $this->redirectToRoute('crab_show', ['id' => $crabId]);
    }

    #[Route(path: '/{id}/link-derogation', name: 'crab_derogation_link', methods: 'GET|POST', defaults: ['label' => 'crab.title.link_derogation'])]
    #[Template('quality/crab/link_derogation.html.twig')]
    #[IsGranted('FEATURE_LINK_DEROGATION')]
    public function linkDerogation(#[ApiValueResolverAttribute(parameters: ['resource' => self::CRAB_URL])] ApiData $crab, Request $request)
    {
        $form = $this->createForm(DerogationLinkType::class);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $payload = array_merge($form->getData(), ['@id' => $crab->getIri()]);
            try {
                $this->container->get(Client::class)->save(\sprintf('%s/%s', self::CRAB_URL, $crab->getIriId()), $payload);

                return $this->redirectToRoute('crab_show', ['id' => $crab->getIriId()]);
            } catch (ClientException $e) {
                $this->container->get(ViolationMapper::class)->mapToForm($e, $form);
            }
        }

        return [
            'crab' => $crab,
            'form' => $form->createView(),
        ];
    }

    /**
     * Returns the first non-empty value of a (potentially multiple) filter, or null when inactive.
     * The KPI charts work on a single factory/category, while the underlying filters are multiple.
     */
    private function firstActiveFilterValue(?FilterData $filterData): mixed
    {
        if (null === $filterData || !$filterData->hasValue()) {
            return null;
        }

        $value = $filterData->getValue();
        if (\is_array($value)) {
            $value = array_values(array_filter($value, static fn ($item) => null !== $item && '' !== $item));

            return $value[0] ?? null;
        }

        return $value;
    }

    private function resolveFactoryName(mixed $factory): ?string
    {
        if (!\is_string($factory) || '' === $factory) {
            return null;
        }

        try {
            return $this->container->get(Client::class)->get($factory)['name'] ?? null;
        } catch (ClientException) {
            return null;
        }
    }

    private function buildKpiChart(string $x, array $payload, ?string $factoryName, ?string $category, bool $stripPlotLabel = false): ChartBuilder
    {
        $kpi = $this->container->get(Client::class)->get(\sprintf('/reports/resource=/quality/crabs;x=%s;y=', $x), ['query' => $payload]);

        $chartBuilder = $this->container->get(ChartBuilderFactory::class)
            ->getColumnChartBuilder()
            ->setTitle($this->container->get(TranslatorInterface::class)->trans('crab.chart.by_locations', [
                '%factory%' => $factoryName,
                '%category%' => $category,
                '%by%' => $kpi['x'],
            ], 'crab'))
            ->addYAxis('CRAB Count')
        ;

        foreach ($kpi['xTotals'] as $key => $value) {
            $chartBuilder->addPlot(
                'Value',
                $stripPlotLabel ? preg_replace('/\s.*$|-.*$/', '', (string) $key) : $key,
                $value,
                [],
                ['name' => (string) $key]
            );
        }

        return $chartBuilder;
    }
}
