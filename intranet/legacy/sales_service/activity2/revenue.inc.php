<?php
$DEFAULT_TITLE .= "\Revenue";

$xItemTrans = [
	'location_from' => 'SS
	O',
	'juridical_entity' => 'Juridical Entity',
	'location_to' => 'Factory',
	'asm_fullname' => 'ASM',
	'sor_id' => 'SOR ID#',
	'buyer_customer_display' => 'Customer (BUYER)',
	'user_customer_display' => 'Customer (END USER)',
	'cu_nama' => 'Customer Name',
	'agnt_nama' => 'Agent Name',
	'cu_orno' => 'Customer PO#',
	'ctry' => 'Country',
	'sol_id' => 'SOL ID#',
	'sls_orno' => 'SSO PO# to Factory',
	'inco' => 'Inco',
	'period' => 'Revenue Month',
	'sols_model' => 'Model Ordered',
	'tcur' => 'Unit Revenue Currency',
	'tval' => 'Unit Revenue Amount',
	'rate_cross' => 'Cross Rate',
	'tval_dcur' => "Unit Revenue Amount ($DCUR)",
	'notes' => 'Notes',
];

switch ($m[2]) {
	case 'groupMargins':
		// Check access
		if (!$user->isInGroup(['role_CFO', 'gg_ADMIN', 'role_COO', 'role_FC'])) {
			$DEFAULT_ERROR[] = 'ERROR: You do not have permissions to view this record...';
			break;
		}

		switch ($m[3]) {
			case 'fullCSV':
				$xItemsCSV = [
					'level' => 'Report level',
					'period' => ucfirst($m[1]) . ' month',
					'location_from' => 'SSO',
					'sol_id' => 'SOL ID#',
					'asm_fullname' => 'ASM',
					'cu_type' => 'Customer Type',
					'buyer_customer_display' => 'Customer (BUYER)',
					'user_customer_display' => 'Customer (END USER)',
					'cu_new' => 'New Customer',
					'intro_new' => 'New Introduction ?',
					'ctry' => 'Country',
					'continent' => 'Geographical customer area',
					'airport_code' => 'Airport Code',
					'location_to' => 'Factory',
					'del_dat' => 'Customer EXW Request',
					'ddel_est1' => 'Factory EXW Promise',
					'dgt_rev' => 'Estimated GT Date',
					'dgt_act' => 'Actual GT Date',
					'date_shipped' => 'Ship Date',
					'er_type' => 'ER type',
					'er_model' => 'ER model',
					'sn' => 'SN#',
					'esrl_dt_estim' => 'Estimated Date of Arrival',
					'del_ctry' => 'ER country',
					'dcur' => 'Default curency',
					'pris_unit_default_cur' => 'Unit Gross Selling Price in dcur',
					'pris_unit' => "Unit Gross Selling Price in $DCUR",
					'qty' => 'Quantity',
					'pris_xtot_default_cur' => 'Total Gross Selling Price in dcur',
					'pris_xtot' => "Total Gross Selling Price in $DCUR",
					'pris_tot_int_cur' => 'Total Internal Sales Price in dcur',
					'pris_tot_int_dcur' => "Total Internal Sales Price in $DCUR",
					'pris_base_unit_tot_cur' => 'Total Base Unit Sales Price in dcur',
					'pris_base_unit_tot_dcur' => "Total Base Unit Sales Price in $DCUR",
					'pric_tot_ext_dcur' => "Total External Cost Transactions in $DCUR",
					'pric_trans_tot' => 'Total Cost Transportation',
					'pric_tax_tot' => 'Total Cost Taxes and duties',
					'pric_parts_tot' => 'Total Cost Spare Parts',
					'pric_misc_tot' => 'Total Cost Misc items',
					'pric_agent_com_tot' => 'Total Cost Agent commission',
					'pric_spe_disc_tot' => 'Total Cost Special Discount',
					'pris_parts_comp_tot' => 'Total Sales Parts & Components',
					'prin_unit' => "Unit net selling price in $DCUR",
					'prin_xtot' => "Total net selling price in $DCUR",
					'inco' => 'Inco Terms',
					'inco_loc' => 'Inco Location',
					'pris_tp_default_curency' => 'Currency TP negociated',
					'pris_tp_default_dcur' => 'Unit Price TP negociated in currency TP',
					'pris_tp_default_dcur_tot' => 'Total Price TP negociated in currency TP',
					'pris_tp_in_dcur' => "Unit Price TP negociated in $DCUR",
					'pris_tp_in_dcur_tot' => "Total Price TP negociated in $DCUR",
					'marg_xtot' => "Total Margin in $DCUR",
					'group_margin_per' => 'Actual Group Direct margin (%)',
					'margin_percent' => 'Actual Sales Margin (%)',
					'dir_margin_per' => 'Actual Factory Margin (%)',
					'factory_rev' => 'Transfer Price',
					'act_hour' => 'Actual Hours',
					'std_hour' => 'Target Hours',
					'std_lab_cost' => 'Standard Labour Costs',
					'act_lab_cost' => 'Actual Labour Costs',
					'std_mat' => 'Standard Material',
					'act_mat' => 'Actual Material',
					'std_other_mat' => 'Standard Other Material',
					'act_other_mat' => 'Actual Other Material',
					'std_other_dir_cost' => 'Standard Other Direct Costs',
					'act_other_dir_cost' => 'Actual Other Direct Costs',
					'comment' => 'Comments',
					'dp_amt' => 'Downpayment Amount',
					'dtran_sso' => 'Date Recognition Revenue SSO',
					'nref_sso' => 'Invoice number SSO',
					'dref_sso' => 'Invoice Date SSO',
					'dtran_erp' => 'Date Recognition Revenue Factory',
					'nref_erp' => 'Invoice number Factory',
					'dref_erp' => 'Invoice Date Factory',
					'notes' => 'Notes',
					'discc_pc' => 'Customer Discount %',
					'discf_pc' => 'Factory Discount %',
				];
				$rowsCSV = [];
				$i = 0;
				$rows = $_SESSION['data_report'];
				foreach ($rows as $row) {
					// 1 - TRANS level -->
					$i++;
					// Get qty of ER linked to the TRANS
					$query = 'SELECT SUM(er_batch_qty) AS qty FROM service WHERE tranid_sso=' . $row['tid'];
					$qty = tldUtils::getSqlRowToAssocArray($query);
					$row['qty'] = $qty['qty'];
					// Get the full summary for misc calculation of REVENUE
					$sol = new tldSOL($row['sol_id']);
					$option = [
						'dcur' => $DCUR,
						'dt_cur' => $row['dtran_sso'],
						'qty' => $row['qty'],
					];
					$summary = _getFullSummary($sol, $option);
					// Calculate/Prepare info for trans level
					$data = [
						'dcur' => $summary['dcur'],
						'pris_unit_default_cur' => $summary['pris_unit_default_cur'],
						'pris_unit' => $summary['pris_unit'],
						'pris_xtot_default_cur' => $row['tval'],
						'pris_xtot' => $row['tval_dcur'],
					];
					// Total Net selling price in DCUR
					$data['prin_xtot'] = $row['tval_dcur'] - $summary['pris_tot_ext_dcur'];
					// Unit Net selling price in DCUR
					$data['prin_unit'] = $data['prin_xtot'] / $row['qty'];
					// Total Margin in DCUR
					//$data["marg_xtot"]=$data["prin_xtot"]-$summary["pris_tp_in_dcur"];
					$data['marg_xtot'] = ROUND(($row['tval_dcur'] * $summary['prin_unit'] / $summary['pris_unit']) * $summary['marg_unit_pc'] / 100, 2);
					// Merge data
					$row['level'] = "$i - TRANS";
					$rowsCSV[] = $row + $data;


					// 2 - ER level for each TRANS -->
					// Get list of ER linked to the trans
					$query = <<<EOF
                SELECT
                	er.id AS erid,
                	er.airport_code,
                	er.del_ctry,
                	unit.del_dat,
                	unit.ddel_est1,
                	er.dgt_rev,
                	er.dgt_act,
                	er.date_shipped,
                	er.sn,
                	er.er_batch_qty AS qty,
                	trans_erp.dtran AS dtran_erp,
                	trans_erp.nref AS nref_erp,
                	trans_erp.dref AS dref_erp
                FROM service AS er
                	LEFT JOIN sor_units AS unit ON er.sor_uid=unit.id
                	LEFT JOIN sor_lines AS sol ON sol.id=unit.parent_id
                	LEFT JOIN sor_tran AS trans_erp ON er.tranid_erp = trans_erp.id
                		AND trans_erp.ttyp='R' AND trans_erp.tgrp='ERP' AND trans_erp.dtran IS NOT NULL
                WHERE
                	er.tranid_sso={$row['tid']}
EOF;
					$ers = tldUtils::getSqlToAssocArray($query);
					// Foreach ER get data
					$j = 0;
					foreach ($ers as $er) {
						$j++;
						// Get Summary calculation for the ER
						$option = [
							'dcur' => $DCUR,
							'dt_cur' => $row['dtran_sso'],
							'qty' => $er['qty'],
						];
						$er_summary = _getFullSummary($sol, $option);
						// Prepare/Calculate data for the ER level
						$er_summary['dcur'] = '';
						$er_summary['pris_unit_default_cur'] = '';
						$er_summary['pris_unit'] = '';
						$er_summary['pris_xtot_default_cur'] = '';
						$er_summary['pris_xtot'] = '';
						$er_summary['prin_unit'] = '';
						$er_summary['prin_xtot'] = '';
						$er_summary['marg_xtot'] = '';
						$er_summary['notes'] = '';
						// Total External Transactions in DCUR
						$er_summary['pric_tot_ext_dcur'] = $er_summary['pric_trans_tot'] + $er_summary['pric_tax_tot'] +
							$er_summary['pric_parts_tot'] + $er_summary['pric_misc_tot'] +
							$er_summary['pric_agent_com_tot'] + $er_summary['pric_spe_disc_tot'];
						// Merge data
						$er_summary['level'] = "$i.$j - ER";
						$rowsCSV[] = $er_summary + $er + $row;
					}
				}
				$option = ['xItems' => $xItemsCSV, 'showTitles' => true];
				$report = new tldCSV($rowsCSV, $option);
				$report->out();
				exit;
				break;
			case 'xls':
				$rows = $_SESSION['data_report'];
				$report = new tldXLS(
					$rows,
					[
						'xItems' => $_SESSION['data_xitems'],
						'showTitles' => true,
					]
				);
				$report->out();
				exit;
				break;
		}

		// Listing
		$factoryList = tldLocation::getFactoryList('smartyOptionsLocationLocation');
		$ssoList = tldLocation::getSalesOrgList('smartyOptionsLocationLocation');
		$customerList = tldCustomer::getList('smartyOptions');
		$models = tldModel::getList();
		$familyList = ['' => ''] + tldType::getList('smartyOptions_Name');
		// Form
		$form = new HTML_QuickForm('frmReport', 'post');
		$form->addElement('hidden', 'm[0]', 'activity2');
		$form->addElement('hidden', 'm[1]', 'revenue');
		$form->addElement('hidden', 'm[2]', 'groupMargins');
		$form->addElement('header', 'title', 'Group Margins - by Period');
		$form->addElement('date', 'start', 'Start', ['format' => 'Y-m', 'addEmptyOption' => false, 'minYear' => date('Y') - 4, 'maxYear' => date('Y')]);
		$form->addElement('date', 'end', 'End', ['format' => 'Y-m', 'addEmptyOption' => false, 'minYear' => date('Y') - 4, 'maxYear' => date('Y')]);
		$form->addElement('select', 'sso', 'SSO', ['' => ''] + $ssoList);
		$form->addElement('select', 'factory', 'Factory', ['' => ''] + $factoryList);
		$form->addElement('select', 'customer', 'Customer', ['' => ''] + $customerList);
		$form->addElement('checkbox', 'recursiveCustomer', 'Customer with children ?');
		$form->addElement('select', 'model', 'Model', ['' => ''] + $models);
		$form->addElement('select', 'type', 'Product Family', ['' => ''] + $familyList);
		$form->addRule('start', 'Required', 'required');
		$form->addRule('end', 'Required', 'required');
		$form->setDefaults(['start' => '2014-01', 'end' => date('Y-m')]);
		$form->addElement('submit', 'btnSubmit', 'Submit');

		if (!$form->validate()) {
			$body = $form->toHTML();
			break;
		}

		$vars = tldUtils::cleanupFormInput($form->exportValues());
		// Security check for role_FC and role_COO based on Factory
		if ($user->isInGroup(['role_COO', 'role_FC'])) {
			$factory = $vars['factory'];
			$sso = $vars['sso'];
			$BuID = $user->getBUID();
			$location = new tldLocation($BuID);
			$BuName = $location->getBuName();

			$allowedSSO = [];
			foreach (tldLocation::byRegionID($user->getRegionID()) as $bu) {
				if ($bu['role'] === 'SSO') {
					$allowedSSO[] = $bu['location'];
				}
			}

			if (!in_array($BuID, [5, 59]) && ($BuName != $factory && !in_array($sso, $allowedSSO))) {
				$DEFAULT_ERROR[] = 'ERROR: You are not allowed to check the data for this SSO/Factory...';
				break;
			}
		}
		// Year and Month calculation
		$WHERE = '';
		$start = vsprintf('%1$04d%2$02d', $vars['start']);
		$end = vsprintf('%1$04d%2$02d', $vars['end']);
		if (!empty($vars['sso'])) {
            $WHERE .= " AND bu_from.location = '{$vars['sso']}'";
			$title_sso = ' - ' . $vars['sso'];
		}
		if (!empty($vars['factory'])) {
            $WHERE .= " AND bu_to.location = '{$vars['factory']}'";
			$title_factory = ' - ' . $vars['factory'];
		}
		if (!empty($vars['customer'])) {
			$parentCustomer = new tldCustomer($vars['customer']);
			$children = [(int)$vars['customer'] => null];
			if (!empty($vars['recursiveCustomer'])) {
				$parentCustomer->getChildListRec($children);
			}
			$customers = implode(', ', (array_keys($children)));
            $WHERE .= " AND (sors.user_customer_id IN ($customers) OR sors.buyer_customer_id IN ($customers))";
			$title_customer = ' - ' . $parentCustomer->getCustomerName();
			if (!empty($vars['recursiveCustomer'])) {
				$title_customer .= ' and children';
			}
		}
		if (!empty($vars['model'])) {
            $WHERE .= " AND sols.model = '{$vars['model']}'";
			$title_model = ' - ' . $vars['model'];
		}
		if (!empty($vars['type'])) {
            $WHERE .= " AND service.type = '{$vars['type']}'";
			$title_model = ' - ' . $vars['type'];
		}
		$query = <<<EOF
SELECT
    bu_from.location AS location_from,
    sols.id AS sol_id,
    (SELECT CONCAT(lastname,', ',firstname)
    	FROM people WHERE people.id=sors.asm
    ) AS asm_fullname,
    (SELECT type FROM customers
    	WHERE customer_name=sors.cu_nama
    ) AS cu_type,
    "" AS continent,
    bu_to.location AS location_to,
    (SELECT cat.en FROM products_categories AS cat
		LEFT JOIN models ON models.parent_id=cat.id
		WHERE models.model=sols.model LIMIT 1
	) AS er_type,
	(SELECT customers.customer_name FROM customers
	WHERE customers.id=sors.user_customer_id
    ) AS user_customer_display,
    (SELECT customers.customer_name FROM customers
	WHERE customers.id=sors.buyer_customer_id
    ) AS buyer_customer_display,
    sols.model AS er_model,
    sors.id AS sor_id,
    sors.cu_nama AS cu_nama,
    sors.agnt_nama AS agnt_nama,
    sors.cu_orno,
    sors.user_customer_id,
    sors.buyer_customer_id,
    sols.dt_opened,
    sols.ctry,
    sols.intro_new,
    sols.id AS sol_id,
    sols.sls_orno,
    sols.inco,
    sols.inco_loc,
    DATE_FORMAT(trans.dtran, '%Y%m') AS period,
    sols.model AS sols_model,
    trans.id AS tid,
    DATE_FORMAT(trans.dt, '%m/%d/%Y') AS dt,
    trans.dtran AS dtran_sso,
    trans.nref AS nref_sso,
    trans.dref AS dref_sso,
    trans.tcur,
    trans.notes,
    trans.tval,
    from_rate.rate AS rate_from,
    to_rate.rate AS rate_to,
    ROUND((if(to_rate.rate is null, 1, to_rate.rate))/
        (if(from_rate.rate is null, 1, from_rate.rate)),4
    ) AS rate_cross,
    ROUND(trans.tval*(if(to_rate.rate is null, 1, to_rate.rate))/
        (if(from_rate.rate is null, 1, from_rate.rate)),2
    ) AS tval_dcur,
    (SELECT COUNT(*) FROM service
    	WHERE tranid_sso=trans.id
    ) AS nb_er_linked_to_trans,
    ROUND(
        IF(
        	(SELECT COUNT(*) FROM service WHERE tranid_sso=trans.id)>1,
        	trans.tval/(SELECT COUNT(*) FROM service WHERE tranid_sso=trans.id),
        	trans.tval
    	),2
    ) AS unit_rev_amnt,
    mfg_margins.*,
    service.sn as er_sn,
    sors.bu AS sso_erp,
    sols.id AS erp_id,
    service.type as model_type
FROM
	sor AS sors
    JOIN sor_lines AS sols ON sors.id=sols.parent_id
    JOIN sor_tran AS trans ON sols.id=trans.parent_id
    JOIN service ON trans.id=service.tranid_sso
    LEFT JOIN mfg_margins ON service.id=mfg_margins.er_id
    LEFT JOIN locations AS bu_to on sols.bu = bu_to.id
    LEFT JOIN locations AS bu_from ON sors.bu = bu_from.erp
    LEFT JOIN erp_forex2 AS from_rate
        ON from_rate.nam_year*12+from_rate.nam_month=YEAR(trans.dtran)*12+MONTH(trans.dtran)-1
        AND from_rate.nam_cur=trans.tcur AND from_rate.typ='END'
    LEFT JOIN erp_forex2 AS to_rate
        ON to_rate.nam_year*12+to_rate.nam_month=YEAR(trans.dtran)*12+MONTH(trans.dtran)-1
        AND to_rate.nam_cur='$DCUR' AND to_rate.typ='END'
WHERE
	trans.ttyp='R'
    AND trans.tgrp='SSO'
    AND trans.dtran IS NOT NULL
    $WHERE
HAVING
	period BETWEEN '$start' AND '$end' 
ORDER BY
	period,
	sol_id
EOF;
		$rows = tldUtils::getSqlToAssocArray($query);
		foreach ($rows as $key => $row) {
			$sol = new tldSOL($row['sol_id']);
			$data = $sol->getSummary();
			$rows[$key]['margin_percent'] = $data['marg_unit_pc'];
			$rows[$key]['discf_pc'] = $data['discf_pc'];
			$rows[$key]['discc_pc'] = $data['discc_pc'];
			$rows[$key]['dir_margin'] = ROUND(($rows[$key]['factory_rev'] - $rows[$key]['act_lab_cost'] - $rows[$key]['act_mat'] - $rows[$key]['act_other_mat'] - $rows[$key]['act_other_dir_cost']), 2);
            $divisor = (float) $rows[$key]['factory_rev'];
            $rows[$key]['dir_margin_per'] = $divisor ? round(($rows[$key]['dir_margin'] / $divisor) * 100, 2) : 0;
			$rows[$key]['group_margin_per'] = round((($rows[$key]['dir_margin_per'] / 100) + ($rows[$key]['margin_percent'] / 100) * (1 - ($rows[$key]['dir_margin_per'] / 100))) * 100, 2);
		}

		if (isset($_SESSION['data_report'])) {
			unset($_SESSION['data_report']);
		}
		$_SESSION['data_report'] = $rows;

		$xItems = [
			'period' => 'Revenue Month',
			'er_sn' => 'ER SN',
			'sol_id' => 'SOL#',
			'location_from' => 'SSO',
			'location_to' => 'Factory',
			'buyer_customer_display' => 'Customer (BUYER)',
			'user_customer_display' => 'Customer (END USER)',
			'cu_nama' => 'Customer Name',
			'agnt_nama' => 'Agent Name',
			'ctry' => 'Country',
			'sols_model' => 'Model',
			'tcur' => 'Revenue Currency',
			'unit_rev_amnt' => 'Unit Net Selling Price',
			'discc_pc' => 'Cust. Discount (%)',
			'group_margin_per' => 'Actual Group Direct margin (%)',
			'margin_percent' => 'Actual Sales Margin (%)',
			'discf_pc' => 'Fact. Discount (%)',
			'dir_margin_per' => 'Actual Factory Margin (%)',
			'notes' => 'Notes',
			'comment' => 'Comments',
			'dt_opened' => 'SOL opening date',
		];
		if (isset($_SESSION['data_xitems'])) {
			unset($_SESSION['data_xitems']);
		}
		$_SESSION['data_xitems'] = $xItems;

		$DEFAULT_MENU .= <<<EOF
        <br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
        <a href="$php_self?m[0]=activity2&m[1]=revenue&m[2]=groupMargins&m[3]=xls">Download XLS</a>&nbsp;|&nbsp;
        <a href="$php_self?m[0]=activity2&m[1]=revenue&m[2]=groupMargins&m[3]=fullCSV">Download CSV</a>
EOF;

		$report = new tldReportColumnar(
			$rows,
			[
				'xItems' => $xItems,
				'title' => "Group Margins between $start and $end$title_sso$title_factory$title_customer$title_model",
				'links' => [
					'sol_id' => '/en/private/sales_service/sales.php?m[0]=sol&m[1]=view&id=',
					'er_sn' => '/en/private/product_support/index.ps.php?m[0]=equipment&m[1]=search&m[2]=bySN&sn=',
				],
			]
		);
		$body .= $report->fetch();
		break;
	case 'listBySSOERPPeriod':
		$sess_params = &$sess['sales_service']['activity'];
		if (empty($sess_params['year'])) {
			$DEFAULT_ERROR[] = 'ERROR: Year was not set in session...';
			break;
		}
		$year = ($sess_params['year'] === 'ALL') ? '%' : $sess_params['year'];
		if ($showmargin == 1) {
			$DEFAULT_MENU .= <<<EOF
        <br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
        <a href="$php_self?m[0]=activity2&m[1]=$m[1]&m[2]=$m[2]&m[3]=xls&year=$year&y=$y&x=$x&z=$z&showmargin=1">Download XLS</a>&nbsp;|&nbsp;
        <a href="$php_self?m[0]=activity2&m[1]=$m[1]&m[2]=$m[2]&m[3]=fullCSV&y=$y&x=$x&z=$z&showmargin=1">Download FULL ACCT CSV</a>
EOF;
		} else {
			$DEFAULT_MENU .= <<<EOF
        <br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
        <a href="$php_self?m[0]=activity2&m[1]=$m[1]&m[2]=$m[2]&m[3]=xls&year=$year&y=$y&x=$x&z=$z">Download XLS</a>&nbsp;|&nbsp;
        <a href="$php_self?m[0]=activity2&m[1]=$m[1]&m[2]=$m[2]&m[3]=fullCSV&y=$y&x=$x&z=$z">Download FULL ACCT CSV</a>
EOF;
		}
		if (empty($sess_params['location_from'])) {
			$DEFAULT_ERROR[] = 'ERROR: SSO was not set in session...';
			break;
		}
		$location_from = ($sess_params['location_from'] === 'ALL') ? '%' : $sess_params['location_from'];

		if (empty($sess_params['location_to'])) {
			$DEFAULT_ERROR[] = 'ERROR: Factory was not set in session...';
			break;
		}
		$sess_params['location_to'] = $x;

		$location_to = ($sess_params['location_to'] === 'ALL') ? '%' : $sess_params['location_to'];

		$period = ($y === 'ALL') ? '%' : $y;

		$query = <<<EOF
SELECT
    juridical.name AS juridical_entity,
    bu_from.location AS location_from,
    sols.id AS sol_id,
    (SELECT CONCAT(lastname,', ',firstname) FROM people WHERE people.id=sors.asm) AS asm_fullname,
    (SELECT type FROM customers WHERE customer_name=sors.cu_nama) AS cu_type,
    "" AS continent,
    bu_to.location AS location_to,
    (SELECT cat.en FROM products_categories AS cat LEFT JOIN models ON models.parent_id=cat.id WHERE models.model=sols.model LIMIT 1) AS er_type,
	(SELECT customers.customer_name FROM customers WHERE customers.id=sors.user_customer_id) AS user_customer_display,
    (SELECT customers.customer_name FROM customers WHERE customers.id=sors.buyer_customer_id) AS buyer_customer_display,
    sors.t_cuno,
    sols.model AS er_model,
    sors.id AS sor_id,
    sors.cu_nama AS cu_nama,
    sors.cu_orno,
    sors.agnt_nama AS agnt_nama,
    sols.ctry,
    sols.intro_new,
    sols.id AS sol_id,
    sols.sls_orno,
    sols.inco,
    sols.inco_loc,
    DATE_FORMAT(trans.dtran, '%Y%m') AS period,
    sols.model AS sols_model,
    trans.id AS tid,
    DATE_FORMAT(trans.dt, '%m/%d/%Y') AS dt,
    trans.dtran AS dtran_sso,
    trans.nref AS nref_sso,
    trans.dref AS dref_sso,
    trans.tcur,
    trans.notes,
    trans.tval,
    from_rate.rate AS rate_from,
    to_rate.rate AS rate_to,
    ROUND(COALESCE(to_rate.rate, 1)/COALESCE(from_rate.rate, 1), 4) AS rate_cross,
    FORMAT(ROUND(trans.tval*COALESCE(to_rate.rate, 1)/COALESCE (from_rate.rate, 1), 2), 2) AS tval_dcur,
    ROUND(trans.tval*COALESCE (to_rate.rate, 1)/COALESCE (from_rate.rate, 1),2) AS tval_dcur,
    (SELECT MIN(date) FROM mod_logs WHERE module='SOL' AND parent_id=sols.id AND comment LIKE '%CREATE_FACTORY_SO%') AS dt_createFactorySo,
    (SELECT MIN(date) FROM mod_logs WHERE module='SOL' AND parent_id=sols.id AND comment LIKE '%PRINT_FACTORY_SO_ACK%') AS dt_printFactorySoAck
FROM
	sor AS sors
    JOIN sor_lines AS sols ON sors.id=sols.parent_id
    JOIN sor_tran AS trans ON sols.id=trans.parent_id
    LEFT JOIN locations AS bu_to on sols.bu = bu_to.id
    LEFT JOIN locations AS bu_from ON sors.bu = bu_from.erp
    LEFT JOIN erp_forex2 AS from_rate
        ON from_rate.nam_year*12+from_rate.nam_month=YEAR(trans.dtran)*12+MONTH(trans.dtran)-1
        AND from_rate.nam_cur=trans.tcur AND from_rate.typ='END'
    LEFT JOIN erp_forex2 AS to_rate
        ON to_rate.nam_year*12+to_rate.nam_month=YEAR(trans.dtran)*12+MONTH(trans.dtran)-1
        AND to_rate.nam_cur='$DCUR' AND to_rate.typ='END'
    LEFT JOIN tld_juridical_locations AS juridical ON juridical.id=sors.juridical_entity_id
WHERE trans.ttyp='R'
    AND trans.tgrp='SSO'
    AND trans.dtran IS NOT NULL
    AND bu_from.location LIKE '$location_from'
    AND bu_to.location LIKE '$location_to'
    AND DATE_FORMAT(trans.dtran, '%Y') LIKE '$year'
    AND DATE_FORMAT(trans.dtran, '%Y%m') LIKE '$period'
ORDER BY location_from, location_to, period
EOF;

		$rows = tldUtils::getSqlToAssocArray($query);
		if ($showmargin == 1) {
			foreach ($rows as $key => $row) {
				$sol = new tldSOL($row['sol_id']);
				$data = $sol->getSummary(['dcur' => $dcur]);
				$rows[$key]['margin_percent'] = $data['marg_unit_pc'];
			}
			$xItemTrans = [
				'location_from' => 'SSO',
				'location_to' => 'Factory',
				'asm_fullname' => 'ASM',
				'sor_id' => 'SOR ID#',
				'buyer_customer_display' => 'Customer (BUYER)',
				'user_customer_display' => 'Customer (END USER)',
				'cu_nama' => 'Customer Name',
				'agnt_nama' => 'Agent Name',
				'cu_orno' => 'Customer PO#',
				'ctry' => 'Country',
				'sol_id' => 'SOL ID#',
				'sls_orno' => 'SSO PO# to Factory',
				'inco' => 'Inco',
				'period' => 'Revenue Month',
				'sols_model' => 'Model Ordered',
				'tcur' => 'Unit Revenue Currency',
				'tval' => 'Unit Revenue Amount',
				'margin_percent' => 'Margin %',
				'rate_cross' => 'Cross Rate',
				'tval_dcur' => "Unit Revenue Amount ($DCUR)",
				'notes' => 'Notes',
			];
		}
		switch ($m[3]) {
			case 'fullCSV':
				$rowsCSV = [];
				$i = 0;
				foreach ($rows as $row) {
					// 1 - TRANS level -->
					$i++;
					// Get qty of ER linked to the TRANS
					$query = 'SELECT SUM(er_batch_qty) AS qty FROM service WHERE tranid_sso=' . $row['tid'];
					$qty = tldUtils::getSqlRowToAssocArray($query);
					$row['qty'] = $qty['qty'];
					// Get the full summary for misc calculation of REVENUE
					$sol = new tldSOL($row['sol_id']);
					$option = [
						'dcur' => $DCUR,
						'dt_cur' => $row['dtran_sso'],
						'qty' => $row['qty'],
					];
					$summary = _getFullSummary($sol, $option);
					// Calculate/Prepare info for trans level
					$data = [
						'dcur' => $summary['dcur'],
						'pris_unit_default_cur' => $summary['pris_unit_default_cur'],
						'pris_unit' => $summary['pris_unit'],
						'pris_xtot_default_cur' => $row['tval'],
						'pris_xtot' => $row['tval_dcur'],
					];
					// Total Net selling price in DCUR
					$data['prin_xtot'] = $row['tval_dcur'] - $summary['pris_tot_ext_dcur'];
					// Unit Net selling price in DCUR
					$data['prin_unit'] = 0 !== (int) $row['qty'] ? $data['prin_xtot'] / (int) $row['qty'] : 0;
					// Total Margin in DCUR
					//$data["marg_xtot"]=$data["prin_xtot"]-$summary["pris_tp_in_dcur"];
                    if (0 !== (int) $row['tval_dcur'] && 0 !== (int) $summary['prin_unit'] && 0 !== (int) $summary['pris_unit'] && 0 !== (int) $summary['marg_unit_pc']) {
                        $data['marg_xtot'] = ROUND(($row['tval_dcur'] * $summary['prin_unit'] / $summary['pris_unit']) * $summary['marg_unit_pc'] / 100, 2);
                    } else {
                        $data['marg_xtot'] = 0;
                    }
					// Merge data
					$row['level'] = "$i - TRANS";
					$rowsCSV[] = $row + $data;


					// 2 - ER level for each TRANS -->
					// Get list of ER linked to the trans
					$query = <<<EOF
                SELECT
                	er.id AS erid,
                	er.airport_code,
                	er.del_ctry,
                	unit.del_dat,
                	unit.ddel_est1,
                	er.dgt_rev,
                	er.dgt_act,
                	er.date_shipped,
                	er.sn,
                	er.er_batch_qty AS qty,
                	trans_erp.dtran AS dtran_erp,
                	trans_erp.nref AS nref_erp,
                	trans_erp.dref AS dref_erp
                FROM service AS er
                	LEFT JOIN sor_units AS unit ON er.sor_uid=unit.id
                	LEFT JOIN sor_lines AS sol ON sol.id=unit.parent_id
                	LEFT JOIN sor_tran AS trans_erp ON er.tranid_erp = trans_erp.id
                		AND trans_erp.ttyp='R' AND trans_erp.tgrp='ERP' AND trans_erp.dtran IS NOT NULL
                WHERE
                	er.tranid_sso={$row['tid']}
EOF;
					$ers = tldUtils::getSqlToAssocArray($query);
					// Foreach ER get data
					$j = 0;
					foreach ($ers as $er) {
						$j++;
						// Get Summary calculation for the ER
						$option = [
							'dcur' => $DCUR,
							'dt_cur' => $row['dtran_sso'],
							'qty' => $er['qty'],
						];
						$er_summary = _getFullSummary($sol, $option);
						// Prepare/Calculate data for the ER level
						$er_summary['dcur'] = '';
						$er_summary['pris_unit_default_cur'] = '';
						$er_summary['pris_unit'] = '';
						$er_summary['pris_xtot_default_cur'] = '';
						$er_summary['pris_xtot'] = '';
						$er_summary['prin_unit'] = '';
						$er_summary['prin_xtot'] = '';
						$er_summary['marg_xtot'] = '';
						$er_summary['notes'] = '';
						// Total External Transactions in DCUR
						$er_summary['pric_tot_ext_dcur'] = $er_summary['pric_trans_tot'] + $er_summary['pric_tax_tot'] +
							$er_summary['pric_parts_tot'] + $er_summary['pric_misc_tot'] +
							$er_summary['pric_agent_com_tot'] + $er_summary['pric_spe_disc_tot'];
						// Merge data
						$er_summary['level'] = "$i.$j - ER";
						$rowsCSV[] = $er_summary + $er + $row;
					}
				}
				$option = ['xItems' => $xItemsCSV, 'showTitles' => true];
				$report = new tldCSV($rowsCSV, $option);
				$report->out();
				exit;
				break;
			case 'xls':
				$option = ['xItems' => $xItemTrans, 'showTitles' => true];
				$report = new tldXLS($rows, $option);
				$report->out();
				exit;
				break;
			default:
				$report = new tldReportColumnar(
					$rows,
					[
						'xItems' => $xItemTrans,
						'title' => "Revenue for SSO ${sess_params['location_from']}, Factory ${sess_params['location_to']} and Year ${sess_params['year']}, ($DCUR)",
						'links' => [
							'sor_id' => "$php_self?m[0]=sor&m[1]=view&id=",
							'sol_id' => "$php_self?m[0]=sol&m[1]=view&id=",
							'sn' => '/en/private/product_support/index.ps.php?m[0]=equipment&m[1]=search&m[2]=bySN&sn=',
						],
					]
				);
				$body .= $report->fetch();
				break;
		}
		break;
	case 'sumBySSOERP':
		$sess_params = &$sess['sales_service']['activity'];
		if (empty($year)) {
			$DEFAULT_ERROR[] = 'ERROR: Year was not set...';
			break;
		}
		$sess_params['year'] = $year;

		if (empty($y)) {
			$DEFAULT_ERROR[] = 'ERROR: SSO was not set...';
			break;
		}
		$sess_params['location_from'] = $y;

		if (empty($x)) {
			$DEFAULT_ERROR[] = 'ERROR: Factory was not set...';
			break;
		}
		$sess_params['location_to'] = $x;

		$location_to = $x === 'ALL' ? '%' : $x;
		$location_from = $y === 'ALL' ? '%' : $y;

		if ($x === 'ALL' && $y === 'ALL') {
			$GROUP = 'location_to, period';
		} elseif ($x !== 'ALL' && $y === 'ALL') {
			$GROUP = 'location_to, period';
		} elseif ($x === 'ALL' && $y !== 'ALL') {
			$GROUP = 'location_from, location_to, period';
		} else {
			$GROUP = 'location_from, location_to, period';
		}

		$query = <<<EOF
SELECT
    bu_from.location AS location_from,
    bu_to.location AS location_to,
    date_format(dtran, '%Y%m')as period,
    ROUND(SUM(trans.tval*COALESCE (to_rate.rate, 1)/COALESCE (from_rate.rate, 1)),2) as tval_dcur
FROM
sor AS sors
    JOIN sor_lines as sols on sors.id=sols.parent_id
    JOIN sor_tran AS trans on sols.id=trans.parent_id
    LEFT JOIN locations AS bu_to on sols.bu = bu_to.id
    LEFT JOIN locations AS bu_from ON sors.bu = bu_from.erp
    LEFT JOIN erp_forex2 AS from_rate
        ON from_rate.nam_year*12+from_rate.nam_month=YEAR(trans.dtran)*12+MONTH(trans.dtran)-1
        AND from_rate.nam_cur=trans.tcur AND from_rate.typ='END'
    LEFT JOIN erp_forex2 AS to_rate
        ON to_rate.nam_year*12+to_rate.nam_month=YEAR(trans.dtran)*12+MONTH(trans.dtran)-1
        AND to_rate.nam_cur='$DCUR' AND to_rate.typ='END'
WHERE
    trans.tgrp='SSO'
    AND trans.ttyp='R'
    AND YEAR(trans.dtran)=$year
    AND bu_from.location LIKE '$location_from'
    AND bu_to.location LIKE '$location_to'
GROUP BY $GROUP
EOF;
		$rows = tldUtils::getSqlToAssocArray($query);
		$form = new tldMatrix(
			$rows,
			'location_to', 'period', 'tval_dcur',
			"$php_self?m[0]=activity2&m[1]=revenue&m[2]=listBySSOERPPeriod",
			"Revenue for SSO ${sess_params['location_from']}, Factory ${sess_params['location_to']} and Year ${sess_params['year']}, ($DCUR)"
		);
		$body .= $form->fetch();
		break;
	case 'marginBySSOERPYear':
		$BuID = $user->getBUID();
		$location = new tldLocation($BuID);
		$BuName = $location->getBuName();
		//if($user->getBUID() != 5 && ($BuName != $y && $BuName != $x)){
		if (!in_array($BuID, [5, 59]) && (!$user->isInGroupLevel('role_CEO', tldLocation::getERPByLocation($y)) && !$user->isInGroupLevel('role_CEO', tldLocation::getERPByLocation($x)))) {
			$DEFAULT_ERROR[] = 'ERROR: You are not allowed to check the data for this SSO...';
			break;
		}
		$sess_params = &$sess['sales_service']['activity'];
		if (empty($year)) {
			$DEFAULT_ERROR[] = 'ERROR: Year was not set...';
			break;
		}
		$sess_params['year'] = $year;

		if (empty($y)) {
			$DEFAULT_ERROR[] = 'ERROR: SSO was not set...';
			break;
		}
		$sess_params['location_from'] = $y;

		if (empty($x)) {
			$DEFAULT_ERROR[] = 'ERROR: Factory was not set...';
			break;
		}
		$sess_params['location_to'] = $x;

		$location_to = $x === 'ALL' ? '%' : $x;
		$location_from = $y === 'ALL' ? '%' : $y;

		$query = <<<EOF
SELECT
	sols.id,
	trans.tval*(if(to_rate.rate is null, 1, to_rate.rate))/
        (if(from_rate.rate is null, 1, from_rate.rate)) as tval_dcur,
    bu_from.location AS location_from,
    bu_to.location AS location_to,
    date_format(dtran, '%Y%m')as period
FROM sor AS sors
    JOIN sor_lines as sols on sors.id=sols.parent_id
    JOIN sor_tran AS trans on sols.id=trans.parent_id
    LEFT JOIN locations AS bu_to on sols.bu = bu_to.id
    LEFT JOIN locations AS bu_from ON sors.bu = bu_from.erp
    LEFT JOIN erp_forex2 AS from_rate
        ON from_rate.nam_year*12+from_rate.nam_month=YEAR(trans.dtran)*12+MONTH(trans.dtran)-1
        AND from_rate.nam_cur=trans.tcur AND from_rate.typ='END'
    LEFT JOIN erp_forex2 AS to_rate
        ON to_rate.nam_year*12+to_rate.nam_month=YEAR(trans.dtran)*12+MONTH(trans.dtran)-1
        AND to_rate.nam_cur='$DCUR' AND to_rate.typ='END'
WHERE
    trans.tgrp='SSO'
    AND trans.ttyp='R'
    AND YEAR(trans.dtran)=$year
	AND bu_from.location LIKE '$location_from'
    AND bu_to.location LIKE '$location_to'
GROUP BY trans.id
EOF;

		$rows = tldUtils::getSqlToAssocArray($query);
		$margin_calculations = [];
		$margin = [];
		foreach ($rows as &$row) {
			$sol = new tldSOL($row['id']);
			$data = $sol->getSummary(['dcur' => $dcur]);
			if ($data['prin_xtot'] != 0) {
				$row['tot_net_price'] = $data['prin_xtot'];
				$row['tot_gross_price'] = $data['pris_xtot'];
				$row['tot_margin'] = $data['marg_xtot'];
				$period = $row['period'];
				$trans_price = $row['tval_dcur'];
				$location_to = $row['location_to'];
				$location_from = $row['location_from'];
				$margin_calculations[$period][$location_to]['total_marg'] = $margin_calculations[$period][$location_to]['total_marg'] + ($trans_price * ($row['tot_margin'] / $row['tot_gross_price']));
				$margin_calculations[$period][$location_to]['total_net'] = $margin_calculations[$period][$location_to]['total_net'] + ($trans_price * ($row['tot_net_price'] / $row['tot_gross_price']));
				$margin_calculations[$period][$location_to]['period'] = $period;
				$margin_calculations[$period][$location_to]['location_to'] = $location_to;
			}
		}
		foreach ($margin_calculations as $level1 => $level2) {
			foreach ($level2 as $data) {
				$margin[] = ['period' => $data['period'], 'location_to' => $data['location_to'], 'avg_margin_percent' => $data['total_marg'] * 100 / $data['total_net'], 'total_marg' => $data['total_marg'], 'total_net' => $data['total_net']];
			}
		}
		$form = new tldMatrix(
			$margin,
			'location_to', 'period', 'avg_margin_percent',
			"$php_self?m[0]=activity2&m[1]=revenue&m[2]=listBySSOERPPeriod&showmargin=1",
			"Revenue Average Margin (%) for SSO ${sess_params['location_from']}, Factory ${sess_params['location_to']} and Year ${sess_params['year']}, ($DCUR)",
			['decimals' => 'true', 'average_margin_sa2' => 'true']
		);
		$body .= $form->fetch();

		break;
    case 'partialHomepage':
        $year = $year ?: date('Y');
        // Ajax Request
        if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && 'XMLHttpRequest' === $_SERVER['HTTP_X_REQUESTED_WITH']) {
            $DEFAULT_TEMPLATE = 'empty.tpl';
            $DEFAULT_MENU = '';
            $body = '<br/>';
        } else {
            $body .= '<br/>';
        }
        if (!$user->isInGroup(['gg_ADMIN', 'gg_EXCOM'])) {
            return $body;
        }

        $query = getMarginQuery($year);
        $rows = tldUtils::getSqlToAssocArray($query);
        $margin_calculations = [];
        $margin = [];
        foreach ($rows as &$row) {
            $sol = new tldSOL($row['id']);
            $data = $sol->getSummary(['dcur' => $dcur]);
            if ($data['prin_xtot'] != 0) {
                $row['tot_net_price'] = $data['prin_xtot'];
                $row['tot_margin'] = $data['marg_xtot'];
                $location_from = $row['location_from'];
                $location_to = $row['location_to'];
                $margin_calculations[$location_from][$location_to]['total_marg'] = $margin_calculations[$location_from][$location_to]['total_marg'] + $row['tot_margin'];
                $margin_calculations[$location_from][$location_to]['total_net'] = $margin_calculations[$location_from][$location_to]['total_net'] + $row['tot_net_price'];
                $margin_calculations[$location_from][$location_to]['location_from'] = $location_from;
                $margin_calculations[$location_from][$location_to]['location_to'] = $location_to;
            }
        }
        foreach ($margin_calculations as $level1 => $level2) {
            foreach ($level2 as $data) {
                $margin[] = ['location_from' => $data['location_from'], 'location_to' => $data['location_to'], 'avg_margin_percent' => $data['total_marg'] * 100 / $data['total_net'], 'total_marg' => $data['total_marg'], 'total_net' => $data['total_net']];
            }
        }
        $report = new tldMatrix(
            $margin,
            'location_to', 'location_from', 'avg_margin_percent',
            "$php_self?m[0]=activity2&m[1]=revenue&m[2]=marginBySSOERPYear&year=$year",
            "Revenue Average Margin (%) by ALL SSO, ALL Factory for $year",
            ['decimals' => 'true', 'average_margin_sa2' => 'true']
        );
        $body .= $report->fetch();

        break;
	default:
		$year = date('Y');
		$query = getRevenueQuery($DCUR, $year);
		$rows = tldUtils::getSqlToAssocArray($query);
		$form = new tldMatrix(
			$rows,
			'location_to', 'location_from', 'tval_dcur',
			"$php_self?m[0]=activity2&m[1]=revenue&m[2]=sumBySSOERP&year=$year",
			"YTD Revenue by ALL SSO, ALL Factory for $year, in $DCUR"
		);
		$body .= $form->fetch();
        if ($user->isInGroup(['gg_ADMIN', 'gg_EXCOM'])) {
            // Margin Total ------------------------------------------------------------------------------------
            $body .= <<<HTML
<div class="js-revenue-detail-target" >
    <br />
    Loading...       
</div>

<script type="text/javascript">
    document.addEventListener("DOMContentLoaded", function() {
        var headers = new Headers();
        headers.append('X-Requested-With', 'XMLHttpRequest')
        fetch('$php_self?m[0]=activity2&m[1]=revenue&m[2]=partialHomepage&year=$year', {headers: headers}).then(function(response) {
            return response.text()
        }).then(function(body) {
            document.querySelector('.js-revenue-detail-target').innerHTML = body
        })
    });
</script>
HTML;
        }

		// Historic version
		$form = new HTML_QuickForm('frmSA2');
		$form->addElement('hidden', 'm[0]', $m[0]);
		$form->addElement('hidden', 'm[1]', $m[1]);
		$form->addElement('header', 'title', 'Historical Revenues');
		$form->addElement('date', 'year', 'Select year',
			['format' => 'Y', 'minYear' => 2019, 'maxYear' => date('Y') - 1]
		);
		$form->addElement('submit', 'btnSubmit', 'Submit');
		$form->setDefaults(['year' => ['Y' => date('Y') - 1]]);
		$body .= '<br>' . $form->toHTML();

		if ($form->validate()) {
			$vars = tldUtils::cleanupFormInput($form->exportValues());
			$year = $vars['year']['Y'];
			$query = getRevenueQuery($DCUR, $year);
			$rows = tldUtils::getSqlToAssocArray($query);
			$form = new tldMatrix(
				$rows,
				'location_to', 'location_from', 'tval_dcur',
				"$php_self?m[0]=activity2&m[1]=revenue&m[2]=sumBySSOERP&year=$year",
				"YTD Revenue by ALL SSO, ALL Factory for $year, in $DCUR"
			);
			$body .= $form->fetch();
			if ($user->isInGroup(['gg_ADMIN', 'gg_EXCOM'])) {
				$query = getMarginQuery($year);
				$rows = tldUtils::getSqlToAssocArray($query);
				$margin_calculations = [];
				$margin = [];
				foreach ($rows as &$row) {
					$sol = new tldSOL($row['id']);
					$data = $sol->getSummary(['dcur' => $dcur]);
					if ($data['prin_xtot'] != 0) {
						$row['tot_net_price'] = $data['prin_xtot'];
						$row['tot_margin'] = $data['marg_xtot'];
						$location_from = $row['location_from'];
						$location_to = $row['location_to'];
						$margin_calculations[$location_from][$location_to]['total_marg'] = $margin_calculations[$location_from][$location_to]['total_marg'] + $row['tot_margin'];
						$margin_calculations[$location_from][$location_to]['total_net'] = $margin_calculations[$location_from][$location_to]['total_net'] + $row['tot_net_price'];
						$margin_calculations[$location_from][$location_to]['location_from'] = $location_from;
						$margin_calculations[$location_from][$location_to]['location_to'] = $location_to;
					}
				}
				foreach ($margin_calculations as $level1 => $level2) {
					foreach ($level2 as $data) {
						$margin[] = ['location_from' => $data['location_from'], 'location_to' => $data['location_to'], 'avg_margin_percent' => $data['total_marg'] * 100 / $data['total_net'], 'total_marg' => $data['total_marg'], 'total_net' => $data['total_net']];
					}
				}
				$form2 = new tldMatrix(
					$margin,
					'location_to', 'location_from', 'avg_margin_percent',
					"$php_self?m[0]=activity2&m[1]=revenue&m[2]=marginBySSOERPYear&year=$year",
					"Revenue Average Margin (%) by ALL SSO, ALL Factory for $year",
					['decimals' => 'true', 'average_margin_sa2' => 'true']
				);
				$body .= $form2->fetch();
			}
		}
}

