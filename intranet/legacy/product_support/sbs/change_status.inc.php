<?php 
$DEFAULT_TITLE .= "\Change Status";

if($sb->getStatus()=='LOCKED'){
    $DEFAULT_ERROR[] = "You can not move this SB anymore, please use SB3 instead";
    return;
}

// List of users that tasks will be created when changing status
$EVP_NOTIFICATION_LIST = array(
	"300"=>26,	    // Scott gordon
    "310"=>21,      // Rene De la llana
	"540"=>1555,	// Marko Schottler
	"560"=>100,     // Christophe Lesbaudy
	"600"=>954,	    // Chris tam
	"650"=>66	    // Alex Lam
);

$allowed = $sb->getStatusAllowed();
if(empty($allowed)){
    $DEFAULT_ERROR[]="Can not change status after ".$sb->getStatus();
    return;
}

$form = new HTML_QuickForm('frmChangeStatus', 'post');
$form->addElement(	'header', 'title', 'Change status to ->'.$allowed['fwd']);
$form->addElement(	'hidden', 'm[0]', 'sbs');
$form->addElement(	'hidden', 'm[1]', 'view');
$form->addElement(	'hidden', 'm[2]', 'change_status');
$form->addElement(	'hidden', 'id', $id);
$form->addElement(  'textarea', 'comment', 'Comment', array("rows"=>10, "cols"=>40));
if($allowed['back']){
	$form->addElement(	'submit', 'direction', 'Backwards');
}
$form->addElement(	'submit', 'direction', 'Forwards');

if(!$form->validate()){
	$body .= $form->toHTML();
	return;
}

$a = tldUtils::cleanupFormInput($form->exportValues());
// Get status
if($a['direction'] == 'Forwards'){
    $status = $allowed['fwd'];
}else{
    $status = $allowed['back'];
}
// Change SB status
$error = $sb->changeStatus(	$status, $user->getID(), array('msg'=>$msg));
if(is_string($error)){
	$DEFAULT_ERROR[] = "ERROR: there was a problem changing status, returned error was... $error<br>";
	return;
}

