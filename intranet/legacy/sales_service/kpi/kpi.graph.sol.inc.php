<?php

$graph = new tldGraph();
$graph->setTitle('SOL process time');
$graph->setXAxisTitle('Months');
$graph->setMultipleYAxisTitle("Days");
$graph->setMultipleYAxisTitle("Number of SOL",array("opposite"=>true));

$months = [];
$valBar = [];
for ($dateStart = new DateTime($vars['dt_from']),
     $dateStop = new DateTime($vars['dt_to']);
     $dateStart <= $dateStop;
     $dateStart->add(DateInterval::createFromDateString('1 months'))) {
    $m = date_format($dateStart,"M");
    $months[] = $m;
    $valBar[$m]["val"] = null;
    $valBar[$m]["nb"] = null;
    $valBar[$m]["sol"] = null;
}

$dateStart = (new DateTime($vars["dt_from"]))->format('Y-m-d');
$dateStop = (new DateTime($vars["dt_to"]))->add(DateInterval::createFromDateString('1 months'))->sub(DateInterval::createFromDateString('1 days'))->format('Y-m-d');
$query="";
switch ($vars["type_graph"]) {
    case 'sso':
        $bu = $vars["graph_sso"];
        $query=<<<SQL
SELECT sor_lines.id AS 'sol', date, DATEDIFF(date, dt_opened) AS 'days'
FROM sor
  JOIN locations on locations.erp=sor.bu
  JOIN sor_lines on sor.id=sor_lines.parent_id
  JOIN mod_logs on mod_logs.parent_id=sor_lines.id
WHERE locations.id = $bu
  AND mod_logs.module = 'SOL'
  AND comment like 'Status moved to CREATE_PO%'
AND date BETWEEN CAST('$dateStart' AS DATE) AND CAST('$dateStop' AS DATE)
ORDER BY sor_lines.id ASC;
SQL;
        $def = <<<HTML
<a href="javascript:void(0);" 
onmouseover="javascript:overlib('<p><b>How it is generated?</b>' +
   '<br>In the TLD language, this is the time between the SOL is at PENDING level until the CREATE_PO level.</p>',
    CAPTION,'The time needed to process a SOL (in days)',
    WIDTH,'500',OFFSETX,50,VAUTO,FGCOLOR,'#eeeeee',BGCOLOR,'gray',CAPCOLOR,'#dedede');"
     onmouseout="javascript:nd();">Definition: SOL process time</a>
HTML;
        break;
    case 'factory':
        $bu = $vars["graph_factory"];
        $query=<<<SQL
SELECT solend.sol AS 'sol', solend.date AS 'date', DATEDIFF(solend.date, solstart.date) AS 'days'
FROM (
SELECT sor_lines.id AS 'sol', MIN(date) as 'date'
FROM sor_lines
JOIN mod_logs on mod_logs.parent_id=sor_lines.id
WHERE sor_lines.bu = $bu
      AND mod_logs.module = 'SOL'
      AND comment like 'Status moved to PRINT_FACTORY_SO_ACK%'
  GROUP BY sor_lines.id
HAVING date BETWEEN CAST('$dateStart' AS DATE) AND CAST('$dateStop' AS DATE)
) solend
JOIN (
       SELECT sor_lines.id AS 'sol', MIN(date) as 'date'
       FROM sor_lines
         JOIN mod_logs on mod_logs.parent_id=sor_lines.id
       WHERE sor_lines.bu = $bu
             AND mod_logs.module = 'SOL'
             AND comment like 'Status moved to CREATE_FACTORY_SO%'
       GROUP BY sor_lines.id
     ) solstart ON solstart.sol=solend.sol
ORDER BY sol ASC;
SQL;
        $def = <<<HTML
