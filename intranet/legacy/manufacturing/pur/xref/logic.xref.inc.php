<?php
$DEFAULT_TITLE .= "\XRef";

switch($m[1]){
	case "searchByAltPNByTLDPNByCustomer":
	case "searchByCustomerPN":
	case "searchByVendorPN":
	case "searchByAltPNByTLDPN":
	case "searchByXerfDESC":
	default:
	$body .= "This page has been migrated and should not be displayed anymore.";
}
?>
