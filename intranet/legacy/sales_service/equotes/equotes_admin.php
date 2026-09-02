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
  array("table"=>"csr","form_type"=>"main_tpl","title"=>"Customer Service Request","cancel"=>"table&form_type=",
  		"mode"=>"record_view",
		"prn_table_menu"=>"<a href=\"/en/private/sales_service/service.php?m[0]=csr\">Back to Module</a>&nbsp;|&nbsp;",
 		"prn_record_menu"=>"<b><a href=\"/en/private/sales_service/service.php?m[0]=csr&m[1]=view&id={id}\">Back to Module</a></b>&nbsp;|&nbsp;",
  		"default_sort"=>"id"),
  array("name"=>"id",		"label"=>"Primary Key",		"type"=>"primary_key",	"table"=>"true","dump"=>"true"),
  array("name"=>"parent_id","label"=>"Foreign Key",		"type"=>"foreign_key"),
	array("name"=>"call_via",		"label"=>"Call Method",				"type"=>"select",		"table"=>"true","dump"=>"true",
  		"select_list"=>array(	"TEL","EMAIL")
		),
  array("name"=>"dt",		"label"=>"Date Entered",	"type"=>"auto_date"),
	array("name"=>"status",		"label"=>"Status",				"type"=>"select",		"table"=>"true","dump"=>"true",
  		"select_list"=>array(	"OPEN","CLOSED")
		),
  array("name"=>"entered_by","label"=>"Entered By",     "type"=>"auto_user",	"table"=>"false","dump"=>"true",
            "field"=>"id"),
  array("name"=>"assignor",	"label"=>"Assignor",		"type"=>"select",	"table"=>"false","dump"=>"true",
  		"select_list"=>tldDirectory::getUserList("smartyOptions"), "source"=>"smartyOptions"),
  array("name"=>"dt_sche",		"label"=>"Scheduled Date and Time",	"type"=>"date"),
  array("name"=>"apc",		"label"=>"Airport Code",					"type"=>"select",	"table"=>"true","dump"=>"true",
  		"source"=>"smartyOptions", "select_list"=>array(""=>"") + tldAirport::getList()),
  array("name"=>"tech_id",	"label"=>"Service Tech",					"type"=>"select",	"table"=>"false","dump"=>"true",
  		"select_list"=>$sts, "source"=>"smartyOptions"),
  array("name"=>"cust_nama",	"label"=>"Customer Name","type"=>"select_db",	"table"=>"true","dump"=>"true",
  		"select_query"=>"SELECT * FROM customers WHERE hidden<>'1' ORDER BY customer_name",
  		"select_field_1"=>"customer_name","select_field_2"=>"customer_name"),
array("name"=>"cust_cona",	"label"=>"Customer Contact",	"type"=>"text",			"table"=>"true"),
array("name"=>"cust_tela",	"label"=>"Customer Tel",	"type"=>"text",			"table"=>"true"),
array("name"=>"cust_telb",	"label"=>"Customer Tel Alt",	"type"=>"text",			"table"=>"true"),
  array("name"=>"notes",	"label"=>"Notes",	"type"=>"textarea",		"table"=>"false","dump"=>"true",
  		"textarea_params"=>" wrap=\"VIRTUAL\" cols=\"80\" rows=\"10\""),
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