<?php
include_once('sales_service.inc.php');
$DEFAULT_TITLE .= '/Customer Dashboard';

if (!$user->isInGroup(['gg_ADMIN', 'gg_PARTS', 'gg_ACCT'])) {
    $DEFAULT_ERROR[] = 'ERROR: You do not have permissions';
    return;
}

switch ($m[1]) {
    case 'view':
        if (empty($id)) {
            $DEFAULT_ERROR[] = 'ERROR: Customer number not set...';
            break;
        }
        $id = TldDatabase::escape($id);
        $cust = new tldERPCustomer($DEFAULT_ERP, $id);
        if ($cust->isEmpty()) {
            $DEFAULT_ERROR[] = 'ERROR: Customer not found...';
            break;
        }
        $DEFAULT_TITLE .= "/CUNO#$id";
        $DEFAULT_MENU .= <<<EOF
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<a href="$php_self?m[0]=cuno&m[1]=view&id=$id">Customer Dashboard</a>
&nbsp;|&nbsp;<a href="$php_self?m[0]=cuno&m[1]=view&m[2]=cdel&id=$id" title="Delivery Address List">Delivery</a>
&nbsp;|&nbsp;<a href="$php_self?m[0]=cuno&m[1]=view&m[2]=ccor&id=$id" title="Postal address list">Postal</a>
&nbsp;|&nbsp;<a href="$php_self?m[0]=cuno&m[1]=view&m[2]=inv&id=$id" title="Open Invoices">Invoices</a>
&nbsp;|&nbsp;<a href="/en/private/parts/cuno/help.pdf">Help</a>
EOF;

        switch ($m[2]) {
            case 'cdel':
                $rows = tldCDEL::byERPCUNO($DEFAULT_ERP, $id);
                $report = new tldReportColumnar(
                    $rows,
                    [
                        'xItems' => [
                            't_cdel' => 'CDEL',
                            't_nama' => 'Name',
                            't_namb' => 'Line 1',
                            't_namc' => 'Line 2',
                            't_namd' => 'Line 3',
                            't_name' => 'Line 4',
                            't_namf' => 'Line 5',
                            't_ccty' => 'Country',
                        ],
                        'title' => 'Delivery address',
                    ]
                );
                $body .= $report->fetch();
                break;
            case 'ccor':
                $rows = tldCCOR::byERPCUNO($DEFAULT_ERP, $id);
                $report = new tldReportColumnar(
                    $rows,
                    [
                        'xItems' => [
                            't_ccor' => 'CCOR',
                            't_nama' => 'Name',
                            't_namb' => 'Line 1',
                            't_namc' => 'Line 2',
                            't_namd' => 'Line 3',
                            't_name' => 'Line 4',
                            't_namf' => 'Line 5',
                            't_ccty' => 'Country',
                        ],
                        'title' => 'Postal address',
                    ]
                );
                $body .= $report->fetch();
                break;
            case 'inv':
                $report = new tldReportColumnar(
                    tldINV::byOpenByCuno($DEFAULT_ERP, $id, ['returnAll' => true]),
                    [
                        'xItems' => [
                            'ninv_fullname' => 'Inv#',
                            't_eono' => 'Customer PO#',
                            'status' => 'Status',
                            't_docd' => 'Inv Date',
                            't_dued' => 'Due Date',
                            'days_late' => 'Over due<br>(in days)',
                            't_balc' => 'Balance',
                            't_ccur' => 'Currency',
                        ],
                        'title' => 'Open Invoices',
                        'links' => [
                            'ninv_fullname' => "/en/private/finance/finance.php?m[0]=inv&m[1]=view&erp=$DEFAULT_ERP&id=",
                        ],
                        'showItemNumbers' => true,
                    ]
                );
                $body .= $report->fetch();
                break;
            default:
                // Customer ERP Header Info ----------------------------->
                $headers = $cust->getHeader();
                $form = new tldAssocTable(
                    $headers,
                    [
                        't_cuno' => 'ID#',
                        't_nama' => 'Name',
                        't_namb' => '',
                        't_namc' => '',
                        't_namd' => '',
                        't_name' => '',
                    ]
                );
                $cells[] = $form->fetch();
                // Customer status and Misc Info
                $content = '<h2>Customer Status: ' . $cust->getStatus() . '</h2>' .
                    '<h2>Terms of Payment: ' . $cust->getTermsOfPayment() . '</h2>' .
                    '<h2>Customer Credit Limit: ' . $cust->getCreditLimit() . '</h2>';;

                if (!empty($headers['t_txta'])) {
                    $content .= "<h2><a href='/en/private/erp/texts?ctxt[]=" . $headers['t_txta'] . "&title=baan.titles.customer_text' target='_blank'>Customer Text</a></h2>";
                }
                if (!empty($headers['t_txtb'])) {
                    $content .= "<h2><a href='/en/private/erp/texts?ctxt[]=" . $headers['t_txtb'] . "&title=baan.titles.customer_parts_text' target='_blank'>Customer Parts Text</a></h2>";
                }
                $cells[] = $content;

                // Invoice and Order balance statistics
                $cells[] = include('cuno.stats.matrix.tpl.php');
                // display 3 columns header info
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
                // List the other CRT Teams ----------------------------->
                $report = new tldReportColumnar(
                    tldCRT::byErpCuno($DEFAULT_ERP, $id),
                    [
                        'xItems' => [
                            'id' => 'CRT ID#',
                            'customer_id' => 'Customer ID',
                            'customer_name' => 'Customer Name',
                            'erp' => 'ERP#',
                            'cuno' => 'ERP Customer#',
                            'sales_rep' => 'Sales rep',
                            'parts_rep' => 'Parts rep',
                            'services_rep' => 'Services Rep',
                            'parts_location' => 'Parts location',
                            'services_location' => 'Services location',
                        ],
                        'title' => 'Related Customer Relationship Teams',
                        'links' => [
                            'id' => '/en/private/sales_service/sales.php?m[0]=crt&m[1]=view&id=',
                            'customer_id' => '/en/private/sales_service/sales.php?m[0]=customers&m[1]=view&id=',
                        ],
                        'functions' => [
                            'Send Email/PDF' => [
                                'url' => "/en/private/sales_service/sales.php?m[0]=customers&m[1]=view&m[2]=email&m[3]=compose&erp=$DEFAULT_ERP&cuno=$id&id=",
                                'param' => 'customer_id',
                            ],
                            'Download PDF Zip' => [
                                'url' => "/en/private/sales_service/sales.php?m[0]=customers&m[1]=view&m[2]=zip&m[3]=download&erp=$DEFAULT_ERP&cuno=$id&id=",
                                'param' => 'customer_id',
                            ],
                        ],
                    ]
                );
                $body .= $report->fetch();
                // List latest OPEN Order Lines ----------------------------->
                $rows = tldSO::byCUNO_OPENPO($DEFAULT_ERP, $id);
                $report = new tldReportColumnar(
                    $rows,
                    [
                        'xItems' => [
                            't_odat' => 'Order Date',
                            't_orno' => 'Order#',
                            't_refa' => 'Ref A',
                            't_refb' => 'Ref B',
                            't_eono' => 'Cust PO#',
                            't_pono' => '#',
                            't_oqua' => 'Qty',
                            't_dqua' => 'Del',
                            't_bqua' => 'Back',
                            't_ddta' => 'Pl.Del',
                            't_item' => 'PN',
                            't_dsca' => 'Description',
                            't_ordr' => 'OO',
                            't_stoc' => 'OH',
                            't_allo' => 'AL',
                            'purs_orno' => 'PO#',
                            'purs_pono' => '#',
                            'purs_ddtb' => 'Date',
                            'purs_oqua' => 'Qty',
                            'purs_dqua' => 'Del',
                            'purs_bqua' => 'Back',
                            'purs_suno' => 'Sup',
                        ],
                        'title' => 'Open Sales Order Lines by Customer with potential PO lines max. 200',
                        'links' => [
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
                            'purs_orno' => [
                                'url' => "/en/private/manufacturing/pur/dev.php?m[0]=po&m[1]=view&erp=$DEFAULT_ERP",
                                'params' => [
                                    'id' => 'purs_orno',
                                ],
                            ],
                            'purs_suno' => [
                                'url' => "/en/private/manufacturing/pur/dev.php?m[0]=vendors&m[1]=listing&m[2]=byERPSuno&erp=$DEFAULT_ERP",
                                'params' => [
                                    'suno' => 'purs_suno',
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
                                'url' => "https://www.tld-gse.com/en/private/finance/finance.php?m[0]=archive&erp=$DEFAULT_ERP&doctype=SALES ORDER ACK&id=",
                                'param' => 't_orno',
                            ],
                        ],
                    ]
                );
                $body .= $report->fetch();
                // Get latest shipped since 6 months ----------------------------->
                $a = 'DATEDIFF(month, sols.t_ddat, GETDATE())<=6';
                $rows = _getShippedLinesByConstraints($DEFAULT_ERP, $id, $a);
                $report = new tldReportColumnar(
                    $rows,
                    [
                        'xItems' => [
                            't_eono' => 'PO#',
                            't_orno' => 'SO#',
                            't_dino' => 'Packing slip',
                            't_invn' => 'Invoice #',
                            't_aitc' => 'Customer P/N',
                            't_item' => 'TLD P/N',
                            't_dsca' => 'Description',
                            't_dqua' => 'Qty Shipped',
                            'courier' => 'Courrier',
                            'trno' => 'Tracking',
                            't_ddat' => 'Shipped date',
                        ],
                        'links' => [
                            't_orno' => [
                                'url' => "/en/private/finance/finance.php?m[0]=so&m[1]=view&erp=$DEFAULT_ERP",
                                'params' => [
                                    'id' => 't_orno',
                                ],
                            ],
                            't_dino' => [
                                'url' => "/en/private/finance/finance.php?m[0]=archive&erp=$DEFAULT_ERP&doctype=PACKING+SLIP&id=",
                                'params' => [
                                    'id' => 't_dino',
                                ],
                            ],
                            't_invn' => [
                                'url' => "/en/private/finance/finance.php?m[0]=archive&erp=$DEFAULT_ERP&doctype=SALES+INVOICE&id=",
                                'params' => [
                                    'id' => 't_invn',
                                ],
                            ],
                            'trno' => [
                                'url' => 'https://www.tld-gse.com/shared/redirect_courier.php?',
                                'params' => [
                                    'courier' => 'courier',
                                    'trno' => 'trno',
                                ],
                            ],
                        ],
                        'title' => 'Recently Shipped lines, past 6 Months, Max 200',
                        'showItemNumbers' => true,
                    ]
                );
                $body .= $report->fetch();
        }
        break;
    case 'listing':
        switch ($m[2]) {
            case 'byCREP':
                if (empty($crep)) {
                    $DEFAULT_ERROR[] = 'ERROR: Rep email not set...';
                    break;
                }
                $email = strtolower($user->getEmail());
                $query = <<<EOF
select
    sors.t_orno, sors.t_odat, sors.t_cuno, cus.t_nama, sors.t_crep, reps.t_info,
    sols.t_dino, sols.t_invn
from ttdsls040$DEFAULT_ERP as sors
	join ttdsls045$DEFAULT_ERP as sols on sors.t_orno=sols.t_orno
 LEFT JOIN ttccom010$DEFAULT_ERP AS cus on sors.t_cuno=cus.t_cuno
 LEFT JOIN ttccom001$DEFAULT_ERP AS reps on sors.t_crep=reps.t_emno
     WHERE lower(sors.t_info)=$email
order by sors.t_odat DESC
EOF;
                break;
        }
        if (count($rows)) {
            $report = new tldReportColumnar(
                $rows,
                [
                    'xItems' => [
                        't_orno' => 'Order#',
                        't_odat' => 'Order Date',
                        't_nama' => 'Customer Name',
                        't_cuno' => 'Customer#',
                        't_crep' => 'Rep#',
                        't_info' => 'Rep Email',
                        't_dino' => 'Packing Slip#',
                        't_invn' => 'Invoice#',
                    ],
                    'title' => 'Sales Order List',
                    'links' => [
                        't_ninv' => [
                            'url' => "$php_self?m[0]=ps&m[1]=view",
                            'params' => [
                                't_ninv' => 't_ninv',
                                't_orno' => 't_orno',
                            ],
                        ],
                    ],
                ]
            );
            $body .= $report->fetch();
        } else {
            $DEFAULT_ERROR[] = 'ERROR: No Sales Orders found...';
        }
        break;
    default:
        $form = new HTML_QuickForm('frmCUNO', 'get');
        $form->addElement('hidden', 'm[0]', 'cuno');
        $form->addElement('hidden', 'm[1]', 'view');
        $form->addElement('header', 'title', 'View Customer Dashboard');
        $form->addElement('text', 'id', 'Customer Number', ['size' => 12]);
        $form->addElement('submit', 'btnSubmit', 'Submit');
        $body .= $form->toHTML();

        $form = new HTML_QuickForm('frmCUNOSearch', 'get');
        $form->addElement('hidden', 'm[0]', 'cuno');
        $form->addElement('hidden', 'm[1]', 'search');
        $form->addElement('header', 'title', 'Search By Customer Name');
        $form->addElement('text', 'name', 'Customer Name', ['size' => 12]);
        $form->addElement('submit', 'btnSubmit', 'Submit');
        $body .= $form->toHTML();


        if ($m[1] === 'search') {
            $rows = tldERPCustomer::byERPName($DEFAULT_ERP, $name);
            if (!$rows) {
                $DEFAULT_ERROR[] = "WARNING: No results found for $name...";
                break;
            }
            $report = new tldReportColumnar(
                $rows,
                [
                    'xItems' => [
                        't_cuno' => 'Customer ID#',
                        't_nama' => 'Customer Name',
                    ],
                    'title' => 'Customer List',
                    'links' => [
                        't_cuno' => "$php_self?m[0]=cuno&m[1]=view&id=",
                    ],
                ]
            );
            $body .= $report->fetch();
        }
}

/**
 * @param $ERP
 * @param $CUNO
 * @param array|string $a
 * @param array $opt
 * @return array
 */
function _getShippedLinesByConstraints($ERP, $CUNO, $a = '', $opt = [])
{
    $DATA = [];
    // Get shipping lines from BAAN
    if (is_array($a)) {
        $WHERE = tldUtils::constructWhere($a);
    } else {
        $WHERE = $a;
    }
    if (!empty($WHERE)) {
        $WHERE = "AND $WHERE";
    }
    if (!empty($opt['limit'])) {
        $SELECT = 'TOP ' . $opt['limit'];
    } else {
        $SELECT = 'TOP 200';
    }
    $query = <<<EOF
SELECT
    $SELECT
    sors.t_eono,
    sors.t_orno,
    sors.t_refa,
    sors.t_refb,
    sors.t_cuno,
    sors.t_crep,
    sors.t_cotp,
    SUBSTRING(convert(varchar, sors.t_odat, 120), 0, 11) AS t_odat,
    cus.t_nama,
    reps.t_info,
    sols.t_pono,
    sols.t_item,
    sols.t_oqua,
    sols.t_dqua,
    sols.t_bqua,
    sols.t_dino,
    sols.t_ssls,
    SUBSTRING(convert(varchar, sols.t_ddat, 120), 0, 11) AS t_ddat,
    sols.t_ttyp + CAST(sols.t_invn AS char) as t_invn,
    itms.t_dsca,
    itms.t_ordr,
    itms.t_stoc,
    itms.t_allo,
    citms.t_aitc
FROM
    ttdsls040$ERP as sors
    LEFT JOIN ttdsls045$ERP as sols on sors.t_orno=sols.t_orno
    LEFT JOIN ttiitm001$ERP AS itms ON sols.t_item=itms.t_item
    LEFT JOIN ttiitm012$ERP AS citms ON itms.t_item = citms.t_item 
    	AND sors.t_cuno = citms.t_cuno
    LEFT JOIN ttccom010$ERP AS cus ON sors.t_cuno=cus.t_cuno
    LEFT JOIN ttccom001$ERP AS reps on sors.t_crep=reps.t_emno
WHERE
	sors.t_cuno='$CUNO'
	AND sors.t_cotp<>'SU1'
    AND sols.t_ssls=7
    $WHERE
ORDER BY
    sols.t_dino DESC,
    sors.t_orno DESC,
    sols.t_pono
EOF;
//echo $query;
    $rows = tldUtils::getSqlToAssocArray($query, 'odbc', ['src' => 'baan']);
    // Get tracking info from WEB
    foreach ($rows as $row) {
        $a = ['num' => $row['t_dino']];
        $rowsDINOTRNO = _getDINOTRNOListByConstraints($ERP, $a);
        if (count($rowsDINOTRNO)) {
            $DATA[] = array_merge($row, $rowsDINOTRNO[0]);
        } else {
            $DATA[] = $row;
        }
    }
    return $DATA;
}

function _getDINOTRNOListByConstraints($ERP, $a)
{
    if (is_array($a)) {
        $WHERE = tldUtils::constructWhere($a);
    } else {
        $WHERE = $a;
    }
    if (!empty($WHERE)) {
        $WHERE = "AND $WHERE";
    }
    $query = <<<EOF
SELECT
    t1.id, t1.dt, t1.dat, t1.erp, t1.num,
    t2.courier,
    t2.trno
FROM erp_archive AS t1
    LEFT JOIN erp_dino_trno AS t2 ON t1.erp=t2.erp AND t1.num=t2.dino
WHERE
    t1.doc_type='PACKING SLIP'
	AND t1.erp=$ERP and t1.xml<>''
	$WHERE
EOF;
    return tldUtils::getSqlToAssocArray($query);
}
