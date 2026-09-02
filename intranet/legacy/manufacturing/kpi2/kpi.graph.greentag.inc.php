<?php

use ApiBundle\Client;
use ApiBundle\Iri\Iri;

include_once 'sales_service.inc.php';

$kpiDefinition = new tldKpiDefinition('greentag_GTcount', $lang);

$graph = new tldGraph();
$graph->setTitle($kpiDefinition->getTitle() . " - $factoryLabel");

// Case of multiple series
$bu = $_SESSION['kpi']['form']['bu_id'];
$factory = TldDatabase::escape($factoryLabel);
$date_query = (new DateTime($_SESSION['kpi']['form']['dt_from']))->format('Ym');
$date_con = (new DateTime($_SESSION['kpi']['form']['dt_from']))->format('Y-m');
if ($_SESSION['kpi']['graph'] === 'Green Tag - by type'){
    $model = implode("','",$_SESSION['kpi']['form']["types_". $bu]);
    $filter = 'service.type';
} else {
    $model = implode("','",$_SESSION['kpi']['form']["models_". $bu]);
    $filter = 'service.model';
}
$unitType = $_SESSION['kpi']['form']['unit_type'];

// Transform month into shortname
$fulldate = new \DateTime($date_query.'01');
$lastday = $fulldate->format('t');

$ydm = clone $fulldate;
// Populate X Axis based on month chosen
for ($i = 1; $i <= $lastday; $i++) {
    $d[$ydm->format('Ymd')] = ["Name" => "$i"];
    $ydm->modify('+1 day');
}

$factoryCondition = $factory === 'ALL' ? '' : " AND man_location LIKE '$factory' ";
$unitTypeCondition = $unitType === 'ALL' ? '' : " AND service.sn LIKE '$unitType%' ";
$modelCondition = $unitType === 'T' && $factory !== 'ALL' ? " AND $filter IN ('$model') " : '';


// quantities fix defined at the beginning of the month
global $kernel;
$client = $kernel->getContainer()->get(Client::class);
$location = $client->findBy('locations', ['name' => $factory]);
$startDate = $fulldate->format('Y-m-d');
$endDate = (clone $fulldate)->modify('last day of this month')->format('Y-m-d');
$criteria = [
    'day' => [
        'after' => $startDate,
        'before' => $endDate,
    ],
    'manufacturerLocation' => $location->first()['@id'],
];

if ('P' === $unitType) {
    $criteria['equipmentRecords.combinationMode'] = 'PRE-ASSEMBLY';
}

$reports = $client->findBy('estimated_green_tag_quantity_reports', $criteria);
$reportsArray = array_map(fn($item) => $item, $reports->all());
$reportsByDay = [];

foreach ($reportsArray as $item) {
    $data = $item->toArray();
    if (!isset($data['day'])) {
        continue;
    }

    $dayKey = (new \DateTime($data['day']))->format('Ymd');
    $reportsByDay[$dayKey] = $data;
}


if ('T' === $unitType) {
    foreach ($reportsByDay as $day => &$report) {
        $filteredER = [];

        foreach ($report['equipmentRecords'] ?? [] as $er) {
            if ($er['combinationMode'] ?? '' !== 'PRE-ASSEMBLY') {
                $filteredER[] = $er;
            }
        }

        $report['equipmentRecords'] = $filteredER;
    }
    unset($report);
}


foreach ($reportsByDay as $day => $report) {
    $count = count($report['equipmentRecords'] ?? []);
    $d[$day]['Estimated GT (Qty set at the begin of the month)'] = $count;
}

$previous = 0;
foreach ($d as $day => &$data) {
    $data['Estimated GT (Qty set at the begin of the month)'] = ($data['Estimated GT (Qty set at the begin of the month)'] ?? 0) + $previous;
    $previous = $data['Estimated GT (Qty set at the begin of the month)'];
}
unset($data);

// Estimated GT Backlog (offset)
$query =<<<SQL
SELECT COUNT(distinct service.id) AS num
FROM service
WHERE PERIOD_DIFF('$date_query', DATE_FORMAT(service.dgt_rev,'%Y%m')) > 0
  $unitTypeCondition
  $modelCondition
  AND service.dgt_act='' AND service.dgt_com='' AND service.dyt='' 
  AND service.dgt_rev <>'' AND service.date_shipped IS NULL
