<?php
// Get MOO
$moo_id = tldModule::getMOOIDByModule("gwf");
$DEFAULT_TITLE .= "\Group Workflow";
$DEFAULT_MENU .=<<<EOF
	<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
	<a href="$php_self?m[0]=gwf">Home</a>
	&nbsp;|&nbsp;<a href="$php_self?m[0]=gwf&m[1]=forms&m[2]=new">New GWF</a>
	&nbsp;|&nbsp;<a href="$php_self?m[0]=gwf&m[1]=forms&m[2]=byID">By Num</a>
	&nbsp;|&nbsp;<a href="$php_self?m[0]=gwf&m[1]=listing&m[2]=byKeyword">By Keyword</a>
	&nbsp;|&nbsp;<a href="$php_self?m[0]=gwf&m[1]=listing&m[2]=byDescription">By Description</a>
	&nbsp;|&nbsp;<a href="$php_self?m[0]=gwf&m[1]=reports">Reports</a>
	&nbsp;|&nbsp;<a href="/en/private/mis/mis.php?m[0]=help&m[1]=dms&id=830">Help</a>
	&nbsp;|&nbsp;<a href="/en/private/directory/index.php?m[0]=people&m[1]=view&id=$moo_id">Owner</a>
EOF;

if($user->isInGroup("superuser")){
	$DEFAULT_MENU .=<<<EOF
	&nbsp;|&nbsp;<a href="gwf/gwf_admin.php">GWF Admin</a>
EOF;
}

// New category list (replaces list list.gwf.category from db) from ticket #397826
// 'CATEGORY' => Supervisor ID
$category_list = [
    'ACCOUNTING PROCESS/DOC' => 12303,  // Nicolas BORNE
    'BUILDING MAINTENANCE' =>  7069, // Pierre PERROT
    'CASH MANAGEMENT' => 2636,  // Laurent Jamet
    'ENGINEERING' => 108,  // Laurent Decoux
    'ERP' => 11233,  // Xavier Rolland
    'MANUFACTURING' => null, // Nicolas Vérin
    'FINANCE' => 2636,  // Laurent Jamet
    'GS CHINA' => 1272,  // FENG Chunlei
    'GS INDIA' => 9491,  // Venkatesh PRASAD
    'GS TURKEY & EASTERN EUROPE' => 7069, // Pierre PERROT
    'HR' => 1220,  // Yves Crespel
    'INTERNAL AUDIT' => 10774, // Clément JOLLY
    'LOGISTICS' => 7069, // Pierre PERROT
    'MANAGEMENT' => 365,  // David Flahault
    'MIS' => 5531 ,  // BRUN Frédéric
    'PRODUCT LIABILITY' => 1619,  // Karen Chabrières
    'PRODUCT SUPPORT' => 2015,  // Emeline KLEIN
    'PURCHASING' => 7069, // Pierre PERROT
    'QUALITY' => 463, // Sebastien Fabre
    'SAFETY AND HEALTH' => 1619,  // Karen Chabrières
    'SERVICE' => 293,  // Celine Tessier
    'TECHNICAL TRAINING' => 125,  // Chad Yergeau
    'COMPLIANCE' => 1619, // Karen Chabrières
    'AERO Specialties' => 3195,  // Brad Streeter
    'SAS-APU OFF' => 4880,  // Jérémie Magain
    'ENVIRONMENT' => null,
    'GS Maghreb' => 7069, // Pierre PERROT
];

