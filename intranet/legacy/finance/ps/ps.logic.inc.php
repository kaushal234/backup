<?php
include_once 'erp.inc.php';

// ACL access
if (!$user->isInGroup(['gg_ADMIN', 'gg_ACCT', 'gg_SALES', 'gg_PARTS', 'gg_PARTS_AGENTS', 'gg_PUR', 'gg_MIS', 'gg_SUPPORT'])) {
    $DEFAULT_ERROR[] = 'ERROR: You do not have permissions for this page..';
    return;
}

$DEFAULT_TITLE .= "\Packing Slips";
$DEFAULT_MENU .= <<<EOF
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<a href="$php_self?m[0]=ps">Home</a>
EOF;

switch ($m[1]) {
    case 'form':
        switch ($m[2]) {
            case 'search':
                $form = new HTML_QuickForm('search', 'get');
                $form->addElement('hidden', 'm[0]', 'ps');
                $form->addElement('hidden', 'm[1]', 'search');
                $form->addElement('header', 'title', 'Search Packing Slips');
                $form->addElement('text', 't_ninv', 'Your ref# or TLD Invoice#');
                $form->addElement('submit', 'btnSubmit', 'Submit');
                $form->addRule('t_ninv', 'Required', 'required');
                if ($form->validate()) {
                    $rows = tldINV::search(
                        $t_ninv,
                        $user->getERP(),
                        array(
                            'where' => array(
                                't_cuno' => $user->getCUNO()
                            )
                        )
                    );
                    $report = new tldReportColumnar(
                        $rows,
                        array(
                            'xItems' => array(
                                't_ninv' => 'Invoice Number#',
                                't_docd' => 'Date',
                                't_dued' => 'Due',
                                't_refr' => 'Ref R',
                                't_orno' => 'Order#'
                            ),
                            'title' => 'Invoices',
                            'links' => array(
                                't_ninv' => array(
                                    'url' => "$php_self?m[0]=ps&m[1]=view",
                                    'params' => array(
                                        't_ninv' => 't_ninv',
                                        't_orno' => 't_orno'
                                    )
                                )
                            )
                        )
                    );
                    $body .= $report->fetch();
                } else {
                    $body = $form->toHTML();
                }
                break;
        }
        break;
    case 'viewps':
        if (empty($id)) {
            $DEFAULT_ERROR[] = 'ERROR: id not set';
            break;
        }
        if (empty($erp)) {
            $DEFAULT_ERROR[] = 'ERROR: erp not set';
            break;
        }
        $DEFAULT_MENU .= <<<EOF
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<a href="$php_self?m[0]=archive&erp=$erp&doctype=PACKING SLIP&id=$id" title="PDF Archive">
<img src="/shared/bluesphere/32x32/mimetypes/pdf.png"></a>
EOF;

        $ps = new tldDINO($id, $erp);
        $report = new tldReportMultilevel(
            $ps->getDetail(),
            ['t_orno'],
            [
                't_pono' => 'Item#',
                't_item' => 'Part Number',
                't_dsca' => 'Description',
                't_cuqs' => 'UM',
                't_dqua' => 'Del',
                't_ddat' => 'Delivery Date'],
            [
                'title' => "Company $erp Packing Slip# $id",
                'links' => [
                    't_orno' => "$php_self?m[0]=so&m[1]=view&erp=$erp&id=",
                    't_item' => [
                        'url' => '/en/private/parts/parts.php?m[0]=inv&m[1]=view',
                        'params' => ['id' => 't_item']
                    ]
                ]
            ]
        );
        $body .= $report->fetch();
        break;
    case 'view':
        if (empty($id)) {
            $DEFAULT_ERROR[] = 'ERROR: id not set';
            break;
        }
        if (empty($erp)) {
            $DEFAULT_ERROR[] = 'ERROR: erp not set';
            break;
        }
        $DEFAULT_MENU .= <<<EOF
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<a href="$php_self?m[0]=archive&erp=$erp&doctype=PACKING SLIP&id=$id" title="PDF Archive">
<img src="/shared/bluesphere/32x32/mimetypes/pdf.png"></a>
EOF;

        $ps = new tldDINO($id, $erp);
        $report = new tldReportMultilevel(
            $ps->getDetail(),
            array('t_orno'),
            array(
                't_pono' => 'Item#',
                't_srnb' => 'Sequence',
                't_item' => 'Part Number',
                't_dsca' => 'Description',
                't_cuqs' => 'UM',
                't_oqua' => 'Qty',
                't_dqua' => 'Del',
                't_bqua' => 'Back',
                't_ssls' => 'Status<br>(7=Shipped)',
                't_ddat' => 'Delivery Date',
                't_orno' => 'SO#'),
            array(
                'title' => "Company $erp Packing Slip# $id",
                'links' => array(
                    't_orno' => "$php_self?m[0]=so&m[1]=view&erp=$erp&id=",
                    't_item' => array(
                        'url' => '/en/private/parts/parts.php?m[0]=inv&m[1]=view',
                        'params' => array('id' => 't_item')
                    )
                )
            )
        );
        $body .= $report->fetch();

        $body .= <<<EOF
    <a href="/en/private/parts/parts.php?m[0]=dino_trno&m[1]=add&erp=$erp&dino=$id">Add Tracking Number</a>
EOF;
        $report = new tldReportColumnar(
            $ps->getTRNO(),
            array(
                'xItems' => array(
                    'id' => 'ID',
                    'dt' => 'Date Time',
                    'courier' => 'Courier',
                    'trno' => 'Track Number'
                ),
                'title' => 'Tracking Numbers',
                'links' => array(
                    'trno' => array(
                        'url' => '/shared/redirect_courier.php?',
                        'params' => array(
                            'courier' => 'courier',
                            'trno' => 'trno',
                            'id' => 'id'
                        ),
                        'target' => '_blank'
                    )
                )
            )
        );
        $body .= $report->fetch();

        break;
    case 'list':
        switch ($m[2]) {
            case 'byCustomerERP':
                if (empty($id) || empty($erp)) {
                    $DEFAULT_ERROR[] = 'ERROR: ERP or Customer number not set';
                    break 2;
                }
                $rows = tldDINO::byCustomerERP($id, $erp);
                break;
        }
        if (count($rows)) {
            $report = new tldReportColumnar(
                $rows,
                array(
                    'xItems' => array(
                        't_dino' => 'Packing Slip#'
                    ),
                    'title' => 'Packing Slips',
                    'links' => array(
                        't_dino' => array(
                            'url' => "$php_self?m[0]=ps&m[1]=view&erp=$erp",
                            'params' => array(
                                'id' => 't_dino'
                            )
                        )
                    )
                )
            );
            $body .= $report->fetch();
        } else {
            $DEFAULT_ERROR[] = "ERROR: No Packing Slips found for $erp and $id";
        }
        break;
    default:
        $form = new HTML_QuickForm('frmInvByNum', 'post');
        $form->addElement('hidden', 'm[0]', 'ps');
        $form->addElement('hidden', 'm[1]', 'view');
        $form->addElement('header', 'title', 'Packing Slip by Number');

        $form->addElement('select', 'erp', 'Factory',
            tldLocation::getERPList('smartyOptions')
        );
        $form->addElement('text', 'id', 'Packing Slip#');
        $form->addElement('submit', 'btnSubmit', 'Submit');
        $body .= $form->toHTML();
}
