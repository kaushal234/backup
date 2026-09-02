<?php
include_once("common.inc.php");
include_once("mis.inc.php");

$divisions = tldRegion::getListAsIdDivision();
$typeList = Tld_Mis_Inventory_ItemType::getListAsIdName();
$brandList = Tld_Mis_Inventory_ItemBrand::getListAsIdName();
$stateList = [
	'GOOD'=>'GOOD',
	'USABLE'=>'USABLE',
	'TO BE REPAIRED'=>'TO BE REPAIRED',
	'UNDER CONTRACT'=>'UNDER CONTRACT',
	'EXPIRED'=>'EXPIRED',
	'UNLINITED'=>'UNLINITED',
];
$locationList = tldLocation::getLocationList("smartyOptions");
$erpList = tldLocation::getERPList("smartyOptionsIDLocation");
$dptList = tldDepartment::getListAsIdDepartment();
$categoryList = Tld_Mis_Inventory_ItemType::getCategoryList();
$yesNoList = ['0' => 'No', '1' => 'Yes', '2' => 'IN PROGRESS'];

$main_tpl = [
	["table" => "mis_inventory_items", "form_type" => "main_tpl", "title" => "MIS Inventory",
		"cancel" => "table&form_type=main_tpl", "mode" => "record_view",
		"child_tables" => "",
		"prn_table_menu" => "<a href=\"/en/private/mis/mis.php?m[0]=inventory\">Back to Module</a>&nbsp;|&nbsp;",
		"prn_record_menu" => "<a href=\"/en/private/mis/mis.php?m[0]=inventory&m[1]=view&id={id}\">Back to Module</a>&nbsp;|&nbsp;",
		"default_sort" => "id"],
	["name" => "id", "label" => "Primary Key", "type" => "primary_key", "table" => "true", "dump" => "true"],
	["name" => "dt", "label" => "Date", "type" => "auto_date", "table" => "true", "dump" => "true"],
	["name" => "dt_warranty_end", "label" => "Warranty Expiration", "type" => "auto_date", "table" => "true", "dump" => "true"],
	["name" => "type_id", "label" => "Type_id",  "type"=>"select",           "table"=>"true",    "dump"=>"true",
		"select_list"=>$typeList, "source"=>"smartyOptions"],
	["name" => "brand_id", "label" => "Brand_id", "type" => "select", "table" => "true", "dump" => "true",
		"select_list"=>$brandList, "source"=>"smartyOptions"],
	["name" => "model", "label" => "Model", "type" => "text", "table" => "true", "dump" => "true",],
	["name" => "manufacturer_sn", "label" => "Manufacturer_sn", "type" => "text", "table" => "true", "dump" => "true",],
	["name" => "tld_sn", "label" => "Serial Number", "type" => "text", "table" => "true", "dump" => "true",
		"textarea_params" => " wrap=\"VIRTUAL\" cols=\"60\" rows=\"5\"",
	],
	["name" => "fixasset_id", "label" => "Fix-Asset#", "type" => "text", "table" => "true", "dump" => "true"],
	["name" => "state", "label" => "State", "type" => "select", "table" => "true", "dump" => "true",
		"select_list"=>$stateList, "source"=>"smartyOptions"],
	["name" => "buyer_bu_id", "label" => "Buyer ID", "type" => "select", "table" => "true", "dump" => "true",
		"select_list"=>$erpList, "source"=>"smartyOptions"],
	["name" => "buyer_dpt_id", "label" => "Buyer DEPT ID", "type" => "select", "table" => "true", "dump" => "true",
		"select_list"=>$dptList, "source"=>"smartyOptions"],
	["name" => "hidden", "label" => "Hidden?", "type" => "select", "table" => "true", "dump" => "true",
		"select_list"=>$yesNoList, "source"=>"smartyOptions"],
];

//Include common db functions
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
