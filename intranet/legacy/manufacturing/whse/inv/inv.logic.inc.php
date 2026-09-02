<?php
include_once('publications.inc.php');
include_once('vault.inc.php');
include_once('erp.others.inc.php');
include_once('erp.inc.php');
include_once('quality.inc.php');
include_once('forms_and_reports.inc.php');
$JS_INCLUDE=array("/shared/javascript/overlib/overlib.js");

switch($m[1]){
	case 'byPNPlanned':
		$form = new HTML_QuickForm('frmInventory', 'get');
		$form->addElement(	'hidden', 'm[0]', 'inv');
		$form->addElement(	'hidden', 'm[1]', 'byPNPlanned');
		$form->addElement(	'header', 'title', "Inventory/PN Information");
		$form->addElement(	'text',   'id',    'Part Number', array("size"=>12));
		$form->addElement(	'select', 'erp',   'Company', tldLocation::getWarehouseList("smartyOptions")+array("510"=>"TLD DTV"));
        $form->addElement('submit', 'btnSubmit', 'Submit');
        $form->setDefaults(["erp" => $DEFAULT_ERP]);
        if(!$form->validate()){
	        $body = $form->toHTML();
	        break;
	    }
		$vars = tldUtils::cleanupFormInput($form->exportValues());
		$erp = $vars['erp'];
		$id = strtoupper($vars['id']);
 		$body = _viewGeneralTab($erp, $id);
 	break;
 	case 'byPNTransaction':
 		$form = new HTML_QuickForm('frmInventory', 'post');
 		$form->addElement(	'hidden', 'm[0]', 'inv');
 		$form->addElement(	'hidden', 'm[1]', 'byPNTransaction');
 		$form->addElement(	'header', 'title', "Past inventory transactions by item");
 		$form->addElement(	'text',   'id',    'Part Number', array("size"=>12));
        $form->addElement('select', 'erp', 'Company', tldLocation::getWarehouseList("smartyOptions") + ["510" => "TLD DTV"]);
        $form->addElement('submit', 'btnSubmit', 'Submit');
        $form->setDefaults(["erp" => $DEFAULT_ERP]);
        if (!$form->validate()){
 			$body = $form->toHTML();
 			break;
 		}
 		$vars = tldUtils::cleanupFormInput($form->exportValues());
 		$erp = $vars['erp'];
 		$id = strtoupper($vars['id']);
 		$body = _viewGeneralTab1($erp, $id,$DEFAULT_ERP);

 	break;
 	case 'byLocationTransaction':
 		$user = new tldUser($GLOBALS["PHP_AUTH_USER"]);
 		if(!isset($erp)){
 		    $erp = tldLocation::getERPByID($user->getBUID());
 		}
 		$warehouseList = array_column(tldCWAR::byERP($erp), 't_cwar', 't_cwar');
 		$form = new HTML_QuickForm('frmInventory', 'post');
 		$form->addElement(	'hidden', 'm[0]', 		'inv');
 		$form->addElement(	'hidden', 'm[1]', 		'byLocationTransaction');
 		$form->addElement(	'hidden', 'erp', 		$erp);
 		$form->addElement(	'header', 'title', 		"Past inventory transaction by location - maximum 1000 records");
 		$form->addElement(	'text',   'id',    		'Part Number',   array("size"=>12));
 		$form->addElement(	'select', 'warehouse',  'Warehouse', 	 array('ALL'=>'ALL')+$warehouseList);
 		$form->addElement(	'text',   'order',  	'Order#',		 array("size"=>12));
		$form->addElement(	'select', 'range',  	'Date Range', 	 ['1'=>'ALL', '0'=>'Past 12 months']);
 		$form->addElement(	'submit', 'btnSubmit', 'Submit');
 		if(!$form->validate()){
 			$body = $form->toHTML();
 			break;
 		}
 		$vars = tldUtils::cleanupFormInput($form->exportValues());
 		$id = strtoupper($vars['id']);
 		$warehouse =$vars['warehouse'];
 		$order = $vars['order'];
		$range = $vars['range'];
 		$body = _viewGeneralTab2($erp, $id, $warehouse, $order, $range);
		$body .= <<<EOF
<br><h3><a href="/en/private/manufacturing/whse/dev.php?m[0]=reports&m[1]=byPNTransaction&erp=$erp&id=$id&btnSubmit=Submit" target=_blank>
Planned inventory movement for PN# $id
</a></h3>

EOF;
 	break;
 	case 'outboundreport':
 		$erpList = tldLocation::getERPList("smartyOptions");
 		$yesNo = array("No", "Yes");
 		$query.="select distinct w_koor2 from [tld].[dbo].[Shortages] order by w_koor2 asc";
 		$rowsKoor = tldUtils::getSqlToAssocArray($query, 'odbc', ['src' => 'baan']);
 		$rowsKoor2[]="ALL";
 		foreach ($rowsKoor as $key1 => $value1) {
 			$rowsKoor2[]=$rowsKoor[$key1]['w_koor2'];
 		}
 		$form = new HTML_QuickForm('frmByNum', 'post');
 		$form->addElement(  'hidden', 'm[0]', 'inv');
 		$form->addElement(  'hidden', 'm[1]', 'outboundreport');
 		$form->addElement(  'header', 'title', 'Outbound Report');
 		$form->addElement(	'select', 	'z',	'Company#', array(""=>"")+$erpList);
 		$form->addElement(	'date', 	'start',	'From',
 				array("format"=>"Y-m-d","minYear"=>date('Y'),"maxYear"=>date('Y')));
 		$form->addElement(	'date', 	'end',	'To',
 				array("format"=>"Y-m-d","minYear"=>date('Y'),"maxYear"=>date('Y')));
 		$form->addElement(	'select', 'ordertype', 'Order Type', array_combine($rowsKoor2, $rowsKoor2));
 		$form->addElement(	'text', 'orderno', 'Order Number');
 		$form->addElement(	'text', 'runno', 'Run Number');
 		$form->addElement(	'text', 'item', 'Item');
 		$form->addElement(	'select', 'Xl', 'Excel file', array_combine($yesNo, $yesNo));
 		$form->addElement(  'submit', 'btnSubmit', 'Submit');
 		$form->setDefaults(
 				array(
 						'start'=>array("d"=>date("d"),"m"=>date("m"),"Y"=>date("Y")),
 						'end'=>array("d"=>date("d"),"m"=>date("m"),"Y"=>date("Y"))
 				)
 		);
 		$location = new tldLocation($user->getBUID());
 		$form->setDefaults(array("z"=>$location->getERP()));
 		// Set Rules
 		$required = array("z");
 		foreach($required as $field) $form->addRule($field,"Field required","required");
 		if(!$form->validate()){
 			$body = $form->toHTML();
 			break;
 		}
 		$vars = tldUtils::cleanupFormInput($form->exportValues());
 		// Check dates
 		$start = new DateTime(implode("-",$vars['start']));
        $end = new DateTime(implode("-",$vars['end']));
 		if($start>$end){
 			$DEFAULT_ERROR[]="ERROR: Start date can not be greater than the end date";
 			$body = $form->toHTML();
 			break;
 		}

 		$query="SELECT ";
 		$query.="SUBSTRING(convert(varchar, tdinv700.t_trdt, 120), 0, 11) t_trdt, ";
 		$query.="case  tdinv700.t_koor
WHEN 1 THEN 'Production Order'
WHEN 2 THEN 'Purchase Order'
WHEN 3 THEN 'Sales Order'
WHEN 4 THEN 'MPS Production Order'
WHEN 5 THEN 'MPS Purchase Order'
WHEN 6 THEN 'MRP Production Order'
WHEN 7 THEN 'MRP Purchase Order'
WHEN 8 THEN 'PRP Production Order'
WHEN 9 THEN 'PRP Purchase Order'
WHEN 10 THEN 'PRP Warehouse Order'
WHEN 11 THEN 'MRP Sales Forecast'
WHEN 12 THEN 'MPS Rough Material Req.'
WHEN 13 THEN 'Sales Quotation'
WHEN 14 THEN 'Purchase Contract'
WHEN 15 THEN 'Sales Contract'
WHEN 16 THEN 'Warehouse Order'
WHEN 17 THEN 'Service Order'
WHEN 18 THEN 'PRP Purchase Order (TP)'
WHEN 19 THEN 'PRP Warehouse Order (TP)'
WHEN 20 THEN 'ISM Warehouse Order (TP)'
WHEN 21 THEN 'Replenishment Order'
WHEN 22 THEN 'DRP Replenishment Order'
WHEN 23 THEN 'DRP Sales Forecast'
WHEN 24 THEN 'ECO Orders'
WHEN 25 THEN 'MPS Interplant Order'
WHEN 26 THEN 'Production Batch'
WHEN 27 THEN 'Warehouse Order'
WHEN 28 THEN 'MPS Production Batch'
WHEN 29 THEN 'MRP Production Batch'
END t_koor, ";
 		$query.="tdinv700.t_orno t_orno, ";
 		$query.="tdinv700.t_pono t_pono, ";
 		$query.="tdinv700.t_item t_item,  ";
 		$query.="tdinv700.t_quan t_quan, ";
 		$query.="tdinv700.t_sern t_sern, ";
 		$query.="tdinv700.t_cwar t_cwar, ";
 		$query.="tdinv700.t_cprj t_cprj, ";
 		$query.="tdinv700.t_stoc t_stoc, ";
 		$query.="tdinv700.t_logn t_logn,  ";
 		$query.="tdinv700.t_kost t_kost, ";
 		$query.="tdilc401.t_runn t_runn ";
 		$query.="from ttdinv700{$vars['z']}  tdinv700 ";
 		$query.="LEFT JOIN ttdilc401{$vars['z']} tdilc401 ON tdilc401.t_orno=tdinv700.t_orno and tdilc401.t_pono=tdinv700.t_pono and tdilc401.t_cprj=tdinv700.t_cprj where 1=1";
 		if ($vars['orderno']<>''){$query.=" and tdinv700.t_orno = '{$vars['orderno']}' " ;}
		if ($vars['ordertype']!='ALL') {
			switch(trim($vars['ordertype'])){
		  	case 'PRP Production Order':
		  		$query.=" and tdinv700.t_koor = 8  ";
		  	break;
		  	case 'Production Order':
		  		$query.=" and tdinv700.t_koor = 1  ";
		  	break;
	  		case 'Purchase Order':
	  			$query.=" and tdinv700.t_koor = 2  ";
	  	    break;
  			case 'Sales Order':
  				$query.=" and tdinv700.t_koor = 3  ";
  			break;
  			case 'Replenishment Order':
  				$query.=" and tdinv700.t_koor = 21  ";
  			break;
  			case 'Warehouse Order':
  				$query.=" and tdinv700.t_koor = 27  ";
  			break;
  			case 'Sales Quotation':
  				$query.=" and tdinv700.t_koor = 13  ";
  			break;
		  }

		}
		$d_start = $start->format('Y-m-d');
		$d_end = $end->format('Y-m-d');
  		if ($vars['runno']<>''){ $query.=" and tdilc401.t_runn = '{$vars['runno']}' " ;}
 		if ($vars['item']<>''){ $query.=" and tdinv700.t_item = '{$vars['item']}' ";		}
 		$query.=" and tdinv700.t_trdt>='" . $d_start . "' " ;
 		$query.=" and tdinv700.t_trdt<='" . $d_end . "' " ;
 		$query.=" ORDER BY tdinv700.t_trdt DESC";

 		$rows = tldUtils::getSqlToAssocArray($query, 'odbc', ['src' => 'baan']);
 		$report = new tldReportColumnar(
 				$rows,
 				array(
 						"xItems"=>array(
 								"t_trdt"	=>"Date",
 								"t_item"	=>"Item",
 								"t_koor"   	=>"Order Type",
 								"t_orno"	=>"Order#",
 								"t_pono"	=>"Position#",
 								"t_sern" 	=>"SEQ#",
 								"t_cprj" 	=>"Project#",
 								"t_quan"	=>"QTY",
 								"t_cwar"	=>"Warehouse",
 								"t_stoc"	=>"Stock",
 								"t_logn"	=>"Login" ,
 								"t_runn" 	=>"Run number"
 						),
 						"title"=>"Outbound Report",
 						""
 				)
 		);

 		// Specific csv request from forms field
 		if($vars['Xl']=='Yes') { $output = 'xls';}
 		// Save the rows into the session
 		if(count($rows)){
 			$sess["outbound"]["list"] = $rows;

 		switch($output){
 			case 'xls':
 				$report = new tldXLS(
 					$sess["outbound"]["list"],
 					array(
 						"xItems"=>array(
 							"t_trdt"	=>"Date",
 							"t_item"	=>"Item",
 							"t_koor"   	=>"Order Type",
 							"t_orno"	=>"Order#",
 							"t_pono"	=>"Position#",
 							"t_sern" 	=>"SEQ#",
 							"t_cprj" 	=>"Project#",
 							"t_quan"	=>"QTY",
 							"t_cwar"	=>"Warehouse",
 							"t_stoc"	=>"Stock",
 							"t_logn"	=>"Login" ,
 							"t_runn" 	=>"Run number"
 					),
 					"showTitles"=>true
 					)
 				);
 				$report->out("outbound_list.xls");
 				exit;
 			}
 		}
 		$body = $report->fetch();

 	break;
}

