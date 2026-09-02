<?php
include_once("erp.inc.php");

if(!$user->isInGroup(array("gg_ADMIN","gg_ACCT","gg_SALES","gg_PARTS","gg_MIS","gg_SUPPORT","gg_PUR"))){
    $DEFAULT_ERROR[] = "ERROR: You do not have permissions for this page.";
    return;
}

$DEFAULT_TITLE .= "\Invoice";
$DEFAULT_MENU .=<<<EOF
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<a href="$php_self?m[0]=inv">Home</a>
&nbsp;|&nbsp;<a href="$php_self?m[0]=inv&m[1]=form&m[2]=byNum">By number</a>
EOF;

switch($m[1]){
case 'form':
    switch($m[2]){
    case 'byNum':
        $DEFAULT_TITLE .= "\INV by Number";
        $form = new HTML_QuickForm('frmInvByNum', 'post');
        $form->addElement(	'hidden', 'm[0]', 'inv');
        $form->addElement(	'hidden', 'm[1]', 'view');
        $form->addElement(	'header', 'title', "INV by Number");

        $form->addElement(	'select', 'erp', 'Factory',
        tldLocation::getERPList("smartyOptions")
        );
        $form->addElement(	'text', 'id', 'INV# inc. tran type..');
        $form->addElement(	'submit', 'btnSubmit', 'Submit');
        $body .= $form->toHTML();
    break;
    case 'search':
        $form = new HTML_QuickForm('search', 'get');
        $form->addElement(	'hidden', 'm[0]', 'inv');
        $form->addElement(	'hidden', 'm[1]', 'search');
        $form->addElement(	'header', 'title', "Search Sales invoices");
        $form->addElement(	'text', 't_ninv', 'Your ref# or TLD Invoice#');
        $form->addElement(	'submit', 'btnSubmit', 'Submit');
        $form->addRule('t_ninv','Required','required');
        if ($form->validate()){
            $rows = tldINV::search(
                $t_ninv,
                $user->getERP(),
                array(
                    "where"=>array(
                        "t_cuno"=>$user->getCUNO()
                    )
                )
            );
            $report = new tldReportColumnar(
                $rows,
                array(
                    "xItems"=>array(
                        "t_ninv"	=>"Invoice Number#",
                        "t_docd"	=>"Date",
                        "t_dued"	=>"Due",
                        "t_refr"	=>"Ref R",
                        "t_orno"=>"Order#"
                    ),
                    "title"=>"Invoices",
                    "links"=>array(
                        "t_ninv"=>array(
                            "url"=>"$php_self?m[0]=inv&m[1]=view",
                            "params"=>array(
                                "t_ninv"=>"t_ninv",
                                "t_orno"=>"t_orno"
                            )
                        )
                    )
                )
            );
            $body .= $report->fetch();
        }else{
            $body = $form->toHTML();
        }
    break;
    }
break;
case 'view':
    if(empty($id)){
        $DEFAULT_ERROR[] = "ERROR: id not set";
        break;
    }
    if(empty($erp)){
        $DEFAULT_ERROR[] = "ERROR: erp not set";
        break;
    }
    if(empty($ttyp)){
    	$inv = new tldINV($id, $erp);
    }else{
    	$inv = new tldINV($id, $erp, array("type"=>$ttyp));
    }
	$header = $inv->getHeader();
    if(empty($header)){
        $DEFAULT_ERROR[] = "ERROR: Problem finding that document";
        break;
    }
    $DEFAULT_MENU .=<<<EOF
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<a href="$php_self?m[0]=archive&erp=$erp&doctype=SALES INVOICE&id=$ttyp$id" title="PDF Archive">
<img src="/shared/bluesphere/32x32/mimetypes/pdf.png"></a>
EOF;
    $report = new tldAssocTable(
        $header,
        array(
            "t_ninv"	=>"Invoice Number#",
            "t_ttyp"	=>"Type",
            "t_docd"	=>"Date",
            "t_dued"	=>"Due",
            "t_refr"	=>"Ref R",
            "t_ccur"	=>"Currency",
            "t_balc"	=>"Balance"
        ),
        array(
            "title"=>	"Invoice #$id",
            "doNotShowEmpty"=>true
        )
    );
    $cells[] = $report->fetch();

    $report = new tldHTMLTable(
        $cells,
        array(
            "cols"=>2,
            array(
                "attribs"=>array(
                    "table"=>" width='100%'"
                )
            )
        )
    );
    $body .= $report->fetch();
    $report = new tldReportMultilevel(
        $inv->getDetail(),
        array("t_orno"),
        array(
            "t_pono"=>"Item#",
            "t_srnb"=>"Sequence",
            "t_item"=>"Part Number",
            "t_dsca"=>"Description",
            "t_cuqs"=>"UM",
            "t_oqua"=>"Qty",
            "t_dqua"=>"Del",
            "t_bqua"=>"Back",
            "t_ssls"=>"Status<br>(7=Shipped)",
            "t_dino"=>"Packing Slip#",
            "t_ddat"=>"Delivery Date",
            "t_orno"=>"SO#"),
        array(
            "links"=>array(
                "t_orno"=>"$php_self?m[0]=so&m[1]=view&erp=$erp&id=",
                "t_dino"=>"$php_self?m[0]=ps&m[1]=view&erp=$erp&id=",
                "t_item"=>array(
                    "url"=>"/en/private/parts/parts.php?m[0]=inv&m[1]=view",
                    "params"=>array("id"=>"t_item")
                )
            )
        )
    );
    $body .= $report->fetch();
break;
case 'viewin':
    if (empty($id)) {
        $DEFAULT_ERROR[] = "ERROR: id not set";
        break;
    }
    if (empty($erp)) {
        $DEFAULT_ERROR[] = "ERROR: erp not set";
        break;
    }
    if (empty($ttyp)) {
        $inv = new tldINV($id, $erp);
    } else {
        $inv = new tldINV($id, $erp, array("type" => $ttyp));
    }
    $header = $inv->getHeader();
    if (empty($header)) {
        $DEFAULT_ERROR[] = "ERROR: Problem finding that document";
        break;
    }
    $DEFAULT_MENU .= <<<EOF
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<a href="$php_self?m[0]=archive&erp=$erp&doctype=SALES INVOICE&id=$ttyp$id" title="PDF Archive">
<img src="/shared/bluesphere/32x32/mimetypes/pdf.png"></a>
EOF;
    $report = new tldAssocTable(
        $header,
        [
            "t_ninv" => "Invoice Number#",
            "t_ttyp" => "Type",
            "t_docd" => "Date",
            "t_dued" => "Due",
            "t_refr" => "Ref R",
            "t_ccur" => "Currency",
            "t_balc" => "Balance"
        ],
        [
            "title" => "Invoice #$id",
            "doNotShowEmpty" => true
        ]
    );
    $cells[] = $report->fetch();

    $report = new tldHTMLTable(
        $cells,
        [
            "cols" => 2,
            [
                "attribs" => [
                    "table" => " width='100%'"
                ]
            ]
        ]
    );
    $body .= $report->fetch();
    $report = new tldReportMultilevel(
        $inv->getDetail(),
        ["t_orno"],
        [
            "t_pono" => "Item#",
            "t_item" => "Part Number",
            "t_dsca" => "Description",
            "t_cuqs" => "UM",
            "t_dqua" => "Del",
            "t_dino" => "Packing Slip#",
            "t_ddat" => "Delivery Date",
        ],
        [
            "links" => [
                "t_orno" => "$php_self?m[0]=so&m[1]=view&erp=$erp&id=",
                "t_dino" => "$php_self?m[0]=ps&m[1]=view&erp=$erp&id=",
                "t_item" => [
                    "url" => "/en/private/parts/parts.php?m[0]=inv&m[1]=view",
                    "params" => ["id" => "t_item"]
                ]
            ]
        ]
    );
    $body .= $report->fetch();
    break;
case 'list':
    switch($m[2]){
    case 'byCustomerERP':
        if(empty($id) || empty($erp)){
            $DEFAULT_ERROR[] = "ERROR: ERP or Customer number not set";
            break 2;
        }
        $rows = tldINV::byOpenByCuno($erp, $id);
        $report_title = "OPEN Invoices by ERP $erp Customer# $id";
    break;
    }
    if(count($rows)){
        $report = new tldReportColumnar(
            $rows,
            array(
                "xItems"=>array(
                    "t_ttyp"	=>"Invoice Type",
                    "t_ninv"	=>"Invoice Number",
                    "t_docd"	=>"Date",
                    "status"	=>"Status",
                    "t_refr"	=>"Ref R"
                ),
                "title"=>$report_title,
                "links"=>array(
                    "t_ninv"=>array(
                        "url"=>"$php_self?m[0]=inv&m[1]=view&erp=$erp",
                        "params"=>array(
                            "id"=>"t_ninv","ttyp"=>"t_ttyp"
                        )
                    )
                )
            )
        );
    	$body .= $report->fetch();
    }else{
        $DEFAULT_ERROR[] = "ERROR: No Invoices found for $erp and $id";
    }
break;
default:
}
?>
