<?php
// Get menu
$DEFAULT_MENU .=<<<EOF
<br/>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<a href="$php_self?m[0]=homepage&m[1]=default">Homepage</a>
&nbsp;|&nbsp; <a href="$php_self?m[0]=homepage&m[1]=user">My Dashboard</a>
&nbsp;|&nbsp; <a href="$php_self?m[0]=homepage&m[1]=user&m[2]=selectEngineer">Dashboard by Engineer</a>
&nbsp;|&nbsp; <a href="/en/private/calendar/calendar.php?m[0]=sub">Team</a>
EOF;

// Get ENG user
if (empty($m[2]) && $uid === 'all') {
    $m[2] = 'allEngineers';
}
$uid = ($uid > 0) ? (int)$uid : $user->getID();
$eng = new tldUser($uid);
if($eng->isEmpty()){
	$DEFAULT_ERROR[] = "Not a valid TLD user";
	return;
}
$is_eng = $eng->isInGroup(array("gg_ENG"));
$bu = new tldLocation($eng->getBUID());
$erp = $bu->getERP();

// Get body
switch($m[1]){
case 'user':
	switch($m[2]){
	case 'selectEngineer':
	    if(!$user->isInGroup(array("gg_ENG","role_ENG","gg_ADMIN"))){
	        $DEFAULT_ERROR[]="ERROR: You do not have permissions...";
	        break;
	    }
	    $engGrp = new tldGroup("gg_ENG", tldLocation::getERPByID($user->getBUID()));
	    if(!count($engGrp->getUserlist())){
	    	$engGrp = new tldGroup("gg_ENG");
	    }
	    $engineers = array_column($engGrp->getUserlist(), 'fullname', 'id');
        if ($user->isInGroup(['role_EM', 'role_RME', 'role_ES'])) {
            $engineers = ['all' => 'ALL ENGINEERS'] + $engineers;
        }
	    $form = new HTML_QuickForm('frmSelectUser', 'get');
	    $form->addElement(  'header', 'title', 'Select Engineer');
	    $form->addElement(  'hidden', 'm[0]', 'homepage');
	    $form->addElement(  'hidden', 'm[1]', 'user');
	    $form->addElement(  'select', 'uid', 'Engineer', [''=>''] + $engineers);
	    $form->addElement(  'submit', 'btnSubmit', 'Submit');
	    $form->addRule('uid', 'This is required', 'required');
	    $body .= $form->toHTML();
	break;
	case 'allEngineers':
            if (!$user->isInGroup(['role_EM', 'role_RME', 'role_ES'])) {
                $DEFAULT_ERROR[] = "ERROR: You do not have permissions...";
                break;
            }
            $engGrp = new tldGroup("gg_ENG", tldLocation::getERPByID($user->getBUID()));
            if (count($engGrp->getUserlist()) <= 0) {
                // The old behavior was to load all engineers, i'll restrict that for now
                // $engGrp = new tldGroup("gg_ENG");
                $DEFAULT_ERROR[] = "ERROR: There no engineers attached to your BU.";
                break;
            }
            $engineers = tldUtils::optionsByKeyValue($engGrp->getUserlist(), "id", "fullname");
            $cells = [];
            foreach ($engineers as $engId => $engname) {

                $USER_BODY = <<<EOF
		<h3>{$engname} Engineering Dashboard</h3>
		<p>
			<a href="/en/private/calendar/calendar.php?mode=&id={$engId}">Task Scheduler</a>
			&nbsp;|&nbsp; <a href="/en/private/calendar/calendar.php?m[0]=tasks&m[1]=listing&m[2]=byUserByClosedPeriod&uid={$engId}">Recently Closed</a>
		</p>
EOF;
                $cells[] = $USER_BODY;
                $query = <<<EOF
SELECT module,
    CASE
        WHEN DATEDIFF(due_date, NOW()) < -60 THEN 'G'
        WHEN DATEDIFF(due_date, NOW()) BETWEEN -60 AND -15 THEN 'F'
        WHEN DATEDIFF(due_date, NOW()) BETWEEN -14 AND -8 THEN 'E'
        WHEN DATEDIFF(due_date, NOW()) BETWEEN -7 AND 0 THEN 'D'
        WHEN DATEDIFF(due_date, NOW()) BETWEEN 1 AND 7 THEN 'C'
        WHEN DATEDIFF(due_date, NOW()) BETWEEN 8 AND 14 THEN 'B'
        WHEN DATEDIFF(due_date, NOW()) > 14 THEN 'A'
    END AS dueid
FROM tasks
WHERE status<>'CLOSED' AND assignee={$engId}
EOF;

                $tcnt = [];
                foreach (tldUtils::getSqlToAssocArray($query) as $task) {
                    switch ($task['dueid']) {
                        case 'A':
                            $due = 'Due &gt;14';
                            break;
                        case 'B':
                            $due = 'Due &lt;14';
                            break;
                        case 'C':
                            $due = 'Due &lt;7';
                            break;
                        case 'D':
                            $due = 'Late &lt;7';
                            break;
                        case 'E':
                            $due = 'Late &lt;14';
                            break;
                        case 'F':
                            $due = 'Late &lt;60';
                            break;
                        case 'G':
                            $due = 'Late &gt;60';
                            break;
                        default:
                            $due = '';
                            break;
                    }

                    $tcnt[$task['module']][$task['dueid']]['mod'] = $task['module'];
                    $tcnt[$task['module']][$task['dueid']]['due'] = $due;
                    $tcnt[$task['module']][$task['dueid']]['num'] += 1;
                }
                $tasks_due = [];
                foreach ($tcnt as $v1 ) {
                    foreach ($v1 as $v2) {
                        $tasks_due[] = $v2;
                    }
                }
                $report = new tldMatrix(
                    $tasks_due,
                    "due", "mod", "num",
                    "/en/private/calendar/calendar.php?m[0]=tasks&m[1]=taskList&m[2]=byDueByMod&userid={$engId}",
                    "Open Tasks Count",
                    [
                        "xItems" => ['Late &gt;60', 'Late &lt;60', 'Late &lt;14', 'Late &lt;7', 'Due &lt;7', 'Due &lt;14', 'Due &gt;14'],
                    ]
                );
                $cells[] = str_replace(['%26lt%3B', '%26gt%3B'], ['%3C', '%3E'], $report->fetch());

            }
            $report = new tldHTMLTable(
                $cells,
                [
                    "cols" => 2,
                    "attribs" => ["table" => " width='100%'", "tr" => " bgcolor='#FFFFFF'"],
                ]
            );
            $body .= $report->fetch();
            break;
        default:
            // USER HOMEPAGE ------------------------------------------------>

		$cells = array();

		$USER_BODY = <<<EOF
		<h3>{$eng->getFullname()} Engineering Dashboard</h3>
		<p>
			<a href="/en/private/calendar/calendar.php?mode=&id={$uid}">Task Scheduler</a>
			&nbsp;|&nbsp; <a href="/en/private/calendar/calendar.php?m[0]=tasks&m[1]=listing&m[2]=byUserByClosedPeriod&uid={$uid}">Recently Closed</a>
			&nbsp;|&nbsp; <a href="/en/private/calendar/calendar.php?m[0]=tasks&m[1]=taskList">Task List</a>
		</p>
		<img width="100" src="/en/private/uploads/{$eng->getPhotoFilename()}" />
EOF;
		$cells[] = $USER_BODY;
		$query = <<<EOF
		SELECT id, module, parent_id, task, due_date,
		CASE
			WHEN DATEDIFF(due_date, NOW()) < -60 THEN 'G'
			WHEN DATEDIFF(due_date, NOW()) BETWEEN -60 AND -15 THEN 'F'
			WHEN DATEDIFF(due_date, NOW()) BETWEEN -14 AND -8 THEN 'E'
			WHEN DATEDIFF(due_date, NOW()) BETWEEN -7 AND 0 THEN 'D'
			WHEN DATEDIFF(due_date, NOW()) BETWEEN 1 AND 7 THEN 'C'
			WHEN DATEDIFF(due_date, NOW()) BETWEEN 8 AND 14 THEN 'B'
			WHEN DATEDIFF(due_date, NOW()) > 14 THEN 'A'
		END AS dueid
		FROM tasks
		WHERE status<>'CLOSED' AND assignee={$uid}
		ORDER BY due_date ASC
EOF;
		$tasks = tldUtils::getSqlToAssocArray($query);
		$tcnt = $engTasks = [];
		$partition = ['EAP' => [], 'MEAP' => []];
		foreach($tasks as $task){
			switch($task['dueid']){
			case 'A': $due = 'Due &gt;14'; break;
			case 'B': $due = 'Due &lt;14'; break;
			case 'C': $due = 'Due &lt;7'; break;
			case 'D': $due = 'Late &lt;7'; break;
			case 'E': $due = 'Late &lt;14'; break;
			case 'F': $due = 'Late &lt;60'; break;
			case 'G': $due = 'Late &gt;60'; break;
			default: $due = ''; break;
			}
            if (in_array($task['module'], ['EAP', 'MEAP'])) {
                $engTasks[] = array_merge($task, ['due' => $due]);
                if( !in_array($task['parent_id'], $partition[$task['module']])) {
                    $partition[$task['module']][] = $task['parent_id'];
                }
            }
			$tcnt[$task['module']][$task['dueid']]['mod'] = $task['module'];
			$tcnt[$task['module']][$task['dueid']]['due'] = $due;
			$tcnt[$task['module']][$task['dueid']]['num'] += 1;
		}
		foreach($tcnt as $v1) foreach($v1 as $v2) $tasks_due[] = $v2;
		$report = new tldMatrix(
		    $tasks_due,
		    "due", "mod", "num",
		    "/en/private/calendar/calendar.php?m[0]=tasks&m[1]=taskList&m[2]=byDueByMod&userid={$uid}",
		    "Open Tasks Count",
		    array(
		    	"xItems" => array('Late &gt;60','Late &lt;60','Late &lt;14','Late &lt;7','Due &lt;7','Due &lt;14','Due &gt;14'),
		    )
		);
	    $cells[] = str_replace(array('%26lt%3B','%26gt%3B'),array('%3C','%3E'),$report->fetch());
		$USER_BODY = <<<EOF
		<h3>Sequences</h3>
		<ul>
EOF;
		if($user->isInGroup(array("gg_ENG", "gg_SUPPORT"))){
		    $USER_BODY.= <<<EOF
		  <li><a href="/en/private/calendar/calendar.php?m[0]=seq&m[1]=new&m[2]=eng.newpartnb">Start new part number sequence</a></li>
EOF;
		}
		$USER_BODY.= <<<EOF
		  <li><a href="/en/private/manufacturing/eng/dev.php?m[0]=searchpn&m[1]=search">Search new part number sequence by Part number</a></li>
		</ul>
EOF;
		$cells[] = $USER_BODY;
	    $query = <<<EOF
	    SELECT '_USER_' AS location,
	    CONCAT('Step ',T1.cur_step) AS step,
	    COUNT(*) AS num
	    FROM tasks AS T1
	    WHERE T1.tplno=22 AND T1.status <> 'CLOSED' AND T1.assignor={$uid}
	    GROUP BY location, step
EOF;
        $seqr = array_merge(tldTask::countByNewItemSEQ(['erp' => $bu->getERP()]), tldUtils::getSqlToAssocArray($query));
        $form = new tldMatrix(
            $seqr,
            "location", "step", "num",
            "$php_self?m[0]=reports&m[1]=listing&m[2]=SEQReport",
            "SEQ New Item Count",
            ['doNotShowYTotals' => true]
        );
        // manipulate username link
		$html = $form->fetch();
		$html = str_replace("&x=_USER_","&x=UID{$eng->getID()}",$html);
		$html = str_replace("_USER_",$eng->getFullname(),$html);
		$cells[] = $html;

            $report = new tldHTMLTable(
                $cells,
                array(
                    "cols"=>2,
                    "attribs"=>array("table"=>" width='100%'","tr"=>" bgcolor='#FFFFFF'")
                )
            );
            $body .= $report->fetch();
            $tasks = $engTasks;

            // Create a Mapping of EAP ids => MEAP ids
            $eapParentMapping = [];
            foreach ($partition['EAP'] as $id) {
                $eap =  new tldEAP($id);
                if (0 === (int)$eap->getParentID()) {
                    $eapParentMapping[$id] = null;
                    continue;
                }
                $parent = $eap->getParentModuleIDFamily();
                if ($parent['module'] !== 'MEAP') {
                    $eapParentMapping[$id] = null;
                    continue;
                }
                $eapParentMapping[$id] = $parent['id'];
            }

            // Get MEAP description
            $meaps = [];
            $meapIds = implode(',', array_unique(array_merge(array_filter($eapParentMapping), $partition['MEAP'])));
            if ($meapIds !== ""){
                $query = <<<SQL
            SELECT
              id,
              CONCAT('MEAP#', id, ' - ', short_desc) AS description         
            FROM meap
            WHERE id IN ($meapIds)
            ORDER BY id
SQL;
                $meaps = array_column(tldUtils::getSqlToAssocArray($query), 'description', 'id');
            }
            $tcnt = $tasksDue = [];
            foreach ($tasks as $key => &$task) {
                $meapDesignation = $task['module'] === 'MEAP' ? $meaps[$task['parent_id']] : $meaps[$eapParentMapping[$task['parent_id']]];
                # Task not finally related to MEAPs should be filtered out
                if (null === $meapDesignation) {
                    unset($tasks[$key]);
                    continue;
                }
                $task['meapDesignation'] = $meapDesignation;

                $tcnt[$meapDesignation][$task['dueid']]['meap'] = $meapDesignation;
                $tcnt[$meapDesignation][$task['dueid']]['due'] = $task['due'];
                $tcnt[$meapDesignation][$task['dueid']]['num'] += 1;
            }
            foreach ($tcnt as $v1) foreach ($v1 as $v2) $tasksDue[] = $v2;

            $form = new tldMatrix(
                $tasksDue,
                "due", "meap", "num",
                "",
                "Task Count By MEAP",
                [
                    "yItems" => array_values($meaps),
                    "xItems" => ['Late &gt;60', 'Late &lt;60', 'Late &lt;14', 'Late &lt;7', 'Due &lt;7', 'Due &lt;14', 'Due &gt;14'],
                    "doNotShowXTotals" => true,
                ]
            );
            $cells = [$form->fetch()];

            $options = '';
            $init = false;
            foreach ($meaps as $key => $value) {
                $options .= '<option value="'.$key.'">'.$value.'</option>';
                $containers .= '<div id="meaplist'.$key.'" '.($init ? ' style="display:none;"' : '').'>';
                $init = true;

                $list = new tldReportColumnar(
                    array_filter($tasks, function ($task) use ($value) { return $task['meapDesignation'] === $value; }),
                    array(
                        "xItems"=>array(
                            "id"=>"ID",
                            "task"=>"Task Description",
                            "due_date"=>"Due Date"
                        ),
                        "sortable"=>"false",
                        "links"=>array("id"=>"/en/private/calendar/calendar.php?m[0]=tasks&m[1]=task&m[2]=view&id=")
                    )
                );
                $containers .= $list->fetch();
                $containers .= '</div>';
            }

            if (count($meaps) > 0) {
                $cells[] = <<<EOF
<h3>Task List By MEAP</h3>
<strong>Select MEAP:</strong>
<select id="meap-select">{$options}</select>
{$containers}
EOF;
            }

            $report = new tldHTMLTable(
                $cells,
                array(
                    "cols"=>1,
                    "attribs"=>array("table"=>" width='100%'","tr"=>" bgcolor='#FFFFFF'")
                )
            );
            $body .= $report->fetch();
            /// stopanto

		$body .= <<<EOF
<script type="text/javascript">
    $(document).ready(function() {
        $('#meap-select').change(function(){
            $('[id*="meaplist"]').hide();
            $('#meaplist'+this.value).show();
            $(this).blur();
        });
    });
</script>
EOF;
	break;
	}
break;
case 'default':
default:
	$cells = array();

	// COMMON HOMEPAGE ------------------------------------------------>

	// Wording and links
	$COMMON_BODY = $smarty->fetch("$PATH/homepage.eng.tpl");
	$COMMON_BODY.= <<<EOF
	<h3>Sequences</h3>
	<ul>
EOF;
	if($user->isInGroup(array("gg_ENG", "gg_SUPPORT"))){
	    $COMMON_BODY.= <<<EOF
	  <li><a href="/en/private/calendar/calendar.php?m[0]=seq&m[1]=new&m[2]=eng.newpartnb">Start new part number sequence</a></li>
EOF;
	}
	$COMMON_BODY.= <<<EOF
	  <li><a href="/en/private/manufacturing/eng/dev.php?m[0]=searchpn&m[1]=search">Search new part number sequence by Part number</a></li>
	</ul>
EOF;
	$cells[] = $COMMON_BODY;

	$rightCell = '';

	// Matrix
	$form = new tldMatrix(
	    tldTask::countByNewItemSEQ(),
	    "location", "step", "num",
	    "$php_self?m[0]=reports&m[1]=listing&m[2]=SEQReport",
	    "SEQ New Item COUNT by Step, Location"
	);
	$rightCell.= $form->fetch();

    global $kernel;

    $client = $kernel->getContainer()->get(\ApiBundle\Client::class);
    $templating = $kernel->getContainer()->get('twig.legacy');

    try {
        $statusByBusinessUnit = $client->get('report', [
            'query' => [
                'resource' => '/quality/first_article_qualifications',
                'x' => 'location.name',
                'y' => 'status',
            ],
        ]);
    } catch (\Symfony\Component\HttpClient\Exception\ClientException $e) {
        // do nothing
    }

    $rightCell.= '<h3>FAQ by location, status</h3>';

    $rightCell.= $templating->render('helper/_matrix_table.html.twig', [
        'data' => $statusByBusinessUnit,
        'link' => [
            'route' => 'first_article_qualifications_search',
            'xParam' => 'location',
            'yParam' => 'status'
        ],
        'headers' => [
            'columns' => ['PENDING', 'IN_PROGRESS', 'CONDITIONAL', 'QUALIFIED', 'REJECTED'],
        ],
        'filters' => [
            'columnHeaders' => "replace({'_': ' '})",
        ],
    ]);

    $cells[] = $rightCell;

        // DISPLAY ------------------------------------------------>

	$report = new tldHTMLTable(
	    $cells,
	    array(
	        "cols"=>2,
	        "attribs"=>array("table"=>" width='100%'","tr"=>" bgcolor='#FFFFFF'"),
	        "title"=>"ENGINEERING DASHBOARD"
	    )
	);
	$body .= $report->fetch();
break;
}

