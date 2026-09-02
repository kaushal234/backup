<?php
include("common.inc.php");

// Check permissions
$user = new tldUser($GLOBALS["PHP_AUTH_USER"]);
if(!$user->isInGroup("superuser")){
	echo "ERROR: You do not have permission to access this page.";
	exit;
}

$main_tpl=array(
	array("table"=>"cal_bp","form_type"=>"main_tpl","title"=>"Business Processes",
	"cancel"=>"table&form_type=main_tpl","mode"=>"record_view",
	"prn_table_menu"=>"<a href=\"/en/private/calendar/calendar.php?m[0]=bp\">Back to Module</a>",
	"prn_record_menu"=>"<a href=\"/en/private/calendar/calendar.php?m[0]=bp&m[1]=view&id={id}\">Back to Process</a>&nbsp;|&nbsp;",
//	"child_tables"=>array("cal_bp_tasks"),
	"default_sort"=>"id"),
	array("name"=>"id",			"label"=>"BP#",			"type"=>"primary_key",	"table"=>"true"),
	array("name"=>"parent_id",	"label"=>"Ref#",				"type"=>"foreign_key",	"table"=>"true"),
	array("name"=>"module",		"label"=>"Module",				"type"=>"select",		"table"=>"true","dump"=>"true",
  		"select_list"=>array(	"NCR","PDC","TTS","CPA","NCR","NOD","SEQ","BP","SB")
		),
	array("name"=>"tplno",		"label"=>"Tpl#",			"type"=>"text",			"table"=>"true"),
	array("name"=>"status",		"label"=>"Status",				"type"=>"select",		"table"=>"true","dump"=>"true",
  		"select_list"=>array(	"OPEN","CLOSED")
		),
	array("name"=>"dt_opened",		"label"=>"Date<br>(yyyy-mm-dd)",	"type"=>"auto_date",	"table"=>"true","dump"=>"true"),
	array("name"=>"dt_closed",	"label"=>"Date Closed (YYYY-MM-DD)",	"type"=>"date",			"table"=>"true"),
	array("name"=>"short_desc",		"label"=>"Short Desc",			"type"=>"text",		"table"=>"false", "width"=>"100"),
	  array("name"=>"filename",		"label"=>"Filename",				"type"=>"file_upload",	"table"=>"true",
			"file_upload_dir"=>"cal_bp"),
	array("name"=>"cur_step",		"label"=>"Current Step",		"type"=>"text",		"table"=>"false")

);

$cal_bp_tasks_tpl=array(
	array("table"=>"cal_bp_tasks","form_type"=>"cal_bp_tasks_tpl",
			"title"=>"Tasks","cancel"=>"record_view&form_type=main_tpl",
			"mode"=>"form_view"),
	array("name"=>"id",			"label"=>"Node#",			"type"=>"primary_key",	"table"=>"true"),
	array("name"=>"parent_id",	"label"=>"Ref#",			"type"=>"foreign_key",	"table"=>"false"),
	array("name"=>"task_id",	"label"=>"Task ID",			"type"=>"text",			"table"=>"false")
);

$SMARTY_LOCATION = "intranet";
$DEFAULT_TEMPLATE = "intranet.tpl";

include("header.inc.php");
include("db_common2.inc.php");
if (!$id)$id=0;
//Include db functions
include("db_admin2.inc.php");
?>