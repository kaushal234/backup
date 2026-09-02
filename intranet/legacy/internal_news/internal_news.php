<?php

$main_tpl = array(
    array("table"=>"internal_news","form_type"=>"main_tpl","title"=>"Internal News",
        "cancel"=>"table&form_type=main_tpl","mode"=>"record_view","default_sort"=>"id"),
    array("name"=>"id",			"label"=>"Article ID",			"type"=>"primary_key",	"table"=>"true"),
    array("name"=>"parent_id",	"label"=>"Foreign Key",			"type"=>"foreign_key",	"table"=>"false"),
    array("name"=>"cat",			"label"=>"Category",			"type"=>"lookup",		"table"=>"true","dump"=>"true",
    	"select_query"=>"SELECT * FROM lists WHERE list_name='categories' ORDER BY list_item",
    	"select_field_1"=>"id","select_field_2"=>"list_item",
        "select_query_view"=>"SELECT * FROM lists WHERE list_name='categories' AND id=","select_field_view"=>"list_item"),
    array("name"=>"date",			"label"=>"Date (yyyy-mm-dd)",	"type"=>"auto_date",	"table"=>"true","dump"=>"true"),
    array("name"=>"title",		"label"=>"Title",				"type"=>"text",			"table"=>"true","dump"=>"true"),
    array("name"=>"en",			"label"=>"Article",				"type"=>"textarea",		"table"=>"false","dump"=>"true",
        "textarea_params"=>" wrap=\"VIRTUAL\" cols=\"60\" rows=\"10\"")
);

include("header.inc.php");
$SMARTY_LOCATION = "intranet";
$DEFAULT_TEMPLATE = "intranet.tpl";
include("db_common2.inc.php");
if(!$id) $id=0;

$user = new tldUser($GLOBALS["PHP_AUTH_USER"]);

if($user->isInGroup(array("superuser","acl_news"))){
	include("db_admin2.inc.php");
}else{
	include("db_readonly2.inc.php");
}

