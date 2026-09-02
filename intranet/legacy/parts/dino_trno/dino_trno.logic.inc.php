<?php
include_once("erp.inc.php");

if(!$user->isInGroup(array("gg_ADMIN","gg_PARTS"))){
    $DEFAULT_ERROR[] = "ERROR: You do not have permissions";
    return;
}

$body = "This page has been migrated and should not be displayed anymore.";

?>