<a href="javascript:void(0);" 
onmouseover="javascript:overlib('<p><b>How it is generated?</b>' +
   '<br>In the TLD language, this is the time between the SOL is (for the first time) at CREATE_FACTORY_SO level until the PRINT_FACTORY_SO_ACK level.</p>',
    CAPTION,'The time needed to process a SOL (in days)',
    WIDTH,'500',OFFSETX,50,VAUTO,FGCOLOR,'#eeeeee',BGCOLOR,'gray',CAPCOLOR,'#dedede');"
     onmouseout="javascript:nd();">Definition: SOL process time</a>
HTML;
        break;
}

$res = tldUtils::getSqlToAssocArray($query);

foreach ($res as $sol) {
    $y = date_format(new DateTime($sol["date"]),"Y");
    $m = date_format(new DateTime($sol["date"]),"M");
    $valBar[$m]["val"] += $sol["days"];
    $valBar[$m]["nb"] += 1;
    $valBar[$m]["sol"] = $valBar[$m]["sol"] . "<b>" . $sol["sol"] ."</b> : ". $sol["days"] . " days</br>" ;
    $valBar[$m]["year"] = $y;
}
foreach ($valBar as $m=> $bar) {
    $valBar[$m]['val'] = null === $bar['val'] || null === $bar['nb'] ? 0 : round($bar['val'] / $bar['nb']);
}

$graph->setXAxisCategories($months);
$graph->addLine(array_column($valBar, "val"), ['name' =>'Average time', "zIndex" => 2]);
$graph->addBar(array_column($valBar, "nb"), ['name' => 'SOL', "enableMouseTracking" => true,
    "stickyTracking" => false, "zIndex" => 1]);
$graph->addSeries(array_column($valBar, "sol"), ["name" => "sols", "visible" => false, "showInLegend" => false]);
$graph->setTooltip(true,["useHTML"=>true]);

if (isset($_SESSION['data_report']))
    unset($_SESSION['data_report']);
$_SESSION['data_report'] = $valBar;

//$graph->addGroupTarget(16);
$body .= $graph->fetch("graph-1");

$js = <<<JS
$(document).ready(function() {
    $('#graph-1').highcharts().tooltip.options.formatter = function() {
        return ('<div style="max-height: 350px; overflow-y: scroll;"> SOL:</br>' + $('#graph-1').highcharts().options.series[2].data[this.point.index] + '</div>');
    }
    $('#graph-2').highcharts().tooltip.options.formatter = function() {
        return ('<div style="max-height: 350px; overflow-y: scroll;">' + $('#graph-2').highcharts().options.series[1].data[this.point.index] + '</div>');
    }
});
JS;
$smarty->assign("html_head", $smarty->get_template_vars("html_head") .'<script type="text/javascript">' . $js . "</script>");

$body .= $def;

$body .= <<<HTML
<a href="$php_self?m[0]=kpi&m[1]=sol&m[2]=XLS" target="_blank" style="margin-left: 5%"><img src="/shared/icons/application/save.png" alt="Download"></a>
HTML;



/////////////////////////////////////////////////////////////////////////////////////////
///
/// SOL print PO
/// /////////////////////////////////////////////////////////////////////////////////////

$graph = new tldGraph();
$graph->setTitle('% SOL modified current month');
$graph->setXAxisTitle('Months');
$graph->setMultipleYAxisTitle("% closed SOL");


$dateStart = (new DateTime($vars["dt_from"]))->format('Y-m-d');
$dateStop = (new DateTime($vars["dt_to"]))->add(DateInterval::createFromDateString('1 months'))->sub(DateInterval::createFromDateString('1 days'))->format('Y-m-d');
$query = "";
switch ($vars["type_graph"]) {
    case 'sso':
        $bu = $vars["graph_sso"];
        $erpBu = tldLocation::getERPByID($bu);
        $WHERE = "sor.bu=$erpBu ";
        break;
    case 'factory':
        $bu = $vars["graph_factory"];
        $WHERE = "sor_lines.bu=$bu ";
        break;
}
$factory = tldLocation::getLocationByID($bu);
$query = <<<EOF
SELECT nam_period AS xval
FROM fin_periods AS p
WHERE
    PERIOD_DIFF( DATE_FORMAT('$dateStart','%Y%m'), p.nam_period )<=0
    AND PERIOD_DIFF( DATE_FORMAT('$dateStop','%Y%m'), p.nam_period )>=0
