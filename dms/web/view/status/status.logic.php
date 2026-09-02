<?php

declare(strict_types=1);
$_TITLE .= " \ "._('Status');

// Check access
if (!_isOwner()) {
    $_ERROR[] = _('You do not have permissions to use this feature');

    return;
}

// Get listing ----------------------------------------------->

$actualStatus = $dms->getStatus();
$allowedStatus = $dms->getAllowedStatus();
if (empty($allowedStatus)) {
    $_ERROR[] = sprintf(_('Can not update status, not other status allowed from %s status'), $actualStatus);

    return;
}
$allowedStatus = array_combine($allowedStatus, $allowedStatus);

// PRE checks (before form display) ------------------------------------------------>

$error = [];
// -- ACL rules
$aclList = $dms->getAclRules();
if ('INTRANET' == $dms->itsHeader['portal'] && 'CONFIDENTIAL' == $dms->itsHeader['access_type']
&& empty($aclList)) {
    $error[] = _('Reason').': '._('For Intranet CONFIDENTIAL DMS, Restriction rules must be set');
}
// -- Notification rules
$notList = $dms->getNotificationRules();
if (!count($notList)) {
    $error[] = _('Reason').': '._('Notification rules must be set');
}
// -- BU
$locationList = $dms->getBu();
if (!count($locationList)) {
    $error[] = _('Reason').': '._('Business unit coverage must be set');
}
// -- Department
$dptList = $dms->getDepartment();
if (!count($dptList)) {
    $error[] = _('Reason').': '._('Department coverage must be set');
}
// -- Approvers
$approverList = $dms->getApprover();
if (!count($approverList)) {
    $error[] = _('Reason').': '._('Approvers must be set');
}

// -- Particularity depending of status
switch ($actualStatus) {
    case 'REVISION':
        $revHeader = $dms->getApprovalRevision();
        if (empty($revHeader)) {
            break;
        }
        if ('CLOSED' !== $revHeader['status']) {
            $error[] = _('Reason').': '.sprintf(_('Approval SEQ#%s for this DMS is still OPEN, please cancel it before trying to update the status'), $revHeader['seq_id']);
            $_BODY .= '<a href="http://www.tld-gse.com/en/private/calendar/calendar.php?m[0]=tasks&m[1]=task&m[2]=view&id='.$revHeader['seq_id'].'">'._('Click here to view Sequence')."#{$revHeader['seq_id']}</a>";
            break;
        }
        break;
    case 'APPROVAL':
        // Get the SEQ#id and check if complete
        $revHeader = $dms->getApprovalRevision();
        $seq = isset($revHeader['seq_id']) ? new tldSEQ($revHeader['seq_id']) : null;
        if (null === $seq) {
            $revision = (int) $dms->itsHeader['revision'];
            $error[] = _('Reason').': '._('No sequence found for revision '.$revision + 1);
            break;
        }

        if (!$seq->isCompleted()) {
            $seqId = $revHeader['seq_id'] ?? '';
            $error[] = _('Reason').': '.sprintf(_('Status change from APPROVAL to ACTIVE should be done through revision SEQ#%s'), $seqId);
            $_BODY .= '<a href="http://www.tld-gse.com/en/private/calendar/calendar.php?m[0]=tasks&m[1]=task&m[2]=view&id='.$seqId.'">'._('Click here to view Sequence').'#'.$seqId.'</a>';
            break;
        }
        $allowedStatus = ['ACTIVE' => 'ACTIVE', 'REVISION' => 'REVISION'];
        break;
}
// If there is error display all !
if (count($error)) {
    $_ERROR[] = _('Can not update status');
    $_ERROR[] = implode('<br>', $error);

    return;
}

// Get form ----------------------------------------------->

