<?php
include_once 'sales_service.inc.php';
include_once 'HTML/QuickForm/advmultiselect.php';

$mooID = tldModule::getMOOIDByModule('SB3');

$DEFAULT_TITLE .= "\SB3 Module";
$DEFAULT_MENU .= <<<EOF
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<a href="$php_self?m[0]=sb">Home</a>
&nbsp;|&nbsp;<a href="$php_self?m[0]=sb&m[1]=forms&m[2]=byNum">By number</a>
&nbsp;|&nbsp;<a href="$php_self?m[0]=sb&m[1]=listing&m[2]=search">Search</a>
&nbsp;|&nbsp;<a href="$php_self?m[0]=sb&m[1]=forms&m[2]=add">Add</a>
&nbsp;|&nbsp;<a href="$php_self?m[0]=sb&m[1]=reports">Reports</a>
&nbsp;|&nbsp;<a href="$php_self?m[0]=sb&m[1]=help">Help</a>
&nbsp;|&nbsp;<a href="/en/private/directory/index.php?m[0]=people&m[1]=view&id=$mooID">MOO</a>
EOF;

if ($user->isInGroup(['superuser']) || ($mooID && $user->getID() == $mooID)) {
    $DEFAULT_MENU .= <<<EOF
&nbsp;|&nbsp;<a href="sb/sb_admin.php">Maintain SB</a>
EOF;
}

