<?php
include_once 'sales_service.inc.php';
include_once 'product_support.inc.php';
include_once 'erp.inc.php';
include_once 'finance.inc.php';

// Get MOO ID
$moo_id = tldModule::getMOOIDByModule('sor');
if (!$user->isInGroup(['gg_ADMIN', 'role_ASM', 'role_SA', 'gg_SUPPORT', 'gg_ACCT', 'gg_TRANSPORT', 'role_EVP', 'gg_PARTS', 'role_CMO'])) {
    $DEFAULT_ERROR[] = 'ERROR: You do not have permission to access this module';
    return;
}

$DEFAULT_TITLE .= "\Sales Order Records";
$DEFAULT_MENU .= <<<EOF
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<a href="$php_self?m[0]=sor">Home</a>
&nbsp;|&nbsp;<a href="$php_self?m[0]=sor&m[1]=form&m[2]=byNum">By number</a>
&nbsp;|&nbsp;<a href="$php_self?m[0]=sor&m[1]=listing&m[2]=search">Search</a>
&nbsp;|&nbsp;<a href="$php_self?m[0]=sor&m[1]=form&m[2]=newSOR1">Submit SOR</a>
EOF;

switch ($m[1]) {
    case 'transfer':
        include 'transfer.inc.php';
        break;
    case 'view':
        include 'view.inc.php';
        break;
    default:
        $body .= '<h3>Sales Records Homepage</h3>';
        break;
}

function getGeneralTab($header)
{
    $id = $header['id'];
    if ($header['status'] === 'CLOSED') {
        $body = <<<EOF
<p class="alert">SOR was closed on ${header['dt_closed']}</p>
EOF;
    }
    $report = new tldAssocTable(
        $header,
        [
            'id' => 'SOR#',
            'location' => 'Sales BU',
            'juridical_entity' => 'Juridical entity',
            'asm_fullname' => 'Area Sales Manager',
            'status' => 'Status',
            'dt_entered' => 'Date',
            'eqno' => 'eQuote#',
            'orno' => 'SSO SO#',
        ],
        ['title' => "Sales Order Record #$id"]
    );
    $cell = $report->fetch();
    // SO link
    if (!empty($header['orno'])) {
        $cell .= '<br>Sales Order Links<br>';
        $ornos = explode(',', $header['orno']);
        foreach ($ornos as $orno) {
            $cell .= <<<EOF
<a href="/en/private/finance/finance.php?m[0]=so&m[1]=view&erp=${header['bu']}&id=$orno">SO#$orno</a><br>
EOF;
        }
    }
    $cells[] = $cell;
    // Customer info
    $report = new tldAssocTable(
        $header,
        [
            'user_customer_display' => 'Customer Name',
            'user_type_display' => 'User Type',
            'cu_new' => 'New customer?',
            'agnt_nama' => 'Sales Agent Name',
        ],
        ['title' => 'End User Details']
    );
    $cells[] = $report->fetch();
    $report = new tldAssocTable(
        $header,
        [
            'buyer_customer_display' => 'Buyer Name',
            'cu_orno' => 'Customer PO#',
            'buyer_type_display' => 'Buyer Type',
            't_cuno' => 'Customer ERP ID',
            'cu_new' => 'New customer?',
            'agnt_nama' => 'Sales Agent Name',
        ],
        ['title' => 'Buyer Details']
    );
    $cells[] = $report->fetch();
    // Display
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
    // SOL listing
    $report = new tldReportColumnar(
        tldSOL::byParent($id),
        [
            'xItems' => [
                'id' => 'Line ID#',
                'status' => 'Status',
                'sls_orno' => 'SSO PO# to Factory',
                'erp_orno' => 'Factory SO#',
                'model' => 'Model',
                'qty_sou' => 'Qty',
                'qty_alloc' => 'Qty Assigned',
                'qty_ship' => 'Qty Shipped',
            ],
            'title' => 'Line Items',
            'links' => ['id' => "$php_self?m[0]=sol&m[1]=view&id="],
        ]
    );
    $body .= $report->fetch();

    return $body;
}

