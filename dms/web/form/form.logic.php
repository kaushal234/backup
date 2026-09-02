<?php

declare(strict_types=1);
$_TITLE .= " \ "._('Form');
$_MENU .= '';

switch ($m[1] ?? null) {
    case 'add':
        $_TITLE .= " \ "._('Add').' - '._('Step').' 1';

        // Check access
        if (!_isTLDUser()) {
            $_ERROR[] = _('You do not have permissions to use this feature');

            return;
        }

        // Get listing
        $acl = new tldGroup('ACL_AUTH_INTRANET');
        $peopleList = $acl->getUserlist('smartyOptions');
        $typeList = tldDMSType::getList();
        $typeListAsIdDesc = tldUtils::optionsByKeyValue($typeList, 'id', 'short_desc');
        $buList = tldLocation::getBuListAsIdBU();
        $langList = tldDMS::getLangList();
        $systList = tldDMS::getSystemRefList();
        $portalList = tldDMS::getPortalList();
        $yesNoList = ['' => '', 'Y' => 'Yes', 'N' => 'No'];
        $accessTypeList = tldDMS::getAccessTypeList();
        $departmentList = tldDepartment::getListAsIdDepartment();
        $periodicityList = tldDMS::getPeriodicityList();

        // Default form values
        $defaults = [];
        if ($user->isInGroup(['role_PSM'])) {
            $typeList = tldDMSType::getList();
            $typeListAsIdDesc = tldUtils::optionsByKeyValue($typeList, 'id', 'short_desc');
            // Get form
            $form = new HTML_QuickForm('add', 'post');
            $form->addElement('hidden', 'm[0]', 'form');
            $form->addElement('hidden', 'm[1]', 'add');
            // General info
            $titleForm = _('Add new DMS').' - '._('Step').' 0';
            $form->addElement('header', 'frmTitle', $titleForm);
            $form->addElement('select', 'type_id', _('Type'), ['' => ''] + $typeListAsIdDesc, ['id' => 'dms_type']);
            $form->addElement('select', 'lang', _('Language'), ['' => ''] + $langList, ['id' => 'dms_language']);
            $form->addElement('submit', 'btnSubmit', _('Submit'));

            // Update also language listing
            $langListSpecial = json_encode(array_keys(tldDMS::getSalesMaterialLangList()));

            $_BODY .= <<<JS
<script type="text/javascript">
    $(document).ready(function () {
        var type = $('#dms_type');
        type.change(function() {
            console.log($( "#dms_type option:selected" ).text())
            if ($( "#dms_type option:selected" ).text() === 'Sales Material') {
                var authorizedLanguages = $langListSpecial;
                $('#dms_language option').each(function() {
                    if (!authorizedLanguages.includes($(this).val())) {
                        $(this).remove();
                    }
                });
            }

        });
    });

</script>
JS;

            if (!$form->validate()) {
                $_BODY .= $form->toHTML();
                break;
            }

            $vars = $form->exportValues();

            // Special case if PSM and DMS Type is Sales Material (id=9) - Set Defaults
            if ('9' === $vars['type_id'] && 'en' === $vars['lang']) {
                $defaults['type_id'] = $vars['type_id'];
                $defaults['sysref'] = 'ISO:9001';
                $defaults['periodicity'] = 24;
                $coo = tldGroup::inGroup('role_COO', tldLocation::getERPByID($user->getBUID()));
                $defaults['approver[1]'] = $coo[0]['id'];
                $rceo = tldGroup::inGroup('role_RCOO', tldLocation::getERPByID($user->getBUID()));
                $defaults['approver[2]'] = $rceo[0]['id'];
                $gcoo = tldGroup::inGroup('role_GCOO');
                $defaults['approver[3]'] = $gcoo[0]['id'];
                $gtd = tldGroup::inGroup('role_GPID');
                $defaults['approver[4]'] = $gtd[0]['id'];
                // Update also language listing
                $langList = tldDMS::getSalesMaterialLangList();
            } else {
                $defaults['type_id'] = $vars['type_id'];
            }
        }

        // Get form
        $form = new HTML_QuickForm('add', 'post');
        $form->addElement('hidden', 'm[0]', 'form');
        $form->addElement('hidden', 'm[1]', 'add');
        // General info
        $titleForm = _('Add new DMS').' - '._('Step').' 1';
        $form->addElement('header', 'frmTitle', $titleForm);
        $form->addElement('header', 'frmTitle0', _('General information'));
        $form->addElement('text', 'title', _('Title'));
        $form->addElement('text', 'subject', _('Subject').'<br><em>('.
            _('Could be the name of a previously not DMS controlled document').')</em>');
        $form->addElement('textarea', 'description', _('Description'),
            ['wrap' => 'VIRTUAL', 'cols' => '60', 'rows' => '8']);
        $form->addElement('select', 'owner_id', _('Owner'), ['' => ''] + $peopleList);
        $form->addElement('select', 'lang', _('Language'), ['' => ''] + $langList);
        $form->addElement('select', 'type_id', _('Type'), ['' => ''] + $typeListAsIdDesc,
            ['onChange' => 'javascript:$.ajax({
            type:"POST",
            url: "ajax.php?m[0]=getPeriodicityByDmsTypeID",
            data:"id="+$(this).val(),
            success: function(data){
                $(\'#periodicity\').val(data);
            }
        });
    ']);
        $form->addElement('select', 'periodicity', _('Revision periodicity').'<br>'._('(in month)'),
            $periodicityList, ['id' => 'periodicity']);
        $form->addElement('select', 'sysref', _('System REF'), ['' => ''] + $systList);
        // Coverage info
        $form->addElement('header', 'frmTitle2', _('Coverage information'));
        $locationField = &$form->addElement(
            'advmultiselect', 'bu', null,
            $buList,
            [
                'size' => 10,
                'class' => 'pool',
                'style' => 'width:200px;',
            ]
        );
        $locationField->setLabel([_('Business unit coverage'), _('Business unit list'), _('Business unit selected')]);
        $locationField->setButtonAttributes('add', ['value' => '-->>', 'class' => 'inputCommand']);
        $locationField->setButtonAttributes('remove', ['value' => '<<--', 'class' => 'inputCommand']);
        $dpt = &$form->addElement(
            'advmultiselect', 'department', null,
            $departmentList,
            [
                'size' => 10,
                'class' => 'pool',
                'style' => 'width:200px;',
            ]
        );
        $dpt->setLabel([_('Department coverage'), _('Department list'), _('Department selected')]);
        $dpt->setButtonAttributes('add', ['value' => '-->>', 'class' => 'inputCommand']);
        $dpt->setButtonAttributes('remove', ['value' => '<<--', 'class' => 'inputCommand']);
        // Revision and Approvers info
        $form->addElement('header', 'frmTitle', _('Revision and Approvers information'));
        $max_step = 11;
        for ($i = 1; $i < $max_step; ++$i) {
            $form->addElement('select', "approver[$i]", _('Approver').' '._('Step')." $i", ['' => ''] + $peopleList);
        }
        // Apply rules
        $fieldsRequired = [
            'title', 'subject', 'description', 'owner_id', 'domain', 'periodicity', 'department', 'bu',
            'lang', 'type_id', 'sysref', 'portal', 'access_type', 'approver[1]',
        ];
        foreach ($fieldsRequired as $field) {
            $form->addRule($field, _('Field is required'), 'required');
        }
        // Set Defaults
        $defaults += [
            'owner_id' => $user->getID(),
            'lang' => $_LANG,
            'periodicity' => 12,
        ];
        $form->setDefaults($defaults);
        $form->addElement('submit', 'btnSubmit', _('Create DMS'));

        if (!$form->validate()) {
            $_BODY .= $form->toHTML();
            break;
        }

        $vars = $form->exportValues();
        // Arrange data for approvers
        $i = 0;
        $approver = [];
        foreach ($vars['approver'] as $stepNumber => $uid) {
            if (empty($uid)) {
                continue;
            }
            ++$i;
            $approver[] = [
                'step' => $i,
                'uid' => $uid,
            ];
        }
        // Check that at least one step was entered
        if (count($approver) < 1) {
            $_ERROR[] = _('Please ensure to have at least one approval step correctly filled in');
            $_BODY .= $form->toHTML();
            break;
        }
        $vars['approver'] = $approver;

        // Create DMS ---------------------------->

        $a = tldUtils::cleanupFormInput($vars);
        $dmsID = tldDMS::insert($a);
        if (is_string($dmsID)) {
            $_ERROR[] = _('Internal error, can not create DMS record').'<br>'._('Reason:').' '.$dmsID;
            break;
        }
        $dms = new tldDMS($dmsID);
        if ($dms->isEmpty()) {
            $_ERROR[] = _('Record not found or empty');
            break;
        }
        $_CONF[] = _('DMS created and general information set');

        // Add Coverage info ---------------------------->

        $buList = tldLocation::getBuListAsIdBU();
        $bus = tldUtils::cleanupFormInput($vars['bu']);
        if (count($bus)) {
            foreach ($bus as $k => $buid) {
                $e = $dms->addBu(['buid' => $buid]);
                if (is_string($e)) {
                    $_ERROR[] = sprintf(_('Business unit %s not added').'<br>'._('Reason').": $e", $buList[$buid]);
                }
            }
            $_CONF[] = _('DMS Business unit coverage creation proceeded');
        }
        $departmentList = tldDepartment::getListAsIdDepartment();
        $dpt = tldUtils::cleanupFormInput($vars['department']);
        if (count($dpt)) {
            foreach ($dpt as $k => $dptid) {
                $e = $dms->addDepartment(['dptid' => $dptid]);
                if (is_string($e)) {
                    $_ERROR[] = sprintf(_('Department %s not added').'<br>'._('Reason').": $e", $departmentList[$dptid]);
                }
            }
            $_CONF[] = _('DMS Department coverage creation proceeded');
        }

        // Add approvers ---------------------------->

        $approvers = tldUtils::cleanupFormInput($vars['approver']);
        foreach ($approvers as $k => $approver) {
            $e = $dms->addApprover(['step' => $approver['step'], 'uid' => $approver['uid']]);
            if (is_string($e)) {
                $_ERROR[] = sprintf(_('Approver step %s not added').'<br>'._('Reason').": $e", $approver['step']);
            }
        }
        $_CONF[] = _('DMS approval steps created');

        // Add logs ---------------------------->

        $dms->addLogEntry($user->getID(), 'DMS CREATED');
        $dms->addLogEntry($user->getID(), 'REVISION');

        // Message and next step link ---------------------------->

        // save dms object in session
        $sess['dms']['add']['dmsObj'] = serialize($dms);
        // Button for next step
        $btnUrl = "$php_self?m[0]=form&m[1]=add2";
        $btnLabel = _('Continue to next step').' >>';
        $_BODY = <<<EOF
<p align="right"><input type="submit" value="$btnLabel" onClick="javascript:window.location.href='$btnUrl';" /></p>
EOF;
        break;
    case 'add2':
        $_TITLE .= " \ "._('Add').' - '._('Step').' 2';
        // Check access
        if (!_isTLDUser()) {
            $_ERROR[] = _('You do not have permissions to use this feature');

            return;
        }
        // Check previous steps
        if (empty($sess['dms']['add']['dmsObj'])) {
            $_ERROR[] = _('Can not retrieve DMS created on previous steps');
            break;
        }
        // Get DMS
        $dms = unserialize($sess['dms']['add']['dmsObj']);
        if ($dms->isEmpty()) {
            $_ERROR[] = _('Can not get DMS created on previous steps');
            break;
        }
        // Get listing
        $portalList = tldDMS::getPortalList();
        $accessTypeList = tldDMS::getAccessTypeList();
        // Get form
        $form = new HTML_QuickForm('add', 'post');
        $form->addElement('hidden', 'm[0]', 'form');
        $form->addElement('hidden', 'm[1]', 'add2');
        $titleForm = sprintf(_("DMS creation for '%s'"), $dms->getTitle());
        $titleForm .= ' - '._('Step').' 2: '._('Portal');
        $form->addElement('header', 'frmTitle', $titleForm);
        $form->addElement('select', 'portal', _('Portal'), ['' => ''] + $portalList);
        $form->addElement('select', 'access_type', _('Access type'), ['' => ''] + $accessTypeList);
        $form->addRule('portal', _('Field is required'), 'required');
        $form->addRule('access_type', _('Field is required'), 'required');
        // Special Case for PSM and Sales Material DMS - Set Default Portal
        if ($user->isInGroup(['role_PSM']) && 9 == $dms->getTypeID()) {
            $defaults = ['portal' => 'EXTRANET'];
            $form->setDefaults($defaults);
        }
        $form->addElement('submit', 'btnSubmit', _('Continue').' >>');

        if (!$form->validate()) {
            $_BODY = $form->toHTML();
            $_BODY .= include 'view/acl/acl.definition.tpl.php';
            $_BODY .= include 'view/acl/acl.definition.restriction.tpl.php';
            break;
        }

        $vars = tldUtils::cleanupFormInput($form->exportValues());
        // Update DMS access restriction
        $a = [
            'portal' => $vars['portal'],
            'access_type' => $vars['access_type'],
        ];
        $e = $dms->update($a);
        if (is_string($e)) {
            $_ERROR[] = _('Was not able to set portal and access type field').'<br>'._('Reason').": $e";
            break;
        }
        // Redirect following access configuration
        if ('CONFIDENTIAL' == $vars['access_type'] && 'INTRANET' == $vars['portal']) {
            header("Location: $php_self?m[0]=form&m[1]=add3");
        } else {
            header("Location: $php_self?m[0]=form&m[1]=add4");
        }
        break;
    case 'add3':
        $_TITLE .= " \ "._('Add').' - '._('Step').' 3';
        // Check access
        if (!_isTLDUser()) {
            $_ERROR[] = _('You do not have permissions to use this feature');

            return;
        }
        // Check previous steps
        if (empty($sess['dms']['add']['dmsObj'])) {
            $_ERROR[] = _('Can not retrieve DMS created on previous steps');
            break;
        }
        // Get DMS
        $dms = unserialize($sess['dms']['add']['dmsObj']);
        if ($dms->isEmpty()) {
            $_ERROR[] = _('Can not get DMS created on previous steps');
            break;
        }

        // Get form
        $titleForm = sprintf(_("DMS creation for '%s'"), $dms->getTitle());
        $titleForm .= ' - '._('Step').' 3: '._('Restriction rules');
        $form = new tldOrgSelectionForm(
            [
                'link' => "$php_self?m[0]=form&m[1]=add3",
                'title' => $titleForm,
            ]
        );
        $_BODY = $form->toHTML();

        // If rules added, add to session
        if ($form->validate()) {
            $vars = tldUtils::cleanupFormInput($form->exportValues());
            // Check if at least one rule was added
            if (!count($vars)) {
                $_ERROR[] = _('Can not add rules, nothing was selected');
            }
            // Check rules limit
            $limitRules = 60;
            if (count($vars) > $limitRules) {
                $_ERROR[] = sprintf(_('Can not add rules, limit of %s rules reached'), $limitRules);
            }
            // Add rules
            foreach ($vars as $val) {
                $add = tldUtils::cleanupFormInput($val);
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
                }
            }
            if (empty($_ERROR)) {
                $_CONF[] = _('Restriction rules added');
            }
        }

        switch ($m[2] ?? null) {
            case 'delete':
                // Check rule id
                if (empty($rid) || !is_numeric($rid)) {
                    $_ERROR[] = _('Parameters sent empty or invalid');
                    break;
                }
                $e = $dms->deleteAclRuleByID($rid);
                if (is_string($e)) {
                    $_ERROR[] = _('Restriction rule not deleted.').'<br>'._('Reason').": $e";
                } else {
                    $_CONF[] = _('Restriction rule deleted');
                }
                break;
        }

        // Get actual rules
        $rules = $dms->getAclRules();
        // Display added restriction rules
        $report = new tldReportColumnar(
            $rules,
            [
                'xItems' => [
                    'division' => _('Division'),
                    'subdivision' => _('Subdivision'),
                    'region' => _('Region'),
                    'location' => _('Business Unit'),
                    'department' => _('Department'),
                    'function_dsc' => _('Function'),
                ],
                'title' => _('Restrictions rules created'),
                'functions' => [
                    'Delete' => [
                        'img' => '/shared/icons/application/delete.png',
                        'url' => "$php_self?m[0]=form&m[1]=add3&m[2]=delete&rid=",
                        'param' => 'id',
                    ],
                ],
            ]
        );
        $_BODY .= $report->fetch();
        // List of users
        $report = new tldReportColumnar(
            $dms->getAllowedUserList(),
            [
                'xItems' => [
                    'fullname' => 'Fullname',
                    'email' => 'Email',
                    'division' => 'Division/Region',
                    'location' => 'Business Unit',
                    'department' => 'Department',
                    'tld_function' => 'Alvest function',
                ],
                'title' => _('Confidential user list from rules above'),
            ]
        );
        $viewLabel = _('Display confidential user list');
        $hideLabel = _('Hide confidential user list');
        $_BODY .= <<<EOF
<br/>
<input type="button" value="$viewLabel" id="btnUserList" onClick="javascript:
    if(\$('#user_list').css('display')=='none'){
        \$('#user_list').css('display','block');
        \$('#btnUserList').attr('value','$hideLabel');
    }
    else{
        \$('#user_list').css('display','none');
        \$('#btnUserList').attr('value','$viewLabel');
    }
"/>
<div id="user_list" style="display:none;">{$report->fetch()}</div>
EOF;
        // Add link for next action if at least a rule was added
        if (count($rules)) {
            $btnUrl = "$php_self?m[0]=form&m[1]=add4";
            $btnLabel = _('Continue to next step').' >>';
            $_BODY .= <<<EOF
<p align="right"><input type="submit" value="$btnLabel" onClick="javascript:window.location.href='$btnUrl';" /></p>
EOF;
        }
        break;
    case 'add4':
        $_TITLE .= " \ "._('Add').' - '._('Step').' 4';
        // Check access
        if (!_isTLDUser()) {
            $_ERROR[] = _('You do not have permissions to use this feature');

            return;
        }
        // Check previous steps
        if (empty($sess['dms']['add']['dmsObj'])) {
            $_ERROR[] = _('Can not retrieve DMS created on previous steps');
            break;
        }
        // Get DMS
        $dms = unserialize($sess['dms']['add']['dmsObj']);
        if ($dms->isEmpty()) {
            $_ERROR[] = _('Can not get DMS created on previous steps');
            break;
        }

        // Get form
        $titleForm = sprintf(_("DMS creation for '%s'"), $dms->getTitle());
        $titleForm .= ' - '._('Step').' 4: '._('Add notification rules');
        $form = new tldOrgSelectionForm(
            [
                'link' => "$php_self?m[0]=form&m[1]=add4",
                'title' => $titleForm,
            ]
        );

        $_BODY = $form->toHTML();

        // If rules added, add to session
        if ($form->validate()) {
            $vars = tldUtils::cleanupFormInput($form->exportValues());
            do {
                // Check if at least one rule was added
                if (!count($vars)) {
                    $_ERROR[] = _('Can not add rules, nothing was selected');
                    break;
                }
                // Check rules limit
                $limitRules = 60;
                if (count($vars) > $limitRules) {
                    $_ERROR[] = sprintf(_('Can not add rules, limit of %s rules reached'), $limitRules);
                    break;
                }

                // Prepare rules
                $toAdd = [];
                foreach ($vars as $val) {
                    $add = tldUtils::cleanupFormInput($val);
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

                $fields = [
                    'bu_id' => 'people.bu_id',
                    'dpt_id' => 'people.dpt_id',
                    'fct_id' => 'people.fct_id',
                ];
                // Check nb people from new rules
                $constraintsRules = [];
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
<a href="$php_self?m[0]=form&m[1]=add4&m[2]=create&confirm=Y" class="button">$yes</a>
<a href="$php_self?m[0]=form&m[1]=add4" class="button">$no</a>
EOF;
                    $sess['dms']['add']['not'] = $toAdd;
                    break;
                }
                $m[2] = 'create';
            } while (0);
        }

        switch ($m[2] ?? null) {
            case 'create':
                if ('Y' == $_GET['confirm']) {
                    $toAdd = $sess['dms']['add']['not'];
                    unset($sess['dms']['add']['not']);
                }
                foreach ($toAdd as $add) {
                    $e = $dms->addNotificationRule($add);
                    if (is_string($e)) {
                        $_ERROR[] = _('Restriction rule not added.').'<br>'._('Reason').": $e";
                    }
                }
                // Confirm if ok
                if (empty($_ERROR)) {
                    $_CONF[] = _('Notification rules added');
                }
                break;
            case 'delete':
                // Check rule id
                if (empty($rid) || !is_numeric($rid)) {
                    $_ERROR[] = _('Parameters sent empty or invalid');
                    break;
                }
                $e = $dms->deleteNotificationRuleByID($rid);
                if (is_string($e)) {
                    $_ERROR[] = _('Notification rule not deleted.').'<br>'._('Reason').": $e";
                } else {
                    $_CONF[] = _('Notification rule deleted');
                }
                break;
        }

        // Get actual rules
        $rules = $dms->getNotificationRules();
        // Display added restriction rules
        $report = new tldReportColumnar(
            $rules,
            [
                'xItems' => [
                    'division' => _('Division'),
                    'subdivision' => _('Subdivision'),
                    'region' => _('Region'),
                    'location' => _('Business Unit'),
                    'department' => _('Department'),
                    'function_dsc' => _('Function'),
                ],
                'title' => _('Notification rules created'),
                'functions' => [
                    'Delete' => [
                        'img' => '/shared/icons/application/delete.png',
                        'url' => "$php_self?m[0]=form&m[1]=add4&m[2]=delete&rid=",
                        'param' => 'id',
                    ],
                ],
            ]
        );
        $_BODY .= $report->fetch();
        // List of users
        $report = new tldReportColumnar(
            $dms->getNotificationUserList(),
            [
                'xItems' => [
                    'fullname' => 'Fullname',
                    'email' => 'Email',
                    'division' => 'Division/Region',
                    'location' => 'Business Unit',
                    'department' => 'Department',
                    'tld_function' => 'Alvest function',
                ],
                'title' => _('Notification user list from rules above'),
            ]
        );
        $viewLabel = _('Display notification user list');
        $hideLabel = _('Hide notification user list');
        $_BODY .= <<<EOF
<br/>
<input type="button" value="$viewLabel" id="btnUserList" onClick="javascript:
    if(\$('#user_list').css('display')=='none'){
        \$('#user_list').css('display','block');
        \$('#btnUserList').attr('value','$hideLabel');
    }
    else{
        \$('#user_list').css('display','none');
        \$('#btnUserList').attr('value','$viewLabel');
    }
"/>
<div id="user_list" style="display:none;">{$report->fetch()}</div>
EOF;
        // Add link for next action
        $btnUrl = "$php_self?m[0]=form&m[1]=add5&id=".$dms->getID();
        $btnLabel = _('Complete creation and view DMS').' >>';
        $_BODY .= <<<EOF
<p align="right"><input type="submit" value="$btnLabel" onClick="javascript:window.location.href='$btnUrl';" /></p>
EOF;
        break;
    case 'add5':
        // Reset session
        $sess['dms']['add'] = null;
        // Redirect to DMS view
        header("Location: $php_self?m[0]=view&id=".$id);
        break;
    case 'byNumber':
        $form = new HTML_QuickForm('add', 'post');
        $form->addElement('hidden', 'm[0]', 'form');
        $form->addElement('hidden', 'm[1]', 'byNumber');
        $form->addElement('header', 'frmTitle', _('Search By ID'));
        $form->addElement('text', 'id', 'DMS#');
        $form->addRule('id', _('Field is required'), 'required');
        $form->addRule('id', _('Field is numeric'), 'numeric');
        $form->addElement('submit', 'btnSubmit', _('Submit'));

        if (!$form->validate()) {
            $_BODY = $form->toHTML();
            break;
        }

        $vars = tldUtils::cleanupFormInput($form->exportValues());
        $rows = tldDMS::byConstraints(['id' => $vars['id']]);
        if (!count($rows)) {
            $_ERROR[] = sprintf(_('DMS#%s not found'), $vars['id']);
            $_BODY = $form->toHTML();
            break;
        }
        header("Location: $php_self?m[0]=view&id=".$vars['id']);
        break;
    case 'search':
        $_TITLE .= " \ "._('Search');
        // Get listing
        $peopleList = tldDirectory::getUserlist('smartyOptions');
        $typeList = tldDMSType::getList();
        $typeListAsIdDesc = tldUtils::optionsByKeyValue($typeList, 'id', 'short_desc');
        $buList = tldLocation::getBuListAsIdBU();
        $langList = tldDMS::getLangList();
        $systList = tldDMS::getSystemRefList();
        $portalList = tldDMS::getPortalList();
        $accessTypeList = tldDMS::getAccessTypeList();
        $yesNoList = ['' => '', 'Y' => 'Yes', 'N' => 'No'];
        $accessTypeList = tldDMS::getAccessTypeList();
        $departmentList = tldDepartment::getListAsIdDepartment();
        $periodicityList = tldDMS::getPeriodicityList();
        $statusList = array_combine(tldDMS::getStatusList(), tldDMS::getStatusList());
        // Get form
        $form = new HTML_QuickForm('add', 'post');
        $form->addElement('hidden', 'm[0]', 'form');
        $form->addElement('hidden', 'm[1]', 'search');
        // Global
        $form->addElement('header', 'frmTitle0', _('Search by keyword'));
        $form->addElement('text', 'keyword', _('Keyword'));
        // General info
        $form->addElement('header', 'frmTitle1', _('Advanced DMS Search'));
        $form->addElement('header', 'frmTitle2', _('General information'));
        $form->addElement('text', 'title', _('Title'));
        $form->addElement('text', 'subject', _('Subject').'<br><em>('.
            _('Could be the name of a previously not DMS controlled document').')</em>');
        $statusField = &$form->addElement(
            'advmultiselect', 'status', null,
            $statusList,
            [
                'size' => 5,
                'class' => 'pool',
                'style' => 'width:200px;',
            ]
        );
        $statusField->setLabel([_('Status'), _('Status list'), _('Status selected')]);
        $statusField->setButtonAttributes('add', ['value' => '-->>', 'class' => 'inputCommand']);
        $statusField->setButtonAttributes('remove', ['value' => '<<--', 'class' => 'inputCommand']);
        $form->addElement('select', 'owner_id', _('Owner'), ['' => ''] + $peopleList);
        $form->addElement('select', 'lang', _('Language'), ['' => ''] + $langList);
        $form->addElement('select', 'type_id', _('Type'), ['' => ''] + $typeListAsIdDesc);
        $form->addElement('select', 'periodicity', _('Revision periodicity').'<br>'._('(in month)'),
            ['' => ''] + $periodicityList);
        $form->addElement('select', 'sysref', _('System REF'), ['' => ''] + $systList);
        $form->addElement('select', 'portal', _('Portal'), ['' => ''] + $portalList);
        $form->addElement('select', 'access_type', _('Access type'), ['' => ''] + $accessTypeList);
        // Coverage info
        $form->addElement('header', 'frmTitle3', _('Coverage information'));
        $locationField = &$form->addElement(
            'advmultiselect', 'bu', null,
            $buList,
            [
                'size' => 10,
                'class' => 'pool',
                'style' => 'width:200px;',
            ]
        );
        $locationField->setLabel([_('Business unit coverage'), _('Business unit list'), _('Business unit selected')]);
        $locationField->setButtonAttributes('add', ['value' => '-->>', 'class' => 'inputCommand']);
        $locationField->setButtonAttributes('remove', ['value' => '<<--', 'class' => 'inputCommand']);

        $ownerLocationField = &$form->addElement(
            'advmultiselect', 'owner_bu', null,
            $buList,
            [
                'size' => 10,
                'class' => 'pool',
                'style' => 'width:200px;',
            ]
        );
        $ownerLocationField->setLabel([sprintf('%s (%s)', _('Business unit coverage'), _('Owner')), _('Business unit list'), _('Business unit selected')]);
        $ownerLocationField->setButtonAttributes('add', ['value' => '-->>', 'class' => 'inputCommand']);
        $ownerLocationField->setButtonAttributes('remove', ['value' => '<<--', 'class' => 'inputCommand']);

        $dpt = &$form->addElement(
            'advmultiselect', 'department', null,
            $departmentList,
            [
                'size' => 10,
                'class' => 'pool',
                'style' => 'width:200px;',
            ]
        );
        $dpt->setLabel([_('Department coverage'), _('Department list'), _('Department selected')]);
        $dpt->setButtonAttributes('add', ['value' => '-->>', 'class' => 'inputCommand']);
        $dpt->setButtonAttributes('remove', ['value' => '<<--', 'class' => 'inputCommand']);
        // Revision and Approvers info
        $form->addElement('header', 'frmTitle4', _('Revision and Approvers information'));
        $form->addElement('select', 'approver', _('Is approver'), ['' => ''] + $peopleList);
        // Revision and Approvers info
        $form->addElement('header', 'frmTitle5', _('By expiration period').' ('._('Based on revision cycle and last activation date calculation').')');
        $form->addElement('text', 'expiration_from', _('Date from').' <em>(YYYY-MM-DD)</em>');
        $form->addElement('text', 'expiration_to', _('Date to').' <em>(YYYY-MM-DD)</em>');
        // Buttons
        $form->addElement('reset', 'btnReset', _('Reset'));
        $form->addElement('submit', 'btnSubmit', _('Submit'));

        $form->setDefaults([
            'bu' => [],
            'owner_bu' => [],
            'department' => [],
        ]);

        if (!$form->validate()) {
            $_BODY .= $form->toHTML();
            break;
        }

        $vars = tldUtils::cleanupFormInput($form->exportValues());
        // List of fields
        $searchFields = [
            'title', 'subject', 'status', 'owner_id', 'lang', 'type_id', 'periodicity', 'portal',
            'access_type', 'sysref', 'bu', 'department', 'approver', 'expiration_from', 'expiration_to', 'owner_bu',
        ];
        $a = [];
        // Global search
        if (!empty($vars['keyword'])) {
            $keyword = '%'.$vars['keyword'].'%';
            $b = [
                'title' => $keyword,
                'subject' => $keyword,
                'status' => $keyword,
                'ownerFullname' => $keyword,
                'typeDesc' => $keyword,
                'sysref' => $keyword,
                'description' => $keyword,
            ];
            $a[] = '('.tldUtils::constructWhere($b, 'OR').')';
        }
        $options = ['where' => ''];
        // Construct constraints
        foreach ($searchFields as $searchField) {
            if (empty($vars[$searchField])) {
                continue;
            }
            switch ($searchField) {
                case 'status':
                    $b = '(';
                    foreach ($vars[$searchField] as $k => $val) {
                        if (0 != $k) {
                            $b .= ' OR ';
                        }
                        $b .= "dms.status = '$val'";
                    }
                    $a[] = $b.')';
                    break;
                case 'bu':
                    $b = '(';
                    foreach ($vars[$searchField] as $k => $val) {
                        if (0 != $k) {
                            $b .= ' OR ';
                        }
                        $b .= "$val IN (SELECT buid FROM dms_bu WHERE parent_id=dms.id)";
                    }
                    $a[] = $b.')';
                    break;
                case 'owner_bu':
                    $options['where'] .= ' (';
                    foreach ($vars[$searchField] as $k => $val) {
                        if (0 != $k) {
                            $options['where'] .= ' OR ';
                        }
                        $options['where'] .= "people.bu_id = $val ";
                    }
                    $options['where'] .= ') ';
                    break;
                case 'department':
                    $b = '(';
                    foreach ($vars[$searchField] as $k => $val) {
                        if (0 != $k) {
                            $b .= ' OR ';
                        }
                        $b .= "$val IN (SELECT dptid FROM dms_department WHERE parent_id=dms.id)";
                    }
                    $a[] = $b.')';
                    break;
                case 'approver':
                    $a[] = "{$vars[$searchField]} IN (SELECT uid FROM dms_approver WHERE parent_id=dms.id)";
                    break;
                case 'title':
                case 'subject':
                    $a[] = "$searchField LIKE '%{$vars[$searchField]}%'";
                    break;
                case 'expiration_from':
                    if (empty($vars[$searchField]) || empty($vars['expiration_to'])) {
                        $_ERROR[] = _('Expiration period not taken in account').': '._('Date From and Date To fields are required');
                        continue 2;
                    }
                    // Check dates
                    try {
                        $fromDate = new DateTime($vars[$searchField]);
                    } catch (Exception $e) {
                        $_ERROR[] = sprintf(_("Expiration 'Date from' not valid (%s)"), $e->getMessage());
                        continue 2;
                    }
                    try {
                        $toDate = new DateTime($vars['expiration_to']);
                    } catch (Exception $e) {
                        $_ERROR[] = sprintf(_("Expiration 'Date To' not valid (%s)"), $e->getMessage());
                        continue 2;
                    }
                    // Check to date greater than from date
                    if ($fromDate > $toDate) {
                        $_ERROR[] = _('Expiration period not valid').': '._('Date To must be greater than Date From');
                        continue 2;
                    }
                    $a[] = <<<EOF
( dms.status LIKE 'ACTIVE'
AND UNIX_TIMESTAMP(DATE_ADD(dms.dt_act, INTERVAL dms.periodicity MONTH))
BETWEEN UNIX_TIMESTAMP('{$fromDate->format('Y-m-d')}')
AND UNIX_TIMESTAMP('{$toDate->format('Y-m-d')}') )
EOF;
                    // include date to to not go to default case...
                case 'expiration_to':
                    break;
                default:
                    $a[] = "$searchField='{$vars[$searchField]}'";
                    break;
            }
        }
        // Check nb constraints
        if (!count($a) && empty($options['where'])) {
            $_ERROR[] = _('No constraints set, can not do search');
        }

        if (empty($a)) {
            $a[] = '1 = 1';
        }

        // Check for errors
        if (count($_ERROR)) {
            $_BODY = $form->toHTML();
            break;
        }
        $HAVING = implode(' AND ', $a);
        $data = tldDMS::byConstraints($HAVING, $options);

        // Check limit (case of search error)
        $limit = 1000;
        if (count($data) > $limit) {
            $_ERROR[] = sprintf(_('Results limit of %s has been reached, please search using more criteria'), $limit);
            $_BODY = $form->toHTML();
            break;
        }

        if (!empty($data)) {
            $sess['dms']['list'] = $data;
            $_MENU .= "
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<a href=\"$php_self?m[0]=listing&out=xls\">"._('Download as XLS').'</a>
';
        }
        $_BODY .= _getListing($data, _('Search results'));
        break;
}