$factoryCondition
SQL;
$estimatedGTBacklog = tldUtils::getSqlRowToAssocArray($query);
$estimatedGTOffset = (int) $estimatedGTBacklog['num'];

// Estimated GT Date
$query = <<<SQL
SELECT
  DATE_FORMAT( service.dgt_rev, '%Y%m%d' ) AS nam_period,
  COUNT(*) AS est_gt
FROM service
WHERE
  DATE_FORMAT( service.dgt_rev, '%Y%m' ) LIKE '$date_query'
  $modelCondition
  $unitTypeCondition
  AND ((  service.dgt_act='' AND service.dgt_com='' AND service.dyt='') OR (service.dgt_com<>'' AND PERIOD_DIFF('$date_query', DATE_FORMAT(service.dgt_com,'%Y%m')) <= 0 ))
$factoryCondition
GROUP BY nam_period
SQL;
$rows = array_column(tldUtils::getSqlToAssocArray($query), 'est_gt', 'nam_period');
$totalizer = $estimatedGTOffset;
foreach ($d as $date => $value) {
    if (array_key_exists($date, $rows)) {
        $totalizer += (int) $rows[$date];
    }
    $d[$date]['Estimated GT (Qty)'] = $totalizer;
}

// Factory Promised Customer Date (offset)
$query = <<<SQL
SELECT COUNT(DISTINCT service.id) AS num
FROM service
LEFT JOIN sor_units AS t3 ON t3.id = service.sor_uid
WHERE
  PERIOD_DIFF('$date_query', DATE_FORMAT(t3.ddel_est1,'%Y%m')) > 0 
  $unitTypeCondition
  $modelCondition
  AND (service.dgt_com = '' OR service.dgt_com >= '$date_con-01')
  AND (service.date_shipped = '' OR service.date_shipped >= '$date_con-01')
  $factoryCondition
SQL;

$factoryPromisedCustomerDateBacklog = tldUtils::getSqlRowToAssocArray($query);
$factoryPromisedCustomerDateOffset = (int) $factoryPromisedCustomerDateBacklog['num'];

// Factory Promised Customer Date
$query = <<<SQL
SELECT DATE_FORMAT( t3.ddel_est1, '%Y%m%d' ) AS nam_period, COUNT(*) AS promised_dt
FROM service
  LEFT JOIN sor_units AS t3 ON t3.id=service.sor_uid
WHERE DATE_FORMAT( t3.ddel_est1, '%Y%m' ) LIKE '$date_query'
 $modelCondition
$unitTypeCondition
AND ((service.dgt_com='' AND service.dgt_act = '') OR DATE_FORMAT(service.dgt_com, '%Y%m') LIKE '$date_query')
$factoryCondition
GROUP BY nam_period
SQL;
$rows = array_column(tldUtils::getSqlToAssocArray($query), 'promised_dt', 'nam_period');

$totalizer = $factoryPromisedCustomerDateOffset;
foreach ($d as $date => $value) {
    if (array_key_exists($date, $rows)) {
        $totalizer += (int) $rows[$date];
    }
    $d[$date]['Factory Promised Customer Date (Qty)'] = $totalizer;
}
$specificUnitTypeCondition = str_replace('service.sn', 'qst.unit', $unitTypeCondition);
$specificModelCondition = str_replace(['service.model', 'service.type'], ['s.model', 's.type'], $modelCondition);
$specificFactoryCondition = str_replace('man_location', 's.man_location', $factoryCondition);
// Released Units
$query = <<<SQL
SELECT DATE_FORMAT(max(ans.created_on), '%Y%m%d' ) AS nam_period, COUNT(DISTINCT s.sn) AS released
from pi_questions_unit qst
left join service s ON qst.unit = s.sn
left join pi_crab_eap cr on qst.id = cr.question_id
left join pi_answers ans on qst.id = ans.parent_id and ans.active = 'Y'
Where qst.t_opno = 990 and qst.active = 'Y'
$specificFactoryCondition
$specificModelCondition
$specificUnitTypeCondition
group by qst.unit having max(ans.created_on) LIKE '$date_con%' and (count(qst.id) - count(ans.id)) = 0
order by nam_period
SQL;

