<?php
include_once("sales_service.inc.php");
include_once("erp.inc.php");
require_once("HTML/QuickForm.php");
require_once('HTML/QuickForm/advmultiselect.php');
require_once 'HTML/QuickForm/altselect.php';

$DEFAULT_TITLE .= "\eCustomers";
$DEFAULT_MENU.=<<<EOF
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<a href="$php_self?m[0]=customers">Home</a>
&nbsp;|&nbsp;<a href="$php_self?m[0]=customers&m[1]=forms&m[2]=byNum">By Number</a>
&nbsp;|&nbsp;<a href="$php_self?m[0]=customers&m[1]=forms&m[2]=advsearch">Search</a>
&nbsp;|&nbsp;<a href="$php_self?m[0]=customers&m[1]=reports">Reports</a>
EOF;

switch($m[1]){
case 'forms':
    $body .= "This page has been migrated and should not be displayed anymore.";
break;
case 'approval':
	include("customers.approval.inc.php");
break;
case 'cleanup':
	$body .= "This page has been migrated and should not be displayed anymore.";
break;
case 'view':
    switch($m[2]){
    case 'editcrt':
    	$body = "This page has been migrated and should not be displayed anymore.";
    break;
    case 'edit':
        $body = "This page has been migrated and should not be displayed anymore.";
    break;
    case 'crt':
        $body = "This page has been migrated and should not be displayed anymore.";
    break;
    case 'hierarchy':
        $body = "This page has been migrated and should not be displayed.";
    break;
    case 'email':
        $body = "This page has been migrated and should not be displayed.";
    break;
    case 'zip':
        if($user->isInGroup(array("gg_SALES","gg_PARTS","gg_SUPPORT","gg_ADMIN","sales_cust_admin"))){
            include("customers.zip.logic.inc.php");
        }else{
            $DEFAULT_ERROR[] = "ERROR: You do not have permission to access this function";
        }
    break;
    case 'so':
        $body = "This page has been migrated and should not be displayed anymore.";
    break;
    case 'inv':
        $body = "This page has been migrated and should not be displayed anymore.";
    break;
    case 'ps':
        $body = "This page has been migrated and should not be displayed anymore.";
    break;
    case 'contacts':
        $body = "This page has been migrated and should not be displayed anymore.";
    break;
	case 'delete':
    	$body = "This page has been migrated and should not be displayed anymore.";
	break;
    case 'log':
        $body = "This page has been migrated and should not be displayed anymore.";
    break;
    default:
        $body = "This page has been migrated and should not be displayed anymore.";
    }
break;
case 'byType':
    $body = "This page has been migrated and should not be displayed anymore.";
break;
case 'reports':
	$body = "This page has been migrated and should not be displayed anymore.";
break;
case 'list':
    $body = "This page has been migrated and should not be displayed anymore.";
break;
case 'last_seq':
    $body = "This page has been migrated and should not be displayed anymore.";
break;
default:
    $body = "This page has been migrated and should not be displayed anymore.";
}

function _getListing($rows,$title,$opt=array()){
    global $php_self;
    // options
    if(!empty($opt['functions'])) $functions = $opt['functions'];
    // Report
    $report = new tldReportColumnar(
        $rows,
        array(
            "xItems"=>array(
                "id"=>"ID#",
                "customer_name"=>"Customer Name",
                "type"=>"Type",
                "customer_address"=>"Address",
                "country"=>"Country",
                "customer_tel"=>"Tel",
                "customer_fax"=>"Fax",
            	"asm_fullname"=>"ASM"
            ),
            "title"=>$title,
            "links"=>array("id"=>"$php_self?m[0]=customers&m[1]=view&id="),
            "functions"=>$functions
        )
    );
    return $report->fetch();
}

?>