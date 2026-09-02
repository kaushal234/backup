<?php
$DEFAULT_TITLE .= '/Transfer to Baan';
$SO_transSeries = [
	'300' => [
		'SN2' => [65, 72],
		'SN1' => [65],
		'SC1' => [65],
		'CAS' => [72],
		'CAC' => [72],
		'SW2' => [92],
		'SN3' => [65, 20],
		'NYC' => [24],
		'NYS' => [24],
		'S01' => [25],
		'S02' => [25],
		'S03' => [25],
		'S04' => [25],
		'S05' => [25],
		'S06' => [25],
		'S07' => [25],
	],
	'540' => [
		'SN3' => [29, 18],
		'C03' => [45],
		'W40' => [54],
	],
	'600' => [
		'SN1' => [25, 26, 27, 28, 50],
		'SU1' => [12],
	],
	'680' => [
		'SN3' => [69, 70],
	],
];

if (!$user->isInGroup(['gg_PARTS'])) {
	$DEFAULT_ERROR[] = 'ERROR: This function not available to you';
	return;
}

if (!empty($msgid)) {
	$sess['msgid'] = $msgid;
}

if ($sess['CART_DEFAULT_DATA']['header']['source'] === 'eparts') {
	$sess['CART_DEFAULT_DATA'] = _decodeHtmlArray($sess['CART_DEFAULT_DATA']);
}

$body .= '<h2>Note:</h2><pre>' . htmlentities($sess['CART_DEFAULT_DATA']['header']['note']) . '</pre>';

