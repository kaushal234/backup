<?php
$DEFAULT_TITLE .= "\KPI";
$DEFAULT_MENU .=<<<EOF
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<a href="$php_self?m[0]=kpi">Home</a>
&nbsp;|&nbsp;<a href="kpi/kpi_admin.php">KPI Admin</a>
EOF;

switch($m[1]){
case "form":

break;
case 'view':

break;
default:
    $body.="<p>Welcome to KPI common module</p>";
break;
}

?>