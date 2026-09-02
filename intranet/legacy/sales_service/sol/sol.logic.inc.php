<?php

use ApiBundle\Client;

include_once 'sales_service.inc.php';
include_once 'erp.inc.php';
include_once 'finance.inc.php';
include_once 'product_support.inc.php';
$JS_INCLUDE=["/shared/javascript/overlib/overlib.js"];

// Get MOO ID
$moo_id = tldModule::getMOOIDByModule('sol');
if (!$user->isInGroup(['role_CSD','gg_ADMIN', 'role_ASM', 'role_SA', 'gg_SUPPORT', 'gg_ACCT', 'gg_TRANSPORT', 'role_EVP', 'gg_PARTS', 'gg_ENG', 'role_MLM', 'role_QAM', 'role_QE', 'role_planner', 'role_PM', 'role_PSM', 'role_PSE', 'role_PSA', 'role_COO', 'role_CMO', 'role_MPE', 'GG_TEST', 'role_PS'])) {
    $DEFAULT_ERROR[] = 'ERROR: You do not have permission to access this module';
    return; // return used to get out of the include (see: http://us2.php.net/manual/en/function.include.php)
}

$DEFAULT_TITLE .= "\Sales Order Record Lines";
$DEFAULT_MENU .= <<<EOF
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<a href="$php_self?m[0]=sol">Home</a>
&nbsp;|&nbsp;<a href="$php_self?m[0]=sol&m[1]=form&m[2]=byNum">By number</a>
&nbsp;|&nbsp;<a href="$php_self?m[0]=sol&m[1]=listing&m[2]=search">Search</a>
&nbsp;|&nbsp;<a href="$php_self?m[0]=sol&m[1]=reports">Reports</a>
&nbsp;|&nbsp;<a href="/en/private/mis/mis.php?m[0]=help&m[1]=dms&id=1234">DMS Help</a>
&nbsp;|&nbsp;<a href="/en/private/directory/index.php?m[0]=people&m[1]=view&id=$moo_id" title="Module Owner">Owner</a>
EOF;

if ($user->isInGroup(['gg_ADMIN', 'gg_ACCT'])) {
    $DEFAULT_MENU .= <<<EOF
	&nbsp;|&nbsp;<a href="sol/sol_admin.php">Maintain SOR Lines</a>
EOF;
}

$SOL_FIELDS = [
    'parent_id' => 'SOR#',
    'id' => 'SOL#',
    'cu_nama' => 'Customer Name',
    'dt_opened' => 'Date Opened',
    'dt_closed' => 'Date Closed',
    'status' => 'Status',
    'dzk_sso' => 'Date of Zero Backlog, SSO',
    'dzk_erp' => 'Date of Zero Backlog, Factory',
    'sls_orno' => 'SSO PO#',
    'erp_orno' => 'Factory SO#',
    'bu' => 'TLD Factory BU',
    'model' => 'Model',
    'eng_tier' => 'Emission Rating',
    'parts_inc' => 'Ship with Spare Parts?',
    'intro_new' => 'New Introduction ?',
    'notes' => 'Notes',
    'warranty_length' => 'Warranty Length (Months)',
    'warranty_length_hours' => 'Warranty Length (Hours)',
    'wrty_spec' => 'Special Warranty Conditions',
    'conf_wrty_erp' => 'Warranty Conditions accepted by factory?',
    'tpay' => 'Payment terms',
    'conf_cxo' => 'Payment terms 100% after shipment or Has the downpayment been received (if any) or Has the LC been opened (if any)',
    'conf_lc' => 'Letter of Credit Required',
    'cu_ocur' => 'Customer Order Currency',
    'dp_amt' => 'Down Payment Amount',
    'dp_pc' => 'Down Payment (in %)',
    'inco' => 'Inco Terms',
    'inco_loc' => 'Inco Location',
    'ctry' => 'Country',
    'del_pen' => 'Late delivery penalties',
    'delpen_cond' => 'Late delivery conditions',
    'conf_sls' => 'Delivery Penalty Accepted by Sales Org',
    'conf_erp' => 'Delivery Penalty Accepted by Factory',
    'trans' => 'Transportation responsability',
    'conf_cis' => 'Customer inspection before shipment',
    'fms_contract_length' => 'FMS contract length in months'
];

// Always reset SOL list in session when not in view mode.
if (!empty($sess['sol']['list']) && $m[1] !== 'view') {
    $sess['sol']['list'] = null;
}

