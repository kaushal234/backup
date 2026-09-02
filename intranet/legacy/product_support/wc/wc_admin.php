<?php
include_once('common.inc.php');
include_once('product_support.inc.php');
$user = new tldUser($GLOBALS["PHP_AUTH_USER"]);

$_factoryList = array_values(tldLocation::getFactoryList('smartyOptions'));
$_salesOrgList = array_values(tldLocation::getSalesOrgList("smartyOptions"));
$_filteringFlagList = tldWC::getFilteringFlagList();

// Default fields configuration
$statusField = array("name"=>"warranty_status",	"label"=>"Status",	"type"=>"locked_field",	"table"=>"true","dump"=>"true", "dup_exc"=>"PENDING");
$fiteringFlagField = array("name"=>"filtering_flag",	"label"=>"Filtering flag",	"type"=>"locked_field",	"table"=>"false","dump"=>"false", "dup_exc"=>"TO BE FILTERED");
$claimDateField = array("name"=>"claim_date", "label"=>"Claim Date", "type"=>"locked_field", "table"=>"true", "dump"=>"true");
$snField = array("name"=>"serial_number", "label"=>"Equipment S/N", "type"=>"locked_field", "table"=>"true", "dump"=>"true");
// Superuser fields configuration
if($user->isInGroup(array("superuser"))){
    $statusField = array("name"=>"warranty_status",	"label"=>"Status (editable)", "type"=>"select",	"table"=>"true","dump"=>"true",
    	"select_list"=>array_merge(tldWC::getOpenStatusList(),tldWC::getClosedStatusList())
    );
    $fiteringFlagField = array("name"=>"filtering_flag",	"label"=>"Filtering flag",	"type"=>"select",	"table"=>"false","dump"=>"false", "dup_exc"=>"TO BE FILTERED",
        "select_list"=>$_filteringFlagList
    );
    $snField = array("name"=>"serial_number", "label"=>"Equipment S/N", "type"=>"text", "table"=>"true", "dump"=>"true");
}

if ($user->isInGroup(['superuser', 'role_PSM', 'role_PSA', 'role_PSE' ])) {
     $claimDateField = array("name"=>"claim_date", "label"=>"Claim Date", "type"=>"auto_date", "table"=>"true", "dump"=>"true");
}

