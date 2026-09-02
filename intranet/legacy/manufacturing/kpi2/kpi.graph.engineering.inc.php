<?php
$colors = ["#7cb5ec", "#434348", "#90ed7d", "#f7a35c", "#8085e9", "#f15c80", "#e4d354", "#2b908f", "#f45b5b", "#91e8e1", '#058DC7', '#50B432', '#ED561B', '#DDDF00', '#24CBE5', '#64E572', '#FF9655', '#FFF263', '#6AF9C4'];
//  SOL
$bu = $_GET['bu'] ?? $_SESSION['kpi']['form']['bu_id'];

if (1 === \count($factorySeries)) {
    $buColors = array_combine($factorySeries, array_slice($colors, 0, \count($factorySeries)));
} else {
    //DTV needs to be included in KPI engineering
    $buColors = array_combine($kpiFactoryList, array_slice($colors, 0, \count($kpiFactoryList)));
}

$models = [];

if (strtolower($_SESSION['kpi']['graph']) === 'engineering') {
    $models = $_SESSION['kpi']['form']["models_$bu"];
}

///////////////////////////////////////////
// Graph OTE
///////////////////////////////////////////
$kpiDefinition = new tldKpiDefinition('engineering_OTE', $lang);
$graph = new tldGraph();
$graph->setTitle($kpiDefinition->getTitle());
$graph->setYAxisTitle('% of gate on time');

$rows = tldMEAP::getOTE($bu);
$memo = [];
foreach ($rows as $v) {
    $factory = $v['location'];
    if (!isset($memo[$factory])) {
        $memo[$factory] = array_fill(0, count($periods), ['y' => null, 'details' => '', 'ratio' => '']);
    }
}

foreach ($periods as $key => $date) {
    foreach ($rows as $v) {
        if ($v['xval'] === $date) {
            $memo[$v['location']][$key] = ['y' => (int)$v['yval'], 'details' => sprintf('%s', $v['details']), 'ratio' => sprintf('%s / %s', $v['onTime'], $v['total'])];
        }
    }
}

$graph->setXAxisCategories($periods);
$graph->setYAxisFloorValue(0);
$graph->setYAxisMaxValue(100);
if ($kpiDefinition->hasGroupTarget()) {
    $graph->addGroupTarget($kpiDefinition->getGroupTarget());
}
foreach ($memo as $factory => $data) {
    $graph->addNamedSerie($factory, $data, ['type' => 'column', 'dataLabels' => ['enabled' => true], 'color' => $buColors[$factory]]);
}

$graph->setTooltip(true, ['useHTML' => true]);
$graph->addChartOptions(['zoomType' => 'x']);
$body .= $graph->fetch("graph-1");

$popup = new tldKpiDefinitionPopup($kpiDefinition);
$body .= $popup->fetch();

$js = <<<JS
$(document).ready(function() {
    var chart = $('#graph-1').highcharts();

    chart.tooltip.options.formatter = function() {
        var point = this.point;
        var details = point.details
            .replace(/PHASE/g, 'GATE')
            .replace(/CLOSURE/g, '');
        
        return ('<div style="max-height: 350px; overflow-y: scroll;">' +
            '<b>' + point.series.name + '</b><br>' +
            'Ratio: ' + this.point.ratio + '<br>' +
            details +
            '</div>');
    };
    chart.redraw();
});
JS;
$smarty->assign("html_head", $smarty->get_template_vars("html_head") . '<script type="text/javascript">' . $js . "</script>");

///////////////////////////////////////////
// Graph On Budget Program Delivery
///////////////////////////////////////////
$kpiDefinition = new tldKpiDefinition('engineering_OnBudgetProgramDelivery', $lang);
$graph = new tldGraph();
$graph->setTitle($kpiDefinition->getTitle());
$graph->setYAxisTitle('MEAP actual hours');
$graph->setXAxisTitle('MEAP budgeted hours');

