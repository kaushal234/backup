<?php
include("common.inc.php");

// Check permissions
$user = new tldUser($GLOBALS["PHP_AUTH_USER"]);
if(!$user->isInGroup("superuser")){
	echo "ERROR: You do not have permission to access this page.";
	exit;
}

$main_tpl=array(
	array("table"=>"cal_seq_tpl","form_type"=>"main_tpl","title"=>"Sequence Templates",
	"cancel"=>"table&form_type=main_tpl","mode"=>"record_view",
//	"prn_table_menu"=>"<a href=\"/en/private/calendar/calendar.php?m[0]=seq\">Back to Module</a>",
//	"prn_record_menu"=>"<a href=\"/en/private/calendar/calendar.php?m[0]=seq&m[1]=view&id={id}\">Back to SEQ</a>&nbsp;|&nbsp;",
	"child_tables"=>array("cal_seq_tpl_nodes"),
	"default_sort"=>"id"),
	array("name"=>"id",			"label"=>"SEQ#",			"type"=>"primary_key",	"table"=>"true"),
//	array("name"=>"module",		"label"=>"Module",				"type"=>"select",		"table"=>"true","dump"=>"true",
 // 		"select_list"=>array(	"NCR","PDC","TTS")
//		),
	array("name"=>"parent_id",	"label"=>"Parent ID#",				"type"=>"foreign_key",	"table"=>"false"),
	array("name"=>"short_desc",		"label"=>"Short Desc",			"type"=>"text",		"table"=>"true", "width"=>"100")
);

$cal_seq_tpl_nodes_tpl=array(
	array("table"=>"cal_seq_tpl_nodes","form_type"=>"cal_seq_tpl_nodes_tpl",
			"title"=>"Sequence Nodes","cancel"=>"record_view&form_type=main_tpl",
			"mode"=>"form_view"),
	array("name"=>"id",			"label"=>"Primary Key",			"type"=>"primary_key",	"table"=>"false"),
	array("name"=>"parent_id",	"label"=>"Foreign Key",			"type"=>"foreign_key",	"table"=>"false"),
	array("name"=>"group_name",		"label"=>"Group",			"type"=>"lookup",			"table"=>"true",	"dump"=>"true",
  		"select_query"=>"SELECT group_name,CONCAT(group_name,' (',description,')') AS dsc FROM people_groups_select ORDER BY dsc",
		"select_field_1"=>"group_name","select_field_2"=>"dsc",
        "select_query_view"=>"select * FROM people_groups_select where group_name=","select_field_view"=>"group_name"),
	array("name"=>"step",			"label"=>"Step",			"type"=>"text",			"table"=>"true")
);

$SMARTY_LOCATION = "intranet";
$DEFAULT_TEMPLATE = "intranet.tpl";

include("header.inc.php");
include("db_common2.inc.php");
if (!$id)$id=0;
//Include db functions
include("db_admin2.inc.php");
?>