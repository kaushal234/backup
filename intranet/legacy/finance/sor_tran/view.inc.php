<?php
if (!$user->isInGroup(['superuser', 'role_SA', 'gg_ADMIN', 'gg_ACCT', 'gg_PARTS', 'role_PSM', 'role_CFO', 'role_FC'])) {
    echo 'You do not have permissions for this page..';
    exit;
}
if (empty($id) || !is_numeric($id)) {
    $DEFAULT_ERROR[] = 'ERROR: no line id set...';
    return;
}
$tran = new tldSORTran($id);
if ($tran->isEmpty()) {
    $DEFAULT_ERROR[] = "ERROR: No SOR Tran#$id found...";
    return;
}
$header = $tran->getHeader();

$DEFAULT_TITLE .= "\SOR Tran#$id";
$DEFAULT_MENU .= <<<EOF
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<a href="$php_self?m[0]=sor_tran&m[1]=view&id=$id">Home</a>
&nbsp;|&nbsp;<a href="$php_self?m[0]=sor_tran&m[1]=view&m[2]=edit&id=$id" title="Edit SOR Tran">Edit</a>
&nbsp;|&nbsp;<a href="$php_self?m[0]=sor_tran&m[1]=view&m[2]=log&id=$id" title="Activity Log">Log</a>
EOF;

