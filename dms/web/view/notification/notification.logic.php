<?php

declare(strict_types=1);
$_TITLE .= " \ "._('Notification');
$_MENU .= "
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<a href=\"$php_self?m[0]=view&m[1]=notification&id=$id\">"._('Home')."</a>&nbsp;|&nbsp;
<a href=\"$php_self?m[0]=view&m[1]=notification&m[2]=userList&id=$id\">"._('User notification list')."</a>&nbsp;|&nbsp;
<a href=\"$php_self?m[0]=view&m[1]=notification&m[2]=rules&m[3]=add&id=$id\">"._('Add rules')."</a>&nbsp;|&nbsp;
<a href=\"$php_self?m[0]=view&m[1]=notification&m[2]=rules&m[3]=deleteAll&id=$id\">"._('Delete all').'</a>
';

if ($error ?? '' === 'check') {
    $_ERROR[] = 'You have to confirm that you revised the notification rules before you can change the status';
}

switch ($m[2] ?? null) {
    case 'rules':
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
                        'link' => "$php_self?m[0]=view&m[1]=notification&m[2]=rules&m[3]=add&id=$id",
                        'title' => _('Add notification rules'),
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
                // Prepare other rules
                $toAdd = [];
                $toLog = [];
                foreach ($vars as $add) {
                    $add = tldUtils::cleanupFormInput($add);
                    if (empty($add['division']) && empty($add['subdivision']) && empty($add['region']) && empty($add['bu']) && empty($add['department']) && empty($add['function'])) {
                        $_NOTE[] = _('One of the notification rules was blank so not added');
                        continue;
                    }
                    $toAdd[] = [
                        'division_id' => $add['division'],
                        'subdivision_id' => $add['subdivision'],
                        'region_id' => $add['region'],
                        'bu_id' => $add['bu'],
                        'dpt_id' => $add['department'],
                        'fct_id' => $add['function'],
                    ];
                }
                $sess['dms']['not']['add'] = $toAdd;
                // Check nb people from new rules
                $constraintsRules = [];

                $fields = [
                    'bu_id' => 'people.bu_id',
                    'dpt_id' => 'people.dpt_id',
                    'fct_id' => 'people.fct_id',
                ];

                foreach ($toAdd as $add) {
                    $constraintsDim = [];
                    foreach ($add as $field => $val) {
                        if (empty($val) || 0 == $val) {
                            continue;
                        }
                        $field = $fields[$field] ?? $field;
                        $constraintsDim[] = "$field=$val";
                    }
                    $constraintsRules[] = '('.implode(' AND ', $constraintsDim).')';
                }
                $constraints = " people.hidden=0 AND people.disabled='N' AND (".implode(' OR ', $constraintsRules).') ';
                $people = tldDirectory::byConstraints($constraints);
                $nbPeople = count($people);
                $limitPeople = 100;
                if ($nbPeople > $limitPeople) {
                    $msg = sprintf(_("More than %s people selected ($nbPeople), are you sure?"), $limitPeople, $nbPeople);
                    $yes = _('Yes');
                    $no = _('No');
                    $_WARNING[] = <<<EOF
$msg
<a href="$php_self?m[0]=view&m[1]=notification&m[2]=rules&m[3]=add2&id=$id" class="button">$yes</a>
<a href="$php_self?m[0]=view&m[1]=notification&m[2]=rules&m[3]=add&id=$id" class="button">$no</a>
EOF;
                    $_BODY = $form->toHTML();
                    break;
                }
                header("Location: $php_self?m[0]=view&m[1]=notification&m[2]=rules&m[3]=add2&id=$id");
                break;
            case 'add2':
                $toAdd = $sess['dms']['not']['add'];
                if (empty($toAdd)) {
                    $_ERROR[] = _('No rules sent or session expired');
                    break;
                }
                // Add rules one by one
                foreach ($toAdd as $a) {
                    $e = $dms->addNotificationRule($a);
                    if (is_string($e)) {
                        $_ERROR[] = _('Notification rule not added.').'<br>'._('Reason').": $e";
                        continue;
                    }
                    // Prepare logs
                    $rule = new tldModOrg($e);
                    $dims = ['division', 'subdivision', 'region', 'location', 'department', 'function_dsc'];
                    $txt = [];
                    foreach ($dims as $dim) {
                        if (!empty($rule->itsHeader[$dim])) {
                            $txt[] = $rule->itsHeader[$dim];
                        }
                    }
                    $toLog[] = implode(' | ', $txt);
                }
                // Unset session data
                unset($sess['dms']['not']['add']);
                // Add log
                $log = 'Notification rule(s) added:<br>- ';
                $log .= implode('<br>- ', $toLog);
                _addLog($user->getID(), $log);
                // Confirm process is done
                $_CONF[] = _('Notification rules addidion completed');
                break;
            case 'deleteAll':
                // Check access
                if (!_isAdmin() && !_isOwner()) {
                    $_ERROR[] = _('You do not have permissions to use this feature');
                    break;
                }
                // Check status
                if (!_isInRevision()) {
                    $_ERROR[] = _('Can not update if DMS status is not in REVISION');
                    break;
                }
                if (($m[4] ?? null) !== 'confirm') {
                    $_WARNING[] = _('Click here to confirm that you want to remove ALL notification rules: ')."<a href=\"$php_self?m[0]=view&m[1]=notification&m[2]=rules&m[3]=deleteAll&m[4]=confirm&id=$id\">"._('Delete all').'</a>';
                    break;
                }
                $e = $dms->deleteAllNotificationRules();
                if (is_string($e)) {
                    $_ERROR[] = _('All notification rules not deleted.').'<br>'._('Reason').": $e";
                    break;
                }
                // Log
                _addLog($user->getID(), 'ALL notification rules deleted');
                $_CONF[] = _('All notification rules deleted successfully');
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
                if (empty($_GET['notid']) || !is_numeric($_GET['notid'])) {
                    $_ERROR[] = _('Parameters sent empty or invalid');
                    break;
                }
                // Prepare log
                $rule = new tldModOrg($_GET['notid']);
                $dims = ['division', 'location', 'department', 'function_dsc'];
                $txt = [];
                foreach ($dims as $dim) {
                    if (!empty($rule->itsHeader[$dim])) {
                        $txt[] = $rule->itsHeader[$dim];
                    }
                }
                // Delete rule
                $e = $dms->deleteNotificationRuleByID($_GET['notid']);
                if (is_string($e)) {
                    $_ERROR[] = _('Notification rule not deleted.').'<br>'._('Reason').": $e";
                    break;
                }
                // Add log
                $log = 'Notification rule deleted: '.implode(' | ', $txt);
                _addLog($user->getID(), $log);
                $_CONF[] = _('Notification rule deleted successfully');
                break;
        }
        $_BODY .= _getNotificationRuleListReport();
        break;
    case 'userList':
        $data = $dms->getNotificationUserList();
        $_BODY .= _getUserListReport($data, _('User notification list'));
        break;
    default:
        $_BODY .= _getNotificationRuleListReport();
        break;
}

function _getNotificationRuleListReport()
{
    global $dms,$php_self,$_PATH;

    return include "$_PATH/notification.view.tpl.php";
}
