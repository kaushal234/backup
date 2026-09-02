<?php
if(empty($id)){
    $DEFAULT_ERROR[]=  "ERROR: no line id set...";
    return;
}
$ccr = new tldCCR($id);
if($ccr->isEmpty()){
    $DEFAULT_ERROR[]=  "ERROR: No CCR#$id found...";
    return;
}
$header = $ccr->getHeader();
$DEFAULT_TITLE .= "\CCR#$id";
$DEFAULT_MENU .=<<<EOF
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<a href="$php_self?m[0]=ccr&m[1]=view&id=$id">General</a>
&nbsp;|&nbsp;<a href="$php_self?m[0]=ccr&m[1]=view&m[2]=msg&id=$id" title="Send message to customer team">Send Message</a>
&nbsp;|&nbsp;<a href="$php_self?m[0]=ccr&m[1]=view&m[2]=tasks&id=$id" title="Related tasks">Tasks</a>
&nbsp;|&nbsp;<a href="$php_self?m[0]=ccr&m[1]=view&m[2]=links&id=$id" title="Related links">Links</a>
&nbsp;|&nbsp;<a href="$php_self?m[0]=ccr&m[1]=view&m[2]=files&id=$id" title="Files linked to this SOR Line">Files</a>
&nbsp;|&nbsp;<a href="$php_self?m[0]=ccr&m[1]=view&m[2]=log&id=$id" title="Activity Log">Log</a>
&nbsp;|&nbsp;<a href="$php_self?m[0]=ccr&m[1]=view&m[2]=changeStatus&id=$id" title="Change Status">Status</a>
&nbsp;|&nbsp;<a href="ccr/ccr_admin.php?mode=record_view&form_type=main_tpl&id=$id" title="Edit this ccr">Edit</a>
EOF;
if($header['cuid']){
$DEFAULT_MENU .=<<<EOF
&nbsp;|&nbsp;<a href="/en/private/sales_service/sales.php?m[0]=customers&m[1]=view&id=${header['cuid']}" title="Go to Customer Record">
Customer</a>
EOF;
}
if($header['exu_id']){
$DEFAULT_MENU .=<<<EOF
&nbsp;|&nbsp;<a href="/en/private/sales_service/sales.php?m[0]=extranet&m[1]=view&id=${header['exu_id']}" title="Go to Extranet User Record">
Extranet User</a>
EOF;
}
switch($m[2]){
case 'msg':
	if($ccr->getStatus() == 'CLOSED'){
        $DEFAULT_ERROR[] = "ERROR: CCR is closed";
        $body .= getGeneralPage();
        break;
    }
    $DEFAULT_ERROR[] = "***WARNING*** THESE MESSAGES ARE VIEWABLE BY CUSTOMER FROM EXTRANET SITE ****";
    $DEFAULT_ERROR[] = "***WARNING*** DO NOT ADD ANY COMMENTS THAT NON TLD PERSONNEL SHOULD NOT VIEW ****";
    $form = new HTML_QuickForm('frmMsg', 'post');
    $form->addElement(  'hidden', 'm[0]', 'ccr');
    $form->addElement(  'hidden', 'm[1]', 'view');
    $form->addElement(  'hidden', 'm[2]', 'msg');
    $form->addElement(  'hidden', 'id', $id);
    $form->addElement(  'header', 'title', "Send message");
    $form->addElement(  'textarea','msg','Message',
        array("wrap"=>"VIRTUAL", "cols"=>"40", "rows"=>"8")
    );
    $form->addRule("msg", "Required", "required");
    $form->addElement(  'submit','btnSubmit','Submit');
    if (!$form->validate()){
        $body .= $form->toHTML();
        break;
    }
    $var = $form->exportValues();
    $a = tldUtils::cleanupFormInput($var);
    $error = $ccr->addMessage($user->getID(), $a['msg']);
    if(is_numeric($error)){
        $DEFAULT_ERROR[] = "Message sent...";
        $e = _notifyCustomer(
            "CCR$id, Message from TLD",
            $var['msg']
        );
    }else{
        $DEFAULT_ERROR[] = $error;
    }
break;
case "tasks":
    $DEFAULT_TITLE .="\Tasks";
    $DEFAULT_MENU .=<<<EOF
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<a href="/en/private/calendar/calendar.php?m[0]=tasks&m[1]=form&m[2]=newTask&module=ccr&parent_id=$id">New Task</a>
EOF;
    $sess["calendar"]["tasks"] = $ccr->getTasks();
    $form = new tldReportMultiLevel(
        $sess["calendar"]["tasks"],
        array(
          "status",
          "due_date"
        ),
        array(
            "id"				=>"Task#",
            "status"			=>"Status",
            "due_date"			=>"Due",
            "task"				=>"Task",
            "assignee_fullname"	=>"Assignee"
        ),
        array(
            "passField"=>"id",
            "title"=>"Tasks",
            "url"=>"/en/private/calendar/calendar.php?m[0]=tasks&m[1]=task&m[2]=view&id="
        )
    );
    $body .= $form->fetch();
break;
case 'log':
    $DEFAULT_TITLE .="\Log";
    $log = $ccr->getLog();
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
case 'files':
	if($ccr->getStatus() == 'CLOSED'){
        $DEFAULT_ERROR[] = "ERROR: CCR is closed";
        $body .= getGeneralPage();
        break;
    }
    $DEFAULT_TITLE .="\Files";
    $DEFAULT_MENU.=<<<EOF
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<a href="/en/private/common/index.php?m[0]=files&m[1]=form&m[2]=newFile&module=ccr&parent_id=$id">Add New File</a>
EOF;
    $DEFAULT_ERROR[] = "***WARNING*** THESE FILES ARE VIEWABLE BY CUSTOMER FROM EXTRANET SITE ****";
    $DEFAULT_ERROR[] = "***WARNING*** DO NOT ADD ANY FILES THAT NON TLD PERSONNEL SHOULD NOT VIEW ****";
    $report = new tldReportColumnar(
        $ccr->getFiles(),
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
    $DEFAULT_MENU.=<<<EOF
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<a href="/en/private/common/index.php?m[0]=links&m[1]=form&m[2]=newLink&module=ccr&parent_id=$id">New Link</a>
EOF;
    $report = new tldReportColumnar(
        tldModLink::byParent($id, 'ccr'),
        array(
            "xItems"=>array(
                "id"=>"ID#",
                "type"	=>"Module",
                "item"	=>"Ref#",
                "dsca"	=>"Description"
            ),
            "title"=>"Links FROM Here...",
            "links"=>array(
               "id"=>"/en/private/common/index.php?m[0]=links&m[1]=view&m[2]=redirect&erp=$erp&id="
            )
        )
    );
    $body .= $report->fetch();
    $report = new tldReportColumnar(
        tldModLink::byItem($id, 'ccr'),
        array(
            "xItems"=>array(
                "id"=>"ID#",
                "module"	=>"Module",
                "parent_id"	=>"Ref#",
                "dsca"	=>"Description"
            ),
            "title"=>"Links TO Here...",
            "links"=>array(
                "id"=>"/en/private/common/index.php?m[0]=links&m[1]=view&m[2]=redirect&reversed=1&erp=$erp&id="
            )
        )
    );
    $body .= $report->fetch();
break;
case 'changeStatus':
    if($ccr->getStatus() == 'CLOSED'){
        $DEFAULT_ERROR[] = "ERROR: CCR is already closed";
        $body .= getGeneralPage();
        break;
    }
    $DEFAULT_ERROR[] = "***WARNING*** THESE LOGS ARE VIEWABLE BY CUSTOMER FROM EXTRANET SITE ****";
    $DEFAULT_ERROR[] = "***WARNING*** DO NOT ADD ANY COMMENTS THAT NON TLD PERSONNEL SHOULD NOT VIEW ****";
    $allowed = $ccr->getStatusAllowed();
    $form = new HTML_QuickForm('frmChangeStatus', 'post');
    $form->addElement(	'header', 'title', 'Change status...');
    $form->addElement(	'hidden', 'm[0]', 'ccr');
    $form->addElement(	'hidden', 'm[1]', 'view');
    $form->addElement(	'hidden', 'm[2]', 'changeStatus');
    $form->addElement(	'hidden', 'id', $id);
    $form->addElement(  'select', 'm[3]', 'New Status', $allowed);
    $form->addElement(  'textarea', 'comment', 'Comment',
                    array("rows"=>10, "cols"=>40));
    $form->addElement(	'submit', 'submit', 'Submit');
    if (!$form->validate()){
        $body .= $form->toHTML();
        break;
    }
    $var = $form->exportValues();
    $a = tldUtils::cleanupFormInput($var);
    //check if new status is in allowed list
    if(!in_array($m[3], $allowed)){
        $DEFAULT_ERROR[] = "ERROR: Selected status is not allowed at this stage";
        break;
    }
    switch($m[3]){
    case 'REVIEW':

    break;
    case 'TLD':

    break;
    case 'CUSTOMER':

    break;
    case 'CLOSED':

    break;
    }
    $e = $ccr->changeStatus($m[3]);
    if(is_string($e)){
        $DEFAULT_ERROR[] = "ERROR: there was a problem changing the status, returned error was $e";
    }else{
        $DEFAULT_ERROR[] = "Status changed...";
        $header = $ccr->getHeader();
        $ccr->addLogEntry($user->getID(), $m[3]);
        if(!empty($a['comment'])){
        	$ccr->addMessage($user->getID(),$a['comment']);
        }
        // Make notifications
        $e = _notifyCustomer(
            "CCR$id, Status changed to ".$m[3],
            $var['comment']
        );
    }
    $body .= getGeneralPage();
break;
default:
    $body .= getGeneralPage();
break;
}

?>