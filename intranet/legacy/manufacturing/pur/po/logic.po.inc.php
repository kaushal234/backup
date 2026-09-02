<?php
$DEFAULT_TITLE .= "\Purchase Orders";
// Get MOO
$moo_id = tldModule::getMOOIDByModule("po");
$DEFAULT_MENU .=<<<EOF
<br/>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<a href="$php_self?m[0]=po">Home</a>&nbsp;|&nbsp;
<a href="$php_self?m[0]=po&m[1]=byNumber">By number</a>&nbsp;|&nbsp;
<a href="$php_self?m[0]=po&m[1]=reports">Reports</a>&nbsp;|&nbsp;
<a href="/en/private/mis/mis.php?m[0]=help&m[1]=dms&id=1908">Help</a>&nbsp;|&nbsp;
<a href="/en/private/directory/index.php?m[0]=people&m[1]=view&id=$moo_id">Owner</a>
EOF;

if(!$user->isInGroup(array("gg_PUR","gg_ADMIN","gg_ACCT","gg_PARTS","gg_ENG","role_COO"))){
	$DEFAULT_ERROR[]="ERROR: You do not have permissions...";
    return;
}

switch($m[1]){
case 'view':
    include("view.po.inc.php");
break;
case 'byNumber':
    $form = new HTML_QuickForm('frmByNum', 'post');
    $form->addElement(  'hidden', 'm[0]', 'po');
    $form->addElement(  'hidden', 'm[1]', 'view');
    $form->addElement(  'header', 'title', 'Get PO');
    $form->addElement(  'select', 'erp', 'Location',
        tldLocation::getERPList("smartyOptions"));
    $form->addElement(  'text',   'id', 'PO#');
    $form->addElement(  'submit', 'btnSubmit', 'Submit');
    $form->setDefaults(array("erp"=>$DEFAULT_ERP));
    $body .= $form->toHTML();
break;
case 'reports':
    switch($m[2]){
    case 'byReceiptsPeriodByCriteria':
        // Listing
        $erpList = tldLocation::getERPList('smartyOptions');
        $supplierList = array();
        $erpObj = new tldBaanERP($DEFAULT_ERP);
        $supplierRawList = $erpObj->getSupplierData(NULL,NULL,array("orderBy"=>"t_suno"));
        foreach($supplierRawList as $sup){
            $supplierList[$sup['t_suno']] = $sup['t_suno']." -> ".$sup['t_nama'];
        }
        $buyerGrp = new tldGroup("role_BYR",$DEFAULT_ERP);
        $buyerList = array_column($buyerGrp->getUserlist(), 'fullname', 'id');
        // Form
        $form = new HTML_QuickForm('frmByNum', 'post');
        $form->addElement(  'hidden', 'm[0]', 'po');
        $form->addElement(  'hidden', 'm[1]', 'listing');
        $form->addElement(  'hidden', 'm[2]', 'byReceiptsPeriod');
        $form->addElement(  'hidden', 'm[3]', 'byCriteria');
        $form->addElement(  'hidden', 'x', $DEFAULT_ERP);
        $form->addElement(  'header', 'title', 'Select criteria');
        $form->addElement(  'select', 'supplier_suno', 'Supplier', array(""=>"")+$supplierList);
        $form->addElement(  'select', 'buyer_id', 'Buyer', array(""=>"")+$buyerList);
        $form->addElement(  'text', 'pn', 'Part number');
        $form->addElement(  'header', 'title', 'Date range');
        $form->addElement(  'text', 'ds', 'Date from', array('class'=>'datepicker'));
        $form->addElement(  'text', 'de', 'Date to', array('class'=>'datepicker'));
        $form->addElement(  'submit', 'btnSubmit', 'Submit');
        $requiredFields = array('ds','de');
        foreach($requiredFields as $field) $form->addRule($field, 'Required', 'required');
        $body .= $form->toHTML();
    break;
    case 'priceAuditByPN':
        $form = new HTML_QuickForm('frmByNum', 'post');
        $form->addElement(  'hidden', 'm[0]', 'po');
        $form->addElement(  'hidden', 'm[1]', 'listing');
        $form->addElement(  'hidden', 'm[2]', 'all');
        $form->addElement(  'hidden', 'm[3]', 'byItem');
        $form->addElement(  'header', 'title', 'Item price audit');
        $form->addElement(  'select', 'x', 'Location',
            tldLocation::getERPList("smartyOptions"));
        $form->addElement(  'text',   'y', 'Part Number');
        $form->addElement(  'submit', 'btnSubmit', 'Submit');
        $form->setDefaults(array("erp"=>$DEFAULT_ERP));
        $body .= $form->toHTML();
    break;
    case 'cancellationByFactoryByUrgency':
        $form = new tldMatrix(
            tldPOL::countCancellationByERPPddt(),
            "level", "location", "num",
            "$php_self?m[0]=reports&m[1]=byCancellationByERPPddt",
            "PO Cancellation count by urgency, by Factory",
            array(
                "xItems"=>array("Urgent", "Week", "Month", ">Month"),
                "doNotShowXTotals"=>true
            )
        );
        $body = $form->fetch();
        $form = new tldMatrix(
            tldPOL::countValueCancellationByERPPddt(),
            "level", "location", "num",
            "$php_self?m[0]=reports&m[1]=byCancellationByERPPddt",
            "PO Cancellation EUR Amount, by urgency, by Factory",
            array(
                "xItems"=>array("Urgent", "Week", "Month", ">Month"),
                "doNotShowXTotals"=>true
            )
        );
        $body.= $form->fetch();
    break;
    case 'reschedulingByFactoryByUrgency':
        $form = new tldMatrix(
    		tldPOL::countReschedulingByERPPddt(),
    		"level", "location", "num",
    		"$php_self?m[0]=reports&m[1]=byReschedulingByERPPddt",
    		"PO Rescheduling count by urgency, by Factory",
    		array(
				"xItems"=>array("Urgent", "Week", "Month", ">Month"),
				"doNotShowXTotals"=>true
    		)
        );
        $body = $form->fetch();
        $form = new tldMatrix(
    		tldPOL::countValueReschedulingByERPPddt(),
    		"level", "location", "num",
    		"$php_self?m[0]=reports&m[1]=byReschedulingByERPPddt",
    		"PO Rescheduling EUR Amount, by urgency, by Factory",
    		array(
				"xItems"=>array("Urgent", "Week", "Month", ">Month"),
				"doNotShowXTotals"=>true
    		)
        );
        $body.= $form->fetch();
    break;
    default:
        $body = $smarty->fetch("$PATH/po/reports/homepage.reports.tpl");
    break;
    }
break;
case 'listing':
    $xItems = [
        "t_suno" => "Vendor#",
        "t_nama" => "Vendor Name",
        "t_orno" => "PO#",
        "t_cotp" => "Order Type",
        "t_odat" => "PO Date",
        "t_pono" => "Line#",
        "t_item" => "PN#",
        "t_revi" => "Rev",
        "t_dsca" => "PN description",
        "t_aitc" => "Vendor PN#",
        "t_oqua" => "Ordered Qty",
        "t_dqua" => "Delivery Qty",
        "t_bqua" => "Back Order Qty",
        "t_ddta" => "Orig Del Date",
        "t_resc" => "Resc Del Date",
        "t_resm" => "Resc Message",
        "t_ddtc" => "Confirm Date",
        "t_ddtd" => "Changed Delivery Date",
        "t_oltm" => "Lead Time",
        "th_date" => "Theoretical Delivery Date",
        "late_resc" => "Days against Resc Date",
        "t_copr" => "Std Cost Price",
        "t_prip" => "Item pur.price",
        "t_pric" => "Order price",
        "t_namb" => "Contact",
        "t_csgp" => "Pur.Stat.Group",
        "t_cbrn" => "Line of Business"
    ];

    // Default $x is EPR#
    $x = TldDatabase::escape($x);
    if($x == 620) {
        $xItems = $xItems + [
                "t_refa" => "Ref A",
                "t_refb" => "Ref B"
            ];
    }
    switch($m[2]){
    case 'all':
        $_title = "ALL PO Lines for company $x";
        // Add columns
        $xItems = $xItems + array(
            "t_pric"=>"Price"
        );
        // To have all PO LINES without multiple receipts,
        // we take the last receipt
        $a = <<<EOF
T2.t_srnb=(
    SELECT MAX(T3.t_srnb) FROM ttdpur045$x AS T3
    WHERE T2.t_orno=T3.t_orno AND T2.t_pono=T3.t_pono
)
EOF;
        switch($m[3]){
        case 'byItem':
            $form = new HTML_QuickForm('frmByNum', 'post');
            $form->addElement(  'hidden', 'm[0]', 'po');
            $form->addElement(  'hidden', 'm[1]', 'listing');
            $form->addElement(  'hidden', 'm[2]', 'all');
            $form->addElement(  'hidden', 'm[3]', 'byItem');
            $form->addElement(  'header', 'title', 'Item price audit');
            $form->addElement(  'select', 'x', 'Location',
                tldLocation::getERPList("smartyOptions"));
            $form->addElement(  'text',   'y', 'Part Number');
            $form->addElement(  'submit', 'btnSubmit', 'Submit');
            $form->setDefaults(array("erp"=>$DEFAULT_ERP));
            $cells[] = $form->toHTML();

            $doctypes = [
                "SALES ORDER ACK", "SALES INVOICE",
                "FINANCE INVOICE", "SALES QUOTATION",
                "PURCHASE ORDER", "PACKING SLIP"
            ];
            $form = new HTML_QuickForm('frmArchive', 'get', "/en/private/finance/finance.php", '', '', true);
            $form->addElement('hidden', 'm[0]', 'archive');
            $form->addElement('header', 'title', 'Document Archive');
            $form->addElement('select', 'erp', 'Company', $erps);
            $form->addElement('select', 'doctype', 'Doc type',
                array_combine($doctypes, $doctypes)
            );
            $form->addElement('text', 'id', 'Doc#', ["size" => 12]);
            $form->addElement('submit', 'btnSubmit', 'Submit');
            $form->setDefaults(["erp" => $DEFAULT_ERP]);
            $cells[] = $form->toHTML();
            $report = new tldHTMLTable(
                $cells,
                [
                    "cols" => 2,
                    "attribs" => [
                        "table" => " width='100%'",
                        "tr" => " bgcolor='#FFFFFF'"
                    ]
                ]
            );
            $body .= $report->fetch();
            $y = TldDatabase::escape($y);
            $a.= "AND T1.t_item LIKE '$y'";
            $_title.=", for ITEM '$y'";
            $rows = tldPOL::byConstraints($x,$a);
            foreach($rows as &$row){
                if($x == 620) {
                    $po = new tldPO($row['t_orno'], $x);
                    $podetail = $po->getHeader();
                    $row['t_refa'] = $podetail['t_refa'];
                    $row['t_refb'] = $podetail['t_refb'];
                }
            	$t_suno = $row['t_suno'];
            	$query = <<<EOF
SELECT CONCAT( vendors.firstname, ' ', vendors.lastname ) AS vendor_name
FROM vendors_suno
LEFT JOIN vendors ON vendors_suno.parent_id = vendors.id
WHERE t_suno LIKE '$t_suno'
EOF;
            	$vendor = tldUtils::getSqlToAssocArray($query);

            }
        break;
        case 'byItemByOpen':
        	$form = new HTML_QuickForm('frmByNum', 'post');
        	$form->addElement(  'hidden', 'm[0]', 'po');
        	$form->addElement(  'hidden', 'm[1]', 'listing');
        	$form->addElement(  'hidden', 'm[2]', 'all');
        	$form->addElement(  'hidden', 'm[3]', 'byItemByOpen');
        	$form->addElement(  'header', 'title', 'Item price audit');
        	$form->addElement(  'select', 'x', 'Location',
        			tldLocation::getERPList("smartyOptions"));
        	$form->addElement(  'text',   'y', 'Part Number');
        	$form->addElement(  'submit', 'btnSubmit', 'Submit');
        	$form->setDefaults(array("erp"=>$DEFAULT_ERP));
        	$body = $form->toHTML();
        	$y = TldDatabase::escape($y);
        	$x = TldDatabase::escape($x);

        	$a.= <<<EOF
AND T2.t_srnb=(SELECT MAX(T3.t_srnb) FROM ttdpur045$x AS T3
	WHERE T2.t_orno=T3.t_orno AND T2.t_pono=T3.t_pono)
AND (T2.t_spur<9 OR (T2.t_spur=9 AND T2.t_bqua>0))
AND T1.t_oqua>T1.t_dqua
EOF;
        	$a.= " AND T1.t_item LIKE '$y'";
        	$_title.=", for ITEM '$y'";
        	$rows = tldPOL::byConstraints($x,$a);
        	foreach($rows as &$row){
        		$t_suno = $row['t_suno'];
        		$query = <<<EOF
SELECT CONCAT( vendors.firstname, ' ', vendors.lastname ) AS vendor_name
FROM vendors_suno
LEFT JOIN vendors ON vendors_suno.parent_id = vendors.id
WHERE t_suno LIKE '$t_suno'
EOF;
        		$vendor = tldUtils::getSqlToAssocArray($query);

        	}
        	break;
        }
    break;
    case 'byReceiptsPeriod':
        $_title = "PO Lines for company $x received from $ds to $de";
        try{
            if(empty($ds) || empty($de)){
                throw new Exception('Empty date');
            }
            $ds = new DateTime($ds);
            $de = new DateTime($de);
        }catch(Exception $e){
            $DEFAULT_ERROR[]="ERROR: Date parameters are invalid. Reason: ".$e->getMessage();
            break 2;
        }
        // Add columns
        $xItems = $xItems + array(
            "t_date"=>"Receipt date",
        	"late_days"=>"Late days vs commited",
            "reliability"=>"OTDP Reliability?",
        );
        switch($m[3]){
        case 'byCriteria':
            $fields = array('pn','supplier_suno','buyer_id');
            $constraints = array("1=1");
            foreach($fields as $field){
                if(empty($_REQUEST[$field])) continue;
                $val = TldDatabase::escape($_REQUEST[$field]);
                switch($field){
                case 'pn':
                    $constraints[] = "T1.t_item='$val'";
                break;
                case 'supplier_suno':
                    $constraints[] = "T3.t_suno='$val'";
                break;
                case 'buyer_id':
                    $byr = new tldUser($val);
                    $email = $byr->getEmail();
                    $constraints[] = "(SELECT RTRIM(t6.t_info) FROM ttccom001$x AS t6 WHERE t6.t_emno=T3.t_ccon)='$email'";
                break;
                }
            }
            $_title.=", by criteria";
            $rows = tldPOL::byReceiptsPeriodByConstraints(
                $x,
                $ds->format('Y-m-d'),
                $de->format('Y-m-d'),
                implode(' AND ',$constraints)
            );
        break;
        case 'byBuyerID':
            $y = TldDatabase::escape($y);
            $buyer = new tldUser($y);
            $email = $buyer->getEmail();
            $_title.=", for buyer '$email'";
            $rows = tldPOL::byReceiptsPeriodByBuyerEmail(
                $x,$ds->format('Y-m-d'),$de->format('Y-m-d'),$email
            );
        break;
        case 'bySuno':
            $y = TldDatabase::escape($y);
            $_title.=", for SUNO '$y'";
            $rows = tldPOL::byReceiptsPeriodBySuno(
                $x,$ds->format('Y-m-d'),$de->format('Y-m-d'),$y
            );
        break;
        case 'byItem':
            $y = TldDatabase::escape($y);
            $_title.=", for ITEM '$y'";
            $rows = tldPOL::byReceiptsPeriodByItem(
                $x,$ds->format('Y-m-d'),$de->format('Y-m-d'),$y
            );
        break;
        default:
            $rows = tldPOL::byReceiptsPeriodByConstraints(
                $x,$ds->format('Y-m-d'),$de->format('Y-m-d'),NULL
            );
        break;
        }
    break;
    case 'byOpenLines':
        $_title = "OPEN Lines for company $x";
        $xItems += ['t_text' => 'Text Field'];
        switch($m[3]){
        case 'byLateVsConfDate':
            $_title.=", late vs confirmation date";
            switch($m[4]){
            case 'byBuyerID':
                $y = TldDatabase::escape($y);
                $buyer = new tldUser($y);
                $email = $buyer->getEmail();
                $_title.=", for buyer '$email'";
                $rows = tldPOL::byOpenByLateVsConfDateByBuyerEmail($x,$email);
            break;
            case 'bySuno':
                $y = TldDatabase::escape($y);
                $_title.=", for SUNO '$y'";
                $rows = tldPOL::byOpenByLateVsConfDateBySuno($x,$y);
            break;
            case 'byItem':
                $y = TldDatabase::escape($y);
                $_title.=", for ITEM '$y'";
                $rows = tldPOL::byOpenByLateVsConfDateByItem($x,$y);
            break;
            default:
                $rows = tldPOL::byOpenByLateVsConfDateByConstraints($x);
            break;
            }
        break;
        case 'byLate':
            $_title.=", late";
            switch($m[4]){
            case 'byBuyerID':
                $y = TldDatabase::escape($y);
                $buyer = new tldUser($y);
                $email = $buyer->getEmail();
                $_title.=", for buyer '$email'";
                $rows = tldPOL::byOpenByLateByBuyerEmail($x,$email);
            break;
            case 'bySuno':
                $y = TldDatabase::escape($y);
                $_title.=", for SUNO '$y'";
                $rows = tldPOL::byOpenByLateBySuno($x,$y);
            break;
            case 'byItem':
                $y = TldDatabase::escape($y);
                $_title.=", for ITEM '$y'";
                $rows = tldPOL::byOpenByLateByItem($x,$y);
            break;
            default:
                $rows = tldPOL::byOpenByLateByConstraints($x);
            break;
            }
        break;
        case 'byUnconfirmed':
            $_title.=", unconfirmed";
            switch($m[4]){
            case 'byBuyerID':
                $y = TldDatabase::escape($y);
                $buyer = new tldUser($y);
                $email = $buyer->getEmail();
                $_title.=", for buyer '$email'";
                $rows = tldPOL::byOpenByUnconfirmedByBuyerEmail($x,$email);
            break;
            case 'bySuno':
                $y = TldDatabase::escape($y);
                $_title.=", for SUNO '$y'";
                $rows = tldPOL::byOpenByUnconfirmedBySuno($x,$y);
            break;
            case 'byItem':
                $y = TldDatabase::escape($y);
                $_title.=", for ITEM '$y'";
                $rows = tldPOL::byOpenByUnconfirmedByItem($x,$y);
            break;
            default:
                $rows = tldPOL::byOpenByUnconfirmedByConstraints($x);
            break;
            }
        break;
        case 'byWithin7Days':
            $_title.=", within 7 days";
            switch($m[4]){
            case 'byBuyerID':
                $y = TldDatabase::escape($y);
                $buyer = new tldUser($y);
                $email = $buyer->getEmail();
                $_title.=", for buyer '$email'";
                $rows = tldPOL::byOpenByWithinNbDaysByBuyerEmail($x,7,$email);
            break;
            case 'bySuno':
                $y = TldDatabase::escape($y);
                $_title.=", for SUNO '$y'";
                $rows = tldPOL::byOpenByWithinNbDaysBySuno($x,7,$y);
            break;
            case 'byItem':
                $y = TldDatabase::escape($y);
                $_title.=", for ITEM '$y'";
                $rows = tldPOL::byOpenByWithinNbDaysByItem($x,7,$y);
            break;
            default:
                $rows = tldPOL::byOpenByWithinNbDaysByConstraints($x,7);
            break;
            }
        break;
        case 'byWithin30Days':
            $_title.=", within 30 days";
            switch($m[4]){
            case 'byBuyerID':
                $y = TldDatabase::escape($y);
                $buyer = new tldUser($y);
                $email = $buyer->getEmail();
                $_title.=", for buyer '$email'";
                $rows = tldPOL::byOpenByWithinNbDaysByBuyerEmail($x,30,$email);
            break;
            case 'bySuno':
                $y = TldDatabase::escape($y);
                $_title.=", for SUNO '$y'";
                $rows = tldPOL::byOpenByWithinNbDaysBySuno($x,30,$y);
            break;
            case 'byItem':
                $y = TldDatabase::escape($y);
                $_title.=", for ITEM '$y'";
                $rows = tldPOL::byOpenByWithinNbDaysByItem($x,30,$y);
            break;
            default:
                $rows = tldPOL::byOpenByWithinNbDaysByConstraints($x,30);
            break;
            }
        break;
        case 'byWithin90Days':
            $_title.=", within 90 days";
            switch($m[4]){
            case 'byBuyerID':
                $y = TldDatabase::escape($y);
                $buyer = new tldUser($y);
                $email = $buyer->getEmail();
                $_title.=", for buyer '$email'";
                $rows = tldPOL::byOpenByWithinNbDaysByBuyerEmail($x,90,$email);
            break;
            case 'bySuno':
                $y = TldDatabase::escape($y);
                $_title.=", for SUNO '$y'";
                $rows = tldPOL::byOpenByWithinNbDaysBySuno($x,90,$y);
            break;
            case 'byItem':
                $y = TldDatabase::escape($y);
                $_title.=", for ITEM '$y'";
                $rows = tldPOL::byOpenByWithinNbDaysByItem($x,90,$y);
            break;
            default:
                $rows = tldPOL::byOpenByWithinNbDaysByConstraints($x,90);
            break;
            }
        break;
        }
    break;
    }

    $DEFAULT_MENU.=<<<EOF
<br/>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<a href="$php_self?m[0]=po&m[1]=listing&out=csv">CSV version</a>
EOF;

    switch($out){
    case 'csv':
        if(empty($sess['po']['listing'])){
            $DEFAULT_ERROR[] = "ERROR: Session expired, please reload the report";
            break;
        }
        $report = new tldCSV(
            $sess['po']['listing'],
            array(
                "xItems"=>$sess['po']['xItems'],
                "showTitles"=>true
            )
        );
        $report->out();
        exit;
    break;
    default:
        $sess['po']['listing'] = $rows;
        $sess['po']['xItems'] = $xItems;
        $report = new tldReportColumnar(
            $rows,
            array(
                "xItems"=>$xItems,
                "title"=>$_title,
                "showNumberOfRows"=>TRUE,
                "links"=>array(
                    "t_orno"=>"$php_self?m[0]=po&m[1]=view&erp=$x&id="
                )
            )
        );
        $body .= $report->fetch();
    break;
    }
break;
case "list":
    $report = new tldReportColumnar(
        tldPO::byVendorErp($id, $erp),
        array(
            "xItems"=>array(
                "t_orno"=>"PO#",
                "t_cotp"=>"Order Type",
				"t_odat"=>"Order Date",
				"t_refa"=>"Ref A",
				"t_refb"=>"Ref B"
            ),
    		"title"=>"Open Purchase Orders",
    		"links"=>array("t_orno"=>"$php_self?m[0]=po&m[1]=view&erp=$erp&id=")
		)
	);
	$body .= $report->fetch();
break;
case 'reports':
	switch($m[2]){
	case 'openLinesByERP':
		if($erp){
			$rows = tldPO::getDetailByERP($erp);
		}else{
			$form = new tldHTMLList(
                tldLocation::getERPList("smartyOptions"),
				 "erp",
				 "$php_self?m[0]=po&m[1]=reports&m[2]=openLinesByERP&erp=",
				 array("title"=>"Please select ERP below")
            );
            $body .= $form->fetch();
		}
	break;
	}
break;
default:
    $body = $smarty->fetch("$PATH/po/homepage.po.tpl");
    $form = new HTML_QuickForm('frmByNum', 'post');
    $form->addElement(  'hidden', 'm[0]', 'po');
    $form->addElement(  'hidden', 'm[1]', 'view');
    $form->addElement(  'header', 'title', 'Get PO');
    $form->addElement(  'select', 'erp', 'Location', tldLocation::getERPList("smartyOptions"));
    $form->addElement(  'text',   'id', 'PO#');
    $form->addElement(  'submit', 'btnSubmit', 'Submit');
    $form->setDefaults(array("erp"=>$DEFAULT_ERP));
    $body .= $form->toHTML();
break;
}


