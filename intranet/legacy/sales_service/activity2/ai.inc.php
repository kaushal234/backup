<?php
$DEFAULT_TITLE .= "\Accrued Income";

$xItemTrans = [
    'location_from' => 'SSO',
    'location_to' => 'Factory',
    'asm_fullname' => 'ASM',
    'sor_id' => 'SOR ID#',
    'buyer_customer_display' => 'Customer (BUYER)',
    'user_customer_display' => 'Customer (END USER)',
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
    case 'listBySSOERPPeriod':
        $sess_params = &$sess['sales_service']['activity'];
        if (empty($sess_params['year'])) {
            $DEFAULT_ERROR[] = 'ERROR: Year was not set in session...';
            break;
        }
        $year = ($sess_params['year'] === 'ALL') ? '%' : $sess_params['year'];
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

        if (empty($sess_params['location_to'])) {
            $DEFAULT_ERROR[] = 'ERROR: Factory was not set in session...';
            break;
        }

        $sess_params['location_to'] = $x;

        $location_to = ($sess_params['location_to'] === 'ALL') ? '%' : $sess_params['location_to'];

        $period = ($y === 'ALL') ? '%' : $y;

        $query = <<<EOF
SELECT
    bu_from.location AS location_from,
    sols.id AS sol_id,
    (SELECT CONCAT(lastname,', ',firstname) 
    	FROM people WHERE people.id=sors.asm
    ) AS asm_fullname,
    (SELECT customers.customer_name FROM customers 
        WHERE customers.id=sors.user_customer_id
    ) AS user_customer_display,
    (SELECT customers.customer_name FROM customers 
        WHERE customers.id=sors.buyer_customer_id
    ) AS buyer_customer_display,
    sors.cu_nama,
    "" AS continent,
    bu_to.location AS location_to,
    (SELECT cat.en FROM products_categories AS cat
		LEFT JOIN models ON models.parent_id=cat.id
		WHERE models.model=sols.model LIMIT 1
	) AS er_type,
    sols.model AS er_model,
    sors.id AS sor_id,
    sors.cu_nama,
    sors.cu_orno,
    sols.ctry,
    sols.id AS sol_id,
    sols.sls_orno,
    sols.inco,
    sols.inco_loc,
    period.nam_period AS period,
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
    (SELECT MIN(date) FROM mod_logs WHERE module='SOL' AND parent_id=sols.id AND comment LIKE '%CREATE_FACTORY_SO%') as dt_createFactorySo,
    (SELECT MIN(date) FROM mod_logs WHERE module='SOL' AND parent_id=sols.id AND comment LIKE '%PRINT_FACTORY_SO_ACK%') as dt_printFactorySoAck
FROM
	fin_periods AS period,
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
WHERE 
	trans.tgrp='SSO'
    AND trans.ttyp='R'
    AND trans.dref IS NOT NULL
    AND PERIOD_DIFF(
    	DATE_FORMAT(trans.dref, '%Y%m'),
    	period.nam_period
    ) <= 0
    AND DATEDIFF(
    	trans.dtran,
    	LAST_DAY(CONCAT(LEFT(period.nam_period,4),'-',RIGHT(period.nam_period,2),'-01'))
    ) > 0
    AND period.nam_period LIKE '$period'
    AND bu_from.location LIKE '$location_from'
    AND bu_to.location LIKE '$location_to'
ORDER BY
	location_from, location_to, period
EOF;
        $rows = tldUtils::getSqlToAssocArray($query);

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
                    // Total net selling price in DCUR
                    $data['prin_xtot'] = $row['tval_dcur'] - $summary['pris_tot_ext_dcur'];
                    // Total Margin in DCUR
                    $data['marg_xtot'] = $data['prin_xtot'] - $summary['pris_tp_in_dcur'];
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
                        'title' => "Revenue for SSO ${sess_params['location_from']}, Factory ${sess_params['location_to']} and Period $period, ($DCUR)",
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

        if ($x === 'ALL') {
            $location_to = '%';
        } else {
            $location_to = $x;
        }
        if ($y === 'ALL') {
            $location_from = '%';
        } else {
            $location_from = $y;
        }
        if ($x === 'ALL' && $y === 'ALL') {
            $GROUP = 'location_to, period';
        } elseif ($x !== 'ALL' && $y === 'ALL') {
            $GROUP = 'location_to, period';
        } elseif ($x === 'ALL' && $y !== 'ALL') {
            $GROUP = 'location_from, location_to, period';
        } else {
            $GROUP = 'location_from, location_to, period';
        }
        // Set window limit in years
        $limit_Y = 2;
        $year_min = $year - $limit_Y;

        $query = <<<EOF
SELECT SQL_NO_CACHE
    bu_from.location AS location_from,
    bu_to.location AS location_to,
    period.nam_period AS period,
    ROUND(SUM(trans.tval*(if(to_rate.rate is null, 1, to_rate.rate))/
        (if(from_rate.rate is null, 1, from_rate.rate))),2
    ) as tval_dcur
FROM
	fin_periods AS period,
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
    AND trans.dref IS NOT NULL
    AND PERIOD_DIFF(
    	DATE_FORMAT(trans.dref, '%Y%m'),
    	period.nam_period
    ) <= 0
    AND DATEDIFF(
    	trans.dtran,
    	LAST_DAY(CONCAT(LEFT(period.nam_period,4),'-',RIGHT(period.nam_period,2),'-01'))
    ) > 0
    AND LEFT(period.nam_period, 4)<='$year'
    AND LEFT(period.nam_period, 4)>='$year_min'
    AND bu_from.location LIKE '$location_from'
    AND bu_to.location LIKE '$location_to'
GROUP BY
    $GROUP
EOF;
        $rows = tldUtils::getSqlToAssocArray($query);
        $form = new tldMatrix(
            $rows,
            'location_to', 'period', 'tval_dcur',
            "$php_self?m[0]=activity2&m[1]=ai&m[2]=listBySSOERPPeriod",
            "Accrued Income for SSO ${sess_params['location_from']}, Factory ${sess_params['location_to']} and Year ${sess_params['year']}, ($DCUR) [$limit_Y years window]",
            [
                'doNotShowXTotals' => true,
                'yItemsReversed' => true,
            ]
        );
        $body .= $form->fetch();
        break;
    default:
        $year = date('Y');
        $query = getAccruedIncomeQuery($DCUR, $year);
        $rows = tldUtils::getSqlToAssocArray($query);
        $form = new tldMatrix(
            $rows,
            'location_to', 'location_from', 'tval_dcur',
            "$php_self?m[0]=activity2&m[1]=ai&m[2]=sumBySSOERP&year=$year",
            "YTD Accrued Income by ALL SSO, ALL Factory for $year, in $DCUR"
        );
        $body .= $form->fetch();
        // Historic version
        $form = new HTML_QuickForm('frmSA2');
        $form->addElement('hidden', 'm[0]', $m[0]);
        $form->addElement('hidden', 'm[1]', $m[1]);
        $form->addElement('header', 'title', 'Historical Accrued Income');
        $form->addElement('date', 'year', 'Select year',
            ['format' => 'Y', 'minYear' => date('Y') - 4, 'maxYear' => date('Y') - 1]
        );
        $form->addElement('submit', 'btnSubmit', 'Submit');
        $form->setDefaults(['year' => ['Y' => date('Y') - 1]]);
        $body .= '<br>' . $form->toHTML();

        if ($form->validate()) {
            $vars = tldUtils::cleanupFormInput($form->exportValues());
            $year = $vars['year']['Y'];
            $query = getAccruedIncomeQuery($DCUR, $year);
            $rows = tldUtils::getSqlToAssocArray($query);
            $form = new tldMatrix(
                $rows,
                'location_to', 'location_from', 'tval_dcur',
                "$php_self?m[0]=activity2&m[1]=ai&m[2]=sumBySSOERP&year=$year",
                "YTD Accrued Income by ALL SSO, ALL Factory for $year, in $DCUR"
            );
            $body .= $form->fetch();
        }
}

function getAccruedIncomeQuery($DCUR, $year)
{
    $query = <<<EOF
SELECT
    bu_from.location AS location_from,
    bu_to.location AS location_to,
    ROUND(SUM(trans.tval*(if(to_rate.rate is null, 1, to_rate.rate))/(if(from_rate.rate is null, 1, from_rate.rate))), 2) as tval_dcur
FROM sor AS sors
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
WHERE trans.tgrp='SSO'
    AND trans.ttyp='R'
    AND trans.dref IS NOT NULL
    AND DATEDIFF(
    	trans.dtran,
    	LAST_DAY(CONCAT('$year','01','01'))
    ) >= 0
GROUP BY location_from, location_to
EOF;
    return $query;
}