$main_tpl=array(
  array("table"=>"warranty","form_type"=>"main_tpl","title"=>"Warranty Claims",
  "cancel"=>"table&form_type=","mode"=>"record_view",
  "child_tables"=>array("warranty_parts","warranty_files"),
  "prn_table_menu"=>"<a href=\"/en/private/product_support/index.ps.php?m[0]=wc\">Back to module</a>&nbsp;|&nbsp;",
  "prn_record_menu"=>"<b><a href=\"/en/private/product_support/index.ps.php?m[0]=wc&m[1]=view&id={id}\">Back to module</a>&nbsp;|&nbsp;".
    "<a href=\"/en/private/product_support/equipment/equipment_admin.php?mode=fromWarranty&id={id}\">Goto Equipment Record</a></b>&nbsp;|&nbsp;",
  "email_title"=>"Record ID",
  "default_sort"=>"claim_date"),
//WARRANTY DETAILS
  array("name"=>"bypass", 	 		"label"=>"<b><i>WARRANY DETAILS</i></b>",		"type"=>"title",		"table"=>"false"),
  array("name"=>"id",				"label"=>"WC&nbsp;####", 						"type"=>"primary_key",	"table"=>"true","dump"=>"true","prefix"=>"WC-"),
  array("name"=>"parent_id",		"label"=>"Foreign Key",							"type"=>"foreign_key",	"table"=>"false"),
  $claimDateField,
  $statusField,
  array("name"=>"entered_by",		"label"=>"Entered By",							"type"=>"auto_user",	"table"=>"false","dump"=>"true",
	"field"=>"email"),
//CUSTOMER AND EQUIPMENT SECTION
  array("name"=>"bypass",			"label"=>"<b><i>CUSTOMER AND EQUIP DETAILS</i></b>",	"type"=>"title",		"table"=>"false"),
  array("name"=>"claimant_details",	"label"=>"Claimant Details (Name, add., tel, fax., etc.)","type"=>"textarea",	"table"=>"false","dump"=>"true",
  		"textarea_params"=>" wrap=\"VIRTUAL\" cols=\"50\" rows=\"7\""),
  array("name"=>"warranty_details",	"label"=>"Warranty Details",							"type"=>"locked_field",	"table"=>"false","dump"=>"true"),
  array("name"=>"customer_name","label"=>"Customer Name",									"type"=>"select_db",	"table"=>"true","dump"=>"true",
  		"select_query"=>"SELECT * FROM customers WHERE hidden<>'1' ORDER BY customer_name",
		"select_field_1"=>"customer_name","select_field_2"=>"customer_name"),
  $snField,
  array("name"=>"type",				"label"=>"Equipment Type",								"type"=>"select_db",	"table"=>"false","dump"=>"true",
  		"select_query"=>"SELECT en FROM products_categories WHERE parent_id='0' ORDER BY en",
		"select_field_1"=>"en","select_field_2"=>"en"),
  array("name"=>"model",			"label"=>"Model",										"type"=>"select_db",	"table"=>"true","dump"=>"true",
  		"select_query"=>"SELECT model FROM models WHERE hide=0 ORDER BY model",
		"select_field_1"=>"model","select_field_2"=>"model"),
  array("name"=>"equipment_location","label"=>"Equipment Location",							"type"=>"textarea",		"table"=>"false","dump"=>"true",
  		"textarea_params"=>" wrap=\"VIRTUAL\" cols=\"50\" rows=\"7\""),
  array("name"=>"man_location",		"label"=>"Manufacturer Location",						"type"=>"select",		"table"=>"false","dump"=>"true",
  		"select_list"=>$_factoryList),
  array("name"=>"sales_org",		"label"=>"Sales Organisation",							"type"=>"select",		"table"=>"false","dump"=>"true",
  		"select_list"=>$_salesOrgList),
  array("name"=>"er_operation_status", "label"=>"ER operation status",                     "type"=>"locked_field", "table"=>"true", "dump"=>"true"),
  array("name"=>"hours",			"label"=>"Hour meter (in Hrs)",							"type"=>"text",	"table"=>"false","dump"=>"true","rule"=>"numeric"),
  array("name"=>"part_failing",		"label"=>"Critical PN failing",							"type"=>"text",			"table"=>"true","dump"=>"true"),
//COLUMN BREAK
  array("name"=>"bypass",			"label"=>"BREAK",										"type"=>"col_break",	"table"=>"false"),
//PRODUCT MANAGER SECTION
  array("name"=>"bypass",			"label"=>"<b><i>FOR PRODUCT MANAGER</i></b>",			"type"=>"title",		"table"=>"false"),
  array("name"=>"prod_man_accept_user",		"label"=>"Signed by",							"type"=>"locked_field",	"table"=>"false","dump"=>"true", "dup_exc"=>""),
  array("name"=>"prod_man_accept_date",		"label"=>"Signed Date",							"type"=>"locked_date",	"table"=>"false","dump"=>"true", "dup_exc"=>""),
  array("name"=>"prod_man_comments",		"label"=>"Comments",							"type"=>"textarea",		"table"=>"false","dump"=>"true", "dup_exc"=>"",
  		"textarea_params"=>" wrap=\"VIRTUAL\" cols=\"50\" rows=\"7\""),
//SERVICE PERSONNEL SECTION
  array("name"=>"bypass",					"label"=>"<b><i>FOR SERVICE PERSONNEL</i></b>",	"type"=>"title",		"table"=>"false"),
  array("name"=>"problem_desc",				"label"=>"Problem Description",					"type"=>"textarea",		"table"=>"false","dump"=>"true",
  		"textarea_params"=>" wrap=\"VIRTUAL\" cols=\"50\" rows=\"7\""),
  array("name"=>"extranet_prob_desc",		"label"=>"Extranet Problem Description",		"type"=>"textarea",		"table"=>"false","dump"=>"true",
  		"textarea_params"=>" wrap=\"VIRTUAL\" cols=\"50\" rows=\"7\""),
  array("name"=>"est_man_hours",			"label"=>"Estimate Labour (Hrs)",				"type"=>"text",			"table"=>"false","dump"=>"true"),
  array("name"=>"technician",	"label"=>"Technician",				"type"=>"select_db",	"table"=>"false","dump"=>"true",
  		"select_query"=>"SELECT CONCAT(t1.lastname,', ',t1.firstname, ' (', t1.email, ')') AS fullname FROM people AS t1, people_groups AS t2 WHERE t1.id=t2.parent_id AND t2.group_name='gg_SERVICE' ORDER BY fullname",
  		"select_field_1"=>"fullname","select_field_2"=>"fullname"),
  array("name"=>"technician_cost_te",		"label"=>"T&amp;E Cost",	"type"=>"text",			"table"=>"false","dump"=>"true","rule"=>"decimal"),
  array("name"=>"technician_cost_labour",	"label"=>"Labour Cost",		"type"=>"text",			"table"=>"false","dump"=>"true","rule"=>"decimal"),
  array("name"=>"parts_cost",				"label"=>"Parts Cost",		"type"=>"text",			"table"=>"false","dump"=>"true","rule"=>"decimal"),
  array("name"=>"note_cost",				"label"=>"Cost Note",					"type"=>"textarea",	"table"=>"false","dump"=>"true",
  		"textarea_params"=>" wrap=\"VIRTUAL\" cols=\"50\" rows=\"7\""),
  array("name"=>"intervention",				"label"=>"TLD personnel required ?",	"type"=>"select",	"table"=>"false","dump"=>"true",
  		"select_list"=>array("NO","YES")
  ),
  array("name"=>"service_date_delivery",	"label"=>"Work Date (yyyy-mm-dd)",				"type"=>"date_type",	"table"=>"true","dump"=>"true"),
  array("name"=>"service_comments",			"label"=>"Comments",							"type"=>"textarea",		"table"=>"false","dump"=>"true",
  		"textarea_params"=>" wrap=\"VIRTUAL\" cols=\"50\" rows=\"7\""),
  array("name"=>"service_ship_inst",		"label"=>"Shipping Instructions (Address, attention, tel, etc)","type"=>"textarea","table"=>"false","dump"=>"true",
  		"textarea_params"=>" wrap=\"VIRTUAL\" cols=\"50\" rows=\"7\""),
//PARTS DEPT SECTION
  array("name"=>"bypass",					"label"=>"<b><i>FOR PARTS DEPT</i></b>",		"type"=>"title",		"table"=>"false"),
  array("name"=>"parts_order_ref",			"label"=>"Parts Order Ref",						"type"=>"textarea",		"table"=>"false","dump"=>"true",
  		"textarea_params"=>" wrap=\"VIRTUAL\" cols=\"50\" rows=\"7\""),
  array("name"=>"parts_date_delivery",		"label"=>"Est. Delivery Date",					"type"=>"date_type",	"table"=>"false","dump"=>"true"),
  array("name"=>"parts_courier",			"label"=>"Courier Name & Tracking#<br>Start with ups, dhl, fed followed by #",			"type"=>"courier",		"table"=>"false","dump"=>"true",
  		"textarea_params"=>" wrap=\"VIRTUAL\" cols=\"50\" rows=\"7\""),
  array("name"=>"part_return_address",		"label"=>"Parts return address",				"type"=>"textarea",		"table"=>"false","dump"=>"true",
  		"textarea_params"=>" wrap=\"VIRTUAL\" cols=\"50\" rows=\"7\""),
// MISC
  $fiteringFlagField,
  array("name"=>"dt_filtering_flag",		"label"=>"Filtering flag date",					"type"=>"locked_field",	"table"=>"false","dump"=>"false"),
);

