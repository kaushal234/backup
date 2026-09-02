<?php
include_once("common.inc.php");
$user = new tldUser($_SERVER["PHP_AUTH_USER"]);

//if(!$user->isInGroup(array("gg_ADMIN","gg_ACCT","gg_SUPPORT","gg_QUALITY"))){
//	echo "ERROR: Access not permitted";
//	exit;
//}

$main_tpl=array(
  array("table"=>"fin_kpi","form_type"=>"main_tpl","title"=>"KPI Admin",
"prn_table_menu"=>"<a href=\"/en/private/manufacturing/reports/reports.php?m[0]=kpi\">Back to Module</a>&nbsp;|&nbsp;",
  "cancel"=>"table&form_type=main_tpl","mode"=>"record_view",
  "default_sort"=>"id"
	),
  array("name"=>"id",			"label"=>"Primary Key",			"type"=>"primary_key",	"table"=>"false"),
  array("name"=>"parent_id",	"label"=>"Foreign Key",			"type"=>"foreign_key",	"table"=>"false"),
  array("name"=>"dt",			"label"=>"Timestamp",			"type"=>"auto_time",			"table"=>"true","dump"=>"true"),
	array("name"=>"buid",			"label"=>"Business Unit",			"type"=>"lookup",	"table"=>"true",
		"select_query"=>"SELECT id,location FROM locations WHERE erp<>0 ORDER BY location",
		"select_field_1"=>"id","select_field_2"=>"location",
        "select_query_view"=>"SELECT * FROM locations WHERE id=",
		"select_field_view"=>"location"),
  array("name"=>"ynam",			"label"=>"Year",				"type"=>"text",		"table"=>"true","dump"=>"true"),
  array("name"=>"mnam",			"label"=>"Month",				"type"=>"select",		"table"=>"true","dump"=>"true",
  		"select_list"=>array("01","02","03","04","05","06","07","08","09","10","11","12")
		),
  array("name"=>"bypass",			"label"=>"<b><i>Cycle Count</b></i>",	"type"=>"title",		"table"=>"false"),
  array("name"=>"cc_qua",		"label"=>"Number of items counted",	"type"=>"text",			"table"=>"true","dump"=>"true"),
  array("name"=>"cc_quv",		"label"=>"Number of items with variance",	"type"=>"text",			"table"=>"true","dump"=>"true"),
  array("name"=>"cc_pric",		"label"=>"Monetary value of items counted",	"type"=>"text",			"table"=>"true","dump"=>"true"),
  array("name"=>"cc_priv",		"label"=>"Monetary value of items with variance",	"type"=>"text",			"table"=>"true","dump"=>"true"),
  array("name"=>"bypass",			"label"=>"<b><i>Work In Progress</b></i>",	"type"=>"title",		"table"=>"false"),
  array("name"=>"wip_vald",		"label"=>"WIP Value in days of sales",	"type"=>"text",			"table"=>"true","dump"=>"true"),
  array("name"=>"inv_vald",		"label"=>"Inventory value in days of sales",	"type"=>"text",			"table"=>"true","dump"=>"true"),
  array("name"=>"bypass",			"label"=>"<b><i>Factory Standard Efficiency</b></i>",	"type"=>"title",		"table"=>"false"),
  array("name"=>"fse_stdh",		"label"=>"Standard hours allocated to units shipped",	"type"=>"text",			"table"=>"true","dump"=>"true"),
  array("name"=>"fse_acth",		"label"=>"Actual hours spent on units shipped",	"type"=>"text",			"table"=>"true","dump"=>"true"),
  array("name"=>"bypass",			"label"=>"<b><i>Productive Hours</b></i>",	"type"=>"title",		"table"=>"false"),
  array("name"=>"phr_proh",	"label"=>"Productive work hours allocated to work orders",	"type"=>"text",			"table"=>"true","dump"=>"true"),
  array("name"=>"phr_poth",		"label"=>"Potential hours of work orders",	"type"=>"text",			"table"=>"true","dump"=>"true"),
  array("name"=>"bypass",			"label"=>"<b><i>Internal Customer Satisfaction (QUARTERLY ONLY!)<br>In MAR, JUN, SEPT, DEC</b></i>",	"type"=>"title",		"table"=>"false"),
  array("name"=>"ics_ame",	"label"=>"TLD America",	"type"=>"text",			"table"=>"true","dump"=>"true"),
  array("name"=>"ics_asi",	"label"=>"TLD Asia",	"type"=>"text",			"table"=>"true","dump"=>"true"),
  array("name"=>"ics_eur",	"label"=>"TLD Europe",	"type"=>"text",			"table"=>"true","dump"=>"true")
);

$SMARTY_LOCATION = "intranet";
$DEFAULT_TEMPLATE = "intranet.tpl";

include("header.inc.php");

//Include common db functions
include("db_common2.inc.php");

if (!$id)$id=0;
//Include db functions
include("db_admin2.inc.php");
?>