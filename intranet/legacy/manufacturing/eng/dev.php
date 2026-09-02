<?php

declare(strict_types=1);
require_once __DIR__.'/../../legacy_autoload.php';
require_once 'autoload_shared.php';

include_once("erp.inc.php");
include_once("eng.inc.php");
include_once("forms_and_reports.inc.php");
include_once("common.inc.php");
include_once("vault.inc.php");

session_start();
if(!isset($_SESSION['sess'])) {
    $_SESSION['sess'] = null;
}
$sess =& $_SESSION['sess'];

$user = new tldUser($GLOBALS["PHP_AUTH_USER"]);

$smarty = tldUtils::getSmarty("intranet");
$smarty->assign("locations", tldLocation::getFactoryList("smartyOptions"));

$php_self = $_SERVER['PHP_SELF'];

// Identify user BU
$erps = tldLocation::getFactoryList('smartyOptionsERPLocation');
if($m[1] === 'changeBU' && !empty($_REQUEST['erp']) && in_array($_REQUEST['erp'], array_keys($erps))){
    $sess['eng']['default_erp'] = $_REQUEST['erp'];
}
if(isset($sess['eng']['default_erp'])){
    $DEFAULT_ERP = $sess['eng']['default_erp'];
}else{
    // Trying to found user location
    $buid = $user->getBUID();
    if(!empty($buid)){
        $bu = new tldLocation($buid);
        $DEFAULT_ERP = $bu->getERP();
    }
    $sess['eng']['default_erp'] = $DEFAULT_ERP;
}

$DEFAULT_MENU =<<<EOF
<a href="$php_self">Home</a>
&nbsp;|&nbsp;<a href="$php_self?m[0]=meap" title="Master EAP (Engineering Activity Process)">MEAP</a>
&nbsp;|&nbsp;<a href="$php_self?m[0]=eap" title="Engineering Activity Process">EAP</a>
&nbsp;|&nbsp;<a href="$php_self?m[0]=cbom" title="Customized Bill of Materials">CBOM</a>
&nbsp;|&nbsp;<a href="$php_self?m[0]=bom" title="Bill of Materials">BOM</a>
&nbsp;|&nbsp;<a href="$php_self?m[0]=revisions_detail" title="Revisions Detail">Revisions Detail</a>
&nbsp;|&nbsp;<a href="$php_self?m[0]=pip" title="Product Inovation Proposals">PIP</a>
&nbsp;|&nbsp;<a href="$php_self?m[0]=item_reservation" title="Item Reservation">Item Reservation</a>
&nbsp;|&nbsp;<a href="/en/private/parts/parts.php?m[0]=inv&m[1]=view" title="Global Inventory">Inventory</a>
&nbsp;|&nbsp;<a href="/en/private/mis/mis.php?m[0]=help&m[1]=dms&id=328" title="Best Practices">Best Practices</a>
&nbsp;|&nbsp;<a href="$php_self?m[0]=timekeeping" title="Timekeeping Module">Timekeeping</a>
&nbsp;|&nbsp;<a href="$php_self?m[0]=reports" title="Reports">Reports</a>
&nbsp;|&nbsp;<a href="$php_self?m[0]=reports&m[1]=search">Search Tools</a>
EOF;

$PATH = "manufacturing/eng";
$DEFAULT_TEMPLATE = "intranet.tpl";
$DEFAULT_TITLE = "Manufacturing\Engineering\ERP#$DEFAULT_ERP";
$DEFAULT_ERROR = [];
$DEFAULT_SUCCESS = [];

tldUtils::logUserAccess();

switch($m[0]){
case 'avail':
case 'bom':
case 'cbom':
case 'dash':
case 'eap':
case 'revisions_detail':
case 'forms':
case 'getfile':
case 'meap':
case 'ml':
case 'pip':
case 'item_reservation':
case 'reports':
case 'searchpn':
case 'tco':
case 'timekeeping':
case 'rtb':
    include("{$m[0]}/{$m[0]}.logic.inc.php");
    break;
default:
    include("homepage.inc.php");
break;
}

$smarty->assign("error", implode("<br>", $DEFAULT_ERROR));
$smarty->assign("success", implode("<br>", $DEFAULT_SUCCESS));
$smarty->assign("menu", $DEFAULT_MENU);
$smarty->assign("body", $body);

if(empty($title)) {
    $title = $DEFAULT_TITLE;
}
$smarty->assign("title", $title);

if(empty($template)) {
    $template = $DEFAULT_TEMPLATE;
}
if($template<>"NO_TEMPLATE") {
    $smarty->display($template);
}