// Status was changed without error
switch($status){
case 'APPROVAL':
	// 1 - Creation of BP to collect EVP approvals
	$a["owner"] = $user->getID();
	$a["short_desc"] = "SB#$id: Approval";
	$a["long_desc"] ="A new SB proposal has been created and requires EVP approvals.";
	$bpid = tldBP::insert($id, $a, "SB");
	if(!is_numeric($bpid)){
		$DEFAULT_ERROR[] = "ERROR: There was a problem creating a BP for SB Approval";
		return;
	}
	$bp = new tldBP($bpid);
	$bp->addLogEntry($user->getID(),"BP CREATED for SB#$id");
	// 2 - Get task description
	$header['urgency']=strtolower(substr($header['urgency'],3));
	$smarty->assign("sb", $header);
	$smarty->assign("bpid", $bpid);
	$task_message = $smarty->fetch("product_support/sbs/change_status/approval.message.tpl");
	// 3 - Send tasks for each EVP linked to the created BP
	foreach ($EVP_NOTIFICATION_LIST as $sso=>$evp){
		$seqVals = array(
			"assignee"	=> $evp,
			"assignor"	=> $user->getID(),
			"task"		=> TldDatabase::escape($task_message),
			"due_date"	=> array("value"=>5, "unit"=>"DAY"),
			"bu_id"		=> $user->itsDetails["bu_id"],
			"mod_seq"   => "SINGLE_LEVEL"
		);
		$taskid = $bp->addSEQ($seqVals,0);
		if(is_numeric($taskid)){
			$task = new tldTask($taskid);
			$task->notifyAssignee("Your approval of this new SB required.".$task_message,
				"SB#$id EVP APPROVAL");
		}
		else $DEFAULT_ERROR[] =  "ERROR: There was a problem creating tasks for user with id $evp";
	}
break;
case 'ER_SELECTION':
	// 1 - Creation of BP to collect EVP approvals
	$a["owner"] = $user->getID();
	$a["short_desc"] = "SB#$id: ER Selection";
	$a["long_desc"] ="A new SB has been approved and requires ER selection.";
	$bpid = tldBP::insert($id, $a, "SB");
	if(!is_numeric($bpid)){
		$DEFAULT_ERROR[] = "ERROR: There was a problem creating a BP for SB ER Selection";
		return;
	}
	// 2 - Get task description
	$header['urgency']=strtolower(substr($header['urgency'],3));
	$smarty->assign("sb", $header);
	$smarty->assign("bpid", $bpid);
	$task_message = $smarty->fetch("product_support/sbs/change_status/er_selection.message.tpl");
	// 3 - Send tasks for each EVP
	$bp = new tldBP($bpid);
	$bp->addLogEntry($user->getID(),"BP CREATED for SB#$id");
	foreach ($EVP_NOTIFICATION_LIST as $sso=>$evp){
		$seqVals = array(
			"assignee"	=> $evp,
			"assignor"	=> $user->getID(),
			"task"		=> TldDatabase::escape($task_message),
			"due_date"	=> array("value"=>5, "unit"=>"DAY"),
			"bu_id"		=> $user->itsDetails["bu_id"],
            "mod_seq"   => "SINGLE_LEVEL"
		);
		$taskid = $bp->addSEQ($seqVals,0);
		if(is_numeric($taskid)){
			$task = new tldTask($taskid);
			$task->notifyAssignee("Your ER selection of this SB is required.".$task_message,
				"SB#$id ER SELECTION");
		}
		else $DEFAULT_ERROR[] =  "ERROR: There was a problem creating tasks for user with id $evp";
	}
break;
case 'IMPLEMENTATION':
	// 1 - Creation of BP to collect EVP approvals
	$a["owner"] = $user->getID();
	$a["short_desc"] = "SB#$id: IMPLEMENTATION";
	$a["long_desc"] ="A new SB requires implementation.";
	$bpid = tldBP::insert($id, $a, "SB");
	if(!is_numeric($bpid)){
		$DEFAULT_ERROR[] = "ERROR: There was a problem creating a BP for SB IMPLEMENTATION";
		return;
	}
	// 2 - Get task description
	$header['urgency']=strtolower(substr($header['urgency'],3));
	$smarty->assign("sb", $header);
	$smarty->assign("bpid", $bpid);
	$task_message = $smarty->fetch("product_support/sbs/change_status/implementation.message.tpl");
	// 3 - Send tasks for each EVP
	$bp = new tldBP($bpid);
	$bp->addLogEntry($user->getID(),"BP CREATED for SB#$id");
	foreach($EVP_NOTIFICATION_LIST as $sso=>$evp){
		$seqVals = array(
			"assignee"	=> $evp,
			"assignor"	=> $user->getID(),
			"task"		=> TldDatabase::escape($task_message),
			"due_date"	=> array("value"=>5, "unit"=>"DAY"),
			"bu_id"		=> $user->itsDetails["bu_id"],
            "mod_seq"   => "SINGLE_LEVEL"
		);
		$taskid = $bp->addSEQ($seqVals,0);
		if(is_numeric($taskid)){
			$task = new tldTask($taskid);
			$task->notifyAssignee("Your implementation of this SB is required.".$task_message,
				"SB#$id IMPLEMENTATION");
		}
		else $DEFAULT_ERROR[] =  "ERROR: There was a problem creating tasks for user with id $evp";
	}
break;
}

// Add log entry
$m = $status;
if($a['comment']) $m .= "\n".$a['comment'];
$sb->addLogEntry($user->getID(), $m);
$sb->refresh();

// Get general Tab
$header = $sb->getHeader();
$body .= _getGeneralTab();

?>