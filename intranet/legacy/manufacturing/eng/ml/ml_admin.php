<?php
include_once("common.inc.php");

$main_tpl=array(
  array("table"=>"erp_ml","form_type"=>"main_tpl","title"=>"ML Admin",
  "child_tables"=>array("erp_mll"),
  "cancel"=>"table&form_type=","mode"=>"record_view",
  "prn_table_menu"=>"<a href=\"/en/private/manufacturing/eng/dev.php?m[0]=ml\">Back to ML Module</a>&nbsp;|&nbsp;",
//  "prn_record_menu"=>"<a href=\"/en/private/manufacturing/eng/dev.php?m[0]=eap&m[1]=view&id={id}\"><b>Back to EAP</b></a>&nbsp;|&nbsp;",
  "default_sort"=>"id"),
//start of table definitions
  array("name"=>"id",			"label"=>"ID&nbsp;####",	"type"=>"primary_key",	"table"=>"true","dump"=>"true"),
  array("name"=>"parent_id",	"label"=>"Foreign Key",		"type"=>"foreign_key",	"table"=>"false"),
	array("name"=>"erp",			"label"=>"ERP",		"type"=>"text",	"table"=>"true"),
	array("name"=>"t_prno",			"label"=>"Project Number",		"type"=>"text",	"table"=>"true"),
	array("name"=>"dt_entered",			"label"=>"DT Entered",		"type"=>"date",	"table"=>"true"),
	array("name"=>"dsca",			"label"=>"Short Description",		"type"=>"text",			"table"=>"true",
  		"width"=>"60"
		)
);

$erp_mll_tpl=array(
  array("table"=>"erp_mll","form_type"=>"erp_mll_tpl",
  		"title"=>"Material List Lines",
  		"cancel"=>"record_view&form_type=main_tpl","mode"=>"table"
		),
  array("name"=>"id",			"label"=>"Primary Key",				"type"=>"primary_key",	"table"=>"false"),
  array("name"=>"parent_id",	"label"=>"Foreign Key",				"type"=>"foreign_key",	"table"=>"false"),
  array("name"=>"tsec",			"label"=>"PID",		"type"=>"text"),
array("name"=>"t_prno",			"label"=>"Project Number",		"type"=>"text"),
array("name"=>"t_mitm",			"label"=>"Parent PN",		"type"=>"text",	"table"=>"true"),
array("name"=>"t_pono",			"label"=>"Position Number",		"type"=>"text",	"table"=>"true"),
array("name"=>"t_sitm",			"label"=>"PN",		"type"=>"text",	"table"=>"true"),
array("name"=>"t_qana",			"label"=>"Qty",		"type"=>"text",	"table"=>"true"),
array("name"=>"t_cuni",			"label"=>"UM",		"type"=>"text",	"table"=>"true"),
array("name"=>"t_dsca",			"label"=>"Description",		"type"=>"text",	"table"=>"true"),
array("name"=>"t_csig",			"label"=>"Code",	"type"=>"text",	"table"=>"true"),
array("name"=>"t_revi",			"label"=>"Rev",		"type"=>"text",	"table"=>"true"),
array("name"=>"t_indt",			"label"=>"Start Date (YYYY-MM-DD)",		"type"=>"auto_date",	"table"=>"true"),
array("name"=>"t_exdt",			"label"=>"End Date (YYYY-MM-DD)",		"type"=>"auto_date",	"table"=>"true"),
array("name"=>"p",				"label"=>"P",		"type"=>"text",	"table"=>"true"),
array("name"=>"m",				"label"=>"M",		"type"=>"text",	"table"=>"true"),
array("name"=>"o",				"label"=>"O",		"type"=>"text",	"table"=>"true"),
array("name"=>"c",				"label"=>"C",		"type"=>"text",	"table"=>"true"),
array("name"=>"lev",			"label"=>"Level",	"type"=>"text")
);


$SMARTY_LOCATION = "intranet";
$DEFAULT_TEMPLATE = "intranet.tpl";

include_once("header.inc.php");
include_once("db_common2.inc.php");
$user = new tldUser($GLOBALS["PHP_AUTH_USER"]);

if($user->isInGroup(array('gg_ENG',"gg_ADMIN"))){
	include("db_admin2.inc.php");
}else{
	include("db_readonly2.inc.php");
}
?>