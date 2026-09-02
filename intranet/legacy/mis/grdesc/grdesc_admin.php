<?php
include_once("common.inc.php");
$user = new tldUser($GLOBALS["PHP_AUTH_USER"]);

if(!$user->isInGroup(array("superuser"))){
	echo "ERROR: You do not have permissions for this page";
	exit;
}

$grpDev = new tldGroup("role_DEV");
$peopleDev = $grpDev->getUserlist("smartyOptions");

$main_tpl=array(
  array("table"=>"people_groups_select","form_type"=>"main_tpl","title"=>"TLD Groups",
  "prn_table_menu"=>"<b><a href=\"users_and_groups.php\">Back to module</a></b> | ",
  "cancel"=>"table&form_type=main_tpl","mode"=>"record_view",
  "default_sort"=>"group_name"
	),
	array("name"=>"id",				"label"=>"Primary Key",		"type"=>"primary_key",	"table"=>"false"),
	array("name"=>"parent_id",		"label"=>"Foreign Key",		"type"=>"foreign_key",	"table"=>"false"),
	array("name"=>"group_name",		"label"=>"Group Name",		"type"=>"text",			"table"=>"true"),
  array("name"=>"description",		"label"=>"Description",		"type"=>"textarea",		"table"=>"true","dump"=>"true",
		"textarea_params"=>" wrap=\"VIRTUAL\" cols=\"60\" rows=\"10\""),
	array("name"=>"link",			"label"=>"Link",			"type"=>"text",			"table"=>"true")
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