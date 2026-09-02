<?php

declare(strict_types=1);
session_start();
if (!isset($_SESSION['sess'])) {
    $_SESSION['sess'] = null;
}
$sess = &$_SESSION['sess'];

include_once 'common.inc.php';
include_once 'dms.inc.php';

$user = unserialize($sess['dms']['user']);
if (!is_a($user, 'tldUser') || empty($user)) {
    echo 'You must log in in DMS portal and be superuser to use the DMS admin';
    exit;
}

// Listing
$statusList = tldDMS::getStatusList();
$statusList = array_combine($statusList, $statusList);
$typeList = tldUtils::optionsByKeyValue(tldDMSType::getList(), 'id', 'short_desc');
$periodicityList = tldDMS::getPeriodicityList();
$portalList = tldDMS::getPortalList();
$accessTypeList = tldDMS::getAccessTypeList();

$main_tpl = [
    [
        'table' => 'dms',
        'form_type' => 'main_tpl',
        'title' => 'DMS',
        'cancel' => 'table&form_type=',
        'mode' => 'record_view',
        'prn_table_menu' => '<a href="/index.php?m[0]=admin">Back to Module</a>&nbsp;|&nbsp;',
        'prn_record_menu' => '<b><a href="/index.php?m[0]=view&id={id}">Back to Module</a></b>&nbsp;|&nbsp;',
        'default_sort' => 'id',
    ],
    ['name' => 'id',           'label' => 'DMS#',               'type' => 'primary_key',      'table' => 'true',    'dump' => 'true'],
    ['name' => 'type_id',       'label' => 'Type',              'type' => 'select',           'table' => 'true',    'dump' => 'true',
        'select_list' => $typeList, 'source' => 'smartyOptions', ],
    ['name' => 'status',       'label' => 'Status',              'type' => 'select',           'table' => 'true',    'dump' => 'true',
        'select_list' => $statusList, 'source' => 'smartyOptions', ],
    ['name' => 'periodicity',       'label' => 'Periodicity',              'type' => 'select',           'table' => 'true',    'dump' => 'true',
        'select_list' => $periodicityList, 'source' => 'smartyOptions', ],
    ['name' => 'access_type',       'label' => 'Access type',              'type' => 'select',           'table' => 'true',    'dump' => 'true',
        'select_list' => $accessTypeList, 'source' => 'smartyOptions', ],
    ['name' => 'portal',       'label' => 'Portal',              'type' => 'select',           'table' => 'true',    'dump' => 'true',
        'select_list' => $portalList, 'source' => 'smartyOptions', ],
];

echo "<h2 style=color:red;' Warning: if you update the status, please make sure that all the sequences linked to the DMS are cancelled'</h2><br/>";

if (!empty($sess_error_message)) {
    echo "<p style=\"color:red;text-align:center;\">$sess_error_message</p><br/>";
}

include 'header.inc.php';
include 'db_common2.inc.php';
$SMARTY_LOCATION = 'intranet';
$DEFAULT_TEMPLATE = 'intranet.tpl';
if (!$id) {
    $id = 0;
}

if ($user->isInGroup(['superuser'])) {
    include 'db_admin2.inc.php';
} else {
    include 'db_readonly2.inc.php';
}
