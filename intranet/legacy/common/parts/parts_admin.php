<?php
include("common.inc.php");

$user = new tldUser($GLOBALS["PHP_AUTH_USER"]);
if(!$user->isInGroup("superuser")){
	echo "ERROR: You do not have permission to access this page.";
    exit;
}

$main_tpl=array(
	array("table"=>"mod_parts","form_type"=>"main_tpl","title"=>"Module PARTS",
	"cancel"=>"table&form_type=main_tpl","mode"=>"record_view",
	"prn_table_menu"=>"<a href=\"/en/private/common/index.php?m[0]=parts\">Back to Module</a>",
	"prn_record_menu"=>"<a href=\"/en/private/common/index.php?m[0]=parts&m[1]=view&id={id}\">Back to Module</a>&nbsp;|&nbsp;",
	"default_sort"=>"id"),
	array("name"=>"id",			"label"=>"Primary Key",			"type"=>"primary_key",	"table"=>"true"),
	array("name"=>"parent_id",	"label"=>"Ref#",				"type"=>"text",	        "table"=>"true","dump"=>"true"),
	array("name"=>"module",		"label"=>"Module",				"type"=>"select",		"table"=>"true","dump"=>"true",
        "select_list"=>array_keys(tldUtils::getModLinks())),
    array("name"=>"pn",			"label"=>"Part Number",			"type"=>"text",			"table"=>"true","dump"=>"true"),
    array("name"=>"dsc",		"label"=>"Description",			"type"=>"text",			"table"=>"true","dump"=>"true"),
    array("name"=>"qty",		"label"=>"Quantity",			"type"=>"text",			"table"=>"true","dump"=>"true"),
);

$SMARTY_LOCATION = "intranet";
$DEFAULT_TEMPLATE = "intranet.tpl";

include_once("header.inc.php");
include("db_common2.inc.php");
if (!$id)$id=0;
include("db_admin2.inc.php");
?>
