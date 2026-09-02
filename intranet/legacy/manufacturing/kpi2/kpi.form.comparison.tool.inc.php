<?php
require_once('HTML/QuickForm/advmultiselect.php');

// FORM CONFIG
$DEFAULT_TITLE .= '/Comparison of factories';

$groupForm = [];
$kpiFactoryList = tldUtils::optionsByKeyValue(
    tldLocation::byConstraints(['disable' => 0, 'factory' => 'Y', 'hidden' => 0]),
    'id',
    'location'
);
$graphList = ['Engineering'];
$graphList = ['' => 'Select Graph'] + array_combine($graphList, $graphList);

$actionUrl = "/en/private/manufacturing/index.php?m[0]=kpi2&m[1]=comparison";
$kpiToolForm = new HTML_QuickForm('kpiToolfrm', 'POST', $actionUrl);
$kpiToolForm->addElement('hidden', 'm[0]', 'kpi2');
$kpiToolForm->addElement('hidden', 'm[1]', 'comparison');
$kpiToolForm->addElement('header', 'title', 'Graph configuration');
$groupForm[] = $kpiToolForm->createElement(
    'advmultiselect',
    'bu_ids',
    'Factories',
    $kpiFactoryList,
    ['size' => 10, 'class' => 'pool', 'style' => 'width:250px;', 'id' => 'bu_ids']
);
$groupForm[] = $kpiToolForm->createElement(
    'text',
    'dt_from',
    'Start',
    ['class' => 'datepicker', 'data-dateformat' => 'yy-mm', 'size' => 7, 'id' => 'start_date']
);
$groupForm[] = $kpiToolForm->createElement(
    'text',
    'dt_to',
    'End',
    ['id' => 'state', 'class' => 'datepicker', 'data-dateformat' => 'yy-mm', 'size' => 7]
);

$groupForm[] = $kpiToolForm->createElement('select', 'graph', 'Graph', $graphList, ['id' => 'graph_type', 'onchange' => 'checkstate()']);

$js = <<<JS
 function checkstate(keepChosen){
            $('#state').show();
            $('input[name="unit_type"]').each(function (ind, el) {
              $(el).parent('td').hide()
            })
            var begin = new Date();
            var year = parseInt(begin.getFullYear()) - 1;
            var month = parseInt(begin.getMonth()) + 1; // Months in JS are indexed 0-11
            var month2 = parseInt(month)+1;
            if(month === 12) {
              year = begin.getFullYear();
              month2 = '01';
            }

            var month2 = String(month2);
            if (month2.length < 2) {
              month2 = "0" + month2;
            }
            document.getElementById('start_date').value = year + '-' + month2;
    }
JS;

$smarty->assign('html_head', '<script type="text/javascript">' . $js . '</script>');
$groupForm[] = $kpiToolForm->createElement('submit', 'btnSubmit', 'Submit');
$kpiToolForm->addGroup($groupForm);
$defaults = [
    'dt_from' => (new DateTime())->modify('-5 months')->format('Y-m'),
    'dt_to' => date('Y-m'),
    'graph' => 'Engineering'
];

// --- Form config

$kpiToolForm->setDefaults($defaults);
$body = $kpiToolForm->toHtml();

// FORM PROCESSING
if ($kpiToolForm->validate() && $kpiToolForm->isSubmitted()) {
    $vars = $kpiToolForm->exportValues();
    // -- factory
    if (empty($vars['bu_ids']) || !is_array($vars['bu_ids'])) {
        $DEFAULT_ERROR[] = 'ERROR: At least one factory must be selected.';
    }
    $buList = [];
    foreach ($vars['bu_ids'] as $buId) {
        if (is_numeric($buId)) {
            $bu = new tldLocation($buId);
            if (!$bu->isEmpty()) {
                $buList[$bu->itsID] = $bu;
            }
        }
    }
    // -- Graph
    if (!isset($vars['graph']) || empty($vars['graph']) || !in_array($vars['graph'], $graphList)) {
        $DEFAULT_ERROR[] = 'ERROR: Graph field is empty or invalid';
    }
    // -- dates
    try {
        $start = new \DateTime($vars['dt_from']);
    } catch (Exception $e) {
        $DEFAULT_ERROR[] = "ERROR: Start period invalid. Reason: $e";
    }
    try {
        $end = new \DateTime($vars['dt_to']);
        $end->modify(
            sprintf('+%d days', $end->format('t') - $end->format('j'))
        );
    } catch (Exception $e) {
        $DEFAULT_ERROR[] = "ERROR: End period invalid. Reason: $e";
    }
    // Check start & end date
    if ($end < $start) {
        $DEFAULT_ERROR[] = 'ERROR: Start period can not be greater than End period';
    }
    // Check period <= 1 year
    $interval = $start->diff($end);
    $nbDays = $interval->format('%a');
    if ($nbDays > 365 && $vars['graph'] !== 'Green Tag') {
        $DEFAULT_ERROR[] = "ERROR: KPI period should not be bigger than a year (Period selected is {$interval->format('%R%a')} days)";
    }

    // if there is any error stop the script
    if (count($DEFAULT_ERROR ?? [])) {
        return;
    }

// ------------------------
//      DISPLAY KPI
// ------------------------
    $DEFAULT_TITLE .= "/{$vars['graph']}";

    // ---------------------------
    //    CONFIG & PARAMS
    // ---------------------------

    $factoryLabel = empty($buList)
        ? 'ALL'
        : implode(' / ', array_map(
            fn(tldLocation $bu) => $bu->getShortName(),
            $buList
        ));

    $from = $start->format('Y-m-d');
    $to = $end->format('Y-m-d');

    $start = (new DateTime($from))->modify('first day of this month');
    $end = (new DateTime($to))->modify('first day of next month');
    $periods = [];
    foreach (new DatePeriod($start, new DateInterval('P1M'), $end) as $dt) {
        $periods[] = $dt->format('Y-m');
    }

    // Language when applicable
    $availableLang = ['en', 'fr'];
    $lang = substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 2);
    if (!in_array($lang, $availableLang, true)) {
        $lang = 'en';
    }

    // Display graphs
    $graphsToDisplay = strtolower($vars['graph']);
    switch ($graphsToDisplay) {
        case 'engineering':
            include("kpi.graph.$graphsToDisplay.comparison.inc.php");
            break;
        default:
            $DEFAULT_ERROR[] = 'This graph is not supported';
            break;
    }

}