switch ($m[1]) {
    case 'form':
        include './sol/form.inc.php';
        break;
    case 'view':
        include 'sol/view.inc.php';
        break;
    case 'reports':
        include 'sol/sol.reports.inc.php';
        break;
    case 'listing':
        $xItems = [
            'parent_id' => 'SOR ID#',
            'id' => 'SOL ID#',
            'dt_opened' => 'Date Opened',
            'status' => 'Status',
            'user_customer_display' => 'Customer Name (END USER)',
            'buyer_customer_display' => 'Customer Name (BUYER)',
            'erp_fullname' => 'Factory',
            'sls_orno' => 'PO#',
            'erp_orno' => 'Factory SO#',
            'model' => 'Model',
            'qty_sou' => 'Qty Ordered',
            'qty_alloc' => 'Qty Assigned',
            'qty_ship' => 'Qty Shipped',
            'conf_sls' => 'Delivery Penalty Accepted by SSO',
            'conf_erp' => 'Delivery Penalty Accepted by Factory',
        ];
        switch ($m[2]) {
            case 'byCreateFactoryDate':
                If ($bu != '') {
                    $z = $bu;
                    $bu_name = tldLocation::getLocationByID($bu);
                    $bu_title = " - For $bu_name";
                }
                $rows = tldSOL::byCreateFactoryDate($x, $y, $z);
                $TITLE = "SOLs with a CREATE_FACTORY_SO date set between $x and $y $bu_title";
                $xItems = [
                    'parent_id' => 'SOR ID#',
                    'id' => 'SOL ID#',
                    'dt_opened' => 'Date Opened',
                    'status' => 'Status',
                    'dt_create_factory' => 'Create Factory Date',
                    'user_customer_display' => 'Customer Name (END USER)',
                    'buyer_customer_display' => 'Customer Name (BUYER)',
                    'erp_fullname' => 'Factory',
                    'sls_orno' => 'PO#',
                    'erp_orno' => 'Factory SO#',
                    'model' => 'Model',
                    'qty_sou' => 'Qty Ordered',
                    'qty_alloc' => 'Qty Assigned',
                    'qty_ship' => 'Qty Shipped',
                    'conf_sls' => 'Delivery Penalty Accepted by SSO',
                    'conf_erp' => 'Delivery Penalty Accepted by Factory',
                ];
                break;
            case 'search':
                if (isset($m[3], $x) && 'xls' === $m[3]) {
                    $rows = tldSOL::search($x);
                    break;
                }
                $DEFAULT_TITLE .= "\Search";
                $form = new HTML_QuickForm('frmSOLSearch', 'get', '', '', '', true);
                $form->addElement('header', 'title', 'Search SOL by PO#, model or customer name');
                $form->addElement('hidden', 'm[0]', 'sol');
                $form->addElement('hidden', 'm[1]', 'listing');
                $form->addElement('hidden', 'm[2]', 'search');
                $form->addElement('text', 'target', 'Look for', ['size' => '20']);
                $form->addElement('submit', 'btnSubmit', 'Submit');
                $form->addRule('target', 'This is required', 'required');

                if (!$form->validate()) {
                    $body = $form->toHTML();
                    break;
                }

                $form->freeze();
                $rows = tldSOL::search($target);
                // Used for Excel
                $x = $target;
                $TITLE = 'SOL search results';
                break;
            case 'byCashForecastSSO':
                $x = TldDatabase::escape($x);
                $rows = tldSOL::byCashForecast('SSO', $x);
                $TITLE = "SOL SSO cash forecast for $x";
                break;
            case 'byCashForecastERP':
                $x = TldDatabase::escape($x);
                $rows = tldSOL::byCashForecast('ERP', $x);
                $TITLE = "SOL Factory cash forecast for $x";
                break;
            case 'byEngineeringFlag':
                $x = TldDatabase::escape($x);
                $rows = tldSOL::byConstraints(" erp_fullname LIKE '$x' and engineering_flag=1 AND T1.status != 'CLOSED' ");
                $TITLE = "SOR Line with Engineering Flag for $x";
                $solIds = array_column($rows, 'id');
                $estGTBySOL = [];
                if ($solIds) {
                    $inList = implode(',', $solIds);
                    foreach (tldUtils::getSqlToAssocArray(<<<SQL
                        SELECT sor_lines.id, MIN(service.dgt_rev) AS min_est_gt
                        FROM sor_lines
                        LEFT JOIN sor_units ON sor_units.parent_id = sor_lines.id
                        LEFT JOIN service ON service.sor_uid = sor_units.id
                        WHERE sor_lines.id IN ($inList)
                        GROUP BY sor_lines.id
                    SQL) as $r) {
                        $estGTBySOL[$r['id']] = $r['min_est_gt'];
                    }
                }
                $meapIdsBySol = [];
                $eapIdsBySol = [];
                foreach (tldModLink::byParent($solIds, 'SOL', 'MEAP') as $l) {
                    $meapIdsBySol[$l['parent_id']][] = $l['item'];
                }
                foreach (tldModLink::byItem($solIds, 'SOL', 'MEAP') as $l) {
                    $meapIdsBySol[$l['item']][] = $l['parent_id'];
                }
                foreach (tldModLink::byParent($solIds, 'SOL', 'EAP') as $l) {
                    $eapIdsBySol[$l['parent_id']][] = $l['item'];
                }
                foreach (tldModLink::byItem($solIds, 'SOL', 'EAP') as $l) {
                    $eapIdsBySol[$l['item']][] = $l['parent_id'];
                }
                $meapStatuses = [];
                $eapStatuses = [];
                $allMeapIds = array_unique(array_merge(...(array_values($meapIdsBySol) ?: [[]])));
                $allEapIds = array_unique(array_merge(...(array_values($eapIdsBySol) ?: [[]])));
                if ($allMeapIds) {
                    foreach (tldUtils::getSqlToAssocArray('SELECT id, status FROM meap WHERE id IN (' . implode(',', $allMeapIds) . ')') as $r) {
                        $meapStatuses[$r['id']] = $r['status'];
                    }
                }
                if ($allEapIds) {
                    foreach (tldUtils::getSqlToAssocArray('SELECT id, status FROM eap WHERE id IN (' . implode(',', $allEapIds) . ')') as $r) {
                        $eapStatuses[$r['id']] = $r['status'];
                    }
                }
                foreach ($rows as &$row) {
                    $row['estimated_gt_date'] = $estGTBySOL[$row['id']] ?? null;
                    $eapLinks = [];
                    foreach (array_unique($meapIdsBySol[$row['id']] ?? []) as $meapId) {
                        $url = tldModLink::getURL('MEAP', $meapId);
                        $eapLinks[] = "<a href=\"$url\">#{$meapId}</a> - ({$meapStatuses[$meapId]})";
                    }
                    foreach (array_unique($eapIdsBySol[$row['id']] ?? []) as $eapId) {
                        $url = tldModLink::getURL('EAP', $eapId);
                        $eapLinks[] = "<a href=\"$url\">#{$eapId}</a> - ({$eapStatuses[$eapId]})";
                    }
                    $row['eap_info'] = implode('; ', $eapLinks);
                }
                unset($row);
                $xItems = array_merge($xItems, [
                    'estimated_gt_date' => 'Estimated GT Date',
                    'eap_info' => 'EAP',
                ]);
                break;
            case 'byRequiredExportLicence':
                $x = TldDatabase::escape($x);
                $rows = tldSOL::byConstraints(" erp_fullname LIKE '$x' and export_licence_status='REQUIRED' AND T1.status != 'CLOSED' ");
                $TITLE = "SOR Line waiting for export licence for $x";
                break;
            case 'byERPPriorInProgress':
                $erp = TldDatabase::escape($erp);
                $constraint = "T2.location = '$erp' AND T1.status NOT IN ('IN_PROGRESS','SHIPPED','CLOSED')";
                $rows = tldSOL::byConstraints($constraint);
                $TITLE = "Sales Order Lines prior to IN_PROGRESS for Factory $x";
                break;
            case 'byERPCustomerByASM':
                $id = TldDatabase::escape($id);
                $erp = TldDatabase::escape($x);
                $customer = $y;
                $sso = TldDatabase::escape($sso);
                $rows = tldSOL::byERPCustomerByASM($erp, $y, $id, $sso);
                if (empty($sso)) {
                    $TITLE = "SOL by ERP $x - Customer $y for ASM# $id - Last 12 months";
                } else {
                    $TITLE = "SOL by ERP $x - Customer $y for SSO# $sso - Last 12 months";
                }
                break;
            case 'byERPStatus':
                $x = TldDatabase::escape($x);
                $y = TldDatabase::escape($y);

                if (isset($m[3]) && 'xls_options' === $m[3]) {
                    $rows = tldSOL::optionsByERPStatus($x, $y);
                } else {
                    $rows = tldSOL::byERPStatus($x, $y);
                }

                $additionalColumns = [
                    'delivery_country' => 'Delivery country',
                    'sso_fullname' => 'SSO',
                ];

                if (isset($m[3]) && ('xls' === $m[3] || 'xls_options' === $m[3])) {
                    $additionalColumns['options'] = 'Options';
                    $additionalColumns['publishedTP_currency'] = 'Published TP / Currency';
                    $additionalColumns['publishedPriceList_currency'] = 'Published Price List / Currency';
                }

                if ($y === 'CREATE_FACTORY_SO') {
                    $xItems = [
                        'parent_id' => 'SOR ID#',
                        'id' => 'SOL ID#',
                        'dt_opened' => 'Date Opened',
                        'status' => 'Status',
                        'dt_create_factory' => 'Date Create Factory',
                        'user_customer_display' => 'Customer Name (END USER)',
                        'buyer_customer_display' => 'Customer Name (BUYER)',
                        'erp_fullname' => 'Factory',
                        'sls_orno' => 'PO#',
                        'erp_orno' => 'Factory SO#',
                        'model' => 'Model',
                        'qty_sou' => 'Qty Ordered',
                        'qty_alloc' => 'Qty Assigned',
                        'qty_ship' => 'Qty Shipped',
                        'conf_sls' => 'Delivery Penalty Accepted by SSO',
                        'conf_erp' => 'Delivery Penalty Accepted by Factory',
                    ];
                }

                $xItems = array_merge($xItems, $additionalColumns);
                $TITLE = "Sales Order Lines for Factory $y Status $x";
                break;
            case 'bySSOStatus':
                $x = TldDatabase::escape($x);
                $y = TldDatabase::escape($y);
                $rows = tldSOL::bySSOStatus($y, $x);
                $TITLE = "Sales Order Lines for SSO $y Status $x";
                break;
            case 'bySSOERPStatus':
                $xItems = [
                    'parent_id' => 'SOR ID#',
                    'id' => 'SOL ID#',
                    'dt_opened' => 'Date Opened',
                    'status' => 'Status',
                    'sso_fullname' => 'SSO',
                    'user_customer_display' => 'Customer Name (END USER)',
                    'buyer_customer_display' => 'Customer Name (BUYER)',
                    'erp_fullname' => 'Factory',
                    'sls_orno' => 'PO#',
                    'erp_orno' => 'Factory SO#',
                    'model' => 'Model',
                    'qty_sou' => 'Qty Ordered',
                    'qty_alloc' => 'Qty Assigned',
                    'qty_ship' => 'Qty Shipped',
                    'conf_sls' => 'Delivery Penalty Accepted by SSO',
                    'conf_erp' => 'Delivery Penalty Accepted by Factory',
                    'parts_inc' => 'Ship with Spare Parts?',
                ];
                $x = TldDatabase::escape($x);
                $y = TldDatabase::escape($y);
                $z = TldDatabase::escape($z);
                $rows = tldSOL::bySSOERPStatus($x, $y, $z);
                $TITLE = "Sales Order Lines for SSO '$x' ERP '$y' Status '$z'";
                break;
            case 'byUnassigned':    //SOR lines without units assigned yet
                $rows = tldSOL::byUnassigned();
                $TITLE = 'Unassigned Sales Order Lines';
                break;
            case 'byFactoryEstimatedGTPeriod':
                $a = [];
                $x = TldDatabase::escape($x);
                $y = TldDatabase::escape($y);
                $z = TldDatabase::escape($z);
                if (!empty($z)) {
                    $a['sor.bu'] = $z;
                }
                // Get results
                $TITLE = "Sales Order Lines for Factory $y for all SSO Estimated GT in $x";
                $rows = tldSOL::byFactoryEstimatedGTPeriodByConstraints($y, $x, null, true);
                // define columns
                $xItems = [
                    'id' => 'SOL ID#',
                    'status' => 'Status',
                    'bu' => 'SSO',
                    'user_customer_display' => 'USER Customer',
                    'buyer_customer_display' => 'BUYER Customer',
                    'qty_sou' => 'Qty Ordered',
                    'factory_fullname' => 'Factory',
                    'sn' => 'ER SN',
                    'model' => 'Model',
                    'dgt_rev' => 'Estimated GT',
                ];
                // Get additional info
                switch ($m[3]) {
                    case 'detailsByOptionType':
                        if (empty($option)) {
                            $DEFAULT_ERROR[] = 'ERROR: option parameters empty or invalid, results degraded';
                            break;
                        }
                        // Check the option type
                        if ($option === 'SPARE PARTS') {
                            if (!$user->isInGroup(['gg_ADMIN', 'gg_SALES', 'gg_PARTS', 'role_EVP', 'role_SA', 'role_SPM'])) {
                                $DEFAULT_ERROR[] = 'ERROR: You do not have permissions to get breakdown info';
                                break;
                            }
                            $TITLE .= " - with $option info";
                            $xItems = array_merge($xItems, [
                                'caty' => 'Option',
                                'dsca' => 'Description',
                                'pric_cur' => 'Negotiated TP Currency',
                                'pric' => 'Negotiated TP',
                                'pris_cur' => 'Actual Sales Price Currency',
                                'pris' => 'Actual Sales Price',
                            ]);
                            // for each lines get parts details
                            $finalRows = [];
                            foreach ($rows as $k => $row) {
                                $partsList = tldSOROpts::byParent($row['id'], ['include' => ['SPARE PARTS']]);
                                if (!count($partsList)) {
                                    $finalRows[] = $row;
                                    continue;
                                }
                                foreach ($partsList as $partVal) {
                                    $finalRows[] = array_merge($partVal, $row);
                                }
                            }
                            $rows = $finalRows;
                        }
                        break;
                }
                break;
            case 'byLateFOR':
                $rows = tldSOL::byLateFOR();
                $TITLE = 'Sales Order Lines by Late Factory Order Review';
                break;
            case 'byAllCustomerID':
                $x = TldDatabase::escape($x);
                $rows = tldSOL::byAllCustomerID($x);
                $TITLE = "Sales Order Lines for buyer and user customer#$x";
                break;
            case 'bySOLAcknowledged':
            case 'bySOLEligibleToAcknowledgment':
            case 'bySOLDeliquent':
                // Check permissions
                if (!$user->isInGroup(['gg_ADMIN', 'role_CEO', 'role_EVP', 'role_SA'])) {
                    $DEFAULT_ERROR[] = 'ERROR: you do not have permission for this report';
                    break;
                }

                $xItems['asm_fullname'] = 'ASM';
                $sso = $y = TldDatabase::escape($y);

                if (null !== $since) {
                    $since = $x = TldDatabase::escape($since);
                } elseif (!empty($x)) {
                    // used for XLS
                    $since = TldDatabase::escape($x);
                }

                if (empty($since)) {
                    return [];
                }

                if (null !== $until) {
                    $until = $z = TldDatabase::escape($until);
                } elseif (!empty($z)) {
                    // used for XLS
                    $until = TldDatabase::escape($z);
                }
                switch ($m[2]) {
                    case 'bySOLAcknowledged':
                        $rows = tldSOL::bySalesOrderAcknowledgementBySSO($sso, $since, !empty($until) ? $until : null);
                        $TITLE = "Sales Order Lines acknowledged for SSO $sso";
                        break;
                    case 'bySOLEligibleToAcknowledgment':
                        $rows = tldSOL::bySalesOrderEligibleToAcknowledgementBySSO($sso, $since, !empty($until) ? $until : null);
                        $TITLE = "Sales Order Lines eligible to acknowledgment for SSO $sso";
                        break;
                    case 'bySOLDeliquent':
                        $acknowledgedSOLs = array_column(tldSOL::bySalesOrderAcknowledgementBySSO($sso, $since, !empty($until) ? $until : null), 'id');
                        $rows = tldSOL::bySalesOrderEligibleToAcknowledgementBySSO($sso, $since, !empty($until) ? $until : null);
                        $rows = array_filter($rows, static function (array $row) use ($acknowledgedSOLs) {
                            return !in_array($row['id'], $acknowledgedSOLs, true);
                        });
                        $TITLE = "Sales Order Lines not acknowledged for SSO $sso";
                        break;
                    default:
                        $rows = [];
                        $TITLE = '';

                }
                break;
        }
        if (count($rows ?? [])) {
            $DEFAULT_MENU .= <<<EOF
            <br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
            <a href="$php_self?m[0]={$m[0]}&m[1]={$m[1]}&m[2]={$m[2]}&m[3]=xls&x=$x&y=$y&z=$z">XLS version</a>
            | <a href="$php_self?m[0]={$m[0]}&m[1]={$m[1]}&m[2]={$m[2]}&m[3]=xls_options&x=$x&y=$y&z=$z">XLS one options by lines</a>
EOF;
            // Saving list in session
            $sess['sol']['list'] = $rows;

            switch ($m[3]) {
                case 'xls':
                case 'xls_options':
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
                default:
                    if ($m[2] === 'byERPModelByASM') {
                        $xItems = $xItems + ['asm_fullname' => 'ASM'];
                    }
                    $report = new tldReportColumnar(
                        $rows,
                        [
                            'xItems' => $xItems,
                            'title' => $TITLE,
                            'links' => [
                                'parent_id' => "$php_self?m[0]=sor&m[1]=view&id=",
                                'id' => "$php_self?m[0]=sol&m[1]=view&id="],
                        ]
                    );
                    $body .= $report->fetch();
                    break;
            }
        } else {
            $DEFAULT_ERROR[] = 'ERROR: No SOLs found...';
        }
        break;
    default:
        $body .= $smarty->fetch("$PATH/sol/homepage.sol.tpl");
        $form = new tldMatrix(
            tldSOL::countByERPStatus(),
            'location', 'status', 'num',
            "$php_self?m[0]=sol&m[1]=listing&m[2]=byERPStatus",
            'SOL Count by Status, Factory',
            [
                'yItems' => [
                    'PENDING', 'CREATE_PO', 'EVP_APPROVAL', 'PRINT_SO_ACK', 'CREATE_SO_ACK',
                    'PRINT_PO', 'CREATE_FACTORY_SO', 'PRINT_FACTORY_SO_ACK',
                    'ENGINEER_REVIEW', 'ENGINEERING_APPROVAL', 'MATERIALS_PLANNING',
                    'PSM_APPROVAL', 'IN_PROGRESS', 'SHIPPED', 'CLOSED',
                ],
            ]
        );
        $body .= $form->fetch();
        $report = new tldReportColumnar(
            tldSOL::byLatest(),
            [
                'xItems' => [
                    'parent_id' => 'SOR ID#',
                    'id' => 'SOR Line ID#',
                    'status' => 'Status',
                    'user_customer_display' => 'Customer Name (END USER)',
                    'buyer_customer_display' => 'Customer Name (BUYER)',
                    'bu_fullname' => 'Factory',
                    'sls_orno' => 'PO#',
                    'erp_orno' => 'Factory SO#',
                    'model' => 'Model',
                    'qty_sou' => 'Qty Ordered',
                    'conf_sls' => 'Delivery Penalty<br>Accepted by SSO',
                    'conf_erp' => 'Delivery Penalty<br>Accepted by Factory',
                ],
                'title' => 'Latest',
                'links' => [
                    'parent_id' => "$php_self?m[0]=sor&m[1]=view&id=",
                    'id' => "$php_self?m[0]=sol&m[1]=view&id=",
                ],
            ]
        );
        $body .= $report->fetch();
}

