<?php
include_once("common.inc.php");
include_once("forms_and_reports.inc.php");
include_once("sales_service.inc.php");
include_once("erp.inc.php");
$user = new tldUser($GLOBALS["PHP_AUTH_USER"]);

if(!$user->isInGroup(array("gg_MIS"))){
	echo "You do not have permissions for this page..";
	exit;
}

$main_tpl=array(
  array("table"=>"customer_feedback","form_type"=>"main_tpl","title"=>"Customer Feedback","cancel"=>"table&form_type=",
  		"mode"=>"record_view",
		"prn_table_menu"=>"<a href=\"/en/private/sales_service/sales.php?m[0]=cust_feedback\">Back to Module</a>&nbsp;|&nbsp;",
  		"prn_record_menu"=>"<b><a href=\"/en/private/sales_service/sales.php?m[0]=cust_feedback&m[1]=view&id={id}\">Back to Module</a></b>&nbsp;|&nbsp;",
  		"default_sort"=>"id"),
  array("name"=>"id",			"label"=>"Primary Key",			"type"=>"primary_key",		"table"=>"true",	"dump"=>"true"),
  array("name"=>"parent_id",	"label"=>"Foreign Key",			"type"=>"foreign_key",		"table"=>"false",	"dump"=>"false"),
  array("name"=>"dt",		    "label"=>"Date",		"type"=>"locked_date"),
  array("name"=>"source",		"label"=>"Source",				"type"=>"select",			"table"=>"true",	"dump"=>"true",
        "select_list"=>array("EXTRANET","TLD-GROUP.COM")),
  array("name"=>"subject",	    "label"=>"Subject",		        "type"=>"text",				"table"=>"true",	"dump"=>"true"),
  array("name"=>"recipients",	"label"=>"Recipients",		    "type"=>"text",				"table"=>"true",	"dump"=>"false"),
  array("name"=>"customer_name","label"=>"Customer Name",		"type"=>"text",				"table"=>"true",	"dump"=>"true"),
  array("name"=>"customer_id",	"label"=>"Customer ID",			"type"=>"select",			"table"=>"true",	"dump"=>"true",
  		"select_list"=>tldCustomer::getList("smartyOptions"),
  		"source"=>"smartyOptions"),
  array("name"=>"ext_user_id",	"label"=>"Extranet User ID#",	"type"=>"text",				"table"=>"true",	"dump"=>"false"),
  array("name"=>"name",	        "label"=>"Name",	            "type"=>"text",				"table"=>"true",	"dump"=>"true"),
  array("name"=>"email",	    "label"=>"Email",	            "type"=>"text",				"table"=>"true",	"dump"=>"false"),
  array("name"=>"title",	    "label"=>"Title",	            "type"=>"text",				"table"=>"true",	"dump"=>"false"),
  array("name"=>"country",	    "label"=>"Country",	            "type"=>"text",				"table"=>"true",	"dump"=>"false"),
  array("name"=>"phone",	    "label"=>"Phone",	            "type"=>"text",				"table"=>"true",	"dump"=>"false"),
  array("name"=>"er_sn",	    "label"=>"ER SN#",	            "type"=>"text",				"table"=>"true",	"dump"=>"false"),
  array("name"=>"message",		"label"=>"Message",				"type"=>"textarea",			"table"=>"false",	"dump"=>"true",
  		"textarea_params"=>" wrap=\"VIRTUAL\" cols=\"80\" rows=\"10\""),
);

$SMARTY_LOCATION = "intranet";
$DEFAULT_TEMPLATE = "intranet.tpl";

include("header.inc.php");

//Include common db functions
include("db_common2.inc.php");
if (!$id)$id=0;
//Include db functions
if($user->isInGroup(array("gg_MIS"))){
    include("db_admin2.inc.php");
}else{
    include("db_readonly2.inc.php");
}

