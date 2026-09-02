<?php

$main_tpl = array(
	array(
		'table'=>'translations',
		'form_type'=>'main_tpl',
		'title'=>'Translations',
		'cancel'=>'table&form_type=main_tpl',
		'mode'=>'record_view',
		'default_sort'=>'id'
	),
	
	array(
		'name'=>'id',
		'label'=>'Primary Key',
		'type'=>'primary_key',
		'table'=>'false'
	),
	
	array(
		'name'=>'parent_id',
		'label'=>'Foreign Key',
		'type'=>'foreign_key',
		'table'=>'false'
	),
	
	array(
		'name'=>'en',
		'label'=>'English (Lookup Default)',
		'type'=>'textarea',
		'table'=>'true',
		'dump'=>'true',
		'textarea_params'=>' wrap="VIRTUAL" cols="60" rows="10"'
	),
	
	array(
		'name'=>'fr',
		'label'=>'French',
		'type'=>'textarea',
		'table'=>'false',
		'dump'=>'true',
		'textarea_params'=>' wrap="VIRTUAL" cols="60" rows="10"'
	),
	
	array(
		'name'=>'de',
		'label'=>'German',
		'type'=>'textarea',
		'table'=>'false',
		'dump'=>'true',
		'textarea_params'=>' wrap="VIRTUAL" cols="60" rows="10"'
	),
	
	array(
		'name'=>'pt',
		'label'=>'Portuguese',
		'type'=>'textarea',
		'table'=>'false',
		'dump'=>'true',
		'textarea_params'=>' wrap="VIRTUAL" cols="60" rows="10"'
	),
	
	array(
		'name'=>'es',
		'label'=>'Spanish',
		'type'=>'textarea',
		'table'=>'false',
		'dump'=>'true',
		'textarea_params'=>' wrap="VIRTUAL" cols="60" rows="10"'
	),
);

include("header.inc.php");

$SMARTY_LOCATION = "intranet";
$DEFAULT_TEMPLATE = "intranet.tpl";

//Include common db functions
include("db_common2.inc.php");
if (!$id)$id=0;
//Include db functions
include("db_admin2.inc.php");
