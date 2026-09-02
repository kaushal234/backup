<?php
//  production
$kpiDefinition = new tldKpiDefinition('production_industrialnetsales',$lang);
$bu = $_SESSION['kpi']['form']['bu_id'];
// Graph
$graph = new tldGraph();
$graph->setTitle($kpiDefinition->getTitle());
$graph->setYAxisTitle('Net sales / Productive hours');
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
// Case of multiple series
foreach ($factorySeries as $factory) {
    // Get data & prepare it
    $productive = $data = $xAxis = $dataSerie = [];
    // Foreach months, calculate KPI
    foreach ($graph_period as $period) {
        $period_Ym = $period['xval'];
        $period_Ymd = substr($period['xval'], 0, 4) . "-" . substr($period['xval'], -2, 2) . "-01";
        $xAxis[] = $period_Ym;
    }

    $ERP = tldLocation::getERPByLocation($factory);
    $buid = tldLocation::getIDByERP($ERP);

    $query = <<<EOF
        SELECT ROUND(if(phr_proh=0, 0, erp_tot_sls/phr_proh),2) as yval,
            DATE_FORMAT(CONCAT(ynam,'-',mnam,'-01'), '%Y-%m') AS xval
        FROM fin_kpi AS T1
        WHERE T1.buid = $buid AND DATE_FORMAT(CONCAT(ynam,'-',mnam,'-01'), '%Y-%m-%d') BETWEEN '$from' AND '$to'
        
EOF;

//ORDER BY claim_date, model
    $rows = tldUtils::getSqlToAssocArray($query);
    foreach ($rows as $unit) {
        $productive[$unit["xval"]]['nb'] +=$unit["yval"];
        $xAxis[] = $unit["xval"];
    }

    $graph->setXAxisCategories($xAxis);
    $graph->addSeries(array_column($productive, "nb"), ["name" => $factory,'type'=>'column','dataLabels'=>['enabled'=>true],
        'enableMouseTracking'=>true]);
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
$kpiDefinition = new tldKpiDefinition('production_industrialnetsalessurface',$lang);
$bu = $_SESSION['kpi']['form']['bu_id'];
// Graph
$graph = new tldGraph();
$graph->setTitle($kpiDefinition->getTitle());
$graph->setYAxisTitle('Net sales / ∑ Work surface');
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
// Case of multiple series
foreach ($factorySeries as $factory) {
    // Get data & prepare it
    $productivity = $data = $xAxis = $dataSerie = [];
    // Foreach months, calculate KPI
    foreach ($graph_period as $period) {
        $period_Ym = $period['xval'];
        $period_Ymd = substr($period['xval'], 0, 4) . "-" . substr($period['xval'], -2, 2) . "-01";
        $xAxis[] = $period_Ym;
    }

    $ERP = tldLocation::getERPByLocation($factory);
    $buid = tldLocation::getIDByERP($ERP);

    $query = <<<EOF
        SELECT ROUND(if(workshop_surface=0, 0, erp_tot_sls/workshop_surface),2) as yval,
            DATE_FORMAT(CONCAT(ynam,'-',mnam,'-01'), '%Y-%m') AS xval
        FROM fin_kpi AS T1
        WHERE T1.buid = $buid AND DATE_FORMAT(CONCAT(ynam,'-',mnam,'-01'), '%Y-%m-%d') BETWEEN '$from' AND '$to'
        
EOF;


    $rows = tldUtils::getSqlToAssocArray($query);
    foreach ($rows as $val) {
        $productivity[$val["xval"]]['nb'] +=$val["yval"];
        $xAxis[] = $val["xval"];
    }

    $graph->setXAxisCategories($xAxis);
    $graph->addSeries(array_column($productivity, "nb"), ["name" => $factory,'type'=>'column','dataLabels'=>['enabled'=>true],
        'enableMouseTracking'=>true]);
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
$kpiDefinition = new tldKpiDefinition('production_whse&SFE',$lang);
$bu = $_SESSION['kpi']['form']['bu_id'];
// Graph
$graph = new tldGraph();
$graph->setTitle($kpiDefinition->getTitle());
$graph->setYAxisTitle('Qty of WHSE / Qty of SFE');
// Case of multiple series
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
// Case of multiple series
foreach ($factorySeries as $factory) {
    // Get data & prepare it
    $productives = $data = $xAxis = $dataSerie = [];
    // Foreach months, calculate KPI
    foreach ($graph_period as $period) {
        $period_Ym = $period['xval'];
        $period_Ymd = substr($period['xval'], 0, 4) . "-" . substr($period['xval'], -2, 2) . "-01";
        $xAxis[] = $period_Ym;
    }

    $ERP = tldLocation::getERPByLocation($factory);
    $buid = tldLocation::getIDByERP($ERP);

    $query = <<<EOF
        SELECT ROUND(if((nb_sgl/2+nb_sfe)=0, 0, (nb_whsekeeper+nb_wgl/2)*100/(nb_sgl/2+nb_sfe)),2) as yval,
            DATE_FORMAT(CONCAT(ynam,'-',mnam,'-01'), '%Y-%m') AS xval
        FROM fin_kpi AS T1
        WHERE T1.buid = $buid AND DATE_FORMAT(CONCAT(ynam,'-',mnam,'-01'), '%Y-%m-%d') BETWEEN '$from' AND '$to'
        
EOF;

    $rows = tldUtils::getSqlToAssocArray($query);
    foreach ($rows as $row) {
        $productives[$row["xval"]]['nb'] +=$row["yval"];
        $xAxis[] = $row["xval"];
    }

    $graph->setXAxisCategories($xAxis);
    $graph->addSeries(array_column($productives, "nb"), ["name" => $factory,'type'=>'column','dataLabels'=>['enabled'=>true],
        'enableMouseTracking'=>true]);
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
