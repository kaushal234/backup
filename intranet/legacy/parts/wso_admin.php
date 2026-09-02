<?php
include_once("common.inc.php");

$main_tpl = [
    ["table" => "erp_wso", "form_type" => "main_tpl", "title" => "Web SO",
        "child_tables" => ["erp_wso_lines"],
        "cancel" => "table&form_type=", "mode" => "record_view",
        "default_sort" => "id"],
//start of table definitions
    ["name" => "id", "label" => "ID&nbsp;####", "type" => "primary_key", "table" => "true", "dump" => "true"],
    ["name" => "parent_id", "label" => "Foreign Key", "type" => "foreign_key", "table" => "false"],
    ["name" => "status", "label" => "Status", "type" => "locked_field", "table" => "true", "dump" => "true"],
    ["name" => "t_odat", "label" => "Date<br>(yyyy-mm-dd)", "type" => "auto_date", "table" => "true", "dump" => "true"],
    ["name" => "erp", "label" => "ERP", "type" => "lookup", "table" => "true", "dump" => "true",
        "select_query" => "SELECT erp,location FROM locations WHERE erp<>'' ORDER BY location",
        "select_field_1" => "id", "select_field_2" => "location",
        "select_query_view" => "SELECT * FROM locations WHERE erp=",
        "select_field_view" => "location"],
    ["name" => "t_cdel", "label" => "Delivery Address Code", "type" => "text", "table" => "true", "dump" => "true",
        "width" => "30"],
    ["name" => "t_cuno", "label" => "Customer#", "type" => "text", "table" => "true", "dump" => "true",
        "width" => "30"],
    ["name" => "t_orno", "label" => "Order#", "type" => "text", "table" => "true", "dump" => "true",
        "width" => "30"],
];

$erp_wso_lines_tpl = [
    ["table" => "erp_wso_lines", "form_type" => "erp_wso_lines_tpl",
        "title" => "Lines",
        "cancel" => "record_view&form_type=main_tpl", "mode" => "table"],
    ["name" => "id", "label" => "Primary Key", "type" => "primary_key", "table" => "true"],
    ["name" => "parent_id", "label" => "Foreign Key", "type" => "foreign_key", "table" => "false"],
    ["name" => "ITEM", "label" => "Part Number", "type" => "text", "table" => "true",
        "width" => "20"],
    ["name" => "DESCRIPTION", "label" => "Description", "type" => "text", "table" => "true",
        "width" => "50"],
    ["name" => "t_oqua", "label" => "Qty", "type" => "text", "table" => "true",
        "width" => "4"],
    ["name" => "manid", "label" => "Manual#", "type" => "text", "table" => "true",
        "width" => "20"],
    ["name" => "docid", "label" => "Doc#", "type" => "text", "table" => "true",
        "width" => "20"],
];
$SMARTY_LOCATION = "intranet";
$DEFAULT_TEMPLATE = "intranet.tpl";

include_once("header.inc.php");
include_once("db_common2.inc.php");
$user = new tldUser($GLOBALS["PHP_AUTH_USER"]);

if ($user->isInGroup(["gg_PARTS"])) {
    include("db_admin2.inc.php");
} else {
    include("db_readonly2.inc.php");
}
