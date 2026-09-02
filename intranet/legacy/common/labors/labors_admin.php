<?php
include("common.inc.php");

$user = new tldUser($GLOBALS["PHP_AUTH_USER"]);
if(!$user->isInGroup("superuser")){
	echo "ERROR: You do not have permission to access this page.";
    exit;
}

$main_tpl=array(
	array("table"=>"mod_labors","form_type"=>"main_tpl","title"=>"Module Labors",
	"cancel"=>"table&form_type=main_tpl","mode"=>"record_view",
	"prn_table_menu"=>"<a href=\"/en/private/common/index.php?m[0]=labors\">Back to Module</a>",
	"prn_record_menu"=>"<a href=\"/en/private/common/index.php?m[0]=labors&m[1]=view&id={id}\">Back to Module</a>&nbsp;|&nbsp;",
	"default_sort"=>"id"),
	array("name"=>"id",			"label"=>"Primary Key",			"type"=>"primary_key",	"table"=>"true"),
	array("name"=>"parent_id",	"label"=>"Module Ref#",			"type"=>"text",	        "table"=>"true","dump"=>"true"),
	array("name"=>"module",		"label"=>"Module",				"type"=>"select",		"table"=>"true","dump"=>"true",
        "select_list"=>array_keys(tldUtils::getModLinks())),
    array("name"=>"dt_open",  	"label"=>"Date creation",       "type"=>"auto_date",  	"table"=>"true","dump"=>"true"),
    array("name"=>"poster_id",  "label"=>"Poster",      		"type"=>"select",  		"table"=>"true","dump"=>"true",
    	"select_list"=>tldDirectory::getUserlist("smartyOptions"),	"source"=>"smartyOptions"),
    array("name"=>"user_id",    "label"=>"User concerned",      "type"=>"select",  		"table"=>"true","dump"=>"true",
    	"select_list"=>tldDirectory::getUserlist("smartyOptions"),	"source"=>"smartyOptions"),
    array("name"=>"dt_work",    "label"=>"Work Date",        	"type"=>"date",  		"table"=>"true","dump"=>"true"),
    array("name"=>"description","label"=>"Description",         "type"=>"text",         "table"=>"false","dump"=>"true"),
    array("name"=>"nb_hours",   "label"=>"Hours",    			"type"=>"text",         "table"=>"true","dump"=>"true"),
);

$SMARTY_LOCATION = "intranet";
$DEFAULT_TEMPLATE = "intranet.tpl";

include_once("header.inc.php");
include("db_common2.inc.php");
if (!$id)$id=0;
include("db_admin2.inc.php");
?>
