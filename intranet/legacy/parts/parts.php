<?php
include_once('common.inc.php');
include_once('sales_service.inc.php');
include_once('forms_and_reports.inc.php');
include_once('erp.inc.php');
include_once('ecommerce.inc.php');

session_start();
unset($_SESSION['sess']);
if (!isset($_SESSION['sess'])) {
    $_SESSION['sess'] = null;
}
$sess =& $_SESSION['sess'];

$user = new tldUser($GLOBALS['PHP_AUTH_USER']);

$smarty = tldUtils::getSmarty('intranet');
$smarty->assign('width', '95%');
$smarty->assign('m', $m ?? []);

$php_self = $_SERVER['PHP_SELF'];
$PATH = 'parts';
$DEFAULT_ERROR = [];
$DEFAULT_SUCCESS = [];

$DEFAULT_TITLE = 'Spare Parts Module';
$DEFAULT_MENU = <<<EOF
<a href="$php_self" title="SPH Homepage">Home</a>
&nbsp;|&nbsp;<a href="$php_self?m[0]=forms&m[1]=changeBU" title="Select different Baan company">Change Company</a>
&nbsp;|&nbsp;<a href="/en/private/manufacturing/pur/dev.php?m[0]=isr" title="Interco Shipping Record Module">ISR</a>
&nbsp;|&nbsp;<a href="$php_self?m[0]=spr" title="Spare Parts Requests">SPR</a>
&nbsp;|&nbsp;<a href="/en/private/parts/spq" title="Spare Parts Quote">SPQ</a>
&nbsp;|&nbsp;<a href="$php_self?m[0]=cuno" title="Customer Dashboard">Customer Dashboard</a>
&nbsp;|&nbsp;<a href="/en/private/parts/transportation-note" title="Transporation notes">Transportation Notes</a>
&nbsp;|&nbsp;<a href="$php_self?m[0]=reports" title="Reports">Reports</a>
&nbsp;|&nbsp;<a href="$php_self?m[0]=kpi">KPI</a>
&nbsp;|&nbsp;<a href="/en/private/parts/help/HELP_PARTS.pdf">Help</a>
EOF;

// List of ERP
$erps = tldLocation::getFactoryList('smartyOptions') + tldLocation::getSalesOrgList('smartyOptions') + tldLocation::getSPHList('smartyOptions');
$erps = array_intersect(tldLocation::getERPList('smartyOptions'), $erps);
$erp = $erp ?? 0;
// BU change request
if ($m[1] === 'changeBU' && array_key_exists((int) ($erp), $erps)) {
    $sess['parts']['default_erp'] = $erp;
}
// Look if BU selected
if (isset($sess['parts']['default_erp'])) {
    $DEFAULT_ERP = $sess['parts']['default_erp'];
} else {
    $bu = new tldLocation($user->getBUID());
    $buErp = (int) $bu->getERP();
    // For LAC redirect to AME, default using user's BU ID
    $DEFAULT_ERP = $buErp === 310 ? 300 : $buErp;

    // Check ERP info from user
    if (empty($DEFAULT_ERP) || !array_key_exists($DEFAULT_ERP, $erps)) {
        $DEFAULT_ERP = 300; // Default to AME
    }
    $sess['parts']['default_erp'] = $DEFAULT_ERP;
}


switch ($m[0]) {
    case 'forms':

    case 'dino_trno':
    case 'reports':
    case 'kpi':
    case 'search':
    case 'spr':
        global $kernel;
        $container = $kernel->getContainer();
        $router = $container->get('router');
        $DEFAULT_ERROR[]= sprintf(
            'This module has been migrated. It will remain opened until the existing backlog has been processed. <a href="%s">Please use the new SPR module from now</a>.',
            $router->generate('spare_parts_request_home')
        );
    case 'so':
    case 'cuno':
    case 'inv':
    case 'customer_account':
    case 'cart':
    case 'spq':
        include("{$m[0]}/{$m[0]}.logic.inc.php");
        break;
    default:
        include('homepage.inc.php');
        break;
}
if (isset($JS_INCLUDE)) {
    $smarty->assign('js_includes', $JS_INCLUDE);
}
$smarty->assign('menu', $DEFAULT_MENU . ($menu ?? ''));
$smarty->assign('body', $body ?? '');
$DEFAULT_TITLE .= '/' . $erps[$DEFAULT_ERP];

if (empty($title)) {
    $title = $DEFAULT_TITLE;
}
$smarty->assign('title', $title);
$smarty->assign('error', implode('<br>', $DEFAULT_ERROR));
$smarty->assign("success", implode("<br>", $DEFAULT_SUCCESS));

$DEFAULT_TEMPLATE = 'intranet.tpl';
if (empty($template)) {
    $template = $DEFAULT_TEMPLATE;
}
if ($template !== 'NO_TEMPLATE') {
    $smarty->display($template);
}
