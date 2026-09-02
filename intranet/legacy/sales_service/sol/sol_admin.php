<?php
include_once('common.inc.php');
include_once('sales_service.inc.php');
$user = new tldUser($GLOBALS['PHP_AUTH_USER']);

if (!$user->isInGroup(['gg_ADMIN', 'gg_ACCT'])) {
    echo 'You do not have permissions for this page..';
    exit;
}

if ($mode === 'duplicate' && !$user->isInGroup(['superuser'])) {
    echo $mode . ': You do not have permissions for this function..';
    exit;
}

if ($user->isInGroup(['superuser'])) {
    $statusField = ['name' => 'status', 'label' => 'Status', 'type' => 'select', 'table' => 'true', 'select_list' => tldSOL::getStatusList(),];
} else {
    $statusField = ['name' => 'status', 'label' => 'Status', 'type' => 'locked_field', 'table' => 'true'];
}

$main_tpl = [
    [
        'table' => 'sor_lines', 'form_type' => 'main_tpl', 'title' => 'Sales Order Record Lines', 'cancel' => 'table&form_type=', 'mode' => 'record_view',
        'child_tables' => ['sor_opts', 'sor_units', 'sor_tran'],
        'prn_table_menu' => '<a href="/en/private/sales_service/sales.php?m[0]=sol">Back to Module</a>&nbsp;|&nbsp;',
        'prn_record_menu' => '<b><a href="/en/private/sales_service/sales.php?m[0]=sol&m[1]=view&id={id}">Back to Module</a></b>&nbsp;|&nbsp;',
        'default_sort' => 'id',
    ],
    ['name' => 'id', 'label' => 'Primary Key', 'type' => 'primary_key'],
    ['name' => 'parent_id', 'label' => 'Foreign Key', 'type' => 'foreign_key'],
    $statusField,
    [
        'name' => 'bu', 'label' => 'Business Unit', 'type' => 'lookup', 'table' => 'true',
        'select_query' => 'SELECT id,location FROM locations WHERE erp!=0 ORDER BY location',
        'select_field_1' => 'id', 'select_field_2' => 'location',
        'select_query_view' => 'SELECT * FROM locations WHERE id=',
        'select_field_view' => 'location',
        'options' => ['required' => true],
    ],
    ['name' => 'sls_orno', 'label' => 'Sales PO#', 'type' => 'text', 'table' => 'true'],
    ['name' => 'erp_orno', 'label' => 'Factory SO#', 'type' => 'text', 'table' => 'true'],
    ['name' => 'model', 'label' => 'Model', 'type' => 'text', 'table' => 'true'],
    ['name' => 'eng_tier', 'label' => 'Emission Rating', 'type' => 'select', 'table' => 'true', 'dump' => 'true', 'source' => 'lists', 'list_name' => 'list.engine.tiers'],
    ['name' => 'dzk_sso', 'label' => 'Date, Zero Backlog, SSO', 'type' => 'text', 'table' => 'true'],
    ['name' => 'dzk_erp', 'label' => 'Date, Zero Backlog, ERP', 'type' => 'text', 'table' => 'true'],
//-- Delivery info -------------->
    ['name' => 'del_pen', 'label' => 'Delivery penalties?', 'type' => 'select', 'dump' => 'true', 'select_list' => ['Y', 'N']],
    ['name' => 'delpen_cond', 'label' => 'Delivery penaly conditions', 'type' => 'text'],
    ['name' => 'conf_sls', 'label' => 'Delivery penalties accepted by sales?', 'type' => 'select', 'dump' => 'true', 'select_list' => ['Y', 'N']],
    ['name' => 'conf_erp', 'label' => 'Delivery penalties accepted  by factory?', 'type' => 'select', 'dump' => 'true', 'select_list' => ['Y', 'N']],
    ['name' => 'conf_cis', 'label' => 'Customer inspection before shipment?', 'type' => 'select', 'dump' => 'true', 'select_list' => ['Y', 'N']],
    ['name' => 'inco', 'label' => 'Inco Terms', 'type' => 'select', 'table' => 'true', 'dump' => 'true', 'source' => 'lists', 'list_name' => 'list.inco.terms'],
    ['name' => 'inco_loc', 'label' => 'Inco location', 'type' => 'text', 'table' => 'true'],
    ['name' => 'ctry', 'label' => 'Country', 'type' => 'select', 'table' => 'true', 'dump' => 'true', 'select_list' => tldCountry::optionsAsNameName(), 'source' => 'smartyOptions'],
//-- Warranty info -------------->
    ["name" => "warranty_length", "label" => "Warranty Length (Months)", "type" => "select", "table" => "false", "dump" => "true", "select_list" => array_values(tldEquipment::getAllowedWarrantyLength())],
    ['name' => 'warranty_length_hours', 'label' => 'Warranty Length (Hours)', 'type' => 'text', 'table' => 'false', 'dump' => 'true'],
    ['name' => 'wrty_std', 'label' => 'Standard Warranty Conditions', 'type' => 'textarea', 'dump' => 'true', 'table' => 'false', 'textarea_params' => ' wrap="VIRTUAL" cols="80" rows="10"'],
    ['name' => 'wrty_spec', 'label' => 'Special Warranty Conditions', 'type' => 'textarea', 'table' => 'false', 'dump' => 'true', 'textarea_params' => ' wrap="VIRTUAL" cols="80" rows="10"'],
    ['name' => 'conf_wrty_erp', 'label' => 'Warranty Conditions accepted by factory?', 'type' => 'select', 'dump' => 'true', 'select_list' => ['Y', 'N']],
//-- Payment info -------------->
    ['name' => 'tpay', 'label' => 'Terms of payment', 'type' => 'textarea', 'table' => 'false', 'textarea_params' => ' wrap="VIRTUAL" cols="80" rows="10"'],
    ['name' => 'conf_cxo', 'label' => 'Confirmed by CXO?', 'type' => 'select', 'dump' => 'true', 'select_list' => ['Y', 'N']],
    ['name' => 'conf_lc', 'label' => 'Letter of credit required?', 'type' => 'select', 'dump' => 'true', 'select_list' => ['Y', 'N']],
    ['name' => 'dp_pc', 'label' => 'Down payment %', 'type' => 'text'],
    ['name' => 'dp_amt', 'label' => 'Down payment', 'type' => 'text', 'table' => 'true'],
    ['name' => 'receivedp_amt', 'label' => 'Received Down payment', 'type' => 'text', 'table' => 'true'],
    ['name' => 'receive_lc', 'label' => 'Letter of credit received?', 'type' => 'select', 'dump' => 'true', 'select_list' => ['Y', 'N']],
    ['name' => 'cu_ocur', 'label' => 'Customer PO Currency', 'type' => 'select', 'table' => 'true', 'source' => 'lists', 'list_name' => 'list.common.currency'],
//-- Other info -------------->
    ['name' => 'intro_new', 'label' => 'New Introduction ?', 'type' => 'select', 'dump' => 'true', 'select_list' => ['Customer', 'Product', 'No']],
    ['name' => 'parts_inc', 'label' => 'Ship with Spare Parts?', 'type' => 'select', 'dump' => 'true', 'select_list' => ['Y', 'N']],
    ['name' => 'docs_inc', 'label' => 'Special Documentary Requirements', 'type' => 'textarea', 'dump' => 'true', 'textarea_params' => ' wrap="VIRTUAL" cols="80" rows="10"'],
    ['name' => 'notes', 'label' => 'Notes', 'type' => 'textarea', 'textarea_params' => ' wrap="VIRTUAL" cols="80" rows="10"'],
    ['name' => 'factory_margin', 'label' => 'Factory Margin (%)', 'type' => 'text'],
    ['name' => 'fms_contract_length', 'label' => 'FMS contract length in months (0 to 240)', 'type' => 'text'],
    ['name' => 'certificate_of_origin_required', 'label' => 'Certificate of origin required', 'type' => 'bool'],
];

