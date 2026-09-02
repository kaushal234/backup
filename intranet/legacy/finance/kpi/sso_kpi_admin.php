<?php
include_once('common.inc.php');
$user = new tldUser($_SERVER['PHP_AUTH_USER']);

if (!$user->isInGroup(['superuser', 'role_SPM', 'gg_ACCT'])) {
    echo 'You do not have permissions for this page..';
    exit;
}

$main_tpl = [
	[
		'table' => 'locations_kpi_sso',
		'form_type' => 'main_tpl',
		'title' => 'Maintain KPI SSO',
		'cancel' => 'table&form_type=main_tpl',
		'mode' => 'record_view',
		'default_sort' => 'id',
	],
    ['name' => 'id', 'label' => 'Primary Key', 'type' => 'primary_key', 'table' => 'true'],
    ['name' => 'parent_id', 'label' => 'Foreign Key', 'type' => 'foreign_key', 'table' => 'false'],
	[
		'name' => 'parent_id',
		'label' => 'Location',
		'type' => 'lookup',
		'table' => 'true',
		'dump' => 'true',
		'select_query' =>"SELECT id,location FROM locations WHERE erp != '' ORDER BY location",
		'select_field_1' => 'id',
		'select_field_2' => 'location',
		'select_query_view' => 'SELECT * FROM locations WHERE id=',
		'select_field_view' => 'location',
	],
    ['name' => 'ynam', 'label' => 'Year', 'type' => 'text', 'table' => 'true', 'dump' => 'true'],
    ['name' => 'mnam', 'label' => 'Month', 'type' => 'select', 'table' => 'true', 'dump' => 'true', 'select_list' => ['01', '02', '03', '04', '05', '06', '07', '08', '09', '10', '11', '12']],
    ['name' => 'bypass', 'label' => '<b><i>Sales and Service Organization Section</i></b>', 'type' => 'title', 'table' => 'false'],
    ['name' => 'sso_tot_sls', 'label' => 'Total Sales', 'type' => 'text', 'table' => 'true', 'dump' => 'true'],
    ['name' => 'sso_tot_ar', 'label' => 'Accounts Receivables', 'type' => 'text', 'table' => 'true', 'dump' => 'true'],
    ['name' => 'sso_fin_gds', 'label' => 'Finished Goods', 'type' => 'text', 'table' => 'true', 'dump' => 'true'],
    ['name' => 'bypass', 'label' => '<b><i>Spare Parts Hub Section</i></b>', 'type' => 'title', 'table' => 'false'],
    ['name' => 'sph_tot_sls', 'label' => 'Total Sales', 'type' => 'text', 'table' => 'true', 'dump' => 'true'],
    ['name' => 'sph_net_inv_val', 'label' => 'NET Inventory Value', 'type' => 'text', 'table' => 'true', 'dump' => 'true']
];

$SMARTY_LOCATION = 'intranet';
$DEFAULT_TEMPLATE = 'intranet.tpl';
include('header.inc.php');
include('db_common2.inc.php');
if (!$id) $id = 0;
include('db_admin2.inc.php');
?>
