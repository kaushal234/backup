<?php
require_once 'HTML/QuickForm/advmultiselect.php';
$DEFAULT_TITLE .= "\ML";

$DEFAULT_MENU .=<<<EOF
	<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
	<a href="$php_self?m[0]=ml">ML Home</a>
	&nbsp;|&nbsp;<a href="$php_self?m[0]=ml&m[1]=forms&m[2]=byID">By Number</a>
	&nbsp;|&nbsp;<a href="$php_self?m[0]=ml&m[1]=reports">Reports</a>
EOF;
if($user->isInGroup(array("gg_ENG","gg_ADMIN"))){
	$DEFAULT_MENU .=<<<EOF
	&nbsp;|&nbsp;<a href="./ml/ml_admin.php">Maintain MLs</a>
EOF;
}

switch($m[1]){
default:
	$body = $smarty->fetch("$PATH/ml/homepage.ml.tpl");
}


?>