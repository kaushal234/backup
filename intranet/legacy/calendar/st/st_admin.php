<?php
include("common.inc.php");
include_once("calendar.inc.php");

$user = new tldUser($GLOBALS["PHP_AUTH_USER"]);

if($id){
	if($mode == "update"){
		$st = new tldST($id[0]);
	}else{
		$st = new tldST($id);
	}
	if(!in_array($st->getAssignor(),$user->getAllSubordinatesID($user->getID())) && ($st->getAssignor()<> $user->getID()) && !$user->isInGroup("gg_ADMIN")){
		echo "You do not have permission to access this page.";
		exit;
	}
}

$main_tpl=array(
	array("table"=>"cal_st","form_type"=>"main_tpl","title"=>"ST",
	"cancel"=>"table&form_type=main_tpl","mode"=>"record_view",
	"prn_table_menu"=>"<a href=\"/en/private/calendar/calendar.php?m[0]=st\">Back to Module</a>",
	"prn_record_menu"=>"<a href=\"/en/private/calendar/calendar.php?m[0]=st&m[1]=view&id={id}\">Back to ST</a>&nbsp;|&nbsp;",
	"default_sort"=>"id"),
	array("name"=>"id",			"label"=>"Primary Key",			"type"=>"primary_key",	"table"=>"true"),
	array("name"=>"parent_id",	"label"=>"Ref#",				"type"=>"text",	"table"=>"true"),
	array("name"=>"module",		"label"=>"Module",	"type"=>"text",	"table"=>"true","dump"=>"true"),
	array("name"=>"type",		"label"=>"Type",		"type"=>"select",		"table"=>"true",
		"select_list"=>array(	"Miscellaneous",
							"Documents",
							"Environmental",
							"Training",
							"Facilities",
							"Quality",
                            "Security",
							"iBS"
							)
		),
	array("name"=>"bu_id",		"label"=>"Location",			"type"=>"lookup",			"table"=>"true",	"dump"=>"true",
  		"select_query"=>"SELECT id, CONCAT(location,' (',erp,')') as dsc FROM locations WHERE erp<>0 ORDER BY dsc",
		"select_field_1"=>"id","select_field_2"=>"dsc",
        "select_query_view"=>"select CONCAT(location,' (',erp,')') as dsc FROM locations WHERE id=","select_field_view"=>"dsc"
		),
	array("name"=>"assignor",	"label"=>"Assignor",	"type"=>"select",	"table"=>"true","dump"=>"true",
  		"select_list"=>tldDirectory::getUserList("smartyOptions"), "source"=>"smartyOptions"),
	array("name"=>"assignee",	"label"=>"Assignee",	"type"=>"select",	"table"=>"true","dump"=>"true",
  		"select_list"=>tldDirectory::getUserList("smartyOptions"), "source"=>"smartyOptions"),
	array("name"=>"date_start",		"label"=>"Start<br>(yyyy-mm-dd)",	"type"=>"auto_date",	"table"=>"true","dump"=>"true"),
	array("name"=>"date_end",	"label"=>"End<br>(YYYY-MM-DD)",	"type"=>"date",			"table"=>"true"),
	array("name"=>"repd_val",		"label"=>"Repetition value",	"type"=>"text",	"table"=>"true","dump"=>"true"),
	array("name"=>"repd_unit",		"label"=>"Repetition unit",	"type"=>"text",	"table"=>"true","dump"=>"true"),
	array("name"=>"leadtime_value",		"label"=>"lead time value",	"type"=>"text",	"table"=>"true","dump"=>"true"),
	array("name"=>"leadtime_unit",		"label"=>"lead time unit",	"type"=>"text",	"table"=>"true","dump"=>"true"),
	array("name"=>"escalation_trigger",	"label"=>"Escalation_trigger(Days)","type"=>"text","table"=>"true","dump"=>"true"),
	array("name"=>"description",		"label"=>"description",			"type"=>"textarea",		"table"=>"true",
  		"textarea_params"=>" wrap=\"VIRTUAL\" cols=\"40\" rows=\"7\""),
	array("name"=>"referencetype",		"label"=>"Reference type",			"type"=>"select",		"table"=>"true","dump"=>"true",
		"select_list"=>["","DMS","GWF"]),
	array("name"=>"reference",		"label"=>"Reference",	"type"=>"text",	"table"=>"true","dump"=>"true"),
	array("name"=>"auto_close",		"label"=>"Auto Close",	"type"=>"text",	"table"=>"true","dump"=>"true"),
//	array("name"=>"action",		"label"=>"action",			"type"=>"textarea",		"table"=>"false",
//  		"textarea_params"=>" wrap=\"VIRTUAL\" cols=\"40\" rows=\"7\"")

);

$SMARTY_LOCATION = "intranet";
$DEFAULT_TEMPLATE = "intranet.tpl";

include("header.inc.php");
include("db_common2.inc.php");
if (!$id)$id=0;
//Include db functions
include("db_admin2.inc.php");
?>
