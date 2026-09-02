<?php
$DEFAULT_TITLE .= "\Keys";

$DEFAULT_MENU .=<<<EOF
<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<a href="$php_self?m[0]=keys">Home</a>
EOF;
if($user->isInGroup("superuser")){
	$DEFAULT_MENU .=<<<EOF
	&nbsp;|&nbsp;<a href="keys/keys_admin.php">Keys Admin</a>
EOF;
}
?>