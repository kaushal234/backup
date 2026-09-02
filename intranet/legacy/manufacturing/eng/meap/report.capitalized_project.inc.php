<?php
require_once 'PHPExcel.php';
require_once 'PHPExcel/Writer/Excel2007.php';

// Form
$DEFAULT_TITLE .= "\Capitalized Project";
$form = new HTML_QuickForm('frmReport', 'post');
$form->addElement(	'header', 'title', 'Select Factory');
$form->addElement(	'hidden', 'm[0]', 'meap');
$form->addElement(	'hidden', 'm[1]', 'capitalized_project');
$form->addElement('select', 'factory', 'Business Unit', ["" => "", "ALL" => "ALL"] + tldLocation::getFactoryList('smartyOptionsIDLocation'));
$form->addElement(	'checkbox', 'download', 'Download ?');
$form->addElement(	'submit', 'btnSubmit', ' GO ');
$form->addRule('factory', 'This is required', 'required');
$body .= $form->toHTML();

if ($form->validate()){
	$vars = $form->exportValues();
	$download = (bool)$vars['download'];
	// Get MEAPs
	$con = array();
	$con['meap.econ_capitalized'] = 'Y';
	if ($vars['factory'] !== 'ALL') {
        $con['erp.id'] = (int)$vars['factory'];
    }
	$meaps = tldMEAP::byConstraints($con);
	if (!count($meaps))
	{
		$DEFAULT_ERROR[] = 'No results found!';
		return;
	}
	if (!$download) {
        ob_start();
    }
	// Build report header
	for ($months=array(),$start=new DateTime('first day of 12 months ago'),$i=0;$i<12;$i++,$start->add(new DateInterval('P1M')))
	{
		$months[$start->format('Y-m')] = $start->format('M y');
	}
	if ($download)
	{
		// Xls
    	$objPHPExcel = new PHPExcel();
    	$objPHPExcel->setActiveSheetIndex(0);
    	$objPHPExcel->getActiveSheet()->setTitle('Report'.(($vars['factory'] !== 'ALL')?" for {$meaps[0]['factory_fullname']}":''));

    	// Titles
    	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(0, 1, 'BU'); // A
    	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(1, 1, 'MEAP #'); // B
    	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(2, 1, 'Status'); // C
    	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(3, 1, 'Short Description'); // D
    	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(4, 1, 'Link to Goal Sheet'); // E
    	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(5, 1, 'Last 12 months Engineering hours data (End of month cumulated hours)'); // F

    	for ($colid = 5, $col = 'F', $mvalues = array_values($months), $i = 0; $i < count($mvalues); $i++, $colid++, $col++)
    	{
    		$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($colid, 2, $mvalues[$i]);
    		$lastcol = $col;
    	}

    	// Merge
    	$objPHPExcel->getActiveSheet()->mergeCells('A1:A2');
    	$objPHPExcel->getActiveSheet()->mergeCells('B1:B2');
    	$objPHPExcel->getActiveSheet()->mergeCells('C1:C2');
    	$objPHPExcel->getActiveSheet()->mergeCells('D1:D2');
    	$objPHPExcel->getActiveSheet()->mergeCells('E1:E2');
    	$objPHPExcel->getActiveSheet()->mergeCells("F1:{$lastcol}1");

    	// Style
		$styleArray = array(
			'font' => array('bold' => true),
			'alignment' => array('wrap' => true, 'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER, 'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER),
			'borders' => array('bottom' => array('style' => PHPExcel_Style_Border::BORDER_THIN)),
			'fill' => array('type' => PHPExcel_Style_Fill::FILL_SOLID, 'color' => array('rgb' => 'FFFF00'))
		);

    	$objPHPExcel->getActiveSheet()->getStyle("A1:{$lastcol}2")->applyFromArray($styleArray);
    	$objPHPExcel->getActiveSheet()->freezePane('A3');
	}
	else
	{
		// Html
		$smarty->assign("width", "100%");
?>
		<style type="text/css">
		._report thead tr th {
			background: #2971a8;
			color: white;
		}
		</style>
		<h3>Capitalized Project Report<?php if($vars['factory'] !== 'ALL') {
                echo " for {$meaps[0]['factory_fullname']}";
            } ?></h3>
		<table class="_report" cellpadding="3">
			<thead>
				<tr>
					<th rowspan="2">BU</th>
					<th rowspan="2">MEAP #</th>
					<th rowspan="2">Status</th>
					<th rowspan="2">Short Description</th>
					<th rowspan="2">Link to Goal Sheet</th>
					<th colspan="<?php echo count($months);?>">Last 12 months Engineering hours data (End of month cumulated hours)</th>
				</tr>
				<tr>
					<?php foreach($months as $month):?>
					<th><?php echo $month;?></th>
					<?php endforeach;?>
				</tr>
			</thead>
			<tbody>
<?php
	}
	// Build report data
	foreach ($meaps as $k => $meap)
	{
		if ($download)
		{
			// Xls
	    	$meap = array_map('trim', $meap);
	    	$offset = $k + 3;

	    	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(0, $offset, $meap['factory_fullname']);
	    	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(1, $offset, $meap['id']);
    		$objPHPExcel->getActiveSheet()->getCellByColumnAndRow(1, $offset)->getHyperlink()->setUrl("http://{$_SERVER['HTTP_HOST']}/en/private/manufacturing/eng/dev.php?m[0]=meap&m[1]=view&id={$meap['id']}");
	    	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(2, $offset, $meap['status']);
	    	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(3, $offset, $meap['short_desc']);
	    	if ($f = _getGSLink($meap))
	    	{
		    	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(4, $offset, $f['filename']);
	    		$objPHPExcel->getActiveSheet()->getCellByColumnAndRow(4, $offset)->getHyperlink()->setUrl("http://{$_SERVER['HTTP_HOST']}/en/private/common/index.php?m[0]=files&m[1]=view&m[2]=out&id={$f['id']}");
	    	}
	    	else
	    	{
		    	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(4, $offset, '');
	    	}
	    	foreach(array_keys($months) as $k => $dt)
	    	{
	    		$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($k + 5, $offset, _getDHValue($meap, $dt));
	    	}
		}
		else
		{
			// Html
?>
				<tr bgcolor="<?php echo ($k % 2 === 0) ? '#eeeeee' : '#d0d0d0';?>">
					<td><?php echo $meap['factory_fullname'];?></td>
					<td><a href="<?php echo $php_self;?>?m[0]=meap&m[1]=view&id=<?php echo $meap['id'];?>"><?php echo $meap['id'];?></a></td>
					<td><?php echo $meap['status'];?></td>
					<td><?php echo htmlentities($meap['short_desc']);?></td>
					<td><?php echo _getGSLink($meap);?></td>
					<?php foreach(array_keys($months) as $dt):?>
					<td><?php echo _getDHValue($meap, $dt);?></td>
					<?php endforeach;?>
				</tr>
<?php
		}
	}
	// Build report footer
	if ($download)
	{
		// Xls
    	$objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(11);
    	$objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(9);
    	$objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(11);
    	$objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(75);
    	$objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(50);
    	for ($col = 'F', $i = 0; $i < count($mvalues); $i++, $col++)
    	{
    		$objPHPExcel->getActiveSheet()->getColumnDimension($col)->setWidth(11);
    	}

		// Alignment
		$styleArray = array(
			'alignment' => array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER)
		);

		$objPHPExcel->getActiveSheet()->getStyle("A3:C{$offset}")->applyFromArray($styleArray);
		$objPHPExcel->getActiveSheet()->getStyle("F3:{$lastcol}{$offset}")->applyFromArray($styleArray);

		// Underline hyperlinks
		$styleArray = array(
			'font' => array('underline' => PHPExcel_Style_Font::UNDERLINE_SINGLE, 'color' => array('rgb' => '0000FF'))
		);

    	$objPHPExcel->getActiveSheet()->getStyle("B3:B{$offset}")->applyFromArray($styleArray);
    	$objPHPExcel->getActiveSheet()->getStyle("E3:E{$offset}")->applyFromArray($styleArray);
	}
	else
	{
		// Html
?>
			</tbody>
		</table>
<?php
	}
	// Display report
	if ($download)
	{
		// Xls
    	$objWriter = new PHPExcel_Writer_Excel2007($objPHPExcel);
    	header('Content-Disposition: attachment; filename="Capitalized Project Report'.(($vars['factory'] !== 'ALL')?" for {$meaps[0]['factory_fullname']}":'').'.xlsx"');
    	$objWriter->save('php://output');
    	exit;
	}
	else
	{
		// Html
		$body .= ob_get_contents();
		ob_end_clean();
	}
}


// Functions
function _getGSLink($meap){
	global $download;
	$gs = tldModFile::byParent($meap['id'], 'MEAP', 2);
	if(empty($gs)) {
        return '';
    }
	$f = end($gs);
	if ($download) {
		// Xls
		return $f;
	}
	else 	{
		// Html
		$f = array_map('htmlentities', $f);
		return "<a href=\"/en/private/common/index.php?m[0]=files&m[1]=view&m[2]=out&id={$f['id']}\" title=\"Download file: {$f['filename']}\"><img src=\"/shared/bluesphere/16x16/actions/filesave.png\" /></a>";
	}
}

function _getDHValue($meap, $dt){
	$query = "
	SELECT archived_value
	FROM meap_archive
	WHERE
		parent_id={$meap['id']} AND
		archive_type='econ_actual_dh' AND
		archive_dt LIKE '{$dt}%'
	
	";
	$dh = tldUtils::getSqlRowToAssocArray($query);
	if (!is_numeric($dh['archived_value'])) {
        return 'N/A';
    }
	return $dh['archived_value'];
}

