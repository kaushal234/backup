<?php
if (empty($id) || !is_numeric($id)) {
    $DEFAULT_ERROR[] = 'ERROR: Parameter sent empty or invalid...';
    return;
}
$sb = new tldSB3($id);
$header = $sb->itsHeader;
if ($sb->isEmpty()) {
    $DEFAULT_ERROR[] = "ERROR: SB#$id not found...";
    return;
}

$DEFAULT_TITLE .= "\SB#$id";
$DEFAULT_MENU .= <<<EOF
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<a href="$php_self?m[0]=sb&m[1]=view&id=$id">General</a>
&nbsp;|&nbsp;<a href="$php_self?m[0]=sb&m[1]=view&m[2]=summary&id=$id">Summary</a>
&nbsp;|&nbsp;<a href="$php_self?m[0]=sb&m[1]=view&m[2]=summary&m[3]=selection&id=$id">SSD decision Dashboard</a>
&nbsp;|&nbsp;<a href="$php_self?m[0]=sb&m[1]=view&m[2]=summary&m[3]=implementation&id=$id">Implementation Dashboard</a>
&nbsp;|&nbsp;<a href="$php_self?m[0]=sb&m[1]=view&m[2]=edit&id=$id">Edit</a>
&nbsp;|&nbsp;<a href="$php_self?m[0]=sb&m[1]=view&m[2]=logs&id=$id">Logs</a>
&nbsp;|&nbsp;<a href="$php_self?m[0]=sb&m[1]=view&m[2]=tasks&id=$id">Tasks</a>
&nbsp;|&nbsp;<a href="$php_self?m[0]=sb&m[1]=view&m[2]=files&id=$id">Files</a>
&nbsp;|&nbsp;<a href="$php_self?m[0]=sb&m[1]=view&m[2]=parts&id=$id">Parts</a>
&nbsp;|&nbsp;<a href="$php_self?m[0]=sb&m[1]=view&m[2]=links&id=$id">Links</a>
&nbsp;|&nbsp;<a href="$php_self?m[0]=sb&m[1]=view&m[2]=coverage&id=$id">ER Coverage</a>
&nbsp;|&nbsp;<a href="$php_self?m[0]=sb&m[1]=view&m[2]=signature&id=$id">Signature</a>
&nbsp;|&nbsp;<a href="$php_self?m[0]=sb&m[1]=view&m[2]=status&id=$id">Status</a>
&nbsp;|&nbsp;<a href="$php_self?m[0]=sb&m[1]=view&m[2]=duplicate&id=$id">Duplicate</a>
EOF;
if ('PENDING' === $sb->getStatus()) {
    $DEFAULT_MENU .= <<<EOF
&nbsp;|&nbsp;<a href="$php_self?m[0]=sb&m[1]=view&m[2]=delete&id=$id">Delete</a>
&nbsp;|&nbsp;<a href="sb/sb_admin.php?mode=record_view&form_type=main_tpl&id=$id">Admin</a>
EOF;
} else {
    $DEFAULT_ERROR[] = "You can delete SB only in PENDING status, for other status ask Customer Service Director to move SB to CANCELLED status. We cannot allow DELETE as SB may have pending CSR, SPR or TASKS.";
}


// Header at all time ----->
$body .= include 'sb.view.header.tpl.php';
//<-------------------------

