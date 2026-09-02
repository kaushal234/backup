<?php
include_once("common.inc.php");
include_once("sales_service.inc.php");
include_once("forms_and_reports.inc.php");

session_start();
if(!isset($_SESSION['sess'])) {
    $_SESSION['sess'] = null;
}
$sess =& $_SESSION['sess'];

$user = new tldUser($GLOBALS["PHP_AUTH_USER"]);
$body = '';
$smarty = tldUtils::getSmarty("intranet");
$smarty->assign("m",$m);
$php_self = $_SERVER['PHP_SELF'];

// Get default SALES BU
$locations = tldLocation::getLocationList('smartyOptions');
if($m[1]=='changeBU' && in_array($_REQUEST['buid'], array_keys($locations))){
    $sess['sales']['default_buid'] = $_REQUEST['buid'];
}
// Trying to found user location
if(!isset($sess['sales']['default_buid'])){
    // Try from account info
    $buid = $user->getBUID();
    if(!empty($buid)){
        $bu = new tldLocation($buid);
        $DEFAULT_BUID = $bu->getID();
    }
    // Try from SALES permissions
    else{
        $erp = $user->isInGroup("gg_SALES");
        if(is_array($erp)){
            $erp = $erp[0];
        }
        $DEFAULT_BUID = tldLocation::getIDByERP($erp);
    }
    // If nothing found, default to TLD EUR
    if(!in_array($DEFAULT_BUID, array_keys($locations))){
        $DEFAULT_BUID = 7;
    }
    $sess['sales']['default_buid'] = $DEFAULT_BUID;
}

$DEFAULT_BUID = TldDatabase::escape($sess['sales']['default_buid']);
$DEFAULT_BU = new tldLocation($DEFAULT_BUID);

$PATH = "sales_service";
$DEFAULT_TEMPLATE = "intranet.tpl";
$DEFAULT_TITLE = "Sales Module\\".$DEFAULT_BU->getShortName();
$DEFAULT_ERROR = array();
$JS_INCLUDE	= array();

