<?php
include_once("common.inc.php");
include_once("forms_and_reports.inc.php");
include_once("sales_service.inc.php");
include_once("erp.inc.php");
include_once("eng.inc.php");
$user = new tldUser($GLOBALS["PHP_AUTH_USER"]);

if (!$user->isInGroup(["role_EM", "gg_ADMIN", "role_ES"])) {
	echo "You do not have permissions for this page..";
	exit;
}

$main_tpl = [
	["table" => "timekeeping_lists", "form_type" => "main_tpl", "title" => "Timekeeping Project/type Admin", "cancel" => "table&form_type=",
		"mode" => "record_view",
		"prn_table_menu" => "<a href=\"/en/private/manufacturing/eng/dev.php?m[0]=timekeeping\">Back to Module</a>&nbsp;|&nbsp;",
		"prn_record_menu" => "<b><a href=\"/en/private/manufacturing/eng/dev.php?m[0]=timekeeping\">Back to Module</a></b>&nbsp;|&nbsp;",
		"default_sort" => "id"],
	["name" => "id", "label" => "Primary Key", "type" => "primary_key", "table" => "true", "dump" => "true"],
	["name" => "parent_id", "label" => "Foreign Key", "type" => "foreign_key", "table" => "false", "dump" => "false"],
	["name" => "type", "label" => "Type", "type" => "select", "table" => "true", "dump" => "true",
		"select_list" => ["PROJECT", "OTHER"]],
	["name" => "name", "label" => "Name", "type" => "text", "table" => "true", "dump" => "true"],
	["name" => "status", "label" => "Status", "type" => "select", "table" => "true", "dump" => "true",
		"select_list" => ["ACTIVE", "EXPIRED"]],
];

$SMARTY_LOCATION = "intranet";
$DEFAULT_TEMPLATE = "intranet.tpl";

include("header.inc.php");

//Include common db functions
include("db_common2.inc.php");
if (!$id) {
	$id = 0;
}
//Include db functions
if ($user->isInGroup(["gg_ADMIN", "role_EM", "role_ES"])) {
	include("db_admin2.inc.php");
} else {
	include("db_readonly2.inc.php");
}

