<?php
if (!$user->isInGroup(['gg_ADMIN', 'role_ASM', 'role_SA', 'role_EVP', 'role_CFO', 'role_COO'])) {
    $DEFAULT_ERROR[] = 'ERROR: You do not have permission to access this page';
    return;
}

$DEFAULT_TITLE .= "\Breakdown";
if (!is_PrintSOAckPassed($sol->getStatus())
    || $user->isInGroup('SUPERUSER')
    || tldModule::getKeyUserIdByModule('SOL') === $user->getID()
    || tldModule::getMOOIDByModule('SOL') === $user->getID()
) {
    $DEFAULT_MENU .= <<<EOF
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<a href="$php_self?m[0]=sol&m[1]=view&m[2]=breakdown&m[3]=FormInternal&id=$id">Add Internal Item</a>&nbsp;|&nbsp;
<a href="$php_self?m[0]=sol&m[1]=view&m[2]=breakdown&m[3]=FormExternal&id=$id">Add External Item</a>
EOF;
}
if ($btnSubmit == 'Cancel') {
    $m[3] = '';
}

$OPT_FIELDS = [
    'id' => 'Option#',
    'parent_id' => 'SOL#',
    'caty' => 'Category',
    'dsca' => 'Description',
    'mrsp_cur' => 'Published TP Currency',
    'mrsp' => 'Published TP',
    'pric_cur' => 'Negotiated TP Currency',
    'pric' => 'Negotiated TP',
    'prip_cur' => 'Published Sales Price Currency',
    'prip' => 'Published Sales Price',
    'pris_cur' => 'Actual Sales Price Currency',
    'pris' => 'Actual Sales Price',
];

$curs = $sol->getCURS();
$curList = array_keys($curs);
$curList[] = 'USD';
$DCUR = $sol->getDCUR();
$iDCUR = array_search($DCUR, $curList);

$intCaty = $sol::getInternalCategoriesList();

// Check if allowed after EVP_APPROVAL
if (!is_AllowedAfterEVP_APPROVAL()) {
    $DEFAULT_ERROR[] = 'ERROR: You do not have permissions to make any modifications after EVP_APPROVAL status';
    return;
}
if (!empty($m[3]) && $sol->getStatus() === 'CLOSED') {
    $DEFAULT_ERROR[] = "ERROR: The SOL is already closed and doesn't allow to use this function.";
    return;
}