ORDER BY nam_period
EOF;

$graph_period = tldUtils::getSqlToAssocArray($query);
foreach ($graph_period as $period) {
    $period_Ym = $period['xval'];
    $period_Ymd = substr($period['xval'], 0, 4) . "-" . substr($period['xval'], -2, 2);
    $xAxis[] = $period_Ym;

    $subQuery =  <<<SQL
SELECT COUNT(*)
FROM sor_lines
LEFT JOIN sor on sor.id=sor_lines.parent_id
WHERE sor_lines.dt_closed LIKE '{$period_Ymd}%' AND $WHERE
SQL;


    $query = <<<SQL
SELECT
  GROUP_CONCAT(DISTINCT sor_lines.id) AS sols,
  ROUND(COUNT(DISTINCT sor_lines.id) * 100 / ($subQuery), 2) AS prct,
  ($subQuery) AS total
FROM sor_lines
LEFT JOIN sor on sor.id=sor_lines.parent_id
LEFT JOIN mod_logs ON mod_logs.parent_id=sor_lines.id
WHERE mod_logs.module='SOL' AND mod_logs.comment LIKE '%Status changed back to PRINT_SO_ACK%' 
  AND sor_lines.dt_closed LIKE '{$period_Ymd}%'
  AND $WHERE
SQL;

    $row = tldUtils::getSqlRowToAssocArray($query);

    $sols = [];
    if (!empty($row['sols'])) {
        $query = <<<EOF
SELECT
  parent_id AS id,
  count(*) AS num
FROM mod_logs
WHERE module='SOL' AND parent_id IN ({$row['sols']}) AND mod_logs.comment LIKE '%Status changed back to PRINT_SO_ACK%'
GROUP BY parent_id
EOF;
        $sols = tldUtils::getSqlToAssocArray($query);
    }

    $tooltip = 'SOL: ('.count($sols).'/'.$row['total'].')</br>';
    $tooltip .= implode('', array_map(function (array $line) {
        return "<b>{$line['id']}:</b> {$line['num']}</br>";
    }, $sols));

    $modifiedSol[] = [
        'date' => $period_Ymd,
        'val' => isset($row['prct']) ? (float) $row['prct'] : 0.0,
        'tooltip' => $tooltip,
        'sols' => $sols,
    ];
}

$graph->setXAxisCategories($xAxis);
$graph->addBar(array_column($modifiedSol, 'val'), ['name' => $factory, 'enableMouseTracking' => true, 'stickyTracking' => false]);
$graph->addSeries(array_column($modifiedSol, 'tooltip'), ['name' => 'tooltip', 'visible' => false, 'showInLegend' => false]);
$graph->setTooltip(true, ['useHTML' => true]);

if (isset($_SESSION['modified_sol_report'])) {
    unset($_SESSION['modified_sol_report']);
}
$_SESSION['modified_sol_report'] = $modifiedSol;

$def = <<<HTML
<a href="javascript:void(0);" 
onmouseover="javascript:overlib('<p><b>How it is generated?</b>' +
   '<br>Number of SOL closed in the month which status was changed back to PRINT_SO_ACK before CLOSED / # SOL closed in the month.</p>',
    CAPTION,'% SOL modified current month',
    WIDTH,'500',OFFSETX,50,VAUTO,FGCOLOR,'#eeeeee',BGCOLOR,'gray',CAPCOLOR,'#dedede');"
     onmouseout="javascript:nd();">Definition: SOL modified current month</a>
HTML;

$graph->addGroupTarget(10);
$body .= $graph->fetch('graph-2');

$body .= $def;

$body .= <<<HTML
<a href="$php_self?m[0]=kpi&m[1]=sol&m[2]=XLS-modified" target="_blank" style="margin-left: 5%"><img src="/shared/icons/application/save.png" alt="Download"></a>
HTML;
?>
