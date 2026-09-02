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
  array("table"=>"pip","form_type"=>"main_tpl","title"=>"Product Innovation Proposals","cancel"=>"table&form_type=",
  		"mode"=>"record_view",
		"prn_table_menu"=>"<a href=\"/en/private/manufacturing/eng/dev.php?m[0]=pip\">Back to Module</a>&nbsp;|&nbsp;",
 		"prn_record_menu"=>"<b><a href=\"/en/private/manufacturing/eng/dev.php?m[0]=pip&m[1]=view&id={id}\">Back to Module</a></b>&nbsp;|&nbsp;",
  		"default_sort"=>"id"),
  array("name"=>"id",			"label"=>"ID&nbsp;####", 				"type"=>"primary_key",	"table"=>"true","dump"=>"true"),
  array("name"=>"parent_id",	"label"=>"Foreign Key",					"type"=>"foreign_key",	"table"=>"false"),
  array("name"=>"status",		"label"=>"Status",						"type"=>"locked_field",		"table"=>"true","dump"=>"true"),

	array("name"=>"factory",	"label"=>"Factory Location",			"type"=>"lookup",	"table"=>"true",
		"select_query"=>"SELECT id,location FROM locations WHERE erp<>0 ORDER BY location",
		"select_field_1"=>"id","select_field_2"=>"location",
        "select_query_view"=>"SELECT * FROM locations WHERE id=",
		"select_field_view"=>"location"),
  array("name"=>"ifactor",		"label"=>"Importance Factor",		"type"=>"select",		"table"=>"false","dump"=>"true",
  		"select_list"=>array(	"1","10","100","1000")
		),
  array("name"=>"product_type",		"label"=>"Equipment Type",			"type"=>"select_db",	"table"=>"true","dump"=>"true",
  		"select_query"=>"SELECT en FROM products_categories ORDER BY en",
		"select_field_1"=>"en","select_field_2"=>"en"),
  array("name"=>"model",		"label"=>"Equipment Model<br>(use ALL_MODELS selection for no specific model)",	"type"=>"select_db",	"table"=>"false","dump"=>"true",
  		"select_query"=>"SELECT model FROM models ORDER BY model",
		"select_field_1"=>"model","select_field_2"=>"model"),
  array("name"=>"date",			"label"=>"Date<br>(yyyy-mm-dd)",	"type"=>"auto_date",	"table"=>"true","dump"=>"true"),
	array("name"=>"short_desc",	"label"=>"Short Description",		"type"=>"text",			"table"=>"true","dump"=>"true",
	"width"=>"50"),
  array("name"=>"description",	"label"=>"Description",			"type"=>"textarea",		"table"=>"false","dump"=>"true",
  		"textarea_params"=>" wrap=\"VIRTUAL\" cols=\"80\" rows=\"10\""),
  array("name"=>"resolution",	"label"=>"Resolution",			"type"=>"textarea",		"table"=>"false","dump"=>"true",
  		"textarea_params"=>" wrap=\"VIRTUAL\" cols=\"80\" rows=\"10\""),
  array("name"=>"rejection_reason",	"label"=>"Rejection Reason",			"type"=>"textarea",		"table"=>"false","dump"=>"true",
  		"textarea_params"=>" wrap=\"VIRTUAL\" cols=\"80\" rows=\"10\""),
  array("name"=>"picture_filename",		"label"=>"Picture",				"type"=>"file_upload",	"table"=>"true",
  		"file_upload_dir"=>"pip"),
  array("name"=>"poster",	"label"=>"Posted By",					"type"=>"select",	"table"=>"false","dump"=>"true",
  		"select_list"=>tldDirectory::getUserList("smartyOptions"), "source"=>"smartyOptions"),
  array("name"=>"initiator",	"label"=>"Initiator",				"type"=>"select",	"table"=>"false","dump"=>"true",
  		"select_list"=>tldDirectory::getUserList("smartyOptions"), "source"=>"smartyOptions")
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