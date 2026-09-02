<?php
$DEFAULT_TITLE .= "\ER Coverage";
$DEFAULT_MENU .= <<<EOF
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<a href="$php_self?m[0]=sb&m[1]=view&m[2]=coverage&id=$id">Home</a>
&nbsp;|&nbsp;<a href="$php_self?m[0]=sb&m[1]=view&m[2]=coverage&m[3]=add&id=$id">Add</a>
&nbsp;|&nbsp;<a href="$php_self?m[0]=sb&m[1]=view&m[2]=coverage&m[3]=generate&id=$id">Generate ER list</a>
&nbsp;|&nbsp;<a href="$php_self?m[0]=sb&m[1]=view&m[2]=coverage&m[3]=reset&id=$id">Reset ER list</a>
EOF;

// WARNING ---->

$DEFAULT_ERROR[] = 'WARNING: Creation of affected ER from coverage in SB3 do not behave the same way as old SB module!';
$DEFAULT_ERROR[] = 'Please make sure you check the preview list when you generate the affected ER list or, if already generated, in summary before CSM_APPROVAL';

// Application rules ------>

switch ($m[3]) {
    case 'add':
    case 'view':
    case 'generate':
    case 'reset':
        if (!$sb->isPendingStatus() || !$user->isInGroup(['role_PSM', 'role_PSE', 'role_PSA', 'gg_SUPPORT'])) {
            $DEFAULT_ERROR[] = 'ERROR: You do not have permission or SB not PENDING anymore';
            $m[3] = null;
        }
        break;
}

// Application logic ------>