switch ($m[1]) {
    case 'admin':
        $DEFAULT_TITLE .= "\Admin";
        $DEFAULT_MENU .= <<<EOF
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<a href="$php_self?m[0]=sb&m[1]=admin">Home</a>
&nbsp;|&nbsp;<a href="$php_self?m[0]=sb&m[1]=admin&m[2]=csm_approval">Run CSM_APPROVAL script</a>
&nbsp;|&nbsp;<a href="$php_self?m[0]=sb&m[1]=admin&m[2]=customer_to_decide">Run CUSTOMER_TO_DECIDE script</a>
EOF;

        if (!$user->isInGroup(['superuser'])) {
            $DEFAULT_ERROR[] = 'You do not have permissions';
            break;
        }

        switch ($m[2]) {
            case 'csm_approval':
                $body = tldSB3::checkCSM_APPROVAL();
                break;
            case 'customer_to_decide':
                $body = tldSB_Line::checkCUSTOMER_TO_DECIDE();
                break;
        }
        break;
    case 'forms':
        switch ($m[2]) {
            case 'byNum':
                $form = new HTML_QuickForm('frmByNum', 'post');
                $form->addElement('hidden', 'm[0]', 'sb');
                $form->addElement('hidden', 'm[1]', 'forms');
                $form->addElement('hidden', 'm[2]', 'byNum');
                $form->addElement('header', 'title', 'SB by Number (will check on SB1 and SB3 module)');
                $form->addElement('text', 'id', 'SB#');
                $form->addElement('submit', 'btnSubmit', 'Submit');
                $form->addRule('id', 'Required', 'required');
                $body .= $form->toHTML();

                if (!$form->validate()) {
                    break;
                }

                $vars = tldUtils::cleanupFormInput($form->exportValues());
                // If ID >= 4000 it is SB3
                if ($id >= 4000) {
                    header("Location: $php_self?m[0]=sb&m[1]=view&id=$id");
                    exit;
                }

                // Get data
                $id = $vars['id'];
                $sb1 = new tldSB($id);
                if (!$sb1->isEmpty()) {
                    $sbs[] = $sb1;
                    $body .= "<p><a href=\"$php_self?m[0]=sbs&m[1]=view&id=$id\">SB found in SB1 module: Click here to view SB#$id</a></p>";
                }
                $sb3 = new tldSB3($id);
                if (!$sb3->isEmpty()) {
                    $sbs[] = $sb3;
                    $body .= "<p><a href=\"$php_self?m[0]=sb&m[1]=view&id=$id\">SB found in SB3 module: Click here to view SB#$id</a></p>";
                }
                // if nothing give error
                if (empty($sbs)) {
                    $DEFAULT_ERROR[] = "ERROR: No SB found for $id";
                }
                break;
            case 'add':
                $DEFAULT_TITLE .= "\Add";
                if (!$user->isInGroup(['gg_ADMIN', 'role_PSM', 'role_PSE', 'role_PSA'])) {
                    $DEFAULT_ERROR[] = 'ERROR: You do not have permissions';
                    break;
                }
                $DEFAULT_ERROR[] = 'WARNING: All SB information is available to customer via extranet except for confidential SB.';
                $DEFAULT_ERROR[] = 'Please make sure to use english and appropriate wording.';
                // Listing
                $factoryList = tldLocation::getFactoryList('smartyOptionsIDLocation');
                $categoryList = tldSB3::getCategoryList();
                $typeList = tldSB3::getTypeList();
                $iFactorList = tldSB3::getIFactorList();
                $yesNoList = ['Y' => 'Y', 'N' => 'N'];
                // Form
                $form = new HTML_QuickForm('frmNew');
                $form->addElement('hidden', 'm[0]', 'sb');
                $form->addElement('hidden', 'm[1]', 'forms');
                $form->addElement('hidden', 'm[2]', 'add');
                $form->addElement('header', 'headform', 'Add new SB');
                $form->addElement('select', 'bu_id', 'Factory', ['' => ''] + $factoryList);
                $form->addElement('select', 'category', 'Category', ['' => ''] + $categoryList,
                    ['onChange' => "javascript:
                switch($(this).val()){
                case 'COMPULSORY':
                    $('#sb_field_type').val('');
                    $('#sb_field_type').attr('disabled','disabled');
                    $('#iFactor').val('1000');
                break;
                case 'RECOMMENDED':
                    $('#sb_field_type').val('');
                    $('#sb_field_type').attr('disabled','disabled');
                    $('#iFactor').val('100');
                break;
                case 'INFORMATION':
                    $('#sb_field_type').removeAttr('disabled');
                    $('#iFactor').val('10');
                break;
                }
        "]);
                $form->addElement('textarea', 'category_reason', nl2br("Further indications for CSMs\n(SB category rationale, ER coverage, etc.)\nNot viewed by customers"), ['wrap' => 'VIRTUAL', 'cols' => '30', 'rows' => '3']);
                $form->addElement('select', 'type', 'Type', ['' => ''] + $typeList, ['id' => 'sb_field_type']);
                $form->addElement('select', 'ifactor', 'IFactor', ['' => ''] + $iFactorList, ['id' => 'iFactor']);
                $form->addElement('select', 'confidential', 'Confidential?', ['' => ''] + $yesNoList);
                $form->addElement('text', 'title', 'Title');
                $form->addElement('textarea', 'description', 'Description', ['wrap' => 'VIRTUAL', 'cols' => '60', 'rows' => '5']);
                $form->addElement('text', 'labor', 'Labor (in minutes)');
                $form->addElement('select', 'nb_tech_needed', 'Technician needed', ['1' => '1', '2' => '2', '3' => '3', '4' => '4', '5' => '5']);
                $form->addElement('select', 'parts_needed', 'Parts needed?', ['' => ''] + $yesNoList);
                $form->addElement('submit', 'btnSubmit', 'Submit');
                $requiredFields = ['bu_id', 'category', 'ifactor', 'confidential', 'title', 'description', 'labor', 'nb_tech_needed', 'parts_needed'];
                foreach ($requiredFields as $field) {
                    $form->addRule($field, 'Required', 'required');
                }
                $form->setDefaults(['bu_id' => $user->getBUID()]);

                if (!$form->validate()) {
                    $body .= $form->toHTML();
                    break;
                }

                $vars = tldUtils::cleanupFormInput($form->exportValues());

                $vars['poster_id'] = $user->getID();
                $e = tldSB3::insert($vars);
                if (is_string($e)) {
                    $DEFAULT_ERROR[] = "ERROR: Problem adding new SB...<br/>Reason: $e";
                    break;
                }
                $body .= <<<EOF
<p>SB#$e created successfully!</p>
<meta http-equiv="Refresh" content="2;url=$php_self?m[0]=sb&m[1]=view&id=$e">
EOF;
                break;
            case 'impactedERLine':
                // Form
                $form = new HTML_QuickForm('frmNew', 'get', '', '', null, true);
                $form->addElement('hidden', 'm[0]', 'sb');
                $form->addElement('hidden', 'm[1]', 'forms');
                $form->addElement('hidden', 'm[2]', 'impactedERLine');
                $form->addElement('header', 'headform1', 'Search');
                $form->addElement('select', 'sso_name', 'SSO', ['ALL' => 'ALL'] + tldLocation::getSalesOrgList('smartyOptionsLocationLocation'));
                $form->addElement('select', 'sb_status', 'SB status', ['ALL' => 'ALL'] + tldSB3::getStatusList());
                $form->addElement('select', 'sb_category', 'SB category', ['ALL' => 'ALL'] + tldSB3::getCategoryList());
                $form->addElement('select', 'isi', 'ISI', ['ALL' => 'ALL'] + tldSB_Line::getStatusList());
                $form->addElement('submit', 'btnSubmit', 'Submit');

                if (!$form->validate()) {
                    $body .= $form->toHTML();
                    return;
                }

                $vars = tldUtils::cleanupFormInput($form->exportValues());
                $a = [];
                $title = 'ER Count by ISI, by SB Category ';

                if ($vars['sso_name'] !== 'ALL') {
                    $a[] = " er.sso_service = '{$vars['sso_name']}' ";
                    $title .= "for SSO Service {$vars['sso_name']} ";
                }
                if ($vars['sb_status'] !== 'ALL') {
                    $a[] = " sb.status = '{$vars['sb_status']}' ";
                    $title .= "for SB status {$vars['sb_status']} ";
                } else {
                    $inClause = implode("', '", tldSB3::getStatusList());
                    $a[] = " sb.status IN ('$inClause') ";
                }
                if ($vars['isi'] !== 'ALL') {
                    $a[] = " sb_lines.status = '{$vars['isi']}' ";
                    $title .= "for ISI {$vars['isi']} ";
                } else {
                    $a[] = " sb_lines.status != '' ";
                }
                if ($vars['sb_category'] !== 'ALL') {
                    $a[] = " sb.category = '{$vars['sb_category']}' ";
                    $title .= "for SB category {$vars['sb_category']} ";
                }
                $a = implode(' AND ', $a);
                $data = tldSB_Line::countByStatusByCategoryByConstraints($a);

                $report = new tldMatrix(
                    $data, 'status', 'category', 'num', '',
                    $title,
                    [
                        'xItems' => tldSB_Line::getStatusList(),
                        'yItems' => tldSB3::getCategoryList(),
                    ]
                );
                $body .= $report->fetch();
                break;
            case 'sbCountBySSOByFactory':
                // Form
                $form = new HTML_QuickForm('frm');
                $form->addElement('hidden', 'm[0]', 'sb');
                $form->addElement('hidden', 'm[1]', 'forms');
                $form->addElement('hidden', 'm[2]', 'sbCountBySSOByFactory');
                $form->addElement('header', 'headform1', 'Search');
                $form->addElement('select', 'factory_id', 'Factory', ['ALL' => 'ALL'] + tldLocation::getFactoryList('smartyOptionsIDLocation'));
                $form->addElement('select', 'sso_name', 'SSO', ['ALL' => 'ALL'] + tldLocation::getSalesOrgList('smartyOptionsLocationLocation'));
                $form->addElement('select', 'decision', 'Decision', ['CSM_APPROVAL' => 'CSM_APPROVAL', 'SSD_DECISION' => 'SSD_DECISION']);
                $form->addElement('text', 'from', 'From', ['class' => 'datepicker', 'value' => (new Datetime('first day of this month last year'))->format('Y-m-d')]);
                $form->addElement('text', 'to', 'To', ['class' => 'datepicker', 'value' => (new Datetime('first day of this month'))->format('Y-m-d')]);
                $form->addElement('submit', 'btnSubmit', 'Submit');

                if (!$form->validate()) {
                    $body .= $form->toHTML();
                    return;
                }

                $vars = tldUtils::cleanupFormInput($form->exportValues());
                $decision = $vars['decision'];
                $a = [];
                if ($vars['factory_id'] !== 'ALL') {
                    $a[] = " sb.bu_id = '{$vars['factory_id']}'";
                }
                if ($vars['sso_name'] !== 'ALL') {
                    $a[] = " service.sso_service = '{$vars['sso_name']}'";
                }
                if ($vars['from']) {
                    $a[] = ($decision === 'CSM_APPROVAL') ? " sb.dt_ssd_approval >= '{$vars['from']}'" : " sb.dt_ssd_decision >= '{$vars['from']}'";
                }
                if ($vars['to']) {
                    $a[] = ($decision === 'CSM_APPROVAL') ? " sb.dt_ssd_approval <= '{$vars['to']}'" : " sb.dt_ssd_decision <= '{$vars['to']}'";
                }
                $start = new DateTime($vars['from'] ?: 'first day of this month last year');
                $end = new DateTime($vars['to'] ?: 'today');
                $interval = DateInterval::createFromDateString('1 month');
                $period = new DatePeriod($start, $interval , $end);

                $rows = tldSB3::getSBMonthlyReport($a, $decision);
                $rows = array_combine(array_column($rows, 'date'), $rows);
                foreach($period as $month) {
                    $date = $month->format('Y-m');
                    if (null === ($rows[$date] ?? null)) {
                        $rows[$date] = [
                            "total" => "0",
                            "total_compulsory" => "0",
                            "total_compulsory_line" => "0",
                            "date" => $date,
                        ];
                    }
                }
                ksort($rows);
                foreach($rows as $row) {
                    $dataEnd[] = [
                        'date' => $row['date'],
                        'type' => 'Total SB',
                        'value' => $row['total'],
                    ];
                    $dataEnd[] = [
                        'date' => $row['date'],
                        'type' => 'Total Compulsory SB',
                        'value' => $row['total_compulsory'],
                    ];
                    $dataEnd[] = [
                        'date' => $row['date'],
                        'type' => 'Total Compulsory SB Lines',
                        'value' => $row['total_compulsory_line'],
                    ];
                }
                $report = new tldMatrix(
                    $dataEnd, 'date', 'type', 'value',
                    "$php_self?m[0]=sb&m[1]=listing&m[2]=bySBDate&factory={$vars['factory_id']}&sso={$vars['sso_name']}&decision=$decision&from={$vars['from']}&to={$vars['to']}",
                    "SB in $decision",
                    [
                        'doNotShowXTotals' => true,
                        'yItems' => ['Total SB', 'Total Compulsory SB', 'Total Compulsory SB Lines'],
                    ]
                );
                $body .= $report->fetch();
                break;
        }
        break;
    case 'view':
        include 'sb/sb.view.inc.php';
        break;
    case 'reports':
        $DEFAULT_TITLE .= "\Reports";

        switch ($m[2]) {
            case 'sbImplementationLaborByStatusAPCBySSO':

                // SSO SELECTION ---->
                // If no sso parameter, form to select SSO
                if (empty($_REQUEST['ssoid']) || !is_numeric($_REQUEST['ssoid'])) {
                    $form = new HTML_QuickForm('frm');
                    $form->addElement('hidden', 'm[0]', 'sb');
                    $form->addElement('hidden', 'm[1]', 'reports');
                    $form->addElement('hidden', 'm[2]', $m[2]);
                    $form->addElement('hidden', 'm[3]', $m[3]);
                    $form->addElement('hidden', 'm[4]', $m[4]);
                    $form->addElement('header', 'header', 'Select SSO');
                    $form->addElement('select', 'ssoid', 'SSO', ['' => ''] + tldLocation::getSalesOrgList('smartyOptionsIDLocation'));
                    $form->addElement('submit', 'btnSubmit', 'Submit');
                    $body .= $form->toHTML();
                }
                // Check sso parameter
                $sso = new tldLocation($_REQUEST['ssoid']);
                if ($sso->isEmpty()) {
                    $DEFAULT_ERROR[] = "ERROR: SSO#{$_REQUEST['ssoid']} not found";
                    break;
                }

                $a = tldUtils::cleanupFormInput(['sb.status' => '%IMPLEMENTATION', 'er.sso_service' => $sso->getShortName()]);
                $matrix = new tldMatrix(
                    tldSB_Line::countEstimatedOperationHoursByStatusByAPCByConstraints($a),
                    'status', 'apc_code', 'num',
                    "$php_self?m[0]=sb&m[1]=listing&m[2]=lines&m[3]=sbImplementationLaborByStatusAPCBySSO&ssoid={$sso->getID()}",
                    'SB (PARTIAL) IMPLEMENTATION, Estimated Hours of operation by ISI, by APC for ' . $sso->getShortName(),
                    [
                        'xItems' => tldSB_Line::getStatusList(),
                        'decimals' => true,
                    ]
                );
                $body .= $matrix->fetch();
                break;
            default:
                $body = $smarty->fetch("$PATH/sb/reports/homepage.reports.tpl");
                break;
        }
        break;
    case 'listing':
        include 'sb/sb.listing.inc.php';
        break;
    case 'help':
        $DEFAULT_TITLE .= "\Help";
        $body = <<<EOF
<h3>SB Help</h3>
<ul>
  <li>
    <a href="/en/private/mis/mis.php?m[0]=help&m[1]=dms&id=888">SB III User Guide</a>
  </li>
  <li>
    <a href="/en/private/mis/mis.php?m[0]=help&m[1]=dms&id=20">Internal SB Rules</a> - 
    <span style="color:red;">TLD ONLY, not for customer release</span>
  </li>
  <li>
    <a href="/en/private/mis/mis.php?m[0]=help&m[1]=dms&id=318">SB official TLD policy versus Customers</a>
  </li>
</ul>
EOF;
        break;
    default:
        // User dashboard -------------------->
        if ($user->isInGroup(['role_PSM', 'role_COO', 'role_CSM', 'role_CSA', 'role_EVP', 'role_CEO', 'role_GTD', 'role_AST'])) {
            include 'sb.dashboard.inc.php';
        }
        // Common dashboard ------------------>
        $body .= '<h2 style="border-bottom: 1px solid grey;">Common SB dashboard</h2>';
        // SB by Factory
        $matrix = new tldMatrix(
            tldSB3::countByFactoryStatusByConstraints(),
            'status', 'factory', 'num',
            "$php_self?m[0]=sb&m[1]=listing&m[2]=byFactoryStatus",
            'SB Count by Factory, Status',
            [
                'xItems' => tldSB3::getStatusList(),
            ]
        );
        $body .= $matrix->fetch();
        // Latest SB
        $body .= _getListing(
            tldSB3::byLatest(),
            'Latest SB'
        );
        break;
}


function _getListing($rows, $_title, $xItems = null, $options = [])
{
    global $php_self;
    // Default Columns
    if (empty($xItems)) {
        $xItems = [
            'id' => 'SB#',
            'dt' => 'Date',
            'poster_fullname' => 'Poster',
            'factory' => 'Factory',
            'status' => 'Status',
            'category' => 'Category',
            'confidential' => 'Confidential?',
            'ifactor' => 'IF',
            'title' => 'Title',
            'factory_part_availability_status' => 'Factory parts availability'
        ];
    }
    // Default options
    if (empty($options['links'])) {
        $options['links'] = [
            'id' => "$php_self?m[0]=sb&m[1]=view&id=",
        ];
    }
    // create report
    $report = new tldReportColumnar(
        $rows,
        [
            'xItems' => $xItems,
            'title' => $_title,
            'links' => $options['links'],
            'showNumberOfRows' => true,
        ]
    );
    return $report->fetch();
}

function _getERcountBySSOByStatusMatrix($rows, $title, $link, $opt = [])
{
    $matrix = new tldMatrix(
        $rows,
        'status', 'sso_service_name', 'num',
        $link,
        $title,
        [
            'xItems' => $opt['xItems'],
            'yItems' => $opt['yItems'],
        ]
    );
    return $matrix->fetch();
}
