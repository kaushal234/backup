<?php

declare(strict_types=1);
$_TITLE .= " \ "._('Approvers');
$_MENU .= "
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<a href=\"$php_self?m[0]=view&m[1]=approver&id=$id\">"._('Home')."</a>&nbsp;|&nbsp;
<a href=\"$php_self?m[0]=view&m[1]=approver&m[2]=edit&id=$id\">"._('Edit').'</a>
';

$approverList = $dms->getApprover();
$approverListAsStepVals = [];
foreach ($approverList as $stepRev) {
    $approverListAsStepVals[$stepRev['step']] = [
        'id' => $stepRev['id'],
        'step' => $stepRev['step'],
        'uid' => $stepRev['uid'],
    ];
}

switch ($m[2] ?? null) {
    case 'edit':
        $_TITLE .= " \ "._('Edit');
        // Check access
        if (!_isOwner()) {
            $_ERROR[] = _('You do not have permissions to use this feature');
            break;
        }
        // Check status
        if (!_isInRevision()) {
            $_ERROR[] = _('Can not update if DMS status is not in REVISION');
            break;
        }
        // Get listing
        $peopleList = tldDirectory::getUserlist('smartyOptions');
        // Get form
        $form = new HTML_QuickForm('add', 'post');
        $form->addElement('hidden', 'm[0]', 'view');
        $form->addElement('hidden', 'm[1]', 'approver');
        $form->addElement('hidden', 'm[2]', 'edit');
        $form->addElement('hidden', 'id', $id);
        $form->addElement('header', 'title', _('Revision approvers'));
        $max_step = 11;
        for ($i = 1; $i < $max_step; ++$i) {
            $form->addElement('header', "frmTitleRev$i", _('Revision Step')." $i");
            $form->addElement('select', "approver[$i]", _('Approver'), ['' => ''] + $peopleList);
            $form->setDefaults(["approver[$i]" => $approverListAsStepVals[$i]['uid']]);
        }
        $form->addRule('approver[1]', _('Field is required'), 'required');
        $form->addElement('reset', 'btnReset', _('Reset'));
        $form->addElement('submit', 'btnSubmit', _('Submit'));

        if (!$form->validate()) {
            $_BODY = $form->toHTML();
            break;
        }

        $vars = tldUtils::cleanupFormInput($form->exportValues());
        $vars['approver'] = tldUtils::cleanupFormInput($vars['approver']);
        $toDo = [];
        $toLog = [];
        // Loop max steps and see what needs to be done
        for ($i = 1; $i < $max_step; ++$i) {
            // Case the step is deleted
            if (!empty($approverListAsStepVals[$i]) && empty($vars['approver'][$i])) {
                $prevUser = new tldUser($approverListAsStepVals[$i]['uid']);
                $toDo[] = sprintf(_('Step %s deleted'), $i);
                $toLog[] = "Step $i deleted for ".$prevUser->getFullname();
            }
            // Case the step is still alive
            elseif (!empty($approverListAsStepVals[$i]) && !empty($vars['approver'][$i])) {
                // But the approver is updated
                if ($approverListAsStepVals[$i]['uid'] != $vars['approver'][$i]) {
                    $approverUser = new tldUser($vars['approver'][$i]);
                    $prevUser = new tldUser($approverListAsStepVals[$i]['uid']);
                    $toDo[] = sprintf(_('Step %s updated with %s'), $i, $approverUser->getFullname());
                    $toLog[] = "Step $i updated from ".$prevUser->getFullname().' to '.$approverUser->getFullname();
                }
            }
            // case the step is added
            elseif (empty($approverListAsStepVals[$i]) && !empty($vars['approver'][$i])) {
                $approverUser = new tldUser($vars['approver'][$i]);
                $toDo[] = sprintf(_('Step %s added with %s'), $i, $approverUser->getFullname());
                $toLog[] = "Step $i added for ".$approverUser->getFullname();
            }
        }
        // Check if there is changes
        if (!count($toDo)) {
            $_BODY = _('No update required');
            break;
        }
        // Delete ALL
        $e = $dms->deleteApprovers();
        if (is_string($e)) {
            $_ERROR[] = _('Internal error').'<br>'._('Reason').": $e";
            break;
        }
        // Add ALL
        $i = 0;
        foreach ($vars['approver'] as $stepNumber => $uid) {
            if (empty($uid)) {
                continue;
            }
            ++$i;
            $e = $dms->addApprover(['step' => (int) $i, 'uid' => (int) $uid]);
            if (is_string($e)) {
                $_ERROR[] = sprintf(_('Approver step %s not added').'<br>'._('Reason').": $e", $i);
            }
        }
        if (empty($_ERROR)) {
            $_CONF[] = _('Revision approvers successfully updated and steps ordered');
        }
        $_BODY = implode('<br>', $toDo);
        // Log
        $log = 'Approver steps updated:<br><ul>';
        foreach ($toLog as $logText) {
            $log .= "<li>$logText</li>";
        }
        $log .= '</ul>';
        _addLog($user->getID(), $log);
        break;
    default:
        $report = new tldReportColumnar(
            $approverList,
            [
                'xItems' => [
                    'step' => _('Step#'),
                    'approverFullname' => _('Approver'),
                ],
            ]
        );
        $_BODY = $report->fetch();
        break;
}
