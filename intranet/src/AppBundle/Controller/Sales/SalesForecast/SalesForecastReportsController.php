<?php

declare(strict_types=1);

namespace AppBundle\Controller\Sales\SalesForecast;

use ApiBundle\Client;
use ApiBundle\Form\ViolationMapper;
use ApiBundle\Iri\Iri;
use AppBundle\Chart\ChartBuilder;
use AppBundle\Chart\ChartBuilderFactory;
use AppBundle\Filters\Type\Sales\SalesForecastCustomerReportFilter;
use AppBundle\Filters\Type\Sales\SalesForecastWeekQuantityReportFilter;
use AppBundle\Filters\Type\Sales\SalesForecastWeekReportFilter;
use AppBundle\Form\Type\Directory\Location\SSOChoiceType;
use AppBundle\Form\Type\Directory\People\ASMChoiceType;
use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpClient\Exception\ClientException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route(path: '/sales/sales-forecasts', defaults: ['alvest_module' => 'SFR', 'moduleDomain' => 'sales_forecasts'])]
class SalesForecastReportsController extends AbstractController
{
    private readonly Client $client;

    private readonly AuthorizationCheckerInterface $authorizationChecker;

    private readonly TranslatorInterface $translator;

    private readonly FormFactoryInterface $formFactory;

    private readonly ViolationMapper $violationMapper;

    private readonly ChartBuilderFactory $chartBuilderFactory;

    public function __construct(
        Client $client,
        TranslatorInterface $translator,
        FormFactoryInterface $formFactory,
        ViolationMapper $violationMapper,
        ChartBuilderFactory $chartBuilderFactory,
        AuthorizationCheckerInterface $authorizationChecker,
    ) {
        $this->client = $client;
        $this->translator = $translator;
        $this->formFactory = $formFactory;
        $this->violationMapper = $violationMapper;
        $this->chartBuilderFactory = $chartBuilderFactory;
        $this->authorizationChecker = $authorizationChecker;
    }

    #[Route(path: '/dashboard', name: 'sales_forecasts_dashboard', methods: ['GET'])]
    #[Template('sales/sales_forecasts/dashboard.html.twig')]
    public function dashboard(Request $request)
    {
        $me = $this->client->get('/me');

        $military = $request->query->has('military');

        $reportSFRBySSOFactoryASM = $this->client->get('reports/resource=/sales/sales_forecasts;x=sso.name;y=factory.name', [
            'query' => [
                'options' => [
                    'asm' => $me['@id'],
                    'military' => $military,
                ],
            ],
        ]);

        return [
            'limit_date' => (new \DateTime('2 month ago'))->format('Y-m-d'),
            'military' => $military,
            'SFRBySSOFactory' => $this->client->get('reports/resource=/sales/sales_forecasts;x=sso.name;y=factory.name', [
                'query' => [
                    'options' => [
                        'military' => $military,
                    ],
                ],
            ]),
            'SFRBySSOFactoryDelinquent' => $this->client->get('reports/resource=/sales/sales_forecasts;x=sso.name;y=factory.name', [
                'query' => [
                    'options' => [
                        'delinquent' => true,
                        'military' => $military,
                    ],
                ],
            ]),
            'SFRBySSOFactoryRecentlyOrdered' => $this->client->get('reports/resource=/sales/sales_forecasts;x=sso.name;y=factory.name', [
                'query' => [
                    'options' => [
                        'recently' => 'ordered',
                        'military' => $military,
                    ],
                ],
            ]),
            'SFRBySSOFactoryRecentlyLost' => $this->client->get('reports/resource=/sales/sales_forecasts;x=sso.name;y=factory.name', [
                'query' => [
                    'options' => [
                        'recently' => 'lost',
                        'military' => $military,
                    ],
                ],
            ]),
            'SFRBySSOFactoryHotDeals' => $this->client->get('reports/resource=/sales/sales_forecasts;x=sso.name;y=factory.name', [
                'query' => [
                    'options' => [
                        'hot_deals' => true,
                        'military' => $military,
                    ],
                ],
            ]),
            'SFRBySSOFactoryASM' => $reportSFRBySSOFactoryASM,
            'user' => $me,
        ];
    }