$rows = tldMEAP::getOnBudgetProgramDelivery($bu);
$memo = [];
$maxX = $maxY = $min = 0;
foreach ($rows as $row) {
    $maxX = max($maxX, (int)$row['econ_target_dh']);
    $maxY = max($maxY, (int)$row['econ_actual_dh']);
    if (!array_key_exists($row['location'], $memo)) {
        $memo[$row['location']] = [];
    }
    $memo[$row['location']][] = ['x' => (float)$row['econ_target_dh'], 'y' => (float)$row['econ_actual_dh'], 'meap' => $row['id'], 'ifactor' => $row['ifactor'], 'short_desc' => mb_convert_encoding($row['short_desc'], 'UTF-8', mb_list_encodings()), 'status' => $row['status']];
}
foreach ($memo as $factory => $data) {
    $graph->addScatter($data, ['name' => $factory, 'dataLabels' => ['enabled' => false], 'marker' => ['radius' => 4], 'enableMouseTracking' => true, 'color' => $buColors[$factory]]);
}

$graph->addNamedSerie('target', [[0, 0], [min($maxX, $maxY), min($maxX, $maxY)]], ['type' => 'spline', 'dataLabels' => ['enabled' => false], 'enableMouseTracking' => false, 'showInLegend' => false, 'marker' => ['radius' => 0], 'color' => 'rgba(223, 83, 83, .5)']);
$graph->setYAxisFloorValue(0);
$graph->setYAxisMaxValue($maxY);
$graph->addXAxisOptions(['startOnTick' => true, 'endOnTick' => true, 'showLastLabel' => true, 'min' => 0, 'max' => $maxX]);
$graph->addChartOptions(['zoomType' => 'xy', 'height' => 800]);

$graph->setTooltip(true, ['useHTML' => true]);

$body .= '<div style="min-height:800px;">'.$graph->fetch("graph-2").'</div>';

$js = <<<JS
$(document).ready(function() {
    $('#graph-2').highcharts().tooltip.options.formatter = function() {
        var detail = this.point;
        return ( '<b>MEAP ' + detail.meap + '</b> (IF' + detail.ifactor + ') - <b>' + detail.status + '</b><br/><small>' + detail.short_desc + '</small><br/><br/>budgeted: <b>' + this.x + '</b><br/>' + (detail.status === 'CLOSED' ? 'actual' : 'eac') +': <b>' + this.y + '</b>');
    };
});
JS;

$smarty->assign("html_head", $smarty->get_template_vars("html_head").'<script type="text/javascript">'.$js."</script>");

$popup = new tldKpiDefinitionPopup($kpiDefinition);
$body .= $popup->fetch();

///////////////////////////////////////////
// Graph EAP Qty remaining Open
///////////////////////////////////////////
$kpiDefinition = new tldKpiDefinition('engineering_EAPQtyRemainingPerMonth', $lang);
$graph = new tldGraph();
$graph->setTitle($kpiDefinition->getTitle());

$WHERE = '';
if ("ALL" !== $bu) {
    $WHERE = "AND locations.id=$bu";
}

$query = <<<EOF
		SELECT CONCAT(SUBSTR(nam_period, 1, 4), '-', SUBSTR(nam_period, 5, 2)) as nam_period
		FROM fin_periods
		WHERE PERIOD_DIFF(DATE_FORMAT(NOW(), '%Y%m'), nam_period) BETWEEN 0 AND 11
		ORDER BY nam_period
EOF;

$months = tldUtils::getSqlToAssocArray($query);
foreach ($months as $month) {
    $date = $month['nam_period'];
    $query = <<<SQL
SELECT
    locations.location AS location,
    COALESCE(mods.type, 'NO_TYPE') AS zval,
    COUNT(DISTINCT(eap.id)) AS yval,
    '$date' as xval
FROM
    eap
        INNER JOIN locations ON eap.factory = locations.id
        LEFT JOIN mod_models AS mods ON mods.parent_id = eap.id AND mods.module = 'EAP'
WHERE
    locations.factory = 'Y'
  AND locations.disable = 0
  AND locations.hidden = 0
  AND date_format(eap.dt_opened, '%Y-%m') <= '$date' 
  AND (date_format(eap.dt_closed, '%Y-%m') > '$date' OR eap.dt_closed LIKE '0000-00-00%')
$WHERE
GROUP BY zval, xval, locations.location
ORDER BY locations.location, xval
SQL;
    $results[] = tldUtils::getSqlToAssocArray($query);
}