$form = new HTML_QuickForm('add', 'post');
$form->addElement('hidden', 'm[0]', 'view');
$form->addElement('hidden', 'm[1]', 'status');
$form->addElement('hidden', 'id', $id);
$form->addElement('header', 'frmTitle', "DMS#$id: ".sprintf(_('Change Status from %s to'), $actualStatus));
$form->addElement('select', 'status', _('New status'), ['' => ''] + $allowedStatus);
switch ($actualStatus) {
    case 'REVISION':
        $_BODY .= <<<'JS'
<script type="text/javascript">
$(function(){
    $('#draftFileField').change(function(){
        // check if checkbox exists
        var checkbox = $('#useOldDraft');
        if(checkbox.length == 0) return;
        // if file to upload, uncheck checkbox, else check it
        if($(this).val().length) checkbox.attr('checked', false);
        else checkbox.attr('checked', true);
    });
});
</script>
JS;

        $form->addElement('textarea', 'purpose', _('Revision purpose'), ['rows' => 4, 'cols' => 40]);
        $form->addElement('header', 'frmTitle2', _('Draft file for sequence approval'));
        $form->addElement('file', 'draft', _('Upload file'), ['id' => 'draftFileField']);
        $form->addRule('purpose', _('Field is required'), 'required');
        // Any old revision?
        $oldRevisions = $dms->getRevisions();
        $fid = $oldRevisions[0]['src_fid'] ?? null;
        if (null !== $fid) {
            $fileObj = new tldFile($fid);
            if (!$fileObj->isEmpty()) {
                $fieldLabel = _('Or re-use editable document from previous revision');
                $fieldLabel .= '<br>('.str_replace('\\', '', $fileObj->getOriginalFileName()).')';
                $checkbox = &$form->addElement('checkbox', 'old_draft', $fieldLabel, null, ['checked' => 'checked', 'id' => 'useOldDraft']);
            }
        }
        $form->addElement('checkbox', 'notification_confirmation', '<span style="color:red">'._("I confirm I didn't forget to revise the notification rules").'</span>', null, ['id' => 'color: red']);
        break;
    case 'APPROVAL':
        // get revision obj
        $rev = new tldDMSRevision($revHeader['id']);
        $revNumber = $rev->itsHeader['revision'];
        // Get type of DMS & allowed files
        $dmsType = new tldDMSType($dms->itsHeader['type_id']);
        $srcAllowedFileExt = tldUtils::optionsByKeyValue($dmsType->getSrcAllowedExtensionList(), 'value', 'value');
        $pubAllowedFileExt = tldUtils::optionsByKeyValue($dmsType->getPubAllowedExtensionList(), 'value', 'value');
        if (empty($pubAllowedFileExt) || empty($srcAllowedFileExt)) {
            $_ERROR[] = _('Allowed extension list not configured in ADMIN, please contact MIS');

            return;
        }
        // Form
        $form->addElement('header', 'frmTitle2', sprintf(_('Attach final documents to revision #%s'), $revHeader['revision']));
        // Source
        $fileLabel = '('._('Allowed extension').': '.implode(' ', $srcAllowedFileExt).')';
        $form->addElement('file', 'src_fid', _('Final Editable Document').'<br><em style="color:grey;">'.$fileLabel.'</em>');
        // Final
        $fileLabel = '('._('Allowed extension').': '.implode(' ', $pubAllowedFileExt).')';
        $form->addElement('file', 'pub_fid', _('Final Document').'<br><em style="color:grey;">'.$fileLabel.'</em>');
        // Purpose
        $form->addElement('textarea', 'purpose', _('Revision purpose'), ['rows' => 4, 'cols' => 40]);
        // Apply rules
        $form->addRule('src_fid', _('Field is required'), 'required');
        $form->addRule('pub_fid', _('Field is required'), 'required');
        $form->addRule('purpose', _('Field is required'), 'required');
        // Default
        $form->setDefaults(['purpose' => $rev->itsHeader['purpose']]);
        break;
    case 'ACTIVE':
    case 'EXPIRED':
        $form->addElement('textarea', 'log', _('Reason'), ['rows' => 4, 'cols' => 40]);
        break;
}
$form->addRule('status', _('Field is required'), 'required');
$form->addElement('submit', 'btnSubmit', _('Submit'));

if (!$form->validate()) {
    $_BODY .= $form->toHTML();
    $_BODY .= include "$_PATH/status.definition.tpl.php";

    return;
}

$varsRaw = $form->exportValues();
$vars = tldUtils::cleanupFormInput($varsRaw);
$MSG = [];

