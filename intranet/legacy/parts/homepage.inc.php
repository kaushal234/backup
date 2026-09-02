<?php
include_once('sales_service.inc.php');

$email = strtolower($user->getEmail());
$domain_list = [
    'tld-america.com' => 300,    // TLD AME
    'tld-europe.com' => 540,    // TLD EUR
    'tld-meai.com' => 540,    // TLD MEAI
    'tld-asia.com' => 640,  // TLD SHA
    'aerospecialties.com' => 250 //Aerospecialties
];

/////---- SEARCH
$form = new HTML_QuickForm('frmSOByNum', 'get', '', '', '', true);
$form->addElement('hidden', 'm[0]', '');
$form->addElement('hidden', 'm[1]', 'bySearch');
$form->addElement('header', 'title', "<div style='background: lightcoral; padding: 0 5px 0 5px;'><div>'Sales Order Search (BAAN)'</div><div style='font-style: italic;font-size: 10px'>in Infor LN 'Sales order intake workbench'</div></div>");
$form->addElement('select', 'erp', 'Company', tldLocation::getERPList('smartyOptions'));
$form->addElement('text', 'orno', 'SO#', ['size' => 12]);
$cotpList = tldSO::getTypeListByERP($domain_list[$user->getDomain()]);
$form->addElement('select', 'cotp', 'Order type', ['' => ''] + tldUtils::optionsByKeyValue($cotpList, 't_cotp', 't_cotp'));
$form->addElement('text', 'cuno', 'Cust#', ['size' => 6]);
$form->addElement('text', 'nama', 'Cust Name', ['size' => 12]);
$form->addElement('text', 'eono', 'Cust PO#', ['size' => 12]);
$form->addElement('text', 'refa', 'Ref A', ['size' => 12]);
$form->addElement('text', 'refb', 'Ref B', ['size' => 12]);
$form->addElement('text', 'dino', 'Pack Slip#', ['size' => 12]);
$form->addElement('text', 'invn', 'Inv# (inc. type)', ['size' => 12]);
$form->addElement('text', 'item', 'Part#', ['size' => 12]);
$form->addElement('submit', 'btnSubmit', 'Submit');
$form->addElement('reset', 'btnClear', 'Clear');
$form->setDefaults(['erp' => $DEFAULT_ERP]);
$cell .= $form->toHTML();
////////-----END SEARCH



/////----PMOC BY SN
$form = new HTML_QuickForm('frmPMOC', 'post', $php_self, '', '', true);
$form->addElement('hidden', 'm[0]', 'cart');
$form->addElement('hidden', 'm[1]', 'import');
$form->addElement('hidden', 'm[2]', 'bySN_1');
$form->addElement('header', 'title', "<div style='background: lightcoral; padding: 0 5px 0 5px;'><div>PMOC to Cart by SN (BAAN)</div><div style='font-style: italic;font-size: 10px'>'Birst BI-16'</div></div>");
$form->addElement('text', 'sn', 'ER SN#', ['size' => 10]);
$form->addElement('checkbox', 'codes[p]', 'P items', 'select');
$form->addElement('checkbox', 'codes[m]', 'M items', 'select');
$form->addElement('checkbox', 'codes[o]', 'O items', 'select');
$form->addElement('checkbox', 'codes[c]', 'C items', 'select');
$form->addElement('submit', 'btnSubmit', 'Submit');
$cell .= $form->toHTML();
//////-----END PMOC BY SN
$cells[] = $cell;

////---SPH PURCHASES
$form = new HTML_QuickForm('frmInvByNum', 'post', '/en/private/manufacturing/pur/dev.php', '', '', true);
$form->addElement('hidden', 'm[0]', 'po');
$form->addElement('hidden', 'm[1]', 'view');
$form->addElement('hidden', 'm[2]', 'shipped');
$form->addElement('header', 'title', "<div style='background: lightcoral; padding: 0 5px 0 5px;'><div>Purchase Orders Number (BAAN)</div><div style='font-style: italic;font-size: 10px'>In Infor LN 'PO intake workbench' </div></div>");
$form->addElement('select', 'erp', 'Company', tldLocation::getERPList('smartyOptions'));
$form->addElement('text', 'id', 'PO#', ['size' => 12]);
$form->addElement('submit', 'btnSubmit', 'Submit');
$form->setDefaults(['erp' => $DEFAULT_ERP]);
$cell .= '</br>';
$cell = $form->toHTML();
////---END SPH PURCHASES

