<?php
include_once 'eng.inc.php';
include_once 'forms_and_reports.inc.php';

$DEFAULT_TITLE .= "\Item Reservation";
$DEFAULT_MENU .= <<<EOF
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<a href="$php_self?m[0]=item_reservation">Home</a>
&nbsp;|&nbsp; <a href="$php_self?m[0]=item_reservation&m[1]=listing&m[2]=reserveItem">Reserve an item</a>
&nbsp;|&nbsp; <a href="$php_self?m[0]=item_reservation&m[1]=reports">Reports</a>
&nbsp;|&nbsp; <a href="/en/private/mis/mis.php?m[0]=help&m[1]=dms&id=730">Help</a>
EOF;

if (!$user->isInGroup(['gg_ENG', 'role_EM', 'gg_ADMIN'])) {
    $DEFAULT_ERROR[] = 'ERROR: You do not have intranet permission to access this module';
    return;
}

// check if user authorized for rapid entry or superuser
$query = "select count(*) as RapidEntry from tttaad231000 where t_cpac='ti' and t_cmod='edm' and t_cses='9101m910' and t_user='{$user->getBannUserID()}' ";
$rowsTmp1 = tldUtils::getSqlRowToAssocArray($query, 'odbc', ['src' => 'baan']);

$query = "select t_utyp as TypeUser from tttaad200000 where t_user='{$user->getBannUserID()}' ";
$rowsTmp2 = tldUtils::getSqlRowToAssocArray($query, 'odbc', ['src' => 'baan']);

if ($rowsTmp1['RapidEntry'] == 0 and $rowsTmp2['TypeUser'] != 1) {
    $DEFAULT_ERROR[] = 'ERROR: You do not have Baan permission to access this module';
    return;
}

