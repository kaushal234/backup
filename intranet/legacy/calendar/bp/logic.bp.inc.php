<?php
$DEFAULT_TITLE .= "\BP";

$DEFAULT_MENU .=<<<EOF
	<br>
	&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
	<a href="$php_self?m[0]=bp">Home</a>
EOF;
//	&nbsp;|&nbsp;<a href="$php_self?m[0]=bp&m[1]=form&m[2]=byNumber">By Number</a>
//	&nbsp;|&nbsp;<a href="$php_self?m[0]=bp&m[1]=reports">Reports</a>
if($user->isInGroup("superuser")) {
    $DEFAULT_MENU .=<<<EOF
        &nbsp;|&nbsp;<a href="bp/bp_admin.php">BP Admin</a>
EOF;
}
switch($m[1]) {
    case 'view':
        if(empty($id)) {
            $DEFAULT_ERROR[] = "ERROR: BP ID not set";
            break;
        }
        $bp = new tldBP($id);
        $header = $bp->getHeader();

        $DEFAULT_MENU .=<<<EOF
	<br>
	&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
	&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
	<a href="$php_self?m[0]=bp&m[1]=view&id=$id">General</a>
	&nbsp;|&nbsp;<a href="$php_self?m[0]=bp&m[1]=view&m[2]=tasks&id=$id">Tasks &amp; Sequences</a>
	&nbsp;|&nbsp;<a href="$php_self?m[0]=bp&m[1]=view&m[2]=files&id=$id">Files</a>
	&nbsp;|&nbsp;<a href="$php_self?m[0]=bp&m[1]=view&m[2]=logs&id=$id">Logs</a>
	&nbsp;|&nbsp;<a href="$php_self?m[0]=bp&m[1]=view&m[2]=transfert&id=$id">Transfert Owner</a>
	&nbsp;|&nbsp;<a href="$php_self?m[0]=bp&m[1]=view&m[2]=close&id=$id">Mark as DONE</a>
EOF;

        if($user->isInGroup("superuser")) {
            $DEFAULT_MENU .=<<<EOF
		&nbsp;|&nbsp;<a href="/en/private/calendar/bp/bp_admin.php?mode=record_view&form_type=main_tpl&id=$id">Edit</a>
EOF;
        }
        if($header['module']) {
            $modLinks = tldUtils::getModLinks();
            $modLink = $modLinks[$header['module']];
            $parent_id = $header['parent_id'];
            $DEFAULT_MENU .=<<<EOF
		&nbsp;|&nbsp;<a href="$modLink$parent_id" title="This BP is linked to a document in another module, click here to view...">
		Go to ${header['module']}#$parent_id</a>
EOF;
        }

        switch($m[2]) {
       		case 'transfert':
       			$userBP = new tldUser($bp->itsHeader['owner']);
        		if($userBP->getID()!=$user->getID() &&
        		$userBP->getSupervisor()!=$user->getID()){
					$DEFAULT_ERROR[] = "ERROR: Only the BP owner or his supervisor can transfer this BP";
					break;
				}
       			$user_list = tldDirectory::getUserlist("smartyOptions");
       			$DEFAULT_TITLE .= "\Transfert";
				$form = new HTML_QuickForm('frm', 'post');
				$form->addElement(	'hidden', 'm[0]', 'bp');
				$form->addElement(	'hidden', 'm[1]', 'view');
				$form->addElement(	'hidden', 'm[2]', 'transfert');
				$form->addElement(	'hidden', 'id', $id);
				$form->addElement(	'header', 'title', "Transfert Owner");
				$form->addElement(	'select', 'uid', 'New Owner', $user_list);
				$form->addElement(	'submit', 'btnSubmit', 'Submit');
				$form->addRule('uid', 'This is required', 'required');
				if ($form->validate()){
					$vars = tldUtils::cleanupFormInput($form->exportValues());
					if($vars['uid']==$bp->itsHeader['owner']){
						$DEFAULT_ERROR[] = "ERROR: BP owner is already ".$bp->itsHeader['owner_fullname'];
						break;
					}
					$e = $bp->transfert($vars['uid']);
					if(!is_string($e)){
						$body = "<br/>BP owner successfully updated!";
						$bp->addLogEntry($user->getID(),"BP owner UPDATED to ".$bp->itsHeader['owner_fullname']);
					}
					else {
                        $DEFAULT_ERROR[] = "INTERNAL ERROR: BP owner not updated.<br/>Reason: $e";
                    }
				}
				else {
                    $body = $form->toHTML();
                }
      		break;
            case 'files':
                $DEFAULT_MENU.=<<<EOF
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<a href="/en/private/common/index.php?m[0]=files&m[1]=form&m[2]=newFile&module=BP&parent_id=$id">Add New File</a>
EOF;
                $DEFAULT_TITLE .= "\Files";
                $form = new tldReportColumnar(	$bp->getFiles(),
                    array("xItems"=>array(	"id"=>"ID#",
                    "date"=>"Date",
                    "description"=>"Description",
                    "filename"=>"Filename"),
                    "title"=>"File list",
                    "links"=>array("id"=>"/en/private/common/index.php?m[0]=files&m[1]=view&m[2]=out&id=")
                    )
                );
                $body .= $form->fetch();
                break;
            case "tasks":
                $sess["calendar"]["tasks"] = $bp->getTasks('ALL');
                $form = new tldReportMultiLevel($sess["calendar"]["tasks"],
                    array("status","due_date"),
                    array(	"id"				=>"Task#",
                    "status"			=>"Status",
                    "due_date"			=>"Due",
                    "overdue_icon"		=>"Overdue?",
                    "task"				=>"Task",
                    "assignee_fullname"	=>"Assignee"
                    ),
                    array("passField"=>"id",
                    "title"=>"Tasks &amp; Sequences",
                    "url"=>"/en/private/calendar/calendar.php?m[0]=tasks&m[1]=task&m[2]=view&id=")
                );
                $body .= $form->fetch();
                break;
			case "logs":
                $report = new tldReportColumnar($bp->getLog(),
	                array("xItems"=>array(
		                "id"=>"ID#",
		                "date"=>"Date",
		                "poster_fullname"=>"Poster",
		                "comment"=>"Comment")
	                )
	            );
			    $body .= $report->fetch();
                break;
            default:
                switch($m[2]) {
                    case 'close':
                        if($bp->isClosed()) {
                            $DEFAULT_ERROR[] = "ERROR: BP is already closed";
                            break;
                        }
                        //Task 93069 & 290605
                        if($header['owner'] <> $user->getID() AND !$user->isInGroup(array("role_EM","role_ES"))) {
                            $DEFAULT_ERROR[] = "ERROR: Only the owner can close this BP";
                            break;
                        }
                        $error = $bp->close();
                        if(is_string($error)) {
                            $DEFAULT_ERROR[] = $error;
                        }
                        else {
                            $bp->addLogEntry($user->getID(), "CLOSED");
                        }
                        $header = $bp->getHeader();
                        break;
                }
                //if there is an attachment, show it here
                if (strtoupper(substr($header['filename'], -3))=="JPG") {
                    $body .=<<<EOF
			<a href="/en/private/uploads/cal_bp/${header['filename']}" target="_blank">
				<img src="/en/private/uploads/cal_bp/${header['filename']}" width="250" align="right">
			</a>
EOF;
                }
                elseif($header['filename']<>"") {
                    $body .=<<<EOF
			<a href="/en/private/uploads/cal_bp/${header['filename']}" target="_blank">
				<img src="/shared/bluesphere/64x64/mimetypes/document.png" align="right" alt="Download Attachment">
			</a>
EOF;
                }
                $report = new tldAssocTable(
                    $header,
                    array("id"=>"Process #",
                    "status"=>"Status",
                    "module"=>"Module",
                    "parent_id"=>"Ref#",
                    "owner_fullname"=>"Owner",
                    "dt_opened"=>"Date Opened",
                    "short_desc"=>"Short Description",
                    "long_desc"=>"Description",
                    "proposal_solution"=>"Proposal solution",
                    "actions"=>"Actions",
                    "filename"=>"Attachment Filename",
                    "dt_closed"=>"Date closed"
                    ),
                    array(
                        "title"=>"General",
                        "links"=>array(
                            "parent_id" => $modLink
                        )
                    )
                );
                $body .= $report->fetch();
        }
//post processing
        break;
    default:
        $body = $smarty->fetch("$PATH/${m[0]}/homepage.${m[0]}.tpl");
        $report = new tldReportColumnar(
            tldBP::byLatest(),
            array(
            "xItems"=>array("id"=>"BP#",
            "status"=>"Status",
            "module"=>"Module",
            "parent_id"=>"Ref#",
            "dt_opened"=>"Date Opened",
            "dt_closed"=>"Date Closed",
            "short_desc"=>"Short Description"
            ),
            "title"=>"Most Recently Created Processes",
            "links"=>array("id"=>"$php_self?m[0]=bp&m[1]=view&id=")
            )
        );
        $body .= $report->fetch();
}