$memo = [];
$results = array_merge(...$results);
$totals = [];
$sortedTotal = [];
foreach ($results as $v) {
    $type = $v['zval'];
    $location = $v['location'];
    if (!isset($memo[$location][$type])) {
        $memo[$location][$type] = array_fill(0, count($periods), ['y' => null]);
    }
    $totals[$v['xval']] = null === ($totals[$v['xval']] ?? null) ? $v['yval'] : $totals[$v['xval']] + $v['yval'];
}
foreach ($periods as $key => $date) {
    foreach ($results as $v) {
        if ($v['xval'] === $date) {
            $memo[$v['location']][$v['zval']][$key] = ['y' => (int)$v['yval']];
        }
    }
    foreach($totals as $month => $total) {
        if ($month === $date) {
            $sortedTotal[] = $total;
        }
    }
}
$graph->setXAxisCategories($periods);
foreach ($memo as $location => $types) {
    foreach ($types as $type => $data) {
        $serieName = $type;
        if (\count($factorySeries) > 1) {
            $serieName .= " - $factory";
        }
        $graph->addNamedSerie($serieName, $data, ['type' => 'column', 'dataLabels' => ['enabled' => true], 'stacking' => 'normal', 'stack' => $location]);
    }
}
$graph->addScatter($sortedTotal, ['name' => 'Totals', 'dataLabels' => ['enabled' => true], 'marker' => ['enabled' => true, 'radius' => 0, 'symbol' => 'circle']]);

$graph->setTooltip(true, ['useHTML' => true]);
$graph->addChartOptions(['zoomType' => 'x', 'height' => 400]);
$body .= $graph->fetch("graph-3");

$popup = new tldKpiDefinitionPopup($kpiDefinition);
$body .= $popup->fetch();

///////////////////////////////////////////
// Graph EAP Qty remaining Open by category
///////////////////////////////////////////
$kpiDefinition = new tldKpiDefinition('engineering_EAPQtyRemainingPerMonthByCategory', $lang);
$graph = new tldGraph();
$graph->setTitle($kpiDefinition->getTitle());

$WHERE = '';
if ("ALL" !== $bu) {
    $WHERE = "AND locations.id=$bu";
}

$query = <<<EOF
		SELECT CONCAT(SUBSTR(nam_period, 1, 4), '-', SUBSTR(nam_period, 5, 2)) as nam_period
		FROM fin_periods
		WHERE PERIOD_DIFF(DATE_FORMAT(NOW(), '%Y%m'), nam_period) BETWEEN 0 AND 11
		ORDER BY nam_period
EOF;

$months = tldUtils::getSqlToAssocArray($query);
$results = [];
foreach ($months as $month) {
    $date = $month['nam_period'];
    $query = <<<SQL
SELECT
    locations.location AS location,
    COALESCE(eap.category, 'NO_CATEGORY') AS zval,
    COUNT(DISTINCT(eap.id)) AS yval,
    '$date' as xval
FROM
    eap
        INNER JOIN locations ON eap.factory = locations.id
WHERE
    locations.factory = 'Y'
  AND locations.disable = 0
  AND locations.hidden = 0
  AND date_format(eap.dt_opened, '%Y-%m') <= '$date' 
  AND (date_format(eap.dt_closed, '%Y-%m') > '$date' OR eap.dt_closed LIKE '0000-00-00%')
$WHERE
GROUP BY zval, xval, locations.location
ORDER BY locations.location, xval
SQL;
    $results[] = tldUtils::getSqlToAssocArray($query);
}
$memo = [];
$results = array_merge(...$results);
$totals = [];
$sortedTotal = [];
foreach ($results as $v) {
    $type = $v['zval'];
    $location = $v['location'];
    if (!isset($memo[$location][$type])) {
        $memo[$location][$type] = array_fill(0, count($periods), ['y' => null]);
    }
    $totals[$v['xval']] = null === ($totals[$v['xval']] ?? null) ? $v['yval'] : $totals[$v['xval']] + $v['yval'];
}
foreach ($periods as $key => $date) {
    foreach ($results as $v) {
        if ($v['xval'] === $date) {
            $memo[$v['location']][$v['zval']][$key] = ['y' => (int)$v['yval']];
        }
    }
    foreach($totals as $month => $total) {
        if ($month === $date) {
            $sortedTotal[] = $total;
        }
    }
}
$graph->setXAxisCategories($periods);
foreach ($memo as $location => $types) {
    foreach ($types as $type => $data) {
        $serieName = $type;
        if (\count($factorySeries) > 1) {
            $serieName .= " - $factory";
        }
        $graph->addNamedSerie($serieName, $data, ['type' => 'column', 'dataLabels' => ['enabled' => true], 'stacking' => 'normal', 'stack' => $location]);
    }
}
$graph->addScatter($sortedTotal, ['name' => 'Totals', 'dataLabels' => ['enabled' => true], 'marker' => ['enabled' => true, 'radius' => 0, 'symbol' => 'circle']]);

