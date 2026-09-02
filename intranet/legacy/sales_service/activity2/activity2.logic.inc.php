<?php
include_once('sales_service.inc.php');
include_once('finance.inc.php');

if (!$user->isInGroup(['gg_ADMIN', 'role_PSM', 'role_PSE', 'role_PSA', 'role_COO', 'role_SA', 'gg_ACCT', 'role_EVP'])) {
	$DEFAULT_ERROR[] = 'ERROR: You do not have permission to access this module';
	return;
}

// Enlarge width when report list asked
if (in_array($m[2], ['listBySSOERPPeriod', 'listBySSOERP'])) {
	$smarty->assign('width', 1024);
}
if (empty($sess['sales_service']['activity']['dcur'])) {
	$sess['sales_service']['activity']['dcur'] = 'USD';
}

$DCUR = &$sess['sales_service']['activity']['dcur'];


$DEFAULT_TITLE .= "\Sales Activity ($DCUR)";
$DEFAULT_MENU .= <<<EOF
	<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
    <a href="$php_self?m[0]=activity2">Home</a>
    &nbsp;|&nbsp;<a href="$php_self?m[0]=activity2&m[1]=bookings">Bookings</a>
    &nbsp;|&nbsp;<a href="$php_self?m[0]=activity2&m[1]=revenue">Revenue</a>
    &nbsp;|&nbsp;<a href="$php_self?m[0]=activity2&m[1]=backlog">Backlog</a>
    &nbsp;|&nbsp;<a href="$php_self?m[0]=activity2&m[1]=ai">Accrued Income</a>
    &nbsp;|&nbsp;<a href="$php_self?m[0]=activity2&m[1]=sol_pending">Pending</a>
    &nbsp;|&nbsp;<a href="$php_self?m[0]=activity2&m[1]=changeDcur">Change Display Currency</a>
EOF;

if ($user->isInGroup(['gg_ADMIN', 'gg_ACCT'])) {
	$DEFAULT_MENU .= <<<EOF
&nbsp;|&nbsp;<a href="/en/private/finance/sor_tran/sor_tran_admin.php" title="SOR Transaction Admin">Trans Admin</a>
EOF;
}

$xItemsCSV = [
	'level' => 'Report level',
	'period' => ucfirst($m[1]) . ' month',
	'location_from' => 'SSO',
	'sol_id' => 'SOL ID#',
	'sor_id' => 'SOR#',
	'asm_fullname' => 'ASM',
	'cu_type' => 'Customer Type',
	'buyer_customer_display' => 'Customer (BUYER)',
	't_cuno' => 'Buyer ERP ID',
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
	'margin_percent' => 'Margin %',
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
	'marg_unit' => 'Unit Margin',
	'dt_createFactorySo' => 'Date CREATE_FACTORY_SO',
	'dt_printFactorySoAck' => 'Date PRINT_FACTORY_SO_ACK',
];

switch ($m[1]) {
	case 'backlog':
		include_once('backlog.inc.php');
		break;
	case 'revenue':
		include_once('revenue.inc.php');
		break;
	case 'bookings':
		include_once('bookings.inc.php');
		break;
	case 'ai':
		include_once('ai.inc.php');
		break;
	case 'sol_pending':
		include_once('sol.pending.inc.php');
		break;
	case 'changeDcur':
		$report = new tldHTMLList(
			tldForex::getCurrencyList(),
			null,
			"$php_self?m[0]=activity2&dcur="
		);
		$body .= $report->fetch();
		break;
	default:
		if ($dcur) {
			$sess['sales_service']['activity']['dcur'] = $dcur;
		}
		$DEFAULT_ERROR[] = "Default currency is {$sess['sales_service']['activity']['dcur']}";
		$body .= $smarty->fetch("$PATH/activity2/homepage.activity.tpl");
		break;
}

/**
 * @param tldSOL $sol
 * @param array $options
 */
