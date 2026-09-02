<?php
include_once("common.inc.php");

$thisUser = new tldUser($GLOBALS["PHP_AUTH_USER"]);

if(!$thisUser->isInGroup("gg_MIS")){
	echo "Access not permitted.";
	exit;
}

// List of items
$query = <<<EOF
SELECT
	id, type, serial, LEFT(description,30) AS description,
	CONCAT(type,', ',serial,', ',LEFT(description,30),' (',id,')') AS full_description
FROM mis_inv ORDER BY full_description
EOF;
$rawData = tldUtils::getSqlToAssocArray($query);
$parentList = array();
foreach($rawData as $data){
    $label = $data['full_description'];
    if($data['type']=='PERSONNEL'){
        $label = $data['serial'].', '.$data['description'].", ({$data['id']})";
    }
    $parentList[$data['id']] = $label;
}

$main_tpl=array(
	array("table"=>"mis_inv","form_type"=>"main_tpl","title"=>"MIS Inventory",
		"cancel"=>"table&form_type=main_tpl","mode"=>"record_view",
		"child_tables"=>array("mis_inv_comments"),
		"prn_table_menu"=>"<a href=\"/en/private/mis/mis.php?m[0]=inv\">Back to Module</a>&nbsp;|&nbsp;",
		"prn_record_menu"=>"<a href=\"/en/private/mis/mis.php?m[0]=inv&m[1]=browse&id={id}\">Back to Module</a>&nbsp;|&nbsp;",
		"default_sort"=>"id"),
	array("name"=>"id","label"=>"Primary Key","type"=>"primary_key","table"=>"true", "dump"=>"true"),
    array("name"=>"parent_id","label"=>"Parent","type"=>"select","table"=>"true", "dump"=>"true",
        "select_list"=>$parentList, "source"=>"smartyOptions"),
	array("name"=>"date","label"=>"Date","type"=>"auto_date","table"=>"true" ,"dump"=>"true"),
	array("name"=>"d_wrty","label"=>"Warranty Expiration","type"=>"auto_date","table"=>"true" ,"dump"=>"true"),
	array("name"=>"qty",		"label"=>"Qty",		"type"=>"text",			"table"=>"true", "dump"=>"true",
		"width"=>"5"),
	array("name"=>"description","label"=>"Description","type"=>"textarea","table"=>"true", "dump"=>"true",
		"textarea_params"=>" wrap=\"VIRTUAL\" cols=\"60\" rows=\"5\""
	),
	array("name"=>"make",		"label"=>"Make",		"type"=>"text",			"table"=>"true", "dump"=>"true"),
	array("name"=>"model",		"label"=>"Model",		"type"=>"text",			"table"=>"true", "dump"=>"true"),
	array("name"=>"serial",		"label"=>"TLD SN",		"type"=>"text",			"table"=>"true", "dump"=>"true",
		"width"=>"20"),
	array("name"=>"man_serial",	"label"=>"MAN SN",		"type"=>"text",			"table"=>"true", "dump"=>"true",
		"width"=>"40"),
	array("name"=>"type",		"label"=>"Type",		"type"=>"select",		"table"=>"true", "dump"=>"true",
		"source"=>"lists", "list_name"=>"list.mis.inv.type")
);

$mis_inv_comments_tpl=array(
	array("table"=>"mis_inv_comments","form_type"=>"mis_inv_comments_tpl",
	"title"=>"Comments",
	"cancel"=>"record_view&form_type=main_tpl","mode"=>"table"),
	array("name"=>"id","label"=>"Primary Key","type"=>"primary_key","table"=>"true"),
	array("name"=>"parent_id","label"=>"Foreign Key","type"=>"foreign_key","table"=>"false"),
	array("name"=>"poster","label"=>"Posted By","type"=>"auto_user","table"=>"true",
			"field"=>"id"),
	array("name"=>"date","label"=>"Date","type"=>"auto_date","table"=>"true"),
	array("name"=>"comment","label"=>"Comment","type"=>"textarea","table"=>"true",
	"textarea_params"=>" wrap=\"VIRTUAL\" cols=\"30\" rows=\"5\""),
  array("name"=>"filename",				"label"=>"Filename",				"type"=>"file_upload",	"table"=>"false",
 		"file_upload_dir"=>"mis_inv_comments","dup_exc"=>"clear")
);
$SMARTY_LOCATION = "intranet";
$DEFAULT_TEMPLATE = "intranet.tpl";

include_once("header.inc.php");
include("header.inc.php");

//Include common db functions
include("db_common2.inc.php");
if (!$id)$id=0;
//Include db functions
include("db_readonly2.inc.php");
