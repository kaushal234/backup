<?php
$main_tpl=array(
  array("table"=>"lists","form_type"=>"main_tpl","title"=>"Lists",
  "cancel"=>"table&form_type=main_tpl","mode"=>"record_view",
  "default_sort"=>"list_name"),
  array("name"=>"id",			"label"=>"Primary Key","type"=>"primary_key","table"=>"false"),
  array("name"=>"list_name",	"label"=>"List Name",	"type"=>"text","table"=>"true", "dump"=>"true"),
  array("name"=>"list_key",		"label"=>"List Key",	"type"=>"text","table"=>"true"),
  array("name"=>"list_item",	"label"=>"List Item",	"type"=>"text","table"=>"true")
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
