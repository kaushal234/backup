<?php
include("common.inc.php");

$user = new tldUser($GLOBALS["PHP_AUTH_USER"]);
if(!$user->isInGroup("superuser")){
	echo "ERROR: You do not have permission to access this page.";
    exit;
}

$main_tpl=array(
	array("table"=>"mod_not","form_type"=>"main_tpl","title"=>"Module NOT",
	"cancel"=>"table&form_type=main_tpl","mode"=>"record_view",
	"prn_table_menu"=>"<a href=\"/en/private/common/index.php?m[0]=not\">Back to Module</a>",
	"prn_record_menu"=>"<a href=\"/en/private/common/index.php?m[0]=not&m[1]=view&id={id}\">Back to Module</a>&nbsp;|&nbsp;",
	"default_sort"=>"id"),
	array("name"=>"id",			"label"=>"Primary Key",			"type"=>"primary_key",	"table"=>"true"),
	array("name"=>"parent_id",	"label"=>"Ref#",				"type"=>"text",	        "table"=>"true","dump"=>"true"),
	array("name"=>"module",		"label"=>"Module",				"type"=>"select",		"table"=>"true","dump"=>"true",
        "select_list"=>array_keys(tldUtils::getModLinks())),
    array("name"=>"uid",		"label"=>"Poster",				"type"=>"select",		"table"=>"true","dump"=>"true",
        "select_list"=>tldDirectory::getUserList("smartyOptions"), "source"=>"smartyOptions"),
	array("name"=>"dt",				"label"=>"Date",				"type"=>"locked",	    "table"=>"true","dump"=>"true"),
	array("name"=>"email_from",		"label"=>"From",				"type"=>"text",	    	"table"=>"true","dump"=>"true"),
	array("name"=>"email_to",		"label"=>"To",					"type"=>"text",	    	"table"=>"true","dump"=>"true"),
	array("name"=>"email_cc",		"label"=>"Cc",					"type"=>"text",	    	"table"=>"true","dump"=>"true"),
	array("name"=>"email_bcc",		"label"=>"Bcc",					"type"=>"text",	    	"table"=>"true","dump"=>"true"),
	array("name"=>"email_subject",	"label"=>"Subject",				"type"=>"text",	    	"table"=>"true","dump"=>"true"),
	array("name"=>"email_body",    	"label"=>"Body",             	"type"=>"textarea",     "table"=>"false","dump"=>"true",
        "textarea_params"=>" wrap='VIRTUAL' cols='40' rows='7'")
);

$SMARTY_LOCATION = "intranet";
$DEFAULT_TEMPLATE = "intranet.tpl";

include_once("header.inc.php");
include("db_common2.inc.php");
if (!$id)$id=0;
include("db_admin2.inc.php");
?>
