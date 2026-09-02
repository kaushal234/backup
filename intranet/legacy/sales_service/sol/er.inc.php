<?php

use ApiBundle\Client;

include_once 'user.inc.php';

$DEFAULT_TITLE .= "\Units";

$router = $kernel->getContainer()->get('router');
$odpRoute = $router->generate('on_time_delivery_planning_show', ['orderLineNumber' => $sol->itsID]);
$odpEditRoute = $router->generate('on_time_delivery_planning_edit', ['orderLineNumber' => $sol->itsID]);

$DEFAULT_MENU .= <<<EOF
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<a href="$php_self?m[0]=sol&m[1]=view&m[2]=er&id=$id">Home</a>&nbsp;|&nbsp;
<a href="$php_self?m[0]=sol&m[1]=view&m[2]=er&m[3]=editUnits&m[4]=add&id=$id" title="Add unit">Add Unit</a>&nbsp;|&nbsp;
<a href="$php_self?m[0]=sol&m[1]=view&m[2]=er&m[3]=editUnits&m[4]=form&id=$id" title="Edit units, delivery dates and batch qty">Units Quick edit</a>&nbsp;|&nbsp;
<a href="$php_self?m[0]=sol&m[1]=view&m[2]=er&m[3]=createESR&&id=$id" title="Create ESR/ESRL">Create ESR</a>&nbsp;|&nbsp;
<a href="$odpRoute" title="Go to ODP View">ODP View</a> |
<a href="$odpEditRoute" title="Go to ODP Edit">Schedule PDI with ODP</a>
EOF;
//https://www.tld-gse.com/en/private/product_support/index.ps.php?m[0]=odp&m[1]=listing&m[2]=byErpSSO&x=TLD+MEAI&y=TLD+STL
// let the possibility to create ER if all units not assigned
if ($header['qty_alloc'] < $header['qty_sou']) {
    $DEFAULT_MENU .= <<<EOF
&nbsp;|&nbsp;<a href="$php_self?m[0]=sol&m[1]=view&m[2]=er&m[3]=create&id=$id">Create ERs</a>
EOF;
}

// Create flag to know if we are in the case of trailers and dollies to handle the batch qty differently
$datasheet = tldDatasheet::byModel($header['model']);
if ($datasheet['parent_id'] == 15) {
    $fl_trailersDollies = true;
}

