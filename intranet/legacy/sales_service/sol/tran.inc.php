<?php
//display currency $dispcur is set in the main view.inc.php controller

$SSO_GRP = ["gg_ADMIN", "gg_ACCT", "role_SA"];
$ERP_GRP = ["gg_ADMIN", "gg_ACCT", "role_PSM", "role_PSE", "role_PSA"];

if (!$user->isInGroup(array_merge($SSO_GRP,$ERP_GRP))){
    $DEFAULT_ERROR[] = "ERROR: You do not have permission to access this section"; 
    return;
}

$DEFAULT_MENU .=<<<EOF
<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<a href="$php_self?m[0]=sol&m[1]=view&m[2]=tran&id=$id" title="View Summary Page">Summary</a>&nbsp;|&nbsp;
<a href="$php_self?m[0]=sol&m[1]=view&m[2]=tran&m[3]=sso&id=$id" title="View SSO Transactions">SSO</a>&nbsp;|&nbsp;
<a href="$php_self?m[0]=sol&m[1]=view&m[2]=tran&m[3]=erp&id=$id" title="View Factory Transactions">Factory</a>
&nbsp;|&nbsp;<a href="$php_self?m[0]=sol&m[1]=view&m[2]=tran&m[3]=editDateOfZeroBacklog&id=$id" title="Edit Dates of Zero Backlog">Edit Dates of Zero Backlog</a>
EOF;

$tsum = $sol->getTranSummary($sol->getDCUR());

if(isset($sess['return_url'])){
	$DEFAULT_MENU .=<<<EOF
    <a href="${sess['return_url']}">
    <img src="/shared/bluesphere/32x32/actions/backward.png" 
    align="right" title="Return to ACTIVE BACKLOG REPORT"></a>
EOF;
}

