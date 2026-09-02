<?php
if(empty($id) || !is_numeric($id)){
    $DEFAULT_ERROR[] = "ERROR: ID not set or invalid...";
    return;
}
$cpa = new tldCPA($id);
if($cpa->isEmpty()){
    $DEFAULT_ERROR[] = "ERROR: CPA#$id not found...";
    return;
}
$header = $cpa->getHeader();
if($single){
    unset($sess["cpa"]["list"]);
}
$DEFAULT_TITLE .= "CPA#$id";
$DEFAULT_MENU .=<<<EOF
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<a href="$php_self?m[0]=cpa&m[1]=view&id=$id">General</a>
&nbsp;|&nbsp;<a href="$php_self?m[0]=cpa&m[1]=view&m[2]=edit&id=$id">Edit</a>
&nbsp;|&nbsp;<a href="$php_self?m[0]=cpa&m[1]=view&m[2]=containmentAction&id=$id">Containment actions</a>
&nbsp;|&nbsp;<a href="$php_self?m[0]=cpa&m[1]=view&m[2]=rootCause&id=$id">Final root cause</a>
&nbsp;|&nbsp;<a href="$php_self?m[0]=cpa&m[1]=view&m[2]=correctiveAction&id=$id">Corrective actions</a>
&nbsp;|&nbsp;<a href="$php_self?m[0]=cpa&m[1]=view&m[2]=preventiveAction&id=$id">Preventive actions</a>
&nbsp;|&nbsp;<a href="$php_self?m[0]=cpa&m[1]=view&m[2]=verificationOfEffectiveness&id=$id">Verification of Effectiveness</a>
&nbsp;|&nbsp;<a href="$php_self?m[0]=cpa&m[1]=view&m[2]=log&id=$id">Logs</a>
&nbsp;|&nbsp;<a href="$php_self?m[0]=cpa&m[1]=view&m[2]=followers&id=$id">Followers</a>
&nbsp;|&nbsp;<a href="$php_self?m[0]=cpa&m[1]=view&m[2]=tasks&id=$id">Tasks</a>
&nbsp;|&nbsp;<a href="$php_self?m[0]=cpa&m[1]=view&m[2]=files&id=$id">Files</a>
&nbsp;|&nbsp;<a href="$php_self?m[0]=cpa&m[1]=view&m[2]=links&id=$id">Links</a>
&nbsp;|&nbsp;<a href="$php_self?m[0]=cpa&m[1]=view&m[2]=changeStatus&id=$id">Change Status</a>
&nbsp;|&nbsp;<a href="$php_self?m[0]=cpa&m[1]=view&m[2]=delete&id=$id">Delete</a>
&nbsp;|&nbsp;<a href="$php_self?m[0]=cpa&m[1]=view&m[2]=sheet&id=$id">CPA sheet</a>
EOF;

if($user->isInGroup(array("gg_ADMIN"))){
    $DEFAULT_MENU .=<<<EOF
&nbsp;|&nbsp;<a href="/en/private/manufacturing/qa/cpa/cpa_admin.php?mode=record_view&form_type=main_tpl&id=$id">Admin</a>
EOF;
}

