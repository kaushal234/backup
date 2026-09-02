<?php
$main_tpl=array(
  array("table"=>"news","form_type"=>"main_tpl","title"=>"External News",
  "cancel"=>"table&form_type=main_tpl","mode"=>"record_view",
  "default_sort"=>"date"),
  array("name"=>"id","label"=>"Primary Key","type"=>"primary_key","table"=>"false"),
  array("name"=>"parent_id","label"=>"Foreign Key","type"=>"foreign_key","table"=>"false"),
  array("name"=>"cat","label"=>"Category","type"=>"lookup","table"=>"false","select_query"=>"SELECT * FROM categories ORDER BY name","select_field_1"=>"id","select_field_2"=>"name",
        "select_query_view"=>"SELECT * FROM categories where id=","select_field_view"=>"image"),
  array("name"=>"date","label"=>"Date&nbsp;(yyyy-mm-dd)","type"=>"auto_date","table"=>"true"),
  array("name"=>"en_title","label"=>"English Title","type"=>"text","table"=>"true"),
//  array("name"=>"en","label"=>"English Article","type"=>"textarea","table"=>"false","textarea_params"=>" wrap=\"VIRTUAL\" cols=\"60\" rows=\"10\""),
//  array("name"=>"fr_title","label"=>"French Title","type"=>"text","table"=>"false"),
//  array("name"=>"fr","label"=>"French Article","type"=>"textarea","table"=>"false","textarea_params"=>" wrap=\"VIRTUAL\" cols=\"60\" rows=\"10\""),
  array("name"=>"ja_title","label"=>"Japanese Title","type"=>"text","table"=>"false"),
  array("name"=>"ja","label"=>"Japanese Article","type"=>"textarea","table"=>"false","textarea_params"=>" wrap=\"VIRTUAL\" cols=\"60\" rows=\"10\"")
//  array("name"=>"pt_title","label"=>"Portugues Title","type"=>"text","table"=>"false"),
//  array("name"=>"pt","label"=>"Portugues Article","type"=>"textarea","table"=>"false","textarea_params"=>" wrap=\"VIRTUAL\" cols=\"60\" rows=\"10\"")
);
$SMARTY_LOCATION = "intranet";
$DEFAULT_TEMPLATE = "intranet.tpl";

include("header.inc.php");
$lang = "ja";
//Include common db functions
include("db_common2.inc.php");
if (!$id)$id=0;
//Include db functions
include("db_admin2.inc.php");