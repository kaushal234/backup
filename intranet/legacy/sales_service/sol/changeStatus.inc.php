<?php
require_once 'HTML/QuickForm.php';
require_once 'HTML/QuickForm/advmultiselect.php';

$currentStatus = $sol->getStatus();

if (true !== $result = $sol->checkCustomerStatus()) {
    $DEFAULT_ERROR[] = $result;
    return;
}

if ($currentStatus === 'PRINT_FACTORY_SO_ACK'
	&& !empty($m[3]) && $m[3] === 'force_PSM_APPROVAL'
	&& strtolower($_SERVER['REQUEST_METHOD']) === 'post'
	&& $user->isInGroup(['role_PSM', 'role_PSE', 'role_PSA'])
) {
	$currentStatus = 'PSM_APPROVAL';
}

$allowedStatus = $sol->getStatusAllowed($user->getID());

$listCC = $sol->getCC();

switch ($currentStatus) {
	case 'PRINT_FACTORY_SO_ACK':
		if (!$sol->isFullyAllocated()) {
			$DEFAULT_ERROR[] = 'ERROR: Cannot change status until there are enough equipment assigned...';
			break;
		}
		// Get list of SOL units
		$rows = tldSORUnit::byParent($id);
		// Get the form with Units promise delivery dates9
		$form = new HTML_QuickForm('frmPRINT_FACTORY_SO_ACK', 'post');
		$form->addElement('header', 'title', 'Change status to ->' . $allowed['fwd']);
		$form->addElement('hidden', 'm[0]', 'sol');
		$form->addElement('hidden', 'm[1]', 'view');
		$form->addElement('hidden', 'm[2]', 'changeStatus');
		$form->addElement('hidden', 'id', $id);
		$form->addElement('text', 'erp_orno', 'Factory Sales Order#');
		foreach ($rows as $i => $row) {
            $deliveryDate = new \DateTime($row['del_dat']);
            $promisedCBOMDate = clone $deliveryDate;
            $promisedCBOMDate = $promisedCBOMDate->sub(new \DateInterval('P6W'));
            if ($row['ddel_est1'] !== '0000-00-00') {
                $deliveryDate = new \DateTime($row['ddel_est1']);
            }
			$form->addElement('header', 'title', 'Unit ' . ($row['sn']));
			$form->addElement('date', "pdat_array[${row['id']}]",
				"Requested Delivery Date ${row['del_dat']} <br> Your Promised Delivery Date",
				['format' => 'Y-m-d', 'minYear' => date('Y') - 2, 'maxYear' => date('Y') + 3]
			);
			$form->addElement('date', "promised_cbom_date[${row['id']}]",
				'Promised CBOM date',
				['format' => 'Y-m-d', 'minYear' => date('Y') - 2, 'maxYear' => date('Y') + 3]
			);
			$form->addElement('hidden', "unitLabel_array[${row['id']}]", $i + 1);
			$fields[] = "pdat_array[${row['id']}]";
            $form->addRule("promised_cbom_date[${row['id']}]", 'Required', 'required');
			$form->setDefaults(
				[
                    "pdat_array[${row['id']}]" => [
                        'Y' => $deliveryDate->format('Y'),
                        'm' => $deliveryDate->format('m'),
                        'd' => $deliveryDate->format('d'),
                    ],
                    "promised_cbom_date[${row['id']}]" => [
                        'Y' => $promisedCBOMDate->format('Y'),
                        'm' => $promisedCBOMDate->format('m'),
                        'd' => $promisedCBOMDate->format('d'),
                    ],
				],
			);
		}
		$form->addElement('textarea', 'comment', 'Comment', ['rows' => 10, 'cols' => 40]);
		$ccForm =& $form->addElement(
			'advmultiselect', 'cc_users', null,
			tldDirectory::getUserlist('smartyOptions'),
			[
				'size' => 10,
				'class' => 'pool',
				'style' => 'width:200px;',
			]
		);
		$ccForm->setLabel(['CC others (OPTIONAL)', 'Addressbook', 'CC']);
		$ccForm->setButtonAttributes('add', ['value' => '-->>', 'class' => 'inputCommand']);
		$ccForm->setButtonAttributes('remove', ['value' => '<<--', 'class' => 'inputCommand']);
		$form->addElement('file', 'filename', 'File (OPTIONAL)');
		$form->addElement('submit', 'btnSubmit', 'Submit');
		$form->addRule('erp_orno', 'Required', 'required');
		$defaults['erp_orno'] = $header['erp_orno'];
		$defaults['cc_users'] = $listCC;
		$form->setDefaults($defaults);

		if ($user->isInGroup(['role_PSM', 'role_PSE', 'role_PSA'])) {
			//Second form to go to IN PROGRESS
			$form2 = new HTML_QuickForm('frmChangeStatus', 'post');
			$form2->addElement('header', 'warning', '<span style="color:red;">TO BE USED IN CASE OF MODIFICATION ONLY</span>');
			$form2->addElement('header', 'title', 'Change status to ->' . $allowed['fwd2']);
			$form2->addElement('hidden', 'm[0]', 'sol');
			$form2->addElement('hidden', 'm[1]', 'view');
			$form2->addElement('hidden', 'm[2]', 'changeStatus');
			$form2->addElement('hidden', 'm[3]', 'force_PSM_APPROVAL');
			$form2->addElement('hidden', 'id', $id);
			$form2->addElement('textarea', 'comment', 'Comment', ['rows' => 10, 'cols' => 40]);

			$ccForm2 =& $form2->addElement(
				'advmultiselect', 'cc_users', null,
				tldDirectory::getUserlist('smartyOptions'), ['size' => 10, 'class' => 'pool', 'style' => 'width:200px;']
			);
			$ccForm2->setLabel(['CC others (OPTIONAL)', 'Addressbook', 'CC']);
			$ccForm2->setButtonAttributes('add', ['value' => '-->>', 'class' => 'inputCommand']);
			$ccForm2->setButtonAttributes('remove', ['value' => '<<--', 'class' => 'inputCommand']);
			$form2->addElement('file', 'filename', 'File (OPTIONAL)');
			$form2->addElement('submit', 'direction', 'Forwards to IN PROGRESS', ['class' => 'noDisableOnSubmit']);
			$form2->setDefaults(['comment' => "Promise Date Confirmed?\r\nSchedule OK?\r\nInformation required from SSO?\n\nPSM approval comments:"]);
			$form2->setDefaults(['cc_users' => $listCC]);
		}

        $formPopulate = new HTML_QuickForm('PopulateForm', 'post');
        $formPopulate->addElement('header', 'title', 'Populate field Promised CBOM date for all units');
        $formPopulate->addElement('date', 'populate_cbom_date',
            'Promised CBOM date',
            ['format' => 'Y-m-d', 'minYear' => date('Y') - 2, 'maxYear' => date('Y') + 3]
        );
        $formPopulate->addElement('button', 'populate', 'Populate', ['id' => 'populate']);

		if (!$form->validate() && ($form2 === null || !$form2->validate())) {
            $body .= $formPopulate->toHTML();
			$body .= '</br>' . $form->toHTML();
			if ($form2 !== null) {
				$body .= '</br></br></br></br></br></br>' . $form2->toHTML();
			}
            $body .= <<<'HTML'
<script type="text/javascript">
    document.getElementById('populate').addEventListener('click', function() {
        const yearValue = document.querySelector('[name="populate_cbom_date[Y]"]').value;  
        const yearSelects = document.querySelectorAll('select[name^="promised_cbom_date"][name$="[Y]"]');
        yearSelects.forEach(function(input) {
            input.value = yearValue
        });
        const monthValue = document.querySelector('[name="populate_cbom_date[m]"]').value;  
        const monthSelects = document.querySelectorAll('select[name^="promised_cbom_date"][name$="[m]"]');
        monthSelects.forEach(function(input) {
            input.value = monthValue
        });
        const dayValue = document.querySelector('[name="populate_cbom_date[d]"]').value;  
        const daySelects = document.querySelectorAll('select[name^="promised_cbom_date"][name$="[d]"]');
        daySelects.forEach(function(input) {
            input.value = dayValue
        });
    });
</script>
HTML;
			break;
		}
		$a = $form->exportValues();
		$parsedComment = str_replace(["\r\n", "\r", "\n"], '<br/>', $a['comment']);
		$vals = tldUtils::cleanupFormInput($a);
		// Save the dates
		if (count($vals['pdat_array'])) {
			// Check promise delivery if in past
			foreach ($vals['pdat_array'] as $soruid => $pdat) {
				$unitPromiseDeliveryDate = \DateTimeImmutable::createFromFormat('Y-m-d H:i:s', $pdat['Y'] . '-' . $pdat['m'] . '-' . $pdat['d'] . ' 00:00:00');
				$errors = \DateTimeImmutable::getLastErrors();
				if (!empty($errors['warning_count']) || !empty($errors['error_count'])) {
					$DEFAULT_ERROR[] = "ERROR: SOR Unit#{$vals['unitLabel_array'][$soruid]} promise delivery date is invalid.";
					$body = $form->toHTML();
					break 2;
				}

				// A not empty erp_orno means that we already went through this step once.
				if (empty($header['erp_orno']) && $unitPromiseDeliveryDate < new DateTime('midnight')) {
					$DEFAULT_ERROR[] = "ERROR: SOR Unit#{$vals['unitLabel_array'][$soruid]} promise delivery date is in the past";
					$body = $form->toHTML();
					break 2;
				}

				$promiseDeliveryDateLimit = (new \DateTimeImmutable($unitPromiseDeliveryDate->format('Y-m-t')))->modify('-2 day');
				if ($unitPromiseDeliveryDate >= $promiseDeliveryDateLimit) {
					$DEFAULT_ERROR[] = "ERROR: SOR Unit#{$vals['unitLabel_array'][$soruid]} promise delivery date is in the 3 last days of the month.";
					$body = $form->toHTML();
					break 2;
				}
			}
			// If checks ok, update dates
			foreach ($vals['pdat_array'] as $soruid => $pdat) {
				$soru = new tldSORUnit($soruid);
				if ($id == $soru->getPID()) {
					$soru->setDDEL_EST1(implode('-', $pdat));
				} else {
					$DEFAULT_ERROR[] = "ERROR: SOR Unit# $soruid does not belong to SOR Line# $id";
				}
			}
		}

        foreach ($vals['promised_cbom_date'] as $sorUnitId => $value) {
            $sorUnit = new tldSORUnit($sorUnitId);
            if($sorUnit->isEmpty()){
                $DEFAULT_ERROR[] =  "No SOR unit #$sorUnitId found...";
                break 2;
            }
            if(null === ($equipmentRecordId = $sorUnit->getHeader()['erid'])) {
                $DEFAULT_ERROR[] =  "No equipment record found for SOR unit #$sorUnitId";
                break 2;
            }
            $equipmentRecord = new tldEquipment($equipmentRecordId);
            $equipmentRecord->updateRecord(
                ['promised_cbom_date' => sprintf('%s-%s-%s', $value['Y'], $value['m'], $value['d'])],
                ['promised_cbom_date']
            );
        }

		// Get cc list
		$CC = [];
		if (count($a['cc_users'] ?? []) > 0) {
			foreach ($a['cc_users'] as $userid) {
				$ccUser = new tldUser($userid);
				$ccUserEmail = $ccUser->getEmail();
				if (empty($ccUserEmail)) {
					continue;
				}
				$CC[] = $ccUserEmail;
				if (!in_array($userid, $listCC, false)) {
					$sol->addCC($userid);
				}
			}
			$CC = array_unique($CC);
		}
		// Update SOL status
		$status = $allowed['fwd'];
		$error = $sol->changeStatus(
			$status,
			$user->getID(),
			[
				'msg' => $parsedComment,
				'cc' => $CC,
			]
		);
		if (is_string($error)) {
			$DEFAULT_ERROR[] = "ERROR: there was a problem changing status, returned error was... $error<br>";
			break;
		}
		// Add logs
		$m = "Status moved to $status";
		if ($vals['comment']) {
			$m .= "\n" . $vals['comment'];
		}
		$sol->addLogEntry($user->getID(), $m);
		// Look for a file to add
		$file = $form->getElement('filename');
		$file_array = $file->getValue();
		if ($file_array['tmp_name'] != '') {
			$e = $sol->addFile(
				[
					'poster' => $user->getID(),
					'description' => "File posted from status change to $status",
					'filename' => "sol_$id\_file_$status",
				],
				$file_array
			);
			if (is_string($e)) {
				$DEFAULT_ERROR[] = "INTERNAL ERROR: There was a problem attaching the file<br>$e...";
			} else {
				$DEFAULT_ERROR[] = 'File successfully attached';
			}
		}
		// Add SO ACK #
		$error = $sol->setERP_ORNO($vals['erp_orno']);
		// Refresh header and display SOL
		$sol->refresh();
		$header = $sol->itsHeader;
		$body .= getGeneralTab($header);

		//Send email for SSO ACK
		if ($status === 'ENGINEER_REVIEW') {

			$summary = new tldAssocTable(
				$header,
				[
					'buyer_customer_display' => 'Customer Name (BUYER)',
					'user_customer_display' => 'Customer Name (END USER)',
					'qty_sou' => 'Quantity',
					'model' => 'Model',
				],
				['plain' => 'useless', 'title' => 'Quick Summary']
			);
			$message = <<<EOF
<p>Please click <a href="https://www.tld-gse.com/en/private/sales_service/sales.php?m[0]=sol&m[1]=view&m[2]=er&m[3]=receipt&id={$sol->getID()}">HERE</a> to download the acknowledgement of receipt from SOL#{$sol->getID()}.</p>
<p>If needed, you can always re-generate it from the SOL page.<br><br><a href="https://www.tld-gse.com/en/private/sales_service/sales.php?m[0]=sol&m[1]=view&id={$sol->getID()}">
click here to view online.</a><br/><br/>
</p>
EOF;
			$message .= $summary->fetch();
			$asm = new tldUser($header['asmID']);
			$CC = array_unique(array_column((new tldGroup('ROLE_SA'))->getUserlistBySSO($asm->getBUID()), 'email'));
			tldUtils::emailAttachment(
				$asm->getEmail(),
				'noreply@tld-gse.com',
				"SOL#{$sol->getID()} acknowledgement of receipt is ready",
				$message,
				null,
				$CC
			);
		}

		break;
	case 'CREATE_PO':
	case 'EVP_APPROVAL':
		$form = new HTML_QuickForm('frmCREATE_PO', 'post');
		$form->addElement('header', 'title', 'Change status to ->' . $allowed['fwd']);
		$form->addElement('hidden', 'm[0]', 'sol');
		$form->addElement('hidden', 'm[1]', 'view');
		$form->addElement('hidden', 'm[2]', 'changeStatus');
		$form->addElement('hidden', 'status', $allowed['fwd']);
		$form->addElement('hidden', 'id', $id);
		if ($header['del_pen'] === 'Y') {
			$DEFAULT_ERROR[] = 'There are deliver penalties associated with this order...';
			$form->addElement('select', 'accept', 'Do you accept the delivery penalties?',
				['' => '', 'N' => 'N', 'Y' => 'Y']
			);
		}
		if ($currentStatus !== 'EVP_APPROVAL') {
			$form->addElement('text', 'sls_orno', 'Sales and Service Organization PO#');
			$form->addRule('sls_orno', 'Required', 'required');
		}
		$form->addElement('textarea', 'comment', 'Comment', ['rows' => 10, 'cols' => 40]);
		$ccForm =& $form->addElement(
			'advmultiselect', 'cc_users', null,
			tldDirectory::getUserlist('smartyOptions'),
			[
				'size' => 10,
				'class' => 'pool',
				'style' => 'width:200px;',
			]
		);
		$ccForm->setLabel(['CC others (OPTIONAL)', 'Addressbook', 'CC']);
		$ccForm->setButtonAttributes('add', ['value' => '-->>', 'class' => 'inputCommand']);
		$ccForm->setButtonAttributes('remove', ['value' => '<<--', 'class' => 'inputCommand']);
		$form->addElement('file', 'filename', 'File (OPTIONAL)');
		$options = [];
		if ('CREATE_PO' === $currentStatus && in_array('**DEMO**', [$sol->itsHeader['user_customer_display'], $sol->itsHeader['buyer_customer_display']], true)) {
            $options = ['onClick' => "return confirm('Please check the Options in the Breakdown include the LINK.\\n\\nPress OK to forward to EVP Approval status if option list includes the LINK.\\nOtherwise modify the Breakdown before proceeding');"];
        }
		$form->addElement('submit', 'btnSubmit', 'Forwards', $options);
		// Default values & rules
		$form->setDefaults([
			'accept' => $header['conf_erp'],
			'sls_orno' => $header['sls_orno'],
			'cc_users' => $listCC,
		]);
		$form->addRule('accept', 'Required', 'required');


		if (!$form->validate()) {
			$body .= $form->toHTML();
			break;
		}

		$a = $form->exportValues();



        // notify if deviation > 10%
        $dcur = $sol->getDCUR();
        $intCaty = tldSOL::getInternalCategoriesList();
        $intLines = tldSOROpts::byParent($id, ['include' => $intCaty, 'dcur' => $dcur]);
        $intTotals = $intLines ? tldSOROpts::totalsByParent($id, ['include' => $intCaty, 'dcur' => $dcur]) : [];

        $extCaty = tldSOL::getExternalCategoriesList();
        $extLines = tldSOROpts::byParent($id, ['include' => $extCaty, 'dcur' => $dcur]);
        $extTotals = $extLines ? tldSOROpts::totalsByParent($id, ['include' => $extCaty, 'dcur' => $dcur]) : [];

        $sumExternalItemCost = (int)$extTotals['pris_tot_in_dcur'];
        $sumActualSalesPrice = (int)$intTotals['pris_tot_in_dcur'];
        $deviation = 0;
        if (0 !== $sumExternalItemCost && 0 !== $sumActualSalesPrice) {
            $deviation = ($sumExternalItemCost / $sumActualSalesPrice) * 100;
        }

        $sso = $sol->getSSOERP();
        
        if ($deviation > 10) {
            $subjectNotification = "SOL#$id - External item cost over 10% of Actual Sales Price";

            $solTotalValue = number_format($sumActualSalesPrice + $sumExternalItemCost, 2);

            $bodyNotification = "Please check SOL#$id: external item cost is over 10% of Actual Sales Price.<br><br>";

            $solSummaryReport = new tldAssocTable(
                [
                    'buyer_customer_display' => $sol->itsHeader['buyer_customer_display'] ?? 'N/A',
                    'user_customer_display'  => $sol->itsHeader['user_customer_display'] ?? 'N/A',
                    'model'                  => $sol->itsHeader['model'] ?? 'N/A',
                    'sol_total_value'        => "$dcur $solTotalValue",
                    'sol_number'             => "SOL#$id",
                ],
                [
                    'buyer_customer_display' => 'eCUSTOMER BUYER',
                    'user_customer_display'  => 'eCUSTOMER USER',
                    'model'                  => 'Machine (Model)',
                    'sol_total_value'        => 'SOL Value',
                    'sol_number'             => 'SOL Number',
                ],
                ['title' => 'SOL Description']
            );
            $bodyNotification .= $solSummaryReport->fetch();

            $extLinesReport = new tldReportColumnar(
                $extLines,
                [
                    'xItems' => [
                        'caty'         => 'Category',
                        'dsca'         => 'Description',
                        'pric_in_dcur' => "Cost ($dcur)",
                        'pris_in_dcur' => "Sales Price ($dcur)",
                    ],
                    'title'    => 'External Items',
                    'sortable' => 'NO',
                ]
            );
            $bodyNotification .= $extLinesReport->fetch();

            $bodyNotification .= "<br><a href=\"https://www.tld-gse.com/en/private/sales_service/sales.php?m[0]=sol&m[1]=view&id=$id\">Click here to see SOL#$id</a>";

            tldGroup::emailMultipleGroups(
                [
                    $sso => ['ROLE_RCEO', 'ROLE_CFO'],
                    'GLOBAL' => ['ROLE_TCOO', 'ROLE_TCEO'],
                ],
                'noreply@tld-gse.com',
                $subjectNotification,
                $bodyNotification,
                ['charset' => 'UTF-8']
            );
        }

        $parsedComment = str_replace(["\r\n", "\r", "\n"], '<br/>', $a['comment']);
		$vals = tldUtils::cleanupFormInput($a);
		// Add the SO#
		if (isset($vals['sls_orno'])) {
			$error = $sol->setSLS_ORNO($vals['sls_orno']);
			if (is_string($error)) {
				$DEFAULT_ERROR[] = "INTERNAL ERROR: Could not update SSO PO#. Reason: $error";
			}
		}

		// Update delivery penalty
		if ($header['del_pen'] === 'Y') {
			$error = $sol->updateHeader(['conf_erp' => $vals['accept']]);
			if (is_string($error)) {
				$DEFAULT_ERROR[] = "INTERNAL ERROR: Could not update factory delivery penalty. Reason: $error";
			}
		}
		// Get cc list
		$CC = [];
		if (count($a['cc_users'] ?? []) > 0) {
			foreach ($a['cc_users'] as $userid) {
				$ccUser = new tldUser($userid);
				$ccUserEmail = $ccUser->getEmail();
				if (empty($ccUserEmail)) {
					continue;
				}
				$CC[] = $ccUserEmail;
				if (!in_array($userid, $listCC, false)) {
					$sol->addCC($userid);
				}
			}
			$CC = array_unique($CC);
		}
		// PRE action
        if ($status === 'PRINT_SO_ACK') {
            // Add SOL SUMMARY report to notification
            $parsedComment .= getEVP_APPROVALMessage();
        }
		// Update SOL status
		$error = $sol->changeStatus(
			$status,
			$user->getID(),
			[
				'msg' => $parsedComment,
				'cc' => $CC,
			]
		);
		if (is_string($error)) {
			$DEFAULT_ERROR[] = "ERROR: there was a problem changing status, returned error was... $error";
			break;
		}
		// refresh SOL
		$sol->refresh();
		// Add logs
		$m = 'Status moved to ' . $allowed['fwd'];
		if ($vals['comment']) {
			$m .= "\n" . $vals['comment'];
		}
		$sol->addLogEntry($user->getID(), $m);
		// Look for a file to add
		$file = $form->getElement('filename');
		$file_array = $file->getValue();
		if ($file_array['tmp_name'] != '') {
			$e = $sol->addFile(
				[
					'poster' => $user->getID(),
					'description' => "File posted from status change to $status",
					'filename' => "sol_$id\_file_$status",
				],
				$file_array
			);
			if (is_string($e)) {
				$DEFAULT_ERROR[] = "INTERNAL ERROR: There was a problem attaching the file<br>$e...";
			} else {
				$DEFAULT_ERROR[] = 'File successfully attached';
			}
		}

		// Post action
		switch ($status) {
			case 'PRINT_SO_ACK':
				// Check Commission Rate and Notify if delinquent
				// Are all SOR lines at EVP_APPROVAL ?
				if ($sol->areAllLinesPastEVP()) {
					// Get current SOL DCUR
					$dcur = $sol->getDCUR();

					// Initialize variables
					$sor = [
                        'trans_tot' => 0,
                        'tot_gross_sell_price' => 0,
                        'tot_sales_com' => 0,
                    ];
					$sorLines = [];
					$rows = tldSOL::byParent($sol->getParentID());

					// Order data into arrays & Tables
					foreach ($rows as $row) {
						$sso = $row['sor_erp'];
						$buyer = $row['buyer_customer_display'];
						$line = new tldSOL($row['id']);
						$summary = $line->getSummary(['dcur' => $dcur]);
						$sorLines[$row['id']] = [
						    'id' => $row['id'],
						    'dcur' => $dcur,
						    'model' => $line->getModel(),
						    'qty' => $line->getQTY(),
						    'tot_gross_sell_price' => $summary['pris_xtot'],
						    'tot_sales_com_pc' => round(($summary['pric_agent_com_tot'] / ($summary['pris_xtot'] - $summary['trans_tot'])) * 100, 1),
                        ];
						$sor['trans_tot'] += $summary['trans_tot'];
						$sor['tot_gross_sell_price'] += $summary['pris_xtot'];
						$sor['tot_sales_com'] += $summary['pric_agent_com_tot'];
					}
					$sor['dcur'] = $dcur;
					$sor['tot_sales_com_pc'] = round(($sor['tot_sales_com'] / ($sor['tot_gross_sell_price'] - $sor['trans_tot'])) * 100, 1);

					$sorReport = new tldAssocTable(
						$sor,
						[
							'tot_gross_sell_price' => 'Total Gross Selling Price',
							'tot_sales_com' => 'Total Commissions',
							'dcur' => 'Currency',
							'tot_sales_com_pc' => 'Global % of commissions',
						],
						['title' => "SOR REF#{$sol->getParentID()}"]
					);

					$solReport = new tldReportColumnar(
						$sorLines,
						[
							'xItems' => [
								'id' => 'SOL#',
								'model' => 'Model',
								'qty' => 'Quantity',
								'tot_gross_sell_price' => 'Total Gross Selling Price',
								'dcur' => 'Currency',
								'tot_sales_com_pc' => 'Comissions %',
							],
							'title' => 'SOR Lines Details',
							'links' => [
								'id' => 'https://www.tld-gse.com/en/private/sales_service/sales.php?m[0]=sol&m[1]=view&id='],
							'sortable' => 'NO',
						]
					);

					// Check if Commission amount is delinquent
					$flagDelinquent = false;
					if ($sor['tot_gross_sell_price'] <= 2000000 && $sor['tot_sales_com_pc'] > 7) {
						$flagDelinquent = true;
					}
					if ($sor['tot_gross_sell_price'] > 2000000 && $sor['tot_gross_sell_price'] <= 4000000 && $sor['tot_sales_com_pc'] > 6) {
						$flagDelinquent = true;
					}
					if ($sor['tot_gross_sell_price'] > 4000000 && $sor['tot_sales_com_pc'] > 5) {
						$flagDelinquent = true;
					}

					// Notify if delinquent
					if ($flagDelinquent) {
                        $order = (new tldSOR($sol->getParentID()))->getHeader();
                        $agent = $order['agnt_nama'];
                        
						$message = $sorReport->fetch();
						$message .= '<br>';
						$message .= $solReport->fetch();

						$sso = $sol->getSSOERP();
						$e = tldGroup::emailMultipleGroups([
							900 => ['role_CFO', 'role_LM', 'role_GCEO', 'role_GCOO'],
							$sso => ['role_CEO', 'role_RCEO', 'role_EVP']],
							'noreply@tld-gse.com',
							"DMS #1406 - Compliance Agents and Distributors K - SOR#{$sol->getParentID()} - $buyer - $agent Commission rate is beyond DMS recommendations",
							$message
						);
						if (is_string($e)) {
							$DEFAULT_ERROR[] = "ERROR: Problem sending Delinquent Commission notification email, error returned was $e";
						}
					}
				}

				// Check Base Unit Currencies
				$base = tldSOROpts::byParent($id, ['include' => ['BASE UNIT']]);
				if (false !== ($currencyData = current($base)) && $currencyData['mrsp_cur'] !== $currencyData['pris_cur']) {
					// SSO CFO
					$to = [];
					$grp = new tldGroup('role_CFO', $sol->getSSOERP());
					$to = array_merge($to, $grp->getEmailList());
					// Factory CFO
					$grp = new tldGroup('role_CFO', $sol->getFactoryERP());
					$to = array_merge($to, $grp->getEmailList());
					// Group CFO
					$grp = new tldGroup('role_CFO', '900');
					$to = array_merge($to, $grp->getEmailList());
					// Group ERP_FOREX
					$grp = new tldGroup('ERP_FOREX', '900');
					$to = array_merge($to, $grp->getEmailList());

					$message = <<<EOF
<p>Warning - this SOL creates a Forex risk : #$id.</p>
<p>Please refer to parts information below:</p>
EOF;
					$message .= $solReport->fetch();
					$sol->notify(
						array_unique($to),
						"Forex Risk on SOL #$id",
						$message
					);
				}

				// Check if parts needed for this SOL
				$partsList = tldSOROpts::byParent($id, ['include' => ['SPARE PARTS']]);
				if (!count($partsList)) {
					break;
				}
				// Else notify people to prepare parts
				$message = <<<EOF
<p>SOL#$id updated to $status. A set of spare parts has to be shipped with unit.</p>
<p>Please refer to parts information below:</p>
EOF;
				$report = new tldReportColumnar(
					$partsList,
					[
						'xItems' => [
							'id' => 'Option#',
							'caty' => 'Category',
							'dsca' => 'Description',
							'pric_cur' => 'Negotiated TP Currency',
							'pric' => 'Negotiated TP',
							'pris_cur' => 'Actual Sales Price Currency',
							'pris' => 'Actual Sales Price',
						],
						'sortable' => 'NO',
					]
				);
				$message .= $report->fetch();
                $sol->createPartsTask($user, $message);
				break;
		}

		// Display header
		$header = $sol->getHeader();
		$body .= getGeneralTab($header);
		break;
	default:
		// Make PRE checks
		switch ($currentStatus) {
			case 'PENDING':
				// No check for now
				break;
			case 'IN_PROGRESS':
				if (!$sol->isFullyGreenTagged()) {
					$DEFAULT_ERROR[] = 'ERROR: Cannot change status until all units have been green tagged...';
					break 2;
				}
				if (!$sol->isFullyShipped()) {
					$DEFAULT_ERROR[] = 'ERROR: Cannot change status until all units have been assigned a actual ship date...';
					break 2;
				}
				if (!$sol->hasAllTasksClosed()) {
					$DEFAULT_ERROR[] = 'ERROR: Cannot change status until all tasks are closed...';
					break 2;
				}
				if ($sol->hasMissingExportLicence()) {
					$DEFAULT_ERROR[] = 'ERROR: Cannot change status until the export licence has been marked as OBTAINED (and uploaded)...';
					break 2;
				}
				break;
			case 'SHIPPED':
				if (!$sol->isFullyGreenTagged()) {
					$DEFAULT_ERROR[] = 'ERROR: Cannot close SOL, one or more ERs have not been green tagged...';
					break 2;
				}
				if (!$sol->isFullyShipped()) {
					$DEFAULT_ERROR[] = 'ERROR: Cannot close SOL, one or more ERs have not been shipped...';
					break 2;
				}
				if (!$sol->isAPCFullySet()) {
					$DEFAULT_ERROR[] = 'ERROR: Cannot close SOL, one or more ERs have no airport code set...';
					break 2;
				}
				// TLD PV (id55) LEB (id68) doesn't generate manuals (yet)
				if (!$sol->isFullyLinkedToManual() && 55 !== (int)$sol->getFactoryID() && 68 !== (int)$sol->getFactoryID()) {
					$DEFAULT_ERROR[] = 'ERROR: Cannot close SOL, one or more ERs have no manual...';
					break 2;
				}
				if (!$sol->hasAllTasksClosed()) {
					$DEFAULT_ERROR[] = 'ERROR: Cannot close SOL, all tasks are not closed...';
					break 2;
				}
				break;
		}

		// Default text/info in form
		switch ($currentStatus) {
			case 'CREATE_FACTORY_SO':
				$flagPSM = true;
				break;
			case 'ENGINEER_REVIEW':
				$defaults = [
					'comment' => "CBOM / Design Required?\r\nDesign Schedule?\n\nEngineer review comments:",
				];
				break;
			case 'ENGINEERING_APPROVAL':
				$defaults = [
					'comment' => "CBOM / Design Required?\r\nDesign Schedule?\n\nEngineering approval comments:",
				];
				break;
			case 'MATERIALS_PLANNING':
				$defaults = [
					'comment' => "MPS / PRP / CBOM\r\nSchedule / Assy Start\r\nMore info required?\n\nMaterials planning comments:",
				];
				break;
			case 'PSM_APPROVAL':
				$defaults = [
					'comment' => "Promise Date Confirmed?\r\nSchedule OK?\r\nInformation required from SSO?\n\nPSM approval comments:",
				];
				break;
			case 'SHIPPED':
				$defaults = [
					'comment' => "BOL / UHF / CERTS Attached\n\nShipped comments:",
				];
				break;
		}
		$form = new HTML_QuickForm('frmChangeStatus', 'post');
		$form->addElement('header', 'title', 'Change status to ->' . $allowed['fwd']);
		$form->addElement('hidden', 'm[0]', 'sol');
		$form->addElement('hidden', 'm[1]', 'view');
		$form->addElement('hidden', 'm[2]', 'changeStatus');
		$form->addElement('hidden', 'id', $id);
		$form->addElement('textarea', 'comment', 'Comment', ['rows' => 10, 'cols' => 40]);
		if ($flagPSM) {
			$form->addElement('text', 'factory_margin', 'Projected Factory Margin (%)');
			$form->addElement('checkbox', 'engineering_flag', 'Engineering Flag');
			$form->addElement('select', 'export_licence_status', 'Export Licence', array_combine(tldSOL::getExportLicenceStatuses(), tldSOL::getExportLicenceStatuses()));
			$form->addElement('textarea', 'comment_psm', 'Margin comment (optional)', ['rows' => 5, 'cols' => 40]);
			$form->addRule('factory_margin', 'Required', 'required');
			$form->addRule('export_licence_status', 'Required', 'required');
			$defaults['factory_margin'] = $header['factory_margin'];
			$defaults['engineering_flag'] = $header['engineering_flag'];
			$defaults['export_licence_status'] = $header['export_licence_status'];
		}
		$ccForm =& $form->addElement(
			'advmultiselect', 'cc_users', null,
			tldDirectory::getUserlist('smartyOptions'),
			[
				'size' => 10,
				'class' => 'pool',
				'style' => 'width:200px;',
			]
		);
		$ccForm->setLabel(['CC others (OPTIONAL)', 'Addressbook', 'CC']);
		$ccForm->setButtonAttributes('add', ['value' => '-->>', 'class' => 'inputCommand']);
		$ccForm->setButtonAttributes('remove', ['value' => '<<--', 'class' => 'inputCommand']);
		$form->addElement('file', 'filename', 'File (OPTIONAL)');
		if (!empty($allowedStatus['back'])) {
			$form->addElement('submit', 'direction', 'Backwards', ['class' => 'noDisableOnSubmit']);
		}
		$form->addElement('submit', 'direction', 'Forwards', ['class' => 'noDisableOnSubmit']);
		$form->setDefaults($defaults);
		$form->setDefaults(['cc_users' => $listCC]);

		if (!$form->validate()) {
			$body .= $form->toHTML();
			break;
		}

		$a = $form->exportValues();
		$parsedComment = str_replace(["\r\n", "\r", "\n"], '<br/>', $a['comment']);
		$a = tldUtils::cleanupFormInput($a);
		if ($flagPSM) {
			$parsedCommentPsm = str_replace(["\r\n", "\r", "\n"], '<br/>', $a['comment_psm']);
			if (!is_numeric($a['factory_margin']) || $a['factory_margin'] < 0 || $a['factory_margin'] > 100) {
				$DEFAULT_ERROR[] = 'ERROR: Factory Margin is in percent. It has to be numeric and between 0 and 100!';
				break;
			}
		}
		// look status

		$status = $allowedStatus['fwd'];
		if ($currentStatus === 'PSM_APPROVAL'
			&& !empty($m[3]) && $m[3] === 'force_PSM_APPROVAL'
			&& strtolower($_SERVER['REQUEST_METHOD']) === 'post'
			&& $user->isInGroup(['role_PSM', 'role_PSE', 'role_PSA'])
		) {
			$status = $allowedStatus['fwd2'];
		}

		if ($a['direction'] === 'Backwards') {
			$status = $allowedStatus['back'];
		}
		// Get cc list
		$CC = [];
		if (count($a['cc_users'] ?? []) > 0) {
			foreach ($a['cc_users'] as $userid) {
				$ccUser = new tldUser($userid);
				$ccUserEmail = $ccUser->getEmail();
				if (empty($ccUserEmail)) {
					continue;
				}
				$CC[] = $ccUserEmail;
				if (!in_array($userid, $listCC, false)) {
					$sol->addCC($userid);
				}
			}
			$CC = array_unique($CC);
		}
		// Update the status
		$error = $sol->changeStatus(
			$status,
			$user->getID(),
			[
				'msg' => $parsedComment,
				'cc' => $CC,
			]
		);
		if (is_string($error)) {
			$DEFAULT_ERROR[] = "ERROR: there was a problem changing status, returned error was... $error<br>";
			break;
		}

		// Post action
		switch ($status) {
			case 'PRINT_SO_ACK':
				$buyer = new tldCustomer($header['buyer_customer_id']);
				$contactBuyer = $buyer->getContactList();
				if (empty($contactBuyer) && $buyer->getCustomerName() !== '**STOCK**') {
					$taskMsg = <<<EOF
New SOL Creation Process
{$buyer->getCustomerName()} (#{$buyer->getID()})
	    
There is no customer contact for this eCustomer. Please go to the contacts section of this eCustomer and create a contact person with contact details (phone and email).
	    
<a href="https://www.tld-gse.com/en/private/sales_service/sales.php?m[0]=customers&m[1]=view&m[2]=contacts&m[3]=add&id={$buyer->getID()}">Click here to create a new {$buyer->getCustomerName()} contact</a>
	    
This will be required to organise commissioning and further communication for after-sales purposes.
EOF;
					// Prepare data
					$asmID = $buyer->getAsmID();
					if (empty($asmID)) {
						$asmID = $header['asmID'];
					}
					$user = new tldUser($asmID);
					$a = tldUtils::cleanupFormInput([
						'assignee' => $asmID,
						'assignor' => $asmID,
						'task' => $taskMsg,
						'bu_id' => $user->itsDetails['bu_id'],
						'due_date' => ['value' => 6, 'unit' => 'DAY'],
					]);
					// Create task
					$taskid = tldTask::insert(0, $a, 'USER');
					if (is_numeric($taskid)) {
						$task = new tldTask($taskid);
						$task->notifyAssignee(
							"<br><br><a href=\"https://www.tld-gse.com/en/private/calendar/calendar.php?m[0]=tasks&m[1]=task&m[2]=view&id=$taskid\">Click here to see task</a>",
							'New SOL Creation Process');

						error_log("Task #$taskid opened for " . $user->getFullname() . "\n");
					}
				}
				if ($header['buyer_customer_id'] !== $header['user_customer_id']) {
					$endUser = new tldCustomer($header['user_customer_id']);
					$contactEndUser = $endUser->getContactList();
					if (empty($contactEndUser)) {
						$taskMsg = <<<EOF
New SOL Creation Process
{$endUser->getCustomerName()}  (#{$endUser->getID()})
	    
There is no customer contact for this eCustomer. Please go to the contacts section of this eCustomer and create a contact person with contact details (phone and email).
	    
This will be required to organise commissioning and further communication for after-sales purposes.
EOF;
						// Prepare data
						$asmID = $endUser->getAsmID();
						if (empty($asmID) || $asmID == 0) {
							$asmID = $header['asmID'];
						}
						$user = new tldUser($asmID);
						$a = tldUtils::cleanupFormInput([
							'assignee' => $asmID,
							'assignor' => $asmID,
							'task' => $taskMsg,
							'bu_id' => $user->itsDetails['bu_id'],
							'due_date' => ['value' => 6, 'unit' => 'DAY'],
						]);
						// Create task
						$taskid = tldTask::insert(0, $a, 'USER');
						if (is_numeric($taskid)) {
							$task = new tldTask($taskid);
							$task->notifyAssignee(
								"<br><br><a href=\"https://www.tld-gse.com/en/private/calendar/calendar.php?m[0]=tasks&m[1]=task&m[2]=view&id=$taskid\">Click here to see task</a>",
								'New SOL Creation Process');

							error_log("Task #$taskid opened for " . $user->getFullname() . "\n");
						}
					}
				}
				break;
			case 'PRINT_FACTORY_SO_ACK':
				if ($currentStatus === 'ENGINEER_REVIEW') {
					// If we are going back in the workflow, we don't need to check update all those values.
					break;
				}

                if (($newExportLicenceStatus = $a['export_licence_status']) !== ($oldExportLicenceStatus = $sol->itsHeader['export_licence_status'])) {
                    $updateExportLicenceQueryResult = $sol->updateHeader(['export_licence_status' => $newExportLicenceStatus]);

                    if (is_string($updateExportLicenceQueryResult)) {
                        $DEFAULT_ERROR[] = "ERROR : there was a problem inserting the Export Licence status";
                    } else {
                        $resultChangeExportLicence = $sol->onChangeExportLicenseStatus($user, $oldExportLicenceStatus, $newExportLicenceStatus, $currentStatus);
                        if (is_string($resultChangeExportLicence)) {
                            $DEFAULT_ERROR[] = "ERROR on change Export licence status : $resultChangeExportLicence";
                        }
                    }
                }

                // Look for delivery penalty
				if (!$sol->isDeliveryPenaltyAcceptedByFactory()) {
					$sol->notifyDeliveryPenaltyRejectionByFactory($user->getEmail());
				}
				// Update Margin and Notify
				$errorMargin = $sol->updateMargin($a['factory_margin']);
				if (is_string($errorMargin)) {
					$DEFAULT_ERROR[] = "ERROR: there was a problem inserting the Factory Margin, returned error was... $errorMargin<br>";
					break;
				}

				if (isset($a['engineering_flag'])) {
					$errorEngFlag = $sol->updateHeader(['engineering_flag' => $a['engineering_flag']]);
					if (is_string($errorEngFlag)) {
						$DEFAULT_ERROR[] = "ERROR: there was a problem inserting the Engineering Flag, returned error was... $errorEngFlag<br>";
						break;
					}
				}

				$data = $sol->getSummary();
				$base = tldSOROpts::byParent($id, ['include' => ['BASE UNIT']]);
				$data['base_tp_cur'] = $base[0]['pric_cur'];
				$data['factory_margin'] = $a['factory_margin'];
				$data['sso_fullname'] = $header['sso_fullname'];
				$data['user_customer_display'] = $header['user_customer_display'];
				$data['buyer_customer_display'] = $header['buyer_customer_display'];
				$data['bu_fullname'] = $header['bu_fullname'];
				$data['model'] = $header['model'];
				$data['cur_prin'] = $sol->getDcur();
				$data['cur_tp'] = $sol->getDcur();
				$data['qty_sou'] = $header['qty_sou'];
				$data['qty_alloc'] = $header['qty_alloc'];
				$data['qty_ship'] = $header['qty_ship'];
				$data['group_factory_margin'] = round((($data['marg_unit_pc'] / 100) + (1 - ($data['marg_unit_pc'] / 100)) * ($data['factory_margin'] / 100)) * 100, 2);
				$message = "A new Projected Margin was entered for SOL#$id:<br><br>$parsedCommentPsm<br><br>";
				$marginForm = new tldAssocTable(
					$data,
					[
						'sso_fullname' => 'SSO',
						'user_customer_display' => 'Customer Name (END USER)',
						'buyer_customer_display' => 'Customer Name (BUYER)',
						'bu_fullname' => 'Factory',
						'model' => 'Model',
						'qty_sou' => 'Quantity Ordered',
						'qty_alloc' => 'Quantity Assigned',
						'qty_ship' => 'Quantity Shipped',
						'cur_prin' => 'Unit Net Selling Price Currency',
						'prin_unit' => 'Unit Net Selling Price',
						'pris_tp_in_dcur' => 'Negociated TP in Unit Net Selling Price Currency',
						'base_tp_cur' => 'Base Unit Negotiated TP Currency',
						'discc_pc' => 'Customer Discount (%)',
						'discf_pc' => 'Factory Discount (%)',
						'marg_unit_pc' => 'Sales Margin (%)',
						'factory_margin' => 'Projected Margin (%)',
						'group_factory_margin' => 'Projected Group Margin (%)',
					],
					['title' => 'SOL Information']
				);
				$message .= $marginForm->fetch();
				$message .= "<br><a href='https://www.tld-gse.com/en/private/sales_service/sales.php?m[0]=sol&m[1]=view&m[2]=lines&id=$id'>Please click here to access SOL#$id</a>";

				$e = tldGroup::emailMultipleGroups([
					900 => ['role_CFO', 'role_COO'],
					$header['bu_erp'] => ['role_PSM', 'role_PSE', 'role_PSA', 'role_COO', 'role_CFO', 'role_FC', 'role_CEO', 'role_RCOO']],
					'noreply@tld-gse.com',
					"SOL#$id, " . $header['bu_fullname'] . ' - Projected Margin',
					$message
				);
				if (is_string($e)) {
					$DEFAULT_ERROR[] = "ERROR: Problem sending Projected Margin notification email, error returned was $e";
				}
				break;
		}

		// Add log
		$m = "Status moved to $status";
		if ($a['comment']) {
			$m .= "\n\n" . $a['comment'];
		}
		$sol->addLogEntry($user->getID(), $m);
		// Look for a file to add
		$file = $form->getElement('filename');
		$file_array = $file->getValue();
		if ($file_array['tmp_name'] != '') {
			$e = $sol->addFile(
				[
					'poster' => $user->getID(),
					'description' => "File posted from status change to $status",
					'filename' => "sol_$id\_file_$status",
				],
				$file_array
			);
			if (is_string($e)) {
				$DEFAULT_ERROR[] = "INTERNAL ERROR: There was a problem attaching the file<br>$e...";
			} else {
				$DEFAULT_ERROR[] = 'File successfully attached';
			}
		}
		// Refresh header and display SOL
		$sol->refresh();
		$header = $sol->itsHeader;
		$body .= getGeneralTab($header);
		break;
}


function getEVP_APPROVALMessage()
{
    /** @var tldSOL $sol */
	$sol = &$GLOBALS['sol'];
	$sorid = $sol->getParent();
	$sor = new tldSOR($sorid);
	// SOR info
	$report = new tldAssocTable(
		$sor->itsHeader,
		[
			'id' => 'SOR#',
			'location' => 'Sales BU',
			'asm_fullname' => 'Area Sales Manager',
			'status' => 'Status',
			'dt_entered' => 'Date',
			'eqno' => 'eQuote#',
			'orno' => 'SSO SO#',
			'user_customer_display' => 'Customer Name (END USER)',
			'cu_orno' => 'Customer PO#',
			'buyer_customer_display' => 'Customer Name (BUYER)',
			't_cuno' => 'Customer ERP ID',
			'cu_new' => 'New customer?',
			'agnt_nama' => 'Sales Agent Name',
		],
		['title' => "Sales Order Record #$sorid"]
	);
	$msg = $report->fetch();
	// SOL info
	$msg .= <<<EOF
<br>
<a href="https://www.tld-gse.com/en/private/sales_service/sales.php?m[0]=sol&m[1]=view&id=$sol->itsID">Go to SOL online</a>
<br>
EOF;
	$report = new tldAssocTable(
		$sol->getHeader(),
		[
			'parent_id' => 'SOR#',
			'id' => 'SOL#',
			'sls_orno' => 'SSO PO#',
			'erp_orno' => 'Factory SO#',
			'spacer1' => '---spacer---',
			'status' => 'Status',
			'bu_fullname' => 'Factory BU',
			'model' => 'Model',
			'qty_sou' => 'Quantity Ordered',
			'qty_alloc' => 'Quantity Assigned',
			'qty_ship' => 'Quantity Shipped',
			'ctry' => 'Country of Sale',
			'spacer2' => '---spacer---',
			'parts_inc' => 'Ship with Spare Parts?',
			'notes' => 'Notes',
			'spacer3' => '---spacer---',
			'tpay' => 'Payment Terms',
			'conf_cxo' => 'Payment terms 100% after shipment or Has the downpayment been received (if any) or Has the LC been opened (if any)',
			'conf_lc' => 'Letter of Credit Required?',
			'cu_ocur' => 'Customer Order Currency',
			'dp_amt' => 'Down Payment Amount',
			'dp_pc' => 'Down Payment %',
		],
		['title' => 'SOL Information']
	);
	$msg .= $report->fetch();
	$msg .= getSummaryTab(
		$sol,
		$GLOBALS['smarty'],
		$GLOBALS['PATH'],
		$sol->getDCUR(),
		'sso'
	);
	return $msg;
}