switch($m[3]){
case 'sso':
    $DEFAULT_MENU .=<<<EOF
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<a href="$php_self?m[0]=sol&m[1]=view&m[2]=tran&m[3]=sso&m[4]=addRev&id=$id" title="Add SSO Revenue Transaction">Add Rev Tran</a>
&nbsp;|&nbsp;<a href="$php_self?m[0]=sol&m[1]=view&m[2]=tran&m[3]=sso&m[4]=finalize&id=$id" title="Finalize SSO Transactions">Finalize</a>
EOF;

    if(!$user->isInGroup(array_merge($SSO_GRP))){
        $DEFAULT_ERROR[] = "ERROR: You do not have permission to access this section"; 
        break;
    }

    switch($m[4]){
    case 'finalize':
        if($sol->getDZKSSO() <> '0000-00-00'){
            $DEFAULT_ERROR[] = "ERROR: SSO Transactions have already been finalized";
            $body .= getViewTranSSO();
            break;
        }

        $form = new HTML_QuickForm('frmFinalize', 'post');
        $form->addElement('hidden', 'm[0]', 'sol');
        $form->addElement('hidden', 'm[1]', 'view');
        $form->addElement('hidden', 'm[2]', 'tran');
        $form->addElement('hidden', 'm[3]', 'sso');
        $form->addElement('hidden', 'm[4]', 'finalize');
        $form->addElement('hidden', 'id', $id);
        $form->addElement('header', 'title', 'Finalize SSO transactions.');
        if($tsum['ssok_tot'] <> 0){
            $form->addElement(	'select', "action",
                    'What to do with remaining backlog amount',
                    array("cancel"=>"Cancel remaining backlog by creating a negative booking",
                        "ignore"=>"Just finalize the SSO transactions")
            );
        }
        $form->addElement(	'select', "conf",
			'Select Y to proceed...',
			array(""=>"", "Y"=>"Y")
		);
		$form->addElement('textarea', 'notes', 'Notes', array("rows"=>5, "cols"=>40));
        $form->addElement('submit', 'btnSubmit', 'Finalize SSO Transactions...');
 		$form->addRule("conf", 'Required', 'required');
        if(!$form->validate()){
            if($tsum['ssok_tot'] <> 0){
                $DEFAULT_ERROR[] =  'WARNING: There is a remaining backlog amount of '.
                        $sol->getDCUR().$tsum['ssok_tot'];
            }
            $body .= $form->toHTML();
            break;
        }
        $vars = tldUtils::cleanupFormInput($form->exportValues());
        if($vars['conf'] <> 'Y'){
            $DEFAULT_ERROR[] =  "WARNING: Confirmation required before proceeding...";
            $body .= $form->toHTML();
            break;
        }
        switch($vars['action']){
            case 'cancel':
                $e = $sol->cancelKSSO($vars['notes']);
                if(is_numeric($e)){
                    $DEFAULT_ERROR[] = "Remaining SSO backlog cancelled successfully.";
                }else{
                    $DEFAULT_ERROR[] = "ERROR: problem cancelling remaining backlog, returned error was $e";
                }
                break;
             default:
                $e = $sol->setDZKSSO(date("Y-m-d"));
                if(is_string($e)){
                    $DEFAULT_ERROR[] = "ERROR: problem setting Date of Zero Backlog, returned error was $e";
                }else{
                    $DEFAULT_ERROR[] = "SOL successfully finalized.";
                }
                break;
        }
    break;
    case 'addRev':
        if($sol->getDZKSSO() <> '0000-00-00'){
            $DEFAULT_ERROR[] = "ERROR: SSO Transactions have already been finalized";
            $body .= getViewTranSSO();
       		break;
        }
        // Get list of ER
        $units = tldSORUnit::byParent($id);
		// Get form
        $form = new HTML_QuickForm('frmAddSSOR', 'post');
        $form->addElement('hidden', 'm[0]', 'sol');
        $form->addElement('hidden', 'm[1]', 'view');
        $form->addElement('hidden', 'm[2]', 'tran');
        $form->addElement('hidden', 'm[3]', 'sso');
        $form->addElement('hidden', 'm[4]', 'addRev');
        $form->addElement('hidden', 'id', $id);
        // Transaction field
        $form->addElement('header', 'title', 'Add SSO Revenue Transaction');
        $form->addElement('text', "rrd", 'SSO Revenue Recognition Date', ["class" => "datepicker"]);
        $form->addElement('text', 'nref', 'Invoice Number');
        $form->addElement('text', 'dref', 'Invoice Date', ["class" => "datepicker"]);
        $form->addElement('select', 'tcur', 'Currency', tldForex::getCurrencyList());
        $form->addElement('text', 'tval', 'Revenue Value');
        $form->addElement('textarea', 'notes', 'Notes', array("rows"=>5, "cols"=>40));
    	// ER selection
        $form->addElement(	'header', 'title', 'Select ER(s) for this transaction');
	    foreach ($units as $row){
	    	if(empty($row['sn']) || !empty($row['tranid_sso'])) continue; // exclude unassigned units or with tranid not null
	    	$label = "SN#{$row['sn']} {$row['model']} {$row['man_location']} - Batch qty:".$row['er_batch_qty'];
			$form->addElement('checkbox', "ers[{$row['erid']}]", NULL, $label);
	    }
		$form->addElement('submit', 'btnSubmit', 'Add Revenue transaction...');
        $form->addRule("tcur", 'Required', 'required');
        $form->addRule("tval", 'Required', 'required');
        $form->addRule("nref", 'Required', 'required');
        $form->setDefaults(array(
			"rrd"=>date("Y-m-d"),
        	"dref"=>date("Y-m-d"),
			"tcur"=>$sol->getDCUR(),
			"tval"=>$tsum['ssok_tot'])
        );
        
        if(!$form->validate()){
	        if(!$sol->isfullyAllocated()){
	        	$DEFAULT_ERROR[] = "WARNING: All or some Units have not been assigned yet, you will not be able to 
	        	link ER(s) to this transaction for these units.";
	        }
            $body .= $form->toHTML();
            $body .= getViewTranSSO();
            break;
        }
        $vars = tldUtils::cleanupFormInput($form->exportValues());
        $datesBooking = tldSORTran::getPostDateByType('B',$id, 'SSO');

        // Check if there is (B)ooking Transactions set
        $condition = true;
        // Check (R)evenue posting date VS (B)ooking posting date
        $dateRevenue = new \DateTimeImmutable($vars['rrd']);
//        I'm not even sure that this loop is/was required
        foreach ($datesBooking as $date) {
            $dateBooking = new \DateTimeImmutable($date['dtran']);
            if ($dateRevenue < $dateBooking) {
                $condition = false;
                break;
            }
        }
        if($condition == true){
            $e = $sol->addSORTranSSOR($vars['rrd'], $vars['tcur'],
                $vars['tval'], $vars['notes'], $vars['nref'], $vars['dref']);
        }else{
        	$DEFAULT_ERROR[] = "ERROR: (R)evenue posting date must be greater than (B)ooking posting date";
        	$body .= $form->toHTML();
        	break;
        }
        if(is_numeric($e)){
            $DEFAULT_ERROR[] = "SSO Revenue Transaction successfully added";
			// Update transaction id on ER
            if(!empty($vars['ers'])){
				foreach(array_keys($vars['ers']) as $erid){
            		$er = new tldEquipment($erid);
            		if(empty($er->itsDetails['tranid_sso'])){
            			// Set the transaction ID to the ER
            			$error = $er->setTranID($e,"SSO");
            			if(is_string($error)){
            				$DEFAULT_ERROR[] = "INTERNAL ERROR: SSO transaction#$e not set to ER#$erid -> $error";
            				continue;
            			}
            			// Set the revenue recognition date by default to the ER
            			$error = $er->setRRD($vars['rrd'],"SSO");
            			if(is_string($error)){
            				$DEFAULT_ERROR[] = "INTERNAL ERROR: SSO RRD not set to ER#$erid -> $error";
            				continue;
            			}
            			// Add into log
            			$er->addLogEntry(
            				$user->getID(),
            				"Set SSO transaction#$e with RRD as of ".$vars['rrd']
            			);
            		}else{
            			$DEFAULT_ERROR[] = "ERROR: ER#$erid not linked, ER have been already recognize in the trans#".$er->itsDetails['tranid_sso'];
            		}
            	}
            }
        }else{
        	$DEFAULT_ERROR[] = "ERROR: problem creating transaction, returned error was $e";
        }
        $body .= getViewTranSSO();
     break;
     default:
         $body .= getViewTranSSO();
    }
break;
case 'erp':
	$DEFAULT_MENU .=<<<EOF
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<a href="$php_self?m[0]=sol&m[1]=view&m[2]=tran&m[3]=erp&m[4]=addRev&id=$id" title="Add Factory Revenue Transaction">Add Rev Tran</a>
&nbsp;|&nbsp;<a href="$php_self?m[0]=sol&m[1]=view&m[2]=tran&m[3]=erp&m[4]=finalize&id=$id" title="Finalize Factory Transactions">Finalize</a>
EOF;

    if(!$user->isInGroup(array_merge($ERP_GRP))){
        $DEFAULT_ERROR[] = "ERROR: You do not have permission to access this section"; 
        break;
    }

	switch($m[4]){
    case 'finalize':
        if($sol->getDZKERP() <> '0000-00-00'){
            $DEFAULT_ERROR[] = "ERROR: ERP Transactions have already been finalized";
            $body .= getViewTranERP();
            break;
        }

        $form = new HTML_QuickForm('frmFinalize', 'post');
        $form->addElement('hidden', 'm[0]', 'sol');
        $form->addElement('hidden', 'm[1]', 'view');
        $form->addElement('hidden', 'm[2]', 'tran');
        $form->addElement('hidden', 'm[3]', 'erp');
        $form->addElement('hidden', 'm[4]', 'finalize');
        $form->addElement('hidden', 'id', $id);
        $form->addElement('header', 'title', 'Finalize Factory transactions.');
        if($tsum['erpk_tot'] <> 0){
            $form->addElement(	'select', "action",
                    'What to do with remaining backlog amount',
                    array("cancel"=>"Cancel remaining backlog by creating a negative booking",
                        "ignore"=>"Just finalize the Factory transactions")
            );
        }
        $form->addElement(	'select', "conf",
			'Select Y to proceed...',
			array(""=>"", "Y"=>"Y")
		);
        $form->addElement('textarea', 'notes', 'Notes', array("rows"=>5, "cols"=>40));
        $form->addElement('submit', 'btnSubmit', 'Finalize Factory Transactions...');
     	$form->addRule("conf", 'Required', 'required');
        if(!$form->validate()){
            if($tsum['erpk_tot'] <> 0){
                $DEFAULT_ERROR[] =  'WARNING: There is a remaining backlog amount of '.
                        $sol->getDCUR().$tsum['erpk_tot'];
            }
            $body .= $form->toHTML();
            break;
        }
        $vars = tldUtils::cleanupFormInput($form->exportValues());
        if($vars['conf'] <> 'Y'){
            $DEFAULT_ERROR[] =  "WARNING: Confirmation required before proceeding...";
            $body .= $form->toHTML();
            break;
        }
        switch($vars['action']){
            case 'cancel':
                $e = $sol->cancelKERP($vars['notes']);
                if(is_numeric($e)){
                    $DEFAULT_ERROR[] = "Remaining ERP backlog cancelled successfully.";
                }else{
                    $DEFAULT_ERROR[] = "ERROR: problem cancelling remaining backlog, returned error was $e";
                }
                break;
            default:
                $e = $sol->setDZKERP(date("Y-m-d"));
                if(is_string($e)){
                    $DEFAULT_ERROR[] = "ERROR: problem setting Date of Zero Backlog, returned error was $e";
                }else{
                    $DEFAULT_ERROR[] = "SOL successfully finalized.";
                }
                break;
        }
    break;
    case 'addRev':
        if($sol->getDZKERP() <> '0000-00-00'){
            $DEFAULT_ERROR[] = "ERROR: ERP Transactions have already been finalized";
            $body .= getViewTranERP();
            break;
        }
        // Get list of ER
        $units = tldSORUnit::byParent($id);
        // Display form
        $form = new HTML_QuickForm('frmAddRev', 'post');
        $form->addElement('hidden', 'm[0]', 'sol');
        $form->addElement('hidden', 'm[1]', 'view');
        $form->addElement('hidden', 'm[2]', 'tran');
        $form->addElement('hidden', 'm[3]', 'erp');
        $form->addElement('hidden', 'm[4]', 'addRev');
        $form->addElement('hidden', 'id', $id);
        // Transaction
        $form->addElement('header', 'title', 'Add Factory Revenue Transaction');
        $form->addElement('text', "rrd", 'Factory Revenue Recognition Date', ['class' => 'datepicker']);
        $form->addElement('text', 'nref', 'Invoice Number');
        $form->addElement('text', 'dref', 'Invoice Date', ['class' => 'datepicker']);
        $form->addElement('select', 'tcur', 'Currency',
			tldForex::getCurrencyList()
        );
        $form->addElement('text', 'tval', 'Revenue Value');
        $form->addElement('textarea', 'notes', 'Notes', array("rows"=>5, "cols"=>40));
		// ER selection
        $form->addElement(	'header', 'title', 'Select ER(s) for this transaction');
	    foreach ($units as $row){
	    	if(empty($row['sn']) || !empty($row['tranid_erp'])) continue; // exclude unassigned units or with tranid not null
	    	$label = "SN#{$row['sn']} {$row['model']} {$row['man_location']} - Batch qty:".$row['er_batch_qty'];
			$form->addElement('checkbox', "ers[{$row['erid']}]", NULL, $label);
	    }
        $form->addElement('submit', 'btnSubmit', 'Add Revenue transaction...');
        $form->addRule("tcur", 'Required', 'required');
        $form->addRule("tval", 'Required', 'required');
        $form->addRule("nref", 'Required', 'required');
        $form->setDefaults(array(
            "rrd"=>date("Y-m-d"),
			"dref"=>date("Y-m-d"),
            "tcur"=>$sol->getDCUR(),
            "tval"=>$tsum['erpk_tot'])
        );
        
        if(!$form->validate()){
	        if(!$sol->isfullyAllocated()){
	        	$DEFAULT_ERROR[] = "WARNING: All or some Units have not been assigned yet, you will not be able to 
	        	link ER(s) to this transaction for these units.";
	        }
            $body .= $form->toHTML();
            $body .= getViewTranERP();
            break;
        }
        $vars = tldUtils::cleanupFormInput($form->exportValues());
        $datesBooking = tldSORTran::getPostDateByType('B',$id, 'ERP');
        // Check if there is (B)ooking Transactions set
        $condition = true;
        // Check (R)evenue posting date VS (B)ooking posting date
        $dateRevenue = new \DateTimeImmutable($vars['rrd']);
//        I'm not even sure that this loop is/was required
        foreach ($datesBooking as $date) {
            $dateBooking = new \DateTimeImmutable($date['dtran']);
            if($dateRevenue < $dateBooking) {
                $condition = false;
                break;
            }
        }
        if($condition == true){
        $e = $sol->addSORTranERPR($vars['rrd'], $vars['tcur'],
        $vars['tval'], $vars['notes'], $vars['nref'], $vars['dref']);
        }else{
        	$DEFAULT_ERROR[] = "ERROR: (R)evenue posting date must be greater than (B)ooking posting date";
        	$body .= $form->toHTML();
        	break;
        }
        if(is_numeric($e)){
            $DEFAULT_ERROR[] = "Factory Revenue Transaction successfully added";
        	// Update transaction id on ER
            if(!empty($vars['ers'])){
				foreach(array_keys($vars['ers']) as $erid){
            		$er = new tldEquipment($erid);
            		if(empty($er->itsDetails['tranid_erp'])){
	            		// Set the transaction ID to the ER
	            		$error = $er->setTranID($e,"ERP");
	            		if(is_string($error)){
	            			$DEFAULT_ERROR[] = "INTERNAL ERROR: Factory transaction#$e not set to ER#$erid -> $error";
	            			continue;
	            		}
	            		// Set the revenue recognition date by default to the ER
	            		$error = $er->setRRD($vars['rrd'],"ERP");
	            		if(is_string($error)){
	            			$DEFAULT_ERROR[] = "INTERNAL ERROR: Factory RRD not set to ER#$erid -> $error";
	            			continue;
	            		}
	            		// Add into log
	            		$er->addLogEntry(
	            			$user->getID(),
	            			"Set Factory transaction#$e with RRD as of ".$vars['rrd']
	            		);
					}else{
            			$DEFAULT_ERROR[] = "ERROR: ER#$erid not linked, ER have been already recognize in the trans#".$er->itsDetails['tranid_erp'];
            		}
            	}
            }
        }else{
        	$DEFAULT_ERROR[] = "ERROR: problem creating factory revenue transaction, returned error was $e";
        }
        $body .= getViewTranERP();
      break;
        default:
            $body .= getViewTranERP();
    }
break;
    case 'editDateOfZeroBacklog':
        if (!$user->isInGroup(["role_FC", "gg_ADMIN", "role_SA"])) {
            $DEFAULT_ERROR[] = "ERROR: You do not have permission to access this section";
        }
        // Get form
        $form = new HTML_QuickForm('frmeditDateOfZeroBacklog', 'post');
        $form->addElement('hidden', 'm[0]', 'sol');
        $form->addElement('hidden', 'm[1]', 'view');
        $form->addElement('hidden', 'm[2]', 'tran');
        $form->addElement('hidden', 'm[3]', 'editDateOfZeroBacklog');
        $form->addElement('hidden', 'id', $id);
        $form->addElement('text', 'dzk_sso', 'Date of Zero Backlog, SSO');
        $form->addElement('text', 'dzk_erp', 'Date of Zero Backlog, Factory');
        $form->addElement('submit', 'btnSubmit', 'Submit');

        $form->setDefaults($header);

        if(!$form->validate()){
            $body .= $form->toHTML();
            break;
        }

        $rawData = $form->exportValues();
        $vars = tldUtils::cleanupFormInput($rawData);
        // update the SOL
        unset($vars['m'],$vars['id'],$vars['btnSubmit']);
        $e = $sol->updateHeader($vars);
        if(is_string($e)){
            $DEFAULT_ERROR[] = $e;
            break;
        }
        $sol->refresh();

        if(is_EVPstatusPassed($header['status'])){
            $fieldToCheck = [
                "dzk_sso",
                "dzk_erp",
            ];
            _updateProcessAfterEVP_APPROVAL(
                $SOL_FIELDS,
                $fieldToCheck,
                $header,
                $rawData
            );
        }
        $body .= "<p>SOL#$id successfully updated !</p>";
        break;
	default:
    $report = new tldAssocTable(
        $sol->getTranSummary($dispcur),
        array(
            "ssob_tot"=>"SSO Bookings",
            "ssor_tot"=>"SSO Revenues",
            "ssok_tot"=>"SSO Backlog",
            "erpb_tot"=>"Factory Bookings",
            "erpr_tot"=>"Factory Revenues",
            "erpk_tot"=>"Factory Backlog"
        ),
        array("title"=>"Transaction Summary ($dispcur)")
    );
    $body .= $report->fetch();
break;
}

