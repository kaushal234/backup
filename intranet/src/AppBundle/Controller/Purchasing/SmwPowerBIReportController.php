<?php

declare(strict_types=1);

namespace AppBundle\Controller\Purchasing;

use ApiBundle\Client;
use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpClient\Exception\ClientException;
use Symfony\Component\Routing\Annotation\Route;

#[Route(path: '/purchasing/smw-meeting', defaults: ['alvest_module' => 'SMW'])]
class SmwPowerBIReportController extends AbstractController
{
    public const string RESOURCE_URL = 'power_bi/reports';

    public static function getSubscribedServices(): array
    {
        return array_merge(parent::getSubscribedServices(), [
            Client::class,
        ]);
    }

    #[Route(path: '/{id}', name: 'smw_power_bi_report', methods: ['GET'], requirements: ['id' => '\d+(?:\.\d+)*'])]
    #[Template('purchasing/smw/power_bi_reports.html.twig')]
    public function show(string $id)
    {
        $reports = [
            '2.1' => [
                [
                    'id' => null,
                ],
                [
                    'id' => '3',
                    'title' => 'quality_smw.report_title.r_2_1_1',
                    'table' => 'public v_bi41_otdp',
                    'column' => 'businessunit',
                    'page' => 'OTDP (SMW)',
                ],
                [
                    'id' => '3',
                    'title' => 'quality_smw.report_title.r_2_1_2',
                    'table' => 'public v_bi41_otdp',
                    'column' => 'businessunit',
                    'page' => 'Confirmation Dashboard by BU',
                ],
            ],
            '2.2' => [
                [
                    'id' => '38',
                    'title' => 'quality_smw.report_title.r_2_2',
                    'table' => 'bi76_buyer_dashboard_planned',
                    'column' => 'site',
                    'page' => 'MRP Review : PO/DO placed in late',
                ],
            ],
            '2.3' => [
                [
                    'id' => '42',
                    'title' => 'quality_smw.report_title.r_2_3',
                    'table' => 'DimSite',
                    'column' => 'Site',
                    'page' => 'Shortage (SMW)',
                ],
            ],
            '6.1' => [
                [
                    'id' => '2',
                    'title' => 'quality_smw.report_title.r_6_1_1',
                    'table' => 'v_bi37_inventory_by_site_warehouse',
                    'column' => 'site',
                    'page' => 'Raw Material Inventory / Buyer',
                ],
                [
                    'id' => '2',
                    'title' => '',
                    'table' => 'v_bi37_inventory_by_site_warehouse',
                    'column' => 'site',
                    'page' => 'TOP 10 Inventory Line',
                ],
                [
                    'id' => '1',
                    'title' => 'quality_smw.report_title.r_6_1_2',
                    'table' => 'bi08_inbound_outbound_evolution',
                    'column' => 'Site',
                ],
            ],
            '6.2' => [
                [
                    'id' => '1',
                    'title' => 'quality_smw.report_title.r_6_2',
                    'table' => 'bi08_inbound_outbound_evolution',
                    'column' => 'Site',
                    'page' => 'TOP 10 Inbound (SMW)',
                ],
            ],
            '6.3' => [
                [
                    'id' => '1',
                    'title' => 'quality_smw.report_title.r_6_3',
                    'table' => 'bi08_inbound_outbound_evolution',
                    'column' => 'Site',
                    'page' => 'TOP 10 Worst Inventory coverage (SMW)',
                ],
            ],
            '6.4' => [
                [
                    'id' => '4',
                    'title' => 'quality_smw.report_title.r_6_4',
                    'table' => 'Bi42_top_10',
                    'column' => 'shiptosite',
                    'page' => 'TOP 10 In Transit Items',
                ],
            ],
            '6.5' => [
                [
                    'id' => '2',
                    'title' => 'quality_smw.report_title.r_6_5',
                    'table' => 'v_bi37_inventory_by_site_warehouse',
                    'column' => 'site',
                    'page' => 'Quarantine TOP 10',
                ],
            ],
            '6.6' => [
                [
                    'id' => '2',
                    'title' => 'quality_smw.report_title.r_6_6',
                    'table' => 'v_bi37_inventory_by_site_warehouse',
                    'column' => 'site',
                    'page' => 'Obsolete Stock / Slow Moving',
                ],
            ],
            '6.7' => [
                [
                    'id' => '31',
                    'title' => 'quality_smw.report_title.r_6_7_1',
                    'table' => 'v_bi75_pipo_report',
                    'column' => 'Site',
                    'page' => 'TOP 10 PIPO in value',
                ],
                [
                    'id' => '31',
                    'title' => 'quality_smw.report_title.r_6_7_2',
                    'table' => 'v_bi75_pipo_report',
                    'column' => 'Site',
                    'page' => 'TOP 10 PIPO Oldest',
                ],
            ],
            '7.1' => [
                [
                    'id' => '43',
                    'title' => 'quality_smw.report_title.r_7_1',
                    'table' => 'SiteDm',
                    'column' => 'Site',
                    'page' => 'Inbound and Outbound Rates',
                ],
            ],
            '7.3' => [
                [
                    'id' => '43',
                    'title' => 'quality_smw.report_title.r_7_3',
                    'table' => 'SiteDm',
                    'column' => 'Site',
                    'page' => 'Warehouse % per Month',
                ],
            ],
            '7.4' => [
                [
                    'id' => '43',
                    'title' => 'quality_smw.report_title.r_7_4',
                    'table' => 'SiteDm',
                    'column' => 'Site',
                    'page' => 'Global Warehouse Productivity',
                ],
            ],
            '8.1' => [
                [
                    'id' => '29',
                    'title' => 'quality_smw.report_title.r_8_1',
                    'table' => 'v_bi80_cbom_change_reason',
                    'column' => 'SITE',
                ],
            ],
        ];

        $match = $reports[$id] ?? [];
        $result = [];
        $cache = [];

        foreach ($match as $key => $report) {
            $reportId = $report['id'];

            if (null === $reportId) {
                continue;
            }

            if (!isset($cache[$reportId])) {
                $client = $this->container->get(Client::class);

                try {
                    $response = $client->find(self::RESOURCE_URL, $reportId);
                    $cache[$reportId] = $response;
                } catch (ClientException $e) {
                    $cache[$reportId] = null;
                }
            }

            $response = $cache[$reportId];

            if ($response && !empty($response['powerBiUuid'])) {
                $result[] = [
                    ...$match[$key],
                    'uuid' => $response['powerBiUuid'],
                ];
            }
        }

        return [
            'reports' => $result,
            'id' => $id,
        ];
    }
}
