<?php
include_once("mis.inc.php");

$showJiraIssue = $_GET['showJiraIssue'] ?? 'true';

$DEFAULT_TITLE = "MIS DASHBOARD";
$DEFAULT_MENU =<<<EOF
<a href="$php_self?m[0]=dashboard2">My Dashboard</a>
&nbsp;|&nbsp;<a href="$php_self?m[0]=tasks&m[1]=taskList">Task list (classic)</a>
&nbsp;|&nbsp;<a href="$php_self?m[0]=gantt">Task list (gantt)</a>
&nbsp;|&nbsp;MIS inventory <a href="/en/private/mis/mis.php?m[0]=inventory">[NEW]</a> <a href="/en/private/mis/mis.php?m[0]=inv">[OLD]</a>
EOF;

$smarty->assign("width",1300);

// user selection tool ----------------------->

// default
if(!empty($sess['dash_user_id'])){
    $dashUser = new tldUser((int)$sess['dash_user_id']);
}else{
    $dashUser = $user;
}
// form
$peopleList = tldUtils::optionsByKeyValue(
    tldUser::byConstraints(array(
    	'department'=>'Management of Information System',
        'hidden'=>'0',
        'disabled' =>'N'
    )),
    "id","fullname"
);
$userSelection = new HTML_QuickForm('frmPeople', 'post', "$php_self?m[0]=dashboard2");
$userSelection->addElement('header', 'title', "Select user dashboard");
$userSelection->addElement('select', 'uid', 'User', array(''=>'')+$peopleList, array('onChange'=>'this.form.submit();'));
$userSelection->setDefaults(['uid'=>$dashUser->getID()]);
$body.= $userSelection->toHTML();
if($userSelection->validate()){
    $dashUser = new tldUser((int)$_POST['uid']);
    $sess['dash_user_id'] = $_POST['uid'];
}


$DEFAULT_TITLE.=" - ".$dashUser->getFullname();

// -- Module
$cells = array();
$moduleLinkList = tldUtils::getModLinks();
$moduleList = array_keys($moduleLinkList);

$moduleList = array_combine($moduleList, array_map(static function($module) {
    switch ($module) {
        case 'SFR':
        case 'SOR':
        case 'MIM':
        case 'TTS':
        case 'TASK':
            return "$module (Legacy Id)";
        case 'SFR2':
        case 'SOR2':
        case 'MIM2':
        case 'TTS2':
        case 'TASK2':
            return substr($module, 0, -1);
    }
    return $module;
}, $moduleList));
$formModule = new HTML_QuickForm('frmModule', 'post');
$formModule->addElement(	'hidden', 'm[0]', $m[0]);
$formModule->addElement(	'hidden', 'm[1]', 'byModuleID');
$formModule->addElement(	'header', 'title', "See Module Record");
$formModule->addElement(	'select', 'module', 'Module', array(''=>'')+$moduleList);
$formModule->addElement(	'text', 'id', 'Number');
$formModule->addElement(	'submit', 'btnSubmit', 'Submit');
$formModule->setDefaults(array('module'=>"TASK"));
$cells[] = $formModule->toHTML();

// -- display
$report = new tldHTMLTable(
	$cells,
	array(
		"cols"=>5,
		"attribs"=>array(
			"table"=>" width='100%'",
			"tr"=>" bgcolor='#FFFFFF'",
	        "td"=>" width='25%'",
		)
	)
);
$body.= $report->fetch();

// Reporting and tools ----------------------->