$warranty_parts_tpl = array(
    array("table"=>"warranty_parts","form_type"=>"warranty_parts_tpl",
        "title"=>"Warranty Parts","cancel"=>"record_view&form_type=main_tpl",
        "mode"=>"form_view"),
	array("name"=>"id",					"label"=>"Primary Key",								"type"=>"primary_key",	"table"=>"false"),
	array("name"=>"parent_id",			"label"=>"Foreign Key",								"type"=>"foreign_key",	"table"=>"false"),
	array("name"=>"supply_it",			"label"=>"Track(Y/N)",									"type"=>"select",		"table"=>"true","dump"=>"true",
		"select_list"=>array("NO","YES")),
	array("name"=>"notes",				"label"=>"Note",									"type"=>"textarea",		"table"=>"true"),
	array("name"=>"brand",				"label"=>"Part Brand",								"type"=>"text",			"table"=>"true"),
	array("name"=>"part_number",		"label"=>"Part Number",								"type"=>"text",			"table"=>"true"),
	array("name"=>"part_description",	"label"=>"Description",								"type"=>"text",			"table"=>"true"),
	array("name"=>"sn",					"label"=>"SN",										"type"=>"text",			"table"=>"true"),
	array("name"=>"failure_type",		"label"=>"Failure Type",							"type"=>"select",		"table"=>"true",
		"select_list"=>array(
            "HYDRAULIC",
			"ELECTRICAL",
			"MECHANICAL",
			"PLUMBING",
			"PNEUMATIC",
			"REFRIGERATION"
		)
    ),
	array("name"=>"failure_system",	   "label"=>"System", "type"=>"select", "table"=>"true",
		"source"=>"lists", "list_name"=>"warranty.failure_system"),
	array("name"=>"um",	               "label"=>"UM", "type"=>"text", "table"=>"true"),
	array("name"=>"quantity",          "label"=>"Qty Shipped", "type"=>"text", "table"=>"true"),
	array("name"=>"qty_in",            "label"=>"Request Qty to be shipped back", "type"=>"text", "table"=>"true"),
	array("name"=>"d_in",              "label"=>"Date Returned", "type"=>"date", "table"=>"true")
);

