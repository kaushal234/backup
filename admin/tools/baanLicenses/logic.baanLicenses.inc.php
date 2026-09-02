<?php
include_once('erp.inc.php');

$DEFAULT_TITLE .= "\BaanLicenses";
$DEFAULT_MENU .= <<<EOF
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<a href="$php_self?m[0]=baanLicenses">Home</a>
&nbsp;|&nbsp;<a href="$php_self?m[0]=baanLicenses&m[1]=form&m[2]=LicDash">Licences Dashboard</a>
&nbsp;|&nbsp;<a href="$php_self?m[0]=baanLicenses&m[1]=form&m[2]=RawLicensesData">Display Raw Licenses Data</a>
&nbsp;|&nbsp;<a href="$php_self?m[0]=baanLicenses&m[1]=form&m[2]=WrongUsersSetting">Wrong Users Setting</a>
&nbsp;|&nbsp;<a href="$php_self?m[0]=baanLicenses&m[1]=form&m[2]=ActLicUs">Actual Licences Usage</a>
EOF;

if (!$user->isInGroup(['superuser'])) {
    $DEFAULT_ERROR[] = 'ERROR: You do not have permissions';
    return;
}

switch ($m[1]) {
    case 'form':
        switch ($m[2]) {
            case 'ActLicUs':
                $report = new tldAssocTable(
                    tldUtils::getSqlRowToAssocArray('SELECT COUNT(*) AS cnt, dt FROM erp_licences where dt = (SELECT dt FROM erp_licences ORDER by id DESC LIMIT 1)'),
                    ['cnt' => 'Number of licence used', 'dt' => 'Last record'],
                    ['title' => 'Baan licences usage']
                );
                $body .= $report->fetch();

                $report = new tldReportColumnar(
                    tldUtils::getSqlToAssocArray('SELECT baan_id, COUNT(*) AS cnt FROM erp_licences where dt = (SELECT dt FROM erp_licences ORDER by id DESC LIMIT 1) GROUP BY baan_id HAVING cnt > 1'),
                    [
                        'xItems' => [
                            'baan_id' => 'Account',
                            'cnt' => 'Licence Number',
                        ],
                        'title' => 'Multiple licences:',
                    ]
                );
                $body .= $report->fetch();
                break;
            case 'RawLicensesData': // report
                $users = ['ALL' => 'ALL'] + array_column(tldUtils::getSqlToAssocArray('SELECT DISTINCT baan_id FROM erp_licences ORDER BY baan_id ASC'), 'baan_id', 'baan_id');
                $baanComp = ['ALL' => 'ALL'] + array_column(tldUtils::getSqlToAssocArray("SELECT DISTINCT baan_comp FROM erp_licences WHERE baan_comp IS NOT NULL AND baan_comp != '' ORDER BY baan_comp ASC"), 'baan_comp', 'baan_comp');
                $department = ['ALL' => 'ALL'] + array_column(tldUtils::getSqlToAssocArray('SELECT DISTINCT department FROM erp_licences ORDER BY department ASC'), 'department', 'department');

                $form = new HTML_QuickForm('frmRawData', 'post');
                $form->addElement('hidden', 'm[0]', 'baanLicenses');
                $form->addElement('hidden', 'm[1]', 'form');
                $form->addElement('hidden', 'm[2]', 'RawLicensesData');
                $form->addElement('header', 'header', 'Raw Licenses Data');

                $form->addElement('date', 'x', 'From', ['format' => 'Y-m-d', 'minYear' => date('Y') - 2, 'maxYear' => date('Y') + 2]);
                $form->setDefaults(['x' => date('Y-m-d')]);
                $form->addElement('date', 'y', 'To', ['format' => 'Y-m-d', 'minYear' => date('Y') - 2, 'maxYear' => date('Y') + 2]);
                $form->setDefaults(['y' => date('Y-m-d')]);

                $form->addElement('select', 'user', 'User', ['ALL' => 'ALL'] + $users);
                $form->addElement('select', 'comp', 'Company', ['ALL' => 'ALL'] + $baanComp);
                $form->addElement('select', 'dept', 'Department', ['ALL' => 'ALL'] + $department);
                $form->addElement('text', 'limit', 'Limit');
                $form->setDefaults(['limit' => '100']);
                $form->addElement('submit', 'btnSubmit', 'Submit');
                $body = $form->toHTML();

                $vars = tldUtils::cleanupFormInput($form->exportValues());
                $vars['from'] = implode('-', $vars['x']) . ' 00:00:00';
                $vars['to'] = implode('-', $vars['y']) . ' 23:59:59';
                $query = "SELECT licences, baan_id, baan_comp, department, dt FROM erp_licences WHERE dt>='{$vars['from']}' AND dt<='{$vars['to']}'";
                if ('ALL' !== $vars['user']) {
                    $query .= " AND baan_id='{$vars['user']}'";
                }
                if ('ALL' !== $vars['comp']) {
                    $query .= " AND baan_comp='{$vars['comp']}'";
                }
                if ('ALL' !== $vars['dept']) {
                    $query .= " AND department='{$vars['dept']}'";
                }

                $query .= " LIMIT 0,{$vars['limit']}";

                $rows = tldUtils::getSqlToAssocArray($query);

                $report = new tldReportColumnar($rows);
                $body .= $report->fetch();
                break;
            case 'RawLicensesDetail':
                $form = new HTML_QuickForm('frmRawData', 'post');
                $form->addElement('hidden', 'm[0]', 'baanLicenses');
                $form->addElement('hidden', 'm[1]', 'form');
                $form->addElement('hidden', 'm[2]', 'RawLicensesDetail');
                $form->addElement('header', 'header', 'Raw Licenses Detail');
                $form->addElement('text', 'x', 'Date');
                $form->setDefaults(['x' => urldecode($_GET['x'])]);
                $form->addElement('submit', 'btnSubmit', 'Submit');
                $body = $form->toHTML();

                $vars = tldUtils::cleanupFormInput($form->exportValues());

                $query = "SELECT licences, baan_id, baan_comp, department, dt FROM erp_licences WHERE dt='{$vars['x']}'";
                $rows = tldUtils::getSqlToAssocArray($query);

                $report = new tldReportColumnar($rows);
                $body .= $report->fetch();
                break;

            case 'WrongUsersSetting':
                $query = 'SELECT LTRIM(RTRIM(t_user)) as t_user, t_comp, LTRIM(RTRIM(t_name)) AS t_name FROM tttaad200000';
                $baanUsers = tldUtils::getSqlToAssocArray($query, 'odbc', ['src' => 'baan']);
                $baanUserList = array_column($baanUsers, 't_user');

                $query = "SELECT lastname, firstname, TRIM(baan_id) AS baan_id, hidden, disabled FROM people WHERE baan_id != ''";
                $intranetUsers = tldUtils::getSqlToAssocArray($query);
                $intranetBaanUserList = array_column($intranetUsers, 'baan_id');

                $xItems = [
                    't_user' => 'Baan User',
                    't_name' => 'Name',
                    't_comp' => 'Baan company',
                    'lastname' => 'Web Lastname',
                    'firstname' => 'Web Firstname',
                    'baan_id' => 'Web baan_id',
                    'comment' => 'Comment',
                    'last_baan_usage' => 'Last Baan usage',
                ];
                // Keys are preserved by the array_diff
                $baanUsersWithoutIntranet = array_diff($baanUserList, $intranetBaanUserList);
                $rows = [];
                foreach ($baanUsersWithoutIntranet as $key => $unused) {
                    $query = "SELECT MAX(dt) AS dt FROM erp_licences WHERE baan_id='{$baanUsers[$key]['t_user']}'";
                    $result = tldUtils::getSqlToAssocArray($query);
                    $rows[] = [
                        't_user' => $baanUsers[$key]['t_user'],
                        't_name' => $baanUsers[$key]['t_name'],
                        't_comp' => $baanUsers[$key]['t_comp'],
                        'comment' => 'Baan user not found in Intranet Directory',
                        'last_baan_usage' => isset($result[0]['dt']) ? $result[0]['dt'] : '',
                    ];
                }

                // Keys are preserved by the array_diff
                $intranetUserWithoutBaanId = array_diff($intranetBaanUserList, $baanUserList);
                foreach ($intranetUserWithoutBaanId as $key => $unused) {
                    if ($intranetUsers[$key]['disabled']) {
                        continue;
                    }

                    $query = "SELECT MAX(dt) AS dt FROM erp_licences WHERE baan_id='{$intranetUsers[$key]['baan_id']}'";
                    $result = tldUtils::getSqlToAssocArray($query);
                    $rows[] = [
                        'lastname' => $intranetUsers[$key]['lastname'],
                        'firstname' => $intranetUsers[$key]['firstname'],
                        'baan_id' => $intranetUsers[$key]['baan_id'],
                        'comment' => 'Intranet user with unknown baan_id',
                        'last_baan_usage' => isset($result[0]['dt']) ? $result[0]['dt'] : '',
                    ];

                }
                $report = new tldReportColumnar($rows, ['xItems' => $xItems, 'showItemNumbers' => true, 'title' => 'Wrong Users Setting']);
                $body .= $report->fetch();
                break;
            case 'LicDash':
                $form = new HTML_QuickForm('frmbaanLicenses', 'post');
                $form->addElement('hidden', 'm[0]', 'baanLicenses');
                $form->addElement('hidden', 'm[1]', 'form');
                $form->addElement('hidden', 'm[2]', 'LicDash');
                $form->addElement('header', 'title', 'Set options below');
                $form->addElement('date', 'date_from', 'From date', ['format' => 'Ymd', 'minYear' => date('Y') - 2, 'maxYear' => date('Y') + 2]);
                $form->addElement('date', 'date_to', 'To date', ['format' => 'Ymd', 'minYear' => date('Y') - 2, 'maxYear' => date('Y') + 2]);

                $form->addRule('date_from', 'Required', 'required');
                $form->addRule('date_to', 'Required', 'required');

                $form->setDefaults(['date_from' => date('Y-m-d')]);
                $form->setDefaults(['date_to' => date('Y-m-d')]);

                $form->addElement('submit', 'btnSubmit', 'Generate');

                if (!$form->validate()) {
                    $body .= $form->toHTML();
                    return;
                }

                $vars = tldUtils::cleanupFormInput($form->exportValues());
                $vars['from'] = implode('-', $vars['date_from']) . ' 00-00-00';
                $vars['to'] = implode('-', $vars['date_to']) . ' 23-59-59';

                $body .= $form->toHTML();
                $body .= '<p><strong>Baan licenses analyse</strong></p>';

                $query = <<<SQL
        SELECT erp_licences.baan_id AS baan_id,
        erp_licences.baan_comp AS baan_comp,
        erp_licences.department AS dpt,
        erp_licences.licences AS licences,
        erp_licences.dt AS dt, 
        people.div_id AS div_id,
        people.bu_id AS bu_id,
        people.dpt_id AS dpt_id
        FROM erp_licences
        LEFT JOIN people ON erp_licences.baan_id=people.baan_id 
        WHERE dt >= '{$vars['from']}' AND dt <='{$vars['to']}';
SQL;
                $licenceDetails = tldUtils::getSqlToAssocArray($query);
                $erpRegions = [
                    220 => 'EUR',
                    250 => 'NAM',
                    300 => 'NAM',
                    400 => 'NAM',
                    410 => 'NAM',
                    420 => 'NAM',
                    500 => 'EUR',
                    510 => 'EUR',
                    520 => 'EUR',
                    540 => 'EUR',
                    560 => 'EUR',
                    570 => 'EUR',
                    600 => 'ASI',
                    620 => 'ASI',
                    640 => 'ASI',
                    660 => 'ASI',
                    680 => 'ASI',
                ];

                $erps = array_keys($erpRegions);
                $regions = array_unique($erpRegions);
                sort($regions);

                $departments = [
                    'Material control & Purchasing',
                    'Spare Parts',
                    'Finance & Accounting',
                    'Engineering',
                    'Production',
                    'Product Support',
                    'Quality Assurance',
                    'Sales & Service',
                    'Service',
                    'Management of Information System',
                    'Sales Administration',
                    'General Management',
                    'Human Ressources',
                    'Unknown',
                ];

                $html .= <<<HTML
<table cellpadding="3">
	<thead style="color: white; background-color: #2971A8;">
		<tr>
            <th>Date Time</th>
            <th>TOT Baan</th>
            <th>TOT N/A</th>
            <th style="background-color: white;">&nbsp;</th>
HTML;

                foreach ($regions as $region) {
                    $html .= "<th>TOT {$region}</th>";
                }

                $html .= '<th style="background-color: white;">&nbsp;</th>';
                foreach ($erps as $erp) {
                    $html .= "<th>$erp</th>";
                }
                $html .= '<th style="background-color: white;">&nbsp;</th>';
                foreach ($departments as $department) {
                    $html .= "<th>{$department}</th>";
                }

                $html .= <<<HTML
        </tr>
	</thead>
<tbody>
HTML;

                $results = [];
                $totals = ['counter' => 0, 'TOT Baan' => 0, 'TOT N/A' => 0] + array_fill_keys($regions, 0) + array_fill_keys($erps, 0) + array_fill_keys($departments, 0);

                foreach ($licenceDetails as $detail) {
                    if (!array_key_exists($detail['dt'], $results)) {
                        $results[$detail['dt']] = ['TOT Baan' => $detail['licences'], 'TOT N/A' => 0] + array_fill_keys($regions, 0) + array_fill_keys($erps, 0) + array_fill_keys($departments, 0);
                        ++$totals['counter'];
                        $totals['TOT Baan'] += $detail['licences'];
                    }

                    if (array_key_exists($detail['baan_comp'], $results[$detail['dt']])) {
                        ++$results[$detail['dt']][$detail['baan_comp']];
                        ++$results[$detail['dt']][$erpRegions[$detail['baan_comp']]];
                        ++$totals[$detail['baan_comp']];
                        ++$totals[$erpRegions[$detail['baan_comp']]];
                    } else {
                        // comp number = 0
                        ++$results[$detail['dt']]['TOT N/A'];
                        ++$totals['TOT N/A'];
                    }

                    $department = \in_array($detail['dpt'], $departments, true) ? $detail['dpt'] : 'Unknown';
                    ++$results[$detail['dt']][$department];
                    ++$totals[$department];
                }

                $bgColor = '#eeeeee';
                foreach ($results as $date => $values) {
                    $bgColor = $bgColor === '#eeeeee' ? '#d0d0d0' : '#eeeeee';
                    $dt = urlencode($date);
                    $html .= <<<HTML
            <tr style="background-color: $bgColor;">
                <td><a href='http://www.tld-gse.com/admin/tools/index.php?m[0]=baanLicenses&m[1]=form&m[2]=RawLicensesDetail&x=$dt'>$date</a></td>
                <td>{$values['TOT Baan']}</td>
                <td>{$values['TOT N/A']}</td>
            <td style="background-color: white;">&nbsp;</td>
HTML;
                    foreach ($regions as $region) {
                        $html .= "<td>{$values[$region]}</td>";
                    }

                    $html .= '<td style="background-color: white;">&nbsp;</td>';
                    foreach ($erps as $erp) {
                        $html .= "<td>{$values[$erp]}</td>";
                    }
                    $html .= '<td style="background-color: white;">&nbsp;</td>';
                    foreach ($departments as $department) {
                        $html .= "<td>{$values[$department]}</td>";
                    }

                    $html .= '</tr>';
                }
                $html .= <<<HTML
</tbody>
<tfoot style="color: white; background-color: #2971A8;">
    <tr>
        <td>AVG</td>
HTML;
                $html .= '<td>' . round($totals['TOT Baan'] / $totals['counter'], 0) . '</td>';
                $html .= '<td>' . round($totals['TOT N/A'] / $totals['counter'], 0) . '</td>';
                $html .= '<td style="background-color: white;">&nbsp;</td>';
                foreach ($regions as $region) {
                    $html .= '<td>' . round($totals[$region] / $totals['counter'], 0) . '</td>';
                }

                $html .= '<td style="background-color: white;">&nbsp;</td>';
                foreach ($erps as $erp) {
                    $html .= '<td>' . round($totals[$erp] / $totals['counter'], 0) . '</td>';
                }
                $html .= '<td style="background-color: white;">&nbsp;</td>';
                foreach ($departments as $department) {
                    $html .= '<td>' . round($totals[$department] / $totals['counter'], 0) . '</td>';
                }

                $html .= <<<HTML
    </tr>
    <tr>
        <td>AVG %</td>
        <td>100%</td>
HTML;

                $html .= '<td>' . round(($totals['TOT N/A'] / $totals['TOT Baan']) * 100, 0) . '%</td>';
                $realLicences = $totals['TOT Baan'] - $totals['TOT N/A'];

                $html .= '<td style="background-color: white;">&nbsp;</td>';
                foreach ($regions as $region) {
                    $html .= '<td>' . round($totals[$region] / $realLicences * 100, 0) . '%</td>';
                }

                $html .= '<td style="background-color: white;">&nbsp;</td>';
                foreach ($erps as $erp) {
                    $html .= '<td>' . round($totals[$erp] / $realLicences * 100, 0) . '%</td>';
                }
                $html .= '<td style="background-color: white;">&nbsp;</td>';
                foreach ($departments as $department) {
                    $html .= '<td>' . round($totals[$department] / $realLicences * 100, 0) . '%</td>';
                }
                $html .= '</tr>';
                $html .= '</tfoot>';
                $html .= '</table>';
                $html .= '</div>';
                $body .= $html;
                break;
        }
        break;
}
