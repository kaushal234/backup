<?php
include("common.inc.php");
$user = new tldUser($GLOBALS["PHP_AUTH_USER"]);

// Get listing
$moduleList = array_column(tldModule::getList(), 'module', 'module');
$mList = array("01","02","03","04","05","06","07","08","09","10","11","12");

$main_tpl=array(
	array("table"=>"mod_kpi","form_type"=>"main_tpl","title"=>"KPI Admin",
	"cancel"=>"table&form_type=main_tpl","mode"=>"record_view",
	"prn_table_menu"=>"<a href=\"/en/private/common/index.php?m[0]=kpi\">Back to Module</a>",
	"default_sort"=>"id"),
	array("name"=>"id",			"label"=>"Primary Key",  "type"=>"primary_key",	"table"=>"true"),
	array("name"=>"module",     "label"=>"Module",       "type"=>"select",      "table"=>"true","dump"=>"true",
        "select_list"=>$moduleList, "source"=>"smartyOptions"),
    array("name"=>"key1",       "label"=>"Key",          "type"=>"text",        "table"=>"true","dump"=>"true"),
    array("name"=>"y",          "label"=>"Year (yyyy)",  "type"=>"text",        "table"=>"true","dump"=>"true"),
    array("name"=>"m",          "label"=>"Month (mm)",   "type"=>"text",        "table"=>"true","dump"=>"true"),
    array("name"=>"name",		"label"=>"KPI Name",     "type"=>"text",	    "table"=>"true","dump"=>"true"),
    array("name"=>"val",		"label"=>"KPI Value",    "type"=>"text",	    "table"=>"true","dump"=>"true")
);

$SMARTY_LOCATION = "intranet";
$DEFAULT_TEMPLATE = "intranet.tpl";
include_once("header.inc.php");
include_once("db_common2.inc.php");
if(!$id) $id=0;

if($user->isInGroup("superuser")){
    include("db_admin2.inc.php");
}else{
    include("db_readonly2.inc.php");
}
?>