switch($m[2]){
case 'delete':
    if(!$user->isInGroup(["superuser"]) && $user->getID()<>$moo_id && ((!$user->isInGroup(["role_QAM"]) && !$user->isInGroup(["role_QA"])) || $cpa->getStatus()!=='PENDING')){
    	$DEFAULT_ERROR[]="ERROR: You do not have permissions";
        break;
    }
    $form = new HTML_QuickForm('frmDel', 'post');
    $form->addElement(  'hidden', 'm[0]', 'cpa');
    $form->addElement(  'hidden', 'm[1]', 'view');
    $form->addElement(  'hidden', 'm[2]', 'delete');
    $form->addElement(  'hidden', 'id',   $id);
    $form->addElement(  'header', 'title', "Delete CPA#$id ?");
    $form->addElement(  'select', 'confirm', 'Do you confirm?',
        array(""=>"","Y"=>"Yes, I confirm"));
    $form->addRule('confirm', 'Required', 'required');
    $form->addElement('submit', 'btnSubmit', 'Submit');

    if(!$form->validate()){
        $body.= $form->toHTML();
        break;
    }

    $e = $cpa->delete();
    if(is_string($e)){
        $DEFAULT_ERROR[] = "ERROR: CPA not deleted. Reason: $e";
        break;
    }
    $cpa->addLogEntry($user->getID(), 'CPA deleted');
    $body = "CPA#$id deleted successfully!";
break;
case 'containmentAction':
    $DEFAULT_TITLE .= "\Containment actions";

    // Check status
    if(!in_array($cpa->getStatus(),array("PENDING","INVESTIGATION"))){
        $DEFAULT_ERROR[]="ERROR: Can not use containment action form, CPA must be in status PENDING or INVESTIGATION";
        break;
    }

    // Form
    $form = new HTML_QuickForm('frmContainAct', 'post');
    $form->addElement(  'hidden', 'm[0]', 'cpa');
    $form->addElement(  'hidden', 'm[1]', 'view');
    $form->addElement(  'hidden', 'm[2]', 'containmentAction');
    $form->addElement(  'hidden', 'id',   $id);
    $form->addElement(  'header', 'title', "Is there a containment action to implement?");
    $form->addElement(	'textarea', 'field1', 'Internal', array("wrap"=>"VIRTUAL", "cols"=>"50", "rows"=>"6"));
    $form->addElement(	'textarea', 'field2', 'External', array("wrap"=>"VIRTUAL", "cols"=>"50", "rows"=>"6"));
    $form->addElement(	'textarea', 'field3', 'Other', array("wrap"=>"VIRTUAL", "cols"=>"50", "rows"=>"6"));
    $form->addElement(  'submit', 'btnSubmit', 'Submit');
	$form->addRule('field1', 'Required', 'required');
    $form->addRule('field2', 'Required', 'required');
    $form->addRule('field3', 'Required', 'required');
    $form->setDefaults($cpa->getFormDataByName('containmentActionsForm'));

    if($form->validate()){
        $vars = $form->exportValues();
        $formated = <<<EOF
____________________________________________________

<p>Internal</p>
____________________________________________________

{$vars['field1']}
____________________________________________________

<p>External</p>
____________________________________________________

{$vars['field2']}
____________________________________________________

<p>Other</p>
____________________________________________________

{$vars['field3']}
EOF;
        // record data in field
        $formated = TldDatabase::escape($formated);
        $e = $cpa->update(array('containment_action'=>$formated));
        if(is_string($e)){
            $DEFAULT_ERROR[]="INTERNAL ERROR: Can not update CPA. Reason: $e";
            break;
        }
        // record form data submitted
        $formData = tldUtils::cleanupFormInput($vars);
        $cpa->saveFormData('containmentActionsForm',$formData);
        // refresh to apply update
        $cpa->refresh();
    }

    // Display info + form ----->

    $report = new tldAssocTable(
        $cpa->itsHeader,
        array(
            'containment_action'=>'Containment actions',
        )
    );
    $body = <<<EOF
{$report->fetch()}
<br>
{$form->toHTML()}
EOF;
break;
case 'rootCause':
    $DEFAULT_TITLE .= "\Final root cause";

    // Check status
    if($cpa->getStatus()<>'INVESTIGATION'){
        $DEFAULT_ERROR[]="ERROR: Can not set root cause, CPA must be in status INVESTIGATION";
        break;
    }

    // Form
    $form = new HTML_QuickForm('frmRootCause', 'post');
    $form->addElement(  'hidden', 'm[0]', 'cpa');
    $form->addElement(  'hidden', 'm[1]', 'view');
    $form->addElement(  'hidden', 'm[2]', 'rootCause');
    $form->addElement(  'hidden', 'id',   $id);
    $form->addElement(  'header', 'title', "Why problem appeared?");
    $form->addElement(  'header', 'title2', "<em>Please list all potential root causes: All related document should be attached in files section (fishbone...)</em>");
    $form->addElement(	'textarea', 'field1', 'Final root cause', array("wrap"=>"VIRTUAL", "cols"=>"50", "rows"=>"6"));
    $form->addElement(	'textarea', 'field2', 'Why it hasn\'t been detected?', array("wrap"=>"VIRTUAL", "cols"=>"50", "rows"=>"6"));
    $form->addElement(  'submit', 'btnSubmit', 'Submit');
	$form->addRule('field1', 'Required', 'required');
    $form->addRule('field2', 'Required', 'required');
    $form->setDefaults($cpa->getFormDataByName('rootCauseForm'));

    if($form->validate()){
        $vars = $form->exportValues();
        $formated = <<<EOF
____________________________________________________
<p>Final root cause</p>
____________________________________________________
{$vars['field1']}
____________________________________________________
<p>Why it hasn't been detected?</p>
____________________________________________________
{$vars['field2']}<br>
EOF;
        // record data in field
        $formated = TldDatabase::escape($formated);
        $e = $cpa->update(array('root_cause'=>$formated));
        if(is_string($e)){
            $DEFAULT_ERROR[]="INTERNAL ERROR: Can not update CPA. Reason: $e";
            break;
        }
        // record form data submitted
        $formData = tldUtils::cleanupFormInput($vars);
        $cpa->saveFormData('rootCauseForm',$formData);
        // refresh to apply update
        $cpa->refresh();
    }

    // Display info + form ----->

    $report = new tldAssocTable(
        $cpa->itsHeader,
        array(
            'root_cause'=>'Final root cause',
        )
    );
    $body = <<<EOF
{$report->fetch()}
<br>
{$form->toHTML()}
EOF;
break;
case 'correctiveAction':
    $DEFAULT_TITLE .= "\Corrective Action";

    // Check status
    if($cpa->getStatus()<>'ACTION'){
        $DEFAULT_ERROR[]="ERROR: Can not use corrective action form, CPA must be in status ACTION";
        break;
    }

    switch($m[3]){
    case 'submit':
        $vars = $_POST['actions'];
        $rows = array();
        $formated = NULL;
        // prepare data
        foreach($vars as $who=>$actions){
            $tempTxt = NULL;
            foreach($actions as $k=>$vals){
                // Check if data for this action
                if(empty($vals['ref'])) continue;
                // keep valid data with same form structure to record later
                $rows[$who][(int)$k] = $vals;
                // prepare formated text
                $tempTxt.=<<<EOF
{$vals['action']}: {$vals['ref']}

EOF;
            }
            if(empty($tempTxt)) continue;
            $formated.=<<<EOF
____________________________________________________

<p>$who</p>
____________________________________________________

<p>$tempTxt</p>
EOF;
        }
        // check if one line at least entered
        if(count($rows)<1){
            $DEFAULT_ERROR[]="ERROR: No actions has been selected, make sure to set all informations";
            break;
        }
        // record data in field
        $formated = TldDatabase::escape($formated);
        $e = $cpa->update(array('corrective_action'=>$formated));
        if(is_string($e)){
            $DEFAULT_ERROR[]="INTERNAL ERROR: Can not update CPA. Reason: $e";
            break;
        }
        // record form data submitted
        $cpa->saveFormData('correctiveActionsForm',$rows);
        // refresh CPA
        $cpa->refresh();
    break;
    }

    // Display info and form ------>

    $report = new tldAssocTable(
        $cpa->itsHeader,
        array(
            'corrective_action'=>'Corrective action',
        ),
        array(
            'title'=>'Actual Corrective action'
        )
    );
    $body = $report->fetch();
    $FORM_DEFAULT = $cpa->getFormDataByName('correctiveActionsForm');
    $body.= include("forms/form.corrective_action.tpl.php");
break;
case 'preventiveAction':
    $DEFAULT_TITLE .= "\Preventive Action";

    // Check status
    if($cpa->getStatus()<>'ACTION'){
        $DEFAULT_ERROR[]="ERROR: Can not set preventive action, CPA must be in status ACTION";
        break;
    }

    // Form
    $form = new HTML_QuickForm('frmPrevAct', 'post');
    $form->addElement(  'hidden', 'm[0]', 'cpa');
    $form->addElement(  'hidden', 'm[1]', 'view');
    $form->addElement(  'hidden', 'm[2]', 'preventiveAction');
    $form->addElement(  'hidden', 'id',   $id);
    $form->addElement(  'header', 'title1', "Is there any further actions to prevent such problem?");
    $form->addElement(	'textarea', 'field1', '', array("wrap"=>"VIRTUAL", "cols"=>"50", "rows"=>"6"));
    $form->addElement(  'header', 'title2', "Is there any further actions to detect such similar problem in future?");
    $form->addElement(	'textarea', 'field2', '', array("wrap"=>"VIRTUAL", "cols"=>"50", "rows"=>"6"));
    $form->addElement(  'submit', 'btnSubmit', 'Submit');
	$form->addRule('field1', 'Required', 'required');
    $form->addRule('field2', 'Required', 'required');
    $form->setDefaults($cpa->getFormDataByName('preventiveActionForm'));

    if($form->validate()){
        $vars = $form->exportValues();
        $formated = <<<EOF
____________________________________________________

<p>Is there any further actions to prevent such problem?</p>
____________________________________________________

{$vars['field1']}
____________________________________________________

<p>Is there any further actions to detect such similar problem in future?</p>
____________________________________________________

{$vars['field2']}
EOF;
        // record data in field
        $formated = TldDatabase::escape($formated);
        $e = $cpa->update(array('preventive_action'=>$formated));
        if(is_string($e)){
            $DEFAULT_ERROR[]="INTERNAL ERROR: Can not update CPA. Reason: $e";
            break;
        }
        // record form data submitted
        $formData = tldUtils::cleanupFormInput($vars);
        $cpa->saveFormData('preventiveActionForm',$formData);
        // refresh to apply update
        $cpa->refresh();
    }

    // Display info + form ----->

    $report = new tldAssocTable(
        $cpa->itsHeader,
        array(
            'preventive_action'=>'Preventive action',
        )
    );
    $body = <<<EOF
{$report->fetch()}
<br>
{$form->toHTML()}
EOF;
break;
case 'verificationOfEffectiveness':
    $DEFAULT_TITLE.= '\Verification of Effectiveness';

    if(!$user->isInGroup(array('role_COO','role_CMO','role_CEO','role_QAM','role_QA','cpa'))){
        $DEFAULT_ERROR[]='ERROR: You do not have permissions';
        break;
    }

    // Form
    $form = new HTML_QuickForm('VerificationEffectiveness', 'post');
    $form->addElement(  'hidden', 'm[0]', 'cpa');
    $form->addElement(  'hidden', 'm[1]', 'view');
    $form->addElement(  'hidden', 'm[2]', 'verificationOfEffectiveness');
    $form->addElement(  'hidden', 'id',   $id);
    $form->addElement(  'header', 'title', 'Verification of effectiveness');
    $form->addElement(	'textarea', 'verification_description', 'Description',	['wrap'=>'VIRTUAL', 'cols'=>'80', 'rows'=>'5']);
    $form->addElement(  'submit', 'btnSubmit', 'Submit');
    $form->addRule('description', 'Required', 'required');

    if(!$form->validate()){
        $body = $form->toHTML();
        break;
    }

    $vars = tldUtils::cleanupFormInput($form->exportValues());

    $e = $cpa->update(['verification_description' => $vars['verification_description']], ['verification_description']);
    if(is_string($e)){
        $DEFAULT_ERROR[] = 'ERROR: Problem updating...<br/>Reason: $e';
        break;
    }

    $cpa->addLogEntry($user->getID(), 'verification of effectiveness :'.$vars['verification_description']);

    $body.= '<br/>CPA updated successfully!';
    $body.= _getGeneralPage();

    break;

case 'edit':
    $DEFAULT_TITLE .= "\Edit";
    if(!$user->isInGroup(array("role_COO","role_CMO","role_CEO","role_QAM","role_QA","cpa"))){
    	$DEFAULT_ERROR[]="ERROR: You do not have permissions";
        break;
    }
    if($cpa->isClosed()){
        $DEFAULT_ERROR[]="ERROR: You cannot edit a CLOSED CPA";
        break;
    }
    // Listing
    $peopleList = tldDirectory::getUserlist("smartyOptions");
    $factoryList = tldLocation::getERPList("smartyOptionsIDLocation");
    $statusList = tldCPA::getStatusList();
    $typeList = tldList::optionsByListNameAsListItemListItem('list.cpa.type');
    $deptList = tldDepartment::getListAsDepartmentDepartment();
    $iFactorList = tldCPA::getIFactorList();
    // Form
    $form = new HTML_QuickForm('frmEdit', 'post');
    $form->addElement(  'hidden', 'm[0]', 'cpa');
    $form->addElement(  'hidden', 'm[1]', 'view');
    $form->addElement(  'hidden', 'm[2]', 'edit');
    $form->addElement(  'hidden', 'id',   $id);
    $form->addElement(  'header', 'title', "Edit CPA#$id");
    $form->addElement(	'select', 'initiator', 'Initiator', $peopleList);
	$form->addElement(	'select', 'bu', 'Factory', $factoryList);
	$form->addElement(	'text', 'status', 'Status', array("disabled"=>"disabled"));
	$form->addElement(	'select', 'ifactor', 'Importance Factor', $iFactorList);
	$form->addElement(  'select', 'proj_leader', 'Project Leader', array(""=>"")+$peopleList);
	$form->addElement(  'text', 'date_target', 'Target Date', array('class'=>'datepicker'));
    $form->addElement(  'select', 'dept', 'Department', $deptList);
    $form->addElement(  'select', 'type', 'Type', $typeList);
	$form->addElement(	'text', 'short_desc', 'Short Description', array("size"=>"50"));
	$form->addElement(	'textarea', 'description', 'Full Description',
		array("wrap"=>"VIRTUAL", "cols"=>"40", "rows"=>"8"));
	$form->addElement(  'header', 'title2', "Picture file - Actual: <em>{$header['picture_filename']}</em>");
	$form->addElement(	'file', 'picture_filename', "Change picture?");
    $form->addElement(  'submit', 'btnSubmit', 'Submit');
    $requiredList = array('initiator','bu','ifactor','dept','type','short_desc','description');
    foreach($requiredList as $required){
        $form->addRule($required, 'Required', 'required');
    }
    $form->setDefaults($header);

    if(!$form->validate()){
        $body.= $form->toHTML();
        break;
    }

    $vars = tldUtils::cleanupFormInput($form->exportValues());
    // Special case
    if($cpa->getStatus() <> "PENDING" && (empty($vars['proj_leader']) || empty($vars['date_target']))){
        $DEFAULT_ERROR[]="ERROR: Project Leader and Target Date are mandatory fields";
        $body.= $form->toHTML();
        break;
    }
    $fields = array(
    	'initiator'=>'Initiator',
    	'bu'=>'Factory',
    	'ifactor'=>'IF',
        'proj_leader'=>'Project Leader',
        'date_target'=>'Target Date',
    	'dept'=>'Department',
    	'type'=>'Type',
    	'short_desc'=>'Short Description',
    	'description'=>'Description',
    	'picture_filename'=>'Picture file',
    );
    // Change picture if any
    $file = $form->getElement("picture_filename");
	if($file->isUploadedFile()){
	    $attr = $file->getValue();
	    $destPath = tldCPA::getPathToUploadFile();
		// Create file name and check if not existing already
		$filepath = $destPath.time().$attr["name"];
		while(file_exists($filepath)){
			$filepath = $destPath.time().$attr["name"];
		}
		$vars["picture_filename"] = basicFile::cleanupName(basename($filepath));
		$file->moveUploadedFile($destPath, $vars["picture_filename"]);
	}else{
	    unset($fields['picture_filename']);
	}
	// Update CPA
    $e = $cpa->update($vars, array_keys($fields));
    if(is_string($e)){
        $DEFAULT_ERROR[] = "ERROR: Problem updating...<br/>Reason: $e";
        break;
    }
    // Notification
    // Check Followers
    $followers = $cpa->getFollowers();
    $cc = array();
    if($followers){
        foreach($followers as $follower){
            $cc_user = new tldUser($follower['id']);
            $cc[] = $cc_user->getEmail();
        }
    }
    $msg =<<<EOF
CPA#$id has been updated.<br>
EOF;
    $cpa->notifyInitiator($msg,"CPA#$id updated",$cc);
    $body.= "<br/>CPA updated successfully!";
    // Log changes
    unset($fields['bu']);
    unset($fields['initiator']);
    unset($fields['proj_leader']);
    $fields['bu_fullname'] = 'Factory';
    $fields['initiator_fullname'] = 'Initiator';
    $fields['proj_leader_fullname'] = 'Project Leader';
    $changeLog = _generateChangeListLog($fields, $header, $cpa->getHeader());
    $logMsg = "CPA updated:<br>$changeLog";
    $cpa->addLogEntry($user->getID(),$logMsg);
    $body.= _getGeneralPage();
break;
case 'log':
    $DEFAULT_TITLE.= "\Logs";
    $report = new tldReportColumnar(
        $cpa->getLog(),
            array(
                "xItems"=>array(
                    "id"                =>"ID#",
                    "date"              =>"Date",
                    "poster_fullname"   =>"Poster",
                    "comment"           =>"Comment"
                )
            )
        );
    $body .= $report->fetch();
break;
case 'followers':
    $DEFAULT_TITLE .= "\Followers";
    if($cpa->getInitiatorID()==$user->getID() || $user->isInGroup(array("gg_ADMIN","role_CMO","role_QAM" ))){
        $DEFAULT_MENU .=<<<EOF
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<a href="$php_self?m[0]=cpa&m[1]=view&m[2]=followers&id=$id">Followers</a>
&nbsp;|&nbsp;<a href="/en/private/common/index.php?m[0]=members&m[1]=new&module=CPA&parent_id=$id">Add Followers</a>
&nbsp;|&nbsp;<a href="/en/private/common/index.php?m[0]=members&m[1]=delete&module=CPA&parent_id=$id">Delete Followers</a>
EOF;
    }
    $form = new tldReportColumnar(
        $cpa->getFollowers(),
        array(
            "xItems"=>array(
                "id"=>"UID#",
                "lastname"=>"Lastname",
                "firstname"=>"Firstname"
            ),
            "title"=>"Followers",
            "links"=>array(
                "id"=>"/en/private/directory/index.php?m[0]=people&m[1]=view&id="
            )
        )
    );
    $body .= $form->fetch();
break;
case 'files':
    $DEFAULT_TITLE .="\Files";
    $DEFAULT_MENU.=<<<EOF
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<a href="/en/private/common/index.php?m[0]=files&m[1]=form&m[2]=newFile&module=CPA&parent_id=$id">Add New File</a>
EOF;
    $report = new tldReportColumnar(
        $cpa->getFiles(),
        array(
            "xItems"=>array(
                "id"=>"File ID",
                "date"=>"Date",
                "description"=>"Description",
                "filename"=>"Filename"
            ),
            "links"=>array("id"=>"/en/private/common/index.php?m[0]=files&m[1]=view&id=")
        )
    );
    $body .= $report->fetch();
break;
case "tasks":
    $DEFAULT_TITLE .="\Tasks";
    $status = $cpa->getStatus();
    if($status<>'PENDING' && $status<>'CLOSED'){
        $DEFAULT_MENU .=<<<EOF
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<a href="/en/private/calendar/calendar.php?m[0]=tasks&m[1]=form&m[2]=newTask&module=CPA&parent_id=$id">New Task</a>
EOF;
}
    $sess["calendar"]["tasks"] = $cpa->getStatusTasksByConstraints("1=1");
    $form = new tldReportMultiLevel(
        $sess["calendar"]["tasks"],
        array("cpa_status","status"),
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
            "title"=>"Tasks by CPA status",
            "url"=>"/en/private/calendar/calendar.php?m[0]=tasks&m[1]=task&m[2]=view&id="
        )
    );
    $body .= $form->fetch();
