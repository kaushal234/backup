<?php
include_once("common.inc.php");
$user = new tldUser($_SERVER["PHP_AUTH_USER"]);

//if(!$user->isInGroup(array("gg_ADMIN","gg_ACCT","gg_SUPPORT","gg_QUALITY"))){
//	echo "ERROR: Access not permitted";
//	exit;
//}

$main_tpl = [
    ["table" => "fin_kpi", "form_type" => "main_tpl", "title" => "KPI Admin",
        "prn_table_menu" => "<a href=\"/en/private/manufacturing/index.php?m[0]=kpi\">Back to Module</a>&nbsp;|&nbsp;",
        "cancel" => "table&form_type=main_tpl", "mode" => "record_view",
        "default_sort" => "id"
    ],
    ["name" => "id", "label" => "Primary Key", "type" => "primary_key", "table" => "false"],
    ["name" => "parent_id", "label" => "Foreign Key", "type" => "foreign_key", "table" => "false"],
    ["name" => "dt", "label" => "Timestamp", "type" => "auto_time", "table" => "true", "dump" => "true"],
    ["name" => "buid", "label" => "Business Unit", "type" => "lookup", "table" => "true", "dump" => "true",
        "select_query" => "SELECT id,location FROM locations WHERE erp<>0 ORDER BY location",
        "select_field_1" => "id", "select_field_2" => "location",
        "select_query_view" => "SELECT * FROM locations WHERE id=",
        "select_field_view" => "location"],
    ["name" => "ynam", "label" => "Year", "type" => "text", "table" => "true", "dump" => "true"],
    ["name" => "mnam", "label" => "Month", "type" => "select", "table" => "true", "dump" => "true",
        "select_list" => ["01", "02", "03", "04", "05", "06", "07", "08", "09", "10", "11", "12"]
    ],
    ["name" => "bypass", "label" => "<b><i>Factory Section</i></b>", "type" => "title", "table" => "false"],
    ["name" => "erp_tot_sls", "label" => "Total Sales", "type" => "text", "table" => "true", "dump" => "true"],
    ["name" => "erp_tot_pur", "label" => "Total Purchases", "type" => "text", "table" => "true", "dump" => "true"],
    ["name" => "erp_net_inv_val", "label" => "Net Inventory Value(Total RM,WIP,FG)", "type" => "text", "table" => "true", "dump" => "true"],
    ["name" => "erp_net_wip_val", "label" => "Net WIP Value", "type" => "text", "table" => "true", "dump" => "true"],
    ["name" => "erp_net_raw_material_val", "label" => "Net Raw Material Value", "type" => "text", "table" => "true", "dump" => "true"],
    ["name" => "erp_tot_ar", "label" => "Accounts Receivables", "type" => "text", "table" => "true", "dump" => "true"],
    ["name" => "erp_tot_ap", "label" => "Trade Accounts Payables", "type" => "text", "table" => "true", "dump" => "true"],
    ["name" => "bypass", "label" => "<b><i>Cycle Count</i></b>", "type" => "title", "table" => "false"],
    ["name" => "cc_qua", "label" => "Number of items counted", "type" => "text", "table" => "true", "dump" => "true"],
    ["name" => "cc_quv", "label" => "Number of items with variance", "type" => "text", "table" => "true", "dump" => "true"],
    ["name" => "cc_pric", "label" => "Monetary value of items counted", "type" => "text", "table" => "true", "dump" => "true"],
    ["name" => "cc_priv", "label" => "Monetary value of items with variance", "type" => "text", "table" => "true", "dump" => "true"],
    ["name" => "bypass", "label" => "<b><i>Work In Progress</i></b>", "type" => "title", "table" => "false"],
    ["name" => "wip_vald", "label" => "WIP Value in days of sales", "type" => "text", "table" => "true", "dump" => "true"],
    ["name" => "inv_vald", "label" => "Inventory value in days of sales", "type" => "text", "table" => "true", "dump" => "true"],
    ["name" => "bypass", "label" => "<b><i>Factory Standard Efficiency</b></i>", "type" => "title", "table" => "false"],
    ["name" => "fse_stdh", "label" => "Standard hours allocated to units shipped", "type" => "text", "table" => "true", "dump" => "true"],
    ["name" => "fse_acth", "label" => "Actual hours spent on units shipped", "type" => "text", "table" => "true", "dump" => "true"],
    ["name" => "bypass", "label" => "<b><i>Productive Hours</i></b>", "type" => "title", "table" => "false"],
    ["name" => "phr_proh", "label" => "Productive work hours allocated to work orders", "type" => "text", "table" => "true", "dump" => "true"],
    ["name" => "phr_poth", "label" => "Potential hours of work orders", "type" => "text", "table" => "true", "dump" => "true"], ["name" => "bypass", "label" => "<b><i>Employee information</i></b>", "type" => "title", "table" => "false"],
    ["name" => "workshop_surface", "label" => "Workshop surface", "type" => "text", "table" => "true", "dump" => "true"],
    ["name" => "bypass", "label" => "<b><i>Employee information</i></b>", "type" => "title", "table" => "false"],
    ["name" => "nb_whsekeeper", "label" => "Number of warehouse keeper", "type" => "text", "table" => "true", "dump" => "true"],
    ["name" => "nb_sfe", "label" => "Number of shopfloor employee", "type" => "text", "table" => "true", "dump" => "true"],
    ["name" => "nb_wgl", "label" => "Number of warehouse group leader", "type" => "text", "table" => "true", "dump" => "true"],
    ["name" => "nb_sgl", "label" => "Number of shopfloor group leader", "type" => "text", "table" => "true", "dump" => "true"],
    ["name" => "bypass", "label" => "<b><i>Internal Customer Satisfaction (QUARTERLY ONLY!)<br>In MAR, JUN, SEPT, DEC</b></i>", "type" => "title", "table" => "false"],
    ["name" => "ics_ame", "label" => "TLD America", "type" => "text", "table" => "true", "dump" => "true"],
    ["name" => "ics_asi", "label" => "TLD Asia", "type" => "text", "table" => "true", "dump" => "true"],
    ["name" => "ics_prc", "label" => "TLD China", "type" => "text", "table" => "true", "dump" => "true"],
    ["name" => "ics_eur", "label" => "TLD Europe", "type" => "text", "table" => "true", "dump" => "true"],
    ["name" => "ics_meai", "label" => "TLD MEAI", "type" => "text", "table" => "true", "dump" => "true"],
    ["name" => "ics_laaj", "label" => "TLD LAC", "type" => "text", "table" => "true", "dump" => "true"],
    ["name" => "ics_nam", "label" => "TLD NAM", "type" => "text", "table" => "true", "dump" => "true"],
    ["name" => "ics_aero", "label" => "AERO", "type" => "text", "table" => "true", "dump" => "true"],
];

$SMARTY_LOCATION = "intranet";
$DEFAULT_TEMPLATE = "intranet.tpl";

include("header.inc.php");

//Include common db functions
include("db_common2.inc.php");

if (!$id) $id = 0;
//Include db functions 
include("db_admin2.inc.php");
?>