<?php
include_once("erp.inc.php");

$DEFAULT_TITLE .= "\AP (BETA)";

switch($m[1]){
	case 'CreateApInv':
	case 'form':
	case 'view':
	case 'reports':
	case 'approvers':
	default:
		$body = "This page has been migrated and should not be displayed anymore.";
	break;
}
?>
