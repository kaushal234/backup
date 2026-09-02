<?php

use ApiBundle\Client;
use Symfony\Component\HttpClient\Exception\ClientException;

include_once 'vault.inc.php';
include_once 'erp.inc.php';
include_once 'eng.inc.php';
include_once 'Image/Graph.php';
include_once 'product_support.inc.php';
include_once 'sales_service.inc.php';
require_once 'HTML/QuickForm/advmultiselect.php';
$body = '';
$DEFAULT_TITLE .= "\ER";
$DEFAULT_MENU .= <<<EOF
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<a href="$php_self?m[0]=equipment">Home</a>
&nbsp;|&nbsp;<a href="$php_self?m[0]=equipment&m[1]=listing&m[2]=search">Search</a>
&nbsp;|&nbsp;<a href="$php_self?m[0]=equipment&m[1]=listing&m[2]=advSearch">Advanced Search</a>
&nbsp;|&nbsp;<a href="$php_self?m[0]=equipment&m[1]=listing&m[2]=byComponent">By Component</a>
&nbsp;|&nbsp;<a href="$php_self?m[0]=equipment&m[1]=reports">Reports</a>
&nbsp;|&nbsp;<a href="$php_self?m[0]=equipment&m[1]=help">Help</a>
EOF;

if ($user->isInGroup(['er_cleanup', 'role_CSM', 'role_EVP', 'gg_ADMIN', 'superuser'])) {
	$DEFAULT_MENU .= <<<EOF
&nbsp;|&nbsp;<a href="$php_self?m[0]=equipment&m[1]=cleanup">Cleanup Tools</a>
EOF;
}
if ($user->isInGroup(['gg_ADMIN', 'gg_MIS', 'role_PSM', 'role_PSE', 'role_PSA', 'gg_SUPPORT'])) {
	$DEFAULT_MENU .= <<<EOF
&nbsp;|&nbsp;<a href="$php_self?m[0]=equipment&m[1]=forms&m[2]=add" title="Create Equipment Record">Create ER</a>
&nbsp;|&nbsp;<a href="$php_self?m[0]=equipment&m[1]=forms&m[2]=addPAS" title="Create Pre-Assembly">Create PAS</a>
EOF;
}
if ($user->isInGroup(['role_SA'])) {
	$DEFAULT_MENU .= <<<EOF
&nbsp;|&nbsp;<a href="$php_self?m[0]=equipment&m[1]=forms&m[2]=uploadFMSdata" title="Upload FMS contract data">FMS upload</a>
EOF;
}
if ($user->isInGroup(['gg_ADMIN', 'gg_MIS'])) {
	$DEFAULT_MENU .= <<<EOF
&nbsp;|&nbsp;<a href="equipment/equipment_admin.php">Admin</a>
EOF;
}