$warranty_files_tpl=array(
  array("table"=>"warranty_files","form_type"=>"warranty_files_tpl",
  		"title"=>"Warranty Files",
  		"cancel"=>"record_view&form_type=main_tpl","mode"=>"table"
		),
  array("name"=>"id",			"label"=>"Primary Key",				"type"=>"primary_key",	"table"=>"false"),
  array("name"=>"parent_id",	"label"=>"Foreign Key",				"type"=>"foreign_key",	"table"=>"false"),
  array("name"=>"date",			"label"=>"Date (YYYY-MM-DD)",		"type"=>"auto_date",	"table"=>"true"),
  array("name"=>"description",	"label"=>"File description",		"type"=>"text",			"table"=>"true",
  		"width"=>"25"
		),
  array("name"=>"filename",		"label"=>"Filename",				"type"=>"file_upload",	"table"=>"true",
  		"file_upload_dir"=>"warranty_files")
);

$SMARTY_LOCATION = "intranet";
$DEFAULT_TEMPLATE = "intranet.tpl";

include("header.inc.php");
include("db_common2.inc.php");
if(!$id)$id=0;

switch($mode){
    case 'form_add':
    case 'duplicate':
        switch($form_type){
		case 'main_tpl':
		    $sess_error_message = "WC can be created only from TOC module";
			$mode=NULL;
			$form_type=NULL;
			$id=$_POST["id"][0];
		break;
        }
    break;
	case 'query':
	switch($m[1]){
		case 'byStatusFactory':
			$query = <<< EOF
			SELECT * FROM warranty
			WHERE warranty.man_location='$factory'
EOF;
			if(!empty($status))
				$query .= " AND warranty_status='$status'";
			$MY_SESS["warranty"]["adv_search_query"] = $query;
			$mode="table";
		break;
	}
	break;
	case 'email':
		$id=$MY_SESS[$running]["current_id"];
		$row1 = tldUtils::getSqlRowToAssocArray("select * from warranty where id=$id");
		$subject.=" STATUS: ".$row1["warranty_status"].", RETURN PART?=>".$row1["return_parts"];
	break;
}

if(!empty($sess_error_message)){
    echo "<p style=\"color:red;align:center;\">ERROR: $sess_error_message</p>";
}

//Include db functions
if($user->isInGroup(array("warranty","role_ASM","gg_ADMIN","gg_SALES","gg_SALES_AGENTS","gg_SUPPORT","gg_SERVICE","gg_PARTS","gg_WAREHOUSE"))){
	include("db_admin2.inc.php");
}else{
	include("db_readonly2.inc.php");
}