function _getListing($rows,$title){
    global $php_self,$erp;
    $report = new tldReportColumnar(
        $rows,
        array(
            "xItems"=>array(
                "t_suno"=>"Vendor#",
                "t_orno"=>"PO#",
                "t_odat"=>"PO Date",
                "t_cotp"=>"Order Type",
                "t_pono"=>"Line#",
                "t_item"=>"PN#",
                "t_dsca"=>"PN description",
                "t_aitc"=>"Vendor PN#",
                "t_oqua"=>"Ordered Qty",
                "t_dqua"=>"Delivery Qty",
                "t_bqua"=>"Back Order Qty",
                "t_ddta"=>"Orig Del Date",
                "t_resc"=>"Resc Del Date",
                "t_ddtc"=>"Confirm Date",
                "late_resc"=>"Days against Resc Date"
            ),
            "title"=>$title,
            "showNumberOfRows"=>TRUE,
            "links"=>array(
                "t_orno"=>"$php_self?m[0]=po&m[1]=view&erp=$erp&id="
            )
        )
    );
    return $report->fetch();
}

function _getDisplayPo($po,$mode=NULL){
	$a = $po->asArray();
	$report = new tldAssocTable(
	    $a["header"],
        array(
        	"t_orno"=>"PO#",
            "t_cotp"=>"Order Type",
        	"t_ccur"=>"Currency",
			"t_suno"=>"Supplier#",
        	"supplier"=>"Supplier Name",
			"t_odat"=>"Order Date",
			"t_refa"=>"Ref A",
			"t_refb"=>"Ref B",
			"byr_email"=>"Buyer Email"
        ),
		array("title"=>"Purchase Order #$id")
	);
	$GLOBALS["smarty"]->assign("header", $report->fetch());
	$report = new tldAssocTable(
	    $a["header"]["cwar_data"],
		array(
			"t_nama"=>"Company Name",
			"t_namb"=>"Address",
			"t_namc"=>"",
			"t_namd"=>"",
			"t_name"=>""
		),
		array("title"=>"Delivery Address")
	);
	$GLOBALS["smarty"]->assign("delivery_address", $report->fetch());

	switch($mode){
        case 'shipped':
            $rows = array_merge($po->getDetail() , $po->getShippedDetail());
        break;
	default:

		$rows = array_merge($po->getDetail() , $po->getShippedDetail());
        // get the last 12 month
        $today = new DateTime();
        $thisYear = $today->format('Y');
        $dateYM = [];
        for ($i = 0; $i < 12; $i++) {
            $today->modify('-1 month');
            $year = $today->format('Y');
            $month = $today->format('n');

            //the last 12 month can be on 2 years so we associate the month to its year
            $dateYM[$year][] = 't_aupp_' . $month;
            //get the names of args
            // because in tdinv750 the values are listed by year, janaury is t_aupp_1, february is t_aupp_2 ect...
            $dateYM['query'][] = $year . '.t_aupp_' . $month;
            $monthsArg[] ='t_aupp_'.($i+1);
        }
        //sum all the arg selected
        $selectArg = 'Y' . implode(' + Y', $dateYM['query']);
        unset($dateYM['query']);
        //create the query. Join the same table if a second year is needed
        $years = array_keys($dateYM);
		foreach($rows as &$row){
		    $erp = $po->getERP();
		    $query=<<<EOF
SELECT tld750.t_item,
SUM(tld750.t_uscu) AS t_uscu
FROM ttdtld750{$po->getERP()} tld750
WHERE tld750.t_item='{$row['t_item']}' AND CAST(tld750.t_year AS CHAR(4))+CAST(tld750.t_mont AS VARCHAR(2)) IN ('$dateYM')
GROUP BY tld750.t_item
EOF;

            $query = <<<SQL
SELECT RTRIM(Y$years[0].t_item) AS t_item,
      'Past 12 months' AS date,
      {$selectArg} AS t_uscu FROM ttdinv750{$erp} AS Y$years[0]
SQL;
            if (count($years) === 2) {
                $query .= <<<SQL
    INNER JOIN ttdinv750{$erp} AS Y$years[1]
    ON Y$years[0].t_item = Y$years[1].t_item
SQL;
            }
            $query .= <<<SQL
    WHERE Y$years[0].t_item='{$row['t_item']}'
    AND Y$years[0].t_year = '$years[0]'
SQL;
            if (count($years) === 2) {
                $query .= <<<SQL
    AND Y$years[1] . t_year = '$years[1]'
SQL;
            }
            $query .= ';';
            $data = tldUtils::getSqlRowToAssocArray($query, 'odbc', ['src' => 'baan']);
		    if($data){
		        $row['t_uscu'] = $data['t_uscu'];
		    }else{
		        $row['t_uscu'] = 0;
		    }
		    $total = $total + (float)$row['t_amta'];
//		    $row['t_pric'] = round($row['t_pric'], 2);
		}
	break;
	}

    $GLOBALS["smarty"]->assign("mode",$mode);
	$GLOBALS["smarty"]->assign("po",$a["header"]["t_orno"]);
	$GLOBALS["smarty"]->assign("detail",$rows);
	$GLOBALS["smarty"]->assign("total",$total);
	return $GLOBALS["smarty"]->fetch($GLOBALS["PATH"]."/po/view.po.tpl");
}
?>