function _viewGeneralTab($erp, $id){

    $cells = array();
    $TXTA = array();
    $invs = $erp;

    // get languages
    $_langs = array();
    $query=<<<EOF
    SELECT t_clan, RTRIM(t_dsca) AS t_dsca
    FROM tttaad110000
EOF;
    $res = tldUtils::getSqlToAssocArray($query, 'odbc', ['src' => 'baan']);
    foreach($res AS $r){
    	$_langs[$r['t_clan']] = $r['t_dsca'];
    }
   	$myerp = tldERP::getERPOb($invs);
   	$header = empty($myerp) ? [] : $myerp->getItemData($id);

    	// Get text
    $txta = array();

    	// Source ITM
    $a = $t = array();
    $rows = empty($myerp) ? [] : tldBaanERP::getTXT($header['t_txta']);
    foreach($rows AS $row){
    	if(!in_array($row['t_text'], (array)$t[$row['t_clan']])){
	    		// Fix for Task#407321 to drop french text in asian companies
	    	if(in_array($inv,array(600,620,640,660,680)) AND $row['t_clan']==4)continue;
	    		// End fix
    		$a[$row['t_clan']] .= $row['t_text'];
    		$t[$row['t_clan']][] = $row['t_text'];
    	}
    }
    foreach($a AS $k => &$v) $v = "<em><u>{$_langs[$k]}</u>:</em> "._cleanTXT($v);
    $str = trim(implode('<hr/>', array_unique($a)));
    if(!empty($str)){
    	$txta[] = array_merge($rows[0],array('SRC'=>'ITM','TXT'=>$str,'ERP'=>$inv));
    }
   	foreach($txta AS $a){
    	$TXTA[$a['t_ctxt']] = $a;
    }
	if(!empty($header)){
			// Get inventory data
        $rows = $myerp->getInvData($id);
        $edmbom[] = $rows['ERP']+$rows['ITM'];
        if(count($rows)){
            foreach($rows as $row){
                $row['wh_sfst'] = $row['sfst'];
                $lines[] = $header + $row;
            }
        }else{
                $lines[] = $header;
        }
    }
    $TXTA = array_values($TXTA);

	$end =date('Y-m-d');
    $start = date('Y-m-d',time()-180*3600*24);
    $cells[2] = <<<EOF
    <br/><br/><br/><br/><br/><br/><h2><div style='background: lightcoral; padding: 0 5px 0 5px;'><div>TRANSACTIONS (BAAN)</div><div style='font-style: italic;font-size: 10px'>in Infor LN 'Inventory 360'</div></div></h2><br>

EOF;
    $cells[2] .= <<<EOF
<br><h3><a href="/en/private/manufacturing/whse/dev.php?m[0]=reports&m[1]=byPNTransaction&erp=$erp&id=$id&btnSubmit=Submit" target=_blank>
Planned inventory movement for PN# $id
</a></h3>

EOF;
    $cells[2] .= <<<EOF
<h3><a href="/en/private/manufacturing/whse/dev.php?m[0]=reports&m[1]=byLocationTransaction&erp=$erp&id=$id&btnSubmit=Submit" target=_blank>
    Past inventory transaction for PN# $id in past 12 months
    </a></h3>
EOF;
	$cells[2] .= <<<EOF
<h3><a href="/en/private/manufacturing/whse/dev.php?m[0]=reports&m[1]=byLocationTransaction&erp=$erp&id=$id&range=1&btnSubmit=Submit" target=_blank>
    Past inventory transaction for PN# $id full history data
    </a></h3>
EOF;
    $cells[2] .= _Receipt(tldPOL::byReceiptsPeriodByItem($erp,$start,$end,$id),"Receipt for PN# $id");
	$num = tldISR::countByPNByBuToStatus(tldLocation::getIDByERP($erp),$id);
    $cells[2] .= <<<EOF
    <br/><br/><div style='background: lightcoral; padding: 0 5px 0 5px;'><div><b>Transit QTY from ISR: </b> (BAAN)</div><div style='font-style: italic;font-size: 10px'>ISR are replace by LN load</div></div>&nbsp;&nbsp;$num<br>
EOF;
    $query=<<<EOF
SELECT TOP 5 sfc001.t_pdno pdno, sfc001.t_cprj cprj, sfc001.t_mitm mitm, sfc001.t_cwar cwar,
CASE substring(convert(varchar, sfc001.t_prdt, 120), 1, 10) WHEN '1753-01-01' THEN '' ELSE substring(convert(varchar, sfc001.t_prdt, 120), 1, 10) END prdt,
CASE substring(convert(varchar, sfc001.t_dldt, 120), 1, 10) WHEN '1753-01-01' THEN '' ELSE substring(convert(varchar, sfc001.t_dldt, 120), 1, 10) END dldt,
sfc001.t_qrdr qrdr, sfc001.t_qdlv qdlv
FROM ttisfc001{$erp} sfc001
WHERE sfc001.t_mitm='$id'
ORDER BY sfc001.t_prdt DESC
EOF;

    $rows = tldUtils::getSqlToAssocArray($query, 'odbc', ['src' => 'baan']);
    $caption = "WOs for PN #{$id} for company {$erp}";
    if(!count($rows)==0){
        $report = new tldReportColumnar($rows,
        	array(	"xItems" => array(
        						"pdno"=>"Production Order#",
        						"cprj"=>"Project#",
        						"tcwar"=>"Warehouse",
        						"prdt"=>"Production start date",
        						"dldt"=>"Delivery Date",
        						"qrdr"=>"Quantity Ordered",
        						"qdlv"=>"Quantity Delivered",
        				),
        				"",
        				"title"=>$caption
        		)
        );
        $cells[2] .= $report->fetch();
    }else{
        $cells[2] .= <<<EOF
        						<h3>No WO for PN# $id</h3>
EOF;
    }

$buid= tldLocation::getIDByERP($erp);
$query=<<<EOF
SELECT tasks.*,tpl.short_desc AS template
FROM tasks
LEFT JOIN cal_seq_tpl AS tpl ON tasks.tplno=tpl.id
WHERE task like '%$id%' AND bu_id = $buid AND module like 'SEQ' AND tplno in (27,28,29,58,59,61,62,63,64,65,66,67,68)
ORDER BY date DESC
LIMIT 2
EOF;

$rows = tldUtils::getSqlToAssocArray($query);
$caption = "Last 2 inventory SEQ for PN #$id";
if(!count($rows)==0) {

    $report = new tldReportColumnar($rows,
        ["xItems" => [
            "id" => "SEQ#",
            "template"=>"SEQ Template Name",
            "date" => "Date",
        ],
            "",
            "title" => $caption,
            "links"=> ["id" => "/en/private/calendar/calendar.php?m[0]=tasks&m[1]=task&m[2]=view&id="]
        ]
    );
    $cells[2] .= $report->fetch();
}
    // Link to sequence
    $sequence =<<<EOF
    <h2>Manufacturing\Engineering\EPR#$erp\Search new Part Number Sequence</h2><br>
EOF;

    // Get data
    $rows = tldTask::byPN($id, 20);

    $report = new tldReportColumnar(
        $rows,
		[
			'xItems' => [
				'id' => 'Task/SEQ#',
				'module' => 'Module',
				'parent_id' => 'Ref#',
				'pn' => 'PN#',
				'status' => 'Status',
				'date' => 'Date Opened',
				'dt_closed' => 'Date Closed',
				'due_date' => 'Due Date',
				'assignor_fullname' => 'Assignor',
				'assignee_fullname' => 'Assignee',
				'task' => 'Task',
			],
			'title' => "New SEQ with #PN$id",
			'links' => [
				'id' => '/en/private/calendar/calendar.php?m[0]=tasks&m[1]=task&m[2]=view&id='
			]
		]
    );
    $sequence .= $report->fetch();

    $report = new tldHTMLTable(
        $sequence,
        [
            "attribs"=>["table"=>" width='100%'","tr"=>" bgcolor='#FFFFFF'"]
        ]
    );

    $cells[2] .= $report->fetch();

    global $smarty;
        // Include jqzoominc lib to have the zoom on pictures
    $smarty->assign("html_head", $smarty->fetch(dirname(__FILE__) . "/jqzoominc.inv.tpl"));
        // Get Photo Vault Controller
    $photoController = new tldPhotoController();
        // Get single Photo Vault Controller
    $filePath = $photoController->fileExistsInVault($erp,"$id.JPG");
    $imgParams=array();
    if(!empty($filePath)){
     	$imgParams[]=array(
        	'link'=>array(
	        	'm'=>array(0=>'getfile',1=>'photo'),
	        	'erp'=>$erp,
	        	'item'=>$id,
        	),
        	'img'=>array(
	        	'm'=>array(0=>'getfile',1=>'photo'),
	        	'erp'=>$erp,
	        	'item'=>$id,
	        	'new_width'=>256,
        	),
    	);
    	$IMG=<<<EOF
<a href="/en/private/manufacturing/eng/dev.php?m[0]=getfile&m[1]=photo&erp=$erp&item=$id" class="jqzoom" style="" title="">
<img src="/en/private/manufacturing/eng/dev.php?m[0]=getfile&m[1]=photo&erp=$erp&new_width=256&item=$id&new_width=256" title="" align="right">
</a>
EOF;
	}
        // If the PN folder exists, add multiple PN pictures
    $folderpath = $photoController->fileExistsInVault($erp,$id);
    $lines =true;
    if(!empty($folderpath)){
        	// Get list of the other files in vault
    $files = $photoController->getDirFilelist($erp, $folderpath);
        	// retrieve all picture files to display it
    foreach($files as $key=>$file){
        if(strtolower($file)=='thumbs.db') continue;
        $imgParams[]=array(
        	'link'=>array(
	        	'm'=>array(0=>'getfile',1=>'photo'),
	        	'erp'=>$erp,
	        	'item'=>$id,
	        	'key'=>$key,
        		),
        	'img'=>array(
	        	'm'=>array(0=>'getfile',1=>'photo'),
        		'erp'=>$erp,
        		'item'=>$id,
        		'key'=>$key,
        		'new_width'=>128,
        		),
        	);
    	}
	 }

     $imgHtml = '';
     foreach($imgParams AS $img){
        $imgHtml .= '<a href="/en/private/manufacturing/eng/dev.php?'.http_build_query($img['link']).'" class="jqzoom"><img src="/en/private/manufacturing/eng/dev.php?'.http_build_query($img['img']).'" alt="" align="right" /></a>' . "\n";
     }
     $linkHtml = '';
     foreach($imgParams AS $key=>$img){
        $linkHtml .= '<br/><a href="/en/private/manufacturing/eng/dev.php?'.http_build_query($img['link']).'" target="_blank">Open Image #'.($key+1).' In New Window</a>' . "\n";
        $lines =false;
     }

     $cells[1] = <<<EOF
<table width="100%">
  <tr>
  	<td>$imgHtml</td>
  </tr>
  <tr>
  	<td>$linkHtml</td>
  </tr>
</table>
EOF;
    if ($lines) {
        $cells[1] .= <<<EOF
     	<br/><br/><br/><br/><br/><br/>
EOF;
    }

    $cells[1] .= <<<EOF
    <h2><div style='background: lightcoral; padding: 0 5px 0 5px;'><div>USAGE (BAAN)</div><div style='font-style: italic;font-size: 10px'>in Infor LN 'Inventory 360'</div></div></h2><br>

EOF;
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
        AND t_item = '$id'
        order by t_year ASC;
SQL;

    $rows = tldUtils::getSqlToAssocArray($query2, "odbc", array("src"=>"baan"));

    $query3 = <<<SQL
select itm.t_cups as 'um' from ttiitm001{$erp} as itm where itm.t_item = '$id';
SQL;

    $um = tldUtils::getSqlRowToAssocArray($query3, "odbc", array("src"=>"baan"));
    $usage['past12months']['t_item'] = $id;
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
        "Statistical Usage for PN# $id / UM: {$um["um"]}",
        [
            "doNotShowTotals"=>TRUE,
            "yItemsRawOrder"=>TRUE,
        ]
        );

	$cells[1] .= $reportUsage->fetch();

    $cells[1] .=<<<EOF
    <br><h2><div style='background: lightcoral; padding: 0 5px 0 5px;'><div>INVENTORY (BAAN)</div><div style='font-style: italic;font-size: 10px'>in Infor LN 'Inventory 360'</div></div></h2><br>

