<?php
$DEFAULT_TITLE .= "\\"._("Help");

switch($m[1] ?? null){
default:
    $body = include("$PATH/help.inc.tpl.php");
break;
}

?>