    #[Route(path: '/dashboard-asm', name: 'sales_forecasts_dashboard_asm', methods: ['GET|POST'])]
    #[Template('sales/sales_forecasts/dashboard_asm.html.twig')]
    public function dashboardASM(Request $request)
    {
        $asm = null;
        $form = $this
            ->formFactory
            ->createNamed(
                'asm',
                ASMChoiceType::class,
                null,
                [
                    'csrf_protection' => false,
                    'method' => Request::METHOD_GET,
                ]
            );

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            try {
                $asm = $this->client->find('people', Iri::id($data));
            } catch (ClientException $e) {
                $this->violationMapper->mapToForm($e, $form);
            }
        } else {
            $asm = $this->client->get('/me');
        }

        if (null !== $asm['businessUnit']
            && null !== $asm['businessUnit']['location']
            && null !== $asm['businessUnit']['location']['currency']) {
            $currency = $asm['businessUnit']['location']['currency']['name'];
        } else {
            $currency = 'USD';
        }
        $notActiveCustomers = $this->client->findBy('sales/customers', ['open_sales_forecast' => false, 'mainSalesRepresentative.asm' => $asm['@id']]);

        $valorizations = $this->client->get('reports/resource=/sales/sales_forecasts;x=date;y=value', [
            'query' => [
                'options' => [
                    'asm' => $asm['@id'],
                ],
            ],
        ]);

        $valorizationsByFactory = $this->client->get('reports/resource=/sales/sales_forecasts;x=date;y=value', [
            'query' => [
                'options' => [
                    'asm' => $asm['@id'],
                    'byFactory' => true,
                ],
            ],
        ]);

        $valorizationsPonderated = $this->client->get('reports/resource=/sales/sales_forecasts;x=date;y=value', [
            'query' => [
                'options' => [
                    'asm' => $asm['@id'],
                    'ponderated' => true,
                ],
            ],
        ]);

        $valorizationsPonderatedByFactory = $this->client->get('reports/resource=/sales/sales_forecasts;x=date;y=value', [
            'query' => [
                'options' => [
                    'asm' => $asm['@id'],
                    'byFactory' => true,
                    'ponderated' => true,
                ],
            ],
        ]);

        $chartBuilderModel = $this->chartBuilderFactory
            ->getColumnChartBuilder()
            ->addYAxis('Amount')
            ->disableLegend()
        ;

        $replacements = ['%target%' => $asm['lastname'].', '.$asm['firstname'], '%currency%' => $currency];

        $chartBuilder = clone $chartBuilderModel;
        $chartBuilder->setTitle($this->translator->trans('sales_forecasts.charts.valorization', $replacements, 'sales_forecasts'));

        $chartBuilderPonderated = clone $chartBuilderModel;
        $chartBuilderPonderated->setTitle($this->translator->trans('sales_forecasts.charts.valorization_ponderated', $replacements, 'sales_forecasts'));

        $chartBuilderFactory = clone $chartBuilderModel;
        $chartBuilderFactory->setTitle($this->translator->trans('sales_forecasts.charts.valorization_by_factory', $replacements, 'sales_forecasts'));

        $chartBuilderPonderatedFactory = clone $chartBuilderModel;
        $chartBuilderPonderatedFactory->setTitle($this->translator->trans('sales_forecasts.charts.valorization_ponderated_by_factory', $replacements, 'sales_forecasts'));

        foreach ($valorizations['xTotals'] as $key => $value) {
            $month = (new \DateTime($key))->format('Y-m');
            $chartBuilder->addPlot(
                'Value',
                $month,
                $value
            );
        }

        foreach ($valorizationsPonderated['xTotals'] as $key => $value) {
            $month = (new \DateTime($key))->format('Y-m');
            $chartBuilderPonderated->addPlot(
                'Value',
                $month,
                $value
            );
        }