$graph->setTooltip(true, ['useHTML' => true]);
$graph->addChartOptions(['zoomType' => 'x', 'height' => 400]);
$body .= $graph->fetch("graph-3");

$popup = new tldKpiDefinitionPopup($kpiDefinition);
$body .= $popup->fetch();

///////////////////////////////////////////
// Graph EAP Qty Notified
///////////////////////////////////////////
$kpiDefinition = new tldKpiDefinition('engineering_EAPQtyNotifiedPerMonth', $lang);
$graph = new tldGraph();
$graph->setTitle($kpiDefinition->getTitle());

$WHERE = $INNER_WHERE = '';
if ("ALL" !== $bu) {
    $WHERE = "AND eap.factory=$bu";
}

$query = <<<EOF
		SELECT CONCAT(SUBSTR(nam_period, 1, 4), '-', SUBSTR(nam_period, 5, 2)) as nam_period
		FROM fin_periods
		WHERE PERIOD_DIFF(DATE_FORMAT(NOW(), '%Y%m'), nam_period) BETWEEN 0 AND 11
		ORDER BY nam_period
EOF;

$months = tldUtils::getSqlToAssocArray($query);
$eapNotified = [];

foreach ($months as $month) {
    $date = $month['nam_period'];
    $query = <<<SQL
SELECT
    locations.location AS location,
    '$date' AS xval,
    COUNT(DISTINCT(eap.id)) AS yval
FROM eap
         INNER JOIN locations ON eap.factory = locations.id
         INNER JOIN (
             SELECT parent_id, MIN(id), date
             FROM mod_logs
             WHERE module = 'EAP' AND comment = 'NOTIFICATION'
             group by parent_id
         ) log ON log.parent_id = eap.id
WHERE DATE_FORMAT(log.date, '%Y-%m') = '$date'
  $WHERE
GROUP BY xval, eap.factory
SQL;
    $eapNotified[] = tldUtils::getSqlToAssocArray($query);
}
$memo = [];
$results = array_merge(...$eapNotified);
foreach ($results as $v) {
    if (!isset($memo['EAP Notified'])) {
        $memo['EAP Notified'] = array_fill(0, count($periods), ['y' => null]);
    }
}
foreach ($periods as $key => $date) {
    foreach ($results as $v) {
        if ($v['xval'] === $date) {
            $memo['EAP Notified'][$key] = ['y' => (int)$v['yval']];
        }
    }
}

$graph->setXAxisCategories($periods);
foreach ($memo as $name => $data) {
    $graph->addNamedSerie($name, $data, ['type' => 'column', 'dataLabels' => ['enabled' => true], 'color' => $buColors[$name]]);
}

$query = <<<SQL
SELECT
	date_format(eap.dt_opened, '%Y-%m') AS xval,
	COUNT(DISTINCT(eap.id)) AS yval,
	locations.location
FROM fin_periods as p
LEFT JOIN eap ON date_format(eap.dt_opened, '%Y%m')=p.nam_period
LEFT JOIN locations ON eap.factory=locations.id
WHERE eap.dt_opened BETWEEN '$from' AND '$to' AND eap.factory=$bu
GROUP BY xval, locations.location
ORDER BY locations.location, xval
SQL;

$eapOpened = tldUtils::getSqlToAssocArray($query);

$memoOpened = [];
// Fill array with default value for keep numeric keys otherwise it will not be display in the graph.
foreach ($periods as $key => $date) {
    $memoOpened['EAP Opened'][$key] = ['y' => 0];
}
foreach ($periods as $key => $date) {
    foreach ($eapOpened as $v) {
        if ($v['xval'] === $date) {
            $memoOpened['EAP Opened'][$key] = ['y' => (int)$v['yval']];
        }
    }
}