if (!empty($m[3]) && is_PrintSOAckPassed($sol->getStatus()) && !$user->isInGroup('SUPERUSER')) {

    if (!$user->isInGroup('role_SA') || $m[3] !== 'FormExternal' || $action !== 'update' || $partial === null) {
        $DEFAULT_ERROR[] = "ERROR: The SOL status doesn't allow to use this function, you must ask for a SOL modification.";
        return;
    }

}
switch ($m[3]) {

    case 'FormInternal':
        $form = new HTML_QuickForm('frmAddInternal', 'post');
        $form->addElement('hidden', 'm[0]', 'sol');
        $form->addElement('hidden', 'm[1]', 'view');
        $form->addElement('hidden', 'm[2]', 'breakdown');
        $form->addElement('hidden', 'm[3]', 'FormInternal');
        $form->addElement('hidden', 'id', $id);
        $form->addElement('header', 'title', 'Internal Item');
        $form->addElement('select', 'caty', 'Category', ['' => ''] + $intCaty);
        $form->addElement('text', 'dsca', 'Description', ['size' => 30]);
        $mrspGRP[] =& $form->createElement('select', 'imrsp_cur', 'Currency', $curList);
        $mrspGRP[] =& $form->createElement('text', 'mrsp', 'Published TP');
        $field_mrspGRP = $form->addGroup($mrspGRP, null, 'Published TP', '&nbsp;');
        $pricGRP[] =& $form->createElement('select', 'ipric_cur', 'Currency', $curList);
        $pricGRP[] =& $form->createElement('text', 'pric', 'Negotiated TP');
        $form->addGroup($pricGRP, null, 'Negotiated TP', '&nbsp;');
        $prisGRP[] =& $form->createElement('select', 'ipris_cur', 'Currency', $curList);
        $prisGRP[] =& $form->createElement('text', 'pris', 'Actual Sales Price');
        $form->addGroup($prisGRP, null, 'Actual Sales Price', '&nbsp;');
        $form->addElement('submit', 'btnSubmit', 'Submit');
        $form->addElement('submit', 'btnSubmit', 'Cancel');
        $form->setDefaults(['imrsp_cur' => $iDCUR, 'ipric_cur' => $iDCUR, 'ipris_cur' => $iDCUR]);
        $form->addRule('caty', 'Required', 'required');
        // Check type of action for the form
        switch ($action) {
            case 'update':
                if (empty($optid) || !is_numeric($optid)) {
                    $DEFAULT_ERROR[] = 'ERROR: Option ID missing or invalid';
                    break 2;
                }
                $form->addElement('hidden', 'action', 'update');
                $form->addElement('hidden', 'optid', $optid);
                $opt = new tldSOROpts($optid);
                $optHeader = $opt->itsHeader;
                $form->setDefaults(
                    [
                        'caty' => $optHeader['caty'],
                        'dsca' => $optHeader['dsca'],
                        'mrsp' => $optHeader['mrsp'],
                        'pric' => $optHeader['pric'],
                        'pris' => $optHeader['pris'],
                        'imrsp_cur' => array_search($optHeader['mrsp_cur'], $curList),
                        'ipric_cur' => array_search($optHeader['pric_cur'], $curList),
                        'ipris_cur' => array_search($optHeader['pris_cur'], $curList),
                    ]
                );
                $form->updateElementAttr('caty', ['disable' => 'disabled']);
                break;
        }
        // Set Required
        $form->addGroupRule(
            $field_mrspGRP->getName(),
            [
                'imrsp_cur' => [['Required', 'required']],
                'mrsp' => [['Required', 'required']],
            ]
        );

        if (!$form->validate()) {
            $body .= $form->toHTML();
            break;
        }

        $rawData = $form->exportValues();
        // prepare currencies
        $rawData['mrsp_cur'] = $curList[$rawData['imrsp_cur']];
        $rawData['pric_cur'] = $curList[$rawData['ipric_cur']];
        $rawData['pris_cur'] = $curList[$rawData['ipris_cur']];
        // force publish price list info
        $rawData['prip_cur'] = $rawData['mrsp_cur'];
        $rawData['prip'] = round($rawData['mrsp'] / 0.9, 2);
        // clean data
        $vars = tldUtils::cleanupFormInput($rawData);

        // Action to perform
        switch ($vars['action']) {
            case 'update':
                if (empty($optid) || !is_numeric($optid)) {
                    $DEFAULT_ERROR[] = 'ERROR: Option ID missing or invalid';
                    break 2;
                }
                $opt = new tldSOROpts($vars['optid']);
                $optHeader = $opt->itsHeader;
                if (is_EVPstatusPassed($header['status'])) {
                    // check caty and description
                    if ($vars['caty'] != $optHeader['caty'] || ($vars['dsca'] != $optHeader['dsca'] && $optHeader['caty'] == 'BASE UNIT')) {
                        $DEFAULT_ERROR[] = 'ERROR: This action is not allowed with this SOL status';
                        break 2;
                    }
                }
                $e = $opt->update($vars);
                if (is_string($e)) {
                    $DEFAULT_ERROR[] = "ERROR: Could not update option<br>Reason: $e";
                    break 2;
                }

                // Log changes if applicable
                if (is_EVPstatusPassed($header['status'])) {
                    $fieldToCheck = [
                        'caty', 'dsca', 'mrsp_cur', 'mrsp', 'pric_cur',
                        'pric', 'prip_cur', 'prip', 'pris_cur', 'pris',
                    ];
                    _updateProcessAfterEVP_APPROVAL(
                        $OPT_FIELDS,
                        $fieldToCheck,
                        $optHeader,
                        $rawData,
                        ['msg' => "Option#{$opt->itsID} - {$vars['dsca']}"]
                    );
                }
                break;
            default:
                $e = $sol->addOpt($vars);
                if (is_string($e)) {
                    $DEFAULT_ERROR[] = "ERROR: Could not add option<br>Reason: $e";
                    break 2;
                }
                $msg = "Option $e, {$vars['caty']}, {$vars['dsca']} added";
                if (is_EVPstatusPassed($header['status'])) {
                    _logAfterEVP_APPROVAL(TldDatabase::escape($msg));
                    if ($user->isInGroup(['role_ASM'])) {
                        _notifyAfterEVP_APPROVAL(null, mb_convert_encoding($msg, 'UTF-8', mb_list_encodings()));
                    }
                }
                break;
        }

        $breakdown = $sol->getBreakdown(['include' => tldSOL::getInternalCategoriesList()]);
        $optionsDesc = '';
        foreach ($breakdown as $breakdownLine) {
            $optionsDesc .= $breakdownLine['dsca'] . "\n";
        }

        foreach (tldSORUnit::byParent($id) as $sorUnit) {
            if (null === $sorUnit['erid']) {
                continue;
            }
            $er = new tldEquipment($sorUnit['erid']);
            if ($er->itsDetails !== null) {
                $er->updateRecord(['options_desc' => $optionsDesc], ['options_desc']);
            }
        }

        // Need to update trans
        $e = $sol->updateTran();
        if (is_string($e)) {
            $DEFAULT_ERROR[] = $e;
        }
        break;
    case 'FormExternal':
        $form = new HTML_QuickForm('frmAddExternal', 'post');
        $form->addElement('hidden', 'm[0]', 'sol');
        $form->addElement('hidden', 'm[1]', 'view');
        $form->addElement('hidden', 'm[2]', 'breakdown');
        $form->addElement('hidden', 'm[3]', 'FormExternal');
        $form->addElement('hidden', 'id', $id);
        $form->addElement('header', 'title', 'External Item');
        $form->addElement('select', 'caty', 'Category', ['' => ''] + tldSOL::getExternalCategoriesList());
        $form->addElement('text', 'dsca', 'Description', ['size' => 30]);
        $pricGRP[] =& $form->createElement('select', 'ipric_cur', 'Currency', $curList);
        $pricGRP[] =& $form->createElement('text', 'pric', 'Cost');
        $form->addGroup($pricGRP, 'group_cost', 'Cost', '&nbsp;');
        $prisGRP[] =& $form->createElement('select', 'ipris_cur', 'Currency', $curList);
        $prisGRP[] =& $form->createElement('text', 'pris', 'Sales Price');
        $form->addGroup($prisGRP, 'group_sales', 'Actual Sales Price', '&nbsp;');
        $form->addElement('submit', 'btnSubmit', 'Submit');
        $form->addElement('submit', 'btnSubmit', 'Cancel');
        $form->setDefaults(['ipric_cur' => $iDCUR, 'ipris_cur' => $iDCUR]);
        $form->addRule('caty', 'Required', 'required');

        // Check type of action for the form
        switch ($action) {
            case 'update':
                if (empty($optid) || !is_numeric($optid)) {
                    $DEFAULT_ERROR[] = 'ERROR: Option ID missing or invalid';
                    break 2;
                }
                $form->addElement('hidden', 'action', 'update');
                $form->addElement('hidden', 'optid', $optid);
                if (null !== $partial) {
                    $form->addElement('hidden', 'partial', '');
                    $form->removeElement('group_cost');
                    $form->removeElement('group_sales');
                    $form->addElement('hidden', 'pric', $optHeader['pric']);
                    $form->addElement('hidden', 'pris', $optHeader['pris']);
                    $form->addElement('hidden', 'ipric_cur', array_search($optHeader['pric_cur'], $curList));
                    $form->addElement('hidden', 'ipris_cur', array_search($optHeader['pris_cur'], $curList));
                }
                $opt = new tldSOROpts($optid);
                $optHeader = $opt->itsHeader;
                $form->setDefaults(
                    [
                        'caty' => $optHeader['caty'],
                        'dsca' => $optHeader['dsca'],
                        'pric' => $optHeader['pric'],
                        'pris' => $optHeader['pris'],
                        'ipric_cur' => array_search($optHeader['pric_cur'], $curList),
                        'ipris_cur' => array_search($optHeader['pris_cur'], $curList),
                        'group_cost' => ['pric' => $optHeader['pric'], 'ipric_cur' => array_search($optHeader['pric_cur'], $curList)],
                        'group_sales' => ['pris' => $optHeader['pris'], 'ipris_cur' => array_search($optHeader['pris_cur'], $curList)],
                    ]
                );
                break;
        }

        if (!$form->validate()) {
            $body .= $form->toHTML();
            break;
        }

        $rawData = $form->exportValues();
        if (isset($rawData['group_cost'])) {
            $rawData['pric_cur'] = $curList[$rawData['group_cost']['ipric_cur']];
            $rawData['pric'] = $rawData['group_cost']['pric'];
        } else {
            $rawData['pric_cur'] = $curList[$rawData['ipric_cur']];
        }
        if (isset($rawData['group_sales'])) {
            $rawData['pris_cur'] = $curList[$rawData['group_sales']['ipris_cur']];
            $rawData['pris'] = $rawData['group_sales']['pris'];
        } else {
            $rawData['pris_cur'] = $curList[$rawData['ipris_cur']];
        }

        $vars = tldUtils::cleanupFormInput($rawData);

        switch ($vars['action']) {
            case 'update':
                if (empty($vars['optid']) || !is_numeric($vars['optid'])) {
                    $DEFAULT_ERROR[] = 'ERROR: Option ID missing or invalid';
                    break 2;
                }
                $opt = new tldSOROpts($vars['optid']);
                $optHeader = $opt->itsHeader;
                $e = $opt->update($vars);
                if (is_string($e)) {
                    $DEFAULT_ERROR[] = "ERROR: Could not update option<br>Reason: $e";
                    break 2;
                }
                // Log changes if applicable
                if (is_EVPstatusPassed($header['status'])) {
                    $fieldToCheck = [
                        'caty', 'dsca', 'pric_cur', 'pric', 'pris_cur', 'pris',
                    ];
                    _updateProcessAfterEVP_APPROVAL(
                        $OPT_FIELDS,
                        $fieldToCheck,
                        $optHeader,
                        $rawData,
                        ['msg' => "Option#{$opt->itsID} - {$vars['dsca']}"]
                    );
                }
                break;
            default:
                $e = $sol->addOpt($vars);
                if (is_string($e)) {
                    $DEFAULT_ERROR[] = "ERROR: Could not add option<br>Reason: $e";
                    break 2;
                }
                $msg = "Option $e, {$vars['caty']}, {$vars['dsca']} added";
                if (is_EVPstatusPassed($header['status'])) {
                    _logAfterEVP_APPROVAL(TldDatabase::escape($msg));
                    if ($user->isInGroup(['role_ASM'])) {
                        _notifyAfterEVP_APPROVAL(null, mb_convert_encoding($msg, 'UTF-8', mb_list_encodings()));
                    }
                }
                break;
        }
        //need to update trans
        $e = $sol->updateTran();
        if (is_string($e)) {
            $DEFAULT_ERROR[] = $e;
        }
        break;
    case 'del':
        if (empty($optid) || !is_numeric($optid)) {
            $DEFAULT_ERROR[] = 'ERROR: Option ID empty or invalid';
            break;
        }
        $opt = new tldSOROpts($optid);
        if ($opt->getParentID() != $id) {
            $DEFAULT_ERROR[] = "ERROR: Option $optid does not belong to SOL $id that you are viewing";
            break;
        }
        // Check if opts is BASE UNIT
        if (is_EVPstatusPassed($header['status'])) {
            if ($opt->itsHeader['caty'] == 'BASE UNIT') {
                $DEFAULT_ERROR[] = 'ERROR: BASE UNIT can not be deleted';
                break;
            }
        }
        $e = $opt->del();
        if (is_string($e)) {
            $DEFAULT_ERROR[] = $e;
            break;
        }
        //need to update trans
        $e = $sol->updateTran();
        if (is_string($e)) {
            $DEFAULT_ERROR[] = $e;
            break;
        }
        // Check when EVP APPROVAL reached
        $msg = "Option $optid, {$opt->itsHeader['caty']}, {$opt->itsHeader['dsca']} deleted";
        if (is_EVPstatusPassed($header['status'])) {
            _logAfterEVP_APPROVAL(TldDatabase::escape($msg));
            if ($user->isInGroup(['role_ASM'])) {
                _notifyAfterEVP_APPROVAL(null, mb_convert_encoding($msg, 'UTF-8', mb_list_encodings()));
            }
        }
        $body .= '<p>Item deleted successfully !</p>';

        $breakdown = $sol->getBreakdown(['include' => tldSOL::getInternalCategoriesList()]);
        $optionsDesc = '';
        foreach ($breakdown as $breakdownLine) {
            $optionsDesc .= $breakdownLine['dsca'] . "\n";
        }

        foreach (tldSORUnit::byParent($id) as $sorUnit) {
            if (null === $sorUnit['erid']) {
                continue;
            }
            $er = new tldEquipment($sorUnit['erid']);
            if ($er->itsDetails !== null) {
                $er->updateRecord(['options_desc' => $optionsDesc], ['options_desc']);
            }
        }
        break;
}

if (!is_PrintSOAckPassed($sol->getStatus()) || $user->isInGroup('SUPERUSER')) {
    $smarty->assign('options', ['edit' => 'YES', 'delete' => 'YES']);
} elseif (!$sol->isClosed() && $user->isInGroup('role_SA')) {
    $smarty->assign('options', ['edit' => 'partial']);
}

$smarty->assign('id', $id);
$body .= getBreakdownTab($sol, $smarty, $PATH, $DCUR, 'sso');
