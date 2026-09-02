<?php

declare(strict_types=1);

namespace AppBundle\Controller\Quality;

use ApiBundle\Client;
use ApiBundle\Form\ViolationMapper;
use ApiBundle\Hydra\HydraCollection;
use ApiBundle\Model\ApiData;
use AppBundle\Chart\ChartBuilder;
use AppBundle\Chart\ChartBuilderFactory;
use AppBundle\Configuration\ApiValueResolverAttribute;
use AppBundle\Filters\Type\Quality\LocationCleanlinessFiltersType;
use AppBundle\Form\Type\Quality\QHSEProgressType;
use Cake\Chronos\Chronos;
use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpClient\Exception\ClientException;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route(path: '/quality/qhse-progress', defaults: ['alvest_module' => 'QHSE', 'moduleDomain' => 'qhse'])]
class QualityHealthSafetyEnvironmentProgressController extends AbstractController
{
    final public const RESOURCE_URL = 'quality/qhse_progress';

    private readonly Client $client;

    private readonly ViolationMapper $violationMapper;

    private readonly TranslatorInterface $translator;
    private readonly ChartBuilder $chartBuilder;

    public function __construct(Client $client, ViolationMapper $violationMapper, TranslatorInterface $translator, ChartBuilderFactory $chartBuilderFactory)
    {
        $this->client = $client;
        $this->violationMapper = $violationMapper;
        $this->translator = $translator;
        $this->chartBuilder = $chartBuilderFactory->getLineChartBuilder();
    }

    #[Route(path: '/list', name: 'qhse_list', methods: 'GET')]
    #[Template('quality/qhse/list.html.twig')]
    public function list(#[ApiValueResolverAttribute(parameters: ['resource' => self::RESOURCE_URL])] HydraCollection $qualityHealthSafetyEnvironmentProgresses)
    {
        return ['qhseProgress' => $qualityHealthSafetyEnvironmentProgresses];
    }

    #[Route(path: '', name: 'qhse_home', methods: 'GET|POST')]
    #[Template('quality/qhse/index.html.twig')]
    public function index(Request $request)
    {
        $formFilters = $this->container->get('form.factory')->createNamed('', LocationCleanlinessFiltersType::class, [], ['method' => 'GET']);

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
                'location' => $formFilters->get('location')->getData(),
                'date' => [
                    'after' => Chronos::parse('-12 months')->format('Y-m'),
                    'before' => Chronos::now()->format('Y-m'),
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
            $qhse_progress = $this->client->findBy(self::RESOURCE_URL, $parameters)->getIterator()->getArrayCopy();
        } catch (ClientException $e) {
            $this->violationMapper->mapToForm($e, $formFilters);
            $qhse_progress = [];
        }
        $chartBuilder = null;
        if (null !== $locationName) {
            $chartBuilder = $this->chartBuilder
                ->setTitle("ISO 14001 KPI for $locationName")
                ->addYAxis('Rating', ['min' => 0, 'max' => 100])
            ;

            foreach ($qhse_progress as $rating) {
                $month = Chronos::instance(new \DateTime($rating['date']))->format('Y-m');
                $chartBuilder->addPlot(
                    $locationName,
                    $month,
                    $rating['rating']
                );
            }

            $period = new \DatePeriod($startingDate, new \DateInterval('P1M'), $endingDate->modify('+1 month'));

            $chartBuilder->reMapXValues(array_map([ChartBuilder::class, 'formatMonth'], iterator_to_array($period)));
        }

        // Add action
        $form = $this->container->get('form.factory')->createNamed('qhse', QHSEProgressType::class, ['location' => $formFilters->get('location')->getData()]);
        $form->handleRequest($request);
        if ($form->isSubmitted()) {
            try {
                $data = $form->getData();
                $this->client->save(self::RESOURCE_URL, $data);

                $this->addFlash(
                    'success',
                    $this->translator->trans('cleanliness.messages.success.add', [], 'cleanliness')
                );

                return $this->redirectToRoute('qhse_home',
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
            //            'chart' => $chartBuilder->buildConfig(),
            'formFilters' => $formFilters->createView(),
            'form' => $form->createView(),
            'parameters' => $parameters,
        ];
    }

    #[Route(path: '/{id}/show', name: 'qhse_show', methods: 'GET|POST')]
    #[Route(path: '/{id}/edit', name: 'qhse_edit', methods: 'GET|POST')]
    #[Template('quality/qhse/edit.html.twig')]
    #[IsGranted('FEATURE_QHSE_WRITE')]
    public function edit(#[ApiValueResolverAttribute(parameters: ['resource' => self::RESOURCE_URL])] ApiData $qualityHealthSafetyEnvironmentProgress, Request $request)
    {
        $form = $this->container->get('form.factory')->createNamed('qhse', QHSEProgressType::class, $qualityHealthSafetyEnvironmentProgress);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $data = $form->getData();
                $data['@id'] = $qualityHealthSafetyEnvironmentProgress['@id'];
                $this->client->save(self::RESOURCE_URL, $data);

                $this->addFlash(
                    'success',
                    $this->translator->trans('cleanliness.messages.success.edit', [], 'cleanliness')
                );

                return $this->redirectToRoute('qhse_home',
                    [
                        'location' => $data['location'],
                        'date' => [
                            'after' => (new \DateTime($data['date']))->format('Y-m-d'),
                            'before' => (new \DateTime($data['date']))->modify('-12 months')->format('Y-m-d'),
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
            'qhse_progress' => $qualityHealthSafetyEnvironmentProgress,
            'form' => $form->createView(),
        ];
    }

    #[Route(path: '/{id}/delete', name: 'qhse_delete', methods: 'GET|DELETE')]
    #[IsGranted('FEATURE_QHSE_WRITE')]
    public function delete($id): RedirectResponse
    {
        try {
            $this->client->remove(self::RESOURCE_URL, $id);

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

        return $this->redirectToRoute('qhse_list');
    }

    #[Route(path: '/benchmark/{type}', name: 'qhse_benchmark', methods: 'GET', requirements: ['type' => 'factories|non-factories'])]
    #[Template('quality/qhse/benchmark.html.twig')]
    public function benchmark($type)
    {
        $startingDate = new \DateTime('11 months ago');
        $endingDate = new \DateTime('first day of this month');

        $parameters['date'] = [
            'after' => $startingDate->format('Y-m'),
            'before' => $endingDate->format('Y-m'),
        ];

        $parameters['location.capability.factory'] = 'factories' === $type ? 1 : 0;

        $qhseProgress = $this->client->findBy(self::RESOURCE_URL, $parameters);

        $chartBuilder = $this->chartBuilder
            ->setTitle($this->translator->trans('factories' === $type ? 'cleanliness.title.benchmarkFactory' : 'cleanliness.title.BenchmarkNoFactory', [], 'cleanliness'))
            ->addYAxis($this->translator->trans('qhse.fields.rating', [], 'qhse'), ['min' => 0, 'max' => 100])
        ;

        foreach ($qhseProgress as $rating) {
            $month = (new \DateTime($rating['date']))->format('Y-m');
            $chartBuilder->addPlot(
                $rating['location']['name'],
                $month,
                $rating['rating']
            );
        }

        $period = new \DatePeriod($startingDate, new \DateInterval('P1M'), $endingDate->modify('+1 month'));

        $chartBuilder->reMapXValues(array_map([ChartBuilder::class, 'formatMonth'], iterator_to_array($period)));

        return [
            'qhseProgress' => $qhseProgress,
            'parameters' => $parameters,
            'chart' => $chartBuilder->buildConfig(),
        ];
    }
}
