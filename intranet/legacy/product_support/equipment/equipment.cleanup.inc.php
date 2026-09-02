<?php
if(!$user->isInGroup(array("er_cleanup","role_CSM","role_EVP","gg_ADMIN","superuser"))){
	$DEFAULT_ERROR[] = "ERROR: You do not have permission to access this function";
	return;
}

$DEFAULT_TITLE .= "\Cleanup Tools";
$DEFAULT_MENU.=<<<EOF
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<a href="$php_self?m[0]=equipment&m[1]=cleanup">Home</a>
&nbsp;|&nbsp;<a href="$php_self?m[0]=equipment&m[1]=cleanup&m[2]=transSSOByCust">Transfer SSO By Customer</a>
EOF;

switch($m[2]){
case 'transSSOByCust':
	$DEFAULT_TITLE .= "\Transfer";
	// Listing
	$customerList = tldCustomer::getList("smartyOptions");
	// Form
	$form = new HTML_QuickForm('frmTransfer', 'post');
	$form->addElement(	'hidden', 'm[0]', 'equipment');
	$form->addElement(	'hidden', 'm[1]', 'cleanup');
	$form->addElement(	'hidden', 'm[2]', 'transSSOByCust');
	$form->addElement(	'header', 'title', "Transfer ER SSO By Customer");
	$form->addElement(	'select', 'sales_org', 'Select Sales Organization to Assign', array(""=>"") + 
		tldLocation::getSalesOrgList("smartyOptionsLocationLocation"));
	$form->addElement(	'select', 'customer_id', 'From ERs With Customer Name (END USER)', 
	    array(""=>"")+$customerList);
	$form->addElement(	'submit', 'btnSubmit', 'Do Transfer !');
	$form->addRule('sales_org', 'Required', 'required');
	$form->addRule('customer_id', 'Required', 'required');
	
	if(!$form->validate()){
		$body .= $form->toHTML();
		break;
	}
	
	$vars = tldUtils::cleanupFormInput($form->exportValues());
	$customer_id = $vars['customer_id'];
	$sales_org = $vars['sales_org'];
	$query = <<<EOF
	UPDATE
		service
	SET
		sales_org='$sales_org'
	WHERE
		customer_id=$customer_id
EOF;
	$e = tldUtils::sqlQuery($query);
	if(is_string($e)){
	    $DEFAULT_ERROR[] = "ERROR: Internal error, can not update. Reason: $e";
	    break;
	}
	$nbAffected = TldDatabase::affectedRows();
	$body .= 'Done! Updated '.$nbAffected.' ER Records!';
	if($nbAffected==0) break;

	// Create Task to SAM
	$erp = tldLocation::getERPByLocation($sales_org);
	$samGrp = new tldGroup("role_SAM",$erp);
	$samList = $samGrp->getUserlist();
	if(empty($samList)){
	    $samList[0]['id'] = 1220; // Yves Crespel
	}
	$customer_name = TldDatabase::escape($customerList[$customer_id]);
	$sales_org_name = TldDatabase::escape($sales_org);
	$taskData = array(
		"assignee"=>$samList[0]['id'],
		"assignor"=>$user->getID(),
		"task"=> <<<EOF
<p>All ERs from customer $customer_name has been transfered to SSO $sales_org_name</p>
<p>Please create manually a new CRT for customer $customer_name in SSO $sales_org_name</p>
<p>Then, associate new Bann Customer number (CUNO) if exists and link contacts</p>
<p>Call MIS if any problem</p>
EOF
	);
    $eTask = tldTask::insert($customer_id, $taskData, 'ECUST');
    if(is_string($eTask)) {
        $DEFAULT_ERROR[] =  "Could not create new task for Sales Admin Managers to manage CRT. Reason: $eTask";
        break;
    }
    // Notify
    $task = new tldTask($eTask);
    $message = <<<EOF
Task #$eTask has been created.\n<br>
Please log in to the TLD-GSE intranet and go to the calendar module to view your open tasks.\n<br>
<a href="https://www.tld-gse.com/en/private/calendar/calendar.php?m[0]=tasks&m[1]=task&m[2]=view&id=$eTask">
Click here to go to Task.
</a>
EOF;
    $task->notifyAssignee(
        $message,
        "Task# ".$task->itsID." created: check CRT from ER SSO transfer"
    );
    // Confirm
    $body.= "<br>Task#$eTask created to manage CRT";
break;
default:
	$body .= "<h2>ER Cleanup Tools</h2>";
break;
}