// PRE action (before status change) ----------------------------------------------->
switch ($actualStatus) {
    case 'REVISION':
        if (!(bool) $vars['notification_confirmation']) {
            header("Location: $php_self?m[0]=view&m[1]=notification&m[2]=rules&id=$id&error=check");
            exit;
        }
        if ('ACTIVE' === $vars['status']) {
            if (null === $dms->itsHeader['revision'] || '' === $dms->itsHeader['revision']) {
                $_ERROR[] = _('No revision found');

                return;
            }
            $MSG[] = sprintf(_('Revision %s successfully reactivated'), $dms->itsHeader['revision']);
            break;
        }
        // Handle seq file to add
        if (0 === ($vars['old_draft'] ?? 0)) {
            // Get the upload File
            $file = $form->getElement('draft');
            $file_array = $file->getValue();
            if (empty($file_array['tmp_name'])) {
                $_ERROR[] = _('No file was uploaded');

                return;
            }
            $fileUpload = new basicFile($file_array['tmp_name']);
            if (!$fileUpload->isFile()) {
                $_ERROR[] = _('File uploaded is not valid');

                return;
            }
        } else {
            // Get the previous revision file
            $file_array['tmp_name'] = $fileObj->getFilepath();
            $file_array['name'] = $fileObj->getOriginalFileName();
        }

        // Create new SEQ revision

        // -- Get future revision #
        $revNum = $dms->getNextRevisionNumber();
        // -- description
        $seqDesc = <<<EOF
<p>DMS#$id - {$dms->itsHeader['typeDesc']}, {$dms->itsHeader['contributorLocation']}, {$dms->itsHeader['title']}</p>
<p>Revision #$revNum under APPROVAL, please approve attached document file in next comment...</p>
<p>Revision Purpose:<br>{$varsRaw['purpose']}</p>
EOF;
        // -- prepare steps
        $steps = [];
        foreach ($approverList as $approver) {
            $steps[] = [
                'uid' => $approver['uid'],
                'step' => $approver['step'],
                'days_to_do' => 3,
                'dsca' => 'For approval',
            ];
        }
        // -- add last step for owner
        $steps[] = [
            'uid' => $dms->getOwnerID(),
            'step' => count($approverList) + 1,
            'days_to_do' => 3,
            'dsca' => 'For owner to close sequence and move DMS to ACTIVE',
        ];
        // -- create sequence
        $eSeq = tldSEQ::insert(
            $dms->getID(),
            [
                'assignor' => $dms->itsHeader['owner_id'],
                'assignee' => $approverList[0]['uid'],
                'task' => TldDatabase::escape($seqDesc),
                'due_date' => ['value' => 15, 'unit' => 'DAY'],
                'file_info' => $file_array,
                'seq_mode' => 'USER_LEVEL',
                'steps' => $steps,
            ],
            0,
            'DMS'
        );
        if (is_string($eSeq)) {
            $_ERROR[] = _('Can not create new sequence revision').'<br>'._('Reason').": $eSeq";

            return;
        }
        $MSG[] = sprintf(_('SEQ#%s successfully created'), $eSeq);
        // -- notify
        $seq = new tldSEQ($eSeq);
        $seq->notifyAssignee(
            "SEQ#$eSeq for DMS#$id revision $revNum waiting for your approval<br>{$dms->itsHeader['title']}<br><br><a href='http://www.tld-gse.com/en/private/calendar/calendar.php?m[0]=tasks&m[1]=task&m[2]=view&id=$eSeq'>Click here to see the sequence</a>",
            "SEQ#$eSeq for DMS#$id revision $revNum waiting for your approval"
        );

        // Create new revision

        $eRev = $dms->addRevision(
            [
                'revision' => $revNum,
                'src_fid' => 0,
                'pub_fid' => 0,
                'seq_id' => $eSeq,
                'purpose' => $vars['purpose'],
            ]
        );
        if (is_string($eRev)) {
            $_ERROR[] = _('Can not create new revision').'<br>'._('Reason').": $eRev";

            return;
        }
        $MSG[] = sprintf(_('Revision %s successfully created and under approval'), $revNum);
        $dms->addLogEntry($user->getID(), "SEQ#$eSeq created for revision#$revNum");
        break;
    case 'APPROVAL':
        if ('REVISION' === $vars['status']) {
            break;
        }
        $a = [];
        // check if files was uploaded
        $fileList = [
            'src_fid' => _('Final Editable Document'),
            'pub_fid' => _('Final Document'),
        ];
        foreach ($fileList as $field => $fileLabel) {
            $file = $form->getElement($field);
            // check uploaded file
            $file_array = $file->getValue();
            if (empty($file_array['tmp_name'])) {
                $_ERROR[] = "$fileLabel: "._('File not uploaded');
                continue;
            }
            if (!is_file($file_array['tmp_name'])) {
                $_ERROR[] = "$fileLabel: "._('File uploaded not valid');
                continue;
            }
            // Check file type/extention
            $fileUploadExtension = mb_strtolower(basicFile::getExtensionFromFileName($file_array['name']));
            switch ($field) {
                case 'src_fid':
                    $allowedExtensionList = $srcAllowedFileExt;
                    break;
                case 'pub_fid':
                    $allowedExtensionList = $pubAllowedFileExt;
                    break;
            }
            if (!in_array($fileUploadExtension, $allowedExtensionList, true)) {
                $_ERROR[] = "$fileLabel: "._('File extension not valid').', '.
                    _('only the following extensions are allowed').': '.
                    implode(' ', $allowedExtensionList);
                continue;
            }

            /*
             * REMOVING PDF STAMP
             * PDFTK not available for PHP >5.3
             */
            // For final document, and if pdf, add TAG
            if ('pub_fid' === $field && 'pdf' === $fileUploadExtension) {
                $fileToStamp = new basicFile($file_array['tmp_name']);
                $fileToStamp->rename($fileToStamp->getFileName().'.pdf');
                // Stamp
                $pdftk = new tldPDFToolKit();
                $e = $pdftk->stampText($fileToStamp->getFilePath(), "DMS#$id rev $revNumber", ['background' => false]);
                if (is_string($e)) {
                    $_NOTE[] = "$fileLabel: "._('PDF file tag process error')."-> $e";
                    $file_array['tmp_name'] = $fileToStamp->getFilePath();
                } else {
                    // Re assign file variable
                    $file_array['tmp_name'] = $pdftk->itsOutputFile->getFilePath();
                }
            }
            // */
            // Upload to tldFile
            $fileUploadName = "DMS_$id\_rev_$revNumber.$fileUploadExtension";
            $fid = tldFile::upload(
                $file_array['tmp_name'],
                tldDMS::getFilePath(),
                $fileUploadName
            );
            if (is_string($fid)) {
                $_ERROR[] = "$fileLabel: "._('File not uploaded').': '.$fid;
                continue;
            }
            // Assign revision file id
            $a[$field] = $fid;
        }

        // look if there is issues with files
        if (!empty($_ERROR)) {
            $_BODY .= $form->toHTML();

            return;
        }

        // if ok, update/assign all info
        $a['purpose'] = $vars['purpose'];
        $e = $rev->update($a);
        if (is_string($e)) {
            $_ERROR[] = _('Revision informations not updated').': '.$e;
            $_BODY .= $form->toHTML();

            return;
        }
        // Liste des fields
        $fieldsToUpdate = [
            'src_fid' => 'Final Editable Document',
            'pub_fid' => 'Final Document',
            'purpose' => 'Revision purpose',
        ];
        // Prepare logs
        $flagLog = false;
        $log = 'Revision updated:<br><ul>';
        foreach ($fieldsToUpdate as $field => $label) {
            // if not change of the field, go to next one
            if ($revHeader[$field] == $a[$field]) {
                continue;
            }
            // else log
            switch ($field) {
                case 'src_fid':
                case 'pub_fid':
                    if (!isset($a[$field])) {
                        continue 2;
                    }
                    $from = 'file#'.$revHeader[$field];
                    $to = 'file#'.$a[$field];
                    break;
                default:
                    $from = $revHeader[$field];
                    $to = $a[$field];
                    break;
            }
            $log .= "<li>$label <strong>FROM</strong> \'$from\' <strong>TO</strong> \'$to\'";
            $flagLog = true;
        }
        $log .= '</ul>';
        // Log
        if ($flagLog) {
            $rev->addLogEntry($user->getID(), TldDatabase::escape($log));
        }
        $_CONF[] = _('Revision update and document attachment process completed');
        break;
}
// Finally UPDATE the status ----------------------------------------------->
$e = $dms->updateStatus($vars['status'], $user->getID(), $vars['log'] ?? null);

if ('APPROVAL' === $actualStatus && 'REVISION' === $vars['status']) {
    $e = $dms->deleteApprovalRevision();
    if (is_string($e)) {
        $_ERROR[] = _("Can not delete approval revision linked to this sequence: $e");

        return;
    }
}
if (is_string($e)) {
    $_ERROR[] = _('DMS status not updated').'<br>'._('Reason').": $e";

    return;
}
$_CONF[] = sprintf(_('DMS status successfully updated to %s'), $vars['status']);

// Confirmation message

$_BODY .= implode('<br>', $MSG);
$_BODY .= _getGeneralView();