/////----ARCHIVE SEARCH
$doctypes = [
    'SALES ORDER ACK', 'SALES INVOICE',
    'FINANCE INVOICE', 'SALES QUOTATION',
    'PURCHASE ORDER', 'PACKING SLIP',
];
$form = new HTML_QuickForm('frmArchive', 'get', '/en/private/finance/finance.php', '', '', true);
$form->addElement('hidden', 'm[0]', 'archive');
$form->addElement('header', 'title', "<div style='background: lightcoral; padding: 0 5px 0 5px;'><div>Document Archive (BAAN)</div><div style='font-style: italic;font-size: 10px'>In Infor LN 'PO intake workbench' </div></div>");
$form->addElement('select', 'erp', 'Company', $erps);
$form->addElement('select', 'doctype', 'Doc type',
    array_combine($doctypes, $doctypes)
);
$form->addElement('text', 'id', 'Doc#', ['size' => 12]);
$form->addElement('submit', 'btnSubmit', 'Submit');
$form->setDefaults(['erp' => $DEFAULT_ERP]);
$cell .= $form->toHTML() . ' NB. For invoices inc type e.g. SLU22600308';

/////----END ARCHIVE SEARCH

$cells[] = $cell;


/////----INVENTORY
$form = new HTML_QuickForm('frmInventory', 'get', '', '', '', true);
$form->addElement('hidden', 'm[0]', 'inv');
$form->addElement('hidden', 'm[1]', 'view');
$form->addElement('header', 'title', 'Inventory/PN Information');
$form->addElement('text', 'id', 'Part Number', ['size' => 12]);
$form->addElement('submit', 'btnSubmit', 'Submit');
$cell = $form->toHTML();
//////-----END INVENTORY
/////----SPR
$form = new HTML_QuickForm('frmSPR', 'get', '', '', '', true);
$form->addElement('hidden', 'm[0]', 'spr');
$form->addElement('hidden', 'm[1]', 'view');
$form->addElement('header', 'title', 'Spare Parts Request');
$form->addElement('text', 'id', 'Spare Parts Request#', ['size' => 10]);
$form->addElement('submit', 'btnSubmit', 'Submit');
$cell .= $form->toHTML();
//////-----END SPR
/////----SB
$form = new HTML_QuickForm('frmByNum', 'post', '/en/private/product_support/index.ps.php', '', '', true);
$form->addElement('hidden', 'm[0]', 'sb');
$form->addElement('hidden', 'm[1]', 'forms');
$form->addElement('hidden', 'm[2]', 'byNum');
$form->addElement('header', 'title', 'Service Bulletin');
$form->addElement('text', 'id', 'Service Bulletin#', ['size' => 10]);
$form->addElement('submit', 'btnSubmit', 'Submit');
$cell .= $form->toHTML();
//////-----END SB
/////----WC
$form = new HTML_QuickForm('frmSB', 'get', '/en/private/product_support/index.ps.php', '', '', true);
$form->addElement('hidden', 'm[0]', 'wc');
$form->addElement('hidden', 'm[1]', 'view');
$form->addElement('header', 'title', 'Warranty Claim');
$form->addElement('text', 'id', 'Warranty Claim#', ['size' => 10]);
$form->addElement('submit', 'btnSubmit', 'Submit');
$cell .= $form->toHTML();
//////-----END WC

// Get MISC links
$cell .= <<<EOF
<h3>Search</h3>
<ul>
	<li>
	<a href="/en/private/product_support/index.ps.php?m[0]=publications&m[1]=documents&m[2]=search&m[3]=byPartNumber">
	Search Parts Diagrams for a Part Number...</a>
	</li>
	<li>
	<a href="/en/private/product_support/index.ps.php?m[0]=publications&m[1]=documents&m[2]=search&m[3]=byPartNumberER">
	Search ER and its Parts Diagrams for a Part Number...</a>
	</li>