break;
case 'links':
    $DEFAULT_TITLE .= "\Links";
    $DEFAULT_MENU.=<<<EOF
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<a href="/en/private/common/index.php?m[0]=links&m[1]=form&m[2]=newLink&module=CPA&parent_id=$id">Add New Link</a>
EOF;
    $report = new tldReportColumnar(
        $cpa->getLinksFromHere(),
        array(
            "xItems"=>array(
                "id"    =>"ID#",
                "type"  =>"Module",
                "item"  =>"Ref#",
                "dsca"  =>"Description"
            ),
            "title"=>"Links FROM Here...",
            "links"=>array(
                "id"=>"/en/private/common/index.php?m[0]=links&m[1]=view&id="
            )
        )
    );
    $body.= $report->fetch();
    $report = new tldReportColumnar(
        $cpa->getLinksToHere(),
        array(
            "xItems"=>array(
                "id"        =>"ID#",
                "module"    =>"Module",
                "parent_id" =>"Ref#",
                "dsca"      =>"Description"
            ),
            "title"=>"Links TO Here...",
            "links"=>array(
                "id"=>"/en/private/common/index.php?m[0]=links&m[1]=view&reversed=1&id="
            )
        )
    );
    $body.= $report->fetch();
break;
case 'changeStatus':
    if (!$user->isInGroup(["gg_ADMIN", "role_CMO", "role_COO", "role_CEO", "role_QAM", "role_QA", "role_SAM"]) && (!(($user->isInGroup(["role_CSD", "role_GTD", "role_CIO", "role_CMO", "role_CPO"]) || $user->isInGroupLevel("role_CFO", 900)) && $header['bu'] == 5))) {
        $upgroup = 1;
    }
    if (!$user->isInGroup(["gg_ADMIN", "role_CMO", "role_COO", "role_CEO", "role_QAM", "role_QA", "role_SAM", "role_PM", "role_MPE", "role_MLM", "role_EM"]) && (!(($user->isInGroup(["role_CSD", "role_GTD", "role_CIO", "role_CMO", "role_CPO"]) || $user->isInGroupLevel("role_CFO", 900)) && $header['bu'] == 5))) {
        $DEFAULT_ERROR[] = "ERROR: You do not have permission to change CPA status";
        break;
    } else {
        if ($user->isInGroup(["role_MLM"]) && $upgroup  && ($cpa->itsHeader['ifactor'] == 1000 || ($cpa->itsHeader['ifactor'] < 1000 && !($cpa->itsHeader['dept']==='Material control & Purchasing' && $cpa->itsHeader['bu']===$user->getBUID())))){
            $DEFAULT_ERROR[] = "ERROR: You do not have permission to change CPA status";
            break;
        }
        if ($user->isInGroup(["role_PM"]) && $upgroup && ($cpa->itsHeader['ifactor'] == 1000 || ($cpa->itsHeader['ifactor'] < 1000 && !($cpa->itsHeader['dept']==='Production' && $cpa->itsHeader['bu']===$user->getBUID())))){

            $DEFAULT_ERROR[] = "ERROR: You do not have permission to change CPA status";
            break;
        }
        if ($user->isInGroup(["role_MPE"]) && $upgroup && ($cpa->itsHeader['ifactor'] == 1000 || ($cpa->itsHeader['ifactor'] < 1000 && !($cpa->itsHeader['dept']==='Production' && $cpa->itsHeader['bu']===$user->getBUID())))){

            $DEFAULT_ERROR[] = "ERROR: You do not have permission to change CPA status";
            break;
        }
        if ($user->isInGroup(["role_EM"]) && $upgroup && ($cpa->itsHeader['ifactor'] == 1000 || ($cpa->itsHeader['ifactor'] < 1000 && !($cpa->itsHeader['dept']==='Engineering' && $cpa->itsHeader['bu']===$user->getBUID())))){
            $DEFAULT_ERROR[] = "ERROR: You do not have permission to change CPA status";
            break;
        }
    }
    // Listing
    $peopleList = tldDirectory::getUserlist("smartyOptions");
    $followers = $cpa->getFollowers();
    $statusList = $cpa->getAllowedStatus();
    if(empty($statusList)){
        $DEFAULT_ERROR[] = "ERROR: No allowed status found with actual status";
        break;
    }
    if (in_array('CLOSED', $statusList, true) && (string) $cpa->itsHeader['verification_description'] === ''){
        unset ($statusList['CLOSED']);
        $DEFAULT_ERROR[] = 'To close CPA, verification of effectiveness must be completed';
    }
    // form
    $form = new HTML_QuickForm('frmStatus', 'post');
    $form->addElement(  'hidden', 'm[0]', 'cpa');
    $form->addElement(  'hidden', 'm[1]', 'view');
    $form->addElement(  'hidden', 'm[2]', 'changeStatus');
    $form->addElement(  'hidden', 'id', $id);
    $form->addElement(  'select', 'status', 'New Status', $statusList);
    $form->addElement(  'textarea', 'reason', 'Reason',
        array("wrap"=>"VIRTUAL", "cols"=>"40", "rows"=>"8"));
    if($cpa->getStatus()=="PENDING"){
        $form->addElement(  'select', 'proj_leader', 'Project Leader', array(""=>"")+$peopleList);
        $form->addElement(  'text', 'date_target', 'Target Date', array('class'=>'datepicker'));
        $form->addRule('proj_leader', "Required", "required");
        $form->addRule('date_target', "Required", "required");
    }
    $ams =& $form->addElement('advmultiselect', 'userids', null,
        $peopleList,
        array(
            'size' => 10,
            'class' => 'pool',
            'style' => 'width:200px;'
        )
    );
    $ams->setLabel(array('CC others (max 20 recipients)... (OPTIONAL)', 'Addressbook', 'CC'));
    $ams->setButtonAttributes('add', array('value' => '-->>', 'class' => 'inputCommand'));
    $ams->setButtonAttributes('remove', array('value' => '<<--', 'class' => 'inputCommand'));
    // Default values
    $defaultFollowers = array();
    foreach($followers as $follower){
        $defaultFollowers[] = $follower['id'];
    }
    $form->setDefaults(array("userids"=>$defaultFollowers));
    // Rules
    $form->addRule('reason', "Required", "required");
    $form->addRule('status', "Required", "required");
    $form->addElement(  'submit', 'btnSubmit', 'Submit');

    if(!$form->validate()){
        $body = $form->toHTML();
        break;
    }

    $vars = tldUtils::cleanupFormInput($form->exportValues());
    // Look for options
    $options = array();
    // Record options
    $options['reason'] = $vars['reason'];
    if($vars['proj_leader']) $options['proj_leader'] = $vars['proj_leader'];
    if($vars['date_target']) $options['date_target'] = $vars['date_target'];
    // Check options for notification
    if(count($userids ?? [])){
        foreach($userids as $userid){
            $cc_user = new tldUser($userid);
            $options['cc'][] = $cc_user->getEmail();
        }
    }
    // Change status
    $e = $cpa->changeStatus($user->getID(), $vars['status'], $options);
    if(is_string($e)){
        $DEFAULT_ERROR[] = "There was a problem changing the status. '$e'";
    break;
    }
    $body .= _getGeneralPage();