switch ($m[2]) {
    case 'edit':
        // Get listing
        $TransType = ['B', 'R'];
        $TransType = array_combine($TransType, $TransType);
        $FactoryList = ['ERP', 'SSO'];
        $FactoryList = array_combine($FactoryList, $FactoryList);
        // Form
        $form = new HTML_QuickForm('frmEdit', 'post');
        $form->addElement('hidden', 'm[0]', 'sor_tran');
        $form->addElement('hidden', 'm[1]', 'view');
        $form->addElement('hidden', 'm[2]', 'edit');
        $form->addElement('hidden', 'id', $id);
        $form->addElement('header', 'title', "Edit SOR Tran#$id");
        $form->addElement('text', 'id', 'TRAN#', ['disabled' => 'disabled']);
        $form->addElement('text', 'parent_id', 'SOL#', ['size' => 52]);
        $form->addElement('text', 'dt', 'Date entered', ['disabled' => 'disabled']);
        $form->addElement('select', 'ttyp', 'Trans Type<br>(B)ooking<br>(R)evenue', ['' => ''] + $TransType);
        $form->addElement('select', 'tgrp', 'Factory (ERP)/SSO', ['' => ''] + $FactoryList);
        $form->addElement('text', 'dtran', 'Posting Date', ['size' => 52]);
        $form->addElement('text', 'dref', 'Reference Date', ['size' => 52]);
        $form->addElement('text', 'nref', 'Invoice Number', ['size' => 52]);
        $form->addElement('select', 'tcur', 'Original Currency', ['' => ''] + tldForex::getCurrencyList());
        $form->addElement('text', 'tval', 'Original Value', ['size' => 52]);
        $form->addElement('textarea', 'notes', 'Notes', ['rows' => 5, 'cols' => 40]);
        $form->addElement('submit', 'btnSubmit', 'Submit');
        $form->addElement('reset', 'btnReset', 'Reset');
        // Set required
        $required = ['parent_id', 'ttyp', 'tgrp', 'tcur', 'tval'];
        foreach ($required as $field) {
            $form->addRule($field, 'Required', 'required');
        }
        $form->setDefaults($header);

        if (!$form->validate()) {
            $body = $form->toHTML();
            break;
        }

        $vars = tldUtils::cleanupFormInput($form->exportValues());
        $fields = ['parent_id', 'ttyp', 'tgrp', 'dtran', 'dref', 'nref', 'tcur', 'tval', 'notes'];
        // Update the transaction
        $e = $tran->update($vars, $fields);
        if (is_string($e)) {
            $DEFAULT_ERROR[] = "ERROR: Problem updating...<br/>Reason: $e";
            break;
        }
        // If transaction Posting Date updated, update ER rrd date
        if ($header['dtran'] != $vars['dtran'] && $vars['ttyp'] === 'R') {
            $erList = tldEquipment::bySORTran($header['tgrp'], $header['id']);
            if (count($erList)) {
                foreach ($erList as $erVal) {
                    $er = new tldEquipment($erVal['id']);
                    $e = $er->setRRD($vars['dtran'], $vars['tgrp']);
                    if (is_string($e)) {
                        $DEFAULT_ERROR[] = "INTERNAL ERROR: can not update ER#{$erVal['sn']} RRD: $e";
                        continue;
                    }
                    $er->addLogEntry($user->getID(), "RRD {$vars['tgrp']} updated to {$vars['dtran']} from Trans#$id update");
                    $body .= "<br>ER#{$erVal['sn']} RRD updated to {$vars['dtran']}";
                }
            }
        }
        // Log changes
        $logs = [];
        $fields_to_log = ['parent_id', 'ttyp', 'tgrp', 'dtran', 'dref', 'nref', 'tcur', 'tval'];
        foreach ($fields_to_log as $field) {
            if ($header[$field] != $vars[$field]) {
                $logs[] = "<li>{$FIELD_DESIGNATION[$field]} from '{$header[$field]}' to '{$vars[$field]}'</li>";
            }
        }
        if (count($logs)) {
            $msg = 'Transaction updated:<br><ul>' . implode('', $logs) . '</ul>';
            $e = $tran->addLogEntry(
                $user->getID(),
                TldDatabase::escape($msg)
            );
            if (is_string($e)) {
                $DEFAULT_ERROR[] = "ERROR: Problem adding logs...<br/>Reason: $e";
            }
            $body .= '<br>' . $msg;
        }
        $body .= "<br>SOR Tran#$id updated successfully!";
        break;
    case 'log':
        $DEFAULT_TITLE .= "\Log";
        $DEFAULT_MENU .= <<<EOF
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
EOF;
        $report = new tldReportColumnar(
            $tran->getLog(),
            [
                'xItems' => [
                    'id' => 'ID#',
                    'date' => 'Date',
                    'poster_fullname' => 'Poster',
                    'module' => 'Module',
                    'comment' => 'Comment',
                ],
            ]
        );
        $body .= $report->fetch();
        break;
    default:
        $report = new tldAssocTable(
            $header,
            [
                'id' => 'TRAN#',
                'parent_id' => 'SOL#',
                'dt' => 'Date Entered',
                'ttyp' => 'Trans Type<br>(B)ooking<br>(R)evenue',
                'tgrp' => 'Factory (ERP)/SSO',
                'dtran' => 'Posting Date',
                'nref' => 'Invoice Number',
                'tcur' => 'Original Currency',
                'tval' => 'Original Value',
                'notes' => 'Notes',
            ],
            [
                'title' => 'SOR Transaction Detail',
                'links' => [
                    'parent_id' => '/en/private/sales_service/sales.php?m[0]=sol&m[1]=view&id=',
                ],
            ]
        );
        $body .= $report->fetch();
        $report = new tldReportColumnar(
            tldEquipment::bySORTran(
                $header['tgrp'],
                $header['id']
            ),
            [
                'xItems' => [
                    'id' => 'ID#',
                    'sn' => 'SN#',
                    'man_location' => 'Factory',
                    'customer_name' => 'Customer',
                    'model' => 'Model',
                    'airport_code' => 'Airport Code',
                    'dgt_com' => 'First GT Date',
                    'date_shipped' => 'Ship Date',
                    'rrd_sso' => 'Revenue Recognition Date, SSO',
                    'rrd_erp' => 'Revenue Recognition Date, Factory',
                ],
                'title' => 'ER Linked to Transaction',
                'links' => [
                    'id' => '/en/private/product_support/index.ps.php?m[0]=equipment&m[1]=view&id=',
                ],
            ]
        );
        $body .= $report->fetch();
        break;
}