</ul>
<h3>Browse</h3>
<ul>
	<li>
	<a href="/en/private/product_support/index.ps.php?m[0]=publications&m[1]=manuals&m[2]=search&m[3]=byBrandModel">
	Browse Manuals by Brand and Model...</a>
	</li>
	<li>
	<a href="/en/private/product_support/index.ps.php?m[0]=equipment&m[1]=forms&m[2]=byCustomer">
	Browse Equipment Records by Customer...</a>
	</li>
</ul>
EOF;
$cells[] = $cell;

////--EST GT

$report = new tldMatrix(
    tldSOL::countByFactoryEstimatedGTPeriod(),
    'period', 'man_location', 'GTCount',
    "/en/private/sales_service/sales.php?m[0]=sol&m[1]=listing&m[2]=byFactoryEstimatedGTPeriod&m[3]=detailsByOptionType&option=SPARE PARTS&z=",
    'SOL Units with Spare Parts',
    [
        'doNotShowTotals' => true,
        'xItems' => [
            '1 Week',
            '2 Weeks',
            '1 Month',
            'ALL'
        ],
    ]
);
$cell = $report->fetch();
$report = new tldMatrix(
    tldSPR::countBySPHStatus(),
    'status', 'sph_fullname', 'num',
    "$php_self?m[0]=spr&m[1]=listing&m[2]=bySPHStatus",
    'SPR Count by Status, SPH',
    [
        'xItems' => [
            'OPEN',
            'SHIPPED',
            'CLOSED',
        ],
    ]
);
$cell .= $report->fetch();
$cells[] = $cell;
////--END OF EST GT


