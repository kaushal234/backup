<?php
require_once("HTML/QuickForm.php");
require_once('HTML/QuickForm/advmultiselect.php');

$DEFAULT_TITLE .= "/AGR";
$DEFAULT_MENU .=<<<EOF
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<a href="$php_self?m[0]=agr">Home</a>
&nbsp;|&nbsp;<a href="$php_self?m[0]=agr&m[1]=listing&m[2]=search">Search</a>
&nbsp;|&nbsp;<a href="$php_self?m[0]=agr&m[1]=form&m[2]=new">New</a>
&nbsp;|&nbsp;<a href="agr/agr_admin.php">Maintain AGR</a>
&nbsp;|&nbsp;<a href="$php_self?m[0]=agr&m[1]=xls" title="Download Full AGR List"> Download List (XLS)</a>
EOF;

$body = 'This page has been migrated and should not be displayed anymore.';