$sor_opts_tpl = [
    ['table' => 'sor_opts', 'form_type' => 'sor_opts_tpl', 'title' => 'Items', 'cancel' => 'record_view&form_type=main_tpl', 'mode' => 'table'],
    ['name' => 'id', 'label' => 'Primary Key', 'type' => 'primary_key'],
    ['name' => 'parent_id', 'label' => 'Foreign Key', 'type' => 'foreign_key'],
    ['name' => 'caty', 'label' => 'Category', 'type' => 'select', 'table' => 'true', 'dump' => 'true', 'source' => 'lists', 'list_name' => 'list.sol.caty.int'],
    ['name' => 'dsca', 'label' => 'Description', 'type' => 'text', 'table' => 'true'],

    ['name' => 'mrsp_cur', 'label' => 'Published TP Currency', 'type' => 'select', 'table' => 'true', 'source' => 'lists', 'list_name' => 'list.common.currency'],
    ['name' => 'mrsp', 'label' => 'Published TP', 'type' => 'text', 'table' => 'true'],
    ['name' => 'pric_cur', 'label' => 'Negotiated TP Currency', 'type' => 'select', 'table' => 'true', 'source' => 'lists', 'list_name' => 'list.common.currency'],
    ['name' => 'pric', 'label' => 'Negotiated TP', 'type' => 'text', 'table' => 'true'],
    ['name' => 'prip_cur', 'label' => 'Published Sales Price Currency', 'type' => 'select', 'table' => 'true', 'source' => 'lists', 'list_name' => 'list.common.currency'],
    ['name' => 'prip', 'label' => 'Published Sales Price', 'type' => 'text', 'table' => 'true'],
    ['name' => 'pris_cur', 'label' => 'Actual Sales Price Currency', 'type' => 'select', 'table' => 'true', 'source' => 'lists', 'list_name' => 'list.common.currency'],
    ['name' => 'pris', 'label' => 'Actual Sales Price', 'type' => 'text', 'table' => 'true'],
];

