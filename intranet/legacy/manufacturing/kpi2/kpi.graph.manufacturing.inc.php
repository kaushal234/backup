<?php

$bu = $_SESSION['kpi']['form']['bu_id'];
$graphSelected = $_SESSION['kpi']['form']['graph'];
if ($bu === "ALL") {
    $DEFAULT_ERROR[] = "'ALL' is not a valid value for this KPI.";
    return;
}
$nb = count($_SESSION['kpi']['form']["models_". $bu]);
$nbType = count($_SESSION['kpi']['form']["types_". $bu]);

if (($nb === 0 || $nb > 10) && $graphSelected === 'Manufacturing - by product'){
    $DEFAULT_ERROR[] = "You need at least one model and not more than 10.";
    return;
} elseif  (($nbType === 0 || $nbType > 2) && $graphSelected === 'Manufacturing - by type'){
    $DEFAULT_ERROR[] = "You need at least one type and not more than 2.";
    return;
}

$graph = new tldGraph();
$graph->setTitle('LEAD-TIME');
$graph->setXAxisTitle('Months');
$graph->setMultipleYAxisTitle("Days");
$graph->setMultipleYAxisTitle("Number of Machines",array("opposite"=>true));

$months = [];
$valBarLeadTime = [];
$valBarPassageTime = [];
$valBarCycleTime = [];
for ($dateStart = new DateTime($_SESSION['kpi']['form']['dt_from']),
     $dateStop = new DateTime($_SESSION['kpi']['form']['dt_to']);
     $dateStart <= $dateStop;
     $dateStart->add(DateInterval::createFromDateString('1 months'))) {
    $m = date_format($dateStart,"M");
    $months[] = $m;
    $valBarLeadTime[$m]["val"] = 0;
    $valBarLeadTime[$m]["nb"] = 0;
    $valBarLeadTime[$m]["units"] = [];
    $valBarPassageTime[$m]["val"] = 0;
    $valBarPassageTime[$m]["nb"] = 0;
    $valBarPassageTime[$m]["units"] = [];
    $valBarCycleTime[$m]["val"] = 0;
    $valBarCycleTime[$m]["nb"] = 0;
    $valBarCycleTime[$m]["units"] = [];
}
$graph->setXAxisCategories($months);

if ($_SESSION['kpi']['graph'] === 'Manufacturing - by type'){
    $model = implode("','",$_SESSION['kpi']['form']["types_". $bu]);
    $filter = 'service.type';
} else {
    $model = implode("','",$_SESSION['kpi']['form']["models_". $bu]);
    $filter = 'service.model';
}


    $dateStart = (new DateTime($_SESSION['kpi']['form']['dt_from']))->format('Y-m-d');
    $dateStop = (new DateTime($_SESSION['kpi']['form']['dt_to']))->add(DateInterval::createFromDateString('1 months'))->sub(DateInterval::createFromDateString('1 days'))->format('Y-m-d');
    $query=<<<SQL
    select DATE_FORMAT(dgt_com, '%Y-%m') AS 'month', service.sn, DATEDIFF(dgt_com, mod_logs.date) AS 'days', service.dgt_com, service.t_prno
    from service
      join sor_units on service.sor_uid = sor_units.id
      join locations on locations.location=service.man_location
      join sor_lines on sor_units.parent_id = sor_lines.id
      join mod_logs on mod_logs.id = (SELECT id FROM mod_logs
      WHERE sor_lines.id = mod_logs.parent_id AND mod_logs.module = 'SOL' AND comment like '%CREATE_FACTORY_SO%' ORDER BY mod_logs.date ASC LIMIT 1)
    WHERE dgt_com BETWEEN CAST('$dateStart' AS DATE) AND CAST('$dateStop' AS DATE)
      AND $filter IN ('$model')
      AND locations.id = $bu
    ORDER BY month ASC, sn ASC
SQL;

    $res = tldUtils::getSqlToAssocArray($query);

