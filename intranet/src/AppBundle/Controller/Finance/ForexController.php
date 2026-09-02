<?php

declare(strict_types=1);

namespace AppBundle\Controller\Finance;

use ApiBundle\Client;
use ApiBundle\Form\ViolationMapper;
use ApiBundle\Http\CsvStreamedResponseFactory;
use ApiBundle\Hydra\HydraCollection;
use ApiBundle\Model\ApiData;
use AppBundle\Configuration\ApiValueResolverAttribute;
use AppBundle\Filters\Type\Finance\ExchangeRateDateFilterType;
use AppBundle\Filters\Type\Finance\ExchangeRateFilterType;
use AppBundle\Form\Type\Finance\ExchangeRateType;
use Cake\Chronos\Chronos;
use Psr\Cache\InvalidArgumentException;
use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpClient\Exception\ClientException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route(path: '/finance/forex', defaults: ['alvest_module' => 'FRX'])]
class ForexController extends AbstractController
{
    public function __construct(
        private readonly Client $client,
        private readonly CsvStreamedResponseFactory $csvStreamedResponseFactory,
        private readonly ViolationMapper $violationMapper,
        private readonly TranslatorInterface $translator,
    ) {
    }

    #[Route(path: '/', name: 'forex_home', methods: ['GET'])]
    #[Template('finance/forex/home.html.twig')]
    public function home(Request $request)
    {
        $year = null;
        $formFilters = $this->createForm(ExchangeRateDateFilterType::class, null, ['method' => 'GET', 'action' => $this->generateUrl('forex_home')]);
        $formFilters->handleRequest($request);
        if ($formFilters->isSubmitted() && $formFilters->isValid()) {
            $parameters = $formFilters->getData();
            $year = $parameters['year'];
        }

        $endOfMonthReport = $this->client->get('reports/resource=/exchange_rates;x=applicatedOn;y=currency.name', ['query' => [
            'options' => [
                'type' => 'endOfMonth',
                'currency' => 'EUR',
                'year' => $year,
            ],
        ],
        ]);

        $monthlyAverageReport = $this->client->get('reports/resource=/exchange_rates;x=applicatedOn;y=currency.name', ['query' => [
            'options' => [
                'type' => 'monthlyAverage',
                'currency' => 'EUR',
                'year' => $year,
            ],
        ],
        ]);

        $endOfMonthReportInUSD = $this->client->get('reports/resource=/exchange_rates;x=applicatedOn;y=currency.name', ['query' => [
            'options' => [
                'type' => 'endOfMonth',
                'currency' => 'USD',
                'year' => $year,
            ],
        ],
        ]);

        $monthlyAverageReportInUSD = $this->client->get('reports/resource=/exchange_rates;x=applicatedOn;y=currency.name', ['query' => [
            'options' => [
                'type' => 'monthlyAverage',
                'currency' => 'USD',
                'year' => $year,
            ],
        ],
        ]);

        $yearToDateAverage = $this->client->get('reports/resource=/exchange_rates;x=applicatedOn;y=currency.name', ['query' => [
            'options' => [
                'type' => 'yearToDateAverage',
                'currency' => 'EUR',
                'year' => $year,
            ],
        ],
        ]);

        $yearToDateAverageInUSD = $this->client->get('reports/resource=/exchange_rates;x=applicatedOn;y=currency.name', ['query' => [
            'options' => [
                'type' => 'yearToDateAverage',
                'currency' => 'USD',
                'year' => $year,
            ],
        ],
        ]);

        return [
            'endOfMonthReport' => $endOfMonthReport,
            'endOfMonthReportInUSD' => $endOfMonthReportInUSD,
            'monthlyAverageReport' => $monthlyAverageReport,
            'monthlyAverageReportInUSD' => $monthlyAverageReportInUSD,
            'yearToDateAverage' => $yearToDateAverage,
            'yearToDateAverageInUSD' => $yearToDateAverageInUSD,
            'formFilters' => $formFilters->createView(),
        ];
    }

