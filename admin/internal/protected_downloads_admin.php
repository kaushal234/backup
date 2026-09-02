<?php
$main_tpl=array(
  array("table"=>"downloads","form_type"=>"main_tpl","title"=>"Downloads","cancel"=>"table&form_type=",
  "mode"=>"record_view",
  "default_sort"=>"id",
   "child_tables"=>array("downloads_perms")
   ),
//start of table definitions
  array("name"=>"id",					"label"=>"ID",			"type"=>"primary_key",	"table"=>"true","dump"=>"true",
  		"dup_exc"=>"clear"),
  array("name"=>"parent_id",			"label"=>"Foreign Key",				"type"=>"foreign_key",	"table"=>"false"),
//  array("name"=>"section",				"label"=>"Category",				"type"=>"lookup",		"table"=>"true","dump"=>"true",
// 		"select_query"=>"SELECT * FROM categories ORDER BY name","select_field_1"=>"id","select_field_2"=>"name",
//        "select_query_view"=>"SELECT * FROM categories where id=","select_field_view"=>"image"),
  array("name"=>"date",					"label"=>"Date&nbsp;(yyyy-mm-dd)",	"type"=>"auto_date",	"table"=>"true","dump"=>"true"),
  array("name"=>"description",			"label"=>"Description",				"type"=>"textarea",		"table"=>"true","dump"=>"true",
  		"textarea_params"=>" wrap=\"VIRTUAL\" cols=\"50\" rows=\"7\""),
  array("name"=>"version",				"label"=>"Version",					"type"=>"text",			"table"=>"true","dump"=>"true"),
  array("name"=>"filename",				"label"=>"Filename",				"type"=>"file_upload",	"table"=>"true",
  		"file_upload_type"=>"protected","file_upload_table"=>"downloads_perms",
		"file_upload_dir"=>"downloads","dup_exc"=>"clear"),
  array("name"=>"lang",					"label"=>"Language",				"type"=>"text",			"table"=>"true","dump"=>"true"),
  array("name"=>"format",				"label"=>"Format",					"type"=>"text",			"table"=>"false","dump"=>"true")
);
$downloads_perms_tpl=array(
	array("table"=>"downloads_perms","form_type"=>"downloads_perms_tpl",
			"title"=>"Download Permissions","cancel"=>"record_view&form_type=main_tpl",
			"mode"=>"form_view"),
	array("name"=>"id",				"label"=>"Primary Key",		"type"=>"primary_key",	"table"=>"false"),
	array("name"=>"parent_id",		"label"=>"Foreign Key",		"type"=>"foreign_key",	"table"=>"false"),
	array("name"=>"access",			"label"=>"Access",			"type"=>"lookup",		"table"=>"true",	"dump"=>"true",
  		"select_query"=>"SELECT CONCAT(lastname,', ',firstname) AS fullname,id FROM people ORDER BY fullname",
		"select_field_1"=>"id","select_field_2"=>"fullname",
        "select_query_view"=>"SELECT CONCAT(lastname,', ',firstname) AS fullname FROM people where id=","select_field_view"=>"fullname"),
	array("name"=>"level",			"label"=>"Level",			"type"=>"text",			"table"=>"true")
);
$SMARTY_LOCATION = "intranet";
$DEFAULT_TEMPLATE = "intranet.tpl";

include("header.inc.php");
//$form_array=${$MY_SESS[$running]["form_type"]};
//Include common db functions
include("db_common2.inc.php");

//Include db functions
include("db_admin2.inc.php");

?>