function getGeneralTab($header, $extraContent = '')
{
    global $sol;
    if ($header['conf_lc'] === 'Y') {
        $GLOBALS['DEFAULT_ERROR'][] = 'WARNING: Letter of Credit is required for this order...';
    }
    if ($header['del_pen'] === 'Y') {
        $GLOBALS['DEFAULT_ERROR'][] = 'WARNING: There are DELIVERY PENALTIES with this order...';
    }
    if ($header['del_pen'] === 'Y' && $header['conf_sls'] === 'N') {
        $GLOBALS['DEFAULT_ERROR'][] = 'WARNING: SALES ORG does NOT agree to DELIVERY PENALTIES...';
    }
    if ($header['del_pen'] === 'Y' && $header['conf_erp'] === 'N') {
        $GLOBALS['DEFAULT_ERROR'][] = 'WARNING: FACTORY does NOT agree to DELIVERY PENALTIES...';
    }
    if ($header['parts_inc'] === 'Y') {
        $GLOBALS['DEFAULT_ERROR'][] = 'WARNING: There are parts that should be shipped with this order...';
    }
    if ($header['wrty_spec'] != '' && $header['conf_wrty_erp'] === 'N') {
        $GLOBALS['DEFAULT_ERROR'][] = 'WARNING: Special warranty conditions have NOT been accepted by FACTORY...';
    }
    if ($header['sso_erp'] === '0'){
        $GLOBALS['DEFAULT_ERROR'][] = 'WARNING: The SSO ERP parameter is not filled in, you could share sensitive data with other SSO ERP. Please check with IT';
    }
    $header['certificate_of_origin_required'] = $header['certificate_of_origin_required'] === '1' ? 'Yes' : 'No';

    if (!$sol->isfullyAllocated()) {
        $GLOBALS['DEFAULT_ERROR'][] = 'WARNING: not enough units have been assigned to this Sales Order Record line yet...';
    }

    $report = new tldAssocTable(
        $header,
        [
            'parent_id' => 'SOR#',
            'id' => 'SOL#',
            'sfr_id' => 'SFR#',
            'user_customer_display' => 'Customer Name (END USER)',
            'buyer_customer_display' => 'Customer Name (BUYER)',
            'dt_opened' => 'Date Opened',
            'dt_closed' => 'Date Closed',
            'dzk_sso' => 'Date of Zero Backlog, SSO',
            'dzk_erp' => 'Date of Zero Backlog, Factory',
            'sls_orno' => 'SSO PO#',
            'erp_orno' => 'Factory SO#',
            'spacer1' => '---spacer---',
            'status' => 'Status',
            'bu_fullname' => 'Factory BU',
            'model' => 'Model',
            'eng_tier' => 'Emission Rating',
            'qty_sou' => 'Quantity Ordered',
            'qty_alloc' => 'Quantity Assigned',
            'qty_ship' => 'Quantity Shipped',
            'batch_quantity' => 'Batch Quantity',
            'spacer2' => '---spacer---',
            'inco' => 'Inco Terms',
            'inco_loc' => 'Inco Location',
            'ctry' => 'Country',
            'spacer3' => '---spacer---',
            'intro_new' => 'New Introduction ?',
            'conf_cis' => 'Customer inspection<br>before shipment?',
            'parts_inc' => 'Ship with Spare Parts?',
            'factory_shipping_fullname' => 'Factory Shipping Parts',
            'docs_inc' => 'Special Documentary Requirements',
            'notes' => 'Notes',
            'delivery_address' => 'Delivery Address',
            'export_licence_status' => 'Export Licence',
            'certificate_of_origin_required' => 'Certificate of origin required?'
        ],
        [
            'title' => 'General',
            'links' => [
                'parent_id' => "$php_self?m[0]=sor&m[1]=view&id=",
                'sfr_id' => ['url' => tldModLink::getURL('SFR2', $header['sfr_id'])],
            ]
        ]
    );
    $cells[] = $report->fetch();

    $cell = '';
    if (!empty($header['erp_orno'])) {
        $cell .= 'Factory Sales Order Links<br>';
        $ornos = explode(',', $header['erp_orno']);
        foreach ($ornos as $orno) {
            $cell .= <<<EOF
<a href="/en/private/finance/finance.php?m[0]=so&m[1]=view&erp={$header['bu_erp']}&id=$orno">SO#$orno</a><br>
EOF;
        }
    }

    if (!empty($header['sls_orno'])) {
        $cell .= 'SSO Purchase Order Links<br>';
        $pos = explode(',', $header['sls_orno']);
        foreach ($pos as $po) {
            $cell .= <<<EOF
<a href="/en/private/finance/finance.php?m[0]=po&m[1]=view&erp={$header['sso_erp']}&id=$po">PO#$po</a><br>
EOF;
        }
    }

    $cell .= $extraContent;

    $cells[] = $cell;

    $report = new tldHTMLTable(
        $cells,
        [
            'cols' => 2,
            'attribs' => [
                'table' => " width='100%'",
                'tr' => " bgcolor='#FFFFFF'",
            ],
        ]
    );
    $body .= $report->fetch();

    return $body;
}