EOF;
    $cells[1] .= <<<EOF
<h3><a href="/en/private/parts/parts.php?m[0]=inv&m[1]=view&id=$id" target="_blank">Stock By company and Warehouse</a></h3> 
EOF;

     // Get data
     $v = array();
     $v[]="itm.t_item='$id'";
     $xItems2 = array(
     		't_item'=>'Item#',
     		't_info'=>'Buyer',
     		'stock_cwar'=>'Warehouse',
     		't_wloc'=>'Location',
     		't_stks'=>'Stock On Hand',
     		'total_copr'=>'Total STD price'
     );
     $baanCompObj = new tldBaanERP($erp);
     $rows2 = $baanCompObj->getWarehouseDataByConstraints(implode(' AND ',$v));
     $report2 = new tldReportColumnar(
     		$rows2,
     		array(
     				"xItems"=>$xItems2,
     				"title"=>"PN# $id By Warehouse By Location"
     		)
    );
    $cells[1] .= $report2->fetch();
    $report = new tldReportColumnar(
     		$TXTA,
     		array(
     				"xItems"=>array(
     						"t_ctxt"	=>"Text#",
     						"dt"		=>"Date",
     						"SRC"		=>"Source",
     						"TXT"		=>"Text"
     				),
     				"title"=>"Text for PN# $id"
     		)
    );
    $cells[1] .= $report->fetch();
    $transitionqty=tldPOL::countDeliveryStatsByConstraints($erp,array("T1.t_item"=>$id));
    $transqty = $transitionqty["total_qty_delivery"]-$rows2[0]['t_stoc'];
    $temp = array();
    $temp[] = array_merge(tldPOL::countOpenStatsByItem2($erp,$id),tldPOL::countDeliveryStatsByConstraints($erp,array("T1.t_item"=>$id)),tldPOL::countOrderedByConstraints($erp,array("T1.t_item"=>$id)), array("total_qty_tran"=>$transqty));
    $form = new HTML_QuickForm('frmInventory', 'get');
    $form->addElement(	'hidden', 'm[0]', 'inv');
    $form->addElement(	'hidden', 'm[1]', 'byPNPlanned');
    $form->addElement(	'header', 'title', "Inventory/PN Information");
    $form->addElement(	'text',   'id',    'Part Number', array("size"=>12));
    $form->addElement('select', 'erp', 'Company', tldLocation::getWarehouseList("smartyOptions") + ["510" => "TLD DTV"]);
    $form->addElement('submit', 'btnSubmit', 'Submit');
    $form->setDefaults(["erp" => $DEFAULT_ERP]);
    $cells[0] = $form->toHTML();
    $cells[0] .=<<<EOF
    <h2>PURCHASE</h2><br>
