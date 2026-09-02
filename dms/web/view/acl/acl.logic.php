<?php

declare(strict_types=1);
$_TITLE .= " \ "._('Restrictions');
$_MENU .= "
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<a href=\"$php_self?m[0]=view&m[1]=acl&id=$id\">"._('Home')."</a>&nbsp;|&nbsp;
<a href=\"$php_self?m[0]=view&m[1]=acl&m[2]=edit&id=$id\">"._('Edit portal')."</a>&nbsp;|&nbsp;
<a href=\"$php_self?m[0]=view&m[1]=acl&m[2]=userList&id=$id\">"._('User restriction list').'</a>
';

if ('CONFIDENTIAL' == $dms->itsHeader['access_type']) {
    $_MENU .= "
&nbsp;|&nbsp;<a href=\"$php_self?m[0]=view&m[1]=acl&m[2]=rules&m[3]=add&id=$id\">"._('Edit rules').'</a>
';
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
        $portalList = tldDMS::getPortalList();
        $accessTypeList = tldDMS::getAccessTypeList();
        $tldGroupList = tldUtils::optionsByKeyValue(tldGroup::getGroups(), 'group_name', 'group_name');
        // Get form
        $form = new HTML_QuickForm('add', 'post');
        $form->addElement('hidden', 'm[0]', 'view');
        $form->addElement('hidden', 'm[1]', 'acl');
        $form->addElement('hidden', 'm[2]', 'edit');
        $form->addElement('hidden', 'id', $id);
        $form->addElement('header', 'frmTitle', _('Edit portal'));
        $form->addElement('select', 'portal', _('Portal'), ['' => ''] + $portalList);
        $form->addElement('select', 'access_type', _('Access type'), ['' => ''] + $accessTypeList);
        $form->addRule('portal', _('Field is required'), 'required');
        $form->addRule('access_type', _('Field is required'), 'required');

        $form->setDefaults($dms->itsHeader);
        $form->addElement('reset', 'btnReset', _('Reset'));
        $form->addElement('submit', 'btnSubmit', _('Submit'));

        if (!$form->validate()) {
            $_BODY = $form->toHTML();
            $_BODY .= include "$_PATH/acl.definition.tpl.php";
            $_BODY .= include "$_PATH/acl.definition.restriction.tpl.php";
            break;
        }

        $vars = tldUtils::cleanupFormInput($form->exportValues());
        $dmsFields = [
            'portal' => 'Portal',
            'access_type' => 'Access type',
        ];
        $e = $dms->update($vars, array_keys($dmsFields));
        if (is_string($e)) {
            $_ERROR[] = _('DMS access information not updated').'<br>'._('Reason').": $e";
            break;
        }
        // remove all rules if now public
        if ('PUBLIC' == $vars['access_type']) {
            $e = $dms->deleteAllAclRules();
            if (!is_string($e)) {
                $_CONF[] = _('All Restriction rules deleted successfully');
            }
        }
        $_CONF[] = _('DMS access information successfully updated');
        // Add log
        $flagLog = false;
        $log = 'DMS portal update:<br><ul>';
        foreach ($dmsFields as $field => $label) {
            // if not change of the field, go to next one
            if ($header[$field] == $vars[$field]) {
                continue;
            }
            // else log
            switch ($field) {
                default:
                    $from = $header[$field];
                    $to = $vars[$field];
                    break;
            }
            $log .= "<li>$label <strong>FROM</strong> \'$from\' <strong>TO</strong> \'$to\'";
            $flagLog = true;
        }
        $log .= '</ul>';
        // Log
        if ($flagLog) {
            _addLog($user->getID(), $log);
        }
        // Refresh header
        $dms->refresh();
        // Case it is confidential
        if ('CONFIDENTIAL' == $dms->itsHeader['access_type']) {
            header("Refresh: 1; URL=$php_self?m[0]=view&m[1]=acl&m[2]=rules&m[3]=add&id=$id");
        }
        break;
    case 'rules':
        $_TITLE .= " \ "._('Restriction rules');

        if ('CONFIDENTIAL' != $dms->itsHeader['access_type']) {
            $_ERROR[] = _('DMS Access type information are not set to CONFIDENTIAL');
            break;
        }

        switch ($m[3] ?? null) {
            case 'add':
                $_TITLE .= " \ "._('Add');
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
                // Get form
                $form = new tldOrgSelectionForm(
                    [
                        'link' => "$php_self?m[0]=view&m[1]=acl&m[2]=rules&m[3]=add&id=$id",
                        'title' => _('Add restriction rules'),
                    ]
                );

                if (!$form->validate()) {
                    $_BODY = $form->toHTML();
                    break;
                }

                $vars = tldUtils::cleanupFormInput($form->exportValues());
                // Check if at least one rule was added
                if (!count($vars)) {
                    $_ERROR[] = _('Can not add rules, nothing was selected');
                    $_BODY = $form->toHTML();
                    break;
                }
                // Check rules limit
                $limitRules = 60;
                if (count($vars) > $limitRules) {
                    $_ERROR[] = sprintf(_('Can not add rules, limit of %s rules reached'), $limitRules);
                    $_BODY = $form->toHTML();
                    break;
                }
                // Add rules one by one
                foreach ($vars as $add) {
                    $add = tldUtils::cleanupFormInput($add);
                    $a = [
                        'division_id' => $add['division'],
                        'subdivision_id' => $add['subdivision'],
                        'region_id' => $add['region'],
                        'bu_id' => $add['bu'],
                        'dpt_id' => $add['department'],
                        'fct_id' => $add['function'],
                    ];
                    $e = $dms->addAclRule($a);
                    if (is_string($e)) {
                        $_ERROR[] = _('Restriction rule not added.').'<br>'._('Reason').": $e";
                        break;
                    }
                    // Prepare logs
                    $rule = new tldModOrg($e);
                    $dims = ['division', 'location', 'department', 'function_dsc'];
                    $txt = [];
                    foreach ($dims as $dim) {
                        if (!empty($rule->itsHeader[$dim])) {
                            $txt[] = $rule->itsHeader[$dim];
                        }
                    }
                    $toLog[] = implode(' | ', $txt);
                }
                // Add log
                $log = 'Restriction rule(s) added:<br>- ';
                $log .= implode('<br>- ', $toLog);
                _addLog($user->getID(), $log);
                // Confirm process is done
                $_CONF[] = _('Restriction rules addidion completed');
                break;
            case 'deleteAll':
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
                $e = $dms->deleteAllAclRules();
                if (is_string($e)) {
                    $_ERROR[] = _('All restriction rules not deleted.').'<br>'._('Reason').": $e";
                    break;
                }
                // Log
                _addLog($user->getID(), 'ALL restriction rules deleted');
                $_CONF[] = _('All Restriction rules deleted successfully');
                break;
            case 'delete':
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
                if (empty($_GET['aclid']) || !is_numeric($_GET['aclid'])) {
                    $_ERROR[] = _('Parameters sent empty or invalid');
                    break;
                }
                // Prepare log
                $rule = new tldModOrg($_GET['aclid']);
                $dims = ['division', 'location', 'department', 'function_dsc'];
                $txt = [];
                foreach ($dims as $dim) {
                    if (!empty($rule->itsHeader[$dim])) {
                        $txt[] = $rule->itsHeader[$dim];
                    }
                }
                // Delete rule
                $e = $dms->deleteAclRuleByID($_GET['aclid']);
                if (is_string($e)) {
                    $_ERROR[] = _('Restriction rule not deleted.').'<br>'._('Reason').": $e";
                    break;
                }
                // Add log
                $log = 'Restriction rule deleted: '.implode(' | ', $txt);
                _addLog($user->getID(), $log);
                $_CONF[] = _('Restriction rule deleted successfully');
                break;
        }
        // By all time, display rules list
        $_BODY .= _getAclRuleList();
        break;
    case 'userList':
        $access_type = $dms->itsHeader['access_type'];
        switch ($access_type) {
            case 'CONFIDENTIAL':
                $data = $dms->getAllowedUserList();
                $_BODY .= _getUserListReport($data, _('User restriction list'));
                break;
            case 'PUBLIC':
                $_BODY .= _('All users can access this document');
                break;
        }
        break;
    default:
        $report = new tldAssocTable(
            $dms->itsHeader,
            [
                'portal' => _('Portal'),
                'access_type' => _('Access type'),
            ],
            ['title' => _('Portal')]
        );
        $_BODY = $report->fetch();
        // If confidential, display rules
        if ('CONFIDENTIAL' == $dms->itsHeader['access_type']) {
            $_BODY .= _getAclRuleList();
        }
        // Display definitions
        $_BODY .= include "$_PATH/acl.definition.tpl.php";
        // If confidential, display rules definitions
        if ('CONFIDENTIAL' == $dms->itsHeader['access_type']) {
            $_BODY .= include "$_PATH/acl.definition.restriction.tpl.php";
        }
        break;
}

function _getAclRuleList()
{
    global $dms,$php_self,$_PATH;

    return include "$_PATH/acl.view.tpl.php";
}
