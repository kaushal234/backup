<?php

declare(strict_types=1);

namespace AppBundle\Controller\Materials\Warehouse;

use ApiBundle\Client;
use AppBundle\Chart\Materials\Warehouse\MonthlyActivitiesChartsProvider;
use AppBundle\Filters\Type\Materials\Warehouse\MonthlyActivitiesKPIFilterType;
use Cake\Chronos\Chronos;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpClient\Exception\ClientException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route(path: '/materials/warehouse/monthly-activities', defaults: ['alvest_module' => 'WHSE', 'moduleDomain' => 'warehouse_monthly_activities'])]
class MonthlyActivitiesController extends AbstractController
{
    #[Route(path: '', name: 'warehouse_monthly_activities_home', methods: 'GET')]
    public function index(Request $request, Client $client, MonthlyActivitiesChartsProvider $chartsProvider): Response
    {
        $form = $this->createForm(MonthlyActivitiesKPIFilterType::class);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $parameters = array_merge(['context' => ['datetime_format' => 'Y-m'], 'order[location.name]' => 'asc', 'order[applicatedOn]' => 'asc', 'pagination' => false], $form->getData());

            if (null === $parameters['applicatedOn']['after'] && null === $parameters['applicatedOn']['before']) {
                $parameters['applicatedOn'] = [
                    'after' => Chronos::parse('midnight first day of last month last year')->format('Y-m'),
                    'before' => Chronos::parse('midnight last day of last month')->format('Y-m'),
                ];
            }

            try {
                $activities = $client->get('materials/warehouse/monthly_activities', ['query' => $parameters]);
            } catch (ClientException $e) {
            }

            $chartsProvider->initialize($activities['hydra:member'] ?? [], $parameters, $form->get('fullTimeEquivalentMonthlyHours')->getData());
        }

        return $this->render('materials/warehouse/monthly_activities/kpi.html.twig', [
            'activityChart' => $chartsProvider->getActivityChartConfig(),
            'productivityChart' => $chartsProvider->getProductivityChartConfig(),
            'fullTimeEquivalentChart' => $chartsProvider->getFullTimeEquivalentChartConfig(),
            'aggregates' => $chartsProvider->getAggregates(),
            'targets' => MonthlyActivitiesChartsProvider::TARGETS,
            'formFilters' => $form->createView(),
        ]);
    }
}
