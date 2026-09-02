<?php
include_once("common.inc.php");
$user = new tldUser($GLOBALS["PHP_AUTH_USER"]);

if(!$user->isInGroup(array("gg_ADMIN"))){
	echo "You do not have permissions for this page..";
	exit;
}

$grp = new tldGroup("gg_SERVICE");
$sts = $grp->getUserlist("smartyOptions");

$main_tpl=array(
  array("table"=>"ccr","form_type"=>"main_tpl","title"=>"Customer Communication Records","cancel"=>"table&form_type=",
  		"mode"=>"record_view",
		"prn_table_menu"=>"<a href=\"/en/private/sales_service/sales.php?m[0]=ccr\">Back to Module</a>&nbsp;|&nbsp;",
 		"prn_record_menu"=>"<b><a href=\"/en/private/sales_service/sales.php?m[0]=ccr&m[1]=view&id={id}\">Back to Module</a></b>&nbsp;|&nbsp;",
  		"default_sort"=>"id"),
  array("name"=>"id",		"label"=>"Primary Key",		"type"=>"primary_key",	"table"=>"true","dump"=>"true"),
  array("name"=>"parent_id","label"=>"Foreign Key",		"type"=>"foreign_key"),
	array("name"=>"type",		"label"=>"Type",				"type"=>"select",		"table"=>"true","dump"=>"true",
  		"source"=>"lists", "list_name"=>"list.ccr.type"
		),
  array("name"=>"dt",       "label"=>"Date Entered",    "type"=>"auto_date"),
  array("name"=>"dt_eta",     "label"=>"Est completion date",    "type"=>"date"),
  array("name"=>"status",       "label"=>"Status",                      "type"=>"locked_field",     "table"=>"true","dump"=>"true"),
	array("name"=>"cuid",			"label"=>"Customer",			"type"=>"lookup",		"table"=>"true",
		"select_query"=>"SELECT id,customer_name FROM customers ORDER BY customer_name",
		"select_field_1"=>"id","select_field_2"=>"customer_name",
        "select_query_view"=>"SELECT * FROM customers WHERE id=",
		"select_field_view"=>"customer_name",
		"options"=>array("required"=>true)
	),
   array("name"=>"buid",    "label"=>"SSO",            "type"=>"lookup",   "table"=>"true",
        "select_query"=>"SELECT id,location FROM locations WHERE role LIKE 'SSO' ORDER BY location",
        "select_field_1"=>"id","select_field_2"=>"location",
        "select_query_view"=>"SELECT * FROM locations WHERE id=",
        "select_field_view"=>"location"),
  array("name"=>"ifactor",      "label"=>"Importance Factor",       "type"=>"select",       "table"=>"false","dump"=>"true",
        "select_list"=>array(   "1","10","100","1000")
        ),
  array("name"=>"model",        "label"=>"Equipment Model<br>(use ALL_MODELS selection for no specific model)", "type"=>"select_db",    "table"=>"false","dump"=>"true",
        "select_query"=>"SELECT model FROM models ORDER BY model",
        "select_field_1"=>"model","select_field_2"=>"model"),
    array("name"=>"dsca", "label"=>"Short Description",       "type"=>"text",         "table"=>"true","dump"=>"true",
    "width"=>"50"),
  array("name"=>"dscb",  "label"=>"Description",         "type"=>"textarea",     "table"=>"false","dump"=>"true",
        "textarea_params"=>" wrap=\"VIRTUAL\" cols=\"80\" rows=\"10\""),
    array("name"=>"email", "label"=>"Main Email",       "type"=>"text",         "table"=>"true","dump"=>"true",
    "width"=>"50"),
  array("name"=>"email_cc",   "label"=>"Email CC",          "type"=>"textarea",     "table"=>"false","dump"=>"true",
        "textarea_params"=>" wrap=\"VIRTUAL\" cols=\"80\" rows=\"10\"")
//  array("name"=>"rejection_reason", "label"=>"Rejection Reason",            "type"=>"textarea",     "table"=>"false","dump"=>"true",
//        "textarea_params"=>" wrap=\"VIRTUAL\" cols=\"80\" rows=\"10\""),
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