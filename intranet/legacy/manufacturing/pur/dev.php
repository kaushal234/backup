<?php
include_once("eng.inc.php");
include_once("erp.inc.php");
include_once("common.inc.php");
include_once("vendor.inc.php");
include_once("forms_and_reports.inc.php");
include_once ("HTML/QuickForm.php");
$JS_INCLUDE=array("/shared/javascript/overlib/overlib.js");

session_start();
if(!isset($_SESSION['sess'])) {
    $_SESSION['sess'] = null;
}
$sess =& $_SESSION['sess'];

$body = '';
$user = new tldUser($GLOBALS["PHP_AUTH_USER"]);

$smarty = tldUtils::getSmarty("intranet");
$smarty->assign("js_includes",$JS_INCLUDE);
$smarty->assign("m",$m);

$php_self = $_SERVER['PHP_SELF'];
$DEFAULT_MENU =<<<EOF
<a href="$php_self">Home</a>
&nbsp;|&nbsp;<a href="$php_self?m[0]=forms&m[1]=changeBU" title="Select different Baan company">Change Company</a>
&nbsp;|&nbsp;<a href="$php_self?m[0]=vendors">Vendors</a>
 | <a href="/en/private/materials/evendors_news">eVendors News</a>
&nbsp;|&nbsp;<a href="$php_self?m[0]=xref">Xref</a>
&nbsp;|&nbsp;<a href="$php_self?m[0]=po">PO</a>
&nbsp;|&nbsp;<a href="$php_self?m[0]=isr" title="Interco Shipping Record Module">ISR</a>
&nbsp;|&nbsp;<a href="$php_self?m[0]=vwc">VWC</a>
&nbsp;|&nbsp;<a href="$php_self?m[0]=rfq">RFQ</a>
&nbsp;|&nbsp;<a href="$php_self?m[0]=stdcost">Std Cost</a>
&nbsp;|&nbsp;<a href="$php_self?m[0]=reports">Reports</a>
EOF;

if($user->isInGroup(array("role_MLM","gg_ENG","gg_PUR"))){
    $DEFAULT_MENU .=<<<EOF
&nbsp;|&nbsp;<a href="/en/private/manufacturing/eng/dev.php?m[0]=bom">BOM Benchmark</a>
EOF;
}

$erps = tldLocation::getERPList('smartyOptions');
if($m[1]=='changeBU' && !empty($_REQUEST['erp']) && in_array($_REQUEST['erp'], array_keys($erps))){
    $sess['pur']['default_erp'] = $_REQUEST['erp'];
}
if(isset($sess['pur']['default_erp'])){
    $DEFAULT_ERP = $sess['pur']['default_erp'];
}else{
    // Trying to found user location
    $buid = $user->getBUID();
    if(!empty($buid)){
        $bu = new tldLocation($buid);
        $DEFAULT_ERP = $bu->getERP();
    }
    if(!in_array($DEFAULT_ERP, array_keys($erps))){
        $DEFAULT_ERP = $bu->getERP();
    }
    $sess['pur']['default_erp'] = $DEFAULT_ERP;
}
$PATH = "manufacturing/pur";
$DEFAULT_TEMPLATE = "intranet.tpl";
$DEFAULT_TITLE = "Manufacturing\Purchasing\ERP#$DEFAULT_ERP";
$DEFAULT_ERROR = array();

switch($m[0]){
case 'isr':
    include("{$m[0]}/{$m[0]}.logic.inc.php");
break;
case 'vendors':
case 'xref':
case 'po':
case 'vwc':
case 'rfq':
case 'mrp':
case 'stdcost':
case 'reports':
case 'forms':
    include($m[0].'/logic.'.$m[0].'.inc.php');
break;
case 'home':
default:
    include("homepage.inc.php");
break;
}

$smarty->assign("menu",$DEFAULT_MENU.(isset($meun) ? $menu : ''));
$smarty->assign("body",$body);
if(empty($title)) {
    $title = $DEFAULT_TITLE;
}
$smarty->assign("title",$title);
$smarty->assign("error",implode('<br/>',$DEFAULT_ERROR));

if(empty($template)) {
    $template = $DEFAULT_TEMPLATE;
}
if($template<>"NO_TEMPLATE") {
    $smarty->display($template);
}
?>
