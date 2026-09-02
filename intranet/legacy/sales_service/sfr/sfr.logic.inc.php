<?php

$DEFAULT_TITLE .= "\Sales Forecast Records";
$DEFAULT_MENU .=<<<EOF
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
EOF;
$DEFAULT_MENU .="<a href=\"$php_self?m[0]=sfr\" title=\"SFR Homepage\">Home</a>\n";

if ($user->isInGroup(["role_EVP", "role_COO", "role_CMO", "role_CEO", "role_FC", "role_SA", "role_PSM", "role_PSE", "role_PSA", "gg_ADMIN"])
    || ($user->isInGroup(["gg_ACCT"]) && in_array(900, (array)$user->isInGroup("gg_ACCT"))) // Only GROUP gg_ACCT
) {
    $DEFAULT_MENU .= <<<EOF
&nbsp;|&nbsp;<a href="$php_self?m[0]=sfr&m[1]=cash" title="Cash forecast">Cash forecast</a>
EOF;
}

if ($m[1] === 'cash') {
    include('sfr.cash.inc.php');
} else {
    $body = 'This page has been migrated and should not be displayed anymore.';
}
