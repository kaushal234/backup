<?php

declare(strict_types=1);

namespace AppBundle\Controller\Quality;

use ApiBundle\Client;
use ApiBundle\Form\ViolationMapper;
use ApiBundle\Model\ApiData;
use AppBundle\Chart\ChartBuilder;
use AppBundle\Chart\ChartBuilderFactory;
use AppBundle\Configuration\ApiValueResolverAttribute;
use AppBundle\Filters\Type\Quality\LocationCleanlinessFiltersType;
use AppBundle\Form\Type\Quality\LocationCleanlinessType;
use Cake\Chronos\Chronos;
use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpClient\Exception\ClientException;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route(path: '/quality/cleanliness', defaults: ['alvest_module' => 'CL', 'breadcrumb_label' => 'menu.cleanliness.title', 'moduleDomain' => 'location_cleanliness'])]
class LocationCleanlinessController extends AbstractController
{
    final public const RESOURCE_URL = 'quality/cleanliness';

    private readonly Client $client;

    private readonly FormFactoryInterface $formFactory;

    private readonly ViolationMapper $violationMapper;

    private readonly TranslatorInterface $translator;
    private readonly ChartBuilder $chartBuilder;

    public function __construct(Client $client, FormFactoryInterface $formFactory, ViolationMapper $violationMapper, TranslatorInterface $translator, ChartBuilderFactory $chartBuilderFactory)
    {
        $this->client = $client;
        $this->formFactory = $formFactory;
        $this->violationMapper = $violationMapper;
        $this->translator = $translator;
        $this->chartBuilder = $chartBuilderFactory->getLineChartBuilder();
    }

    #[Route(path: '/list', name: 'location_cleanliness_list', methods: 'GET')]
    #[Template('quality/cleanliness/list.html.twig')]
    public function list(Request $request)
    {
        $page = $request->query->getInt('page', 1);

        $parameters = [
            'page' => $page,
            'itemsPerPage' => 25,
        ];

        $cleanliness = $this->client->findBy(self::RESOURCE_URL, $parameters, ['id']);

        return [
            'cleanliness' => $cleanliness,
            'paginationUrl' => [
                'route' => $request->attributes->get('_route'),
                'parameters' => $request->query->all(),
            ],
        ];
    }

    /**
     * @todo if we want to add a Group target: do the same as in MonthlyActivitiesChartsProvider::initializeProductivityChartBuilder()
     */
    #[Route(path: '', name: 'location_cleanliness_home', methods: 'GET|POST')]
    #[Template('quality/cleanliness/index.html.twig')]
    public function index(Request $request)
    {
        $formFilters = $this->formFactory->createNamed('', LocationCleanlinessFiltersType::class, [], ['method' => 'GET']);

        $requestDates = $request->query->all()['date'] ?? [];
        $formFilters->handleRequest($request);
        if ($formFilters->isSubmitted()) {
            $parameters = $formFilters->getData();
            if (null === $parameters['date']['after'] && null === $parameters['date']['before']) {
                $parameters['date'] = [
                    'after' => $requestDates['after'],
                    'before' => $requestDates['before'],
                ];
            }
        } else {
            $parameters = [
                'date' => [
                    'after' => Chronos::parse('-13 months')->format('Y-m'),
                    'before' => Chronos::parse('-1 month')->format('Y-m'),
                ],
            ];

            if (null !== ($userLocation = $formFilters->get('location')->getData())) {
                $parameters += ['location' => $userLocation];
            }
        }

        $locations = $formFilters->get('location')->getConfig()->getOption('choices');
        $locations = array_flip($locations);

        $locationName = isset($parameters['location']) ? $locations[$parameters['location']] : null;
        $startingDate = Chronos::instance(new \DateTime($parameters['date']['after']));
        $endingDate = Chronos::instance(new \DateTime($parameters['date']['before']));

        try {
            $cleanliness = $this->client->findBy(self::RESOURCE_URL, $parameters)->getIterator()->getArrayCopy();
        } catch (ClientException $e) {
            $this->violationMapper->mapToForm($e, $formFilters);
            $cleanliness = [];
        }

        $chartBuilder = null;
        if (null !== $locationName) {
            $chartBuilder = $this->chartBuilder
                ->setTitle("Cleanliness KPI for $locationName")
                ->addYAxis('Rating', ['min' => 0, 'max' => 5])
            ;

            foreach ($cleanliness as $c) {
                $month = Chronos::instance(new \DateTime($c['date']))->format('Y-m');
                $chartBuilder->addPlot(
                    $locationName,
                    $month,
                    $c['rating']
                );
            }

            $period = new \DatePeriod($startingDate, new \DateInterval('P1M'), $endingDate->modify('+1 month'));

            $chartBuilder->reMapXValues(array_map([ChartBuilder::class, 'formatMonth'], iterator_to_array($period)));
        }

        // Add action
        $form = $this->formFactory->createNamed('cleanliness', LocationCleanlinessType::class, ['location' => $formFilters->get('location')->getData()]);
        $form->handleRequest($request);
        if ($form->isSubmitted()) {
            try {
                $data = $form->getData();
                $this->client->save(self::RESOURCE_URL, $data);

                $this->addFlash(
                    'success',
                    $this->translator->trans('cleanliness.messages.success.add', [], 'cleanliness')
                );

                return $this->redirectToRoute('location_cleanliness_home',
                    [
                        'location' => $data['location'],
                        'date' => [
                            'after' => Chronos::instance(new \DateTime($data['date']))->modify('-12 months')->format('Y-m'),
                            'before' => Chronos::instance(new \DateTime($data['date']))->format('Y-m'),
                        ],
                    ]);
            } catch (ClientException $e) {
                $this->addFlash(
                    'error',
                    $this->translator->trans('cleanliness.messages.error.add', [], 'cleanliness')
                );
            }
        }

        return [
            'locationName' => $locationName,
            'chart' => null !== $chartBuilder ? $chartBuilder->buildConfig() : [],
            'formFilters' => $formFilters->createView(),
            'form' => $form->createView(),
            'parameters' => $parameters,
        ];
    }

