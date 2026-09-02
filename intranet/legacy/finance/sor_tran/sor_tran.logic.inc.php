<?php
include_once('sales_service.inc.php');
if (!$user->isInGroup(['superuser', 'role_SA', 'gg_ADMIN', 'gg_ACCT', 'gg_PARTS', 'ROLE_RCEO']) && !(('view' === ($m[1] ?? null)) && $user->isInGroup(['role_PSM', 'role_CFO', 'role_FC']))) {
    echo 'You do not have permissions for this page..';
    return;
}

$DEFAULT_TITLE .= "\SOR Transactions";
$DEFAULT_MENU .= <<<EOF
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<a href="$php_self?m[0]=sor_tran">Home</a>
&nbsp;|&nbsp;<a href="$php_self?m[0]=sor_tran&m[1]=form&m[2]=byNum">By number</a>
&nbsp;|&nbsp;<a href="$php_self?m[0]=sor_tran&m[1]=form&m[2]=search">Search</a>
&nbsp;|&nbsp;<a href="sor_tran/sor_tran_admin.php">SOR Trans Admin</a>
EOF;

$FIELD_DESIGNATION = [
    'id' => 'TRAN#',
    'parent_id' => 'SOL#',
    'dt' => 'Date Entered',
    'ttyp' => 'Trans Type',
    'tgrp' => 'BU Type',
    'dtran' => 'Posting Date',
    'nref' => 'Invoice Number',
    'tcur' => 'Original Currency',
    'tval' => 'Original Value',
    'notes' => 'Notes',
];