foreach ($memoOpened as $name => $data) {
    $graph->addNamedSerie($name, $data, ['type' => 'column', 'dataLabels' => ['enabled' => true], 'color' => $buColors[$name]]);
}

$graph->setTooltip(true, ['useHTML' => true]);
$graph->addChartOptions(['zoomType' => 'x']);

if ($kpiDefinition->hasGroupTarget()) {
    $graph->addGroupTarget($kpiDefinition->getGroupTarget());
}
$body .= $graph->fetch("graph-4");

$popup = new tldKpiDefinitionPopup($kpiDefinition);
$body .= $popup->fetch();

///////////////////////////////////////////
// Graph EAP Notified Repartition by type
///////////////////////////////////////////
$kpiDefinition = new tldKpiDefinition('engineering_EAPNotifiedRepartitionByType', $lang);
$graph = new tldGraph();
$graph->setTitle($kpiDefinition->getTitle());
$graph->setYAxisTitle('Total of Notified EAP');

$rows = tldEAP::countEAPNotifiedByType($from, $to, $bu);
$memo = [];
$totals = [];
$sortedTotal = [];

foreach ($rows as $v) {
    $type = $v['zval'];
    $location = $v['location'];
    if (!isset($memo[$location][$type])) {
        $memo[$location][$type] = array_fill(0, count($periods), ['y' => null]);
    }
    $totals[$v['xval']] = null === ($totals[$v['xval']] ?? null) ? $v['yval'] : $totals[$v['xval']] + $v['yval'];
}
foreach ($periods as $key => $date) {
    foreach ($rows as $v) {
        if ($v['xval'] === $date) {
            $memo[$v['location']][$v['zval']][$key] = ['y' => (int)$v['yval']];
        }
    }

    foreach($totals as $month => $total) {
        if ($month === $date) {
            $sortedTotal[] = $total;
        }
    }
}

$graph->setXAxisCategories($periods);
foreach ($memo as $location => $types) {
    foreach ($types as $type => $data) {
        $serieName = $type;
        if (\count($factorySeries) > 1) {
            $serieName .= " - $factory";
        }
        $graph->addNamedSerie($serieName, $data, ['type' => 'column', 'dataLabels' => ['enabled' => true], 'stacking' => 'normal', 'stack' => $location]);
    }
}
$graph->addScatter($sortedTotal, ['name' => 'Totals', 'dataLabels' => ['enabled' => true], 'marker' => ['enabled' => true, 'radius' => 0, 'symbol' => 'circle']]);


$graph->setTooltip(true, ['useHTML' => true]);
$graph->addChartOptions(['zoomType' => 'x', 'height' => 400]);
$body .= $graph->fetch("graph-5");

$popup = new tldKpiDefinitionPopup($kpiDefinition);
$body .= $popup->fetch();

///////////////////////////////////////////
// Graph EAP % NOTIFIED in 72 hours
///////////////////////////////////////////
$kpiDefinition = new tldKpiDefinition('engineering_EAPNotifiedIn72Hrs', $lang);
$graph = new tldGraph();
$graph->setTitle($kpiDefinition->getTitle());

$WHERE = $INNER_WHERE = '';
if ("ALL" !== $bu) {
    $WHERE = "AND eap.factory=$bu";
}

$query = <<<EOF
		SELECT CONCAT(SUBSTR(nam_period, 1, 4), '-', SUBSTR(nam_period, 5, 2)) as nam_period
		FROM fin_periods
		WHERE PERIOD_DIFF(DATE_FORMAT(NOW(), '%Y%m'), nam_period) BETWEEN 0 AND 11
		ORDER BY nam_period
EOF;

$months = tldUtils::getSqlToAssocArray($query);
$eapNotified = [];
foreach ($months as $month) {
    $date = $month['nam_period'];
    $query = <<<SQL
SELECT
    locations.location AS location,
    '$date' AS xval,
    COUNT(*) AS yval,
    eap.category AS zval
FROM eap
         INNER JOIN locations ON eap.factory = locations.id
         INNER JOIN (
             SELECT parent_id, MIN(id), date
             FROM mod_logs
             WHERE module = 'EAP' AND comment = 'NOTIFICATION'
             group by parent_id
         ) log ON log.parent_id = eap.id
WHERE TIMESTAMPDIFF(HOUR, eap.dt_opened, log.date) <= 72
  AND DATE_FORMAT(eap.dt_opened, '%Y-%m') = '$date'
  $WHERE
GROUP BY xval, eap.factory, zval
SQL;
    $eapNotified[] = tldUtils::getSqlToAssocArray($query);
}

