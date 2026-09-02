<?php
include("common.inc.php");

$user = new tldUser($GLOBALS["PHP_AUTH_USER"]);
if(!$user->isInGroup("superuser")){
	echo "ERROR: You do not have permission to access this page.";
}

$main_tpl = [
	["table" => "cal_seq_tpl", "form_type" => "main_tpl", "title" => "Sequence Templates",
		"cancel" => "table&form_type=main_tpl", "mode" => "record_view",
		"prn_table_menu" => "<a href=\"/en/private/calendar/calendar.php?m[0]=seq\">Back to Module</a>",
		"child_tables" => ["cal_seq_tpl_nodes"],
		"default_sort" => "id"],
	["name" => "id", "label" => "SEQ TPL#", "type" => "primary_key", "table" => "true"],
	["name" => "parent_id", "label" => "Parent ID#", "type" => "foreign_key", "table" => "false"],
	["name" => "name", "label" => "Name", "type" => "text", "table" => "true", "width" => "100", "dup_exc" => "PLEASE CHANGE"],
	["name" => "short_desc", "label" => "Short Desc", "type" => "text", "table" => "true", "width" => "100"],
	["name" => "def_escalation_trigger", "label" => "Default escalation trigger", "type" => "text", "table" => "true"],
	["name" => "private", "label" => "Private?", "type" => "select", "table" => "true", "dump" => "true",
		"select_list" => ["N", "Y"]],
	["name" => "parallel", "label" => "Parallel?", "type" => "select", "table" => "true", "dump" => "true",
		"select_list" => ["N", "Y"]],
];

$cal_seq_tpl_nodes_tpl = [
	["table" => "cal_seq_tpl_nodes", "form_type" => "cal_seq_tpl_nodes_tpl",
		"title" => "Sequence Nodes", "cancel" => "record_view&form_type=main_tpl",
		"mode" => "form_view",
        "default_sort" => "step"],
	["name" => "id", "label" => "Primary Key", "type" => "primary_key", "table" => "false"],
	["name" => "parent_id", "label" => "Foreign Key", "type" => "foreign_key", "table" => "false"],
	["name" => "group_name", "label" => "Group", "type" => "lookup", "table" => "true", "dump" => "true",
		"select_query" => "SELECT group_name,CONCAT(group_name,' (',description,')') AS dsc FROM people_groups_select ORDER BY dsc",
		"select_field_1" => "group_name", "select_field_2" => "dsc",
		"select_query_view" => "select * FROM people_groups_select where group_name=", "select_field_view" => "group_name"],
	["name" => "allow_supervisor", "label" => "Supervisor Inclusion", "type" => "select", "table" => "true", "dump" => "true",
		"select_list" => [0=>"", 1=>"Supervisor in Next Group",2=>"Supervisor Only"], "source" =>"smartyOptions"],
	["name" => "step", "label" => "Step", "type" => "text", "table" => "true"],
	["name" => "dsca", "label" => "Comment", "type" => "textarea", "table" => "true",
		"textarea_params" => " wrap=\"VIRTUAL\" cols=\"40\" rows=\"7\""],
];


$SMARTY_LOCATION = "intranet";
$DEFAULT_TEMPLATE = "intranet.tpl";

include("header.inc.php");
include("db_common2.inc.php");
if (!$id)$id=0;
//Include db functions
include("db_admin2.inc.php");
?>