EOF;
    $cells[0] .= _POSummary($temp,"<div style='background: lightcoral; padding: 0 5px 0 5px;'><span>PO Summary for PN #$id (BAAN)</span><span style='font-style: italic;font-size: 10px'>in Infor LN 'Inventory 360'</span></div>",$erp,$id);

	$xitems = [
		't_suno' => 'Supplier Code',
		't_nama' => 'Supplier Name',
		'ALT_DESCRIPTION' => 'Description',
		'rev'=>'Revision',
		't_cwar' => 'Warehouse Code',
		'cwar_fullname' => 'Warehouse',
		'LEAD' => 'Leadtime',
		't_kitm' => 'Item Type',
		't_cpha' => 'Phantom',
		't_citg' => 'Item Group',
		'wh_sfst' => 'SPH Safety Stock',
		't_sfst' => 'Factory Safety Stock',
		't_reop' => 'Reoder Point',
		'stoc' => 'On Hand',
		't_mioq' => 'MOQ',
		't_blck' => 'On Hold',
		't_ordr' => 'On Order',
		't_quot' => 'Quotation For Sales',
		't_allo' => 'Allocated',
		't_ecoq' => 'Economic Order',
		't_ltdt' => 'Last Transaction Date',
		't_lcod' => 'Last Counting Date',
		't_abcc' => 'Classification',
		'STDCCUR' => 'STD Cur',
		'STDCOST' => 'Std Cost',
		"t_ccur" => "Currency",
		"t_prip" => "Current Price",
		't_buyr' => 'Buyer',
		't_csig' => 'Signal Code',
		"t_aitm" => "Alternative Item Information",
	];
	$suno = $header['t_suno'];
	$header['t_suno'] = "<a href='/en/private/manufacturing/pur/dev.php?m[0]=vendors&m[1]=listing&m[2]=byERPSuno&erp=$erp&suno=".$header['t_suno']."' target='_blank'>".$header['t_suno']."</a>";
	$query = "select rtrim(itm011.t_aitm)  FROM ttiitm011$erp AS itm011 where itm011.t_item = '{$header['ITEM']}'";
    $rows = tldUtils::getSqlToAssocArray($query, "odbc", ["src"=>"baan"]);
    foreach($rows AS $val){
    	$val =join(",",$val);
    	$temp_array[] = $val;
	}
	if ($temp_array ?? []){
		$header["t_aitm"] = implode("\n",$temp_array);
	}

    $form = new tldAssocTable(
    		$header,
    		$xitems,
    		[
    				"title"=>"<div style='background: lightcoral; padding: 0 5px 0 5px;'><div>General (BAAN)</div><div style='font-style: italic;font-size: 10px'>in Infor LN 'Inventory 360'</div></div>"
    		]
    );

	$cells[0] .= $form->fetch();


	$queryQty=<<<EOF
SELECT  * FROM ttdpur030$erp WHERE  t_item='$id' AND t_suno='$suno' and t_qanp>0 and (t_tdat = '1753-01-01 00:00:00.000' or t_tdat = '2099-12-31 00:00:00.000' or t_tdat = '2099-12-30 00:00:00.000')
EOF;

	$rowsQty = tldUtils::getSqlToAssocArray($queryQty, "odbc", ["src"=>"baan"]);
	$report = new tldReportColumnar(
		$rowsQty,
		[
			'xItems' => [
				't_qanp' => 'Quantity',
				't_pric' => 'Price',
			],
			'sortable' => 'no',
			"title"=>"<div style='background: lightcoral; padding: 0 5px 0 5px;'><div>Price by Quantity (BAAN)</div><div style='font-style: italic;font-size: 10px'>in Infor LN 'Inventory 360'</div></div>"
		]
	);

	$cells[0] .= $report->fetch();

    $xreflink ='<br/>&nbsp;&nbsp;<u><a href="/en/private/manufacturing/pur/dev.php?m[0]=xref&m[1]=searchByVendorPN" target="_blank">Search by Vendor PN</a></u>';
    $xreflink .='<br/>&nbsp;&nbsp;<u><a href="/en/private/manufacturing/pur/dev.php?m[0]=xref&m[1]=searchByAltPNByTLDPN&erpid='.$erp.'&pn='.$id.'&btnSubmit=Submit" target="_blank">Search by TLD PN (Supplier)</a></u>';
    $xreflink .='<br/>&nbsp;&nbsp;<u><a href="/en/private/manufacturing/pur/dev.php?m[0]=xref&m[1]=searchByAltPNByTLDPNByCustomer&erpid='.$erp.'&pn='.$id.'&btnSubmit=Submit" target="_blank">Search by TLD PN (Customer)</a></u>';
    $xreflink .='<br/>&nbsp;&nbsp;<u><a href="/en/private/manufacturing/pur/dev.php?m[0]=xref&m[1]=searchByCustomerPN" target="_blank">Search by Customer PN</a></u>';
	$xreflink .='<br/>&nbsp;&nbsp;<u><a href="/en/private/manufacturing/pur/dev.php?m[0]=xref&m[1]=searchByXerfDESC&erpid='.$erp.'" target="_blank">Search by Xref Desc</a></u>';
    $cells[0] .= <<<EOF

<h4>$xreflink</h4>

EOF;
    $cells[0] .=<<<EOF
    <h2>ENGINEERING</h2>
EOF;
    // Links to access EDM/BOM
    $edmlink ='<br/><a href="/en/private/manufacturing/eng/dev.php?m[0]=edm&m[1]=view&erp='.$erp.'&item='.$id.'&btnSubmit=Submit" target="_blank">EDM LINK</a>';
    $bomlink ='<br/><a href="/en/private/manufacturing/eng/dev.php?m[0]=bom&m[1]=view&erp='.$erp.'&pn='.$id.'&date='.date('Y-m-d').'&btnSubmit=Submit" target="_blank">BOM LINK</a>';
    $whereusedlink0 ='<br/><a href="/en/private/manufacturing/eng/dev.php?_qf__frmSearchBOMMono=&m[0]=reports&m[1]=listing&m[2]=SearchBOMMono&Item='.$id.'&z='.$erp.'&Activ=N&btnSubmit=Submit" target="_blank">Where-Used (Mono-level BOMs)</a>';
    $whereusedlink ='<br/><a href="/en/private/manufacturing/eng/dev.php?_qf__frmSearchBOMMulti=&m[0]=reports&m[1]=listing&m[2]=SearchBOMMulti&Item='.$id.'&z='.$erp.'&Activ=N&btnSubmit=Submit" target="_blank">Where-Used (Multi-level PBOMs)</a>';
    $whereusedlink2 ='<br/><a href="/en/private/manufacturing/eng/dev.php?_qf__frmSearchWO=&m[0]=reports&m[1]=listing&m[2]=SearchWO&Item='.$id.'&z='.$erp.'&Project_Status=All&btnSubmit=Submit" target="_blank">Where-Used (Work Orders)</a>';
	$user = new tldUser($GLOBALS["PHP_AUTH_USER"]);
    if($user->isInGroup(["gg_ENG", "gg_SUPPORT"])){
		$newpartslink = <<<EOF
		  <br/><br/><a href="/en/private/calendar/calendar.php?m[0]=seq&m[1]=new&m[2]=eng.newpartnb">Start new part number sequence</a></li>
EOF;
	}
    $cells[0] .= <<<EOF


<h3>  $edmlink   $bomlink   $whereusedlink0   $whereusedlink   $whereusedlink2   $newpartslink</h3>

EOF;

    $cells[1] .=<<<EOF
    <h2>QUALITY</h2><br>
EOF;
	global $kernel;
	$router = $kernel->getContainer()->get('router');
	$client = $kernel->getContainer()->get(\ApiBundle\Client::class);

	$arr['partNumbers'] = $id;
	$arr['start'] = date("Y-m-d",strtotime("-24 month"));
	$arr['end'] = date('Y-m-d');
	$cells[1] .= _listWC(tldWC::byConstraintsTopPN($arr), "Last 24 Months WC for PN# $id");
	$cells[1] .= <<<EOF
<h3><a href="/en/private/product_support/index.ps.php?m[0]=wc&m[1]=listing&m[2]=byPN&pn=$id&btnSubmit=Submit" target="_blank">WC Full List</a></h3>
EOF;

	$vwcRoute = $router->generate('vendor_warranty_claim_home', ['partNumber' => $id]);
	$cells[1] .= <<<EOF