switch($m[1]){
case 'qrcodegenerator':
    include "phpqrcode/qrlib.php";
    // List
    $correctionLevel = array('L'=>'Low','M'=>'Medium','Q'=>'Good','H'=>'Hight');
    $sizeList = array();
    for($i=1;$i<11;$i++) {
        $sizeList[$i] = $i;
    }
    // Form
    $form = new HTML_QuickForm('frmTask', 'post');
    $form->addElement(	'hidden', 'm[0]', $m[0]);
    $form->addElement(	'hidden', 'm[1]', 'qrcodegenerator');
    $form->addElement(	'header', 'title', "Generate QRcode");
    $form->addElement(	'text', 'data', 'Data');
    $form->addElement(	'select', 'size', 'Matrix size', $sizeList);
    $form->addElement(	'select', 'level', 'Correction level', $correctionLevel);
    $form->setDefaults(array('size'=>5,'level'=>'M'));
    $form->addElement(	'submit', 'btnSubmit', 'Submit');
    $body.= $form->toHTML();

    if(!$form->validate()) {
        break;
    }

    $filename = "/tmp/".md5($_REQUEST['data'].microtime()).'.qrcode.png';
    QRcode::png($_REQUEST['data'], $filename, $_REQUEST['level'], $_REQUEST['size'], 2);
    $file = new basicFile($filename);
    $body.= <<<EOF
<img src="data:image/png;base64,{$file->getBase64()}" />
EOF;
break;
case 'byModuleID':
    $id = trim($_REQUEST['id']);
    $url = $moduleLinkList[$_REQUEST['module']].$id;
    if(in_array($_REQUEST['module'], tldModLink::getMigratedModules(), true)) {
        $url .= '/show';
    }
    header("Location: $url");
    exit;
break;
case 'listing':
    $constraints = [];
    $constraints[] = " T1.assignee = {$dashUser->getID()} ";

    if ('true' !== $showJiraIssue) {
        $constraints[] = " T1.jira_issue IS NULL ";
    }

    switch($m[2]){
    case 'byCategoryModule':
        $status = TldDatabase::escape($x);
        if($status !== 'ALL') {
            $constraints[] = "T1.status='$status'";
        }
        $module = TldDatabase::escape($y);
        if($module !== 'ALL') {
            $constraints[] = "m.module='$module'";
        }

        $title_report = "Task list";

        // get data
        $rows = tldTask::byOpenByConstraints(implode(' AND ',$constraints),"T1.cat DESC");
        $shortDescriptionLength = 150;
        foreach ($rows as $key => $row) {
            $rows[$key]['shot_description'] = \mb_strimwidth($row['task'], 0, $shortDescriptionLength, "...");
        }

        // save in session
        $sess["calendar"]["tasks"] = $rows;
        // display
        $report = new tldReportColumnar(
            $rows,
            array(
                "xItems"=>array(
                    'id'=>'Task#',
                    'cat'=>'Cat',
                    'status'=>'Status',
                    'shot_description'=>'Description',
                ),
                "title"=>$title_report,
                "links"=>array(
                    "id"=>array(
                        "url"=>"$php_self?m[0]=tasks&m[1]=task&m[2]=view",
                        "params"=>array('id'=>'id'),
                        "target"=>"_blank",
                    ),
                    'module'=>array(
                        "url"=>"$php_self?m[0]={$m[0]}&m[1]=byModuleID",
                        "params"=>array('id'=>'parent_id','module'=>'module'),
                        "target"=>"_blank",
                    ),
                ),
                "functions"=>array(
                    "Add comment"=>array(
                        "url"=>"$php_self?m[0]=tasks&m[1]=task&m[2]=comment",
                        "param"=>array('id'=>'id'),
                        "target"=>"_blank",
                        "img"=>"/shared/icons/application/add.png"
                    ),
                    "Reschedule"=>array(
                        "url"=>"$php_self?m[0]=tasks&m[1]=task&m[2]=reschedule",
                        "param"=>array('id'=>'id'),
                        "target"=>"_blank",
                        "img"=>"/shared/icons/application/agenda.png"
                    ),
                    "Transfer"=>array(
                        "url"=>"$php_self?m[0]=tasks&m[1]=task&m[2]=transfer",
                        "param"=>array('id'=>'id'),
                        "target"=>"_blank",
                        "img"=>"/shared/icons/application/arrowright.png"
                    ),
                    "Category"=>array(
                        "url"=>"$php_self?m[0]=tasks&m[1]=task&m[2]=category",
                        "param"=>array('id'=>'id'),
                        "target"=>"_blank",
                        "img"=>"/shared/icons/application/flag.png"
                    )
                )
            )
        );
        $body.= $report->fetch();
        break 2;
    case 'byStatusModule':
        $status = TldDatabase::escape($x);
        if($status<>'ALL') {
            $constraints[] = "T1.status='$status'";
        }
        $module = TldDatabase::escape($y);
        if($module<>'ALL') {
            $constraints[] = "T1.module='$module'";
        }
    break;
    case 'byAssigneeByStatusModule':
        $status = TldDatabase::escape($x);
        if($status<>'ALL') {
            $constraints[] = "T1.status='$status'";
        }
        $module = TldDatabase::escape($y);
        if($module<>'ALL') {
            $constraints[] = "T1.module='$module'";
        }
    break;
    case 'byStatusCategory':
        $status = TldDatabase::escape($x);
        if($status<>'ALL') {
            $constraints[] = "T1.status='$status'";
        }
        $category = TldDatabase::escape($y);
        if($category<>'ALL') {
            $constraints[] = "T1.cat='$category'";
        }
        switch($m[3]){
        case 'vip':
            $constraints[] = "(b.fct_id IN(SELECT id FROM tld_functions WHERE level IN('ALVEST STEERING COMMITTEE','EXECUTIVES')) AND b.id<>49)";
        break;
        }
    break;
    case 'byBUCategory':
        $bu = TldDatabase::escape($y);
        if($bu<>'ALL') {
            $constraints[] = "b.bu_id=(SELECT id FROM locations WHERE location LIKE '$bu')";
        }
        $category = TldDatabase::escape($x);
        if($category<>'ALL') {
            $constraints[] = "T1.cat='$category'";
        }
    break;
    case 'OpenSequencebyTemplateDescriptionByStepByUserDashboardDivision':
        // redirect to sequence listing
        header("location: /en/private/calendar/calendar.php?m[0]=seq&m[1]=listing&m[2]=byOpenByHRTemplateByDivision&x={$dashUser->itsDetails['division']}&y=$x&step=$y");
    break;
    }

    $title_report = "Task list";

    $body .=<<<EOF
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<a href="$php_self?{$_SERVER['QUERY_STRING']}&assigneeonly=0">Show ALL</a>  |  
<a href="$php_self?{$_SERVER['QUERY_STRING']}&assigneeonly=1">Show Mine Only In Assignee</a>  |  
<a href="$php_self?{$_SERVER['QUERY_STRING']}&assigneeonly=2">Show Mine Only In Assignor</a> |
EOF;
    $body .= 'true' === $showJiraIssue
        ? " <a href=\"$php_self?{$_SERVER['QUERY_STRING']}&showJiraIssue=false\">Hide JIRA</a>"
        : " <a href=\"$php_self?{$_SERVER['QUERY_STRING']}&showJiraIssue=true\">Show JIRA</a>"
    ;

    // get data
    $rows = tldTask::byOpenByConstraints(implode(' AND ',$constraints),"T1.id DESC");
    // save in session
    $sess["calendar"]["tasks"] = $rows;
    // display
    $report = new tldReportColumnar(
		$rows,
		array(
			"xItems"=>array(
                'id'=>'Task#',
		        'module'=>'Module',
		        'cat'=>'Cat',
		        'status'=>'Status',
		        'assignor_fullname'=>'Assignor',
				'assignee_fullname'=>'Assignee',
				'task'=>'Description',
				'date'=>'Created at',
				'last_comment_date'=>'Commented at',
		        "lastcomment"=>"Last Comment"
            ),
			"title"=>$title_report,
			"links"=>array(
				"id"=>array(
            		"url"=>"$php_self?m[0]=tasks&m[1]=task&m[2]=view",
                    "params"=>array('id'=>'id'),
                    "target"=>"_blank",
                ),
                'module'=>array(
            		"url"=>"$php_self?m[0]={$m[0]}&m[1]=byModuleID",
                    "params"=>array('id'=>'parent_id','module'=>'module'),
                    "target"=>"_blank",
                ),
            ),
            "functions"=>array(
                "Add comment"=>array(
                    "url"=>"$php_self?m[0]=tasks&m[1]=task&m[2]=comment",
                    "param"=>array('id'=>'id'),
                    "target"=>"_blank",
                    "img"=>"/shared/icons/application/add.png"
                ),
                "Reschedule"=>array(
                    "url"=>"$php_self?m[0]=tasks&m[1]=task&m[2]=reschedule",
                    "param"=>array('id'=>'id'),
                    "target"=>"_blank",
                	"img"=>"/shared/icons/application/agenda.png"
                ),
                "Transfer"=>array(
                    "url"=>"$php_self?m[0]=tasks&m[1]=task&m[2]=transfer",
                    "param"=>array('id'=>'id'),
                    "target"=>"_blank",
                	"img"=>"/shared/icons/application/arrowright.png"
                ),
                "Category"=>array(
                    "url"=>"$php_self?m[0]=tasks&m[1]=task&m[2]=category",
                    "param"=>array('id'=>'id'),
                    "target"=>"_blank",
                	"img"=>"/shared/icons/application/flag.png"
                )
            )
		)
	);
	$body.= $report->fetch();
break;
default:
    $cells = array();
	// -- Matrix of SEQUENCE USER

	$query=<<<EOF
SELECT
    tpl.short_desc AS template,
    cur_step,
    (SELECT division FROM tld_regions
        WHERE tld_regions.id=people.div_id
    ) AS division,
    COUNT(*) AS num
FROM tasks
    LEFT JOIN cal_seq_tpl AS tpl ON tasks.tplno=tpl.id
    LEFT JOIN people ON people.id=tasks.parent_id
WHERE
    tasks.tplno IN(23,45,47,76,77)
    AND tasks.status<>'CLOSED'
    AND people.div_id={$dashUser->getRegionID()}
GROUP BY
    template,
    cur_step
EOF;
	$form = new tldMatrix(
	    tldUtils::getSqlToAssocArray($query),
	    "template", "cur_step", "num",
	    "$php_self?m[0]={$m[0]}&m[1]=listing&m[2]=OpenSequencebyTemplateDescriptionByStepByUserDashboardDivision",
	    "OPEN HR Sequence for {$dashUser->itsDetails['division']}"
    );
	$cells[0] = $form->fetch();

	// display
	$report = new tldHTMLTable(
    	$cells,
    	array(
        	"cols"=>4,
        	"attribs"=>array(
    			"table"=>" width='100%'",
    			"tr"=>" bgcolor='#FFFFFF'",
    	        "td"=>" width='25%'",
    		)
		)
    );
    $body.= $report->fetch();

	// Project list ------->

	$query = <<<EOF
SELECT *,
(SELECT CONCAT(firstname,' ',lastname) FROM people WHERE id=mis_tts.owner) AS owner_fullname,
(SELECT CONCAT(firstname,' ',lastname) FROM people WHERE id=mis_tts.assignee) AS assignee_fullname
FROM mis_tts
WHERE (owner={$dashUser->getID()} OR assignee={$dashUser->getID()}) AND status NOT IN('CLOSED','QUEUE')
ORDER BY ifactor DESC
EOF;
	$report = new tldReportColumnar(
		tldUtils::getSqlToAssocArray($query),
		array(
			"xItems"=>array(
                'id'=>'Project#',
				'ifactor'=>'IF',
		        'status'=>'Status',
		        'owner_fullname'=>'Owner',
		        'assignee_fullname'=>'Assignee',
	            'problem'=>'Description'
            ),
			"title"=>"My projects as Owner or Assignee",
			"links"=>array(
				"id"=>"/en/private/mis/mis.php?m[0]=tts&m[1]=view&id=",
            ),
            "functions"=>array(
                "Tasks"=>array(
                    "url"=>"/en/private/mis/mis.php?m[0]=tts&m[1]=view&m[2]=tasks&id=",
                    "param"=>array('id'=>'id'),
                    "target"=>"_blank",
                ),
                "Timesheets"=>array(
                    "url"=>"/en/private/mis/mis.php?m[0]=tts&m[1]=view&m[2]=timesheets&id=",
                    "param"=>array('id'=>'id'),
                    "target"=>"_blank",
                )
            )
		)
	);
	$body.= $report->fetch();

	// GWF ---->

	$gwfList = tldGWF::byConstraints(
       "status<>'CLOSED' AND (gwf.assignor={$dashUser->getID()} OR ".
       "gwf.id IN(SELECT parent_id FROM mod_lists WHERE module LIKE 'GWF' AND list_name='MEMBERS' AND value={$dashUser->getID()}))"
   );

	$report = new tldReportColumnar(
		$gwfList,
		array(
			"xItems"=>array(
                'id'                =>'GWF#',
				"status"	        =>"Status",
    			"date"	            =>"Date Opened",
    			"ifactor"           =>"IFactor",
    			"wks_open"          =>"Weeks Open",
    			"ctg"               =>"Category",
    			"dsca"              =>"Short Desc",
    			"kwd1"              =>"Key Word 1"
            ),
			"title"=>"My GWF as Owner or Member",
			"links"=>array(
				"id"=>"$php_self?m[0]=gwf&m[1]=view&id=",
            ),
            "functions"=>array(
                "Tasks"=>array(
                    "url"=>"$php_self?m[0]=gwf&m[1]=view&m[2]=tasks",
                    "param"=>array('id'=>'id'),
                    "target"=>"_blank",
                )
            )
		)
	);
	$body.= $report->fetch();
break;
}
