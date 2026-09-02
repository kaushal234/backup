<?php

declare(strict_types=1);
$_TITLE .= " \ "._('Coverage');
$_MENU .= "
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<a href=\"$php_self?m[0]=view&m[1]=coverage&id=$id\">"._('Home')."</a>&nbsp;|&nbsp;
<a href=\"$php_self?m[0]=view&m[1]=coverage&m[2]=editBu&id=$id\">"._('Edit BU')."</a>&nbsp;|&nbsp;
<a href=\"$php_self?m[0]=view&m[1]=coverage&m[2]=editDepartment&id=$id\">"._('Edit Department').'</a>
';

$dmsDptList = $dms->getDepartment();
$dmsBuList = $dms->getBu();

switch ($m[2] ?? null) {
    case 'editBu':
        $_TITLE .= " \ "._('Edit Business Unit');
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
        $buList = tldLocation::getBuListAsIdBU();
        // Get form
        $form = new HTML_QuickForm('add', 'post');
        $form->addElement('hidden', 'm[0]', 'view');
        $form->addElement('hidden', 'm[1]', 'coverage');
        $form->addElement('hidden', 'm[2]', 'editBu');
        $form->addElement('hidden', 'id', $id);
        $form->addElement('header', 'title', _('Business Unit coverage'));
        $locationField = &$form->addElement(
            'advmultiselect', 'bu', null,
            $buList,
            [
                'size' => 10,
                'class' => 'pool',
                'style' => 'width:200px;',
            ]
        );
        $locationField->setLabel([null, _('Business unit list'), _('Business unit selected')]);
        $locationField->setButtonAttributes('add', ['value' => '-->>', 'class' => 'inputCommand']);
        $locationField->setButtonAttributes('remove', ['value' => '<<--', 'class' => 'inputCommand']);
        $form->addRule('bu', _('Field is required'), 'required');
        $form->setDefaults(['bu' => tldUtils::optionsByKeyValue($dmsBuList, 'buid', 'buid')]);
        $form->addElement('reset', 'btnReset', _('Reset'));
        $form->addElement('submit', 'btnSubmit', _('Submit'));

        if (!$form->validate()) {
            $_BODY = $form->toHTML();
            break;
        }

        $vars = tldUtils::cleanupFormInput($form->exportValues());
        // Look for addition
        $toAdd = [];
        $toAdd = array_diff(
            $vars['bu'],
            tldUtils::optionsByKeyValue($dmsBuList, 'buid', 'buid')
        );
        if (count($toAdd)) {
            foreach ($toAdd as $buid) {
                $e = $dms->addBu(['buid' => $buid]);
                if (is_string($e)) {
                    $_ERROR[] = sprintf(_('Business unit %s not added'), $buList[$buid]).'<br>'._('Reason').": $e";
                    continue;
                }
                $_BODY .= '<br>'.sprintf(_('Business unit %s added'), $buList[$buid]);
            }
        }
        // Look for deletation
        $toDelete = [];
        $toDelete = array_diff(
            tldUtils::optionsByKeyValue($dmsBuList, 'id', 'buid'),
            $vars['bu']
        );
        if (count($toDelete)) {
            foreach ($toDelete as $id => $buid) {
                $e = $dms->deleteBuByID($id);
                if (is_string($e)) {
                    $_ERROR[] = sprintf(_('Business unit %s not deleted'), $buList[$buid]).'<br>'._('Reason').": $e";
                    continue;
                }
                $_BODY .= '<br>'.sprintf(_('Business unit %s deleted'), $buList[$buid]);
            }
        }
        if (empty($_ERROR)) {
            $_CONF[] = _('Business unit successfully updated');
        }
        break;
    case 'editDepartment':
        $_TITLE .= " \ "._('Edit Department');
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
        $departmentList = tldDepartment::getListAsIdDepartment();
        // Get form
        $form = new HTML_QuickForm('add', 'post');
        $form->addElement('hidden', 'm[0]', 'view');
        $form->addElement('hidden', 'm[1]', 'coverage');
        $form->addElement('hidden', 'm[2]', 'editDepartment');
        $form->addElement('hidden', 'id', $id);
        $form->addElement('header', 'title', _('Department'));
        $department = &$form->addElement(
            'advmultiselect', 'department', null,
            $departmentList,
            [
                'size' => 10,
                'class' => 'pool',
                'style' => 'width:200px;',
            ]
        );
        $department->setLabel([null, _('Department list'), _('Department selected')]);
        $department->setButtonAttributes('add', ['value' => '-->>', 'class' => 'inputCommand']);
        $department->setButtonAttributes('remove', ['value' => '<<--', 'class' => 'inputCommand']);
        $form->addRule('department', _('Field is required'), 'required');
        $form->setDefaults(['department' => tldUtils::optionsByKeyValue($dmsDptList, 'dptid', 'dptid')]);
        $form->addElement('reset', 'btnReset', _('Reset'));
        $form->addElement('submit', 'btnSubmit', _('Submit'));

        if (!$form->validate()) {
            $_BODY = $form->toHTML();
            break;
        }

        $vars = tldUtils::cleanupFormInput($form->exportValues());
        // Look for addition
        $toAdd = [];
        $toAdd = array_diff(
            $vars['department'],
            tldUtils::optionsByKeyValue($dmsDptList, 'dptid', 'dptid')
        );
        if (count($toAdd)) {
            foreach ($toAdd as $dptid) {
                $e = $dms->addDepartment(['dptid' => $dptid]);
                if (is_string($e)) {
                    $_ERROR[] = sprintf(_('Department %s not added'), $departmentList[$dptid]).'<br>'._('Reason').": $e";
                    continue;
                }
                $_BODY .= '<br>'.sprintf(_('Department %s added'), $departmentList[$dptid]);
            }
        }
        // Look for deletation
        $toDelete = [];
        $toDelete = array_diff(
            tldUtils::optionsByKeyValue($dmsDptList, 'id', 'dptid'),
            $vars['department']
        );
        if (count($toDelete)) {
            foreach ($toDelete as $tableID => $dptid) {
                $e = $dms->deleteDepartmentByID($tableID);
                if (is_string($e)) {
                    $_ERROR[] = sprintf(_('Department %s not deleted'), $departmentList[$dptid]).'<br>'._('Reason').": $e";
                    continue;
                }
                $_BODY .= '<br>'.sprintf(_('Department %s deleted'), $departmentList[$dptid]);
            }
        }
        if (empty($_ERROR)) {
            $_CONF[] = _('Department successfully updated');
        }
        break;
    default:
        $reportBu = new tldReportColumnar(
            $dmsBuList,
            [
                'xItems' => [
                    'location' => _('Business Unit'),
                ],
                'showItemNumbers' => true,
                'title' => _('Business unit coverage'),
            ]
        );
        $_BODY = $reportBu->fetch();
        $reportDpt = new tldReportColumnar(
            $dmsDptList,
            [
                'xItems' => [
                    'department' => _('Department'),
                ],
                'showItemNumbers' => true,
                'title' => _('Department coverage'),
            ]
        );
        $_BODY .= $reportDpt->fetch();
        break;
}
