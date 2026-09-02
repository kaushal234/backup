<?php
$main_tpl=array(
  array("table"=>"extranet_login_logs","form_type"=>"main_tpl","title"=>"Extranet Usage",
  "cancel"=>"table&form_type=main_tpl","mode"=>"record_view",
  "default_sort"=>"dt"),
  array("name"=>"id",			"label"=>"ID",			"type"=>"primary_key",	"table"=>"false"),
  array("name"=>"user_id",		"label"=>"User ID",		"type"=>"text",			"table"=>"true","dump"=>"true"),
  array("name"=>"portal",		"label"=>"Portal",		"type"=>"text",			"table"=>"true","dump"=>"true"),
  array("name"=>"dt",			"label"=>"Timestamp",	"type"=>"text",			"table"=>"true","dump"=>"true"),
  array("name"=>"ip_address",	"label"=>"IP Address",	"type"=>"text",			"table"=>"true","dump"=>"true"),
  array("name"=>"http_referer",	"label"=>"Referer",		"type"=>"text",			"table"=>"true","dump"=>"true"),
  array("name"=>"user_agent",	"label"=>"User Agent",	"type"=>"text",			"table"=>"true","dump"=>"true")
);
$SMARTY_LOCATION = "intranet";
$DEFAULT_TEMPLATE = "intranet.tpl";

include("header.inc.php");
//$form_array=${$MY_SESS[$running]["form_type"]};

//Include common db functions
include("db_common2.inc.php");

if (!$id)$id=0;
//Include db functions
include("db_admin2.inc.php");