$rows = tldUtils::getSqlToAssocArray($query);
$rows = array_reduce($rows, function ($memo, $value) {
    if (!array_key_exists($value['nam_period'], $memo)) {
        $memo[$value['nam_period']] = 0;
    }
    $memo[$value['nam_period']] += (int) $value['released'];

    return $memo;
}, []);

$totalizer = 0;
foreach ($d as $date => $value) {
    if (array_key_exists($date, $rows)) {
        $totalizer += (int) $rows[$date];
    }
    $d[$date]['Released (Qty)'] = $totalizer;
}

// 1st GT
$query = <<<SQL
SELECT DATE_FORMAT( dgt_com, '%Y%m%d' ) AS nam_period, count(*) AS first_gt
FROM service
WHERE DATE_FORMAT( dgt_com, '%Y%m' ) LIKE '$date_query'
$modelCondition
$unitTypeCondition
$factoryCondition
GROUP BY nam_period
SQL;
$rows = array_column(tldUtils::getSqlToAssocArray($query), 'first_gt', 'nam_period');

$totalizer = 0;
foreach ($d as $date => $value) {
    if (array_key_exists($date, $rows)) {
        $totalizer += (int) $rows[$date];
    }
    $d[$date]['1st GT (Qty)'] = $totalizer;
}


$graph->setXAxisCategories(array_keys($d));
$graph->setYAxisFloorValue(0);

$x= [];
foreach(['Estimated GT (Qty)', 'Factory Promised Customer Date (Qty)', 'Released (Qty)', '1st GT (Qty)', 'Estimated GT (Qty set at the begin of the month)'] as $key) {
    $x[$key] = array_column($d, $key, 'Name');
}

foreach ($x AS $key => $avg) {
       $options = ['name' => $key];
       if ($key === 'Estimated GT (Qty)' || $key === 'Released (Qty)') {
           $options['dashStyle'] = 'longdash';
       }
       $graph->addLine(array_slice($avg, 0, count($d)), $options);

}

// Group target
if ($kpiDefinition->hasGroupTarget()) {
    $graph->addGroupTarget($kpiDefinition->getGroupTarget());
}
// Display
$body .= $graph->fetch();

// Add Definition popup
$popup = new tldKpiDefinitionPopup($kpiDefinition);
$body .= $popup->fetch();

$query =<<<SQL
    SELECT service.*, t3.ddel_est1 as promiseddate
    FROM service
           LEFT JOIN sor_units AS t3 ON t3.id=service.sor_uid
    WHERE service.dgt_rev < CURDATE()
      $unitTypeCondition
      $modelCondition
      AND service.dgt_act='' AND service.dgt_com='' AND service.dyt=''
       AND service.dgt_rev <>'' AND service.date_shipped IS NULL
    $factoryCondition
    ORDER BY  service.dgt_rev ASC 
SQL;
$item = [
    "id" => "ER#",
    "sor_uid" => "SOR#",
    "sn" => "S/N",
    "type" => "Type",
    "model" => "Model",
    "man_location" => "Manufacture location",
    "customer_name" => "Customer",
    "sales_org" => "Sales Orgnization",
    "promiseddate" => "Promised Date",
    "dyt" => "Yellow Tag Date",
    "dgt_rev" => "Estimated Green Tag Date",
    "dgt_com" => "First Green Tag Date",
    "dgt_act" => "Actual Green Tag Date",
    "date_shipped" => "Actual Ship Date",
];


$rows = $query ? tldUtils::getSqlToAssocArray($query) : [];

$report = new tldReportColumnar(
    $rows,
    [
        'xItems' => $item,
        'links' => ['id' => '/en/private/product_support/index.ps.php?m[0]=equipment&m[1]=view&id='],
        'title' => 'Late Estimated GT Backlog '.date('Y-m-d'),
        'showNumberOfRows' => true,
    ]
);
$body .= $report->fetch();