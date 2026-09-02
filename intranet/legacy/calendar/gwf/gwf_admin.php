<?php
include_once("common.inc.php");
include_once("calendar.inc.php");
$user = new tldUser($GLOBALS["PHP_AUTH_USER"]);

if($id){
	if($mode == "update"){
		$gwf = new tldGWF($id[0]);
	}else{
		$gwf = new tldGWF($id);
	}

	if($gwf->getAssignor()<>$user->getID() && !$user->isInGroup("superuser")){
		echo "ERROR: You do not have permission to access this page.";
		exit;
	}
}

$main_tpl=array(
	array("table"=>"gwf","form_type"=>"main_tpl","title"=>"Group Workflow",
	"cancel"=>"table&form_type=main_tpl","mode"=>"record_view",
	"prn_table_menu"=>"<a href=\"/en/private/calendar/calendar.php?m[0]=gwf\">Back to Module</a>",
	"prn_record_menu"=>"<a href=\"/en/private/calendar/calendar.php?m[0]=gwf&m[1]=view&id={id}\">Back to gwf</a>&nbsp;|&nbsp;",
	"default_sort"=>"id"),
	array("name"=>"id",			"label"=>"Primary Key",			"type"=>"primary_key",	"table"=>"true"),
	array("name"=>"parent_id",	"label"=>"Ref#",				"type"=>"hidden"),
	array("name"=>"dt_closed",	"label"=>"Date closed",			"type"=>"locked_date", 	"table"=>"true","dump"=>"true"),
	array("name"=>"pvt",		"label"=>"Confidential?",    		"type"=>"select",		"table"=>"true","dump"=>"true",
  		"select_list"=>array(	"Y","N")
		),
	array("name"=>"ctg",		"label"=>"Category",			"type"=>"select",	"table"=>"true",
			"source"=>"lists", "list_name"=>"list.gwf.category"),
	array("name"=>"ifactor",		"label"=>"Ifactor",			"type"=>"text",	"table"=>"true"),
	array("name"=>"status",		"label"=>"Status",				"type"=>"select",		"table"=>"true","dump"=>"true",
  		"select_list"=>array(	"OPEN","CLOSED")
		),
	array("name"=>"date",		"label"=>"Date<br>(yyyy-mm-dd)",	"type"=>"locked_date",	"table"=>"true","dump"=>"true"),
	array("name"=>"dest",		"label"=>"Est Completion Date<br>(yyyy-mm-dd)",	"type"=>"date",	"table"=>"true","dump"=>"true"),
	array("name"=>"bu",	"label"=>"Business Unit",			"type"=>"lookup",	"table"=>"true",
		"select_query"=>"SELECT id,location FROM locations ORDER BY location",
		"select_field_1"=>"id","select_field_2"=>"location",
        "select_query_view"=>"SELECT * FROM locations WHERE id=",
		"select_field_view"=>"location"),
  array("name"=>"type",		"label"=>"Equipment Type",			"type"=>"select_db",	"table"=>"true","dump"=>"true",
  		"select_query"=>"SELECT en FROM products_categories ORDER BY en",
		"select_field_1"=>"en","select_field_2"=>"en"),
  array("name"=>"model",		"label"=>"Equipment Model<br>(use ALL_MODELS selection for no specific model)",	"type"=>"select_db",	"table"=>"false","dump"=>"true",
  		"select_query"=>"SELECT model FROM models WHERE hide=0 ORDER BY model",
		"select_field_1"=>"model","select_field_2"=>"model"),
	array("name"=>"assignor",	"label"=>"Assignor",	"type"=>"select",	"table"=>"true","dump"=>"true",
  		"select_list"=>tldDirectory::getUserList("smartyOptions"), "source"=>"smartyOptions"),
	array("name"=>"dsca",		"label"=>"Short Description",			"type"=>"text",	"table"=>"true"),
	array("name"=>"dscb",		"label"=>"Description",				"type"=>"textarea",
  		"textarea_params"=>" wrap=\"VIRTUAL\" cols=\"40\" rows=\"7\""),
	array("name"=>"kwd1",		"label"=>"Key Word 1 (max 20 chars.)",			"type"=>"text"),
	array("name"=>"kwd2",		"label"=>"Key Word 2 (max 20 chars.)",			"type"=>"text"),
	array("name"=>"kwd3",		"label"=>"Key Word 3 (max 20 chars.)",			"type"=>"text"),
	array("name"=>"kwd4",		"label"=>"Key Word 4 (max 20 chars.)",			"type"=>"text"),
	array("name"=>"kwd5",		"label"=>"Key Word 5 (max 20 chars.)",			"type"=>"text"),
	array("name"=>"picture_filename",		"label"=>"Picture",				"type"=>"file_upload",	"table"=>"true",
  		"file_upload_dir"=>"gwf")

);

$gwf_members_tpl=array(
	array("table"=>"gwf_members","form_type"=>"gwf_members_tpl",
			"title"=>"GWF Members","cancel"=>"record_view&form_type=main_tpl",
			"mode"=>"form_view"),
	array("name"=>"id",			"label"=>"Primary Key",		"type"=>"primary_key",	"table"=>"false"),
	array("name"=>"parent_id",	"label"=>"Foreign Key",		"type"=>"foreign_key",	"table"=>"false"),
	array("name"=>"uid",		"label"=>"User",			"type"=>"select",		"table"=>"true","dump"=>"true",
  		"select_list"=>tldDirectory::getUserList("smartyOptions"), "source"=>"smartyOptions")
);


$SMARTY_LOCATION = "intranet";
$DEFAULT_TEMPLATE = "intranet.tpl";

include_once("header.inc.php");

//Include common db functions
include("db_common2.inc.php");
if (!$id)$id=0;
//Include db functions
include("db_admin2.inc.php");
