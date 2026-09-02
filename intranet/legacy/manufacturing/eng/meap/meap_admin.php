<?php
include_once('common.inc.php');
$user = new tldUser($GLOBALS['PHP_AUTH_USER']);

$main_tpl = [
	[
	    'table' => 'meap', 'form_type' => 'main_tpl', 'title' => 'Master EAP (Engineering Activity Process)', 'cancel' => 'table&form_type=',
		'child_tables' => ['meap_pcd', 'meap_factories'],
		'mode' => 'record_view',
		'prn_table_menu' => '<a href="/en/private/manufacturing/eng/dev.php?m[0]=meap">Back to Module</a>&nbsp;|&nbsp;',
		'prn_record_menu' => '<b><a href="/en/private/manufacturing/eng/dev.php?m[0]=meap&m[1]=view&id={id}">Back to Module</a></b>&nbsp;|&nbsp;',
		'default_sort' => 'id',
    ],
	['name' => 'id', 'label' => 'ID&nbsp;####', 'type' => 'primary_key', 'table' => 'true', 'dump' => 'true'],
	['name' => 'parent_id', 'label' => 'Foreign Key', 'type' => 'foreign_key', 'table' => 'false'],
	[
	    'name' => 'status', 'label' => 'Status', 'type' => ($user->isInGroup(['superuser'])) ? 'select' : 'locked_field', 'table' => 'false', 'dump' => 'true',
		'select_list' => [
			'PROPOSAL' => 'PROPOSAL',
			'PHASE_0' => 'PHASE_0',
			'PHASE_1' => 'PHASE_1',
			'PHASE_2' => 'PHASE_2',
			'PHASE_3' => 'PHASE_3',
			'PHASE_4' => 'PHASE_4',
			'GATE_PROPOSAL' => 'GATE_PROPOSAL',
			'GATE_0' => 'GATE_0',
			'GATE_1' => 'GATE_1',
			'GATE_2' => 'GATE_2',
			'GATE_3' => 'GATE_3',
			'GATE_4' => 'GATE_4',
			'REJECTED' => 'REJECTED',
			'SUSPENDED' => 'SUSPENDED',
			'CLOSED' => 'CLOSED',
		], 'source' => 'smartyOptions'
    ],
	[
	    'name' => 'factory', 'label' => 'Factory Location', 'type' => 'lookup', 'table' => 'true',
		'select_query' => 'SELECT id,location FROM locations WHERE erp!=0 ORDER BY location',
		'select_field_1' => 'id', 'select_field_2' => 'location',
		'select_query_view' => 'SELECT * FROM locations WHERE id=',
		'select_field_view' => 'location'
    ],
	['name' => 'ifactor', 'label' => 'Importance Factor', 'type' => 'select', 'table' => 'false', 'dump' => 'true', 'select_list' => ['1', '10', '100', '1000', '10000']],
	['name' => 'product_type', 'label' => 'Equipment Type', 'type' => 'select', 'table' => 'true', 'dump' => 'true', 'select_list' => ['ALL_TYPES' => 'ALL_TYPE', 'SYSTEM' => 'SYSTEM'] + tldUtils::getSqlToAssocArray('SELECT en FROM products_categories ORDER BY en', 'smartyOptions', 'en'), 'source' => 'smartyOptions'],
	['name' => 'model', 'label' => 'Equipment Model', 'type' => 'text', 'table' => 'true', 'dump' => 'true',],
	['name' => 'date', 'label' => 'Date<br>(yyyy-mm-dd)', 'type' => 'auto_date', 'table' => 'true', 'dump' => 'true'],
	['name' => 'short_desc', 'label' => 'Short Description', 'type' => 'text', 'table' => 'true', 'dump' => 'true', 'width' => '50'],
	['name' => 'description', 'label' => 'Description', 'type' => 'textarea', 'table' => 'false', 'dump' => 'true', 'textarea_params' => ' wrap="VIRTUAL" cols="80" rows="10"'],
	['name' => 'resolution', 'label' => 'Resolution', 'type' => 'textarea', 'table' => 'false', 'dump' => 'true', 'textarea_params' => ' wrap="VIRTUAL" cols="80" rows="10"'],
	['name' => 'rejection_reason', 'label' => 'Rejection Reason', 'type' => 'textarea', 'table' => 'false', 'dump' => 'true', 'textarea_params' => ' wrap="VIRTUAL" cols="80" rows="10"'],
	['name' => 'filename', 'label' => 'Uploaded File', 'type' => 'file_upload', 'table' => 'true', 'file_upload_dir' => 'meap'],
	['name' => 'poster', 'label' => 'Posted By', 'type' => 'select', 'table' => 'false', 'dump' => 'true', 'select_list' => tldDirectory::getUserList('smartyOptions'), 'source' => 'smartyOptions'],
	['name' => 'proj_leader', 'label' => 'Project Leader', 'type' => 'select', 'table' => 'false', 'dump' => 'true', 'select_list' => tldGroup::getUserListByMultipleGroup(['gg_ENG', 'role_ENG'], null, 'smartyOptions'), 'source' => 'smartyOptions'],
	['name' => 'econ_capitalized', 'label' => 'Is Capitalized?', 'type' => 'select', 'table' => 'false', 'dump' => 'true', 'select_list' => ['' => '', 'Y' => 'Y', 'N' => 'N', 'S' => 'Sold Program'], 'source' => 'smartyOptions'],
	['name' => 'econ_currency', 'label' => 'Currency', 'type' => 'select', 'table' => 'false', 'dump' => 'true', 'select_list' => ['' => ''] + tldMEAP::getEconCurrencyList(), 'source' => 'smartyOptions'],
	['name' => 'type', 'label' => 'Type', 'type' => 'select', 'table' => 'false', 'dump' => 'true', 'select_list' => ['' => ''] + array_combine(tldMEAP::getTypes(), tldMEAP::getTypes()), 'source' => 'smartyOptions'],
	['name' => 'purpose', 'label' => 'Purpose', 'type' => 'select', 'table' => 'false', 'dump' => 'true', 'select_list' => ['' => ''] + array_combine(tldMEAP::getPurposes(), tldMEAP::getPurposes()), 'source' => 'smartyOptions'],
	['name' => 'cost_calculation_method', 'label' => 'Cost calculation method', 'type' => 'select', 'table' => 'false', 'dump' => 'true', 'select_list' => array_combine(tldMEAP::getCostsCalculationMethods(), tldMEAP::getCostsCalculationMethods()), 'source' => 'smartyOptions'],
];