$valGt = [];
foreach ($res as $unit) {
    $m = date_format(new DateTime($unit["month"]),"M");
    $valBarLeadTime[$m]["val"] += $unit["days"];
    $valBarLeadTime[$m]["nb"] += 1;
    $valBarLeadTime[$m]["units"] = $valBarLeadTime[$m]["units"] . "<b>" . $unit["sn"] ."</b> : ". $unit["days"] . " days</br>" ;
    $valBarLeadTime[$m]["unit"][$unit["t_prno"]] = $unit["sn"];
    $valGt["project"][] = $unit["t_prno"];
    $valGt[$unit["t_prno"]] = $unit["dgt_com"];
    $valGt["units"][] = $unit["sn"];
}

foreach ($valBarLeadTime as $m=> $bar) {
    if ($bar["nb"] !== null && $bar["nb"] !== 0 && $bar["val"] !== null) {
        $valBarLeadTime[$m]["val"] = round($bar["val"] / $bar["nb"]);
    }
}

if ($nb < 2) {
    $graph->addLine(array_column($valBarLeadTime, "val"), ['name' => $model . ' Average lead-time', "zIndex" => 2]);
    $graph->addBar(array_column($valBarLeadTime, "nb"), ['name' => $model . ' GT', "enableMouseTracking" => true,
        "stickyTracking" => false, "zIndex" => 1]);
} else {
    $display = str_replace("'","",$model);
    $graph->addLine(array_column($valBarLeadTime, "val"), ['name' => $display . ' Average lead-time', "zIndex" => 2]);
    $graph->addBar(array_column($valBarLeadTime, "nb"), ['name' => $display . ' GT', "enableMouseTracking" => true,
        "stickyTracking" => false, "zIndex" => 1]);
}
$graph->addSeries(array_column($valBarLeadTime, "units"), ["name" => "units", "visible" => false, "showInLegend" => false]);
$graph->setTooltip(true,["useHTML"=>true]);

//$graph->addGroupTarget(16);
$leadGraph = $graph->fetch("graph-1");

$js = <<<JS
$(document).ready(function() {
    $('#graph-1').highcharts().tooltip.options.formatter = function() {
        return ('<div style="max-height: 350px;;overflow-y: scroll;"> Units:</br>' + $('#graph-1').highcharts().options.series[2].data[this.point.index] + '</div>');
    }
});
JS;
$smarty->assign("html_head", $smarty->get_template_vars("html_head") .'<script type="text/javascript">' . $js . "</script>");

$def = <<<HTML
<a href="javascript:void(0);" 
onmouseover="javascript:overlib('<p><b>What it does measure exactly?</b>' +
 '<br>This is what the customers and the SSO perceive from the factories in term of performance. The backlog impacts negatively this KPI.</p>' +
  '<p><b>How it is generated?</b>' +
   '<br>In the TLD language, this is the time between the SOL is at factory level (CREATE_FACTORY_SO) until the GT date.</p>',
    CAPTION,'Lead-Time : The time from factory order reception to the GT date (in days)',
    WIDTH,'500',OFFSETX,50,VAUTO,FGCOLOR,'#eeeeee',BGCOLOR,'gray',CAPCOLOR,'#dedede');"
     onmouseout="javascript:nd();">Definition: LEAD TIME</a></br></br></br></br>
HTML;
$leadDef = $def;

$graph = new tldGraph();
$graph->setTitle('PASSAGE TIME');
$graph->setXAxisTitle('Months');
$graph->setXAxisCategories($months);
$graph->setMultipleYAxisTitle("Days");
$graph->setMultipleYAxisTitle("Number of Machines",array("opposite"=>true));
$bu = $_SESSION['kpi']["bu"];
$erp = $bu->getERP();

$query=<<<SQL
select maxDate.unit AS 'sn', stopDate, DATEDIFF(stopDate, startDate) AS 'days', startDate
FROM (select unit, MAX(pi_answers.created_on) as 'stopDate', count(pi_questions_unit.id) as 'questions', count(pi_answers.id) as 'answers'
      from pi_questions_unit
        left join pi_answers on pi_answers.parent_id=pi_questions_unit.id
        join service on pi_questions_unit.unit=service.sn
      where comp=$erp and t_opno=995
            AND $filter IN ('$model')
            AND ((pi_answers.created_on BETWEEN CAST('$dateStart' AS DATE) AND CAST('$dateStop' AS DATE)) OR (pi_answers.id IS NULL))
            AND pi_questions_unit.active = 'Y' AND pi_answers.active = 'Y'
      GROUP BY unit
      HAVING questions = answers) maxDate
  JOIN (select unit, MIN(pi_answers.created_on) as 'startDate'
        from pi_questions_unit
          join pi_answers on pi_answers.parent_id=pi_questions_unit.id
          join service on pi_questions_unit.unit=service.sn
        where comp=$erp and t_opno > 1 AND t_opno < 995
              AND $filter IN ('$model')
        GROUP BY unit) minDate on minDate.unit=maxDate.unit
