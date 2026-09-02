<?php
include_once('common.inc.php');
include_once('common.inc.php');
include_once('sales_service.inc.php');

$user = new tldUser($GLOBALS['PHP_AUTH_USER']);

// Listing
$peopleList = tldDirectory::getUserlist('smartyOptions');
$factoryList = tldLocation::getFactoryList('smartyOptionsIDLocation');
$statusList = tldSB3::getStatusList();
$categoryList = tldSB3::getCategoryList();
$typeList = tldSB3::getTypeList();
$iFactorList = tldSB3::getIFactorList();
$lineStatusList = ['' => ''] + tldSB_Line::getStatusList();
$serviceDecisionList = array_keys(tldSB_Line::getServiceDecisionList());
$serviceDecisionList = ['' => ''] + array_combine($serviceDecisionList, $serviceDecisionList);
$partsDecisionList = array_keys(tldSB_Line::getPartDecisionList());
$partsDecisionList = ['' => ''] + array_combine($partsDecisionList, $partsDecisionList);
$modelList = tldModel::getList();
$ssoList = tldLocation::getSalesOrgList('smartyOptionsIDLocation');
$closureTypeList = ['' => ''] + tldSB_Line::getClosureTypeList();
$customerDecisionList = ['' => ''] + tldSB_Line::getCustomerDecisionList();

$main_tpl = [
    [
        'table' => 'sb', 'form_type' => 'main_tpl', 'title' => 'Service Bulletins',
        'cancel' => 'table&form_type=', 'mode' => 'record_view',
        'child_tables' => ['sb_lines', 'sb_coverage', 'sb_signature'],
        'prn_table_menu' => '<a href="/en/private/product_support/index.ps.php?m[0]=sb">Back to Module</a>&nbsp;|&nbsp;',
        'prn_record_menu' => '<b><a href="/en/private/product_support/index.ps.php?m[0]=sb&m[1]=view&id={id}">Back to Module</a></b>&nbsp;|&nbsp;',
        'default_sort' => 'id',
    ],
    ['name' => 'id', 'label' => 'SB#', 'type' => 'primary_key', 'table' => 'true', 'dump' => 'true'],
    ['name' => 'parent_id', 'label' => 'Foreign Key', 'type' => 'foreign_key', 'table' => 'false', 'dump' => 'false'],
    ['name' => 'dt', 'label' => 'Date', 'type' => 'locked_date', 'table' => 'true', 'dump' => 'true'],
    ['name' => 'poster_id', 'label' => 'Poster', 'type' => 'select', 'table' => 'false', 'dump' => 'true',
        'select_list' => $peopleList, 'source' => 'smartyOptions'],
    ['name' => 'bu_id', 'label' => 'Factory', 'type' => 'select', 'table' => 'true', 'dump' => 'true',
        'select_list' => $factoryList, 'source' => 'smartyOptions'],
    ['name' => 'status', 'label' => 'Status', 'type' => 'select', 'table' => 'true', 'dump' => 'true',
        'select_list' => $statusList, 'source' => 'smartyOptions'],
    ['name' => 'category', 'label' => 'Category', 'type' => 'select', 'table' => 'true', 'dump' => 'true',
        'select_list' => $categoryList, 'source' => 'smartyOptions'],
    ['name' => 'category_reason', 'label' => nl2br("Further indications for CSMs\n(SB category rationale, ER coverage, etc.)\nNot viewed by customers"), 'type' => 'textarea', 'table' => 'false', 'dump' => 'true',
        'textarea_params' => ' wrap="VIRTUAL" cols="30" rows="3"'],
    ['name' => 'type', 'label' => 'Type', 'type' => 'select', 'table' => 'false', 'dump' => 'true',
        'select_list' => $typeList, 'source' => 'smartyOptions'],
    ['name' => 'ifactor', 'label' => 'IFactor', 'type' => 'select', 'table' => 'false', 'dump' => 'true',
        'select_list' => $iFactorList, 'source' => 'smartyOptions'],
    ['name' => 'confidential', 'label' => 'Confidential', 'type' => 'select', 'table' => 'true', 'dump' => 'true',
        'select_list' => ['N' => 'N', 'Y' => 'Y'], 'source' => 'smartyOptions'],
    ['name' => 'title', 'label' => 'Title', 'type' => 'text', 'table' => 'true', 'dump' => 'true'],
    ['name' => 'description', 'label' => 'Description', 'type' => 'textarea', 'table' => 'false', 'dump' => 'true',
        'textarea_params' => ' wrap="VIRTUAL" cols="60" rows="8"'],
    ['name' => 'parts_needed', 'label' => 'Parts needed', 'type' => 'select', 'table' => 'false', 'dump' => 'true',
        'select_list' => ['N' => 'N', 'Y' => 'Y'], 'source' => 'smartyOptions'],
    ['name' => 'factory_part_availability_status', 'label' => 'PFactory parts availability', 'type' => 'select', 'table' => 'false', 'dump' => 'true',
        'select_list' => tldSB3::getPartsAvailabilityChoicesList(), 'source' => 'smartyOptions'],
    ['name' => 'labor', 'label' => 'Labor (in minute)', 'type' => 'text', 'table' => 'false', 'dump' => 'true'],
    ['name' => 'nb_tech_needed', 'label' => 'Technician needed', 'type' => 'text', 'table' => 'false', 'dump' => 'true'],
    ['name' => 'dt_ssd_approval', 'label' => 'CSM approval date', 'type' => 'locked_date', 'table' => 'false', 'dump' => 'true'],
    ['name' => 'dt_ssd_decision', 'label' => 'SSD decision date', 'type' => 'locked_date', 'table' => 'false', 'dump' => 'true'],
    ['name' => 'dt_closed', 'label' => 'Closed date', 'type' => 'locked_date', 'table' => 'false', 'dump' => 'true'],
];