switch ($m[2]) {
    case 'status':
        include 'sb/sb.view.status.inc.php';
        break;
    case 'summary':
        include 'sb/sb.view.summary.inc.php';
        break;
    case 'coverage':
        include 'sb/sb.view.coverage.inc.php';
        break;
    case 'lines':
        if (empty($lid) || !is_numeric($lid)) {
            $DEFAULT_ERROR[] = 'ERROR: Parameter sent empty or invalid...';
            break;
        }
        $line = new tldSB_Line($lid);
        if ($line->isEmpty() || $line->getParentID() !== $sb->getID()) {
            $DEFAULT_ERROR[] = "ERROR: SB line# $lid not found...";
            break;
        }
        $line_header = $line->getHeader();
        unset($line_header['id']);
        $DEFAULT_TITLE .= "\Line#$lid for ER#{$line->getERSN()}";

        switch ($m[3]) {
            case 'edit':
                $DEFAULT_TITLE .= "\Edit";
                if (!$user->isInGroup(['superuser']) && !$user->isInGroupLevel('role_CSM', $line->getSSOERP()) && !$user->isInGroupLevel('role_CSD', 900) && !$user->isInGroup(['role_CSA'])) {
                    $DEFAULT_ERROR[] = 'ERROR: You do not have permission to edit this SB Line';
                    return;
                }

                $closureTypeList = tldSB_Line::getClosureTypeList();
                $customerDecisionList = tldSB_Line::getCustomerDecisionList();
                $statusList = tldSB_Line::getStatusList();
                // Form
                $form = new HTML_QuickForm('frmSBLineEdit', 'post');
                $form->addElement('hidden', 'm[0]', 'sb');
                $form->addElement('hidden', 'm[1]', 'view');
                $form->addElement('hidden', 'm[2]', 'lines');
                $form->addElement('hidden', 'm[3]', 'edit');
                $form->addElement('hidden', 'id', $id);
                $form->addElement('hidden', 'lid', $lid);
                $form->addElement('header', 'headform', "Edit SB Line#$lid");
                $form->addElement('text', 'parent_id', 'SB#', ['disabled' => 'disabled']);
                $form->addElement('text', 'er_id', 'ER#', ['disabled' => 'disabled']);
                $form->addElement('text', 'part_decision', 'SSD Part Decision', ['disabled' => 'disabled']);
                $form->addElement('select', 'cust_part_decision', 'Customer Part Decision', ['' => ''] + $customerDecisionList);
                $form->addElement('text', 'service_decision', 'SSD Service Decision', ['disabled' => 'disabled']);
                $form->addElement('select', 'cust_service_decision', 'Customer Service Decision', ['' => ''] + $customerDecisionList);
                $form->addElement('text', 'spr_id', 'SPR#');
                $form->addElement('text', 'csr_id', 'CSR#');
                $form->addElement('select', 'status', 'Status', ['' => ''] + $statusList);
                $form->addElement('select', 'closure_type', 'Closure Type', ['' => ''] + $closureTypeList);
                $form->addElement('text', 'dt_cust_to_decide', 'Date Customer to Decide');
                $form->addElement('submit', 'btnSubmit', 'Submit');
                $form->addElement('reset', 'btnReset', 'Reset');
                $form->addRule('status', 'Required', 'required');

                $form->setDefaults($line_header);

                if (!$form->validate()) {
                    $body .= $form->toHTML();
                    break;
                }

                // Cleanup form values
                $vars = tldUtils::cleanupFormInput($form->exportValues());
                $fields = ['cust_part_decision', 'cust_service_decision', 'spr_id', 'csr_id', 'status', 'closure_type', 'dt_cust_to_decide'];
                // Update SB Line
                $e = $line->update($vars, $fields);
                if (is_string($e)) {
                    $DEFAULT_ERROR[] = "ERROR: Problem updating...<br/>Reason: $e";
                    break;
                }

                //Logging
                $FIELD_DESIGNATION = [
                    'cust_part_decision' => 'Customer Part Decision',
                    'cust_service_decision' => 'Customer Service Decision',
                    'spr_id' => 'SPR ID#',
                    'csr_id' => 'CSR ID#',
                    'status' => 'Status',
                    'closure_type' => 'Closure Type',
                    'dt_cust_to_decide' => 'Date Customer to Decide',
                ];
                $line_updated = new tldSB_Line($lid);
                $line_header_updated = $line_updated->getHeader();
                $logs = [];
                //Log if updated header <> original header
                foreach ($fields as $field) {
                    if ($line_header_updated[$field] != $line_header[$field]) {
                        $logs[] = "<li><b>{$FIELD_DESIGNATION[$field]}</b> from '{$line_header[$field]}' to '{$line_header_updated[$field]}'</li>";
                    }
                }
                if (count($logs)) {
                    $msg = "SB Line#$lid was manually edited:<br><ul>" . implode('', $logs) . '</ul>';
                    $e = $line->addLogEntry(
                        $user->getID(),
                        TldDatabase::escape($msg)
                    );
                    $s = $sb->addLogEntry(
                        $user->getID(),
                        TldDatabase::escape("SB Line#$lid was edited")
                    );
                    if (is_string($e) || is_string($s)) {
                        $DEFAULT_ERROR[] = "ERROR: Problem adding logs...<br/>Reason: $e $s";
                    }
                    $body .= '<br>' . $msg;
                }
                $body .= "SB Line#$lid edited successfully!";
                break;
            case 'NOT':
                $DEFAULT_TITLE .= "\NOT";
                if (isset($m[4]) && $m[4] === 'getEmailPDF') {
                    if (empty($notid) || !is_numeric($notid)) {
                        $DEFAULT_ERROR[] = 'ERROR: parameters sent empty or invalid';
                        break;
                    }
                    $sbNot = new tldSBNOT($notid);
                    if ($sbNot->isEmpty()) {
                        $DEFAULT_ERROR[] = 'ERROR: SB notification log not found';
                        break;
                    }
                    if ($sbNot->getParentID() !== $lid) {
                        $DEFAULT_ERROR[] = "ERROR: SB notification log #$notid not found in SB Line#$lid";
                        break;
                    }
                    $file = new tldFile($sbNot->getFileID());
                    if ($file->isEmpty()) {
                        $DEFAULT_ERROR[] = 'ERROR: File not found';
                        break;
                    }
                    $file->download("SB_$id\_notification_{$line->getERSN()}_{$sbNot->itsHeader['dt']}.pdf");
                    exit;
                }
                $body .= include 'sb.not.listing.inc.tpl.php';
                break;
        }
        break;
    case 'signature':
        $JS_INCLUDE=["/shared/javascript/overlib/overlib.js"];

        $DEFAULT_TITLE .= "\Signature";

        switch ($m[3]) {
            case 'sign':
                $status = TldDatabase::escape($_GET['status']); // likely useless
                $sid = TldDatabase::escape($_GET['sid']);
                // Take the signature entry and check
                $signature = new tldSB_Signature($sid);
                if ($signature->isEmpty() || $signature->getParentID() !== $sb->getID()) {
                    $DEFAULT_ERROR[] = 'ERROR: record not found or invalid';
                    break;
                }

                $sbLines = $sb->getLinesByConstraints();
                $ssoServiceEmail = [];
                foreach ($sbLines as $line){
                    $erId = $line['er_id'];
                    $er = new tldEquipment($erId);
                    $ssoServiceId = tldLocation::getIDByLocation($er->getSSOService());
                    $ssoService = new tldLocation($ssoServiceId);
                    $ssoServiceEmail[] = $ssoService->getServiceEmail();
                }
                $notifiedSSOServiceEmail = array_unique($ssoServiceEmail);


                $signatureStatus = $signature->getStatus();
                $sbStatus = $sb->getStatus();
                if (!in_array($sbStatus, tldSB3::getAllowedSignatureStatuses($signatureStatus), true)) {
                    $DEFAULT_ERROR[] = "ERROR: Can not sign for $signatureStatus when SB in $sbStatus";
                    break;
                }
                // Check if already done
                $ssoId = $signature->getSSOID();
                $sso = new tldLocation($signature->getSSOID());
                if ((int)$signature->getUserID() !== 0) {
                    $DEFAULT_ERROR[] = "ERROR: {$sso->getShortName()} already signed";
                    break;
                }
                // Different check following the status
                // Check if allowed
                if (!$user->isInGroup(['role_CSD'])
                    && (!$user->isInGroupLevel('role_EVP', $sso->getERP()) || 'SSD_DECISION' !== $signatureStatus)
                    && (!$user->isInGroupLevel('role_CSM', $sso->getERP()) || 'CSM_APPROVAL' !== $signatureStatus)
                ) {
                    $DEFAULT_ERROR[] = 'ERROR: You do not have permissions for SSO ' . $sso->getShortName();
                    break;
                }
                // Check that all ER selected by SSD in the SSO
                if (in_array($sbStatus, ['SSD_DECISION', 'PARTIAL_IMPLEMENTATION'], true) && !$sb->isFullySelected($ssoId)) {
                    $DEFAULT_ERROR[] = "ERROR: {$sso->getShortName()} Implementation decision not fully done";
                    $DEFAULT_ERROR[] = "<a href=\"/en/private/product_support/index.ps.php?m[0]=sb&m[1]=view&m[2]=summary&m[3]=selection&id=$id\">Click here to make decision on affected ER in summary</a>";
                    break 2;
                }

                // Check if there is at list a SSD Decision C to notify SPM
                $lines = $sb->getLinesByConstraints('1=1', ['where' => "sso.id={$sso->getID()}"]);
                foreach ($lines as $line) {
                    if ($line['part_decision'] === 'C') {
                        $toSso = (new tldGroup('role_SPM', $sso->getERP()))->getEmailList();
                        $to = array_merge($toSso, $notifiedSSOServiceEmail);
                        if (!empty($to)) {
                            $sb->notifyTLD(
                                $to,
                                'noreply@tld-gse.com',
                                "SB#$id has been identified as a potential spare parts sale.",
                                <<<EOF
Please look at SB#$id for which your SSD has identified a potential spare parts sale.
Investigate and leverage this sales opportunities.
Do not hesitate to share with other Spare Part Hubs if you feel this particular SB has some sales potential.
EOF,
                            );
                        }
                        break;
                    }
                }
                // Finally sign
                $e = $signature->update([
                    'user_id' => $user->getID(),
                    'dt' => date('Y-m-d H:i:s'),
                ]);
                if (is_string($e)) {
                    $DEFAULT_ERROR[] = "ERROR: Internal error: $e";
                    break;
                }
                $log = "$signatureStatus signed for {$sso->getShortName()}";
                $sb->addLogEntry($user->getID(), $log);
                $body .= "<br>$log";

                // Check if fully sign --------------->
                // Change SB status automatically
                if (is_string($newStatus = $sb->getAllowedStatus(['sso' => $ssoId]))) {
                    $DEFAULT_ERROR[] = "ERROR: Problem checking allowed status automatically...<br/>Reason: $allowedStatus";
                    break;
                }
                $newStatus = end($newStatus);
                // Change status automatically
                $e = $sb->updateStatus(0, $newStatus, ['sso' => $ssoId]);
                if (is_string($e)) {
                    $DEFAULT_ERROR[] = "ERROR: Problem updating status automatically...<br/>Reason: $e";
                    break;
                }

                if ($sb->isFullySignedByStatus($signatureStatus)) {
                    $sb->addLogEntry(0, $sb->getStatus() . ' fully signed');
                    $body .= "<p>SB was fully signed -> SB status updated to $newStatus</p>";
                    if ($signatureStatus === 'SSD_DECISION') {
                        $sb->notifyTeamCSM();
                    }
                }
                break;
            case 'log':
                if (!$user->isInGroup(['role_CSM'])) {
                    $DEFAULT_ERROR[] = 'ERROR: You do not have permissions';
                    break;
                }
                $sid = (int)TldDatabase::escape($_GET['sid']);

                $form = new HTML_QuickForm('addLog', 'post');
                $form->addElement('hidden', 'm[0]', 'sb');
                $form->addElement('hidden', 'm[1]', 'view');
                $form->addElement('hidden', 'm[2]', 'signature');
                $form->addElement('hidden', 'm[3]', 'log');
                $form->addElement('hidden', 'id', $id);
                $form->addElement('hidden', 'sid', $sid);
                $form->addElement('header', 'header', 'Add log');
                $form->addElement('textarea', 'comment', 'Comment');
                $form->addElement('submit', 'btnSubmit', 'Submit');

                if (!$form->validate() && !$form->isSubmitted()) {
                    $body .= $form->toHTML();
                    break;
                }
                $vars = $form->exportValues();
                $e = $sb->addSignLogEntry(
                    $user->getID(),
                    TldDatabase::escape($vars['comment']),
                    TldDatabase::escape($vars['sid'])
                );
                if (is_string($e)) {
                    $DEFAULT_ERROR[] = "ERROR: Problem adding logs...<br/>Reason: $e";
                }
                break;
        }
        // Display signatures list
        $cells = [];
        $cells[] = _getSignatoryReport('CSM_APPROVAL');
        $cells[] = _getSignatoryReport('SSD_DECISION');
        $report = new tldHTMLTable(
            $cells,
            [
                'cols' => 2,
                'attribs' => [
                    'table' => " width='100%'",
                    'tr' => " bgcolor='#FFFFFF'",
                ],
            ]
        );
        $body .= $report->fetch();
        $canReject = ($user->isInGroup(['role_CSM']) && $sb->getStatus() === 'CSM_APPROVAL')
            || ($user->isInGroup(['role_EVP']) && $sb->getStatus() === 'SSD_DECISION');

        if ($canReject) {
            $body .= "<br><br><b><a href='$php_self?m[0]=sb&m[1]=view&m[2]=status&m[3]=reject&id=$id'>Click here to Reject this SB</a></b>";
        }
        break;
    case 'edit':
        $DEFAULT_TITLE .= "\Edit";
        // Check Permissions
        if (!$user->isInGroup(['role_PSM', 'role_PSE', 'role_PSA', 'role_CSD'])) {
            $DEFAULT_ERROR[] = 'WARNING: You do not have permissions';
            break;
        }
        $yesNoList = ['' => '', 'Y' => 'Y', 'N' => 'N'];
        $optionDisabled = $header['category'] === 'COMPULSORY' ? ['disabled' => 'disabled'] : [];
        $form = new HTML_QuickForm('frmNew');
        $form->addElement('hidden', 'm[0]', 'sb');
        $form->addElement('hidden', 'm[1]', 'view');
        $form->addElement('hidden', 'm[2]', 'edit');
        $form->addElement('hidden', 'id', $id);
        $form->addElement('header', 'headform', 'Edit SB');
        if (!$sb->isAnyImplementationStatus()) {

            $form->addElement('select', 'bu_id', 'Factory', ['' => ''] + tldLocation::getFactoryList('smartyOptionsIDLocation'));
            $form->addElement('select', 'category', 'Category', ['' => ''] + tldSB3::getCategoryList(),
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
            $form->addElement('select', 'type', 'Type', ['' => ''] + tldSB3::getTypeList(), ['id' => 'sb_field_type'] + $optionDisabled);
            $form->addElement('select', 'ifactor', 'IFactor', ['' => ''] + tldSB3::getIFactorList(), ['id' => 'iFactor']);
            $form->addElement('select', 'confidential', 'Confidential?', $yesNoList);
            $form->addElement('text', 'title', 'Title');
            $form->addElement('textarea', 'description', 'Description', ['wrap' => 'VIRTUAL', 'cols' => '60', 'rows' => '5']);
            $form->addElement('text', 'labor', 'Labour (in minutes)');
            $form->addElement('select', 'nb_tech_needed', 'Technician needed', ['0' => '0', '1' => '1', '2' => '2', '3' => '3', '4' => '4', '5' => '5']);
            $form->addElement('select', 'parts_needed', 'Parts needed?', $yesNoList);
            $requiredFields = ['bu_id', 'category', 'ifactor', 'confidential', 'title', 'description', 'labor', 'nb_tech_needed', 'parts_needed'];
            foreach ($requiredFields as $field) {
                $form->addRule($field, 'Required', 'required');
            }
        }
        $form->addElement('select', 'factory_part_availability_status', 'Factory parts availability', ['' => ''] + tldSB3::getPartsAvailabilityChoicesList());
        if ($sb->getStatus() !== 'PENDING') {
            $form->addRule('factory_part_availability_status', 'Required', 'required');
        }
        $form->addElement('submit', 'btnSubmit', 'Submit');

        $form->setDefaults($header);

        if (!$form->validate()) {
            $body .= $form->toHTML();
            break;
        }

        $rawVars = $form->exportValues();
        $vars = tldUtils::cleanupFormInput($rawVars);
        $fields = ['bu_id', 'category', 'category_reason', 'type', 'ifactor', 'confidential', 'title', 'description', 'labor', 'nb_tech_needed', 'parts_needed', 'factory_part_availability_status'];
        if ($sb->isAnyImplementationStatus()) {
            $fields = ['factory_part_availability_status'];
        }
        $e = $sb->update($vars, $fields);
        if (is_string($e)) {
            $DEFAULT_ERROR[] = "ERROR: Problem updating...<br/>Reason: $e";
            break;
        }
        // If not PENDING, log and notify
        if (!$sb->isPendingStatus()) {
            $fl_update = false;
            $fieldDescriptionList = $sb->getFieldDescriptionList();
            $changeLog = 'Info updated:<br><ul>';
            // Check what was changed
            foreach ($fields as $field) {
                if ($rawVars[$field] == $header[$field]) {
                    continue;
                }
                $fl_update = true; // flag there was a change
                $changeLog .= "<li>{$fieldDescriptionList[$field]} from '{$header[$field]}' to '{$rawVars[$field]}'</li>";
            }
            $changeLog .= '</ul>';
            // if there was a real modification
            if ($fl_update) {
                // Log it
                $sb->addLogEntry($user->getID(), TldDatabase::escape($changeLog));
                // Notify SSD
                $sb->notifyTLD(
                    array_merge($sb->getEVPRecipients(), $sb->getCSMRecipients()),
                    $user->getEmail(),
                    "SB#{$sb->getID()} updated when status is {$sb->getStatus()}",
                    "SB#{$sb->getID()} has been updated by {$user->getFullname()}<br><br>" . $changeLog,
                    $user->getEmail()
                );
                // Confirm
                $body .= '<br/>SB update notified to SSD impacted';
            }
        }
        $body .= '<br/>SB updated successfully!';
        $sb->refresh();
        $body .= _getGeneralView();
        break;
    case 'duplicate':
        if (!$user->isInGroup(['role_PSM', 'role_PSE', 'role_PSA', 'gg_ADMIN', 'role_CSD'])) {
            $DEFAULT_ERROR[] = 'ERROR: You do not have permissions';
            break;
        }
        $form = new HTML_QuickForm('frmNew', 'post');
        $form->addElement('hidden', 'm[0]', 'sb');
        $form->addElement('hidden', 'm[1]', 'view');
        $form->addElement('hidden', 'm[2]', 'duplicate');
        $form->addElement('hidden', 'id', $id);
        $form->addElement('header', 'title', "Duplicate SB#$id ?");
        $form->addElement('select', 'confirm', 'Do you confirm?',
            ['' => '', 'Y' => 'Yes, I confirm']);
        $form->addRule('confirm', 'Required', 'required');
        $form->addElement('submit', 'btnSubmit', 'Submit');

        if (!$form->validate()) {
            $body .= $form->toHTML();
            break;
        }

        $e = $sb->duplicate($user->getID());
        if (is_string($e)) {
            $DEFAULT_ERROR[] = "ERROR: SB not duplicated. Reason: $e";
            break;
        }
        $body .= <<<EOF
<p>SB#$e created successfully from duplication of SB#$id<br>
<a href="$php_self?m[0]=sb&m[1]=view&id=$e">Click here to see SB#$e</a></p>
EOF;
        break;
    case 'delete':
        if (!$sb->isPendingStatus() || !$user->isInGroup(['role_PSM', 'role_PSE', 'role_PSA', 'gg_ADMIN', 'role_CSD'])) {
            $DEFAULT_ERROR[] = 'ERROR: You do not have permissions';
            break;
        }
        $form = new HTML_QuickForm('frmNew', 'post');
        $form->addElement('hidden', 'm[0]', 'sb');
        $form->addElement('hidden', 'm[1]', 'view');
        $form->addElement('hidden', 'm[2]', 'delete');
        $form->addElement('hidden', 'id', $id);
        $form->addElement('header', 'title', "Delete SB#$id ?");
        $form->addElement('select', 'confirm', 'Do you confirm?',
            ['' => '', 'Y' => 'Yes, I confirm']);
        $form->addRule('confirm', 'Required', 'required');
        $form->addElement('submit', 'btnSubmit', 'Submit');

        if (!$form->validate()) {
            $body .= $form->toHTML();
            break;
        }
        // Save the headers before deleting
        $printedVersion = $sb->getPrintVersion();

        $e = $sb->delete();
        if (is_string($e)) {
            $DEFAULT_ERROR[] = "ERROR: SB not deleted. Reason: $e";
            break;
        }
        // Notify PSM & COO
        $gpsm = new tldGroup('role_PSM', $sb->getFactoryERP());
        $TO = $gpsm->getEmailList();
        $gpse = new tldGroup('role_PSE', $sb->getFactoryERP());
        $TO = $gpse->getEmailList();
        $gpsa = new tldGroup('role_PSA', $sb->getFactoryERP());
        $TO = $gpsa->getEmailList();
        $gcoo = new tldGroup('role_COO', $sb->getFactoryERP());
        $CC = $gcoo->getEmailList();
        $msg = <<<EOF
SB#$id has been deleted by {$user->getFullname()}
<br><br>
$printedVersion
EOF;
        $sb->notifyTLD($TO, 'noreply@tld-gse.com', "SB#$id deleted", $msg, $CC, ['noPrintedVersion' => true]);
        $body .= "SB#$id deleted successfully!";
        break;
    case 'tasks':
        $DEFAULT_TITLE .= "\Tasks";
        $DEFAULT_MENU .= <<<EOF
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<a href="/en/private/calendar/calendar.php?m[0]=tasks&m[1]=form&m[2]=newTask&module=SB3&parent_id=$id">New Task</a>
EOF;
        $sess['calendar']['tasks'] = $sb->getTasks();
        $form = new tldReportMultiLevel(
            $sess['calendar']['tasks'],
            ['status'],
            [
                'id' => 'Task#',
                'status' => 'Status',
                'due_date' => 'Due',
                'task' => 'Task',
                'assignee_fullname' => 'Assignee',
            ],
            [
                'passField' => 'id',
                'title' => 'Tasks',
                'url' => '/en/private/calendar/calendar.php?m[0]=tasks&m[1]=task&m[2]=view&id=',
            ]
        );
        $body .= $form->fetch();
        break;
    case 'logs':
        if ($user->isInGroup(['role_PSM', 'role_PSE', 'role_PSA'])) {
            $DEFAULT_MENU .= <<<EOF
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<a href="$php_self?m[0]=sb&m[1]=view&m[2]=logs&m[3]=add&id=$id">Add</a>
EOF;
        }

        if ($m[3] === 'add') {
            if (!$user->isInGroup(['role_PSM', 'role_PSE', 'role_PSA'])) {
                $DEFAULT_ERROR[] = 'ERROR: You do not have permissions';
                break;
            }
            $form = new HTML_QuickForm('frm', 'post');
            $form->addElement('hidden', 'm[0]', 'sb');
            $form->addElement('hidden', 'm[1]', 'view');
            $form->addElement('hidden', 'm[2]', 'logs');
            $form->addElement('hidden', 'm[3]', 'add');
            $form->addElement('hidden', 'id', $id);
            $form->addElement('header', 'header', 'Add log');
            $form->addElement('textarea', 'comment', 'Comment');
            $form->addElement('submit', 'btnSubmit', 'Submit');

            if (!$form->validate() && !$form->isSubmitted()) {
                $body .= $form->toHTML();
                break;
            }
            $vars = $form->exportValues();
            $e = $sb->addLogEntry(
                $user->getID(),
                TldDatabase::escape($vars['comment'])
            );
            if (is_string($e)) {
                $DEFAULT_ERROR[] = "ERROR: Problem adding logs...<br/>Reason: $e";
            }
        }

        $DEFAULT_TITLE .= "\Logs";
        $report = new tldReportColumnar(
            $sb->getLog(),
            [
                'xItems' => [
                    'id' => 'ID#',
                    'date' => 'Date',
                    'poster_fullname' => 'Poster',
                    'comment' => 'Comment',
                ],
            ]
        );
        $body .= $report->fetch();
        break;
    case 'files':
        $DEFAULT_TITLE .= "\Files";
        $DEFAULT_MENU .= <<<EOF
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<a href="/en/private/common/index.php?m[0]=files&m[1]=form&m[2]=newFile&module=SB3&parent_id=$id">Add Customer File</a>
&nbsp;|&nbsp;<a href="/en/private/common/index.php?m[0]=files&m[1]=form&m[2]=newFile&module=SB3&parent_id=$id&level=1">Add TLD File</a>
EOF;

        $body .= _getFileListing($sb->getCustomerFiles(), 'Customer files (Visible in EXTRANET)');
        $body .= _getFileListing($sb->getTLDFiles(), 'TLD files (Visible to TLD only! DO NOT SHARE)');
        break;
    case 'links':
        $DEFAULT_MENU .= <<<EOF
    <br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
    <a href="/en/private/common/index.php?m[0]=links&m[1]=form&m[2]=newLink&module=SB3&parent_id=$id">New Link</a>
EOF;
        $report = new tldReportColumnar(
            $sb->getLinksFromHere(),
            [
                'xItems' => [
                    'id' => 'ID#',
                    'type' => 'Module',
                    'item' => 'Ref#',
                    'dsca' => 'Description',
                ],
                'title' => 'Links FROM Here...',
                'links' => [
                    'id' => '/en/private/common/index.php?m[0]=links&m[1]=view&id=',
                ],
            ]
        );
        $body .= $report->fetch();
        $report = new tldReportColumnar(
            $sb->getLinksToHere(),
            [
                'xItems' => [
                    'id' => 'ID#',
                    'module' => 'Module',
                    'parent_id' => 'Ref#',
                    'dsca' => 'Description',
                ],
                'title' => 'Links TO Here...',
                'links' => [
                    'id' => '/en/private/common/index.php?m[0]=links&m[1]=view&reversed=1&id=',
                ],
            ]
        );
        $body .= $report->fetch();
        break;
    case 'parts':
        $DEFAULT_TITLE .= "\Parts";

        if (!$sb->isPartsNeeded()) {
            $DEFAULT_ERROR[] = 'ERROR: No parts are needed for this SB';
            break;
        }

        $DEFAULT_MENU .= <<<EOF
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<a href="/en/private/common/index.php?m[0]=parts&m[1]=add&module=SB3&parent_id=$id">Add Parts</a>
EOF;

        $factory_erp = tldLocation::getERPByLocation($header['factory']);
        $DEFAULT_ERROR[] = 'WARNING: Those parts will be used for SPR & CSR creation';
        $report = new tldReportColumnar(
            $sb->getParts(),
            [
                'xItems' => [
                    'pn' => 'Part Number',
                    'dsc' => 'Description',
                    'qty' => 'Quantity',
                ],
                'title' => 'SB Parts',
                'links' => [
                    'pn' => [
                        'url' => "/en/private/manufacturing/eng/dev.php?m[0]=bom&m[1]=view&erp=$factory_erp",
                        'params' => ['pn' => 'pn'],
                    ],
                ],
                'functions' => [
                    'Edit' => [
                        'url' => '/en/private/common/index.php?m[0]=parts&m[1]=view&m[2]=edit',
                        'param' => ['id' => 'id'],
                        'img' => '/shared/icons/miscellaneous/edit.png',
                    ],
                    'Delete' => [
                        'url' => '/en/private/common/index.php?m[0]=parts&m[1]=view&m[2]=delete',
                        'param' => ['id' => 'id'],
                        'confirmPopup' => 'Are you sure to delete?',
                        'img' => '/shared/icons/miscellaneous/delete.png',
                    ],
                ],
                'showItemNumbers' => true,
            ]
        );
        $body .= $report->fetch();
        break;
    default:
        $body .= _getGeneralView();
        break;
}


function _getGeneralView()
{
    global $sb, $id, $php_self;
    // General
    $report = new tldAssocTable(
        $sb->getHeader(),
        [
            'id' => 'SB#',
            'dt' => 'Date',
            'poster_fullname' => 'Poster',
            'factory' => 'Factory',
            'status' => 'Status',
            'category' => 'Category',
            'category_reason' => "Further indications for CSMs\n(SB category rationale, ER coverage, etc.)\nNot viewed by customers",
            'type' => 'Type',
            'confidential' => 'Confidential',
            'ifactor' => 'IFactor',
            'title' => 'Title',
            'description' => 'Description (this field is visible by customer in EXTRANET platform)',
            'labor' => 'Labor (in minutes)',
            'nb_tech_needed' => 'Technician needed',
            'parts_needed' => 'Parts needed',
            'factory_part_availability_status' => 'Parts Availability Status',
        ],
        ['title' => "SB#$id Details"]
    );
    $cells[0] = $report->fetch();
    // Add files & Parts
    $sb = new tldSB3($id);
    $header = $sb->itsHeader;
    $factory_erp = tldLocation::getERPByLocation($header['factory']);
    $report = new tldReportColumnar(
        $sb->getParts(),
        [
            'xItems' => [
                'pn' => 'Part Number',
                'dsc' => 'Description',
                'qty' => 'Quantity',
            ],
            'title' => 'SB Parts',
            'links' => [
                'pn' => [
                    'url' => "/en/private/manufacturing/eng/dev.php?m[0]=bom&m[1]=view&erp=$factory_erp",
                    'params' => ['pn' => 'pn'],
                ],
            ],
        ]
    );
    $cells[1] = $report->fetch();
    $report = new tldReportColumnar(
        $sb->getCustomerFiles(),
        [
            'xItems' => [
                'id' => 'File ID',
                'date' => 'Date',
                'poster_fullname' => 'Poster',
                'description' => 'Description',
                'filename' => 'Filename',
            ],
            'links' => [
                'id' => '/en/private/common/index.php?m[0]=files&m[1]=view&m[2]=out&id=',
            ],
            'title' => 'SB Customer Files',
        ]
    );
    $cells[1] .= $report->fetch();
    $report = new tldReportColumnar(
        $sb->getTLDFiles(),
        [
            'xItems' => [
                'id' => 'File ID',
                'date' => 'Date',
                'poster_fullname' => 'Poster',
                'description' => 'Description',
                'filename' => 'Filename',
            ],
            'links' => [
                'id' => '/en/private/common/index.php?m[0]=files&m[1]=view&m[2]=out&id=',
            ],
            'title' => 'SB TLD Files (DO NOT TRANSFER TO CUSTOMER)',
        ]
    );
    $cells[1] .= $report->fetch();
    // Display
    $report = new tldHTMLTable(
        $cells,
        [
            'cols' => 2,
            'attribs' => ['table' => " width='100%'", 'tr' => " bgcolor='#FFFFFF'"],
        ]
    );
    $body = $report->fetch();
    // ER Matrix stats
    $body .= _getERcountBySSOByStatusMatrix(
        tldSB_Line::countBySSOByStatusByParentID($id),
        'ER Statistics by SSO by Implementation Status Indicator',
        "$php_self?m[0]=sb&m[1]=view&m[2]=summary&m[4]=bySSObyStatus&id=$id",
        ['xItems' => tldSB_Line::getStatusList()]
    );
    return $body;
}

function _getFileListing($rows, $title)
{
    $report = new tldReportColumnar(
        $rows,
        [
            'xItems' => [
                'id' => 'File ID',
                'date' => 'Date',
                'poster_fullname' => 'Poster',
                'description' => 'Description',
                'filename' => 'Filename',
            ],
            'links' => [
                'id' => '/en/private/common/index.php?m[0]=files&m[1]=view&m[2]=out&id=',
            ],
            'title' => $title,
            'functions' => [
                'Edit' => [
                    'url' => '/en/private/common/index.php?m[0]=files&m[1]=view&m[2]=edit',
                    'param' => ['id' => 'id'],
                    'img' => '/shared/icons/miscellaneous/edit.png',
                ],
                'Delete' => [
                    'url' => '/en/private/common/index.php?m[0]=files&m[1]=view&m[2]=del',
                    'param' => ['id' => 'id'],
                    'confirmPopup' => 'Are you sure to delete?',
                    'img' => '/shared/icons/miscellaneous/delete.png',
                ],
            ],
        ]
    );
    return $report->fetch();
}

function _generateChangeListLog($fields, $DataBefore, $DataAfter)
{
    $list = [];
    foreach ($fields as $field => $label) {
        if (!isset($DataBefore[$field]) || !isset($DataAfter[$field])) {
            continue;
        }
        if ($DataBefore[$field] == $DataAfter[$field]) {
            continue;
        }
        $list[] = "<li><b>$label</b> from {$DataBefore[$field]} to {$DataAfter[$field]}</li>";
    }
    // if nothing changed
    if (empty($list)) {
        return false;
    }
    return '<ul>' . implode('', $list) . '</ul>';
}

function _getSignatoryReport($status)
{
    global $php_self, $sb, $id;

    $options = [
        'xItems' => [
            'sso_name' => 'SSO impacted',
            'dt' => 'Signed date',
            'signatory_fullname' => 'Signed by',
        ],
        'title' => "$status signatures",
        'functions' => [
            'Sign' => [
                'url' => "$php_self?m[0]=sb&m[1]=view&m[2]=signature&m[3]=sign&id=$id&status=$status",
                'param' => ['sid' => 'id'],
            ],
        ],
    ];

    $rows = $sb->getSignatureListByStatus($status);
    foreach ($rows as &$row) {
        $html = tldUtils::renderHtmlOrNl2br($row['logs']);
        if ($row['logs'] !== null) {
            $row['log'] = "<img src='/shared/bluesphere/16x16/actions/toggle_log.png' onmouseover=\"overlib('$html',WIDTH, 350, OFFSETX, 30, VAUTO, FGCOLOR, '#eeeeee', BGCOLOR, 'gray', CAPCOLOR, '#dedede');\" onmouseout=\"nd();\"/>";
        }
    }

    $options['xItems'] = [
        'sso_name' => 'SSO impacted',
        'dt' => 'Signed date',
        'signatory_fullname' => 'Signed by',
        'log' => 'Log',
    ];
    $options['functions'] = [
        'Sign' => [
            'url' => "$php_self?m[0]=sb&m[1]=view&m[2]=signature&m[3]=sign&id=$id&status=$status",
            'param' => ['sid' => 'id'],
        ],
        'Add log' => [
            'img' => '/shared/icons/application/add.png',
            'url' => "$php_self?m[0]=sb&m[1]=view&m[2]=signature&m[3]=log&id=$id",
            'param' => ['sid' => 'id'],
        ],
    ];
    $report = new tldReportColumnar($rows, $options);

    return $report->fetch();
}

function _getSSOFromUserByGroups($group)
{
    global $user;
    $SSO_USER_LIST = [];
    // Get all SSO
    $ssoList = tldLocation::getSalesOrgList('smartyOptionsIDLocation');
    // If superuser, ALL SSO
    if ($user->isInGroup(['superuser', 'role_PSM', 'role_PSE', 'role_PSA', 'role_RME', 'role_CSD'])) {
        return $ssoList;
    }
    // Get groups BU
    $userGroups = $user->getGroups();
    foreach ($userGroups as $userGroup) {
        // Check applicable groups
        if (!in_array($userGroup['group_name'], $group, true)) {
            continue;
        }
        // Check if company is really an SSO
        if (!in_array($userGroup['bu_name'], $ssoList, true)) {
            continue;
        }
        // Add it to the list
        $SSO_USER_LIST[$userGroup['bu_id']] = $userGroup['bu_name'];
        // Hack for TLD AME SCM to be considered as TLD AME
        if ($userGroup['bu_id'] === '44') {
            $SSO_USER_LIST[11] = 'TLD AME';
        }
    }
    return $SSO_USER_LIST;
}
