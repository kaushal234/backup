<?php
include_once 'sales_service.inc.php';

// ---------------------------
//    CSR Commissioning
// ---------------------------

// Definition
$kpiDefinition = new tldKpiDefinition('quality_perfectCommissioning',$lang);

// Graph
$graph = new tldGraph();
$graph->setTitle($kpiDefinition->getTitle()." - $factoryLabel");
$models = [];
if ($_SESSION['kpi']['graph'] === 'Quality' && count($factorySeries) === 1){
    $models = $_SESSION['kpi']['form']["models_". $bu->getID()];
}


// -------------------------
//         Matrix
// -------------------------

$twelveMonthAgo = (new DateTime('12 month ago'))->modify('first day of this month')->format('Y-m-d');
$sixMonthAgo = (new DateTime('6 month ago'))->modify('first day of this month')->format('Y-m-d');
$end = (new DateTime('now'))->format('Y-m-d');
$beginningOfPreviousMonth = (new DateTime('first day of last month'))->format('Y-m-d');
$endOfPreviousMonth = (new DateTime('last day of last month'))->format('Y-m-d');
$factory = count($factorySeries) > 1 ? 'ALL' : $bu->getBuName();

$dataOneYear = tldCSR::kpi_perfectCommissioning_byPeriodByFactory($twelveMonthAgo, $end, $factory, $models, true);
$dataSixMonths = tldCSR::kpi_perfectCommissioning_byPeriodByFactory($sixMonthAgo, $end, $factory, $models, true);
$dataOneMonths = tldCSR::kpi_perfectCommissioning_byPeriodByFactory($beginningOfPreviousMonth, $endOfPreviousMonth, $factory, $models, true, false);

$matrixData = [];
foreach ([
        '12 months rolling average' => $dataOneYear[0],
        '6 months rolling average' => $dataSixMonths[0],
        'Previous month rolling average' => $dataOneMonths[0],
    ] as $key => $data) {
    $value = null === $data['val'] ? 0 : round($data['val']);
    $nbTotal = null === $data['nb_commissioned'] ? 0 : $data['nb_commissioned'];

    $matrixData[] = [
        'time' => sprintf('%s (perfect score(%%) / total)', $key),
        'value' => sprintf('%s%% / %s', $value, $nbTotal),
    ];
}

$matrix = new tldMatrix(
    $matrixData,
    '', "time", "value",
    null,
    "Commissioning Scores",
    ['doNotShowTotals' => true, 'doNotFormatCells' => true]
);

$body .= $matrix->fetch();

// -------------------------
//         Graph
// -------------------------

// Case of multiple series
foreach($factorySeries as $factory)
{
    $xAxis = $nbPerfectCommissioning = $nbCommissioning = [];
    // Get data & prepare it

    $start = (new DateTime($from))->modify('first day of this month');
    $end = (new DateTime($to))->modify('first day of next month');
    $interval = DateInterval::createFromDateString('1 month');
    $period  = new DatePeriod($start, $interval, $end);
    $data = [];

    foreach ($period as $dt) {
        $month['factory'] = $factory;
        $month['month'] = $dt->format("Ym");
        $month['val'] = 0;
        $month['nb_commissioned'] = 0;
        $data[$dt->format("Ym")] = $month;
    }

    $rows = tldCSR::kpi_perfectCommissioning_byPeriodByFactory($from,$to,$factory, $models);
    foreach ($rows as $row) {
        if (isset($data[$row['month']])) {
            $data[$row['month']] = $row;
        }

        $data[$row['month']]['list_sn'] = implode(array_map(static function ($serial) {
            return '<b>'.$serial.'</b></br>';
        }, explode(',', $row['list_sn'] ?? '')));
    }

    foreach ($data as $row)
    {
        $xAxis[] = $row['month'];
        $nbPerfectCommissioning[] = (int)$row['val'];
        $nbCommissioning[] = (int)$row['nb_commissioned'];
        $listSerials[] =  $row['list_sn'];
    }

    $graph->setXAxisCategories($xAxis);
    $graph->addBar($nbPerfectCommissioning, ['name'=>$factory.' noted 5/5/5 (in %)', "enableMouseTracking" => true, "stickyTracking" => false]);
    $graph->addLine($nbCommissioning, ['name'=>$factory.' Total commissioned']);
    $graph->addSeries($listSerials, ['name' => 'sns', 'visible' => false, 'showInLegend' => false]);
    $graph->setTooltip(true, ['useHTML' => true]);
}
// Group target
if($kpiDefinition->hasGroupTarget())
{
    $graph->addGroupTarget($kpiDefinition->getGroupTarget());
}
// Display
$body.= $graph->fetch('csr-graph');

// Add Definition popup
$popup = new tldKpiDefinitionPopup($kpiDefinition);
$body .= $popup->fetch();

