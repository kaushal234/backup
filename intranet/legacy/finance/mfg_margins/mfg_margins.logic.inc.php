<?php

include_once("sales_service.inc.php");

// ACL access
if(!$user->isInGroup(array("gg_ADMIN","role_CFO","role_FC"))){
	$DEFAULT_ERROR[] = "ERROR: You do not have permissions for this page..";
	return;
}

$DEFAULT_TITLE .= "\Manufacturing Margins";

switch($m[1]){
case 'view':
	include_once("mfg_margins/view.inc.php");
break;
case 'reports':
	include_once("mfg_margins/mfg_margins.reports.inc.php");
break;
case 'byNum':
    $body = "This page has been migrated and should not be displayed anymore.";
break;
case 'add':
    $body = "This page has been migrated and should not be displayed anymore.";
break;
case 'addmultiple':
    $body = "This page has been migrated and should not be displayed anymore.";
break;
default:
    $body = "This page has been migrated and should not be displayed anymore.";
break;
}
