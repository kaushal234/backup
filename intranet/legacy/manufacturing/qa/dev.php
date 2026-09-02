<?php
include_once("common.inc.php");
include_once("quality.inc.php");
include_once("forms_and_reports.inc.php");
$JS_INCLUDE=array("/shared/javascript/overlib/overlib.js");

session_start();
if(!isset($_SESSION['sess'])) $_SESSION['sess'] = null;
$sess =& $_SESSION['sess'];

$user = new tldUser($GLOBALS["PHP_AUTH_USER"]);
$smarty = tldUtils::getSmarty("intranet");
$smarty->assign("js_includes",$JS_INCLUDE);
$smarty->assign("m",$m);

$php_self = $_SERVER['PHP_SELF'];

$DEFAULT_MENU =<<<EOF
<a href="$php_self">Home</a>
&nbsp;|&nbsp;<a href="$php_self?m[0]=ncr">NCR</a>
&nbsp;|&nbsp;<a href="$php_self?m[0]=cpa">CPA</a>
&nbsp;|&nbsp;<a href="$php_self?m[0]=scar">SCAR</a>
&nbsp;|&nbsp;<a href="$php_self?m[0]=gt">GT</a>
&nbsp;|&nbsp;<a href="$php_self?m[0]=crab">CRABS</a>
&nbsp;|&nbsp;<a href="$php_self?m[0]=reports">Reports</a>
EOF;
$PATH = "manufacturing/qa";
$DEFAULT_TEMPLATE = "intranet.tpl";
$DEFAULT_TITLE = "Manufacturing\QA";
$DEFAULT_ERROR = array();

if($m[0]){
	include("${m[0]}/${m[0]}.logic.inc.php");
}else{
	$body = $smarty->fetch("$PATH/homepage.qa.tpl");

/*	$report = new tldReportColumnar(tldVWC::byQuery(array("status"=>"VALIDATE_SCAR")),
				array("xItems"=>array("id"=>"VWC #",
						"status"=>"Status",
						"type"=>"Type",
						"date"=>"Date",
						"erp"=>"ERP",
						"location"=>"Location",
						"suno"=>"Supplier Num",
						"module"=>"Module",
						"parent_id"=>"Ref#"),
						"links"=>array("id"=>"/en/private/manufacturing/pur/dev.php?m[0]=vwc&m[1]=view&id="),
						"title"=>"VWCs Pending SCAR Validation"
				)
			);
	$body .= $report->fetch();
*/
}

switch ($sess['encoding']) {
    case 'zh':
        ini_set('default_charset', 'GB2312');
        break;
    case 'utf8':
        ini_set('default_charset', 'UTF-8');
        break;
    default:
        ini_set('default_charset', 'ISO-8859-1');
        break;
}

$smarty->assign("lang",$sess['encoding']);
$smarty->assign("menu",$DEFAULT_MENU.(isset($menu) ? $menu : ''));
$smarty->assign("body",$body);
$smarty->assign("width", 1024);
if(empty($title)) $title = $DEFAULT_TITLE;
$smarty->assign("title",$title);
$smarty->assign("error",implode("<br>", $DEFAULT_ERROR));

if(empty($template)) $template = $DEFAULT_TEMPLATE;
if($template<>"NO_TEMPLATE")
	$smarty->display($template);




?>
