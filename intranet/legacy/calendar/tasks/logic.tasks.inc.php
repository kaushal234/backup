<?php
include_once("quality.inc.php");
include_once("mis.inc.php");
include_once("user.inc.php");
include_once("product_support.inc.php");
include_once("sales_service.inc.php");

$JS_INCLUDE=array("/shared/javascript/overlib/overlib.js");
$smarty->assign("js_includes",$JS_INCLUDE);

$DEFAULT_TITLE .= "\Tasks";
$DEFAULT_MENU .=<<<EOF
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<a href="$php_self?m[0]=tasks&m[1]=taskList" title="Display list of open tasks">Task Lists</a>
&nbsp;|&nbsp;<a href="$php_self?m[0]=tasks&m[1]=form&m[2]=newTask" title="Create a brand new task for a team member, peer or supervisor">New USER Task</a>
&nbsp;|&nbsp;<a href="$php_self?m[0]=tasks&m[1]=form&m[2]=byNumber" title="I have the TASK#">By Num</a>
&nbsp;|&nbsp;<a href="$php_self?m[0]=tasks&m[1]=listing&m[2]=byRefNum" title="I have the reference number related to the task">By Ref Num</a>
&nbsp;|&nbsp;<a href="$php_self?m[0]=tasks&m[1]=listing&m[2]=byUserSearch" title="Search my related tasks for something...">Search</a>
&nbsp;|&nbsp;<a href="$php_self?m[0]=tasks&m[1]=reports" title="List available reports">Reports</a>
&nbsp;|&nbsp;<a href="$php_self?m[0]=tasks&m[1]=form&m[2]=translationRequest">Translation request</a>
EOF;

if($user->isInGroup("gg_MIS")) {
    $DEFAULT_MENU .=<<<EOF
&nbsp;|&nbsp;<a href="$php_self?m[0]=tasks&m[1]=form&m[2]=advSearch">Advanced Search</a>
EOF;
}
if($user->isInGroup("tasks")) {
    $DEFAULT_MENU .=<<<EOF
&nbsp;|&nbsp;<a href="tasks/tasks_admin.php">Tasks Admin</a>
EOF;
}

// List of TPL ID and ACL associated for search SEQ
$ACL_SEQ_TPL = array(
    // investmentbudget.request
    41 => array("gg_ACCT")+tldGroup::getManagerGroups(),
    // investmentbudget.request.factory
    88 => array("gg_ACCT")+tldGroup::getManagerGroups(),
    // mis.investmentbudget.request
    46 => array("gg_ACCT")+tldGroup::getManagerGroups(),
    // mis.investmentbudget.request.leb
    82 => array("gg_ACCT")+tldGroup::getManagerGroups(),
    // acct.coo.ap.NonPOInvoice.approval
    24 => array("role_FC")+tldGroup::getManagerGroups(),
    // acct.coo.ceo.ap.NonPOInvoice.approval
    25 => array("role_FC")+tldGroup::getManagerGroups(),
    // acct.coo.rcoo.ceo.ap.NonPOInvoice.approval
    83 => array("role_FC")+tldGroup::getManagerGroups(),
    // acct.coo.rcoo.ap.NonPOInvoice.approval
    86 => array("role_FC")+tldGroup::getManagerGroups(),
    // seq.nonpo.sso.1
    100 => array("role_FC")+tldGroup::getManagerGroups(),
    // seq.nonpo.sso.2
    98 => array("role_FC")+tldGroup::getManagerGroups(),
    // seq.nonpo.factory.1
    96 => array("role_FC")+tldGroup::getManagerGroups(),
    // seq.nonpo.factory.2
    97 => array("role_FC")+tldGroup::getManagerGroups(),
    // seq.nonpo.factory.3
    99 => array("role_FC")+tldGroup::getManagerGroups(),
    // seq.nonpo.factory.4
    101 => array("role_FC")+tldGroup::getManagerGroups(),
    // SEQ_1Y_ETHIC_ATTESTATION
    126 => array("gg_HR")+tldGroup::getManagerGroups(),
    // SEQ_3Y_ETHIC_ATTESTATION
    130 => array("gg_HR")+tldGroup::getManagerGroups(),
);
// Also allow people that have some roles in the SEQ template as they can already search for them using standard SEQ search
foreach($ACL_SEQ_TPL as $tplno => &$allowedGroups){
    $tpl = new tldSEQTpl((int) $tplno);
    $groups = array_column($tpl->getNodes(), 'group_name');

    if($user->isInGroup($groups)){
        $allowedGroups = array_unique(array_merge($allowedGroups, $groups));
    }
}

