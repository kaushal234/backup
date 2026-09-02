<?php
include("common.inc.php");
include_once("calendar.inc.php");

$user = new tldUser($GLOBALS["PHP_AUTH_USER"]);

$main_tpl=array(
	array("table"=>"srm_item","form_type"=>"main_tpl","title"=>"Shared Resource Item",
	"cancel"=>"table&form_type=main_tpl","mode"=>"record_view",
	"prn_table_menu"=>"<a href=\"/en/private/calendar/calendar.php?m[0]=srm\">Back to SRM</a>&nbsp;|&nbsp;",
	"child_tables"=>array("srm_itemlocation"),
	"default_sort"=>"id"),
	array("name"=>"id",			"label"=>"Primary Key",			"type"=>"primary_key",	"table"=>"true"),
	array("name"=>"name",		"label"=>"Name",				"type"=>"text",			"table"=>"true"),
	array("name"=>"type",		"label"=>"Type",				"type"=>"text",			"table"=>"true"),
	array("name"=>"location_id",	"label"=>"Location",			"type"=>"select",			"table"=>"true",	"dump"=>"true",
  		"select_list"=>tldLocation::getLocationList("smartyOptions"), "source"=>"smartyOptions"),
	array("name"=>"owner_id",	"label"=>"Owner",		"type"=>"select",		"table"=>"true","dump"=>"true",
			"select_list"=>tldDirectory::getUserList("smartyOptions"), "source"=>"smartyOptions"),
	array("name"=>"description",		"label"=>"description",			"type"=>"textarea",		"table"=>"true",
  		"textarea_params"=>" wrap=\"VIRTUAL\" cols=\"40\" rows=\"7\"")
);

$srm_itemlocation_tpl=array(
	array("table"=>"srm_itemlocation","form_type"=>"srm_itemlocation_tpl",
		"title"=>"SRM Item's Location","cancel"=>"record_view&form_type=main_tpl",
		"mode"=>"form_view"),
	array("name"=>"id",				"label"=>"Primary Key",			"type"=>"primary_key",	"table"=>"false"),
	array("name"=>"parent_id",		"label"=>"Foreign Key",			"type"=>"foreign_key",	"table"=>"false"),
	array("name"=>"location_id",	"label"=>"Location",			"type"=>"select",		"table"=>"true",	"dump"=>"true",
		"select_list"=>tldLocation::getLocationList("smartyOptions"), "source"=>"smartyOptions"),
);

$SMARTY_LOCATION = "intranet";
$DEFAULT_TEMPLATE = "intranet.tpl";

include("header.inc.php");
include("db_common2.inc.php");
if (!$id)$id=0;
//Include db functions
if($user->isInGroup(array("gg_MIS"))){
	include("db_admin2.inc.php");
}else{
	include("db_readonly2.inc.php");
}
?>