$DEFAULT_MENU =<<<EOF
<a href="$php_self">Home</a>
EOF;
if(!$user->isAgent() || ($user->isAgent() && $user->isInGroup("gg_SALES_AGENTS"))){
	$DEFAULT_MENU .=<<<EOF
&nbsp;|&nbsp;<a href="$php_self?m[0]=forms&m[1]=changeBU">Change Company</a>
EOF;
}
$DEFAULT_MENU .=<<<EOF
&nbsp;|&nbsp;<a href="$php_self?m[0]=catalogue">Catalogue</a>
EOF;
if(!$user->isAgent() || ($user->isAgent() && $user->isInGroup("gg_SALES_AGENTS"))){
	$DEFAULT_MENU .=<<<EOF
&nbsp;|&nbsp;<a href="$php_self?m[0]=equotes" target="_blank">Equotes</a>
EOF;
}
$DEFAULT_MENU .=<<<EOF
&nbsp;|&nbsp;<a href="$php_self?m[0]=gse_aircraft_data">GSE/AC&nbsp;Data</a>
EOF;
if(!$user->isAgent() || ($user->isAgent() && $user->isInGroup("gg_SALES_AGENTS"))){
	$DEFAULT_MENU .=<<<EOF
&nbsp;|&nbsp;<a href="$php_self?m[0]=customers">eCustomers</a>
&nbsp;|&nbsp;<a href="$php_self?m[0]=crt" title="Customers Relationship Team">CRT</a>
&nbsp;|&nbsp;<a href="$php_self?m[0]=extranet">eContact</a>
&nbsp;|&nbsp;<a href="$php_self?m[0]=cor" title="Competitive Data">Competition</a>
&nbsp;|&nbsp;<a href="$php_self?m[0]=questionaire">Questionaire</a>
&nbsp;|&nbsp;<a href="/en/private/product_support/index.ps.php?m[0]=odp" title="On time delivery planning">ODP</a>
&nbsp;|&nbsp;<a href="$php_self?m[0]=inventory" title="Equipment Inventory">Inventory</a>
&nbsp;|&nbsp;<a href="$php_self?m[0]=sor" title="Sales Order Records">SOR</a>
&nbsp;|&nbsp;<a href="$php_self?m[0]=sol" title="Sales Order Lines">SOL</a>
&nbsp;|&nbsp;<a href="$php_self?m[0]=esr" title="Equipment Shipping Records">ESR</a>
&nbsp;|&nbsp;<a href="salesareas/salesareas_admin.php" title="Sales Areas">Sales Areas</a>
&nbsp;|&nbsp;<a href="$php_self?m[0]=activity2" title="Sales Activity Reports">Sales Activity</a>
EOF;
    if(true === $user->isInGroup(array("role_ASM","gg_SALES_AGENTS")) && !$user->isInGroup(array("role_EVP"))){
        $DEFAULT_MENU .="&nbsp;|&nbsp;<a href=\"$php_self?m[0]=sfr&m[1]=home&m[2]=asmDashboard\" title=\"Sales Forecast Records\">SFR</a>\n";
    }else{
        $DEFAULT_MENU .="&nbsp;|&nbsp;<a href=\"$php_self?m[0]=sfr\" title=\"Sales Forecast Records\">SFR</a>\n";
    }
    $DEFAULT_MENU .=<<<EOF
&nbsp;|&nbsp;<a href="$php_self?m[0]=fcr" title="Forecast Closure Records">FCR</a>
&nbsp;|&nbsp;<a href="$php_self?m[0]=cpr" title="Competitor Pricing Record">CPR</a>
&nbsp;|&nbsp;<a href="$php_self?m[0]=mim" title="Market Intelligence Module">MIM</a>
&nbsp;|&nbsp;<a href="$php_self?m[0]=reports" title="Sales Reports and Statistic">Reports</a>
&nbsp;|&nbsp;<a href="$php_self?m[0]=kpi" title="Key Performance Indicators">KPI</a>
&nbsp;|&nbsp;<a href="$php_self?m[0]=ccr" title="Customer Communication Records">CCR (ALPHA)</a>
EOF;
}

if($user->isInGroup(array("gg_MIS","acl_tld_cms","acl_tld_cmsl"))){
	$DEFAULT_MENU .="&nbsp;|&nbsp;<a href='$php_self?m[0]=sac' title='Sales App Configurator'>Sales App Configurator (TLD CMS)</a>";
}
if($user->isInGroup(array("role_ASM","role_CEO","role_COO","role_CHAIRMAN", "role_CSD"))){
    $DEFAULT_MENU .="&nbsp;|&nbsp;<a href='$php_self?m[0]=cust_feedback' title='Customer Feedback'>Customer Feedback</a>";
}

$date = new DateTime();


switch($m[0]){
case 'ccr':
case 'kpi':
case 'activity2':
case 'so':
case 'sol':
case 'sor':
case 'catalogue':
case 'equotes':
case 'gse_aircraft_data':
case 'customers':
case 'crt':
case 'extranet':
case 'cor':
case 'questionaire':
case 'esr':
case 'sfr':
case 'fcr':
case 'mim':
case 'reports':
case 'forms':
case 'inventory':
case 'cpr':
case 'sac':
case 'cust_feedback':
case 'kpi_pdi_crab':
	include($m[0].'/'.$m[0].'.logic.inc.php');
break;
case 'home':
default:
    include('homepage.inc.php');
break;
}

$smarty->assign("js_includes", $JS_INCLUDE);
$smarty->assign("menu",$DEFAULT_MENU.($menu ?? ''));
$smarty->assign("body",$body);
if(empty($title)) {
    $title = $DEFAULT_TITLE;
}
$smarty->assign("title",$title);
$smarty->assign("error",implode("<br>", $DEFAULT_ERROR));

if(empty($template)) {
    $template = $DEFAULT_TEMPLATE;
}
if($template !== "NO_TEMPLATE") {
    $smarty->display($template);
}
