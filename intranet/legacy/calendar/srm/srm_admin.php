<?php
include("common.inc.php");
include_once("calendar.inc.php");

$user = new tldUser($GLOBALS["PHP_AUTH_USER"]);

// listing
$itemList = array_column(tldSRM::getItemList(), 'name', 'id');

$main_tpl=array(
	array("table"=>"srm","form_type"=>"main_tpl","title"=>"SRM",
	"cancel"=>"table&form_type=main_tpl","mode"=>"record_view",
	"prn_table_menu"=>"<a href=\"/en/private/calendar/calendar.php?m[0]=srm\">Back to Module</a>",
	"prn_record_menu"=>"<a href=\"/en/private/calendar/calendar.php?m[0]=srm&m[1]=view&id={id}\">Back to SRM</a>&nbsp;|&nbsp;",
	"default_sort"=>"id"),
	array("name"=>"id",			"label"=>"Primary Key",			"type"=>"primary_key",	"table"=>"true"),
	array("name"=>"open_date",	"label"=>"Open Date",			"type"=>"auto_date",	"table"=>"true","dump"=>"true"),
	array("name"=>"poster_id",	"label"=>"Poster",				"type"=>"select",		"table"=>"true","dump"=>"true",
  		"select_list"=>tldDirectory::getUserList("smartyOptions"), "source"=>"smartyOptions"),
	array("name"=>"requestor_id",	"label"=>"Requestor",		"type"=>"select",		"table"=>"true","dump"=>"true",
			"select_list"=>tldDirectory::getUserList("smartyOptions"), "source"=>"smartyOptions"),
	array("name"=>"date_from",	"label"=>"Start",	"type"=>"datetime",	"table"=>"true"),
	array("name"=>"date_to",	"label"=>"End",		"type"=>"datetime",	"table"=>"true"),
	array("name"=>"item_id",	"label"=>"Item",	"type"=>"select",		"table"=>"true","dump"=>"true",
		"select_list"=>$itemList, "source"=>"smartyOptions"),
	array("name"=>"description",		"label"=>"description",			"type"=>"textarea",		"table"=>"true",
  		"textarea_params"=>" wrap=\"VIRTUAL\" cols=\"40\" rows=\"7\""),
	array("name"=>"status",		"label"=>"Status",		"type"=>"select",		"table"=>"true",
		"select_list"=>tldSRM::getStatusList(), "source"=>"smartyOptions")
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