        foreach ($valorizationsByFactory['rows'] as $key => $data) {
            foreach ($data as $factory => $value) {
                $month = (new \DateTime($key))->format('Y-m');
                $chartBuilderFactory->addPlot(
                    $factory,
                    $month,
                    $value['value']
                );
            }
        }

        foreach ($valorizationsPonderatedByFactory['rows'] as $key => $data) {
            foreach ($data as $factory => $value) {
                $month = (new \DateTime($key))->format('Y-m');
                $chartBuilderPonderatedFactory->addPlot(
                    $factory,
                    $month,
                    $value['value']
                );
            }
        }

        $startingDate = new \DateTime('midnight first day of this month');
        $endingDate = new \DateTime('midnight first day of this month next year');

        $period = new \DatePeriod($startingDate, new \DateInterval('P1M'), $endingDate);

        $values = array_map([ChartBuilder::class, 'formatMonth'], iterator_to_array($period));

        $chartBuilder->reMapXValues($values);
        $chartBuilderPonderated->reMapXValues($values);
        $chartBuilderFactory->reMapXValues($values);
        $chartBuilderPonderatedFactory->reMapXValues($values);

        $chartBuilderFactory->reMapXValues(array_map([ChartBuilder::class, 'formatMonth'], iterator_to_array($period)));

        $chartBuilderPonderatedFactory->reMapXValues(array_map([ChartBuilder::class, 'formatMonth'], iterator_to_array($period)));

        $topCustomersReport = $this->client->get('reports/resource=/sales/sales_forecasts;x=top_ten;y=value', [
            'query' => [
                'options' => [
                    'asm' => $asm['@id'],
                ],
            ],
        ]);

        $topTenCustomers = [];
        foreach ($topCustomersReport['xTotals'] as $key => $value) {
            $topTenCustomers[] = [
                'name' => $key,
                'value' => $value,
                'iri' => $topCustomersReport['metadata']['xIris'][$key],
                'id' => Iri::id($topCustomersReport['metadata']['xIris'][$key]),
            ];
        }

