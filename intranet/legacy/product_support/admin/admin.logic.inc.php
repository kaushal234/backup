<?php
require_once ("HTML/QuickForm.php");
include_once("calendar.inc.php");
//this file is included in an upper level file, index.ps.php
$DEFAULT_TITLE .= "\Admin";
$DEFAULT_MENU .=<<<EOF
	<br>
	&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
	<a href="$php_self?m[0]=admin">Admin Home</a>
	&nbsp;|&nbsp;<a href="/en/private/product_support/admin/models_admin.php">Maintain Models</a>
	&nbsp;|&nbsp;<a href="/en/private/product_support/admin/airport_codes_admin.php">Maintain Airport Codes</a>
	&nbsp;|&nbsp;<a href="/en/private/sales/aircraft-compatibilities">Maintain NTO</a>
EOF;

switch($m[1]){
default:
	$body .= $smarty->fetch("$PATH/admin/homepage.admin.tpl");
}
?>