switch($m[1]) {
case "form":
    include("form.tasks.inc.php");
break;
case "task":
    include("view.tasks.inc.php");
break;
case "taskList":
    $DEFAULT_TITLE .= "\Tasks Lists";

    $user_id = $user->getID();

    $DEFAULT_MENU .=<<<EOF
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<a href="$php_self?m[0]=tasks&m[1]=taskList" title="All My Tasks">My Tasks</a>
&nbsp;|&nbsp;<a href="$php_self?m[0]=tasks&m[1]=listing&m[2]=byRecentlyClosed" title="Recently Closed">Recently Closed</a>
&nbsp;|&nbsp;<a href="$php_self?m[0]=tasks&m[1]=taskList&m[2]=assigned" title="Assigned Tasks">Assigned</a>
&nbsp;|&nbsp;<a href="$php_self?m[0]=tasks&m[1]=taskList&m[2]=initiated" title="Initiated Tasks">Initiated</a>
&nbsp;|&nbsp;<a href="$php_self?m[0]=tasks&m[1]=taskList&m[2]=subordinate" title="Team member Tasks">My team members</a>
&nbsp;|&nbsp;<a href="$php_self?m[0]=seq&m[1]=listing&m[2]=quickedit" title="Quick SEQ Approval">Quick SEQ Approval</a>
EOF;

    $linked_users = $user->getLinkedUsersID();
    if(count($linked_users)){
        $DEFAULT_MENU .=<<<EOF
&nbsp;|&nbsp;<a href="$php_self?m[0]=tasks&m[1]=taskList&m[2]=byLinkedUser">User linked</a>
EOF;
    }

	$params = http_build_query($_GET + array('format'=>'csv'));
    $DEFAULT_MENU .= <<<EOF
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<a href="$php_self?$params">CSV</a>
EOF;

    $xItems = array(
        "id"=>"Task#",
        "module"=>"Module",
        "parent_id"=>"Ref#",
//     	"cat"=>"Category",
        "status"=>"Status",
        "overdue_icon"=>"Overdue (in days)",
        "escalation_trigger"=>"Escalation trigger",
        "hours"=>"Est hour",
        "due_date"=>"Due",
        "assignor_fullname"=>"Assignor",
    	"assignor_BU"=>"Assignor BU",
        "assignee_fullname"=>"Assignee",
        "task"=>"Task",
    	"lastcomment"=>"Last Comment"
    );
    if($user->isInGroup(array("gg_MIS"))){
        $xItems['cat'] = "TTS?";
    }

    switch($m[2]){
	case 'byDueByMod':
        if($_GET['z']){
            $user_id = TldDatabase::escape($_GET['z']);
        }
        if(empty($userid) || !is_numeric($userid)){
            $userid = $user->getID();
        }
        $u = new tldUser($userid);
        if(!$u->isValid()){
            $DEFAULT_ERROR[] = "ERROR: TLD user#$userid not found...";
            break 2;
        }
	    $_title = "Open tasks for {$u->getFullname()}";
	    $constraints = " T1.assignee={$userid} ";
	    switch($x){
        case 'Late':
            $constraints .= " AND DATEDIFF(due_date, NOW()) < 0 ";
            $_title .= " - Late ";
            break;
        case 'Due':
            $constraints .= " AND DATEDIFF(due_date, NOW()) >= 0 ";
            $_title .= " - Due ";
            break;
    	case 'Late >60':
    		$constraints .= " AND DATEDIFF(due_date, NOW()) < -60 ";
	    	$_title .= " - Late &gt; 60 days ";
    	break;
    	case 'Late <60':
    		$constraints .= " AND DATEDIFF(due_date, NOW()) BETWEEN -60 AND -14 ";
	    	$_title .= " - Late between 15 and 60 days ";
    	break;
    	case 'Late <14':
    		$constraints .= " AND DATEDIFF(due_date, NOW()) BETWEEN -14 AND -8 ";
	    	$_title .= " - Late between 8 and 14 days ";
    	break;
    	case 'Late <7':
    		$constraints .= " AND DATEDIFF(due_date, NOW()) BETWEEN -7 AND 0 ";
	    	$_title .= " - Late between 0 and 7 days ";
    	break;
    	case 'Due <7':
    		$constraints .= " AND DATEDIFF(due_date, NOW()) BETWEEN 1 AND 7 ";
	    	$_title .= " - Due between 1 and 7 days ";
    	break;
    	case 'Due <14':
    		$constraints .= " AND DATEDIFF(due_date, NOW()) BETWEEN 8 AND 14 ";
	    	$_title .= " - Due between 8 and 14 days ";
    	break;
    	case 'Due >14':
    		$constraints .= " AND DATEDIFF(due_date, NOW()) > 14 ";
	    	$_title .= " - Due &gt; 14 days ";
    	break;
	    }
	    if($y<>'ALL'){
	    	$constraints .= " AND T1.module='".TldDatabase::escape($y)."' ";
	    	$_title .= " - Mod: {$y} ";
	    }
		$rows = tldTask::byOpenByConstraints($constraints);
	break;
    case 'byUserByCategory': // MIS only for now
        $email = TldDatabase::escape($y);
        $icat = TldDatabase::escape($x);
        $_title = "Open tasks by user $y, category $x";
        // Generates constraints
        $a[] = <<<EOF
tasks.status<>'CLOSED'
AND tasks.assignee IN(SELECT id FROM people WHERE dpt_id=7)
AND tasks.module LIKE 'TTS'
EOF;
        if($email<>'ALL'){
            $a[]="assignee_fullname='$email'";
        }
        if($icat<>'ALL'){
            $a[]="iStatusCategoryTime='$icat'";
        }
        // additional filters
        switch($m[3]){
        case 'byProjectOnly':
            $a[]="tasks.parent_id IN(SELECT id FROM mis_tts WHERE status<>'QUEUE')";
        break;
        }
        // Get data
        $rows = tldTTS::tasksByConstraints(implode(' AND ',$a));
        $xItems['queue_category'] = "Queue";
        $xItems['domain'] = "Domain";
        $xItems['date'] = "Date opened";
        $xItems['nb_days'] = "Days opened";
    break;
    case 'byUserByIF': // MIS only for now
        $email = TldDatabase::escape($y);
        $icat = TldDatabase::escape($x);
        $_title = "Open tasks by user $y, IF $x";
        // Generates constraints
        $a[] = <<<EOF
tasks.status<>'CLOSED'
AND tasks.assignee IN(SELECT id FROM people WHERE dpt_id=7)
AND tasks.module LIKE 'TTS' AND tasks.ifactor>1
EOF;
        if($email<>'ALL'){
            $a[]="assignee_fullname='$email'";
        }
        if($icat<>'ALL'){
            $a[]="ifactor='$icat'";
        }
        if(!empty($m[3])){
            $a[]="assignee=$m[3]";
        }
        // Get data
        $rows = tldTTS::tasksByIF(implode(' AND ',$a));
        $xItems['queue_category'] = "Queue";
        break;
    case 'byQueueByCategory':
        $queue = TldDatabase::escape($y);
        $icat = TldDatabase::escape($x);
        $_title = "Open tasks by queue $y, category $x";
        // Generates constraints
        $a[] = "status<>'CLOSED'";
        if($queue<>'ALL'){
            $a[]="queue_category='$queue'";
        }
        if($icat<>'ALL'){
            $a[]="iStatusCategoryTime='$icat'";
        }
        $rows = tldTTS::tasksByConstraints(implode(' AND ',$a));
        $xItems['queue_category'] = "Queue";
    break;
    case 'byLinkedUser':
        if(!count($linked_users)){
            $DEFAULT_ERROR[] = "ERROR: No user linked to your account...";
            break;
        }
        $uids = implode("','",$linked_users);
        if (is_numeric($userid)){$uids = $userid;}
        $a = " (T1.assignee IN ('$uids') OR T1.assignor IN ('$uids')) ";
        $rows = tldTask::byOpenByConstraints($a);
        $_title = "Open tasks by linked user";
    break;
    case 'bySubordinate':
    	if((string)$userid === "all"){
    		$u = new tldUser($user_id);
    		$Subordinates=$u->getSubordinates('smartyOptions');
    		foreach ($Subordinates as $key=>$Subordinate)
    		{
      			$SubordinateArray[]=$key;
    			$rows[]= array();
    			$rows = array_merge_recursive(tldTask::byUser($key),$rows);
    		}
    		break;
    	}
        if(empty($userid) || !is_numeric($userid)){
            $DEFAULT_ERROR[] = "ERROR: ID empty or invalid...";
            break 2;
        }
        $u = new tldUser($userid);
        if(!$u->isValid()){
            $DEFAULT_ERROR[] = "ERROR: TLD user#$userid not found...";
            break 2;
        }
        $_title = "All Tasks, ".$u->getFullname();
        $constraints = "(T1.assignee=$userid OR T1.assignor=$userid)";
        switch($m[3]){
        case 'project':
            $rows = tldTask::getProjectOpenTasksByConstraints($constraints);
            $_title.= " - Project";
        break;
        case 'noneProject':
            $rows = tldTask::getNoneProjectOpenTasksByConstraints($constraints);
            $_title.= " - None Project";
        break;
        default:
    		$DEFAULT_MENU .=<<<EOF
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<a href="$php_self?m[0]=tasks&m[1]=taskList&m[2]=subordinate&userid=$userid" title="Subordinate Tasks">Get team members List for {$u->getFullname()}</a>
EOF;
        	$rows = tldTask::byUser($userid);
        break;
        }
    break;
    case 'initiated':
        $rows = tldTask::byAssignor($user_id);
        $_fields = array("module");
        $_title = "Initiated Tasks";
    break;
    case 'assigned':
        $rows = tldTask::byAssignee($user_id);
        $_fields = array("module");
        $_title = "Assigned Tasks";
    break;
    case 'subordinate':
    	if(!empty($userid) AND is_numeric($userid)){
    		$u = new tldUser($userid);
	        if(!$u->isValid()){
	            $DEFAULT_ERROR[] = "ERROR: TLD user#$userid not found...";
	            break 2;
	        }
    		$subs = $u->getSubordinates();
    		$result = array();
    	}else{
    		$subs = $user->getSubordinates();
    		$result = array(array("id"=>"all","name"=>"ALL"));
    		$u =& $user;
    	}

        $temp = array();
        foreach($subs as $value){
        	$temp[] = array("id"=>$value['id'],"name"=>"{$value['lastname']}, {$value['firstname']}");
        }
		$res = array_merge($result,$temp);
        if(!count($subs)){
            $DEFAULT_ERROR[] =  "No team members found.";
            unset($result);
        }
        $html = "<h3>Team members List for {$u->getFullname()}</h3><ul>%s</ul>";
        $li = array();
        foreach($res AS $value){
        	$li[] = "<li><a href=\"$php_self?m[0]=tasks&m[1]=taskList&m[2]=bySubordinate&userid={$value['id']}\">{$value['name']}</a></li>";
        }
        $body .= sprintf($html, implode('',$li));
        // Look for linked users
        if(count($result ?? [])){
	        $linked_users = array_merge($result,$user->getLinkedUsers());
	        if(count($linked_users)>1){
	            $form = new tldHTMLList(
	                $linked_users,
	                array(
	                    "key"=>array("userid"=>"id"),
	                    "value"=>array("lastname", "firstname")
	                ),
	                "$php_self?m[0]=tasks&m[1]=taskList&m[2]=byLinkedUser",
	                array("title"=>"Linked user list")
	            );
	            $body .= $form->fetch();
	        }
        }
    break;
    case 'byUser':
        if(!$user->isInGroup("gg_MIS")){
            $DEFAULT_ERROR[] =  "You are not allowed to view task for this user";
            break 2;
        }
        $userid = TldDatabase::escape($userid);
        $rows = tldTask::byUser($userid);
        $_title = "Tasks of User#$userid";
        $xItems['queue_category'] = "Queue";
    break;
    case 'myModules':
        $rows = tldTask::byMOO($user->getID(), isset($_GET['includeClosed']));
        $_fields = array("module");
        $_title = "My modules tasks";
        break;
    case 'byIntranetModule':
        $_fields = array("module");
        $_title = "My modules tasks";
        $constraint = 'ticket_module_id IS NOT NULL';
        if (($_GET['module'] ?? null) !== 'ALL') {
            $modules = tldModule::byConstraints(['module' => $_GET['module']]);
            if (null === ($moduleId = $modules[0]['id'] ?? null)) {
                $rows = [];
                break;
            }
            $constraint.= " AND ticket_module_id = $moduleId";
        }

        $developers = $_GET['developers'] ?? array_keys((new tldGroup('ROLE_DEV'))->getUserlist('smartyOptions'));
        $constraint.= sprintf(' AND m.uid IN (%s)', implode(', ', $developers));

        $moos = $_GET['moos'] ?? array_keys(tldModule::getMOOList());
        $constraint.= sprintf(' AND m.oid IN (%s)', implode(', ', $moos));

        $parameter = $_GET['parameter'] ?? null;
        switch ($parameter) {
            case 'OPEN':
            case 'IN PROGRESS':
            case 'PAUSE':
                $constraint.= " AND status = '$parameter'";
                break;
            case tldTTS::ASSIGNED_TO_MOO:
                $constraint.= " AND assignee = m.oid";
                break;
            case tldTTS::ASSIGNED_TO_ASSIGNOR:
                $constraint.= " AND assignee = assignor";
                break;
            case tldTTS::ASSIGNED_TO_DEV:
            case 'C ' . tldTTS::ASSIGNED_TO_DEV:
                $additionalConstraint = '';
                if ($parameter === 'C ' . tldTTS::ASSIGNED_TO_DEV) {
                    $additionalConstraint = " AND T1.cat='C' ";
                }
                $constraint.= " AND (SELECT IF(COUNT(*) = 0, NULL, 1) FROM people_groups pg INNER JOIN people p ON p.email = pg.email WHERE p.id = T1.assignee AND pg.group_name='ROLE_DEV' $additionalConstraint GROUP BY p.id) IS NOT NULL";
                break;
            case 'IF 1':
            case 'IF 10':
            case 'IF 100':
            case 'IF 1000':
                [, $if] = explode(' ', $parameter);
                $constraint.= " AND ifactor = $if";
                break;
            case 'A':
            case 'B':
            case 'C':
                $constraint.= " AND cat = '$parameter'";
                break;
        }
        if (!isset($_GET['includeClosed'])) {
            $constraint.= " AND status != 'CLOSED'";
        }
        $rows = tldTask::byConstraints($constraint);
        break;
    default:
        if($user->isInGroup("gg_MIS")){
            $DEFAULT_MENU .= <<<EOF
&nbsp;|&nbsp;<a href="$php_self?m[0]=tasks&m[1]=taskList&m[2]=byUser&m[3]=noneProject&userid=$user_id">None Project Tasks</a>
&nbsp;|&nbsp;<a href="$php_self?m[0]=tasks&m[1]=taskList&m[2]=byUser&m[3]=project&userid=$user_id">Project Tasks</a>
EOF;
            $xItems['queue_category'] = "Queue";
        }
        $rows = tldTask::byUser($user_id);
        $_fields = array("module","owner_fullname");
        $_title = "All My Tasks";
    }

    if(count($rows ?? [])) {

        $sess["calendar"]["tasks"] = $rows;

        switch($format) {
        case 'csv':
        	foreach($rows AS &$row){
        		$row['task'] = strip_tags($row['task']);
        		$row['lastcomment'] = strip_tags((string) $row['lastcomment']);
        	}
            $CSVItems= [
                "id"=>"Task#",
                "module"=>"Module",
                "parent_id"=>"Ref#",
                "status"=>"Status",
                "due_date"=>"Due",
                "hours"=>"Est time",
                "assignor_fullname"=>"Assignor",
                "assignee_fullname"=>"Assignee",
                "task"=>"Task",
                'lastcomment' => 'last comment',
            ];
            if($user->isInGroup("gg_MIS")) {
                $CSVItems = $xItems;
                unset($CSVItems['overdue_icon']);
            }
		    $report = new tldCSV(
		        $rows,
		        array(
		            "xItems"=>$CSVItems,
		            "showTitles"=>true
		        )
		    );
		    $report->out();
		    exit;
        break;
        default:
            $form = new tldReportColumnar(
                $rows,
                array(
                    "xItems"=>$xItems,
                    "title"=>$_title,
                    "links"=>array("id"=>"$php_self?m[0]=tasks&m[1]=task&m[2]=view&id=")
                )
            );
            $body .= $form->fetch();
        }
    }
break;
case "byPeriod":
    $DEFAULT_MENU .= <<<EOF
<br>
<a href="$php_self">Week</a>&nbsp;|&nbsp;
<a href="$php_self">Month</a>&nbsp;|&nbsp;<a href="$php_self">Year</a>
EOF;
    switch($m[2]) {
    default:
        $start = date("Y-m-d");
        $end = date("Y-m-d");
        $sess["calendar"]["tasks"] = tldTask::byAssignee($user->getId(), $start, $end);
    }
break;
case 'reports':
    $DEFAULT_TITLE .= '\Reports';
    switch($m[2]) {
    case 'userStats':
        $_user = new tldUser($id);
        if($_user->isValid()) {
            $sess["calendar"]["tasks"] = tldTask::byAssignee($_user->getID(), 'module', $module);
            $form = new tldReportMultiLevel($sess["calendar"]["tasks"],
                array("module","owner_fullname"),
                array(
                    "id"				=>"Task#",
                    "parent_id"			=>"Ref#",
                    "status"			=>"Status",
                    "due_date"			=>"Due",
                    "overdue_icon"		=>"Overdue?",
                    "assignor_fullname"=>"Assignor",
                    "task"				=>"Task"
                ),
                array("passField"=>"id",
                "title"=>"Assigned Tasks to ".$_user->getFullname(),
                "url"=>"$php_self?m[0]=tasks&m[1]=task&m[2]=view&id=")
            );
            $body = $form->fetch();
        }
    break;
    case 'userStatsByModule':
        if(isset($module)) {
            $form = new tldReportColumnar(	tldTask::getAssignees(array("module"=>$module)),
                array("xItems"=>array(	"fullname"=>"Full Name",
                "taskCount"=>"Num Tasks",
                "overdueCount"=>"Num Over Due",
                "email"=>"Show Tasks for..."),
                "title"=>"$module Module, User Tasks Statistics",
                "links"=>array("email"=>$_SERVER['PHP_SELF']."?m[0]=tasks&m[1]=reports&m[2]=userStats&module=$module&id=")
                )
            );
            $body = $form->fetch();
        }else {
            $modlist = tldTask::getModuleList();
            foreach($modlist as $mod) {
                $result[$mod] = $mod;
            }
            $form = new tldHTMLList($result,
                '',
                $_SERVER['REQUEST_URI'].'&module=',
                array("title"=>"Please select module...")
            );
        }
        $body = $form->fetch();
    break;
    case 'statsByModule':
        $form = new tldReportColumnar(
            tldTask::getStatsByModule(),
            array("xItems"=>array(	"module"=>"Module",
            "totalNum"=>"Total Num Tasks Opened",
            "numOpen"=>"Num Currently Open",
            "avgDays"=>"Average Num Days Open"),
            "title"=>"Module Task Statistics"
            )
        );
        $body = $form->fetch();
    break;
    default:
        $body .= $smarty->fetch("$PATH/tasks/reports/homepage.reports.inc.tpl");
    break;
    }
break;
case 'listing':
	$xItems = array(
		"id"				=>"Task/SEQ#",
		"module"            =>"Module",
        "parent_id"         =>"Ref#",
		"status"			=>"Status",
		"date"				=>"Date Opened",
		"dt_closed"			=>"Date Closed",
		"due_date"			=>"Due Date",
		"assignor_fullname"	=>"Assignor",
        "assignor_email"    =>"Assignor Email",
        "assignee_fullname"	=>"Assignee",
		"task"          	=>"Task",
	);
    switch($m[2]){
    case 'byWeeksLate':
        $rows = tldTask::byWeeksOverdue($assignee, $wk);
        $u = new tldUser($assignee);
        $_TITLE = "Tasks for ".$u->getFullname()." $wk Weeks Overdue";
    break;
    case 'byUserByClosedPeriod':
        // Listing
        $userList = array();
        if($user->isInGroup(array('superuser'))){
            $userList = tldDirectory::getUserlist("smartyOptions");
        }else{
            // subordinates
            $userList = $user->getSubordinates("smartyOptions");
            // + himself
            $userList[$user->getID()] = $user->getFullname();
        }
        // Form
        $form = new HTML_QuickForm('frm', 'GET');
        $form->addElement(	'hidden', 'm[0]', 'tasks');
        $form->addElement(	'hidden', 'm[1]', 'listing');
        $form->addElement(	'hidden', 'm[2]', 'byUserByClosedPeriod');
        $form->addElement(	'header', 'title', "Task closed by period");
        $form->addElement(	'select', 'uid', "User", $userList);
        $form->addElement(	'text', 'dt_from', "From");
        $form->addElement(	'text', 'dt_to', "To");
        $form->addElement(	'submit', 'btnSubmit', 'Submit');
        $form->addRule('uid','Required','required');
        $form->addRule('dt_from','Required','required');
        $form->addRule('dt_to','Required','required');
        $defaultUID = empty($_REQUEST['uid']) ? $user->getID() : $_REQUEST['uid'];
        $form->setDefaults(array(
        	'uid'=>$defaultUID,
        	'dt_from'=>date('Y-m').'-01',
            'dt_to'=>date('Y-m-d')
        ));

        if(!$form->validate()){
            $body = $form->toHTML();
            break 2;
        }

        $vars = tldUtils::cleanupFormInput($form->exportValues());
        $a = <<<EOF
T1.status LIKE 'CLOSED'
AND (T1.assignee={$vars['uid']} OR T1.assignor={$vars['uid']})
AND DATEDIFF('{$vars['dt_from']}',T1.dt_closed)<=0
AND DATEDIFF('{$vars['dt_to']}',T1.dt_closed)>=0
EOF;
        $rows = tldTask::byConstraints($a);
        $_TITLE = "Task closed period result";
    break;
    case 'myTeamByClosedPeriod':
        // Form
        $form = new HTML_QuickForm('frm', 'GET');
        $form->addElement(	'hidden', 'm[0]', 'tasks');
        $form->addElement(	'hidden', 'm[1]', 'listing');
        $form->addElement(	'hidden', 'm[2]', 'myTeamByClosedPeriod');
        $form->addElement(	'header', 'title', "Task closed by period");
        $form->addElement(	'text', 'dt_from', "From");
        $form->addElement(	'text', 'dt_to', "To");
        $form->addElement(	'submit', 'btnSubmit', 'Submit');
        $form->addRule('dt_from','Required','required');
        $form->addRule('dt_to','Required','required');
        $form->setDefaults(array(
            'dt_from'=>date('Y-m').'-01',
            'dt_to'=>date('Y-m-d')
        ));

        if(!$form->validate()){
            $body = $form->toHTML();
            break 2;
        }

        $vars = tldUtils::cleanupFormInput($form->exportValues());
        $orWhere = '';
        foreach ($user->getSubordinates() as $subordinate) {
            $orWhere = sprintf('%s OR T1.assignee = %s OR T1.assignor=%s', $orWhere, $subordinate['id'], $subordinate['id']);
        }

        $orWhere = substr($orWhere, 4);
        // Listing
        $a = <<<EOF
T1.status LIKE 'CLOSED'
AND ($orWhere)
AND DATEDIFF('{$vars['dt_from']}',T1.dt_closed)<=0
AND DATEDIFF('{$vars['dt_to']}',T1.dt_closed)>=0
EOF;
        $rows = tldTask::byConstraints($a);
        $_TITLE = "Task closed period result";
    break;
    case 'byRecentlyClosed':
        $userid = $user->getID();
        if(!empty($_REQUEST['userid'])){
            $userid = TldDatabase::escape($_REQUEST['userid']);
        }
        // check user
        $u = new tldUser($userid);
        if($u->isEmpty()){
            $DEFAULT_ERROR[] =  "ERROR: User#$userid not found";
            break 2;
        }
        if(isset($uid)){
        	$rows = tldTask::byRecentlyClosed($uid, 20);
        }else{
        	$rows = tldTask::byRecentlyClosed($userid, 20);
        }
        $_TITLE = "Recently closed tasks for ".$u->getFullname();
    break;
    case 'byRefNum':
        $form = new HTML_QuickForm('frmCancel', 'get','','','',TRUE);
        $form->addElement(	'hidden', 'm[0]', 'tasks');
        $form->addElement(	'hidden', 'm[1]', 'listing');
        $form->addElement(	'hidden', 'm[2]', 'byRefNum');
        $form->addElement(	'header', 'title', "Task/Seq by Ref#");
        $form->addElement(	'text', 'id', "Ref#");
        $form->addElement(	'submit', 'btnSubmit', 'Submit');
        if($form->validate() || !empty($_GET['id'])){
            $form->freeze();
            $rows = tldTask::byParent($id, "ALL", "ALL");
            if(count($rows)==0) {
                $DEFAULT_ERROR[] =  "ERROR: No matches found for $id";
                break;
            }
        }else{
            $body = $form->toHTML();
        }
    break;
    case 'byUserSearch':
        $form = new HTML_QuickForm('frmUserSearch', 'get','','','',TRUE);
        $form->addElement(	'hidden', 'm[0]', 'tasks');
        $form->addElement(	'hidden', 'm[1]', 'listing');
        $form->addElement(	'hidden', 'm[2]', 'byUserSearch');
        $form->addElement(	'header', 'title', "Search all my tasks...");
        $form->addElement(	'text', 'id', "Search for...");
        $form->addElement(	'checkbox', 'opened', "Not CLOSED only ?");
        $form->addElement(	'checkbox', 'seq', "SEQ only ?");
        $form->addElement(	'submit', 'btnSubmit', 'Submit');
        if ($form->validate()) {
        # If the form validates then freeze the data
            $form->freeze();
            $rows = tldTask::searchByUser($user->getID(), $id, compact('opened', 'seq'));
            if (!$rows) {
                $DEFAULT_ERROR[] =  "ERROR: No matches found for $id";
                break;
            }
        } else {
            $body = $form->toHTML();
        }
    break;
    case 'byTplStatus':
    	$datas = tldSEQTpl::getSEQTplList();
    	// Filter private sequence template
    	$tplList = array();
    	foreach($datas as $data){
    		if($data['private']=="Y") continue;
    		$tplList[] = $data['name'];
    	}
    	// Check template if public
    	if(!in_array($y,$tplList)){
    		$DEFAULT_ERROR[] =  "ERROR: $y is private";
    		break;
    	}
    	$rows = tldSEQ::byTplStatus($x,$y);
        if(count($rows)==0){
			$DEFAULT_ERROR[] =  "ERROR: No matches found...";
			break;
		}
        // Redefine fields
        $xItems = array(
            "id"                =>"SEQ#",
            "module"            =>"Module",
            "parent_id"         =>"Ref#",
            "status"            =>"Status",
            "cur_step"          =>"Current Step",
            "date"              =>"Date Opened",
            "dt_closed"         =>"Date Closed",
            "due_date"          =>"Due Date",
            "assignor_fullname" =>"Assignor",
            "assignee_fullname" =>"Assignee",
            "task"              =>"Task"
        );
    break;
    case 'advSearch':
        if ($user->isInGroup(["gg_MIS"])) {
            $acl_form_fields = array(
                "assignee","assignor","status","task","bu_id","tplno","cur_step",
                "open_start","open_end","close_start","close_end","module","parent_id",
                "assignor_div_id", 'assignee_bu_id'
            );
        } else {
            $acl_form_fields = array(
                "parent_id","status","task","bu_id","tplno","open_start","open_end", 'module', 'assignee_bu_id'
            );
        }
        $a = [];
        foreach ($_REQUEST as $key => $raw) {
            if(!in_array($key, $acl_form_fields) || empty($raw)) {
                continue;
            }

            if (in_array($raw, ["%"])) {
                continue;
            }

            // If not MIS do checks
            if (!$user->isInGroup(["gg_MIS"])) {
                // ACL check per key
                switch ($key) {
                    case 'tplno':
                        if (array_key_exists($raw, $ACL_SEQ_TPL) && !$user->isInGroup($ACL_SEQ_TPL[$raw])) {
                            $DEFAULT_ERROR[]="ERROR: Not enough constraints to run a safe search...";
                            break 2;
                        }
                        break;
                }
            }
            // rename key for query
            if(in_array($key,array("bu_id","parent_id"))){
                $key = "T1.$key";
            }
            // Construct constraint query
            switch($key){
            case "open_start";
                try{ $date = new DateTime(implode("-",$raw)); }
                catch(Exception $e){ continue 2; }
                $date = $date->format('Y-m-d');
                $a[] = " DATEDIFF('$date',date)<=0 ";
            break;
            case "open_end";
                try{ $date = new DateTime(implode("-",$raw)); }
                catch(Exception $e){ continue 2; }
                $date = $date->format('Y-m-d');
                $a[] = " DATEDIFF('$date',date)>=0 ";
            break;
            case "close_start";
                try{ $date = new DateTime(implode("-",$raw)); }
                catch(Exception $e){ continue 2; }
                $date = $date->format('Y-m-d');
                $a[] = " DATEDIFF('$date',dt_closed)<=0 ";
            break;
            case "close_end";
                try{ $date = new DateTime(implode("-",$raw)); }
                catch(Exception $e){ continue 2; }
                $date = $date->format('Y-m-d');
                $a[] = " DATEDIFF('$date',dt_closed)>=0 ";
            break;
            case "assignor_div_id":
                if (is_array($raw)) {
                    foreach ($raw as $key=>$value) {
                        $raw[$key] = TldDatabase::escape($value);
                    }
                    $a[] = sprintf(" b.div_id IN (%s) ", implode(", ", $raw));
                    break;
                }
                $raw = TldDatabase::escape($raw);
                $a[] = " b.div_id=$raw ";
            break;
            case "assignee_bu_id":
                $raw = TldDatabase::escape($raw);
                $a[] = " a.bu_id=$raw ";
            break;
            case "tplno":
                if (is_array($raw)) {
                    foreach ($raw as $key=>$value) {
                        $raw[$key] = TldDatabase::escape($value);
                    }
                    $a[] = sprintf(" T1.tplno IN (%s) ", implode(", ", $raw));
                    break;
                }
                $raw = TldDatabase::escape($raw);
                $a[] = " T1.tplno=$raw ";
                break;
            case "task":
                $raw = TldDatabase::escape($raw);
                $a[] = " $key LIKE '%$raw%' ";
            break;
            default:
                $raw = TldDatabase::escape($raw);
                if(is_numeric($raw)){
                    $a[] = " $key=$raw ";
                }elseif(is_string($raw)){
                    $a[] = " $key LIKE '$raw' ";
                }
            break;
            }
        }
        if(empty($a)){
            $DEFAULT_ERROR[]="ERROR: Not enough constraints to run a safe search...";
            break;
        }
        $constraints = implode("AND",$a);
        $rows = tldTask::search($constraints);

        $_TITLE = "Search results";
        if(count($rows)==0){
            $DEFAULT_ERROR[] =  "ERROR: No matches found...";
            break;
        }
    break;
        case 'byModule':
            $rows = tldTask::byUserbyModule($user->getID(), $module, $category);
            $_TITLE = "Tasks for ".$user->getFullname()." in module $module";
            break;
        case 'byModulesNotMigrated':
            $tasks = tldTask::countByModuleNotMigrated($user->getID());
            $modules = array_column($tasks,'module');
            $rows = tldTask::byUserbyModulesNotMigrated($user->getID(), $modules, $category);
            $inlineModules = implode(", ", array_unique($modules));

            $_TITLE = "Tasks for ".$user->getFullname()." in module $inlineModules";
            break;
    }
    if(isset($rows)){
    	$DEFAULT_MENU .=<<<EOF
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<a href="{$_SERVER["REQUEST_URI"]}&m[3]=csv">CSV version</a>
EOF;
        switch($m[3]){
        case 'csv':
        	foreach($rows AS &$row){
        		$row['task'] = strip_tags($row['task']);
        	}
            $report = new tldCSV( $rows,
            		array(
		            	"xItems"=>array(
	                        "id"=>"Task#",
	                        "module"=>"Module",
	                        "parent_id"=>"Ref#",
	                        "status"=>"Status",
                            "date" => "Date Opened",
                            "dt_closed" => "Date Closed",
	                        "due_date"=>"Due",
	                        "assignor_fullname"=>"Assignor",
                            "assignor_email"=>"Assignor Email",
	                        "assignee_fullname"=>"Assignee",
		            		"task"=>"Task description"
			            ),
            			"showTitles"=>true
		            )
            );
    		$report->out();
    		exit;
        break;
        default:
	        $sess["calendar"]["tasks"] = $rows;
	        $report = new tldReportColumnar(
	            $rows,
	            array(
		            "xItems"=>$xItems,
		            "title"=>$_TITLE,
		            "links"=>array("id"=>"$php_self?m[0]=tasks&m[1]=task&m[2]=view&id=")
	            )
	        );
        	$body .= $report->fetch();
		break;
    	}
    }
break;
case 'multiLevel':
    switch($m[2]) {
    case 'byUserModule':
        $rows = tldTask::qryLateByAssigneeModule();
        $_TITLE = "Late Tasks by Assignee, Module";
        $LEVELS = array("assignee_fullname","module");
    break;
    }
    if($rows) {
        $report = new tldReportMultiLevel(
            $rows,
            $LEVELS,
            array("id"=>"Task/SEQ#",
            "parent_id"=>"Ref#",
            "status"=>"Status",
            "date"=>"Date Opened",
            "due_date"=>"Due Date"
            ),
            array("title"=>$_TITLE,
            "passField"=>"id",
            "url"=>"$php_self?m[0]=tasks&m[1]=task&m[2]=view&id="
            )
        );
        $body .= $report->fetch();
    }
break;
case 'matrix':
    switch($m[2]) {
    case 'countbyMonthModule':
        $form = new tldMatrix(
            tldTask::countByMonthModule(),
            "module", "month_closed", "num",
            "",
            "Count of Tasks Closed by Month, Module for previous 365 days");
        $body .= $form->fetch();
    break;
    }
break;
default:
    $body = <<<EOF
<h3>Tasks</h3>
<p>Welcome to Tasks module</p>
EOF;
break;
}

?>