switch ($m[1]) {
    case 'WCOpen':
        $date15DaysAgo = (new DateTime('15 days ago'))->format('Y-m-d');
        $rows = tldSPR::countByWcRequest($date15DaysAgo);
        $data = [];
        foreach ($rows as $row) {
            $data[] = ['man_location' => $row['man_location'], 'status' => 'today', 'num' => $row['today']];
            $data[] = ['man_location' => $row['man_location'], 'status' => 'late', 'num' => $row['late']];
        }
        $report = new tldMatrix(
            $data,
            'status', 'man_location', 'num',
            "$php_self?m[0]=spr&m[1]=listing&m[2]=byWCstatus",
            'OPEN WC Requests',
            [
                'xItems' => [
                    'today',
                    'late',
                ],
            ]
        );
        $body .= $report->fetch();
        break;
    case 'bySearch':
        $DEFAULT_ERP = $erp;
        $sess['parts']['default_erp'] = $erp;
        $TITLE .= "Sales Order List, Company $erp";
        if (!empty($orno)) {
            $w[] = " sors.t_orno=$orno";
            $TITLE .= " SO#$orno";
        }
        if (!empty($cuno)) {
            $w[] = " sors.t_cuno='$cuno' ";
            $TITLE .= " Cust#$cuno";
        }
        if (!empty($cotp)) {
            $w[] = " sors.t_cotp='$cotp' ";
            $TITLE .= " Type $cotp";
        }
        if (!empty($nama)) {
            $w[] = " cus.t_nama like '%$nama%' ";
            $TITLE .= " Name $nama";
        }
        if (!empty($eono)) {
            $w[] = " sors.t_eono LIKE '%$eono' ";
            $TITLE .= " Cust PO#:$eono";
        }
        if (!empty($refa)) {
            $w[] = " LOWER(sors.t_refa)=LOWER('$refa')";
            $TITLE .= " RefA:$refa";
        }
        if (!empty($refb)) {
            $w[] = " LOWER(sors.t_refb)=LOWER('$refb')";
            $TITLE .= " RefB:$refb";
        }
        if (!empty($dino)) {
            $w[] = " sols.t_dino=$dino";
            $TITLE .= " Packing Slip#$dino";
        }
        if (!empty($item)) {
            $w[] = " sols.t_item='$item'";
            $TITLE .= " Part Number#$item";
        }
        if (!empty($invn)) {
            $w[] = " sols.t_ttyp='" . substr($invn, 0, 3) . "' AND  sols.t_invn=" . substr($invn, 3);
            $TITLE .= " Invoice#$invn";
        }
        $dataRows = _getLastPartsDataByConstraints($w, ' t_odat DESC,');
        // get the last 12 month
        $date = new DateTime();
        $thisYear = $date->format('Y');
        $dateYM = [];
        for ($i = 0; $i < 12; $i++) {
            $date->modify('-1 month');
            $year = $date->format('Y');
            $month = $date->format('n');

            //the last 12 month can be on 2 years so we associate the month to its year
            $dateYM[$year][] = 't_aupp_' . $month;
            //get the names of args

            $monthsArg[] ='t_aupp_'.($i+1);
        }
        $year = [];
        //get item statistical usage for each last 3 years
        for($i=0; $i<4; $i++){
            $year[] = $thisYear-$i;
        }
        $arg= implode(' , ',$monthsArg);
        $yearsList = "'".implode("','",$year)."'";
        $query2 = <<<SQL
        SELECT RTRIM(t_item) AS t_item,
        $arg,
        RTRIM(t_year) AS date FROM ttdinv750{$erp}
        WHERE t_year IN ($yearsList)
        AND t_item = '$item'
        order by t_year ASC;
SQL;

        $rows = tldUtils::getSqlToAssocArray($query2, "odbc", array("src"=>"baan"));

        $query3 = <<<SQL
select itm.t_cups as 'um' from ttiitm001{$erp} as itm where itm.t_item = '$item';
SQL;

        $um = tldUtils::getSqlRowToAssocArray($query3, "odbc", array("src"=>"baan"));
        $usage['past12months']['t_item'] = $item;
        $usage['past12months']['date'] = "past 12 months";
        $usage['past12months']['t_uscu'] = 0;
        foreach ($rows as $row) {
            //get the line for the last 12months
            if($dateYM[$row['date']]){
                //get the month in the last 12 months and calculate the sum
                foreach ($dateYM[$row['date']] as $month){
                    if($row[$month]) {
                        $usage['past12months']['t_uscu'] += $row[$month];
                    }
                }
            }
        }

        foreach($rows as $index => &$row){
            $usage[$index]['t_item'] = $row['t_item'];
            unset($row['t_item']);
            $usage[$index]['date'] = $row['date'];
            unset($row['date']);
            $usage[$index]['t_uscu'] = array_sum($row);
        }

        $reportUsage = new tldMatrix(
            $usage,
            "date", "t_item", "t_uscu",
            "",
            "Statistical Usage for Company $erp PN# $item / UM: {$um["um"]}",
            [
                "doNotShowTotals"=>TRUE,
                "yItemsRawOrder"=>TRUE,
            ]
        );
        $body .= $reportUsage->fetch();
        $body .= _getLastPartsDataListingReport($dataRows, $TITLE);
        break;
    case 'myRecentlyShipped':
        $TITLE .= "Recently Shipped Lines, $email";
        $w[] = "lower(reps.t_info)='$email'";
        $w[] = "sors.t_cotp<>'SU1'";
        $w[] = 'sols.t_ssls=7';
        $SORT = ' sols.t_dino DESC,';
        $rows = _getLastPartsDataByConstraints($w, $SORT);
        $body = _getLastPartsDataListingReport($rows, $TITLE);
        break;
    case 'myOpenOrderLines':
        $TITLE .= "Open Sales Order Lines, $email";
        $subordinates = $user->getAllSubordinatesID($user->getUserid());
        $sales = array_merge(tldFunction::getUserlist('SA'), tldFunction::getUserlist('SAMP'));
        if ($subordinates && $user->isInGroup('role_SPM')) {
            $form = new HTML_QuickForm('frmReport', 'post');
            $form->addElement('hidden', 'm[0]', '');
            $form->addElement('hidden', 'm[1]', 'myOpenOrderLines');
            $form->addElement('header', 'title', 'Show open orders from team members:');

            foreach ($sales as $sale) {
                if (in_array($sale['id'], $subordinates)) {
                    $form->addElement('checkbox', $sale['id'], $sale['fullname']);
                    $userlist[$sale['id']] = $sale['email'];
                }

            }
            $form->addElement('checkbox', 'OTHERS', 'OTHERS');

            $form->addElement('submit', 'btnSubmit', 'Submit');
            if ($form->validate()) {
                unset($w);
                $a = [];
                $res = $form->exportValues();
                if ($res['OTHERS'] ?? null) {
                    $others = sprintf("LOWER(reps.t_info) NOT IN ('%s')", implode("','", array_keys($userlist)));
                }
                foreach ($userlist as $key => $val) {
                    if ($res[$key] && 'OTHERS' !== $res[$key]) {
                        $a[] = "'$val'";
                    }
                }
                if ($a) {
                    $inClause = 'LOWER(reps.t_info) IN ('.implode(',', $a).')';
                }
                $w[] = sprintf('(%s OR %s)', $inClause ?? '1=0', $others ?? '1=0');
            } else {
                $w[] = "lower(reps.t_info)='$email'";
            }
        } else {
            $w[] = "lower(reps.t_info)='$email'";
        }
        $w[] = "sors.t_cotp != 'SU1'";
        $w[] = 'sols.t_ssls != 7';
        $w[] = 'sols.t_oqua != sols.t_dqua';
        $body = $form->toHTML();
        $rows = _getLastPartsDataByConstraints($w, '', 500);
        $body .= _getLastPartsDataListingReport($rows, $TITLE, 500);
        break;
    default:
        $report = new tldHTMLTable(
            $cells,
            [
                'cols' => 4,
                'attribs' => [
                    'table' => "width='100%'",
                    'tr' => " bgcolor='#FFFFFF'",
                ],
            ]
        );
        $body = $report->fetch();
        break;
}

