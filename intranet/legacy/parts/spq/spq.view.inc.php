<?php
if(empty($id)){
	$DEFAULT_ERROR[]=  "ERROR: no line id set...";
	return;
}
$spq = new tldSPQ($id);
if($spq->isEmpty()){
	$DEFAULT_ERROR[]=  "ERROR: No SPQ#$id found...";
	return;
}
$header = $spq->getHeader();

$DEFAULT_TITLE .= "\SPQ#$id";
$DEFAULT_MENU .=<<<EOF
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<a href="$php_self?m[0]=spq&m[1]=view&id=$id">General</a>&nbsp;|&nbsp;
<a href="$php_self?m[0]=spq&m[1]=view&m[2]=edit&id=$id" title="Edit this SPQ">Edit</a>&nbsp;|&nbsp;
<a href="$php_self?m[0]=spq&m[1]=view&m[2]=changeStatus&id=$id" title="Change SPQ status">Change status</a>&nbsp;|&nbsp;
<a href="$php_self?m[0]=spq&m[1]=view&m[2]=logs&id=$id" title="Activity Logs">Logs</a>&nbsp;|&nbsp;
<a href="$php_self?m[0]=spq&m[1]=view&m[2]=files&id=$id" title="SPQ related Files">Files</a>&nbsp;|&nbsp;
<a href="$php_self?m[0]=spq&m[1]=view&m[2]=links&id=$id" title="SPQ linked Module">Links</a>&nbsp;|&nbsp;
<a href="$php_self?m[0]=spq&m[1]=view&m[2]=tasks&id=$id" title="SPQ related Tasks">Tasks</a>
EOF;
if($user->isInGroup(array("gg_MIS","role_SPM","role_CSD"))){
	$DEFAULT_MENU .=<<<EOF
&nbsp;|&nbsp;<a href="$php_self?m[0]=spq&m[1]=view&m[2]=delete&id=$id" title="Delete SPQ#$id">Delete</a>
EOF;
}
if($user->isInGroup(array("gg_MIS"))){
	$DEFAULT_MENU .=<<<EOF
&nbsp;|&nbsp;<a href="spq/spq_admin.php?mode=record_view&form_type=main_tpl&id=$id" title="Edit this SPQ">Admin</a>
EOF;
}

