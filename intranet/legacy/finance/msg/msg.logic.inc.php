<?php
$DEFAULT_TITLE .= "\MSG (BETA)";
$DEFAULT_MENU .=<<<EOF
<br/>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<a href="$php_self?m[0]=msg">Home</a>
&nbsp;|&nbsp;<a href="$php_self?m[0]=msg&m[1]=byNumber">By number</a>
&nbsp;|&nbsp;<a href="$php_self?m[0]=msg&m[1]=report">Report</a>
&nbsp;|&nbsp;<a href="/en/private/finance/msg/MSG_help.pdf">Help</a>
EOF;

$xItems = array(
	"id"=>"MSG#",
	"dt"=>"Date",
	"doc_type"=>"Document Type",
	"eparts_order"=>"eParts Order #",
	"pono"=>"Customer PO#",
	"from_fullname"=>"From",
	"to_fullname"=>"To",
	"status"=>"Status"
);

switch($m[1]){
case 'byNumber':
	$form = new HTML_QuickForm('frmByNum', 'post');
	$form->addElement(	'hidden', 'm[0]', 'msg');
	$form->addElement(	'hidden', 'm[1]', 'view');
	$form->addElement(	'header', 'title', "Search by ID");
	$form->addElement(	'text', 'id', "MSG#");
	$form->addElement(	'submit', 'btnSubmit', 'Submit');
    $body = $form->toHTML();
break;
case 'listing':
	switch($m[2]){
	case 'byERPStatus':
		$x = TldDatabase::escape($x);
		$y = TldDatabase::escape($y);
		$rows = tldERPMSG::byERPStatus($x,$y);
		$_title = "MSG to ERP company $x with status $y";
	break;
	}
	if(count($rows)<1){
		$DEFAULT_ERROR[]="ERROR: No lines found";
		break;
	}
	switch($m[3]){
	case 'xls':
		$report = new tldXLS(
			$rows,
			array(
				"xItems"=>$xItems,
				"showTitles"=>true
			)
		);
		$report->out();
		exit;
	break;
	default:
		$body .= _getListing($rows,$_title);
	break;
	}
break;
case 'view':
	if(empty($id) || !is_numeric($id)){
		$DEFAULT_ERROR[] = "ERROR: ID sent invalid or empty";
		break;
	}
	$msg = new tldERPMSG($id);
	if($msg->isEmpty()){
		$DEFAULT_ERROR[] = "ERROR: MSG#$id not found";
		break;
	}
	$header = $msg->getHeader();

	$DEFAULT_TITLE .= "\MSG#$id";
	$DEFAULT_MENU .=<<<EOF
<br/>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<a href="$php_self?m[0]=msg&m[1]=view&id=$id">General</a>&nbsp;|&nbsp;
<a href="$php_self?m[0]=msg&m[1]=view&m[2]=transmit&id=$id">Transmit</a>&nbsp;|&nbsp;
<a href="$php_self?m[0]=msg&m[1]=view&m[2]=status&id=$id">Status</a>&nbsp;|&nbsp;
<a href="$php_self?m[0]=msg&m[1]=view&m[2]=log&id=$id">Log</a>
EOF;

	switch($m[2]){
	case 'log':
    $DEFAULT_TITLE .="\Log";
    $report = new tldReportColumnar(
    	$msg->getLog(),
        array(
        	"xItems"=>array(
	        	"id"				=>"ID#",
		        "date"				=>"Date",
		        "poster_fullname"	=>"Poster",
		        "comment"			=>"Comment"
        	)
        )
    );
    $body .= $report->fetch();
	break;
	case 'status':
		if($msg->isClosed()){
			$DEFAULT_ERROR[] = "ERROR: MSG#$id already CLOSED";
			break;
		}
		$form = new HTML_QuickForm('frmStatus', 'post');
		$form->addElement(	'hidden', 'm[0]', 'msg');
		$form->addElement(	'hidden', 'm[1]', 'view');
		$form->addElement(	'hidden', 'm[2]', 'status');
		$form->addElement(	'hidden', 'id', $id);
		$form->addElement(	'header', 'title', 'Change status to CLOSED');
		$form->addElement(	'select', 'confirm', 'Do you confirm?',
			array(""=>"","Y"=>"I confirm"));
		$form->addElement('submit', 'btnSubmit', 'Submit');

		if(!$form->validate()){
			$body = $form->toHTML();
			break;
		}
		$vars = tldUtils::cleanupFormInput($form->exportValues());
		if($vars['confirm']<>"Y"){
			$DEFAULT_ERROR[] = "ERROR: You need to confirm to change status";
			break;
		}
		$e = $msg->changeStatus("CLOSED");
		if(is_string($e)){
			$DEFAULT_ERROR[] = "ERROR: MSG#$id status not updated<br>Reason: $e";
			break;
		}
		$e = $msg->addLogEntry($user->getID(),"Status changed to CLOSED");
		$body = "MSG#$id status changed successfully to CLOSED";
	break;
	case 'transmit':
		// Get msg data
		$DATA_MSG = $msg->getData();
		// What kind of document?
		switch($header['doc_type']){
		case 'SALES ORDER':
			if(!$user->isInGroup(array("gg_PARTS","role_SPM","gg_ADMIN"))){
				$DEFAULT_ERROR[]="ERROR: You do not have permissions to transmit MSG#$id";
				break 2;
			}
			$body = <<<EOF
<br/><br/>
<a href="/en/private/parts/parts.php?m[0]=cart&m[1]=import&m[2]=byMSG&id=$id&erp_whs={$header['erp_to']}">
Click here to transfer data of MSG#$id to the parts CART</a>
EOF;
/*
 * For historical purpose, keep track of old logic
 *
			// Look for type of xml
			$x = simplexml_load_string($DATA_MSG);
			$root = $x->getName();
			// Transform the xml from the "ERP from" to a TLD array to assign default value in form
			switch($root){
			case 'PROCESS_PO_003':
				$erpFromObj = tldERP::getERPOb($header['erp_from']);
				$raw_array = $erpFromObj->outPurchaseOrder($DATA_MSG);
			break;
			case 'cXML':
				$cxml = new tldCXML($header['erp_to']);
				$raw_array = $cxml->outPurchaseOrder($DATA_MSG);
			break;
			}
			// Assign Default form values
			$DATA_FORM_DEFAULT = array(
				"header"=>array(
					"t_cuno"=>$raw_array['header']['customer']['t_cuno'],
					"t_eono"=>$raw_array['header']['custpo'],
					"t_ddat"=>date("Y-m-d"),
					//"t_cfrw"=>$raw_array[''],
					"t_scom"=>$raw_array['header']['shipComplete'],
					"note"=>$raw_array['header']['note'],
					//"t_cdel"=>$raw_array[''],
					"t_nama"=>$raw_array['header']['deliverAddress']['line1'],
					"t_namb"=>$raw_array['header']['deliverAddress']['line2'],
					"t_namc"=>$raw_array['header']['deliverAddress']['line3'],
					"t_namd"=>$raw_array['header']['deliverAddress']['line4'],
					"t_name"=>$raw_array['header']['deliverAddress']['line5']
				)
			);
			foreach($raw_array['lines'] as $line){
				$DATA_FORM_DEFAULT["lines"][] = array(
	                "t_item"=>$line['product'],
	                "t_oqua"=>$line['qty']
            	);
			}

			// Assign the ERP# to send data
			$TO_ERP = $header['erp_to'];

			// Get some listing
			$erpCust = new tldERPCustomer($TO_ERP, $DATA_FORM_DEFAULT['header']['t_cuno']);
			// Get list of deliveries for this customer
			$cdelList = $erpCust->getCDELList();
			if(count($cdelList)<1){
				$DEFAULT_ERROR[]="WARNING: No delivery code founded for CUNO {$DATA_FORM_DEFAULT['header']['t_cuno']} in ERP#$TO_ERP";
			}

			// Form to update the SO if necessary
			$form = new HTML_QuickForm('frmES0', 'post');
			$form->addElement(	'hidden', 'm[0]', 'msg');
			$form->addElement(	'hidden', 'm[1]', 'view');
			$form->addElement(	'hidden', 'm[2]', 'submit');
			$form->addElement(	'hidden', 'id', $id);
			// SO header
			$form->addElement(	'header', 'title', "SO header");
			$form->addElement(	'text', "header[t_cuno]", "Cuno#");
			$form->addElement(	'text', "header[t_eono]", "Customer PO#");
			$form->addElement(	'text', "header[t_ddat]", "Order date");
			$form->addElement(	'hidden', "header[t_cfrw]", NULL);
			$form->addElement(	'select', "header[t_scom]", "Ship Complete?", array("N"=>"N","Y"=>"Y"));
			$form->addElement(	'textarea', "header[note]", "Note", array("rows"=>5, "cols"=>40));
			// Address
			$form->addElement(	'header', 'title', "Shipping address <a href=\"#\" title=\"Delivery code or shipping address must be entered\">[?]</a>");
			$form->addElement(	'select', "header[t_cdel]", "Customer delivery code", $cdelList);
			$form->addElement(	'text', "header[t_nama]", "Address line 1", array('style'=>'width:300px;'));
			$form->addElement(	'text', "header[t_namb]", "Address line 2", array('style'=>'width:300px;'));
			$form->addElement(	'text', "header[t_namc]", "Address line 3", array('style'=>'width:300px;'));
			$form->addElement(	'text', "header[t_namd]", "Address line 4", array('style'=>'width:300px;'));
			$form->addElement(	'text', "header[t_name]", "Address line 5", array('style'=>'width:300px;'));
			$form->addElement(	'text', "header[t_namf]", "Address line 6", array('style'=>'width:300px;'));
			// SO Lines
			$i=1;
			foreach($DATA_FORM_DEFAULT['lines'] as $line){
				$form->addElement(	'header', 'title', "Line# $i");
				$pn = &$form->addElement(	'text', "lines[$i][t_item]", "PN#");
				$qty = &$form->addElement(	'text', "lines[$i][t_oqua]", "Qty");
				$pn->setValue($line['t_item']);
				$qty->setValue($line['t_oqua']);
				$i++;
			}
			// Buttons
			$form->addElement('submit', 'btnSubmit', 'Submit');
			$form->addElement('reset', 	'btnReset',  'Reset');
			// Add defaults values
			$form->setDefaults($DATA_FORM_DEFAULT);
			// Set required
			$form->addRule("header[t_cuno]", 'This is required', 'required');
			$form->addRule("header[t_eono]", 'This is required', 'required');
			$form->addRule("header[t_ddat]", 'This is required', 'required');

			if(!$form->validate()){
				$body = $form->toHTML();
				break;
			}

			// Get data submitted and process
			$vars = tldUtils::cleanupFormInput($form->exportValues());
			// Add few details to send to SOAP
			$vars['header']['erp']=$TO_ERP;
			$vars['header']['datetime']=date('Y-m-d H:i:s');
			// Check the delivery
			if(empty($vars['header']['t_cdel'])
			&& empty($vars['header']['t_nama'])){
				$DEFAULT_ERROR[] = "ERROR: Delivery address is required";
				$body = $form->toHTML();
				break;
			}
			// Post the SO to BAAN
			//$e = tldERP::transmitPurchaseOrder($src, $xml, $TO_ERP, $opts="");
			//$e = tldSO::post($TO_ERP,$vars);
			if(is_string($e)){
				$DEFAULT_ERROR[] = "ERROR: SO not submitted to BAAN<br>Reason: $e";
				break;
			}
			$e = $msg->addLogEntry($user->getID(),"SO submitted to BAAN");
			$body = "MSG#$id successfully submitted to BAAN";
*/
		break;
		default:
			$DEFAULT_ERROR[] = "ERROR: Functionnality not available for {$header['doc_type']} documents";
		break;
		}
	break;
	default:
		$body = _getGeneralTab();
	break;
	}
break;
default:
	$body = $smarty->fetch("finance/msg/homepage.msg.tpl");
	// Get matrix
	$matrix = new tldMatrix(
		tldERPMSG::countByERPStatus(),
		"to_fullname", "status", "nb",
		"$php_self?m[0]=msg&m[1]=listing&m[2]=byERPStatus",
		"MSG Count by Status, ERP company to",
		array("yItems"=>array('PENDING','CLOSED'))
	);
	$body.= $matrix->fetch();
	// Get latest
	$body.= _getListing(tldERPMSG::byLatest(),"Latest MSG");
break;
}

function _getGeneralTab(){
	global $header,$xItems,$id;
	$report = new tldAssocTable(
        $header,
        $xItems,
        array("title"=>"MSG#$id")
	);
	return $report->fetch();
}

function _getListing($rows,$title){
	global $php_self,$xItems;
	$report = new tldReportColumnar(
		$rows,
		array(
			"xItems"=>$xItems,
			"title"=>$title,
			"links"=>array("id"=>"$php_self?m[0]=msg&m[1]=view&id=")
        )
	);
	return $report->fetch();
}
?>