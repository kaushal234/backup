<?php
include_once("common.inc.php");
$user = new tldUser($GLOBALS["PHP_AUTH_USER"]);

if (!$user->isInGroup(["gg_ADMIN", "gg_MIS"])) {
    echo "You do not have permissions for this page..";
    exit;
}

$main_tpl = [
    [
        "table" => "erp_archive", "form_type" => "main_tpl",
        "title" => "BAAN Document Archive", "cancel" => "table&form_type=",
        "mode" => "record_view",
        "prn_table_menu" => "<a href=\"/en/private/finance/finance.php?m[0]=archive\">Back to Module</a>&nbsp;|&nbsp;",
        "default_sort" => "id",
    ],
    ["name" => "id", "label" => "Primary Key", "type" => "primary_key", "table" => "true", "dump" => "true"],
    ["name" => "dt", "label" => "Date Entered", "type" => "auto_date", "table" => "true"],
    ["name" => "erp", "label" => "ERP", "type" => "text", "table" => "true", "dump" => "true"],
    ["name" => "doc_type", "label" => "Doc type", "type" => "text", "table" => "true", "dump" => "true"],
    ["name" => "dest", "label" => "Dest", "type" => "text", "table" => "true", "dump" => "true"],
    ["name" => "num", "label" => "Doc Num", "type" => "text", "table" => "true", "dump" => "true"],
    ["name" => "dat", "label" => "Document date", "type" => "date", "table" => "true"],
    ["name" => "filepath", "label" => "File path", "type" => "text", "table" => "true", "dump" => "true"],
    ["name" => "xml", "label" => "XML", "type" => "textarea", "table" => "false", "dump" => "true",
        "textarea_params" => " wrap=\"VIRTUAL\" cols=\"80\" rows=\"10\""],
    ["name" => "response", "label" => "Response", "type" => "text", "table" => "true", "dump" => "true"],
    ["name" => "status", "label" => "Status", "type" => "text", "table" => "true", "dump" => "true"],
    ["name" => "flow_type", "label" => "Flow Type", "type" => "text", "table" => "true", "dump" => "true"],
];

$SMARTY_LOCATION = "intranet";
$DEFAULT_TEMPLATE = "intranet.tpl";

include("header.inc.php");

//Include common db functions
include("db_common2.inc.php");
if (!$id) $id = 0;
//Include db functions
include("db_admin2.inc.php");
