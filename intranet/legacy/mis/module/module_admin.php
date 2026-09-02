<?php
include_once("common.inc.php");
$user = new tldUser($GLOBALS["PHP_AUTH_USER"]);

if (!$user->isInGroup(["superuser"])) {
    echo "ERROR: You do not have permissions for this page";
    exit;
}

$grpDev = new tldGroup("role_DEV");
$peopleDev = $grpDev->getUserlist("smartyOptions");

$main_tpl = [
    [
        "table" => "com_modules",
        "form_type" => "main_tpl",
        "title" => "Modules",
        "cancel" => "table&form_type=",
        "mode" => "record_view",
        "prn_table_menu" => "<a href=\"/en/private/mis/mis.php?m[0]=module\">Back to Module</a>&nbsp;|&nbsp;",
        "prn_record_menu" => "<b><a href=\"/en/private/mis/mis.php?m[0]=module&m[1]=view&id={id}\">Back to Module</a></b>&nbsp;|&nbsp;",
        "default_sort" => "id",
    ],
    [
        "name" => "id",
        "label" => "Primary Key",
        "type" => "primary_key",
        "table" => "true",
        "dump" => "true"
    ],
    [
        "name" => "parent_id",
        "label" => "Foreign Key",
        "type" => "foreign_key"
    ],
    [
        "name" => "module",
        "label" => "Module code",
        "type" => "text",
        "table" => "true",
        "dump" => "true"
    ],
    [
        "name" => "dsc",
        "label" => "Description",
        "type" => "text",
        "table" => "true",
        "dump" => "true"
    ],
    [
        "name" => "oid",
        "label" => "Module owner",
        "type" => "select",
        "table" => "true",
        "dump" => "true",
        "select_list" => tldDirectory::getUserList("smartyOptions"),
        "source" => "smartyOptions"
    ],
    [
        "name" => "uid",
        "label" => "MIS owner",
        "type" => "select",
        "table" => "true",
        "dump" => "true",
        "select_list" => $peopleDev, 
        "source" => "smartyOptions"
    ],
    [
        "name" => "note",
        "label" => "Details",
        "type" => "textarea",
        "table" => "false",
        "dump" => "false",
        "textarea_params" => " wrap=\"VIRTUAL\" cols=\"50\" rows=\"8\""
    ],
    [
        "name" => "link",
        "label" => "Module link",
        "type" => "text",
        "table" => "false",
        "dump" => "false"
    ],
    [
        "name" => "help_page_id",
        "label" => "DMS# for Help page",
        "type" => "text", "table" => "true",
        "dump" => "true"
    ],
    [
        "name" => "user_guide_id",
        "label" => "DMS# for user guide",
        "type" => "text",
        "table" => "true",
        "dump" => "true"
    ],
    [
        "name" => "migrated",
        "label" => "Migrated to Symfony",
        "type" => "select",
        "table" => "true",
        "dump" => "true",
        "select_list" => [
            'No',
            'Yes',
        ],
        "source" => "smartyOptions",
    ],
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