break;
case 'sheet':
    // Prepare the HTML view
    $viewHTML = NULL;
    // CPA info
    $cpaInfo = new tldAssocTable(
        $cpa->itsHeader,
        array(
            'id'=>'CPA#',
            'date'=>'Date',
            'poster_fullname'=>'Poster',
            'initiator_fullname'=>'Initiator',
            'proj_leader_fullname'=>'Project Leader',
            'date'=>'Date',
            'date_target'=>'Target Date',
            'status'=>'Status',
            'bu_fullname'=>'Factory',
            'short_desc'=>'Short description',
            'description'=>'Description'
        )
    );
    // CPA main file
    $cpaFileName = $cpa->getFileName();
    $cpaImg = NULL;
    $cpaMainFile = new basicFile($cpa->getFilePath());
    if($cpaMainFile->isFileExists()){
        $typeMime = $cpaMainFile->getMimeTypeFromExtension();
        $typeMimeSplited = preg_split("#/#", $typeMime);
        if(strtolower($typeMimeSplited[0])=="image"){
            $cpaImg = <<<EOF
<img src="data:$typeMime;base64,{$cpaMainFile->getBase64()}" width="250" />
EOF;
        }else{
            $cpaImg = "<p><em>File: $cpaFileName</em></p>";
        }
    }else{
        $cpaImg = "<p><em>File: none</em></p>";
    }

    // Investigation
    $investigation = NULL;
    $investigationInfo = new tldAssocTable(
        $cpa->itsHeader,
        array(
            'containment_action'=>'Containment actions',
            'root_cause'=>'Final root cause',
        )
    );
    $investigation.= $investigationInfo->fetch();
    $taskInvestigation = new tldReportColumnar(
        $cpa->getStatusTasksByConstraints(array('cpa_status'=>'INVESTIGATION')),
        array(
            "xItems"=>array(
                "id"                =>"Task#",
                "status"            =>"Status",
                "due_date"          =>"Due date",
                "task"              =>"Task",
                "assignee_fullname" =>"Assignee"
            ),
            "title"=>"Investigation Tasks",
            "sortable"=>"no"
        )
    );
    $investigation.= $taskInvestigation->fetch();
    // Action
    $action = NULL;
    $actionInfo = new tldAssocTable(
        $cpa->itsHeader,
        array(
            'corrective_action'=>'Corrective action',
            'preventive_action'=>'Preventive action',
        )
    );
    $action.= $actionInfo->fetch();
    $taskAction = new tldReportColumnar(
        $cpa->getStatusTasksByConstraints(array('cpa_status'=>'ACTION')),
        array(
            "xItems"=>array(
                "id"                =>"Task#",
                "status"            =>"Status",
                "due_date"          =>"Due date",
                "task"              =>"Task",
                "assignee_fullname" =>"Assignee"
            ),
            "title"=>"Action Tasks",
            "sortable"=>"no"
        )
    );
    $action.= $taskAction->fetch();
    // Closure
    $closure = NULL;
    if($cpa->getStatus()=="CLOSED"){
        $closureInfo = new tldAssocTable(
            $cpa->itsHeader,
            array(
                'date_closed'=>'Closed date',
                'final_fweight'=>'Final FW',
                'resolution'=>'Resolution'
            )
        );
        $closure.=$closureInfo->fetch();
    }elseif($cpa->getStatus()=="REJECTED"){
        $closureInfo = new tldAssocTable(
            $cpa->itsHeader,
            array(
                'rejection_reason'=>'Reason for Rejecting'
            )
        );
        $closure.=$closureInfo->fetch();
    }else{
        $closure.="<p>Not closed yet</p>";
    }
    $generationDateInfo = date('Y-m-d (H:i)');
    // HTML
    $viewHTML = <<<EOF
