<?php
include("common.inc.php");
$user = new tldUser($GLOBALS["PHP_AUTH_USER"]);
if(!$user->isInGroup("superuser")){
	echo "ERROR: You do not have permission to access this page.";
    exit;
}

$main_tpl=array(
	array("table"=>"mod_logs","form_type"=>"main_tpl","title"=>"Logs Admin",
	"cancel"=>"table&form_type=main_tpl","mode"=>"record_view",
	"prn_table_menu"=>"<a href=\"/en/private/common/index.php?m[0]=logs\">Back to Module</a>",
//	"prn_record_menu"=>"<a href=\"/en/private/calendar/calendar.php?m[0]=tasks&m[1]=task&m[2]=view&id={id}\">Back to Task</a>&nbsp;|&nbsp;",
//	"child_tables"=>array("tasks_comments"),
	"default_sort"=>"id"),
	array("name"=>"id",			"label"=>"Primary Key",			"type"=>"primary_key",	"table"=>"true"),
	array("name"=>"module",		"label"=>"Module",				"type"=>"select",		"table"=>"true","dump"=>"true",
  		"select_list"=>array(	"NCR","PDC","TTS","CPA","NCR","NOD","SEQ","BP")
		),
	array("name"=>"parent_id",	"label"=>"Ref#",				"type"=>"text",			"table"=>"true"),
	array("name"=>"date",		"label"=>"Date",				"type"=>"auto_date",	"table"=>"true","dump"=>"true"),
	array("name"=>"poster",		"label"=>"Poster",				"type"=>"text",	"table"=>"true","dump"=>"true"),
	array("name"=>"comment",	"label"=>"Comment",				"type"=>"textarea",		"table"=>"false",
  		"textarea_params"=>" wrap='VIRTUAL' cols='40' rows='7'")
);

$SMARTY_LOCATION = "intranet";
$DEFAULT_TEMPLATE = "intranet.tpl";

include_once("header.inc.php");

//Include common db functions
include("db_common2.inc.php");
if (!$id)$id=0;
//Include db functions
include("db_admin2.inc.php");
?>
