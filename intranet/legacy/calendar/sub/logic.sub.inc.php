<?php
$DEFAULT_TITLE .= "/Team Calendar Status";

$subs = $user->getSubordinates();
if($subs){
	$DEFAULT_MENU.=<<<EOF
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<a href="$php_self?m[0]=tasks&m[1]=form&m[2]=byUser" title="Search by user">Search by user</a> |
<a href="$php_self?m[0]=tasks&m[1]=listing&m[2]=myTeamByClosedPeriod" title="Recently closed for my team">Recently closed for my team</a>
EOF;
}
$body .= renderSubordinate($user);

foreach($subs as $sub){
    $subordinate = new tldUser($sub['id']);
    $body .= renderSubordinate($subordinate, 1);
    foreach($subordinate->getSubordinates() as $subSub){
        $body .= renderSubordinate(new tldUser($subSub['id']), 2);
    }
}

function renderSubordinate(\tldUser $tldUser, $level = 0) {
    $render = '';
    $style = sprintf('padding-left: %spx; margin: 15px 0;', $level * 50);
    $photo = $tldUser->getPhotoFilename() ? '/en/private/uploads/'.$tldUser->getPhotoFilename() : '/shared/no_photo.jpg';
    $USER_BODY = <<<EOF
    <table style="$style">
  <tr>
    <td style="padding: 20px 10px 0 0">
        <img width="100" src="$photo">
    </td>
    <td>
		<h3>{$tldUser->getFullname()} Task Dashboard</h3>
		<p>
			<a href="/en/private/calendar/calendar.php?mode=&id={$tldUser->getID()}">Task Scheduler</a>
			&nbsp;|&nbsp; <a href="/en/private/calendar/calendar.php?m[0]=tasks&m[1]=listing&m[2]=byUserByClosedPeriod&uid={$tldUser->getID()}">Recently Closed</a>
		</p>
EOF;
    $render.= $USER_BODY;
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
WHERE status<>'CLOSED' AND assignee={$tldUser->getID()}
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
        "/en/private/calendar/calendar.php?m[0]=tasks&m[1]=taskList&m[2]=byDueByMod&userid={$tldUser->getID()}",
        "Open Tasks Count",
        [
            "xItems" => ['Late &gt;60', 'Late &lt;60', 'Late &lt;14', 'Late &lt;7', 'Due &lt;7', 'Due &lt;14', 'Due &gt;14'],
        ]
    );
    $render.= str_replace(['%26lt%3B', '%26gt%3B'], ['%3C', '%3E'], $report->fetch());
    $render.=<<<EOF
    </td>
  </tr>
</table>
EOF;

    return $render;
}

?>
