<?php
include_once("eng.inc.php");

$DEFAULT_TITLE .= "\Total Cost of Ownership";
$DEFAULT_MENU .=<<<EOF
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<a href="$php_self?m[0]=tco">Home</a>
&nbsp;|&nbsp;<a href="./tco/definitions.pdf">Help</a>
EOF;

$body .= $smarty->fetch("$PATH/tco/homepage.tco.tpl");

?>