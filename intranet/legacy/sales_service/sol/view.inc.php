<?php

use ApiBundle\Client;
use Symfony\Component\HttpClient\Exception\ClientException;

if (empty($id)) {
    $DEFAULT_ERROR[] = 'ERROR: no line id set...';
    return;
}
$sol = new tldSOL($id);
if ($sol->isEmpty()) {
    $DEFAULT_ERROR[] = "ERROR: No SOL#$id found...";
    return;
}
$header = $sol->itsHeader;
$smarty->assign('sol', $header);
if ($setcur) {
    $sess['sales_service']['sol']['dispcur'] = strtoupper($setcur);
    $DEFAULT_ERROR[] = 'Display currency now set to ' . $sess['sales_service']['sol']['dispcur'];
}
if ($sess['sales_service']['sol']['dispcur']) {
    $dispcur = $sess['sales_service']['sol']['dispcur'];
} else {
    $dispcur = $sol->getDCUR();
}

$DEFAULT_TITLE .= "\SOR Line#$id ($dispcur)";
$DEFAULT_MENU .= <<<EOF
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<a href="$php_self?m[0]=sol&m[1]=view&m[2]=lines&id=$id">General</a>
&nbsp;|&nbsp;<a href="$php_self?m[0]=sol&m[1]=view&m[2]=tasks&id=$id" title="Related tasks">Tasks</a>
&nbsp;|&nbsp;<a href="$php_self?m[0]=sol&m[1]=view&m[2]=files&id=$id" title="Files linked to this SOR Line">Files</a>
&nbsp;|&nbsp;<a href="$php_self?m[0]=sol&m[1]=view&m[2]=log&id=$id" title="Activity Log">Log</a>
&nbsp;|&nbsp;<a href="$php_self?m[0]=sol&m[1]=view&m[2]=links&id=$id">Links</a>
&nbsp;|&nbsp;<a href="$php_self?m[0]=sol&m[1]=view&m[2]=delivery&id=$id" title="Delivery information">Delivery</a>
&nbsp;|&nbsp;<a href="$php_self?m[0]=sol&m[1]=view&m[2]=payment&id=$id" title="Payment information">Payment & Warranty</a>
&nbsp;|&nbsp;<a href="$php_self?m[0]=sol&m[1]=view&m[2]=summary&id=$id" title="Summary information">Summary</a>
&nbsp;|&nbsp;<a href="$php_self?m[0]=sol&m[1]=view&m[2]=options&id=$id" title="Options information">Options</a>
&nbsp;|&nbsp;<a href="$php_self?m[0]=sol&m[1]=view&m[2]=breakdown&id=$id" title="Breakdown information">Breakdown</a>
&nbsp;|&nbsp;<a href="$php_self?m[0]=sol&m[1]=view&m[2]=er&id=$id" title="Equipment Records linked to this SOR Line">ER</a>
&nbsp;|&nbsp;<a href="$php_self?m[0]=sol&m[1]=view&m[2]=changeStatus&id=$id" title="Change status of SOR Line">Status</a>
&nbsp;|&nbsp;<a href="$php_self?m[0]=sol&m[1]=view&m[2]=tran&id=$id" title="Finance Transactions">Tran</a>
&nbsp;|&nbsp;<a href="$php_self?m[0]=sol&m[1]=view&m[2]=changeDispCur&id=$id" title="Change Display Currency">Currency</a>
&nbsp;|&nbsp;<a href="$php_self?m[0]=sol&m[1]=view&m[2]=dup&id=$id" title="Make a duplicate copy">Duplicate</a>
&nbsp;|&nbsp;<a href="$php_self?m[0]=sol&m[1]=view&m[2]=del&id=$id" title="Delete SOL">Delete</a>
&nbsp;|&nbsp;<a href="$php_self?m[0]=sor&m[1]=view&id=${header['parent_id']}" title="Go to parent SOR">SOR#${header['parent_id']}</a>
EOF;
if ($header['sfr_id']) {
    global $kernel;
    try {
        $container = $kernel->getContainer();
        $router = $container->get('router');
        $sfrRoute = $router->generate('sales_forecasts_show', ['id' => $header['sfr_id']]);
    } catch (\Exception $e) {
        $DEFAULT_ERROR[] = 'Error while generating SFR route';
    }
    $DEFAULT_MENU .= <<<EOF
&nbsp;|&nbsp;<a href="$sfrRoute" title="Go to SFR">SFR#{$header['sfr_id']}</a>
EOF;
}
if ($user->isInGroup(['ROLE_SA', 'ROLE_ASM', 'gg_ADMIN']) && (is_EngRevPassed($sol->getStatus()) || $sol->isClosed())) {
    $DEFAULT_MENU .= <<<EOF
&nbsp;|&nbsp;<a href="$php_self?m[0]=sol&m[1]=view&m[2]=er&m[3]=receipt&m[4]=form&id=$id" title="Download Acknowledgement of Receipt">Download Acknowledgement of Receipt</a>
EOF;
}

$DEFAULT_MENU .= <<<EOF
&nbsp;|&nbsp;<a href="$php_self?m[0]=sol&m[1]=view&m[2]=editSOL&id=$id" title="Edit this SOL">Edit SOL</a>
EOF;
if (is_PrintSOAckPassed($sol->getStatus()) && $sol->getStatus() !== 'CLOSED') {
    $DEFAULT_MENU .= <<<EOF
&nbsp;|&nbsp;<a href="$php_self?m[0]=sol&m[1]=view&m[2]=requestSOLModification&id=$id" title="Request SOL modification">Request SOL modification</a>
EOF;
}
if ($user->isInGroup(['role_FC', 'gg_ADMIN', 'role_PSM', 'ROLE_FAM'])) {
    $DEFAULT_MENU .= <<<EOF
	&nbsp;|&nbsp;<a href="$php_self?m[0]=sol&m[1]=view&m[2]=factorymargin&id=$id" title="Factory Margin">Factory Margin</a>
EOF;
}

if (!$sol->isClosed()) {
    $DEFAULT_MENU .= <<<EOF
&nbsp;|&nbsp;<a href="$php_self?m[0]=sol&m[1]=view&m[2]=changeStatus&m[3]=toClose&id=$id" title="Close SOR Line as Stock Unit Order">Close</a>
&nbsp;|&nbsp;<a href="$php_self?m[0]=sol&m[1]=view&m[2]=changeStatus&m[3]=toCancel&id=$id" title="Cancel SOL">Cancel</a>
EOF;
}
if (is_EngRevPassed($sol->getStatus())) {
    $DEFAULT_MENU .= <<<EOF
&nbsp;|&nbsp;<a href="$php_self?m[0]=sol&m[1]=view&m[2]=changeStatus&m[3]=toEngReview&id=$id" title="Restart Factory Order Review process">Order Revision</a>	
EOF;
}
if ($user->isInGroup(['gg_ADMIN', 'superuser'])
    || ($user->isInGroupLevel('role_SAM', $header['sso_erp']) && in_array($header['status'], tldSol::getSSOStatusList(), true))
    || ($user->isInGroupLevel('role_PSM', $header['bu_erp']) && in_array($header['status'], tldSol::getFactoryStatusList(), true))
) {
    $DEFAULT_MENU .= <<<EOF
        &nbsp;|&nbsp;<a href="$php_self?m[0]=sol&m[1]=view&m[2]=adminStatus&id=$id" title="Admin status">Admin Status</a>	
EOF;
}
if ($user->isInGroup(['gg_ADMIN', 'superuser'])) {
    $DEFAULT_MENU .= <<<EOF
        &nbsp;|&nbsp;<a href="/en/private/sales_service/sol/sol_admin.php?mode=record_view&form_type=main_tpl&id=$id">Admin</a>
EOF;
}
if ($user->isInGroup(['gg_ADMIN', 'gg_ENG', 'role_ENG', 'role_EM'])) {
    $DEFAULT_MENU .= <<<EOF
&nbsp;|&nbsp;<a href="/en/private/manufacturing/eng/dev.php?m[0]=timekeeping&m[1]=form&m[2]=add2&module_id=$id&link=SOL&category=Design+Task&location={$header['bu_erp']}">Create Timesheet</a>
EOF;
}


// Show menu listing of SOL
$body .= $smarty->fetch("$PATH/sol/sol.menu.tpl");