function _getFullSummary($sol, $options)
{
    static $cache = [];

	$id = $sol->itsID;
	$header = $sol->itsHeader;

	if (!empty($options['qty'])) {
		$qty = $options['qty'];
	} else {
		$qty = $options['qty'] = $sol->getQTY();
	}
    $solDefaultCurrency = $sol->getDCUR();
	if ($options['dcur'] && in_array($options['dcur'], ['USD', 'EUR'], true)) {
		$dcur = $options['dcur'];
	} else {
		$dcur = $options['dcur'] = $solDefaultCurrency;
	}

	if (isset($options['dt_cur'])) {
		$dt_cur = $options['dt_cur'];
	} else {
		$dt_cur = $options['dt_cur'] = $sol->itsHeader['dt_opened'];
	}


    $cacheKey = sprintf('summary-%s-%s', $id, md5(serialize(($options))));
    if (array_key_exists($cacheKey, $cache)) {
        return $cache[$cacheKey];
    }

    // Default currency
    $a = ['dcur' => $solDefaultCurrency];

    $blueprint = [
        'mrsp_tot_in_dcur' => null,
        'prip_tot_in_dcur' => null,
        'pric_tot_in_dcur' => null,
        'pris_tot_in_dcur' => null,
        'pris_tot' => null,
        'pric_tot' => null,
        'prip_tot' => null,
        'mrsp_tot' => null,
    ];

    $keys = array_keys($blueprint);
	$totals = array_fill_keys(['internal', 'internalDefaultCurrency', 'external', 'externalDefaultCurrency', 'baseUnit', 'otherTotals', 'agentCommission', 'parts', 'taxes', 'transportation', 'misc', 'specialDiscount'], $blueprint);

    $intCaty = tldSOL::getInternalCategoriesList();
    $extCaty = tldSOL::getExternalCategoriesList();
	foreach (_totalsOptionsByParent($id, ['dcur' => $dcur, 'dt_cur' => $dt_cur]) as $total) {
        if (in_array($total['caty'], $intCaty, true)) {
            foreach ($keys as $key) {
                $totals['internal'][$key] = (string) ($totals['internal'][$key] + $total[$key]);
            }
            if ('BASE UNIT' === $total['caty']) {
                $totals['baseUnit'] = $total;
                unset($totals['baseUnit']['caty']);
            }
        } elseif (in_array($total['caty'], $extCaty, true)) {
            foreach ($keys as $key) {
                $totals['external'][$key] = (string) ($totals['external'][$key] + $total[$key]);
            }
            if (in_array($total['caty'], ['SPARE PARTS', 'COMPONENTS'], true)) {
                foreach ($keys as $key) {
                    $totals['otherTotals'][$key] = (string) ($totals['otherTotals'][$key] + $total[$key]);
                }
                if ('SPARE PARTS' === $total['caty']) {
                    $totals['parts'] = $total;
                    unset($totals['parts']['caty']);
                }
            } elseif ('AGENT COMMISSION' === $total['caty']) {
                $totals['agentCommission'] = $total;
                unset($totals['agentCommission']['caty']);
            }  elseif ('TAXES AND DUTIES' === $total['caty']) {
                $totals['taxes'] = $total;
                unset($totals['taxes']['caty']);
            } elseif ('TRANSPORTATION' === $total['caty']) {
                $totals['transportation'] = $total;
                unset($totals['transportation']['caty']);
            } elseif ('MISC. ITEMS' === $total['caty']) {
                $totals['misc'] = $total;
                unset($totals['misc']['caty']);
            } elseif ('SPECIAL DISCOUNT' === $total['caty']) {
                $totals['specialDiscount'] = $total;
                unset($totals['specialDiscount']['caty']);
            }
        }
    }

	if ($dcur === $solDefaultCurrency) {
	    $totals['internalDefaultCurrency'] = $totals['internal'];
	    $totals['externalDefaultCurrency'] = $totals['external'];
    } else {
	    foreach (_totalsOptionsByParent($id, ['dcur' => $solDefaultCurrency, 'dt_cur' => $dt_cur]) as $total) {
            if (in_array($total['caty'], $intCaty, true)) {
                foreach ($keys as $key) {
                    $totals['internalDefaultCurrency'][$key] = (string) ($totals['internalDefaultCurrency'][$key] + $total[$key]);
                }
            } elseif (in_array($total['caty'], $extCaty, true)) {
                foreach ($keys as $key) {
                    $totals['externalDefaultCurrency'][$key] = (string) ($totals['externalDefaultCurrency'][$key] + $total[$key]);
                }
            }
        }
    }

	// Get BASE UNIT
    $a['pris_base_unit_dcur'] = $totals['baseUnit']['pris_tot_in_dcur'];
    $a['pris_base_unit_tot_dcur'] = $a['pris_base_unit_dcur'] * $qty;
    $a['pris_base_unit_cur'] = $totals['baseUnit']['pris_tot'];
    $a['pris_base_unit_tot_cur'] = $a['pris_base_unit_cur'] * $qty;

	// Get SPARE PARTS and COMPONENTS -> Other external transactions
	// sales
	$a['pris_parts_comp'] = $totals['otherTotals']['pris_tot_in_dcur'];
	$a['pris_parts_comp_tot'] = $a['pris_parts_comp'] * $qty;
	// cost
	$a['pric_parts_comp'] = $totals['otherTotals']['pric_tot_in_dcur'];
	$a['pric_parts_comp_tot'] = $a['pric_parts_comp'] * $qty;

	// Get COMMISSION
	// sales
	$a['agent_com'] = $totals['agentCommission']['pris_tot_in_dcur'];
	$a['agent_com_tot'] = $a['agent_com'] * $qty;
	// cost
	$a['pric_agent_com'] = $totals['agentCommission']['pric_tot_in_dcur'];
	$a['pric_agent_com_tot'] = $a['pric_agent_com'] * $qty;

	// Get SPARE PARTS
	// sales
	$a['parts'] = $totals['parts']['pris_tot_in_dcur'];
	$a['parts_tot'] = $a['parts'] * $qty;
	// cost
	$a['pric_parts'] = $totals['parts']['pric_tot_in_dcur'];
	$a['pric_parts_tot'] = $a['pric_parts'] * $qty;

	// Get TAXES AND DUTIES
	// sales
	$a['tax'] = $totals['taxes']['pris_tot_in_dcur'];
	$a['tax_tot'] = $a['tax'] * $qty;
	// cost
	$a['pric_tax'] = $totals['taxes']['pric_tot_in_dcur'];
	$a['pric_tax_tot'] = $a['pric_tax'] * $qty;

	// Get TRANSPORTATION
	// sales
	$a['trans'] = $totals['transportation']['pris_tot_in_dcur'];
	$a['trans_tot'] = $a['trans'] * $qty;
	// cost
	$a['pric_trans'] = $totals['transportation']['pric_tot_in_dcur'];
	$a['pric_trans_tot'] = $a['pric_trans'] * $qty;

	// Get MISC. ITEMS
	// sales
	$a['misc'] = $totals['misc']['pris_tot_in_dcur'];
	$a['misc_tot'] = $a['misc'] * $qty;
	// cost
	$a['pric_misc'] = $totals['misc']['pric_tot_in_dcur'];
	$a['pric_misc_tot'] = $a['pric_misc'] * $qty;

	// Get SPECIAL DISCOUNT
	// sales
	$a['spe_disc'] = $totals['specialDiscount']['pris_tot_in_dcur'];
	$a['spe_disc_tot'] = $a['spe_disc'] * $qty;
	// cost
	$a['pric_spe_disc'] = $totals['specialDiscount']['pric_tot_in_dcur'];
	$a['pric_spe_disc_tot'] = $a['pric_spe_disc'] * $qty;

	// Total Internal transaction
	$a['pris_tot_int_cur'] = $totals['internal']['pris_tot'] * $qty;
	$a['pris_tot_int_dcur'] = $totals['internal']['pris_tot_in_dcur'] * $qty;
	// Total External transaction
	$a['pris_tot_ext_dcur'] = $totals['external']['pris_tot_in_dcur'] * $qty;
	$a['pric_tot_ext_dcur'] = $totals['external']['pric_tot_in_dcur'] * $qty;

	// Unit Gross Selling Price = Internal + External transaction
	$a['pris_unit'] = $totals['internal']['pris_tot_in_dcur'] + $totals['external']['pris_tot_in_dcur'];
	// Unit Gross Selling Price in default currency
	$a['pris_unit_default_cur'] = $totals['internalDefaultCurrency']['pris_tot_in_dcur'] + $totals['externalDefaultCurrency']['pris_tot_in_dcur'];
	// Total Unit Gross Selling Price = Unit Gross Selling Price * Quantity
	$a['pris_xtot'] = $a['pris_unit'] * $qty;
	// Total Unit Gross Selling Price in default currency
	$a['pris_xtot_default_cur'] = $a['pris_unit_default_cur'] * $qty;

	// Calculate down payment if saved as a percentage, otherwise use the value
	if ((int)$header['dp_amt'] !== 0) {
		$a['dp'] = $header['dp_amt'];
	} else {
		$a['dp'] = $header['dp_pc'] * $a['pris_xtot'] / 100;
	}

	// Exworks sales price = Actual Net selling price (Internal transaction)
	$a['pris_exw_unit'] = $totals['internal']['pris_tot_in_dcur'];
	// Exworks total price = Exworks sales price * Quantity
	$a['pris_exw_xtot'] = $a['pris_exw_unit'] * $qty;

	// Unit Net Selling Price = Unit Gross Selling Price - External transactions cost
	$a['prin_unit'] = $a['pris_unit'] - $totals['external']['pric_tot_in_dcur'] + $totals['otherTotals']['pric_tot_in_dcur'];
	// Total Net Selling Price = Unit Net Selling Price * Quantity
	$a['prin_xtot'] = $a['prin_unit'] * $qty;

	// Customer discount = [Total of Published Price List + (Components and Spare Parts Costs) / 0,9 + all other External Item Costs] - Unit Gross Selling Price
	$customerDiscount = round($totals['internal']['prip_tot_in_dcur'] + ($totals['otherTotals']['pric_tot_in_dcur'] / 0.9) + ($totals['external']['pric_tot_in_dcur'] - $totals['otherTotals']['pric_tot_in_dcur']) - $a['pris_unit'], 2);
	if ($customerDiscount > 0 && $totals['internal']['prip_tot_in_dcur'] != 0) {
		$a['discc'] = $customerDiscount;
		$a['discc_tot'] = $a['discc'] * $qty;
		$a['discc_pc'] = round($a['discc'] * 100 / ($totals['internal']['prip_tot_in_dcur'] + ($totals['otherTotals']['pric_tot_in_dcur'] / 0.9)), 2);
	} else {
		$a['discc'] = 'No discount';
		$a['discc_tot'] = 'No discount';
		$a['discc_pc'] = '';
	}

	// Negociated TP
	$a['pris_tp_in_dcur'] = $totals['internal']['pric_tot_in_dcur'];
	$a['pris_tp_in_dcur_tot'] = $a['pris_tp_in_dcur'] * $qty;
	// Negociated TP in default cur
	$a['pris_tp_default_dcur'] = $totals['internalDefaultCurrency']['pric_tot_in_dcur'];
	$a['pris_tp_default_dcur_tot'] = $a['pris_tp_default_dcur'] * $qty;
	// Negociated TP Currency
	$a['pris_tp_default_curency'] = $solDefaultCurrency;

	// Factory discount = Published TP - Negotiated TP
	if ($totals['internal']['mrsp_tot_in_dcur'] > $totals['internal']['pric_tot_in_dcur']) {
		$a['discf'] = $totals['internal']['mrsp_tot_in_dcur'] - $totals['internal']['pric_tot_in_dcur'];
		$a['discf_tot'] = $a['discf'] * $qty;
		$a['discf_pc'] = round($a['discf'] * 100 / $totals['internal']['mrsp_tot_in_dcur'], 2);
	} else {
		$a['discf'] = 'No discount';
		$a['discf_tot'] = 'No discount';
		$a['discf_pc'] = '';
	}

	// Unit cost = Negotiated TP + External transaction cost
	$a['cost_unit'] = $totals['internal']['pric_tot_in_dcur'] + $totals['external']['pric_tot_in_dcur'];
	// Total cost = Unit cost * Quantity
	$a['cost_xtot'] = $a['cost_unit'] * $qty;

	// Unit margin = Gross Selling Price - Unit Cost
	$a['marg_unit'] = $a['pris_unit'] - $a['cost_unit'];
	// Unit margin percentage = Unit margin*100 / Unit Net Selling Price
	if ($a['prin_unit'] <> 0) {
		$a['marg_unit_pc'] = round($a['marg_unit'] * 100 / $a['prin_unit'], 2);
	} else {
		$a['marg_unit_pc'] = 0;
	}
	// Total margin = Unit margin * Quantity
	$a['marg_xtot'] = $a['marg_unit'] * $qty;

    $cache[$cacheKey] = $a;

    return $cache[$cacheKey];
}