switch($m[1]){
case 'forms':
	switch($m[2]){
	case 'byID':
		$form = new HTML_QuickForm('frmByID', 'post');
		$form->addElement(	'hidden', 'm[0]', 'gwf');
		$form->addElement(	'hidden', 'm[1]', 'view');
		$form->addElement(	'text','id','GWF Number');
		$form->addElement(	'submit','btnSubmit','Submit');
		$body .= $form->toHTML();
	break;
	case 'new':
		$form = new HTML_QuickForm('frmNewGWF', 'post');
		$form->addElement(	'header', 'title', 'Submit New GWF Form');
		$form->addElement(	'hidden', 'm[0]', 'gwf');
		$form->addElement(	'hidden', 'm[1]', 'forms');
		$form->addElement(	'hidden', 'm[2]', 'new');
		$form->addElement(	'select', 'ctg', 'Category',
            array_merge(array(""=>""), array_combine(array_keys($category_list), array_keys($category_list)))
        );
		$factories = array(""=>"")+tldLocation::getLocationList("smartyOptions");
		$form->addElement(	'select', 'pvt', 'Confidential to members only?',
							array(""=>"", "Y"=>"Y", "N"=>"N"));
		$form->addElement(	'select', 'ifactor', 'Importance Factor',array("1"=>"1", "10"=>"10", "100"=>"100", "1000"=>"1000"));
		$form->addElement(	'text', 'dsca', 'Short Description (English only)',
							array("size"=>"50")
						);
		$form->addElement(	'textarea', 'dscb', 'Full Description',
							array("wrap"=>"VIRTUAL", "cols"=>"40", "rows"=>"8")
						);
		$form->addElement(	'select', 'bu', 'Business Unit',
							$factories
						);
		$categories = ["" => "", "ALL_TYPES" => "ALL_TYPE"] + tldType::getTypes('', 'smartyOptions');

		$form->addElement('select', 'type', 'Product Type', $categories);
		$form->addElement('select', 'model', 'Product Model', ['' => ''] + tldUtils::getSqlToAssocArray('SELECT model FROM models WHERE hide=0 ORDER BY model', 'smartyOptions', ['model', 'model']));
		$form->addElement('date', 'dest', 'Est. Completion Date (default +2weeks)', ["format" => "Ymd", "minYear" => date("Y"), "maxYear" => date("Y") + 2]);
		$form->addElement('text', 'kwd1', 'Key Word #1', ["size" => "20"]);
		$form->addElement('text', 'kwd2', 'Key Word #2', ["size" => "20"]);
		$form->addElement('text', 'kwd3', 'Key Word #3', ["size" => "20"]);
		$form->addElement('text', 'kwd4', 'Key Word #4', ["size" => "20"]);
		$form->addElement('text', 'kwd5', 'Key Word #5', ["size" => "20"]);
		$ams =& $form->addElement('advmultiselect', 'members', null,
									tldDirectory::getUserlist("smartyOptions"),
								   array('size' => 10,
										 'class' => 'pool',
										 'style' => 'width:500px;'
										)
		);
		$ams->setLabel(['Members... (OPTIONAL)', 'Addressbook', 'CC']);
		$ams->setButtonAttributes('add', ['value' => '-->>', 'class' => 'inputCommand']);
		$ams->setButtonAttributes('remove', ['value' => '<<--', 'class' => 'inputCommand']);

		$form->setDefaults(["dest" => [
				"Y" => date("Y", strtotime("+2 week")),
				"m" => date("m", strtotime("+2 week")),
				"d" => date("d", strtotime("+2 week")),
			]]
		);
		$form->addElement(	'submit', 'btnSubmit', 'Submit');
		$form->addRule('pvt', 'This is required', 'required');
		$form->addRule('ctg', 'This is required', 'required');
		$form->addRule('bu', 'This is required', 'required');
		$form->addRule('dsca', 'This is required', 'required');
		$form->addRule('dscb', 'This is required', 'required');
		if (!$form->validate()){
			$body = $form->toHTML();
			break;
		}
		# If the form validates then freeze the data
		$form->freeze();
		$header = tldUtils::cleanupFormInput($form->exportValues());
		//get text values and save to db
		$header["assignor"] = $user->getId();
		if (!empty($category_list[$header['ctg']]) && !in_array($category_list[$header['ctg']], $header['members'] ?? [])) {
			if (is_array($category_list[$header['ctg']])) {
				foreach ($category_list[$header['ctg']] as $autoMember) {
					if (!in_array($autoMember, $header['members'])) {
						$header['members'][] = $autoMember;
					}
				}
			} else {
				$header['members'][] = $category_list[$header['ctg']];
			}
		}
		$error = tldGWF::insert($header);
		if (!is_numeric($error)) {
			$DEFAULT_ERROR[] = "Could not create new GWF. There was an error processing. The error returned is '$error'";
		} else {
			if (!empty($header['members'])) {
				$gwfId = $error;
				$gwf = new tldGWF($gwfId);
				$query = 'SELECT p.email FROM people AS p WHERE p.id IN ('.implode(',', $header['members']).')';
				$mailCc = tldUtils::getSqlToAssocArray($query, 'smartyOptions', ['email', 'email']);
				$mailTo = [$user->getEmail()];
				$mailSubject = 'GWF #'.$gwfId.' Created by '.$user->getFullname();
				$mailBody = '<p>This is to inform you that GWF #'.$gwfId.' - '.$gwf->getDsca().' has been created by '.$user->getFullname().' and you have been included as member.</p>';
				$mailBody .= '<p>If you want to see the GWF, please log in to the TLD-GSE intranet and go to the GWF module to view.</p>';
				$mailBody .= '<a href="https://'.$_SERVER['HTTP_HOST'].$php_self.'?m[0]=gwf&m[1]=view&id='.$gwfId.'">Click here to go to GWF : '.$gwf->getDsca().'</a>';
				tldUtils::emailAttachment($mailTo, 'noreply@tld-gse.com', $mailSubject, $mailBody, '', $mailCc);
			}

			$body .= <<<EOF
				<a href="$php_self?m[0]=gwf&m[1]=view&id=$error">GWF# $error has now been created, click here.</a>
EOF;
		}

	break;
	}
break;
case 'view':
	if(empty($id)){
		$DEFAULT_ERROR[] = "ERROR: no id set to view";
		break;
	}
	$gwf = new tldGWF($id);
	if($gwf->isEmpty()){
		$DEFAULT_ERROR[] = "ERROR: Could not create gwf object";
		break;
	}
	//check if confidential and a member
	if($gwf->isPrivate() && $gwf->isMember($user->getID()) !== true && !$user->isInGroup("superuser")){
		$DEFAULT_ERROR[] = "ERROR: Only members can access this confidential GWF";
		break;
	}

	$header = $gwf->getHeader();
	$smarty->assign("gwf", $header);
	//check if current id is in the sess.gwfs.list array i.e. need to destroy memory of list
	if($single) unset($sess["gwf"]["list"]);

	$DEFAULT_TITLE.= "\GWF#$id";
	$DEFAULT_MENU.=<<<EOF
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<a href="$php_self?m[0]=gwf&m[1]=view&m[2]=&id=$id">General</a>
&nbsp;|&nbsp;<a href="$php_self?m[0]=gwf&m[1]=view&m[2]=members&id=$id">Members</a>
&nbsp;|&nbsp;<a href="$php_self?m[0]=gwf&m[1]=view&m[2]=tasks&id=$id">Tasks</a>
&nbsp;|&nbsp;<a href="$php_self?m[0]=gwf&m[1]=view&m[2]=comments&id=$id" title="Tasks comments from linked modules">Linked Comments</a>
&nbsp;|&nbsp;<a href="$php_self?m[0]=gwf&m[1]=view&m[2]=addComment&id=$id">Add comment</a>
&nbsp;|&nbsp;<a href="$php_self?m[0]=gwf&m[1]=view&m[2]=files&id=$id">Files</a>
&nbsp;|&nbsp;<a href="$php_self?m[0]=gwf&m[1]=view&m[2]=links&id=$id">Links</a>
&nbsp;|&nbsp;<a href="$php_self?m[0]=gwf&m[1]=view&m[2]=log&id=$id">Log</a>
&nbsp;|&nbsp;<a href="$php_self?m[0]=gwf&m[1]=view&m[2]=changeStatus&id=$id">Change Status</a>
 | <a href="$php_self?m[0]=gwf&m[1]=view&m[2]=duplicate&id=$id">Duplicate</a>
EOF;
	$body = $smarty->fetch("$PATH/gwf/gwf.menu.tpl");
if($user->isInGroup("superuser") || $gwf->getAssignor()==$user->getID()){
	$DEFAULT_MENU.=<<<EOF
&nbsp;|&nbsp;<a href="gwf/gwf_admin.php?mode=record_view&form_type=main_tpl&id=$id">Edit</a>
EOF;
}
	switch($m[2]){
	case 'links':
		$DEFAULT_MENU.=<<<EOF
	<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
	<a href="/en/private/common/index.php?m[0]=links&m[1]=form&m[2]=newLink&module=GWF&parent_id=$id">Add New Link</a>
EOF;
		$DEFAULT_TITLE .= "\Links";
        $report = new tldReportColumnar(tldModLink::byParent($id, 'GWF'),
            ["xItems" => [
                "id" => "ID#",
                "type" => "Module",
                "item" => "Ref#",
                "dsca" => "Description"
            ],
                "title" => "Links FROM Here...",
                "links" => ["id" => "/en/private/common/index.php?m[0]=links&m[1]=view&m[2]=redirect&erp=$erp&id="],
                "functions" => [
                    "Delete" => [
                        "url" => "/en/private/common/index.php?m[0]=links&m[1]=view&m[2]=delconf&id=",
                        "param" => ['id' => 'id'],
                        "img" => "/shared/icons/application/delete.png",
                    ],
                ],
            ]
        );
        $body .= $report->fetch();
        $report = new tldReportColumnar(tldModLink::byItem($id, 'GWF'),
            ["xItems" => [
                "id" => "ID#",
                "module" => "Module",
                "parent_id" => "Ref#",
                "dsca" => "Description"
            ],
                "title" => "Links TO Here...",
                "links" => ["id" => "/en/private/common/index.php?m[0]=links&m[1]=view&m[2]=redirect&reversed=1&erp=$erp&id="],
                "functions" => [
                    "Delete" => [
                        "url" => "/en/private/common/index.php?m[0]=files&m[1]=view&m[2]=delconf&id=",
                        "param" => ['id' => 'id'],
                        "img" => "/shared/icons/application/delete.png",
                    ],
                ],
            ]
        );
        $body .= $report->fetch();
	break;
	case 'members':
		$DEFAULT_MENU .=<<<EOF
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<a href="$php_self?m[0]=gwf&m[1]=view&m[2]=members&id=$id">Members</a>
&nbsp;|&nbsp;<a href="/en/private/common/index.php?m[0]=members&m[1]=new&module=GWF&parent_id=$id">Add Members</a>
&nbsp;|&nbsp;<a href="/en/private/common/index.php?m[0]=members&m[1]=delete&module=GWF&parent_id=$id">Delete Members</a>
EOF;
        $DEFAULT_TITLE .= "\Members";
        $form = new tldReportColumnar(
            $gwf->getMembers(),
            array(
                "xItems"=>array(
                    "id"=>"UID#",
                    "lastname"=>"Lastname",
                    "firstname"=>"Firstname"
                ),
                "title"=>"Members",
                "links"=>array(
                    "id"=>"/en/private/directory/index.php?m[0]=people&m[1]=view&id="
                )
            )
        );
        $body .= $form->fetch();
	break;
	case 'files':
		$DEFAULT_MENU.=<<<EOF
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<a href="/en/private/common/index.php?m[0]=files&m[1]=form&m[2]=newFile&module=GWF&parent_id=$id">Add New File</a>
EOF;
		$DEFAULT_TITLE .= "\Files";
		$form = new tldReportColumnar(
            $gwf->getFiles(),
			array(
                "xItems"=>array(
                    "id"=>"ID#",
                    "date"=>"Date",
                    "poster_fullname"=>"Poster",
                    "description"=>"Description",
                    "filename"=>"Filename"
                ),
				"title"=>"File list",
				"links"=>array(
                    "id"=>"/en/private/common/index.php?m[0]=files&m[1]=view&id="
                )
            )
        );
		$form1 = new tldReportColumnar($gwf->getFileByTasks("ALL"),
			array(
				"xItems"=>array(
					"id"=>"ID#",
					"date"=>"Date",
					"poster_fullname"=>"Poster",
					"filename"=>"Filename"
				),
				"title"=>"File list in Tasks",
				"links"=>array(
					"filename"=>"/en/private/uploads/tasks_comments/"
				)
			)
		);
		$body .= $form->fetch();
		$body .= $form1->fetch();
	break;
	case 'tasks':
		$DEFAULT_TITLE .= "\Tasks";
		//only show to admin people or the initiator
//		if($user->isInGroup("gg_ADMIN") || $user->getID()==$gwf->getAssignor()){
		$DEFAULT_MENU.=<<<EOF
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<a href="/en/private/calendar/calendar.php?m[0]=tasks&m[1]=form&m[2]=newTask&module=GWF&parent_id=$id">Add New Task</a>
EOF;
//}
		if($user->isInGroup("gg_MIS")){
			$DEFAULT_MENU.=<<<EOF
&nbsp;|&nbsp;<a href="$php_self?m[0]=gwf&m[1]=view&m[2]=tasks&m[3]=quickEdit&id=$id">Quick edit</a>
EOF;
		}
		if ($user->isInGroup('superuser') || $user->getID() === $gwf->getAssignor()) {
			$DEFAULT_MENU.=<<<EOF
&nbsp;|&nbsp;<a href="$php_self?m[0]=gwf&m[1]=view&m[2]=tasks&m[3]=pauseAll&id=$id">Pause all tasks</a>
EOF;
		}
		$sess["calendar"]["tasks"] = $gwf->getTasks();
		$form = new tldReportMultiLevel(
			$sess["calendar"]["tasks"],
			[
				"status",
				"due_date"
			],
			[
				"id" => "Task#",
				"status" => "Status",
				"date" => "Created At",
				"due_date" => "Due",
				"cat" => "Category",
				"overdue_icon" => "Overdue?",
				"task" => "Task",
				"assignee_fullname" => "Assignee",
				"dt_closed" => "Closed Date",
				"ifactor" => "iFactor",
			],
			[
				"passField" => "id",
				"title" => "Tasks",
				"url" => "/en/private/calendar/calendar.php?m[0]=tasks&m[1]=task&m[2]=view&id="
			]
		);
		$body .= $form->fetch();
		switch($m[3]) {
			case 'quickEdit':
				$DEFAULT_TITLE .= "\Quick Edit";
				if (!$user->isInGroup("gg_MIS")) {
					$DEFAULT_ERROR[] = "ERROR: You do not have permissions";
					break;
				}

				// ONLY OPEN TASKS
				$rows = $gwf->getOpenTasks();

				// display form
				if (!$_POST['form_submit']) {
					$body = include('gwf/gwf.tasks.quickedit.tpl.php');
					break;
				}

				$vars = tldUtils::cleanupFormInput($_POST);

				// THEME update ------------>

				foreach ($vars['task'] as $id => $values) {
					// check task
					$task = new tldTask((int)$id);
					if ($task->isEmpty()) continue;
					// Clean data
					$values = tldUtils::cleanupFormInput($values);
					// Fields allowed
					$fields = ['due_date', 'cat'];
					// Update fields
					foreach ($fields as $field) {
						if ($task->itsHeader[$field] == $values[$field]) continue;
						$e = $task->update([$field => $values[$field]]);
						if (is_string($e)) {
							$DEFAULT_ERROR[] = "INTERNAL ERROR: Could not update task#$id field '$field' to {$values[$field]}: $e";
						}
					}
				}
				$body = "<p>Updated process done!</p><a href='/en/private/calendar/calendar.php?m[0]=gwf&m[1]=view&m[2]=tasks&id={$task->itsHeader['parent_id']}'>Click here go back to task list</a>";
				break;
			case 'pauseAll':
				if(($m[4] ?? null) !== 'confirm') {
					$body .= "<strong style='color:red'>Click here to confirm that you want to PAUSE all tasks: </strong>"."<a href=\"$php_self?m[0]=gwf&m[1]=view&m[2]=tasks&m[3]=pauseAll&m[4]=confirm&id=$id\">Pause all</a>";
					break;
				}
				$gwf->pauseAllTasks();
				$body .= "<strong style='color:green'>All OPEN tasks link to this GWF has been PAUSED successfully</strong>";
				break;
		}

    break;
    case 'comments':
$query=<<<EOF
select
	l.type, l.item, tasks.*, c.comment
from gwf
	left join mod_links AS l on gwf.id=l.parent_id and l.module='GWF'
	left join tasks on tasks.module=l.type and tasks.parent_id=l.item
	left join tasks_comments AS c on tasks.id=c.parent_id
where
    gwf.id=$id
EOF;
		$report = new tldReportColumnar(
            tldUtils::getSqlToAssocArray($query),
			array(
                "xItems"=>array(
                    "type"=>"Module",
                    "item"=>"Ref#",
                    "id"=>"ID#",
                    "date"=>"Date",
                    "poster_fullname"=>"Poster",
                    "comment"=>"Comment"
                ),
                "title"=>"Tasks comments of module records linked to this GWF"
			)
		);
		$body .= $report->fetch();

    break;
	case 'log':
/*		if(strtoupper($vwc->getStatus()) <> "CLOSED"){
		$DEFAULT_MENU.=<<<EOF
	<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
	<a href="$php_self?m[0]=vwc&m[1]=view&m[2]=log&m[3]=addNote&id=$id">Add Note</a>
EOF;
		}
*/
		$DEFAULT_TITLE .="\Log";
		$report = new tldReportColumnar($gwf->getLogs(),
			array("xItems"=>array("id"=>"ID#",
								"date"=>"Date",
								"poster_fullname"=>"Poster",
								"comment"=>"Comment"
								)
			)
		);
		$body .= $report->fetch();
	break;
	case 'changeStatus':
		if($user->getID()<>$gwf->getAssignor()){
			$DEFAULT_ERROR[] = "ERROR: Only the moderator can change the status of a GWF";
			break;
		}
		if(strtoupper($gwf->getStatus()) == "CLOSED"){
			$DEFAULT_ERROR[] = "ERROR: GWF is already CLOSED";
			break;
		}
		$list = $gwf->changeStatus();
        $ot=tldTask::byOpenByConstraints("T1.module='GWF' AND T1.parent_id=".(int)$id);
        if(!isset($statusid) AND count($ot)>0){
            $DEFAULT_ERROR[] = "WARNING: Unable to close this GWF when there are still open tasks...";
        }

		$form = new HTML_QuickForm('frmSearch', 'post');
		$form->addElement(	'hidden', 'm[0]', 'gwf');
		$form->addElement(	'hidden', 'm[1]', 'view');
		$form->addElement(	'hidden', 'm[2]', 'changeStatus');
		$form->addElement(	'hidden', 'id', $id);
		$form->addElement(	'header', 'title', "Change Status");
		$form->addElement(	'select', 'statusid', 'New Status', $list);
		$form->addElement(	'textarea',	"comment", 'Comment (Optional)',
            array("wrap"=>"VIRTUAL", "cols"=>"40", "rows"=>"8")
        );
        if(!$gwf->isPrivate()){
		    $ams =& $form->addElement('advmultiselect', 'cc_users', null,
        		tldDirectory::getUserlist("smartyOptions"),
		        array(
        			'size'  => 10,
			        'class' => 'pool',
	    		    'style' => 'width:500px;'
		        )
		    );
		    $ams->setLabel(array('CC others... (OPTIONAL)', 'Addressbook', 'CC'));
		    $ams->setButtonAttributes('add', array('value'=>'-->>', 'class'=>'inputCommand'));
		    $ams->setButtonAttributes('remove', array('value'=>'<<--', 'class'=>'inputCommand'));
        }
        $form->addElement(	'submit', 'btnSubmit', 'Submit');
        foreach(tldModMember::byParent($id, 'GWF') AS $member){
        	$members[] = $member['id'];
        }
        $form->setDefaults(array("cc_users"=>$members));
		if(!$form->validate()){
		    $body = $form->toHTML();
		    break;
		}
		$vars = $form->exportValues();

        if($list[$vars['statusid']]=='CLOSED' AND count($ot)>0){
            $DEFAULT_ERROR[] = "ERROR: Unable to close when there are still open tasks...";
		    $body = $form->toHTML();
            break;
        }

		$vars = $form->exportValues();
		$e = $gwf->changeStatus($list[$vars['statusid']]);
		if(is_string($e)){
		    $DEFAULT_ERROR[]="ERROR: Status not changed.<br>Reason $e";
		    break;
		}


        $subject = "GWF #$id {$list[$vars['statusid']]} by ".$user->getFullname();
        $DEFAULT_ERROR[] =  $subject;
        $message =<<<EOF
$subject\n<br>
If you want to see the GWF, please log in to the TLD-GSE intranet and go to the GWF module to view.\n<br>
<a href="http://{$_SERVER['HTTP_HOST']}$php_self?m[0]=gwf&m[1]=view&id=$id">
Click here to go to GWF: {$gwf->getDsca()}
</a><br><br>
$comment
EOF;

		if( count($vars['cc_users'] ?? [])>0){
			foreach($vars['cc_users'] as $userid){
				$ccUser = new tldUser($userid);
				$ccList[] = $ccUser->getEmail();
			}
		}
		tldUtils::emailAttachment($user->getEmail(), "noreply@tld-gse.com", $subject, $message, "", $ccList);


		$error = $gwf->addLogEntry($user->getID(), $list[$vars['statusid']]."\n\n".$vars['comment']);
		$body .= _getGeneralTab($gwf);
	break;
	case 'duplicate':
		$form = new HTML_QuickForm('frmNew', 'post');
		$form->addElement('hidden', 'm[0]', 'gwf');
		$form->addElement('hidden', 'm[1]', 'view');
		$form->addElement('hidden', 'm[2]', 'duplicate');
		$form->addElement('hidden', 'id', $id);
		$form->addElement('header', 'title', "Duplicate GWF#$id ?");
		$form->addElement('select', 'confirm', 'Do you confirm?',
			['' => '', 'Y' => 'Yes, I confirm']);
		$form->addRule('confirm', 'Required', 'required');
		$form->addElement('submit', 'btnSubmit', 'Submit');

		if (!$form->validate()) {
			$body .= $form->toHTML();
			break;
		}

		$e = $gwf->duplicate($user->getID());
		if (is_string($e)) {
			$DEFAULT_ERROR[] = "ERROR: GWF not duplicated. Reason: $e";
			break;
		}
		$body .= <<<EOF
<p>GWF#$e created successfully from duplication of GWF#$id<br>
<a href="$php_self?m[0]=gwf&m[1]=view&id=$e">Click here to see GWF#$e</a></p>
EOF;
		break;
	case 'addComment':
		$form = new HTML_QuickForm('frmSearch', 'post');
		$form->addElement('hidden', 'm[0]', 'gwf');
		$form->addElement('hidden', 'm[1]', 'view');
		$form->addElement('hidden', 'm[2]', 'addComment');
		$form->addElement('hidden', 'id', $_GET['id']);
		$form->addElement('header', 'title', 'Add Comment');
		$form->addElement('textarea', 'comment', 'Comment',
			['wrap' => 'VIRTUAL', 'cols' => '40', 'rows' => '8']
		);
		$form->addElement('submit', 'btnSubmit', 'Submit');

		if (!$form->validate()) {
			$body = $form->toHTML();
			break;
		}

		$gwf->addLogEntry($user->getID(), addslashes($form->getElementValue('comment')));
		$body .= 'The comment has been successfully added.';
	break;
	default:
		$DEFAULT_TITLE .= "\General";
		$header=$gwf->getHeader();
        $filename=$header["picture_filename"];
		if($filename<>""){
			if(basicFile::getMediaType($filename)==="picture"){
				$body.= <<<EOF
				<a href="/en/private/uploads/gwf/$filename"><img src="/en/private/uploads/gwf/$filename" width="250" align="right"></a>
EOF;
			}
			if(basicFile::getMediaType($filename) !== "unknown" && basicFile::getMediaType($filename) !== "picture"){
				$body.= <<<EOF
				<a href="/en/private/uploads/gwf/$filename"><img src="/shared/icons/pdf-icon.gif" width="90" height="90" border="0" align="right"></a>
EOF;
			}
			$body.= <<<EOF
			<br/><a href="$php_self?m[0]=gwf&m[1]=view&m[2]=unlink&id=$id">Unlink Attachment</a>
EOF;
			switch($m[2]){
				case 'unlink':
					$body= <<<EOF
					Are you sure you want to unlink the file attachment to GWF#$id?
					<a href="$php_self?m[0]=gwf&m[1]=view&m[2]=unlink2&id=$id">YES</a> &nbsp;|&nbsp;
					<a href="$php_self?m[0]=gwf&m[1]=view&id=$id">NO</a>
EOF;
				break 2;
				case 'unlink2':
					$e = $gwf->disablePicture();
					if(is_string($e)){
		    			$DEFAULT_ERROR[]="ERROR: Picture not disabled.<br>Reason $e";
					    break;
					}
					$body= <<<EOF
					<meta http-equiv="refresh" content="0;url=$php_self?m[0]=gwf&m[1]=view&id=$id">
EOF;
				break 2;
			}
		}
		$body .= _getGeneralTab($gwf);
		if($gwf->getStatus() == "CLOSED") $body .= _getGeneralTabConclusion($gwf);
	}
break;
case 'reports':
    $DEFAULT_TITLE .= "\Reports";
    switch($m[2]){
    default:
        $body = <<<EOF
<h3>Columnar Reports</h3>
<ul>
  <li><a href="$php_self?m[0]=gwf&m[1]=listing&m[2]=byDelinquent">GWF Deliquent Audit</a></li>
</ul>
EOF;
    break;
    }
break;
case 'listing':
	switch($m[2]){
	case 'byKeyword':
		$DEFAULT_TITLE .= "\Keyword search";
		$form = new HTML_QuickForm('frm', 'get', "", "", "", true);
		$form->addElement(	'hidden', 'm[0]', 'gwf');
		$form->addElement(	'hidden', 'm[1]', 'listing');
		$form->addElement(	'hidden', 'm[2]', 'byKeyword');
		$form->addElement(	'header', 'title', "Search by keyword");
		$form->addElement(	'text', 'keyword', 'Search Keyword');
		$form->addElement(	'submit', 'btnSubmit', 'Submit');
		if($form->validate()){
			$form->freeze();
			$a = $form->exportValues();
			$rows = tldGWF::bySingleKeyword($a['keyword']);
			$_TITLE = "GWF by Keyword";
		}else{
			$body = $form->toHTML();
		}
	break;
	case 'byDescription':
		$DEFAULT_TITLE .= "\Description search";
		$form = new HTML_QuickForm('frm', 'get', "", "", "", true);
		$form->addElement(	'hidden', 'm[0]', 'gwf');
		$form->addElement(	'hidden', 'm[1]', 'listing');
		$form->addElement(	'hidden', 'm[2]', 'byDescription');
		$form->addElement(	'header', 'title', 'Search by description');
		$form->addElement(	'text',   'desc',  'Description');
		$form->addElement(	'submit', 'btnSubmit', 'Search');
		if($form->validate()){
			$form->freeze();
			$data = $form->exportValues();
			$rows = tldGWF::byDescription("%".trim($data['desc'])."%");
            $_TITLE = "GWF by Description '".$data['desc']."'";
		}else{
		    $body = $form->toHTML();
		}
	break;
	case 'byCategoryLocation':
        if($m[3] == 'includeClosed'){
            $_TITLE = "GWF by Category '$y' Location '$x' (INCLUDING CLOSED)";
            $rows = tldGWF::byCategoryLocation($y, $x, array("includeClosed"=>true));
        }else{
            $_TITLE = "GWF by Category '$y' Location '$x'";
            $rows = tldGWF::byCategoryLocation($y, $x);
        }
	break;
	case 'byDelinquent':
	    $_TITLE = "GWF Delinquent Audit - GWF Open with no task open";
        $rows = tldGWF::byDelinquent();
    break;
	}

	if(!isset($rows)){
	    break;
	}elseif(count($rows)<1){
        $DEFAULT_ERROR[]="No record found...";
        break;
	}

	$DEFAULT_MENU .= <<<EOF
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<a href="{$_SERVER["REQUEST_URI"]}&m[3]=xls">Download XLS</a>
EOF;

	switch($m[3]){
    case 'xls':
	    $report = new tldXLS(
            $sess["gwf"]["list"],
            array(
                "xItems"=>array(
    				"id"                =>"GWF#",
        			"status"	        =>"Status",
        			"date"	            =>"Date Opened",
        			"dest"              =>"Est Completion",
        			"dt_closed"         =>"Date closed",
        			"ifactor"           =>"IFactor",
        			"wks_open"          =>"Weeks Open",
        			"ctg"               =>"Category",
        			"assignor_fullname" =>"Moderator",
        			"dsca"              =>"Short Desc",
        			"kwd1"              =>"Key Word 1"
    	        ),
                "showTitles"=>true
            )
        );
		$report->out();
		exit;
    break;
    default:
        $sess["gwf"]["list"] = $rows;
        $body .= _getListing($rows,$_TITLE);
	break;
	}
break;
default:
    $body = $smarty->fetch("$PATH/gwf/homepage.gwf.tpl");
    $opts = [];
    $fClosed = "";
    if($m[2] == 'includeClosed'){
        $opts = array("includeClosed"=>true);
        $fClosed = "includeClosed";
        $TITLE = "GWF Count by Category, Location (INCLUDING CLOSED)";
    }else{
        $TITLE = "GWF Count by Category, Location";
    }
    $body .=<<<EOF
<a href="$php_self?m[0]=gwf">DO NOT include closed...</a>
&nbsp;|&nbsp;<a href="$php_self?m[0]=gwf&m[2]=includeClosed">Include closed...</a>
EOF;
    $form = new tldMatrix(
        tldGWF::countByCategoryLocation($opts),
        "location", "ctg", "num",
        "$php_self?m[0]=gwf&m[1]=listing&m[2]=byCategoryLocation&m[3]=$fClosed",
        $TITLE,
		[
			'stickyLeft' => true,
		]
    );
    $body .= $form->fetch();
	$body .= _getListing(tldGWF::byAssignor($user->getID()),"Your Group Tasks");
}

