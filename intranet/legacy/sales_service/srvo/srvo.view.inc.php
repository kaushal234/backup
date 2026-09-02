<?php
if(empty($id) || empty($erp)){
    $DEFAULT_ERROR[] = "ERROR: parameters empty or invalid";
    return;
}
$id = TldDatabase::escape($id);
$erp = TldDatabase::escape($erp);
$so = new tldServiceOrder($id,$erp);
if($so->isEmpty()){
    $DEFAULT_ERROR[] = "ERROR: SRVO# $id in ERP $erp not found";
    return;
}

$DEFAULT_TITLE .= "\SRVO#$id ($erp)";
$DEFAULT_MENU.=<<<EOF
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<a href="$php_self?m[0]=srvo&m[1]=view&erp=$erp&id=$id">Home</a>
&nbsp;|&nbsp;<a href="$php_self?m[0]=srvo&m[1]=view&m[2]=parts&erp=$erp&id=$id">Parts</a>
&nbsp;|&nbsp;<a href="$php_self?m[0]=srvo&m[1]=view&m[2]=labours&erp=$erp&id=$id">Labours</a>
&nbsp;|&nbsp;<a href="$php_self?m[0]=srvo&m[1]=view&m[2]=notes&erp=$erp&id=$id">Notes</a>
EOF;

// Check if any CSR linked
$csrID = $so->getCSRID();
if(!empty($csrID)){
    $DEFAULT_MENU.=<<<EOF
&nbsp;|&nbsp;<a href="$php_self?m[0]=csr&m[1]=view&id=$csrID">CSR#$csrID</a>
EOF;
}else{
    $DEFAULT_ERROR[] = "WARNING: No CSR found for this Baan Service Order";
}

if($user->isInGroup(array('superuser'))){
    $DEFAULT_MENU.=<<<EOF
&nbsp;|&nbsp;<a href="$php_self?m[0]=srvo&m[1]=view&m[2]=csr_mapping&erp=$erp&id=$id">CSR Mapping</a>
&nbsp;|&nbsp;<a href="$php_self?m[0]=srvo&m[1]=view&m[2]=baanSoapTrans&erp=$erp&id=$id">Soap Transactions</a>
EOF;
}

switch($m[2]){
case 'notes':
    $DEFAULT_TITLE .= "\Notes";
    // Call notes
    $body.=_getNotesReport($so->getCallNotes(),'Call notes');
    // Repair not
    $body.=_getNotesReport($so->getRepairNotes(),'Repair notes');
    // Job sheets
    $body.=_getNotesReport($so->getJobSheetNotes(),'Job sheets');
break;
case 'parts':
    $DEFAULT_TITLE .= "\Parts";
    $report = new tldReportColumnar(
        $so->getParts(),
        array(
            "xItems"=>array(
                "t_item"=>"Part Number",
                "t_dsca"=>"Description",
                "t_quan"=>"Quantity",
                "t_cuni"=>"UM"
            ),
            "title"=>"Parts"
        )
    );
    $body = $report->fetch();
break;
case 'labours':
    $DEFAULT_TITLE .= "\Labours";
    $report = new tldReportColumnar(
        $so->getLabours(),
        array(
            "xItems"=>array(
                "t_hrdt"=>"Date",
                "t_hrea"=>"Hours",
                "t_emno"=>"Employee"
            ),
            "title"=>"Labours"
        )
    );
    $body = $report->fetch();
break;
case 'baanSoapTrans':
    $DEFAULT_TITLE .= "\BAAN SOAP Transactions";
    // Constraints
    $constraints = <<<EOF
t_comp={$so->getERP()} AND t_key1={$so->getID()}
AND t_prog IN('createCSR','updateCSR')
EOF;
    $report = new tldReportColumnar(
        tldBaanSoapTransaction::byConstraints($constraints),
        array(
            "xItems"=>array(
                "t_iden"=>"Identification",
                "t_prog"=>"Program",
                "t_key1"=>"Key1",
                "t_key2"=>"Key2",
                "t_comp"=>"ERP",
                "t_date"=>"Date",
                "t_time"=>"Time",
                "t_stat"=>"Status",
                "t_xml1"=>"XML",
                "t_mesg"=>"Message",
            ),
            "title"=>"Transactions"
        )
    );
    $body = $report->fetch();
break;
case 'csr_mapping':
    $data = tldCSR::getMappingDataFromServiceOrder($so);
    foreach($data as $k=>$val){
        $body.="$k : $val<br>";
    }
break;
default:
    $cells = array();
    // SRVO info
    $report = new tldAssocTable(
    	$so->itsHeader,
        array(
            't_orno'=>'Service Order#',
            'erp'=>'ERP#',
            't_ddt1'=>'Date',
            'orderSeriesDesc'=>'Type',
        	't_swor'=>'Status',
            't_cuno'=>'Cuno#',
            't_ccon'=>'Contract#',
            't_cins'=>'Installation',
            't_cloc'=>'Location',
            't_desc'=>'Description',
            't_refe'=>'Contact',
            't_telp'=>'Telephone',
            'symptom_desc'=>'Symptom',
            'problem_desc'=>'Problem',
            'fix_desc'=>'Fix',
        ),
		array("title"=>"Service Order details")
	);
	$cells[] = $report->fetch();
	// Corresponding ER
	$erSN = TldDatabase::escape($so->getInstallationSN());
	$erHeader = tldEquipment::bySN($erSN);
	$report = new tldAssocTable(
    	$erHeader[0],
        array(
            "id"=>"ER#",
			"sn"=>"Equipment SN#",
			"status"=>"Status",
			"cust_asset_num"=>"Customer Asset#",
			"type"=>"Type",
			"model"=>"Model",
			"man_location"=>"Manufacturer location",
			"apc_fullname"=>"Airport",
			"sales_org"=>"SSO",
            "hours"=>"Hourmeter"
		),
		array(
			"title"=>"ER Details",
			"links"=>array("id"=>"/en/private/product_support/index.ps.php?m[0]=equipment&m[1]=view&id=")
	    )
	);
	$cells[] = $report->fetch();
    // Display
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
    $body = $report->fetch();
break;
}


function _getNotesReport($rows,$title){
    $report = new tldReportColumnar(
        $rows,
        array(
            "xItems"=>array(
                'dt'=>'Date',
            	't_user'=>'User',
        		't_text'=>'Note',
            ),
            "title"=>$title,
            "showItemNumbers"=>TRUE
        )
    );
    return $report->fetch();
}