$memo = [];
$results = array_merge(...$eapNotified);
foreach ($results as $v) {
    $type = $v['zval'];
    $location = $v['location'];
    if (!isset($memo[$location][$type])) {
        $memo[$location][$type] = array_fill(0, count($periods), ['y' => null]);
    }
}
foreach ($periods as $key => $date) {
    foreach ($results as $v) {
        if ($v['xval'] === $date) {
            $memo[$v['location']][$v['zval']][$key] = ['y' => (int)$v['yval']];
        }
    }
}
$graph->setXAxisCategories($periods);
foreach ($memo as $location => $types) {
    foreach ($types as $type => $data) {
        $serieName = $type;
        if (\count($factorySeries) > 1) {
            $serieName .= " - $factory";
        }
        $graph->addNamedSerie($serieName, $data, ['type' => 'column', 'dataLabels' => ['enabled' => true], 'stacking' => 'normal', 'stack' => $location]);
    }
}

if ($kpiDefinition->hasGroupTarget()) {
    $graph->addGroupTarget($kpiDefinition->getGroupTarget());
}

$graph->setTooltip(true, ['useHTML' => true]);
$graph->addChartOptions(['zoomType' => 'x', 'height' => 400]);
$body .= $graph->fetch("graph-6");

$popup = new tldKpiDefinitionPopup($kpiDefinition);
$body .= $popup->fetch();

///////////////////////////////////////////
// Graph Avg number of day open
///////////////////////////////////////////
$kpiDefinition = new tldKpiDefinition('engineering_EAPAverageNumberOfDaysOpen', $lang);
$graph = new tldGraph();
$graph->setTitle($kpiDefinition->getTitle());

$startFinPeriods = $start->format('Ym');
$endFinPeriods = $end->format('Ym');

$WHERE = $INNER_WHERE = '';
if ("ALL" !== $bu) {
    $WHERE = "AND locations.id=$bu";
}

$query = <<<SQL
SELECT
    locations.location AS location,
    p.nam_period AS xval,
    (SELECT
        ROUND(SUM(
            TO_DAYS(LAST_DAY(DATE_FORMAT(CONCAT(p.nam_period,'01'),'%Y%m%d')))-TO_DAYS(eap.dt_opened)
        ) / COUNT(*), 2)
    FROM eap
    WHERE
        eap.factory=locations.id
        AND PERIOD_DIFF(DATE_FORMAT(eap.dt_opened, '%Y%m'), p.nam_period) < 0
        AND (eap.dt_closed LIKE '0000-00-00%' OR PERIOD_DIFF(DATE_FORMAT(eap.dt_closed, '%Y%m'), nam_period) > 0)
        $INNER_WHERE
    ) AS yval
FROM
    fin_periods AS p,
    locations
WHERE
    p.nam_period >= $startFinPeriods AND p.nam_period < $endFinPeriods
    AND locations.factory='Y' AND locations.disable=0 AND locations.hidden=0
    $WHERE
ORDER BY locations.location
SQL;

$rows = tldUtils::getSqlToAssocArray($query);

$memo = [];
foreach ($rows as $v) {
    $factory = $v['location'];
    if (!isset($memo[$factory])) {
        $memo[$factory] = array_fill(0, count($periods), ['y' => null]);
    }
}
foreach ($periods as $key => $date) {
    $formattedDate = str_replace('-', '', $date);
    foreach ($rows as $v) {
        if ($v['xval'] === $formattedDate) {
            $memo[$v['location']][$key] = ['y' => (float)$v['yval']];
        }
    }
}

$graph->setXAxisCategories($periods);
foreach ($memo as $factory => $data) {
    $graph->addNamedSerie($factory, $data, ['type' => 'column', 'dataLabels' => ['enabled' => true], 'color' => $buColors[$factory]]);
}

$graph->setTooltip(true, ['useHTML' => true]);
$graph->addChartOptions(['zoomType' => 'x']);