/**
 * @param tldSOL $sol
 */
function getSummaryTab($sol, $smarty, $PATH, $dcur, $profile = 'factory')
{
    $id = $sol->getID();
    $smarty->assign('id', $id);
    // Fields & templates following the profile
    switch ($profile) {
        case 'sso':
            $xItems = [
                'qty' => 'Quantity',
                'pris_unit' => 'Unit Gross Selling Price',
                'pris_xtot' => 'Total Gross Selling Price',
                'spacer1' => '---spacer---',
                'prin_unit' => 'Unit Net Selling Price',
                'prin_xtot' => 'Total Net Selling Price',
                'spacer2' => '---spacer---',
                'discc' => 'Customer Discount',
                'discc_pc' => 'Customer Discount %',
                'discf' => 'Factory Discount',
                'discf_pc' => 'Factory Discount %',
                'spacer3' => '---spacer---',
                'cost_unit' => 'Unit Cost',
                'cost_xtot' => 'Total Cost',
                'spacer4' => '---spacer---',
                'dp' => 'Down Payment',
                'spacer5' => '---spacer---',
                'marg_unit' => 'Unit margin',
                'marg_unit_pc' => 'Unit Margin %',
                'marg_xtot' => 'Total margin',
            ];
            break;
        case 'factory':
        default:
            $template = 'view.sol.options.int.factory.tpl';
            $xItems = [
                'qty' => 'Quantity',
                'spacer2' => '---spacer---',
                'discf' => 'Factory Discount',
                'discf_pc' => 'Factory Discount %',
            ];
            break;
    }
    // General summary
    $report = new tldAssocTable(
        $sol->getSummary(['dcur' => $dcur]),
        $xItems,
        ['title' => "Summary ($dcur)"]
    );
    $body = $report->fetch();
    // Breakdown details
    $body .= getBreakdownTab($sol, $smarty, $PATH, $dcur, $profile);
    // Return results
    return $body;
}

