<?php
$DEFAULT_TITLE .= "\MRP";
$DEFAULT_MENU .=<<<EOF
<br/>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<a href="$php_self?m[0]=mrp">Home</a>&nbsp;|&nbsp;
<a href="$php_self?m[0]=mrp&m[1]=reports">Reports</a>
EOF;

switch($m[1]){
case 'reports':
    $body = $smarty->fetch("$PATH/mrp/reports/homepage.reports.tpl");
break;
case 'listing':
    switch($m[2]){
    case 'byLate':
    	if(empty($erp)) $erp = $DEFAULT_ERP;
        $caption = "Late MRP for ERP $erp";
        $erp = TldDatabase::escape($erp);
        switch($m[3]){
        case 'byBuyerID':
            $uid = TldDatabase::escape($uid);
            $buyer = new tldUser($uid);
            $email = $buyer->getEmail();
            $caption.=", for buyer '$email'";
            $rows = tldMRP::byLateByBuyerEmail($erp,$email);
        break;
        case 'bySuno':
            $suno = TldDatabase::escape($suno);
            $caption.=", for SUNO '$suno'";
            $rows = tldMRP::byLateBySuno($erp,$suno);
        break;
        case 'byItem':
            $pn = TldDatabase::escape($pn);
            $caption.=", for ITEM '$pn'";
            $rows = tldMRP::byLateByItem($erp,$pn);
        break;
        default:
            $rows = tldMRP::byLateByConstraints($erp);
        break;
        }
    break;
    case 'byWithin7Days':
        $caption = "Late MRP for ERP $erp";
        $erp = TldDatabase::escape($erp);
        switch($m[3]){
        case 'byBuyerID':
            $uid = TldDatabase::escape($uid);
            $buyer = new tldUser($uid);
            $email = $buyer->getEmail();
            $caption.=", for buyer '$email'";
            $rows = tldMRP::byWithinNbDaysByBuyerEmail($erp,7,$email);
        break;
        case 'bySuno':
            $suno = TldDatabase::escape($suno);
            $caption.=", for SUNO '$suno'";
            $rows = tldMRP::byWithinNbDaysBySuno($erp,7,$suno);
        break;
        case 'byItem':
            $pn = TldDatabase::escape($pn);
            $caption.=", for ITEM '$pn'";
            $rows = tldMRP::byWithinNbDaysByItem($erp,7,$pn);
        break;
        default:
            $rows = tldMRP::byWithinNbDaysByConstraints($erp,7);
        break;
        }
    break;
    }

    $DEFAULT_MENU.=<<<EOF
<br/>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<a href="{$_SERVER["REQUEST_URI"]}&m[5]=xls">XLS version</a>
EOF;

    switch($m[5]){
    case 'xls':
    	/*
        $report = new tldXLS(
            $rows,
            array(
                "xItems"=>array(
                    "t_orno"=>"PO# MRP",
                    "t_suno"=>"SUNO#",
                    "t_podt"=>"Planned Order Date",
                    "t_pddt"=>"Planned Delivery Date",
                    "t_item"=>"PN#",
            		"revision"=>"Cur Rev",
                    "vendor_pn"=>"Vendor PN#",
                    "t_oqan"=>"Qty",
                    "t_dsca"=>"PN Desc",
                    "byr_email"=>"Buyer",
                    "t_cwar"=>"Warehouse Code"
                ),
                "showTitles"=>true
            )
        );
        $report->out();
        */

    	$edm = new basicEDM($DEFAULT_ERP);
    	$aReleasedController = new tldReleasedController();
    	$date = date('Y-m-d');

    	require_once 'PHPExcel.php';
    	require_once 'PHPExcel/Writer/Excel2007.php';

    	$objPHPExcel = new PHPExcel();
    	$objPHPExcel->setActiveSheetIndex(0);
    	$objPHPExcel->getActiveSheet()->setTitle($caption);

    	// Titles
    	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(0, 1, 'PO# MRP');
    	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(1, 1, 'SUNO#');
    	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(2, 1, 'Planned Order Date');
    	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(3, 1, 'Planned Delivery Date');
    	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(4, 1, 'PN#');
    	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(5, 1, 'Cur Rev');
    	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(6, 1, 'Vendor PN#');
    	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(7, 1, 'Qty');
    	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(8, 1, 'PN Description');
    	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(9, 1, 'Buyer');
    	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(10, 1, 'Warehouse Code');
        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(11, 1, 'Signal Code');
    	// Style date
    	$objPHPExcel->getActiveSheet()->getStyle('C:D')->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_DATE_YYYYMMDD2);

		$styleArray = array(
			'font' => array('bold' => true),
			'alignment' => array('wrap' => true, 'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER, 'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER),
			'borders' => array('top' => array('style' => PHPExcel_Style_Border::BORDER_THIN))
		);

    	$objPHPExcel->getActiveSheet()->getStyle('A1:K1')->applyFromArray($styleArray);
    	$objPHPExcel->getActiveSheet()->getStyle('A:B')->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
    	$objPHPExcel->getActiveSheet()->getStyle('E:E')->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
    	$objPHPExcel->getActiveSheet()->getStyle('G:G')->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);

    	// Get data
    	foreach($rows as $key => $row){
    		$row = array_map('trim', $row);
    		$offset = $key + 2;

    		$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(0, $offset, $row['t_orno']);
    		$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(1, $offset, $row['t_suno']);

    		// Planned Order Date
    		$curDate = DateTime::createFromFormat('Y-m-d', $row['t_podt']);
    		$dtExcel = PHPExcel_Shared_Date::PHPToExcel($curDate);
    		$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(2, $offset, $dtExcel);

    		// Planned Delivery Date
    		$curDate = DateTime::createFromFormat('Y-m-d', $row['t_pddt']);
    		$dtExcel = PHPExcel_Shared_Date::PHPToExcel($curDate);
    		$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(3, $offset, $dtExcel);

    		// PN#
    		$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(4, $offset, $row['t_item']);
    		$objPHPExcel->getActiveSheet()->getCellByColumnAndRow(4, $offset)->getHyperlink()->setUrl("http://{$_SERVER['HTTP_HOST']}/en/private/parts/parts.php?m[0]=inv&m[1]=view&erp={$DEFAULT_ERP}&id={$row['t_item']}");

    		// Cur Rev
    		$file = $edm->getFilenameByPNDate($row['t_item'], $date);
    		if($file AND $aReleasedController->fileExistsInVault($DEFAULT_ERP, $file)){
    			$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(5, $offset, trim($edm->getCurrentRevLevel($row['t_item'])));
    			$objPHPExcel->getActiveSheet()->getCellByColumnAndRow(5, $offset)->getHyperlink()->setUrl("http://{$_SERVER['HTTP_HOST']}/en/private/manufacturing/eng/dev.php?m[0]=getfile&m[1]=drawing&erp={$DEFAULT_ERP}&item={$row['t_item']}&date={$date}");
    		}

    		$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(6, $offset, $row['vendor_pn']);
    		$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(7, $offset, $row['t_oqan']);
    		$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(8, $offset, $row['t_dsca']);

    		// Buyer
    		$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(9, $offset, $row['byr_email']);
    		$objPHPExcel->getActiveSheet()->getCellByColumnAndRow(9, $offset)->getHyperlink()->setUrl("mailto:{$row['byr_email']}");

    		$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(10, $offset, $row['t_cwar']);
            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(11, $offset,$row['t_csig']);
    	}

    	// Set height & width
    	$objPHPExcel->getActiveSheet()->getRowDimension('1')->setRowHeight(28.7);
    	$objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(12);
    	$objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(9);
    	$objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(19);
    	$objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(21);
    	$objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(17);
    	$objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(9);
    	$objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(59);
    	$objPHPExcel->getActiveSheet()->getColumnDimension('H')->setWidth(7);
    	$objPHPExcel->getActiveSheet()->getColumnDimension('I')->setWidth(55);
    	$objPHPExcel->getActiveSheet()->getColumnDimension('J')->setWidth(35);
    	$objPHPExcel->getActiveSheet()->getColumnDimension('K')->setWidth(13.71);

    	// Borders
		$styleArray = array(
			'borders' => array('allborders' => array('style' => PHPExcel_Style_Border::BORDER_THIN))
		);

		$objPHPExcel->getActiveSheet()->getStyle("A1:L{$offset}")->applyFromArray($styleArray);


    	// Header colors
		$styleArray = array(
			'fill' => array('type' => PHPExcel_Style_Fill::FILL_SOLID, 'color' => array('rgb' => 'FFFF00'))
		);

		$objPHPExcel->getActiveSheet()->getStyle('A1:L1')->applyFromArray($styleArray);

		// Freeze pane
		$objPHPExcel->getActiveSheet()->freezePane('A2');

		// Alignment
		$styleArray = array(
			'alignment' => array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER)
		);

		$objPHPExcel->getActiveSheet()->getStyle("A2:F{$offset}")->applyFromArray($styleArray);
		$objPHPExcel->getActiveSheet()->getStyle("H2:H{$offset}")->applyFromArray($styleArray);
		$objPHPExcel->getActiveSheet()->getStyle("J2:K{$offset}")->applyFromArray($styleArray);

		// Underline hyperlinks
		$styleArray = array(
			'font' => array('underline' => PHPExcel_Style_Font::UNDERLINE_SINGLE)
		);

    	$objPHPExcel->getActiveSheet()->getStyle("E2:F{$offset}")->applyFromArray($styleArray);
    	$objPHPExcel->getActiveSheet()->getStyle("J2:J{$offset}")->applyFromArray($styleArray);

    	// Output
    	$objWriter = new PHPExcel_Writer_Excel2007($objPHPExcel);
    	header('Content-Disposition: attachment; filename="MRP.xlsx"');
    	$objWriter->save('php://output');

        exit;
    break;
    default:
        $body .= _getListing($rows,$caption);
    break;
    }
