<?php

use ApiBundle\Client;
use Symfony\Component\HttpClient\Exception\ClientException;

include_once("common.inc.php");
include_once("sales_service.inc.php");
include_once("user.inc.php");
$user = new tldUser($GLOBALS["PHP_AUTH_USER"]);

$_factoryList = ["" => ""] + array_values(tldEquipment::getUsedFactoryList());
$_apcList = ["" => ""] + tldAirport::getList();
$_engTierTypes = ["" => ""] + array_values(tldList::optionsByListNameAsListItemListItem("list.engine.tiers"));
$_customers = ["" => ""] + tldCustomer::getList("smartyOptions");
$_salesOrg = ["" => ""] + tldLocation::getSalesOrgList("smartyOptionsLocationLocation");
$_salesRep = ["" => ""] + tldGroup::getUserListByMultipleGroup(["gg_SALES"], null, ["smartyOptionsTech_name" => true]);
$_combinationList = ["" => ""] + tldEquipment::getCombinationModeList();
global $kernel;
try {
    $container = $kernel->getContainer();
    $client = $container->get(Client::class);
} catch (\Exception $e) {
    $DEFAULT_ERROR[] = 'Client could not be fetched.';
}

$formattedComponents = [];
try {
    $components = $client->get('equipment_serial_components', ['query' => ['order' => ['name' => 'ASC']]]);
    foreach ($components['hydra:member'] as $key => $component) {
        $formattedComponents[$key] = $component['name'];
    }
} catch (ClientException $exception) {
    $DEFAULT_ERROR[] = 'Components could not be fetched.';
}


// Locked field rules ------------------------>

// -- list of field (default conf)
$warranty_length_FieldType = "locked_field";
$date_warranty_end_FieldType = "locked_field";
$warranty_conditions_FieldType = "locked_field";
$serviceContractFieldType = "locked_field";
// -- Apply rules
if ($user->isInGroup(["role_PSM", "role_PSE", "role_PSA", "gg_ADMIN"])) {
    $warranty_length_FieldType = "select";
    $date_warranty_end_FieldType = "date";
    $warranty_conditions_FieldType = "textarea";
}
if ($user->isInGroup(["role_PSM", "role_PSE", "role_PSA", "gg_ADMIN", "role_CSM"])) {
    $serviceContractFieldType = "select";
}

// ADMIN tables ------------------------------>

