<?php

use ApiBundle\Client;

switch ($m[2]) {
	case 'byCreateFactoryDate':
		$DEFAULT_TITLE .= "\SOR Line by Create Factory SO Date";
		$form = new HTML_QuickForm('frmByNum', 'post');
		$form->addElement('hidden', 'm[0]', 'sol');
		$form->addElement('hidden', 'm[1]', 'listing');
		$form->addElement('hidden', 'm[2]', 'byCreateFactoryDate');
		$form->addElement('header', 'title', 'SOL Create Factoy SO Period');
		$form->addElement('text', 'x', 'Start Date(YYYY-MM-DD)', ['class' => 'datepicker']);
		$form->addElement('text', 'y', 'End Date(YYYY-MM-DD)', ['class' => 'datepicker']);
		$form->addElement('select', 'bu', 'TLD Factory BU',
			['' => '', '28' => 'TLD JST'] + tldLocation::getFactoryList('smartyOptionsIDLocation')
			+ tldLocation::getSalesOrgList('smartyOptionsIDLocation'));
		$form->addElement('submit', 'btnSubmit', 'Submit');
		$form->setDefaults(['start' => date('Y-m-d', strtotime('first day of -3 months')), 'end' => date('Y-m-d', strtotime('last day of last month'))]);
		$form->addRule('start', 'Required', 'required');
		$form->addRule('end', 'Required', 'required');
		$body .= $form->toHTML();
		break;
	case 'byERPPriorInProgress':
		$DEFAULT_TITLE .= "\SOR Line Prior to IN_PROGRESS by ERP";
		$form = new HTML_QuickForm('frmByNum', 'post');
		$form->addElement('hidden', 'm[0]', 'sol');
		$form->addElement('hidden', 'm[1]', 'listing');
		$form->addElement('hidden', 'm[2]', 'byERPPriorInProgress');
		$form->addElement('header', 'title', 'SOR Line by ERP');
		$form->addElement('select', 'erp', 'Factory',
			['28' => 'TLD JST'] + tldLocation::getFactoryList('smartyOptionsLocationLocation')
			+ tldLocation::getSalesOrgList('smartyOptionsLocationLocation')
		);
		$form->addElement('submit', 'btnSubmit', 'Submit');
		$body .= $form->toHTML();
		break;
	case 'byNum':
		$DEFAULT_TITLE .= "\SOR Line by Number";
		$form = new HTML_QuickForm('frmByNum', 'post');
		$form->addElement('hidden', 'm[0]', 'sol');
		$form->addElement('hidden', 'm[1]', 'view');
		$form->addElement('header', 'title', 'SOR Line Number');
		$form->addElement('text', 'id', 'SOR Line#');
		$form->addElement('submit', 'btnSubmit', 'Submit');
		$body .= $form->toHTML();
		break;
	case 'bySSOERPStatus':
		$DEFAULT_TITLE .= "\SOR Line by SSO,ERP,Status";
		$form = new HTML_QuickForm('frmByNum', 'post');
		$form->addElement('hidden', 'm[0]', 'sol');
		$form->addElement('hidden', 'm[1]', 'listing');
		$form->addElement('hidden', 'm[2]', 'bySSOERPStatus');
		$form->addElement('header', 'title', 'SOR Line by SSO,ERP,Status');
		$form->addElement('select', 'x', 'SSO', ['ALL' => 'ALL']
			+ tldLocation::getSalesOrgList('smartyOptionsLocationLocation')
		);
		$form->addElement('select', 'y', 'Factory', ['ALL' => 'ALL', '28' => 'TLD JST']
			+ tldLocation::getFactoryList('smartyOptionsLocationLocation')
			+ tldLocation::getSalesOrgList('smartyOptionsLocationLocation')
		);
		$form->addElement('select', 'z', 'Status',
			[
				'ALL' => 'ALL',
				'PENDING' => 'PENDING',
				'CREATE_PO' => 'CREATE_PO',
				'PRINT_PO' => 'PRINT_PO',
				'EVP_APPROVAL' => 'EVP_APPROVAL',
				'PRINT_SO_ACK' => 'PRINT_SO_ACK',
				'CREATE_FACTORY_SO' => 'CREATE_FACTORY_SO',
				'PRINT_FACTORY_SO_ACK' => 'PRINT_FACTORY_SO_ACK',
				'ER_ASSIGNMENT' => 'ER_ASSIGNMENT',
				'IN_PROGRESS' => 'IN_PROGRESS',
				'SHIPPED' => 'SHIPPED',
				'CLOSED' => 'CLOSED',
			]
		);
		$form->addElement('submit', 'btnSubmit', 'Submit');
		$body .= $form->toHTML();
		break;
	case 'byCashForecastSSO':
		$DEFAULT_TITLE .= "\SOR Line SSO Cash Forecast";
		$form = new HTML_QuickForm('frm', 'post');
		$form->addElement('hidden', 'm[0]', 'sol');
		$form->addElement('hidden', 'm[1]', 'listing');
		$form->addElement('hidden', 'm[2]', 'byCashForecastSSO');
		$form->addElement('header', 'title', 'SOL for SSO cash forecast');
		$form->addElement('select', 'x', 'SSO', ['' => '']
			+ tldLocation::getSalesOrgList('smartyOptionsLocationLocation')
		);
		$form->addElement('submit', 'btnSubmit', 'Submit');
		$body .= $form->toHTML();
		break;
	case 'byCashForecastERP':
		$DEFAULT_TITLE .= "\SOR Line SSO Cash Forecast";
		$form = new HTML_QuickForm('frm', 'post');
		$form->addElement('hidden', 'm[0]', 'sol');
		$form->addElement('hidden', 'm[1]', 'listing');
		$form->addElement('hidden', 'm[2]', 'byCashForecastERP');
		$form->addElement('header', 'title', 'SOL for Factory cash forecast');
		$form->addElement('select', 'x', 'Factory', ['' => '', '28' => 'TLD JST'] +
			tldLocation::getFactoryList('smartyOptionsLocationLocation')
			+ tldLocation::getSalesOrgList('smartyOptionsLocationLocation')
		);
		$form->addElement('submit', 'btnSubmit', 'Submit');
		$body .= $form->toHTML();
		break;
	case 'byEngineeringFlag':
		$DEFAULT_TITLE .= "\SOR Line with Engineering Flag";
		$form = new HTML_QuickForm('frm', 'post');
		$form->addElement('hidden', 'm[0]', 'sol');
		$form->addElement('hidden', 'm[1]', 'listing');
		$form->addElement('hidden', 'm[2]', 'byEngineeringFlag');
		$form->addElement('header', 'title', 'SOR Line with Engineering Flag');
		$form->addElement('select', 'x', 'Factory', ['' => ''] + tldLocation::getFactoryList('smartyOptionsLocationLocation'));
		$form->addElement('submit', 'btnSubmit', 'Submit');
		$body .= $form->toHTML();
		break;
	case 'byRequiredExportLicence':
		$DEFAULT_TITLE .= "\SOR Line waiting for Export Licence";
		$form = new HTML_QuickForm('frm', 'post');
		$form->addElement('hidden', 'm[0]', 'sol');
		$form->addElement('hidden', 'm[1]', 'listing');
		$form->addElement('hidden', 'm[2]', 'byRequiredExportLicence');
		$form->addElement('header', 'title', 'SOR Line waiting for Export Licence');
		$form->addElement('select', 'x', 'Factory', ['' => ''] + tldLocation::getFactoryList('smartyOptionsLocationLocation'));
		$form->addElement('submit', 'btnSubmit', 'Submit');
		$body .= $form->toHTML();
		break;
	case 'addSOL':
		if ($_REQUEST['pid']) {
			$sess['sol'] = [];
			$sess['sol']['pid'] = $_REQUEST['pid'];
		}
		$formCancel = new HTML_QuickForm('frmCancel', 'post');
		$formCancel->addElement('hidden', 'm[0]', 'sor');
		$formCancel->addElement('hidden', 'm[1]', 'view');
		$formCancel->addElement('hidden', 'id', $pid);
		$formCancel->addElement('submit', 'btnSubmit', 'Cancel');
		$body .= $formCancel->toHTML();

		$form = new HTML_QuickForm('frmAddSOL');
		$form->addElement('header', 'title', "Adding SOR Line to SOR# $pid, step 1...");
		$form->addElement('hidden', 'm[0]', 'sol');
		$form->addElement('hidden', 'm[1]', 'form');
		$form->addElement('hidden', 'm[2]', 'addSOL');
		$form->addElement('hidden', 'pid', $pid);
		$form->addElement('text', 'num', 'What is the quantity of units customer wants?');
		$curs = tldList::optionsByListNameAsListItemListItem('list.common.currency');
		$form->addElement('select', 'dcur', 'What is the DEFAULT currency for this transaction?',
			['' => ''] + $curs);
		$form->addElement('checkbox', 'light', 'Is Equipment record is light ?');
		$form->addElement('header', 'title', 'Select which currencies are involved in this transaction. Click all that apply');
		foreach ($curs as $cur) {
			$form->addElement('checkbox', "cur[$cur]", "$cur", 'Select');
		}
		$form->addElement('submit', 'btnSubmit', 'Next Step...');
		$form->setDefaults(['num' => 1]);
		$form->addRule('model', 'Required', 'required');
		$form->addRule('num', 'Required', 'required');
		$form->addRule('dcur', 'Required', 'required');

		if ($form->validate()) {
			$vars = tldUtils::cleanupFormInput($form->exportValues());
			//save values into the session for step 2
			if (!in_array($vars['dcur'], $curs)) {
				$DEFAULT_ERROR[] = 'ERROR: default currency given is invalid...';
				break;
			}
			$sess['sol']['dcur'] = $vars['dcur'];
			$sess['sol']['curs'] = array_keys($vars['cur'] ?? []);
			$sess['sol']['num'] = $vars['num'];
			$sess['sol']['light'] = $vars['light'];
			if (is_array($sess['sol']['curs']) && !in_array($vars['dcur'], $sess['sol']['curs'])) {
				$sess['sol']['curs'][] = $vars['dcur'];
			}
			$body .= "<a href=\"$self?m[0]=sol&m[1]=form&m[2]=addSOL2\">Click to continue to step 2...</a>";
		} else {
			$body .= $form->toHTML();
		}
		break;
	case 'addSOL2':
		$DEFAULT_TITLE .= "\New SOR Line";
		// Check qty and dcur from previous step
		if (empty($sess['sol']['dcur']) || empty($sess['sol']['num'])) {
			$DEFAULT_ERROR[] = 'ERROR: no default currency or qty set in step 1...';
			$body .= '<input type="button" value="Previous step" onClick="javascript:window.history.back();">';
			break;
		}
		$pid = &$sess['sol']['pid'];
		$sor = new tldSOR($sess['sol']['pid']);
		$sor_header = $sor->itsHeader;
		$sorLines = $sor->getLines();
		$factories = tldLocation::getFactoryList('smartyOptionsIDLocation');

		// Get list
		$TIERS = tldList::optionsByListNameAsListItemListItem('list.engine.tiers');
		// Get Form
		$formCancel = new HTML_QuickForm('frmCancel', 'get');
		$formCancel->addElement('hidden', 'm[0]', 'sor');
		$formCancel->addElement('hidden', 'm[1]', 'view');
		$formCancel->addElement('hidden', 'id', $pid);
		$formCancel->addElement('submit', 'btnSubmit', 'Cancel');
		$form = new HTML_QuickForm('frmAddSORLine', 'post');
		$form->addElement('hidden', 'm[0]', 'sol');
		$form->addElement('hidden', 'm[1]', 'form');
		$form->addElement('hidden', 'm[2]', 'addSOL2');
		$form->addElement('header', 'title', "Add new SOR line to SOR# $pid Form");
		$form->addElement('header', 'title', 'Factory and product information');
		$form->addElement('text', 'sfr_id', 'SFR New id (Not Legacy ID');
		if ($sess['sol']['light'] !== '1') {
			$form->addRule('sfr_id', 'Should be numeric', 'numeric');
			if (isset($sor_header['buyer_customer_display']) && !in_array($sor_header['buyer_customer_display'], ['**STOCK**', '**DEMO**'], true) && isset($sor_header['user_customer_display']) && !in_array($sor_header['user_customer_display'], ['**STOCK**', '**DEMO**'], true)) {
				$form->addRule('sfr_id', 'Required', 'required');
			}
		}
		$form->addElement('select', 'bu', 'TLD Factory BU', ['' => '', '28' => 'TLD JST'] + $factories + tldLocation::getSalesOrgList('smartyOptionsIDLocation'));
		$models = tldUtils::getSqlToAssocArray("SELECT model FROM models WHERE hide=0 AND model<>' ALL_MODELS' ORDER BY model", 'smartyOptions', ['model', 'model']);
		$form->addElement('select', 'model', 'Product Model', ['' => $line['model']] + $models		);
		$form->addElement('select', 'eng_tier', 'Emission Rating', ['' => ''] + $TIERS);
		$form->addElement('header', 'title', 'Warranty details');
		$form->addElement('textarea', 'wrty_spec', 'Describe Special Warranty Conditions if any (Blank means the TLD Standard Warranty terms apply)', ['wrap' => 'VIRTUAL', 'cols' => '40', 'rows' => '8']);
		$form->addElement('select', 'conf_wrty_erp', 'Have these Special Warranty Conditions formaly accepted and backed-up by the TLD Factory?', ['' => '', 'N' => 'N', 'Y' => 'Y']);
		$form->addElement('select', 'warranty_length', 'Warranty Length (Months)', ['' => ''] + tldEquipment::getAllowedWarrantyLength(), $options);
		$form->addElement('text', 'warranty_length_hours', 'Warranty Length (Hours)', $options);
		$form->addElement('header', 'title', 'Delivery details');
		$listTrans = tldList::optionsByListNameAsListKeyListItem('list.inco.terms');
		$form->addElement('select', 'inco', 'Inco Terms', ['' => ''] + $listTrans);
		$form->addElement('text', 'inco_loc', 'Inco Location', ['size' => 40]);
		$form->addElement('select', 'ctry', 'Country (Destination)', ['' => ''] + tldCountry::optionsAsNameName());
		$form->addElement('textarea', 'delivery_address', 'Delivery address', ['wrap' => 'VIRTUAL', 'cols' => '40', 'rows' => '8']);
		$form->addElement('select', 'del_pen', 'Delivery Penalties?', ['' => '', 'N' => 'N', 'Y' => 'Y']);
		$form->addElement('textarea', 'delpen_cond', 'Please describe delivery penalty', ['wrap' => 'VIRTUAL', 'cols' => '40', 'rows' => '8']);
		$form->addElement('select', 'conf_sls', 'Have these late delivery penalties formaly been approved by the TLD Sales Organization?',['' => '', 'N' => 'N', 'Y' => 'Y']);
		$form->addElement('select', 'conf_erp', 'Are these late delivery penalties approved and Backed-up by the TLD Factory?', ['' => '', 'N' => 'N', 'Y' => 'Y']);
		$form->addElement('select', 'conf_cis', 'Customer inspection before shipment?', ['' => '', 'N' => 'N', 'Y' => 'Y']);
		$form->addElement('textarea', 'docs_inc', 'Special Documentary Requirements', ['wrap' => 'VIRTUAL', 'cols' => '40', 'rows' => '8']);
		$form->addElement('header', 'title', 'Payment details');
		$form->addElement('select', 'conf_lc', 'Letter of Credit required?', ['' => '', 'N' => 'N', 'Y' => 'Y']);
		$form->addElement('text', 'dp_pc', 'Down Payment, Percentage of total sale');
		$form->addElement('textarea', 'tpay', 'Describe Terms of Payment', ['wrap' => 'VIRTUAL', 'cols' => '40', 'rows' => '8']);
		$form->addElement('header', 'title', 'FMS Contract');
		$form->addElement('text', 'fms_contract_length', 'FMS Contract lenght in months', ['placeholder' =>  'Enter 0 if not relevant']);
		$form->addRule('fms_contract_length', 'Should be a number between 0 and 240', 'regex', '/^(240|2([0-3]?\d)|(1\d{2})|(\d{1,2}))$/');
		$form->addElement('header', 'title', 'Forex Rates, PLEASE NOTE THAT ALL RATES ARE PER USD REGARDLESS WHETHER YOU USE USD OR NOT...');
		$form->addElement('header', 'title', 'USD 1.0 = EUR ' . round(1 / tldForex::getUSDPerEUR(), 4));
		$form->addElement('header', 'title', 'Default currency for this SOR Line is ' . $sess['sol']['dcur']);

		foreach ($sess['sol']['curs'] ?? [] as $cu) {
			if ($cu === 'EUR' || $cu === 'USD') {
				continue;
			}
			$forex = [];
			$form->addElement('text', "curs[$cu]", "USD 1.0 = $cu");
			$defaults["curs[$cu]"] = tldForex::getRate('USD', $cu);
		}

		$form->addElement('header', 'title', 'Special Notes');
		$form->addElement('select', 'parts_inc', 'If parts are purchased, ship with unit?', ['' => '', 'N' => 'N', 'Y' => 'Y']);
		$form->addElement('select', 'factory_shipping', 'Factory Shipping Parts',
			['' => ''] + $factories
		);
		addFormRuleFactoryShipping($form);
		$form->addElement('textarea', 'notes', 'Note...', ['wrap' => 'VIRTUAL', 'cols' => '40', 'rows' => '8']);

		$form->addElement('select', 'intro_new', 'New Introduction ?', ['' => '', 'Customer' => 'Customer', 'Product' => 'Product', 'No' => 'No']);
		// Need to pass num to validate stage other wise these fields won't be processed
		$fields = [
			'bu', 'model', 'eng_tier', 'conf_lc', 'del_dat', 'del_pen', 'wrty_std', 'dp_amt',
			'cu_nama', 'cu_ocur', 'rate_neg', 'pric_curn', 'rate_pub', 'pric_curp', 'conf_cis',
			'pris_unit', 'conf_sls', 'conf_erp', 'conf_wrty_erp', 'parts_inc', 'cu_price', 'inco',
			'inco_loc', 'ctry', 'intro_new', 'tpay', 'fms_contract_length', 'warranty_length', 'warranty_length_hours'
		];

// Get delivery dates for each unit or batchs

		$form->addElement('header', 'title', 'Requested delivery dates');
		$form->addElement('button', 'SetDelEarlyToYes', 'Set ALL units early delivery to Yes', ['onclick' => "javascript:$('.del_early').val('Y');"]		);

		if ($sess['sol']['num'] > 10) {
			//create 5 batches
			$DEFAULT_ERROR[] = 'WARNING: Requested QTY is over 10, dates must be specified in batches.';
			for ($i = 1; $i < 11; $i++) {
				$form->addElement('header', 'title', "Requested delivery dates - Batch $i");
				$form->addElement('text', "batchs[$i][qty]", 'Quantity', ['size' => 5]);
				$form->addElement('date', "batchs[$i][del_dat_array]", 'Requested Delivery Date',
					['format' => 'Y-m-d', 'minYear' => date('Y') - 2, 'maxYear' => date('Y') + 2]);
				$form->addElement('text', "batchs[$i][short_desc]", 'Short Description', ['size' => 40]);
				$form->addElement('text', "batchs[$i][del_location]", 'Delivery location', ['size' => 40]);
				$form->addElement('select', "batchs[$i][del_early]", 'Early delivery ok?',
					['N' => 'No', 'Y' => 'Yes'], ['class' => 'del_early']);
				$form->addElement('checkbox', "batchs[$i][commissioning]", 'Commissioning');
					$defaults["batchs[$i][del_dat_array]"] = ['Y' => date('Y'), 'm' => date('m'), 'd' => date('d')];
			}
			$defaults['batchs[1][qty]'] = $sess['sol']['num'];
			$form->addRule('batchs[1][qty]', 'At least one batch must have a qty', 'required');
		} else {
			for ($i = 1; $i < $sess['sol']['num'] + 1; $i++) {
				$form->addElement('header', 'title', "Requested delivery dates - Unit $i");
				$form->addElement('text', "batchs[$i][short_desc]", 'Short Description');
				$form->addElement('date', "batchs[$i][del_dat_array]", 'Requested Delivery Date, Unit# ' . $i,
					['format' => 'Y-m-d', 'minYear' => date('Y') - 2, 'maxYear' => date('Y') + 2]);
				$form->addElement('hidden', "batchs[$i][qty]", 1);
				$form->addElement('text', "batchs[$i][del_location]", 'Delivery location', ['size' => 40]);
				$form->addElement('select', "batchs[$i][del_early]", 'Early delivery ok?',
					['N' => 'No', 'Y' => 'Yes'], ['class' => 'del_early']);
				$form->addElement('checkbox', "batchs[$i][commissioning]", 'Commissioning');
				$fields[] = "batchs[$i][del_dat_array]";
				$defaults["batchs[$i][del_dat_array]"] = ['Y' => date('Y'), 'm' => date('m'), 'd' => date('d')];
			}
		}

		foreach ($fields as $field) {
			$form->addRule($field, 'Required', 'required');
		}
		$form->addRule('model', 'Required', 'required');
		$form->addElement('submit', 'btnSubmit', 'Submit');
		if ($defaults) {
			$form->setDefaults($defaults);
		}

// FORM VALIDATION --->
		global $kernel;
		if ($form->validate()) {
			$vars = tldUtils::cleanupFormInput($form->exportValues());
			if (!empty($vars['sfr_id'])) {
				try {
					$container = $kernel->getContainer();
					$client = $container->get(Client::class);
					$apiSalesForecast = $client->find('sales/sales_forecasts', $vars['sfr_id']);
					if (!in_array($apiSalesForecast['status'], ['PARTIAL', 'ORDERED'], true)) {
						$DEFAULT_ERROR[] = "ERROR: Provided SFR#{$vars['sfr_id']} is not in ORDERED nor PARTIAL status.";
						break;
					}
				} catch (\Exception $e) {
					$DEFAULT_ERROR[] = 'SFR could not be retrieved.';
				}
			}
			if (empty($pid)) {
				$DEFAULT_ERROR[] = 'ERROR: no SOR ID# set';
				break;
			}
			if (count($vars) == 0) {
				$DEFAULT_ERROR[] = 'ERROR: no SOR lines to add...';
				break;
			}
			// Check units quantity
			if (isset($batchs)) {
				$qty_given = 0;
				foreach ($batchs as $batch) $qty_given += (int) $batch['qty'];
				if ($qty_given <> $sess['sol']['num']) {
					$DEFAULT_ERROR[] = 'ERROR: Quantity given do not match quantity set in the previous step! Should be ' . $sess['sol']['num'];
					$form->setDefaults($vars);
					$body .= $formCancel->toHTML();
					$body .= $form->toHTML();
					break;
				}
			}
			// Check delivery penalties
			if ($vars['del_pen'] == 'N') {
				$vars['conf_sls'] = 'N';
				$vars['conf_erp'] = 'N';
				$DEFAULT_ERROR[] = 'NOTE: Delivery penalties set to N, so SSO and ERP penalties set automatically to N';
			}
			$sor = new tldSOR($pid);

			// Import default currency
			$vars['cu_ocur'] = $sess['sol']['dcur'];
			// Add line
			$solid = $sor->addLine($vars, $user->getID());
			if (!is_numeric($solid)) {
				$DEFAULT_ERROR[] = "There was a problem adding the SOR Line to SOR#$pid";
				break;
			}
			$sol = new tldSOL($solid);


			if ($sol->itsHeader['sfr_id'] && 0 !== $sol->itsHeader['sfr_id']) {
				try {
					$container = $kernel->getContainer();
					$client = $container->get(Client::class);
					$apiSalesForecast = $client->find('sales/sales_forecasts', $sol->itsHeader['sfr_id']);
					tldModLink::insert('SOL', $solid, 'SFR2', $apiSalesForecast['id']);
				} catch (\Exception $e) {
					$DEFAULT_ERROR[] = 'Link between SOL and SFR could not be created.';
				}
			}

			$sol->addLogEntry($user->getID(), "SOR Line# $solid added to SOR# $pid");
			$sol->openIbsTask($sorLines, $sor->getHeader());
			$sol->openModelIbsTask($sorLines, $sor->getHeader());
			//save the forex data
			$sol->addListEntry('DCUR', '', $sess['sol']['dcur']);
			$e = $sol->addListEntry('CURS', 'EUR', round(1 / tldForex::getUSDPerEUR(), 4));
			if (is_array($vars['curs'])) {
				$vars['curs'] = tldUtils::cleanupFormInput($vars['curs']);
				foreach ($vars['curs'] as $key => $value) {
					$e = $sol->addListEntry('CURS', $key, $value);
					if (!is_numeric($e)) {
						$DEFAULT_ERROR[] = $e;
					}
					if ($defaults["curs[$key]"] != $value) {
						$DEFAULT_ERROR[] = "Forex Rate for $key was changed from default for SOR Line $solid";
					}
				}
			}

			// Add unit delivery info
			// Get type of the SOL equipment
			$datasheet = tldDatasheet::byModel($vars['model']);
			// If trailers and Dollies (id=15) create batch qty for units
			if ($datasheet['parent_id'] == 15) {
				foreach ($vars['batchs'] as $batch) {
					// Check if qty given
					if ($batch['qty'] <= 0) {
						continue;
					}
					// Add units
					$unit['parent_id'] = $solid;
					$unit['del_dat'] = implode('-', $batch['del_dat_array']);
					$unit['short_desc'] = $batch['short_desc'];
					$unit['del_location'] = $batch['del_location'];
					$unit['del_early'] = $batch['del_early'];
					$unit['commissioning'] = isset($batch['commissioning']) ? 1 : 0;
					$unit['batch_qty'] = $batch['qty'];
					$unit = tldUtils::cleanupFormInput($unit);
					$e = tldSORUnit::insert($unit);
					if (!is_numeric($e)) {
						$DEFAULT_ERROR[] = $e;
					}
				}
			} else {
				foreach ($vars['batchs'] as $batch) {
					// Check if qty given
					if ($batch['qty'] <= 0) {
						continue;
					}
					// Add units as much as requested
					for ($x = 0; $x < $batch['qty']; $x++) {
						$unit['parent_id'] = $solid;
						$unit['del_dat'] = implode('-', $batch['del_dat_array']);
						$unit['short_desc'] = $batch['short_desc'];
						$unit['del_location'] = $batch['del_location'];
						$unit['del_early'] = $batch['del_early'];
						$unit['commissioning'] = isset($batch['commissioning']) ? 1 : 0;
						$unit['batch_qty'] = 1;
						$unit = tldUtils::cleanupFormInput($unit);
						$e = tldSORUnit::insert($unit);
						if (!is_numeric($e)) {
							$DEFAULT_ERROR[] = $e;
						}
					}
				}
			}

			$body .= <<<EOF
		<br><br><br><br><br><br><br>
		<a href="$php_self?m[0]=sol&m[1]=view&id=$solid">SOR Line# $solid add to SOR# $pid, click here to view</a>
EOF;
		} else {
			$body .= $formCancel->toHTML();
			$body .= $form->toHTML();
		}
		break;
}


function _checkQty($batchs)
{
	global $sess;
	$qty_given = 0;
	foreach ($batchs as $batch) $qty_given += $batch['qty'];
	if ($sess['sol']['num'] <> $qty_given) {
		return false;
	} else {
		return true;
	}
}

?>