switch ($m[3]) {
    case 'createESR':
        // Check permissions
        if (!$user->isInGroup(['gg_ADMIN', 'role_SA', 'gg_TRANSPORT'])) {
            $DEFAULT_ERROR[] = 'ERROR: You do not have permission to access this page';
            break;
        }
        // Include jquery
        $JS_INCLUDE[] = '/include/jquery/jquery.js';
        // Form
        $ER = tldSORUnit::byParent($id);

        if (empty($ER)) {
            $DEFAULT_ERROR[] = "INTERNAL ERROR: Cannot create ESR/ESRL. Reason: no Equipment Record serial number was found for SOL#$id, or all ER are already in an ESR not CLOSED";
        } else {
            $client = $kernel->getContainer()->get(Client::class);

            try {
                $serialNumbers = array_values(array_unique(array_filter(array_column($ER, 'sn'))));

                $equipmentRecordIds = [];
                foreach ($serialNumbers as $serialNumber) {
                    $equipmentRecord = $client->findOneBy('equipment_records', ['serialNumber' => $serialNumber, 'normalization_groups_override' => ['equipment_record_esr_check']]);
                    // only equipment record with no esr or with esr closed can be assigned in ESR from a sol
                    if (($equipmentRecord['availableForEquipmentShippingRecord'] ?? false) === true) {
                        $equipmentRecordIds[] = $equipmentRecord['@id'];
                    }
                }
                if ([] === $equipmentRecordIds) {
                    $DEFAULT_ERROR[] = "INTERNAL ERROR: Cannot create ESR/ESRL. Reason: no eligible Equipment Record was found for SOL#$id. All ER may already be linked to an Equipment Shipping Record that is not CLOSED.";
                    break;
                }

                $customerIds = [];

                if (!empty($sol->itsHeader['user_customer_id'])) {
                    $customer = $client->findOneBy('sales/customers', ['legacyId' => (int) $sol->itsHeader['user_customer_id']]);
                    $customerIds[] = $customer['@id'];
                }

                if (!empty($sol->itsHeader['buyer_customer_id'])) {
                    $buyer = $client->findOneBy('sales/customers', ['legacyId' => (int) $sol->itsHeader['buyer_customer_id']]);

                    if (!in_array($buyer['@id'], $customerIds, true)) {
                        $customerIds[] = $buyer['@id'];
                    }
                }

                $apiSso = $client->findOneBy('locations', ['name' => $sol->itsHeader['sso_fullname']])['@id'] ?? null;
                $apiIncoterm = $client->findOneBy('sales/incoterms', ['code' => $sol->itsHeader['inco']])['@id'] ?? null;

                if (null === $apiSso) {
                    $DEFAULT_ERROR[] = "INTERNAL ERROR: Cannot create ESR/ESRL. Reason: no SSO could be resolved for SOL#$id.";
                    break;
                }

                $nextUrl = $router->generate('equipment_shipping_record_add_from_sol', [
                    'customerIds' => $customerIds,
                    'sso' => $apiSso,
                    'equipmentRecordIds' => $equipmentRecordIds,
                    'incoterm' => $apiIncoterm,
                    'shipAuthorization' => (string) $sol->itsHeader['conf_cxo'],
                ]);

                header('Location: ' . $nextUrl);
                exit;
            } catch (Exception $e) {
                $DEFAULT_ERROR[] = "INTERNAL ERROR: Cannot create ESR/ESRL. Reason: " . $e->getMessage();
            }
        }
        break;
    case 'editUnits':
        // Check permissions
        if (!$user->isInGroup(['gg_ADMIN', 'role_PSM', 'role_PSE', 'role_PSA', 'role_SA', 'role_EVP', 'role_ASM'])) {
            $DEFAULT_ERROR[] = 'ERROR: You do not have permission to access this page';
            break;
        }
        // Check if allowed after EVP_APPROVAL
        if (!is_AllowedAfterEVP_APPROVAL() && !$user->isInGroup(['gg_ADMIN', 'role_PSM', 'role_PSE', 'role_PSA'])) {
            $DEFAULT_ERROR[] = 'ERROR: You do not have permissions to make any modifications after EVP_APPROVAL status';
            break;
        }
        switch ($m[4]) {
            case 'form':
                $sor = $sor ?: new tldSOR($sol->getParent());
                if ($fl_trailersDollies) {
                    $smarty->assign('fl_trailersDollies', 'y');
                }
                // Create quick edit form regarding groups
                if ($user->isInGroup(['gg_ADMIN', 'role_PSM', 'role_PSE', 'role_PSA'])) {
                    $smarty->assign('factory', 'y');
                }
                if ($user->isInGroup(['role_SA', 'role_EVP']) && in_array($header['status'], ['PENDING', 'CREATE_PO', 'EVP_APPROVAL', 'PRINT_SO_ACK', 'CREATE_SO_ACK', 'PRINT_PO'])) {
                    $smarty->assign('factory', 'y');
                }
                if ($user->isInGroup(['gg_ADMIN', 'role_SA', 'role_ASM', 'role_EVP'])) {
                    $smarty->assign('sale', 'y');
                    $smarty->assign('unit', 'y');
                    $smarty->assign('early', 'y');
                }
                // In all cases, can update final destination
                $ssoList = ['' => ''] + tldLocation::getSalesOrgList('smartyOptionsIDLocation');
                $smarty->assign('apcList', ['' => ''] + tldAirport::getList());

                // get list of ER
                $ER = tldSORUnit::byParent($id);
                $smarty->assign('nextURL', "$php_self?m[0]=sol&m[1]=view&m[2]=er&m[3]=editUnits&m[4]=update&id=$id");
                $smarty->assign('er', $ER);
                $smarty->assign('id', $id);
                $smarty->assign('sor', $sor->itsHeader);

                $js = <<<'HTML'
<script type="application/javascript">
	document.addEventListener("DOMContentLoaded", function () {
	    var airports = document.querySelector('#airports').innerHTML;
	    document.querySelectorAll('select[name$="[airport_code]"').forEach(function(el) {
	      el.insertAdjacentHTML('beforeend', airports)
	    })
	    document.querySelectorAll('select[name=airport_code').forEach(function(el) {
	      el.insertAdjacentHTML('beforeend', airports)
	    })
       
		var inputs = document.querySelectorAll('input[name$="[sleep_com]"');

		var disableSleepingCommissionSSOSelect = function (input) {
		  var select = input.parentNode.querySelector('select');
		  select.disabled = false;
		  if (!!!parseFloat(input.value)) {
		    select.value = '';
		    select.disabled = true;
		  }
		}

		inputs.forEach(function (input) {
		  disableSleepingCommissionSSOSelect(input);
		  
		  input.addEventListener('change', function (e) {
		  	  disableSleepingCommissionSSOSelect(e.target);
		  })
		})

		// Handle the copy buttons
		document.querySelectorAll('.js-button-selector').forEach(function(button) {
            button.addEventListener('click', function (e) {
                e.preventDefault();
                var input = e.target.parentNode.querySelector('.js-form-selector')
                document.querySelectorAll('[name$="['+input.name+']"').forEach(function(el) {
                    el.value = input.value
                })
            })
        })
	});

</script>
HTML;
                $smarty->assign('html_head', $js);
                $body .= $smarty->fetch("$PATH/sol/er.form.tpl");
                break;
            case 'update':
                if (empty($_POST['units']) || !is_array($_POST['units'])) {
                    $DEFAULT_ERROR[] = 'ERROR: No units date set or data invalid !';
                    break;
                }
                $LOGS = [];
                // Update process
                foreach ($_POST['units'] as $uid => $data) {
                    if (!is_numeric($uid)) {
                        $DEFAULT_ERROR[] = 'ERROR: Unit id invalid !';
                        continue;
                    }
                    // Secure data
                    $data = tldUtils::cleanupFormInput($data);
                    // Get unit info
                    $unit = new tldSORUnit($uid);
                    $unitHeader = $unit->itsHeader;
                    // check if unit assigned to a ER (batch qty not editable in this case as the ER batch qty will not match)
                    $er = tldEquipment::bySORUnit($uid);
                    $erObj = new tldEquipment($er[0]['id']);
                    // check ER exists
                    if (!$erObj->isEmpty()) {
                        // Update APC from form
                        $erObj->setAPC($data['airport_code']);
                        // Update delivery location
                        $erObj->setDeliveryLocation($data['del_location']);
                        // Update Project number
                        $erObj->setProjectNumber($data['t_prno']);
                        // Update Project number
                        $erObj->setCustomerAssetNumber($data['cust_asset_num']);
                    }
                    if (empty((float)$data['sleep_com'])) {
                        $data['sleep_com_sso_id'] = 0;
                    } else {
                        $DEFAULT_ERROR[] = "WARNING: SOR Unit#$uid not updated. There is no SSO associated to the sleeping commission.";
                        continue;
                    }
                    // Check Factory Promised Delivery Date is not in last 3 days of given month
                    if (isset($data['ddel_est1']) && $data['ddel_est1'] != '0000-00-00') {
                        try {
                            $date = new DateTime($data['ddel_est1']);
                        } catch (Exception $e) {
                            $DEFAULT_ERROR[] = "WARNING: SOR Unit#$uid not updated. Factory Promised Delivery Date is invalid.";
                            continue;
                        }
                        $lastDay = $date->format('Y-m-t');
                        $date = new DateTime($lastDay);
                        $date->modify('-1 day');
                        $lastDay1 = $date->format('Y-m-d');
                        $date->modify('-1 day');
                        $lastDay2 = $date->format('Y-m-d');
                        if (in_array($data['ddel_est1'], [$lastDay, $lastDay1, $lastDay2], true)) {
                            $DEFAULT_ERROR[] = "WARNING: SOR Unit#$uid not updated. Factory Promised Delivery Date cannot be in the last 3 days of given month";
                            continue;
                        }
                    }
                    // different checks regarding batch qty
                    if (isset($data['batch_qty'])) {
                        if (!empty($er) && $er[0]['er_batch_qty'] != $data['batch_qty']) {
                            $DEFAULT_ERROR[] = "WARNING: SOR Unit#$uid not updated, Unit and ER batch quantity do not match.<br/>
						You need to ask the PSM to unassign or update the ER batch qty of this ER to be able
						to change the unit batch qty.";
                            continue;
                        }
                        // check qty to be at least equal to 1
                        if (isset($data['batch_qty']) && $data['batch_qty'] < 1) {
                            $DEFAULT_ERROR[] = "WARNING: SOR Unit#$uid not updated, quantity must be at least equal to 1";
                            continue;
                        }
                        // check if adding qty after evp approval
                        if (is_EVPstatusPassed($header['status']) && $unitHeader['batch_qty'] < $data['batch_qty']) {
                            $DEFAULT_ERROR[] = "WARNING: SOR Unit#$uid not updated, you can not add qty when EVP_APPROVAL is reached";
                            continue;
                        }
                        // Check batch qty rules
                        if (!$fl_trailersDollies && $data['batch_qty'] > 1) {
                            $DEFAULT_ERROR[] = "WARNING: SOR Unit#$uid not updated, you can not have a batch qty > 1, batch qty is for dollies and traillers";
                            continue;
                        }
                    } else {
                        $data['batch_qty'] = $unitHeader['batch_qty'];
                    }
                    // Update unit
                    $unit->updateHeader($data);
                    // Prepare logs
                    if (isset($data['airport_code']) && $unitHeader['airport_code'] != $data['airport_code']) {
                        $LOGS[$uid][] = "Airpot Code from '{$unitHeader['airport_code']}' to '{$data['airport_code']}'";
                    }
                    if (isset($data['del_dat']) && $unitHeader['del_dat'] != $data['del_dat']) {
                        $LOGS[$uid][] = "Requested delivery from '{$unitHeader['del_dat']}' to '{$data['del_dat']}'";
                    }
                    if (isset($data['del_early']) && $unitHeader['del_early'] != $data['del_early']) {
                        $LOGS[$uid][] = "Early delivery from '{$unitHeader['del_early']}' to '{$data['del_early']}'";
                    }
                    if (isset($data['batch_qty']) && $unitHeader['batch_qty'] != $data['batch_qty']) {
                        $LOGS[$uid][] = "Batch Quantity from '{$unitHeader['batch_qty']}' to '{$data['batch_qty']}'";
                    }
                    if (isset($data['short_desc']) && $unitHeader['short_desc'] != $data['short_desc']) {
                        $LOGS[$uid][] = "Short description from '{$unitHeader['short_desc']}' to '{$data['short_desc']}'";
                    }
                    if (in_array($header['status'], ['PRINT_FACTORY_SO_ACK', 'ER_ASSIGNMENT', 'IN_PROGRESS', 'SHIPPED', 'CLOSED'])
                        && isset($data['ddel_est1']) && $unitHeader['ddel_est1'] != $data['ddel_est1']) {
                        $LOGS[$uid][] = "Promise delivery from '{$unitHeader['ddel_est1']}' to '{$data['ddel_est1']}' after PRINT_FACTORY_SO_ACK status";
                    }
                }
                // Notification
                if (is_EVPstatusPassed($header['status'])) {
                    $msg = null;
                    foreach ($LOGS as $uid => $logs) {
                        if (count($logs) < 1) {
                            continue;
                        }
                        $msg .= "SOR Unit#$uid UPDATED:<ul>";
                        foreach ($logs as $log) {
                            $msg .= "<li>$log</li>";
                        }
                        $msg .= '</ul>';
                    }
                    // Notify if the ASM made the update
                    _logAfterEVP_APPROVAL(TldDatabase::escape($msg));
                    if ($user->isInGroup(['role_ASM', 'role_EVP'])) {
                        _notifyAfterEVP_APPROVAL(null, $msg);
                    }
                }
                //need to update trans
                $e = $sol->updateTran();
                if (is_string($e)) {
                    $DEFAULT_ERROR[] = $e;
                }
                $body .= '<br/>Units update process finished successfully !';
                break;
            case 'del':
                // 1 - do not allow PSM to delete units
                if (!$user->isInGroup(['gg_ADMIN', 'role_SA', 'role_ASM', 'role_EVP'])) {
                    $DEFAULT_ERROR[] = 'ERROR: You do not have permission to access this page';
                    break;
                }
                // 2 - check data sent
                if (!is_numeric($uid)) {
                    $DEFAULT_ERROR[] = 'ERROR: Data sent invalid!';
                    break;
                }
                // 3 - check if unit assigned to a ER
                $er = tldEquipment::bySORUnit($uid);
                if (!empty($er)) {
                    $DEFAULT_ERROR[] = "ERROR: SOR Unit#$uid not deleted, you need to ask the PSM to unassign the ER before making this action.";
                    break;
                }
                // 4 - delete unit
                $unit = new tldSORUnit($uid);
                $e = $unit->delete();
                if (is_string($e)) {
                    $DEFAULT_ERROR[] = "INTERNAL ERROR: Unit not deleted<br/>$e";
                    break;
                }
                $DEFAULT_ERROR[] = 'Unit deleted successfully';
                if (is_EVPstatusPassed($header['status'])) {
                    $msg = "SOR Unit#$uid DELETED after EVP_APPROVAL status";
                    _logAfterEVP_APPROVAL($msg);
                    if ($user->isInGroup(['role_ASM', 'role_EVP'])) {
                        _notifyAfterEVP_APPROVAL(null, $msg);
                    }
                }
                //need to update trans
                $e = $sol->updateTran();
                if (is_string($e)) {
                    $DEFAULT_ERROR[] = $e;
                }
                break;
            case 'add':
                // Check permissions
                if (!$user->isInGroup(['gg_ADMIN', 'role_SA', 'role_ASM', 'role_EVP'])) {
                    $DEFAULT_ERROR[] = 'ERROR: You do not have permission to access this page';
                    break;
                }
                // Check qty - can not add units, so qty to a SOL which have reached EVP approval
                if (is_EVPstatusPassed($header['status'])) {
                    $DEFAULT_ERROR[] = 'ERROR: You can not add unit after EVP_APPROVAL status, please create a new SOL';
                    break;
                }
                $form = new HTML_QuickForm('frmCreateUnit', 'post');
                $form->addElement('header', 'title', 'Create Unit');
                $form->addElement('hidden', 'm[0]', 'sol');
                $form->addElement('hidden', 'm[1]', 'view');
                $form->addElement('hidden', 'm[2]', 'er');
                $form->addElement('hidden', 'm[3]', 'editUnits');
                $form->addElement('hidden', 'm[4]', 'add');
                $form->addElement('hidden', 'id', $id);
                $form->addElement('text', 'short_desc', 'Short description');
                $form->addElement('textarea', 'long_desc', 'Long Description', ['wrap' => 'VIRTUAL', 'cols' => '30', 'rows' => '4']
                );
                if ($fl_trailersDollies) {
                    $form->addElement('text', 'batch_qty', 'Batch quantity');
                }
                if (320 === (int)$sol->getSSOERP()) {
                    $form->addElement('text', 'dpas_rating', 'DPAS Rating');
                }

                $form->addElement('text', 'del_location', 'Requested delivery Location');
                $form->addElement('text', 'del_dat', 'Requested delivery Date');
                $form->addElement('select', 'del_early', 'Early delivery ok?', ['N' => 'No', 'Y' => 'Yes']);
                $form->addElement('submit', 'btnSubmit', 'Submit');
                $form->addRule('short_desc', 'Required', 'required');
                $form->addRule('batch_qty', 'Required', 'required');
                $form->addRule('del_dat', 'Required', 'required');
                $defaults = ['del_dat' => date('Y-m-d'), 'batch_qty' => 1];
                $form->setDefaults($defaults);

                if ($form->validate()) {
                    $body .= $form->toHTML();
                    break;
                }
                $form->freeze();
                $data = tldUtils::cleanupFormInput($form->exportValues());
                $data['parent_id'] = $id;
                // batch qty rule
                if (!$fl_trailersDollies) {
                    $data['batch_qty'] = 1;
                }
                $e = tldSORUnit::insert($data);
                if (is_string($e)) {
                    $DEFAULT_ERROR[] = "INTERNAL ERROR: Unit not created<br/>$e";
                    break;
                }
                $DEFAULT_ERROR[] = 'Unit created successfully !';
                if (is_EVPstatusPassed($header['status'])) {
                    $msg = "SOR Unit#$e CREATED after EVP_APPROVAL status";
                    _logAfterEVP_APPROVAL($msg);
                    if ($user->isInGroup(['role_ASM', 'role_EVP'])) {
                        _notifyAfterEVP_APPROVAL(null, $msg);
                    }
                }
                //need to update trans
                $e = $sol->updateTran();
                if (is_string($e)) {
                    $DEFAULT_ERROR[] = $e;
                }

                break;
            case 'dup':
                // Check permissions
                if (!$user->isInGroup(['gg_ADMIN', 'role_SA', 'role_ASM', 'role_EVP'])) {
                    $DEFAULT_ERROR[] = 'ERROR: You do not have permission to access this page';
                    break;
                }
                // Check qty - can not add units, so qty to a SOL which have reached EVP approval
                if (is_EVPstatusPassed($header['status'])) {
                    $DEFAULT_ERROR[] = 'ERROR: You can not add unit after EVP_APPROVAL status, please create a new SOL';
                    break;
                }
                $form = new HTML_QuickForm('frmCreateUnit', 'post');
                $form->addElement('header', 'title', "Duplicate Unit #$id");
                $form->addElement('hidden', 'm[0]', 'sol');
                $form->addElement('hidden', 'm[1]', 'view');
                $form->addElement('hidden', 'm[2]', 'er');
                $form->addElement('hidden', 'm[3]', 'editUnits');
                $form->addElement('hidden', 'm[4]', 'dup');
                $form->addElement('hidden', 'id', $id);
                $form->addElement('hidden', 'uid', $uid);
                $form->addElement('text', 'quantity', 'Quantity');
                $form->addElement('submit', 'btnSubmit', 'Submit');
                $form->addRule('quantity', 'This field is required', 'required');
                $form->addRule('quantity', 'This field must be an integer', 'numeric');
                $form->setDefaults(['quantity' => 1]);

                if ($form->validate()) {
                    $body .= $form->toHTML();
                    break;
                }
                $form->freeze();
                $data = tldUtils::cleanupFormInput($form->exportValues());
                $original = new tldSORUnit($uid);
                for ($i = 0; $i < $data['quantity']; $i++) {
                    if (is_string($duplicate = $original->duplicate($id))) {
                        $DEFAULT_ERROR[] = $duplicate;
                        break;
                    }

                    $DEFAULT_ERROR[] = "SOR Unit#$duplicate successfully created from SOR Unit#$uid duplication.";
                    if (is_EVPstatusPassed($header['status'])) {
                        $msg = "SOR Unit#$duplicate CREATED after EVP_APPROVAL status from SOR Unit#$uid duplication";
                        _logAfterEVP_APPROVAL($msg);
                        if ($user->isInGroup(['role_ASM', 'role_EVP'])) {
                            _notifyAfterEVP_APPROVAL(null, $msg);
                        }
                    }
                }
                //need to update trans
                $e = $sol->updateTran();
                if (is_string($e)) {
                    $DEFAULT_ERROR[] = $e;
                }
                break;
        }
        break;
    case 'create':
        if (!$user->isInGroup(['gg_ADMIN', 'role_PSM', 'role_PSE', 'role_PSA'])) {
            $DEFAULT_ERROR[] = 'ERROR: You do not have permission to access this page';
            break;
        }
        $form = new HTML_QuickForm('frmCreateER1', 'post', '', '', '', true);
        $form->addElement('header', 'title', 'Automated ER Creation, step 1...');
        $form->addElement('hidden', 'm[0]', 'sol');
        $form->addElement('hidden', 'm[1]', 'view');
        $form->addElement('hidden', 'm[2]', 'er');
        $form->addElement('hidden', 'm[3]', 'create1');
        $form->addElement('hidden', 'id', $id);
        $form->addElement('text', 'num', 'Qty of ERs to create');
        $form->addElement('select', 'batch', 'Create and Assign ER to batch only?', ['Y' => 'YES', 'N' => 'NO']);
        $form->addElement('submit', 'btnSubmit', 'Submit');
        $form->setDefaults(['num' => $header['qty_sou'] - $header['qty_alloc'], 'batch' => 'N']);
        $body .= $form->toHTML();
        break;
    case 'create1':
        if (!$user->isInGroup(['gg_ADMIN', 'role_PSM', 'role_PSE', 'role_PSA'])) {
            $DEFAULT_ERROR[] = 'ERROR: You do not have permission to access this page';
            break;
        }
        // check the number of equipment to create
        if ($num > $header['qty_sou'] - $header['qty_alloc']) {
            $DEFAULT_ERROR[] = 'ERROR: number of equipment to create automatically is limited to the quantity of units which have not been allocated yet...';
            break;
        }
        $form = new HTML_QuickForm('frmCreateER2', 'post', '', '', '', true);
        $form->addElement('header', 'title', 'Automated ER Creation, step 2...');
        $form->addElement('hidden', 'm[0]', 'sol');
        $form->addElement('hidden', 'm[1]', 'view');
        $form->addElement('hidden', 'm[2]', 'er');
        $form->addElement('hidden', 'm[3]', 'create1');
        $form->addElement('hidden', 'num', $num);
        $form->addElement('hidden', 'batch', $batch);
        $form->addElement('hidden', 'id', $id);

        $form->addElement('text', 'location_short', 'Final End User Location, short version', ['size' => 50]);
        $form->addElement('select', 'airport_code', 'Airport Code', ['' => ''] + tldAirport::getList());
        $form->addElement('textarea', 'warranty_conditions', 'Warranty Conditions', ['wrap' => 'VIRTUAL', 'cols' => '60', 'rows' => '8']);
        $form->addElement('textarea', 'options_desc', 'Description of Selected Options', ['wrap' => 'VIRTUAL', 'cols' => '60', 'rows' => '8']);
        $form->addElement('textarea', 'mfg_comments', 'Comments', ['wrap' => 'VIRTUAL', 'cols' => '60', 'rows' => '8']);
        $form->addElement('text', 'diml', 'Length (mm)', ['size' => 9]);
        $form->addElement('text', 'dimw', 'Width (mm)', ['size' => 9]);
        $form->addElement('text', 'dimh', 'Height (mm)', ['size' => 9]);
        $form->addElement('text', 'dimk', 'Weight (KG)', ['size' => 9]);
        if ($user->isInGroup(['gg_ADMIN', 'role_PSA', 'role_PSM'])) {
            $form->addElement('select', 'maintenance_contract_ref', 'FMS contract', ['' => ''] + tldEquipment::getFmsContractType());
        }
        $form->addElement('submit', 'btnSubmit', 'Submit');

        $defaults = [
            'ddel_est1' => date('Y-m-d'),
            'dgt_est' => date('Y-m-d'),
        ];

        $breakdown = $sol->getBreakdown(['include' => tldSOL::getInternalCategoriesList()]);

        if (count($breakdown)) {
            foreach ($breakdown as $breakdown_line) {
                $defaults['options_desc'] .= $breakdown_line['dsca'] . "\n";
            }
        }
        $defaults['warranty_conditions'] = sprintf("%s\n\n%s\n\n%s", $sol->getWrtyStd(), $sol->getWrtyCond(), $sol->getWrtySpec());
        $form->setDefaults($defaults);

        if ($form->validate()) {
            $form->freeze();
            $formVals = $form->exportValues();
            $p = $formVals;
            $num = $formVals['num'];

//            These 3 next lines solve the solution of this TTS#9924 on 23/01/2025
//            But, it created a regression, talk in this TTS#15330 on 24/04/2025
//            These str_replace remove linebreaks and disorder the display in the ER.
//            If you encounter any new problems with formatting of textarea during creation of ER, DO NOT uncomment
//            these lines. Find a solution to fix the problem at the origin of datas.
//            Actually, it seems creating en ER from SOL is ok.
//            $p['warranty_conditions'] = str_replace(["\r\n", "\n", "\r"], " ", $p['warranty_conditions']);
//            $p['options_desc'] = str_replace(["\r\n", "\n", "\r"], " ", $p['options_desc']);
//            $p['mfg_comments'] = str_replace(["\r\n", "\n", "\r"], " ", $p['mfg_comments']);

            if ($num < 1) {
                $DEFAULT_ERROR[] = 'ERROR: number of equipment to create must be at least one...';
                break;
            }
            if ($num > $header['qty_sou'] - $header['qty_alloc']) {
                $DEFAULT_ERROR[] = 'ERROR: number of equipment to create automatically is limited to the quantity of units which have not been allocated yet...';
                break;
            }
            //move certain header values to input array
            //get common data from SOR or SOL
            $sor = new tldSOR($sol->getParent());
            $sorheader = $sor->itsHeader;
            $p['customer_name'] = $sorheader['user_customer_display'];
            $p['customer_id'] = $sorheader['user_customer_id'];
            $p['buyer_customer_id'] = $sorheader['buyer_customer_id'];
            // set model and type
            $p['model'] = $header['model'];
            $p['eng_tier'] = $header['eng_tier'];
            $modelType = tldType::byModel($header['model']);
            $p['type'] = $modelType['en'];
            //set manufacturer location
            $p['man_location'] = $header['bu_fullname'];
            //set sales organization with the one of the SOR
            $p['sales_org'] = $sorheader['location'];
            $p['sso_service'] = $sorheader['location'];
            // clean desc
            $sales_rep = new tldUser($sorheader['asm']);
            $p['sales_rep'] = $sales_rep->getFullname();

            if (!empty($sol->itsHeader['ctry'])) {
                $p['del_ctry'] = $sol->itsHeader['ctry'];
            }
            // Get unit list with ER linked if assigned
            $sorus = tldSORUnit::byParent($id);
            // Check if SOL is fully allocated
            if (count($sorus) < 1) {
                $DEFAULT_ERROR[] = 'ERROR: This SOL is fully allocated with ERs...';
                break;
            }

            // Create Equipment and assign to unit foreach units
            foreach ($sorus as $unit) {
                // Check if Qty
                // Check if unit already assigned
                if (!empty($unit['erid'])) {
                    continue;
                }
                $p['delivery_location'] = $unit['del_location'];
                $p['fms_contract_duration'] = $header['fms_contract_duration'];
                $p['warranty_length'] = $header['warranty_length'];
                $p['warranty_length_hours'] = $header['warranty_length_hours'];
                // Choose which way we create ER and assign unit
                if ($p['batch'] === 'Y') {
                    // Check if batch
                    if ($unit['batch_qty'] <= 1) {
                        continue;
                    }
                    // Check if nb ER match with unit batch quantity
                    if ($unit['batch_qty'] <= $num) {
                        $p['er_batch_qty'] = $unit['batch_qty'];
                        // Clean up data before ER creation
                        $p = tldUtils::cleanupFormInput($p);
                        $newerid = tldEquipment::create($p);
                        tldUtils::log_event("ER#$newerid added to SOL# $id");
                        $er = new tldEquipment($newerid, true);
                        $er->addLogEntry($user->getID(), "Created and assigned from SOL#$id");
                        $e = $er->setSOR_UID($unit['id']);
                        if (is_string($e)) {
                            $DEFAULT_ERROR[] = $e;
                        } else {
                            $num -= $unit['batch_qty'];
                        }
                    } else {
                        continue;
                    }
                } else {
                    // Check if batch
                    if ((int)$unit['batch_qty'] !== 1) {
                        continue;
                    }
                    // Check if nb ER match with unit batch quantity
                    if ($num >= 1) {
                        $p['er_batch_qty'] = 1;
                        // Clean up data before ER creation
                        $p = tldUtils::cleanupFormInput($p);
                        $newerid = tldEquipment::create($p);
                        tldUtils::log_event("ER#$newerid added to SOL# $id");
                        $er = new tldEquipment($newerid, true);
                        $er->addLogEntry($user->getID(), "Created and assigned from SOL#$id");
                        $e = $er->setSOR_UID($unit['id']);
                        if (is_string($e)) {
                            $DEFAULT_ERROR[] = $e;
                        } else {
                            $num--;
                        }
                    } else {
                        break;
                    }
                }
            }
            // Inform if all ER have been created and assigned
            if ($num > 0) {
                $DEFAULT_ERROR[] = "WARNING: $num ER(s) have not been created and assigned, check batches before to create automaticly ER<br>";
            } else {
                $DEFAULT_ERROR[] = 'ER(s) created and assigned successfully!';
            }
            // get report
            $sol->refresh();
            $header = $sol->itsHeader;
        } else {
            $DEFAULT_ERROR[] = "You are about to automatically create <font size=\"4\"><b>$num x " . $header['model'] . '</b></font> Equipment Record(s). Pls make sure all data is accurate!...<br><br>';
            $body .= $form->toHTML();
        }
        break;
    case 'receipt':
        if (!$user->isInGroup(['gg_ADMIN', 'role_SA', 'role_ASM', 'role_EVP'])) {
            $DEFAULT_ERROR[] = 'ERROR: You do not have permission to access this page';
            break;
        }
        switch ($m[4]) {
            case 'form':
                $report = new tldReportColumnar(
                    array_filter($sol->getFiles(), static function (array $file) {
                        return isset($file['description']) && preg_match('/SOL#\d{5} Acknowledgment - [\d-]{10} [\d:]{8}$/', $file['description']);
                    }),
                    [
                        'xItems' => [
                            'id' => 'File ID',
                            'description' => 'Description',
                            'filename' => 'Filename',
                            'poster' => 'Poster'],
                        'links' => ['id' => '/en/private/common/index.php?m[0]=files&m[1]=view&id='],
                    ]
                );
                $body .= $report->fetch() . '<br/><br/><br/><br/>';
                if (!$sol->isClosed()) {
                    $ER = tldSORUnit::byParent($id);
                    $smarty->assign('nextURL', "$php_self?m[0]=sol&m[1]=view&m[2]=er&m[3]=receipt&m[4]=update&id=$id");
                    $smarty->assign('er', $ER);
                    $smarty->assign('id', $id);
                    $body .= $smarty->fetch("$PATH/sol/sol_ack.form.tpl");
                }

                break 2;
            case 'update':
                if (empty($_POST['units']) || !is_array($_POST['units'])) {
                    $DEFAULT_ERROR[] = 'ERROR: No units date set or data invalid !';
                    break 2;
                }
                // Update process
                foreach ($_POST['units'] as $uid => $data) {
                    if (!is_numeric($uid)) {
                        $DEFAULT_ERROR[] = 'ERROR: Unit id invalid !';
                        continue;
                    }
                    // Secure data
                    $data = tldUtils::cleanupFormInput($data);

                    $unit = new tldSORUnit($uid);
                    if (!isset($data['ddel_asm']) || empty($data['ddel_asm']) || $data['ddel_asm'] == '0000-00-00') {
                        $DEFAULT_ERROR[] = 'ERROR: The AMS promised date has not been filled !';
                        break 2;
                    }

                    // Update unit
                    if ($data['ddel_asm'] !== $unit->itsHeader['ddel_asm']) {
                        \DateTime::createFromFormat('Y-m-d', $data['ddel_asm']);
                        $errors = \DateTime::getLastErrors();
                        if (!empty($errors['warning_count']) || !empty($errors['error_count'])) {
                            $DEFAULT_ERROR[] = "ERROR: Invalid date ('{$data['ddel_asm']}')";
                            continue;
                        }
                        $unit->updateHeader($data);
                    }
                }

                if (!empty($DEFAULT_ERROR)) {
                    $DEFAULT_ERROR[] = 'ERROR: The file was not generated.';
                    break;
                }

                $sol->refresh();
                $file = $sol->getReceipt();

                if (is_string($file)) {
                    $DEFAULT_ERROR[] = $file;
                    break;
                }
                $name = sprintf('SOL#%s %s.pdf', $id, date('Y-m-d H-i-s'));
                $e = $sol->addFile(
                    [
                        'poster' => $user->getID(),
                        'description' => sprintf('SOL#%s Acknowledgment - %s', $id, date('Y-m-d H:i:s')),
                    ],
                    [
                        'tmp_name' => $file->itsFilepath,
                        'name' => $name,
                    ]
                );
                $file->out($name);
                exit;
            default:
                $report = new tldReportColumnar(
                    array_filter($sol->getFiles(), function (array $file) {
                        return isset($file['description']) && preg_match('/SOL#\d{5} Acknowledgment - [\d-]{10} [\d:]{8}$/', $file['description']);
                    }),
                    [
                        'xItems' => [
                            'id' => 'File ID',
                            'description' => 'Description',
                            'filename' => 'Filename',
                            'poster' => 'Poster'],
                        'links' => ['id' => '/en/private/common/index.php?m[0]=files&m[1]=view&id='],
                    ]
                );
                $body .= $report->fetch();
                break 2;
        }
    case 'assign':
        if (!$user->isInGroup(['gg_ADMIN', 'role_SA', 'role_PSM', 'role_PSE', 'role_PSA', 'role_COO'])) {
            $DEFAULT_ERROR[] = 'ERROR: You do not have permission to access this page';
            break;
        }
        switch ($m[4]) {
            case 'add':
                if ($soruid && $erid) {
                    if (!is_numeric($soruid) && !is_numeric($erid)) {
                        $DEFAULT_ERROR[] = 'ERROR: Data sent invalid!';
                        break;
                    }
                    //check if sor unit is already linked to an ER
                    $rows = tldEquipment::bySORUnit($soruid);
                    if (count($rows)) {
                        $DEFAULT_ERROR[] = 'ERROR: This unit is already assigned. Please unassign before assigning a new ER on this unit';
                        break;
                    }
                    $er = new tldEquipment($erid);
                    $unit = new tldSORUnit($soruid);
                    // Check if batch qty match
                    if ($er->itsDetails['er_batch_qty'] <> $unit->itsHeader['batch_qty']) {
                        $DEFAULT_ERROR[] = "ERROR: Batch quantity of ER#$erid and SORUnit#$soruid do not match";
                        break;
                    }
                    // Check engine tiers match
                    if ($er->itsDetails['eng_tier'] <> $header['eng_tier']) {
                        $DEFAULT_ERROR[] = "ERROR: Engine tier of ER#$erid and SOL#$id do not match";
                        break;
                    }

                    // Assign the ER to the unit
                    $e = $er->setSOR_UID($soruid);
                    if (is_string($e)) {
                        $DEFAULT_ERROR[] = "ERROR: there was a problem assigning ER to SOR, error returned was $e";
                        break;
                    }

                    // SOR data
                    $sor = new tldSOR($sol->getParent());
                    $sorheader = $sor->itsHeader;

                    // Capture assigned customers
                    $assigned_customers = [
                        'old' => [
                            'customer_name' => $er->itsDetails['user_customer_display'],
                            'custmer_id' => $er->itsDetails['customer_id'],
                            'buyer_customer_id' => $er->itsDetails['buyer_customer_id'],
                            'buyer_customer_name' => $er->itsDetails['buyer_customer_display'],
                        ],
                        'new' => [
                            'customer_name' => $sorheader['user_customer_display'],
                            'custmer_id' => $sorheader['user_customer_id'],
                            'buyer_customer_id' => $sorheader['buyer_customer_id'],
                            'buyer_customer_name' => $sorheader['buyer_customer_display'],
                        ],
                    ];

                    // Update the ER regarding SOL data
                    // Manufacturer location
                    $factory = new tldLocation($header['bu']);
                    // Get ASM
                    $sales_rep = new tldUser($sorheader['asm']);

                    // Construct er updated details
                    $newHeader = [
                        'buyer_customer_id' => $assigned_customers['new']['buyer_customer_id'],
                        'customer_id' => $assigned_customers['new']['custmer_id'],
                        'customer_name' => $assigned_customers['new']['customer_name'],
                        'customer_name_prev' => $assigned_customers['old']['customer_name'],
                        'sales_org' => $sorheader['location'],
                        'sso_service' => $sorheader['location'],
                        'sales_rep' => $sales_rep->getFullname(),
                        'delivery_location' => $unit->itsHeader['del_location'],
                        'warranty_length' => $header['warranty_length'],
                        'warranty_length_hours' => $header['warranty_length_hours'],
                        'fms_contract_length' => $header['fms_contract_length'],
                        'fms_end_use_date' => '0000-00-00',
                    ];

                    if (!empty($sol->itsHeader['ctry'])) {
                        $newHeader['del_ctry'] = $sol->itsHeader['ctry'];
                    }
                    $newHeader = tldUtils::cleanupFormInput($newHeader);
                    // Copy SOL options -- After cleanup on purpose to preserve multiline
                    // User inputs are controlled at breakdown level
                    $breakdown = $sol->getBreakdown(['include' => tldSOL::getInternalCategoriesList()]);

                    foreach ($breakdown as $breakdown_line) {
                        $newHeader['options_desc'] .= $breakdown_line['dsca'] . "\n";
                    }

                    $warrantyDesc = [];
                    $WrtyStd = $sol->getWrtyStd();
                    if (!empty($WrtyStd)) {
                        $warrantyDesc[] = TldDatabase::escape($WrtyStd);
                    }
                    $WrtyCond = $sol->getWrtyCond();
                    if (!empty($WrtyCond)) {
                        $warrantyDesc[] = TldDatabase::escape($WrtyCond);
                    }
                    $WrtySpec = $sol->getWrtySpec();
                    if (!empty($WrtySpec)) {
                        $warrantyDesc[] = TldDatabase::escape($WrtySpec);
                    }
                    $newHeader['warranty_conditions'] = implode("\n\n", $warrantyDesc);
                    $e = $er->updateRecord($newHeader, array_keys($newHeader));
                    if (is_string($e)) {
                        $DEFAULT_ERROR[] = "ERROR: ER info not updated from SOL data<br>Reason: $e";
                        break;
                    }
                    // Set ER log
                    $log_comment = <<<EOF
ER reassigned:
USER: (from) {$assigned_customers['old']['customer_name']} (to) {$assigned_customers['new']['customer_name']}
BUYER: (from) {$assigned_customers['old']['buyer_customer_name']} (to) {$assigned_customers['new']['buyer_customer_name']}
EOF;
                    $er->addLogEntry($user->getID(), $log_comment);
                } else {
                    $xItems = [
                        'id' => 'ID#',
                        'sn' => 'SN#',
                        'man_location' => 'Factory',
                        'user_customer_display' => 'Customer',
                        'model' => 'Model',
                        'eng_tier' => 'Emission Rating',
                        'airport_code' => 'Airport Code',
                        'date_shipped' => 'Ship Date',
                        'er_batch_qty' => 'Batch Qty',
                    ];
                    $rows = tldEquipment::bySimilarModelUnallocated($id);
                    $report = new tldReportColumnar($rows,
                        [
                            'xItems' => $xItems,
                            'title' => "Unallocated Similar Models, click ID# to assign ER to SOR Unit $soruid",
                            'links' => ['id' => "$php_self?m[0]=sol&m[1]=view&m[2]=er&m[3]=assign&m[4]=add&id=$id&soruid=$soruid&erid="],
                            'showItemNumbers' => true,
                        ]
                    );
                    $body .= $report->fetch();
                    $rows = tldEquipment::bySimilarModelUnshipped($id);
                    $report = new tldReportColumnar($rows,
                        [
                            'xItems' => $xItems,
                            'title' => "Unshipped Similar Models, could be linked to other SOR Lines!! , click ID# to assign to SOR Unit $soruid",
                            'links' => ['id' => "$php_self?m[0]=sol&m[1]=view&m[2]=er&m[3]=assign&m[4]=add&id=$id&soruid=$soruid&erid="],
                            'showItemNumbers' => true,
                        ]
                    );
                    $body .= $report->fetch();
                    $rows = tldEquipment::bySimilarModelDemo($id);
                    $report = new tldReportColumnar($rows,
                        [
                            'xItems' => $xItems,
                            'title' => "Similar **DEMO** Models, could be linked to other SOR Lines!! , click ID# to assign to SOR Unit $soruid",
                            'links' => ['id' => "$php_self?m[0]=sol&m[1]=view&m[2]=er&m[3]=assign&m[4]=add&id=$id&soruid=$soruid&erid="],
                            'showItemNumbers' => true,
                        ]
                    );
                    $body .= $report->fetch();
                }
                break;
            case 'remove':
                if (empty($erid) || !is_numeric($erid)) {
                    $DEFAULT_ERROR[] = 'ERROR: ER id sent to unassign empty or invalid';
                    break;
                }
                $er = new tldEquipment($erid);
                $ER_header = $er->itsDetails;
                // get erp number for each entity of this order
                $bu = [
                    'sso' => $sol->getSSOERP(),
                    'erp' => $sol->getERP(),
                ];
                $sorUnit = new tldSORUnit($er->getSOR_UID());
                if (1 === (int) $sorUnit->itsHeader['commissioning']){
                    $openedCommissioningCsr = tldCSR::byConstraints(sprintf('csr.parent_id = %s AND csr.status IN ("IN PROGRESS", "PENDING")  AND csr.work_type = "Commissioning"', $er->itsId));
                    if($openedCommissioningCsr){
                        $DEFAULT_ERROR[] = sprintf('WARNING: There is at least one Commissioning CSR linked to  <a href="/en/private/product_support/index.ps.php?m[0]=equipment&m[1]=view&m[2]=csr&id=%1$d">this unit (ER#%1$d)</a> Please take action before unassigning this unit',$er->itsId);
                        break;
                    }
                }
                // Unassign ER to the SOL
                $e = $er->setSOR_UID(0);
                if (is_string($e)) {
                    $DEFAULT_ERROR[] = "ERROR: there was a problem unassigning ER from SOR, error returned was $e";
                    break;
                }
                // Unassign ER to the SOL
                $e = $er->setSORLID(0);
                if (is_string($e)) {
                    $DEFAULT_ERROR[] = "ERROR: there was a problem unassigning ER from SOR, error returned was $e";
                    break;
                }
                // Reset ER info
                $resetData = [
                    'customer_name' => '',
                    'customer_id' => '',
                    'buyer_customer_id' => '',
                    'sales_org' => '',
                    'sales_rep' => '',
                    'warranty_conditions' => '',
                    'delivery_location' => '',
                ];
                $e = $er->updateRecord($resetData, array_keys($resetData));
                $error = $er->setAvailableForSale($user->getID());
                if (is_string($e)) {
                    $DEFAULT_ERROR[] = "ERROR: ER {$er->getSN()} info not reseted during unassignement. Reason: $e";
                } elseif(is_string($error)) {
                    $DEFAULT_ERROR[] = "ERROR: ER {$er->getSN()} was not marked as available for sale. Reason: $e";
                }
                else {
                    $er->addLogEntry($user->getID(), "Unassigned from SOL#$id");
                }
                // Notify relevant people
                if ($ER_header['date_shipped'] !== '0000-00-00' || $ER_header['rrd_erp'] !== '0000-00-00' || $ER_header['rrd_sso'] !== '0000-00-00') {
                    $TO = [];
                    // If ER has a Shipment date, email both SSO and Factory
                    if ($ER_header['date_shipped'] !== '0000-00-00') {
                        $TO[] = [
                            $bu['sso'] => ['role_EVP', 'role_SA'],
                            $bu['erp'] => ['role_FC'],
                        ];
                        $m1 = '<li>ER is <strong>Shipped</strong></li>';
                    }
                    // If ER have Revenue Recognition Date, email entities
                    if ($ER_header['rrd_sso'] != '0000-00-00') {
                        $TO[] = [$bu['sso'] => ['role_EVP', 'role_SA', 'role_FC']];
                        $m2 = '<li>ER <strong>SSO revenue</strong> is recognized</li>';
                    }
                    if ($ER_header['rrd_erp'] != '0000-00-00') {
                        $TO[] = [
                            $bu['sso'] => ['role_EVP', 'role_SA', 'role_FC'],
                            $bu['erp'] => ['role_FC'],
                        ];
                        $m3 = '<li>ER <strong>Factory revenue</strong> is recognized</li>';
                    }
                }
                $message = <<<EOF
				<p>ER#${ER_header['id']} was unassigned from SOL#$id with the following details</p>
				<ul>
					$m1
					$m2
					$m3
				</ul>
				<p><a href="https://www.tld-gse.com/en/private/sales_service/sales.php?m[0]=sol&m[1]=view&id=$id">
				Please log into the tld-gse intranet to get more details on this.</a></p>
EOF;
                // Make unique group
                $emailGroup[$bu['erp']] = [];
                $emailGroup[$bu['sso']] = [];
                foreach ($TO as $key => $to) {
                    if (is_array($to[$bu['sso']])) {
                        $emailGroup[$bu['sso']] = array_merge($emailGroup[$bu['sso']], $to[$bu['sso']]);
                    }
                    if (is_array($to[$bu['erp']])) {
                        $emailGroup[$bu['erp']] = array_merge($emailGroup[$bu['erp']], $to[$bu['erp']]);
                    }
                }
                foreach ($emailGroup as $bu => $group) {
                    $emailGroup[$bu] = array_unique($group);
                }
                // Finally send email
                $e = tldGroup::emailMultipleGroups(
                    $emailGroup,
                    null,
                    'ER#' . $ER_header['id'] . ' unassigned from SOL#' . $id,
                    $message
                );
                break;
        }
        //refresh header info
        $sol = new tldSOL($id);
        $header = $sol->itsHeader;
//FALL THROUGH
    default:
        $axItems = [
            'short_desc' => 'Short Description',
            'del_location' => 'Requested Delivery Location',
            'del_dat' => 'Requested Delivery Date',
            'del_early' => 'Early delivery ok?',
            'ddel_est1' => 'Factory Promised Delivery Date',
            'dgt_rev' => 'Estimated GT Date',
            'promised_cbom_date' => 'Promised CBOM Date',
            'last_cbom_update_date' => 'Last CBOM update Date',
            'sn' => 'SN#',
            't_prno' => 'Project#',
            't_pdno' => 'Work Order#',
            'model' => 'Model',
            'cust_asset_num' => 'Customer Asset#',
            'eng_tier' => 'Emission Rating',
            'airport_code' => 'Final Destination',
            'esr_id' => 'ESR#',
            'dgt_act' => 'GT Date',
            'dyt' => 'YT Date',
            'date_shipped' => 'Ship Date',
            'batch_qty' => 'Batch Qty',
            'tranid_sso' => 'SSO, Tran',
            'tranid_erp' => 'Factory, Tran',
            'commissioning' => 'Commissioning',
            'id' => 'Click Below to Assign an ER',
            'erid' => 'Click Below to Unassign this ER',
        ];

        if (320 === (int)$sol->getSSOERP()) {
            $axItems['dpas_rating'] = 'DPAS Rating';
        }

        $aLinks = [
            'sn' => '/en/private/product_support/index.ps.php?m[0]=equipment&m[1]=search&m[2]=bySN&sn=',
            'esr_id' => "$php_self?m[0]=esr&m[1]=view&id=",
            'id' => "$php_self?m[0]=sol&m[1]=view&m[2]=er&m[3]=assign&m[4]=add&id=$id&soruid=",
            'erid' => [
                'url' => "$php_self?m[0]=sol&m[1]=view&m[2]=er&m[3]=assign&m[4]=remove&id=$id",
                'params' => ['erid' => 'erid'],
                'confirmPopup' => 'Please confirm unassignment of this ER ?'],
        ];
        // get list of ER
        $ER = tldSORUnit::byParent($id);
        foreach ($ER as &$sorUnit){
            $sorUnit['commissioning'] = (int) $sorUnit['commissioning'] === 1 ? 'YES' : 'NO';
        }
        unset($sorUnit);
        $report = new tldReportColumnar(
            $ER,
            [
                'xItems' => $axItems,
                'title' => 'SOR Units',
                'links' => $aLinks,
                'functions' => [
                    'Duplicate' => [
                        'img' => '/shared/bluesphere/22x22/actions/editcopy.png',
                        'url' => "$php_self?m[0]=sol&m[1]=view&m[2]=er&m[3]=editUnits&m[4]=dup&id=$id",
                        'param' => ['uid' => 'id'],
                    ],
                ],
                'showItemNumbers' => true,
            ]
        );
        $body .= $report->fetch();
        break;
}