function getBreakdownTab($sol, $smarty, $PATH, $dcur, $profile = 'factory')
{
    $id = $sol->getID();
    $smarty->assign('id', $id);
    // Fields & templates following the profile
    switch ($profile) {
        case 'sso':
            $template = 'view.sol.options.int.sso.tpl';
            break;
        case 'factory':
        default:
            $template = 'view.sol.options.int.factory.tpl';
            break;
    }
    $smarty->assign('dcur', $dcur);
    // --- Internal Items
    $intCaty = tldSOL::getInternalCategoriesList();
    $intLines = tldSOROpts::byParent($id, ['include' => $intCaty, 'dcur' => $dcur]);
    // @TODO there is room to improve because most of the time the totals have already been calculated in getSummary
    $intTotals = $intLines ? tldSOROpts::totalsByParent($id, ['include' => $intCaty, 'dcur' => $dcur]) : [];
    $smarty->assign('title', 'Internal Items');
    $smarty->assign('totals', $intTotals);
    $smarty->assign('lines', $intLines);
    $body = $smarty->fetch("$PATH/sol/$template");
    // --- External Items
    if ($profile === 'sso') {
        $extCaty = tldSOL::getExternalCategoriesList();
        $extLines = tldSOROpts::byParent($id, ['include' => $extCaty, 'dcur' => $dcur]);
        // @TODO there is room to improve because most of the time the totals have already been calculated in getSummary
        $extTotals = $extLines ? tldSOROpts::totalsByParent($id, ['include' => $extCaty, 'dcur' => $dcur]) : [];
        $smarty->assign('title', 'External Items');
        $smarty->assign('totals', $extTotals);
        $smarty->assign('lines', $extLines);
        $body .= $smarty->fetch("$PATH/sol/view.sol.options.ext.tpl");
    }
    // Return results
    return $body;
}