//save to session
$sess['parts']['solbyuser'] = $rows;


////----- Generic functions for the PARTS homepage

function _getLastPartsDataByConstraints($w, $sort = '', $limit = 200)
{
    global $email, $DEFAULT_ERP;
    if (!empty($w) && is_array($w)) {
        $WHERE = implode(' AND ', $w);
    } else {
        $WHERE = " lower(reps.t_info)='$email' ";
    }
    $SORT = TldDatabase::escape($sort);


    $query = 'SELECT ';
    $query .= 'TOP '.$limit.' ';
    $query .= 'sors.t_orno, sors.t_refa, sors.t_refb, sors.t_eono, ';
    $query .= 'sors.t_cuno, sors.t_crep, sors.t_cotp, sors.t_txta, sors.t_txtb, ';
    $query .= "CASE WHEN sors.t_bkyn = 1 THEN 'Y' ELSE 'N' END AS t_bkyn,";
    $query .= 'SUBSTRING(convert(varchar, sors.t_odat, 120), 0, 11) AS t_odat, ';
    $query .= 'sors.t_cpay,  ';
    $query .= "CASE WHEN sors.t_scom = 1 THEN 'Y' ELSE 'N' END AS t_scom, ";
    $query .= 'T2.t_amta, ';
    $query .= 'itms.t_oltm, ';
    $query .= 'cus.t_nama, ';
    $query .= 'reps.t_info, ';
    $query .= 'sols.t_pono, ';
    $query .= 'sols.t_item, ';
    $query .= 'sols.t_oqua, ';
    $query .= 'sols.t_dqua, ';
    $query .= 'sols.t_bqua, ';
    $query .= 'sols.t_dino, ';
    $query .= 'sols.t_ssls, ';
    $query .= 'sols.t_ttyp + CAST(sols.t_invn AS char) as t_invn, CAST(sols.t_invn AS char) as t_inv,';
    $query .= 'itms.t_dsca, ';
    $query .= 'itms.t_ordr, ';
    $query .= 'CONVERT(char(10), T2.t_ddta, 120) AS t_ddta, ';
    $query .= 'T2.t_cwar, ';
    $query .= 'sors.t_cdel, ';
    if ($DEFAULT_ERP == 540) {
        $query .= '(select sum(INV001.t_stoc) ';
        $query .= 'from ttdinv001' . $DEFAULT_ERP . ' INV001 ';
        $query .= ' where INV001.t_item  = sols.t_item ';
        $query .= "and INV001.t_cwar in ('SP1', 'DT2')) as t_stoc, ";
    } else {
        $query .= 'itms.t_stoc, ';
    }

    $query .= 'itms.t_allo ';
    $query .= 'FROM ';
    $query .= 'ttdsls040' . $DEFAULT_ERP . ' as sors ';
    $query .= 'LEFT JOIN ttdsls045' . $DEFAULT_ERP . ' as sols on sors.t_orno=sols.t_orno ';
    $query .= 'LEFT JOIN ttdsls041' . $DEFAULT_ERP . ' AS T2 ON sols.t_orno=T2.t_orno  AND sols.t_pono=T2.t_pono ';
    $query .= 'LEFT JOIN ttiitm001' . $DEFAULT_ERP . ' AS itms ON sols.t_item=itms.t_item ';
    $query .= 'LEFT JOIN ttccom010' . $DEFAULT_ERP . ' AS cus ON sors.t_cuno=cus.t_cuno ';
    $query .= 'LEFT JOIN ttccom001' . $DEFAULT_ERP . ' AS reps ON sors.t_crep=reps.t_emno ';
    $query .= 'WHERE ' . $WHERE . ' ';
    $query .= 'ORDER BY ' . $SORT . ' ';
    $query .= 'sors.t_orno DESC, sols.t_pono ';
    $rows = tldUtils::getSqlToAssocArray($query, 'odbc', ['src' => 'baan']);
    if ($DEFAULT_ERP == 680) {
        foreach ($rows as $key => $val) {
            $invn = $val['t_inv'];
            $query = <<<EOF
                                                SELECT
                                                        LEFT(trim(vat.vat_date),11) AS vat_date, RIGHT(trim(vat.vat_no),8) AS vat_no, (vat.amount+vat.tax) AS amount
                                                FROM vat
                                                WHERE
                                                        vat.invoice_no LIKE trim('$invn')               
EOF;
            $ret[] = tldUtils::getSqlToAssocArray($query);
            $t_ninv = $val['t_inv'];
            $t_orno = $val['t_orno'];
            $inv = new tldINV($t_ninv, $DEFAULT_ERP, ['so' => $t_orno]);
            $header = $inv->getHeader();
            if (count($ret ?? [])) {
                $rows[$key]['vat_date'] = $ret[$key][0]['vat_date'];
                $rows[$key]['vat_no'] = $ret[$key][0]['vat_no'];
                $rows[$key]['amount'] = $ret[$key][0]['amount'];
                $rows[$key]['status'] = $header['status'];
            }
        }
    }
    return $rows;
}

