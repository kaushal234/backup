<?php
include_once("common.inc.php");
$user = new tldUser(0);
$user->setDetailsByEmail($_SERVER["PHP_AUTH_USER"]);

if(!$user->isInGroup("superuser")){
	echo "ERROR: Access not permitted";
	exit;
}
$main_tpl=array(
  array("table"=>"erp_int","form_type"=>"main_tpl","title"=>"ERP Integration Matrix",
//"prn_table_menu"=>"<a href=\"/en/private/finance/finance.php?m[0]=forex\">Back to Module</a>&nbsp;|&nbsp;",
  "cancel"=>"table&form_type=main_tpl","mode"=>"record_view",
  "default_sort"=>"id"
	),
  array("name"=>"id",				"label"=>"Primary Key",			"type"=>"primary_key",	"table"=>"true"),
  array("name"=>"parent_id",		"label"=>"Foreign Key",			"type"=>"foreign_key",	"table"=>"false"),
	array("name"=>"src",			"label"=>"Source Business Unit",			"type"=>"lookup",	"table"=>"true",
		"select_query"=>"SELECT id,location FROM locations WHERE erp<>0 ORDER BY location",
		"select_field_1"=>"id","select_field_2"=>"location",
        "select_query_view"=>"SELECT CONCAT(location,'[',erp,']') AS fullname FROM locations WHERE id=",
		"select_field_view"=>"fullname"),
  array("name"=>"ttyp",				"label"=>"Transaction Type",	"type"=>"select",	"dump"=>"true", "table"=>"true",
  		"source"=>"lists", "list_name"=>"list.erp.ttyp"
	),
	array("name"=>"dst",			"label"=>"Destination Business Unit",			"type"=>"lookup",	"table"=>"true",
		"select_query"=>"SELECT id,location FROM locations WHERE erp<>0 ORDER BY location",
		"select_field_1"=>"id","select_field_2"=>"location",
        "select_query_view"=>"SELECT CONCAT(location,'[',erp,']') AS fullname FROM locations WHERE id=",
		"select_field_view"=>"fullname"),
);

$SMARTY_LOCATION = "intranet";
$DEFAULT_TEMPLATE = "intranet.tpl";

include("header.inc.php");

//Include common db functions
include("db_common2.inc.php");

if (!$id)$id=0;
//Include db functions
include("db_admin2.inc.php");
?>