<?php
// ---------------------------
//          GT 28+
// ---------------------------

$factoryList = array_column(tldLocation::getFactoryList(), 'location');

if (array_diff($factorySeries, $factoryList)) {
    return;
}


// Definition
$kpiDefinition = new tldKpiDefinition('delivery_gt28',$lang);

// Graph
$graph = new tldGraph();
$graph->setTitle($kpiDefinition->getTitle()." - $factoryLabel");
$graph->setYAxisTitle('% of ER');
$graph->setYAxisMaxValue(100);
// Case of multiple series
foreach($factorySeries as $factory)
{
    $xAxis = $dataSerie = [];
    // Get data & prepare it
    $rows = tldODP::kpi_GTEndOfMonth_ByPeriodByFactory(3,$from,$to,$factory);
    foreach ($rows as $row)
    {
        $xAxis[] = $row['month'];
        $dataSerie[] = (int)$row['val'];
    }
    $graph->setXAxisCategories($xAxis);
    $graph->addBar($dataSerie,['name'=>$factory]);
}
// Group target
if($kpiDefinition->hasGroupTarget())
{
    $graph->addGroupTarget($kpiDefinition->getGroupTarget());
}
// Display
$body.= $graph->fetch();

// Add Definition popup
$popup = new tldKpiDefinitionPopup($kpiDefinition);
$body.= $popup->fetch();


// ---------------------------
//          GT 20+
// ---------------------------

// Definition
$kpiDefinition = new tldKpiDefinition('delivery_gt20',$lang);

// Graph
$graph = new tldGraph();
$graph->setTitle($kpiDefinition->getTitle()." - $factoryLabel");
$graph->setYAxisTitle('% of ER');
$graph->setYAxisMaxValue(100);
// Case of multiple series
foreach($factorySeries as $factory)
{
    $xAxis = $dataSerie = [];
    // Get data & prepare it
    $rows = tldODP::kpi_GTEndOfMonth_ByPeriodByFactory(10,$from,$to,$factory);
    foreach ($rows as $row)
    {
        $xAxis[] = $row['month'];
        $dataSerie[] = (int)$row['val'];
    }
    $graph->setXAxisCategories($xAxis);
    $graph->addBar($dataSerie,['name'=>$factory]);
}
// Group target
if($kpiDefinition->hasGroupTarget())
{
    $graph->addGroupTarget($kpiDefinition->getGroupTarget());
}
// Display
$body.= $graph->fetch();

// Add Definition popup
$popup = new tldKpiDefinitionPopup($kpiDefinition);
$body.= $popup->fetch();


// ---------------------------
//        OTDP Factory
// ---------------------------


// Definition
$kpiDefinition = new tldKpiDefinition('delivery_otdpFactory',$lang);

// Graph
$graph = new tldGraph();
$graph->setTitle($kpiDefinition->getTitle()." - $factoryLabel");
$graph->setYAxisTitle('% of ER');
$graph->setYAxisMaxValue(100);
// Case of multiple series
foreach($factorySeries as $factory)
{
    $xAxis = $dataSerie = [];
    // Get data & prepare it
    $rows = tldODP::kpi_otdpGTvsPromise_ByPeriodByFactory($from,$to,$factory);
    foreach ($rows as $row)
    {
        $xAxis[] = $row['month'];
        $dataSerie[] = (int)$row['val'];
    }
    $graph->setXAxisCategories($xAxis);
    $graph->addBar($dataSerie,['name'=>$factory]);
}
// Group target
if($kpiDefinition->hasGroupTarget())
{
    $graph->addGroupTarget($kpiDefinition->getGroupTarget());
}
// Display
$body.= $graph->fetch();

// Add Definition popup
$popup = new tldKpiDefinitionPopup($kpiDefinition);
$body.= $popup->fetch();


// ------------------------------------
//      OTDP Factory AVG Days late
// ------------------------------------


// Definition
$kpiDefinition = new tldKpiDefinition('delivery_otdpFactoryAVGLate',$lang);

// Graph
$graph = new tldGraph();
$graph->setTitle($kpiDefinition->getTitle()." - $factoryLabel");
$graph->setYAxisTitle('Nb of days');
// Case of multiple series
foreach($factorySeries as $factory)
{
    $xAxis = $dataSerie = [];
    // Get data & prepare it
    $rows = tldODP::kpi_otdpAVGdaysLate_ByPeriodByFactory($from,$to,$factory);
    foreach ($rows as $row)
    {
        $xAxis[] = $row['month'];
        $dataSerie[] = (int)$row['val'];
    }
    $graph->setXAxisCategories($xAxis);
    $graph->addBar($dataSerie,['name'=>$factory]);
}
// Group target
if($kpiDefinition->hasGroupTarget())
{
    $graph->addGroupTarget($kpiDefinition->getGroupTarget());
}
// Display
$body.= $graph->fetch();

// Add Definition popup
$popup = new tldKpiDefinitionPopup($kpiDefinition);
$body.= $popup->fetch();
