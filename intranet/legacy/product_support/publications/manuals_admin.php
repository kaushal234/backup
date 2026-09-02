<?php
include_once("common.inc.php");
$user = new tldUser($GLOBALS["PHP_AUTH_USER"]);
tldUtils::logUserAccess();

$main_tpl = [
    ["table" => "manuals", "form_type" => "main_tpl", "title" => "Manuals Admin",
        "cancel" => "table&form_type=", "mode" => "record_view",
        "child_tables" => ["manuals_docs"],
        "email_title" => "Record ID",
        "prn_table_menu" => "<a href=\"/en/private/product_support/index.ps.php?m[0]=publications\">Back to module</a>",
        "prn_record_menu" => "<b><a href=\"/en/private/product_support/index.ps.php?m[0]=publications&m[1]=manuals&m[2]=view&id={id}\">Back to module</a>".
            "&nbsp;|&nbsp;<a href=\"/en/private/product_support/index.ps.php?m[0]=publications&m[1]=manuals&m[2]=view&id={id}\">Preview</a></b>|",
        "default_sort" => "id"],
//start of table definitions
//WARRANTY DETAILS
    ["name" => "id", "label" => "ID", "type" => "primary_key", "table" => "true", "dump" => "true"],
    ["name" => "parent_id", "label" => "Foreign Key", "type" => "foreign_key", "table" => "false"],
    ["name" => "brand", "label" => "Brand", "type" => "text", "table" => "true"],
    ["name" => "model", "label" => "Model", "type" => "text", "table" => "true"],
    ["name" => "description", "label" => "Description", "type" => "textarea", "table" => "true", "dump" => "true",
        "textarea_params" => " wrap=\"VIRTUAL\" cols=\"50\" rows=\"7\""],
    ["name" => "lang", "label" => "Language", "type" => "select", "table" => "true", "dump" => "true",
        "source" => "lists", "list_name" => "list.iso.lang.code",
        "default" => "ENGLISH",
    ],
    ["name" => "features", "label" => "Distinct Features", "type" => "textarea", "table" => "true", "dump" => "true",
        "textarea_params" => " wrap=\"VIRTUAL\" cols=\"50\" rows=\"7\""],
    ["name" => "status", "label" => "Status", "type" => "select", "table" => "false", "dump" => "true",
        "select_list" => ["PRELIMINARY",
            "RELEASED",
        ],
    ],
];

$manuals_docs_tpl = [
    ["table" => "manuals_docs", "form_type" => "manuals_docs_tpl",
        "title" => "Manuals Docs",
        "cancel" => "record_view&form_type=main_tpl", "mode" => "table",
        "default_sort" => "item ASC",
    ],
    ["name" => "id", "label" => "Primary Key", "type" => "primary_key", "table" => "true"],
    ["name" => "parent_id", "label" => "Foreign Key", "type" => "foreign_key", "table" => "false"],
    ["name" => "item", "label" => "Item#", "type" => "text", "table" => "true"],
    ["name" => "doc_num", "label" => "Doc#", "type" => "text", "table" => "true"],
];

$SMARTY_LOCATION = "intranet";
$DEFAULT_TEMPLATE = "intranet.tpl";

include("header.inc.php");
include("db_common2.inc.php");
if (!$id) {
    $id = 0;
}
//Include db functions
if ($user->isInGroup(["manuals", "gg_SUPPORT", "gg_ENG", "gg_ADMIN"])) {
    include("db_admin2.inc.php");
} else {
    include("db_readonly2.inc.php");
}