function _totalsOptionsByParent($pid, $options = '')
{
    static $cache = [];

	if (empty($options['dt_cur'])) {
		return [];
	}

    $cacheKey = sprintf('options-%s-%s', $pid, md5(serialize($options)));
    if (array_key_exists($cacheKey, $cache)) {
        return $cache[$cacheKey];
    }

	$dt_cur = $options['dt_cur'];
	$dcur = !empty($options['dcur']) ? $options['dcur'] : (new tldSOL($pid, true))->getDCUR();

	$WHERE = '';
	if (is_array($options['exclude'])) {
		$WHERE .= " AND caty NOT IN ('" . implode("','", $options['exclude']) . "')";
	}
	if (is_array($options['include'])) {
		$WHERE .= " AND caty IN ('" . implode("','", $options['include']) . "')";
	}

	$query = <<<EOF
	SELECT caty,
		SUM(ROUND(
			t1.mrsp*(
    			IF(
        			(SELECT to_rate.rate FROM erp_forex2 AS to_rate
           			WHERE to_rate.nam_year*12+to_rate.nam_month=YEAR('$dt_cur')*12+MONTH('$dt_cur')-1
    				AND to_rate.nam_cur='$dcur' AND to_rate.typ='END') IS NULL,
    				1,
    				(SELECT to_rate.rate FROM erp_forex2 AS to_rate
           			WHERE to_rate.nam_year*12+to_rate.nam_month=YEAR('$dt_cur')*12+MONTH('$dt_cur')-1
    				AND to_rate.nam_cur='$dcur' AND to_rate.typ='END')
    			)
    			/
				IF(
    				(SELECT IF(from_rate.rate IS NULL, 1, from_rate.rate) FROM erp_forex2 AS from_rate
           			WHERE from_rate.nam_year*12+from_rate.nam_month=YEAR('$dt_cur')*12+MONTH('$dt_cur')-1
    				AND from_rate.nam_cur=t1.mrsp_cur AND from_rate.typ='END') IS NULL,
    				1,
    				(SELECT IF(from_rate.rate IS NULL, 1, from_rate.rate) FROM erp_forex2 AS from_rate
           			WHERE from_rate.nam_year*12+from_rate.nam_month=YEAR('$dt_cur')*12+MONTH('$dt_cur')-1
    				AND from_rate.nam_cur=t1.mrsp_cur AND from_rate.typ='END')
    			)
			),2)
		) AS mrsp_tot_in_dcur,
		SUM(ROUND(
			t1.prip*(
    			IF(
        			(SELECT to_rate.rate FROM erp_forex2 AS to_rate
           			WHERE to_rate.nam_year*12+to_rate.nam_month=YEAR('$dt_cur')*12+MONTH('$dt_cur')-1
    				AND to_rate.nam_cur='$dcur' AND to_rate.typ='END') IS NULL,
    				1,
    				(SELECT to_rate.rate FROM erp_forex2 AS to_rate
           			WHERE to_rate.nam_year*12+to_rate.nam_month=YEAR('$dt_cur')*12+MONTH('$dt_cur')-1
    				AND to_rate.nam_cur='$dcur' AND to_rate.typ='END')
    			)
    			/
				IF(
    				(SELECT IF(from_rate.rate IS NULL, 1, from_rate.rate) FROM erp_forex2 AS from_rate
           			WHERE from_rate.nam_year*12+from_rate.nam_month=YEAR('$dt_cur')*12+MONTH('$dt_cur')-1
    				AND from_rate.nam_cur=t1.prip_cur AND from_rate.typ='END') IS NULL,
    				1,
    				(SELECT IF(from_rate.rate IS NULL, 1, from_rate.rate) FROM erp_forex2 AS from_rate
           			WHERE from_rate.nam_year*12+from_rate.nam_month=YEAR('$dt_cur')*12+MONTH('$dt_cur')-1
    				AND from_rate.nam_cur=t1.prip_cur AND from_rate.typ='END')
    			)
			),2)
		) AS prip_tot_in_dcur,
		SUM(ROUND(
			t1.pric*(
    			IF(
        			(SELECT to_rate.rate FROM erp_forex2 AS to_rate
           			WHERE to_rate.nam_year*12+to_rate.nam_month=YEAR('$dt_cur')*12+MONTH('$dt_cur')-1
    				AND to_rate.nam_cur='$dcur' AND to_rate.typ='END') IS NULL,
    				1,
    				(SELECT to_rate.rate FROM erp_forex2 AS to_rate
           			WHERE to_rate.nam_year*12+to_rate.nam_month=YEAR('$dt_cur')*12+MONTH('$dt_cur')-1
    				AND to_rate.nam_cur='$dcur' AND to_rate.typ='END')
    			)
    			/
				IF(
    				(SELECT IF(from_rate.rate IS NULL, 1, from_rate.rate) FROM erp_forex2 AS from_rate
           			WHERE from_rate.nam_year*12+from_rate.nam_month=YEAR('$dt_cur')*12+MONTH('$dt_cur')-1
    				AND from_rate.nam_cur=t1.pric_cur AND from_rate.typ='END') IS NULL,
    				1,
    				(SELECT IF(from_rate.rate IS NULL, 1, from_rate.rate) FROM erp_forex2 AS from_rate
           			WHERE from_rate.nam_year*12+from_rate.nam_month=YEAR('$dt_cur')*12+MONTH('$dt_cur')-1
    				AND from_rate.nam_cur=t1.pric_cur AND from_rate.typ='END')
    			)
			),2)
		) AS pric_tot_in_dcur,
		SUM(ROUND(
			t1.pris*(
    			IF(
        			(SELECT to_rate.rate FROM erp_forex2 AS to_rate
           			WHERE to_rate.nam_year*12+to_rate.nam_month=YEAR('$dt_cur')*12+MONTH('$dt_cur')-1
    				AND to_rate.nam_cur='$dcur' AND to_rate.typ='END') IS NULL,
    				1,
    				(SELECT to_rate.rate FROM erp_forex2 AS to_rate
           			WHERE to_rate.nam_year*12+to_rate.nam_month=YEAR('$dt_cur')*12+MONTH('$dt_cur')-1
    				AND to_rate.nam_cur='$dcur' AND to_rate.typ='END')
    			)
    			/
				IF(
    				(SELECT IF(from_rate.rate IS NULL, 1, from_rate.rate) FROM erp_forex2 AS from_rate
           			WHERE from_rate.nam_year*12+from_rate.nam_month=YEAR('$dt_cur')*12+MONTH('$dt_cur')-1
    				AND from_rate.nam_cur=t1.pris_cur AND from_rate.typ='END') IS NULL,
    				1,
    				(SELECT IF(from_rate.rate IS NULL, 1, from_rate.rate) FROM erp_forex2 AS from_rate
           			WHERE from_rate.nam_year*12+from_rate.nam_month=YEAR('$dt_cur')*12+MONTH('$dt_cur')-1
    				AND from_rate.nam_cur=t1.pris_cur AND from_rate.typ='END')
    			)
			),2)
		) AS pris_tot_in_dcur,
		SUM(pris) AS pris_tot,
		SUM(pric) AS pric_tot,
		SUM(prip) AS prip_tot,
		SUM(mrsp) AS mrsp_tot
	FROM sor_opts AS t1
	WHERE t1.parent_id=$pid
	$WHERE
	GROUP BY caty
EOF;

    $cache[$cacheKey] = tldUtils::getSqlToAssocArray($query);

    return $cache[$cacheKey];
}
