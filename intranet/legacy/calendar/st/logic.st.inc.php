<?php
// Add overlib library for this section
include_once("dms.inc.php");
$JS_INCLUDE=["/shared/javascript/overlib/overlib.js"];

$smarty->assign("js_includes", $JS_INCLUDE);

$DEFAULT_TITLE .= "\ST";
$DEFAULT_MENU .= <<<EOF
	<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
	<a href="$php_self?m[0]=st&m[1]=STList">STs List</a>
	&nbsp;|&nbsp;<a href="$php_self?m[0]=st&m[1]=forms&m[2]=newST" title="Create a new Scheduled Task Entry">New ST</a>
	&nbsp;|&nbsp;<a href="$php_self?m[0]=st&m[1]=forms&m[2]=byID" title="Seach ST by number">By Num</a>
    &nbsp;|&nbsp;<a href="$php_self?m[0]=st&m[1]=reports" title="Reports">Reports</a>
	&nbsp;|&nbsp;<a href="$php_self?m[0]=st&m[1]=help">Help</a>
EOF;

if ($user->isInGroup("superuser")) {
    $DEFAULT_MENU .= <<<EOF
	&nbsp;|&nbsp;<a href="st/st_admin.php" title="Scheduled Task Admin">ST Admin</a>
EOF;
}

// Reassignment is offered to any user who has direct reports: when someone
// leaves the company their STs are moved to their supervisor, who then
// redispatches them to a team member from here. Superusers can also drive the
// reassignment on behalf of any supervisor.
if ($user->isInGroup("superuser") || count($user->getSubordinates()) > 0) {
    $DEFAULT_MENU .= <<<EOF
	&nbsp;|&nbsp;<a href="$php_self?m[0]=st&m[1]=reassign" title="Reassign my STs to a team member">Reassign STs</a>
EOF;
}

// General Help Page and Fields definition text
// will be used in the "New ST" Form in an tldOverlib popup (definition of the fields)
// will also be used to generate the Help Page of the ST module
$help = array(
    "ST Module Help Page" => "ST stands for \"Scheduled Tasks\".<br>
		This Module will allow you to create a schedule that will trigger
		the opening of a task per your specifications.<br>",
    "Fields Definition" => "<ul>
		<li>Start: Start date of the Schedule, this will also be the opening/due date of the fist task.</li>
		<li>End : End date of the Schedule (Must be > or = to Start Date).</li>
		<li>Every : Schedule for which the task will be opened (eg: Every 10 Days).</li>
		<li>Lead Time : Time needed to get the task done (eg: for a ST with start date of 06/04/2009 and
		lead time of 1 Day, the task will be opened on the 06/03/2009 (start date - lead time) with a due date of 06/04/2009)</li>
		<li>Assignee : Person to assign the Task to.</li>
		<li>Where : Location (Optional, eg: TLD MTL,...).</li>
		<li>Type : Type of the ST (Optional, eg: Training,...).</li>
		<li>Task Description : Description that will appear in the Task.</li>
		</ul>"
);