<h3><a href="$vwcRoute" target="_blank">VWC Full List</a></h3>
EOF;

	try {
		$response = $client->get('quality/non_conformities', [
			'query' => [
				'parts.partNumber' => $id,
				'order' => ['id' => 'desc'],
				'createdAt' => [
					'after' => (new \DateTime('-6 months'))->format('Y-m-d'),
					'before' => (new \DateTime())->format('Y-m-d'),
				],
			],
		]);
	} catch (\Symfony\Component\HttpClient\Exception\ClientException $e) {
		$response = ['hydra:member' => []];
	}
	$ncrs = array_reduce($response['hydra:member'], static function ($list, $ncr) use ($router) {
		$list[] = [
			'link' => sprintf('<a href="%s" target="_blank">#%s</a>', $router->generate('non_conformity_show', ['id' => $ncr['id']]), $ncr['id']),
			'status' => $ncr['status'],
			'date' => (new \DateTime($ncr['createdAt']))->format('Y-m-d'),
			'shortDescription' => $ncr['shortDescription'],
			'process' => (null !== $ncr['process']) ? mb_strtoupper($ncr['process']['category']) : '',
		];

		return $list;
	});
	$ncrReport = new tldReportColumnar($ncrs, [
		'xItems' => [
			'link' => 'NCR #',
			'date' => 'Date',
			'shortDescription' => 'Description',
			'process' => 'Process',
			'status' => 'Status',
		],
		'title' => sprintf('Last 6 Months NCR for PN# %d', $id),
	]);
	$cells[1] .= $ncrReport->fetch();

	$ncrRoute = $router->generate('non_conformity_home', ['filter_non_conformity[partNumber][value]' => $id]);
	$cells[1] .= <<<EOF
<h3><a href="$ncrRoute" target="_blank">NCR Full List</a></h3>
EOF;
	$cells[1] .= _listPDC(tldPDC::byPartNumber(trim($id)), "PDC for PN# $id");
	$cells[1] .= _listGWF(tldGWF::bySingleKeyword(trim($id)), "GWF full list");

	try {
		$response = $client->get('quality/first_article_qualifications', [
			'query' => [
				'partNumbers.number' => $id,
				'order' => ['createdAt' => 'desc'],
			],
		]);
	} catch (\Symfony\Component\HttpClient\Exception\ClientException $e) {
		$response = ['hydra:member' => []];
	}

	$faqs = array_reduce($response['hydra:member'], static function ($memo, $faq) use ($router, $id) {
		$partNumberData = array_column($faq['partNumbers'], 'revision', 'number');
		$memo[] = [
			'link' => sprintf('<a href="%s" target="_blank">#%s</a>', $router->generate('first_article_qualifications_show', ['id' => $faq['id']]), $faq['id']),
			'factory' => $faq['location']['name'],
			'revision' => $partNumberData[$id] ?? '',
			'supplierName' => $faq['supplierName'],
			'status' => $faq['status'],
		];

		return $memo;
	}, []);

	$faqsReport = new tldReportColumnar($faqs, [
		'xItems' => [
			'link' => 'ID',
			'factory' => 'Factory',
			'revision' => 'Revision',
			'supplierName' => 'Supplier',
			'status' => 'Status',
		],
		'title' => 'FAQ list',
	]);
	$cells[1] .= $faqsReport->fetch();
	$scarRoute = $router->generate('supplier_corrective_action_request_home', ['partNumber' => $id]);
	$cells[1] .= <<<EOF
<h3><a href="$scarRoute" target="_blank">SCAR Full List</a></h3>
EOF;
	$cells[1] .= _listEAP(tldEAP::byPartNumber(trim($id)), "EAP for PN# $id");

	$report = new tldHTMLTable(
		$cells,
		array(
			"cols"=>3,
			"attribs"=>array("table"=>" width='100%'","tr"=>" bgcolor='#FFFFFF'")
		)
	);
	$result.= $report->fetch();

	return $result;
}

function _cleanTXT($string){
	// Clean special char
	$string = str_replace(array('>','<'),'',$string);
	// HTML entities
	$string = htmlspecialchars($string, ENT_COMPAT | ENT_HTML401, 'ISO-8859-1');
	// Clean line return
	$string = str_replace(array("\r\n","\r","\n"),'<br/>',$string);
	// Trim
	$string = trim($string);

	return $string;
}

function _listGWF($rows, $title = "")
{
	$report = new tldReportColumnar($rows,
		["xItems" => ["id" => "GWF#",
			"assignor_fullname" =>"Moderator",
			"dsca" => "Description",
			"dest" =>"Est Completion",
			"status" => "Status"],
			"links" => ["id" => "/en/private/calendar/calendar.php?m[0]=gwf&m[1]=view&id="],
			"title" => $title,
		]
	);

	return $report->fetch();
}

function _listPDC($rows, $title = "")
{
	$report = new tldReportColumnar($rows,
		["xItems" => ["id" => "PDC #",
			"date" => "Date",
			"short_desc" => "Description",
			"model" => "Model",
			"status" => "Status"],
			"links" => ["id" => "/en/private/product_support/index.ps.php?m[0]=pdc&m[1]=view&id="],
			"title" => $title,
		]
	);

	return $report->fetch();
}

function _listEAP($rows, $title = "")
{
	$report = new tldReportColumnar($rows,
		["xItems" => ["id" => "EAP#",
			"dt_opened" => "Date",
			"short_desc" => "Description",
			"status" => "Status"],
			"links" => ["id" => "/en/private/manufacturing/eng/dev.php?m[0]=eap&m[1]=view&id="],
			"title" => $title,
		]
	);

	return $report->fetch();
}
function _listWC($rows, $title=""){
	$report = new tldReportColumnar($rows,
		["xItems"=>[
			"id"=>"WC#",
			"claim_date"=>"Claim Date",
			"model"=>"Model",
			"man_location"=>"Location",
			"problem_desc"=>"Description",
			"warranty_status"=>"Status"],
			"links"=>[
				"id"=>[
					'url'=>"/en/private/product_support/index.ps.php?m[0]=wc&m[1]=view&id=",
					'params'=>['id'=>'id'],
					'target'=>'_blank']
			],
			"title"=>$title
		]
	);
	return $report->fetch();
}

function _POSummary($rows, $title="",$erp,$id){

	$xItems = array(
			"POs"=>"Open PO QTY",
			"total_qty_ordered"=>"Ordered QTY",
			"total_qty_delivery"=>"Delivered QTY",
	        "total_qty_tran"=>"Transaction Qty"
	);
	$report = new tldReportColumnar(
			$rows,
			array("xItems"=>$xItems,
					"",
					"title"=>$title,
					"links"=>array(
							"POs"=>array(
									'url'=>"/en/private/manufacturing/pur/dev.php?m[0]=po&m[1]=listing&m[2]=all&m[3]=byItemByOpen&x=$erp&y=$id",
									'params'=>array('val'=>'POs'),
									'target'=>'_blank'),
							"total_qty_ordered"=>array(
									'url'=>"/en/private/manufacturing/pur/dev.php?m[0]=po&m[1]=listing&m[2]=all&m[3]=byItem&x=$erp&y=$id",
									'params'=>array('val'=>'total_qty_ordered'),
									'target'=>'_blank'),
							"total_qty_delivery"=>array(
									'url'=>"/en/private/manufacturing/pur/dev.php?m[0]=po&m[1]=listing&m[2]=all&m[3]=byItem&x=$erp&y=$id",
									'params'=>array('val'=>'total_qty_delivery'),
									'target'=>'_blank')
					)
			)
	);

	return $report->fetch();
}

function _Receipt($rows, $title=""){
	$i =0;
	foreach($rows AS $key=>$val){
		$i = $i+1;
		$newval[$key] = $val;
		if($i==5) break;
	}
	$report = new tldReportColumnar($newval,
        ["xItems" => [
            "t_date" => "Receipt Date",
            "t_sern" => "SEQ#",
            "t_pono" => "POS#",
            "t_orno" => "Order#",
            "receiptqua" => "Del Qty",
            "t_pric" => "Cost (each)",
            "t_ccur" => "Currency"

        ],
            "",
            "title" => $title
        ]
    );
    return $report->fetch();
}

