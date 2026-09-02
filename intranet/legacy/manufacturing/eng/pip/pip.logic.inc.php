<?php
include_once("eng.inc.php");

$DEFAULT_TITLE .= "\Product Inovation Proposal";
$DEFAULT_MENU .=<<<EOF
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<a href="$php_self?m[0]=pip">Home</a>
&nbsp;|&nbsp;<a href="$php_self?m[0]=pip&m[1]=form&m[2]=byNum">By number</a>
&nbsp;|&nbsp;<a href="$php_self?m[0]=pip&m[1]=form&m[2]=new" title="Submit a new PIP">Submit</a>
&nbsp;|&nbsp;<a href="$php_self?m[0]=pip&m[1]=listing&m[2]=search">Search</a>
&nbsp;|&nbsp;<a href="$php_self?m[0]=pip&m[1]=reports">Reports</a>
&nbsp;|&nbsp;<a href="/en/private/mis/mis.php?m[0]=help&m[1]=dms&id=2011">Help</a>
EOF;

if($user->isInGroup(array("gg_ADMIN","gg_ENG"))){
	$DEFAULT_MENU .=<<<EOF
	&nbsp;|&nbsp;<a href="pip/pip_admin.php">Maintain PIP</a>
EOF;
}

switch($m[1]){
case 'form':
    switch($m[2]){
    case 'byNum':
            $DEFAULT_TITLE .= "\PIP by Number";
            $form = new HTML_QuickForm('frmByNum', 'post');
            $form->addElement(	'hidden', 'm[0]', 'pip');
            $form->addElement(	'hidden', 'm[1]', 'view');
            $form->addElement(	'header', 'title', "PIP Number");
            $form->addElement(	'text', 'id', 'PIP#');
            $form->addElement(	'submit', 'btnSubmit', 'Submit');
            $body .= $form->toHTML();
    break;
    case 'new':
        $form = new HTML_QuickForm('frmNew', 'post');
        $form->addElement(	'header', 'title', 'Submit New PIP Form');
        $form->addElement(	'hidden', 'm[0]', 'pip');
        $form->addElement(	'hidden', 'm[1]', 'form');
        $form->addElement(	'hidden', 'm[2]', 'new');
        $form->addElement(	'select', 'initiator', 'Initiator', tldDirectory::getUserlist('smartyOptions'));
        $factories = ["" => ""] + tldLocation::getFactoryList('smartyOptionsIDLocation');
        $categories = ["ALL_TYPES" => "ALL_TYPE"] + tldType::getTypes('en', 'smartyOptions');

        $form->addElement(	'select', 'product_type', 'Product Type', $categories);
        $form->addElement('select', 'model', 'Product Model', tldModel::getList());
        $form->addElement('text', 'short_desc', 'Short Description', ["size" => "50"]);
        $form->addElement('textarea', 'description', 'Full Description', ["wrap" => "VIRTUAL", "cols" => "40", "rows" => "8"]);
        $form->addElement('file', 'filename', 'Picture');
        $form->addElement('submit', 'btnSubmit', 'Submit');
        $form->addRule('factory', 'This is required', 'required');
        $form->setDefaults(["initiator" => $user->getId(), "factory" => ""]);
        if ($form->validate()){
                # If the form validates then freeze the data
                $form->freeze();
                $header = tldUtils::cleanupFormInput($form->exportValues());
                //process the upload file, if any
                $file = $form->getElement("filename");
                if($file->isUploadedFile()){
                        $attr = $file->getValue();
                        $destPath = tldPIP::getFileUploadDirectory();
                        $filepath = $destPath."/".time().$attr["name"];
                        while(file_exists($filepath)){
                                $filepath = $destPath."/".time().$attr["name"];
                        }
                        $header["picture_filename"] = basicFile::cleanupName(basename($filepath));
                        $file->moveUploadedFile($destPath, $header["picture_filename"]);
                }

                //get text values and save to db
                $header["poster"] = $user->getId();

                $error = tldPIP::insert($header);
                if(is_numeric($error)){
                        $pip = new tldPIP($error);
                        $short_desc = $pip->getShortDesc();
                        $body =<<<EOF
                        A new PENDING PIP has been submited. <br><br>
                        <a href="https://www.tld-gse.com/en/private/manufacturing/eng/dev.php?m[0]=pip&m[1]=view&single=1&id=$error">
                        Click here to see PIP#$error</a>
                        <hr>
                        $short_desc
EOF;
                        $pip->notifyInitiator($body, "New PIP #$error has been OPENED");
                        $pip->addLog($user->getID(), "PIP #$error OPENED by poster");
                }else{
                        $smarty->assign("error", "Could not create new PIP. There was an error processing. The error returned is '$error'");
                }
        }else{
                $body = $form->toHTML();
        }
    break;
    }
break;
case 'view':
    if(empty($id)){
            $DEFAULT_ERROR[]=  "ERROR: no line id set...";
            break;
    }
    $pip = new tldPIP($id);
    if($pip->isEmpty()){
		$DEFAULT_ERROR[]=  "ERROR: No PIP#$id found...";
		break;
    }
    $header = $pip->getHeader();
    $smarty->assign("pip", $header);
    $DEFAULT_TITLE .= "\PIP#$id";
    $DEFAULT_MENU .=<<<EOF
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<a href="$php_self?m[0]=pip&m[1]=view&id=$id">General</a>
&nbsp;|&nbsp;<a href="$php_self?m[0]=pip&m[1]=view&m[2]=tasks&id=$id" title="Related tasks">Tasks</a>
&nbsp;|&nbsp;<a href="$php_self?m[0]=pip&m[1]=view&m[2]=links&id=$id" title="Related links">Links</a>
&nbsp;|&nbsp;<a href="$php_self?m[0]=pip&m[1]=view&m[2]=files&id=$id" title="Files linked to this SOR Line">Files</a>
&nbsp;|&nbsp;<a href="$php_self?m[0]=pip&m[1]=view&m[2]=log&id=$id" title="Activity Log">Log</a>
&nbsp;|&nbsp;<a href="$php_self?m[0]=pip&m[1]=view&m[2]=changeStatus&id=$id" title="Change Status">Status</a>
&nbsp;|&nbsp;<a href="pip/pip_admin.php?mode=record_view&form_type=main_tpl&id=$id" title="Edit this pip">Edit</a>
EOF;
    switch($m[2]){
    case "tasks":
            $DEFAULT_TITLE .="\Tasks";
            $DEFAULT_MENU .=<<<EOF
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<a href="/en/private/calendar/calendar.php?m[0]=tasks&m[1]=form&m[2]=newTask&module=pip&parent_id=$id">New Task</a>
EOF;
            $sess["calendar"]["tasks"] = $pip->getTasks();
            $form = new tldReportMultiLevel($sess["calendar"]["tasks"],
                                    array("status","due_date"),
                                    array(	"id"				=>"Task#",
                                                    "status"			=>"Status",
                                                    "due_date"			=>"Due",
                                                    "task"				=>"Task",
                                                    "assignee_fullname"	=>"Assignee"
                                                    ),
                                    array("passField"=>"id",
                                    "title"=>"Tasks",
                                    "url"=>"/en/private/calendar/calendar.php?m[0]=tasks&m[1]=task&m[2]=view&id=")
                                    );
            $body .= $form->fetch();
    break;
    case 'log':
            $DEFAULT_TITLE .="\Log";
            $log = $pip->getLog();
            $report = new tldReportColumnar($log,
                    array("xItems"=>array("id"	=>"ID#",
                    "date"		=>"Date",
                    "poster_fullname"	=>"Poster",
                    "comment"		=>"Comment")
                    )
            );
            $body .= $report->fetch();
    break;
    case 'files':
            $DEFAULT_TITLE .="\Files";
            $DEFAULT_MENU.=<<<EOF
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<a href="/en/private/common/index.php?m[0]=files&m[1]=form&m[2]=newFile&module=pip&parent_id=$id">Add New File</a>
EOF;
            $report = new tldReportColumnar($pip->getFiles(),
                    array("xItems"=>array("id"=>"File ID",
                            "date"=>"Date",
                            "description"=>"Description",
                            "filename"=>"Filename"),
                            "links"=>array("id"=>"/en/private/common/index.php?m[0]=files&m[1]=view&id=")
                    )
            );
            $body .= $report->fetch();
    break;
    case 'links':
            $DEFAULT_MENU.=<<<EOF
    <br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
    <a href="/en/private/common/index.php?m[0]=links&m[1]=form&m[2]=newLink&module=pip&parent_id=$id">New Link</a>
EOF;
            $report = new tldReportColumnar(tldModLink::byParent($id, 'pip'),
                            array("xItems"=>array(
                                                    "id"=>"ID#",
                                                    "type"	=>"Module",
                                                    "item"	=>"Ref#",
                                                    "dsca"	=>"Description"
                                            ),
                                            "title"=>"Links FROM Here...",
                                            "links"=>array("id"=>"/en/private/common/index.php?m[0]=links&m[1]=view&m[2]=redirect&erp=$erp&id=")
                                            )
                                    );
            $body .= $report->fetch();
            $report = new tldReportColumnar(tldModLink::byItem($id, 'pip'),
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
    case 'changeStatus':
        if(in_array($pip->getStatus(), array("CLOSED", "REJECTED"))){
            $DEFAULT_ERROR[] = "ERROR: Cannot change status of a closed or rejected PIP...";
            break;
        }
	$form = new HTML_QuickForm('frmChangeStatus', 'post');
	$form->addElement(	'header', 'title', 'Change status');
	$form->addElement(	'hidden', 'm[0]', 'pip');
	$form->addElement(	'hidden', 'm[1]', 'view');
	$form->addElement(	'hidden', 'm[2]', 'changeStatus');
	$form->addElement(	'hidden', 'id', $id);
        $allowed = $pip->getStatusAllowed();
        if($pip->getStatus() == 'PENDING'){
            $factories = array(""=>"")+tldUtils::getSqlToAssocArray(	"SELECT id,location FROM locations WHERE factory='Y' ORDER BY location", "smartyOptions", ['id', 'location']
                        );
            $form->addElement(	'select', 'factory', 'Location',
                                $factories
                            );
            $form->addElement(	'select', 'ifactor', 'Importance Factor',
                                array(	""=>"", "1"=>"1", "10"=>"10","100"=>"100","1000"=>"1000")
                            );
            $form->addRule('factory', 'This is required', 'required');
            $form->addRule('ifactor', 'This is required', 'required');
        }else{
            $form->addElement(	'select', 'status_id', 'New status',
                    $allowed);
        }
        $form->addElement(  'textarea', 'comment', 'Comment',
			array("rows"=>10, "cols"=>40));
        $form->addElement(	'submit', 'btnSubmit', 'Submit');
	if ($form->validate()){
            $a = $form->exportValues();
            $opts = array("factory" => $a['factory'], "ifactor"=>$a['ifactor']);
            if($pip->getStatus() == 'PENDING'){
                $new_status = 'IN PROGRESS';
            }else{
                $new_status = $allowed[$status_id];
            }
            $error = $pip->changeStatus($new_status, $opts);
            if(is_string($error)){
                $DEFAULT_ERROR[] = "ERROR: there was a problem changing status, returned error was... $error<br>";
            }else{
                //not status for pending except in progress
                $pip->addLog($user->getID(), $new_status);
                if($a['comment']){
                    $pip->addLog($user->getID(), $a['comment']);
                }
                $pip->refresh();
                $header = $pip->getHeader();
                $short_desc = $pip->getShortDesc();
                $msg =<<<EOF
                PIP #$id status changed to $new_status<br><br>
                <a href="https://www.tld-gse.com/en/private/manufacturing/eng/dev.php?m[0]=pip&m[1]=view&single=1&id=$error">
                Click here to see PIP#$error</a>
                <hr>
                $short_desc
EOF;
                $pip->notifyInitiator($msg, "PIP #$id status changed to $new_status");
            }
            $smarty->assign("pip", $header);
            $body .= _getGeneralTab();
	}else{
            $body .= $form->toHTML();
	}
    break;
    default:
        $body .= _getGeneralTab();
}
break;
case 'listing':
    switch($m[2]){
    case 'search':

    break;
    case 'byProduct_type':
        if($id){
                $rows = tldPIP::byProduct(TldDatabase::escape($id));
                $title = "PIP by Product";
        }else{
                $form = new tldHTMLList(tldPIP::getTypes(),
                                 array("key"=>"product_type", "value"=>"product_type"),
                                 "$php_self?m[0]=pip&m[1]=listing&m[2]=byProduct_type&id=");
                $body = $form->fetch();
        }
    break;
    case 'byInitiator':
        if($id){
            $rows = tldPIP::byInitiator($id);
            $title = "PIP for Initiator #$id";
        }else{
            $form = new tldHTMLList(
                tldPIP::getInitiators(),
                array("key"=>"id", "value"=>"initiator_fullname"),
				"$php_self?m[0]=pip&m[1]=listing&m[2]=byInitiator&id="
            );
            $body = $form->fetch();
        }
    break;
    case 'byUserid':
        if($id){
            $rows = tldPIP::byAssignee($id);
            $title = "PIP for Userid #$id";
        }else{
            $form = new tldHTMLList(
                tldTask::getAssignees(array("module"=>"PIP")),
                array("key"=>"id", "value"=>"fullname"),
                "$php_self?m[0]=pip&m[1]=listing&m[2]=byUserid&id="
            );
            $body = $form->fetch();
        }
    break;
    case 'byErpStatus':
        $erp = TldDatabase::escape($y);
        $status = TldDatabase::escape($x);

        $rows = tldPIP::byERPStatus($erp, $status);
        $title = "PIP $erp $status";
    break;
    }
    if(count($rows ?? [])){
        $report = new tldReportColumnar($rows,
                        array("xItems"=>array("id"=>"PIP #",
                                            "status"=>"Status",
                                            "date"=>"Date",
                                            "product_type"=>"Type",
                                            "model"=>"Model",
                                            "short_desc"=>"Short Description"
                                        ),
                                "title"=>$TITLE,
                                "links"=>array("parent_id"=>"$php_self?m[0]=pip&m[1]=view&id=",
                                        "id"=>"$php_self?m[0]=pip&m[1]=view&id=")
                                )
                        );
        $body .= $report->fetch();
    }else{
        $DEFAULT_ERROR[] = "ERROR: No PIPs found...";
    }
break;
default:
	$body .= $smarty->fetch("$PATH/pip/homepage.pip.tpl");
        //COUNT BY FACTORY, STATUS REPORT
	$form = new tldMatrix(	tldPIP::countByFactoryStatus(),
                                "status", "location", "num",
                                "$php_self?m[0]=pip&m[1]=listing&m[2]=byErpStatus",
                                "PIP Count by Status, Factory");
	$body .= $form->fetch();

        //LATEST REPORT
        $sess["pip"]["list"] = tldPIP::byLatest();
	$report = new tldReportColumnar(	$sess["pip"]["list"],
					array("xItems"=>array("id"=>"PIP #",
							"status"=>"Status",
							"date"=>"Date",
							"product_type"=>"Type",
							"model"=>"Model",
							"short_desc"=>"Short Description"
							),
							"links"=>array("id"=>"$php_self?m[0]=pip&m[1]=view&id="),
							"title"=>"Recently Added PIPs"
					)
				);
	$body .= $report->fetch();

}

function _getGeneralTab(){
    global $header;
    if($header['picture_filename']){
        $ret =<<<EOF
    <a href="/en/private/uploads/pip/${header['picture_filename']}" target="_blank">
<img src="/en/private/uploads/pip/${header['picture_filename']}" width="300" align="right"></a>
EOF;
    }
    $report = new tldAssocTable($header,
                    array(
                            "id"		=>"PIP#",
                            "status"		=>"Status",
                            "ifactor"		=>"Importance Factor",
                            "date"		=>"Date Entered",
                            "date_closed"	=>"Date Closed",
                            "date_suspended"	=>"Date Suspended",
                            "factory_fullname"	=>"Factory",
                            "product_type"	=>"Product Type",
                            "short_desc"	=>"Short Description",
                            "initiator_fullname"=>"Initiator",
                            "poster_fullname"	=>"Poster",
                            "description"	=>"Full Description"
//                            "months_open"       =>"Months Open",
                    ),
                    array("title"=>"PIP Details")
            );
    $ret .= $report->fetch();
    return $ret;
}
?>