switch ($m[2]) {
    case 'changeDispCur':
        $report = new tldHTMLList(
            tldForex::getCurrencyList(),
            '',
            "$php_self?m[0]=sol&m[1]=view&id=$id&setcur="
        );
        $body .= $report->fetch();
        break;
    case 'factorymargin':
        if (!$user->isInGroup(['gg_ADMIN', 'role_FC', 'role_PSM', 'ROLE_FAM'])) {
            $DEFAULT_ERROR[] = 'ERROR: You do not have permissions to access this page';
            break;
        }
        $DEFAULT_TITLE .= "\Factory Margin";
        $report = new tldAssocTable(
            $header,
            [
                'bu_fullname' => 'Factory',
                'factory_margin' => 'Projected Factory Margin (%)',
            ],
            ['title' => 'Factory Margin']
        );
        $body .= $report->fetch();
        break;
    case 'editSOL':
        if (!$user->isInGroup(['gg_ADMIN', 'role_ASM', 'role_SA', 'role_PSM', 'role_PSE', 'role_PSA', 'role_FC', 'role_EVP', 'role_COO', 'role_CMO'])) {
            $DEFAULT_ERROR[] = 'ERROR: You do not have permissions to modify a SOL';
            break;
        }
        // Check if allowed after EVP_APPROVAL
        // PSM should be able to edit the factories related fields (WC conditions and delivery penalties)
        // So they get a shorter form after EVP approval
        if (!is_AllowedAfterEVP_APPROVAL() && !$user->isInGroup(['role_PSM', 'role_PSE', 'role_PSA'])) {
            $DEFAULT_ERROR[] = 'ERROR: You do not have permissions to make any modifications after EVP_APPROVAL status';
            break;
        }

        if (is_PrintSOAckPassed($sol->getStatus()) && !$user->isInGroup(['role_SA'])) {
            $DEFAULT_ERROR[] = "ERROR: The SOL status doesn't allow to use this function, you must ask for a SOL modification.";
            break;
        }
        // Get lists
        $models = tldUtils::getSqlToAssocArray(
            "SELECT model FROM models WHERE hide=0 AND model<>' ALL_MODELS' ORDER BY model",
            'smartyOptions', ['model', 'model']
        );
        $TIERS = tldList::optionsByListNameAsListItemListItem('list.engine.tiers');
        if (!empty($header['eng_tier']) && !in_array($header['eng_tier'], $TIERS, true)) {
            $TIERS[$header['eng_tier']] = $header['eng_tier'];
        }
        $FactoryList = ['28' => 'TLD JST'] + tldLocation::getFactoryList('smartyOptionsIDLocation');
        $SalesOrgList = tldLocation::getSalesOrgList('smartyOptionsIDLocation');
        // Get form
        $form = new HTML_QuickForm('frmDelivery', 'post');
        $form->addElement('hidden', 'm[0]', 'sol');
        $form->addElement('hidden', 'm[1]', 'view');
        $form->addElement('hidden', 'm[2]', 'editSOL');
        $form->addElement('hidden', 'id', $id);
        $disabled = ['disabled' => 'disabled'];
        $options = [];
        // General Information
        if (!is_EVPstatusPassed($header['status']) || is_AllowedAfterEVP_APPROVAL()) {
            $form->addElement('header', 'title', 'General Information');
            $form->addElement('text', 'sls_orno', 'SSO PO#', ['size' => 40]);
            $form->addElement('text', 'erp_orno', 'Factory SO#', ['size' => 40]);
            $form->addElement('select', 'bu', 'TLD Factory BU',
                ['' => ''] + $FactoryList + $SalesOrgList
            );
            $options = ['style' => 'width:270px'];
            if (!in_array($header['status'], ['PENDING', 'CREATE_PO', 'EVP_APPROVAL', 'PRINT_SO_ACK', 'CREATE_SO_ACK', 'PRINT_PO'])) {
                $options += $disabled;
                $form->addElement('select', 'model', 'Model',
                    ['' => ''] + $models,
                    $options
                );
            } else {
                $form->addElement('select', 'model', 'Model',
                    ['' => ''] + $models,
                    $options
                );
                $form->addRule('model', 'Required', 'required');
            }

            $options = ['style' => 'width:270px'];
            if (is_EVPstatusPassed($header['status']) && (!$user->isInGroup(['role_SA']) || is_PrintSOAckPassed($sol->getStatus()))) {
                $options += $disabled;
                $form->addElement('select', 'eng_tier', 'Emission Rating',
                    ['' => ''] + $TIERS,
                    $options
                );
            } else {
                $form->addElement('select', 'eng_tier', 'Emission Rating',
                    ['' => ''] + $TIERS,
                    $options
                );
                $form->addRule('eng_tier', 'Required', 'required');
            }

            $form->addElement('select', 'parts_inc', 'Ship with Spare Parts?', ['' => '', 'Y' => 'YES', 'N' => 'NO']);
            $form->addElement('select', 'factory_shipping', 'Factory Shipping Parts',
                ['' => ''] + $FactoryList
            );
            addFormRuleFactoryShipping($form);
            $form->addElement('checkbox', 'certificate_of_origin_required', 'Certificate of origin required?', '');
            $form->addElement('textarea', 'docs_inc', 'Special Documentary Requirements', ['rows' => 5, 'cols' => 40]);
            $form->addElement('textarea', 'notes', 'Notes', ['rows' => 5, 'cols' => 40]);

            $options = $user->isInGroup(['role_FC', 'gg_ADMIN', 'role_SA']) ? [] : $disabled;
            $form->addElement('text', 'dzk_sso', 'Date of Zero Backlog, SSO', $options);
            $form->addElement('text', 'dzk_erp', 'Date of Zero Backlog, Factory', $options);

            $form->addElement('select', 'intro_new', 'New Introduction ?', ['' => '', 'Customer' => 'Customer', 'Product' => 'Product', 'No' => 'No']);
            // Warranty informations
            $options = ['wrap' => 'VIRTUAL', 'cols' => 40, 'rows' => 5];
            if (is_PrintSOAckPassed($sol->getStatus())) {
                $options += $disabled;
            }
            $form->addElement('header', 'title', 'Warranty Information');
            $form->addElement('textarea', 'wrty_spec', 'Special Warranty Conditions', $options);
            $form->addElement('select', 'warranty_length', 'Warranty Length (Months)', ['' => ''] + tldEquipment::getAllowedWarrantyLength(), $options);
            $form->addElement('text', 'warranty_length_hours', 'Warranty Length (Hours)', $options);
            if (!array_key_exists('disabled', $options)) {
                $form->addRule('warranty_length', 'Required', 'required');
                $form->addRule('warranty_length_hours', 'Required', 'required');
            }
        }
        if (!is_EVPstatusPassed($header['status']) || (is_AllowedAfterEVP_APPROVAL() && !is_PrintSOAckPassed($sol->getStatus())) || $user->isInGroup(['role_PSM', 'role_PSE', 'role_PSA'])) {
            $form->addElement('select', 'conf_wrty_erp', 'Warranty Conditions accepted by factory?', ['' => '', 'Y' => 'YES', 'N' => 'NO'], $options);
            if (!array_key_exists('disabled', $options)) {
                $form->addRule('conf_wrty_erp', 'Required', 'required');
            }
        } elseif (is_PrintSOAckPassed($sol->getStatus())) {
            $form->addElement('hidden', 'conf_wrty_erp', $header['conf_wrty_erp']);
        }
        if (!is_EVPstatusPassed($header['status']) || is_AllowedAfterEVP_APPROVAL()) {
            // Payment Conditions
            $cursList = tldList::optionsByListNameAsListItemListItem('list.common.currency');
            $form->addElement('header', 'title', 'Payment Conditions');
            $form->addElement('textarea', 'tpay', 'Payment terms', ['rows' => 5, 'cols' => 40, 'onchange' => 'javascript:this.value=this.value.slice(0,254);']);
            $form->addElement('select', 'conf_cxo', 'Is Payment terms 100% after shipment<br />or Has the downpayment been received (if any)<br />or Has the LC been opened (if any)', ['N' => 'NO', 'Y' => 'YES']);
            $form->addElement('select', 'conf_lc', 'Letter of Credit Required?', ['' => '', 'Y' => 'YES', 'N' => 'NO']);

            $options = ['style' => 'width:270px'];
            if (is_PrintSOAckPassed($sol->getStatus())) {
                $options += $disabled;
                $form->addElement('select', 'cu_ocur', 'Customer Order Currency', ['' => ''] + $cursList, $options);
            } else {
                $form->addElement('select', 'cu_ocur', 'Customer Order Currency', ['' => ''] + $cursList, $options);
                $form->addRule('cu_ocur', 'Required', 'required');
            }

            $form->addElement('text', 'dp_amt', 'Down Payment Amount', ['size' => 40]);
            $form->addElement('text', 'dp_pc', 'Down Payment (in %)', ['size' => 40]);
            $form->addElement('text', 'receivedp_amt', 'Received Down Payment Amount', ['size' => 40]);
            $form->addElement('select', 'receive_lc', 'Letter of Credit Received?', ['' => '', 'Y' => 'YES', 'N' => 'NO']);
            // Delivery Conditions
            $listTrans = tldList::optionsByListNameAsListKeyListItem('list.inco.terms');
            $form->addElement('header', 'title', 'Delivery Conditions');
            $form->addElement('select', 'inco', 'Inco Terms', ['' => ''] + $listTrans, ['style' => 'width:270px']);
            $form->addElement('text', 'inco_loc', 'Inco Location', ['size' => 40]);
            if (is_PrintSOAckPassed($sol->getStatus())) {
                $form->addElement('select', 'ctry', 'Country', ['' => ''] + tldCountry::optionsAsNameName(), $disabled);
            } else {
                $form->addElement('select', 'ctry', 'Country', ['' => ''] + tldCountry::optionsAsNameName());
                $form->addRule('ctry', 'Required', 'required');
            }
        }
        $options = [];
        if (is_PrintSOAckPassed($sol->getStatus())) {
            $options += $disabled;
        }
        if (!is_EVPstatusPassed($header['status']) || is_AllowedAfterEVP_APPROVAL()) {
            $form->addElement('select', 'del_pen', 'Late delivery penalties?', ['' => '', 'Y' => 'YES', 'N' => 'NO'], $options);
            if (!array_key_exists('disabled', $options)) {
                $form->addRule('del_pen', 'Required', 'required');
            }
        } elseif ($user->isInGroup(['role_PSM', 'role_PSE', 'role_PSA'])) {
            $form->addElement('hidden', 'del_pen', $header['del_pen']);
            $form->addRule('del_pen', 'Required', 'required');
        }

        if (!is_EVPstatusPassed($header['status']) || is_AllowedAfterEVP_APPROVAL()) {
            $form->addElement('textarea', 'delpen_cond', 'Late delivery conditions', array_merge(['rows' => 5, 'cols' => 40], $options));
            $form->addElement('select', 'conf_sls', 'Delivery Penalty Accepted by Sales Org?', ['' => '', 'Y' => 'YES', 'N' => 'NO'], $options);
            if (!array_key_exists('disabled', $options)) {
                $form->addRule('conf_sls', 'Required', 'required');
            }
        }
        if (!is_EVPstatusPassed($header['status']) || (is_AllowedAfterEVP_APPROVAL() && !is_PrintSOAckPassed($sol->getStatus())) || $user->isInGroup(['role_PSM', 'role_PSE', 'role_PSA'])) {
            $form->addElement('select', 'conf_erp', 'Are these delivery penalties approved and backed-up by the TLD factory ?', ['' => '', 'Y' => 'YES', 'N' => 'NO']);
            if (!array_key_exists('disabled', $options)) {
                $form->addRule('conf_erp', 'Required', 'required');
            }
            $form->addElement('select', 'conf_dms', 'If needed, has DMS#3228 been completed, signed and stored in the SFR ?', ['' => '', 'Y' => 'YES', 'N' => 'NO']);
            if (!array_key_exists('disabled', $options)) {
                $form->addRule('conf_dms', 'Required', 'required');
            }
        } elseif (is_PrintSOAckPassed($sol->getStatus())) {
            $form->addElement('hidden', 'conf_erp', $header['conf_erp']);
        }

        if (!is_EVPstatusPassed($header['status']) || is_AllowedAfterEVP_APPROVAL()) {
            $form->addElement('text', 'trans', 'Transportation responsibility', ['size' => 40, 'maxlength' => 10]);
            $form->addElement('select', 'conf_cis', 'Customer inspection before shipment?', ['' => '', 'N' => 'N', 'Y' => 'Y'], $options);
            if (!array_key_exists('disabled', $options)) {
                $form->addRule('conf_cis', 'Required', 'required');
            }
        }
        $form->addElement('textarea', 'delivery_address', 'Delivery address',
            ['wrap' => 'VIRTUAL', 'cols' => '40', 'rows' => '8']);
        if ($user->isInGroup(['role_PSM', 'role_PSE', 'role_PSA'])) {
            $form->addElement('select', 'export_licence_status', 'Export Licence', array_combine(tldSOL::getExportLicenceStatuses(), tldSOL::getExportLicenceStatuses()));
            if (!array_key_exists('disabled', $options)) {
                $form->addRule('export_licence_status', 'Required', 'required');
            }
        } else {
            $form->addElement('hidden', 'export_licence_status', $header['conf_erp']);
        }
        $form->addElement('text', 'sfr_id', 'SFR New id (Not Legacy ID)');
        $form->addElement('text', 'batch_quantity', 'Batch Quantity');
        $form->addElement('header', 'title', 'FMS Contract');
        $form->addElement('text', 'fms_contract_length', 'FMS Contract lenght in months', ['placeholder' =>  'Enter 0 if not relevant']);
        $form->addRule('fms_contract_length', 'Should be a number between 0 and 240', 'regex', '/^(240|2([0-3]?\d)|(1\d{2})|(\d{1,2}))$/');
        // Add buttons
        $form->addElement('submit', 'btnSubmit', 'Submit');
        $form->addElement('reset', 'btnReset', 'Reset');
        // Put required field
        $requiredFields = [
            'bu', 'parts_inc', 'tpay', 'conf_cxo', 'conf_lc',
            'inco', 'inco_loc', 'intro_new', 'fms_contract_length', 'batch_quantity'
        ];
        foreach ($requiredFields as $key => $field) {
            $form->addRule($field, 'Required', 'required');
        }
        $form->setDefaults($header);

        if (!$form->validate()) {
            $body .= $form->toHTML();
            break;
        }

        $rawData = $form->exportValues();
        // Check delivery penalties & default values if applicable
        if ($rawData['del_pen'] == 'N') {
            $rawData['conf_sls'] = 'N';
            $rawData['conf_erp'] = 'N';
            $DEFAULT_ERROR[] = 'NOTE: Delivery penalties set to N, so SSO and ERP penalties set automatically to N';
        }
        $rawData['certificate_of_origin_required'] = $rawData['certificate_of_origin_required'] ?? 0;
        // Cleanup form values
        $vars = tldUtils::cleanupFormInput($rawData);

        if (!in_array($header['status'], ['PENDING', 'CREATE_PO', 'EVP_APPROVAL', 'PRINT_SO_ACK', 'CREATE_SO_ACK', 'PRINT_PO'])) {
            // check model
            if (isset($vars['model']) && stripslashes($vars['model']) != $header['model']) {
                $DEFAULT_ERROR[] = 'ERROR: You can not change the model';
                break;
            }
        }
        if (is_EVPstatusPassed($header['status'])) {
            // check qty
            if (isset($vars['qty']) && $vars['qty'] > $header['qty']) {
                $DEFAULT_ERROR[] = 'ERROR: You can not add quantity';
                break;
            }
        }
        if (is_EVPstatusPassed($header['status']) && !$user->isInGroup(['role_SA'])) {
            // Check Tier
            if (isset($vars['eng_tier']) && $vars['eng_tier'] != $header['eng_tier']) {
                $DEFAULT_ERROR[] = 'ERROR: You can not change the emission rating';
                break;
            }
        }

        if (!empty($vars['sfr_id'])) {
            try {
                global $kernel;
                $container = $kernel->getContainer();
                $client = $container->get(Client::class);
                $apiSalesForecast = $client->find('sales/sales_forecasts', $vars['sfr_id']);
                if (!in_array($apiSalesForecast['status'], ['PARTIAL', 'ORDERED'], true)) {
                    $DEFAULT_ERROR[] = "ERROR: Provided SFR#{$vars['sfr_id']} is not in ORDERED nor PARTIAL status.";
                    break;
                }

                if ($vars['sfr_id'] !== $sol->itsHeader['sfr_id']) {
                    tldModLink::insert('SOL', $sol->getID(), 'SFR2', $apiSalesForecast['id']);
                }
            } catch (\Exception $e) {
                $DEFAULT_ERROR[] = 'SFR could not be retrieved.';
            }
        }

        // update the SOL
        unset($vars['m'], $vars['id'], $vars['btnSubmit'], $vars['btnReset']);
        $previousEmissionRating = $sol->getHeader()['eng_tier'];
        $previousModel = $sol->getHeader()['model'];
        $sor = new tldSOR($sol->getHeader()['parent_id']);
        $sorLines = $sor->getLines();

        $e = $sol->updateHeader($vars);
        if (is_string($e)) {
            $DEFAULT_ERROR[] = $e;
            break;
        }

        $resultChangeExportLicence = $sol->onChangeExportLicenseStatus($user, $sol->itsHeader['export_licence_status'], $vars['export_licence_status'], $sol->getStatus());
        if (is_string($resultChangeExportLicence)) {
            $DEFAULT_ERROR[] = "ERROR on change Export licence status : $resultChangeExportLicence";
        }

        $sol->refresh();
        $sol->openIbsTask($sorLines, $sor->getHeader(), $previousEmissionRating);
        $sol->openModelIbsTask($sorLines, $sor->getHeader(), $previousModel);

        // check if FMS contract length changed and reflect it in the linked ERs
        if ((int) $vars['fms_contract_length'] !== (int) $header['fms_contract_length']) {
            foreach(tldEquipment::bySORLine($sol->itsID) as $equipment) {
                $er = new tldEquipment($equipment['id'], true);
                $er->update($vars, ['fms_contract_length']);
                $er->addLogEntry($user->getId(), sprintf('FMS contract length updated from SOL#%s to %s', $header['id'], $vars['fms_contract_length']));
                $body .= "<p>Fms contract length updated for ER#{$equipment['id']}</p>";
            }
        }
        // check if warranty length in months changed and reflect it in the linked ERs
        if ((int) $vars['warranty_length'] !== (int) $header['warranty_length']) {
            foreach(tldEquipment::bySORLine($sol->itsID) as $equipment) {
                $er = new tldEquipment($equipment['id'], true);
                $er->update($vars, ['warranty_length']);
                $er->addLogEntry($user->getId(), sprintf('Warranty length (Months) updated from SOL#%s to %s', $header['id'], $vars['warranty_length']));
                $body .= "<p>Warranty length (Months) updated for ER#{$equipment['id']}</p>";
            }
        }
        // check if warranty length in months changed and reflect it in the linked ERs
        if ((int) $vars['warranty_length_hours'] !== (int) $header['warranty_length_hours']) {
            foreach(tldEquipment::bySORLine($sol->itsID) as $equipment) {
                $er = new tldEquipment($equipment['id'], true);
                $er->update($vars, ['warranty_length_hours']);
                $er->addLogEntry($user->getId(), sprintf('Warranty length (Hours) updated from SOL#%s to %s', $header['id'], $vars['warranty_length_hours']));
                $body .= "<p>Warranty length (Hours) updated for ER#{$equipment['id']}</p>";
            }
        }

        // Email Notification if change in Ship with Spare Parts field
        if (isset($vars['parts_inc']) && $header['parts_inc'] != $vars['parts_inc']) {
            $MSG = "Ship with Spare Parts field changed from '" . $header['parts_inc'] . "' to '" . $vars['parts_inc'] . "'.";

            if ('Y' === $vars['parts_inc']) {
                $sol->createPartsTask($user, $MSG);
            } else {
                // Contents
                $SUBJECT = "SOL#$id - Ship with Spare Parts UPDATE by " . $user->getFullname();
                $MSG .= "<br><br><a href=\"https://www.tld-gse.com/en/private/sales_service/sales.php?m[0]=sol&m[1]=view&id=$id\">Click here to see SOL#$id</a>";
                // Recipients
                // --- SPR
                $TO = $sol->getPartsRecipients();
                // ---  CC sales admin
                $grpSA = new tldGroup('role_SA', $sol->getSSOERP());
                $CC = $grpSA->getEmailList();
                // Notify
                $sol->notify(
                    $TO,
                    $SUBJECT,
                    $SUBJECT,
                    $CC
                );
            }


        }

        if (is_EVPstatusPassed($header['status'])) {
            // check field rules and log to perform
            $fieldToCheck = [
                'sls_orno', 'erp_orno', 'bu', 'model', 'eng_tier', 'parts_inc', 'notes', 'wrty_spec', 'warranty_length', 'warranty_length_hours',
                'conf_wrty_erp', 'tpay', 'conf_cxo', 'conf_lc', 'cu_ocur', 'dp_amt', 'dp_pc', 'inco',
                'inco_loc', 'del_pen', 'delpen_cond', 'conf_sls', 'conf_erp', 'trans', 'conf_cis', 'ctry',
                'dzk_sso', 'dzk_erp', 'fms_contract_length',
            ];
            _updateProcessAfterEVP_APPROVAL(
                $SOL_FIELDS,
                $fieldToCheck,
                $header,
                $rawData
            );
            // check if special warranty conditions changed
            if (!$sol->isWarrantyClaimAcceptedByFactory() && $vars['conf_wrty_erp'] != $header['conf_wrty_erp']) {
                $sol->notifyWarrantyRejectionByFactory($user->getEmail());
                $body .= '<p>Special Warranty conditions rejection by factory notified to SSO</p>';
            }
            // check if delivery penalty changed
            if (!$sol->isDeliveryPenaltyAcceptedByFactory() && $vars['conf_erp'] <> $header['conf_erp']) {
                $sol->notifyDeliveryPenaltyRejectionByFactory($user->getEmail());
                $body .= '<p>Delivery penalty rejection by factory notified to SSO</p>';
            }
        }
        $body .= "<p>SOL#$id successfully updated !</p>";
        break;
    case 'requestSOLModification':
        if (!$user->isInGroup(['gg_ADMIN', 'role_ASM', 'role_SA', 'role_PSM', 'role_PSE', 'role_PSA', 'role_FC', 'role_EVP', 'role_COO', 'role_CMO', 'ROLE_ENG'])) {
            $DEFAULT_ERROR[] = 'ERROR: You do not have permissions to ask for a SOL modification';
            break;
        }
        if (!is_PrintSOAckPassed($sol->getStatus())) {
            $DEFAULT_ERROR[] = 'ERROR: The SOL is not did not pass PRINT_SO_ACK yet, you should use the edit function.';
            break;
        }

        if ($sol->getStatus() === 'CLOSED') {
            $DEFAULT_ERROR[] = "ERROR: This SOL is closed and can't be modified anymore.";
            break;
        }

        $units = tldSORUnit::byParent($id);
        $unitsAlreadyGreenTagged = array_filter($units, function ($unit) {
            return !empty($unit['dgt_act']) && $unit['dgt_act'] !== '0000-00-00';
        });
        $grp = new tldGroup('role_SA', $sol->getSSOERP());
        $salesAdmin = $grp->getUserlist(['smartyOptions' => true]);

        // Get form
        $form = new HTML_QuickForm('frmDelivery', 'post');
        $form->addElement('hidden', 'm[0]', 'sol');
        $form->addElement('hidden', 'm[1]', 'view');
        $form->addElement('hidden', 'm[2]', 'requestSOLModification');
        $form->addElement('hidden', 'id', $id);
        $form->addElement('checkbox', 'breakdown_impacted', 'Click here if modification concerns:<br/>breakdown (excluding external description), factory BU,<br/>model, emission rating, warranty conditions,<br/>customer order currency, country or late penalties', null, ['id' => 'breakdown_impacted']);
        $form->addElement('select', 'sales_admin', 'Sales administrator', $salesAdmin);
        $form->addElement('textarea', 'comment', 'Requested changes description', ['cols' => 40, 'rows' => 8]);
        $form->addElement('static', 'action', 'Resulting action :', ' '); // Do not move, used by JS
        $form->addElement('submit', 'btnSubmit', 'Submit');
        $form->addRule('comment', 'Required', 'required');

        foreach ($unitsAlreadyGreenTagged as $unit) {
            $DEFAULT_ERROR[] = "{$unit['sn']} has already been GT.";
        }
        $body .= <<<'HTML'
<script type="text/javascript">
    $(document).ready(function () {
        var $checkbox = $('#breakdown_impacted'); 
        var $formLine = $('select[name="sales_admin"]').closest('tr');
        var $message = $('input[type="submit"]').closest('tr').prev().find('td').last(); 
        
        function updateState (state) {
          $formLine.find('select').attr('disabled', state)
          $formLine.toggle(!state);
          $message.html('<span style="color:'+(state ? 'red' : 'green')+';">'+(state ? 'The SOL will be sent back to the PRINT_SO_ACK status.' : 'A task will be opened to the selected Sales Admin.')+'</span>');
        }
        
        // Using $checkbox.attr('checked') doesn't work properly when using browser history
        updateState($checkbox[0].checked)
       
        $checkbox.change(function() {
          updateState(this.checked)
        })
    });
</script>
HTML;

        if (!$form->validate()) {
            $body .= $form->toHTML();
            break;
        }

        $vars = tldUtils::cleanupFormInput($form->exportValues());
        $a = $form->exportValues();
        $parsedComment = str_replace(["\r\n", "\r", "\n"], '<br/>', $a['comment']);
        $vars = tldUtils::cleanupFormInput($a);

        // Move the status back in the workflow
        if (array_key_exists('breakdown_impacted', $vars) || empty($vars['sales_admin'])) {
            $newStatus = 'PRINT_SO_ACK';

            // Update the SOL Status
            $res = tldUtils::sqlQuery("UPDATE sor_lines SET status = '$newStatus' WHERE id = $id");
            if (is_string($res)) {
                $DEFAULT_ERROR[] = $res;
                break;
            }
            $sol->refresh();

            // Logging
            $sol->addLogEntry($user->getID(), "SOL#$id - Modification Request - Status changed back to $newStatus</br>{$vars['comment']}");

            // Notification
            $message = '';
            foreach ($unitsAlreadyGreenTagged as $unit) {
                $message .= '<p style="color:red;">' . $unit['sn'] . ' has already been GT.</p>';
            }

            $message .= "<p>Modification Request:</br>$parsedComment</p>";

            $to = (new tldGroup('role_SA', $sol->getSSOERP()))->getEmailList();
            $to = array_merge($to, (new tldGroup('role_PSM', $sol->getERP()))->getEmailList());
            $to = array_merge($to, (new tldGroup('role_PSE', $sol->getERP()))->getEmailList());
            $to = array_merge($to, (new tldGroup('role_PSA', $sol->getERP()))->getEmailList());
            $to[] = $sol->getASMEmail();

            $header = $sol->itsHeader;

            // Notify
            $sol->notify(
                array_unique($to),
                "{$header['model']}, {$header['qty_sou']}, {$header['cu_nama']}, SOL#$id, $newStatus - Modification Request",
                $message,
                $user->getEmail()
            );
        } else {
            $taskid = tldTask::insert(
                $sol->getId(),
                [
                    'assignee' => $vars['sales_admin'],
                    'assignor' => $user->getID(),
                    'task' => $vars['comment'],
                ],
                'SOL'
            );
            if (is_string($taskid)) {
                $DEFAULT_ERROR[] = 'There was an error and the task was not created.';
                break;
            }

            $task = new tldTask($taskid);
            $task->notifyAssignee(
                "<br><br><a href=\"https://www.tld-gse.com/en/private/calendar/calendar.php?m[0]=tasks&m[1]=task&m[2]=view&id=$taskid\">Click here to see task</a>",
                "SOL#{$sol->getId()} - Modification requested"
            );
        }

        header("Location: $php_self?m[0]=sol&m[1]=view&m[2]=lines&id=$id");
        break;

    case 'dup':
        if (!$user->isInGroup(['gg_ADMIN', 'role_ASM', 'role_SA', 'role_EVP'])) {
            $DEFAULT_ERROR[] = 'ERROR: You do not have permissions to access this page';
            break;
        }
        $form = new HTML_QuickForm('frmDupSOL', 'post');
        $form->addElement('hidden', 'm[0]', 'sol');
        $form->addElement('hidden', 'm[1]', 'view');
        $form->addElement('hidden', 'm[2]', 'dup');
        $form->addElement('hidden', 'id', $id);
        $form->addElement('header', 'title', 'Duplicate SOL');
        $form->addElement('select', 'confirm', 'Confirmation', ['', 'I Confirm' => 'I confirm']);
        $form->addElement('submit', 'btnSubmit', 'Duplicate...');

        if (!$form->validate()) {
            $body .= $form->toHTML();
            break;
        }

        $vars = $form->exportValues();
        if ($vars['confirm'] <> 'I Confirm') {
            $body .= $form->toHTML();
            break;
        }
        $newid = $sol->duplicate();

        if (!is_numeric($newid)) {
            $DEFAULT_ERROR[] = "ERROR: Could not duplicate, error was $newid";
            $body .= getGeneralTab($header);
            break;
        }

        $newSol = new tldSOL($newid, true);
        $salesAdmin = new tldGroup('role_SA', $sol->getSSOERP());
        $newSol->notify(
            $salesAdmin->getEmailList(),
            "SOL#$newid was created by duplication of SOL#$id",
            ''
        );

        $body .= <<<EOF
<br><br><br><br><br>
<a href="$php_self?m[0]=sol&m[1]=view&id=$newid">Duplicate SOL created, click here to view...</a>
EOF;
        break;
    case 'cancel':
        if (!$user->isInGroup(['gg_ADMIN', 'role_SA', 'role_EVP'])) {
            $DEFAULT_ERROR[] = 'ERROR: You do not have permissions to access this page';
            break;
        }
        $DEFAULT_ERROR[] = 'WARNING: This will close the SOL and remove any remaining backlog value.';
        $form = new HTML_QuickForm('frmCancelSOL', 'post');
        $form->addElement('hidden', 'm[0]', 'sol');
        $form->addElement('hidden', 'm[1]', 'view');
        $form->addElement('hidden', 'm[2]', 'cancel');
        $form->addElement('hidden', 'id', $id);
        $form->addElement('header', 'title', 'Cancel the above backlog value...');
        $form->addElement('select', 'confirm', 'Confirmation', ['', 'Y' => 'Y']);
        $form->addElement('submit', 'btnSubmit', 'Cancel remaining backlog...');
        $report = new tldAssocTable(
            $sol->getTranSummary($dispcur),
            [
                'ssob_tot' => 'SSO Bookings',
                'ssor_tot' => 'SSO Revenues',
                'ssok_tot' => 'SSO Backlog',
                'erpb_tot' => 'Factory Bookings',
                'erpr_tot' => 'Factory Revenues',
                'erpk_tot' => 'Factory Backlog',
            ],
            ['title' => "Backlog to be cancelled ($dispcur)"]
        );
        $body .= $report->fetch();

        if (!$form->validate()) {
            $body .= $form->toHTML();
            break;
        }

        $vars = $form->exportValues();
        if ($vars['confirm'] !== 'Y') {
            $DEFAULT_ERROR[] = $sol->cancel();
        }
        break;
    case 'del':
        $allowAccess = false;
        $allowedGroups = ['role_CFO', 'role_FC', 'role_SA', 'role_EVP', 'gg_ADMIN'];
        // Check permissions
        if (!is_EVPstatusPassed($sol->getStatus())) {
            // Allowed BEFORE EVP_APPROVAL
            if ($user->isInGroup(['role_ASM']) && (int)$user->getID() === (int)$sol->itsHeader['asmID']) {
                $allowAccess = true;
            }
        }
        // allowed at all time
        if ($user->isInGroup($allowedGroups)) {
            $allowAccess = true;
        }
        if (!$allowAccess) {
            $DEFAULT_ERROR[] = 'ERROR: You do not have permission to delete SOLs...';
            break;
        }
        $form = new HTML_QuickForm('frmDelSOL', 'post');
        $form->addElement('hidden', 'm[0]', 'sol');
        $form->addElement('hidden', 'm[1]', 'view');
        $form->addElement('hidden', 'm[2]', 'del');
        $form->addElement('hidden', 'id', $id);
        $form->addElement('header', 'title', 'Delete SOL');
        $form->addElement('select', 'confirm', 'Confirmation', ['', 'I Confirm' => 'I confirm I want to DELETE this SOL AND have followed the PROCEDURE']);
        $form->addElement('submit', 'btnSubmit', 'Delete...');

        if (!$form->validate()) {
            $body .= $form->toHTML();
            break;
        }

        $vars = $form->exportValues();
        if ($vars['confirm'] !== 'I Confirm') {
            $body .= $form->toHTML();
            break;
        }
        // 1 - Get all ER linked to SOL
        $erList = tldEquipment::bySORLine($id);
        // 2 - Check if EVP_APPROVAL reached to notify or not
        if (is_EVPstatusPassed($header['status'])) {
            // Get bu info
            $bu = [
                'sso' => $header['sso_erp'],
                'erp' => $header['bu_erp'],
            ];
            // Create recipient
            $TO = [
                $bu['sso'] => ['role_EVP', 'role_SA', 'role_FC'],
                $bu['erp'] => ['role_FC', 'role_PSM', 'role_PSE', 'role_PSA']
            ];
            // Create message
            $MSG = '<p>SOL#' . $id . ' have been deleted after EVP_APPROVAL</p>';
            $report = new tldReportColumnar(
                $erList,
                [
                    'xItems' => [
                        'id' => 'ER#',
                        'date_shipped' => 'Shipped date',
                        'rrd_sso' => 'Revenue Recognition date SSO',
                        'rrd_erp' => 'Revenue Recognition date ERP',
                    ],
                    'links' => ['id' => 'https://www.tld-gse.com/en/private/product_support/index.ps.php?m[0]=equipment&m[1]=view&id='],
                    'title' => "ER linked to the SOL#$id",
                    'sortable' => 'no',
                ]
            );
            $MSG .= $report->fetch();
        }
        // 3 - Unassign all ER
        foreach ($erList as $key => $val) {
            $er = new tldEquipment($val['id']);
            $erHeader = $er->itsDetails;
            $unit = new tldSORUnit($erHeader['sor_uid']);
            $unitHeader = $unit->itsHeader;
            // if GT and invoiced by factory to SSO, turn customer name into "**AVAILABLE FOR SALE**"
            if ($unitHeader['dgt_est'] !== '0000-00-00' && $erHeader['rrd_erp'] !== '0000-00-00') {
                $er->setAvailableForSale($unit->itsHeader['id']);
            }
            // unassign ER
            $er->setSOR_UID(0);
        }
        // 3 - Delete SOL
        $e = $sol->delete();
        // send email if evp approval reached
        if (is_EVPstatusPassed($header['status'])) {
            _logAfterEVP_APPROVAL(); // Log the deletion
        }

        global $kernel;

        $client = $kernel->getContainer()->get(Client::class);
        $sor = $client->findOneBy('sales/orders', ['legacyId' => $header['parent_id']]);

        try {
            $log = $client->post('comments', [
                'json' => [
                    'resource' => $sor['@id'],
                    'message' => "SOL#$id DELETED",
                ],
            ]);
        } catch (ClientException $e) {
            $errors = json_decode($e->getResponse()->getContent(), true);
            return 'ERROR: Could not add a log. Reason: ' . $errors['hydra:description'];
        }

        $body .= <<<EOF
<br/><br/>
<p>$note<a href="$php_self?m[0]=sor&m[1]=view&id={$header['parent_id']}">SOL#$id DELETED, click here to go to related SOR...</a></p>
<p>$note<a href="$php_self?m[0]=sol">Or click here to go to SOL homepage...</a></p>
EOF;
        break;
    case 'links':
        $DEFAULT_MENU .= <<<EOF
    <br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
    <a href="/en/private/common/index.php?m[0]=links&m[1]=form&m[2]=newLink&module=SOL&parent_id=$id">New Link</a>
EOF;
        $report = new tldReportColumnar(
            $sol->getLinksFromHere(),
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
            $sol->getLinksToHere(),
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
    case 'tasks':
        $DEFAULT_TITLE .= "\Tasks";
        $DEFAULT_MENU .= <<<EOF
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<a href="/en/private/calendar/calendar.php?m[0]=tasks&m[1]=form&m[2]=newTask&module=SOL&parent_id=$id">New Task</a>
EOF;

        $engTasks = $meaps = $eap = [];
        // Retrieve all MEAP linked to the SOL
        foreach ($sol->getLinksFromHere('MEAP') as $link) {
            $meaps[] = $link['item'];
        }
        foreach ($sol->getLinksToHere('MEAP') as $link) {
            $meaps[] = $link['parent_id'];
        }
        // get all tasks of MEAP and child EAP recursively
        foreach ($meaps as $module_id) {
            $meap = new tldMEAP($module_id);
            $engTasks = (array)$meap->getTasks('ALL');
            $family = $meap->getFamilyTree(true, true);
            foreach ($family as $mod) {
                if ($mod['module'] === 'EAP') {
                    $eap = new tldEAP($mod['id']);
                    $engTasks = array_merge($engTasks, $eap->getTasks('ALL'));
                }
            }
        }
        // Retrieve all EAP directly linked to the SOL
        foreach ($sol->getLinksFromHere('EAP') as $link) {
            $eaps[] = $link['item'];
        }
        foreach ($sol->getLinksToHere('EAP') as $link) {
            $eaps[] = $link['parent_id'];
        }
        // get all tasks of directly linked EAP
        foreach ($eaps as $module_id) {
            $eap = new tldEAP($module_id);
            $engTasks = array_merge($engTasks, $eap->getTasks('ALL'));
        }

        $sess['calendar']['tasks'] = (array) array_merge($sol->getTasks(), $engTasks);
        $form = new tldReportMultiLevel(
            $sol->getTasks(),
            ['status', 'due_date'],
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
                'menu_suffix' => '_sol',
            ]
        );
        $body .= $form->fetch();
        $form = new tldReportMultiLevel(
            $engTasks,
            ['status', 'due_date'],
            [
                'id' => 'Task#',
                'status' => 'Status',
                'due_date' => 'Due',
                'task' => 'Task',
                'assignee_fullname' => 'Assignee',
            ],
            [
                'passField' => 'id',
                'title' => 'Engineering Tasks',
                'url' => '/en/private/calendar/calendar.php?m[0]=tasks&m[1]=task&m[2]=view&id=',
                'menu_suffix' => '_eng',
            ]
        );
        $body .= $form->fetch();
        $erList = tldEquipment::bySORLine($id);
        foreach ($erList as $key => $val) {
            $er = new tldEquipment($val['id']);
            $body .= _getTaskList(array_merge((array)$er->getTasks(), (array)$er->getSeqs()), 'Tasks for ER# ' . $val['id']);
            if ($er->hasPreAssemblyER()) {
                foreach ($er->getCombinationErList('PRE-ASSEMBLY') as $erVal) {
                    $pas = new tldEquipment($erVal['id']);
                    $body .= _getTaskList(array_merge((array)$pas->getTasks($vars), (array)$pas->getSeqs($vars)), 'Tasks for PAS# ' . $pas->getSN());
                }
            }
        }

        break;
    case 'log':
        $DEFAULT_TITLE .= "\Log";

        $form = new HTML_QuickForm('frmNewLogComment', 'post');
        $form->addElement('hidden', 'm[0]', 'sol');
        $form->addElement('hidden', 'm[1]', 'view');
        $form->addElement('hidden', 'm[2]', 'log');
        $form->addElement('hidden', 'id', $id);
        $form->addElement('header', 'title', '<b>INTERNAL LOGS & Communication</b>');
        $form->addElement('textarea', 'comment', 'Comment',
            ['wrap' => 'VIRTUAL', 'cols' => '60', 'rows' => '5']);
        $form->addElement('submit', 'btnSubmit', 'Submit');

        if ($form->validate()) {
            $vars = tldUtils::cleanupFormInput($form->exportValues());
            try {
                $e = $sol->addLogEntry($user->getID(), $vars['comment']);
                $body .= '<b>Log comment added successfully!</b>';
                $logComment = $form->getElement('comment');
                $logComment->setValue('');
            } catch (Exception $e) {
                $DEFAULT_ERROR[] = "ERROR: Unable to add log comment to SOL#{$id}. Reason: {$e}";
                break;
            }
        }
        $body .= $form->toHTML();

        $log = $sol->getLog();
        $report = new tldReportColumnar(
            $log,
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
    case 'breakdown':
        include('view.breakdown.inc.php');
        break;
    case 'delivery':
        $report = new tldAssocTable(
            $header,
            [
                'inco' => 'Inco terms',
                'inco_loc' => 'Inco location',
                'ctry' => 'Country',
                'del_pen' => 'Late delivery penalties',
                'delpen_cond' => 'Late delivery conditions',
                'conf_sls' => 'Delivery Penalty Accepted by Sales Org?',
                'conf_erp' => 'Are these delivery penalties approved and backed-up by the TLD factory ?',
                'conf_dms' => 'If needed, has DMS#3228 been completed, signed and stored in the SFR ?',
                'conf_cis' => 'Customer inspection before shipment?',
                'trans' => 'Transportation responsibility',
            ],
            ['title' => 'Delivery']
        );
        $body .= $report->fetch();
        $report = new tldReportColumnar(
            tldSORUnit::byParent($id),
            [
                'xItems' => [
                    'short_desc' => 'Short Description',
                    'long_desc' => 'Long Description',
                    'del_dat' => 'Requested Delivery Date',
                    'ddel_est1' => 'Factory Promised Delivery Date',
                    'dgt_rev' => 'Estimated GT Date',
                    'batch_qty' => 'Batch qty',
                ],
                'title' => 'SOR Units',
            ]
        );
        $body .= $report->fetch();
        break;
    case 'payment':
        $DEFAULT_MENU .= <<<EOF
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<a href="$php_self?m[0]=sol&m[1]=view&m[2]=payment&m[3]=editRates&id=$id">Edit Rates</a>
EOF;

        // Check if allowed after EVP_APPROVAL and action on it
        if (!is_AllowedAfterEVP_APPROVAL() && !empty($m[3])) {
            $DEFAULT_ERROR[] = 'ERROR: You do not have permissions to make any modifications after EVP_APPROVAL status';
            break;
        }

        switch ($m[3]) {
            case 'editRates':
                $DEFAULT_TITLE .= '\Edit the exchange rate';
                if (!$user->isInGroup('role_CFO')) {
                    $DEFAULT_ERROR[] = 'You do not have permissions for this page..';
                    break;
                }
                $form = new HTML_QuickForm('frmEditRate', 'post');
                $form->addElement('hidden', 'm[0]', 'sol');
                $form->addElement('hidden', 'm[1]', 'view');
                $form->addElement('hidden', 'm[2]', 'payment');
                $form->addElement('hidden', 'm[3]', $m[3]);
                $form->addElement('hidden', 'id', $id);
                $form->addElement('header', 'title', 'Edit the rates');
                $mods = tldModList::byParent($id, 'SOL', ['list_name' => 'CURS']);
                $listIdsAvailable = [];
                foreach ($mods as $mod) {
                    $form->addElement('text', 'listIds[' . $mod['id'] . ']', $mod['list_key']);
                    $form->addRule('listIds[' . $mod['id'] . ']', 'Required', 'required');
                    $form->addRule('listIds[' . $mod['id'] . ']', 'Should be numeric', 'numeric');
                    $form->setDefaults(['listIds[' . $mod['id'] . ']' => $mod['value']]);
                    array_push($listIdsAvailable, $mod['id']);
                }
                $form->addElement('submit', 'btnSubmit', 'Update');

                if (!$form->validate()) {
                    $body .= $form->toHTML();
                    break;
                }

                $form->freeze();
                $vars = tldUtils::cleanupFormInput($form->exportValues());
                foreach ($vars['listIds'] as $listId => $var) {
                    if (in_array($listId, $listIdsAvailable)) {
                        $mod = new tldModList($listId);
                        $modHeader = $mod->itsHeader;
                        $error = $mod->update(
                            [
                                'list_name' => 'CURS',
                                'list_key' => $modHeader['list_key'],
                                'value' => $var,
                            ]
                        );
                        if (!is_string($error)) {
                            $sol->addLogEntry($user->getID(),
                                $modHeader['list_key'] . ' changed from ' . $modHeader['value'] . ' to ' . $var);
                        } else {
                            $DEFAULT_ERROR[] = "ERROR: Problem updating the rate, error returned from update was... $error";
                        }
                    } else {
                        $DEFAULT_ERROR[] = 'ERROR: You do not have permissions to change the form';
                    }
                }
                $body .= getPaymentTab($sol);
                break;
            default:
                $body .= getPaymentTab($sol);
                break;
        }
        break;
    case 'changeStatus':
        if ($sol->isClosed()) {
            $body .= getGeneralTab($header);
            $DEFAULT_ERROR[] = 'ERROR: SOL is already closed.';
            break;
        }
        // For special status change, else use default workflow
        switch ($m[3]) {
            case 'toCancel':
                // Check permissions
                if (!$user->isInGroup(['gg_ADMIN', 'role_SA', 'role_EVP'])) {
                    $DEFAULT_ERROR[] = 'ERROR: You do not have permissions to CANCEL this SOL';
                    break;
                }
                // Check revenue transaction
                $transRevList = tldSORTran::byConstraints("parent_id=$id AND ttyp='R'");
                if (count($transRevList)) {
                    $DEFAULT_ERROR[] = 'ERROR: Can not cancel automatically if there is revenues, please advise accounting to CANCEL this SOL';
                    break;
                }
                // Confirmation form
                $form = new HTML_QuickForm('frmChangeStatus', 'post');
                $form->addElement('header', 'title', 'CANCEL SOL - <span style="color:red;">Cancellation would have to be validated by the SSO Director and Factory COO of this SOL</span>');
                $form->addElement('hidden', 'm[0]', 'sol');
                $form->addElement('hidden', 'm[1]', 'view');
                $form->addElement('hidden', 'm[2]', 'changeStatus');
                $form->addElement('hidden', 'm[3]', 'toCancel');
                $form->addElement('hidden', 'id', $id);
                $form->addElement('select', 'confirm', 'Confirm you have followed TLD procedure', ['' => '', 'y' => 'Yes']);
                $form->addElement('submit', 'submit', 'Submit');
                $form->addRule('confirm', 'Required', 'required');

                if (!$form->validate()) {
                    $body = $form->toHTML();
                    break;
                }

                $rawData = $form->exportValues();
                // Handling transactions
                // get all Bookings
                $transBookingList = tldSORTran::byConstraints("parent_id=$id AND ttyp='B'");
                // create negative Booking
                foreach ($transBookingList as $transVal) {
                    $p = [
                        'nref' => null,
                        'dref' => null,
                        'dtran' => date('Y-m-d'),
                        'tgrp' => $transVal['tgrp'],
                        'ttyp' => $transVal['ttyp'],
                        'tcur' => $transVal['tcur'],
                        'tval' => $transVal['tval'] * -1,
                        'notes' => "Automated booking from SOL#$id cancellation",
                    ];
                    $e = tldSORTran::insert($id, $p);
                    if (is_string($e)) {
                        $DEFAULT_ERROR[] = "ERROR: Can not create negative booking for Trans#{$transVal['id']}. Reason: $e";
                    }
                }
                // Unassing ER
                $erList = tldSORUnit::byParent($id);
                foreach ($erList as $erVal) {
                    $er = new tldEquipment($erVal['erid']);
                    if ($er->isEmpty()) {
                        $DEFAULT_ERROR[] = "ERROR: ER {$er->getSN()} not found";
                        continue;
                    }
                    // unassign ERs from units
                    $e = $er->setSOR_UID(0);
                    if (is_string($e)) {
                        $DEFAULT_ERROR[] = "ERROR: ER {$er->getSN()} not unassigned. Reason: $e";
                    }
                    // reset info
                    $resetData = [
                        'customer_name' => '',
                        'customer_id' => '',
                        'buyer_customer_id' => '',
                        'sales_org' => '',
                        'sales_rep' => '',
                        'warranty_conditions' => '',
                        'rrd_sso' => '',
                        'rrd_erp' => '',
                        'tranid_sso' => '',
                        'tranid_erp' => '',
                    ];
                    $e = $er->updateRecord($resetData, array_keys($resetData));
                    if (is_string($e)) {
                        $DEFAULT_ERROR[] = "ERROR: ER {$er->getSN()} info not reseted. Reason: $e";
                    } else {
                        // Log
                        $er->addLogEntry($user->getID(), "Unassigned from SOL#$id cancellation");
                    }
                }
                // SOL status to CLOSED
                $e = $sol->changeStatus('CANCEL', $user->getID());
                if (is_string($e)) {
                    $DEFAULT_ERROR[] = "ERROR: SOL status not changed. Reason: $e";
                    break;
                }
                $e = $sol->addLogEntry($user->getID(), 'CLOSED - SOL CANCELLED');
                $body = "SOL#$id have been successfully CANCELLED (CLOSED)";
                break;
            case 'toClose':
                // Check permissions
                if (!$user->isInGroup(['gg_ADMIN', 'role_SA', 'role_EVP'])) {
                    $DEFAULT_ERROR[] = 'ERROR: You do not have permissions to CLOSE this SOL here';
                    break;
                }

                // Check the transportation responsibility has been filled
                if (empty($sol->itsHeader['trans'])) {
                    $DEFAULT_ERROR[] = 'ERROR: Transportation responsibility must be filled in the SOL before to CLOSE';
                    break;
                }
                // to CLOSE a SOL we must check that the backlog (in the currency of the SOL) is equal to 0;
                $transTotal = $sol->getTranSummary();
                if ($transTotal['ssok_tot'] != 0 || $transTotal['erpk_tot'] != 0) {
                    $DEFAULT_ERROR[] = 'ERROR: Backlog SSO or Factory not null, need to FINALIZE the SOL before to CLOSE';
                    break;
                }

                // Then get the form
                $form = new HTML_QuickForm('frmChangeStatus', 'post');
                $form->addElement('header', 'title', 'Change status to -> CLOSED');
                $form->addElement('hidden', 'm[0]', 'sol');
                $form->addElement('hidden', 'm[1]', 'view');
                $form->addElement('hidden', 'm[2]', 'changeStatus');
                $form->addElement('hidden', 'm[3]', 'toClose');
                $form->addElement('hidden', 'id', $id);
                $form->addElement('textarea', 'comment', 'Reason for close', ['rows' => 10, 'cols' => 40]);
                $form->addElement('submit', 'submit', 'Submit');
                $form->addRule('comment', 'Required', 'required');

                if (!$form->validate()) {
                    $body = $form->toHTML();
                    break;
                }

                $a = $form->exportValues();
                $parsedComment = str_replace(["\r\n", "\r", "\n"], '<br/>', $a['comment']);
                $a = tldUtils::cleanupFormInput($a);
                $e = $sol->changeStatus('CLOSED', $user->getID(), ['msg' => $parsedComment]);
                if (is_string($e)) {
                    $DEFAULT_ERROR[] = 'INTERNAL ERROR: SOL status not changed';
                    $DEFAULT_ERROR[] = 'Reason: CLOSING rules for cancellation or Transportation are not respected';
                    break;
                }
                $e = $sol->addLogEntry($user->getID(), "CLOSED<br>{$a['comment']}");
                $body = "SOL#$id have been successfully CLOSED";
                break;
            case 'toEngReview':
                // Check permissions
                if (!$user->isInGroup(['gg_ADMIN', 'role_PSM', 'role_PSE', 'role_PSA', 'role_COO', 'role_CMO'])) {
                    $DEFAULT_ERROR[] = 'ERROR: You do not have permissions to change the status';
                    break;
                }
                // Check status
                if (!is_EngRevPassed($sol->getStatus())) {
                    $DEFAULT_ERROR[] = 'ERROR: You do not have permissions to change the status';
                    break;
                }
                $form = new HTML_QuickForm('frmChangeStatus', 'post');
                $form->addElement('header', 'title', 'Change status to -> ENGINEER_REVIEW');
                $form->addElement('hidden', 'm[0]', 'sol');
                $form->addElement('hidden', 'm[1]', 'view');
                $form->addElement('hidden', 'm[2]', 'changeStatus');
                $form->addElement('hidden', 'm[3]', 'toEngReview');
                $form->addElement('hidden', 'id', $id);
                $form->addElement('textarea', 'comment', 'Comment', ['rows' => 10, 'cols' => 40]);
                $form->addElement('submit', 'submit', 'Submit');

                if (!$form->validate()) {
                    $body = $form->toHTML();
                    break;
                }

                $a = $form->exportValues();
                $parsedComment = str_replace(["\r\n", "\r", "\n"], '<br/>', $a['comment']);
                $a = tldUtils::cleanupFormInput($a);
                $error = $sol->changeStatus(
                    'ENGINEER_REVIEW',
                    $user->getID(),
                    ['msg' => $parsedComment]
                );
                if (is_string($error)) {
                    $DEFAULT_ERROR[] = "ERROR: there was a problem changing status, returned error was... $error<br>";
                    break;
                }
                $body .= "SOL#$id status successfully changed to ENGINEER_REVIEW";
                // Add log
                $m = 'ENGINEER_REVIEW';
                if ($a['comment']) {
                    $m .= "\n" . $a['comment'];
                }
                $e = $sol->addLogEntry($user->getID(), $m);
                // Refresh data and display general
                $sol->refresh();
                $header = $sol->itsHeader;
                $body .= getGeneralTab($header);
                break;
            default:
                $allowed = $sol->getStatusAllowed($user->getID());
                if (is_array($allowed)) {
                    include('./sol/changeStatus.inc.php');
                } else {
                    $DEFAULT_ERROR[] = "ERROR: Can not change SOL status. Returned error was $allowed";
                }
                break;
        }
        break;
    case 'ship':
        $form = new HTML_QuickForm('frmShip', 'post');
        $form->addElement('header', 'title', 'Please enter shipping information...');
        $form->addElement('hidden', 'm[0]', 'sol');
        $form->addElement('hidden', 'm[1]', 'view');
        $form->addElement('hidden', 'm[2]', 'ship');
        $form->addElement('hidden', 'id', $id);
        $form->addElement('date', 'dt_ship', 'Ship Date and Time (YMD hm)', ['format' => 'Y-m-d H-i']);
        $form->addElement('textarea', 'ship_addr', 'Ship_to', ['rows' => 10, 'cols' => 40]);
        $form->addElement('textarea', 'final_addr', 'Ultimate Destination', ['rows' => 10, 'cols' => 40]);
        $form->addElement('text', 'carrier', 'Carrier Name');
        $form->addElement('select', 'modality', 'Modality', ['OCEAN' => 'OCEAN', 'LAND' => 'LAND']);
        $form->addElement('text', 'tld_bln', 'TLD Bill of LadingNumber');
        $form->addElement('text', 'ship_bln', "Shipper's Bill of Lading Number");
        $form->addElement('textarea', 'notes', 'Notes', ['rows' => 10, 'cols' => 40]);
        $form->addElement('textarea', 'forwarder', 'Contact/Forwarder', ['rows' => 5, 'cols' => 40]);
        $form->addElement('text', 'trailer_no', 'Trailer Number');
        $form->addElement('text', 'trailer_sn', 'Serial Numbers');
        $form->addElement('text', 't_in', 'Time In');
        $form->addElement('text', 't_out', 'Time Out');
        $form->addElement('text', 'pro_no', 'Pro Number');
        $form->addElement('header', 'title', '... then select Equipment for shipping');
        $rows = tldEquipment::bySORLine($id);
        if (count($rows) == 0) {
            $DEFAULT_ERROR[] = 'ERROR: no equipment available for shipping';
            break;
        }
        foreach ($rows as $row) {
            if (empty($row['esrid'])) {
                $lineText = $row['sn'] . ', ' . $row['customer_name'] . ', ' . $row['model'] . ', ' . $row['airport_code'];
                $form->addElement('select', "ers[${row['id']}]", $lineText,
                    ['' => '', 'SHIP' => 'SHIP']
                );
                if ($ers[$row['id']] == 'SHIP') {
                    $toShip[$row['id']] = $lineText;
                }
            }
        }
        $form->addElement('submit', 'btnSubmit', 'Submit');
        $form->setDefaults([
                'dt_ship' => [
                    'Y' => date('Y'),
                    'm' => date('m'),
                    'd' => date('d'),
                    'H' => date('H'),
                    'i' => date('i')],
            ]
        );

        if ($form->validate()) {
            # If the form validates then freeze the data
            if (count($toShip) == 0) {
                $DEFAULT_ERROR[] = 'ERROR: no equipment selected for shipping. Pls select at least one unit for shipping.';
                $body .= $form->toHTML();
                break;
            }
            $form->freeze();
            $header = tldUtils::cleanupFormInput($form->exportValues());
            $header['sorl_id'] = $id;
            $header['ship_auth'] = '1';
            $esrid = tldESR::insert($header);
            if (is_string($esrid)) {
                $DEFAULT_ERROR[] = "ERROR: Problem creating ESR. Error returned was ($esrid)";
                $body .= $form->toHTML();
                break;
            }
            foreach ($toShip as $erid => $row) {
                $er = new tldEquipment($erid, true);
                $error = $er->setESRID($esrid);
                if (is_string($error)) {
                    $DEFAULT_ERROR[] = "ERROR: there was a problem assigning ER to SOR line. Error returned was ($error)";
                }
            }
            $body .= <<<EOF
        <a href="$php_self?m[0]=esr&m[1]=view&id=$esrid">ESR#$esrid has now been created, click here to view</a>
EOF;
        } else {
            $body .= $form->toHTML();
        }
        break;
    case 'er':
        include('./sol/er.inc.php');
        break;
    case 'esr':
        $report = new tldReportColumnar(
            tldESR::bySORL($id),
            [
                'xItems' => [
                    'id' => 'ESR#',
                    'sorl_id' => 'SOR Line ID#',
                    'status' => 'Status',
                    'd_ship' => 'Date Shipped',
                    'tld_bln' => 'TLD Bill of Lading Number',
                    'ship_bln' => 'Shipper Bill of Lading Number',
                ],
                'title' => 'Equipment Shipping Records',
                'links' => ['id' => "$php_self?m[0]=esr&m[1]=view&id="],
            ]
        );
        $body .= $report->fetch();
        break;
    case 'tran':
        include_once('tran.inc.php');
        break;
    case 'files':
        $DEFAULT_TITLE .= "\Files";
        $DEFAULT_MENU .= <<<EOF
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<a href="$php_self?m[0]=sol&m[1]=view&m[2]=files&m[3]=TLDFile&id=$id">Add Files</a>
EOF;
        switch ($m[3]) {
            case 'TLDFile':
                $DEFAULT_TITLE .= "\Add a TLD File";
                $form = new HTML_QuickForm('frmAddTLDFile');
                $form->addElement('header', 'title', 'Add Files');
                $form->addElement('hidden', 'm[0]', 'sol');
                $form->addElement('hidden', 'm[1]', 'view');
                $form->addElement('hidden', 'm[2]', 'files');
                $form->addElement('hidden', 'm[3]', 'TLDFile');
                $form->addElement('hidden', 'id', $id);
                for ($i = 1; $i < 6; $i++) {
                    $form->addElement('textarea', "description[$i]", 'File Description', ['wrap' => 'VIRTUAL', 'cols' => '60', 'rows' => '4']);
                    $form->addElement('file', "file[$i]", 'File');
                }
                $form->setDefaults(['date' => date('Y-m-d')]);
                $form->addElement('submit', 'btnSubmit', 'Submit');
                if (!$form->validate()) {
                    $body = $form->toHTML();
                    break 2;
                }
                $vars = tldUtils::cleanupFormInput($form->exportValues());
                //File Management
                for ($i = 1; $i < 6; $i++) {
                    $file = $form->getElement("file[$i]");
                    $file_array = $file->getValue();
                    if ($file_array['tmp_name'] != '') {
                        $vars['filename'] = $file_array['name'];
                        $e = tldModFile::insert(
                            [
                                'module' => 'SOL',
                                'parent_id' => $id,
                                'description' => $vars['description'][$i],
                                'filename' => $vars['filename'],
                                'poster' => $user->getID(),
                            ],
                            $file_array
                        );

                        if (is_string($e)) {
                            $DEFAULT_ERROR[] = "ERROR: Problem adding File in the database...<br/> $e";
                            break;
                        }
                        $body .= "File#$e uploaded successfully!";
                    }
                }
                break;
        }
        $report = new tldReportColumnar(
            array_filter($sol->getFiles(), function (array $file) {
                return !isset($file['description']) || (isset($file['description']) && !preg_match('/SOL#\d{5} Acknowledgment - [\d-]{10} [\d:]{8}$/', $file['description']));
            }),
            [
                'xItems' => [
                    'id' => 'File ID',
                    'date' => 'Date',
                    'description' => 'Description',
                    'filename' => 'Filename',
                    'poster' => 'Poster'],
                'links' => ['id' => '/en/private/common/index.php?m[0]=files&m[1]=view&id='],
            ]
        );
        $body .= $report->fetch();
        break;
    case 'summary':
        $DEFAULT_TITLE .= "\Summary";
        // Profiles permissions
        $FACTORY_GRP = [
            'role_planner',
            'GG_ENG',
        ];
        $SSO_GRP = [
            'gg_ADMIN',
            'role_ASM',
            'role_SA',
            'gg_ACCT',
            'role_EVP',
            'role_PSM',
            'role_PSE',
            'role_PSA',
            'role_MLM',
            'role_EM',
            'role_QAM',
            'role_PM',
            'role_FC',
            'role_COO',
            'role_CMO',
        ];
        // Check permissions
        if (!$user->isInGroup(array_merge($FACTORY_GRP, $SSO_GRP))) {
            $DEFAULT_ERROR[] = 'ERROR: You do not have permissions to access this page';
            break;
        }
        $DEFAULT_MENU .= <<<EOF
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<a href="$php_self?m[0]=sol&m[1]=view&m[2]=summary&id=$id">Default Currency</a>&nbsp;|&nbsp;
<a href="$php_self?m[0]=sol&m[1]=view&m[2]=summary&m[3]=USD&id=$id">USD</a>&nbsp;|&nbsp;
<a href="$php_self?m[0]=sol&m[1]=view&m[2]=summary&m[3]=EUR&id=$id">EUR</a>
EOF;


        $dcur = isset($m[3]) ? $m[3] : $sol->getDCUR();
        // Identify profile
        $PROFILE = 'factory';
        if ($user->isInGroup($SSO_GRP)) {
            $PROFILE = 'sso';
        }
        $body .= getSummaryTab($sol, $smarty, $PATH, $dcur, $PROFILE);
        break;
    case 'options':
        $DEFAULT_TITLE .= "\Options";
        $intOptions = $extOptions = [];
        $intCaty = tldSOL::getInternalCategoriesList();
        $intOptions = tldSOROpts::byParent($id, ['include' => $intCaty, 'description_only' => true]);
        $report = new tldReportColumnar(
            $intOptions,
            [
                'xItems' => [
                    'caty' => 'Category',
                    'dsca' => 'Description',
                ],
                'title' => 'Internal options',
                'showItemNumbers' => true,
            ]
        );
        $body .= $report->fetch();
        $extCaty = tldSOL::getExternalCategoriesList();;
        $extOptions = tldSOROpts::byParent($id, ['include' => $extCaty, 'description_only' => true]);
        $report = new tldReportColumnar(
            $extOptions,
            [
                'xItems' => [
                    'caty' => 'Category',
                    'dsca' => 'Description',
                ],
                'title' => 'External options',
                'showItemNumbers' => true,
            ]
        );
        $body .= $report->fetch();
        break;
    case 'map':
        include('./sol/map.inc.php');
        break;
    case 'adminStatus':
        if (!($user->isInGroup(['gg_ADMIN', 'superuser'])
            || ($user->isInGroupLevel('role_SAM', $header['sso_erp']) && in_array($header['status'], tldSol::getSSOStatusList(), true))
            || ($user->isInGroupLevel('role_PSM', $header['bu_erp']) && in_array($header['status'], tldSol::getFactoryStatusList(), true)))
        ) {
            $DEFAULT_ERROR[] = "ERROR: You are not allowed to use this feature.";
            break;
        }

        $statusList = [];
        if ($user->isInGroup(['gg_ADMIN', 'superuser'])) {
            $statusList = tldSol::getStatusList();
        } elseif ($user->isInGroupLevel('role_SAM', $header['sso_erp'])) {
            $statusList = tldSOL::getSSOStatusList();
        } elseif ($user->isInGroupLevel('role_PSM', $header['bu_erp'])) {
            $statusList = tldSOL::getFactoryStatusList();
        }

        $DEFAULT_TITLE .= "\Admin status";
        $form = new HTML_QuickForm('frmAddTLDFile');
        $form->addElement('header', 'title', 'Admin status');
        $form->addElement('hidden', 'm[0]', 'sol');
        $form->addElement('hidden', 'm[1]', 'view');
        $form->addElement('hidden', 'm[2]', 'adminStatus');
        $form->addElement('hidden', 'id', $id);
        $form->addElement('select', 'status', 'Status', array_combine($statusList, $statusList));
        $form->addElement('textarea', 'comment', 'comment', ['wrap' => 'VIRTUAL', 'cols' => '60', 'rows' => '4']);
        $form->addElement('checkbox', 'confirmation', '<span style="color:red">' . _("I confirm I didn't forget a rule on the SOL status") . '</span>', null, ['id' => 'color: red']);
        $form->addRule('status',_('Field is required'),'required');
        $form->addRule('comment',_('Field is required'),'required');
        $form->addRule('confirmation',_('Field is required'),'required');
        $form->addElement('submit', 'btnSubmit', 'Submit');

        if (!$form->validate()) {
            $body = $form->toHTML();
            break;
        }

        $vars = $form->exportValues();
        $sol->changeStatusAdmin($vars['status']);
        $sol->addLogEntry($user->getId(), $vars['comment']);
        $body = "SOL#$id status have been successfully updated";
        break;
    default:
        $msg = null;
        if (!empty($_POST) && isset($_POST['engineering_flag']) && !$user->isInGroup(['role_ENG', 'role_EM'])) {
            $DEFAULT_ERROR[] = 'You are not allowed to modify this value.';
            break;
        } elseif (!empty($_POST) && isset($_POST['engineering_flag']) && $sol->hasEngineeringFlag() !== (bool)$_POST['engineering_flag']) {
            $sol->updateHeader(['engineering_flag' => TldDatabase::escape($_POST['engineering_flag'])]);
            $sol->refresh();
            $msg = "SOL updated:<br><ul><li>Engineering flag updated to '{$_POST['engineering_flag']}'</li></ul>";
        }

        if (null !== $msg) {
            $e = $sol->addLogEntry($user->getID(), TldDatabase::escape($msg));
            if (is_string($e)) {
                $DEFAULT_ERROR[] = "ERROR: Problem adding logs...<br/>Reason: $e";
                break;
            }
            # Avoid resubmitting the form when refreshing the page after form submission
            header("Location: $php_self?m[0]=sol&m[1]=view&m[2]=lines&id=$id");
            exit;
        }

        $color = $sol->hasEngineeringFlag() ? '#6cc071' : '#ff5252';
        $content = $sol->hasEngineeringFlag() ? 'YES' : 'NO';
        $value = sprintf('%b', !$sol->hasEngineeringFlag());
        $confirmMessage = 'Do you want to ' . ($value ? '' : 're') . 'set engineering Flag ?';
        if ($user->isInGroup(['role_ENG', 'role_EM'])) {
            $form = <<<EOF
    <br/>
    <table border="0" cellspadding="2" cellspacing="4" cellpadding="10">
        <tr>
            <td align="center" bgcolor="$color" width="80px">
                <form method="post" action="$php_self?m[0]=sol&m[1]=view&m[2]=lines&id=$id">
                    <input type="hidden" name="engineering_flag" value="$value">
                    <button type="submit" name="submit_param" style="background:none;border:none;padding:0;cursor:pointer;outline:none;" onmouseover='this.style.textDecoration="underline"' onmouseout='this.style.textDecoration="none"' onclick="return confirm('$confirmMessage');">
                        <b>Engineering Flag :</b><br>$content
                    </button>
                </form>
            </td>
        </tr>
    </table>
EOF;
        } else {
            $form = <<<EOF
    <br/>
    <table border="0" cellspadding="2" cellspacing="4" cellpadding="10">
        <tr>
            <td align="center" bgcolor="$color" width="80px">
                    <button type="button" style="background:none;border:none;padding:0;cursor:default;outline:none;" onclick="return false;">
                        <b>Engineering Flag :</b><br>$content
                    </button>
            </td>
        </tr>
    </table>
EOF;
        }

        $body .= getGeneralTab($header, $form);
        break;
}

function _getTaskList($rows, $title = 'ER Tasks')
{
    $form = new tldReportMultiLevel(
        $rows,
        ['status', 'due_date'],
        [
            'id' => 'Task#',
            'status' => 'Status',
            'due_date' => 'Due',
            'task' => 'Task',
            'assignee_fullname' => 'Assignee',
        ],
        [
            'passField' => 'id',
            'title' => $title,
            'url' => '/en/private/calendar/calendar.php?m[0]=tasks&m[1]=task&m[2]=view&id=',
        ]
    );
    return $form->fetch();
}

// Function to Check if EVP_APPROVAL have been reached
function is_EVPstatusPassed($status)
{
    return !in_array($status, ['PENDING', 'CREATE_PO']);
}

// Function to Check if PRINT_SO_ACK have been passed
function is_PrintSOAckPassed($status)
{
    return !in_array($status, ['PENDING', 'CREATE_PO', 'EVP_APPROVAL', 'PRINT_SO_ACK']);
}

// Function to Check if ENGINEER_REVIEW have been reached
function is_EngRevPassed($status)
{
    return in_array($status, ['ENGINEER_REVIEW', 'ENGINEERING_APPROVAL',
            'MATERIALS_PLANNING', 'PSM_APPROVAL', 'IN_PROGRESS', 'SHIPPED']
    );
}

/**
 * Generic method to check permissions after EVP approval
 * @return boolean
 */
function is_AllowedAfterEVP_APPROVAL()
{
    global $sol, $user;
    if ($user->isInGroup(['role_CFO', 'role_FC', 'role_SA', 'role_EVP', 'gg_ADMIN'])
        || $user->getID() == $sol->itsHeader['asmID']
        || !is_EVPstatusPassed($sol->getStatus())) {
        return true;
    }
    return false;
}

/**
 * Generic method to log after EVP approval
 * @param string $msg
 */
function _logAfterEVP_APPROVAL($msg = '')
{
    global $sol, $id, $user, $m;
    // Prepare txt log
    $what = ucfirst($m[2]);
    if (!empty($m[3])) {
        $what .= '/' . ucfirst($m[3]);
    }
    $log = "SOL UPDATED after EVP_APPROVAL status ($what)";
    if (!empty($msg)) {
        $log .= "<br>$msg";
    }
    // Log the modification
    return $sol->addLogEntry($user->getID(), $log);
}

/**
 * Generic method to notify after EVP approval
 * @param string $subject
 * @param string $msg
 */
function _notifyAfterEVP_APPROVAL($subject = '', $msg = '')
{
    global $sol, $id, $user, $m;
    // Get bu info
    $sor = new tldSOR($sol->itsHeader['parent_id']);
    $bu = [
        'sso' => $sol->getSSOERP(),
        'erp' => $sol->getERP(),
    ];
    // Prepare email
    $what = ucfirst($m[2]);
    if (!empty($m[3])) {
        $what .= '/' . ucfirst($m[3]);
    }

    $SUBJECT = $subject;
    if (empty($subject)) {
        $SUBJECT = "SOL#$id updated after EVP_APPROVAL by " . $user->getFullname();
    }
    $BODY = "SOL#$id updated after EVP_APPROVAL ($what)<br>";
    if (!empty($msg)) {
        $BODY .= $msg;
    }

    // Send email
    return tldGroup::emailMultipleGroups(
        [
            $bu['sso'] => ['role_EVP', 'role_SA'],
            $bu['erp'] => ['role_PSM', 'role_PSE', 'role_PSA'],
        ],
        'noreply@tld-gse.com',
        $SUBJECT,
        $BODY . "<br><br><a href=\"https://www.tld-gse.com/en/private/sales_service/sales.php?m[0]=sol&m[1]=view&id=$id\">
		Click here to see SOL#$id</a>",
        ['charset' => 'UTF-8']
    );
}

/**
 * Generic Method to log and notify in details changes made after EVP_APPROVAL
 * @param array $fieldDefinition
 * @param array $fieldToCheck
 * @param array $dataFrom
 * @param array $dataTo
 * @param array $option
 */
function _updateProcessAfterEVP_APPROVAL($fieldDefinition, $fieldToCheck, $dataFrom, $dataTo, $option = [])
{
    global $user;
    // Check which field is updated
    $updatedFields = [];
    foreach ($fieldToCheck as $field) {
        if (!isset($dataTo[$field]) || $dataFrom[$field] == $dataTo[$field]) {
            continue;
        }
        $updatedFields[] = $field;
    }
    // Prepare msg and log
    $logMsg = null;
    if (!empty($option['msg'])) {
        $logMsg = "\r\n" . $option['msg'];
    }
    foreach ($updatedFields as $field) {
        $from = TldDatabase::escape($dataFrom[$field]);
        $to = TldDatabase::escape($dataTo[$field]);
        $logMsg .= "\r\nField {$fieldDefinition[$field]} changed from \'$from\' to \'$to\'";
    }
    _logAfterEVP_APPROVAL($logMsg);
    // Prepare email notification and notify
    $emailMsg = '';
    if (!empty($option['msg'])) {
        $emailMsg .= '<br/>' . mb_convert_encoding($option['msg'], 'UTF-8', mb_list_encodings());
    }
    $emailMsg .= '<ul>';
    foreach ($updatedFields as $field) {
        $from = nl2br(mb_convert_encoding($dataFrom[$field], 'UTF-8', mb_list_encodings()));
        $to = nl2br(mb_convert_encoding($dataTo[$field], 'UTF-8', mb_list_encodings()));
        $emailMsg .= "<li>Field <b>{$fieldDefinition[$field]}</b> changed <b>from</b> '$from' <b>to</b> '$to'</li>";
    }
    $emailMsg .= '</ul>';
    if ($user->isInGroup(['role_ASM', 'role_EVP'])) {
        _notifyAfterEVP_APPROVAL(null, $emailMsg);
    }
    return;
}