switch ($m[1]) {
    case 'reports':
        switch ($m[2]) {
            case 'byDMSByStatusByBU':
                $factoryList = ["ALL"=>"ALL","3"=>"TLD GST"]+tldLocation::getLocationList("smartyOptions");
                //Get form
                $form = new HTML_QuickForm('frm', 'get','','','',true);
                $form->addElement(	'hidden', 	'm[0]', 		'st');
                $form->addElement(	'hidden', 	'm[1]', 		'reports');
                $form->addElement(	'hidden', 	'm[2]', 		'byDMSByStatusByBU');
                $form->addElement(	'header', 	'title', 		'Select filters');
                $form->addElement(  'text',     'dmsid',        'DMS');
                $form->addElement(  'text',     'start',        'Date from', ["class"=>"datepicker"]);
                $form->addElement(  'text',     'end',          'Date to', ["class"=>"datepicker"]);
                $form->addElement(	'select', 	'erp',		    'Company/Location',		$factoryList);
                $form->addElement(	'select', 	'status',		'Status',		["ALL"=>"ALL","ACTIVE"=>"ACTIVE","INACTIVE"=>"INACTIVE"]);
                $form->addElement(	'select', 	'child',		'Show children?',		["1"=>"YES","0"=>"NO"]);
                $form->addElement(  'submit', 	'btnSubmit', 	'Submit');
                $form->addRule('erp', 'Required', 'required');
                $form->addRule('dmsid', 'Required', 'required');
                $form->setDefaults(['start'=>date("Y-m-01"), 'end'=>date("Y-m-d")]);

                if (!$form->validate()) {
                    $body = $form->toHTML();
                    break;
                }

                $formData = tldUtils::cleanupFormInput($form->exportValues());
                $dms = new tldDMS($formData['dmsid']);
                $dmsTree = [];

                // First level of the DMS tree
                $dmsTree[$dms->getID()] = ['header' => $dms->itsHeader, 'level' => 0];

                if ($formData['child']) {
                    $dms->getChildListRec($dmsTree);
                }

                $dmsList = array_keys($dmsTree);
                $_SESSION['dmsList'] = $dmsList;

                $tasks = tldST::getScheduledTasksDMSReport($dmsList, $formData['erp'], $formData['status'], $formData['start'], $formData['end']);

                // Set DMS level on hierarchy
                foreach ($tasks as $key => $task) {
                    $dmsId = $task['dms_id'];
                    $level = $dmsTree[$dmsId]['level'];
                    $tasks[$key]['level'] = $level;
                }

                $items = [
                    'dms' => 'DMS#',
                    'parent' => 'Parent DMS #',
                    'level' => 'Relative level',
                    'st' => 'ST#',
                    'st_description' => 'ST Description',
                    'closed' => 'CLOSED TASKS TOTAL',
                    'open' => 'OPEN TASKS TOTAL',
                    'not_due' => 'Including NOT due',
                    'due' => 'Including LATE',
                    'not_done' => 'CLOSED NOT DONE TASKS TOTAL',
                    'assignee_bu' => 'Assignee BU',
                ];

                $report = new tldReportColumnar(
                    $tasks,
                    [
                        "xItems" => $items,
                        "links"=>[
                            "dms" => [
                                "url" => "$DMS_URL/index.php?m[0]=view",
                                "params" => [
                                    "id" => "dms_id"
                                ]
                            ],
                            "parent" => [
                                "url" => "$DMS_URL/index.php?m[0]=view",
                                "params" => [
                                    "id" => "parent"
                                ]
                            ],
                            "st" => [
                                "url"=>"$php_self?m[0]=st&m[1]=view",
                                "params"=>[
                                    "id"=>"st"
                                ]
                            ],
                            "closed"=>[
                                "url"=>"$php_self?m[0]=st&m[1]=listing&m[2]=closedTasks&start={$formData['start']}&end={$formData['end']}",
                                "params"=>[
                                    "id"=>"st",
                                    "dms" => "dms_id",
                                ]
                            ],
                            "open"=>[
                                "url"=>"$php_self?m[0]=st&m[1]=listing&m[2]=openTasks&start={$formData['start']}&end={$formData['end']}",
                                "params"=>[
                                    "id"=>"st",
                                    "dms" => "dms_id",
                                ]
                            ],
                            "not_due"=>[
                                "url"=>"$php_self?m[0]=st&m[1]=listing&m[2]=undueTasks&start={$formData['start']}&end={$formData['end']}",
                                "params"=>[
                                    "id"=>"st",
                                    "dms" => "dms_id",
                                ]
                            ],
                            "due"=>[
                                "url"=>"$php_self?m[0]=st&m[1]=listing&m[2]=dueTasks&start={$formData['start']}&end={$formData['end']}",
                                "params"=>[
                                    "id"=>"st",
                                    "dms" => "dms_id",
                                ]
                            ],
                            "not_done"=>[
                                "url"=>"$php_self?m[0]=st&m[1]=listing&m[2]=closedTasks&notDone=1&start={$formData['start']}&end={$formData['end']}",
                                "params"=>[
                                    "id"=>"st",
                                    "dms" => "dms_id",
                                ]
                            ]
                        ],
                        "title"=>"Tasks list follow DMS",
                        'showzero' => true,
                        "sumTotalsArray" => ["closed", "open", "due", "not_due", "not_done"],
                        'sumTotalsUrl' => true
                    ]
                );
                $body .= $report->fetch();

                $DEFAULT_MENU .= <<<EOF
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<a href="{$_SERVER["REQUEST_URI"]}&m[3]=export">Download XLS</a>
EOF;

                if ($m['3'] === 'export') {
                    $xls = new tldXLS(
                        $tasks,
                        [
                            'xItems' => $items,
                            'showTitles' =>true
                        ],
                    );
                    $xls->out();
                }
                break;
            case 'taskFromSTs':
                $department = $user->getDepartmentID();
                $today = date('Y-m-d');
                $membersList = tldDirectory::byConstraints(['dpt_id' => $user->getDepartmentID(), 'hidden' => 0]);
                foreach($membersList as $member){
                    $peopleList[$member['id']] = $member['fullname'];
                }

                $subordinatesName = $user->getAllSubordinates($user->getId());
                $team = [];
                foreach ($user->getAllSubordinatesID($user->getId()) as $key => $value) {
                    $team[$value] = $subordinatesName[$key];
                }

                $form = new HTML_QuickForm('form', 'get','','','',true);
                $form->addElement(	'hidden', 'm[0]', 'st');
                $form->addElement(	'hidden', 'm[1]', 'reports');
                $form->addElement(	'hidden', 'm[2]', 'taskFromSTs');
                $form->addElement(	'header', 'title', 'Select filters');
                $form->addElement(  'text', 'start', 'Date from', ['class' => 'datepicker']);
                $form->addElement(  'text', 'end', 'Date to', ['class' => 'datepicker']);
                $departmentForm = &$form->addElement('advmultiselect', 'members', null,
                    $peopleList,
                    ['size' => 10, 'class' => 'pool', 'style' => 'width:500px;']
                );
                $departmentForm->setLabel(['Department members', '', '']);
                $departmentForm->setButtonAttributes('add', ['value' => '-->>', 'class' => 'inputCommand']);
                $departmentForm->setButtonAttributes('remove', ['value' => '<<--', 'class' => 'inputCommand']);
                $teamForm = &$form->addElement('advmultiselect', 'team', null,
                    $team,
                    ['size' => 10, 'class' => 'pool', 'style' => 'width:500px;']
                );
                $teamForm->setLabel(['Team members', '', '']);
                $teamForm->setButtonAttributes('add', ['value' => '-->>', 'class' => 'inputCommand']);
                $teamForm->setButtonAttributes('remove', ['value' => '<<--', 'class' => 'inputCommand']);
                $form->addElement(  'submit', 'btnSubmit', 'Submit');
                $form->addRule('start', 'Required', 'required');
                $form->addRule('end', 'Required', 'required');
                $form->setDefaults(['start' => date('Y-m-01'), 'end' => $today]);

                if (!$form->validate()) {
                    $body = $form->toHTML();
                    break;
                }

                $data = tldUtils::cleanupFormInput($form->exportValues());
                $ids = array_unique(array_merge($data['members'] ?? [], $data['team'] ?? []));
                $scheduleTasks = tldST::byConstraints(sprintf("cal_st.assignee IN (%s) AND cal_st.status = 'ACTIVE'", implode(', ', $ids)));
                $request = sprintf("T1.module = 'ST' AND T1.parent_id IN (%s) AND T1.date BETWEEN '%s' AND '%s'",
                    implode(', ', array_column($scheduleTasks, 'id')),
                    $data['start'],
                    $today
                );
                $tasksOpen = tldTask::byConstraints($request);

                $todayDate = new DateTime($today);
                $endDate = new DateTime($data['end']);
                $results = [];
                foreach ($scheduleTasks as $scheduleTask) {
                    $scheduleTaskStartDate = new DateTime($scheduleTask['date_start']);
                    $scheduleTaskEndDate = new DateTime($scheduleTask['date_end']);
                    $interval = $scheduleTaskStartDate->diff($todayDate);
                    switch ($scheduleTask['repd_unit']) {
                        case 'YEAR':
                            $years = ceil($interval->y/$scheduleTask['repd_val']);
                            $nextStDate = $scheduleTaskStartDate->add(new DateInterval(sprintf('P%sY', $years*$scheduleTask['repd_val'])));
                            while($nextStDate < $scheduleTaskEndDate && $nextStDate < $endDate) {
                                $results[] = $scheduleTask + ['date' => $nextStDate->format('Y-m-d')];
                                $nextStDate = $nextStDate->add(new DateInterval(sprintf('P%sY', $scheduleTask['repd_val'])));
                            }
                        break;
                        case 'MONTH':
                            $months = ceil($interval->m/$scheduleTask['repd_val']);
                            $nextStDate = $scheduleTaskStartDate->add(new DateInterval(sprintf('P%sY%sM', $interval->y, $months*$scheduleTask['repd_val'])));
                            while($nextStDate < $scheduleTaskEndDate && $nextStDate < $endDate) {
                                $results[] = $scheduleTask + ['date' => $nextStDate->format('Y-m-d')];
                                $nextStDate = $nextStDate->add(new DateInterval(sprintf('P%sM', $scheduleTask['repd_val'])));
                            }
                        break;
                        case 'WEEK':
                            $weeks = ceil($interval->days/($scheduleTask['repd_val']*7));
                            $nextStDate = $scheduleTaskStartDate->add(new DateInterval(sprintf('P%sD', $weeks*7*$scheduleTask['repd_val'])));
                            while($nextStDate < $scheduleTaskEndDate && $nextStDate < $endDate) {
                                $results[] = $scheduleTask + ['date' => $nextStDate->format('Y-m-d')];
                                $nextStDate = $nextStDate->add(new DateInterval(sprintf('P%sD', $scheduleTask['repd_val']*7)));
                            }
                        break;
                        case 'DAY':
                            $days = ceil($interval->days/($scheduleTask['repd_val']));
                            $nextStDate = $scheduleTaskStartDate->add(new DateInterval(sprintf('P%sD', $days*$scheduleTask['repd_val'])));
                            while($nextStDate < $scheduleTaskEndDate && $nextStDate < $endDate) {
                                $results[] = $scheduleTask + ['date' => $nextStDate->format('Y-m-d')];
                                $nextStDate = $nextStDate->add(new DateInterval(sprintf('P%sD', $scheduleTask['repd_val'])));
                            }
                        break;
                    }
                }

                foreach ($tasksOpen as $task) {
                    foreach ($scheduleTasks as $scheduleTask) {
                        if ($scheduleTask['id'] === $task['parent_id']) {
                            unset($task['id']);
                            $results[] = $task + $scheduleTask;
                            continue 2;
                        }
                    }
                }

                $items = [
                    'date' => 'Task Open date',
                    'due_date' => 'Task Due Date',
                    'assignor_fullname' => 'Assignor',
                    'assignee_fullname' => 'Assignee',
                    'assigneeEmail' => 'Assignee Email',
                    'id' => 'ST #',
                    'type' => 'ST Type',
                    'description' => 'Task designation',
                    'assigneeRegion' => 'Assignee Region',
                    'assigneeBU' => 'Assignee BU',
                    'assigneeDepartment' => 'Assignee Department',
                    'leadtime' => 'Estimated load time.',
                    'periodicity' => 'Periodicity',
                    'ref' => 'Reference',
                    'autoClose' => 'Status of autoclose field (Y or N)'
                ];

                $report = new tldReportColumnar(
                    $results,
                    [
                        'xItems' => $items,
                        'title' => 'Schedules Task',
                    ]
                );
                $body .= $report->fetch();

                $DEFAULT_MENU .= <<<EOF
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<a href="{$_SERVER["REQUEST_URI"]}&m[3]=xls">Download XLS</a>
EOF;
                if ($m[3] === 'xls') {
                    $xls = new tldXLS(
                        $results,
                        array(
                            "xItems"=>array(
                                'date' => 'Task Open date',
                                'due_date' => 'Task Due Date',
                                'assignor_fullname' => 'Assignor',
                                'assignee_fullname' => 'Assignee',
                                'assigneeEmail' => 'Assignee Email',
                                'id' => 'ST #',
                                'type' => 'ST Type',
                                'description' => 'Task designation',
                                'assigneeRegion' => 'Assignee Region',
                                'assigneeBU' => 'Assignee BU',
                                'assigneeDepartment' => 'Assignee Department',
                                'leadtime' => 'Estimated load time.',
                                'periodicity' => 'Periodicity',
                                'ref' => 'Reference',
                                'autoClose' => 'Autoclose (Y/N)'
                            ),
                            "showTitles"=>true
                        )
                    );
                    $xls->out();
                }
            break;
            case 'STByLocationByType':
                $form = new tldMatrix(
                    tldST::countByBUByType(),
                    "type", "bu_fullname", "num",
                    "$php_self?m[0]=st&m[1]=listing&m[2]=byLocationByType",
                    "ST by location, by type"
                );
                $body = $form->fetch();
            break;
            default:
                $body .= $smarty->fetch("$PATH/st/reports/homepage.reports.tpl");
        }

    break;
    case 'listing':
        $DEFAULT_MENU .= <<<EOF
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<a href="{$_SERVER["REQUEST_URI"]}&m[3]=xls">Download XLS</a>
EOF;
        switch ($m[2]) {
            case 'closedTasks':
                $id = TldDatabase::escape($_GET['id']);
                $dms = TldDatabase::escape($_GET['dms']) !== '' ? [TldDatabase::escape($_GET['dms'])] : $_SESSION['dmsList'];
                $start = TldDatabase::escape($_GET['start']);
                $end = TldDatabase::escape($_GET['end']);

                $rows = tldST::byDMS($dms, $id, $start, $end, 'CLOSED');
                if (true === ($notDone = (bool) $_GET['notDone'])) {
                    $rows = array_filter($rows, static function ($task) {
                        return (bool) $task['closed_not_done'];
                    });
                }
                $title = sprintf('All CLOSED%s Task list, ST#%s', $notDone ? ' NOT DONE': '', $id);
                $report = new tldReportColumnar(
                    $rows,
                    [
                        "xItems"=>[
                            "id" => "Task#",
                            "date" => "Open date",
                            "dt_closed" => "Closed date",
                            "due_date" => "Due date",
                            "escalation_trigger" => "Escalation Trigger(Days)",
                            "assignor_fullname" => "Assignor",
                            "assignee_fullname" => "Assignee",
                            "status" => "Status"
                        ],
                        "title"=> $title,
                        "links"=>[
                            "id"=> "/en/private/calendar/calendar.php?m[0]=tasks&m[1]=task&m[2]=view&id="
                        ]
                    ]
                );

                $body .= $report->fetch();
                if (null !== ($m[3] ?? null) && $m[3] === 'xls') {
                    $report = new tldXLS(
                        $rows,
                        [
                            "xItems" => [
                                "id" => "Task#",
                                "date" => "Open date",
                                "dt_closed" => "Closed date",
                                "due_date" => "Due date",
                                "escalation_trigger" => "Escalation Trigger(Days)",
                                "assignor_fullname" => "Assignor",
                                "assignee_fullname" => "Assignee",
                                "status" => "Status"
                            ],
                            "showTitles"=>true
                        ]
                    );
                    $report->out();
                    exit;
                }
            break;
            case 'openTasks':
                $id = TldDatabase::escape($_GET['id']);
                $dms = TldDatabase::escape($_GET['dms']) !== '' ? [TldDatabase::escape($_GET['dms'])] : $_SESSION['dmsList'];
                $start = TldDatabase::escape($_GET['start']);
                $end = TldDatabase::escape($_GET['end']);

                $rows = $sess["task"]["list"] = tldST::byDMS($dms, $id, $start, $end, ['OPEN', 'IN PROGRESS']);
                $report = new tldReportColumnar(
                    $rows,
                    [
                        "xItems"=>[
                            "id" => "Task#",
                            "date" => "Open date",
                            "due_date" => "Due date",
                            "escalation_trigger" => "Escalation Trigger(Days)",
                            "assignor_fullname" => "Assignor",
                            "assignee_fullname" => "Assignee",
                            "status" => "Status"
                        ],
                        "title"=>'All OPEN Task list',
                        "links"=>[
                            "id"=> "/en/private/calendar/calendar.php?m[0]=tasks&m[1]=task&m[2]=view&id="
                        ]
                    ]
                );
                $body .= $report->fetch();
                if (null !== ($m[3] ?? null) && $m[3] === 'xls') {
                    $report = new tldXLS(
                        $rows,
                        [
                            "xItems" => [
                                "id" => "Task#",
                                "date" => "Open date",
                                "dt_closed" => "Closed date",
                                "due_date" => "Due date",
                                "escalation_trigger" => "Escalation Trigger(Days)",
                                "assignor_fullname" => "Assignor",
                                "assignee_fullname" => "Assignee",
                                "status" => "Status"
                            ],
                            "showTitles"=>true
                        ]
                    );
                    $report->out();
                    exit;
                }
            break;
            case 'dueTasks':
                $id = TldDatabase::escape($_GET['id']);
                $dms = TldDatabase::escape($_GET['dms']) !== '' ? [TldDatabase::escape($_GET['dms'])] : $_SESSION['dmsList'];
                $start = TldDatabase::escape($_GET['start']);
                $end = TldDatabase::escape($_GET['end']);

                $rows = tldST::byDMS($dms, $id, $start, $end, ['OPEN' , 'IN PROGRESS'], 'AND TO_DAYS(now()) - TO_DAYS(tasks.due_date) > 0');
                $report = new tldReportColumnar(
                    $rows,
                    [
                        "xItems"=>[
                            "id" => "Task#",
                            "date" => "Open date",
                            "due_date" => "Due date",
                            "escalation_trigger" => "Escalation Trigger(Days)",
                            "assignor_fullname" => "Assignor",
                            "assignee_fullname" => "Assignee",
                            "status" => "Status"
                        ],
                        "title"=>'All DUE Task list',
                        "links"=>[
                            "id"=> "/en/private/calendar/calendar.php?m[0]=tasks&m[1]=task&m[2]=view&id="
                        ]
                    ]
                );
                $body .= $report->fetch();
                if (null !== ($m[3] ?? null) && $m[3] === 'xls') {
                    $report = new tldXLS(
                        $rows,
                        [
                            "xItems" => [
                                "id" => "Task#",
                                "date" => "Open date",
                                "dt_closed" => "Closed date",
                                "due_date" => "Due date",
                                "escalation_trigger" => "Escalation Trigger(Days)",
                                "assignor_fullname" => "Assignor",
                                "assignee_fullname" => "Assignee",
                                "status" => "Status"
                            ],
                            "showTitles"=>true
                        ]
                    );
                    $report->out();
                    exit;
                }
            break;
            case 'undueTasks':
                $id = TldDatabase::escape($_GET['id']);
                $dms = TldDatabase::escape($_GET['dms']) !== '' ? [TldDatabase::escape($_GET['dms'])] : $_SESSION['dmsList'];
                $start = TldDatabase::escape($_GET['start']);
                $end = TldDatabase::escape($_GET['end']);

                $rows = tldST::byDMS($dms, $id, $start, $end, ['OPEN' , 'IN PROGRESS'], 'AND TO_DAYS(now()) - TO_DAYS(tasks.due_date) < 0');
                $report = new tldReportColumnar(
                    $rows,
                    [
                        "xItems"=>[
                            "id" => "Task#",
                            "date" => "Open date",
                            "due_date" => "Due date",
                            "escalation_trigger" => "Escalation Trigger(Days)",
                            "assignor_fullname" => "Assignor",
                            "assignee_fullname" => "Assignee",
                            "status" => "Status"
                        ],
                        "title"=>'All On-time Task list',
                        "links"=>[
                            "id"=> "/en/private/calendar/calendar.php?m[0]=tasks&m[1]=task&m[2]=view&id="
                        ]
                    ]
                );
                $body .= $report->fetch();
                if (null !== ($m[3] ?? null) && $m[3] === 'xls') {
                    $report = new tldXLS(
                        $rows,
                        [
                            "xItems" => [
                                "id" => "Task#",
                                "date" => "Open date",
                                "dt_closed" => "Closed date",
                                "due_date" => "Due date",
                                "escalation_trigger" => "Escalation Trigger(Days)",
                                "assignor_fullname" => "Assignor",
                                "assignee_fullname" => "Assignee",
                                "status" => "Status"
                            ],
                            "showTitles"=>true
                        ]
                    );
                    $report->out();
                    exit;
                }
            break;
            case 'byLocationByType':
                $type = TldDatabase::escape($_REQUEST['x']);
                $bu = TldDatabase::escape($_REQUEST['y']);
                $_title = "ST list by type by BU";
                $rows = $sess["st"]["list"]  = tldST::byTypeByBU($type, $bu);
                if(!isset($rows)){
                    break;
                }elseif(count($rows)<1){
                    $DEFAULT_ERROR[]="No record found...";
                    break;
                }

                switch($m[3]){
                    case 'xls':
                        $report = new tldXLS(
                            $sess["st"]["list"],
                            array(
                                "xItems"=>[
                                    "id" => "ST#",
                                    "bu_fullname" => "BU id",
                                    "date_start" => "Start",
                                    "date_end" => "End",
                                    "repd_val" => "repetition value",
                                    "repd_unit" => "repetition unit",
                                    "leadtime_value" => "lead time value",
                                    "leadtime_unit" => "lead time unit",
                                    "escalation_trigger" => "Escalation Trigger(Days)",
                                    "assignor_fullname" => "Assignor",
                                    "assignee_fullname" => "Assignee",
                                    "type" => "Type",
                                    "description" => "Description",
                                    "referencetype" =>"Reference Type",
                                    "reference" => "Reference#",
                                    "status" => "Status"
                                ],
                                "showTitles"=>true
                            )
                        );
                        $report->out();
                        exit;
                        break;
                    default:
                        $sess["st"]["list"] = $rows;
                        $report = new tldReportColumnar(
                            $rows,
                            array(
                                "xItems"=>[
                                    "id" => "ST#",
                                    "bu_fullname" => "BU id",
                                    "date_start" => "Start",
                                    "date_end" => "End",
                                    "repd_val" => "repetition value",
                                    "repd_unit" => "repetition unit",
                                    "leadtime_value" => "lead time value",
                                    "leadtime_unit" => "lead time unit",
                                    "escalation_trigger" => "Escalation Trigger(Days)",
                                    "assignor_fullname" => "Assignor",
                                    "assignee_fullname" => "Assignee",
                                    "type" => "Type",
                                    "description" => "Description",
                                    "referencetype" =>"Reference Type",
                                    "reference" => "Reference#",
                                    "status" => "Status"
                                ],
                                "title"=>'ST list',
                                "links"=>array(
                                    "reference"=> "/en/private/mis/mis.php?m[0]=help&m[1]=dms&id="
                                )
                            )
                        );
                        $body .= $report->fetch();
                        break;
                }
        }
    break;
    case 'help':
        $DEFAULT_TITLE .= "\Help";
        foreach ($help as $helpTitle => $helpText) {
            $helpBody .= <<<EOF
<h3>$helpTitle</h3>
<p>$helpText</p>
EOF;
        }
        $body = $helpBody;
        break;
    case 'reassign':
        // Redispatch active STs assigned to a supervisor (they landed there when
        // a team member left the company) to one of that supervisor's direct
        // reports. Both the ST (cal_st.assignee) and the still-open ST tasks are
        // moved.
        //
        // A regular user operates on their own STs. A superuser first picks the
        // supervisor to act on behalf of, then gets the exact same form.
        $DEFAULT_TITLE .= "\Reassign";

        $isSuperuser = $user->isInGroup('superuser');
        $selectedPeopleId = (int) ($_REQUEST['people'] ?? 0);

        if ($isSuperuser) {
            // People who have at least one active direct report.
            $managerRows = tldUtils::getSqlToAssocArray(
                "SELECT p.id, CONCAT(p.lastname, ', ', p.firstname) AS fullname
                 FROM people p
                 WHERE p.hidden = 0 AND p.disabled = 'N'
                   AND p.id IN (
                       SELECT DISTINCT sub.reports_to FROM people sub
                       WHERE sub.hidden = 0 AND sub.disabled = 'N' AND sub.reports_to > 0
                   )
                 ORDER BY fullname"
            );
            $managers = array_column($managerRows, 'fullname', 'id');

            if (!isset($managers[$selectedPeopleId])) {
                // Step 0: pick the supervisor to act on behalf of.
                $peopleForm = new HTML_QuickForm('frmReassignPeople', 'get');
                $peopleForm->addElement('hidden', 'm[0]', 'st');
                $peopleForm->addElement('hidden', 'm[1]', 'reassign');
                $peopleForm->addElement('header', 'title', 'Select a supervisor');
                $peopleForm->addElement('select', 'people', 'Supervisor', ['' => '---'] + $managers);
                $peopleForm->addElement('submit', 'btnSubmit', 'Next');
                $peopleForm->addRule('people', 'Select a supervisor', 'required');
                $body = $peopleForm->toHTML();
                break;
            }

            $supervisor = new tldUser($selectedPeopleId);
        } else {
            $supervisor = $user;
        }

        $supervisorId = (int) $supervisor->getId();
        $supervisorName = $supervisor->getFullname();
        $isSelf = ($supervisorId === (int) $user->getId());
        // Carried through every subsequent request when a superuser drives it.
        $peopleQs = $isSelf ? '' : '&people=' . $supervisorId;

        // Possible new assignees: a supervisor can only pick among their own
        // direct reports; a superuser can pick anyone in the directory.
        if ($isSuperuser) {
            $assigneeChoices = tldDirectory::getUserlist('smartyOptions');
        } else {
            $assigneeChoices = $supervisor->getSubordinates('smartyOptions'); // direct reports only
            if (!count($assigneeChoices)) {
                $DEFAULT_ERROR[] = "You have no team member to reassign STs to.";
                break;
            }
        }

        $stRows = tldST::byConstraints(sprintf(
            "cal_st.assignee = %d AND cal_st.status = 'ACTIVE'",
            $supervisorId
        ));
        if (!count($stRows)) {
            $DEFAULT_ERROR[] = $isSelf
                ? "You have no active ST assigned to you."
                : sprintf('%s has no active ST assigned to them.', $supervisorName);
            break;
        }

        // STs the supervisor is actually allowed to reassign.
        $reassignableStIds = array_map('intval', array_column($stRows, 'id'));

        // Normalise the posted selection (used by both the confirm step and the
        // final apply step).
        $postedStIds = array_values(array_intersect(
            array_map('intval', (array) ($_REQUEST['sts'] ?? [])),
            $reassignableStIds
        ));
        $postedAssignee = (int) ($_REQUEST['assignee'] ?? 0);

        // Final step: the confirmation form has been submitted, apply the change.
        if (!empty($_REQUEST['confirmed']) && $postedStIds && isset($assigneeChoices[$postedAssignee])) {
            $idList = implode(', ', $postedStIds);
            foreach ($postedStIds as $stId) {
                (new tldST($stId))->reassign($postedAssignee);
                tldUtils::log_event(sprintf(
                    'ST#%d reassigned from user %d to user %d',
                    $stId,
                    $supervisorId,
                    $postedAssignee
                ));
            }
            tldUtils::sqlQuery(sprintf(
                "UPDATE tasks SET assignee = %d
                 WHERE module = 'ST' AND parent_id IN (%s)
                   AND status <> 'CLOSED' AND assignee = %d",
                $postedAssignee,
                $idList,
                $supervisorId
            ));

            $DEFAULT_ERROR[] = sprintf(
                '%d ST(s) reassigned to %s.',
                count($postedStIds),
                $assigneeChoices[$postedAssignee]
            );
            $body = sprintf(
                '<p>The following STs are now assigned to %s: %s</p>'
                . '<p><a href="%s?m[0]=st&m[1]=reassign%s">Reassign more STs</a></p>',
                $assigneeChoices[$postedAssignee],
                $idList,
                $php_self,
                $peopleQs
            );
            break;
        }

        $stOptions = [];
        foreach ($stRows as $row) {
            $stOptions[$row['id']] = sprintf(
                'ST#%d - %s (%s)',
                $row['id'],
                $row['description'],
                trim($row['periodicity'])
            );
        }

        $form = new HTML_QuickForm('frmReassignST', 'get');
        $form->addElement('hidden', 'm[0]', 'st');
        $form->addElement('hidden', 'm[1]', 'reassign');
        if (!$isSelf) {
            $form->addElement('hidden', 'people', $supervisorId);
        }
        $form->addElement('header', 'title', sprintf('Reassign STs of %s', $supervisorName));
        $stSelect = &$form->addElement('advmultiselect', 'sts', null, $stOptions, [
            'size' => 10,
            'class' => 'pool',
            'style' => 'width:500px;',
        ]);
        $stSelect->setLabel(['STs to reassign', '', '']);
        $stSelect->setButtonAttributes('add', ['value' => '-->>', 'class' => 'inputCommand']);
        $stSelect->setButtonAttributes('remove', ['value' => '<<--', 'class' => 'inputCommand']);
        $form->addElement('select', 'assignee', 'New assignee', ['' => '---'] + $assigneeChoices);
        $form->addElement('submit', 'btnSubmit', 'Submit');
        $form->addRule('sts', 'Select at least one ST', 'required');
        $form->addRule('assignee', 'Select the new assignee', 'required');

        if (!$form->validate()) {
            $body = $form->toHTML();
            break;
        }

        $data = tldUtils::cleanupFormInput($form->exportValues());
        $newAssigneeId = (int) $data['assignee'];
        $selectedIds = array_values(array_intersect(
            array_map('intval', (array) $data['sts']),
            $reassignableStIds
        ));

        if (!$selectedIds || !isset($assigneeChoices[$newAssigneeId])) {
            $DEFAULT_ERROR[] = "Invalid selection.";
            $body = $form->toHTML();
            break;
        }

        // Confirmation step: recap the selection and post it back with confirmed=1.
        $recapRows = [];
        foreach ($stRows as $row) {
            if (in_array((int) $row['id'], $selectedIds, true)) {
                $recapRows[] = $row;
            }
        }
        $report = new tldReportColumnar($recapRows, [
            'xItems' => [
                'id' => 'ST#',
                'description' => 'Description',
                'periodicity' => 'Periodicity',
            ],
            'title' => sprintf('These STs will be reassigned to %s', $assigneeChoices[$newAssigneeId]),
        ]);
        $hiddenInputs = '';
        if (!$isSelf) {
            $hiddenInputs .= sprintf('<input type="hidden" name="people" value="%d">', $supervisorId);
        }
        foreach ($selectedIds as $sid) {
            $hiddenInputs .= sprintf('<input type="hidden" name="sts[]" value="%d">', $sid);
        }
        $body = $report->fetch();
        $body .= <<<EOF
<form method="post" action="$php_self">
    <input type="hidden" name="m[0]" value="st">
    <input type="hidden" name="m[1]" value="reassign">
    <input type="hidden" name="confirmed" value="1">
    <input type="hidden" name="assignee" value="$newAssigneeId">
    $hiddenInputs
    <input type="submit" class="inputCommand" value="Confirm reassignment">
    <a href="$php_self?m[0]=st&m[1]=reassign$peopleQs">Cancel</a>
</form>
EOF;
        break;
    case "forms":
        switch ($m[2]) {
            case "newST":
                $DEFAULT_TITLE .= "\New";
                $ccAddressList = ('ASO' !== $module) ? tldDirectory::getUserlist('smartyOptions') : tldDirectory::getUserlistByERP($user->getBUID(),'smartyOptions');
                if (empty($module))
                    $module = "ST";
                    // get the list of people for assignees and assignors
                if ($user->isInGroup(['gg_ADMIN', 'gg_ACCT', 'gg_HR'])) {
                    $assignees = tldDirectory::getUserList('smartyOptions');
                } else {
                    $assignees = tldTask::getAssigneesByUser($module);
                }
                // Get the form
                $form = new HTML_QuickForm('frmNewTask', 'post');
                $form->addElement('hidden', 'm[0]', 'st');
                $form->addElement('hidden', 'm[1]', 'forms');
                $form->addElement('hidden', 'm[2]', 'newST');
                $form->addElement('hidden', 'module', $module);
                $form->addElement('hidden', 'parent_id', $parent_id);
                $form->addElement('header', 'title', "Submit new $module task");
                $form->addElement('text', 'date_start', 'Start');
                $form->addElement('text', 'date_end', 'End');
                $groupEvery[] = & $form->createElement('text', 'repd_val', 'Every');
                $groupEvery[] = & $form->createElement('select', 'repd_unit', '', [
                    '' => '',
                    'DAY' => 'DAYS',
                    'WEEK' => 'WEEKS',
                    'MONTH' => 'MONTHS',
                    'YEAR' => 'YEARS'
                ]);
                $form->addGroup($groupEvery, 'every', 'Every:', ' ');
                $groupLeadtime[] = & $form->createElement('text', 'leadtime_value', 'Lead Time');
                $groupLeadtime[] = & $form->createElement('select', 'leadtime_unit', '', [
                    'DAY' => 'DAYS',
                    'WEEK' => 'WEEKS',
                    'MONTH' => 'MONTHS',
                    'YEAR' => 'YEARS'
                ]);
                $form->addGroup($groupLeadtime, 'leadtime', 'Lead time:', ' ');
                $form->addElement('text', 'escalation_trigger', 'Escalation trigger(Days)', [
                    'size' => 10,
                    'maxlength' => 5
                ]);
                if ($user->isInGroup('gg_MIS')) {
                    $form->addElement('select', 'assignor', 'Assignor', $assignees);
                }
                $ams =& $form->addElement(
                    'advmultiselect', 'assignee', null,
                    $ccAddressList,
                    [
                        'size' => 10,
                        'class' => 'pool',
                        'style' => 'width:500px;'
                    ]
                );
                $ams->setLabel(['Assignee (max 15 recipients)... (OPTIONAL)', 'Addressbook', 'Assignee']);
                $form->addRule('assignee', 'Required', 'required');

                $bu_id = ['' => '']+ tldLocation::getLocationList('smartyOptions');
                $form->addElement('select', 'bu_id', 'Where', $bu_id);
                $typeList = [
                    'Miscellaneous' => 'Miscellaneous',
                    'Documents' => 'Documents',
                    'Environmental' => 'Environmental',
                    'Facilities' => 'Facilities',
                    'Health and Safety' => 'Health and Safety',
                    'Security' => 'Security',
                    'Training' => 'Training',
                    'Quality' => 'Quality',
                    'iBS' => 'iBS',
                    'Contract' => 'Contract',
                    'IT Security' => 'IT Security',
                ];
                $form->addElement('select', 'type', 'Type', $typeList);
                $form->addElement('textarea', 'description', 'Task description', [
                    'wrap' => 'VIRTUAL',
                    'cols' => '60',
                    'rows' => '8'
                ]);

                $form->addElement('header', 'title', 'Automatic closure');
                $form->addElement('checkbox', 'auto_close', 'Enable  automatic "closed as not done" for compliance report');
                $autoCloseHelp = new tldOverlib('Enforce a compliance marker to follow up if the task is not completed during the defined period (note that the period is the defined frequency which means From open date to next Scheduled task). This will allow you to use a ST report and check if the compliance to a rule/standard/DMS is achieved.', [
                    'CAPTION' => 'Closed as not done',
                    'WIDTH' => '500',
                    'linkName' => 'See help'
                ]);
                $form->addElement('link', 'auto_close_help', $autoCloseHelp->fetch());

                $form->addElement('header', 'title', 'Reference document');
                $form->addElement('select', 'referencetype', 'Reference type', ['' => '','DMS' => 'DMS', 'GWF' => 'GWF']);
                $form->addElement('text', 'reference', 'Reference#');
                $form->addElement('submit', 'btnSubmit', 'Submit');
                // js popup link to display the form's fields definition onMouseOver
                $popupDef = new tldOverlib($help['Fields Definition'], [
                    'CAPTION' => 'Fileds Definition',
                    'WIDTH' => '500',
                    'linkName' => 'Fields Definitions'
                ]);
                $form->addElement('link', 'defs', $popupDef->fetch());
                // set the default values for the "new ST" form
                $date = new DateTime(date('Y-m-d'));
                $interval = new DateInterval('P1Y');
                $date->add($interval);
                $form->setDefaults([
                    'date_start' => date('Y-m-d'),
                    'escalation_trigger' => 60,
                    'date_end' => $date->format('Y-m-d'),
                    'assignor' => $user->getId(),
                    'assignee' => $user->getId()
                ]);

                // define the rules that will apply to the form

                // setup a custom rule to check the when dates using the function _checkWhenDates()
                $form::registerRule('checkWhenDates', 'callback', '_checkWhenDates');
                $form->addRule('when', 'incorrect date(s)-> check if enterred dates exist and that Start Date < or =  End Date', 'checkWhenDates');
                $form->addRule('escalation_trigger', 'Required', 'required');
                $form->addRule('escalation_trigger', 'Field is numeric', 'numeric');
                $form->addRule('reference', 'Field is numeric', 'numeric');
                $form::registerRule('maxvalue', 'function', 'max_value_f');
                $form->addRule('escalation_trigger', "Maximum value for Escalation factor is 60 days as per TLD rules", 'maxvalue');
                // add the rule for the repetition
                $ruleEvery['repd_val'][] = array(
                    'This is required',
                    'required'
                );
                $ruleEvery['repd_val'][] = array(
                    'numercal value only',
                    'numeric'
                );
                $ruleEvery['repd_val'][] = array(
                    'integter value only',
                    'nopunctuation'
                );
                $ruleEvery['repd_unit'][] = array(
                    'This is required',
                    'required'
                );
                $form->addGroupRule('every', $ruleEvery);

                // add the rule for the lead time
                $ruleLeadtime['leadtime_value'][] = array(
                    'numercal value only',
                    'numeric'
                );
                $ruleLeadtime['leadtime_value'][] = array(
                    'integter value only',
                    'nopunctuation'
                );
                $form->addGroupRule('leadtime', $ruleLeadtime);

                $form->addRule('when', 'This is required', 'required');
                $form->addRule('assignor', 'This is required', 'required');
                $form->addRule('assignee', 'This is required', 'required');
                $form->addRule('description', 'This is required', 'required');

                if (!$form->validate()) {
                    $body = '<p style="color: red">START DATE : Setup on 28 is recommended for end of month scheduled tasks, any setup on 29, 30, 31 will be reset to 1st day of the month to avoid the February issue where 29, 30, 31 could not happen sometimes</p>';
                    $body .= $form->toHTML();
                    break;
                }

                $header = tldUtils::cleanupFormInput($form->exportValues());
                if (! $header["assignor"]) {
                    $header["assignor"] = $user->getId();
                }
                $header["action"] = "st";
                // Check dates
                $start_date = new DateTime($header['date_start']);
                if ((int) $start_date->format('d') > 28 && 'MONTH' === $header['every']['repd_unit']) {
                    $start_date->modify('first day of next month');
                }
                $end_date = new DateTime($header['date_end']);
                if ($start_date > $end_date) {
                    $DEFAULT_ERROR[] = "START date can not be greater than END date";
                    $body = $form->toHTML();
                    break;
                }

                $header['auto_close'] = (bool) ($header['auto_close'] ?? false);
                if (true === $header['auto_close'] && '' === $header['reference']) {
                    $DEFAULT_ERROR[] = 'A reference DMS must be provided when the automatic task closure is enabled';
                    $body = $form->toHTML();
                    break;
                }

                $tasks = [];
                foreach ((array) $header['assignee'] as $id) {
                    $header['assignee'] = $id;
                    $tasks[] = tldST::insert($header);
                }

                foreach($tasks as $key => $error) {
                    if (!is_numeric($error)) {
                        $DEFAULT_ERROR[] = "Could not create new task. There was an error processing. The error returned is '$error'";
                        break;
                    }
                    // Make and send the email notification of the newly created ST to the assignee and assignor.
                    $st = new tldST($error);
                    $assignee = new tldUser($st->getAssignee());
                    $assigneeFullName = $assignee->getFullname();
                    $assignor = new tldUser($st->getAssignor());
                    $message = <<<EOF
    ST #$error has been assigned to $assigneeFullName \n<br>
    Please log in to the TLD-GSE intranet and go to the calendar module to view your open STs.\n<br>
    <a href="https://www.tld-gse.com/en/private/calendar/calendar.php?m[0]=st&m[1]=view&id=$error">Click here to see.</a>
    EOF;
                    $subject = "ST, New: #$error opened for " . $assigneeFullName . " by " . $assignor->getFullname();
                    // Send the email notification to assignee and assignor
                    $st->notifyAssignee($message, $subject);
                    // Create the task if need (if schedule starts today.)
                    $st->doTask();
                    $body .= <<<EOF
    <a href="$php_self?m[0]=st&m[1]=view&id=$error">ST# $error has been created, click here.</a><br/>
EOF;
                }
            break;
            case 'byID':
                $DEFAULT_TITLE .= "\By Num";
                $form = new HTML_QuickForm('frmByID', 'post');
                $form->addElement('hidden', 'm[0]', 'st');
                $form->addElement('hidden', 'm[1]', 'view');
                $form->addElement('text', 'id', 'ST Number');
                $form->addElement('submit', 'btnSubmit', 'Submit');
                $body = $form->toHTML();
                break;
            case 'byBUID':
                $DEFAULT_TITLE .= "\By Location";
                $form = new HTML_QuickForm('frmByBUID', 'post');
                $form->addElement('hidden', 'm[0]', 'st');
                $form->addElement('hidden', 'm[1]', 'STList');
                $form->addElement('hidden', 'm[2]', 'byBUID');
                $form->addElement('header', 'title', 'Get STs by location');
                $form->addElement('select', 'bu_id', 'Location', tldLocation::getLocationList("smartyOptions"));
                $form->addElement('submit', 'btnSubmit', 'Submit');
                $body = $form->toHTML();
                break;
        }
        break;
    case 'view':
        if (empty($id) || ! is_numeric($id)) {
            $DEFAULT_ERROR[] = "ERROR: ID sent empty or invalid...";
            break;
        }
        $st = new tldST($id);
        if ($st->isEmpty()) {
            $DEFAULT_ERROR[] = "ERROR: Could not find task #$id";
            break;
        }
        $DEFAULT_TITLE .= "\ST# $id";
        $DEFAULT_MENU .= <<<EOF
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<a href="$php_self?m[0]=st&m[1]=view&id=$id">General</a>&nbsp;|&nbsp;
<a href="$php_self?m[0]=st&m[1]=view&m[2]=tasks&id=$id">Tasks</a>&nbsp;|&nbsp;
<a href="$php_self?m[0]=st&m[1]=view&m[2]=links&id=$id">Links</a>
EOF;

        if ($user->isInGroup("gg_ADMIN") || $user->itsId == $st->getAssignor() || in_array($st->getAssignor(), $user->getAllSubordinatesID($user->itsId))) {
            $DEFAULT_MENU .= <<<EOF
&nbsp;|&nbsp;<a href="/en/private/calendar/st/st_admin.php?mode=record_view&form_type=main_tpl&id=$id">Edit</a>
EOF;
            $currentStatus = $st->getStatus();
            if ($currentStatus == "INACTIVE") {
                $newStatus = "ACTIVE";
                $DEFAULT_MENU_STATUS = <<<EOF
&nbsp;|&nbsp;<a href="$php_self?m[0]=st&m[1]=view&m[2]=changestatus&id=$id">Change Status</a>
EOF;
            } elseif ($currentStatus == "ACTIVE") {
                $newStatus = "INACTIVE";
                $DEFAULT_MENU_STATUS = <<<EOF
&nbsp;|&nbsp;<a href="$php_self?m[0]=st&m[1]=view&m[2]=changestatus&id=$id">Change Status</a>
EOF;
            }
            $DEFAULT_MENU .= $DEFAULT_MENU_STATUS;
        }

        $stInformation = $st->byID($id);
        $referenceBaseLink = match ($stInformation['referencetype']) {
            'DMS' => '/en/private/mis/mis.php?m[0]=help&m[1]=dms&id=',
            'GWF' => '/en/private/calendar/calendar.php?m[0]=gwf&m[1]=view&m[2]=&id=',
            default => null,
        };

        $report = new tldAssocTable($stInformation,
        [
            "id" => "ST#",
            "bu_fullname" => "BU id",
            "date_start" => "Start",
            "date_end" => "End",
            "repd_val" => "repetition value",
            "repd_unit" => "repetition unit",
            "leadtime_value" => "lead time value",
            "leadtime_unit" => "lead time unit",
            "escalation_trigger" => "Escalation Trigger(Days)",
            "assignor_fullname" => "Assignor",
            "assignee_fullname" => "Assignee",
            "type" => "Type",
            "description" => "Description",
            "referencetype" => "Reference Type",
            "reference" => "Reference#",
            "status" => "Status",
            'auto_close' => 'Close as not done',
        ],
        [
            "title" => "General",
            "links" => ["reference" => $referenceBaseLink]
        ]);
        $body = $report->fetch();

        switch ($m[2]) {
            case 'tasks':
                $sess["calendar"]["tasks"] = $st->getTasks();
                $form = new tldReportMultiLevel($sess["calendar"]["tasks"], array(
                    "status",
                    "due_date"
                ), array(
                    "id" => "Task#",
                    "status" => "Status",
                    "due_date" => "Due",
                    "task" => "Task",
                    "assignee_fullname" => "Assignee"
                ), array(
                    "passField" => "id",
                    "title" => "Tasks",
                    "url" => "/en/private/calendar/calendar.php?m[0]=tasks&m[1]=task&m[2]=view&id="
                ));
                $body = $form->fetch();
            break;
            case 'changestatus':
                $st->changeStatus($newStatus);
                $DEFAULT_ERROR[] = "the ST#" . $st->itsID . " is now $newStatus";
                $st->refresh();
                $header = $st->getHeader();
                $body = _getGeneralTab($header);
            break;
            case 'links':
                $DEFAULT_MENU .= <<<EOF
    <br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
    <a href="/en/private/common/index.php?m[0]=links&m[1]=form&m[2]=newLink&module=ST&parent_id=$id">New Link</a>
EOF;
                $report = new tldReportColumnar(
                    $st->getLinksFromHere(),
                    [
                        "xItems" => [
                            "id" => "ID#",
                            "type" => "Module",
                            "item" => "Ref#",
                            "dsca" => "Description",
                        ],
                        "title" => "Links FROM Here...",
                        "links" => [
                            "id" => "/en/private/common/index.php?m[0]=links&m[1]=view&id=",
                        ],
                    ]
                );
                $body .= $report->fetch();
                $report = new tldReportColumnar(
                    $st->getLinksToHere(),
                    [
                        "xItems" => [
                            "id" => "ID#",
                            "module" => "Module",
                            "parent_id" => "Ref#",
                            "dsca" => "Description",
                        ],
                        "title" => "Links TO Here...",
                        "links" => [
                            "id" => "/en/private/common/index.php?m[0]=links&m[1]=view&reversed=1&id=",
                        ],
                    ]
                );
                $body .= $report->fetch();
            break;
        }
        break;
    case "STList":
        $DEFAULT_TITLE .= "\STs Lists";
        $DEFAULT_MENU .= <<<EOF
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<a href="$php_self?m[0]=st&m[1]=STList">All My STs</a>
&nbsp;|&nbsp;<a href="$php_self?m[0]=st&m[1]=STList&m[2]=assigned">Assigned STs</a>
&nbsp;|&nbsp;<a href="$php_self?m[0]=st&m[1]=STList&m[2]=initiated">Initiated STs</a>
&nbsp;|&nbsp;<a href="$php_self?m[0]=st&m[1]=STList&m[2]=subordinate">Team member STs</a>
EOF;

        if ($user->isInGroup(tldGroup::getManagerGroups())) {
            $DEFAULT_MENU .= <<<EOF
&nbsp;|&nbsp;<a href="$php_self?m[0]=st&m[1]=forms&m[2]=byBUID">Location STs</a>
EOF;
        }
        $opts = [
            "includeInactive" => true
        ];

        switch ($m[2]) {
            case 'byUser':
                $uid = new tldUser($userid);
                if ($m[3] == 'includeInactive') {
                    $opts = [
                        "includeInactive" => false
                    ];
                    $_title = "All STs(ACTIVE ONLY), " . $uid->getFullname();
                } else {
                    $_title = "All STs, " . $uid->getFullname();
                }
                $rows = tldST::byUser($userid, $opts);
                $_link = "$php_self?m[0]=st&m[1]=STList&m[2]=byUser";
                break;
            case 'initiated':
                if ($m[3] == 'includeInactive') {
                    $opts = [
                        "includeInactive" => false
                    ];
                    $_title = "Initiated STs(ACTIVE ONLY)";
                } else {
                    $_title = "Initiated STs";
                }
                $rows = tldST::byAssignor($user->getId(), $opts);
                $_link = "$php_self?m[0]=st&m[1]=STList&m[2]=initiated";
                break;
            case 'assigned':
                if ($m[3] == 'includeInactive') {
                    $opts = [
                        "includeInactive" => false
                    ];
                    $_title = "Assigned STs(ACTIVE ONLY)";
                } else {
                    $_title = "Assigned STs";
                }
                $rows = tldST::byAssignee($user->getId(), $opts);
                $_link = "$php_self?m[0]=st&m[1]=STList&m[2]=assigned";

                break;
            case 'subordinate':
                $subs = $user->getSubordinates();
                $_title = "Team members STs";
                $_link = "$php_self?m[0]=st&m[1]=STList&m[2]=subordinate";
                if (count($subs)) {
                    $form = new tldHTMLList($subs, array(
                        "key" => array(
                            "userid" => "id"
                        ),
                        "value" => array(
                            "lastname",
                            "firstname"
                        )
                    ), "$php_self?m[0]=st&m[1]=STList&m[2]=byUser", array(
                        "title" => "Team members list"
                    ));
                    $body .= $form->fetch();
                } else {
                    $DEFAULT_ERROR[] = "No subordinate tasks found.";
                }
                break 2;
            case 'byBUID':
                if (! $user->isInGroup(tldGroup::getManagerGroups())) {
                    $DEFAULT_ERROR[] = "You do not have permissions...";
                    break;
                }
                if (empty($bu_id) || ! is_numeric($bu_id)) {
                    $DEFAULT_ERROR[] = "Parameters sent empty or invalid...";
                    break;
                }
                $opt = [
                    "cal_st.bu_id" => $bu_id
                ];
                if ($m[3] == 'includeInactive') {
                    $opt = [
                        "cal_st.bu_id" => $bu_id,
                        "status" => "ACTIVE"
                    ];
                    $_title = "STs by location (ACTIVE ONLY)";
                } else {
                    $_title = "STs by location";
                }
                $rows = tldST::byConstraints($opt);
                $_link = "$php_self?m[0]=st&m[1]=STList&m[2]=byBUID&bu_id=$bu_id";
                break;
            default:
                if ($m[3] == 'includeInactive') {
                    $opts = [
                        "includeInactive" => false
                    ];
                    $_title = "All My STs (ACTIVE ONLY)";
                } else {
                    $_title = "All My Active STs";
                }
                $_link = "$php_self?m[0]=st&m[1]=STList";
                $rows = tldST::byUser($user->getId(), $opts);
                break;
        }

        if (count($rows)) {
            $body = $smarty->fetch("calendar/st/homepage.st.tpl");
            $report = new tldReportMultiLevel(
            $rows,
            [
                "type"
            ],
            [
                "id" => "ST#",
                "bu_fullname" => "BU id",
                "date_start" => "Start",
                "date_end" => "End",
                "repd_val" => "repetition value",
                "repd_unit" => "repetition unit",
                "leadtime_value" => "lead time value",
                "leadtime_unit" => "lead time unit",
                "escalation_trigger" => "Escalation_trigger(Days)",
                "assignor_fullname" => "Assignor",
                "assignee_fullname" => "Assignee",
                "type" => "Type",
                "description" => "Description",
                "referencetype" => "Reference Type",
                "reference" => "Reference#",
                "status" => "Status"
            ],
            [
                "title" => $_title,
                "passField" => "id",
                "url" => "$php_self?m[0]=st&m[1]=view&id=",
                "links" => [
                    "reference" => "/en/private/mis/mis.php?m[0]=help&m[1]=dms&id="
                ]
            ]);
            $body .= <<<EOF
<a href="$_link&m[3]=includeInactive">ACTIVE ST ONLY</a>
&nbsp;|&nbsp;<a href="$_link">INCLUDE INACTIVE ST</a>
EOF;
            $body .= $report->fetch();
        } else {
            $body = $smarty->fetch("calendar/st/homepage.st.tpl");
            $body .= <<<EOF
<h3>$_title</h3><br>
<font color="#FF0000">No records found.
EOF;
        }
}