switch ($m[1]) {
	case 'forms':
		switch ($m[2]) {
			case 'addPAS':
				$DEFAULT_TITLE .= "\Create PAS";
				if (!$user->isInGroup(['role_PSM', 'role_PSE', 'role_PSA', 'gg_SUPPORT', 'gg_MIS', 'gg_ADMIN'])) {
					$DEFAULT_ERROR[] = 'ERROR: You do not have permission to create Equipment Records.';
					break;
				}
				// Listing
				$customerRawList = tldCustomer::byType('TLD Manufacturing & Industrial Partners');
				$customerList = ['' => ''] + tldUtils::optionsByKeyValue($customerRawList, 'id', 'customer_name');
				if (empty($customerList)) {
					$DEFAULT_ERROR[] = "WARNING: PAS creation issue. Reason: No customer found with the type 'TLD Manufacturing & Industrial Partners'";
				}
				$factoryList = array_merge(['' => ''], tldLocation::getFactoryList('smartyOptionsLocationLocation'), tldEquipment::getUsedFactoryList());
				ksort($factoryList);
				$typeList = ['' => ''] + tldType::getList('smartyOptions');
				$_engTierTypes = ['' => ''] + tldList::optionsByListNameAsListItemListItem('list.engine.tiers');
				// Form
				$form = new HTML_QuickForm('frmAdd');
				$form->addElement('hidden', 'm[0]', 'equipment');
				$form->addElement('hidden', 'm[1]', 'forms');
				$form->addElement('hidden', 'm[2]', 'addPAS');
				$form->addElement('header', 'title', 'Create New PAS');
				// General info
				$form->addElement('header', 'title', 'General');
				$form->addElement('text', 'esrid', 'ESR ID#');
				$form->addElement('textarea', 'odp_note', 'ODP Comment', ['wrap' => 'VIRTUAL', 'cols' => '40', 'rows' => '7']);
				$form->addElement('header', 'title', 'Customer Details');
				$form->addElement('select', 'buyer_customer_id', 'Customer Name (BUYER)', $customerList);
				$form->addElement('text', 'customer_contact', 'Customer Contact');
				$form->addElement('text', 'customer_ref', 'Customer Ref/PO Number');
				$form->addElement('text', 'date_arrived', 'Arrival Date (YYYY-MM-DD)');
				// ER details
				$form->addElement('header', 'title', 'Equipment Details');
				$form->addElement('select', 'type', 'Equipment Type', $typeList);
				$form->addElement('text', 'er_batch_qty', 'Batch Qty');
				$form->addElement('textarea', 'options_desc', 'Description of Options', ['wrap' => 'VIRTUAL', 'cols' => '40', 'rows' => '7']);
				$form->addElement('select', 'man_location', 'Manufacturer Location', $factoryList);
				$form->addElement('select', 'eng_tier', 'Emission Rating', $_engTierTypes);
				$form->addElement('text', 'diml', 'Length (mm)');
				$form->addElement('text', 'dimw', 'Width (mm)');
				$form->addElement('text', 'dimh', 'Height (mm)');
				$form->addElement('text', 'dimk', 'Weight (KG)');
				// Manufacturing
				$form->addElement('header', 'title', 'Manufacturing Section');
				$form->addElement('textarea', 'mfg_comments', 'MFG Comments', ['wrap' => 'VIRTUAL', 'cols' => '40', 'rows' => '7']);
				$form->addElement('text', 't_pdno', 'Main Work Order#');
				$form->addElement('text', 'sls_orno', 'MFG SO#');
				$form->addElement('submit', 'btnSubmit', 'Submit');
				// Rules & defaults
				$fields = ['man_location', 'buyer_customer_id'];
				foreach ($fields as $field) {
					$form->addRule($field, 'Required', 'required');
				}
				$form->setDefaults(['er_batch_qty' => '1', 'man_location' => $user->itsDetails['location']]);

				if (!$form->validate()) {
					$body = $form->toHTML();
					break;
				}

				$rawVars = $form->exportValues();
				$rawVars['entered_by'] = $user->getEmail();
				$rawVars['customer_id'] = $rawVars['buyer_customer_id'];
				$rawVars['customer_name'] = $customerList[$rawVars['buyer_customer_id']];
				$vars = tldUtils::cleanupFormInput($rawVars);
				// Create ER
				$e = tldEquipment::createPAS($vars);
				if (is_string($e)) {
					$DEFAULT_ERROR[] = "ERROR: Problem creating ER...<br/>Reason: $e";
					break;
				}
				$eq = new tldEquipment($e);
				$log = $eq->addLogEntry(
					$user->getID(),
					"PAS#$e created through ER module"
				);
				if (is_string($log)) {
					$DEFAULT_ERROR[] = "ERROR: Problem adding logs...<br/>Reason: $log";
				}
				$body .= "PAS#$e created successfully!<br><a href='$php_self?m[0]=equipment&m[1]=view&id=$e'>Click here to see the PAS</a>";
				break;
			case 'add':
				$DEFAULT_TITLE .= "\Create a new ER";
				if (!$user->isInGroup(['role_PSM', 'role_PSE', 'role_PSA', 'gg_SUPPORT', 'gg_MIS', 'gg_ADMIN'])) {
					$DEFAULT_ERROR[] = 'ERROR: You do not have permission to create Equipment Records.';
					break;
				}

				$_factoryList = array_merge(['' => ''], tldLocation::getFactoryList('smartyOptionsLocationLocation'), tldEquipment::getUsedFactoryList());
				ksort($_factoryList);

				$_ctryList = ['' => '', 'To Be Defined' => 'To Be Defined'] + tldCountry::optionsAsNameName();
				$_modelList = ['' => ''] + tldModel::getList();
				$_erTypeList = ['' => ''] + tldType::getList('smartyOptions_Name');
				$_engTierTypes = ['' => ''] + tldList::optionsByListNameAsListItemListItem('list.engine.tiers');
				$_salesOrg = ['' => ''] + tldLocation::getSalesOrgList('smartyOptionsLocationLocation');
				$_salesRep = ['' => ''] + tldGroup::getUserListByMultipleGroup(['gg_SALES'], null, ['smartyOptionsTech_name' => true]);
				$_combinationList = ['' => ''] + tldEquipment::getCombinationModeList();
				$userEmail = $user->getEmail();
				$location = new tldLocation($user->getBUID());
				// Form
				$DEFAULT_TITLE .= "\Add";
				$form = new HTML_QuickForm('frmAdd');
				$form->addElement('hidden', 'm[0]', 'equipment');
				$form->addElement('hidden', 'm[1]', 'forms');
				$form->addElement('hidden', 'm[2]', 'add');
				$form->addElement('hidden', 'entered_by', $userEmail);
				$form->addElement('header', 'title', 'Create a New ER');
				$form->addElement('header', 'title', '<b>General</b>');
				$form->addElement('text', 'cust_asset_num', 'Customer Asset#');
				$form->addElement('text', 'esrid', 'ESR ID#');
				$form->addElement('text', 'date_entered', 'Date Entered (YYYY-MM-DD)', ['disabled' => 'disabled']);
				$form->addElement('textarea', 'odp_note', 'ODP Comment', ['wrap' => 'VIRTUAL', 'cols' => '40', 'rows' => '7']);
				$form->addElement('select', 'sales_org', 'Sales Organisation', $_salesOrg);
				$form->addElement('select', 'sales_rep', 'Sales Rep', $_salesRep);
				$form->addElement('header', 'title', '<b>Customer Details</b>');
                $form->addElement('select', 'buyer_customer_id', 'Customer Name (BUYER)', ['' => ''], ['class' => 'select2-customer', 'style' => 'width: 100%']);
                $form->addElement('select', 'customer_id', 'Customer Name (END USER)', ['' => ''], ['class' => 'select2-customer', 'style' => 'width: 100%']);
                $form->addElement('select', 'maintainer_customer_id', 'Customer Name (MAINTAINER)', ['' => ''], ['class' => 'select2-customer', 'style' => 'width: 100%']);
                $form->addElement('select', 'customer_name', 'Customer Name', ['' => ''], ['class' => 'select2-customer-name', 'style' => 'width: 100%']);
                $form->addElement('text', 'customer_contact', 'Customer Contact');
				$form->addElement('text', 'customer_ref', 'Customer Ref/PO Number');
				$form->addElement('text', 'agent_name', 'Agent Name');
				$form->addElement('select', 'airport_code', 'Airport Code', ['' => ''], ['class' => 'select2-airports airports-auto-update-country', 'style' => 'width: 100%']);
				$form->addElement('text', 'location_short', 'Location (Short)');
				$form->addElement('textarea', 'delivery_location', 'Delivery Location<br>(3 letter Airportcode, address)', ['wrap' => 'VIRTUAL', 'cols' => '40', 'rows' => '7']);
				$form->addElement('select', 'del_ctry', 'Delivered Country', $_ctryList);
				$form->addElement('text', 'date_arrived', 'Arrival Date (YYYY-MM-DD)');
				$form->addElement('header', 'title', '<b>Equipment Details</b>');
				$form->addElement('select', 'type', 'Equipment Type', $_erTypeList);
				$form->addElement('select', 'model', 'Equipment Model', $_modelList);
				$form->addElement('text', 'er_batch_qty', 'Batch Qty');
				$form->addElement('textarea', 'options_desc', 'Description of Options', ['wrap' => 'VIRTUAL', 'cols' => '40', 'rows' => '7']);
				$form->addElement('select', 'man_location', 'Manufacturer Location', $_factoryList);
				$form->addElement('select', 'eng_tier', 'Emission Rating', $_engTierTypes);
				$form->addElement('text', 'diml', 'Length (mm)');
				$form->addElement('text', 'dimw', 'Width (mm)');
				$form->addElement('text', 'dimh', 'Height (mm)');
				$form->addElement('text', 'dimk', 'Weight (KG)');
				$form->addElement('header', 'title', '<b>Warranty Details</b>');
				if ($user->isInGroup(['role_PSM', 'role_PSE', 'role_PSA', 'gg_ADMIN'])) {
					$form->addElement('select', 'warranty_length', 'Warranty Length (Months)', tldEquipment::getAllowedWarrantyLength());
					$form->addElement('text', 'date_warranty_end', 'Warranty End Date (YYYY-MM-DD)');
					$form->addElement('textarea', 'warranty_conditions', 'Special Warranty Conditions', ['wrap' => 'VIRTUAL', 'cols' => '40', 'rows' => '7']);
				} else {
					$form->addElement('text', 'warranty_length', 'Warranty Length (Months)', ['disabled' => 'disabled']);
					$form->addElement('text', 'date_warranty_end', 'Warranty End Date (YYYY-MM-DD)', ['disabled' => 'disabled']);
					$form->addElement('textarea', 'warranty_conditions', 'Special Warranty Conditions', ['disabled' => 'disabled', 'wrap' => 'VIRTUAL', 'cols' => '40', 'rows' => '7']);
				}
				$form->addElement('header', 'title', '<b>Manufacturing Section</b>');
				$form->addElement('textarea', 'mfg_comments', 'MFG Comments', ['wrap' => 'VIRTUAL', 'cols' => '40', 'rows' => '7']);
				$form->addElement('text', 't_pdno', 'Main Work Order#');
				$form->addElement('text', 'sls_orno', 'MFG SO#');
				$form->addElement('text', 'dt_commissioned', 'Commissioning Date (YYYY-MM-DD)');

                if ($user->isInGroup(['gg_ADMIN', 'role_PSA', 'role_PSM'])) {
                    $form->addElement('header', 'title', '<b>Service Section</b>');
                    $form->addElement('select', 'maintenance_contract_ref', 'FMS contract', ['' => ''] + tldEquipment::getFmsContractType());
                }

                $form->addElement('submit', 'btnSubmit', 'Submit');
				$fields = ['sales_org', 'man_location', 'buyer_customer_id', 'del_ctry', 'type', 'model', 'diml', 'dimw', 'dimh', 'dimk'];
				foreach ($fields as $field) {
					$form->addRule($field, 'Required', 'required');
				}
				$form->setDefaults(['er_batch_qty' => '1', 'date_entered' => date('Y-m-d'), 'date_warranty_end' => '0000-00-00', 'warranty_length' => '24', 'man_location' => $location->getShortName()]);

				if (!$form->validate()) {
                    $js = <<<HTML
<script type="application/javascript">
	document.addEventListener("DOMContentLoaded", function () {
        let countrySelect = $('select[name="del_ctry"]');
	    let countryDisplay = $('<span></span>').insertAfter(countrySelect); // Add an element to display the country name instead of countrySelect
	    let errorMessage = $('<div style="color: red; font-size: 0.9em; margin-top: 5px; display: none;"></div>').insertAfter(countrySelect); // Error message added under select
    
	    $('.airports-auto-update-country').on('change', function () {
	    	const selectedData = $(this).select2('data');
	    	if (selectedData.length > 0 && selectedData[0]) {
	    		let airportSelected = !!selectedData[0].id; //  null/undefined/empty
    
	    		if (airportSelected) {
	    			let countryName = selectedData[0].country_name || 'undefined_country_name';
	    			let countryOptionExists = countrySelect.find('option').filter(function() {
	    				return $(this).text().trim() === countryName || $(this).val() === countryName;
	    			}).length > 0;
    
	    			if (countryOptionExists) {
	    				countryDisplay.text(countryName).show();
	    				countrySelect.val(countryName).trigger('change'); 
	    				countrySelect.hide(); // Hides the select
	    				errorMessage.hide(); // Hides the error message if displayed
	    			} else {
	    				errorMessage.text('Country not found. Please select it manually.').show();
	    				countryDisplay.hide(); // Hide the text display
	    				countrySelect.show().val(''); // Reset the value and make the select visible
	    			}
	    		} else {
	    			countryDisplay.hide(); // Hide the text display
	    			countrySelect.show().val(''); // Reset the value
	    			errorMessage.hide(); // Hide the error message
	    		}
	    	}
	    });
	});
</script>
HTML;
                    $smarty->assign('html_head', $js);
					$body = $form->toHTML();
					break;
				}

				$vars = tldUtils::cleanupFormInput($form->exportValues());
                $vars['mfg_comments'] = trim(strip_tags($vars['mfg_comments']));
                $vars['sso_service'] = $vars['sales_org'];
                if (!is_numeric($vars['er_batch_qty']) || $vars['er_batch_qty'] < 1) {
					$DEFAULT_ERROR[] = 'ERROR: Batch quantity must be at least 1, please try again.';
					$body = $form->toHTML();
					break;
				}

				// Create ER
				$e = tldEquipment::create($vars);
				if (is_string($e)) {
					$DEFAULT_ERROR[] = "ERROR: Problem creating ER...<br/>Reason: $e";
					break;
				}
				$eq = new tldEquipment($e);
				$log = $eq->addLogEntry(
					$user->getID(),
					"ER#$e created through ER module"
				);
				if (is_string($log)) {
					$DEFAULT_ERROR[] = "ERROR: Problem adding logs...<br/>Reason: $log";
				}
				$body .= "ER#$e created successfully!<br><a href='$php_self?m[0]=equipment&m[1]=view&id=$e'>Click here to see the ER</a>";
				break;
			case 'byCustomer':
				if ($id) {
					$sess['equipment']['list'] = tldEquipment::byCustomer($id);
					$form = new tldReportMultiLevel(
						$sess['equipment']['list'],
						[
							'type', 'model',
						],
						[
							'id' => 'ID#',
							'type' => 'Equipment Type',
							'model' => 'Model',
							'sn' => 'Serial Number',
						],
						[
							'passField' => 'id',
							'title' => 'Step 2: Please select Equipment...',
							'url' => "$php_self?m[0]=equipment&m[1]=view&id=",
						]
					);
					$body = $form->fetch();
                    break;
				}
                $sess['equipment']['list'] = tldEquipment::getCustomerList();
                $form = new tldReportMultiLevel(
                    $sess['equipment']['list'],
                    ['firstChar'],
                    [
                        'customer_name' => 'Customer Name',
                    ],
                    [
                        'passField' => 'customer_name',
                        'title' => 'Step 1: Please select Customer...',
                        'url' => "$php_self?m[0]=equipment&m[1]=forms&m[2]=byCustomer&id=",
                    ]
                );
                $body = $form->fetch();
                break;
            case 'uploadFMSdata':
                $DEFAULT_TITLE .= "\CSV Upload";
                if (!$user->isInGroup(['role_SA'])) {
                    $DEFAULT_ERROR[] = 'ERROR: You do not have permission to update FMS data (limited to ROLE_SA).';
                    break;
                }
                // Excel File Upload
                $form = new HTML_QuickForm('frmUpload', 'post');
                $form->addElement('hidden', 'm[0]', 'equipment');
                $form->addElement('hidden', 'm[1]', 'forms');
                $form->addElement('hidden', 'm[2]', 'uploadFMSdata');
                $form->addElement('header', 'title', 'SIM card management mass upload');
                $form->addElement('file', 'file', 'CSV File (csv)');
                $form->addRule('file', 'Required', 'required');
                $form->addElement('submit', 'btnSubmit', 'Submit');
                $body = $form->toHTML();
                if (!$form->validate()) {
                    $body = $form->toHTML();
                    break;
                }
                $a = $form->exportValues();
                $file = $form->getElement("file");
                $file_array = $file->getValue();
                if ('' === $file_array['tmp_name']) {
                    $DEFAULT_ERROR[] = "ERROR: You didn't upload a file...";
                    break;
                }
                if ('text/csv' !== $file_array['type'] && 'csv' !== $extension = pathinfo($file_array['name'], PATHINFO_EXTENSION)) {
                    $DEFAULT_ERROR[] = "ERROR: The uploaded file in not a CSV, mime type is {$file_array['type']} and extension is $extension";
                    break;
                }
                $fields = [
                    'id',
                    'sn',
                    'maintenance_contract_ref',
                    'man_location',
                    'sales_org',
                    'user_customer_display',
                    'buyer_customer_display',
                    'airport_code',
                    'type',
                    'model',
                    'sim_serial',
                    'sim_check',
                    'phone_number_id',
                    'sim_brand',
                    'obu_serial',
                    'obu_brand',
                    'calculated_fms_end_use_date',
                    'sim_status',
                    'sim_ownership',
                ];
                $fieldsNumber = \count($fields); // 19
                $csv = new tldFileCSV($file_array['tmp_name']);
                $data = array_filter($csv->itsDetails, static function (array $line) use ($fieldsNumber) { return $fieldsNumber === \count($line); });
                $headers = array_shift($data);

                $data = array_map(static function(array $line) use ($fields) { return array_combine($fields, $line); }, $data);
                $statusMapping = ['Pause' => 'PAUSE', 'Terminate' => 'INACTIVE'];
                $allowedStatus = array_keys($statusMapping);
                $fmsContractType = tldEquipment::getFmsContractType();
                $updatedEquipmentIds = [];
                foreach ($data as $line) {
                    if ('OK, 20 digits' !== $line['sim_check']) {
                        $DEFAULT_ERROR[] = sprintf('Skipping line for ER S/N %s: SIM not properly formatted.', $line['sn']);
                        continue;
                    }
                    $status = ucfirst(strtolower(trim($line['sim_status'])));
                    $ownership = strtoupper(trim($line['sim_ownership']));
                    if ('' === $status && '' === $ownership) {
                        $DEFAULT_ERROR[] = sprintf('Skipping line for ER S/N %s: both SIM status and ownership are empty.', $line['sn']);
                        continue;
                    }

                    if ('' !== $status && !in_array($status, $allowedStatus, true)) {
                        $DEFAULT_ERROR[] = sprintf('Skipping line for ER S/N %s: SIM status %s is not allowed.', $line['sn'], $status);
                        continue;
                    }

                    if ('' !== $ownership && !in_array($ownership, $fmsContractType, true)) {
                        $DEFAULT_ERROR[] = sprintf('Skipping line for ER S/N %s: SIM ownership %s is not allowed.', $line['sn'], $ownership);
                        continue;
                    }
                    $updatedData = $fields = $logs = [];
                    $er = new tldEquipment($line['id']);
                    if ('' !== $ownership && $ownership !== $er->itsDetails['maintenance_contract_ref']) {
                        $fields[] = 'maintenance_contract_ref';
                        $fields[] = 'fms_end_use_date';
                        $updatedData['maintenance_contract_ref'] = $ownership;
                        $updatedData['fms_end_use_date'] = '2999-09-09';
                        $logs[] = sprintf("<li><b>FMS contract</b> updated from '%s' to '%s'</li>", $er->itsDetails['maintenance_contract_ref'], $ownership);
                        $logs[] = sprintf("<li><b>FMS end use date</b> updated from '%s' to '2999-09-09'</li>", $er->itsDetails['fms_end_use_date']);
                    }

                    if ('' !== $status && $statusMapping[$status] !== $er->itsDetails['sim_status']) {
                        $fields[] = 'sim_status';
                        $updatedData['sim_status'] = $statusMapping[$status];
                        $logs[] = sprintf("<li><b>SIM status</b> updated from '%s' to '%s'</li>", $er->itsDetails['sim_status'], $statusMapping[$status]);
                    }
                    if ($updatedData) {
                        $er->update($updatedData, $fields);
                        $er->addLogEntry($user->getId(), TldDatabase::escape(sprintf("ER updated via SIM card mass upload:<br><ul>%s</ul>", implode('', $logs))));
                        $updatedEquipmentIds[] = $er->getID();
                    }
                }

                if ($updatedEquipmentIds) {
                    $updatedEquipments = tldEquipment::byConstraints(sprintf('id IN (%s)', implode(',', $updatedEquipmentIds)));
                    $factoriesList = array_unique(array_column($updatedEquipments, 'man_location'));
                    $ssoList = array_unique(array_column($updatedEquipments, 'sales_org'));
                    $locationERPs = array_flip(tldLocation::getLocationList('smartyOptionsERPLocation'));

                    $roles = [];
                    foreach ($ssoList as $l) {
                        $roles[$locationERPs[$l]] = ['role_SAM', 'role_EVP'];
                    }
                    foreach ($factoriesList as $l) {
                        $roles[$locationERPs[$l]][] = 'role_COO';
                        $roles[$locationERPs[$l]][] = 'role_RCOO';
                    }

                    $to = [];
                    $report = new tldReportColumnar($updatedEquipments, [
                        'xItems' => [
                            'sn' => 'S/N',
                            'man_location' => 'Factory',
                            'sales_org' => 'SSO',
                            'type' => 'Type',
                            'model' => 'Model',
                            'user_customer_display' => 'Customer (user)',
                            'buyer_customer_display' => 'Customer (buyer)',
                            'maintenance_contract_ref' => 'FMS contract',
                            'sim_status' => 'SIM status',
                            'fms_end_use_date' => 'FMS end use date',
                        ],
                        'sortable' => 'NOPE',
                    ]);
                    $e = tldGroup::emailMultipleGroups($roles, 'noreply@tld-gse.com',
                     'FMS/Sim card mass upload',
                        "Please find bellow the ER list that were updated<br><br>".$report->fetch()
                    );
                }
                break;
        }
        break;
	case 'search':
		switch ($m[2]) {
			case 'bySN':
				if (empty($sn)) {
					$DEFAULT_ERROR[] = 'ERROR: Serial number required to do search.';
					break;
				}
				$rows = tldEquipment::bySN(trim($sn));
				// if only one entry, redirect to general view
				if (count($rows) == 1) {
					header("Location: $php_self?m[0]=equipment&m[1]=view&id={$rows[0]['id']}");
				}
				break;
			case 'byAsset':
				if (empty($asset)) {
					$DEFAULT_ERROR[] = 'ERROR: Asset number required to do search.';
					break;
				}
				$rows = tldEquipment::byConstraints(
					['cust_asset_num' => $asset],
					['orderBy' => 'location_short']
				);
				break;
		}
		if ($rows) {
			$sess['equipment']['list'] = $rows;
			$form = new tldReportMultiLevel(
				$rows,
				[
					'location_short',
					'type',
				],
				[
					'sn' => 'SN#',
					'cust_asset_num' => 'Asset#',
					'type' => 'Type',
					'user_customer_display' => 'Customer Name',
					'model' => 'Model',
					'location_short' => 'Location',
				],
				[
					'passField' => 'id',
					'title' => "$customer_name Equipment by LOCATION, TYPE",
					'url' => "$php_self?m[0]=equipment&m[1]=view&id=",
				]
			);
			$body .= $form->fetch();
		} else {
			$DEFAULT_ERROR[] = 'ERROR: No search results...';
		}
		break;
	case 'view':
		include 'equipment/view.inc.php';
		break;
	case 'cleanup':
		include_once 'equipment/equipment.cleanup.inc.php';
		break;
	case '3dims':
		$list = [
			'type' => 'Equipment Type',
			'T1.model' => 'Equipment Model',
			'entered_by' => 'Entered By',
			'customer_name' => 'Customer Name',
			'sales_org' => 'Sales Organization',
			'sales_rep' => 'Sales Rep',
			'man_location' => 'Manufacturer Location',
			'del_ctry' => 'Delivery Country',
            'sales_org' => 'SSO',
		];
		$parms = &$sess['equipment']['listing']['3dims'];

		switch ($m[2]) {
			case 'step3':
				$HAVING = '';
				if (isset($x) && $x !== 'ALL') {
					$HAVING = "	HAVING year_shipped='$x'";
				}
				$WHERE = '';
				if ($var1) {
					$w[] = "$dim1='$var1'";
				}
				if (isset($y) && $y !== 'ALL') {
					if ($dim2 === 'del_ctry') {
						$HAVING .= " AND $dim2='$y'";
					} else {
						$w[] = "$dim2='$y'";
					}
				}
				if (!empty($from)) {
					$from = tldDatabase::escape($from);
					$w[] = "date_shipped >= '$from'";
				}
				if (!empty($to)) {
					$to = tldDatabase::escape($to);
					$w[] = "date_shipped <= '$to'";
				}
				if (count($w)) {
					$WHERE = ' AND ' . implode(' AND ', $w);
				}

				$query = <<<EOF
SELECT T1.*,
	IF(T1.customer_id > 0,
	(SELECT customers.customer_name FROM customers WHERE customers.id=T1.customer_id),
	T1.customer_name) AS user_customer_display,
	(SELECT customers.customer_name FROM customers WHERE customers.id=T1.buyer_customer_id) AS buyer_customer_display,
	CASE
        WHEN date_shipped='0000-00-00'
            THEN 'NOT_SHIPPED'
        WHEN (YEAR(date_shipped) < YEAR(NOW())-2 OR date_shipped IS NULL)
            THEN CONCAT(YEAR(NOW())-3,' PRE')
        ELSE
            YEAR(date_shipped)
     END AS year_shipped,
    engs.serial AS eng_sn,
    dpfs.serial AS dpf_sn,
    scrs.serial AS scr_sn,
    CASE WHEN (T1.del_ctry = 'To Be Defined' OR T1.del_ctry = '') AND T1.airport_code IS NOT NULL AND T1.airport_code != '' THEN
      (SELECT c.name FROM airport_codes a LEFT JOIN countries c ON a.ctry_code_2=c.iso_code_2 WHERE a.type = 'Airport' AND a.airport_code=T1.airport_code LIMIT 1)
    ELSE
      del_ctry
    END AS 'del_ctry'
FROM service AS T1
    LEFT JOIN service_serials AS engs ON T1.id=engs.parent_id AND engs.component='ENGINE'
    LEFT JOIN service_serials AS dpfs ON T1.id=dpfs.parent_id AND dpfs.component='ENGINE, DPF'
    LEFT JOIN service_serials AS scrs ON T1.id=scrs.parent_id AND scrs.component='ENGINE, SCR'
WHERE T1.sn NOT REGEXP '^P[0-9]+$'
$WHERE
GROUP BY T1.id
$HAVING
EOF;
				$rows = tldUtils::getSqlToAssocArray($query);
				$sess['er']['list'] = $rows;
				switch ($out) {
					case 'xls':
						$xItems = [
							'id' => 'ID#',
							'sn' => 'SN#',
							'user_customer_display' => 'Customer (END USER)',
							'buyer_customer_display' => 'Customer (BUYER)',
							'type' => 'Type',
							'model' => 'Model',
							'airport_code' => 'Airport Code',
							'date_shipped' => 'Ship Date',
							'eng_sn' => 'Engine SN',
							'dpf_sn' => 'DPF SN',
							'scr_sn' => 'SCR SN',
                            'sales_org' => 'SSO'
						];
						$report = new tldXLS(
							$rows,
							[
								'xItems' => $xItems,
								'showTitles' => true,
							]
						);
						$report->out();
						exit;
						break;
				}
				if ($rows) {
					$url = '';
					if (!empty($from)) {
						$from = tldDatabase::escape($from);
						$url .= "&from=$from";
					}
					if (!empty($to)) {
						$WHERE .= " AND date_shipped <= '$to' ";
						$url .= "&to=$to";
					}
					$DEFAULT_MENU .= <<<EOF
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<a href="$php_self?m[0]=equipment&m[1]=3dims&m[2]=step3$url&dim1=$dim1&dim2=$dim2&var1=$var1&x=$x&y=$y&out=xls">XLS</a>
EOF;
					$report = new tldReportColumnar(
						$rows,
						[
							'xItems' => [
								'id' => 'ID#',
								'sn' => 'SN#',
								'user_customer_display' => 'Customer (END USER)',
								'buyer_customer_display' => 'Customer (BUYER)',
								'model' => 'Model',
								'airport_code' => 'Airport Code',
								'date_shipped' => 'Ship Date',
								'eng_sn' => 'Engine SN',
								'dpf_sn' => 'DPF SN',
								'scr_sn' => 'SCR SN',
                                'sales_org' => 'SSO',
							],
							'title' => "Equipment Count by $var1 {$list[$dim1]}, $y {$list[$dim2]}, $x Year Shipped",
							'links' => [
								'id' => "$php_self?m[0]=equipment&m[1]=view&id=",
							],
						]
					);
					$body .= $report->fetch();
				}
				break;
			case 'step2':
				$WHERE = " WHERE $dim1='$var1' ";

				$url = '';
				if (!empty($from)) {
					$from = tldDatabase::escape($from);
					$WHERE .= " AND date_shipped >= '$from' ";
					$url .= "&from=$from";
				}
				if (!empty($to)) {
					$to = tldDatabase::escape($to);
					$WHERE .= " AND date_shipped <= '$to' ";
					$url .= "&to=$to";
				}
				$year = date('Y');
				$SELECT = "$dim2 AS '$dim2'";
				$clonedDim2 = $dim2;
				if ($dim2 === 'del_ctry') {
					$SELECT = <<<EOF
CASE WHEN (del_ctry = 'To Be Defined' OR del_ctry = '') AND airport_code IS NOT NULL AND airport_code != '' THEN
    (SELECT c.name FROM airport_codes a LEFT JOIN countries c ON a.ctry_code_2=c.iso_code_2 WHERE a.type = 'Airport' AND a.airport_code=T1.airport_code LIMIT 1)
  ELSE
    del_ctry
  END AS 'del_ctry_guessed'
EOF;
					$clonedDim2 = 'del_ctry_guessed';
				}
				$query = <<<EOF
SELECT $SELECT,
CASE
    WHEN date_shipped='0000-00-00'
        THEN 'NOT_SHIPPED'
    WHEN (YEAR(date_shipped) < YEAR(NOW())-2 OR date_shipped IS NULL)
        THEN CONCAT(YEAR(NOW())-3,' PRE')
    ELSE
        YEAR(date_shipped)
 END AS year_shipped,
    count(*) AS num
FROM service AS T1
$WHERE
AND T1.sn NOT REGEXP '^P[0-9]+$'
GROUP BY $clonedDim2, year_shipped
EOF;
				$myrows = tldUtils::getSqlToAssocArray($query);
				$form = new tldMatrix(
					$myrows,
					'year_shipped', $clonedDim2, 'num',
					"$php_self?m[0]=equipment&m[1]=3dims&m[2]=step3$url&dim1=$dim1&dim2=$dim2&var1=$var1",
					"ER Count for $var1, {$list[$dim2]}, Year Shipped");
				$body .= $form->fetch();
				break;
			default:
				$form = new HTML_QuickForm('frm3dims', 'post');
				$form->addElement('header', 'title', 'Show equipment records by 3 dimensions');
				$form->addElement('hidden', 'm[0]', 'equipment');
				$form->addElement('hidden', 'm[1]', '3dims');
				$form->addElement('select', 'dim1', 'First Dimension', $list);
				$form->addElement('select', 'dim2', 'Second Dimension', $list);
				$form->addElement('text', 'start', 'Start Ship Date(YYYY-MM-DD)', ['class' => 'datepicker']);
				$form->addElement('text', 'end', 'End Ship Date((YYYY-MM-DD))', ['class' => 'datepicker']);
				$form->addElement('select', 'out', 'Format', ['' => '', 'csv' => 'csv']);
				$form->addElement('submit',
					'btnSubmit',
					'Submit');
				$form->setDefaults(['dim1' => 'type', 'dim2' => 'model']);
				if ($form->validate()) {
					$p = tldUtils::cleanupFormInput($form->exportValues());
					$dim1 = $p['dim1'];
					$dim2 = $p['dim2'];

					$startDate = DateTime::createFromFormat('Y-m-d', $p['start']);
					$errors = DateTime::getLastErrors();
					if (empty($p['start']) || !empty($errors['warning_count']) || !empty($errors['error_count'])) {
						$DEFAULT_ERROR[] = "ERROR: Start Date invalid. ('{$p['start']}')";
						$body = $form->toHTML();
						break;
					}

					$endDate = DateTime::createFromFormat('Y-m-d', $p['end']);
					$errors = DateTime::getLastErrors();
					if (empty($p['end']) || !empty($errors['warning_count']) || !empty($errors['error_count'])) {
						$DEFAULT_ERROR[] = "ERROR: End Date invalid. ('{$p['end']}')";
						$body = $form->toHTML();
						break;
					}
					$p['from'] = $p['start'];
					$p['to'] = $p['end'];
					$query = <<<EOF
        			SELECT $dim1 AS '$dim1',
        				count(*) as item_count
        			FROM service AS T1
        			WHERE T1.date_shipped>='{$p['from']}' AND T1.date_shipped<='{$p['to']}' AND T1.sn NOT REGEXP '^P[0-9]+$'
        			GROUP BY $dim1
EOF;
					$myrows = tldUtils::getSqlToAssocArray($query);
					switch ($out) {
						case 'csv':
							$report = new tldCSV(
								$myrows,
								[
									'xItems' => [
										$dim1 => $list[$dim1],
										'item_count' => 'Count',
									],
								]
							);
							$report->out();
							exit;
							break;
						default:
							$report = new tldReportColumnar(
								$myrows,
								[
									'xItems' => [
										$dim1 => $list[$dim1],
										'item_count' => 'Count',
									],
									'links' => [
										$dim1 => "$php_self?m[0]=equipment&m[1]=3dims&m[2]=step2&dim1=$dim1&dim2=$dim2&from={$p['from']}&to={$p['to']}&var1=",
									],
								]
							);
							$body .= $report->fetch();
					}
				} else {
					$body = $form->toHTML();
				}
		}
		break;
	case 'tldLinkBySSO':
		if ($m[2] === 'step2' && null !== $y) {
			$DEFAULT_MENU .= <<<EOF
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<a href="$php_self?m[0]=equipment&m[1]=tldLinkBySSO&m[2]=step2&y=$y&out=csv">CSV</a>
EOF;

			$sso = TldDatabase::escape($y);
			$WHERE = $sso !== 'ALL' ? " AND sales_org = '$sso' " : '';
            $WHERE .= " AND service.maintenance_contract_ref <> ''";
			$query = <<<SQL
SELECT
       service.id,
       service.sn,
       service.model as unit_model,
       service.sales_org,
       service.man_location,
       service.dgt_act,
       service.maintenance_contract_ref,
       (SELECT customers.customer_name FROM customers WHERE customers.id = service.buyer_customer_id) AS buyer_customer_display,
       IF(service.customer_id > 0, (SELECT customers.customer_name FROM customers WHERE customers.id = service.customer_id), customer_name) AS user_customer_display,
       (SELECT GROUP_CONCAT(brand ORDER BY id SEPARATOR '\n') FROM service_serials WHERE service_serials.parent_id=service.id AND service_serials.component = 'SIM CARD, LINK') AS brand,
       (SELECT GROUP_CONCAT(model ORDER BY id SEPARATOR '\n') FROM service_serials WHERE service_serials.parent_id=service.id AND service_serials.component = 'SIM CARD, LINK') AS model,
       (SELECT GROUP_CONCAT(serial ORDER BY id SEPARATOR '\n') FROM service_serials WHERE service_serials.parent_id=service.id AND service_serials.component = 'SIM CARD, LINK') AS serial,
       (SELECT GROUP_CONCAT(serial ORDER BY id SEPARATOR '\n') FROM service_serials WHERE service_serials.parent_id=service.id AND service_serials.component = 'OBU, LINK') AS obu_serial
FROM service
INNER JOIN service_serials ON service.id = service_serials.parent_id AND service_serials.component IN ('OBU, LINK', 'SIM CARD, LINK')
$WHERE
GROUP BY service.id
SQL;
			$rows = tldUtils::getSqlToAssocArray($query);

			$xItems = [
				'sn' => 'Serial Number',
				'sales_org' => 'SSO',
				'man_location' => 'Factory',
				'unit_model' => 'Unit Model',
				'dgt_act' => 'Actual GT date',
				'buyer_customer_display' => 'Customer (BUYER)',
				'user_customer_display' => 'Customer (END USER)',
				'brand' => 'Brand',
                'model' => 'SIM CARD Model',
                'serial' => 'SIM CARD Serial',
                'obu_serial' => 'OBU Serial',
                'maintenance_contract_ref' => 'FMS contract'
			];

			if ('csv' === $out) {
				$report = new tldCSV(
					$rows,
					[
						'xItems' => $xItems,
						'showTitles' => true,
					]
				);
				$report->out();
				exit;
			}
			$report = new tldReportColumnar(
                $rows,
				[
					'xItems' => $xItems,
					'showNumberOfRows' => true,
					'title' => 'Link ER Count' . ($sso !== 'ALL' ? " for $sso" : ''),
					'links' => [
						'id' => "$php_self?m[0]=equipment&m[1]=view&id=",
					],
				]
			);
			$body .= $report->fetch();
			break;
		}
		$query = <<<SQL
SELECT service.sales_org, COUNT(distinct service.id) cnt
FROM service_serials
INNER JOIN service ON service.id = service_serials.parent_id
WHERE component IN ('OBU, LINK', 'SIM CARD, LINK')
GROUP BY service.sales_org
SQL;
		$rows = tldUtils::getSqlToAssocArray($query);
		$report = new tldMatrix(
			$rows,
			'', 'sales_org', 'cnt',
			"$php_self?m[0]=equipment&m[1]=tldLinkBySSO&m[2]=step2",
			'Link ER Count by SSO',
			['doNotShowYTotals' => true]
		);
		$body .= $report->fetch();

		break;
	case 'tldLinkByFactory':
		if ($m[2] === 'step2' && null !== $y) {
			$DEFAULT_MENU .= <<<EOF
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<a href="$php_self?m[0]=equipment&m[1]=tldLinkByFactory&m[2]=step2&y=$y&out=csv">CSV</a>
EOF;

			$factory = TldDatabase::escape($y);
			$WHERE = $factory !== 'ALL' ? " AND man_location = '$factory' " : '';
			$query = <<<SQL
SELECT
       service.id,
       service.sn,
       service.model as unit_model,
       service.sales_org,
       service.man_location,
       service.dgt_act,
       (SELECT customers.customer_name FROM customers WHERE customers.id = service.buyer_customer_id) AS buyer_customer_display,
       IF(service.customer_id > 0, (SELECT customers.customer_name FROM customers WHERE customers.id = service.customer_id), customer_name) AS user_customer_display,
       (SELECT GROUP_CONCAT(brand ORDER BY id SEPARATOR '\n') FROM service_serials WHERE service_serials.parent_id=service.id AND service_serials.component = 'SIM CARD, LINK') AS brand,
       (SELECT GROUP_CONCAT(model ORDER BY id SEPARATOR '\n') FROM service_serials WHERE service_serials.parent_id=service.id AND service_serials.component = 'SIM CARD, LINK') AS model,
       (SELECT GROUP_CONCAT(serial ORDER BY id SEPARATOR '\n') FROM service_serials WHERE service_serials.parent_id=service.id AND service_serials.component = 'SIM CARD, LINK') AS serial,
       (SELECT GROUP_CONCAT(serial ORDER BY id SEPARATOR '\n') FROM service_serials WHERE service_serials.parent_id=service.id AND service_serials.component = 'OBU, LINK') AS obu_serial
FROM service
INNER JOIN service_serials ON service.id = service_serials.parent_id AND service_serials.component IN ('OBU, LINK', 'SIM CARD, LINK')
$WHERE
GROUP BY service.id
SQL;
			$rows = tldUtils::getSqlToAssocArray($query);
			$xItems = [
				'sn' => 'Serial Number',
				'sales_org' => 'SSO',
				'man_location' => 'Factory',
				'unit_model' => 'Unit Model',
				'dgt_act' => 'Actual GT date',
				'buyer_customer_display' => 'Customer (BUYER)',
				'user_customer_display' => 'Customer (END USER)',
				'brand' => 'Brand',
				'model' => 'SIM CARD Model',
				'serial' => 'SIM CARD Serial',
                'obu_serial' => 'OBU Serial',
			];

			if ('csv' === $out) {
				$report = new tldCSV(
					$rows,
					[
						'xItems' => $xItems,
						'showTitles' => true,
					]
				);
				$report->out();
				exit;
			}
			$report = new tldReportColumnar(
				$rows,
				[
					'xItems' => $xItems,
					'showNumberOfRows' => true,
					'title' => 'TLD ER Count' . ($factory !== 'ALL' ? " for $factory" : ''),
					'links' => [
						'id' => "$php_self?m[0]=equipment&m[1]=view&id=",
					],
				]
			);
			$body .= $report->fetch();
			break;
		}
		$query = <<<SQL
SELECT service.man_location, COUNT(distinct service.id) cnt
FROM service_serials
INNER JOIN service ON service.id = service_serials.parent_id
WHERE component IN ('OBU, LINK', 'SIM CARD, LINK')
GROUP BY service.man_location
SQL;
		$rows = tldUtils::getSqlToAssocArray($query);
		$report = new tldMatrix(
			$rows,
			'', 'man_location', 'cnt',
			"$php_self?m[0]=equipment&m[1]=tldLinkByFactory&m[2]=step2",
			'Link ER Count by Factory',
			['doNotShowYTotals' => true]
		);
		$body .= $report->fetch();
		break;
	case 'charts':
		switch ($m[2]) {
			case 'equipmentCountByType':
				$query = <<<EOF
			SELECT type, count( * ) AS cnt
			FROM service
			GROUP BY type
EOF;
				$rows = tldUtils::getSqlToAssocArray($query);
				//user row count to determine graph size
				$Graph =& Image_Graph::factory('graph', [790, 600]);
				$Plotarea =& $Graph->addNew('plotarea', [
					'Image_Graph_Axis_Category',
					'Image_Graph_Axis',
					'horizontal',
				]);
				$AxisX =& $Plotarea->getAxis(IMAGE_GRAPH_AXIS_X);
				$AxisX->setTitle('Equipment Type', 'vertical');
				$AxisY =& $Plotarea->getAxis(IMAGE_GRAPH_AXIS_Y);
				$AxisY->setTitle('Number of Equipment');
				$Dataset =& Image_Graph::factory('dataset');
				foreach ($rows as $row) {
					$Dataset->addPoint($row['type'], $row['cnt']);
				}
				$Plot =& $Plotarea->addNew('bar', [&$Dataset]);
				$Plot->setFillColor('#FF000');
				$Graph->add(Image_Graph::factory('title', ['Equipment Record Count by TYPE', 24]));
				$Graph->done();
				exit;
				break;
			case 'byDim':
				$form = new HTML_QuickForm('frmByFactgory', 'post');
				$form->addElement('header', 'title', 'Graph equipment records by...');
				$form->addElement('hidden', 'm[0]', 'equipment');
				$form->addElement('hidden', 'm[1]', 'charts');
				$form->addElement('hidden', 'm[2]', 'byDim');
				$list = ['type' => 'Equipment Type', 'model' => 'Equipment Model', 'entered_by' => 'Entered By',
					'customer_name' => 'Customer Name', 'sales_org' => 'Sales Organization', 'sales_rep' => 'Sales Rep'];
				$form->addElement('select', 'dim', 'Dimension', $list);
				$form->addElement('text', 'start', 'Start date');
				$form->addElement('text', 'end', 'End date');
				$form->addRule('start', 'This is required', 'required');
				$form->addRule('end', 'This is required', 'required');
				$form->setDefaults(['start' => '1900-01-01',
					'end' => date('Y-m-d')]);
				$form->addElement('submit', 'btnSubmit', 'Submit');
				if ($form->validate()) {
					# If the form validates then freeze the data
					$form->freeze();
					$query = <<<EOF
					SELECT $dim, count( * ) AS cnt
					FROM service
					WHERE date_entered BETWEEN '$start' AND '$end'
					GROUP BY $dim
EOF;
					$rows = tldUtils::getSqlToAssocArray($query);
					//user row count to determine graph size
					$numRows = count($rows);
					if ($numRows > 30) {
						$height = count($rows) * 15;
					} else {
						$height = 600;
					}

					$Graph =& Image_Graph::factory('graph', [790, $height]);
					$Plotarea =& $Graph->addNew('plotarea', [
						'Image_Graph_Axis_Category',
						'Image_Graph_Axis',
						'horizontal',
					]);
					$AxisX =& $Plotarea->getAxis(IMAGE_GRAPH_AXIS_X);
					$AxisX->setTitle($list[$dim], 'vertical');
					$AxisY =& $Plotarea->getAxis(IMAGE_GRAPH_AXIS_Y);
					$AxisY->setTitle('Number of Equipment');
					$TITLE = "Equipment Record Count by ${list[$dim]}";
					$Dataset =& Image_Graph::factory('dataset');
					foreach ($rows as $row) {
						$Dataset->addPoint($row[$dim], $row['cnt']);
					}
					$Plot =& $Plotarea->addNew('bar', [&$Dataset]);
					$Plot->setFillColor('#FF000');
					$Graph->add(Image_Graph::factory('title', [$TITLE, 24]));
					$Graph->done();
					exit;
				} else {
					$body = $form->toHTML();
				}
				break;
		}
		break;
	case 'multiLevel':
		switch ($m[2]) {
			case 'shippedWithoutManual':
				$query = <<<EOF
select t1.*,
	IF(t1.customer_id > 0,
	(SELECT customers.customer_name FROM customers WHERE customers.id=t1.customer_id),
	t1.customer_name) AS user_customer_display,
	(SELECT customers.customer_name FROM customers WHERE customers.id=t1.buyer_customer_id) AS buyer_customer_display
from service AS t1 LEFT JOIN service_serials AS t2 ON t1.id=t2.parent_id AND t2.component='MANUAL'
WHERE t2.serial IS NULL AND YEAR(t1.date_shipped) BETWEEN YEAR(NOW())-1 AND YEAR(NOW())
EOF;
				$rows = tldUtils::getSqlToAssocArray($query);
				$_title = 'Equipment Shipped Without Online Manual (previous and current year)';
				break;
		}
		if ($rows) {
			$report = new tldReportMultiLevel(
				$rows,
				[
					'man_location',
					'type',
				],
				[
					'id' => 'ID#',
					'sn' => 'SN#',
					'user_customer_display' => 'Customer (END USER)',
					'buyer_customer_display' => 'Customer (BUYER)',
					'model' => 'Model',
					'man_location' => 'Man Location',
					'date_shipped' => 'Ship Date',
				],
				[
					'passField' => 'id',
					'title' => $_title,
					'url' => "$php_self?m[0]=equipment&m[1]=view&id=",
					'showItemNumbers' => true,
				]
			);
			$body .= $report->fetch();
		}
		break;
	case 'customerCleanUp':
		$DEFAULT_TITLE .= "\Customer Clean UP";

		// ALL/10 years Filter form
		$formFilter = new HTML_QuickForm('frmFilter', 'post', null, null, null, true);
		$formFilter->addElement('hidden', 'm[0]', $m[0]);
		$formFilter->addElement('hidden', 'm[1]', $m[1]);
		$formFilter->addElement('hidden', 'm[2]', $m[2]);
		$formFilter->addElement('header', 'frmTitle', 'Filter');
		$formFilter->addElement('select', 'period', 'By period', ['all' => 'All years', '10' => 'Last 10 Years']);
		$formFilter->addElement('select', 'state', 'State', ['all' => 'All states', 'active' => 'Without retired']);
		$formFilter->addElement('submit', 'btnSubmit', 'Apply');

		// On validation
		if ($formFilter->validate()) {
			$sess['er']['cleanup']['filter'] = $formFilter->exportValues();
		}

		$filterConstraints = [];
		// if filter apply
		if (!empty($sess['er']['cleanup']['filter'])) {
			$vars = tldUtils::cleanupFormInput($sess['er']['cleanup']['filter']);
			if ($vars['period'] !== 'all') {
				$filterConstraints[] = "(YEAR(date_shipped)>YEAR(NOW())-{$vars['period']} OR date_shipped IS NULL)";
				$filterTitle = " - Not shipped or shipped < {$vars['period']} years";
			} else {
				$filterTitle = ' - shipped all years';
			}
			if ($vars['state'] !== 'all') {
				$filterConstraints[] = "state LIKE '{$vars['state']}'";
				$filterTitle .= ', without retired';
			} else {
				$filterTitle .= ', all states';
			}
		} else {
			// Default
			$filterTitle = ' - shipped all years, all states';
		}
		if (!count($filterConstraints)) {
			$filterConstraints[] = '1=1';
		}
		$filterConstraints = implode(' AND ', $filterConstraints);

		switch ($m[2]) {
			case 'matrix':
				$body .= $formFilter->toHTML();
				$form = new tldMatrix(
					tldCustomerCleanup::countBySSOERP($filterConstraints),
					'man_location', 'sales_org', 'num',
					"$php_self?m[0]=equipment&m[1]=customerCleanUp&m[2]=bySSOERP",
					"ER without customer Buyer/User by Factory, SSO $filterTitle"
				);
				$body .= $form->fetch();
				break;
			case 'bySSOERP':
				$xItems = [
					'id' => 'Edit ER',
					'sn' => 'ER SN',
					'model' => 'Model',
					'man_location' => 'Factory',
					'sales_org' => 'SSO',
					'airport_code' => 'APC',
					'date_shipped' => 'Shipped',
					'customer_name' => 'Customer name (old field)',
					'buyer_cust' => 'Buyer',
					'user_cust' => 'User',
					'customer_found' => 'Possible customers matching',
				];
				// Output ------>
				switch ($out) {
					case 'csv':
						$report = new tldCSV(
							$sess['er']['listing'],
							[
								'xItems' => $xItems,
								'showTitles' => true,
							]
						);
						$report->out();
						exit;
						break;
					default:
						// Get Data
						$sso = TldDatabase::escape($y);
						$erp = TldDatabase::escape($x);
						// Prepare constraints
						$WHERE = '';
						if ($sso === 'NO SSO') {
							$WHERE .= " AND service.sales_org LIKE '' ";
						} elseif (!empty($sso) && $sso !== 'ALL') {
							$WHERE .= " AND service.sales_org LIKE '$sso' ";
						}
						if ($erp === 'NO FACTORY') {
							$WHERE .= " AND service.man_location LIKE '' ";
						} elseif (!empty($erp) && $erp !== 'ALL') {
							$WHERE .= " AND service.man_location LIKE '$erp' ";
						}
						if (!empty($filterConstraints)) {
							$WHERE .= " AND $filterConstraints ";
						}
						// Get list of ER with no customer ID
						$query = <<<EOF
SELECT
    service.*,
    (SELECT customer_name FROM customers WHERE id=service.buyer_customer_id) AS buyer_cust,
    (SELECT customer_name FROM customers WHERE id=service.customer_id) AS user_cust,
    (SELECT GROUP_CONCAT('ID#',id,' ',customer_name,' | ')
        FROM customers WHERE customer_name
        LIKE CONCAT( '%', service.customer_name, '%' ) ORDER BY id
    ) AS customer_found
FROM
    service
WHERE
    (buyer_customer_id=0 OR buyer_customer_id IS NULL OR customer_id=0 OR customer_id IS NULL)
    $WHERE
ORDER BY
	date_shipped
EOF;
						$rows = tldUtils::getSqlToAssocArray($query);
						$sess['er']['listing'] = $rows;

						// Display ------------------------------------------->

						$DEFAULT_MENU .= <<<EOF
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<a href="$php_self?m[0]=equipment&m[1]=customerCleanUp&m[2]=bySSOERP&out=csv">CSV</a>
EOF;
						$report = new tldReportColumnar(
							$sess['er']['listing'],
							[
								'xItems' => $xItems,
								'title' => "ER Search and customer name cross search $filterTitle",
								'sortable' => 'N',
								'links' => [
									'id' => [
										'url' => '/en/private/product_support/equipment/equipment_admin.php?mode=form_edit&form_type=main_tpl',
										'params' => ['id' => 'id'],
										'target' => '_blank',
									],
								],
							]
						);
						$body .= $report->fetch();
						break;
				}
				break;
		}
		break;
	case 'reports':
		$DEFAULT_TITLE .= '/Reports';

		switch ($m[2]) {
			case 'byBuByComponents':
				// Listing
				$FactoryList = tldEquipment::getUsedFactoryList();
                global $kernel;
                try {
                    $container = $kernel->getContainer();
                    $client = $container->get(Client::class);
                } catch (\Exception $e) {
                    $DEFAULT_ERROR[] = 'Client could not be fetched.';
                    break;
                }

                $formattedComponents = [];
                try {
                    $components = $client->get('equipment_serial_components', ['query' => ['order' => ['name' => 'ASC']]]);
                    foreach ($components['hydra:member'] as $component) {
                        $formattedComponents[$component['name']] = $component['name'];
                    }
                } catch (ClientException $exception) {
                    $DEFAULT_ERROR[] = 'Components could not be fetched.';
                    break;
                }

				$ssoList = tldLocation::getSalesOrgList('smartyOptionsLocationLocation');
				$CustomerList = tldCustomer::getList('smartyOptions');
				$CustomerNameList = tldCustomer::getList('smartyOptionsCust_name');
				$ModelList = tldUtils::optionsByKeyValue(tldEquipment::getUsedModels(), 'model', 'model');
				$TypeList = tldType::getTypes('en', 'smartyOptions');
				// Form
				$form = new HTML_QuickForm('frm', 'post');
				$form->addElement('hidden', 'm[0]', $m[0]);
				$form->addElement('hidden', 'm[1]', $m[1]);
				$form->addElement('hidden', 'm[2]', $m[2]);
				$form->addElement('header', 'frmTitle', 'Equipment Components by BU');
				$form->addElement('select', 'buyer_customer_id', 'Customer (BUYER)', ['' => ''] + $CustomerList);
				$form->addElement('select', 'customer_id', 'Customer (END USER)', ['' => ''] + $CustomerList);
				$form->addElement('select', 'customer_name', 'Customer Name', ['' => ''] + $CustomerNameList);
				$form->addElement('select', 'model', 'Model', ['' => ''] + $ModelList);
				$form->addElement('select', 'type', 'Type', ['' => ''] + $TypeList);
				$form->addElement('select', 'man_location', 'Factory', ['' => ''] + $FactoryList);
				$form->addElement('select', 'sales_org', 'SSO', ['' => ''] + $ssoList);
				$ams =& $form->addElement(
					'advmultiselect', 'component', null,
                    $formattedComponents,
					['size' => 10, 'class' => 'pool', 'style' => 'width:200px;']
				);
				$ams->setLabel(['Select components:', 'Components list', 'Components to use']);
				$ams->setButtonAttributes('add', ['value' => '-->>', 'class' => 'inputCommand']);
				$ams->setButtonAttributes('remove', ['value' => '<<--', 'class' => 'inputCommand']);
				$form->addElement('select', 'out', 'Format', ['web' => 'web', 'csv' => 'csv']);
				$form->addElement('submit', 'btnSubmit', 'Submit');
				$form->addRule('man_location', 'Required', 'required');
				$form->addRule('component', 'Required', 'required');

				if (!$form->validate()) {
					$body = $form->toHTML();
					break;
				}

				$vars = tldUtils::cleanupFormInput($form->exportValues());
				// Default columns
				$xItems = [
					'id' => 'ER#',
					'sn' => 'SN#',
					'model' => 'Model',
					'user_customer_display' => 'Customer (END USER)',
					'buyer_customer_display' => 'Customer (BUYER)',
					'airport_code' => 'APC',
					'dgt_act' => 'GT date',
					'date_shipped' => 'Shipped',
				];
				$selectExtra = null;
				$fromExtra = null;
				foreach ($vars['component'] as $k => $compName) {
					// xItems
					$xItems["brand_$k"] = "$compName brand";
					$xItems["model_$k"] = "$compName model";
					$xItems["serial_$k"] = "$compName serial";
					// Query select
					$selectExtra .= <<<EOF
,serials_$k.brand AS brand_$k
,serials_$k.model AS model_$k
,serials_$k.serial AS serial_$k
EOF;
					// Query from
					$fromExtra .= <<<EOF
 LEFT JOIN service_serials AS serials_$k ON er.id = serials_$k.parent_id AND serials_$k.component LIKE '$compName'
EOF;
				}
				// Construct Query
				$fieldsToSearch = ['buyer_customer_id', 'customer_id', 'customer_name', 'model', 'type', 'man_location', 'sales_org'];
				$a = [];
				foreach ($vars as $field => $var) {
					if (empty($var) || !in_array($field, $fieldsToSearch, true)) {
						continue;
					}
					$a[$field] = $var;
				}
				$HAVING = is_array($a) ? tldUtils::constructWhere($a) : $a;

				$query = <<<EOF
SELECT
	er.*,
	IF(er.customer_id > 0,
    	(SELECT customers.customer_name FROM customers WHERE customers.id=er.customer_id),
    	er.customer_name
    ) AS user_customer_display,
	(SELECT customers.customer_name FROM customers
		WHERE customers.id=er.buyer_customer_id
	) AS buyer_customer_display
    $selectExtra
FROM service AS er
	$fromExtra
WHERE
	er.man_location LIKE '{$vars['man_location']}'
HAVING $HAVING
ORDER BY
	er.id
EOF;
				$rows = tldUtils::getSqlToAssocArray($query);

				// Display ------------------->
				switch ($out) {
					case 'csv':
						$report = new tldCSV(
							$rows,
							[
								'xItems' => $xItems,
								'showTitles' => true,
							]
						);
						$report->out();
						exit;
						break;
					default:
						$report = new tldReportColumnar(
							$rows,
							[
								'xItems' => $xItems,
								'title' => 'ER component details',
								'links' => [
									'id' => "$php_self?m[0]=equipment&m[1]=view&id=",
								],
							]
						);
						$body .= $report->fetch();
						break;
				}
				break;
			case 'rpt_by_equip_component':
				$query = <<<EOF
SELECT T1.id, T1.customer_name, T1.type, T1.model, T1.date_shipped, T1.location_short, T1.sn, serialsA.brand AS eng_brand, serialsA.model AS eng_model, serialsA.serial AS eng_sn, serialsB.brand AS box_brand, serialsB.model AS box_model, serialsB.serial AS box_sn, serialsC.brand AS comp_brand, serialsC.model AS comp_model, serialsC.serial AS comp_sn, serialsD.brand AS comp_brand, serialsD.model AS comp_model, serialsD.serial AS genset_sn,
	IF(T1.customer_id > 0,
	(SELECT customers.customer_name FROM customers WHERE customers.id=T1.customer_id),
	T1.customer_name) AS user_customer_display,
	(SELECT customers.customer_name FROM customers WHERE customers.id=T1.buyer_customer_id) AS buyer_customer_display
FROM service AS T1
LEFT JOIN service_serials AS serialsA ON T1.id = serialsA.parent_id
AND serialsA.component =  'ENGINE'
LEFT JOIN service_serials AS serialsB ON T1.id = serialsB.parent_id
AND serialsB.component =  'GEAR BOX'
LEFT JOIN service_serials AS serialsC ON T1.id = serialsC.parent_id
AND serialsC.component =  'COMPRESSOR'
LEFT JOIN service_serials AS serialsD ON T1.id = serialsD.parent_id
AND serialsD.component =  'GENERATOR SET'
EOF;
				$report = new tldCSV(tldUtils::getSqlToAssocArray($query),
					[
						'xItems' => [
							'id' => 'Equip ID',
							'type' => 'Equipment Type',
							'model' => 'Model',
							'date_entered' => 'Creation',
							'date_shipped' => 'Shipped',
							'user_customer_display' => 'Customer (END USER)',
							'buyer_customer_display' => 'Customer (BUYER)',
							'location_short' => 'Location',
							'sn' => 'Serial Number',
							'eng_brand' => 'Engine Brand',
							'eng_model' => 'Engine Model',
							'eng_sn' => 'Engine SN',
							'box_brand' => 'Gearbox Brand',
							'box_model' => 'Gearbox Model',
							'box_sn' => 'Gearbox SN',
							'comp_brand' => 'Compressor Brand',
							'comp_model' => 'Compressor Model',
							'comp_sn' => 'Compressor SN',
							'genset_brand' => 'Genset Brand',
							'genset_model' => 'Genset Model',
							'genset_sn' => 'Genset SN',
						],
						'showTitles' => true,
					]
				);
				$report->out();
				exit;
				break;
			default:
				$body .= $smarty->fetch("$PATH/equipment/homepage.reports.tpl");
				break;
		}
		break;
	case 'listing':
		include 'equipment/listing.inc.php';
		break;
	case 'dash':
		include 'equipment/dash.inc.php';
		break;
	case 'help':
		$DEFAULT_TITLE .= "\Help";
		$smarty->assign('DMS_URL', $DMS_URL);
		$body = $smarty->fetch("$PATH/equipment/help.equipment.tpl");
		break;
    case 'customers_ajax':
        $searchParam = $_GET['query'] ?? '';
        $customers = tldCustomer::simpleSearch($searchParam);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(mb_convert_encoding($customers, 'UTF-8', 'ASCII,ISO-8859-1,CP936,UTF-8'));
        exit;
    case 'airports_ajax':
        $searchParam = $_GET['query'] ?? '';
        $customers = tldAirport::search($searchParam);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(mb_convert_encoding($customers, 'UTF-8', 'ASCII,ISO-8859-1,CP936,UTF-8'));
        exit;
    default:
		$body .= $smarty->fetch("$PATH/equipment/homepage.equipment.tpl");
		$sess['equipment']['list'] = tldEquipment::byLatest(10, ['mode' => 'shipped']);
		$report = new tldReportColumnar(
			$sess['equipment']['list'],
			[
				'xItems' => [
					'id' => 'ID#',
					'sn' => 'SN#',
					'man_location' => 'Factory',
					'user_customer_display' => 'Customer (END USER)',
					'buyer_customer_display' => 'Customer (BUYER)',
					'maintainer_customer_display' => 'Customer (MAINTAINER)',
					'del_ctry' => 'Country, Delivery',
					'model' => 'Model',
					'airport_code' => 'Airport Code',
					'date_shipped' => 'Ship Date',
				],
				'title' => 'Recently Shipped Equipment',
				'links' => [
					'id' => "$php_self?m[0]=equipment&m[1]=view&id=",
				],
			]
		);
		$body .= $report->fetch();
}