$main_tpl = [
//table parameters
    ["table" => "service", "form_type" => "main_tpl", "title" => "Equipment Records",
        "cancel" => "table&form_type=", "mode" => "record_view",
        "child_tables" => ["service_serials", "service_files"],
        "prn_table_menu" => "<a href=\"/en/private/product_support/index.ps.php?m[0]=equipment\">Back to module</a>&nbsp;|&nbsp;",
        "prn_record_menu" => "<b><a href=\"/en/private/product_support/index.ps.php?m[0]=equipment&m[1]=view&id={id}\">Back to module</a></b>&nbsp;|&nbsp;",
        "default_sort" => "date_shipped"],
//EQUIPMENT RECORD SECTION
    ["name" => "id", "label" => "ER#", "type" => "primary_key", "table" => "true", "dump" => "true", "dup_exc" => "clear"],
    ["name" => "sn", "label" => "ER SN#", "type" => "text", "table" => "true", "dump" => "true", "dup_exc" => "PLEASE CHANGE"],
    ["name" => "cust_asset_num", "label" => "Customer Asset#", "type" => "text", "table" => "false", "dump" => "true", "dup_exc" => "PLEASE CHANGE"],
    ["name" => "esrid", "label" => "ESR ID#", "type" => "text", "table" => "false", "dump" => "true", "dup_exc" => "clear"],
    ["name" => "entered_by", "label" => "Entered By", "type" => "auto_user", "table" => "false", "dump" => "true"],
    ["name" => "date_entered", "label" => "Date Entered (YYYY-MM-DD)", "type" => "auto_date", "table" => "false", "dump" => "true"],
    ["name" => "odp_note", "label" => "ODP Comment", "type" => "textarea", "table" => "false",
        "textarea_params" => " wrap=\"VIRTUAL\" cols=\"40\" rows=\"7\""],
    ["name" => "sales_org", "label" => "Sales Organisation", "type" => "select", "table" => "true", "dump" => "true",
        "source" => "smartyOptions", "select_list" => $_salesOrg],
    ["name" => "sales_rep", "label" => "Sales Rep", "type" => "select", "table" => "false", "dump" => "true",
        "source" => "smartyOptions", "select_list" => $_salesRep],
    ["name" => "cust_equipment_type", "label" => "Customer Equipment Type", "type" => "text", "table" => "false", "dump" => "true", "dup_exc" => "PLEASE CHANGE"],
//CUSTOMER DETAILS SECTION
    ["name" => "bypass", "label" => "<b><i>CUSTOMER DETAILS</i></b>", "type" => "title", "table" => "false"],
    ["name" => "buyer_customer_id", "label" => "Customer Name (BUYER)", "type" => "lookup", "table" => "true", "dump" => "true",
        "select_query" => "SELECT id,customer_name FROM customers WHERE hidden<>'1' ORDER BY customer_name",
        "select_field_1" => "id", "select_field_2" => "customer_name",
        "select_query_view" => "SELECT customer_name FROM customers WHERE id=",
        "select_field_view" => "customer_name"],
    ["name" => "customer_id", "label" => "Customer Name (END USER)", "type" => "lookup", "table" => "true", "dump" => "true",
        "select_query" => "SELECT id,customer_name FROM customers WHERE hidden<>'1' ORDER BY customer_name",
        "select_field_1" => "id", "select_field_2" => "customer_name",
        "select_query_view" => "SELECT customer_name FROM customers WHERE id=",
        "select_field_view" => "customer_name"],
    ["name" => "maintainer_customer_id", "label" => "Customer Name (MAINTAINER)", "type" => "lookup", "table" => "true", "dump" => "true",
        "select_query" => "SELECT id,customer_name FROM customers WHERE hidden<>'1' AND customer_name NOT LIKE '**%**' ORDER BY customer_name",
        "select_field_1" => "id", "select_field_2" => "customer_name",
        "select_query_view" => "SELECT customer_name FROM customers WHERE id=",
        "select_field_view" => "customer_name"],
    ["name" => "customer_name", "label" => "Customer Name", "type" => "select_db", "table" => "true", "dump" => "true",
        "select_query" => "SELECT customer_name FROM customers WHERE hidden<>'1' ORDER BY customer_name",
        "select_field_1" => "customer_name", "select_field_2" => "customer_name"],
    ["name" => "customer_name_prev", "label" => "Customer Name Previous", "type" => "text", "table" => "false", "dump" => "true"],
    ["name" => "customer_contact", "label" => "Customer Contact", "type" => "text", "table" => "false", "dump" => "true"],
    ["name" => "customer_ref", "label" => "Customer Ref/PO Number", "type" => "text", "table" => "false", "dump" => "true"],
    ["name" => "agent_name", "label" => "Agent name", "type" => "text", "table" => "false", "dump" => "true"],
    ["name" => "airport_code", "label" => "Airport Code", "type" => "select", "table" => "true", "dump" => "true",
        "source" => "smartyOptions", "select_list" => $_apcList],
    ["name" => "location_short", "label" => "Location (Short)", "type" => "text", "table" => "false", "dump" => "true"],
    ["name" => "delivery_location", "label" => "Delivery Location<br>(3 letter Airportcode, address)", "type" => "textarea", "table" => "false", "dump" => "true",
        "textarea_params" => " wrap=\"VIRTUAL\" cols=\"40\" rows=\"7\""],
    ["name" => "del_ctry", "label" => "Delivered country", "type" => "select", "table" => "false",
        "select_list" => ["To Be Defined" => "To Be Defined"] + tldCountry::optionsAsNameName(), "source" => "smartyOptions",
    ],
    ["name" => "date_arrived", "label" => "Arrival Date (YYYY-MM-DD)", "type" => "date_type", "table" => "false", "dump" => "true"],
//COLUMN BREAK
    ["name" => "bypass", "label" => "BREAK", "type" => "col_break", "table" => "false"],
//EQUIPMENT DETAILS SECTION
    ["name" => "bypass", "label" => "<b><i>EQUIPMENT DETAILS</i></b>", "type" => "title", "table" => "false"],
    ["name" => "type", "label" => "Equipment Type", "type" => "select_db", "table" => "false", "dump" => "true",
        "select_query" => "SELECT en FROM products_categories ORDER BY en",
        "select_field_1" => "en", "select_field_2" => "en"],
    ["name" => "model", "label" => "Equipment Model", "type" => "select_db", "table" => "true", "dump" => "true",
        "select_query" => "SELECT model FROM models WHERE hide=0 ORDER BY model",
        "select_field_1" => "model", "select_field_2" => "model"],
    ["name" => "er_batch_qty", "label" => "Batch Qty", "type" => "text", "table" => "true", "dump" => "true"],
    ["name" => "options_desc", "label" => "Description of Options", "type" => "textarea", "table" => "false",
        "textarea_params" => " wrap=\"VIRTUAL\" cols=\"40\" rows=\"7\""],
    ["name" => "man_location", "label" => "Manufacturer Location", "type" => "select", "table" => "true", "dump" => "true",
        "select_list" => $_factoryList,
    ],
    ["name" => "hours", "label" => "Hour meter", "type" => "locked_field", "table" => "false", "dump" => "true"],
    ["name" => "eng_tier", "label" => "Emission Rating", "type" => "select", "table" => "true", "dump" => "true",
        "select_list" => $_engTierTypes,
    ],
    ["name" => "diml", "label" => "Length (mm)", "type" => "text", "table" => "false", "dump" => "true"],
    ["name" => "dimw", "label" => "Width (mm)", "type" => "text", "table" => "false", "dump" => "true"],
    ["name" => "dimh", "label" => "Height (mm)", "type" => "text", "table" => "false", "dump" => "true"],
    ["name" => "dimk", "label" => "Weight (KG)", "type" => "text", "table" => "false", "dump" => "true"],
//WARRANTY DETAILS SECTION
    ["name" => "bypass", "label" => "<b><i>WARRANTY DETAILS</i></b>", "type" => "title", "table" => "false"],
    ["name" => "warranty_length", "label" => "Warranty Length (Months)", "type" => $warranty_length_FieldType, "table" => "false", "dump" => "true", "select_list" => array_values(tldEquipment::getAllowedWarrantyLength())],
    ['name' => 'warranty_length_hours', 'label' => 'Warranty Length (Hours)', 'type' => 'text', 'table' => 'false', 'dump' => 'true'],
    ["name" => "date_warranty_end", "label" => "Warranty End Date (YYYY-MM-DD)", "type" => $date_warranty_end_FieldType, "table" => "false", "dump" => "true", "dup_exc" => "clear"],
    ["name" => "warranty_conditions", "label" => "Special Warranty Conditions", "type" => $warranty_conditions_FieldType, "table" => "false", "dump" => "true", "textarea_params" => " wrap=\"VIRTUAL\" cols=\"40\" rows=\"7\""],
//FOR MANUFACTURING SECTION
    ["name" => "bypass", "label" => "<b><i>MANUFACTURING SECTION</i></b>", "type" => "title", "table" => "false"],
    ["name" => "mfg_comments", "label" => "MFG Comments", "type" => "textarea", "table" => "false", "dump" => "true",
        "textarea_params" => " wrap=\"VIRTUAL\" cols=\"40\" rows=\"7\""],
    ["name" => "t_pdno", "label" => "Main Work Order#", "type" => "text", "table" => "false", "dump" => "true"],
    ["name" => "t_prno", "label" => "MFG Project Number", "type" => "text", "table" => "false", "dump" => "true"],
    ["name" => "sls_orno", "label" => "MFG SO#", "type" => "text", "table" => "false", "dump" => "true"],
//NEXT
    ["name" => "dt_commissioned", "label" => "Date, Commissioning (YYYY-MM-DD)", "type" => "date", "table" => "true", "dump" => "true", "dup_exc" => "clear"],
    ["name" => "maintenance_contract_erp", "label" => "Service contract ERP", "type" => "$serviceContractFieldType", "table" => "false", "dump" => "true"],
    ["name" => "maintenance_contract_ref", "label" => "FMS contract", "type" => "$serviceContractFieldType", "table" => "false", "dump" => "true", "source" => "smartyOptions", "select_list" => ['' => ''] + tldEquipment::getFmsContractType()],
];