order by maxDate.unit ASC
SQL;
$result = tldUtils::getSqlToAssocArray($query);

foreach ($result as $unit) {
    $m = date_format(new DateTime($unit["stopDate"]),"M");
    $valBarPassageTime[$m]["val"] += $unit["days"];
    $valBarPassageTime[$m]["nb"] += 1;
    $valBarPassageTime[$m]["units"] = $valBarPassageTime[$m]["units"] . "<b>" . $unit["sn"] ."</b> : ". $unit["days"] . " days</br>" ;
}

foreach ($valBarPassageTime as $m=> $bar) {
    if ($bar["nb"] !== null && $bar["nb"] !== 0 && $bar["val"] !== null) {
        $valBarPassageTime[$m]["val"] = round($bar["val"] / $bar["nb"]);
    }
}

if ($nb < 2) {
    $graph->addLine(array_column($valBarPassageTime, "val"), ['name' => $model . ' Average passage time', "zIndex" => 2]);
    $graph->addBar(array_column($valBarPassageTime, "nb"), ['name' => $model . ' GT', "enableMouseTracking" => true,
        "stickyTracking" => false, "zIndex" => 1]);
} else {
    $display = str_replace("'","",$model);
    $graph->addLine(array_column($valBarPassageTime, "val"), ['name' => $display . ' Average passage time', "zIndex" => 2]);
    $graph->addBar(array_column($valBarPassageTime, "nb"), ['name' => $display . ' GT', "enableMouseTracking" => true,
        "stickyTracking" => false, "zIndex" => 1]);
}
$graph->addSeries(array_column($valBarPassageTime, "units"), ["name" => "units", "visible" => false, "showInLegend" => false]);
$graph->setTooltip(true,["useHTML"=>true]);

$passageGraph = $graph->fetch("graph-2");

$js = <<<JS
$(document).ready(function() {
    $('#graph-2').highcharts().tooltip.options.formatter = function() {
        return ('<div style="max-height: 350px;;overflow-y: scroll;"> Units:</br>' + $('#graph-2').highcharts().options.series[2].data[this.point.index] + '</div>');
    }
});
JS;
$smarty->assign("html_head", $smarty->get_template_vars("html_head") .'<script type="text/javascript">' . $js . "</script>");

$def = <<<HTML
<a href="javascript:void(0);"
onmouseover="javascript:overlib('<p><b>What it does measure exactly?</b>' +
 '<br>This is the real performance of the factory in term of WIP management (Work in Progress). A longer PT increases directly the WIP and hence the working capital.</p>' +
  '<p><b>How it is generated?</b>' +
   '<br>In the TLD language, this is the time between the first hours logged onto the work order of a unit until the GT date.</p>',
    CAPTION,'PASSAGE TIME : The number of days it takes to build a unit from beginning of assembly to GT.',
    WIDTH,'500',OFFSETX,50,VAUTO,FGCOLOR,'#eeeeee',BGCOLOR,'gray',CAPCOLOR,'#dedede');"
     onmouseout="javascript:nd();">Definition: PASSAGE TIME</a></br></br></br></br>
HTML;
$passageDef = $def;