function getRevenueQuery($DCUR, $year)
{
	$query = <<<EOF
select
    bu_from.location AS location_from,
    bu_to.location AS location_to,
    ROUND(SUM(trans.tval*(if(to_rate.rate is null, 1, to_rate.rate))/(if(from_rate.rate is null, 1, from_rate.rate))), 2) as tval_dcur
from sor AS sors
    join sor_lines as sols on sors.id=sols.parent_id
    join sor_tran AS trans on sols.id=trans.parent_id
    LEFT JOIN locations AS bu_to on sols.bu = bu_to.id
    LEFT JOIN locations AS bu_from ON sors.bu = bu_from.erp
    LEFT JOIN erp_forex2 AS from_rate
        ON from_rate.nam_year*12+from_rate.nam_month=YEAR(trans.dtran)*12+MONTH(trans.dtran)-1
        AND from_rate.nam_cur=trans.tcur AND from_rate.typ='END'
    LEFT JOIN erp_forex2 AS to_rate
        ON to_rate.nam_year*12+to_rate.nam_month=YEAR(trans.dtran)*12+MONTH(trans.dtran)-1
        AND to_rate.nam_cur='$DCUR' AND to_rate.typ='END'
where trans.tgrp='SSO'
    AND trans.ttyp='R'
    AND YEAR(trans.dtran)=$year
GROUP BY location_from, location_to
EOF;
	return $query;
}

function getMarginQuery($year)
{
	$query = <<<EOF
select
	sols.id,
    bu_from.location AS location_from,
    bu_to.location AS location_to
from sor AS sors
    join sor_lines as sols on sors.id=sols.parent_id
    join sor_tran AS trans on sols.id=trans.parent_id
    LEFT JOIN locations AS bu_to on sols.bu = bu_to.id
    LEFT JOIN locations AS bu_from ON sors.bu = bu_from.erp
where trans.tgrp='SSO'
    AND trans.ttyp='R'
    AND YEAR(trans.dtran)=$year
GROUP BY sols.id
EOF;
	return $query;
}