/**
 * @param tldSOL $sol
 */
function getPaymentTab($sol)
{
    $report = new tldAssocTable(
        $sol->itsHeader,
        [
            'tpay' => 'Payment Terms',
            'conf_cxo' => 'Payment terms 100% after shipment or Has the downpayment been received (if any) or Has the LC been opened (if any)',
            'conf_lc' => 'Letter of Credit Required?',
            'cu_ocur' => 'Customer Order Currency',
            'dp_amt' => 'Down Payment Amount',
            'dp_pc' => 'Down Payment %',
            'receivedp_amt' => 'Received Down Payment Amount',
            'receive_lc' => 'Letter of Credit Received?',
        ],
        ['title' => 'Payment Conditions']
    );
    $body = $report->fetch();
    $report = new tldAssocTable(
        $sol->itsHeader,
        [
            'wrty_spec' => 'Special Warranty Conditions',
            'conf_wrty_erp' => 'Warranty Conditions accepted by factory?',
        ],
        ['title' => 'Warranty Conditions']
    );
    $body .= $report->fetch();
    $body .= $sol->getForexRatesReport();
    return $body;
}

function addFormRuleFactoryShipping (HTML_QuickForm $form)
{
    $form->registerRule('factory_shipping', 'callback', function ($value) use ($form) {
        return $form->getElementValue('parts_inc')[0] === 'Y';
    });
    $form->addRule('factory_shipping', 'Value of "Ship with Spare Parts?" must be "Y" to select a factory shipping)', 'factory_shipping');
}