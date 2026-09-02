<?php
$DEFAULT_TITLE .= "\Bookings";

$xItemTrans = [
    'location_from' => 'SSO',
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
    'period' => 'Booking Month',
    'sols_model' => 'Model Ordered',
    'qty' => '# of units in SOL',
    'tcur' => 'Booking Currency',
    'tval' => 'Booking Amount',
    'rate_cross' => 'Cross rate',
    'tval_dcur' => "Booking Amount ($DCUR)",
    'notes' => 'Notes',
];

switch ($m[2]) {
    case 'listBySSOERPPeriod':
        $sess_params = &$sess['sales_service']['activity'];
        $sess_params['year'] = array_key_exists('year', $sess_params) ? $sess_params['year'] : substr($y, 0, 4);
        $sess_params['location_from'] = array_key_exists('location_from', $sess_params) ? $sess_params['location_from'] : $x;

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
        $sess_params['location_to'] = array_key_exists('location_to', $sess_params) ? $sess_params['location_to'] : $location_from;

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
	IF(
		(SELECT count(id) FROM sor_tran WHERE parent_id=sols.id AND id<trans.id) > 0,
		'N','Y'
	) AS initial_trans,
    bu_from.location AS location_from,
    sols.id AS sol_id,
    (SELECT CONCAT(lastname,', ',firstname) FROM people WHERE people.id=sors.asm) AS asm_fullname,
    (SELECT type FROM customers WHERE customer_name=sors.cu_nama) AS cu_type,
    sors.cu_nama AS cu_name,
    sors.agnt_nama AS agnt_nama,
    sols.ctry,
    sols.intro_new,
    "" AS continent,
    bu_to.location AS location_to,
    (SELECT cat.en FROM products_categories AS cat LEFT JOIN models ON models.parent_id=cat.id WHERE models.model=sols.model LIMIT 1) AS er_type,
    (SELECT customers.customer_name FROM customers WHERE customers.id=sors.user_customer_id) AS user_customer_display,
    (SELECT customers.customer_name FROM customers WHERE customers.id=sors.buyer_customer_id) AS buyer_customer_display,
    sors.t_cuno,
    sols.model AS er_model,
    sors.id AS sor_id,
    sors.cu_orno,
    sols.sls_orno,
    sols.inco,
    sols.inco_loc,
    sols.model AS sols_model,
    (SELECT SUM(batch_qty) from sor_units where parent_id=sols.id) as qty,
    DATE_FORMAT(trans.dtran, '%Y%m') AS period,
    trans.dtran,
    trans.tcur AS tcur,
    trans.notes,
    trans.id AS tid,
    FORMAT(trans.tval, 2) AS tval,
    from_rate.rate AS rate_from,
    to_rate.rate AS rate_to,
    ROUND(COALESCE(to_rate.rate, 1)/COALESCE(from_rate.rate, 1), 4)  AS rate_cross,
    FORMAT(ROUND(trans.tval*COALESCE(to_rate.rate, 1)/COALESCE (from_rate.rate, 1), 2), 2) AS tval_dcur,
    (SELECT MIN(date) FROM mod_logs WHERE module='SOL' AND parent_id=sols.id AND comment LIKE '%CREATE_FACTORY_SO%') AS dt_createFactorySo,
    (SELECT MIN(date) FROM mod_logs WHERE module='SOL' AND parent_id=sols.id AND comment LIKE '%PRINT_FACTORY_SO_ACK%') AS dt_printFactorySoAck
FROM
    sor AS sors
    JOIN sor_lines AS sols ON sors.id=sols.parent_id
    JOIN sor_tran AS trans ON sols.id=trans.parent_id
    LEFT JOIN locations AS bu_to on sols.bu = bu_to.id
    LEFT JOIN locations AS bu_from ON sors.sso = bu_from.id
    LEFT JOIN erp_forex2 AS from_rate
        ON from_rate.nam_year*12+from_rate.nam_month=YEAR(trans.dtran)*12+MONTH(trans.dtran)-1
        AND from_rate.nam_cur=trans.tcur AND from_rate.typ='END'
    LEFT JOIN erp_forex2 AS to_rate
        ON to_rate.nam_year*12+to_rate.nam_month=YEAR(trans.dtran)*12+MONTH(trans.dtran)-1
        AND to_rate.nam_cur='$DCUR' AND to_rate.typ='END'
    LEFT JOIN tld_juridical_locations AS juridical ON juridical.id=sors.juridical_entity_id
WHERE
    trans.ttyp='B'
    AND trans.tgrp='SSO'
    AND bu_from.location LIKE '$location_from'
    AND bu_to.location LIKE '$location_to'
    AND DATE_FORMAT(trans.dtran, '%Y') LIKE '$year'
    AND DATE_FORMAT(trans.dtran, '%Y%m') LIKE '$period'
ORDER BY location_from, location_to, period, sol_id
EOF;

        $rows = tldUtils::getSqlToAssocArray($query);
        if ($showmargin == 1 || $showFactoryDiscount == 1 || $showCustomerDiscount == 1) {
            switch (true) {
                case $showmargin == 1:
                    $dataKey = 'margin_percent';
                    $dataColumnTitle = 'Margin (%)';
                    $calcMethod = static function ($data) {
                        return round($data['marg_xtot'] * 100 / $data['prin_xtot'], 2);
                    };
                    break;
                case $showFactoryDiscount == 1:
                    $dataKey = 'factory_discount';
                    $dataColumnTitle = 'Factory discount (%)';
                    $calcMethod = static function ($data) {
                        return round($data['factory_discount'] / $data['published_tp'] * 100, 2);
                    };
                    break;
                case $showCustomerDiscount == 1:
                    $dataKey = 'customer_discount';
                    $dataColumnTitle = 'Customer discount (%)';
                    $calcMethod = static function ($data) {
                        $total = $data['customer_discount_infos']['prip_tot_in_dcur'] + ($data['customer_discount_infos']['pric_tot_in_dcur'] / 0.9);
                        return $data['customer_discount_infos']['discount'] > 0 && $total !== 0 ? round(
                            $data['customer_discount_infos']['discount'] * 100 / $total,
                            2
                        ) : '-';
                    };
                    break;
            }
            foreach ($rows as &$row) {
                $sol = new tldSOL($row['sol_id']);
                $data = $sol->getSummary(['dcur' => $DCUR]);
                if ((int)$data['prin_xtot'] !== 0 && (int)$data['marg_xtot'] !== 0) {
                    $row[$dataKey] = $calcMethod($data);
                }
            }
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
                'period' => 'Booking Month',
                'sols_model' => 'Model Ordered',
                'qty' => '# of units in SOL',
                'tcur' => 'Booking Currency',
                'tval' => 'Booking Amount',
                $dataKey => $dataColumnTitle,
                'rate_cross' => 'Cross rate',
                'tval_dcur' => "Booking Amount ($DCUR)",
                'notes' => 'Notes',
            ];
        }

        switch ($m[3]) {
            case 'fullCSV':
                $rowsCSV = [];
                $i = 0;
                foreach ($rows as $row) {
                    $i++;
                    $sol = new tldSOL($row['sol_id']);
                    // Get detailled summary
                    $option = [
                        'dcur' => $DCUR,
                        'dt_cur' => $row['dtran'],
                        'qty' => $row['qty'],
                    ];
                    if ($row['initial_trans'] === 'Y') {
                        $summary = _getFullSummary($sol, $option);
                        $summary['dcur'] = $row['tcur'];
                        $summary['pris_xtot_default_cur'] = $row['tval'];
                        $summary['pris_xtot'] = $row['tval_dcur'];
                    } else {
                        $row['qty'] = '';
                        $summary = [
                            'dcur' => $row['tcur'],
                            'pris_xtot_default_cur' => $row['tval'],
                            'pris_xtot' => $row['tval_dcur'],
                        ];
                    }
                    // Merge data
                    $row['level'] = "$i - TRANS";
                    $rowsCSV[] = $row + $summary;
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
                        'title' => "Bookings for SSO ${sess_params['location_from']}, Factory ${sess_params['location_to']} and Year ${sess_params['year']}, ($DCUR)",
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
    case 'sumBySSOERPYear':
        $matrixParams = [];

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
            $matrixParams['doNotShowYTotals'] = true;
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

        $query = <<<EOF
SELECT
    bu_from.location AS location_from,
    bu_to.location AS location_to,
    date_format(dtran, '%Y%m')as period,
    ROUND(SUM(trans.tval*(if(to_rate.rate is null, 1, to_rate.rate))/
        (if(from_rate.rate is null, 1, from_rate.rate))), 2
    ) as tval_dcur
FROM
    sor AS sors
    JOIN sor_lines as sols on sors.id=sols.parent_id
    JOIN sor_tran AS trans on sols.id=trans.parent_id
    LEFT JOIN locations AS bu_to on sols.bu = bu_to.id
    LEFT JOIN locations AS bu_from ON sors.sso = bu_from.id
    LEFT JOIN erp_forex2 AS from_rate
        ON from_rate.nam_year*12+from_rate.nam_month=YEAR(trans.dtran)*12+MONTH(trans.dtran)-1
        AND from_rate.nam_cur=trans.tcur AND from_rate.typ='END'
    LEFT JOIN erp_forex2 AS to_rate
        ON to_rate.nam_year*12+to_rate.nam_month=YEAR(trans.dtran)*12+MONTH(trans.dtran)-1
        AND to_rate.nam_cur='$DCUR' AND to_rate.typ='END'
WHERE
    trans.tgrp='SSO'
    AND trans.ttyp='B'
    AND YEAR(trans.dtran)=$year
    AND bu_from.location LIKE '$location_from'
    AND bu_to.location LIKE '$location_to'
GROUP BY $GROUP
EOF;
        $rows = tldUtils::getSqlToAssocArray($query);
        $form = new tldMatrix(
            $rows,
            'location_to', 'period', 'tval_dcur',
            "$php_self?m[0]=activity2&m[1]=bookings&m[2]=listBySSOERPPeriod",
            "Bookings for SSO ${sess_params['location_from']}, Factory ${sess_params['location_to']} and Year ${sess_params['year']}, ($DCUR)",
            $matrixParams
        );
        $body .= $form->fetch();
        break;
    case 'marginBySSOERPYear':
    case 'factoryDiscountBySSOERPYear':
    case 'customerDiscountBySSOERPYear':
        $BuID = $user->getBUID();
        $location = new tldLocation($BuID);
        $BuName = $location->getBuName();
        if (!in_array($BuID, [5, 59]) && ($BuName != $y && $BuName != $x) && !$user->isInGroupLevel('role_CFO', 900)) {
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

        $query = <<<EOF
SELECT
	sols.id,
    bu_from.location AS location_from,
    bu_to.location AS location_to,
    date_format(dtran, '%Y%m')as period
FROM sor AS sors
    JOIN sor_lines as sols on sors.id=sols.parent_id
    JOIN sor_tran AS trans on sols.id=trans.parent_id
    LEFT JOIN locations AS bu_to on sols.bu = bu_to.id
    LEFT JOIN locations AS bu_from ON sors.sso = bu_from.id
WHERE
    trans.tgrp='SSO'
    AND trans.ttyp='B'
    AND YEAR(trans.dtran)=$year
    AND bu_from.location LIKE '$location_from'
    AND bu_to.location LIKE '$location_to'
GROUP BY sols.id
EOF;

        $rows = tldUtils::getSqlToAssocArray($query);
        $calculations = [];
        $tableData = [];
        $distinctLocations = [];
        foreach ($rows as &$row) {
            $sol = new tldSOL($row['id']);
            $data = $sol->getSummary(['dcur' => $DCUR]);
            if ($data['prin_xtot'] !== 0) {
                $row['tot_net_price'] = $data['prin_xtot']; // SOL Total Net Selling Price
                $row['tot_margin'] = $data['marg_xtot']; // SOL Total Margin
                $period = $row['period'];
                $location_to = $row['location_to'];
                $location_from = $row['location_from'];

                if (!array_key_exists($period, $calculations)) {
                    $calculations[$period] = [];
                }

                if (!array_key_exists($location_to, $calculations[$period])) {
                    $calculations[$period][$location_to] = [];
                }

                $calculations[$period][$location_to]['total_marg'] = $calculations[$period][$location_to]['total_marg'] + $row['tot_margin'];
                $calculations[$period][$location_to]['total_net'] = $calculations[$period][$location_to]['total_net'] + $row['tot_net_price'];
                $calculations[$period][$location_to]['period'] = $period;
                $calculations[$period][$location_to]['location_to'] = $location_to;
                $calculations[$period][$location_to]['discf'] += (int) ($data['discf'] ?? 0);
                $calculations[$period][$location_to]['published_tp'] += $data['published_tp'];
                $calculations[$period][$location_to]['customer_discount'] += $data['customer_discount_infos']['discount'];
                $calculations[$period][$location_to]['prip_tot_in_dcur'] += $data['customer_discount_infos']['prip_tot_in_dcur'];
                $calculations[$period][$location_to]['pric_tot_in_dcur'] += $data['customer_discount_infos']['pric_tot_in_dcur'];
                array_push($distinctLocations, $location_to);
            }
        }
        foreach ($calculations as $level1 => $level2) {
            foreach ($level2 as $data) {
                switch ($m[2]) {
                    case 'marginBySSOERPYear':
                        $tableData[] = [
                            'period' => $data['period'],
                            'location_to' => $data['location_to'],
                            'avg_margin_percent' => $data['total_marg'] * 100 / $data['total_net'],
                            'total_marg' => $data['total_marg'],
                            'total_net' => $data['total_net'],
                        ];
                        break;
                    case 'factoryDiscountBySSOERPYear':
                        $tableData[] = [
                            'period' => $data['period'],
                            'location_to' => $data['location_to'],
                            'avg_factory_discount' => $data['published_tp'] !== 0 ? round(
                                $data['discf'] / $data['published_tp'] * 100,
                                2
                            ) : null,
                            'total_marg' => $data['published_tp'] !== 0 ? $data['discf'] : null,
                            'total_net' => $data['published_tp'] !== 0 ? $data['published_tp'] : null,
                        ];
                        break;
                    case 'customerDiscountBySSOERPYear':
                        $total = $data['prip_tot_in_dcur'] + ($data['pric_tot_in_dcur'] / 0.9);
                        $tableData[] = [
                            'period' => $data['period'],
                            'location_to' => $data['location_to'],
                            'avg_customer_discount' => $data['customer_discount'] > 0 && $total !== 0 ? round(
                                $data['customer_discount'] * 100 / $total,
                                2
                            ) : 0,
                            'total_marg' => $data['customer_discount'] !== 0 ? $data['customer_discount'] : null,
                            'total_net' => $total > 0 ? $total : null,
                        ];
                        break;
                }
            }
        }
        $options = [
            'decimals' => true,
            'average_margin_sa2' => true,
            'doNotShowYTotals' => count(array_unique($distinctLocations)) == 1,
        ];
        switch ($m[2]) {
            case 'marginBySSOERPYear':
                $title = "Bookings Average Margin (%) for SSO ${sess_params['location_from']}, Factory ${sess_params['location_to']} and Year ${sess_params['year']}, ($DCUR)";
                $key = 'avg_margin_percent';
                $link = "$php_self?m[0]=activity2&m[1]=bookings&m[2]=listBySSOERPPeriod&showmargin=1";
                break;
            case 'factoryDiscountBySSOERPYear':
                $title = "Average Factory Discount (%) for SSO ${sess_params['location_from']}, Factory ${sess_params['location_to']} and Year ${sess_params['year']}";
                $options['doNotFormatCells'] = true;
                $key = 'avg_factory_discount';
                $link = "$php_self?m[0]=activity2&m[1]=bookings&m[2]=listBySSOERPPeriod&showFactoryDiscount=1";
                break;
            case 'customerDiscountBySSOERPYear':
                $title = "Average Customer Discount (%) for SSO ${sess_params['location_from']}, Factory ${sess_params['location_to']} and Year ${sess_params['year']}";
                $options['doNotFormatCells'] = true;
                $key = 'avg_customer_discount';
                $link = "$php_self?m[0]=activity2&m[1]=bookings&m[2]=listBySSOERPPeriod&showCustomerDiscount=1";
                break;
        }
        $form = new tldMatrix(
            $tableData,
            'location_to',
            'period',
            $key,
            $link,
            $title,
            $options
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
        $calculations = $margin = $factoryDiscounts = $customerDiscounts = [];
        foreach ($rows as &$row) {
            $sol = new tldSOL($row['id']);
            $data = $sol->getSummary(['dcur' => $DCUR]);
            // prin_xtot : total net selling price in $DCUR"
            if ($data['prin_xtot'] != 0) {
                $row['tot_net_price'] = $data['prin_xtot'];
                $row['tot_margin'] = $data['marg_xtot'];
                $location_from = $row['location_from'];
                $location_to = $row['location_to'];

                if (!array_key_exists($location_from, $calculations)) {
                    $calculations[$location_from] = [];
                }

                if (!array_key_exists($location_to, $calculations[$location_from])) {
                    $calculations[$location_from][$location_to] = [
                        'total_marg' => 0,
                        'total_net' => 0,
                        'location_from' => '',
                        'location_to' => '',
                        'factory_discount' => 0,
                        'published_tp' => 0,
                        'customer_discount' => 0,
                        'prip_tot_in_dcur' => 0,
                        'pric_tot_in_dcur' => 0,
                    ];
                }

                $calculations[$location_from][$location_to]['total_marg'] += $row['tot_margin'];
                $calculations[$location_from][$location_to]['total_net'] += $row['tot_net_price'];
                $calculations[$location_from][$location_to]['location_from'] = $location_from;
                $calculations[$location_from][$location_to]['location_to'] = $location_to;
                $calculations[$location_from][$location_to]['factory_discount'] += $data['factory_discount'];
                $calculations[$location_from][$location_to]['published_tp'] += $data['published_tp'];
                $calculations[$location_from][$location_to]['customer_discount'] += $data['customer_discount_infos']['discount'];
                $calculations[$location_from][$location_to]['prip_tot_in_dcur'] += $data['customer_discount_infos']['prip_tot_in_dcur'];
                $calculations[$location_from][$location_to]['pric_tot_in_dcur'] += $data['customer_discount_infos']['pric_tot_in_dcur'];
            }
        }

        foreach ($calculations as $level1 => $level2) {
            foreach ($level2 as $data) {
                $margin[] = [
                    'location_from' => $data['location_from'],
                    'location_to' => $data['location_to'],
                    'avg_margin_percent' => $data['total_marg'] * 100 / $data['total_net'],
                    'total_marg' => $data['total_marg'],
                    'total_net' => $data['total_net'],
                ];
                $factoryDiscounts[] = [
                    'location_from' => $data['location_from'],
                    'location_to' => $data['location_to'],
                    'avg_factory_discount' => 0 !== $data['published_tp']
                        ? round($data['factory_discount'] / $data['published_tp'] * 100,2)
                        : 0,
                    'total_marg' => $data['factory_discount'],
                    'total_net' => $data['published_tp'],
                ];

                $total = $data['prip_tot_in_dcur'] + ($data['pric_tot_in_dcur'] / 0.9);

                $customerDiscounts[] = [
                    'location_from' => $data['location_from'],
                    'location_to' => $data['location_to'],
                    'avg_customer_discount' => $data['customer_discount'] > 0 && $total !== 0 ? round(
                        $data['customer_discount'] * 100 / $total,
                        2
                    ) : 0,
                    'total_marg' => $data['customer_discount'] > 0 ? $data['customer_discount'] : 0,
                    'total_net' => $total > 0 ? $total : null,
                ];
            }
        }
        $table2 = new tldMatrix(
            $margin,
            'location_to', 'location_from', 'avg_margin_percent',
            "$php_self?m[0]=activity2&m[1]=bookings&m[2]=marginBySSOERPYear&year=$year",
            "Bookings Average Margin (%) by ALL SSO, ALL Factory for $year",
            [
                'decimals' => true,
                'average_margin_sa2' => true,
            ]
        );
        $body .= $table2->fetch();

        $table3 = new tldMatrix(
            $factoryDiscounts,
            'location_to', 'location_from', 'avg_factory_discount',
            "$php_self?m[0]=activity2&m[1]=bookings&m[2]=factoryDiscountBySSOERPYear&year=$year",
            "Average Factory Discount (%) by ALL SSO, ALL Factory for $year",
            [
                'decimals' => true,
                'doNotFormatCells' => true,
                'average_margin_sa2' => true,
            ]
        );
        $body .= $table3->fetch();

        $table4 = new tldMatrix(
            $customerDiscounts,
            'location_to', 'location_from', 'avg_customer_discount',
            "$php_self?m[0]=activity2&m[1]=bookings&m[2]=customerDiscountBySSOERPYear&year=$year",
            "Average Customer Discount (%) by ALL SSO, ALL Factory for $year",
            [
                'decimals' => true,
                'doNotFormatCells' => true,
                'average_margin_sa2' => true,
            ]
        );
        $body .= $table4->fetch();
        break;
    default:
        $year = date('Y');

        // Historic version
        $form = new HTML_QuickForm('frmSA2');
        $form->addElement('hidden', 'm[0]', $m[0]);
        $form->addElement('hidden', 'm[1]', $m[1]);
        $form->addElement('header', 'title', 'Historical Bookings');
        $form->addElement('date', 'year', 'Select year',
            [
                'format' => 'Y',
                'minYear' => date('Y') - 10,
                'maxYear' => date('Y') - 1,
            ]
        );
        $form->addElement('submit', 'btnSubmit', 'Submit');
        $form->setDefaults(['year' => ['Y' => date('Y') - 1]]);

        if ($form->validate()) {
            $vars = tldUtils::cleanupFormInput($form->exportValues());
            $year = $vars['year']['Y'];
        }
        $query = getBookingsQuery($DCUR, $year);
        $rows = tldUtils::getSqlToAssocArray($query);
        $table1 = new tldMatrix(
            $rows,
            'location_to', 'location_from', 'tval_dcur',
            "$php_self?m[0]=activity2&m[1]=bookings&m[2]=sumBySSOERPYear&year=$year",
            "YTD Bookings by ALL SSO, ALL Factory for $year, in $DCUR",
            ['string_format' => '%.0f']
        );
        $body .= $table1->fetch();
        if ($user->isInGroup(['gg_ADMIN', 'gg_EXCOM'])) {
            // Margin Total ------------------------------------------------------------------------------------
            $body .= <<<HTML
<div class="js-booking-detail-target" >
    <br />
    Loading...       
</div>

<script type="text/javascript">
    document.addEventListener("DOMContentLoaded", function() {
        var headers = new Headers();
        headers.append('X-Requested-With', 'XMLHttpRequest')
        fetch('$php_self?m[0]=activity2&m[1]=bookings&m[2]=partialHomepage&year=$year', {headers: headers}).then(function(response) {
            return response.text()
        }).then(function(body) {
            document.querySelector('.js-booking-detail-target').innerHTML = body
        })
    });
</script>
HTML;
        }
        $body .= '<br>' . $form->toHTML();
}

function getBookingsQuery($DCUR, $year)
{
    $query = <<<EOF
SELECT
    bu_from.location AS location_from,
    bu_to.location AS location_to,
    ROUND(SUM(trans.tval*COALESCE(to_rate.rate, 1) / COALESCE(from_rate.rate, 1)), 2) as tval_dcur
FROM sor AS sors
    INNER JOIN sor_lines as sols on sors.id=sols.parent_id
    INNER JOIN sor_tran AS trans on sols.id=trans.parent_id
    LEFT JOIN locations AS bu_to on sols.bu = bu_to.id
    LEFT JOIN locations AS bu_from ON sors.sso = bu_from.id
    LEFT JOIN erp_forex2 AS from_rate 
        ON from_rate.nam_year*12+from_rate.nam_month=YEAR(trans.dtran)*12+MONTH(trans.dtran)-1
        AND from_rate.nam_cur=trans.tcur AND from_rate.typ='END'
    LEFT JOIN erp_forex2 AS to_rate
        ON to_rate.nam_year*12+to_rate.nam_month=YEAR(trans.dtran)*12+MONTH(trans.dtran)-1
        AND to_rate.nam_cur='$DCUR' AND to_rate.typ='END'
WHERE trans.tgrp='SSO' AND trans.ttyp='B' AND YEAR(trans.dtran)=$year
GROUP BY location_to, location_from
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
    LEFT JOIN locations AS bu_from ON sors.sso = bu_from.id
where trans.tgrp='SSO'
    AND trans.ttyp='B'
    AND YEAR(trans.dtran)=$year
GROUP BY sols.id
EOF;
    return $query;
}
