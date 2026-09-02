<?php
include_once("common.inc.php");
include_once("forms_and_reports.inc.php");
require_once ("HTML/QuickForm.php");
require_once('HTML/QuickForm/advmultiselect.php');

session_start();
if(!isset($_SESSION['sess'])) $_SESSION['sess'] = null;
$sess =& $_SESSION['sess'];

$user = new tldUser($GLOBALS["PHP_AUTH_USER"]);

$smarty = tldUtils::getSmarty("intranet");
$php_self = $_SERVER['PHP_SELF'];

$PATH = "common";
$DEFAULT_ERROR = array();
$DEFAULT_TEMPLATE = "intranet.tpl";
$DEFAULT_TITLE = "Common Module Functions";
$DEFAULT_MENU =<<<EOF
<a href="$php_self">Home</a>
&nbsp;|&nbsp;<a href="$php_self?m[0]=models">Models</a>
&nbsp;|&nbsp;<a href="$php_self?m[0]=files">Files</a>
&nbsp;|&nbsp;<a href="$php_self?m[0]=links">Links</a>
&nbsp;|&nbsp;<a href="$php_self?m[0]=logs">Logs</a>
&nbsp;|&nbsp;<a href="$php_self?m[0]=keys">Keys</a>
&nbsp;|&nbsp;<a href="$php_self?m[0]=emails">Emails</a>
&nbsp;|&nbsp;<a href="$php_self?m[0]=kpi">KPI</a>
&nbsp;|&nbsp;<a href="$php_self?m[0]=kpireview">KPI Review Notes</a>
&nbsp;|&nbsp;<a href="$php_self?m[0]=costs">Costs</a>
&nbsp;|&nbsp;<a href="$php_self?m[0]=faq">FAQ</a>
&nbsp;|&nbsp;<a href="$php_self?m[0]=parts">Parts</a>
&nbsp;|&nbsp;<a href="$php_self?m[0]=not">NOT</a>
&nbsp;|&nbsp;<a href="$php_self?m[0]=labors">Labors</a>
EOF;

$ACL = array(
	"members","models","files","links","logs","keys","emails",
	"kpi","kpireview","costs","faq","parts","not","labors"
);

if(in_array($m[0],$ACL)){
	include($m[0]."/logic.".$m[0].".inc.php");
}else{
	$body = $smarty->fetch("$PATH/homepage.$PATH.tpl");
}

if($user->isInGroup("superuser")){
	$smarty->assign("menu",$DEFAULT_MENU);
}

$smarty->assign("body",$body);
if(empty($title)) $title = $DEFAULT_TITLE;
$smarty->assign("title",$title);
$smarty->assign("error",implode("<br>", $DEFAULT_ERROR));

if(empty($template)) $template = $DEFAULT_TEMPLATE;
if($template<>"NO_TEMPLATE")
	$smarty->display($template);

?>
