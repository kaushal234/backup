<?php
include_once("common.inc.php");

$main_tpl = [
    ["table" => "isr", "form_type" => "main_tpl", "title" => "Interco Shipping Record Admin",
        "cancel" => "table&form_type=", "mode" => "record_view", "child_tables" => [], "default_sort" => "id",
        "prn_table_menu" => "<a href=\"/en/private/manufacturing/pur/dev.php?m[0]=isr\">Back to Module</a>&nbsp;|&nbsp;",
        "prn_record_menu" => "<b><a href=\"/en/private/manufacturing/pur/dev.php?m[0]=isr&m[1]=view&id={id}\">Back to Module</a></b>&nbsp;|&nbsp;",
        "child_tables" => ["isr_lines"]
    ],
    ["name" => "id", "label" => "Primary Key", "type" => "primary_key", "table" => "true", "dump" => "true"],
    ["name" => "parent_id", "label" => "Foreign Key", "type" => "foreign_key"],
    ["name" => "dt", "label" => "Date Opened", "type" => "auto_date"],
    ["name" => "poster_id", "label" => "Poster", "type" => "select", "table" => "false", "dump" => "true",
        "select_list" => tldDirectory::getUserList("smartyOptions"), "source" => "smartyOptions"],
    ["name" => "status", "label" => "Status", "type" => "select", "table" => "true", "dump" => "true",
        "select_list" => ["PENDING", "IN PROGRESS", "CLOSED","CANCELLED"]],
    ["name" => "bu_from_id", "label" => "BU FROM", "type" => "lookup", "table" => "true",
        "select_query" => "SELECT id,location FROM locations WHERE erp<>0 ORDER BY location",
        "select_field_1" => "id", "select_field_2" => "location",
        "select_query_view" => "SELECT * FROM locations WHERE id=",
        "select_field_view" => "location", "options" => ["required" => true]],
    ["name" => "bu_to_id", "label" => "BU TO", "type" => "lookup", "table" => "true",
        "select_query" => "SELECT id,location FROM locations WHERE erp<>0 ORDER BY location",
        "select_field_1" => "id", "select_field_2" => "location",
        "select_query_view" => "SELECT * FROM locations WHERE id=",
        "select_field_view" => "location", "options" => ["required" => true]],
    ["name" => "cuno", "label" => "ERP customer#", "type" => "text", "table" => "false"],
    ["name" => "ttype", "label" => "Transportation type", "type" => "select", "table" => "true", "dump" => "true",
        "select_list" => ["", "AIR", "OCEAN", "TRAIN", "ROAD"]],
    ["name" => "cnum", "label" => "Container#", "type" => "text", "table" => "false"],
    ["name" => "container_type", "label" => "Container Type", "type" => "select", "table" => "false", "select_list"=>['','20GP', '40GP', '40HQ', '40OT in gauge', '40OT out gauge' ,'air']],
    ["name" => "tnum", "label" => "Tracking#<br/><em>Start with ups, dhl, fed etc.<br/>followed by #</em>", "type" => "text", "table" => "false"],
    ["name" => "dt_outb", "label" => "Outbound Date", "type" => "date", "table" => "true"],
    ["name" => "dt_ship", "label" => "Shipping Date", "type" => "date", "table" => "true"],
    ["name" => "dt_eta", "label" => "ETA Date", "type" => "date", "table" => "true"],
    ["name" => "notes", "label" => "Notes", "type" => "textarea", "table" => "false", "dump" => "true",
        "textarea_params" => " wrap=\"VIRTUAL\" cols=\"80\" rows=\"10\""],
    ["name" => "doc_ship", "label" => "Shipping Document", "type" => "file_upload", "table" => "false",
        "file_upload_dir" => "isr"],
    ["name" => "doc_qa", "label" => "Quality Document", "type" => "file_upload", "table" => "false",
        "file_upload_dir" => "isr"],
    ["name" => "doc_inv", "label" => "Invoice Document", "type" => "file_upload", "table" => "false",
        "file_upload_dir" => "isr"]
];

$isr_lines_tpl = [
    ["table" => "isr_lines", "form_type" => "isr_lines_tpl",
        "title" => "ISR lines",
        "cancel" => "record_view&form_type=main_tpl", "mode" => "table"],
    ["name" => "id", "label" => "Primary Key", "type" => "primary_key"],
    ["name" => "parent_id", "label" => "Foreign Key", "type" => "foreign_key"],
    ["name" => "t_dino", "label" => "Packing slip", "type" => "text", "table" => "true"]
];

$SMARTY_LOCATION = "intranet";
$DEFAULT_TEMPLATE = "intranet.tpl";
include("header.inc.php");

$user = new tldUser($GLOBALS["PHP_AUTH_USER"]);

if (!$user->isInGroup(["vendors", "gg_PUR", "gg_ADMIN", "gg_PARTS"])) {
    echo "You do not have permission to access this page";
    exit;
}

//Include common db functions
include("db_common2.inc.php");
if (!$id) $id = 0;
//Include db functions
include("db_admin2.inc.php");

?>
