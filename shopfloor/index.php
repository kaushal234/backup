<?php
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\PhpBridgeSessionStorage;

require __DIR__ . '/vendor/autoload.php';

session_start();

$session = new Session(new PhpBridgeSessionStorage());
$session->start();

if(!isset($_SESSION['sess'])) $_SESSION['sess'] = [];
$sess =& $_SESSION['sess'];

include_once("common.inc.php");
include_once("dms.inc.php");
include_once("forms_and_reports.inc.php");

$user = new tldUser($GLOBALS["PHP_AUTH_USER"] ?? null);
$smarty = tldUtils::getSmarty("shopfloor");
$php_self = $_SERVER['PHP_SELF'];

$PATH = "";
$DEFAULT_TEMPLATE = "site.tpl";
$DEFAULT_ERROR = array();

// Identify location
$locations = tldLocation::byNetworkAddress(tldUtils::getClientIp());

if(count($locations)<>1){
	$DEFAULT_ERROR[] = _("ERROR: Could not identify location from network address");
	$m=""; // Reset the operation
	$ERP = 'UNKNOWN';
}else{
	$LOCATION = $locations[0];
	$ERP = $LOCATION["erp"];
	$smarty->assign("ERP",$ERP);
}

$DEFAULT_TITLE = "Homepage";
$DEFAULT_MENU =<<<EOF
<a href="$php_self">HOME ($ERP)</a>
&nbsp;|&nbsp;<a href="file://///winfps01/pub_qa/qms/index.htm">QMS</a>
&nbsp;|&nbsp;<a href="$DMS_URL">DMS</a>
&nbsp;|&nbsp;<a href="/shop/autoselect.php">Shop</a>
&nbsp;|&nbsp;<a href="$php_self?m[0]=hr">HR</a>
&nbsp;|&nbsp;<a href="$php_self?m[0]=news">News</a>
&nbsp;|&nbsp;<a href="file://///winfps01/pub_qa/templates/">Templates</a>
EOF;

switch($m[0] ?? null){
case 'dms':
    if(empty($id) || !is_numeric($id)){
        $DEFAULT_ERROR[] = "ERROR: parameter set empty or invalid";
        break;
    }
    // Get File
    $e = tldDMS::downloadByPortal($id, array("INTRANET","EVENDOR","EXTRANET"), false, $user);
    if(is_string($e)){
        $DEFAULT_ERROR[] = $e;
    }
break;
case 'news':
case 'hr':
	include($m[0].'/logic.'.$m[0].'.inc.php');
break;
default:
    $body = include("homepage.tpl.php");
break;
}

$DEFAULT_ERROR = implode("<br>", $DEFAULT_ERROR);
$smarty->assign("location",$LOCATION["location"]);
$smarty->assign("error",$DEFAULT_ERROR);
$smarty->assign("menu",$DEFAULT_MENU.(isset($menu) ? $menu : ''));
$smarty->assign("body",$body);

if(empty($title)) $title = $DEFAULT_TITLE;
$smarty->assign("title",$title);

if(empty($template)) $template = $DEFAULT_TEMPLATE;
if($template<>"NO_TEMPLATE"){
	$smarty->display($template);
}
?>
