<?php
include("common.inc.php");

$user = new tldUser($GLOBALS['PHP_AUTH_USER']);
if (!$user->isInGroup("superuser")) {
    echo "ERROR: You do not have permission to access this page.";
    exit;
}

$main_tpl = [
    ['table' => 'mod_files', 'form_type' => 'main_tpl', 'title' => 'Module Files',
        'cancel' => 'table&form_type=main_tpl', 'mode' => 'record_view',
        'prn_table_menu' => '<a href="/en/private/common/index.php?m[0]=files">Back to Module</a>',
        'prn_record_menu' => '<a href="/en/private/common/index.php?m[0]=files&m[1]=view&id={id}">Back to Module</a>&nbsp;|&nbsp;',
        'default_sort' => 'id'],
    ['name' => 'id', 'label' => 'Primary Key', 'type' => 'primary_key', 'table' => 'true'],
    ['name' => 'parent_id', 'label' => 'Ref#', 'type' => 'text', 'table' => 'true'],
    ['name' => 'module', 'label' => 'Module', 'type' => 'select', 'table' => 'true', "dump" => "true", "select_list" => array_keys(tldUtils::getModLinks())],
    ['name' => 'description', 'label' => 'File Description', 'type' => 'textarea', 'table' => 'false', "textarea_params" => ' wrap="VIRTUAL" cols="40" rows="7"'],
    ['name' => 'fid', 'label' => 'File ID', 'type' => 'text', 'table' => 'true'],
    ['name' => 'level', 'label' => 'Level', 'type' => 'text', 'table' => 'true'],
];

$SMARTY_LOCATION = "intranet";
$DEFAULT_TEMPLATE = "intranet.tpl";

include_once("header.inc.php");

//Include common db functions
include("db_common2.inc.php");
if (!($id ?? null)) {
    $id = 0;
}
//Include db functions
include("db_admin2.inc.php");
