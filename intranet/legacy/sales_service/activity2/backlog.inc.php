<?php
$DEFAULT_TITLE .= "\Backlog";

$xItemTrans = [
	'location_from' => 'SSO',
	'juridical_entity' => 'Juridical Entity',
	'location_to' => 'Factory',
	'asm_fullname' => 'ASM',
	'sor_id' => 'SOR ID#',
	'buyer_customer_display' => 'Customer (BUYER)',
	'user_customer_display' => 'Customer (END USER)',
	'cu_orno' => 'Customer PO#',
	'ctry' => 'Country',
	'sol_id' => 'SOL ID#',
	'sol_status' => 'Status',
	'sls_orno' => 'SSO PO# to Factory',
	'inco' => 'Inco',
	'sols_model' => 'Model Ordered',
	'qty' => 'SOL Qty',
	'tval_dcur' => "Backlog Amount ($DCUR)",
	'backlog_qty' => 'Backlog Qty',
	'dzk_sso' => 'Date Zero Backlog',
	'sol_id2' => 'Add SSO Trans',
];

switch ($m[2]) {
	case 'listBySSOERPPeriod':
		$sess_params = &$sess['sales_service']['activity'];
		if (empty($sess_params['year'])) {
			$DEFAULT_ERROR[] = 'ERROR: Year was not set in session...';
			break;
		}
		$DEFAULT_MENU .= <<<EOF
        <br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
        <a href="$php_self?m[0]=activity2&m[1]=$m[1]&m[2]=$m[2]&m[3]=xls&year=$year&y=$y&x=$x&z=$z">Download XLS</a>&nbsp;|&nbsp;
        <a href="$php_self?m[0]=activity2&m[1]=$m[1]&m[2]=$m[2]&m[3]=fullCSV&y=$y&x=$x&z=$z">Download FULL ACCT CSV</a>
EOF;

		if (empty($sess_params['location_from'])) {
			$DEFAULT_ERROR[] = 'ERROR: SSO was not set in session...';
			break;
		}
		$location_from = ($sess_params['location_from'] === 'ALL') ? '%' : $sess_params['location_from'];

		if ($sess_params['location_to'] === 'ALL') {
			$sess_params['location_to'] = $x;
		}
		$location_to = ($x === 'ALL') ? '%' : $x;
		$period = substr($y, 0, 4) * 12 + substr($y, 4, 2);

		$query = <<<EOF
SELECT
	'$y' AS period,
	juridical.name AS juridical_entity,
	bu_from.location AS location_from,
	bu_to.location AS location_to,
    (SELECT CONCAT(lastname,', ',firstname) 
    	FROM people WHERE people.id=sors.asm
    ) AS asm_fullname,
    (SELECT type FROM customers 
    	WHERE customer_name=sors.cu_nama
    ) AS cu_type,
    "" AS continent,
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
    sors.t_cuno,
    sols.dt_opened AS dt_opened,
    sols.model AS er_model,
    sols.model AS sols_model,
    sors.id AS sor_id,
    sors.cu_nama,
    sors.cu_orno,
    sols.ctry,
    sols.intro_new,
    sols.id AS sol_id,
    sols.id AS sol_id2,
	sols.id AS sol_id3,
    sols.sls_orno,
    sols.inco,
    sols.inco_loc,
	sols.status AS sol_status,
	sols.dzk_sso,
	sols.tpay,
	(select SUM(batch_qty) from sor_units
    WHERE parent_id=sols.id
    ) as qty,
	FORMAT(
        ( SELECT
            ROUND(
                SUM(
                    (IF(trans.ttyp='R', -1, 1))
                    *trans.tval
                    *(if(to_rate.rate is null, 1, to_rate.rate))/
                    (if(from_rate.rate is null, 1, from_rate.rate))
                )
            ,2)
        FROM
            sor_tran AS trans
            LEFT JOIN erp_forex2 AS from_rate
                ON from_rate.nam_year*12+from_rate.nam_month=YEAR(trans.dtran)*12+MONTH(trans.dtran)-1
                AND from_rate.nam_cur=trans.tcur AND from_rate.typ='END'
            LEFT JOIN erp_forex2 AS to_rate
                ON to_rate.nam_year*12+to_rate.nam_month=YEAR(trans.dtran)*12+MONTH(trans.dtran)-1
                AND to_rate.nam_cur='$DCUR' AND to_rate.typ='END'
        WHERE
            trans.parent_id=sols.id
            AND trans.tgrp='SSO'
            AND YEAR(trans.dtran)*12+MONTH(trans.dtran) <= $period
    ), 2) AS tval_dcur,
    (
    	(SELECT SUM(units.batch_qty) FROM sor_units as units 
    	WHERE units.parent_id=sols.id)
		-
		(
			IF(
				(SELECT SUM(er.er_batch_qty) FROM service AS er
					LEFT JOIN sor_tran AS trans ON trans.id=er.tranid_sso
				WHERE
					trans.parent_id=sols.id AND trans.ttyp='R' AND trans.tgrp='SSO'
					AND YEAR(trans.dtran)*12+MONTH(trans.dtran)<=$period)
				IS NULL,
				0,
				(SELECT SUM(er.er_batch_qty) FROM service AS er
					LEFT JOIN sor_tran AS trans ON trans.id=er.tranid_sso
				WHERE
					trans.parent_id=sols.id AND trans.ttyp='R' AND trans.tgrp='SSO'
					AND YEAR(trans.dtran)*12+MONTH(trans.dtran)<=$period)
			)
		)
	) AS backlog_qty,
	(SELECT MIN(date) FROM mod_logs WHERE module='SOL' AND parent_id=sols.id AND comment LIKE '%CREATE_FACTORY_SO%') as dt_createFactorySo,
    (SELECT MIN(date) FROM mod_logs WHERE module='SOL' AND parent_id=sols.id AND comment LIKE '%PRINT_FACTORY_SO_ACK%') as dt_printFactorySoAck
FROM
	sor AS sors
	INNER JOIN sor_lines AS sols ON sors.id=sols.parent_id
	LEFT JOIN locations AS bu_from ON bu_from.erp=sors.bu
	LEFT JOIN locations AS bu_to on bu_to.id=sols.bu
    LEFT JOIN tld_juridical_locations AS juridical ON juridical.id=sors.juridical_entity_id
WHERE
    (sols.dzk_sso='0000-00-00' OR YEAR(sols.dzk_sso)*12+MONTH(sols.dzk_sso)> $period)
    AND bu_from.location LIKE '$location_from'
  	AND bu_to.location LIKE '$location_to'
EOF;

		$rows = array_filter(tldUtils::getSqlToAssocArray($query), static function(array $row) {
		    /** filtering null and '0.00' */
		    return (bool) $row['tval_dcur'][0];
		});

		switch ($m[3]) {
			case 'fullCSV':
				$FIELD_TO_CALCULATE = [
					'pris_unit_default_cur', 'pris_unit', 'pris_xtot_default_cur', 'pris_xtot', 'pric_tot_ext_dcur',
					'pric_trans_tot', 'pric_tax_tot', 'pric_parts_tot', 'pric_misc_tot', 'pric_agent_com_tot',
					'pric_spe_disc_tot', 'pris_parts_comp_tot', 'prin_unit', 'prin_xtot', 'pris_tp_default_dcur',
					'pris_tp_default_dcur_tot', 'pris_tp_in_dcur', 'pris_tp_in_dcur_tot', 'marg_xtot',
				];
				$i = 0;
				$rowsCSV = [];
				foreach ($rows as $row) {
					$i++;
					// SOL to get summary
					$sol = new tldSOL($row['sol_id']);
					// List of Revenue TRANS
					$TRANS_R = [];
					// 1 - SOL level -->
					// Get all transactions of the SOL
					$query = <<<EOF
				SELECT
					IF(
                    	(SELECT count(id) FROM sor_tran 
                    	WHERE parent_id={$row['sol_id']} AND id<trans.id AND ttyp=trans.ttyp) > 0,
                    	'N','Y'
                    ) AS initial_trans,
                    IF(
                    	trans.ttyp='B',
                    	(SELECT SUM(batch_qty) FROM sor_units WHERE parent_id={$row['sol_id']}),
                    	(SELECT SUM(er_batch_qty) AS qty FROM service WHERE tranid_sso=trans.id)
                    ) AS qty,
                    trans.*,
                    trans.id AS tid,
                    trans.dtran AS dtran_sso,
                    trans.nref AS nref_sso,
                    DATE_FORMAT(trans.dt, '%m/%d/%Y') AS dt,
                    from_rate.rate AS rate_from,
                    to_rate.rate AS rate_to,
                    ROUND((if(to_rate.rate is null, 1, to_rate.rate))/
                        (if(from_rate.rate is null, 1, from_rate.rate)),4
                    ) AS rate_cross,
                    ROUND(trans.tval*(if(to_rate.rate is null, 1, to_rate.rate))/
                        (if(from_rate.rate is null, 1, from_rate.rate)),2
                    ) AS tval_dcur
                FROM
                	sor_tran AS trans
                    LEFT JOIN erp_forex2 AS from_rate
                        ON from_rate.nam_year*12+from_rate.nam_month=YEAR(trans.dtran)*12+MONTH(trans.dtran)-1
                        AND from_rate.nam_cur=trans.tcur AND from_rate.typ='END'
                    LEFT JOIN erp_forex2 AS to_rate
                        ON to_rate.nam_year*12+to_rate.nam_month=YEAR(trans.dtran)*12+MONTH(trans.dtran)-1
                        AND to_rate.nam_cur='$DCUR' AND to_rate.typ='END'
                WHERE
                	trans.dtran IS NOT NULL
                	AND trans.tgrp='SSO'
                	AND trans.parent_id={$row['sol_id']}
                	AND YEAR(trans.dtran)*12+MONTH(trans.dtran) <= $period
                ORDER BY trans.dtran
EOF;
					$TRANS = tldUtils::getSqlToAssocArray($query);

					// For each TRANS of the SOL
					foreach ($TRANS as $trans) {
						// CALCULATE TRANS SUMMARY
						$summary = [];
						$option = [
							'dcur' => $DCUR,
							'dt_cur' => $trans['dtran'],
							'qty' => $trans['qty'],
						];
						switch ($trans['ttyp']) {
							case 'B':
								// Special case for TRANS B
								if ($trans['initial_trans'] === 'N') {
									$summary = [
										'qty' => '',
										'dcur' => $trans['tcur'],
										'pris_xtot_default_cur' => $trans['tval'],
										'pris_xtot' => $trans['tval_dcur'],
									];
								} else {
									$summary = _getFullSummary($sol, $option);
									$summary['qty'] = $trans['qty'];
									$summary['dcur'] = $trans['tcur'];
									$summary['pris_xtot_default_cur'] = $trans['tval'];
									$summary['pris_xtot'] = $trans['tval_dcur'];
								}
								break;
							case 'R':
								$summary = _getFullSummary($sol, $option);
								$summary['pris_xtot_default_cur'] = $trans['tval'];
								$summary['pris_xtot'] = $trans['tval_dcur'];
								$summary['prin_xtot'] = $trans['tval_dcur'] - $summary['pris_tot_ext_dcur'];
								$summary['marg_xtot'] = $summary['prin_xtot'] - $summary['pris_tp_in_dcur'];
								// KEEP REVENUE TRANS
								$TRANS_R[] = $summary + $trans + $row;
								break;
						}

						// Initialise SOL data
						$sol_summary = [];

						// Calculate SOL SUMMARY FROM SOL TRANSACTIONS B AND R
						foreach ($FIELD_TO_CALCULATE as $field) {
							if (!isset($summary[$field])) {
								continue;
							}
							if ($trans['ttyp'] === 'B') // Add when Booking
							{
								$sol_summary[$field] += $summary[$field];
							}
							if ($trans['ttyp'] === 'R') // Substract when Revenue
							{
								$sol_summary[$field] -= $summary[$field];
							}
						}
					}
					// Finalize SOL SUMMARY
					$sol_summary['dcur'] = $sol_summary['pris_tp_default_curency'] = $solDefaultCurrency = $sol->getDCUR();
					$sol_summary['backlog_val'] = $row['tval_dcur'];

					// Add SOL data
					$sol_summary['level'] = "$i - SOL";
					$rowsCSV[] = $sol_summary + $row;


					// 1.1 - TRANS level -->
					// Initialise ER variable for each trans
					$ER_trans = [];
					$j = 0;
					// GET LIST OF ER FOR R TRANSACTIONS
					foreach ($TRANS_R as $trans_r) {
						$j++;
						// Get list of ER
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
                	esrl.dt_estimated AS esrl_dt_estim,
                	er.er_batch_qty AS qty,
                	trans_erp.dtran AS dtran_erp,
                	trans_erp.nref AS nref_erp
                FROM service AS er
                	LEFT JOIN esrl AS esrl ON er.id = esrl.erid AND esrl.parent_id = er.esrid
                	LEFT JOIN sor_units AS unit ON er.sor_uid=unit.id
                	LEFT JOIN sor_lines AS sol ON sol.id=unit.parent_id
                	LEFT JOIN sor_tran AS trans_erp ON er.tranid_erp = trans_erp.id
                		AND trans_erp.ttyp='R' AND trans_erp.tgrp='ERP' AND trans_erp.dtran IS NOT NULL
                WHERE
                	er.tranid_sso={$trans_r['tid']}
EOF;
						$ers = tldUtils::getSqlToAssocArray($query);

						// Add Transaction R
						$trans_r['level'] = "$i.$j - Trans R";
						$rowsCSV[] = $trans_r;


// 1.1.1 - ER level to Trans -->
						$k = 0;
						foreach ($ers as $er) {
							$k++;
							// Get Summary calculation for the ER
							$option = [
								'dcur' => $DCUR,
								'dt_cur' => $trans_r['dtran_sso'],
								'qty' => $er['qty'],
							];
							$er_summary = _getFullSummary($sol, $option);
							// Prepare ER data
							$er['pris_unit_default_cur'] = $er_summary['pris_unit_default_cur'];
							$er['pris_unit'] = $er_summary['pris_unit'];
							$er['dcur'] = $solDefaultCurrency;
							// Add ER
							$er['level'] = "$i.$j.$k - ER linked to Trans";
							$rowsCSV[] = $er + $trans + $row;
							// Keep ER trans
							$ER_trans[] = $er['erid'];
						}
					}

// 1.2 - ER LEFT not in previous trans level -->
					$ER_trans = implode(',', $ER_trans);
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
                	esrl.dt_estimated AS esrl_dt_estim,
                	er.er_batch_qty AS qty,
                	trans_erp.dtran AS dtran_erp,
                	trans_erp.nref AS nref_erp
                FROM service AS er
                	LEFT JOIN esrl AS esrl ON er.id = esrl.erid AND esrl.parent_id = er.esrid
                	LEFT JOIN sor_units AS unit ON er.sor_uid=unit.id
                	LEFT JOIN sor_lines AS sol ON sol.id=unit.parent_id
                	LEFT JOIN sor_tran AS trans_erp ON er.tranid_erp = trans_erp.id
                		AND trans_erp.ttyp='R' AND trans_erp.tgrp='ERP' AND trans_erp.dtran IS NOT NULL
                WHERE
                	sol.id={$row['sol_id']}
                	AND er.tranid_sso=''
EOF;
					$ers = tldUtils::getSqlToAssocArray($query);
					foreach ($ers as $er) {
						$j++;
						// Get Summary calculation for the ER
						$option = [
							'dcur' => $DCUR,
							'dt_cur' => $row['dt_opened'],
							'qty' => $er['qty'],
						];
						$er_summary = _getFullSummary($sol, $option);
						// Prepare ER data
						$er['pris_unit_default_cur'] = $er_summary['pris_unit_default_cur'];
						$er['pris_unit'] = $er_summary['pris_unit'];
						$er['dcur'] = $solDefaultCurrency;
						// Add the ER to CSV
						$er['level'] = "$i.$j - ER not linked";
						$rowsCSV[] = $er + $row;
					}
				}
				// Add field for Backlog
				$xItemsCSV['backlog_val'] = "Backlog SSO in $DCUR";
				// Out the CSV report
				$option = ['xItems' => $xItemsCSV, 'showTitles' => true];
				$report = new tldCSV($rowsCSV, $option);
				$report->out();
				exit;
				break;
			case 'xls':
				$option = [
					'xItems' => $xItemTrans + ['tpay' => 'Payment terms'],
					'showTitles' => true,
				];
				$report = new tldXLS($rows, $option);
				$report->out();
				exit;
				break;
			default:
				$report = new tldReportColumnar(
					$rows,
					[
						'xItems' => $xItemTrans,
						'title' => "Backlog by SSO $location_from, ERP $x, Period $y",
						'showzero' => true,
						'links' => [
							'sor_id' => "$php_self?m[0]=sor&m[1]=view&id=",
							'sol_id' => "$php_self?m[0]=sol&m[1]=view&id=",
							'sol_id2' => "$php_self?m[0]=sol&m[1]=view&m[2]=tran&m[3]=sso&m[4]=addRev&id=",
						],
					]
				);
				//save the page to come back
				$sess['return_url'] = $_SERVER['REQUEST_URI'];
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

		$location_from = ($y === 'ALL') ? '%' : $y;
		$location_to = ($x === 'ALL') ? '%' : $x;
		$query = <<<EOF
SELECT
    bu_from.location AS location_from,
    bu_to.location AS location_to,
    periods.nam_period AS period,
    ROUND(
        SUM(
            (
             IF(trans.ttyp='R', -1, 1)*trans.tval*
                (if(to_rate.rate is null, 1, to_rate.rate))/
                (if(from_rate.rate is null, 1, from_rate.rate))
             )
        ),
    2) as tval_dcur
FROM
    fin_periods AS periods,
    sor AS sors
    JOIN sor_lines AS sols ON sors.id=sols.parent_id
    JOIN sor_tran AS trans ON sols.id=trans.parent_id
    LEFT JOIN locations AS bu_from ON bu_from.erp=sors.bu
    LEFT JOIN locations AS bu_to ON bu_to.id=sols.bu
    LEFT JOIN erp_forex2 AS from_rate
        ON from_rate.nam_year*12+from_rate.nam_month=YEAR(trans.dtran)*12+MONTH(trans.dtran)-1
            AND from_rate.nam_cur=trans.tcur AND from_rate.typ='END'
    LEFT JOIN erp_forex2 AS to_rate
        ON to_rate.nam_year*12+to_rate.nam_month=YEAR(trans.dtran)*12+MONTH(trans.dtran)-1
            AND to_rate.nam_cur='$DCUR' AND to_rate.typ='END'
WHERE
    trans.tgrp='SSO'
    AND YEAR(trans.dtran)*12+MONTH(trans.dtran) <= LEFT(periods.nam_period, 4)*12+SUBSTR(periods.nam_period, 5, 2)
    AND LEFT(periods.nam_period, 4)=${sess_params['year']}
    AND LEFT(periods.nam_period, 4)*12+SUBSTR(periods.nam_period, 5, 2) <= YEAR(NOW())*12+MONTH(NOW())
    AND (sols.dzk_sso='0000-00-00'
         OR YEAR(sols.dzk_sso)*12+MONTH(sols.dzk_sso) > LEFT(periods.nam_period, 4)*12+SUBSTR(periods.nam_period, 5, 2)
     )
    AND bu_from.location LIKE '$location_from'
   	AND bu_to.location LIKE '$location_to'
GROUP BY
    location_from,
    location_to,
    period
EOF;
//        $rows = tldUtils::getSqlToAssocArray($query);
        $rows = array_filter(tldUtils::getSqlToAssocArray($query), static function(array $row) {
            /** filtering null and '0.00' */
            return (bool) $row['tval_dcur'][0];
        });
		$form = new tldMatrix(
			$rows,
			'location_to', 'period', 'tval_dcur',
			"$php_self?m[0]=activity2&m[1]=backlog&m[2]=listBySSOERPPeriod",
			"Backlog for SSO ${sess_params['location_from']}, Factory ${sess_params['location_to']} and Year ${sess_params['year']}, ($DCUR)",
			[
				'doNotShowXTotals' => true,
			]
		);
		$body .= $form->fetch();
		break;
	default:
		for ($year = date('Y'); $year > (date('Y') - 2); $year--) {
			$query = <<<EOF
SELECT DISTINCT location_from.location
FROM sor AS sors JOIN locations AS location_from
    ON sors.bu=location_from.erp
WHERE YEAR(sors.dt_entered)=$year
EOF;
			if ($rows = tldUtils::getSqlToAssocArray($query, 'smartyOptions', ['location', 'location'])) {
				$report = new tldHTMLList(
					$rows,
					'',
					"?m[0]=activity2&m[1]=backlog&m[2]=sumBySSOERP&x=ALL&year=$year&y=",
					[
						'title' => "$year Backlog",
					]
				);
				$body .= $report->fetch();
			}
		}
		// Historic version
		$form = new HTML_QuickForm('frmSA2');
		$form->addElement('hidden', 'm[0]', $m[0]);
		$form->addElement('hidden', 'm[1]', $m[1]);
		$form->addElement('header', 'title', 'Historical Backlog');
		$form->addElement('date', 'year', 'Select year',
			['format' => 'Y', 'minYear' => date('Y') - 4, 'maxYear' => date('Y') - 1]
		);
		$form->addElement('submit', 'btnSubmit', 'Submit');
		$form->setDefaults(['year' => ['Y' => date('Y') - 1]]);
		$body .= '<br>' . $form->toHTML();

		if ($form->validate()) {
			$vars = tldUtils::cleanupFormInput($form->exportValues());
			$year = $vars['year']['Y'];
			$query = <<<EOF
SELECT DISTINCT location_from.location
FROM sor AS sors JOIN locations AS location_from ON sors.bu=location_from.erp
WHERE YEAR(sors.dt_entered)=$year
EOF;
			if ($rows = tldUtils::getSqlToAssocArray($query, 'smartyOptions', ['location', 'location'])) {
				$report = new tldHTMLList(
					$rows,
					'',
					"?m[0]=activity2&m[1]=backlog&m[2]=sumBySSOERP&x=ALL&year=$year&y=",
					[
						'title' => "$year Backlog",
					]
				);
				$body .= $report->fetch();
			}
		}

}


function getViewTranSSO()
{
	global $id, $DCUR, $DEFAULT_ERROR;
	$rows = tldSORTran::byParentGroup($id, 'SSO', $DCUR);
	if (is_array($rows)) {
		$report = new tldReportColumnar(
			$rows,
			[
				'xItems' => [
					'id' => 'TRAN#',
					'dt' => 'Date Entered',
					'ttyp' => 'Trans Type<br>(B)ooking<br>(R)evenue',
					'dtran' => 'Posting Date',
					'nref' => 'Invoice Number',
					'tcur' => 'Original Currency',
					'tval' => 'Original Value',
					'dcur_rate' => 'Rate',
					'dcur' => 'Currency',
					'tval_dcur' => 'Value',
					'notes' => 'Notes',
				],
				'title' => "SOL# $id, SSO Transactions ($DCUR)",
			]
		);
		return $report->fetch();
	}

	$DEFAULT_ERROR[] = $rows;
}
