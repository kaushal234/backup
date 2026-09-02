<?php
include_once("erp.inc.php");
include_once("common.inc.php");
include_once("forms_and_reports.inc.php");
include_once('vault.inc.php');
include_once("HTML/QuickForm.php");
$PATH = "manufacturing";

session_start();
if(!isset($_SESSION['sess'])) {
    $_SESSION['sess'] = null;
}
$sess =& $_SESSION['sess'];

$user = new tldUser($GLOBALS["PHP_AUTH_USER"]);
$body = '';
$smarty = tldUtils::getSmarty("intranet");
$DEFAULT_ERROR = [];
$DEFAULT_SUCCESS = [];

$php_self = $_SERVER['PHP_SELF'];
$DEFAULT_TEMPLATE = "intranet.tpl";
$DEFAULT_TITLE = "Manufacturing";
$DEFAULT_MENU =<<<EOF
<a href="$php_self?m[0]=" title="Manufacturing Homepage">Home</a>
&nbsp;|&nbsp;<a href="qa/dev.php"title="Quality Module">Quality</a>
&nbsp;|&nbsp;<a href="eng/dev.php"title="Engineering Module">Engineering</a>
&nbsp;|&nbsp;<a href="pur/dev.php"title="Purchasing Module">Purchasing</a>
&nbsp;|&nbsp;<a href="whse/dev.php"title="Warehouse Module">Warehouse</a>
&nbsp;|&nbsp;<a href="$php_self?m[0]=planning" title="Planning Module">Planning</a>
&nbsp;|&nbsp;<a href="$php_self?m[0]=kpi" title="Key Performance Indicators">KPI</a>
&nbsp;|&nbsp;<a href="$php_self?m[0]=kpi2" title="Key Performance Indicators">KPI (NEW VERSION)</a>
&nbsp;|&nbsp;<a href="$php_self?m[0]=activity" title="Manufacturing Activity">Activity</a>
&nbsp;|&nbsp;<a href="$php_self?m[0]=pi" title="Process & Inspection Online">P&I</a>
&nbsp;|&nbsp;<a href="$php_self?m[0]=reports" title="Manufacturing Reports">Reports</a>
EOF;

$ALLOWED_MODULES = array("kpi","kpi2","activity", "planning", "reports", "pi");

if(($m[0] ?? null) && in_array($m[0], $ALLOWED_MODULES, true)){
    include($m[0].'/'.$m[0].'.logic.inc.php');
}else{
    $smarty->assign('baseUrl', tldWebCam::BASE_URL);
    $smarty->assign('cameras', tldWebCam::getCamList());
    $body = $smarty->fetch("$PATH/homepage.mfg.tpl");
}

$smarty->assign("error",implode("<br>", $DEFAULT_ERROR));
$smarty->assign("success",implode("<br>", $DEFAULT_SUCCESS));
$smarty->assign("menu",$DEFAULT_MENU.(isset($menu) ? $menu : ''));
if(empty($title))  {
    $title = $DEFAULT_TITLE;
}
$smarty->assign("title",$title);
$smarty->assign("body",$body);

if(empty($template)) {
    $template = $DEFAULT_TEMPLATE;
}
if($template !== "NO_TEMPLATE"){
    $smarty->display($template);
}