if ($user->isInGroup(["gg_ACCT", "role_SA", "gg_ADMIN"])) {
    $main_tpl[] = ["name" => "tranid_sso", "label" => "SSO Transaction#", "type" => "text", "table" => "false", "dump" => "true", "dup_exc" => "clear"];
    $main_tpl[] = ["name" => "tranid_erp", "label" => "ERP Transaction#", "type" => "text", "table" => "false", "dump" => "true", "dup_exc" => "clear"];
    $main_tpl[] = ["name" => "rrd_sso", "label" => "Revenue Recognition Date, SSO (YYYY-MM-DD)", "type" => "date", "table" => "false", "dump" => "true", "dup_exc" => "clear"];
    $main_tpl[] = ["name" => "rrd_erp", "label" => "Revenue Recognition Date, Factory (YYYY-MM-DD)", "type" => "date", "table" => "false", "dump" => "true", "dup_exc" => "clear"];
} else {
    $main_tpl[] = ["name" => "tranid_sso", "label" => "SSO Transaction#", "type" => "locked_field", "table" => "false", "dump" => "true", "dup_exc" => "clear"];
    $main_tpl[] = ["name" => "tranid_erp", "label" => "ERP Transaction#", "type" => "locked_field", "table" => "false", "dump" => "true", "dup_exc" => "clear"];
    $main_tpl[] = ["name" => "rrd_sso", "label" => "Revenue Recognition Date, SSO (YYYY-MM-DD)", "type" => "locked_date", "table" => "false", "dump" => "true", "dup_exc" => "clear"];
    $main_tpl[] = ["name" => "rrd_erp", "label" => "Revenue Recognition Date, Factory (YYYY-MM-DD)", "type" => "locked_date", "table" => "false", "dump" => "true", "dup_exc" => "clear"];
}

