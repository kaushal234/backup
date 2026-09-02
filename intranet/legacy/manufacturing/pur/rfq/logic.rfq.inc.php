<?php
$DEFAULT_TITLE .= "\RFQ";
// Get MOO
$moo_id = tldModule::getMOOIDByModule("po");
$DEFAULT_MENU .=<<<EOF
<br/>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<a href="$php_self?m[0]=rfq">Home</a>&nbsp;|&nbsp;
<a href="$php_self?m[0]=rfq&m[1]=byNumber">By number</a>&nbsp;|&nbsp;
<a href="$php_self?m[0]=rfq&m[1]=reports">Reports</a>&nbsp;|&nbsp;
<a href="/en/private/manufacturing/pur/rfq/help.pdf">Help</a>&nbsp;|&nbsp;
<a href="/en/private/directory/index.php?m[0]=people&m[1]=view&id=$moo_id">Owner</a>
EOF;

switch($m[1]){
case 'view':
    include("view.rfq.inc.php");
break;
case 'byNumber':
    $form = new HTML_QuickForm('frmByNum', 'post');
    $form->addElement(  'hidden', 'm[0]', 'rfq');
    $form->addElement(  'hidden', 'm[1]', 'view');
    $form->addElement(  'header', 'title', 'Get RFQ');
    $form->addElement(  'select', 'erp', 'Location',
        tldLocation::getERPList("smartyOptions"));
    $form->addElement(  'text',   'id', 'RFQ#');
    $form->addElement(  'submit', 'btnSubmit', 'Submit');
    $form->setDefaults(array("erp"=>$DEFAULT_ERP));
    $body .= $form->toHTML();
break;
case 'reports':
    $body = $smarty->fetch("$PATH/rfq/reports/homepage.reports.tpl");
break;
case 'listing':
    switch($m[2]){
    case 'bySunoERP':
        $caption = "RFQ by Suno $suno, ERP $erp";
        $suno = TldDatabase::escape($suno);
        $erp = TldDatabase::escape($erp);
        $rows = tldRFQ::byVendorERP($suno, $erp);
    break;
    case 'byOpen':
        $caption = "Open RFQ for ERP $erp";
        $erp = TldDatabase::escape($erp);
        switch($m[3]){
        case 'byBuyerID':
            $uid = TldDatabase::escape($uid);
            $buyer = new tldUser($uid);
            $email = $buyer->getEmail();
            $caption.=", for buyer '$email'";
            $rows = tldRFQ::byOpenByBuyerEmail($erp,$email);
        break;
        case 'bySuno':
            $suno = TldDatabase::escape($suno);
            $caption.=", for SUNO '$suno'";
            $rows = tldRFQ::byOpenBySuno($erp,$suno);
        break;
        case 'byItem':
            $pn = TldDatabase::escape($pn);
            $caption.=", for ITEM '$pn'";
            $rows = tldRFQ::byOpenByItem($erp,$pn);
        break;
        default:
            $rows = tldRFQ::byOpenByConstraints($erp);
        break;
        }
    }

    $DEFAULT_MENU.=<<<EOF
<br/>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<a href="{$_SERVER["REQUEST_URI"]}&m[5]=xls">XLS version</a>
EOF;

    switch($m[5]){
    case 'xls':
        $report = new tldXLS(
            $rows,
            array(
                "xItems"=>array(
                    "t_qono"=>"RFQ#",
                    "t_suno"=>"SUNO#",
                    "byr_email"=>"Buyer",
                    "t_rtdt"=>"Return Date",
                    "t_qspa"=>"Status"
                ),
                "showTitles"=>true
            )
        );
        $report->out();
        exit;
    break;
    default:
        $body .= _getListing($rows,$caption);
    break;
    }
break;
case "list":
	$report = new tldReportColumnar(
        tldRFQ::byVendorERP($id, $erp),
        array(
            "xItems"=>array(
                "t_qono"=>"RFQ#",
                "t_suno"=>"SUNO#",
                "byr_email"=>"Buyer",
                "t_rtdt"=>"Return Date",
                "t_qspa"=>"Status"
            ),
			"title"=>"Vendor #$id Request for Quotations",
			"links"=>array("t_qono"=>"$php_self?m[0]=rfq&m[1]=view&erp=$erp&id=")
		)
	);
	$body .= $report->fetch();
break;
default:
	$body = $smarty->fetch("$PATH/rfq/homepage.rfq.tpl");
    $form = new HTML_QuickForm('frmByNum', 'post');
    $form->addElement(  'hidden', 'm[0]', 'rfq');
    $form->addElement(  'hidden', 'm[1]', 'view');
    $form->addElement(  'header', 'title', 'Get RFQ');
    $form->addElement(  'select', 'erp', 'Location',
        tldLocation::getERPList("smartyOptions"));
    $form->addElement(  'text',   'id', 'RFQ#');
    $form->addElement(  'submit', 'btnSubmit', 'Submit');
    $form->setDefaults(array("erp"=>$DEFAULT_ERP));
    $body .= $form->toHTML();
break;
}

function _getListing($rows,$caption){
    global $php_self,$erp;
    $report = new tldReportColumnar(
        $rows,
        array(
            "xItems"=>array(
                "t_qono"=>"RFQ#",
                "t_suno"=>"SUNO#",
                "byr_email"=>"Buyer",
                "t_rtdt"=>"Return Date",
                "t_qspa"=>"Status"
            ),
            "title"=>$caption,
            "showNumberOfRows"=>TRUE,
            "links"=>array("t_qono"=>"$php_self?m[0]=rfq&m[1]=view&erp=$erp&id=")
        )
    );
    return $report->fetch();
}
?>