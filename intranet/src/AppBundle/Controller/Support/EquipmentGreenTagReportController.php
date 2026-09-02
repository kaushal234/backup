<?php

declare(strict_types=1);

namespace AppBundle\Controller\Support;

use ApiBundle\Client;
use ApiBundle\Iri\Iri;
use AppBundle\Chart\ChartBuilderFactory;
use AppBundle\Filters\Type\Support\EquipmentGreenTagReportFilterType;
use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route(path: '/support/green_tag_reports', defaults: ['alvest_module' => 'ER'])]
class EquipmentGreenTagReportController extends AbstractController
{
    public static function getSubscribedServices(): array
    {
        return array_merge(parent::getSubscribedServices(), [Client::class, FormFactoryInterface::class, ChartBuilderFactory::class, TranslatorInterface::class]);
    }

    #[Route(path: '', name: 'green_tag_report_home', methods: ['GET'])]
    #[Template('support/green_tag_reports/home.html.twig')]
    public function home(Request $request)
    {
        $client = $this->container->get(Client::class);
        $chartConfig = null;
        $summaryTable = [];

        $formFilter = $this->container->get(FormFactoryInterface::class)->createNamed('', EquipmentGreenTagReportFilterType::class, [], [
            'method' => Request::METHOD_GET,
            'action' => $this->generateUrl('green_tag_report_home'),
        ]);

        $filtered = false;
        $formFilter->handleRequest($request);

        if ($formFilter->isSubmitted() && $formFilter->isValid()) {
            $data = $formFilter->getData();
            $filtered = true;

            if (null !== $data['manufacturerLocation'] || null !== $data['day']) {
                $day = $data['day'] instanceof \DateTimeInterface
                    ? $data['day']
                    : new \DateTimeImmutable($data['day']);

                $location = $client->find('locations', Iri::id($data['manufacturerLocation']));

                $reportMonth = $client->get('/reports/resource=/estimated_green_tag_quantity_reports;x=month;y=ratio', [
                    'query' => [
                        'options' => [
                            'factory' => $data['manufacturerLocation'],
                            'month' => $day->format('Y-m'),
                            'families' => $data['equipmentRecords.product.family'],
                        ],
                    ],
                ]);

                $chartBuilder = $this->container->get(ChartBuilderFactory::class)
                    ->getLineChartBuilder()
                    ->setTitle($this->container->get(TranslatorInterface::class)->trans('support.green_tag_report.title', [
                        '%factory%' => $location['name'],
                        '%period%' => $day->format('Y-m'),
                    ], 'support'))
                    ->shareTooltip();

                /** @var \DateTimeImmutable $day */
                $firstDay = (clone $day)->modify('first day of this month')->setTime(0, 0);
                $lastDay = (clone $day)->modify('last day of this month')->setTime(0, 0);
                $period = new \DatePeriod($firstDay, new \DateInterval('P1D'), $lastDay->modify('+1 day'));

                $arearangeData = [];
                $xCategories = [];
                $index = 0;

                $monthlyMin = null;
                $monthlyMax = null;
                foreach ($reportMonth['rows'] as $rowData) {
                    $cell = reset($rowData);
                    if (isset($cell['extraData']['min'])) {
                        $monthlyMin = (int) $cell['extraData']['min'];
                    }
                    if (isset($cell['extraData']['max'])) {
                        $monthlyMax = (int) $cell['extraData']['max'];
                    }
                }

                $today = new \DateTimeImmutable('today');

                foreach ($period as $date) {
                    $dateStr = $date->format('Y-m-d');

                    if (isset($reportMonth['rows'][$dateStr])) {
                        $cell = reset($reportMonth['rows'][$dateStr]);
                        $value = $cell['value'] ?? 0;
                        $min = null !== $cell['extraData']['min'] ? (int) $cell['extraData']['min'] : ($monthlyMin ?? 0);
                        $max = null !== $cell['extraData']['max'] ? (int) $cell['extraData']['max'] : ($monthlyMax ?? 0);
                        $serials = $cell['extraData']['serialNumbers'] ?? [];
                    } else {
                        $value = 0;
                        $min = $monthlyMin ?? 0;
                        $max = $monthlyMax ?? 0;
                        $serials = [];
                    }

                    if ($date > $today && 0 === $value) {
                        $value = null;
                    }

                    $chartBuilder->addPlot(
                        $this->container->get(TranslatorInterface::class)->trans('support.green_tag_report.value', [], 'support'),
                        $dateStr,
                        $value,
                        [],
                        ['extraData' => ['serialNumbers' => $serials]]
                    );

                    $arearangeData[] = [$index, $min, $max];
                    $xCategories[] = $dateStr;
                    ++$index;
                }

                $chartConfig = $chartBuilder->buildConfig();
                $chartConfig['xAxis'] = ['categories' => $xCategories];
                $chartConfig['series'][] = [
                    'name' => $this->container->get(TranslatorInterface::class)->trans('support.green_tag_report.interval', [], 'support'),
                    'data' => $arearangeData,
                    'type' => 'arearange',
                    'linkedTo' => ':previous',
                    'color' => '#7cb5ec',
                    'fillOpacity' => 0.3,
                    'zIndex' => 0,
                    'marker' => ['enabled' => false],
                    'enableMouseTracking' => false,
                ];

                $computeSummaryFromReport = static function (array $reportRows): array {
                    $inRange = 0;
                    $workingDays = 0;
                    $seenMonths = [];

                    foreach ($reportRows as $rowData) {
                        if (empty($rowData) || !\is_array($rowData)) {
                            continue;
                        }

                        $cell = reset($rowData);
                        if (!\is_array($cell)) {
                            continue;
                        }

                        $value = $cell['value'] ?? null;
                        $min = $cell['extraData']['min'] ?? null;
                        $max = $cell['extraData']['max'] ?? null;
                        $wdays = $cell['extraData']['workingDays'] ?? null;

                        if (null !== $wdays && isset($cell['x'])) {
                            $month = mb_substr($cell['x'], 0, 7);
                            if (!\in_array($month, $seenMonths, true)) {
                                $workingDays += (int) $wdays;
                                $seenMonths[] = $month;
                            }
                        }

                        if (null !== $value && null !== $min && null !== $max) {
                            if ($value >= $min && $value <= $max) {
                                ++$inRange;
                            }
                        }
                    }

                    $percentage = $workingDays > 0 ? round(($inRange / $workingDays) * 100, 2) : 0;

                    return [
                        'total' => $workingDays,
                        'inRange' => $inRange,
                        'percentage' => $percentage.'%',
                    ];
                };

                $summaryTable[] = array_merge([
                    'factory' => $location['name'],
                    'label' => $this->container->get(TranslatorInterface::class)->trans('support.green_tag_report.selected_month', [], 'support'),
                    'month' => $day->format('Y-m'),
                ], $computeSummaryFromReport($reportMonth['rows'] ?? []));

                $today = new \DateTimeImmutable('today');
                $firstOfThisMonth = new \DateTimeImmutable($today->format('Y-m-01'));
                $start6 = $firstOfThisMonth->modify('-5 months')->format('Y-m-d');
                $end6 = $firstOfThisMonth->modify('last day of this month')->format('Y-m-d');

                $report6 = $client->get('/reports/resource=/estimated_green_tag_quantity_reports;x=month;y=ratio', [
                    'query' => [
                        'options' => [
                            'factory' => $data['manufacturerLocation'],
                            'start' => $start6,
                            'end' => $end6,
                            'families' => $data['equipmentRecords.product.family'],
                        ],
                    ],
                ]);

                $summaryTable[] = array_merge([
                    'factory' => $location['name'],
                    'label' => $this->container->get(TranslatorInterface::class)->trans('support.green_tag_report.last_6_months', [], 'support'),
                    'month' => (new \DateTimeImmutable($start6))->format('Y-m').' → '.(new \DateTimeImmutable($end6))->format('Y-m'),
                ], $computeSummaryFromReport($report6['rows'] ?? []));

                $year = $day->format('Y');
                $startYear = "$year-01-01";
                $endYear = "$year-12-31";

                $reportYear = $client->get('/reports/resource=/estimated_green_tag_quantity_reports;x=month;y=ratio', [
                    'query' => [
                        'options' => [
                            'factory' => $data['manufacturerLocation'],
                            'start' => $startYear,
                            'end' => $endYear,
                            'families' => $data['equipmentRecords.product.family'],
                        ],
                    ],
                ]);

                $summaryTable[] = array_merge([
                    'factory' => $location['name'],
                    'label' => "Year $year",
                    'month' => $year,
                ], $computeSummaryFromReport($reportYear['rows'] ?? []));
            }
        }

        return [
            'filtered' => $filtered,
            'form' => $formFilter->createView(),
            'chart' => $chartConfig ?? null,
            'summaryTable' => $summaryTable,
        ];
    }
}