if ($user->isInGroup(["gg_ADMIN"])) {
    $main_tpl[] = ["name" => "date_shipped", "label" => "Date, Actual Shipped (YYYY-MM-DD)", "type" => "date", "table" => "true", "dump" => "true", "dup_exc" => "clear"];
    $main_tpl[] = ["name" => "dgt_rev", "label" => "Date, Estimated GT (YYYY-MM-DD)", "type" => "date", "table" => "false", "dump" => "true", "dup_exc" => "clear"];
    $main_tpl[] = ["name" => "dgt_act", "label" => "Date, Actual GT  (YYYY-MM-DD)", "type" => "date", "table" => "true", "dump" => "true", "dup_exc" => "clear"];
    $main_tpl[] = ["name" => "dgt_com", "label" => "Date, First GT (YYYY-MM-DD)", "type" => "date", "table" => "false", "dump" => "true", "dup_exc" => "clear"];
    $main_tpl[] = ["name" => "dyt", "label" => "Date, Yellow Tag  (YYYY-MM-DD)", "type" => "date", "table" => "true", "dump" => "true", "dup_exc" => "clear"];
    $main_tpl[] = ["name" => "parent_id", "label" => "Combined ER#", "type" => "text", "table" => "false", "dump" => "true"];
    $main_tpl[] = ["name" => "comb_mod", "label" => "Combination Mode", "type" => "select", "select_list" => $_combinationList, "source" => "smartyOptions", "table" => "false", "dump" => "true"];
} else {
    $main_tpl[] = ["name" => "date_shipped", "label" => "Date, Actual Shipped (YYYY-MM-DD)", "type" => "locked_date", "table" => "true", "dump" => "true", "dup_exc" => "clear"];
    $main_tpl[] = ["name" => "dgt_rev", "label" => "Date, Estimated GT (YYYY-MM-DD)", "type" => "locked_date", "table" => "false", "dump" => "true", "dup_exc" => "clear"];
    $main_tpl[] = ["name" => "dgt_act", "label" => "Date, Actual GT (YYYY-MM-DD)", "type" => "locked_date", "table" => "true", "dump" => "true", "dup_exc" => "clear"];
    $main_tpl[] = ["name" => "dgt_com", "label" => "Date, First GT (YYYY-MM-DD)", "type" => "locked_date", "table" => "false", "dump" => "true", "dup_exc" => "clear"];
    $main_tpl[] = ["name" => "dyt", "label" => "Date, Yellow Tag  (YYYY-MM-DD)", "type" => "locked_date", "table" => "true", "dump" => "true", "dup_exc" => "clear"];
    $main_tpl[] = ["name" => "parent_id", "label" => "Combined ER#", "type" => "locked", "table" => "false", "dump" => "true"];
    $main_tpl[] = ["name" => "comb_mod", "label" => "Combination Mode", "type" => "locked", "table" => "false", "dump" => "true"];
}

$main_tpl[] = ['name' => 'tld_link', 'label' => 'Link active', 'type' => 'select', 'table' => 'true', 'dump' => 'true', 'select_list' => [0 => 'N', 1 => 'Y'], 'source' => 'smartyOptions'];
$main_tpl[] = ['name' => 'sim_status', 'label' => 'SIM status', 'type' => 'select', 'table' => 'true', 'dump' => 'true', 'select_list' => tldEquipment::getSimStatusList(), 'source' => 'smartyOptions'];
$main_tpl[] = ['name' => 'fms_contract_length', 'label' => 'FMS contract length in months (0 to 240)', 'type' => 'text', 'table' => 'false', 'dump' => 'true'];
$main_tpl[] = ['name' => 'fms_end_use_date', 'label' => 'FMS end use date', 'type' => 'date', 'table' => 'false', 'dump' => 'true', 'dup_exc' => 'clear'];
$main_tpl[] = ['name' => 'light', 'label' => 'ER light ?', 'type' => 'select', 'table' => 'true', 'dump' => 'true', 'select_list' => [0 => 'N', 1 => 'Y'], 'source' => 'smartyOptions'];