break;
default:
	$body = $smarty->fetch("$PATH/mrp/homepage.mrp.tpl");
break;
}

function _getListing($rows,$caption){
    global $php_self,$DEFAULT_ERP;
    $date=date('Y-m-d');
    $edm = new basicEDM($DEFAULT_ERP);
    $aReleasedController = new tldReleasedController();
    foreach($rows as &$row){
    	$row['t_item'] = rtrim($row['t_item']);
    	$file = $edm->getFilenameByPNDate($row['t_item'], $date);
    	if($file AND $aReleasedController->fileExistsInVault($DEFAULT_ERP, $file)){
    		$row['drawing'] =<<<EOF
    		<a href="/en/private/manufacturing/eng/dev.php?m[0]=getfile&m[1]=drawing&erp={$DEFAULT_ERP}&item={$row['t_item']}&date={$date}">
			<img src="/shared/bluesphere/16x16/actions/filesaveas.png" alt="Save file to your hard disk"></a>
EOF;
			$row['revision'] = $edm->getCurrentRevLevel($row['t_item']);
    	}else{
    		$row['drawing'] = '';
    		$row['revision'] = '';
    	}
    }
    $report = new tldReportColumnar(
        $rows,
        [
            "xItems" => [
                "t_orno" => "PO# MRP",
                "t_suno" => "SUNO#",
                "t_podt" => "Planned Order Date",
                "t_pddt" => "Planned Delivery Date",
                "t_item" => "PN#",
                "drawing" => "Drawing",
                "revision" => "Cur Rev",
                "vendor_pn" => "Vendor PN#",
                "t_oqan" => "Qty",
                "t_dsca" => "PN Desc",
                "byr_email" => "Buyer",
                "t_cwar" => "Code warehouse",
                "t_csig" => "Signal Code",
            ],
            "title" => $caption,
            "showNumberOfRows" => TRUE,
            "links" => [
                "t_item" => [
                    "url" => "/en/private/parts/parts.php?m[0]=inv&m[1]=view&erp=$DEFAULT_ERP",
                    "params" => ['id' => 't_item'],
                    "target" => "_blank"
                ]
            ]
        ]
    );
    return $report->fetch();
}
?>