if ($kpiDefinition->hasGroupTarget()) {
    $graph->addGroupTarget($kpiDefinition->getGroupTarget());
}
$body .= $graph->fetch("graph-7");

$popup = new tldKpiDefinitionPopup($kpiDefinition);
$body .= $popup->fetch();

///////////////////////////////////////////
// Graph EAP Age
///////////////////////////////////////////
$kpiDefinition = new tldKpiDefinition('engineering_EAPage', $lang);
$graph = new tldGraph();
$graph->setTitle($kpiDefinition->getTitle());

$WHERE = '';
if ("ALL" !== $bu) {
    $WHERE = "AND eap.factory=$bu";
}

$today = (new DateTime())->format('Y-m-d');

$query = <<<SQL
SELECT
    COUNT(*) AS yval,
    CASE
        WHEN 1 > month THEN '<1 month'
        WHEN (1 <= month AND month < 3) THEN '1M< <3M'
        WHEN (3 <= month AND month < 6) THEN '3M< <6M'
        WHEN (6 <= month AND month < 9) THEN '6M< <9M'
        WHEN (9 <= month AND month < 12) THEN '9M< <12M'
        WHEN 12 <= month THEN '>12M' END AS xval,
    locations.location AS location
FROM (
         SELECT
             TIMESTAMPDIFF(MONTH, eap.dt_opened, '$today') AS month,
             eap.*
         FROM eap
     ) AS eap
INNER JOIN locations ON eap.factory=locations.id
WHERE
    eap.status NOT IN ('CLOSED', 'NOTIFICATION', 'REJECTED')
    $WHERE
GROUP BY locations.location, xval
ORDER BY locations.location
SQL;
$rows = tldUtils::getSqlToAssocArray($query);

$memo = [];
$xAxis = ['<1 month', '1M< <3M', '3M< <6M', '6M< <9M', '9M< <12M', '>12M'];
foreach ($rows as $v) {
    $factory = $v['location'];
    if (!isset($memo[$factory])) {
        $memo[$factory] = array_fill(0, count($xAxis), ['y' => null]);
    }
}

foreach ($xAxis as $key => $date) {
    foreach ($rows as $v) {
        if ($v['xval'] === $date) {
            $memo[$v['location']][$key] = ['y' => (int)$v['yval']];
        }
    }
}

$graph->setXAxisCategories($xAxis);
foreach ($memo as $factory => $data) {
    $graph->addNamedSerie($factory, $data, ['type' => 'column', 'dataLabels' => ['enabled' => true]]);
}

$graph->setTooltip(true, ['useHTML' => true]);
$graph->addChartOptions(['zoomType' => 'x']);

if ($kpiDefinition->hasGroupTarget()) {
    $graph->addGroupTarget($kpiDefinition->getGroupTarget());
}
$body .= $graph->fetch("graph-8");

$popup = new tldKpiDefinitionPopup($kpiDefinition);
$body .= $popup->fetch();

///////////////////////////////////////////
// Graph CBOM on TIME
///////////////////////////////////////////
$kpiDefinition = new tldKpiDefinition('engineering_CBOMOnTime', $lang);
$graph = new tldGraph();
$graph->setTitle($kpiDefinition->getTitle());

$WHERE = '';
if ("ALL" !== $bu) {
    $WHERE = "AND l.id=$bu";
}

$today = (new DateTime())->format('Y-m-d');

$query = <<<SQL
SELECT
    ROUND(100 * SUM(IF(er.last_cbom_update_date < er.promised_cbom_date, 1, 0)) / COUNT(*)) AS yval,
    DATE_FORMAT(er.dgt_com, '%Y-%m') AS xval,
    l.location AS location,
    GROUP_CONCAT(
        IF(
            er.last_cbom_update_date > er.promised_cbom_date,
            CONCAT(er.sn, ': ', DATEDIFF(er.last_cbom_update_date, er.promised_cbom_date), ' days'),
            NULL
        ) ORDER BY er.sn SEPARATOR '<br>'
    ) AS serials_with_delay
FROM service AS er
    INNER JOIN locations AS l ON er.man_location = l.location
WHERE
    er.promised_cbom_date IS NOT NULL
    AND er.last_cbom_update_date IS NOT NULL
    AND er.dgt_com != '0000-00-00'
    AND er.dgt_com BETWEEN '$from' AND '$to'
    $WHERE
