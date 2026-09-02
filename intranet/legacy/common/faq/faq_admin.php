<?php
include("common.inc.php");

$user = new tldUser($GLOBALS["PHP_AUTH_USER"]);
if(!$user->isInGroup("superuser")){
	echo "ERROR: You do not have permission to access this page.";
    exit;
}

$main_tpl=array(
	array("table"=>"mod_faq","form_type"=>"main_tpl","title"=>"Module FAQ",
	"cancel"=>"table&form_type=main_tpl","mode"=>"record_view",
	"prn_table_menu"=>"<a href=\"/en/private/common/index.php?m[0]=faq\">Back to Module</a>",
	"prn_record_menu"=>"<a href=\"/en/private/common/index.php?m[0]=faq&m[1]=view&id={id}\">Back to Module</a>&nbsp;|&nbsp;",
	"default_sort"=>"id"),
	array("name"=>"id",			"label"=>"Primary Key",			"type"=>"primary_key",	"table"=>"true"),
	array("name"=>"parent_id",	"label"=>"Ref#",				"type"=>"text",	        "table"=>"true","dump"=>"true"),
	array("name"=>"module",		"label"=>"Module",				"type"=>"select",		"table"=>"true","dump"=>"true",
        "select_list"=>array_keys(tldUtils::getModLinks())),
    array("name"=>"symptom",    "label"=>"Symptom",             "type"=>"textarea",     "table"=>"false","dump"=>"true",
        "textarea_params"=>" wrap='VIRTUAL' cols='40' rows='7'"),
    array("name"=>"problem",    "label"=>"Problem",             "type"=>"textarea",     "table"=>"false","dump"=>"true",
        "textarea_params"=>" wrap='VIRTUAL' cols='40' rows='7'"),
    array("name"=>"solution",    "label"=>"Solution",             "type"=>"textarea",     "table"=>"false","dump"=>"true",
        "textarea_params"=>" wrap='VIRTUAL' cols='40' rows='7'")
);

$SMARTY_LOCATION = "intranet";
$DEFAULT_TEMPLATE = "intranet.tpl";

include_once("header.inc.php");
include("db_common2.inc.php");
if (!$id)$id=0;
include("db_admin2.inc.php");
?>