function _viewGeneralTab1($erp, $id,$DEFAULT_ERP){
	$cells = array();
	$form = new HTML_QuickForm('frmInventory', 'post');
	$form->addElement(	'hidden', 'm[0]', 'inv');
	$form->addElement(	'hidden', 'm[1]', 'byPNTransaction');
	$form->addElement(	'header', 'title', "Planned inventory movements by item");
	$form->addElement(	'text',   'id',    'Part Number', array("size"=>12));
    $form->addElement('select', 'erp', 'Company', tldLocation::getWarehouseList("smartyOptions") + ["510" => "TLD DTV"]);
    $form->addElement('submit', 'btnSubmit', 'Submit');
    $form->setDefaults(["erp" => $DEFAULT_ERP]);
    $cells[0] .= $form->toHTML();
    $myerp = tldERP::getERPOb($erp);
    if(empty($myerp)){
        echo "ERROR: No data for ERP#$erp";
        exit;
    }
    $xItems2 = [
        "t_oqmf" => "QTY order multiple",
        "t_mioq" => "MOQ",
        "t_maoq" => "QTY order max",
        "t_ecoq" => "Economic Order",
        "t_reop" => "Reop",
        "t_oint" => "Order interval",
        "LEAD" => "Leadtime",
        "t_sftm" => "Security lead time",
        "STDCCUR" => "STD Cur",
        "STDCOST" => "STD Cost"
    ];
    $header = $myerp->getItemData($id);
    $form2 = new tldAssocTable(
        $header,
        $xItems2,
        [
            "title" => "General"
        ]
    );
    $cells[0] .= $form2->fetch();

	$query3=<<<EOF
SELECT  * FROM ttdpur030$erp WHERE  t_item='$id' AND t_suno='{$header['t_suno']}' and t_qanp>0 and (t_tdat = '1753-01-01 00:00:00.000' or t_tdat = '2099-12-31 00:00:00.000' or t_tdat = '2099-12-30 00:00:00.000')
EOF;
	$rows3 =tldUtils::getSqlToAssocArray($query3, 'odbc', ['src' => 'baan']);
	$report = new tldReportColumnar(
		$rows3,
		[
			'xItems' => [
				't_qanp' => 'Quantity',
				't_pric' => 'Price',
			],
			'sortable' => 'no',
			"title"=>"Price by Quantity"
		]
	);
	$cells[0] .= $report->fetch();

	$xItems = array(
			"item"=>"Item",
			"dsca"=>"Description",
			"suno"=>"Supplier",
			"nama"=>"Supp name",
			"cotp"=>"Order type",
			"orno"=>"Order",
			"pono"=>"Order line",
			"odat"=>"Order date",
			"qana"=>"Qty",
			"ddta"=>"Planned delivery date",
			"ddtc"=>"Confirmed delivery date",
			"ddtd"=>"Changed delivery date",
			"date"=>"Planned transaction date",
			"namb"=>"Contact",
			"csgp"=>"Pur.stat group",
			"cbrn"=>"Line of Business",
			"jalo"=>"Requirement date",
			"amta"=>"Line amount",
			"t_aitc"=>"Xref"
	);
	$query=<<<EOF
select
inv150.t_item item,
itm001.t_dsca dsca,
pur040.t_suno suno,
com020.t_nama nama,
pur040.t_cotp cotp,
inv150.t_orno orno,
inv150.t_pono pono,
substring(convert(varchar, pur041.t_odat, 120), 1, 10) odat,
inv150.t_qana qana,
CASE substring(convert(varchar, pur041.t_ddta, 120), 1, 10)
        WHEN '1753-01-01' THEN ''
        ELSE substring(convert(varchar, pur041.t_ddta, 120), 1, 10)
    END ddta,
CASE substring(convert(varchar, pur041.t_ddtc, 120), 1, 10)
        WHEN '1753-01-01' THEN ''
        ELSE substring(convert(varchar, pur041.t_ddtc, 120), 1, 10)
    END ddtc,
CASE substring(convert(varchar, pur041.t_ddtd, 120), 1, 10)
        WHEN '1753-01-01' THEN ''
        ELSE substring(convert(varchar, pur041.t_ddtd, 120), 1, 10)
    END ddtd,
substring(convert(varchar, inv150.t_date, 120), 1, 10) date,
com001.t_namb namb,
itm001.t_csgp csgp,
com020.t_cbrn cbrn,
(select substring(convert(varchar, t_date, 120), 1, 10) from ttimrp030{$erp} mrp030 where mrp030.t_orno=inv150.t_orno and mrp030.t_pono=inv150.t_pono) jalo,
round(convert(DEC(10,3), pur041.t_amta),2) amta,
(SELECT TOP 1 t_aitc FROM ttiitm012$erp
        WHERE t_citt='SUP' AND t_item=itm001.t_item AND t_suno=pur040.t_suno
) AS t_aitc
from ttdpur040$erp pur040
     LEFT JOIN ttccom001$erp com001 ON pur040.t_ccon = com001.t_emno
     LEFT JOIN ttdinv150$erp inv150 ON inv150.t_orno = pur040.t_orno
     LEFT JOIN ttdpur041$erp pur041 ON inv150.t_orno = pur041.t_orno
     LEFT JOIN ttiitm001$erp itm001 ON inv150.t_item = itm001.t_item
     LEFT JOIN ttccom020$erp com020 ON pur041.t_suno = com020.t_suno
where inv150.t_koor = 2 AND inv150.t_item='$id'
EOF;


$rows = tldUtils::getSqlToAssocArray($query, 'odbc', ['src' => 'baan']);
if(!count($rows)==0){
	foreach ($rows as $row){

        $form = new tldAssocTable(
        		$row,
        		$xItems,
        		array(
        				"title"=>"Planned Transaction By Item"
        		)
        );
	}
	$cells[0] .= $form->fetch();
}else{
	$cells[0] .= <<<EOF
		<h3>No transaction for PN# $id</h3>
EOF;
}
    $cells[0] .= <<<EOF
<h3><a href="/en/private/manufacturing/whse/dev.php?m[0]=reports&m[1]=byLocationTransaction&erp=$erp&id=$id&btnSubmit=Submit" target=_blank>
    Past inventory transaction for PN# $id
    </a></h3>

EOF;
        global $smarty;
        // Include jqzoominc lib to have the zoom on pictures
        $smarty->assign("html_head", $smarty->fetch(dirname(__FILE__) . "/jqzoominc.inv.tpl"));
        // Get Photo Vault Controller
        $photoController = new tldPhotoController();
        // Get single Photo Vault Controller
        $filePath = $photoController->fileExistsInVault($erp,"$id.JPG");
        $imgParams=array();
        if(!empty($filePath)){
        	$imgParams[]=array(
        			'link'=>array(
        					'm'=>array(0=>'getfile',1=>'photo'),
        					'erp'=>$erp,
        					'item'=>$id,
        			),
        			'img'=>array(
        					'm'=>array(0=>'getfile',1=>'photo'),
        					'erp'=>$erp,
        					'item'=>$id,
        					'new_width'=>256,
        			),
        	);
        	$IMG=<<<EOF
<a href="/en/private/manufacturing/eng/dev.php?m[0]=getfile&m[1]=photo&erp=$erp&item=$id" class="jqzoom" style="" title="">
<img src="/en/private/manufacturing/eng/dev.php?m[0]=getfile&m[1]=photo&erp=$erp&new_width=256&item=$id&new_width=256" title="" align="right">
</a>
EOF;
        }
        // If the PN folder exists, add multiple PN pictures
        $folderpath = $photoController->fileExistsInVault($erp,$id);
        if(!empty($folderpath)){
        	// Get list of the other files in vault
        	$files = $photoController->getDirFilelist($erp, $folderpath);
        	// retrieve all picture files to display it
        	foreach($files as $key=>$file){
        		if(strtolower($file)=='thumbs.db') continue;
        		$imgParams[]=array(
        				'link'=>array(
        						'm'=>array(0=>'getfile',1=>'photo'),
        						'erp'=>$erp,
        						'item'=>$id,
        						'key'=>$key,
        				),
        				'img'=>array(
        						'm'=>array(0=>'getfile',1=>'photo'),
        						'erp'=>$erp,
        						'item'=>$id,
        						'key'=>$key,
        						'new_width'=>128,
        				),
        		);
        	}
        }

        $imgHtml = '';
        foreach($imgParams AS $img){
        	$imgHtml .= '<a href="/en/private/manufacturing/eng/dev.php?'.http_build_query($img['link']).'" class="jqzoom"><img src="/en/private/manufacturing/eng/dev.php?'.http_build_query($img['img']).'" alt="" align="right" /></a>' . "\n";
        }
        $linkHtml = '';
        foreach($imgParams AS $key=>$img){
        	$linkHtml .= '<br/><a href="/en/private/manufacturing/eng/dev.php?'.http_build_query($img['link']).'" target="_blank">Open Image #'.($key+1).' In New Window</a>' . "\n";
        }
      	$edmHtml .='<img src="/en/private/manufacturing/eng/dev.php?m[0]=getfile&m[1]=photo&erp=$erp&pn=$id&new_width=256" title="" align="middle">';
        $cells[1] .= <<<EOF
<table width="100%">
  <tr>
  	<td>$imgHtml</td>
  	</tr>
  	<tr>
  	<td>$linkHtml</td>
  	</tr>
  	</table>
EOF;

    $itm = new tldITM($id, $erp);
	$mrbstock = $itm->getMRBStock($id, $erp);
    $stock = $itm->getNonNetable();
	$itmheader =  $itm->getHeader();
    if(count($itmheader)){
        $saftystock = $itmheader['t_sfst'];
    }

    global $smarty,$PATH;
    $smarty->assign("rows",tldMRP::PlannedMovement($erp,$id));
    $smarty->assign("width",1400);
    $smarty->assign("title","Planned movement for PN# $id - Actual stock: {$stock['nonnet']} &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; Safety stock: $saftystock &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;  {$mrbstock['whse']} Stock: {$mrbstock['stoc']}");
    $cells[1] .= $smarty->fetch("$PATH/inv/invplan.listing.inc.tpl");

  	$report = new tldHTMLTable(
  			$cells,
  			array(
  					"cols"=>2,
  					"attribs"=>array("table"=>" width='100%'","tr"=>" bgcolor='#FFFFFF'")
  			)
  			);
  	$result.= $report->fetch();
  	return $result;
}
function _viewGeneralTab2($erp, $id, $warehouse, $order, $range){
	$cells = [];
	$caption = "Transaction History By Item and Warehouse";
	$warehouseList = array_column(tldCWAR::byERP($erp), 't_cwar', 't_cwar');
	$form = new HTML_QuickForm('frmInventory', 'post');
	$form->addElement(	'hidden', 'm[0]', 		'inv');
	$form->addElement(	'hidden', 'm[1]', 		'byLocationTransaction');
	$form->addElement(	'header', 'title', 		"Past inventory transaction by location - maximum 1000 records");
	$form->addElement(	'text',   'id',    		'Part Number',   array("size"=>12));
	$form->addElement(	'select', 'warehouse',  'Warehouse', 	 array(''=>'ALL')+$warehouseList);
	$form->addElement(	'text',   'order',  	'Order#',		 array("size"=>12));
	$form->addElement(	'select', 'range',  	'Date Range', 	 ['1'=>'ALL', '0'=>'Past 12 months']);
	$form->addElement(	'submit', 'btnSubmit', 'Submit');
	$cells[0] .= $form->toHTML();
	$cons = "";
	$cons1 = "";
	if ($range == 0 ) {
		$cons = " and t1.t_odat >DATEADD(month, -12, GETDATE()) ";
		$cons1 =" and INV700.t_trdt >DATEADD(month, -12, GETDATE())  ";
		$caption .= " - Past 12 months ";
	}else{
		$caption .= " - All ";
	}
	$query="SELECT * FROM (select TOP 1000 ";
	$query.="INV700.t_item t_item, ";
	$query.="INV700.t_quan t_quan, ";
	$query.="substring(convert(varchar, INV700.t_trdt, 120), 1, 10) t_trdt, ";
//	$query .= "CONVERT(VARCHAR(10),INV700.t_trtm/3600)+'H:'+CONVERT(VARCHAR(10),INV700.t_trtm%3600/60)+'M:'+CONVERT(VARCHAR(10),INV700.t_trtm%3600%60)+'S' t_trtm, ";
	$query.="INV700.t_cwar t_cwar, ";
	$query.="case INV700.t_koor
		WHEN '1' THEN (SELECT sfc001.t_mitm FROM ttisfc001$erp AS sfc001 WHERE sfc001.t_pdno=INV700.t_orno)
		WHEN '2' THEN (SELECT po.t_suno FROM ttdpur040$erp AS po WHERE po.t_orno=INV700.t_orno )
		WHEN '3' THEN (SELECT so.t_cuno FROM ttdsls040$erp AS so WHERE so.t_orno=INV700.t_orno )
        ELSE ''
		END t_alloc, ";
	$query.="case INV700.t_koor
		WHEN '1' THEN (SELECT top 1 ltc001.t_clot from ttdltc001$erp AS ltc001 WHERE ltc001.t_cprj=INV700.t_cprj AND INV700.t_cprj<>'')
		ELSE ''
		END sn, ";
	$query.="case INV700.t_koor
		WHEN '1' THEN 'Production order'
		WHEN '2' THEN 'Purchase order'
		WHEN '3' THEN 'Sales order'
		WHEN '4' THEN 'POF-MPS'
		WHEN '5' THEN 'POA-MPS'
		WHEN '6' THEN 'POF-MRP'
		WHEN '7' THEN 'POA-MRP'
		WHEN '8' THEN 'POF-PRP'
		WHEN '9' THEN 'POA-PRP'
		WHEN '10' THEN 'POM-PRP'
		WHEN '11' THEN 'MRP sales forecast'
		WHEN '12' THEN 'MPS rough material req.'
		WHEN '13' THEN 'Sales quotation'
		WHEN '14' THEN 'Purchase contract'
		WHEN '15' THEN 'Sales contract'
		WHEN '16' THEN 'Warehouse order'
		WHEN '17' THEN 'Service order'
		WHEN '18' THEN 'PRP purchase order'
		WHEN '19' THEN 'PRP warehouse order'
		WHEN '20' THEN 'ISM warehouse order'
		WHEN '21' THEN 'Replenishment order'
		WHEN '22' THEN 'DRP replenishment order'
		WHEN '23' THEN 'DRP sales forecast'
		WHEN '24' THEN 'ECO orders'
		WHEN '25' THEN 'MPS interplant order'
		WHEN '26' THEN 'Production batch'
		WHEN '27' THEN 'Warehouse order'
		WHEN '28' THEN 'MPS production batch'
		WHEN '29' THEN 'MRP production batch'
		END t_koor, ";
	$query.="INV700.t_orno t_orno, ";
	$query.="INV700.t_pono t_pono, ";
	$query.="case INV700.t_kost
		WHEN '1' THEN '-'
		WHEN '2' THEN '-'
		WHEN '3' THEN '+'
		WHEN '4' THEN '+'
		WHEN '5' THEN '-'
		WHEN '6' THEN '-'
		WHEN '7' THEN '-'
		WHEN '8' THEN '-'
		WHEN '9' THEN '-'
		WHEN '10' THEN '-'
		WHEN '11' THEN '-'
		WHEN '12' THEN '-'
		WHEN '13' THEN '-'
		WHEN '14' THEN '-'
		WHEN '15' THEN '-'
		WHEN '16' THEN '-'
		WHEN '17' THEN '+'
		END tp, ";
	$query.="case INV700.t_kost
		WHEN '1' THEN 'Inventory Adjustment'
		WHEN '2' THEN 'Inventory Transfer'
		WHEN '3' THEN 'Purchase Order'
		WHEN '4' THEN 'Purchase Receipt'
		WHEN '5' THEN 'Sales Allocation'
		WHEN '6' THEN 'Sales Delivery'
		WHEN '7' THEN 'Production Issue'
		WHEN '8' THEN 'Production Receipt'
		WHEN '9' THEN 'Operation Costs'
		WHEN '10' THEN 'Subcontracting Costs'
		WHEN '11' THEN 'Actual Surcharges'
		WHEN '12' THEN 'Production Result'
		WHEN '13' THEN 'Purchase Result'
		WHEN '14' THEN 'Lot Result'
		WHEN '15' THEN 'Revaluation'
		WHEN '16' THEN 'Replenishment Delivery'
		WHEN '17' THEN 'Replenishment Receipt'
		END t_kost, ";
    $query.="case INV700.t_koor
		WHEN '1' THEN (select TOP 1 t_text from ttttxt010$erp where t_ctxt=(SELECT min(t_txta) FROM ttisfc001$erp AS sfc001 WHERE sfc001.t_pdno=INV700.t_orno AND sfc001.t_mitm=INV700.t_item ))
		WHEN '2' THEN (select TOP 1 t_text from ttttxt010$erp where t_ctxt=(select min(t_txta) from ttdpur041$erp PUR041 where PUR041.t_orno=INV700.t_orno and PUR041.t_pono=INV700.t_pono and PUR041.t_item=INV700.t_item ))
		WHEN '16' THEN (SELECT TOP 1 t_text from ttttxt010$erp where t_ctxt=(SELECT min(t_txta) from ttdinv100$erp AS inv100 WHERE inv100.t_cprj=INV700.t_cprj and inv100.t_wrho=INV700.t_orno AND inv100.t_item=INV700.t_item ))
		WHEN '27' THEN (SELECT  TOP 1 t_text from ttttxt010$erp where t_ctxt=(SELECT min(t_txta) from ttdinv100$erp AS inv100 WHERE inv100.t_cprj=INV700.t_cprj and inv100.t_wrho=INV700.t_orno AND inv100.t_item=INV700.t_item ))
		END t_txta, ";
	$query.="(select TOP 1 t_text from ttttxt010$erp where t_ctxt=(SELECT max(t_txta) FROM ttdpur045$erp AS PUR045 WHERE PUR045.t_orno=INV700.t_orno AND PUR045.t_item=INV700.t_item )) as qa_text, ";
	$query.="INV700.t_cprj t_cprj, ";
	$query.="INV700.t_stoc t_stoc, ";
	$query.="INV700.t_logn t_logn ";
	$query.="FROM ttdinv700$erp INV700  ";
	$query.="WHERE 1=1 ";
	$query.= $cons1;
	if($id<>''){
		$query.=" AND INV700.t_item='{$id}' ";
	}
	if ($warehouse<>'') {
		$query.=" AND INV700.t_cwar ='{$warehouse}' ";
	}

	if ($order<>'') {
		$query.=" AND INV700.t_orno='{$order}'  ";
	}
	$query.="Order By t_trdt DESC, t_quan DESC ) t3 UNION select * from (SELECT t1.t_item t_item,
           t1.t_oqua as t_quan,
    substring(convert(varchar, t1.t_odat, 120), 1, 10) as t_trdt,
           '' as t_alloc,
           '' as sn,
           t2.t_cwar as t_cwar,
           'Drop shippment' as t_koor, (cast(t1.t_orno as varchar(20)))  as t_orno, t1.t_pono AS t_pono,
           '+-' as tp, 'PO/SO' as t_kost,
           (select TOP 1 t_text from ttttxt010$erp where t_ctxt=(SELECT max(t_txta) FROM ttdpur045$erp AS PUR045 WHERE PUR045.t_orno=t1.t_orno AND PUR045.t_item=t1.t_item )) as qa_text, 
           '' as t_txta, '' as  t_cprj, '' as t_stoc, '' as t_logn

    FROM ttdsls041$erp as t1,
         ttdsls045$erp as t2
    WHERE t2.t_orno = t1.t_orno
      AND t2.t_pono = t1.t_pono
      and t1.t_item LIKE '{$id}'
      and t1.t_drct = 1
	  $cons
) t4";
	$query.= " Order By t_trdt DESC, t_quan DESC";

	$result = tldUtils::getSqlToAssocArray($query, "odbc", ["src" => "baan"]);

	foreach ($result as $val => $key) {
		if ($key['t_koor'] == 'Production order') {
			$cprj = trim($key['t_cprj']);
			$item = trim($key['t_alloc']);
			$query1 = <<<EOF
				SELECT case ITM.t_kitm
WHEN '1' THEN (ITM.t_dsca )
WHEN '2' THEN (ITM.t_dsca )
WHEN '5' THEN (ITM.t_dsca )
WHEN '4' THEN (ITM.t_dsca )
WHEN '3' THEN (SELECT pcs021.t_dsca from ttipcs021$erp AS pcs021 WHERE ITM.t_item=pcs021.t_item AND pcs021.t_cprj='$cprj')
WHEN '6' THEN (ITM.t_dsca )
END AS t_dsca
				FROM ttiitm001$erp AS ITM
				WHERE ITM.t_item='$item'
EOF;
			$ret = tldUtils::getSqlRowToAssocArray($query1, "odbc", ["src" => "baan"]);
			$result[$val]['t_alloc'] = $item . ' - ' . $ret['t_dsca'];;
		}
		if ($key['t_koor'] == 'Purchase order') {
			$cuno = trim($key['t_alloc']);
			$query1 = <<<EOF
				SELECT RTRIM(T7.t_nama) AS t_nama
				FROM ttccom020$erp AS T7
				WHERE T7.t_suno LIKE '$cuno'
EOF;
			$ret = tldUtils::getSqlRowToAssocArray($query1, "odbc", ["src" => "baan"]);
			$result[$val]['t_alloc'] = $ret['t_nama'];
		}
		if ($key['t_koor'] == 'Sales order') {
			$suno = trim($key['t_alloc']);
			$orno = trim($key['t_orno']);
			$query1 = <<<EOF
				SELECT cus.t_nama ,T7.t_cuno
				FROM ttdsls040$erp AS T7
				LEFT JOIN ttccom010$erp AS cus ON T7.t_cuno=cus.t_cuno
				WHERE T7.t_cuno LIKE '$suno' AND T7.t_orno='$orno'
EOF;
			$ret = tldUtils::getSqlRowToAssocArray($query1, "odbc", ["src" => "baan"]);
			$result[$val]['t_alloc'] = $ret['t_cuno'] . '-' . $ret['t_nama'];
		}
		if ($key['t_koor'] == 'Warehouse order' && $key['t_kost'] == 'Inventory Adjustment') {
			$item = trim($key['t_item']);
			$orno = trim($key['t_orno']);
			$query1 = <<<EOF
		    SELECT  inv105.t_recd,inv105.t_dsca
		    FROM ttdinv100$erp AS inv100 
		    LEFT JOIN ttdinv105$erp AS inv105 ON inv105.t_recd =inv100.t_recd 
		    WHERE inv100.t_item='$item' and inv100.t_wrho=$orno
EOF;
			$ret = tldUtils::getSqlRowToAssocArray($query1, "odbc", ["src" => "baan"]);
			$result[$val]['t_alloc'] = $ret['t_recd'] . '-' . $ret['t_dsca'];
		}
		if ($key['t_koor'] == 'Warehouse order' && $key['t_kost'] == 'Inventory Transfer') {
			$whse = trim($key['t_cwar']);
			$item = trim($key['t_item']);
			$query1 = <<<EOF
		    SELECT ilc103.t_lcto,ilc103.t_lcfr,substring(convert(varchar, ilc103.t_idat, 120), 1, 10) AS idat
		    FROM ttdilc103$erp AS ilc103
		    WHERE ilc103.t_item like '$item' and ilc103.t_cwar like '$whse' and substring(convert(varchar, ilc103.t_idat, 120), 1, 10) LIKE '{$key['t_trdt']}'
EOF;

			$ret = tldUtils::getSqlRowToAssocArray($query1, "odbc", ["src" => "baan"]);

			if ($key['t_quan'] > 0) {
				$result[$val]['t_alloc'] = $whse . '-' . $ret['t_lcto'];
			} else {
				$result[$val]['t_alloc'] = $whse . '-' . $ret['t_lcfr'];
			}
		}
	}

	if(!count($result)==0){
        global $smarty,$PATH;
		$smarty->assign('title',$caption);
        $smarty->assign("rows",$result);
        $smarty->assign("width",1400);
        $cells[0] .= $smarty->fetch("$PATH/inv/inv.listing.inc.tpl");
	$report = new tldReportColumnar($result,
		array(	"xItems" => array(
					"t_trdt"=>"Date",
					"t_item"=>"PN",
					"tp"=>"Tp",
					"t_kost"=>"Transaction Type",
					"t_koor"=>"Order Type",
					"t_cwar"=>"Warehouse",
				    "t_alloc"=>"Name",
					"sn"=>"SN",
					"t_orno"=>"Order#",
					"t_quan"=>"QTY",
					"t_pono"=>"Position#",
					"t_cprj"=>"Project#",
					"t_logn"=>"Login Code",
					"t_stoc"=>"Invenory",
					"qa_text"=>"QA note"
				),
				"showNumberOfRows"=>TRUE,
				"title"=>$caption
			)
		);
//		$cells[0] .= $report->fetch();
	}else{
		$cells[0] .= <<<EOF
		<h3>No transaction for PN# $id</h3>
EOF;
	}

