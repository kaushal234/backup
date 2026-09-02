<?php
include("common.inc.php");

$user = new tldUser($GLOBALS["PHP_AUTH_USER"]);
if(!$user->isInGroup("superuser")){
	echo "ERROR: You do not have permission to access this page.";
    exit;
}

$main_tpl=array(
	array("table"=>"mod_costs_type","form_type"=>"main_tpl","title"=>"Costs Type",
	"cancel"=>"table&form_type=main_tpl","mode"=>"record_view","default_sort"=>"id",
	"prn_table_menu"=>"<a href=\"/en/private/common/index.php?m[0]=costs\">Back to Costs Module</a>&nbsp;|&nbsp;"),
	array("name"=>"id",			"label"=>"Primary Key",			"type"=>"primary_key",  "table"=>"true"),
	array("name"=>"module",		"label"=>"Module",				"type"=>"select",       "table"=>"true","dump"=>"true",
        "select_list"=>array_keys(tldUtils::getModLinks())),
    array("name"=>"type",       "label"=>"Type",                "type"=>"text",         "table"=>"true","dump"=>"true"),
);

$SMARTY_LOCATION = "intranet";
$DEFAULT_TEMPLATE = "intranet.tpl";

include_once("header.inc.php");
include("db_common2.inc.php");
if (!$id)$id=0;
include("db_admin2.inc.php");
?>
