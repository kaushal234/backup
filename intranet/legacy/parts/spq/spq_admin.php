<?php
include_once("common.inc.php");
include_once("sales_service.inc.php");
$user = new tldUser($GLOBALS["PHP_AUTH_USER"]);

if(!$user->isInGroup(array("gg_ADMIN","superuser"))){
	echo "You do not have permissions for this page...";
	exit;
}

$main_tpl=array(
	array("table"=>"spq","form_type"=>"main_tpl","title"=>"Spare Parts Quote","cancel"=>"table&form_type=",
  		"mode"=>"record_view",
		"child_tables"=>array("spr_lines"),
		"prn_table_menu"=>"<a href=\"/en/private/parts/parts.php?m[0]=spq\">Back to Module</a>&nbsp;|&nbsp;",
 		"prn_record_menu"=>"<b><a href=\"/en/private/parts/parts.php?m[0]=spq&m[1]=view&id={id}\">Back to Module</a></b>&nbsp;|&nbsp;",
  		"default_sort"=>"id"),
	array("name"=>"id",			"label"=>"Primary Key",		"type"=>"primary_key",	"table"=>"true",	"dump"=>"true"),
	array("name"=>"parent_id",	"label"=>"Foreign Key",		"type"=>"foreign_key"),
	array("name"=>"dt_open",	"label"=>"Date Entered",	"type"=>"auto_date"),
	array("name"=>"sph_id",		"label"=>"SPH",				"type"=>"select",		"table"=>"true",	"dump"=>"true",
	    "select_list"=>tldSPH::getList("smartyOptionsIDLocation"), "source"=>"smartyOptions"),
	array("name"=>"dt_received","label"=>"Quote Request Date",	"type"=>"text",			"table"=>"true"),
	array("name"=>"dt_ship","label"=>"Requested Ship Date",	"type"=>"text",			"table"=>"true"),
	array("name"=>"poster_id",	"label"=>"Owner",		"type"=>"select",		"table"=>"false",	"dump"=>"true",
		"select_list"=>tldDirectory::getUserList("smartyOptions"), "source"=>"smartyOptions"),
	array("name"=>"request_type",	"label"=>"Request Type","type"=>"select",		"table"=>"true",	"dump"=>"true",
		"select_list"=>tldSPQ::getTypeList(), "source"=>"smartyOptions"),
	array("name"=>"status",		"label"=>"Status",			"type"=>"select",		"table"=>"true",	"dump"=>"true",
		"select_list"=>tldSPQ::getStatusList(), "source"=>"smartyOptions"),
 	array("name"=>"customer_id",	"label"=>"Customer",	"type"=>"lookup",		"table"=>"true",	"dump"=>"true",
  		"select_query"=>"SELECT id,customer_name FROM customers WHERE hidden<>'1' ORDER BY customer_name",
  		"select_field_1"=>"id","select_field_2"=>"customer_name",
  		"select_query_view"=>"SELECT customer_name FROM customers WHERE id=",
		"select_field_view"=>"customer_name"),
	array("name"=>"contact_id",	"label"=>"Contact (#ID)",	"type"=>"text",			"table"=>"true"),
	array("name"=>"qono","label"=>"Baan Quotation Number",	"type"=>"text",			"table"=>"true"),
	array("name"=>"qono_val",	"label"=>"Quote Value",		"type"=>"text",			"table"=>"true"),
    array("name"=>"baan_so",	"label"=>"Baan SO#",		"type"=>"text",			"table"=>"true"),
	array("name"=>"rfq",		"label"=>"RFQ#",			"type"=>"text",			"table"=>"true"),
	array("name"=>"comment",		"label"=>"Comments",	"type"=>"textarea",		"table"=>"false",	"dump"=>"true",
  		"textarea_params"=>" wrap=\"VIRTUAL\" cols=\"80\" rows=\"10\""),
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