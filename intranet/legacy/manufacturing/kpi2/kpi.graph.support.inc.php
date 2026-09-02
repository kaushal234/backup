<?php
//  SOL
$kpiDefinition = new tldKpiDefinition('support_WCByModel',$lang);
$bu = $_SESSION['kpi']['form']['bu_id'];
// Graph
$graph = new tldGraph();
$graph->setTitle($kpiDefinition->getTitle());
$graph->setYAxisTitle('Total of Warranties');

$xAxis = [];
$start = (new DateTime($from))->modify('first day of this month');
$end = (new DateTime($to))->modify('first day of next month');
foreach (new DatePeriod($start, new DateInterval('P1M'), $end) as $dt) {
    $xAxis[]  = $dt->format("Ym");
}

// Case of multiple series
foreach ($factorySeries as $factory) {
    $xAxis = $dataSerie = [];
    // Get data & prepare it
    $data = [];

    $WHERE = "";

    $query = <<<EOF
SELECT IF(model='', 'NO_MODEL', model) AS model,
	DATE_FORMAT(claim_date, '%Y-%m') AS display_date,
	COUNT(*) AS cnt
FROM warranty
WHERE claim_date BETWEEN '$from' AND '$to'
    AND man_location = '$factory'
GROUP BY model, display_date ORDER BY model, display_date
EOF;

//ORDER BY claim_date, model
    $rows = tldUtils::getSqlToAssocArray($query);
    $date1_stamp=strtotime($from);
    $date2_stamp=strtotime($to);
    list($date_1['y'],$date_1['m'])=explode("-",date('Y-m',$date1_stamp));
    list($date_2['y'],$date_2['m'])=explode("-",date('Y-m',$date2_stamp));
    $yf= abs($date_2['y']-$date_1['y'])*12 +$date_2['m']-$date_1['m']+1;
    $monarr[] = date('Y-m',$date1_stamp);
    while( ($date1_stamp = strtotime('+1 month', $date1_stamp)) <= $date2_stamp){
        $monarr[] = date('Y-m',$date1_stamp); // 取得递增月;
    }
    foreach ($rows as $v){
        $model=$v['model'];
        if(!isset($memo[$model])){
            $memo[$model] = array_fill(0, $yf, null);
        }
    }

    foreach($monarr as $key=> $date){
        foreach ($rows as $v){
            $model=$v['model'];
            if($v['display_date']==$date){
                $memo[$model][$key]=(int)$v['cnt'];
            }
        }
    }

    $graph->setXAxisCategories($xAxis);
    foreach ($memo as $model => $data) {
        $graph->addNamedSerie($model, $data, ["name" => $model,'type'=>'column','dataLabels'=>['enabled'=>true],
            'enableMouseTracking'=>true]);
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

/////*
///
///
///
///    */
$kpiDefinition = new tldKpiDefinition('support_WCByType',$lang);
$bu = $_SESSION['kpi']['form']['bu_id'];
// Graph
$graph = new tldGraph();
$graph->setTitle($kpiDefinition->getTitle());
$graph->setYAxisTitle('Total of Warranties');
// Case of multiple series
foreach ($factorySeries as $factory) {
    $dataSerie = [];
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
        $dataSerie[] = $period_Ym;
    }
    $WHERE = "";

    $query = <<<EOF
SELECT type,
	DATE_FORMAT(claim_date, '%Y-%m') AS display_date,
	COUNT(*) AS cnt
FROM warranty
WHERE claim_date BETWEEN '$from' AND '$to'
    AND man_location = '$factory'
GROUP BY type, display_date ORDER BY type, display_date
EOF;

//ORDER BY claim_date, type
    $rows = tldUtils::getSqlToAssocArray($query);
    $date1_stamp=strtotime($from);
    $date2_stamp=strtotime($to);
    list($date_1['y'],$date_1['m'])=explode("-",date('Y-m',$date1_stamp));
    list($date_2['y'],$date_2['m'])=explode("-",date('Y-m',$date2_stamp));
    $yf= abs($date_2['y']-$date_1['y'])*12 +$date_2['m']-$date_1['m']+1;
    $montharr[] = date('Y-m',$date1_stamp);
    while( ($date1_stamp = strtotime('+1 month', $date1_stamp)) <= $date2_stamp){
        $montharr[] = date('Y-m',$date1_stamp); // 取得递增月;
    }
    foreach ($rows as $v){
        $type=$v['type'];
        if(!isset($warranty[$type])){
            $warranty[$type] = array_fill(0, $yf, null);
        }
    }

    foreach($montharr as $key=> $date){
        foreach ($rows as $v){
            $type=$v['type'];
            if($v['display_date']==$date){
                $warranty[$type][$key]=(int)$v['cnt'];
            }
        }
    }

    $graph->setXAxisCategories($dataSerie);
    foreach ($warranty as $type => $data) {
        $graph->addNamedSerie($type, $data, ["name" => $type,'type'=>'column','stacking' => 'normal','dataLabels'=>['enabled'=>true],
            'enableMouseTracking'=>true]);
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


//////////////////////////////////////////////////////////////////////////*

/// // pas 5 years
///
///   */




$kpiDefinition = new tldKpiDefinition('support_WCByYear',$lang);
$bu = $_SESSION['kpi']['form']['bu_id'];
// Graph
$graph = new tldGraph();
$graph->setTitle($kpiDefinition->getTitle());
$graph->setYAxisTitle('Total of Warranties');

// Case of multiple series
foreach ($factorySeries as $factory) {
    $xAxis = $dataSerie = [];
    // Get data & prepare it
    $data = [];
    // Get 12 months windows

    $query = <<<EOF
    SELECT count( * ) AS cnt,
        YEAR(claim_date) AS display_date
    FROM warranty
    WHERE YEAR(claim_date) > YEAR('$to')-5
    AND man_location = '$factory'
    GROUP BY display_date
EOF;
    $rows = tldUtils::getSqlToAssocArray($query);

    foreach ($rows as $unit) {
        $warranty[$unit["display_date"]]['nb'] +=$unit["cnt"];
        $xAxis[] = $unit["display_date"];
    }

    $graph->setXAxisCategories($xAxis);
    $graph->addSeries(array_column($warranty, "nb"), ["name" => $factory,'type'=>'column','dataLabels'=>['enabled'=>true],
        'enableMouseTracking'=>true]);


}



// Display
$body .= $graph->fetch();
