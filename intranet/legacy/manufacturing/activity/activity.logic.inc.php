<?php
include_once("finance.inc.php");
if (!$user->isInGroup(["gg_ADMIN", "role_PSM", "role_PSE", "role_PSA", "role_SA", "gg_ACCT", "role_COO", "role_CMO", "role_MLM", "role_PM", "role_planner"])) {
    $DEFAULT_ERROR[] = "ERROR: You do not have permission to access this module";
    return; // return used to get out of the include (see: http://us2.php.net/manual/en/function.include.php)
}

$DEFAULT_MENU .=<<<EOF
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<a href="$php_self?m[0]=activity">Home</a>
&nbsp;|&nbsp;<a href="$php_self?m[0]=activity&m[1]=bookings">Bookings</a>
&nbsp;|&nbsp;<a href="$php_self?m[0]=activity&m[1]=revenue">Revenue</a>
&nbsp;|&nbsp;<a href="$php_self?m[0]=activity&m[1]=backlog">Backlog</a>
&nbsp;|&nbsp;<a href="$php_self?m[0]=activity&m[1]=changeDcur">Change Display Currency</a>
&nbsp;|&nbsp;<a href="$php_self?m[0]=activity&m[1]=reports">Reports</a>
EOF;
if($user->isInGroup(array("gg_ADMIN","gg_ACCT"))) {
    $DEFAULT_MENU .=<<<EOF
        &nbsp;|&nbsp;<a href="/en/private/finance/sor_tran/sor_tran_admin.php" title="SOR Transaction Admin">Trans Admin</a>
EOF;
}

$FIELDS = array(
    "location_from"=>"SSO",
    "location_to"=>"Factory",
    "asm_fullname"=>"ASM",
    "sor_id"=>"SOR ID#",
    "cu_nama"=>"Customer Name",
    "cu_orno"=>"Customer PO#",
    "ctry"=>"Country",
    "sol_id"=>"SOL ID#",
    "sls_orno"=>"SSO PO# to Factory",
    "period"=>"Booking Month",
    "dgt_rev"=>"Current GT Date",
    "dgt_act"=>"Actual GT Date",
    "sols_model"=>"Model Ordered",
    "ers_model"=>"Model Assigned",
    "sn"=>"Serial Number",
    "cust_asset_num"=>"Customer Asset Number",
    "qty"=>"# of Units in SOL",
    "tval_dcur"=>"Unit ".ucfirst($m[1])." Amount"
);

// Enlarge width when report list asked
if($m[2]=="listBySSOERPPeriod") {
    $smarty->assign("width",1024);
}
if(empty($sess['manufacturing']['activity']['dcur']))
    $sess['manufacturing']['activity']['dcur'] = 'USD';

$DCUR = &$sess['manufacturing']['activity']['dcur'];

switch($m[1]) {
case 'reports':
	include("reports.inc.php");
break;
case 'backlog':
    include("backlog.inc.php");
break;
case 'revenue':
    include("revenue.inc.php");
break;
case 'graphs':
	include("graphs.inc.php");
break;
case 'bookings':
    include("bookings.inc.php");
break;
case 'changeDcur':
    $report = new tldHTMLList(tldForex::getCurrencyList(),
        '', "$php_self?m[0]=activity&dcur=");
    $body .= $report->fetch();
break;
default:
    if($dcur) $sess['manufacturing']['activity']['dcur'] = $dcur;
    $DEFAULT_ERROR[] = "Default currency is ".$sess['manufacturing']['activity']['dcur'];
    $body .= $smarty->fetch("$PATH/activity/homepage.activity.tpl");
}

$DEFAULT_TITLE .= "\Manufacturing Sales Activity BETA ($DCUR)";

// If CSV asked
switch($m[3]) {
case "xls":
    $option = array("xItems"=>$FIELDS,"showTitles"=>true);
    $report = new tldXLS($rows,	$option);
    $report->out();
    exit;
break;
}




?>