    #[Route(path: '/{id}/show', name: 'location_cleanliness_show', methods: 'GET|POST')]
    #[Route(path: '/{id}/edit', name: 'location_cleanliness_edit', methods: 'GET|POST')]
    #[Template('quality/cleanliness/edit.html.twig')]
    #[IsGranted('FEATURE_CLEANLINESS_WRITE')]
    public function edit(#[ApiValueResolverAttribute(parameters: ['resource' => 'quality/cleanliness'])] ApiData $locationCleanliness, Request $request)
    {
        $form = $this
            ->formFactory
            ->createNamed(
                'cleanliness',
                LocationCleanlinessType::class,
                $locationCleanliness
            );

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $data = $form->getData();
                $data['@id'] = $locationCleanliness['@id'];

                $this->client->save(self::RESOURCE_URL, $data);

                $this->addFlash(
                    'success',
                    $this->translator->trans('cleanliness.messages.success.edit', [], 'cleanliness')
                );

                $endingDateParam = new \DateTime($data['date']);
                $startingDateParam = (new \DateTime($data['date']))->modify('-12 months');

                return $this->redirectToRoute('location_cleanliness_home',
                    [
                        'location' => $data['location'],
                        'date' => [
                            'after' => $startingDateParam->format('Y-m-d'),
                            'before' => $endingDateParam->format('Y-m-d'),
                        ],
                    ]);
            } catch (ClientException $e) {
                $this->addFlash(
                    'error',
                    $this->translator->trans('cleanliness.messages.error.edit', [], 'cleanliness')
                );
            }
        }

        return [
            'cleanliness' => $locationCleanliness,
            'form' => $form->createView(),
        ];
    }

    #[Route(path: '/{id}/delete', name: 'location_cleanliness_delete', methods: 'GET|DELETE')]
    #[IsGranted('FEATURE_CLEANLINESS_WRITE')]
    public function delete($id): RedirectResponse
    {
        try {
            $this->client->remove('quality/cleanliness', $id);

            $this->addFlash(
                'success',
                $this->translator->trans('cleanliness.messages.success.delete', [], 'cleanliness')
            );
        } catch (ClientException $e) {
            $this->addFlash(
                'error',
                $this->translator->trans('cleanliness.messages.error.delete', [], 'cleanliness')
            );
        }

        return $this->redirectToRoute('location_cleanliness_list');
    }

    #[Route(path: '/benchmark/{type}', name: 'cleanliness_benchmark', methods: 'GET', requirements: ['type' => 'factories|non-factories'])]
    #[Template('quality/cleanliness/benchmark.html.twig')]
    public function benchmark($type)
    {
        $startingDate = new \DateTime('13 months ago');
        $endingDate = new \DateTime('first day of last month');

        $parameters['date'] = [
            'after' => $startingDate->format('Y-m'),
            'before' => $endingDate->format('Y-m'),
        ];

        $parameters['location.capability.factory'] = 'factories' === $type ? 1 : 0;

        $cleanliness = $this->client->findBy(self::RESOURCE_URL, $parameters);

        $chartBuilder = $this->chartBuilder
            ->setTitle($this->translator->trans('factories' === $type ? 'cleanliness.title.benchmarkFactory' : 'cleanliness.title.BenchmarkNoFactory', [], 'cleanliness'))
            ->addYAxis($this->translator->trans('cleanliness.fields.rating', [], 'cleanliness'), ['min' => 0, 'max' => 5])
        ;

        foreach ($cleanliness as $c) {
            $month = (new \DateTime($c['date']))->format('Y-m');
            $chartBuilder->addPlot(
                $c['location']['name'],
                $month,
                $c['rating']
            );
        }

        $period = new \DatePeriod($startingDate, new \DateInterval('P1M'), $endingDate->modify('+1 month'));

        $chartBuilder->reMapXValues(array_map([ChartBuilder::class, 'formatMonth'], iterator_to_array($period)));

        return [
            'cleanliness' => $cleanliness,
            'parameters' => $parameters,
            'chart' => $chartBuilder->buildConfig(),
        ];
    }
}
