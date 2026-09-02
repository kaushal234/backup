<?php
$DEFAULT_TITLE .= "Forex";

$userid = $_SERVER["PHP_AUTH_USER"];
$self = $_SERVER["PHP_SELF"];

$DEFAULT_MENU .= <<<EOF
	<br>
	&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<a href="$self?m[0]=forex">Home</a>
	&nbsp;|&nbsp;<a href="$self?m[0]=forex&m[1]=reports&m[2]=csv">Download CSV</a>
EOF;
$currentUser = new tldUser($GLOBALS["PHP_AUTH_USER"]);
if($currentUser->isInGroup(array("gg_ADMIN","gg_ACCT","erp_forex")))
	$DEFAULT_MENU .=<<<EOF
	&nbsp;|&nbsp;<a href="forex/forex_admin.php">Edit Integrations</a>
EOF;


switch($m[1]){
default:
}

?>
