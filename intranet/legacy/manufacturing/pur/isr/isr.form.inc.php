<?php
$overlib = $smarty->fetch('overlib.inc.js.tpl');
$overlib .= '<script type="text/javascript">$(function(){$(".overlib").overlib()});</script>';
$autosuggest = $smarty->fetch('autosuggest.inc.js.tpl');
$autosuggest .= '<script type="text/javascript">$(function(){var uci=$("[name=assignee]").autosuggest({message:"Begin to type a portion of the name for auto suggestions",
onChange:function(o){$(uci.input.display).val($(o.input.display).val());
$(uci.input.value).val($(o.input.value).val());}});});</script>';

$smarty->assign('html_head', $overlib . $autosuggest);
switch($m[2]){
case 'byNum':
	$form = new HTML_QuickForm('frmByNum', 'post');
	$form->addElement(	'hidden', 'm[0]', 'isr');
	$form->addElement(	'hidden', 'm[1]', 'view');
	$form->addElement(	'header', 'title', 'ISR by number');
	$form->addElement(	'text',   'id',   'ISR#');
	$form->addElement(	'submit', 'btnSubmit', 'Submit');
	$body = $form->toHTML();
break;
case 'search':
	$DEFAULT_TITLE .= "\Search";
	$form = new HTML_QuickForm('frmisrSearch', 'post');
	$form->addElement(	'hidden', 'm[0]', 'isr');
	$form->addElement(	'hidden', 'm[1]', 'listing');
	$form->addElement(	'hidden', 'm[2]', 'search');
	$form->addElement(	'header', 'title','ISR Search');
	$form->addElement(	'text', 'x', 'Search for');
	$form->addElement(	'submit', 'btnSubmit', 'Submit');
	$body = $form->toHTML();
break;
case 'new':
	$DEFAULT_TITLE .= "\New ISR";
	// Get lists
	$erpList = tldLocation::getERPList("smartyOptionsIDLocation");
	$userList = tldDirectory::getUserlist("smartyOptions");
	// Create FORM
	$form = new HTML_QuickForm('frmAddISR', 'post');
	$form->addElement(	'hidden', 'm[0]', 'isr');
	$form->addElement(	'hidden', 'm[1]', 'form');
	$form->addElement(	'hidden', 'm[2]', 'new');
	$form->addElement(	'header', 'title',"New ISR");
	$form->addElement('select', 'bu_from_id', 'BU from', array(""=>"")+$erpList);
	$form->addElement('select', 'bu_to_id', 'BU to', array(""=>"")+$erpList);
	$form->addElement('text', 'cuno', 'ERP Customer#');
    $form->addElement('select', 'ttype', 'Transportation type',
        ["" => "", "AIR" => "AIR", "OCEAN" => "OCEAN", "TRAIN"=>"TRAIN", "ROAD" => "ROAD"]
    );
    $form->addElement( 'text', 'cnum', 'Container#');
	$form->addElement( 'select', 'container_type', 'Container#',['','20GP'=>'20GP', '40GP'=>'40GP', '40HQ'=>'40HQ', '40OT in gauge'=>'40OT in gauge', '40OT out gauge'=>'40OT out gauge' ,'air'=>'air']);
	$form->addElement( 'text', 'tnum', 'Tracking#<br/><em>Start with ups, dhl, fed etc.<br/>followed by #</em>');
	$form->addElement( 'date', 'dt_outb', 'Outbound Date',
	    array("format"=>"Y-m-d","minYear"=>date('Y'),"maxYear"=>date('Y')+3,'addEmptyOption'=>TRUE)
	);
	$form->addElement( 'date', 'dt_ship', 'Shipping Date',
	    array("format"=>"Y-m-d","minYear"=>date('Y'),"maxYear"=>date('Y')+3,'addEmptyOption'=>TRUE)
	);
	$form->addElement( 'date', 'dt_eta', 'ETA Date',
	    array("format"=>"Y-m-d","minYear"=>date('Y'),"maxYear"=>date('Y')+3,'addEmptyOption'=>TRUE)
	);
	$form->addElement( 'textarea', 'notes', 'Notes', array("rows"=>5, "cols"=>30));
	$form->addElement(	'file', 'doc_ship', 'Shipping document');
	$form->addElement(	'file', 'doc_qa', 'Quality document');
	$form->addElement(	'file', 'doc_inv', 'Invoice document');
	$buyerGrp = new tldGroup('role_BYR');
	$buyers = tldUtils::optionsByKeyValue($buyerGrp->getUserlist(), 'id', 'fullname');
	if (empty($buyers)) {
		$DEFAULT_ERROR[] = "ERROR: No buyers found for ERP#$DEFAULT_ERP...";
		break;
	}
	$form->addElement('select', 'assignee', 'Task Assignee', ['' => ''] + $buyers);
	$form->addElement('submit', 'btnSubmit', 'Submit');
	$form->addElement('reset', 	'btnReset',  'Reset');
	$fields=["bu_from_id","bu_to_id","cuno","ttype","dt_outb","dt_ship","dt_eta","assignee"];
	foreach($fields as $field){
	    $form->addRule($field, 'This is required', 'required');
	}
	$form->setDefaults(
	    array(
    		"dt_outb"=>date("Y-m-d"),
    		"dt_ship"=>date("Y-m-d"),
    		"dt_eta"=>date("Y-m-d")
	    )
	);

	if(!$form->validate()){
	    $body .= $form->toHTML();
	    break;
	}

	$vars = tldUtils::cleanupFormInput($form->exportValues());
	// Check customer number in ERP
	$bu = new tldLocation($vars['bu_from_id']);
	$cust = new tldERPCustomer($bu->getERP(),$vars['cuno']);
	if($cust->isEmpty()){
	    $DEFAULT_ERROR[]="ERROR: Customer {$vars['cuno']} not found in ".$bu->getERP();
		$body = $form->toHTML();
	    break;
	}
	$vars['poster_id'] = $user->getID();
	$vars['dt_outb']=implode('-',$vars['dt_outb']);
	$vars['dt_ship']=implode('-',$vars['dt_ship']);
	$vars['dt_eta']=implode('-',$vars['dt_eta']);

	$e = tldISR::insert($vars);
	if(is_string($e)){
		$DEFAULT_ERROR[]="INTERNAL ERROR: ISR not created<br/>Reason: $e";
		break;
	}
	$isr = new tldISR($e);
	$isr->addLogEntry($user->getID(), "PENDING");
	$body .= "<a href=\"$php_self?m[0]=isr&m[1]=view&id=$e\">ISR#$e created, click here to view.</a>";
	$isrinfo = $isr->getHeader();
	$parent_id = $isrinfo['id'];
	$module = 'ISR';
	switch($isrinfo['bu_to_erp']){
		case 620: $assignee = '1701';
		break;
		case 640: $assignee = '2393';
		break;
		case 660: $assignee = '2692';
		break;
		case 680: $assignee = '5065'; //zhu Li
		break;
		case 420: $assignee = '5256'; //GODARD Eloise
		break;
		case 400: $assignee = '5759';
		break;
		case 500: $assignee = '6467';  //CERDAN Marie
		break;
		case 520: $assignee = '4101';  //AIGUILLON Severlne
		break;
		case 540: $assignee = '3071';
		break;
		case 570: $assignee = '5006';
			break;
		case 220: $assignee = '5251';
			break;
		case 300:
			$subject = "ISR#{$isrinfo['id']} created from {$isrinfo['bu_from_erp']} ";
			$message = <<<EOF
$subject\n<br>
Please log in to the TLD-GSE intranet and go to the calendar module to view your open tasks.\n<br>
<a href="http://{$_SERVER['HTTP_HOST']}/en/private/manufacturing/pur/dev.php?m[0]=isr&m[1]=view&id={$isrinfo['id']}">
Click here to go to ISR.
</a>
EOF;
			$isr-> notify($message, $subject , '', 'parts@tld-america.com');
			break;
		case 500:
			$assignee = '4484';
			$ccUser = new tldUser(5966);
			$ccList[] = $ccUser->getEmail();
		break;
	};
	//setup the description
	$assignee = $vars['assignee'];
	$taskDesc = "ISR#$e created - Container# {$isrinfo['cnum']},  ETA date {$isrinfo['dt_eta']}.";
	if(!empty($assignee)) {
		$vals = [
			'module' => $module,
			'assignor' => $user->getID(),
			'assignee' => $assignee,
			'task' => $taskDesc,
			'bu_id' => $isrinfo['bu_to_erp'],
			'escalation_trigger' => '45',
		];
		// setup the due date depending on the leadtime
		if(!empty($isrinfo['dt_eta'])){
			list($y, $m, $d) = explode("-", $isrinfo['dt_eta']);
			$vals['due_date'] = ['Y'=>$y,'m'=>$m,'d'=>$d];
		}
		$task = tldUtils::cleanupFormInput($vals);
	    //insert new task
		$error_task = tldTask::insert($parent_id, $task, $module);
		if (!is_numeric($error_task)) {
			error_log("Could not create new task. There was an error processing. The error returned is '$error_task'");
		} else {
			$task = new tldTask($error_task);

			// built the email notification for the newly created task
			$assignee = new tldUser($task->getAssignee());
			$assignor = new tldUser($task->getAssignor());
			$assignee_fullname = $assignee->getFullname();
			$message = <<<EOF
						Task #$error_task has been assigned to $assignee_fullname.\n<br>
						
						Please log in to the TLD-GSE intranet and go to the calendar module to view your open tasks.\n<br>
						<a href="https://www.tld-gse.com/en/private/calendar/calendar.php?m[0]=tasks&m[1]=task&m[2]=view&id=$error_task">
						Click here to go to Task.
						</a>
						<br>
						Task:<br>
EOF;
			$message .= $task->getTask();
			$subject = "Tasks, New: #$error_task opened for " . $assignee->getFullname() . ' by ' . $assignor->getFullname();
			$task->notifyAssignee($message, $subject,$ccList);
		}
	}

	// Upload DOC files if attached
	$DocFields = array('doc_ship','doc_qa','doc_inv');
	$DocNames = array();
	foreach($DocFields as $DocField){
		$fileTmp = $form->getElement($DocField);
		$fileData = $fileTmp->getValue();
		if(!empty($fileData["tmp_name"])){
			$file = new basicFile($fileData["tmp_name"]);
			$filename = basename(time()."_".$fileData["name"]);
			if($file->copyFile(tldUtils::getPathToUploadFile("isr",$filename))){
				$DocNames[$DocField] = $filename;
			}else{
			    $DEFAULT_ERROR[]="INTERNAL ERROR: Could not copy file '$filename' for $DocField";
			}
		}
	}
	// Update ISR for docs
	if(count($DocNames)>0){
		$res = $isr->update($DocNames);
		if(!is_string($res)){
			foreach($DocNames as $field=>$fileName){
                $body .= "<br/>$fileName successfully added as $field document";
			}
		}else{
		    $DEFAULT_ERROR[]="INTERNAL ERROR: Problem updating ISR#$e to attach documents";
		}
	}
break;
}

?>
