<?php
$DEFAULT_TITLE .= "\ISR";
$DEFAULT_MENU .=<<<EOF
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<a href="$php_self?m[0]=isr">Home</a>&nbsp;|&nbsp;
<a href="$php_self?m[0]=isr&m[1]=form&m[2]=byNum">By number</a>&nbsp;|&nbsp;
<a href="$php_self?m[0]=isr&m[1]=form&m[2]=search">Search</a>&nbsp;|&nbsp;
<a href="$php_self?m[0]=isr&m[1]=listing&m[2]=advsearch">Advanced Search</a>&nbsp;|&nbsp;
<a href="$php_self?m[0]=isr&m[1]=form&m[2]=new">Create</a>&nbsp;|&nbsp;
<a href="$php_self?m[0]=isr&m[1]=reports">Reports</a>&nbsp;|&nbsp;
<a href="/en/private/mis/mis.php?m[0]=help&m[1]=dms&id=3931"">Help</a>
EOF;

if($user->isInGroup(array("gg_ADMIN","superuser"))){
	$DEFAULT_MENU .=<<<EOF
&nbsp;|&nbsp;<a href="isr/isr.admin.inc.php">Maintain ISR</a>
EOF;
}

switch($m[1]){
case 'form':
	include("isr.form.inc.php");
break;
case 'view':
	include("isr.view.inc.php");
break;
case 'reports':
	$body = $smarty->fetch("$PATH/isr/reports/homepage.reports.tpl");
break;
case 'listing':
	switch($m[2]){
	case 'advsearch':
		$form = new HTML_QuickForm('frmSOByNum', 'get', '', '', '', true);
		$form->addElement('hidden', 'm[0]', 'isr');
		$form->addElement('hidden', 'm[1]', 'listing');
		$form->addElement('hidden', 'm[2]', 'advsearch');
		$form->addElement('header', 'title', 'Sales Order Search');
		$form->addElement('select', 'erp', 'BU to', ['ALL'=>'ALL'] + tldLocation::getERPList('smartyOptions'));
		$form->addElement('select', 'erp_from', 'BU from', ['ALL'=>'ALL'] + tldLocation::getERPList('smartyOptions'));
		$form->addElement('text', 'packing', 'Packing Slip#', ['size' => 12]);
		$form->addElement('text', 'orno', 'SO#', ['size' => 12]);
		$form->addElement('text', 'item', 'Part#', ['size' => 12]);
		$form->addElement('submit', 'btnSubmit', 'Submit');
		$form->addElement('reset', 'btnClear', 'Clear');
		$form->setDefaults(['erp' => $DEFAULT_ERP]);
        if (!empty($orno)) {
			$w[] = " sors.t_orno='$orno'";
			$TITLE .= " SO#$orno";
		}
		if (!empty($packing)) {
			$w[] = " sols.t_dino='$packing'";
			$TITLE .= " Packing Slip#$packing";
		}
        if (!empty($item)) {
			$w[] = " sols.t_item='$item'";
			$TITLE .= " Part Number#$item";
		}

		if (count($w ?? [])) {
			$WHERE = ' AND ' . implode(' AND ', $w);
		}
		if ($form->validate()) {
			if (empty($WHERE)) {
				$DEFAULT_ERROR[] = "At least one condition(PO or PN) need to be filled...";
				break;
			}
			$erps = [250, 300, 330, 400, 410, 420, 500, 520, 540, 570, 600, 620, 640, 660, 680];
			$array = array_diff($erps, [$erp]);
			$elements = [];
			foreach ($array as $erp) {
				$elements[] = <<<EOF
				SELECT sols.t_dino  FROM  ttdsls040$erp as sors 
				LEFT JOIN ttdsls045$erp as sols on sors.t_orno=sols.t_orno 
				LEFT JOIN ttdsls041$erp AS T2 ON sols.t_orno=T2.t_orno  AND sols.t_pono=T2.t_pono 
				LEFT JOIN ttiitm001$erp AS itms ON sols.t_item=itms.t_item 
				LEFT JOIN ttccom010$erp AS cus ON sors.t_cuno=cus.t_cuno 
				LEFT JOIN ttccom001$erp AS reps ON sors.t_crep=reps.t_emno 
				WHERE 1=1 $WHERE
EOF;
			$query = '(' . implode(') UNION (', $elements) . ')';
		}

			$rows = tldUtils::getSqlToAssocArray($query, 'odbc', ['src' => 'baan']);
			$isrlist = '';
			foreach ($rows as $key=>$val){
				if($val['t_dino']){
					$isrlist .= $val['t_dino'] .',';
				}
			}
			# If the form validates then freeze the data
			$form->freeze();
			$vars = tldUtils::cleanupFormInput($form->exportValues());
			$rows = tldISR::advSearch(rtrim($isrlist,','), $vars['erp'],$vars['erp_from']);
			$caption = "Search results for $TITLE";
		} else {
			$body .= $form->toHTML();
		}
	break;
		case 'search':
		$rows = tldISR::search(TldDatabase::escape($x));
        $caption = "Search result";
	break;
    case 'byBuFromStatus':
        $x = TldDatabase::escape($x);
        $y = TldDatabase::escape($y);
   		$rows = tldISR::byBuFromStatus($y,$x);
		$caption = "ISR with '$x' status from $y BU";
    break;
    case 'byBuToStatus':
        $x = TldDatabase::escape($x);
        $y = TldDatabase::escape($y);
   		$rows = tldISR::byBuToStatus($y,$x);
		$caption = "ISR with '$x' status heading to $y BU";
    break;
    case 'byMissingDoc':
    	$rows = tldISR::ByMissingDoc();
        $caption = "ISR with missing Documents";
    break;
	}

	if(count($rows ?? [])==0){
	    $DEFAULT_ERROR[]="No rows founded...";
	    break;
	}

	$DEFAULT_MENU .=<<<EOF
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<a href="$php_self?m[0]=isr&m[1]={$m[1]}&m[2]={$m[2]}&m[3]=xls&x=$x&y=$y">XLS</a> | 
<a href="$php_self?m[0]=isr&m[1]={$m[1]}&m[2]={$m[2]}&m[3]=transit&x=$x&y=$y">Report online line in transit<a> | 
<a href="$php_self?m[0]=isr&m[1]={$m[1]}&m[2]={$m[2]}&m[3]=transit&m[4]=xls&x=$x&y=$y">XLS version report line in transit</a>

EOF;

	switch($m[3]){

    case 'transit':
	if(count($rows)==0){
		$DEFAULT_ERROR[]="No rows founded...";
		break;
	}


    $xItem = [
        "id"=>"ISR#",
        "cnum"=>"Contenainer",
		"container_type"=>"Container Type",
        "bu_from_fullname"=>"BU from",
        "ttype"=>"Transportation type",
        "tnum"=>"Tracking#",
        "dt_eta"=>"ETA date",
        "packingslip"=>"Packing slip#",
        "so"=>"SO#",
        "po"=>"PO#",
		"pono"=>"Line#",
        "t_pono"=>"Item#SO",
        "unit_price"=>"Unit Price",
        "amount"=>"Amount",
        "currency"=>"Currency",
        "weight"=>"WT(KGS)",
        "totalwt"=>"Total WT",
        "coo"=>"COO",
        "part_number"=>"Part Number",
        "description"=>"Description",
        "alt_desc"=>"Description(ALT)",
        "t_dqua"=>"Packing slip QTY",
		"invn"=>"Invoice#",
		"invd"=>"Invoice Date"
    ];
	$i = 0;
	foreach ($rows as $k=>$item) {
		$isr = new tldISR($item['id']);
		$header = $isr->getHeader();
		foreach($isr->getLines() as $key=>$ps) {
			// Get packing slip info to get SO#
			$dino = new tldDINO($ps['t_dino'], $header['bu_from_erp']);
			$e = new tldBaanERP($header['bu_from_erp']);
			foreach ($dino->getDetail() as $detail) {
				$i = $i+1;
				$detail['isrlid'] = $ps['id'];
				// Get PO# from SO#
				$so = new tldSO($detail['t_orno'], $header['bu_from_erp']);
				$pol = new tldPOL($header['bu_to_erp'],$so->itsDetails['t_eono']);
				// Get the PO#
				$detail['t_eono'] = $so->itsDetails['t_eono'];
				$detail['t_ccur'] = $so->itsDetails['t_ccur'];
				$line[$i]['invn'] = $so->itsDetails['t_invn'];
				$line[$i]['invd'] = $so->itsDetails['t_invd'];
				// Get the item information
				$item = $e->getItemData($detail['t_item']);
				$line[$i]['id']= $header['id'];
				$line[$i]['cnum'] = $header['cnum'];
                $line[$i]['container_type'] = $header['container_type'];
				$line[$i]['bu_from_fullname']= $header['bu_from_fullname'];
				$line[$i]['ttype'] = $header['ttype'];
				$line[$i]['tnum'] = $header['tnum'];
				$line[$i]['dt_eta'] = $header['dt_eta'];
				$line[$i]['t_pono'] = $header['t_pono'];
				$line[$i]['packingslip'] = $ps['t_dino'];
				$line[$i]['so'] = $detail['t_orno'];
				$line[$i]['po'] = $so->itsDetails['t_eono'];
				$line[$i]['pono'] = $pol->getPonoByBUByConstraints($header['bu_to_erp'],$detail['t_item'],$so->itsDetails['t_eono']);
				$line[$i]['currency'] = $so->itsDetails['t_ccur'];
				$line[$i]['weight'] = $detail['t_wght'] = $item['WT'];
				$line[$i]['totalwt'] = $detail['totalwght'] = $item['WT'] * $detail['t_dqua'];
				$line[$i]['coo'] = $detail['t_ctyo'] = $item['t_ctyo'];
				$line[$i]['part_number'] = $detail['t_item'];
				$line[$i]['description'] = $detail['t_dsca'];
				$line[$i]['alt_desc'] = $detail['t_dscb'];
				$line[$i]['coo'] = $detail['t_ctyo'];
				$line[$i]['unit_price'] = $detail['t_pric'];
				$line[$i]['amount'] = $detail['t_amnt'];
				$line[$i]['t_dqua'] = $detail['t_dqua'];
//				$line[$key]['t_ccde'] = $detail['t_ccde'] = $hscode['t_ccde'];
			}
		}
	}
		$columnarOptions = [
			"xItems"=>$xItem,
			"title"=>'ISR detail list',
			"links"=>[
				"so"=>"/en/private/finance/finance.php?m[0]=so&m[1]=view&erp={$header['bu_from_erp']}&id=",
				"packingslip"=>"/en/private/finance/finance.php?m[0]=ps&m[1]=view&erp={$header['bu_from_erp']}&id=",
				"po"=>"/en/private/manufacturing/pur/dev.php?m[0]=po&m[1]=view&erp={$header['bu_to_erp']}&id=",
				"part_number"=>"/en/private/manufacturing/eng/dev.php?m[0]=bom&m[1]=view&erp={$header['bu_from_erp']}&pn="
			]
		];
		$report = new tldReportColumnar($line, $columnarOptions);
		$sess['isrlines'] = $line;
		$body .= $report->fetch();
		if($m[4] =='xls'){
			$report = new tldXLS(
				$sess['isrlines'],
				[
					"xItems"=>$xItem,
					"showTitles"=>true
				]
			);
			$report->out();
			exit;
		}
    break;
	case 'xls':
        $report = new tldXLS(
            $rows,
            array(
            	"xItems"=>array(
                    "id"=>"ISR#",
        			"dt"=>"Creation Date",
        			"poster_fullname"=>"Poster",
        			"bu_from_fullname"=>"BU from",
        			"bu_to_fullname"=>"BU to",
                    "cuno"=>"ERP Customer#",
        			"status"=>"Status",
        			"ttype"=>"Transportation type",
        			"cnum"=>"Container#",
					"container_type"=>"Container Type",
        			"tnum"=>"Tracking#",
            		"dt_outb"=>"Outbound date",
            		"dt_ship"=>"Shipping date",
            		"dt_eta"=>"ETA date"
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
default:
	$body = $smarty->fetch("$PATH/isr/isr.homepage.tpl");
	$cell = array();
	$matrix = new tldMatrix(
	    tldISR::countByBuToStatus(),
		"status", "location", "num",
		"$php_self?m[0]=isr&m[1]=listing&m[2]=byBuToStatus",
		"ISR Count by BU to, Status",
		array("xItems"=>array('PENDING','IN PROGRESS','CLOSED'))
	);
	$cells[] = $matrix->fetch();
	$matrix = new tldMatrix(
	    tldISR::countByBuFromStatus(),
		"status", "location", "num",
		"$php_self?m[0]=isr&m[1]=listing&m[2]=byBuFromStatus",
		"ISR Count by BU from, Status",
		["xItems"=>['PENDING','IN PROGRESS','CLOSED', 'CANCELLED']]
	);
	$cells[] = $matrix->fetch();
	$report = new tldHTMLTable(
		$cells,
		array(
			"cols"=>2,
            "attribs"=>array(
            	"table"=>" width='100%'",
            	"tr"=>" bgcolor='#FFFFFF'"
			)
		)
	);
	$body .= $report->fetch();
	$body .= _getListing(tldISR::byLatest(),"Latest ISR");
break;
}


function _getGeneralTab(){
	global $isr,$header,$smarty,$PATH;
	// Get tracking link(s)
    $lines = tldCourier::parseMultiple($header["tnum"]);
	if(!empty($lines)){
    	foreach($lines as $line){
    		$form = new tldCourier($line['courier']);
    		$header['tnum_btn'] .= $form->fetch($line['trackNum']);
    	}
	}else{
	    $header['tnum_btn']=$header["tnum"];
	}
	// Display general
	$report = new tldAssocTable(
	    $header,
		array(
			"id"=>"ISR#",
			"dt"=>"Creation Date",
			"poster_fullname"=>"Poster",
			"bu_from_fullname"=>"BU from",
			"bu_to_fullname"=>"BU to",
    		"cuno"=>"ERP Customer#",
			"status"=>"Status",
			"ttype"=>"Transportation type",
			"cnum"=>"Container#",
			"container_type"=>"Container Type",
			"tnum_btn"=>"Tracking#",
    		"dt_outb"=>"Outbound date",
    		"dt_ship"=>"Shipping date",
    		"dt_eta"=>"ETA date",
			"doc_ship"=>"Shipping doc",
			"doc_qa"=>"Quality doc",
			"doc_inv"=>"Invoice doc",
    		"notes"=>"Notes"
		),
		array("title"=>"General")
	);
	$smarty->assign("isr",$header);
	$table = <<<EOF
    <table width="100%">
      <tr>
        <td width="40%">{$report->fetch()}</td>
        <td width="60%">{$smarty->fetch("$PATH/isr/view/view.isr.doc.tpl")}</td>
      </tr>
    </table>
EOF;

	return $table;
}

function _getListing($rows,$title){
	global $php_self;
	foreach ($rows as $k=>$item) {
		$isr = new tldISR($item['id']);
		$header = $isr->getHeader();
		foreach($isr->getLines() as $key=>$ps) {
			// Get packing slip info to get SO#
			$dino = new tldDINO($ps['t_dino'], $header['bu_from_erp']);
			foreach ($dino->getDetail() as $detail) {
				$rows[$k]['amount'] += $detail['t_amnt'];

			}
		}
	}
	$report = new tldReportColumnar(
	    $rows,
		array(
			"xItems"=>array(
    			"id"=>"ISR#",
    			"dt"=>"Creation Date",
    			"poster_fullname"=>"Poster",
    			"bu_from_fullname"=>"BU from",
    			"bu_to_fullname"=>"BU to",
				"cuno"=>"ERP Customer#",
    			"status"=>"Status",
    			"ttype"=>"Transportation type",
    			"cnum"=>"Container#",
				"container_type"=>"Container Type",
    			"tnum"=>"Tracking#",
        		"dt_outb"=>"Outbound date",
        		"dt_ship"=>"Shipping date",
        		"dt_eta"=>"ETA date",
				"amount"=>"Amount"
    		),
			"title"=>$title,
			"links"=>array(
				"id"=>"$php_self?m[0]=isr&m[1]=view&id="
			)
		)
	);
	return $report->fetch();
}
?>