$graph = new tldGraph();
$graph->setTitle('CYCLE TIME');
$graph->setXAxisTitle('Months');
$graph->setXAxisCategories($months);
$graph->setMultipleYAxisTitle("Days");
$graph->setMultipleYAxisTitle("Number of Machines",array("opposite"=>true));
$query=<<<SQL
select maxDate.unit AS 'sn', stopDate, DATEDIFF(stopDate, startDate) AS 'days'
FROM (select unit, MAX(pi_answers.created_on) as 'stopDate', count(pi_questions_unit.id) as 'questions', count(pi_answers.id) as 'answers'
      from pi_questions_unit
        left join pi_answers on pi_answers.parent_id=pi_questions_unit.id
        join service on pi_questions_unit.unit=service.sn
      where comp=$erp and t_opno=970
            AND $filter IN ('$model')
            AND ((pi_answers.created_on BETWEEN CAST('$dateStart' AS DATE) AND CAST('$dateStop' AS DATE)) OR (pi_answers.id IS NULL))
            AND pi_questions_unit.active = 'Y' AND pi_answers.active = 'Y'
      GROUP BY unit
      HAVING questions = answers) maxDate
  JOIN (select unit, MIN(pi_answers.created_on) as 'startDate'
        from pi_questions_unit
          join pi_answers on pi_answers.parent_id=pi_questions_unit.id
          join service on pi_questions_unit.unit=service.sn
        where comp=$erp and t_opno > 500 AND t_opno < 969
              AND $filter IN ('$model')
        GROUP BY unit) minDate on minDate.unit=maxDate.unit
order by maxDate.unit ASC
SQL;
$res = tldUtils::getSqlToAssocArray($query);

foreach ($res as $unit) {
    $m = date_format(new DateTime($unit["stopDate"]),"M");
    $valBarCycleTime[$m]["val"] += $unit["days"];
    $valBarCycleTime[$m]["nb"] += 1;
    $valBarCycleTime[$m]["units"] = $valBarCycleTime[$m]["units"] . "<b>" . $unit["sn"] ."</b> : ". $unit["days"] . " days</br>" ;
}
foreach ($valBarCycleTime as $m=> $bar) {
    if ($bar["nb"] !== null && $bar["nb"] !== 0 && $bar["val"] !== null) {
        $valBarCycleTime[$m]["val"] = round($bar["val"] / $bar["nb"]);
    }
}

if ($nb < 2) {
    $graph->addLine(array_column($valBarCycleTime, "val"), ['name' => $model . ' Average cycle time', "zIndex" => 2]);
    $graph->addBar(array_column($valBarCycleTime, "nb"), ['name' => $model . ' with an answer to question 970', "enableMouseTracking" => true,
        "stickyTracking" => false, "zIndex" => 1]);
} else {
    $display = str_replace("'","",$model);
    $graph->addLine(array_column($valBarCycleTime, "val"), ['name' => $display . ' Average cycle time', "zIndex" => 2]);
    $graph->addBar(array_column($valBarCycleTime, "nb"), ['name' => $display . ' with an answer to question 970', "enableMouseTracking" => true,
        "stickyTracking" => false, "zIndex" => 1]);
}
$graph->addSeries(array_column($valBarCycleTime, "units"), ["name"=>"units","visible"=>false, "showInLegend"=>false]);
$graph->setTooltip(true,["useHTML"=>true]);

//$graph->addGroupTarget(16);
$cycleGraph = $graph->fetch("graph-3");
$js = <<<JS
$(document).ready(function() {
    $('#graph-3').highcharts().tooltip.options.formatter = function() {
        return ('<div style="max-height: 350px;;overflow-y: scroll;"> Units:</br>' + $('#graph-3').highcharts().options.series[2].data[this.point.index] + '</div>');
    }
});
JS;
$smarty->assign("html_head", $smarty->get_template_vars("html_head") .'<script type="text/javascript">' . $js . "</script>");

$def = <<<HTML
<a href="javascript:void(0);" 
onmouseover="javascript:overlib('<p><b>What it does measure exactly?</b>' +
 '<br>When the space starts being limited, the CT becomes key and can reveal the bottle necks. A VSM (Value Stream Mapping) is then necessary to reduce the CT and to produce more units in the same space / time. </p>' +
  '<p><b>How it is generated?</b>' +
   '<br>In the TLD language, this is the time between the operation 500 start (chassis preparation) to closure of operation 970 (release for test). </p>',
    CAPTION,'CYCLE TIME: The days or hours a chassis actually use assembly footprint in the workshop (a slot).',
    WIDTH,'500',OFFSETX,50,VAUTO,FGCOLOR,'#eeeeee',BGCOLOR,'gray',CAPCOLOR,'#dedede');"
     onmouseout="javascript:nd();">Definition: CYCLE TIME</a></br></br></br></br>
HTML;
$cycleDef = $def;

$body .= $cycleGraph;
$body .= $cycleDef;

$body .= $passageGraph;
$body .= $passageDef;

$body .= $leadGraph;
$body .= $leadDef;