<?php
include_once("calendar.inc.php");
include_once("HTML/QuickForm.php");
include_once("forms_and_reports.inc.php");
include_once("quality.inc.php");
include_once("erp.inc.php");
include_once("sales_service.inc.php");
require_once 'HTML/QuickForm/advmultiselect.php';

$DEFAULT_TITLE .= "\NCR";

switch($m[1]){
	case "forms":
		switch($m[2]){
		case 'parts.getinfo':
		case "addParts":
		case "newNCR":
		case "byID":
		case "changeStatus":
			$body .= "This page has been migrated and should not be displayed anymore.";
		}
	break;
	case "view":
		switch($m[2]) {
			case "MRB":
			case "parts":
			case 'edit':
			case 'log':
			case "selectVendor":
			case "print":
			case 'links':
			case "files":
			case "costs":
			case "models":
			case "tasks":
			case "car":
			case "txVendor":
			case "changeStatus":
				$body = "This page has been migrated and should not be displayed anymore.";
		}
	break;
	case "reports":
	case 'list':
	default:
		$body = "This page has been migrated and should not be displayed anymore.";
}
?>
