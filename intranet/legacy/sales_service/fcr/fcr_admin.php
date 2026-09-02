<?php
include_once("common.inc.php");

$smarty = tldUtils::getSmarty("intranet");
$smarty->assign("body", 'This page has been migrated and should not be displayed anymore');
$smarty->display("intranet.tpl");