function _getPrintSOL($id)
{
    global $smarty, $PATH;
    $sol = new tldSOL($id);
    $header = $sol->getHeader();
    // SOL Header
    $report = new tldAssocTable(
        $header,
        [
            'id' => 'SOL#',
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
            'spacer2' => '---spacer---',
            'inco' => 'Inco Terms',
            'inco_loc' => 'Inco Location',
            'ctry' => 'Country',
            'spacer3' => '---spacer---',
            'conf_cis' => 'Customer inspection<br>before shipment?',
            'parts_inc' => 'Ship with Spare Parts?',
            'docs_inc' => 'Special Documentary Requirements',
            'notes' => 'Notes',
        ],
        ['title' => 'General']
    );
    $cells[] = $report->fetch();
    // SOL Summary
    $report = new tldAssocTable(
        $sol->getSummary(),
        [
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
        ],
        ['title' => 'Summary (' . $sol->getDCUR() . ')']
    );
    $cells[] = $report->fetch();
    // SOL Payments Conditions
    $report = new tldAssocTable(
        $header,
        [
            'tpay' => 'Payment Terms',
            'conf_cxo' => 'Payment terms 100% after shipment or Has the downpayment been received (if any) or Has the LC been opened (if any)',
            'conf_lc' => 'Letter of Credit Required?',
            'cu_ocur' => 'Customer Order Currency',
            'dp_amt' => 'Down Payment Amount',
            'dp_pc' => 'Down Payment %',
        ],
        ['title' => 'Payment Conditions']
    );
    $cell = $report->fetch();
    $report = new tldReportColumnar(
        tldModList::byParent(
            $id,
            'SOL',
            ['list_name' => 'CURS']
        ),
        [
            'xItems' => ['list_key' => 'Currency', 'value' => 'Rate'],
            'title' => 'Default Currency is ' . $sol->getDCUR() . '<br>Forex Rates per USD',
        ]
    );
    $cell .= $report->fetch();
    $cells[] = $cell;
    $report = new tldHTMLTable(
        $cells,
        [
            'cols' => 3,
            'attribs' => ['table' => " width='100%'", 'tr' => " bgcolor='#FFFFFF'"],
            'title' => "SOL# $id",
        ]
    );
    $body = $report->fetch();

    $smarty->assign('dcur', $sol->getDCUR());

    $report = new tldReportColumnar(
        tldSORUnit::byParent($id),
        [
            'xItems' => [
                'short_desc' => 'Short Description',
                'long_desc' => 'Long Description',
                'del_dat' => 'Requested Delivery Date',
                'ddel_est1' => 'Factory Promised Delivery Date',
                'dgt_est' => 'Estimated GT Date',
                'batch_qty' => 'Batch quantity',
            ],
            'title' => 'SOR Units',
        ]
    );
    $body .= $report->fetch();

    $intCaty = tldSOL::getInternalCategoriesList();
    $intTotals = tldSOROpts::totalsByParent($id, ['include' => $intCaty]);

    $extCaty = tldSOL::getExternalCategoriesList();
    $extTotals = tldSOROpts::totalsByParent($id, ['include' => $extCaty]);

    $smarty->assign('title', 'Internal Transactions');
    $smarty->assign('totals', $intTotals);
    $smarty->assign('lines', tldSOROpts::byParent($id, ['include' => $intCaty]));
    $body .= $smarty->fetch("$PATH/sol/view.sol.options.int.sso.tpl");

    $smarty->assign('title', 'External Transactions');
    $smarty->assign('totals', $extTotals);
    $smarty->assign('lines', tldSOROpts::byParent($id, ['include' => $extCaty]));
    $body .= $smarty->fetch("$PATH/sol/view.sol.options.ext.tpl");

    return $body;
}