    #[Route(path: '/search', name: 'forex_search', methods: ['GET'])]
    #[Template('finance/forex/search.html.twig')]
    #[IsGranted('FEATURE_EXCHANGE_RATE_WRITE')]
    public function search(Request $request)
    {
        $formFilters = $this->createForm(ExchangeRateFilterType::class, null, ['method' => 'GET', 'action' => $this->generateUrl('forex_search')]);

        $pagination = false;
        $reportTitle = 'forex.reports.last_20_rates';

        $parameters = [];
        $parameters['page'] = $request->query->getInt('page', 1);
        $parameters['order'] = [] !== $request->query->all('order') ? $request->query->all('order') : ['createdAt' => 'desc'];
        $parameters['itemsPerPage'] = $request->query->get('itemsPerPage', 20);

        $formFilters->handleRequest($request);

        if ($formFilters->isSubmitted() && $formFilters->isValid()) {
            if ($formFilters->getClickedButton() && 'download' === $formFilters->getClickedButton()->getName()) {
                $parameters['itemsPerPage'] = 2000;
                $parameters['context'] = ['datetime_format' => 'Y-m'];
                $parameters['properties'] = ['applicatedOn', 'type', 'currency' => ['name'], 'rate'];
                $parameters['order'] = ['type' => 'asc', 'applicatedOn' => 'asc', 'currency.name' => 'asc'];

                return $this->csvStreamedResponseFactory->create('exchange_rates', $parameters, 'exchange_rates.csv');
            }
            $parameters = $formFilters->getData();
            $parameters['pagination'] = false;
            $pagination = 25;
        }
        try {
            $exchangeRates = $this->client->findBy('exchange_rates', $parameters);
        } catch (ClientException $e) {
            $exchangeRates = [];
        }

        return [
            'reportTitle' => $reportTitle,
            'exchangeRates' => $exchangeRates,
            'pagination' => $pagination,
            'formFilters' => $formFilters->createView(),
        ];
    }

    #[Route(path: '/add', name: 'forex_add', methods: 'GET|POST')]
    #[Template('finance/forex/add_edit.html.twig')]
    #[IsGranted('FEATURE_EXCHANGE_RATE_WRITE')]
    public function add(Request $request, CacheInterface $cache)
    {
        $form = $this->createForm(ExchangeRateType::class);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $this->client->save('exchange_rates', $form->getData());

                $this->addFlash(
                    'success',
                    $this->translator->trans('forex.messages.success.create', [], 'forex')
                );

                try {
                    $cache->delete('forex_latest_rates');
                } catch (InvalidArgumentException $e) {
                    // do nothing
                }

                return $this->redirectToRoute('forex_search');
            } catch (ClientException $e) {
                $this->violationMapper->mapToForm($e, $form);
            }
        }

        return ['form' => $form->createView()];
    }

    #[Route(path: '/{id}/edit', name: 'forex_edit', methods: 'GET|POST')]
    #[Template('finance/forex/add_edit.html.twig')]
    #[IsGranted('FEATURE_EXCHANGE_RATE_WRITE')]
    public function edit(Request $request, #[ApiValueResolverAttribute] ApiData $exchangeRate, CacheInterface $cache)
    {
        $form = $this->createForm(ExchangeRateType::class, $exchangeRate);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $this->client->save('exchange_rates', $form->getData());

                $this->addFlash(
                    'success',
                    $this->translator->trans('forex.messages.success.edit', [], 'forex')
                );

                try {
                    $cache->delete('forex_latest_rates');
                } catch (InvalidArgumentException $e) {
                    // do nothing
                }

                return $this->redirectToRoute('forex_search');
            } catch (ClientException $e) {
                $this->violationMapper->mapToForm($e, $form);
            }
        }

        return [
            'form' => $form->createView(),
            'exchangeRate' => $exchangeRate,
        ];
    }

    public function latestRates(): Response
    {
        $parameters = $latestRates = [];
        $parameters['type'] = 'TLD';
        $parameters['order'] = ['applicatedOn' => 'desc'];
        $parameters['currency.name'] = ['USD'];
        try {
            $latestRates['forex.home.eur_usd_rate'] = $this->client->findBy('exchange_rates', $parameters, [], ['cache' => true])->first();
        } catch (ClientException $e) {
            // no op
        }

        $parameters['currency.name'] = ['CAD'];
        try {
            $latestRates['forex.home.eur_cad_rate'] = $this->client->findBy('exchange_rates', $parameters, [], ['cache' => true])->first();
        } catch (ClientException $e) {
            // no op
        }

        $parameters['type'] = 'END';
        $parameters['order'] = ['currency.name' => 'asc'];
        $parameters['currency.name'] = ['CNY', 'USD'];
        $startOfMonth = Chronos::today()->startOfMonth();
        $parameters['applicatedOn'] = ['after' => $startOfMonth->toDateString()];

        try {
            /** @var HydraCollection $rates */
            $rates = $this->client->findBy('exchange_rates', $parameters, [], ['cache' => true]);
            if (2 !== $rates->count()) {
                $parameters['applicatedOn'] = ['strictly_before' => $startOfMonth->toDateString(), 'after' => $startOfMonth->subMonths(1)->toDateString()];
                $rates = $this->client->findBy('exchange_rates', $parameters, [], ['cache' => true]);
            }
        } catch (ClientException $e) {
            $rates = new HydraCollection([]);
        }

        if (2 === $rates->count() && '0.0000000' !== $dividend = $rates->last()['rate']) {
            $rate = $rates->first();
            $rate['rate'] = \sprintf('%0.8f', $rate['rate'] / $dividend);
            $latestRates['forex.home.cny_usd_rate'] = $rate;
        }

        return $this->render('finance/forex/partial/_latest_rates.html.twig', [
            'latestRates' => $latestRates,
        ]);
    }
}