function getViewTranSSO(){
    global $id, $dispcur, $DEFAULT_ERROR;
    $rows = tldSORTran::byParentGroup($id, 'SSO', $dispcur);
    if(is_array($rows)){
        $report = new tldReportColumnar($rows,
            array(
            	"xItems"=>array(
                    "id"=>"TRAN#",
                    "dt"=>"Date Entered",
                    "ttyp"=>"Trans Type<br>(B)ooking<br>(R)evenue",
                    "dtran"=>"Posting Date",
                    "nref"=>"Invoice Number",
            		"dref"=>"Invoice Date",
                    "tcur"=>"Original Currency",
                    "tval"=>"Original Value",
                    "dcur_rate"=>"Rate",
                    "dcur"=>"Currency",
                    "tval_dcur"=>"Value",
                    "notes"=>"Notes"
                ),
    			"links"=>array(
    				"id"=>"/en/private/finance/finance.php?m[0]=sor_tran&m[1]=view&id="
    			),
                "title"=>"SSO Transactions ($dispcur)"
            )
        );
        return $report->fetch();
    }else{
        $DEFAULT_ERROR[] = $rows;
    }
}

function getViewTranERP(){
    global $id, $dispcur, $DEFAULT_ERROR;
    $rows = tldSORTran::byParentGroup($id, 'ERP', $dispcur);
    if(is_array($rows)){
        $report = new tldReportColumnar(
        	$rows,
        	array(
        		"xItems"=>array(
        			"id"=>"TRAN#",
					"dt"=>"Date Entered",
					"ttyp"=>"Trans Type<br>Booking<br>Revenue",
					"dtran"=>"Posting Date",
					"nref"=>"Invoice Number",
					"dref"=>"Invoice Date",
					"tcur"=>"Original Currency",
					"tval"=>"Original Value",
					"dcur_rate"=>"Rate",
					"dcur"=>"Currency",
					"tval_dcur"=>"Value",
					"notes"=>"Notes"
				),
				"links"=>array(
					"id"=>"/en/private/finance/finance.php?m[0]=sor_tran&m[1]=view&id="
				),
				"title"=>"Factory Transactions ($dispcur)"
            )
        );
        return $report->fetch();
    }else{
        $DEFAULT_ERROR[] = $rows;
    }
}
?>
