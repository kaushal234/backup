<?php
include_once("common.inc.php");
include_once("dms.inc.php");

// Lock accesses
$user = new tldUser($GLOBALS["PHP_AUTH_USER"]);
if(!$user->isInGroup(array("superuser"))){
    exit;
}

// Listing
$statusList = array_combine(tldDMS::getStatusList(),tldDMS::getStatusList());

$main_tpl=array(
    array(
    	"table"=>"dms","form_type"=>"main_tpl","title"=>"DMS",
        "cancel"=>"table&form_type=","mode"=>"record_view","default_sort"=>"id",
    ),
    array("name"=>"id",           "label"=>"DMS#",               "type"=>"primary_key",      "table"=>"true",    "dump"=>"true"),
    array("name"=>"parent_id",    "label"=>"Parent DMS#",        "type"=>"locked_field",     "table"=>"true",    "dump"=>"true"),
    array("name"=>"title",        "label"=>"Title",        		 "type"=>"locked_field",     "table"=>"true",    "dump"=>"true"),
    array("name"=>"status",       "label"=>"Status",             "type"=>"select",           "table"=>"true",    "dump"=>"true",
        "select_list"=>$statusList, "source"=>"smartyOptions"),
);

if(!empty($sess_error_message)){
    echo "<p style=\"color:red;text-align:center;\">$sess_error_message</p><br/>";
}

include("header.inc.php");
include("db_common2.inc.php");
$SMARTY_LOCATION = "intranet";
$DEFAULT_TEMPLATE = "intranet.tpl";
if(!$id) $id=0;
include("db_admin2.inc.php");

?>