GROUP BY l.location, xval
ORDER BY l.location, xval;
SQL;

$rows = tldUtils::getSqlToAssocArray($query);

$memo = [];
foreach ($rows as $v) {
    $factory = $v['location'];
    if (!isset($memo[$factory])) {
        $memo[$factory] = array_fill(0, count($periods), ['y' => null, 'serials_with_delay' => '']);
    }
}
foreach ($periods as $key => $date) {
    foreach ($rows as $v) {
        if ($v['xval'] === $date) {
            $memo[$v['location']][$key] = [
                'y' => (int)$v['yval'],
                'serials_with_delay' => $v['serials_with_delay'] ?? ''
            ];
        }
    }
}

$graph->setXAxisCategories($periods);

foreach ($memo as $factory => $data) {
    $graph->addNamedSerie($factory, array_map(function($entry) {
        return [
            'y' => $entry['y'],
            'serials_with_delay' => $entry['serials_with_delay']
        ];
    }, $data), [
        'type' => 'column',
        'dataLabels' => ['enabled' => true]
    ]);
}

$graph->setTooltip(true, [
    'useHTML' => true,
    'headerFormat' => '<b>{point.x}</b><br>',
    'pointFormat' => '{series.name}: {point.y}%<br>' .
        '<b>Late ER(s) :</b><br>' .
        '<div style="max-height:200px; min-width:150px; overflow-y:auto; white-space:normal;">{point.serials_with_delay}</div>'
]);

$graph->addChartOptions(['zoomType' => 'x']);

if ($kpiDefinition->hasGroupTarget()) {
    $graph->addGroupTarget($kpiDefinition->getGroupTarget());
}
$body .= $graph->fetch("graph-9");

$popup = new tldKpiDefinitionPopup($kpiDefinition);
$body .= $popup->fetch();

///////////////////////////////////////////
// Graph CBOM delay average
///////////////////////////////////////////
$kpiDefinition = new tldKpiDefinition('engineering_CBOMDelayAverage', $lang);
$graph = new tldGraph();
$graph->setTitle($kpiDefinition->getTitle());

$WHERE = '';
if ("ALL" !== $bu) {
    $WHERE = "AND l.id=$bu";
}

$today = (new DateTime())->format('Y-m-d');

$query = <<<SQL
SELECT
    ROUND(SUM(DATEDIFF(er.last_cbom_update_date, er.promised_cbom_date))/COUNT(*)) AS yval,
    DATE_FORMAT(er.dgt_com, '%Y-%m') AS xval,
    l.location AS location
FROM service AS er
    INNER JOIN locations AS l ON er.man_location = l.location
WHERE
    er.promised_cbom_date IS NOT NULL
    AND er.last_cbom_update_date IS NOT NULL
    AND er.dgt_com != '0000-00-00'
    AND DATEDIFF(er.last_cbom_update_date, er.promised_cbom_date) > 0
    AND er.dgt_com BETWEEN '$from' AND '$to'
    $WHERE
GROUP BY l.location, xval
ORDER BY l.location;
SQL;
$rows = tldUtils::getSqlToAssocArray($query);

$memo = [];
foreach ($rows as $v) {
    $factory = $v['location'];
    if (!isset($memo[$factory])) {
        $memo[$factory] = array_fill(0, count($periods), ['y' => null]);
    }
}
foreach ($periods as $key => $date) {
    foreach ($rows as $v) {
        if ($v['xval'] === $date) {
            $memo[$v['location']][$key] = ['y' => (int)$v['yval']];
        }
    }
}

$graph->setXAxisCategories($periods);
foreach ($memo as $factory => $data) {
    $graph->addNamedSerie($factory, $data, ['type' => 'column', 'dataLabels' => ['enabled' => true]]);
}

$graph->setTooltip(true, ['useHTML' => true]);
$graph->addChartOptions(['zoomType' => 'x']);

if ($kpiDefinition->hasGroupTarget()) {
    $graph->addGroupTarget($kpiDefinition->getGroupTarget());
}
$body .= $graph->fetch("graph-10");

$popup = new tldKpiDefinitionPopup($kpiDefinition);
$body .= $popup->fetch();
