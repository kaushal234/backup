<?php
include_once("common.inc.php");
include_once("calendar.inc.php");
include_once("forms_and_reports.inc.php");
include_once("ils.inc.php");
include_once("erp.inc.php");
include_once("mis.inc.php");
include_once("sales_service.inc.php");
include_once("dms.inc.php");

session_start();
if(!isset($_SESSION['sess'])) $_SESSION['sess'] = null;
$sess =& $_SESSION['sess'];

$smarty = tldUtils::getSmarty("intranet");
$DEFAULT_TITLE = "ALVEST Directory";
$DEFAULT_ERROR = array();
$user = new tldUser($GLOBALS["PHP_AUTH_USER"]);
$userid = $_SERVER["PHP_AUTH_USER"];
$php_self = $_SERVER["PHP_SELF"];

$DEFAULT_MENU = <<<EOF
    <tld:include src="directory/_menu.html.twig" />
EOF;

switch($m[0]){
case 'divisions':
case 'bu':
case 'locations':
case 'departments':
case 'functions':
case 'people':
case 'orgChart':
case 'reports':
case 'jobs':
    include("{$m[0]}/{$m[0]}.logic.inc.php");
break;
case 'outPhoto':
    /**
     * For compatibility
     * @todo to delete
     */
    header("Location: $php_self?m[0]=people&m[1]=view&m[2]=photo&m[3]=out&id=$id&width=$width");
break;
case 'card':
    /**
     * For compatibility
     * @todo to delete
     */
    header("Location: $php_self?m[0]=people&m[1]=view&id={$n[0]}");
break;
default:
	$body = include('index.tpl.php');
break;
}

$smarty->assign("title", $DEFAULT_TITLE);
$smarty->assign("menu", $DEFAULT_MENU);
$smarty->assign("error",implode("<br>", $DEFAULT_ERROR));
$smarty->assign("body", $body);
$smarty->display("intranet.tpl");
?>
