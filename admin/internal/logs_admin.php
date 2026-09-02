<?php
$main_tpl=array(
  array("table"=>"logs","form_type"=>"main_tpl","title"=>"Transaction logs",
  "cancel"=>"table&form_type=main_tpl","mode"=>"record_view",
  "default_sort"=>"id"),
  array("name"=>"id",			"label"=>"ID",			"type"=>"primary_key",	"table"=>"true"),
  array("name"=>"timestamp",	"label"=>"Timestamp",	"type"=>"text",			"table"=>"true","dump"=>"true"),
  array("name"=>"userid",		"label"=>"Userid",		"type"=>"text",			"table"=>"true","dump"=>"true"),
  array("name"=>"ip",			"label"=>"ip",			"type"=>"text",			"table"=>"true","dump"=>"true"),
  array("name"=>"comment",		"label"=>"Comment",		"type"=>"text",			"table"=>"true","dump"=>"true")
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

?>