switch($m[2]){
case "tasks":
    $DEFAULT_TITLE .="\Tasks";
    if(!in_array($spq->getStatus(),array("ORDERED FULL","ORDERED PARTIAL","LOST","CANCELLED"))){
        $DEFAULT_MENU .=<<<EOF
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<a href="/en/private/calendar/calendar.php?m[0]=tasks&m[1]=form&m[2]=newTask&module=SPQ&parent_id=$id">New Task</a>
EOF;
}
    $sess["calendar"]["tasks"] = $spq->getTasks();
    $form = new tldReportMultiLevel(
        $sess["calendar"]["tasks"],
        array("status","due_date"),
        array(
            "id"                =>"Task#",
            "status"            =>"Status",
            "due_date"          =>"Due",
            "task"              =>"Task",
            "assignor_fullname" =>"Assignor",
            "assignee_fullname" =>"Assignee"
        ),
        array(
            "passField"=>"id",
            "title"=>"Tasks",
            "url"=>"/en/private/calendar/calendar.php?m[0]=tasks&m[1]=task&m[2]=view&id="
        )
    );
    $body .= $form->fetch();
break;
case 'changeStatus':
    $DEFAULT_TITLE .= "\Change Status";
    if(in_array($spq->getStatus(),array("ORDERED FULL","ORDERED PARTIAL","LOST","CANCELLED")) && !$user->isInGroup(array("gg_ADMIN","role_SPM","role_CSD"))){
        $DEFAULT_ERROR[]=  "ERROR: You do not have permissions to update status once CLOSED";
        break;
    }
    // Listing
	$allowed = array_combine($spq->getAllowedStatus(),$spq->getAllowedStatus());
	// Form
	$form = new HTML_QuickForm('frmStatusSPQ', 'post');
	$form->addElement(	'hidden', 'm[0]', 'spq');
	$form->addElement(	'hidden', 'm[1]', 'view');
	$form->addElement(	'hidden', 'm[2]', 'changeStatus');
	$form->addElement(	'hidden', 'id',   $id);
	$form->addElement(	'header', 'title', "Change SPQ status");
	$form->addElement(	'select', 'status', 'Status', $allowed);
	if($spq->getStatus() == "PENDING" || $spq->getStatus() == "SUBMITTED PARTIAL"){
		$form->addElement(  'text',	'dt_submit', 	'Submit Date', 	array('class'=>'datepicker'));
		$form->addRule('dt_submit', 'Required', 'required');
		$form->addElement(	'text', 'qono', 		'Baan Quotation Number (Qono#)');
		$form->addElement(	'text', 'qono_val', 	'Quote value');
	}
	if($spq->getStatus() == "PENDING" || $spq->getStatus() == "SUBMITTED FULL"){
	    $form->addElement(	'text', 'baan_so', 		'Baan SO#');
	}
	$form->addElement(  'textarea', 'comments', 'Comments', array("rows"=>5, "cols"=>40));
	$form->addElement(  'submit', 'btnSubmit', 'Submit');
	$form->setDefaults(array('dt_submit'=>date("Y-m-d"))+$header);
	$form->addRule('comments', 'Required', 'required');
    if(!$form->validate()){
    	$body .= $form->toHTML();
    	break;
    }

    $vars = tldUtils::cleanupFormInput($form->exportValues());
    if($vars['dt_submit'] && $vars['dt_submit'] < substr($header['dt_open'], 0, 10)){
    	$DEFAULT_ERROR[]="ERROR: Submit Date cannot be lower than the SPQ Open Date";
    	$body .= $form->toHTML();
    	break;
    }
    $e = $spq->changeStatus($vars);
    if(is_string($e)){
        $DEFAULT_ERROR[]="INTERNAL ERROR: Can not update status. Reason: $e";
        break;
    }
    $log = <<<EOF
{$vars['status']}
{$vars['comments']}
EOF;
    $spq->addLogEntry($user->getID(),$log);
    $header = $spq->getHeader();
   	$body .= _getGeneralTab($header);
break;
case 'edit':
	if(in_array($spq->getStatus(),array("ORDERED FULL","ORDERED PARTIAL","LOST","CANCELLED"))){
		$DEFAULT_ERROR[]="ERROR: You can not edit a CLOSED SPQ!";
		break;
	}
	$customerList = tldCustomer::getList("smartyOptions");
	$typeList = tldSPQ::getTypeList();
	$sphList = tldSPH::getList("smartyOptionsIDLocation");
	$PartsGrp = new tldGroup("gg_PARTS");
	$PartsList = array_column($PartsGrp->getUserlist(), 'fullname', 'id');
	$contactList = array();
	if($header['customer_id'] != '' && $header['customer_id'] != 0){
		$customer = new tldCustomer($header['customer_id']);
		$contacts = $customer->getContactList();
		foreach($contacts as $contact) {
			$contactList[$contact["id"]] = $contact["fullname"];
		}
	}
	$DEFAULT_TITLE .= "\Edit";
	$form = new HTML_QuickForm('frmEditSPQ', 'post');
	$form->addElement(	'hidden', 'm[0]', 'spq');
	$form->addElement(	'hidden', 'm[1]', 'view');
	$form->addElement(	'hidden', 'm[2]', 'edit');
	$form->addElement(	'hidden', 'id',   $id);
	$form->addElement(	'header', 'title',"Edit SPQ#$id");
	$form->addElement(	'select', 'customer_id',		'eCustomer', 				array(""=>"")+$customerList);
	$form->addElement(	'select', 'sph_id',				'SPH', 						array(""=>"")+$sphList);
	$form->addElement(	'select', 'poster_id',			'Owner', 					array(""=>"")+$PartsList);
	if($spq->getStatus() != "PENDING"){
		$form->addElement(	'text', 'qono', 	'Baan Quotation Number (Qono#)');
		$form->addElement(	'text', 'qono_val', 'Quote value');
	}
	if($spq->getStatus() == "ORDERED FULL" || $spq->getStatus() == "ORDERED PARTIAL"){
	    $form->addElement(	'text', 'baan_so', 		'Baan SO#)');
	}
	$form->addElement(	'select', 'request_type',		'Request Format', 			array(""=>"")+$typeList);
	$form->addElement(  'text',   'dt_received', 		'Request Received Date', 	array('class'=>'datepicker'));
	$form->addElement(  'text',   'dt_ship', 			'Requested Ship Date', 		array('class'=>'datepicker'));
	$form->addElement(	'select', 'contact_id',			'Contact', 					$contactList);
	$form->addElement(  'text',   'rfq', 				'RFQ#');
	$form->addElement(	'textarea','comment',			'Request description',		array("wrap"=>"VIRTUAL", "cols"=>"40", "rows"=>"7"));
	$form->addElement('submit', 'btnSubmit', 'Submit');
	$form->addElement('reset', 	'btnReset',  'Reset');
	$required = array("request_type","dt_received","sph_id","customer_id","poster_id");
	foreach($required as $key=>$field) {
		$form->addRule($field, 'Required', 'required');
	}
	// Set default
	$form->setDefaults($header);

	if(!$form->validate()){
	    $body = $form->toHTML();
	    break;
	}

	$vars = tldUtils::cleanupFormInput($form->exportValues());
	$fields = array(
        "customer_id","request_type","dt_received","baan_so",
        "dt_ship","contact_id","comment","sph_id","poster_id","qono","qono_val","rfq"
    );
	$e = $spq->update($vars,$fields);
	if(is_string($q)){
	    $DEFAULT_ERROR[]="INTERNAL ERROR: SPQ not updated! <br/>Reason: $e";
	    break;
	}
	$FIELD_DESIGNATION = array(
			'customer_id'=>'eCustomer',
			'poster_id'=>'Owner',
			'sph_id'=>'SPH',
	        'baan_so'=>'Baan SO#',
			'qono'=>'Qono#',
			'qono_val'=>'Quote Value',
			'request_type'=>'Request Format',
			'dt_received'=>'Request Received Date',
			'dt_ship'=>'Requested Ship Date',
			'contact_id'=>'Contact',
			'rfq'=>'RFQ#',
			'comment'=>'Request description'
	);
	//Get updated header
	$spq_updated = new tldSPQ($id);
	$header_updated = $spq_updated->getHeader();
	$logs = array();
	//Log if updated header <> original header
	foreach($fields as $field){
		if($header_updated[$field]!=$header[$field]){
			$logs[]="<li><b>{$FIELD_DESIGNATION[$field]}</b> from '{$header[$field]}' to '{$header_updated[$field]}'</li>";
		}
	}
	if(count($logs)){
		$msg = "SPQ#$id updated:<br><ul>".implode("",$logs)."</ul>";
		$e = $spq->addLogEntry(
				$user->getID(),
				TldDatabase::escape($msg)
		);
		if(is_string($e)){
			$DEFAULT_ERROR[] = "ERROR: Problem adding logs...<br/>Reason: $e";
		}
	}
	$body = "SPQ#$id updated successfully!";
	$header = $spq->getHeader();
	$body .= _getGeneralTab($header);
break;
case 'logs':
	$DEFAULT_TITLE .="\Logs";
	$DEFAULT_MENU .=<<<EOF
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<a href="$php_self?m[0]=spq&m[1]=view&m[2]=logs&m[3]=add&id=$id">New Comment</a>
EOF;
switch($m[3]){
	case 'add':
		$form = new HTML_QuickForm('frmAddSPQComment', 'post');
		$form->addElement(	'header', 'title', 'Add comment');
		$form->addElement(	'hidden', 'm[0]', 'spq');
		$form->addElement(	'hidden', 'm[1]', 'view');
		$form->addElement(	'hidden', 'm[2]', 'logs');
		$form->addElement(	'hidden', 'm[3]', 'add');
		$form->addElement(	'hidden', 'id', $id);
		$form->addElement(	'textarea', 'comment', 'Comment',
				array("wrap"=>"VIRTUAL", "cols"=>"30", "rows"=>"4"));
		$form->addElement(	'submit', 'btnSubmit', 'Submit');

		if(!$form->validate()){
			$body = $form->toHTML();
			break;
		}
		$a = $form->exportValues();
		$vars = tldUtils::cleanupFormInput($a);
		$e = $spq->addLogEntry($user->getID(), $vars['comment']);
		if(!is_string($e)){
			$body .= "Comment successfully added!";
		}else{
			$DEFAULT_ERROR[] = "INTERNAL ERROR: Comment not added!<br/>Reason: $e";
		}
	default:
		$log = $spq->getLog();
		$report = new tldReportColumnar(
			$log,
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
}
break;
case 'files':
	$DEFAULT_TITLE .="\Files";
	$DEFAULT_MENU.=<<<EOF
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<a href="/en/private/common/index.php?m[0]=files&m[1]=form&m[2]=newFile&module=SPQ&parent_id=$id">Add New File</a>
EOF;

	$report = new tldReportColumnar(
			$spq->getFiles(),
			array(
					"xItems"=>array(
							"id"=>"File ID",
							"date"=>"Date",
							"description"=>"Description",
							"filename"=>"Filename"
					),
					"links"=>array(
							"id"=>"/en/private/common/index.php?m[0]=files&m[1]=view&id="
					)
			)
	);
	$body .= $report->fetch();
break;
    case 'links':
        $DEFAULT_MENU .= <<<EOF
    <br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
    <a href="$php_self?m[0]=spq&m[1]=form&m[2]=newLink&module=SPQ&parent_id=$id">Add New Link</a>
EOF;
        $DEFAULT_TITLE .= '\Links';
        $report = new tldReportColumnar(tldModLink::byParent($id, 'SPQ'),
            [
                "xItems" => [
                    "id" => "ID#",
                    "type" => "Module",
                    "item" => "Ref#",
                    "dsca" => "Description",
                ],
                "title" => "Links FROM Here...",
                "links" => [
                    "item" => [
                        'url' => "/en/private/common/index.php?m[0]=links&m[1]=view&m[2]=redirect",
                        'params' => ['id' => 'id'],
                    ],
                    "id" => "/en/private/common/index.php?m[0]=links&m[1]=view&id=",
                ],
            ]
        );
        $body .= $report->fetch();
        $report = new tldReportColumnar(tldModLink::byItem($id, 'SPQ'),
            [
                "xItems" => [
                    "id" => "ID#",
                    "module" => "Module",
                    "parent_id" => "Ref#",
                    "dsca" => "Description",
                ],
                "title" => "Links TO Here...",
                "links" => [
                    "parent_id" => [
                        'url' => "/en/private/common/index.php?m[0]=links&m[1]=view&m[2]=redirect&reversed=1",
                        'params' => ['id' => 'id'],
                    ],
                    "id" => "/en/private/common/index.php?m[0]=links&m[1]=view&id=",
                ],
            ]
        );
        $body .= $report->fetch();
        break;
    case 'delete':
        $DEFAULT_TITLE .="\Delete";
	if(!$user->isInGroup(array("role_SPM","gg_ADMIN","role_CSD"))){
		$DEFAULT_ERROR[]="ERROR: You do not have permission to delete SPQ.";
		break;
	}
	$form = new HTML_QuickForm('frmDelete');
	$form->addElement('hidden', 	'm[0]', 		'spq');
	$form->addElement('hidden', 	'm[1]', 		'view');
	$form->addElement('hidden', 	'm[2]', 		'delete');
	$form->addElement('hidden', 	'id', 			$id);
	$form->addElement('header', 	'title', 		"Do you want to delete SPQ#$id ? This is irreversible.");
	$form->addElement('submit', 	'btnSubmit', 	'Confirm');
	if(!$form->validate()){
		$body = $form->toHTML();
		break;
	}else{
		$e = $spq->delete();
		if(is_string($e)){
			$DEFAULT_ERROR[] = "ERROR: Problem deleting...<br/>Reason: $e";
			break;
		}
		$log = $spq->addLogEntry(
				$user->getID(),
				"SPQ#$id deleted"
		);
		if(is_string($log)){
			$DEFAULT_ERROR[] = "ERROR: Problem adding logs...<br/>Reason: $log";
		}
		$body .= "SPQ#$id deleted successfully!";
	}
break;
default:
	$body = _getGeneralTab($header);
break;
}
?>
