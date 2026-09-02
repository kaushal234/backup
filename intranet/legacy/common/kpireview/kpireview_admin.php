<?php
include("common.inc.php");
$user = new tldUser($GLOBALS["PHP_AUTH_USER"]);

$main_tpl=array(
	array("table"=>"mod_kpireview","form_type"=>"main_tpl","title"=>"KPI Review Admin",
	"cancel"=>"table&form_type=main_tpl","mode"=>"record_view",
	"child_tables"=>array("kpireview_comments"),
	"prn_table_menu"=>"<a href=\"/en/private/common/index.php?m[0]=kpireview\">Back to Module</a>",
	"default_sort"=>"id"),
	array("name"=>"id",			"label"=>"Primary Key",  "type"=>"primary_key",	"table"=>"true"),
    array("name"=>"name_id",       "label"=>"KPI Graph URI",          "type"=>"text",        "table"=>"true","dump"=>"true"),
    array("name"=>"xval",          "label"=>"X Value",  "type"=>"text",        "table"=>"true","dump"=>"true"),
    array("name"=>"zval",          "label"=>"Z Value",  "type"=>"text",        "table"=>"true","dump"=>"true"),
    array("name"=>"last_yval",          "label"=>"Last Recorded Y Value",  "type"=>"text",        "table"=>"true","dump"=>"true")
);

$kpireview_comments_tpl=array(
  array("table"=>"mod_kpireview_comments","form_type"=>"kpireview_comments_tpl",
  		"title"=>"KPI Review Notes Comments",
		"cancel"=>"record_view&form_type=main_tpl",
		"mode"=>"table"),
  array("name"=>"id",			"label"=>"Primary Key",		"type"=>"primary_key",	"table"=>"false"),
  array("name"=>"parent_id",	"label"=>"Foreign Key",		"type"=>"foreign_key",	"table"=>"false"),
  array("name"=>"poster",	"label"=>"Posted By",				"type"=>"select","dump"=>"true","table"=>"true",
  		"select_list"=>array("")+tldDirectory::getUserList("smartyOptions"), "source"=>"smartyOptions"),
  array("name"=>"comment",		"label"=>"Comment Msg",					"type"=>"text",			"table"=>"true"),
  array("name"=>"dt",		"label"=>"Created On",			"type"=>"auto_date",			"table"=>"true"),
  array("name"=>"updated",		"label"=>"Updated On",			"type"=>"datetime",			"table"=>"true")
);


$SMARTY_LOCATION = "intranet";
$DEFAULT_TEMPLATE = "intranet.tpl";
include_once("header.inc.php");
include_once("db_common2.inc.php");
if(!$id) $id=0;

if($user->isInGroup("superuser")){
    include("db_admin2.inc.php");
}else{
    include("db_readonly2.inc.php");
}