        return [
            'currency' => $currency,
            'chart' => $chartBuilder->buildConfig(),
            'chartFactory' => $chartBuilderFactory->buildConfig(),
            'chartPonderated' => $chartBuilderPonderated->buildConfig(),
            'chartPonderatedFactory' => $chartBuilderPonderatedFactory->buildConfig(),
            'topTenCustomers' => $topTenCustomers,
            'limit_date' => (new \DateTime('2 month ago'))->format('Y-m-d'),
            'notActiveCustomers' => $notActiveCustomers,
            'asm' => $asm,
            'form' => $form->createView(),
            'SFRBySSOFactoryDelinquentForAsm' => $this->client->get('reports/resource=/sales/sales_forecasts;x=sso.name;y=factory.name', [
                'query' => [
                    'options' => [
                        'asm' => $asm['@id'],
                        'delinquent' => true,
                    ],
                ],
            ]),
            'SFRByFactoryCustomerForAsm' => $this->client->get('reports/resource=/sales/sales_forecasts;x=buyer.name;y=factory.name', [
                'query' => [
                    'options' => [
                        'asm' => $asm['@id'],
                    ],
                ],
            ]),
            'SFRBySSOFactoryHotDealsForAsm' => $this->client->get('reports/resource=/sales/sales_forecasts;x=sso.name;y=factory.name', [
                'query' => [
                    'options' => [
                        'hot_deals' => true,
                        'asm' => $asm['@id'],
                    ],
                ],
            ]),
            'SFRBySSOFactoryRecentlyOrderedForAsm' => $this->client->get('reports/resource=/sales/sales_forecasts;x=sso.name;y=factory.name', [
                'query' => [
                    'options' => [
                        'recently' => 'ordered',
                        'asm' => $asm['@id'],
                    ],
                ],
            ]),
            'SFRBySSOFactoryRecentlyLostForAsm' => $this->client->get('reports/resource=/sales/sales_forecasts;x=sso.name;y=factory.name', [
                'query' => [
                    'options' => [
                        'recently' => 'lost',
                        'asm' => $asm['@id'],
                    ],
                ],
            ]),
        ];
    }

    #[Route(path: '/dashboard-sso', name: 'sales_forecasts_dashboard_sso', methods: ['GET|POST'])]
    #[Template('sales/sales_forecasts/dashboard_sso.html.twig')]
    #[IsGranted('FEATURE_SALES_FORECAST_VALORIZATION_READ')]
    public function dashboardSSO(Request $request)
    {
        $currentUser = $this->client->get('/me');

        $defaultLocation = null;
        if (
            null !== $currentUser['businessUnit']
            && null !== $currentUser['businessUnit']['location']
        ) {
            $defaultLocation = $currentUser['businessUnit']['location']['@id'];
        }

        $form = $this
            ->formFactory
            ->createNamed(
                'sso',
                SSOChoiceType::class,
                $defaultLocation,
                [
                    'csrf_protection' => false,
                    'method' => Request::METHOD_GET,
                ]
            );

        if (!$this->authorizationChecker->isGranted('ACL_SUPERUSER') && !$this->authorizationChecker->isGranted('FEATURE_SALES_FORECAST_VIEW_FULL')) {
            $acls = $this->client->findBy('acls', ['user' => $currentUser['@id'], 'group.features.name' => 'FEATURE_SALES_FORECAST_VALORIZATION_READ']);

            $locations = [];
            foreach ($acls as $acl) {
                $locations[] = $acl['location']['@id'];
            }

            $options = $form->getConfig()->getOptions();
            $ssos = $options['choices'];

            foreach ($ssos as $name => $iri) {
                if (!\in_array($iri, $locations, true)) {
                    unset($ssos[$name]);
                }
            }

            $form = $this
                ->formFactory
                ->createNamed(
                    'sso',
                    SSOChoiceType::class,
                    $defaultLocation,
                    [
                        'csrf_protection' => false,
                        'method' => Request::METHOD_GET,
                        'choices' => $ssos,
                    ]
                );
        }

        $form->handleRequest($request);

        $location = $this->client->get($form->isSubmitted() && $form->isValid() ? $form->getData() : $defaultLocation);

        $currency = $location['currency']['name'];

        $valorizations = $this->client->get('reports/resource=/sales/sales_forecasts;x=date;y=value', [
            'query' => [
                'options' => [
                    'sso' => $location['@id'],
                ],
            ],
        ]);

        $valorizationsByFactory = $this->client->get('reports/resource=/sales/sales_forecasts;x=date;y=value', [
            'query' => [
                'options' => [
                    'sso' => $location['@id'],
                    'byFactory' => true,
                ],
            ],
        ]);

        $valorizationsPonderated = $this->client->get('reports/resource=/sales/sales_forecasts;x=date;y=value', [
            'query' => [
                'options' => [
                    'sso' => $location['@id'],
                    'ponderated' => true,
                ],
            ],
        ]);

        $valorizationsPonderatedByFactory = $this->client->get('reports/resource=/sales/sales_forecasts;x=date;y=value', [
            'query' => [
                'options' => [
                    'sso' => $location['@id'],
                    'byFactory' => true,
                    'ponderated' => true,
                ],
            ],
        ]);

        $chartBuilderModel = $this->chartBuilderFactory
            ->getColumnChartBuilder()
            ->addYAxis('Amount')
            ->disableLegend()
        ;

        $replacements = ['%target%' => $location['name'], '%currency%' => $currency];

        $chartBuilder = clone $chartBuilderModel;
        $chartBuilder->setTitle($this->translator->trans('sales_forecasts.charts.valorization', $replacements, 'sales_forecasts'));

        $chartBuilderPonderated = clone $chartBuilderModel;
        $chartBuilderPonderated->setTitle($this->translator->trans('sales_forecasts.charts.valorization_ponderated', $replacements, 'sales_forecasts'));

        $chartBuilderFactory = clone $chartBuilderModel;
        $chartBuilderFactory->setTitle($this->translator->trans('sales_forecasts.charts.valorization_by_factory', $replacements, 'sales_forecasts'));

        $chartBuilderPonderatedFactory = clone $chartBuilderModel;
        $chartBuilderPonderatedFactory->setTitle($this->translator->trans('sales_forecasts.charts.valorization_ponderated_by_factory', $replacements, 'sales_forecasts'));

        foreach ($valorizations['xTotals'] as $key => $value) {
            $month = (new \DateTime($key))->format('Y-m');
            $chartBuilder->addPlot(
                'Value',
                $month,
                $value
            );
        }

        foreach ($valorizationsPonderated['xTotals'] as $key => $value) {
            $month = (new \DateTime($key))->format('Y-m');
            $chartBuilderPonderated->addPlot(
                'Value',
                $month,
                $value
            );
        }

        foreach ($valorizationsByFactory['rows'] as $key => $data) {
            foreach ($data as $factory => $value) {
                $month = (new \DateTime($key))->format('Y-m');
                $chartBuilderFactory->addPlot(
                    $factory,
                    $month,
                    $value['value']
                );
            }
        }

        foreach ($valorizationsPonderatedByFactory['rows'] as $key => $data) {
            foreach ($data as $factory => $value) {
                $month = (new \DateTime($key))->format('Y-m');
                $chartBuilderPonderatedFactory->addPlot(
                    $factory,
                    $month,
                    $value['value']
                );
            }
        }

        $startingDate = new \DateTime('midnight first day of this month');
        $endingDate = new \DateTime('midnight first day of this month next year');

        $period = new \DatePeriod($startingDate, new \DateInterval('P1M'), $endingDate);

        $values = array_map([ChartBuilder::class, 'formatMonth'], iterator_to_array($period));

        $chartBuilder->reMapXValues($values);
        $chartBuilderPonderated->reMapXValues($values);
        $chartBuilderFactory->reMapXValues($values);
        $chartBuilderPonderatedFactory->reMapXValues($values);

        return [
            'form' => $form->createView(),
            'currency' => $currency,
            'chart' => $chartBuilder->buildConfig(),
            'chartFactory' => $chartBuilderFactory->buildConfig(),
            'chartPonderated' => $chartBuilderPonderated->buildConfig(),
            'chartPonderatedFactory' => $chartBuilderPonderatedFactory->buildConfig(),
        ];
    }

    #[Route(path: '/reports/customers', name: 'sales_forecasts_dashboard_customer', methods: ['GET'], defaults: ['currency' => 'EUR'])]
    #[Template('sales/sales_forecasts/dashboard_customer.html.twig')]
    public function customerDashboard(Request $request)
    {
        $currentUser = $this->client->get('/me');
        $form = $this
            ->formFactory
            ->createNamed(
                '',
                SalesForecastCustomerReportFilter::class,
                [],
                [
                    'action' => $this->generateUrl('sales_forecasts_dashboard_customer'),
                    'method' => 'GET',
                ]
            );

        $currencies = $form->get('currency')->getConfig()->getOptions()['choices'];
        $eur = $currencies['EUR'];

        if (!$request->query->has('currency')) {
            $request->query->set('currency', null !== $currentUser['businessUnit']['location']['currency'] ? $currentUser['businessUnit']['location']['currency']['@id'] : $eur);
        }

        $form->handleRequest($request);

        $parameters = $form->getData();

        $currenciesDictionary = array_flip($currencies);
        $selectedCurrency = $currenciesDictionary[$parameters['options']['currency']];

        return [
            'form' => $form->createView(),
            'customerReport' => $this->client->get('reports/resource=/sales/sales_forecasts;x=customer;y=sso', [
                'query' => ['options' => $parameters['options']],
            ]),
            'selectedCurrency' => $selectedCurrency,
        ];
    }

    #[Route(path: '/reports/per-week', name: 'sales_forecasts_report_per_week', methods: ['GET'], defaults: ['currency' => 'EUR'])]
    #[Template('sales/sales_forecasts/reports/value_per_week.html.twig')]
    public function salesForecastsValuePerWeekReport(Request $request)
    {
        $currentUser = $this->client->get('/me');
        $form = $this
            ->formFactory
            ->createNamed(
                '',
                SalesForecastWeekReportFilter::class,
                [],
                [
                    'action' => $this->generateUrl('sales_forecasts_report_per_week'),
                    'method' => 'GET',
                ]
            );

        $currencies = $form->get('currency')->getConfig()->getOptions()['choices'];
        $eur = $currencies['EUR'];

        if (!$request->query->has('currency')) {
            $request->query->set('currency', null !== $currentUser['businessUnit']['location']['currency'] ? $currentUser['businessUnit']['location']['currency']['@id'] : $eur);
        }

        $form->handleRequest($request);

        $parameters = $form->getData();

        $report = $this->client->get('reports/resource=/sales/sales_forecasts;x=week;y=sso', ['query' => $parameters]);

        $currenciesDictionary = array_flip($currencies);
        $selectedCurrency = $currenciesDictionary[$parameters['options']['currency']];

        if (empty($report['rows'])) {
            return [
                'form' => $form->createView(),
                'selectedCurrency' => $selectedCurrency,
            ];
        }

        $chart = $this->chartBuilderFactory
            ->getLineChartBuilder()
            ->setTitle('')
            ->addYAxis($selectedCurrency, ['min' => 0])
        ;

        foreach ($report['rows'] as $week => $ssos) {
            foreach ($ssos as $sso => $values) {
                $chart->addPlot(
                    $sso,
                    $week,
                    (int) $values['value']
                );
            }
        }

        foreach ($report['xTotals'] as $week => $value) {
            $chart->addPlot(
                'TOTAL',
                $week,
                (int) $value,
                ['options' => ['visible' => false]]
            );
        }

        return [
            'form' => $form->createView(),
            'chart' => $chart->buildConfig(),
            'selectedCurrency' => $selectedCurrency,
        ];
    }

    #[Route(path: '/reports/quantity-per-period/{period}', name: 'sales_forecasts_report_quantity_per_period', methods: ['GET'], requirements: ['period' => 'week|month'])]
    #[Template('sales/sales_forecasts/reports/quantity_per_period.html.twig')]
    public function salesForecastsQuantityPerPeriodReport(Request $request, string $period)
    {
        $form = $this
            ->formFactory
            ->createNamed(
                '',
                SalesForecastWeekQuantityReportFilter::class,
                [],
                [
                    'action' => $this->generateUrl('sales_forecasts_report_quantity_per_period', ['period' => $period]),
                ]
            );

        $form->handleRequest($request);
        $parameters = $form->getData();

        $report = $this->client->get(\sprintf('reports/resource=/sales/sales_forecasts;x=%s;y=quantity_per_sso', $period), ['query' => $parameters]);

        if (empty($report['rows'])) {
            return [
                'period' => $period,
                'form' => $form->createView(),
            ];
        }

        $chart = $this->chartBuilderFactory
            ->getLineChartBuilder()
            ->setTitle('')
            ->addYAxis('quantity of SFR', ['min' => 0])
        ;

        foreach ($report['rows'] as $row => $ssos) {
            foreach ($ssos as $sso => $values) {
                $chart->addPlot(
                    $sso,
                    $row,
                    (int) $values['value']
                );
            }
        }

        foreach ($report['xTotals'] as $row => $totalquantity) {
            $chart->addPlot(
                'TOTAL',
                $row,
                (int) $totalquantity,
                ['options' => ['visible' => false]]
            );
        }

        return [
            'period' => $period,
            'form' => $form->createView(),
            'chart' => $chart->buildConfig(),
        ];
    }
}
