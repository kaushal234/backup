<?php
include("common.inc.php");

$main_tpl=array(
  array("table"=>"erp_approvers","form_type"=>"main_tpl","title"=>"Approvers Admin",
  "cancel"=>"table&form_type=main_tpl","mode"=>"record_view",
  "prn_table_menu"=>"<a href=\"/en/private/finance/finance.php\">Back to module</a>&nbsp;|&nbsp;",
//  "prn_record_menu"=>"<a href=\"/en/private/manufacturing/pur/dev.php?m[0]=vendors&m[1]=view&id={id}\">Vendor Detail</a>&nbsp;|&nbsp;",
//  "child_tables"=>array("vendors_suno"),
  "default_sort"=>"id"),
  array("name"=>"id",				"label"=>"Primary Key",			"type"=>"primary_key",	"table"=>"true"),
  array("name"=>"erp",				"label"=>"ERP",				 	"type"=>"lookup",		"table"=>"true",	"dump"=>"true",
  		"select_query"=>"SELECT erp, CONCAT(location,' (',erp,')') as dsc FROM locations WHERE erp<>0 ORDER BY dsc",
		"select_field_1"=>"erp","select_field_2"=>"dsc",
        "select_query_view"=>"select CONCAT(location,' (',erp,')') as dsc FROM locations WHERE erp=","select_field_view"=>"dsc"
	),
  array("name"=>"type",				"label"=>"Type",				"type"=>"select",			"table"=>"true",	"dump"=>"true",
  		"select_list"=>array(	"vendor"
/*								,
 								"gl",
								"dim1",
  								"dim2",
  								"dim3",
  								"dim4",
  								"dim5"
 */
							)
  ),
  array("name"=>"ref_num",			"label"=>"Ref number",		  	"type"=>"text",			"table"=>"true",	"dump"=>"true"),
  array("name"=>"approver_id",		"label"=>"Approver",			"type"=>"select",		"table"=>"true",	"dump"=>"true",
  		"select_list"=>tldDirectory::getUserList("smartyOptions"), "source"=>"smartyOptions")
);

$user = new tldUser($GLOBALS["PHP_AUTH_USER"]);

if(!$user->isInGroup(array("role_AP","role_CFO","gg_ADMIN"))){
	echo "You do not have permission to access this page";
	exit;
}

$SMARTY_LOCATION = "intranet";
$DEFAULT_TEMPLATE = "intranet.tpl";

include("header.inc.php");
include("db_common2.inc.php");
if (!$id)$id=0;
//Include db functions
include("db_admin2.inc.php");
?>