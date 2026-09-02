<?php
include('kpi.common.inc.php');
$JS_INCLUDE=["/shared/javascript/overlib/overlib.js"];

$smarty->assign('js_includes', $JS_INCLUDE);

global $kernel;
$container = $kernel->getContainer();

$router = $container->get('router');
$url = $router->generate('green_tag_report_home');

$DEFAULT_TITLE .= '/KPI (NEW VERSION)';
$DEFAULT_MENU .= <<<EOF
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<a href="$php_self?m[0]=kpi2">Home</a>
&nbsp;|&nbsp;<a href="$php_self?m[0]=kpi2&m[1]=comparison">Comparison of factories</a>
&nbsp;|&nbsp;<a href="$url">Monitoring GT Graph</a>
EOF;

// ---------------------------
//    KPI FORM & CONTROL
// ---------------------------
$display_graph = false;
switch($m[1]) {
    case 'comparison':
        include('kpi.form.comparison.tool.inc.php');
        break;
    default:
        include('kpi.form.tool.inc.php');
}


// ------------------------
//      DISPLAY KPI
// ------------------------
if (isset($_GET['bu'], $_GET['graph'])) {
    $bu = new tldLocation($_GET['bu']);
    if (!$bu->isEmpty()) {
        if (!isset($_SESSION['kpi'])) {
            $_SESSION['kpi'] = [];
        }

        $_SESSION['kpi']['bu'] = $bu;
        $_SESSION['kpi']['dt_from'] = (new DateTime())->modify('-11 months');
        $_SESSION['kpi']['dt_to'] = new DateTime();

        $_SESSION['kpi']['form'] = [
            'dt_from' => $_SESSION['kpi']['dt_from']->format('Y-m'),
            'dt_to' => $_SESSION['kpi']['dt_to']->format('Y-m'),
        ];
        $_SESSION['kpi']['graph'] = $_GET['graph'];

        $display_graph = true;
    }
}

$factorySeries = [];
if ($display_graph) {

    $DEFAULT_TITLE .= "/{$_SESSION['kpi']['graph']}";

    // ---------------------------
    //    CONFIG & PARAMS
    // ---------------------------

    $factoryLabel = $_SESSION['kpi']['bu'] === null ? 'ALL' : $_SESSION['kpi']['bu']->getShortName();
    $from = $_SESSION['kpi']['dt_from']->format('Y-m-d');
    $to = $_SESSION['kpi']['dt_to']->format('Y-m-d');
    $y = $vals['due_date']['Y'];
    $m = $vals['due_date']['m'];

    $start = (new DateTime($from))->modify('first day of this month');
    $end = (new DateTime($to))->modify('first day of next month');
    $periods = [];
    foreach (new DatePeriod($start, new DateInterval('P1M'), $end) as $dt) {
        $periods[] = $dt->format('Y-m');
    }

    // Manage Series when applicable
    if ($factoryLabel === 'ALL') {
        $factorySeries = array_filter($kpiFactoryList, static function ($factory) {
            return $factory !== 'TLD DTV';
        });
    } else {
        $factorySeries[] = $factoryLabel;
    }

    // Language when applicable
    $availableLang = ['en', 'fr'];
    $lang = substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 2);
    if (!in_array($lang, $availableLang, true)) {
        $lang = 'en';
    }

    // Display graphs
    $graphsToDisplay = strtolower($_SESSION['kpi']['graph']);
    switch ($graphsToDisplay) {
        case 'delivery':
        case 'engineering':
        case 'production':
        case 'quality':
        case 'vendors':
            include("kpi.graph.$graphsToDisplay.inc.php");
            break;
        case 'manufacturing - by product':
        case 'manufacturing - by type':
            include('kpi.graph.manufacturing.inc.php');
            break;
        case 'product support':
            include('kpi.graph.support.inc.php');
            break;
        case 'green tag - by product':
        case 'green tag - by type':
            include('kpi.graph.greentag.inc.php');
            break;
        default:
            $DEFAULT_ERROR[] = 'This graph is not supported';
            break;
    }
}