$js = <<<JS
$(document).ready(function() {
    $('#csr-graph').highcharts().tooltip.options.formatter = function() {
        return ('<div style="max-height: 350px; overflow-y: scroll;"> SN:</br>' + $('#csr-graph').highcharts().options.series[2].data[this.point.index] + '</div>');
    }
});
JS;
$smarty->assign("html_head", $smarty->get_template_vars("html_head") .'<script type="text/javascript">' . $js . "</script>");

// ---------------------------
//    WC average KPI
// ---------------------------

$kpiDefinition = new tldKpiDefinition('quality_avgWC', $lang);
$body.= "<div id='wc-average'></div>";
// Graph
$graph = new tldGraph();
$graph->setTitle($kpiDefinition->getTitle() . " - $factoryLabel");
// Case of multiple series
foreach ($factorySeries as $factory) {
    $xAxis = $dataSerie = [];
    // Get data & prepare it
    $data = [];
    // Get 12 months windows
    $query = <<<EOF
SELECT nam_period AS xval
FROM fin_periods AS p
WHERE
    PERIOD_DIFF( DATE_FORMAT('$from','%Y%m'), p.nam_period )<=0
    AND PERIOD_DIFF( DATE_FORMAT('$to','%Y%m'), p.nam_period )>=0
ORDER BY nam_period
EOF;

    $graph_period = tldUtils::getSqlToAssocArray($query);

    // Foreach months, calculate KPI
    foreach ($graph_period as $period) {
        $period_Ym = $period['xval'];
        $period_Ymd = substr($period['xval'], 0, 4) . "-" . substr($period['xval'], -2, 2) . "-01";

        if ($factory <> "ALL") {
            $WHERE = "er.man_location='$factory'";
            if (!empty($models)) {
                $WHERE = sprintf('%s AND er.model IN ("%s")', $WHERE, implode('", "', $models));
            }
            $rows = tldWC::getAVGWCPeriodByConstraints($period_Ymd, $WHERE);
            $avgWC[] = (float)$rows['val'];
            $dataSerie[] = (int)$rows['val'];
            $xAxis[] = $period_Ym;
        }
        if ($factoryLabel == "ALL") {
            $avgWC[$factory][] = (float)$rows['val'];
        }
    }

    $graph->setXAxisCategories($xAxis);
    if ($factoryLabel == "ALL") {
        $graph->addLine($avgWC[$factory], ['name' => $factory . ' WC - Average number of WC per Machine, shipped < 2 years']);
    } else {
        $graph->addLine($avgWC, ['name' => $factory . ' WC - Average number of WC per Machine, shipped < 2 years']);
    }
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

// ---------------------------
//    WC byType average KPI
// ---------------------------

$kpiDefinition = new tldKpiDefinition('quality_avgWC', $lang);

// Graph
$graph = new tldGraph();
$graph->setTitle($kpiDefinition->getTitle() . " - Per Type - $factoryLabel");

$types = [];
// Case of multiple series
foreach ($factorySeries as $factory) {
    $xAxis = $dataSerie = [];
    // Get data & prepare it
    $data = [];
    // Get 12 months windows
    $query = <<<EOF
SELECT nam_period AS xval
FROM fin_periods AS p
WHERE
    PERIOD_DIFF( DATE_FORMAT('$from','%Y%m'), p.nam_period )<=0
    AND PERIOD_DIFF( DATE_FORMAT('$to','%Y%m'), p.nam_period )>=0
ORDER BY nam_period
EOF;

    $graph_period = tldUtils::getSqlToAssocArray($query);
    // Foreach months, calculate KPI
    foreach ($graph_period as $period) {
        $period_Ym = $period['xval'];
        $period_Ymd = substr($period['xval'], 0, 4) . "-" . substr($period['xval'], -2, 2) . "-01";
        $xAxis[] = $period_Ym;
        $WHERE = "";
        if ($factoryLabel <> "ALL") {
            $WHERE = "er.man_location='$factory'";
        }
        if ($factory === 'TLD STL' && $factoryLabel <> "ALL") {
            $WHERE = $WHERE.' AND er.type <> "Chassis" AND er.type <> "Catering Trucks and Derivatives" ';
        }
        if ($factory === 'TLD STL' && $factoryLabel === "ALL") {
            $WHERE = $WHERE.' er.type <> "Chassis" AND er.type <> "Catering Trucks and Derivatives" ';
        }
        $rows = tldWC::getAVGWCPeriodByConstraints($period_Ymd, $WHERE, 1);
        $results[] = $rows;
        foreach ($rows as $row) {
            if (!in_array($row['type'], $types)) {
                $types[] = $row['type'];
            }
        }
    }
    $graph->setXAxisCategories($xAxis);
}

foreach ($results as $key => $result) {
    foreach ($types as $type) {
        if (!in_array($type, array_column($result, 'type'))) {
            $results[$key][] = ['type' => $type, 'val' => 0];
        }
    }
}

foreach ($results as $result) {
    foreach ($result as $item) {
        $avgWCs[$item['type']][] = (float)$item['val'];
    }
}

foreach ($avgWCs AS $key => $avg) {
    if (isset($avg)) $graph->addLine(array_slice($avg, 0, count($xAxis)), ['name' => $key]);
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
