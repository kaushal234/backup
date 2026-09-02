1<?php
include_once("common.inc.php");
include_once("quality.inc.php");

// listing
$peopleList = tldDirectory::getUserlist("smartyOptions");
$factoryList = tldLocation::getFactoryList('smartyOptionsIDLocation');
$erpList = tldLocation::getERPList('smartyOptionsIDLocation');
$statusList = tldSCAR::getStatusList();
$iFactorList = tldSCAR::getIFactorList();

$main_tpl=array(
    array("table"=>"scar","form_type"=>"main_tpl","title"=>"SCAR admin",
        "cancel"=>"table&form_type=","mode"=>"record_view","default_sort"=>"id",
        "prn_table_menu"=>"<a href=\"/en/private/manufacturing/qa/dev.php?m[0]=scar\">Back to Module</a>&nbsp;|&nbsp;",
        "prn_record_menu"=>"<b><a href=\"/en/private/manufacturing/qa/dev.php?m[0]=scar&m[1]=view&id={id}\">Back to Module</a></b>&nbsp;|&nbsp;"),
    array("name"=>"id",           "label"=>"SCAR#",               "type"=>"primary_key",      "table"=>"true",    "dump"=>"true"),
    array("name"=>"parent_id",    "label"=>"Parent",              "type"=>"locked_field",     "table"=>"false",   "dump"=>"false"),
    array("name"=>"dt",           "label"=>"Date Entered",        "type"=>"locked_date",      "table"=>"true",    "dump"=>"true"),
    array("name"=>"poster_id",    "label"=>"Poster",          	  "type"=>"select",           "table"=>"true",    "dump"=>"true",
        "select_list"=>$peopleList, "source"=>"smartyOptions"),
    array("name"=>"dt_closed",    "label"=>"Date Closed",         "type"=>"locked_date",      "table"=>"true",    "dump"=>"true"),
	array("name"=>"status",       "label"=>"Status",              "type"=>"select",           "table"=>"true",    "dump"=>"true",
        "select_list"=>$statusList, "source"=>"smartyOptions"),
	array("name"=>"bu_id",        "label"=>"Factory",             "type"=>"select",           "table"=>"true",    "dump"=>"true",
        "select_list"=>$factoryList, "source"=>"smartyOptions"),
	array("name"=>"ifactor",      "label"=>"Importance factor",   "type"=>"select",           "table"=>"true",    "dump"=>"true",
        "select_list"=>$iFactorList, "source"=>"smartyOptions"),
	array("name"=>"supplier_bu_id", "label"=>"Supplier ERP",      "type"=>"select",           "table"=>"true",    "dump"=>"true",
        "select_list"=>$erpList, "source"=>"smartyOptions"),
	array("name"=>"supplier_ref", "label"=>"Supplier Ref",        "type"=>"text",             "table"=>"true",    "dump"=>"true"),
	array("name"=>"supplier_name","label"=>"Supplier Name",       "type"=>"text",             "table"=>"true",    "dump"=>"true"),
	array("name"=>"short_desc",	  "label"=>"Short description",   "type"=>"text",             "table"=>"true",    "dump"=>"true"),
	array("name"=>"description",  "label"=>"Description",      	  "type"=>"textarea",   	  "table"=>"false",   "dump"=>"true",
        "textarea_params"=>" wrap=\"VIRTUAL\" cols=\"60\" rows=\"8\""),
	array("name"=>"root_cause",   "label"=>"Root cause",      	  "type"=>"textarea",   	  "table"=>"false",   "dump"=>"true",
        "textarea_params"=>" wrap=\"VIRTUAL\" cols=\"60\" rows=\"8\""),
	array("name"=>"action",  	  "label"=>"Corrective & Preventive actions", "type"=>"textarea", "table"=>"false",   "dump"=>"true",
        "textarea_params"=>" wrap=\"VIRTUAL\" cols=\"60\" rows=\"8\""),
	array("name"=>"commercial",   "label"=>"Commercial agreements",      	  "type"=>"textarea",   	  "table"=>"false",   "dump"=>"true",
        "textarea_params"=>" wrap=\"VIRTUAL\" cols=\"60\" rows=\"8\""),
	array("name"=>"conclusion",   "label"=>"Conclusion",      	  "type"=>"textarea",   	  "table"=>"false",   "dump"=>"true",
        "textarea_params"=>" wrap=\"VIRTUAL\" cols=\"60\" rows=\"8\""),
	array("name"=>"file_name",	  "label"=>"File",				  "type"=>"file_upload",	  "table"=>"true",
    	"file_upload_dir"=>"scar"),
);

if(!empty($sess_error_message)){
    echo "<p style=\"color:red;text-align:center;\">$sess_error_message</p><br/>";
}

include("header.inc.php");
include("db_common2.inc.php");
$SMARTY_LOCATION = "intranet";
$DEFAULT_TEMPLATE = "intranet.tpl";
if(!$id) $id=0;

$user = new tldUser($GLOBALS["PHP_AUTH_USER"]);
if($user->isInGroup(array("superuser"))){
    include("db_admin2.inc.php");
}else{
    include("db_readonly2.inc.php");
}

?>
