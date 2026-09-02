<?php

$DEFAULT_ERROR[]="WARNING: Please remember to always create a translation request when adding/updating a news";

$main_tpl=array(
  array("table"=>"news","form_type"=>"main_tpl","title"=>"External News",
  "prn_record_menu"=>"<b><a href=\"/admin/tools/index.php?m[0]=translation_request&type=news&id={id}\">Create translation request</a></b>&nbsp;|&nbsp;",
  "cancel"=>"table&form_type=main_tpl","mode"=>"record_view",
  "default_sort"=>"date"),
  array("name"=>"id",			"label"=>"Primary Key",				"type"=>"primary_key",	"table"=>"false"),
  array("name"=>"parent_id",	"label"=>"Foreign Key",				"type"=>"foreign_key",	"table"=>"false"),
  array("name"=>"cat",			"label"=>"Category",				"type"=>"lookup",		"table"=>"false","dump"=>"true",
  		"select_query"=>"SELECT * FROM categories ORDER BY name",
		"select_field_1"=>"id","select_field_2"=>"name",
        "select_query_view"=>"SELECT * FROM categories where id=","select_field_view"=>"image"),
  array("name"=>"date",			"label"=>"Date&nbsp;(yyyy-mm-dd)",	"type"=>"auto_date",	"table"=>"true","dump"=>"true"),
  array("name"=>"en_title",		"label"=>"English Title",			"type"=>"text",			"table"=>"true","dump"=>"true"),
  array("name"=>"en",			"label"=>"English Article",			"type"=>"textarea",		"table"=>"false","dump"=>"true",
  		"textarea_params"=>" wrap=\"VIRTUAL\" cols=\"60\" rows=\"10\""),
  array("name"=>"fr_title",		"label"=>"French Title",			"type"=>"text",			"table"=>"false","dump"=>"true"),
  array("name"=>"fr",			"label"=>"French Article",			"type"=>"textarea",		"table"=>"false","dump"=>"true",
  		"textarea_params"=>" wrap=\"VIRTUAL\" cols=\"60\" rows=\"10\""),
  array("name"=>"es_title",		"label"=>"Spanish Title",			"type"=>"text",			"table"=>"false","dump"=>"true"),
  array("name"=>"es",			"label"=>"Spanish Article",			"type"=>"textarea",		"table"=>"false","dump"=>"true",
  		"textarea_params"=>" wrap=\"VIRTUAL\" cols=\"60\" rows=\"10\""),
  array("name"=>"pt_title",		"label"=>"Portugues Title",			"type"=>"text",			"table"=>"false","dump"=>"true"),
  array("name"=>"pt",			"label"=>"Portugues Article",		"type"=>"textarea",		"table"=>"false","dump"=>"true",
  		"textarea_params"=>" wrap=\"VIRTUAL\" cols=\"60\" rows=\"10\""),
  array("name"=>"de_title",		"label"=>"German Title",			"type"=>"text",			"table"=>"false","dump"=>"true"),
  array("name"=>"de",			"label"=>"German Article",		"type"=>"textarea",		"table"=>"false","dump"=>"true",
  		"textarea_params"=>" wrap=\"VIRTUAL\" cols=\"60\" rows=\"10\"")
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