$service_serials_tpl = [
    ["table" => "service_serials", "form_type" => "service_serials_tpl",
        "title" => "Serial Numbers",
        "cancel" => "record_view&form_type=main_tpl",
        "mode" => "table"],
    ["name" => "id", "label" => "Primary Key", "type" => "primary_key", "table" => "false"],
    ["name" => "parent_id", "label" => "Foreign Key", "type" => "foreign_key", "table" => "false"],

    ["name" => "component", "label" => "Component Type", "type" => "select", "table" => "true", "dump" => "true",
        "select_list" => $formattedComponents,
    ],
    ["name" => "brand", "label" => "Brand Name", "type" => "text", "table" => "true"],
    ["name" => "model", "label" => "Model", "type" => "text", "table" => "true"],
    ["name" => "serial", "label" => "Number", "type" => "text", "table" => "true"],
];

$service_files_tpl = [
    ["table" => "service_files", "form_type" => "service_files_tpl",
        "title" => "Customer Files",
        "cancel" => "record_view&form_type=main_tpl", "mode" => "table",
    ],
    ["name" => "id", "label" => "Primary Key", "type" => "primary_key", "table" => "false"],
    ["name" => "parent_id", "label" => "Foreign Key", "type" => "foreign_key", "table" => "false"],
    ["name" => "date", "label" => "Date (YYYY-MM-DD)", "type" => "auto_date", "table" => "true"],
    ["name" => "description", "label" => "File description", "type" => "text", "table" => "true",
        "width" => "25",
    ],
    ["name" => "filename", "label" => "Filename", "type" => "file_upload", "table" => "true",
        "file_upload_dir" => "service_files",
    ],
];

//include("header.inc.php");
//include("db_common.inc.php");

switch ($mode) {
    case 'del':
        if (!$user->isInGroup(["role_PSM", "role_PSE", "role_PSA", "gg_ADMIN", "gg_ENG", "gg_QUALITY", "gg_SUPPORT"])) {
            $sess_error_message = "Your permissions do not allow this.";
            $mode = "record_view";
            $form_type = "main_tpl";
        }
        break;
    case 'fromWarranty':
        $MY_SESS["service"]["adv_search_query"] = <<< EOF
        SELECT DISTINCT service.id,service.* FROM service,warranty 
        WHERE warranty.id=$id AND service.sn=warranty.serial_number
EOF;
        $mode = "table";
        break;
    case 'form_add':
        switch ($form_type) {
            case 'main_tpl':
                if (!$user->isInGroup(["role_PSM", "role_PSE", "role_PSA", "gg_SUPPORT"])) {
                    $sess_error_message = "ERROR: Only SUPPORT can create ERs";
                    $mode = null;
                    $form_type = "main_tpl";
                }
                break;
            case 'service_files_tpl':
                if ($user->isInGroup(["role_PSM", "role_PSE", "role_PSA", "gg_ADMIN", "gg_SUPPORT"])) {
                    $sess_error_message = "WARNING ! THIS FILE WILL BE VIEWABLE BY THE CUSTOMER, MAKE SURE YOUR FILE HAVE NO RESTRICTED ACCESS...<br/>";
                } else {
                    $sess_error_message = "Your permissions do not allow this.";
                    $mode = "record_view";
                    $form_type = "main_tpl";
                }
                break;
        }
        break;
    case 'form_save':
        switch ($form_type) {
            case 'main_tpl':
                if ($_POST["er_batch_qty"][0] < 1) {
                    $sess_error_message = "ERROR: Batch quantity must be at least 1, please try again.";
                    $mode = "form_add";
                    $form_type = "main_tpl";
                }
                break;
        }
        break;
    case 'update':
        switch ($form_type) {
            case 'main_tpl':
                if ($_POST["er_batch_qty"][0] < 1) {
                    $sess_error_message = "ERROR: Batch quantity must be at least 1, please try again.";
                }
                if (!empty($sess_error_message)) {
                    $mode = "form_edit";
                    $form_type = "main_tpl";
                    $id = $_POST["id"][0];
                }
                break;
        }
        break;
}

$SMARTY_LOCATION = "intranet";
$DEFAULT_TEMPLATE = "intranet.tpl";

include("header.inc.php");
include("db_common2.inc.php");
if (!$id) {
    $id = 0;
}
//Include db functions
if ($user->isInGroup(["gg_ADMIN", "gg_MIS"])) {
    include("db_admin2.inc.php");
} else {
    include("db_readonly2.inc.php");
}