switch ($m[2]) {
	case 'byName':
		$id = strtoupper($id);
		$rows = tldERPCustomer::byERPName($DEFAULT_ERP, $id);
		if (count($rows) === 0) {
			$DEFAULT_ERROR[] = "WARNING: No results found for $id...";
			break;
		}
		$report = new tldReportColumnar(
			$rows,
			[
				'xItems' => [
					't_cuno' => 'Customer#',
					't_nama' => 'Customer Name',
				],
				'title' => 'Please select correct customer',
				'links' => [
					't_cuno' => "$php_self?m[0]=cart&m[1]=transfer&m[2]=byCUNO&id=",
				],
			]
		);
		$body .= $report->fetch();
		break;
	case 'byCUNO':
		if (empty($id)) {
			$DEFAULT_ERROR[] = 'ERROR: Customer ID Number not specified';
			$body .= getHomepage();
			break;
		}
		$id = strtoupper($id);
		$cust = new tldERPCustomer($DEFAULT_ERP, $id);
		if ($cust->isEmpty()) {
			$DEFAULT_ERROR[] = "ERROR: No customer with id $id found";
			$body .= getHomepage();
			break;
		}
		$cust = new tldERPCustomer($DEFAULT_ERP, $id);
		$sess['parts']['cart']->setCustomer($cust->getHeader());

		$DEFAULT_ERROR[] = 'Hint: To search addresses on this page, hit CTL+F on your keyboard.';
		$form = new HTML_QuickForm('cancel', 'get');
		$form->addElement('hidden', 'm[0]', 'cart');
		$form->addElement('submit', 'btnSubmit', 'Cancel');
		$body .= $form->toHTML();

		// Display Customer information
		$form = new tldAssocTable(
			$cust->getHeader(),
			[
				't_cuno' => 'ID#',
				't_nama' => 'Name',
				't_namb' => '',
				't_namc' => '',
				't_namd' => '',
				't_name' => '',
			],
			['title' => 'Customer details']
		);
		$body .= $form->fetch();

		// If default data has been set, display customer data
		if (!empty($sess['CART_DEFAULT_DATA']['header']['deliverAddress']['line1'])) {
			$form = new tldAssocTable(
				$sess['CART_DEFAULT_DATA']['header']['deliverAddress'],
				[
					'line1' => 'Line 1',
					'line2' => 'Line 2',
					'line3' => 'Line 3',
					'line4' => 'Line 4',
					'line5' => 'Line 5',
				],
				['title' => 'Please select manual delivery address']
			);
			$body .= $form->fetch();
			$form = new HTML_QuickForm('manual', 'post');
			$form->addElement('hidden', 'm[0]', 'cart');
			$form->addElement('hidden', 'm[1]', 'transfer');
			$form->addElement('hidden', 'm[2]', 'selectPostal');
			$form->addElement('submit', 'btnSubmit', 'Use above address');
			$body .= $form->toHTML();
		}

		// List customer delivery address
		$rows = tldCDEL::byERPCUNO($DEFAULT_ERP, $id);
		$report = new tldReportColumnar(
			$rows,
			[
				'xItems' => [
					't_cdel' => 'CDEL',
					't_nama' => 'Address',
					't_namb' => '',
					't_namc' => '',
					't_namd' => '',
					't_name' => '',
					't_namf' => '',
					't_ccty' => 'Country',
				],
				'title' => 'Select delivery address from BAAN',
				'links' => [
					't_cdel' => "$php_self?m[0]=cart&m[1]=transfer&m[2]=selectPostal&cdel=",
				],
			]
		);
		$body .= $report->fetch();
		break;
	case 'selectPostal':
		// Save deliver address to cart from last step
		if (!empty($_GET['cdel'])) {
			$sess['parts']['cart']->setCDEL($_GET['cdel']);
		} else {
			$sess['parts']['cart']->setDeliverAddress(
				$sess['CART_DEFAULT_DATA']['header']['deliverAddress']
			);
		}
		// If default data has been set, display postal address
		if (!empty($sess['CART_DEFAULT_DATA']['header']['postalAddress']['line1'])) {
			$form = new tldAssocTable(
				$sess['CART_DEFAULT_DATA']['header']['postalAddress'],
				[
					'line1' => 'Line 1',
					'line2' => 'Line 2',
					'line3' => 'Line 3',
					'line4' => 'Line 4',
					'line5' => 'Line 5',
				],
				['title' => 'Please select manual postal address']
			);
			$body .= $form->fetch();
			$form = new HTML_QuickForm('manual', 'post');
			$form->addElement('hidden', 'm[0]', 'cart');
			$form->addElement('hidden', 'm[1]', 'transfer');
			$form->addElement('hidden', 'm[2]', 'selectCarrier');
			$form->addElement('submit', 'btnSubmit', 'Use above address');
			$body .= $form->toHTML();
		}
		// List of customer Postal address
		$cust = $sess['parts']['cart']->getCustomer();
		$custObj = new tldERPCustomer($DEFAULT_ERP, $cust['t_cuno']);
		$report = new tldReportColumnar(
			$custObj->getCCORList(),
			[
				'xItems' => [
					't_ccor' => 'CCOR',
					't_nama' => 'Address',
					't_namb' => '',
					't_namc' => '',
					't_namd' => '',
					't_name' => '',
					't_namf' => '',
					't_ccty' => 'Country',
				],
				'title' => 'Select Postal address from BAAN',
				'links' => [
					't_ccor' => "$php_self?m[0]=cart&m[1]=transfer&m[2]=selectCarrier&ccor=",
				],
			]
		);
		$body .= $report->fetch();
		break;
	case 'selectCarrier':
		// Form for selecting Carrier
		$erp = new tldBaanERP($DEFAULT_ERP);
		$form = new HTML_QuickForm('selectCarrier', 'post', '', '', '', true);
		$form->addElement('hidden', 'm[0]', 'cart');
		$form->addElement('hidden', 'm[1]', 'transfer');
		$form->addElement('hidden', 'm[2]', 'selectCarrier');
		$form->addElement('header', 'title', 'Final step');
		$form->addElement('text', 'custorno', 'Customer PO Number');
		$form->addElement('select', 'carrier', 'Carrier',
			$erp->getCarrierData('', ['smartyOptions' => ['t_cfrw', 't_dsca']])
		);
		$form->addElement('select', 'shipComplete', 'Ship Complete?',
			['N' => 'N', 'Y' => 'Y']
		);
		$form->addElement('date', 'deliverDate', 'Delivery Date',
			['format' => 'Y-m-d', 'minYear' => date('Y'), 'maxYear' => date('Y') + 5]
		);
		$form->addElement('textarea', 'note', 'Note', ['rows' => 8, 'cols' => 50]);
		// Prepare transaction type and series
		$SO_trans = [];
		$SO_series = [];
		foreach ($SO_transSeries[$DEFAULT_ERP] as $trans => $series) {
			$SO_trans[$trans] = $trans;
			foreach ($series as $serie) {
				$SO_series[$trans][$serie] = $serie;
			}
		}
		$sel =& $form->addElement('hierselect', 'misc', 'Transaction type and Series');
		$sel->setOptions([$SO_trans, $SO_series]);
		// Rules and default values
		$noteDefault = $sess['CART_DEFAULT_DATA']['header']['note'] . "\nBilling: ";
		foreach ($sess['CART_DEFAULT_DATA']['header']['billingAddress'] as $line) {
			$noteDefault .= "\n" . $line;
		}
		$form->setDefaults(
			[
				'deliverDate' => ['Y' => date('Y'), 'm' => date('m'), 'd' => date('d')],
				'custorno' => $sess['CART_DEFAULT_DATA']['header']['custpo'],
				'note' => $noteDefault,
			]
		);
		$form->addRule('custorno', 'This is required', 'required');
		$form->addRule('deliverDate', 'This is required', 'required');
		$form->addRule('carrier', 'This is required', 'required');
		$form->addRule('misc', 'This is required', 'required');
		$form->addElement('submit', 'btnSubmit', 'Submit');
		$form->addElement('reset', 'btnReset', 'Reset');
#unset($sess["CART_DEFAULT_DATA"]);
		if ($form->validate()) {
			$form->freeze();
			$vars = tldUtils::cleanupFormInput($form->exportValues());
			$Y = $vars['deliverDate']['Y'];
			$m = substr('0' . $vars['deliverDate']['m'], -2);
			$d = substr('0' . $vars['deliverDate']['d'], -2);
			$sess['parts']['cart']->setDeliverDate("$Y-$m-$d");
			$sess['parts']['cart']->setCarrier($erp->getCarrierData($vars['carrier']));
			$sess['parts']['cart']->setCustORNO($vars['custorno']);
			$CART = $sess['parts']['cart']->toArray();
			// Construct TLD array
			$a = [
				'header' => [
					't_cotp' => $vars['misc'][0],
					't_cuno' => $CART['header']['customer']['t_cuno'],
					't_eono' => $CART['header']['custpo'],
					't_ddat' => $CART['header']['t_odat'],
					't_cdel' => $CART['header']['t_cdel'],
					't_ccor' => $CART['header']['t_ccor'],
					't_cfrw' => $vars['carrier'],
					't_scom' => $vars['shipComplete'],
					'del_nama' => $CART['header']['deliverAddress']['line1'],
					'del_namb' => $CART['header']['deliverAddress']['line2'],
					'del_namc' => $CART['header']['deliverAddress']['line3'],
					'del_namd' => $CART['header']['deliverAddress']['line4'],
					'del_name' => $CART['header']['deliverAddress']['line5'],
					'del_namf' => $CART['header']['deliverAddress']['line6'],
					'post_nama' => $CART['header']['postalAddress']['line1'],
					'post_namb' => $CART['header']['postalAddress']['line2'],
					'post_namc' => $CART['header']['postalAddress']['line3'],
					'post_namd' => $CART['header']['postalAddress']['line4'],
					'post_name' => $CART['header']['postalAddress']['line5'],
					'post_namf' => $CART['header']['postalAddress']['line6'],
					'note' => $vars['note'],
					'series' => $vars['misc'][1],
					'erp' => $DEFAULT_ERP,
					'datetime' => date('Y-m-d H:i:s'),
				],
			];
			foreach ($CART['lines'] as $line) {
				$a['lines'][] = [
					't_item' => $line['item']['ITEM'],
					't_oqua' => $line['qty'],
				];
			}
			$a = _arrayEntities($a);
			// POST the SO to BAAN
			$response = $erp->inPurchaseOrder($a);
			if (is_string($response)) {
				$DEFAULT_ERROR[] = "ERROR: Sales Order not created from CART to $DEFAULT_ERP<br/>Reason: $response";
				break;
			}
			// Close msg
			if (!empty($sess['msgid'])) {
				$msg = new tldERPMSG($sess['msgid']);
				$e = $msg->changeStatus('CLOSED');
				if (is_string($e)) {
					$DEFAULT_ERROR[] = "ERROR: MSG#{$sess['msgid']} status not updated<br>Reason: $e";
				} else {
					$e = $msg->addLogEntry($user->getID(), 'Status changed to CLOSED');
					$body = "MSG#{$sess['msgid']} status changed successfully to CLOSED";
				}
				unset($sess['msgid']);
			}
			$body .= <<<EOF
		<br><br>SUCCESS: Sales Order successfully sent to $DEFAULT_ERP<br>
		The SO for customer {$a['header']['t_cuno']} and its PO#{$a['header']['t_eono']} will be created shortly.
EOF;
			/*
            $so = tldSO::byCUNO_EONO($DEFAULT_ERP,$a['header']['t_cuno'],$a['header']['t_eono']);
            $soid = $so['t_orno'];
            if(empty($soid)){
                $DEFAULT_ERROR[]="ERROR: Sales Order not created from CART to $DEFAULT_ERP";
                $DEFAULT_ERROR[]="Reason: SO submitted not founded";
                break;
            }
            $body.= "<br><br>SUCCESS: Sales Order # $soid successfully created in $DEFAULT_ERP<br/>";
            // Log the transmit to the MSG
            $msg = new tldERPMSG($sess["CART_DEFAULT_DATA"]['MSG_ID']);
            $msg->addLogEntry($user->getID(),"Transmit - SO#$soid created in $DEFAULT_ERP");
            */
			// Clean up vars
			$sess['parts']['cart']->emptyAll();
			$sess['CART_DEFAULT_DATA'] = null;
		} else {
			// Save deliver address to cart from last step
			if (!empty($_GET['cdel'])) {
				$sess['parts']['cart']->setCDEL($_GET['cdel']);
			} else {
				$sess['parts']['cart']->setDeliverAddress(
					$sess['CART_DEFAULT_DATA']['header']['deliverAddress']
				);
			}
			// Save deliver address to cart from last step
			if (!empty($_GET['ccor'])) {
				$sess['parts']['cart']->setCCOR($_GET['ccor']);
			} else {
				$sess['parts']['cart']->setPostalAddress(
					$sess['CART_DEFAULT_DATA']['header']['postalAddress']
				);
			}
			$DEFAULT_ERROR[] = 'WARNING: Hitting Submit will transfer the contents of your cart to baan.';
			$body .= $form->toHTML();
			// Display Customer information
			$cust = $sess['parts']['cart']->getCustomer();
			$form = new tldAssocTable(
				$cust,
				[
					't_cuno' => 'ID#',
					't_nama' => 'Name',
					't_namb' => '',
					't_namc' => '',
					't_namd' => '',
					't_name' => '',
				],
				['title' => 'Customer details']
			);
			$cells[] = $form->fetch();

			// Display CDEL or manual delivery address

			$cdel = $sess['parts']['cart']->getCDEL();
			if (!empty($cdel)) {
				$delivery = new tldCDEL($DEFAULT_ERP, $cust['t_cuno'], $cdel);
				$form = new tldAssocTable(
					$delivery->getHeader(),
					[
						't_cdel' => 'CDEL',
						't_nama' => 'Name',
						't_namb' => '',
						't_namc' => '',
						't_namd' => '',
						't_name' => '',
						't_namf' => '',
						't_ccty' => '',
					],
					['title' => 'Delivery address']
				);
				$cells[] = $form->fetch();
			} else {
				$deliveryAddress = $sess['parts']['cart']->getDeliverAddress();
				$form = new tldAssocTable(
					$deliveryAddress,
					[
						'line1' => 'Line 1',
						'line2' => 'Line 2',
						'line3' => 'Line 3',
						'line4' => 'Line 4',
						'line5' => 'Line 5',
					],
					['title' => 'Delivery address']
				);
				$cells[] = $form->fetch();
			}

			// Display CCOR or manual Customer Postal address

			$ccor = $sess['parts']['cart']->getCCOR();
			if (!empty($ccor)) {
				$postal = new tldCCOR($DEFAULT_ERP, $ccor, $cust['t_cuno']);
				$form = new tldAssocTable(
					$postal->getHeader(),
					[
						't_ccor' => 'CCOR',
						't_nama' => 'Name',
						't_namb' => '',
						't_namc' => '',
						't_namd' => '',
						't_name' => '',
						't_namf' => '',
						't_ccty' => '',
					],
					['title' => 'Customer Postal Address']
				);
				$cells[] = $form->fetch();
			} else {
				$deliveryAddress = $sess['parts']['cart']->getPostalAddress();
				$form = new tldAssocTable(
					$deliveryAddress,
					[
						'line1' => 'Line 1',
						'line2' => 'Line 2',
						'line3' => 'Line 3',
						'line4' => 'Line 4',
						'line5' => 'Line 5',
					],
					['title' => 'Customer Postal Address']
				);
				$cells[] = $form->fetch();
			}

			$report = new tldHTMLTable(
				$cells,
				[
					'cols' => 3,
					'attribs' => [
						'table' => " width='100%'",
						'tr' => " bgcolor='#FFFFFF'",
					],
				]
			);
			$body .= $report->fetch();
		}
		break;
	default:
		$body .= getHomepage();
		break;
}