$meap_pcd_tpl = [
	[
		'table' => 'meap_pcd',
		'form_type' => 'meap_pcd_tpl',
		'title' => 'Provisional Dates',
		'cancel' => 'record_view&form_type=main_tpl',
		'mode' => 'table',
	],
	['name' => 'id', 'label' => 'Primary Key', 'type' => 'primary_key', 'table' => 'false'],
	['name' => 'parent_id', 'label' => 'Foreign Key', 'type' => 'foreign_key', 'table' => 'false'],
	['name' => 'type', 'label' => 'MILESTONE', 'type' => 'locked_field', 'table' => 'true'],
	['name' => 'description', 'label' => 'DESCRIPTION', 'type' => 'locked_field', 'table' => 'true'],
	['name' => 'original_target', 'label' => 'ORIGINAL TARGET', 'type' => 'locked_field', 'table' => 'true'],
	['name' => 'management_target', 'label' => 'MANAGEMENT TARGET', 'type' => 'locked_field', 'table' => 'true'],
	['name' => 'current_target', 'label' => 'CURRENT PROJECT TARGET', 'type' => 'locked_field', 'table' => 'true'],
	['name' => 'actual_date', 'label' => 'ACTUAL CLOSURE DATE (YYYY-MM-DD)', 'type' => 'auto_date', 'table' => 'true'],
];

$meap_factories_tpl = [
	[
		'table' => 'meap_factories',
		'form_type' => 'meap_factories_tpl',
		'title' => 'Sister Factories',
		'cancel' => 'record_view&form_type=main_tpl',
		'mode' => 'table',
	],
	['name' => 'id', 'label' => 'Primary Key', 'type' => 'primary_key', 'table' => 'false'],
	['name' => 'parent_id', 'label' => 'Foreign Key', 'type' => 'foreign_key', 'table' => 'false'],
	[
	    'name' => 'factory_id', 'label' => 'Factory Location', 'type' => 'lookup', 'table' => 'true',
		'select_query' => 'SELECT id,location FROM locations WHERE erp!=0 ORDER BY location',
		'select_field_1' => 'id', 'select_field_2' => 'location',
		'select_query_view' => 'SELECT * FROM locations WHERE id=',
		'select_field_view' => 'location'
    ],
];

$SMARTY_LOCATION = 'intranet';
$DEFAULT_TEMPLATE = 'intranet.tpl';

include_once('header.inc.php');
include_once('db_common2.inc.php');
$user = new tldUser($GLOBALS['PHP_AUTH_USER']);

if ($user->isInGroup(['gg_ENG', 'eap', 'gg_ADMIN'])) {
	include('db_admin2.inc.php');
} else {
	include('db_readonly2.inc.php');
}
