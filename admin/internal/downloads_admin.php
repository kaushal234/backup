<?php
include_once('common.inc.php');

$main_tpl=array(
  array(
  	"table"=>"downloads","form_type"=>"main_tpl","title"=>"Downloads","cancel"=>"table&form_type=",
    "mode"=>"record_view","default_sort"=>"id","child_tables"=>array("downloads_perms")
  ),
  array("name"=>"id",					"label"=>"ID",			"type"=>"primary_key",	"table"=>"true","dump"=>"true",
  		"dup_exc"=>"clear"),
  array("name"=>"parent_id",			"label"=>"Foreign Key",				"type"=>"foreign_key",	"table"=>"false"),
  array("name"=>"category",				"label"=>"Category",				"type"=>"text",			"table"=>"true","dump"=>"true"),
  array("name"=>"date",					"label"=>"Date&nbsp;(yyyy-mm-dd)",	"type"=>"auto_date",	"table"=>"true","dump"=>"true"),
  array("name"=>"description",			"label"=>"Description",				"type"=>"textarea",		"table"=>"true","dump"=>"true",
  		"textarea_params"=>" wrap=\"VIRTUAL\" cols=\"50\" rows=\"7\""),
  array("name"=>"version",				"label"=>"Version",					"type"=>"text",			"table"=>"true","dump"=>"true"),
  array("name"=>"filename",				"label"=>"Filename",				"type"=>"file_upload",	"table"=>"true",
		"file_upload_table"=>"downloads_perms",
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
	array("name"=>"access",			"label"=>"Access",			"type"=>"select",		"table"=>"true",	"dump"=>"true",
  		"select_list"=>tldDirectory::getUserList("smartyOptions"),
	    "source"=>"smartyOptions"
	),
	array("name"=>"level",			"label"=>"Level",			"type"=>"text",			"table"=>"true")
);

$SMARTY_LOCATION = "intranet";
$DEFAULT_TEMPLATE = "intranet.tpl";

include("header.inc.php");
include("db_common2.inc.php");
include("db_admin2.inc.php");

?>