switch ($m[1]) {
    case 'form':
        switch ($m[3]) {
            case 'xls':
                $report = new tldXLS(
                    $rows,
                    [
                        'xItems' => $xItems,
                        'showTitles' => true,
                    ]
                );
                $report->out();
                exit;
            case 'csv':
                $report = new tldCSV(
                    $rows,
                    [
                        'xItems' => $xItems,
                        'showTitles' => true,
                    ]
                );
                $report->out();
                exit;
            default:
                $report = new tldReportColumnar($rows,
                    [
                        'xItems' => $xItems,
                        'title' => $caption,
                        'links' => $links,
                    ]
                );
                $body .= $report->fetch();
                break;
        }
        break;
    case 'reports':
        switch ($m[2]) {
            case 'OriginReservationAuditUser':
                $xItems = [
                    't_user' => 'User',
                    't_comp' => 'Company',
                    't_web' => 'Intranet',
                    't_baan' => 'Baan',
                ];

                $form = new HTML_QuickForm('frmByNum', 'post');
                $form->addElement('hidden', 'm[0]', 'item_reservation');
                $form->addElement('hidden', 'm[1]', 'reports');
                $form->addElement('hidden', 'm[2]', 'OriginReservationAuditUser');
                $form->addElement('header', 'title', 'Origin reservation audit by user');
                $form->addElement('date', 'x', 'Date from', ['format' => 'Y-m-d', 'minYear' => date('Y') - 5, 'maxYear' => date('Y')]);
                $form->addElement('date', 'y', 'Date to', ['format' => 'Y-m-d', 'minYear' => date('Y') - 5, 'maxYear' => date('Y')]);
                $form->addElement('submit', 'btnSubmit', 'Submit');
                $form->setDefaults(['erp' => $DEFAULT_ERP, 'x' => date('Y-m-01'), 'y' => date('Y-m-d')]);

                if ($form->validate()) {
                    $vars = tldUtils::cleanupFormInput($form->exportValues());
                    $vars['from'] = implode('-', $vars['x']);
                    $vars['to'] = implode('-', $vars['y']);
                    $vars['$dateTmpF'] = $vars['x']['Y'] . '-' . substr('00' . $vars['x']['m'], -2) . '-' . substr('00' . $vars['x']['d'], -2);
                    $vars['$dateTmpT'] = $vars['y']['Y'] . '-' . substr('00' . $vars['y']['m'], -2) . '-' . substr('00' . $vars['y']['d'], -2);
                    $rows = tldUtils::getSqlToAssocArray("EXEC EdmReservationByUser '{$vars['$dateTmpF']}','{$vars['$dateTmpT']}'", 'odbc', ['src' => 'baan']);
                    $caption = 'Origin reservation audit by user';
                }

                $body .= $form->toHTML();
                $report = new tldReportColumnar($rows,
                    [
                        'xItems' => $xItems,
                        'title' => $caption,
                        'links' => $links,
                    ]
                );
                $body .= $report->fetch();
                break;
            case 'OriginReservationAuditComp':
                $xItems = [
                    't_comp' => 'Company',
                    't_web' => 'Intranet',
                    't_baan' => 'Baan',
                ];

                $form = new HTML_QuickForm('frmByNum', 'post');
                $form->addElement('hidden', 'm[0]', 'item_reservation');
                $form->addElement('hidden', 'm[1]', 'reports');
                $form->addElement('hidden', 'm[2]', 'OriginReservationAuditComp');
                $form->addElement('header', 'title', 'Origin reservation audit by company');
                $form->addElement('date', 'x', 'Date from', ['format' => 'Y-m-d', 'minYear' => date('Y') - 5, 'maxYear' => date('Y')]);
                $form->addElement('date', 'y', 'Date to', ['format' => 'Y-m-d', 'minYear' => date('Y') - 5, 'maxYear' => date('Y')]);
                $form->addElement('submit', 'btnSubmit', 'Submit');
                $form->setDefaults(['erp' => $DEFAULT_ERP, 'x' => date('Y-m-01'), 'y' => date('Y-m-d')]);


                if ($form->validate()) {
                    $vars = tldUtils::cleanupFormInput($form->exportValues());

                    $vars['from'] = implode('-', $vars['x']);
                    $vars['to'] = implode('-', $vars['y']);

                    $vars['$dateTmpF'] = $vars['x']['Y'] . '-' . substr('00' . $vars['x']['m'], -2) . '-' . substr('00' . $vars['x']['d'], -2);
                    $vars['$dateTmpT'] = $vars['y']['Y'] . '-' . substr('00' . $vars['y']['m'], -2) . '-' . substr('00' . $vars['y']['d'], -2);


                    $rows = tldUtils::getSqlToAssocArray("EXEC EdmReservationByComp '{$vars['$dateTmpF']}','{$vars['$dateTmpT']}'", 'odbc', ['src' => 'baan']);
                    $caption = 'Origin reservation audit by company';
                }

                $body .= $form->toHTML();

                $report = new tldReportColumnar($rows,
                    [
                        'xItems' => $xItems,
                        'title' => $caption,
                        'links' => $links,
                    ]
                );
                $body .= $report->fetch();

                break;
            case 'ItemReservationsbyUser':
                $xItems = [
                    'eitm' => 'Reserved Item',
                    'dsca' => 'Description',
                    'userr' => 'User',
                    'comp' => 'Company',
                    'rdat' => 'Reservation date',
                    'stat' => 'Status',
                    'orig' => 'Origin',
                    'SEQ' => 'Sequence',
                    'STATUS' => 'Status',
                    'Search' => 'Search',
                ];

                // Get listing
                $erpList = tldLocation::getERPList('smartyOptions');
                // Get form
                $form = new HTML_QuickForm('frmItemReservationsbyUser', 'get', '', '', '', true);
                $form->addElement('hidden', 'm[0]', 'item_reservation');
                $form->addElement('hidden', 'm[1]', 'reports');
                $form->addElement('hidden', 'm[2]', 'ItemReservationsbyUser');
                $form->addElement('header', 'title', 'Item Reservations by User:');
                $form->addElement('select', 'stat', 'Status',
                    ['ALL' => 'ALL', 'Reserved' => 'Reserved', 'Completed' => 'Completed']);
                $form->addElement('select', 'z', 'Company#', ['ALL' => 'ALL'] + $erpList);
                $form->addElement('date', 'x', 'Date from',
                    ['format' => 'Y-m-d', 'minYear' => date('Y') - 5, 'maxYear' => date('Y')]);
                $form->addElement('date', 'y', 'Date to',
                    ['format' => 'Y-m-d', 'minYear' => date('Y') - 5, 'maxYear' => date('Y')]);
                $form->setDefaults(['x' => date('Y-m-01')]);
                $form->setDefaults(['y' => date('Y-m-d')]);
                $form->setDefaults(['z' => 'ALL']);

                $edmerp = in_array((int)$DEFAULT_ERP, [250, 220], true) ? $DEFAULT_ERP : 400;

                $query = "SELECT DISTINCT t_user as userr from ttiedm905$edmerp";
                $users = array_column(tldUtils::getSqlToAssocArray($query, 'odbc', ['src' => 'baan']), 'userr');

                $form->addElement('select', 'user', 'User', ['ALL' => 'ALL'] + array_combine($users, $users));
                $form->addRule('user', 'Required', 'required');
                $form->addElement('text', 'limit', 'Limit');
                $form->addElement('submit', 'btnSubmit', 'Submit');

                $form->setDefaults(
                    [
                        'limit' => '100',
                        'user' => $user->getBannUserID(),
                    ]
                );

                if ($form->validate()) {
                    $vars = tldUtils::cleanupFormInput($form->exportValues());


                    $vars['from'] = implode('-', $vars['x']);
                    $vars['to'] = implode('-', $vars['y']);

                    $vars['$dateTmpF'] = $vars['x']['Y'] . '-' . substr('00' . $vars['x']['m'], -2) . '-' . substr('00' . $vars['x']['d'], -2);
                    $vars['$dateTmpT'] = $vars['y']['Y'] . '-' . substr('00' . $vars['y']['m'], -2) . '-' . substr('00' . $vars['y']['d'], -2);

                    $query = <<<SQL
select
    top {$vars['limit']} F1.t_eitm as eitm, 
    F1.t_dsca as dsca,
    F1.t_user as userr, 
    SUBSTRING(convert(varchar, F1.t_rdat, 120), 0, 11) as rdat,
    F1.t_stat as stat,
    (select CASE count(tld999.t_key1) WHEN null then 'Baan' WHEN 0 then 'Baan' ELSE 'Web' END FROM ttctld999500 tld999 WHERE tld999.t_key1 = F1.t_user and tld999.t_key2 = F1.t_eitm) as orig,
    (select aad200.t_comp from tttaad200000 aad200 where aad200.t_user = F1.t_user ) as comp
FROM ttiedm905$edmerp F1
WHERE 1=1 AND F1.t_rdat>='{$vars['$dateTmpF']}' AND F1.t_rdat<='{$vars['$dateTmpT']}'
SQL;
                    if ($vars['stat'] !== 'ALL') {
                        $query .= "and F1.t_stat='{$vars['stat']}' ";
                    }
                    if ($vars['user'] !== 'ALL') {
                        $query .= "and F1.t_user='{$vars['user']}' ";
                    }
                    if ($vars['z'] !== 'ALL') {
                        $query .= "and (select aad200.t_comp from tttaad200000 aad200 where aad200.t_user = F1.t_user ) = {$vars['z']}";
                    }

                    $query .= 'order by F1.t_rdat desc ';
                    $rows = tldUtils::getSqlToAssocArray($query, 'odbc', ['src' => 'baan']);
                    $caption = 'Item Reservations by user';
                }


                foreach ($rows as &$value) {
                    // Link to sequence:
                    $rowsTmp1 = tldUtils::getSqlRowToAssocArray("select max(parent_id) as parent_id from mod_keys where module='SEQ' and type='eng.newpartnb' and key1='{$value['comp']}' and key2='{$value['eitm']}'");
                    if (trim($rowsTmp1['parent_id']) == '') {
                        $value['SEQ'] = "<a href='https://www.tld-gse.com/en/private/calendar/calendar.php?m[0]=seq&m[1]=new&m[2]=eng.newpartnb&pn=" . trim($value['eitm']) . '&bu_id=' . trim($value['comp']) . "'>Create sequence</a>";
                        $value['STATUS'] = '';
                    } else {
                        $value['SEQ'] = "<a href='https://www.tld-gse.com/en/private/calendar/calendar.php?m[0]=tasks&m[1]=task&m[2]=view&single=1&id=" . $rowsTmp1['parent_id'] . "'>View sequence</a>";
                        // get SEQ status
                        $rowsTmp2 = tldUtils::getSqlRowToAssocArray("select max(status) as status from tasks where id='{$rowsTmp1['parent_id']}'");
                        $value['STATUS'] = $rowsTmp2['status'];
                    }
                    // Add search text
                    $rowsTmp3 = tldUtils::getSqlRowToAssocArray("select t_mesg from ttctld999500 where t_stat='ok' and t_prog='titldedm92011' and t_key1='{$value['userr']}' and t_key2='{$value['eitm']}'", 'odbc', ['src' => 'baan']);
                    $value['Search'] = $rowsTmp3['t_mesg'];
                }

                $body .= $form->toHTML();
                $report = new tldReportColumnar($rows,
                    [
                        'xItems' => $xItems,
                        'title' => $caption,
                        'links' => $links,
                    ]
                );
                $body .= $report->fetch();
                break;
            default:
                $body = $smarty->fetch("$PATH/item_reservation/reports/homepage.reports.tpl");
        }
        break;
    case 'listing':
        // Get default ERP for connected user
        $location = new tldLocation($user->getBUID());
        switch ($m[2]) {
            case 'reserveItem':
                // Step description
                $body .= $smarty->fetch("$PATH/item_reservation/step1.item_reservation.tpl");
                // Report columns
                $xItems = [
                    't_comp' => 'Company',
                    't_item' => 'Item',
                    't_dsca' => 'Description',
                    't_dscb' => 'XREF desc.',
                    't_kitm' => 'Item Type',
                    't_citg' => 'Item Group',
                    't_ctyp' => 'Product Type',
                    't_csel' => 'Owner',
                    't_csig' => 'Signal Code',
                    't_engi' => 'Engineer',
                    't_cuni' => 'Unit',
                    'PMOC' => 'PMOC',
                    't_cpha' => 'Phantom',
                    't_draw' => 'Drawing',
                ];
                $erpList = tldLocation::getERPList('smartyOptions');
                $form1 = new HTML_QuickForm('frmreserveItem', 'get', '', '', '', true);
                $form1->addElement('hidden', 'm[0]', 'item_reservation');
                $form1->addElement('hidden', 'm[1]', 'listing');
                $form1->addElement('hidden', 'm[2]', 'reserveItem');
                $form1->addElement('header', 'title', 'Select search text:');
                $form1->addElement('text', 'SearchText', 'Search text');
                $form1->addElement('text', 'MaxResult', 'Max results');
                $form1->addElement('submit', 'btnSubmit', 'Submit', ['class' => 'disablesubmit']);
                $form1->addRule('z', 'This is required', 'required');

                $form1->setDefaults(['SearchText' => '', 'MaxResult' => '100']);
                $cell = "<br><a href='dev.php?m[0]=item_reservation&m[1]=listing&m[2]=doReservation'>Reserve a new item?</a><br>";

                $linkReserve = false;
                $_SESSION['SearchText'] = '';
                if ($form1->validate()) {
                    $linkReserve = true;
                    $vars = tldUtils::cleanupFormInput($form1->exportValues());
                    $vars['SearchText'] = '%' . $vars['SearchText'] . '%';
                    $_SESSION['SearchText'] = $vars['SearchText'];
                    $rows = tldUtils::getSqlToAssocArray("EXEC Search_Tool_Magic '{$vars['SearchText']}','{$vars['MaxResult']}', '0'", 'odbc', ['src' => 'baan']);
                    $caption = '';
                    $today = date('Y-m-d');
                    $cell .= <<<HTML
<br><br>

<div class="columnar">
<table class="sortable" cellpadding="3">
    <thead>
        <tr>
            <th>Company</th>
            <th>Item</th>
            <th>Description</th>
            <th>XREF desc.</th>
            <th>Item Type</th>
            <th>Item Group</th>
            <th>Product Type</th>
            <th>Owner</th>
            <th>Signal Code</th>
            <th>Engineer</th>
            <th>Unit</th>
            <th>PMOC</th>
            <th>Phantom</th>
            <th>Drawing</th>
        </tr>
    </thead>
<tbody style="font-family: Arial, Helvetica, sans-serif; font-size: 13px;color: black; font-weight: normal;">
HTML;
                    $flag = true;
                    foreach ($rows as $row) {
                        $color = $flag ? '#eeeeee' : '#d0d0d0';
                        $flag = !$flag;
                        $cell .= <<<HTML
<tr bgcolor="$color">
    <td>{$row['t_comp']}</td>
    <td>{$row['t_item']}</td>
    <td>{$row['t_dsca']}</td>
    <td>{$row['t_dscb']}</td>
    <td>{$row['t_kitm']}</td>
    <td>{$row['t_citg']}</td>
    <td>{$row['t_ctyp']}</td>
    <td>{$row['t_csel']}</td>
    <td>{$row['t_csig']}</td>
    <td>{$row['t_engi']}</td>
    <td>{$row['t_cuni']}</td>
    <td>{$row['PMOC']}</td>
    <td>{$row['t_cpha']}</td>
    <td>
        <a target="_blank" href="/en/private/manufacturing/eng/dev.php?m[0]=getfile&m[1]=drawing&date={$today}&item={$row['t_item']}&erp={$row['t_comp']}">
            <img src="shared/bluesphere/16x16/actions/filesaveas.png" alt="Save file to your hard disk">
        </a>
    </td>
</tr>
HTML;
                    }
                    $cell .= '</tbody></table><br><br>';
                }
                $body .= $form1->toHTML();
                $body .= $cell;
                break;
            case 'doReservation':
                echo $submitted . '<br>';
                echo $DSCA . '<br>';
                $body .= $smarty->fetch("$PATH/item_reservation/step2.item_reservation.tpl.php");

                if ($submitted === 'Y') {
                    if (strlen($DSCA) > 0) {
                        $a = [
                            'erp' => $location->getERP(),
                            't_dsca' => $DSCA,
                            't_user' => $user->getBannUserID(),
                            'search' => $_SESSION['SearchText'],
                        ];
                        $a = array_map('stripslashes', $a);
                        tldITM::postEdmItemReservation($location->getERP(), $a);

                        $m[2] = 'myItems';
                        header("location: $php_self?_qf__frmreserveItem=&m[0]=item_reservation&m[1]=listing&m[2]=myItems&stat=Reserved&user={$user->getBannUserID()}&limit=200&btnSubmit=Submit");
                        break;
                    }
                }

                break;
            case 'myItems':
                $xItems = [
                    'eitm' => 'Reserved Item',
                    'dsca' => 'Description',
                    'userr' => 'User',
                    'rdat' => 'Reservation date',
                    'stat' => 'Item Status',
                    'SEQ' => 'Sequence',
                    'STATUS' => 'Seq Status',
                ];

                $erpList = tldLocation::getERPList('smartyOptions');
                $form = new HTML_QuickForm('frmitem_reservation', 'get', '', '', '', true);
                $form->addElement('hidden', 'm[0]', 'item_reservation');
                $form->addElement('hidden', 'm[1]', 'listing');
                $form->addElement('hidden', 'm[2]', 'myItems');
                $form->addElement('header', 'title', 'EDM Actual reservations:');
                $form->addElement('select', 'stat', 'Status', ['Reserved' => 'Reserved', 'Completed' => 'Completed']);
                $edmerp = in_array((int)$DEFAULT_ERP, [250, 220], true) ? $DEFAULT_ERP : 400;
                $users = array_column(tldUtils::getSqlToAssocArray("SELECT DISTINCT t_user AS userr FROM ttiedm905$edmerp", 'odbc', ['src' => 'baan']), 'userr');
                $form->addElement('select', 'user', 'User', ['ALL' => 'ALL'] + array_combine($users, $users));
                $form->addRule('user', 'Required', 'required');
                $form->addElement('text', 'limit', 'Limit');
                $form->addElement('submit', 'btnSubmit', 'Submit');
                $form->setDefaults(['limit' => '100', 'user' => $user->getBannUserID()]);

                if ($form->validate()) {
                    $vars = tldUtils::cleanupFormInput($form->exportValues());

                    $query = <<<SQL
 SELECT 
    top {$vars['limit']} F1.t_eitm as eitm,
    F1.t_dsca as dsca,
    F1.t_user as userr,
    SUBSTRING(convert(varchar, F1.t_rdat, 120), 0, 11) as rdat,
    F1.t_stat as stat,
    (select aad200.t_comp from tttaad200000 aad200 WHERE aad200.t_user = F1.t_user ) as comp FROM ttiedm905400 F1
WHERE F1.t_stat='{$vars['stat']}'
SQL;

                    if ($vars['user'] !== 'ALL') {
                        $query .= "and F1.t_user='{$vars['user']}' ";
                    }
                    $query .= 'order by F1.t_rdat desc, F1.t_eitm desc ';
                    $rows = tldUtils::getSqlToAssocArray($query, 'odbc', ['src' => 'baan']);
                    $caption = 'Actual reservations';

                    // Link to sequence:
                    foreach ($rows as &$value) {
                        $rowsTmp1 = tldUtils::getSqlRowToAssocArray("SELECT MAX(parent_id) AS parent_id FROM mod_keys WHERE module='SEQ' AND type='eng.newpartnb' AND key1='{$value['comp']}' AND key2='{$value['eitm']}'");
                        if (trim($rowsTmp1['parent_id']) == '') {
                            $value['SEQ'] = "<a href='https://www.tld-gse.com/en/private/calendar/calendar.php?m[0]=seq&m[1]=new&m[2]=eng.newpartnb&pn=" . trim($value['eitm']) . '&bu_id=' . trim($value['comp']) . "'>Create sequence</a>";
                            $value['STAping TUS'] = '';
                        } else {
                            $value['SEQ'] = "<a href='https://www.tld-gse.com/en/private/calendar/calendar.php?m[0]=tasks&m[1]=task&m[2]=view&single=1&id=" . $rowsTmp1['parent_id'] . "'>View sequence</a>";
                            // get SEQ status
                            $rowsTmp2 = tldUtils::getSqlRowToAssocArray("SELECT MAX(status) AS status FROM tasks WHERE id='{$rowsTmp1['parent_id']}'");
                            $value['STATUS'] = $rowsTmp2['status'];
                        }
                    }
                }
                $body = $smarty->fetch("$PATH/item_reservation/step3.item_reservation.tpl");
                $body .= $form->toHTML();

                $report = new tldReportColumnar($rows,
                    [
                        'xItems' => $xItems,
                        'title' => $caption,
                        'links' => $links,
                    ]
                );
                $body .= $report->fetch();
                break;
        }
        break;
    default:
        $body .= $smarty->fetch("$PATH/item_reservation/homepage.item_reservation.tpl");
        $rows = tldUtils::getSqlToAssocArray('select F1.t_stat as stat, F2.t_comp as comp, count(*) as num from ttiedm905400 F1,  tttaad200000 F2 where F2.t_user = F1.t_user  group by F1.t_stat,  F2.t_comp', 'odbc', ['src' => 'baan']);

        $form = new tldMatrix($rows,
            'stat', 'comp', 'num',
            "$php_self?m[0]=item_reservation&m[1]=listing&m[2]=myItems",
            'Item reservation by Company'
        );
        $body .= $form->fetch();
}