function max_value_f($element_name, $element_value)
{
    return $element_value <= 60;
}

// local function used to check the dates inputed in the "new ST" from
function _checkWhenDates($dt)
{
    // if start and end date are ok then return true
    if (checkdate($dt["date_start"]["m"], $dt["date_start"]["d"], $dt["date_start"]["Y"]) && checkdate($dt["date_end"]["m"], $dt["date_end"]["d"], $dt["date_end"]["Y"]) && (mktime(0, 0, 0, $dt["date_end"]["m"], $dt["date_end"]["d"], $dt["date_end"]["Y"]) >= mktime(0, 0, 0, $dt["date_start"]["m"], $dt["date_start"]["d"], $dt["date_start"]["Y"] . ""))) {
        return true;
    } else
        return false;
}

function _getGeneralTab($header)
{
    $report = new tldAssocTable($header,
    [
        "id" => "ST#",
        "bu_fullname" => "BU id",
        "date_start" => "Start",
        "date_end" => "End",
        "repd_val" => "repetition value",
        "repd_unit" => "repetition unit",
        "leadtime_value" => "lead time value",
        "leadtime_unit" => "lead time unit",
        "escalation_trigger" => "Escalation Trigger(Days)",
        "assignor_fullname" => "Assignor",
        "assignee_fullname" => "Assignee",
        "type" => "Type",
        "description" => "Description",
        "referencetype" =>"Reference Type",
        "reference" => "Reference#",
        "status" => "Status"
    ],
    [
        "title" => "General",
        "links" => ["reference" => "/en/private/mis/mis.php?m[0]=help&m[1]=dms&id="]
    ]);
    return $report->fetch();
}

?>
