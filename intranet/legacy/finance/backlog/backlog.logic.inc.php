<?php
include_once("sales_service.inc.php");
$DEFAULT_TITLE .= "\SSO Bookings Summary";
//$DEFAULT_MENU .=<<<EOF
//<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
//<a href="$php_self?m[0]=backlog">Home</a>
//EOF;

switch($m[1]){
default:
	$body .= "<h3>SSO Bookings Summary</h3>";
	$y = date('Y');
	$form = new tldMatrix(tldSOR::sumBookingsBySSOERPYear('TLD AME', 'ALL', $y),
				"location_to", "period", "bookings",
				"/en/private/sales_service/sales.php?m[0]=activity&m[1]=bookings&m[2]=listBySSOERPPeriod&year=$y&z=TLD AME",
				"$y Bookings for TLD AME, in USD"
	);
	$body .= $form->fetch();
	$form = new tldMatrix(tldSOR::sumBookingsBySSOERPYear('TLD EUR', 'ALL', $y),
				"location_to", "period", "bookings",
				"/en/private/sales_service/sales.php?m[0]=activity&m[1]=bookings&m[2]=listBySSOERPPeriod&year=$y&z=TLD EUR",
				"$y Bookings for TLD EUR, in USD"
	);
	$body .= $form->fetch();
	$form = new tldMatrix(tldSOR::sumBookingsBySSOERPYear('TLD ASI', 'ALL', $y),
				"location_to", "period", "bookings",
				"/en/private/sales_service/sales.php?m[0]=activity&m[1]=bookings&m[2]=listBySSOERPPeriod&year=$y&z=TLD ASI",
				"$y Bookings for TLD ASI, in USD"
	);
	$body .= $form->fetch();
}

?>