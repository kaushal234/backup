<?php
include_once("common.inc.php");

$main_tpl=array(
    array("table"=>"stats_access_log","form_type"=>"main_tpl","title"=>"Stats Admin","default_sort"=>"id",
        "cancel"=>"table&form_type=","mode"=>"record_view",
    ),
    array("name"=>"id",			    "label"=>"ID#",           "type"=>"primary_key",	"table"=>"true","dump"=>"true"),
    array("name"=>"dt",	            "label"=>"Date",          "type"=>"locked_field",	"table"=>"true","dump"=>"true"),
    array("name"=>"user_id",	    "label"=>"User#",         "type"=>"text",	        "table"=>"false","dump"=>"true"),
    array("name"=>"user_email",     "label"=>"Email",         "type"=>"text",           "table"=>"true","dump"=>"true"),
    array("name"=>"user_env",       "label"=>"Environment",   "type"=>"text",           "table"=>"false","dump"=>"true"),
    array("name"=>"user_ip",        "label"=>"IP@",           "type"=>"text",           "table"=>"true","dump"=>"true"),
    array("name"=>"request_type",   "label"=>"Request",       "type"=>"text",           "table"=>"true","dump"=>"true"),
    array("name"=>"request_uri",    "label"=>"URI",           "type"=>"text",           "table"=>"false","dump"=>"true"),
    array("name"=>"request_m0",     "label"=>"m[0]",          "type"=>"text",           "table"=>"true","dump"=>"true"),
    array("name"=>"request_m1",     "label"=>"m[1]",          "type"=>"text",           "table"=>"true","dump"=>"true"),
    array("name"=>"request_m2",     "label"=>"m[2]",          "type"=>"text",           "table"=>"true","dump"=>"true"),
    array("name"=>"request_m3",     "label"=>"m[3]",          "type"=>"text",           "table"=>"true","dump"=>"true"),
    array("name"=>"request_m4",     "label"=>"m[4]",          "type"=>"text",           "table"=>"false","dump"=>"true"),
    array("name"=>"request_m5",     "label"=>"m[5]",          "type"=>"text",           "table"=>"false","dump"=>"true")
);

$SMARTY_LOCATION = "intranet";
$DEFAULT_TEMPLATE = "intranet.tpl";
include("header.inc.php");
include("db_common2.inc.php");
if(!$id)$id=0;
include("db_readonly2.inc.php");
//include("db_admin2.inc.php");
?>