function _getGeneralTab($gwf){
    $id = $gwf->itsID;
	$form = new tldAssocTable(
        $gwf->getHeader(),
		array(
			"id"			    =>"GWF#",
			"pvt"		        =>"Confidential",
			"status"		    =>"Status",
	        "ifactor"           =>"IFactor",
			"wks_open"		    =>"Weeks Open",
			"assignor_fullname"	=>"Moderator",
			"payload_class"		=>"Form Type",
			"date"			    =>"Date",
		    "dt_closed"		    =>"Date closed",
			"dest"			    =>"Est Completion",
			"ctg"			    =>"Category",
			"bu_fullname"	    =>"Business Unit",
			"type"			    =>"Type",
			"model"			    =>"Model",
			"dsca"			    =>"Short Description",
			"dscb"			    =>"Description",
			"last_comment"      =>"Last Comment",
			"kwd1"			    =>"Key Word 1",
			"kwd2"			    =>"Key Word 2",
			"kwd3"			    =>"Key Word 3",
			"kwd4"			    =>"Key Word 4",
			"kwd5"			    =>"Key Word 5"
			),
			array("title"=>"General")
		);
	$cells[0] = $form->fetch();
	$report = new tldReportColumnar(
			$gwf->getMembers(),
			array(
					"xItems"=>array(
							"id"=>"UID#",
							"lastname"=>"Lastname",
							"firstname"=>"Firstname"
					),
					"title"=>"Members",
					"links"=>array(
							"id"=>"/en/private/directory/index.php?m[0]=people&m[1]=view&id="
					)
			)
	);
	$cells[1] .= $report->fetch();
	$form = new tldReportColumnar(
		tldTask::byParent($id, 'GWF', 'OPEN'),
		[
			'xItems' =>[
				'id' => 'Task#',
				'date' => 'Created at',
				'status' => 'Status',
				'due_date' => 'Due',
				'cat' => 'Category',
				'overdue_icon' => 'Overdue?',
				'task' => 'Task',
				'assignee_fullname' => 'Assignee',
				'dt_closed' => 'Closed Date',
			],
			'title' => 'Open Tasks',
			'links' => ['id'=>'/en/private/calendar/calendar.php?m[0]=tasks&m[1]=task&m[2]=view&id=']
			]

	);
	$cells[1] .= $form->fetch();
	$report = new tldReportColumnar(tldModLink::byParent($id, 'GWF'),
			array("xItems"=>array(
					"id"=>"ID#",
					"type"	=>"Module",
					"item"	=>"Ref#",
					"dsca"  =>"Description"
			),
					"title"=>"Links FROM Here...",
					"links"=>array("id"=>"/en/private/common/index.php?m[0]=links&m[1]=view&m[2]=redirect&erp=$erp&id=")
			)
	);
	$cells[1] .= $report->fetch();
	$report = new tldReportColumnar(tldModLink::byItem($id, 'GWF'),
			array("xItems"=>array(
					"id"=>"ID#",
					"module"	=>"Module",
					"parent_id"	=>"Ref#",
					"dsca"  =>"Description"
			),
					"title"=>"Links TO Here...",
					"links"=>array("id"=>"/en/private/common/index.php?m[0]=links&m[1]=view&m[2]=redirect&reversed=1&erp=$erp&id=")
			)
	);
	$cells[1] .= $report->fetch();
	$report = new tldHTMLTable(
			$cells,
			array(
					"cols"=>2,
					"attribs"=>array(
							"table"=>" width='100%'",
							"tr"=>" bgcolor='#FFFFFF'"
					)
			)
	);
	$body .= $report->fetch();
	$form = new tldReportColumnar(
			$gwf->getFiles(),
			array(
					"xItems"=>array(
							"id"=>"ID#",
							"date"=>"Date",
							"poster_fullname"=>"Poster",
							"description"=>"Description",
							"filename"=>"Filename"
					),
					"title"=>"File list",
					"links"=>array(
							"id"=>"/en/private/common/index.php?m[0]=files&m[1]=view&id="
					)
			)
	);
	$form1 = new tldReportColumnar($gwf->getFileByTasks("ALL"),
			array(
					"xItems"=>array(
							"id"=>"ID#",
							"date"=>"Date",
							"poster_fullname"=>"Poster",
							"filename"=>"Filename"
					),
					"title"=>"File list in Tasks",
					"links"=>array(
							"filename"=>"/en/private/uploads/tasks_comments/"
					)
			)
	);
	$body .= $form->fetch();
	$body .= $form1->fetch();
	return $body;
}

function _getListing($rows,$title){
    global $php_self;
    $report = new tldReportColumnar(
        $rows,
        array(
    		"xItems"=>array(
				"id"                =>"GWF#",
    			"status"	        =>"Status",
    			"date"	            =>"Date Opened",
    			"dest"              =>"Est Completion",
    			"dt_closed"         =>"Date closed",
    			"ifactor"           =>"IFactor",
    			"wks_open"          =>"Weeks Open",
    			"ctg"               =>"Category",
    			"assignor_fullname" =>"Moderator",
    			"dsca"              =>"Short Desc",
    			"kwd1"              =>"Key Word 1",
    			"nb_tasks"          =>"Nums Open tasks",
				"last_comment"      =>"Last comment",
	        ),
			"title"=>$title,
			"links"=>array("id"=>"$php_self?m[0]=gwf&m[1]=view&id=")
        )
    );
    return $report->fetch();
}

function _getGeneralTabConclusion($gwf){
	$form = new tldAssocTable(
	    $gwf->getConclusion(),
		array(
			"poster_fullname"	=>"Poster",
    		"comment"		    =>"Comment"
	    ),
        array("title"=>"Conclusion")
    );
	return $form->fetch();
}

?>