if(count($result)<2){
	global $smarty;
	// Include jqzoominc lib to have the zoom on pictures
	$smarty->assign("html_head", $smarty->fetch(dirname(__FILE__) . "/jqzoominc.inv.tpl"));
	// Get Photo Vault Controller
	$photoController = new tldPhotoController();
	// Get single Photo Vault Controller
	$filePath = $photoController->fileExistsInVault($erp,"$id.JPG");
	$imgParams=array();
	if(!empty($filePath)){
		$imgParams[]=array(
				'link'=>array(
						'm'=>array(0=>'getfile',1=>'photo'),
						'erp'=>$erp,
						'item'=>$id,
				),
				'img'=>array(
						'm'=>array(0=>'getfile',1=>'photo'),
						'erp'=>$erp,
						'item'=>$id,
						'new_width'=>256,
				),
		);
		$IMG=<<<EOF
<a href="/en/private/manufacturing/eng/dev.php?m[0]=getfile&m[1]=photo&erp=$erp&item=$id" class="jqzoom" style="" title="">
<img src="/en/private/manufacturing/eng/dev.php?m[0]=getfile&m[1]=photo&erp=$erp&new_width=256&item=$id&new_width=256" title="" align="right">
</a>
EOF;
	}
	// If the PN folder exists, add multiple PN pictures
	$folderpath = $photoController->fileExistsInVault($erp,$id);
	if(!empty($folderpath)){
		// Get list of the other files in vault
		$files = $photoController->getDirFilelist($erp, $folderpath);
		// retrieve all picture files to display it
		foreach($files as $key=>$file){
			if(strtolower($file)=='thumbs.db') continue;
			$imgParams[]=array(
					'link'=>array(
							'm'=>array(0=>'getfile',1=>'photo'),
							'erp'=>$erp,
							'item'=>$id,
							'key'=>$key,
					),
					'img'=>array(
							'm'=>array(0=>'getfile',1=>'photo'),
							'erp'=>$erp,
							'item'=>$id,
							'key'=>$key,
							'new_width'=>128,
					),
			);
		}
	}

	$imgHtml = '';
	foreach($imgParams AS $img){
		$imgHtml .= '<a href="/en/private/manufacturing/eng/dev.php?'.http_build_query($img['link']).'" class="jqzoom"><img src="/en/private/manufacturing/eng/dev.php?'.http_build_query($img['img']).'" alt="" align="right" /></a>' . "\n";
	}
	$linkHtml = '';
	foreach($imgParams AS $key=>$img){
		$linkHtml .= '<br/><a href="/en/private/manufacturing/eng/dev.php?'.http_build_query($img['link']).'" target="_blank">Open Image #'.($key+1).' In New Window</a>' . "\n";
	}
	$edmHtml .='<img src="/en/private/manufacturing/eng/dev.php?m[0]=getfile&m[1]=photo&erp=$erp&pn=$id&new_width=256" title="" align="middle">';
	$cells[1] .= <<<EOF
<table width="100%">
  <tr>
  	<td>$imgHtml</td>
  	</tr>
  	<tr>
  	<td>$linkHtml</td>
  	</tr>
  	</table>
EOF;
}

	$report = new tldHTMLTable(
			$cells,
			array(
					"cols"=>2,
					"attribs"=>array("table"=>" width='100%'","tr"=>" bgcolor='#FFFFFF'")
			)
	);
	$result = $report->fetch();
	return $result;
}
?>