// Get cart item list
$body .= getCart();


function getHomepage()
{
	global $sess, $php_self, $DEFAULT_ERROR;
	// Check if the CUNO is already set and redirect directly
	if (!empty($sess['CART_DEFAULT_DATA']['header']['customer']['t_cuno'])
		&& empty($DEFAULT_ERROR)) {
		$url = "$php_self?m[0]=cart&m[1]=transfer&m[2]=byCUNO&id=";
		$url .= $sess['CART_DEFAULT_DATA']['header']['customer']['t_cuno'];
		$body = "<br/>BAAN customer number founded, continue processing...<br/><br/>
		<meta http-equiv=\"refresh\" content=\"0;URL=$url\">";
	}
	// Form transfer by cuno
	$form = new HTML_QuickForm('step1', 'get');
	$form->addElement('header', 'title', 'Select Customer by Customer ID');
	$form->addElement('hidden', 'm[0]', 'cart');
	$form->addElement('hidden', 'm[1]', 'transfer');
	$form->addElement('hidden', 'm[2]', 'byCUNO');
	$form->addElement('text', 'id', 'Customer Number');
	$form->addElement('submit', 'btnSubmit', 'Submit');
	$body .= $form->toHTML();

	// Form transfer by customer name
	$form = new HTML_QuickForm('step1', 'get');
	$form->addElement('header', 'title', 'Select Customer by Customer Name');
	$form->addElement('hidden', 'm[0]', 'cart');
	$form->addElement('hidden', 'm[1]', 'transfer');
	$form->addElement('hidden', 'm[2]', 'byName');
	$form->addElement('text', 'id', 'Customer Name');
	$form->addElement('submit', 'btnSubmit', 'Submit');
	$body .= $form->toHTML();
	return $body;
}

function _decodeHtmlArray($array)
{
	$return = [];
	foreach ($array as $key => $value) {
		if (is_array($value)) {
			$return[$key] = _decodeHtmlArray($value);
		} else {
			$return[$key] = html_entity_decode($value);
		}
	}
	return $return;
}

function _arrayEntities($value)
{
	if (is_scalar($value)) {
		return htmlspecialchars(iconv('ISO-8859-1', 'UTF-8', $value), ENT_QUOTES | ENT_XML1, 'UTF-8');
	}

	$array = [];
	foreach ($value as $k => $v) {
		$array[$k] = _arrayEntities($v);
	}
	return $array;
}
