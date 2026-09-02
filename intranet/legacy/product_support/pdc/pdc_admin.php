<?php
include_once('common.inc.php');
include_once('product_support.inc.php');

$user = new tldUser($GLOBALS['PHP_AUTH_USER']);

$main_tpl = [
    ['table' => 'demerit', 'form_type' => 'main_tpl', 'title' => 'PDC',
        'cancel' => 'table&form_type=', 'mode' => 'record_view',
        'child_tables' => ['demerit_history'],
        'prn_table_menu' => '<a href="/en/private/product_support/index.ps.php?m[0]=pdc">Back to Demerit Module</a>&nbsp;|&nbsp;',
        'prn_record_menu' => '<a href="/en/private/product_support/index.ps.php?m[0]=pdc&m[1]=view&m[2]=&id={id}"><b>Back to PDC</b></a>&nbsp;|&nbsp;',
        'email_title' => 'Demerit ID', 'default_sort' => 'id'],
    ['name' => 'id', 'label' => 'PDC#', 'type' => 'primary_key', 'table' => 'true', 'dump' => 'true'],
    ['name' => 'parent_id', 'label' => 'Foreign Key', 'type' => 'foreign_key', 'table' => 'false'],
    ['name' => 'status', 'label' => 'Status', 'type' => 'select', 'table' => 'true', 'dump' => 'true',
        'select_list' => tldPDC::getStatusList(), 'source' => 'smartyOptions'],
    ['name' => 'last_status', 'label' => 'Last Status', 'type' => 'select', 'table' => 'false', 'dump' => 'false',
        'select_list' => tldPDC::getStatusList(), 'source' => 'smartyOptions'],
    ['name' => 'factory', 'label' => 'Factory Location', 'type' => 'lookup', 'table' => 'true', 'dump' => 'true',
        'select_query' => 'SELECT id,location FROM locations WHERE erp<>0 ORDER BY location',
        'select_field_1' => 'id', 'select_field_2' => 'location',
        'select_query_view' => 'SELECT * FROM locations WHERE id=',
        'select_field_view' => 'location'],
    ['name' => 'ifactor', 'label' => 'Importance Factor', 'type' => 'select', 'table' => 'false', 'dump' => 'true',
        'select_list' => ['1', '10', '100', '1000']],
    ['name' => 'product_type', 'label' => 'Equipment Type', 'type' => 'select_db', 'table' => 'true', 'dump' => 'true',
        'select_query' => 'SELECT en FROM products_categories ORDER BY en',
        'select_field_1' => 'en', 'select_field_2' => 'en'],
    ['name' => 'model', 'label' => 'Equipment Model<br>(use ALL_MODELS selection for no specific model)', 'type' => 'select_db', 'table' => 'false', 'dump' => 'true',
        'select_query' => 'SELECT model FROM models ORDER BY model',
        'select_field_1' => 'model', 'select_field_2' => 'model'],
    ['name' => 'date', 'label' => 'Date<br>(yyyy-mm-dd)', 'type' => 'auto_date', 'table' => 'true', 'dump' => 'true'],
    ['name' => 'short_desc', 'label' => 'Short Description', 'type' => 'text', 'table' => 'true', 'dump' => 'true', 'width' => '50'],
    ['name' => 'description', 'label' => 'Description', 'type' => 'textarea', 'table' => 'false', 'dump' => 'true',
        'textarea_params' => ' wrap="VIRTUAL" cols="80" rows="10"'],
    ['name' => 'containment_action', 'label' => 'Containment actions', 'type' => 'textarea', 'table' => 'false', 'dump' => 'true',
        'textarea_params' => ' wrap="VIRTUAL" cols="80" rows="10"'],
    ['name' => 'root_cause', 'label' => 'Final root cause', 'type' => 'textarea', 'table' => 'false', 'dump' => 'true',
        'textarea_params' => ' wrap="VIRTUAL" cols="80" rows="10"'],
    ['name' => 'corrective_action', 'label' => 'Corrective actions', 'type' => 'textarea', 'table' => 'false', 'dump' => 'true',
        'textarea_params' => ' wrap="VIRTUAL" cols="80" rows="10"'],
    ['name' => 'preventive_action', 'label' => 'Preventive actions', 'type' => 'textarea', 'table' => 'false', 'dump' => 'true',
        'textarea_params' => ' wrap="VIRTUAL" cols="80" rows="10"'],
    ['name' => 'resolution', 'label' => 'Resolution', 'type' => 'textarea', 'table' => 'false', 'dump' => 'true',
        'textarea_params' => ' wrap="VIRTUAL" cols="80" rows="10"'],
    ['name' => 'rejection_reason', 'label' => 'Rejection Reason', 'type' => 'textarea', 'table' => 'false', 'dump' => 'true',
        'textarea_params' => ' wrap="VIRTUAL" cols="80" rows="10"'],
    ['name' => 'picture_filename', 'label' => 'Picture', 'type' => 'file_upload', 'table' => 'true',
        'file_upload_dir' => 'demerit'],
    ['name' => 'poster', 'label' => 'Posted By', 'type' => 'select', 'table' => 'false', 'dump' => 'true',
        'select_list' => tldDirectory::getUserList('smartyOptions'), 'source' => 'smartyOptions'],
    ['name' => 'initiator', 'label' => 'Initiator', 'type' => 'select', 'table' => 'false', 'dump' => 'true',
        'select_list' => tldDirectory::getUserList('smartyOptions'), 'source' => 'smartyOptions'],
];

$demerit_history_tpl = [
    ['table' => 'demerit_history', 'form_type' => 'demerit_history_tpl',
        'title' => 'Fweight History', 'cancel' => 'record_view&form_type=main_tpl',
        'mode' => 'form_view'],
    ['name' => 'id', 'label' => 'Primary Key', 'type' => 'primary_key', 'table' => 'false'],
    ['name' => 'parent_id', 'label' => 'Foreign Key', 'type' => 'foreign_key', 'table' => 'false'],
    ['name' => 'date', 'label' => 'Date', 'type' => 'locked_field', 'table' => 'true'],
    ['name' => 'fweight', 'label' => 'FWeight', 'type' => 'locked_field', 'table' => 'true'],
];

// Do not allow addition/edition/delete of FW history

switch ($form_type) {
    case 'demerit_history_tpl':
        if (!in_array($mode, ['form_email', 'record_view'])) {
            $sess_error_message = 'ERROR: Fweight history only for viewing';
            $mode = 'record_view';
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
if ($user->isInGroup(["superuser"])) {
    include("db_admin2.inc.php");
} else {
    include("db_readonly2.inc.php");
}

