<?php
include_once("common.inc.php");
include_once("forms_and_reports.inc.php");
include_once("sales_service.inc.php");
include_once("erp.inc.php");
include_once("eng.inc.php");
$user = new tldUser($GLOBALS["PHP_AUTH_USER"]);

if(!$user->isInGroup(array("role_EM","gg_ADMIN","role_ES"))){
	echo "You do not have permissions for this page..";
	exit;
}

$main_tpl = [
	["table" => "timekeeping", "form_type" => "main_tpl", "title" => "Timekeeping Admin", "cancel" => "table&form_type=",
		"mode" => "record_view",
		"prn_table_menu" => "<a href=\"/en/private/manufacturing/eng/dev.php?m[0]=timekeeping\">Back to Module</a>&nbsp;|&nbsp;",
		"prn_record_menu" => "<b><a href=\"/en/private/manufacturing/eng/dev.php?m[0]=timekeeping&m[1]=view&id={id}\">Back to Module</a></b>&nbsp;|&nbsp;",
		"default_sort" => "id"],
	["name" => "id", "label" => "Primary Key", "type" => "primary_key", "table" => "true", "dump" => "true"],
	["name" => "parent_id", "label" => "Foreign Key", "type" => "foreign_key", "table" => "false", "dump" => "false"],
	["name" => "tld_dpt", "label" => "TLD Department", "type" => "text", "table" => "true", "dump" => "false"],
	["name" => "location", "label" => "Location", "type" => "select", "table" => "true", "dump" => "true",
		"select_list" => tldLocation::getFactoryList("smartyOptions"),
		"source" => "smartyOptions"],
	["name" => "user_id", "label" => "User ID", "type" => "select", "table" => "true", "dump" => "true",
		"select_list" => tldGroup::getUserListByMultipleGroup(["gg_ADMIN", "acl_timekeeping"], "", "smartyOptions"),
		"source" => "smartyOptions"],
	["name" => "dt_open", "label" => "Date (YYYY-MM-DD)", "type" => "date", "table" => "true", "dump" => "true"],
	["name" => "category", "label" => "Category", "type" => "select", "table" => "true", "dump" => "true",
		"select_list" => ["Engineering Task", "Training", "Supplier visit", "TLD Manuf. Support", "TLD Field Support", "TLD Spare Parts Support", "Other", "Non Productive Hours", "Vacation-Sick Leave", "Meeting"]],
	["name" => "product_type", "label" => "Product Type", "type" => "text", "table" => "true", "dump" => "true"],
	["name" => "project", "label" => "Project Type", "type" => "select", "table" => "true", "dump" => "true",
		"select_list" => tldTimekeeping::getProjectList("smartyOptions"),
		"source" => "smartyOptions"],
	["name" => "link", "label" => "Link Type", "type" => "select", "table" => "true", "dump" => "true",
		"select_list" => tldTimekeeping::getLinkList()],
	["name" => "module_id", "label" => "Module ID#", "type" => "text", "table" => "true", "dump" => "true"],
	["name" => "time_actual", "label" => "Time Actual (in % of day)", "type" => "text", "table" => "true", "dump" => "true"],
	["name" => "time_forecast", "label" => "Forecasted Time (in % of day)", "type" => "text", "table" => "true", "dump" => "true"],
	["name" => "hours_actual", "label" => "Time Actual (in Hours))", "type" => "text", "table" => "true", "dump" => "true"],
	["name" => "hours_forecast", "label" => "Forecasted Time (in Hours))", "type" => "text", "table" => "true", "dump" => "true"],
	["name" => "comment", "label" => "Comment", "type" => "textarea", "table" => "false", "dump" => "true",
		"textarea_params" => " wrap=\"VIRTUAL\" cols=\"80\" rows=\"10\""],
];

$SMARTY_LOCATION = "intranet";
$DEFAULT_TEMPLATE = "intranet.tpl";

include("header.inc.php");

//Include common db functions
include("db_common2.inc.php");
if (!$id)$id=0;
//Include db functions
if($user->isInGroup(array("gg_ADMIN", "role_EM","role_ES"))){
    include("db_admin2.inc.php");
}else{
    include("db_readonly2.inc.php");
}

?>
