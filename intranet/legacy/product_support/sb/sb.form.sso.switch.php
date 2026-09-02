<?php

// SSO SWITCH ------------------------------------------------->
$SB_LINES_CONSTRAINTS = null;
$SSO_ID = null;
$SSO = null;

// List of SSO impacted in SB
$SB_IMPACTED_SSO = $sb->getImpactedSSOByConstraints();

// List of Allowed SSO depending of the section summary
switch ($m[3]) {
    case 'implementation':
        $USER_IMPACTED_SSO = array_intersect_key(
            _getSSOFromUserByGroups(['role_EVP', 'role_CSM', 'role_CSA', 'gg_SERVICE', 'gg_PARTS', 'role_PSM', 'role_PSE', 'role_PSA', 'role_RME']),
            $SB_IMPACTED_SSO
        );
        break;
    case 'selection':
        $USER_IMPACTED_SSO = array_intersect_key(
            _getSSOFromUserByGroups(['role_EVP']),
            $SB_IMPACTED_SSO
        );
        break;
    default:
        $USER_IMPACTED_SSO = ['ALL' => 'ALL'] + $SB_IMPACTED_SSO;
        break;
}

// SSO form SWITCH
$frmSwitch = new HTML_QuickForm('frmSwitch', 'post', '', '', ['id' => 'frmSwitch'], true);
$frmSwitch->addElement('hidden', 'm[0]', 'sb');
$frmSwitch->addElement('hidden', 'm[1]', 'view');
$frmSwitch->addElement('hidden', 'm[2]', 'summary');
$frmSwitch->addElement('hidden', 'm[3]', $m[3]);
$frmSwitch->addElement('hidden', 'id', $id);
$frmSwitch->addElement('header', 'header', 'SSO Switch Tool');
$frmSwitch->addElement('select', 'sso_switch', 'SSO', ['' => ''] + $USER_IMPACTED_SSO, ['onChange' => "javascript:$('#frmSwitch').submit();"]);
// Form validation
if ($frmSwitch->validate()) {
    $vars = tldUtils::cleanupFormInput($frmSwitch->exportValues());
    if (array_key_exists($vars['sso_switch'], $USER_IMPACTED_SSO)) {
        $sess['sb'][$id]['sso_id'] = $vars['sso_switch'];
    } elseif (empty($vars['sso_switch'])) {
        $DEFAULT_ERROR[] = 'ERROR: No SSO selected';
    } else {
        $DEFAULT_ERROR[] = 'ERROR: Your permissions do not allow you to select this SSO in this section';
        $DEFAULT_ERROR[] = 'Please switch to another SSO';
    }
} // Else try to find default SSO regarding user profile
elseif (empty($sess['sb'][$id]['sso_id'])) {
    // Take default user BU
    if (array_key_exists($user->getBUID(), $USER_IMPACTED_SSO)) {
        $sess['sb'][$id]['sso_id'] = $user->getBUID();
    } elseif (!empty($USER_IMPACTED_SSO)) {
        $sso_id = key($USER_IMPACTED_SSO);
        $sess['sb'][$id]['sso_id'] = $sso_id;
    }
}
$frmSwitch->setDefaults(['sso_switch' => $sess['sb'][$id]['sso_id']]);
$body .= $frmSwitch->toHTML();

// IDENTIFY SSO from session and check ACL

switch ($m[3]) {
    case 'implementation':
    case 'selection':
        // For selection, make sure ONE sso is selected
        if (empty($sess['sb'][$id]['sso_id']) || !is_numeric($sess['sb'][$id]['sso_id'])) {
            $DEFAULT_ERROR[] = 'WARNING: The ER coverage of this SB does not concern your Sale Service Organization.';
            unset($m[3]);
            break;
        }
        $SSO_ID = TldDatabase::escape($sess['sb'][$id]['sso_id']);
        $SSO = new tldLocation($SSO_ID);
        if ($SSO->isEmpty()) {
            $DEFAULT_ERROR[] = 'ERROR: Can not continue, unknown SSO from session';
            unset($m[3]);
            break;
        }
        $DEFAULT_TITLE .= "\\" . $SSO->getShortName();
        $SB_LINES_CONSTRAINTS[] = 'sso.id=' . $SSO->getID();
        break;
    default:
        // If SSO selected
        if (!empty($sess['sb'][$id]['sso_id']) && is_numeric($sess['sb'][$id]['sso_id'])) {
            $SSO_ID = TldDatabase::escape($sess['sb'][$id]['sso_id']);
            $SSO = new tldLocation($SSO_ID);
            if ($SSO->isEmpty()) {
                $DEFAULT_ERROR[] = 'ERROR: Can not continue, unknown SSO from session';
                break;
            }
            $DEFAULT_TITLE .= "\\" . $SSO->getShortName();
            $SB_LINES_CONSTRAINTS[] = 'sso.id=' . $SSO->getID();
        } // If all SSO
        elseif ($sess['sb'][$id]['sso_id'] === 'ALL') {
            $DEFAULT_TITLE .= "\All SSO";
        } // If no SSO
        else {
            $DEFAULT_TITLE .= "\All SSO";
            $DEFAULT_ERROR[] = 'WARNING: Could not define default SSO from your profile';
        }
        break;
}
