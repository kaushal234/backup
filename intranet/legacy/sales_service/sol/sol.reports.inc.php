<?php
$DEFAULT_TITLE .= "\Reports";

require_once('HTML/QuickForm/advmultiselect.php');

switch ($m[2]) {

	case 'SalesAdminReport':
		$DEFAULT_TITLE .= "\SalesReport";
		$ssoList = tldLocation::getSalesOrgList('smartyOptionsIDLocation');
		$asmList = tldCustomer::getAsmList();

		if (!$user->isInGroup(['role_SA', 'role_SAM', 'gg_ADMIN'])) {
			$DEFAULT_ERROR[] = 'You do not have permissions';
			return;
		}

		$form = new HTML_QuickForm('frmSalesAdminReport', 'post');
		$form->addElement('header', 'title', 'SOL report');
		$form->addElement('hidden', 'm[0]', 'sol');
		$form->addElement('hidden', 'm[1]', 'reports');
		$form->addElement('hidden', 'm[2]', 'SalesAdminReport');

		$solStatuses = tldSol::getStatusList();
		$statusForm = $form->addElement(
			'advmultiselect', 'x', null,
			array_combine($solStatuses, $solStatuses),
			[
				'size' => 10,
				'class' => 'pool',
				'style' => 'width:200px;',
			]
		);
		$statusForm->setLabel(['Status']);
		$statusForm->setButtonAttributes('add', ['value' => '-->>', 'class' => 'inputCommand']);
		$statusForm->setButtonAttributes('remove', ['value' => '<<--', 'class' => 'inputCommand']);

        $asmList = tldCustomer::getAsmList();
        $asmForm = $form->addElement(
            'advmultiselect', 'y', null,
            $asmList,
            [
                'size' => 10,
                'class' => 'pool',
                'style' => 'width:200px;',
            ]
        );
        $asmForm->setLabel(['ASM']);
        $asmForm->setButtonAttributes('add', ['value'=>'-->>','class'=>'inputCommand']);
        $asmForm->setButtonAttributes('remove', ['value'=>'<<--','class'=>'inputCommand']);

        $form->addElement('checkbox', 'xls', 'Download XLS');

		$isGranted = $user->isInGroup(['role_SAM', 'gg_ADMIN']);

		if ($isGranted) {
			$form->addElement('select', 'sso', 'SS0', ["" => ""] + $ssoList);
			$form->addElement('date', 'start', 'Start (GT Date)', ['class' => 'datepicker']);
			$form->addElement('date', 'end', 'End', ['class' => 'datepicker']);
		}

		$form->addElement('submit', 'btnSubmit', 'Submit');
		$form->setDefaults([
			'start' => date("Y-m-d"),
			'end' => date("Y-m-d"),
			'sso' => $user->getBUID(),
		]);

		$vars = tldUtils::cleanupFormInput($form->exportValues());
		$vars['x'] = !isset($vars['x']) ? $solStatuses : $vars['x'];
		$vars['y'] = !isset($vars['y']) ? array_keys($asmList) : $vars['y'];

		$start = !isset($vars['start']) || $vars['start'] === $vars['end'] ? null : new DateTime(implode("-", $vars['start']));
		$end = !isset($vars['end']) || $vars['end'] === $vars['start'] ? null : new DateTime(implode("-", $vars['end']));
		$sso = !isset($vars['sso']) ? null : $vars['sso'];

		if ($vars['start'] > $vars['end']) {
			$DEFAULT_ERROR[] = 'The starting date must be before the ending date';
			return;
		}

		if (!$form->validate()) {
			$body = $form->toHTML();
			break;
		}

		$rows = tldSOL::getSalesAdminReport($vars['y'], $vars['x'], $sso, $start, $end);

        $sols = [];
        foreach ($rows as $row) {
            if (!isset($sols[$row['id']])) {
                $sols[$row['id']] = [];
            }
            $sols[$row['id']][] = $row;
        }
        $sols = array_map(static function (array $units) {
            $unitsWithEquipmentRecords = array_filter($units, function ($solUnit) {
                return (bool)$solUnit['er'];
            });
            if (empty($unitsWithEquipmentRecords)) {
                return [$units[0]];
            }
            return $unitsWithEquipmentRecords;
        }, $sols);
        $rows = array_merge(...$sols);

        $xItems = [
		'location' => 'Factory BU',
		'fullname' => 'ASM',
		'customer_name' => 'buyer name',
		'parent_id' => 'SOR ID#',
		'id' => 'SOL ID#',
		'orno' => 'SO',
		'sls_orno' => 'PO',
		'model' => 'Model',
		'sn' => 'SN',
		'inco' => 'INCO',
		'inco_loc' => 'INCO location',
		'conf_cxo' => 'Down Payment Received',
		'conf_cis' => 'Customer inspection before shipment',
		'esrid' => 'ESR',
		'dgt_rev' => 'Est GT',
		'dgt_act' => 'GT Date',
		'date_shipped' => 'Date shipped',
		'notes' => 'Commentaire',
	    ];

		$intCaty = tldSOL::getInternalCategoriesList();
		$extCaty = tldSOL::getExternalCategoriesList();

		$linkGenerator = function ($items, $module, $erp) {
			$items = preg_split("/(\/|,)/", $items);

			return array_map(function ($item) use ($erp, $module) {
				$id = trim($item);
				return sprintf('<a href="/en/private/finance/finance.php?m[0]=%s&m[1]=view&id=%s&erp=%s" target="_blank" >%s</a>', $module, $id, $erp, $id);
			}, $items);
		};

		foreach ($rows as &$sol) {

		    $sol['sls_orno'] = implode(',', $linkGenerator($sol['sls_orno'], 'po', $sol['bu']));
            $sol['orno'] = implode(',', $linkGenerator($sol['orno'], 'so', $sol['bu']));
		    $sol['notes'] = ($sol['status'] === 'PENDING' && $sol['sor_notes'] !== '' && $sol['sor_notes'] !== null ) ? $sol['sor_notes'] : $sol['sol_notes'];

			if ($isGranted) {

				$xItems = array_merge($xItems, [
					'pris_unit' => 'Unit Gross Seling price',
					'prin_unit' => 'Unit Net Selling Price',
					'marg_unit' => 'Unit margin',
					'rrd_sso' => 'Revenue recognition Date SSO',
					'rrd_erp' => 'Revenue recognition Date ERP',
					'ddel_est1' => 'Factory Promised Delivery Date',
					'airport_code' => 'Final Destination',
				]);

				$id = $sol['id'];
				$dcur = $sol['cu_ocur'];

				// Get all internal transaction
				$intTotals = tldSOROpts::totalsByParent($id, ['include' => $intCaty, 'dcur' => $dcur]);
				// Get all external transaction
				$extTotals = tldSOROpts::totalsByParent($id, ['include' => $extCaty, 'dcur' => $dcur]);
				// Get other external transaction (ONLY parts and components)
				$otherTotals = tldSOROpts::totalsByParent(
					$id,
					[
						'include' => ['SPARE PARTS', 'COMPONENTS'],
						'dcur' => $dcur,
					]
				);

				//	 Unit Gross Selling Price = Internal + External transaction
				$a['pris_unit'] = $intTotals['pris_tot_in_dcur'] + $extTotals['pris_tot_in_dcur'];

				//	 Unit Net Selling Price = Unit Gross Selling Price - External transactions cost
				$a['prin_unit'] = $a['pris_unit']
					- $extTotals['pric_tot_in_dcur']
					+ $otherTotals['pric_tot_in_dcur'];

				// Unit cost = Negotiated TP + External transaction cost
				$a['cost_unit'] = $intTotals['pric_tot_in_dcur'] + $extTotals['pric_tot_in_dcur'];

				//	 Unit margin = Gross Selling Price 锟�Unit Cost
				$a['marg_unit'] = $a['pris_unit'] - $a['cost_unit'];

				$sol['pris_unit'] = $a['pris_unit'];
				$sol['prin_unit'] = $a['prin_unit'];
				$sol['marg_unit'] = $a['marg_unit'];

			};
		}

		$report = new tldReportColumnar(
			$rows,
			[
				'xItems' => $xItems,
				'title' => 'Sales Admin Report',
				'links' => [
					'parent_id' => [
						'url' => "$php_self?m[0]=sor&m[1]=view",
						'params' => ['id' => 'parent_id'],
						'target' => '_blank'],
					'id' => [
						'url' => "$php_self?m[0]=sol&m[1]=view",
						'params' => ['id' => 'id'],
						'target' => '_blank'],
					'sn' => [
						'url' => '/en/private/product_support/index.ps.php?m[0]=equipment&m[1]=search&m[2]=bySN',
						'params' => ['sn' => 'sn'],
						'target' => '_blank'],
					'esrid' => [
						'url' => '/en/private/sales_service/sales.php?m[0]=esr&m[1]=view',
						'params' => ['id' => 'esrid'],
						'target' => '_blank'],
				],
			]
	);

	$body .= $report->fetch();

	if ($vars['xls']) {
		$report = new tldXLS(
			$rows,
			array(
				"xItems" => $xItems,
				"showTitles" => true
			)
		);
		$report->out("SalesAdminReport.xls");
		exit;
	}

	break;

	case 'SOLOption':
		$DEFAULT_TITLE .= "\SOL Options Search";
		// listing
		$factoryList = array_column(tldLocation::byConstraints('id IN (SELECT DISTINCT bu from sor_lines)'),'location', 'id') ;
		// Form
		$form = new HTML_QuickForm('frmSOLOptionSearch', 'post');
		$form->addElement('header', 'title', 'Search SOL by options');
		$form->addElement('hidden', 'm[0]', 'sol');
		$form->addElement('hidden', 'm[1]', 'reports');
		$form->addElement('hidden', 'm[2]', 'SOLOption');
		$form->addElement('text', 'target', 'Look for (SOL Options)...', ["size" => "20"]);
		$form->addElement('select', 'bu', 'Factory', ["" => ""] + $factoryList);
		$form->addElement('select', 'model', 'Model', ["" => ""] + tldModel::getList());
		$form->addElement('date', 'start', 'Start (Opening Date)', ["format" => "Y-m-d", 'addEmptyOption' => false, "minYear" => date("Y") - 10, "maxYear" => date("Y")]);
		$form->addElement('date', 'end', 'End', ["format" => "Y-m-d", 'addEmptyOption' => false, "minYear" => date("Y") - 3, "maxYear" => date("Y")]);
		$form->addElement('submit', 'btnSubmit', 'Submit');
		$form->addRule('target', 'This is required', 'required');
		$form->addRule('bu', 'This is required', 'required');
		$form->setDefaults(['start' => date("Y-m-d", strtotime('first day of -3 months')), 'end' => date("Y-m-d", strtotime('last day of last month'))]);

		if (!$form->validate()) {
			$body = $form->toHTML();
			break;
		}

		$vars = tldUtils::cleanupFormInput($form->exportValues());
		$start = vsprintf('%1$04d-%2$02d-%3$02d', $vars['start']);
		$end = vsprintf('%1$04d-%2$02d-%3$02d', $vars['end']);
		$rows = tldSOL::searchOptions($vars['target'], $vars['bu'], $start, $end, $vars['model']);
		$TITLE = "SOL Options Search for '{$vars['target']}' between $start and $end in {$factoryList[$vars['bu']]}";
		$xItems = [
			"parent_id" => "SOR ID#",
			"id" => "SOL ID#",
			"dt_opened" => "Date Opened",
			"status" => "Status",
			"dt_create_factory" => "Create Factory Date",
			"user_customer_display" => "Customer Name (END USER)",
			"buyer_customer_display" => "Customer Name (BUYER)",
			"erp_fullname" => "Factory",
			"sls_orno" => "PO#",
			'erp_orno' => 'Factory SO#',
			"model" => "Model",
			"qty_sou" => "Qty Ordered",
			"qty_alloc" => "Qty Assigned",
			"qty_ship" => "Qty Shipped",
			"conf_sls" => "Delivery Penalty Accepted by SSO",
			"conf_erp" => "Delivery Penalty Accepted by Factory",
		];
		$report = new tldReportColumnar(
			$rows,
			[
				"xItems" => $xItems,
				"title" => "$TITLE",
				"links" => [
					"parent_id" => "$php_self?m[0]=sor&m[1]=view&id=",
					"id" => "$php_self?m[0]=sol&m[1]=view&id=",
				],
				"functions" => [
					"Option details" => [
						"url" => "$php_self?m[0]=sol&m[1]=view&m[2]=options&id=",
						"param" => ["id" => "id"],
						"target" => "_blank",
					],
				],
			]
		);
		$body .= $report->fetch();
		break;
	case 'auditTransportationCost':
		// Check permissions
		if (!$user->isInGroup(["gg_ADMIN", "role_CEO", "role_EVP", "role_SA", "role_FC"])) {
			$DEFAULT_ERROR[] = "ERROR: you do not have permission for this report";
			break;
		}
		// listing
		$ssoList = tldLocation::getSalesOrgList("smartyOptions");
		$curList = tldList::optionsByListNameAsListItemListItem('list.common.currency');
		// Form
		$form = new HTML_QuickForm('frm', 'post');
		$form->addElement('hidden', 'm[0]', 'sol');
		$form->addElement('hidden', 'm[1]', 'reports');
		$form->addElement('hidden', 'm[2]', 'auditTransportationCost');
		$form->addElement('header', 'title', 'Transportation cost audit');
		$form->addElement('select', 'sso_erp', 'SSO', ['' => ''] + $ssoList);
		$form->addElement('select', 'cur', 'Currency', ['' => ''] + $curList);
		$form->addElement('date', 'dt_opened_from', 'Open from', ["format" => "Y-m-d", "minYear" => date('Y') - 3, 'maxYear' => date('Y')]);
		$form->addElement('date', 'dt_opened_to', 'Open to', ["format" => "Y-m-d", "minYear" => date('Y') - 3, 'maxYear' => date('Y')]);
		// required
		$requiredFields = ['sso_erp', 'cur', 'dt_opened_from', 'dt_opened_to'];
		foreach ($requiredFields as $field) {
			$form->addRule($field, 'Required', 'required');
		}
		// default
		$form->setDefaults([
			'sso_erp' => tldLocation::getIDByERP($user->getBUID()),
			'cur' => 'USD',
			'dt_opened_from' => date('Y-m') . '-01',
			'dt_opened_to' => date('Y-m-d'),
		]);
		// button
		$form->addElement('submit', 'btnSubmit', 'Submit');
		$body = $form->toHTML();

		if ($form->validate()) {
			$vars = tldUtils::cleanupFormInput($form->exportValues());
			$filters = ['sso_erp', 'dt_opened_from', 'dt_opened_to'];
			$a = [];
			foreach ($filters as $filter) {
				switch ($filter) {
					case 'dt_opened_from':
						$from = implode('-', $vars['dt_opened_from']);
						$to = implode('-', $vars['dt_opened_to']);
						$a[] = "sol.dt_opened BETWEEN '$from' AND '$to'";
						break;
					case 'sso_erp':
						$a[] = "sso.erp={$vars['sso_erp']}";
						break;
				}
			}
			$rows = tldSOL::getTransportationCostAuditByConstraints(implode(' AND ', $a), $vars['cur']);
		}

		$xItems = [
			"id" => "SOL#",
			"asm_fullname" => "ASM",
			"opening_date" => "Date",
			"status" => "Status",
			"sso_fullname" => "SSO",
			"erp_fullname" => "Factory",
			"buyer_customer_display" => "Customer Buyer",
			"ctry" => "Country",
			"model" => "Model",
			"inco" => "Inco terms",
			"qty_sou" => "Qty ordered",
			"qty_esr" => "Qty assigned to ESR",
			"cur" => "Currency",
			"total_sol_sales_cur" => "Total SOL Sales Price",
			"total_sol_cur" => "Total SOL Cost",
			"total_esr_cur" => "Total ESR Cost",
			"total_diff_cur" => "Total SOL/ESR Cost gap",
			"total_diff_sales_cur" => "Total SOL Sales/Cost gap",
		];

		switch ($out) {
			case 'xls':
				$report = new tldXLS(
					$sess['sol']['listing'],
					[
						"xItems" => $xItems,
						"showTitles" => true,
					]
				);
				$report->out("transportationCostAudit.xls");
				exit;
				break;
			default:
				// if no variables, form not submited yet
				if (!isset($rows)) {
					break;
				}
				// put data in session
				$sess['sol']['listing'] = $rows;
				// Link for xls extraction
				$DEFAULT_MENU .= <<<EOF
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<a href="$php_self?m[0]=sol&m[1]=reports&m[2]=auditTransportationCost&out=xls">XLS version</a>
EOF;
				// Display report
				$report = new tldReportColumnar(
					$rows,
					[
						"xItems" => $xItems,
						"title" => "Transportation cost audit",
						"links" => [
							"id" => "$php_self?m[0]=sol&m[1]=view&id=",
							"total_sol_cur" => [
								"url" => "$php_self?m[0]=sol&m[1]=view&m[2]=summary",
								"params" => ["id" => "id"],
							],
							"total_esr_cur" => [
								"url" => "$php_self?m[0]=sol&m[1]=view&m[2]=er",
								"params" => ["id" => "id"],
							],
						],
					]
				);
				$body .= $report->fetch();
				break;
		}
		break;
	case 'salesOrderAcknowledgment':
		// Check permissions
		if (!$user->isInGroup(['gg_ADMIN', 'role_CEO', 'role_EVP', 'role_SA', 'role_ASM'])) {
			$DEFAULT_ERROR[] = 'ERROR: you do not have permission for this report';
			break;
		}
		// listing
		$ssoList = tldLocation::getSalesOrgList("smartyOptions");
		$curList = tldList::optionsByListNameAsListItemListItem('list.common.currency');
		// Form
		$form = new HTML_QuickForm('frm');
		$form->addElement('hidden', 'm[0]', 'sol');
		$form->addElement('hidden', 'm[1]', 'reports');
		$form->addElement('hidden', 'm[2]', 'salesOrderAcknowledgment');
		$form->addElement('header', 'title', 'Sales Order Acknowledgment report');
		$form->addElement('text', 'since', 'SOL since', ['class' => 'datepicker']);
		$form->addElement('text', 'until', 'SOL until', ['class' => 'datepicker']);
		$form->addElement('submit', 'btnSubmit', 'Submit');
		$form->addRule('since', 'Required', 'required');
		// default
		$form->setDefaults([
			'since' => date('Y-m') . '-01',
		]);

		$body = $form->toHTML();

		if ($form->validate()) {
			$cells = [];
			$vars = tldUtils::cleanupFormInput($form->exportValues());
			$since = $vars['since'];
			$until = isset($vars['until']) ? $vars['until'] : null;

			$report = new tldMatrix(
				$eligibeSOLs = tldSOL::countSalesOrderEligibleToAcknowledgementBySSO($since, $until),
				null, 'location', 'uniqueSOLNumber',
				"$php_self?m[0]=sol&m[1]=listing&m[2]=bySOLEligibleToAcknowledgment&since=$since&until=$until",
				'Eligible SOL',
				[
					'doNotShowYTotals' => true,
				]
			);

			$cells[] = $report->fetch();

			$report = new tldMatrix(
				$acknowledgedSOLs = tldSOL::countSalesOrderAcknowledgementBySSO($since, $until),
				null, 'location', 'uniqueSOLNumber',
				"$php_self?m[0]=sol&m[1]=listing&m[2]=bySOLAcknowledged&since=$since&until=$until",
				'Acknowledged SOL',
				[
					'doNotShowYTotals' => true,
				]
			);

			$cells[] = $report->fetch();

			$acknowledgedSOLs = array_column($acknowledgedSOLs, 'uniqueSOLNumber', 'location');
			$delinquents = $eligibeSOLs;
			foreach ($delinquents as &$sol) {
				$sol['uniqueSOLNumber'] -= isset($acknowledgedSOLs[$sol['location']]) ? (int)$acknowledgedSOLs[$sol['location']] : 0;
			}
			$report = new tldMatrix(
				$delinquents,
				null, 'location', 'uniqueSOLNumber',
				"$php_self?m[0]=sol&m[1]=listing&m[2]=bySOLDeliquent&since=$since&until=$until",
				'Delinquent SOL',
				[
					'doNotShowYTotals' => true,
				]
			);

			$cells[] = $report->fetch();

			$table = new tldHTMLTable(
				$cells,
				[
					'cols' => 3,
					'attribs' => [
						'table' => " width='100%'",
						'tr' => " bgcolor='#FFFFFF'",
						'td' => " width='50%'",
					],
				]
			);
			$body .= $table->fetch();
		}

		break;
	case 'pricing':
		if (!$user->isInGroup(["gg_ADMIN", "role_EVP", "role_FC", "role_CFO"])) {
			$DEFAULT_ERROR[] = "ERROR: you do not have permission for this report";
			break;
		}
		$query = <<<EOF
    select sors.dt_entered, sols.id, sols.model, sols.ctry,
        ssos.location as sso_fullname,
        erps.location as erp_fullname,
        (SELECT cust.customer_name FROM customers AS cust WHERE cust.id=sors.buyer_customer_id) AS buyer_customer_display
from sor as sors
    join sor_lines as sols ON sors.id=sols.parent_id
    LEFT JOIN locations AS ssos ON sors.bu=ssos.erp
    LEFT JOIN locations AS erps ON sols.bu=erps.id

EOF;
		if ($m[3] === 'ytd') {
			$form = new HTML_QuickForm('frmSOLDate', 'post');
			$form->addElement('hidden', 'm[0]', 'sol');
			$form->addElement('hidden', 'm[1]', 'reports');
			$form->addElement('hidden', 'm[2]', 'pricing');
			$form->addElement('hidden', 'm[3]', 'ytd');
			$form->addElement('header', 'title', 'Select date:');
			$form->addElement('date', 'x', 'From',
				["format" => "Y", "minYear" => date('Y') - 5, "maxYear" => date('Y')]);
			$form->addElement('submit', 'btnSubmit', 'Submit');
			$form->addRule('x', 'This is required', 'required');
			$body .= $form->toHTML();
			if ($form->validate()) {
				$vars = tldUtils::cleanupFormInput($form->exportValues());
				$a = (int)$vars['x']['Y'];
				$query .= <<<EOF
		WHERE year(sors.dt_entered)>= $a
EOF;
				$rows = tldUtils::getSqlToAssocArray($query);
				foreach ($rows as $row) {
					$sol = new tldSOL($row['id']);
					$summary = $sol->getSummary();
					$row['discc_pc'] = $summary['discc_pc'];
					$row['discf_pc'] = $summary['discf_pc'];
					$row['marg_unit_pc'] = $summary['marg_unit_pc'];
					$row['qty'] = $summary['qty'];
					$r[] = $row;
				}
				$report = new tldReportColumnar(
					$r,
					[
						"xItems" => [
							"dt_entered" => "SOR Date",
							"id" => "SOL#",
							"model" => "Model",
							"qty" => "Qty",
							"ctry" => "Country",
							"sso_fullname" => "SSO",
							"erp_fullname" => "Factory",
							"buyer_customer_display" => "Customer Buyer",
							"discc_pc" => "Customer Discount%",
							"discf_pc" => "Factory Discount%",
							"marg_unit_pc" => "Unit Margin",
						],
						"title" => $TITLE,
					]
				);
				$DEFAULT_MENU .= <<<EOF
        <br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
        <a href="$php_self?m[0]=sol&m[1]=reports&m[2]=pricing&m[4]=xls&start=$a">Download XLS</a>
EOF;
				$body .= $report->fetch();
			}
		} else {
			$YEAR = date("Y") - 1;
			if ($m[4] === 'xls') {
				$query .= <<<EOF
				WHERE year(sors.dt_entered)>= $start
EOF;
			} else {
				$query .= <<<EOF
						WHERE year(sors.dt_entered)= $YEAR
EOF;
			}
			$rows = tldUtils::getSqlToAssocArray($query);
			foreach ($rows as $row) {
				$sol = new tldSOL($row['id']);
				$summary = $sol->getSummary();
				$row['discc_pc'] = $summary['discc_pc'];
				$row['discf_pc'] = $summary['discf_pc'];
				$row['marg_unit_pc'] = $summary['marg_unit_pc'];
				$row['qty'] = $summary['qty'];
				$r[] = $row;
			}

			$report = new tldXLS(
				$r,
				[
					"xItems" => [
						"dt_entered" => "SOR Date",
						"id" => "SOL#",
						"model" => "Model",
						"qty" => "Qty",
						"ctry" => "Country",
						"sso_fullname" => "SSO",
						"erp_fullname" => "Factory",
						"buyer_customer_display" => "Customer Buyer",
						"discc_pc" => "Customer Discount%",
						"discf_pc" => "Factory Discount%",
						"marg_unit_pc" => "Unit Margin",
					],
					"showTitles" => true,
				]
			);
			$report->out("pricing_data_$YEAR.xls");
			exit;
		}
		break;
	case "OpenTasksBySSOERPStatus":
		$DEFAULT_TITLE .= "\Open SOL Tasks";
		if (!$user->isInGroup(["gg_ADMIN", "role_COO", "role_PSM", "role_PSE", "role_PSA", "role_EM", "role_PM", "role_ENG", "gg_ENG", "role_QAM", "role_MLM", "role_PLANNER"])) {
			$DEFAULT_ERROR[] = "ERROR: you do not have permission for this report";
			break;
		}
		$form = new HTML_QuickForm('frm', 'get', "", "", "", true);
		$form->addElement('hidden', 'm[0]', 'sol');
		$form->addElement('hidden', 'm[1]', 'reports');
		$form->addElement('hidden', 'm[2]', 'OpenTasksBySSOERPStatus');
		$form->addElement('header', 'title', "View SOL Open Tasks by SSO, ERP, Status");
		$form->addElement('select', "x", 'SSO', ["" => "", "ALL" => "ALL"]
			+ tldLocation::getSalesOrgList("smartyOptionsLocationLocation")
		);
		$form->addElement('select', "y", 'Factory', ["" => "", "ALL" => "ALL"]
			+ tldLocation::getFactoryList("smartyOptionsLocationLocation")
			+ tldLocation::getSalesOrgList("smartyOptionsLocationLocation")
		);
		$solStatuses = tldSol::getStatusList();
		$statusForm = $form->addElement(
			'advmultiselect', 'z', null,
			array_combine($solStatuses, $solStatuses),
			[
				'size' => 10,
				'class' => 'pool',
				'style' => 'width:200px;',
			]
		);
		$statusForm->setLabel(['Status']);
		$statusForm->setButtonAttributes('add', ['value' => '-->>', 'class' => 'inputCommand']);
		$statusForm->setButtonAttributes('remove', ['value' => '<<--', 'class' => 'inputCommand']);
		$form->addElement('submit', 'btnSubmit', 'Submit');
		$form->addRule('x', 'This is required', 'required');
		$form->addRule('y', 'This is required', 'required');
		$form->addRule('z', 'This is required', 'required');
		if (!$form->validate()) {
			$body = $form->toHTML();
			break;
		}

		$vals = tldUtils::cleanupFormInput($form->exportValues());
		$rows = tldSOL::bySSOERPStatus($vals['x'], $vals['y'], $vals['z']);

		foreach ($rows AS $row) {
			$gantts[$row['id']] = tldTask::getGanttOverview('SOL', $row['id'], "status<>'CLOSED'");
			$sol = new tldSOL($row['id']);
			$meaps = $eaps = $subEaps = [];
			foreach ($sol->getLinksFromHere("MEAP") as $link) {
				$meaps[] = $link['item'];
			}
			foreach ($sol->getLinksToHere("MEAP") as $link) {
				$meaps[] = $link['parent_id'];
			}
			// get all tasks of MEAP and child EAP recursively
			foreach ($meaps as $module_id) {
				$gantts[$row['id']] = array_merge($gantts[$row['id']], (array)tldTask::getGanttOverview('MEAP', $module_id, "status<>'CLOSED'"));
				$meap = new tldMEAP($module_id);
				$family = $meap->getFamilyTree(true, true);
				foreach ($family as $mod) {
					if ($mod['module'] === 'EAP') {
						$subEaps[] = $mod['id'];
						$gantts[$row['id']] = array_merge($gantts[$row['id']], (array)tldTask::getGanttOverview('EAP', $mod['id'], "status<>'CLOSED'"));
					}
				}
			}
			foreach ($sol->getLinksFromHere("EAP") as $link) {
				$eaps[] = $link['item'];
			}
			foreach ($sol->getLinksToHere("EAP") as $link) {
				$eaps[] = $link['parent_id'];
			}
			$eaps = array_diff($eaps, $subEaps);
			foreach ($eaps as $module_id) {
				$gantts[$row['id']] = array_merge($gantts[$row['id']], (array)tldTask::getGanttOverview('EAP', $module_id, "status<>'CLOSED'"));
			}
		}

		if (!count($gantts)) {
			$DEFAULT_ERROR[] = "Unable to find any open tasks";
			break;
		}
		$DEFAULT_MENU .= <<<EOF
        <br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
        <a href="$php_self?m[0]=sol&m[1]=reports&m[3]=fullCSV&y=$y&x=$x&z=$z">Download tasks CSV</a>
EOF;
		$overlib = $smarty->fetch('overlib.inc.js.tpl');
		$overlib .= '<script type="text/javascript">$(function(){$("[data-body]").overlib()});</script>';
		$smarty->assign("html_head", $overlib);
		$smarty->assign("width", "100%");

		$groups = [];
		foreach ($gantts AS $sol => $gantt) {
			// Get SOL Info
			$solHeader = (new tldSOL($sol))->itsHeader;
			$enduser_cust = $solHeader['user_customer_display'];
			$model_name = $solHeader['model'];
			$min_est_GT = tldSOL::getMinEstGTDate($sol);
			$reports = [];
			$gCnt = count($gantt);
			if ($gCnt) {
				// Prepare gantt report
				$ovd = 0;
				foreach ($gantt AS &$task) {
					$task['late_stats'] = "Opened: {$task['date']} Due: {$task['due_date']}";
					if ($task['overdue']) {
						$ovd++;
						$task['late'] = 'OVERDUE';
						$task['late_stats'] .= " ({$task['days_late']} days late)";
					} elseif ($task['status'] !== 'CLOSED') {
						$task['late'] = 'ON TIME';
						$task['late_stats'] .= " (on time)";
					} else {
						$task['late'] = 'N/A';
						$task['late_stats'] .= " (closed)";
					}
					$task['last_ten_comments_html'] = str_replace("\n", "&lt;br/&gt;", htmlentities($task['last_ten_comments']));
					$task['task_add_comment'] = $task['id'];
					$task['task_reschedule'] = $task['id'];
					$task['task_transfer'] = $task['id'];
					$task['task_close'] = $task['id'];
					$task['customer'] = $enduser_cust;
					$task['status'] = $solHeader['status'];
					$task['min_est_GT'] = $min_est_GT;
					$task['model'] = $model_name;
				}
				$form = new tldGanttChart("SOL_OPENTASKS_$sol", $gantt, [
					// xItems
					'id' => 'ID',
					'assignor_lastname' => 'Assignor',
					'ifactor' => 'iFactor',
					'status' => 'Status',
					'assignee_fullname' => 'Assignee',
					'task' => 'Task Description',
					'date' => 'Days Open',
					'last_ten_comments_html' => 'Log',
					'late' => 'Late',
					'task_add_comment' => 'Add Comment',
					'task_reschedule' => 'Reschedule',
					'task_transfer' => 'Transfer',
					'task_close' => 'Close',
				], [
					// Options
					'parentAttributes' => [
						'table' => 'border="0"',
					],
					'sortable' => [],
					'links' => [
						'id' => "/en/private/calendar/calendar.php?m[0]=tasks&m[1]=task&m[2]=view&id=",
						'task_add_comment' => "/en/private/calendar/calendar.php?m[0]=tasks&m[1]=task&m[2]=comment&id=",
						'task_reschedule' => "/en/private/calendar/calendar.php?m[0]=tasks&m[1]=task&m[2]=reschedule&id=",
						'task_transfer' => "/en/private/calendar/calendar.php?m[0]=tasks&m[1]=task&m[2]=transfer&id=",
						'task_close' => "/en/private/calendar/calendar.php?m[0]=tasks&m[1]=task&m[2]=closeConfirm&id=",
					],
					'groupAttributes' => [
						'late' => [
							'OVERDUE' => 'style="font-weight:bold;background:#900;color:#fff;"',
							'ON TIME' => 'style="font-weight:bold;background:#090;color:#fff;"',
						],
					],
					'groupHeaders' => [
						[
							'columns' => ['task_add_comment', 'task_reschedule', 'task_transfer', 'task_close'],
							'title' => 'Actions',
						],
					],
					'columnSettings' => [
						'id' => [
							'callback' => 'intval',
							'title' => 'ID #%d',
						],
						'ifactor' => [
							'type' => 'ifactor',
						],
						'task' => [
							'type' => 'description',
						],
						'last_ten_comments_html' => [
							'icon' => 'log',
							'useIconValue' => true,
							'cellAttributes' => 'data-body="%s"',
							'imgTitle' => 'last_ten_comments',
							'tdTitle' => 'last_ten_comments',
						],
						'date' => [
							'type' => 'gantt',
							'zoom' => 'day',
							'due' => 'due_date',
						],
						'late' => [
							'tdTitle' => 'late_stats',
						],
						'task_add_comment' => [
							'callback' => 'intval',
							'icon' => 'mail',
							'useIconHeader' => true,
							'useIconValue' => true,
							'title' => "Add New Comment Task#%d",
						],
						'task_reschedule' => [
							'callback' => 'intval',
							'icon' => 'schedule',
							'useIconHeader' => true,
							'useIconValue' => true,
							'title' => "Reschedule Task#%d",
						],
						'task_transfer' => [
							'callback' => 'intval',
							'icon' => 'transfer',
							'useIconHeader' => true,
							'useIconValue' => true,
							'title' => "Transfer Task#%d",
						],
						'task_close' => [
							'callback' => 'intval',
							'icon' => 'close',
							'useIconHeader' => true,
							'useIconValue' => true,
							'title' => "Close Task#%d",
						],
					],
				]);

				$groups[] = <<<EOF
<h3>SOL#$sol / $enduser_cust / $model_name / Estimated GT: $min_est_GT</h3>
<p>
	<b>Open Tasks:</b> $gCnt &nbsp; <b>Overdue:</b> $ovd &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
	<a href="$php_self?m[0]=sol&m[1]=view&m[2]=lines&id=$sol" title="View SOL#$sol">View SOL</a>
</p>
{$form->fetch()}
<br/>
EOF;
				$soltasks[] = $gantt;
			}
		}
		$k = 0;
		foreach ($soltasks as $key => $val) {
			foreach ($val as $key2 => $val2) {
				$newhello[$k] = $val2;
				$k++;
			}
			$k++;
		}
		$sess['task']['list'] = $newhello;
		$body .= implode("\n<br/><hr/><br/>\n", $groups);
		break;

	default:
		switch ($m[3]) {
			case 'fullCSV':
				$option = ["xItems" => [
					// xItems
					'id' => 'Task ID',
					'parent_id' => 'SOL#',
					'model' => 'Model',
					'customer' => 'Customer Name',
					'assignor_fullname' => 'Assignor',
					'assignee_fullname' => 'Assignee',
					'min_est_GT' => 'First Unit EST GT',
					'task' => 'Description',
					'status' => 'Status',
				],
					"showTitles" => true];
				$report = new tldCSV($sess['task']['list'], $option);
				$report->out();
				exit;
				break;
		}
		$body = $smarty->fetch("$PATH/sol/reports/homepage.reports.tpl");
		break;
}
