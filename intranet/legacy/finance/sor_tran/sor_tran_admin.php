<?php
include('common.inc.php');
$user = new tldUser($GLOBALS['PHP_AUTH_USER']);
if (!$user->isInGroup(['superuser', 'gg_ADMIN', 'role_SA', 'gg_ACCT'])) {
	echo '<span style="color:red">ERROR: You do not have permission to access this page.</span>';
	exit();
}

$main_tpl = [
	['table' => 'sor_tran', 'form_type' => 'main_tpl', 'title' => 'SOR Transactions',
		'cancel' => 'table&form_type=main_tpl', 'mode' => 'record_view',
		'prn_table_menu' => '<a href="/en/private/finance/finance.php?m[0]=sor_tran">Back to Module</a>',
//	"prn_record_menu"=>"<a href=\"/en/private/calendar/calendar.php?m[0]=tasks&m[1]=task&m[2]=view&id={id}\">Back to Task</a>&nbsp;|&nbsp;",
//	"child_tables"=>array("tasks_comments"),
		'default_sort' => 'id'],
	['name' => 'id', 'label' => 'Primary Key', 'type' => 'primary_key', 'table' => 'true'],
	['name' => 'parent_id', 'label' => 'SOL#', 'type' => 'text', 'table' => 'true'],
	['name' => 'dt', 'label' => 'Date<br>(YYYY-MM-DD)', 'type' => 'auto_date', 'table' => 'true', 'dump' => 'true'],
	['name' => 'nref', 'label' => 'Ref Number (Invoice/SO)', 'type' => 'text', 'table' => 'true', 'dump' => 'true'],
	['name' => 'dref', 'label' => 'Date of Ref (YYYY-MM-DD)', 'type' => 'date', 'table' => 'true'],
	['name' => 'dtran', 'label' => 'Date, Trans (YYYY-MM-DD)', 'type' => 'date', 'table' => 'true'],
	['name' => 'tgrp', 'label' => 'Group, SSO=Sales Org, ERP=Factory', 'type' => 'select', 'table' => 'true', 'dump' => 'true',
		'select_list' => ['SSO', 'ERP'],
	],
	['name' => 'ttyp', 'label' => 'Type (B)ooking or (R)evenue', 'type' => 'select', 'table' => 'true', 'dump' => 'true',
		'select_list' => ['B', 'R'],
	],
	['name' => 'tcur', 'label' => 'Currency', 'type' => 'select', 'table' => 'true', 'dump' => 'true',
		'source' => 'lists', 'list_name' => 'list.common.currency'],
	['name' => 'tval', 'label' => 'Transaction Value', 'type' => 'text', 'table' => 'true'],
	['name' => 'notes', 'label' => 'Notes', 'type' => 'textarea', 'table' => 'false',
		'textarea_params' => ' wrap="VIRTUAL" cols="40" rows="7"'],
];


switch ($form_type) {
	case 'main_tpl':
		switch ($mode) {
			case 'form_edit':
			case 'update':
				if (!empty($_GET['id'])) {
					$id = $_GET['id'];
				} else {
					$id = $_POST['id'][0];
				}
				$_ERROR = 'Edit not allowed in admin (here), please go back to module, then edit';
				$mode = 'record_view';
				$form_type = 'main_tpl';
				break;
		}
		break;
}


$SMARTY_LOCATION = 'intranet';
$DEFAULT_TEMPLATE = 'intranet.tpl';

include_once('header.inc.php');

if (!empty($_ERROR)) {
	echo '<p style="color:red;">' . $_ERROR . '</p>';
}

//Include common db functions
include('db_common2.inc.php');
if (!$id) {
	$id = 0;
}
//Include db functions
include('db_admin2.inc.php');

