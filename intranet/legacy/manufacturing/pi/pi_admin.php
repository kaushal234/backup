<?php
include_once("common.inc.php");
include_once("forms_and_reports.inc.php");
include_once("sales_service.inc.php");
include_once("erp.inc.php");
$user = new tldUser($GLOBALS["PHP_AUTH_USER"]);

if(!$user->isInGroup(array("gg_MIS"))){
	echo "You do not have permissions for this page..";
	exit;
}

$families=tldPI::getPiFamilies();
foreach ($families as $key1 => $value1) {
    $family[]=$families[$key1]['family'];
}

$main_tpl = [
	["table" => "pi_questions", "form_type" => "main_tpl", "title" => "P&I Questions", "cancel" => "table&form_type=",
		"mode" => "record_view",
		"prn_table_menu" => "<a href=\"/en/private/manufacturing/index.php?m[0]=pi\">Back to Module</a>&nbsp;|&nbsp;",
		"prn_record_menu" => "<b><a href=\"/en/private/manufacturing/index.php?m[0]=pi&m[1]=view&id={id}\">Back to Module</a></b>&nbsp;|&nbsp;",
		"default_sort" => "id"],
	["name" => "id", "label" => "Primary Key", "type" => "primary_key", "table" => "true", "dump" => "true"],
	["name" => "parent_id", "label" => "Foreign Key", "type" => "foreign_key", "table" => "false", "dump" => "false"],
	["name" => "t_opno", "label" => "Operation#", "type" => "text", "table" => "true", "dump" => "true"],
	["name" => "t_item", "label" => "PN#", "type" => "text", "table" => "true", "dump" => "true"],
	["name" => "model", "label" => "Model", "type" => "select", "table" => "true", "dump" => "true", "select_list" => array_combine($family, $family), "source" => "smartyOptions"],
	["name" => "subject_en", "label" => "Subject (EN)", "type" => "text", "table" => "true", "dump" => "true"],
	["name" => "subject_fr", "label" => "Subject (FR)", "type" => "text", "table" => "true", "dump" => "true"],
	["name" => "subject_zh", "label" => "Subject (ZH)", "type" => "text", "table" => "true", "dump" => "true"],
	["name" => "desc_en", "label" => "Description (EN)", "type" => "textarea", "table" => "false", "dump" => "true",
		"textarea_params" => " wrap=\"VIRTUAL\" cols=\"80\" rows=\"10\""],
	["name" => "desc_fr", "label" => "Description (FR)", "type" => "textarea", "table" => "false", "dump" => "true",
		"textarea_params" => " wrap=\"VIRTUAL\" cols=\"80\" rows=\"10\""],
	["name" => "desc_zh", "label" => "Description (ZH)", "type" => "textarea", "table" => "false", "dump" => "true",
		"textarea_params" => " wrap=\"VIRTUAL\" cols=\"80\" rows=\"10\""],
	["name" => "position", "label" => "Position", "type" => "text", "table" => "false", "dump" => "true"],
	["name" => "help_en", "label" => "Help (EN)", "type" => "text", "table" => "false", "dump" => "true"],
	["name" => "help_fr", "label" => "Help (FR)", "type" => "text", "table" => "false", "dump" => "true"],
	["name" => "help_zh", "label" => "Help (ZH)", "type" => "text", "table" => "false", "dump" => "true"],

	["name" => "attachment_en", "label" => "Picture (EN)", "type" => "text", "table" => "false", "dump" => "true"],
	["name" => "attachment_fr", "label" => "Picture (FR)", "type" => "text", "table" => "false", "dump" => "true"],
	["name" => "attachment_zh", "label" => "Picture (ZH)", "type" => "text", "table" => "false", "dump" => "true"],

	["name" => "answer_type", "label" => "Answer Type", "type" => "select", "table" => "false", "dump" => "true",
		"select_list" => ["", "YES/NO", "Decimal", "Alphanumeric", "Match List"]],
	["name" => "answer_unit", "label" => "Answer Unit", "type" => "select", "table" => "false", "dump" => "true",
		"select_list" => [""] + tldPI::getUnits(),
		"source" => "smartyOptions"],
	["name" => "match_list", "label" => "Answer Match List", "type" => "text", "table" => "false", "dump" => "true"],
	["name" => "answer_max", "label" => "Answer Max", "type" => "text", "table" => "false", "dump" => "true"],
	["name" => "answer_min", "label" => "Answer Min", "type" => "text", "table" => "false", "dump" => "true"],
	["name" => "non_conformity", "label" => "Non Conformity Value", "type" => "select", "table" => "false", "dump" => "true",
		"select_list" => ["Y", "N"]],
	["name" => "created_on", "label" => "Created On", "type" => "date", "table" => "true", "dump" => "true"],
	["name" => "entered_by", "label" => "Entered By", "type" => "select", "table" => "true", "dump" => "true",
		"select_list" => tldGroup::getUserListByMultipleGroup(["gg_MIS"], "", "smartyOptions"),
		"source" => "smartyOptions"],
	["name" => "updated_on", "label" => "Updated On", "type" => "date", "table" => "false", "dump" => "true"],
	["name" => "updated_by", "label" => "Update By", "type" => "select", "table" => "false", "dump" => "true",
		"select_list" => tldGroup::getUserListByMultipleGroup(["gg_MIS"], "", "smartyOptions"),
		"source" => "smartyOptions"],
	["name" => "create_mode", "label" => "Creation Mode", "type" => "date", "table" => "false", "dump" => "true"],
	["name" => "gt1", "label" => "GT1", "type" => "select", "table" => "false", "dump" => "true",
		"select_list" => ["Y", "N"]],
	["name" => "gt3", "label" => "GT3", "type" => "select", "table" => "false", "dump" => "true",
		"select_list" => ["Y", "N"]],
	["name" => "active", "label" => "Active", "type" => "select", "table" => "true", "dump" => "true", "select_list" => ["Y", "N"]],
];

foreach (tldPI::getFactoriesList() as $code => $name) {
	$main_tpl[] = ["name" => $code, "label" => $name, "type" => "text", "table" => "false", "dump" => "true"];
}

$SMARTY_LOCATION = "intranet";
$DEFAULT_TEMPLATE = "intranet.tpl";

include("header.inc.php");

//Include common db functions
include("db_common2.inc.php");
if (!$id)$id=0;
//Include db functions
if($user->isInGroup(array("gg_ADMIN", "gg_MIS"))){
	include("db_admin2.inc.php");
}else{
	include("db_readonly2.inc.php");
}

?>