switch ($m[3]) {
    case 'add':
        $DEFAULT_TITLE .= "\Add";
        $form = new HTML_QuickForm('frmNew');
        $form->addElement('hidden', 'm[0]', 'sb');
        $form->addElement('hidden', 'm[1]', 'view');
        $form->addElement('hidden', 'm[2]', 'coverage');
        $form->addElement('hidden', 'm[3]', 'add');
        $form->addElement('hidden', 'id', $id);
        $form->addElement('header', 'headform', 'Add new coverage');
        $form->addElement('header', 'headform', '1. By TLD model');
        $form->addElement('select', 'model', 'Model', ['' => ''] + tldModel::getList());
        $form->addElement('header', 'headform', '2. By SN range');
        $form->addElement('text', 'sn_from', 'SN from');
        $form->addElement('text', 'sn_to', 'SN to');
        $form->addElement('header', 'headform', '3. By specific list of SN');
        $form->addElement('textarea', 'sn_list', 'SN list<br>(separated by a space or a new line)',
            ['wrap' => 'VIRTUAL', 'cols' => '60', 'rows' => '5', 'class' => 'no-editor' ]);
        $form->addElement('submit', 'btnSubmit', 'Submit');

        if (!$form->validate()) {
            $body .= $form->toHTML();
            break;
        }

        $vars = tldUtils::cleanupFormInput($form->exportValues());
        $vars['sn_list'] = stripcslashes($vars['sn_list']);
        // Check range SN
        if (!empty($vars['sn_from']) xor !empty($vars['sn_to'])) {
            $DEFAULT_ERROR[] = "ERROR: If SN range is used, make sure to set both 'SN from' and 'SN to' fields";
            $body .= $form->toHTML();
            break;
        }
        $e = $sb->addCoverage($vars);
        if (is_string($e)) {
            $DEFAULT_ERROR[] = "ERROR: Can not add. Reason: $e";
            break;
        }
        $body .= 'Coverage successfully added';
        $body .= _getCoverageListReport();
        break;
    case 'view':
        if (empty($cid) || !is_numeric($cid)) {
            $DEFAULT_ERROR[] = 'ERROR: Parameter sent empty or invalid...';
            break;
        }
        $coverage = new tldSB_coverage($cid);
        if ($coverage->isEmpty()) {
            $DEFAULT_ERROR[] = 'ERROR: Parameter sent empty or invalid...';
            break;
        }
        $DEFAULT_TITLE .= "\Coverage#$cid";

        switch ($m[4]) {
            case 'edit':
                $DEFAULT_TITLE .= "\Edit";
                $form = new HTML_QuickForm('frm');
                $form->addElement('hidden', 'm[0]', 'sb');
                $form->addElement('hidden', 'm[1]', 'view');
                $form->addElement('hidden', 'id', $id);
                $form->addElement('hidden', 'm[2]', 'coverage');
                $form->addElement('hidden', 'm[3]', 'view');
                $form->addElement('hidden', 'cid', $cid);
                $form->addElement('hidden', 'm[4]', 'edit');
                $form->addElement('header', 'headform', 'Edit coverage');
                $form->addElement('header', 'headform', '1. By TLD model');
                $form->addElement('select', 'model', 'Model', ['' => ''] + tldModel::getList());
                $form->addElement('header', 'headform', '2. By SN range');
                $form->addElement('text', 'sn_from', 'SN from');
                $form->addElement('text', 'sn_to', 'SN to');
                $form->addElement('header', 'headform', '3. By specific SN');
                $form->addElement('textarea', 'sn_list', 'SN list',
                    ['wrap' => 'VIRTUAL', 'cols' => '60', 'rows' => '5']);
                $form->addElement('submit', 'btnSubmit', 'Submit');
                $coverageHeader = $coverage->itsHeader;
                unset($coverageHeader['id']);
                $form->setDefaults($coverageHeader);

                if (!$form->validate()) {
                    $body .= $form->toHTML();
                    break;
                }

                $vars = tldUtils::cleanupFormInput($form->exportValues());
                // Check range SN
                if (!empty($vars['sn_from']) xor !empty($vars['sn_to'])) {
                    $DEFAULT_ERROR[] = "ERROR: If SN range is used, make sure to set both 'SN from' and 'SN to' fields";
                    $body .= $form->toHTML();
                    break;
                }
                $e = $coverage->update($vars, ['model', 'sn_from', 'sn_to', 'sn_list']);
                if (is_string($e)) {
                    $DEFAULT_ERROR[] = "ERROR: Can not update. Reason: $e";
                    break;
                }
                $body .= 'Coverage successfully updated';
                $body .= _getCoverageListReport();
                break;
            case 'delete':
                $e = $coverage->delete();
                if (is_string($e)) {
                    $DEFAULT_ERROR[] = "ERROR: Can not delete. Reason: $e";
                    break;
                }
                $body .= 'Coverage successfully deleted';
                $body .= _getCoverageListReport();
                break;
        }
        break;
    case 'emulate':
        $report = new tldReportColumnar(
            $sb->getERListFromCoverage(),
            [
                'xItems' => [
                    'id' => 'ER#',
                    'sn' => 'SN#',
                    'model' => 'Model',
                    'man_location' => 'Factory',
                    'sales_org' => 'SSO',
                    'date_shipped' => 'Ship date',
                ],
                'title' => 'ER List preview (generated from coverage configuration)',
            ]
        );
        $body .= $report->fetch();
        break;
    case 'generate':
        // Check if there is already some lines
        $lines = $sb->getLinesByConstraints();
        if (count($lines)) {
            $DEFAULT_ERROR[] = 'ERROR: Can not generate, ER list already generated. Please reset first';
            break;
        }

        $covLines = $sb->getCoverageList();
        if (!count($covLines)) {
            $DEFAULT_ERROR[] = 'ERROR: Can not generate, ER coverage not setup';
            break;
        }
        // Get list of coverage
        $ERListFromCoverage = $sb->getERListFromCoverage();
        if (count($ERListFromCoverage) > 500) {
            $DEFAULT_ERROR[] = 'WARNING: There is more than 500 ERs for this SB, please double check if normal.';
        }
        $impactedUnits = [];
        foreach ($ERListFromCoverage as $er) {
            if ($er['date_shipped'] === '0000-00-00') {
                $impactedUnits[] = $er['sn'];
            }
        }

        // Form to confirm generation of SB lines
        $form = new HTML_QuickForm('frm');
        $form->addElement('hidden', 'm[0]', 'sb');
        $form->addElement('hidden', 'm[1]', 'view');
        $form->addElement('hidden', 'id', $id);
        $form->addElement('hidden', 'm[2]', 'coverage');
        $form->addElement('hidden', 'm[3]', 'generate');
        $form->addElement('header', 'headform1', count($ERListFromCoverage) . ' ER(s) found from coverage configuration');
        $form->addElement('header', 'headform2', 'Please confirm you want to generate ER list below');
        $form->addElement('header', 'headform2', "Note that unshipped ER will generate a CRAB and won't appear in SB summary");
        if (!empty($impactedUnits)) {
            $form->addElement('static', null, null, "<p style='color:#FF0000;'>ER that will be impacted by a CRAB: <strong>" . implode(', ', $impactedUnits) . ' </strong></p>');
            $form->addElement('static', null, null, "<p>Open a CRAB for ? (unit not fitted with a CRAB will be added to the implementation list)</p>");
            foreach ($impactedUnits as $er){
                $form->addElement('checkbox', $er, $er);
            };
        }
        $form->addElement('select', 'confirm', 'Confirm?', ['' => '', 'Y' => 'Yes']);
        $form->addElement('submit', 'btnSubmit', 'Submit');
        $form->addRule('confirm', 'Required', 'required');

        if (!$form->validate()) {
            $body .= $form->toHTML();
            // Preview ER list to be generated
            $report = new tldReportColumnar(
                $ERListFromCoverage,
                [
                    'xItems' => [
                        'id' => 'ER#',
                        'sn' => 'SN#',
                        'model' => 'Model',
                        'man_location' => 'Factory',
                        'sales_org' => 'SSO',
                        'date_shipped' => 'Ship date',
                    ],
                    'title' => 'ER List preview (generated from coverage configuration)',
                ]
            );

            $body .= $report->fetch();
            break;
        }

        $erToCrab= [];
        foreach ($form->getSubmitValues() as $key => $value){
            if ($value === "1") {
                $erToCrab[] = $key;
            }
        }

        $e = $sb->generateLines($user->getID(), $erToCrab);
        if (is_string($e)) {
            $DEFAULT_ERROR[] = "ERROR: An error occurred -> ER list creation stopped. Reason: $e";
            break;
        }
        $body .= <<<EOF
<p>ER list successfully generated and attached to the SB<br>
<a href="$php_self?m[0]=sb&m[1]=view&m[2]=summary&id=$id">Please go to summary to view the list</p>
EOF;
        break;
    case 'reset':
        // Form to confirm reset of SB lines
        $form = new HTML_QuickForm('frm');
        $form->addElement('hidden', 'm[0]', 'sb');
        $form->addElement('hidden', 'm[1]', 'view');
        $form->addElement('hidden', 'id', $id);
        $form->addElement('hidden', 'm[2]', 'coverage');
        $form->addElement('hidden', 'm[3]', 'reset');
        $form->addElement('header', 'headform', 'Please confirm removal of the generated ER list');
        $form->addElement('select', 'confirm', 'Confirm?', ['' => '', 'Y' => 'Yes']);
        $form->addElement('submit', 'btnSubmit', 'Submit');
        $form->addRule('confirm', 'Required', 'required');

        if (!$form->validate()) {
            $body .= $form->toHTML();
            break;
        }

        $e = $sb->resetLines();
        if (is_string($e)) {
            $DEFAULT_ERROR[] = "ERROR: An error occurred. Reason: $e";
            break;
        }
        $body .= 'Generated ER list successfully removed from the SB';
        break;
    default:
        $body .= _getCoverageListReport();
        break;
}


function _getCoverageListReport()
{
    global $php_self, $id, $sb;
    // Display coverage
    $report = new tldReportColumnar(
        $sb->getCoverageList(),
        [
            'xItems' => [
                'model' => 'Model',
                'sn_from' => 'SN from',
                'sn_to' => 'SN to',
                'sn_list' => 'SN list',
            ],
            'title' => 'SB Coverage',
            'showItemNumbers' => true,
            'functions' => [
                'Edit' => [
                    'url' => "$php_self?m[0]=sb&m[1]=view&m[2]=coverage&id=$id&m[3]=view&m[4]=edit",
                    'param' => ['cid' => 'id'],
                    'img' => '/shared/icons/miscellaneous/edit.png',
                ],
                'Delete' => [
                    'url' => "$php_self?m[0]=sb&m[1]=view&m[2]=coverage&id=$id&m[3]=view&m[4]=delete",
                    'param' => ['cid' => 'id'],
                    'confirmPopup' => 'Are you sure to delete?',
                    'img' => '/shared/icons/miscellaneous/delete.png',
                ],
            ],
        ]
    );
    return $report->fetch();
}
