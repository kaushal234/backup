<?php

declare(strict_types=1);

namespace App\Pi\Utils;

use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class UnitManager
{
    private const PROJECT_STATUSES = [
        3 => 'active',
    ];

    private const WORK_ORDER_STATUSES = [
        1 => 'free',
        2 => 'planned',
        3 => 'printed',
        4 => 'in.prod',
        5 => 'active',
        6 => 'completed',
        7 => 'closed',
        8 => 'archived',
        9 => 'cancelled',
        10 => 'modify',
    ];

    /**
     * @var HttpClientInterface
     */
    private $client;

    public function __construct(HttpClientInterface $client)
    {
        $this->client = $client;
    }

    /**
     * @throws \Exception
     */
    public function getUnit($comp, $sn, $lang = 'en'): array
    {
        $query = <<<SQL
SELECT sn,s.id AS id, TRIM(s.t_prno) AS t_prno , TRIM(s.t_pdno) AS t_pdno, p.family AS family, s.status FROM service AS s
                                  LEFT JOIN pi_unit_family AS p ON s.sn=p.unit
                                  WHERE sn = '$sn';
SQL;
        $er = \tldUtils::getSqlRowToAssocArray($query);

        if (!$er['sn']) {
            throw new \Exception('Unit not found in PIO database');
        }

        $productionOrder = $er['t_pdno'];

        if ('' === $productionOrder) {
            try {
                $response = $this->client->request('GET', \sprintf('ion/projects/site=%d;project=%s', $comp, $er['t_prno']))->toArray();
            } catch (\Exception $exception) {
                throw new \Exception('API request error <br/> Please open a TTS or contact your supervisor');
            }

            if (null === $item = ($response['productionOrders'][0] ?? null)) {
                throw new \Exception("Project not found for unit {$er['sn']} <br/> Please open a TTS or contact your supervisor");
            }
            if (null === ($productionOrder = ($item['productionOrderIdentifier'] ?? null))) {
                throw new \Exception("Production order not found for Project {$item['project']} <br/> Please open a TTS or contact your supervisor");
            }
        }

        try {
            $project = $this->client->request('GET', \sprintf('ion/projects/site=%d;project=%s', $comp, $er['t_prno']), [
                'query' => [
                    'productionOrder' => $productionOrder,
                    'languageID' => 'CH' === $lang ? 'zh' : mb_strtolower($lang),
                ],
            ])->toArray();
        } catch (ClientExceptionInterface $exception) {
            throw new \Exception("Project not found for unit {$er['sn']} <br/> Please open a TTS or contact your supervisor");
        } catch (ServerExceptionInterface $exception) {
            throw new \Exception('API request error <br/> Please open a TTS or contact your supervisor');
        }

        if ([] === $project['productionOrders']) {
            throw new \Exception("Production order not found for Project {$project['projectIdentifier']} <br/> Please open a TTS or contact your supervisor");
        }

        // store the whole project in the session to find it later when needed for task-related operations
        $_SESSION['project'] = $project;

        if ('' === $er['t_pdno']) {
            $eq = new \tldEquipment($er['id'], true);
            $eq->updateRecord(['t_pdno' => $productionOrder], ['t_pdno']);
        }

        if (self::PROJECT_STATUSES[3] !== $project['status']) {
            throw new \Exception("Project {$project['projectIdentifier']} not in status active <br/> Please open a TTS or contact your supervisor");
        }

        if (
            self::WORK_ORDER_STATUSES[3] !== $project['productionOrders'][0]['status']
            && self::WORK_ORDER_STATUSES[4] !== $project['productionOrders'][0]['status']
            && self::WORK_ORDER_STATUSES[5] !== $project['productionOrders'][0]['status']
            && self::WORK_ORDER_STATUSES[10] !== $project['productionOrders'][0]['status']
        ) {
            throw new \Exception("Production order status inactive {$project['productionOrders'][0]['productionOrderIdentifier']}<br/> Please open a TTS or contact your supervisor");
        }
        // GET FIRST LEVEL PN
        try {
            $customizedBillOfMaterials = $this->client->request(
                'GET',
                \sprintf('ion/customized_bill_of_materials/site=%d;project=%s', $comp, $project['projectIdentifier']),
            )->toArray();
        } catch (\Exception $exception) {
            throw new \Exception('API CBOM request error <br/> Please open a TTS or contact your supervisor');
        }

        return [
            'sn' => $sn,
            'id' => $er['id'],
            'family' => $er['family'],
            't_prno' => $project['projectIdentifier'],
            'projectStatus' => $project['status'],
            'productionStartDate' => (new \DateTime($project['productionOrders'][0]['productionStart']))->format('Y-m-d'),
            't_pdno' => $project['productionOrders'][0]['productionOrderIdentifier'],
            'workOrderStatus' => ucfirst($project['productionOrders'][0]['status']),
            'cbom' => array_column($customizedBillOfMaterials['items'], 'partNumber'),
        ];
    }
}
