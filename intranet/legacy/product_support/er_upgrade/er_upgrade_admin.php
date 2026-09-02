<?php
include_once("common.inc.php");
include_once("forms_and_reports.inc.php");
$user = new tldUser($GLOBALS["PHP_AUTH_USER"]);

if(!$user->isInGroup(array("gg_ADMIN","gg_MIS"))){
	echo "You do not have permissions for this page..";
	exit;
}

$main_tpl=array(
  array("table"=>"service_upgrades","form_type"=>"main_tpl","title"=>"ER Upgrade Admin","cancel"=>"table&form_type=",
  		"mode"=>"record_view",
		"prn_table_menu"=>"<a href=\"/en/private/product_support/index.ps.php?m[0]=er_upgrade\">Back to module</a>&nbsp;|&nbsp;",
  		"prn_record_menu"=>"<b><a href=\"/en/private/product_support/index.ps.php?m[0]=er_upgrade&m[1]=view&id={id}\">Back to module</a></b>&nbsp;|&nbsp;",
  		"default_sort"=>"id"),
  array("name"=>"id",			"label"=>"Primary Key",			"type"=>"primary_key",		"table"=>"true",	"dump"=>"true"),
  array("name"=>"parent_id",	"label"=>"Foreign Key",			"type"=>"foreign_key",		"table"=>"false",	"dump"=>"false"),
    array("name" => "poster_id", "label" => "Poster ID", "type" => "select", "table" => "true", "dump" => "true",
        "select_list" => tldGroup::getUserListByMultipleGroup(["role_PSM", "role_PSE", "role_PSA", "gg_PARTS", "gg_SERVICE", "gg_MIS"], "", "smartyOptions"),
        "source" => "smartyOptions"),
  array("name"=>"dt_open",		"label"=>"Date of Opening (YYYY-MM-DD)",		"type"=>"date",				"table"=>"true",	"dump"=>"true"),
  array("name"=>"dt_upgrade",	"label"=>"Effective Upgrade Date (YYYY-MM-DD)",	"type"=>"date",				"table"=>"true",	"dump"=>"true"),
  array("name"=>"description",	"label"=>"Description",			"type"=>"textarea",			"table"=>"false",	"dump"=>"true",
  		"textarea_params"=>" wrap=\"VIRTUAL\" cols=\"80\" rows=\"10\""),
  array("name" => "pn", "label" => "Part Number", "type" => "text", "table" => "true", "dump" => "true"),
);

$SMARTY_LOCATION = "intranet";
$DEFAULT_TEMPLATE = "intranet.tpl";

include("header.inc.php");

//Include common db functions
include("db_common2.inc.php");
if (!$id)$id=0;
//Include db functions
if($user->isInGroup(array("gg_ADMIN", "gg_MIS"))){
    include("db_admin2.inc.php");
}else{
    include("db_readonly2.inc.php");
}

?>
