<?php

$DEFAULT_TITLE .= "\Logs";

$DEFAULT_MENU .=<<<EOF
<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<a href="$php_self?m[0]=logs">Home</a>
EOF;
if($user->isInGroup("superuser")){
	$DEFAULT_MENU .=<<<EOF
	&nbsp;|&nbsp;<a href="logs/logs_admin.php">Logs Admin</a>
EOF;
}
switch($m[1]){
case "form":
	switch($m[2]){
	case "newLog":
		$DEFAULT_MENU="";
		$DEFAULT_TITLE .= "\New Log";
		//check module
                $module = strtoupper($module);
		$modules = tldTask::getModuleList();
		if(!in_array($module, $modules)){
			"ERROR: Module '$module' is not valid";
			break;
		}
		//check parent_id
		if(empty($parent_id)){
			$DEFAULT_ERROR[] = "ERROR: parent_id not set";
			break;
		}
		$form = new HTML_QuickForm('frmNew', 'post');
		$form->addElement(	'header', 'title', "Submit new $module Link");
		$form->addElement(	'hidden', 'm[0]', 'log');
		$form->addElement(	'hidden', 'm[1]', 'form');
		$form->addElement(	'hidden', 'm[2]', 'newLog');
		$form->addElement(	'hidden', 'module', $module);
		$form->addElement(	'hidden', 'parent_id', $parent_id);
		$form->addElement(	'textarea','note','Note',
							array("wrap"=>"VIRTUAL", "cols"=>"40", "rows"=>"8")
						);
		$form->addElement(	'submit', 'btnSubmit', 'Submit');
		$form->addRule("Note","This is a required field.","required");
		if ($form->validate()){
			# If the form validates then freeze the data
			$form->freeze();
			$vals = $form->exportValues();
			$error = tldModLog::insert($vals['module'], $vals['parent_id'], $user->getID(), $vals['note']);
			if(!is_numeric($error)){
				$DEFAULT_ERROR[] = "ERROR: There was an error adding the new log...$error";
			}
			$url = tldModLink::getURL($vals['module'], $vals['parent_id']);
			$body =<<<EOF
			<a href="$url">Click here to go back to $module #$parent_id</a>
EOF;
		}else{
			$body = $form->toHTML();
		}
	break;
	}
break;
case 'view':
    if(empty($id)){
        $DEFAULT_ERROR[] = "ERROR: No Log ID# given...";
        break;
    }
	$log = new tldModLog($id);
    $header  = $log->itsHeader;
    $module = $log->getModule();
    $pid = $log->getParentID();
    $url = tldModLink::getURL($module, $pid);

    $DEFAULT_MENU .=<<<EOF
    <br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
    <a href="$php_self?m[0]=logs&m[1]=view&id=$id">General</a>
    &nbsp;|&nbsp;<a href="$php_self?m[0]=logs&m[1]=view&m[2]=links&id=$id">Links</a>
    &nbsp;|&nbsp;<a href="/en/private/common/logs/logs_admin.php?mode=record_view&form_type=main_tpl&id=$id">Edit</a>
&nbsp;|&nbsp;<a href="$url">Back to $module #$pid</a>
EOF;
    switch($m[2]){
    case 'links':
        $DEFAULT_TITLE .="\Links";
        $report = new tldReportColumnar($log->getLinksFromHere(),
                array("xItems"=>array(
                            "id"=>"ID#",
                            "type"	=>"Module",
                            "item"	=>"Ref#",
                            "dsca"	=>"Description"
                        ),
                        "title"=>"Links FROM here...",
                        "links"=>array("id"=>"/en/private/common/index.php?m[0]=links&m[1]=view&m[2]=redirect&erp=$erp&id=")
                        )
                    );
        $body .= $report->fetch();
        $report = new tldReportColumnar($log->getLinksToHere(),
                array("xItems"=>array(
                            "id"=>"ID#",
                            "module"	=>"Module",
                            "parent_id"	=>"Ref#",
                            "dsca"	=>"Description"
                        ),
                        "title"=>"Links TO Here...",
                        "links"=>array("id"=>"/en/private/common/index.php?m[0]=links&m[1]=view&m[2]=redirect&reversed=1&erp=$erp&id=")
                        )
                    );
        $body .= $report->fetch();
    break;
    default:
        $form = new tldAssocTable($header,
                array("id"=>"Log ID#",
                    "module"=>"Ref Mod",
                    "parent_id"=>"Ref#",
                    "date"=>"Date",
                    "poster_fullname"=>"Poster",
                    "comment"=>"Comment"),
                array("title"=>"Log Entry")
            );
        $body .= $form->fetch();
    }
break;
default:
//	$body = $smarty->fetch("$PATH/${m[0]}/homepage.${m[0]}.tpl");
}