$sb_lines_tpl = [
    [
        'table' => 'sb_lines', 'form_type' => 'sb_lines_tpl', 'title' => 'SB Lines',
        'cancel' => 'record_view&form_type=main_tpl', 'mode' => 'form_view',
    ],
    ['name' => 'id', 'label' => 'SB Line#', 'type' => 'primary_key', 'table' => 'true'],
    ['name' => 'parent_id', 'label' => 'Foreign Key', 'type' => 'foreign_key', 'table' => 'false'],
    ['name' => 'er_id', 'label' => 'ER#', 'type' => 'text', 'table' => 'true', 'dump' => 'true'],
    ['name' => 'part_decision', 'label' => 'Part decision', 'type' => 'select', 'table' => 'true', 'dump' => 'true',
        'select_list' => $partsDecisionList, 'source' => 'smartyOptions'],
    ['name' => 'cust_part_decision', 'label' => 'Customer Part decision', 'type' => 'select', 'table' => 'true', 'dump' => 'true',
        'select_list' => $customerDecisionList, 'source' => 'smartyOptions'],
    ['name' => 'spr_id', 'label' => 'SPR#', 'type' => 'text', 'table' => 'true', 'dump' => 'true'],
    ['name' => 'service_decision', 'label' => 'Service decision', 'type' => 'select', 'table' => 'true', 'dump' => 'true',
        'select_list' => $serviceDecisionList, 'source' => 'smartyOptions'],
    ['name' => 'cust_service_decision', 'label' => 'Customer Service decision', 'type' => 'select', 'table' => 'true', 'dump' => 'true',
        'select_list' => $customerDecisionList, 'source' => 'smartyOptions'],
    ['name' => 'csr_id', 'label' => 'CSR#', 'type' => 'text', 'table' => 'true', 'dump' => 'true'],
    ['name' => 'status', 'label' => 'Status', 'type' => 'select', 'table' => 'true', 'dump' => 'true',
        'select_list' => $lineStatusList, 'source' => 'smartyOptions'],
    ['name' => 'dt_cust_to_decide', 'label' => 'Date Customer to decide', 'type' => 'date', 'table' => 'true', 'dump' => 'true'],
    ['name' => 'closure_type', 'label' => 'Closure type', 'type' => 'select', 'table' => 'true', 'dump' => 'true',
        'select_list' => $closureTypeList, 'source' => 'smartyOptions'],
];

$sb_coverage_tpl = [
    [
        'table' => 'sb_coverage', 'form_type' => 'sb_coverage_tpl', 'title' => 'SN Coverage',
        'cancel' => 'record_view&form_type=main_tpl', 'mode' => 'form_view',
    ],
    ['name' => 'id', 'label' => 'Primary Key', 'type' => 'primary_key', 'table' => 'false'],
    ['name' => 'parent_id', 'label' => 'Foreign Key', 'type' => 'foreign_key', 'table' => 'false'],
    ['name' => 'model', 'label' => 'Model', 'type' => 'select', 'table' => 'true', 'dump' => 'true',
        'select_list' => ['' => ''] + $modelList, 'source' => 'smartyOptions'],
    ['name' => 'sn_from', 'label' => 'SN from', 'type' => 'text', 'table' => 'true', 'dump' => 'true'],
    ['name' => 'sn_to', 'label' => 'SN to', 'type' => 'text', 'table' => 'true', 'dump' => 'true'],
    ['name' => 'sn_list', 'label' => 'SN List', 'type' => 'textarea', 'table' => 'true', 'dump' => 'true',
        'textarea_params' => ' wrap="VIRTUAL" cols="80" rows="5" class="no-editor"'],
];

$sb_signature_tpl = [
    [
        'table' => 'sb_signature', 'form_type' => 'sb_signature_tpl', 'title' => 'SB Signatures',
        'cancel' => 'record_view&form_type=main_tpl', 'mode' => 'form_view',
    ],
    ['name' => 'id', 'label' => 'Primary Key', 'type' => 'primary_key', 'table' => 'false'],
    ['name' => 'parent_id', 'label' => 'Foreign Key', 'type' => 'foreign_key', 'table' => 'false'],
    ['name' => 'dt', 'label' => 'Date', 'type' => 'locked_date', 'table' => 'true', 'dump' => 'true'],
    ['name' => 'user_id', 'label' => 'Signatory', 'type' => 'select', 'table' => 'true', 'dump' => 'true',
        'select_list' => $peopleList, 'source' => 'smartyOptions'],
    ['name' => 'sso_id', 'label' => 'SSO', 'type' => 'select', 'table' => 'true', 'dump' => 'true',
        'select_list' => $ssoList, 'source' => 'smartyOptions'],
    ['name' => 'status', 'label' => 'Status', 'type' => 'select', 'table' => 'true', 'dump' => 'true',
        'select_list' => $statusList, 'source' => 'smartyOptions'],
];

include('header.inc.php');
include('db_common2.inc.php');
$SMARTY_LOCATION = 'intranet';
$DEFAULT_TEMPLATE = 'intranet.tpl';
if (!$id) {
    $id = 0;
}

$mooID = tldModule::getMOOIDByModule('SB3');
if ($user->isInGroup(['superuser', 'role_CSD']) || ($mooID && $user->getID() == $mooID)) {
    include('db_admin2.inc.php');
} else {
    include('db_readonly2.inc.php');
}