function _getLastPartsDataListingReport($rows, $title, $max = 200)
{
    global $php_self, $DEFAULT_ERP;
    $xitem = [
        't_odat' => 'Order Date',
        't_ddta' => 'Delivery Date',
        't_orno' => 'Order#',
        't_cotp' => 'Type',
        't_dino' => 'Pack#',
        't_invn' => 'Inv#',
        't_cuno' => 'Cust#',
        't_nama' => 'Cust Name',
        't_refa' => 'Ref A',
        't_refb' => 'Ref B',
        't_eono' => 'Cust PO#',
        't_pono' => '#',
        't_oqua' => 'Qty',
        't_dqua' => 'Del',
        't_bqua' => 'Back',
        't_item' => 'PN',
        't_dsca' => 'Description',
        't_ordr' => 'OO',
        't_stoc' => 'OH',
        't_allo' => 'AL',
        't_bkyn' => 'Blocked ?',
    ];
    if (strpos($title, 'Open Sales Order Lines') === 0) {
        $xitem = [
            't_odat' => 'Order Date',
            't_ddta' => 'Delivery Date',
            't_orno' => 'Order#',
            't_cotp' => 'Type',
            't_cuno' => 'Cust#',
            't_nama' => 'Cust Name',
            't_refa' => 'Ref A',
            't_refb' => 'Ref B',
            't_eono' => 'Cust PO#',
            't_oqua' => 'Qty',
            't_bqua' => 'Back',
            't_item' => 'PN',
            't_dsca' => 'Description',
            't_ordr' => 'OO',
            't_stoc' => 'OH',
            't_allo' => 'AL',
            't_amta' => 'Net Amount',
            't_scom' => 'Ship',
            't_cpay' => 'Payment terms',
            't_crep' => 'Sales',
            't_oltm' => 'Lead-time',
            't_cwar' => 'Warehouse',
            't_cdel' => 'Delivery Address Code',
        ];
    }
    if ($DEFAULT_ERP == 680) {
        $xitem = [
            't_odat' => 'Order Date',
            't_ddta' => 'Delivery Date',
            't_orno' => 'Order#',
            't_cotp' => 'Type',
            't_dino' => 'Pack#',
            't_invn' => 'Inv#',
            't_info' => 'Sale REP',
            't_cuno' => 'Cust#',
            't_nama' => 'Cust Name',
            't_refa' => 'Ref A',
            't_refb' => 'Ref B',
            't_eono' => 'Cust PO#',
            't_pono' => '#',
            't_oqua' => 'Qty',
            't_dqua' => 'Del',
            't_bqua' => 'Back',
            't_item' => 'PN',
            't_dsca' => 'Description',
            't_ordr' => 'OO',
            't_stoc' => 'OH',
            't_allo' => 'AL',
            'vat_no' => 'VAT#',
            'vat_date' => 'Invoice date',
            'amount' => 'Amount',
            'status' => 'Status',
            't_trdt' => 'Date',
        ];
    }
    $report = new tldReportColumnar(
        $rows,
        [
            'xItems' => $xitem,
            'title' => $title . ' max. '.$max,
            'links' => [
                't_cuno' => [
                    'url' => "$php_self?m[0]=cuno&m[1]=view",
                    'params' => [
                        'id' => 't_cuno',
                    ],
                ],
                't_orno' => [
                    'url' => "/en/private/finance/finance.php?m[0]=so&m[1]=view&erp=$DEFAULT_ERP",
                    'params' => [
                        'id' => 't_orno',
                    ],
                ],
                't_item' => [
                    'url' => "$php_self?m[0]=inv&m[1]=view",
                    'params' => [
                        'id' => 't_item',
                    ],
                ],
                't_dino' => [
                    'url' => "/en/private/finance/finance.php?m[0]=ps&m[1]=view&erp=$DEFAULT_ERP",
                    'params' => [
                        'id' => 't_dino',
                    ],
                ],
                't_invn' => [
                    'url' => "/en/private/finance/finance.php?m[0]=inv&m[1]=view&erp=$DEFAULT_ERP",
                    'params' => [
                        'id' => 't_invn',
                    ],
                ],
            ],
            'functions' => [
                'SOACK PDF' => [
                    'url' => "/en/private/finance/finance.php?m[0]=archive&erp=$DEFAULT_ERP&doctype=SALES ORDER ACK&id=",
                    'param' => 't_orno',
                ],
                'PACK PDF' => [
                    'url' => "/en/private/finance/finance.php?m[0]=archive&erp=$DEFAULT_ERP&doctype=PACKING%20SLIP&id=",
                    'param' => 't_dino',
                ],
                'INV PDF' => [
                    'url' => "/en/private/finance/finance.php?m[0]=archive&erp=$DEFAULT_ERP&doctype=SALES%20INVOICE&id=",
                    'param' => 't_invn',
                ],
                'Header' => [
                    'url' => '/en/private/erp/texts?ctxt[]=',
                    'param' => 't_txta',
                    'target' => '_blank',
                    'bypassDisplay' => static function ($params, $lineValues) {
                        return empty($lineValues['t_txta']);
                    },
                ],
                'Footer' => [
                    'url' => '/en/private/erp/texts?ctxt[]=',
                    'param' => 't_txtb',
                    'target' => '_blank',
                    'bypassDisplay' => static function ($params, $lineValues) {
                        return empty($lineValues['t_txtb']);
                    },
                ],
            ],
            'showItemNumbers' => true,
        ]
    );
    return $report->fetch();
}