$sor_units_tpl = [
    ['table' => 'sor_units', 'form_type' => 'sor_units_tpl', 'title' => 'Units ordered', 'cancel' => 'record_view&form_type=main_tpl', 'mode' => 'table'],
    ['name' => 'id', 'label' => 'Primary Key', 'type' => 'primary_key'],
    ['name' => 'parent_id', 'label' => 'Foreign Key', 'type' => 'foreign_key'],
    ['name' => 'short_desc', 'label' => 'Short Description', 'type' => 'text', 'table' => 'true'],
    ['name' => 'long_desc', 'label' => 'Long Description', 'type' => 'textarea', 'table' => 'false', 'dump' => 'true', 'textarea_params' => ' wrap="VIRTUAL" cols="80" rows="10"'],
    ['name' => 'batch_qty', 'label' => 'Batch Qty', 'type' => 'text', 'table' => 'true'],
    ['name' => 'del_location', 'label' => 'Requested Delivery Location', 'type' => 'text', 'table' => 'true'],
    ['name' => 'del_dat', 'label' => 'Requested Delivery Date', 'type' => 'text', 'table' => 'true'],
    ['name' => 'ddel_est1', 'label' => 'Factory Promised Delivery Date', 'type' => 'text', 'table' => 'true'],
    ['name' => 'del_early', 'label' => 'Early delivery ok?', 'type' => 'select', 'table' => 'true', 'select_list' => ['Y', 'N']],
];

$sol_tran_tpl = [
    ['table' => 'sol_tran', 'form_type' => 'sol_tran_tpl', 'title' => 'SOL Transactions', 'cancel' => 'record_view&form_type=main_tpl', 'mode' => 'table'],
    ['name' => 'id', 'label' => 'Primary Key', 'type' => 'primary_key'],
    ['name' => 'parent_id', 'label' => 'Foreign Key', 'type' => 'foreign_key'],
    ['name' => 'dtran', 'label' => 'Transaction Date', 'type' => 'auto_date', 'table' => 'true'],
    ['name' => 'ttyp', 'label' => 'Revenue or Booking', 'type' => 'select', 'dump' => 'true', 'table' => 'true', 'select_list' => ['B', 'R']],
    ['name' => 'tval', 'label' => 'Transaction Value', 'type' => 'text', 'table' => 'true'],
    ['name' => 'notes', 'label' => 'Notes', 'type' => 'textarea', 'textarea_params' => ' wrap="VIRTUAL" cols="80" rows="10"'],
];

$sor_tran_tpl = [
    ['table' => 'sor_tran', 'form_type' => 'sor_tran_tpl', 'title' => 'SOR Transactions', 'cancel' => 'record_view&form_type=main_tpl', 'mode' => 'table'],
    ['name' => 'id', 'label' => 'Primary Key', 'type' => 'primary_key'],
    ['name' => 'parent_id', 'label' => 'Foreign Key', 'type' => 'foreign_key'],
    ['name' => 'dtran', 'label' => 'Transaction Date', 'type' => 'auto_date', 'table' => 'true'],
    ['name' => 'tgrp', 'label' => 'SSO or ERP (Factory)', 'type' => 'select', 'dump' => 'true', 'table' => 'true', 'select_list' => ['SSO', 'ERP']],
    ['name' => 'ttyp', 'label' => 'Revenue or Booking', 'type' => 'select', 'dump' => 'true', 'table' => 'true', 'select_list' => ['B', 'R']],
    ['name' => 'tcur', 'label' => 'Currency', 'type' => 'select', 'table' => 'true', 'source' => 'lists', 'list_name' => 'list.common.currency'],
    ['name' => 'tval', 'label' => 'Transaction Value', 'type' => 'text', 'table' => 'true'],
    ['name' => 'notes', 'label' => 'Notes', 'type' => 'textarea', 'table' => 'true', 'textarea_params' => ' wrap="VIRTUAL" cols="80" rows="10"'],
];

$SMARTY_LOCATION = 'intranet';
$DEFAULT_TEMPLATE = 'intranet.tpl';

include('header.inc.php');

//Include common db functions
include('db_common2.inc.php');
if (!$id) {
    $id = 0;
}
//Include db functions
if ($user->isInGroup(['gg_ADMIN', 'gg_ACCT'])) {
    include('db_admin2.inc.php');
} else {
    include('db_readonly2.inc.php');
}