switch ($m[1]) {
    case 'form':
        switch ($m[2]) {
            case 'byNum':
                $DEFAULT_TITLE .= "\SOR Tran by Number";
                $form = new HTML_QuickForm('frmSOByNum', 'post');
                $form->addElement('hidden', 'm[0]', 'sor_tran');
                $form->addElement('hidden', 'm[1]', 'view');
                $form->addElement('header', 'title', 'SOR Tran by Number');
                $form->addElement('text', 'id', 'SOR Tran#');
                $form->addElement('submit', 'btnSubmit', 'Submit');
                $body .= $form->toHTML();
                break;
            case 'search':
                $form = new HTML_QuickForm('search', 'post');
                $form->addElement('hidden', 'm[0]', 'sor_tran');
                $form->addElement('hidden', 'm[1]', 'form');
                $form->addElement('hidden', 'm[2]', 'search');
                $form->addElement('header', 'header', 'Search SOR Trans by');
                $form->addElement('text', 'parent_id', 'SOL#');
                $form->addElement('text', 'sn', 'ER Serial Number');
                $form->addElement('select', 'ttyp', 'Trans type', ['' => '', 'B' => '(B)ooking', 'R' => '(R)Revenue']);
                $form->addElement('select', 'tgrp', 'BU type', ['' => '', 'ERP' => 'Factory', 'SSO' => 'SSO']);
                $form->addElement('text', 'nref', 'INV/SO Number');
                $form->addElement('submit', 'btnSubmit', 'Submit');

                if (!$form->validate()) {
                    $body .= $form->toHTML();
                    break;
                }

                $vars = tldUtils::cleanupFormInput($form->exportValues());
                $a = [];
                $fields = ['parent_id', 'sn', 'ttyp', 'tgrp', 'nref'];
                foreach ($fields as $field) {
                    if (empty($vars[$field])) {
                        continue;
                    }
                    switch ($field) {
                        case 'sn':
                            $ers = tldEquipment::bySN($vars['sn']);
                            if (count($ers) < 1) {
                                $DEFAULT_ERROR[] = "WARNING: ER not found with SN#{$vars['sn']}";
                                continue 2;
                            }
                            $ids = [];
                            if ($ers[0]['tranid_sso'] != 0) {
                                $ids[] = $ers[0]['tranid_sso'];
                            }
                            if ($ers[0]['tranid_erp'] != 0) {
                                $ids[] = $ers[0]['tranid_erp'];
                            }
                            if (count($ids) < 1) {
                                $DEFAULT_ERROR[] = "WARNING: ER SN#{$vars['sn']} have no transactions";
                                continue 2;
                            }
                            $ids = implode(',', $ids);
                            $a[] = "id IN ($ids)";
                            break;
                        default:
                            $a[] = "$field LIKE '{$vars[$field]}'";
                            break;
                    }
                }
                // check if enough constraints
                if (count($a) < 1) {
                    $DEFAULT_ERROR[] = 'ERROR: Can not search, not enough constraints set in the form';
                    $body .= $form->toHTML();
                    break;
                }
                $a = implode(' AND ', $a);
                $rows = tldSORTran::byConstraints($a);
                $body .= _getListing($rows, 'Search results');
                break;
        }
        break;
    case 'addSorTran':
        if (empty($id)) {
            $DEFAULT_ERROR[] = 'ERROR: no SOL id set...';
            break;
        }
        $sol = new tldSOL($id);
        if ($sol->isEmpty()) {
            $DEFAULT_ERROR[] = "ERROR: No SOL#$id found...";
            break;
        }
        $body .= <<<EOF
   <a href="/en/private/sales_service/sales.php?m[0]=sol&m[1]=view&id=$id">Back to SOL$id</a>
EOF;
        $header = $sol->getHeader();
        switch ($m[2]) {
            case 'finalize':
                if ($sol->getDZKERP() !== '0000-00-00') {
                    $DEFAULT_ERROR[] = 'ERROR: ERP Transactions have already been finalized';
                    break;
                }

                $form = new HTML_QuickForm('frmFinalize', 'post');
                $form->addElement('hidden', 'm[0]', 'sol');
                $form->addElement('hidden', 'm[1]', 'view');
                $form->addElement('hidden', 'm[2]', 'tran');
                $form->addElement('hidden', 'm[3]', 'erp');
                $form->addElement('hidden', 'm[4]', 'finalize');
                $form->addElement('hidden', 'id', $id);
                $form->addElement('header', 'title', 'Finalize Factory transactions.');
                if ($tsum['erpk_tot'] <> 0) {
                    $form->addElement('select', 'action',
                        'What to do with remaining backlog amount',
                        ['cancel' => 'Cancel remaining backlog by creating a negative booking',
                            'ignore' => 'Just finalize the Factory transactions']
                    );
                }
                $form->addElement('select', 'conf',
                    'Select Y to proceed...',
                    ['' => '', 'Y' => 'Y']
                );
                $form->addElement('textarea', 'notes', 'Notes', ['rows' => 5, 'cols' => 40]);
                $form->addElement('submit', 'btnSubmit', 'Finalize Factory Transactions...');
                $form->addRule('conf', 'Required', 'required');
                if (!$form->validate()) {
                    if ($tsum['erpk_tot'] <> 0) {
                        $DEFAULT_ERROR[] = 'WARNING: There is a remaining backlog amount of ' .
                            $sol->getDCUR() . $tsum['erpk_tot'];
                    }
                    $body .= $form->toHTML();
                    break;
                }
                $vars = tldUtils::cleanupFormInput($form->exportValues());
                if ($vars['conf'] !== 'Y') {
                    $DEFAULT_ERROR[] = 'WARNING: Confirmation required before proceeding...';
                    $body .= $form->toHTML();
                    break;
                }
                switch ($vars['action']) {
                    case 'cancel':
                        $e = $sol->cancelKERP($vars['notes']);
                        if (is_numeric($e)) {
                            $DEFAULT_ERROR[] = 'Remaining ERP backlog cancelled successfully.';
                        } else {
                            $DEFAULT_ERROR[] = "ERROR: problem cancelling remaining backlog, returned error was $e";
                        }
                        break;
                    default:
                        $e = $sol->setDZKERP(date('Y-m-d'));
                        if (is_string($e)) {
                            $DEFAULT_ERROR[] = "ERROR: problem setting Date of Zero Backlog, returned error was $e";
                        } else {
                            $DEFAULT_ERROR[] = 'SOL successfully finalized.';
                        }
                }
                break;
            case 'addRev':
                if ($sol->getDZKERP() !== '0000-00-00') {
                    $DEFAULT_ERROR[] = 'ERROR: ERP Transactions have already been finalized';
                    $body .= getViewTranERP();
                    break;
                }
                // Get list of ER
                $units = tldSORUnit::byParent($id);
                // Display form
                $form = new HTML_QuickForm('frmAddRev', 'post');
                $form->addElement('hidden', 'm[0]', 'sol');
                $form->addElement('hidden', 'm[1]', 'view');
                $form->addElement('hidden', 'm[2]', 'tran');
                $form->addElement('hidden', 'm[3]', 'erp');
                $form->addElement('hidden', 'm[4]', 'addRev');
                $form->addElement('hidden', 'id', $id);
                // Transaction
                $form->addElement('header', 'title', 'Add Factory Revenue Transaction');
                $form->addElement('date', 'rrd', 'Factory Revenue Recognition Date',
                    ['format' => 'Y-m-d']);
                $form->addElement('text', 'nref', 'Invoice Number');
                $form->addElement('select', 'tcur', 'Currency', tldForex::getCurrencyList());
                $form->addElement('text', 'tval', 'Revenue Value');
                $form->addElement('textarea', 'notes', 'Notes', ['rows' => 5, 'cols' => 40]);
                // ER selection
                $form->addElement('header', 'title', 'Select ER(s) for this transaction');
                foreach ($units as $row) {
                    if (empty($row['sn']) || !empty($row['tranid_erp'])) {
                        continue;
                    } // exclude unassigned units or with tranid not null
                    $label = "SN#{$row['sn']} {$row['model']} {$row['man_location']} - Batch qty:" . $row['er_batch_qty'];
                    $form->addElement('checkbox', "ers[{$row['erid']}]", null, $label);
                }
                $form->addElement('submit', 'btnSubmit', 'Add Revenue transaction...');
                $form->addRule('tcur', 'Required', 'required');
                $form->addRule('tval', 'Required', 'required');
                $form->addRule('nref', 'Required', 'required');

                $form->setDefaults([
                    'rrd' => date('Y-m-d'),
                    'tcur' => $sol->getDCUR(),
                    'tval' => $tsum['erpk_tot'],
                ]);
                if (!$form->validate()) {
                    if (!$sol->isfullyAllocated()) {
                        $DEFAULT_ERROR[] = 'WARNING: All or some Units have not been assigned yet, you will not be able to
                link ER(s) to this transaction for these units.';
                    }
                    $body .= $form->toHTML();
                    $body .= getViewTranERP();
                    break;
                }
                $vars = tldUtils::cleanupFormInput($form->exportValues());
                $e = $sol->addSORTranERPR(implode('-', $vars['rrd']), $vars['tcur'], $vars['tval'], $vars['notes'], $vars['nref']);
                if (is_string($e)) {
                    $DEFAULT_ERROR[] = "ERROR: problem creating factory revenue transaction, returned error was $e";
                } else {
                    $DEFAULT_ERROR[] = 'Factory Revenue Transaction successfully added';
                    // Update transaction id on ER
                    if (!empty($vars['ers'])) {
                        foreach (array_keys($vars['ers']) as $erid) {
                            $er = new tldEquipment($erid);
                            if (empty($er->itsDetails['tranid_erp'])) {
                                $er->setTranID($e, 'ERP');
                                $er->addLogEntry($user->getID(), "Set ERP transaction#$e");
                            } else {
                                $DEFAULT_ERROR[] = "ERROR: ER#$erid not linked, ER have been already recognize in the trans#" . $er->itsDetails['tranid_erp'];
                            }
                        }
                    }
                }
                break;
        }
        break;
    case 'view':
        include_once('sor_tran/view.inc.php');
        break;
    case 'list':
        break;
    default:
        $body = $smarty->fetch("$PATH/sor_tran/homepage.sor_tran.tpl");
        $body .= _getListing(tldSORTran::byLatest(), 'Latest SOR Transactions');
        break;
}

function _getListing($rows, $title)
{
    $report = new tldReportColumnar(
        $rows,
        [
            'xItems' => [
                'id' => 'TRAN#',
                'parent_id' => 'SOL#',
                'dt' => 'Date Entered',
                'ttyp' => 'Trans Type<br>(B)ooking<br>(R)evenue',
                'tgrp' => 'Factory (ERP)/SSO',
                'dtran' => 'Posting Date',
                'dref' => 'Reference Date',
                'nref' => 'Invoice Number',
                'tcur' => 'Original Currency',
                'tval' => 'Original Value',
                'notes' => 'Notes',
            ],
            'title' => $title,
            'links' => [
                'parent_id' => '/en/private/sales_service/sales.php?m[0]=sol&m[1]=view&id=',
                'id' => '/en/private/finance/finance.php?m[0]=sor_tran&m[1]=view&id=',
            ],
        ]
    );
    return $report->fetch();
}