<html>
  <head>
    <style>
body { font-family: arial; }
#header { border: 1px solid black; padding: 10px; text-align: center;}
#footer p { font-size: 8px; text-align: center; color: grey; margin-top: 10px;}
h2 { font-size: 16px; border-bottom: 1px solid black; padding: 5px;}
h3 { font-size: 13px;}
td, th, p { font-size: 10px;}

    </style>
  </head>
  <body>
  	<!-- HEADER -->
    <div id="header">
      <h1>CPA#{$cpa->getID()}</h1>
      <p>{$cpa->getShortDesc()}</p>
    </div>
	<!-- CPA INFO -->
	<h2>DETAILS</h2>
    <table width="100%" padding="5%">
	  <tr>
	    <td width="55%">
          {$cpaInfo->fetch()}
	    </td>
	    <td width="35%">$cpaImg</td>
      </tr>
    </table>
	<!-- CPA INVESTIGATION -->
	<div style="page-break-inside:avoid;">
	  <h2>INVESTIGATION</h2>
	    $investigation
    </div>
	<!-- CPA ACTION -->
	<div style="page-break-inside:avoid;">
	  <h2>ACTION</h2>
	    $action
    </div>
	<!-- CPA CLOSURE -->
	<div style="page-break-inside:avoid;">
	  <h2>CLOSURE</h2>
	    $closure
    </div>
    <!-- FOOTER -->
    <br>
    <div id="footer">
      <p>Generated $generationDateInfo by {$user->getFullname()}</p>
    </div>
  </body>
</html>
EOF;
    // Transform and send in PDF
    $pdf = new tldHTML2PDF($viewHTML);
    $pdf->outFile("CPA#$id.pdf");
break;
default:
    $body .= _getGeneralPage();
break;
}

?>
