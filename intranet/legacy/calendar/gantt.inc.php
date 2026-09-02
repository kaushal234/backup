<?php
$JS_INCLUDE=array("/shared/javascript/overlib/overlib.js");
$DEFAULT_MENU .=<<<EOF
<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<a href="$php_self?m[0]=tasks&m[1]=taskList" title="Display list of open tasks">Task Lists</a>
&nbsp;|&nbsp;<a href="$php_self?m[0]=tasks&m[1]=form&m[2]=newTask" title="Create a brand new task for a team member, peer or supervisor">New USER Task</a>
EOF;
if(!$user->isAgent()) {
	$DEFAULT_MENU .=<<<EOF
&nbsp;|&nbsp;<a href="$php_self?m[0]=tasks&m[1]=form&m[2]=byNumber" title="I have the TASK#">By Num</a>
&nbsp;|&nbsp;<a href="$php_self?m[0]=tasks&m[1]=listing&m[2]=byRefNum" title="I have the reference number related to the task">By Ref Num</a>
&nbsp;|&nbsp;<a href="$php_self?m[0]=tasks&m[1]=listing&m[2]=byUserSearch" title="Search my related tasks for something...">Search</a>
&nbsp;|&nbsp;<a href="$php_self?m[0]=tasks&m[1]=reports" title="List available reports">Reports</a>
&nbsp;|&nbsp;<a href="$php_self?m[0]=tasks&m[1]=form&m[2]=advSearch">Advanced Search</a>
EOF;
}

$DEFAULT_MENU .=<<<EOF
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<a href="$php_self?mode=&id=$user->itsID" title="Calendar Homepage">My List</a>
&nbsp;|&nbsp;<a href="$php_self?m[0]=dashboard">My Dashboard</a>
&nbsp;|&nbsp;<a href="$php_self?mode=ical&id=$user->itsID" title="Download all tasks as ical">Download</a>
&nbsp;|&nbsp;<a href="$php_self?m[0]=edit&id=$user->itsID" title="Prioritize my tasks">Maintain MyTasks</a>
EOF;
switch($m[0]){

case 'sav':
    if(count($_REQUEST['ifactor'])){
        foreach($_REQUEST['ifactor'] as $taskId=>$if){
            $task = new tldTask($taskId);
            if($task->getAssignee() == $user->getID()){
                if($task->getIFactor()<>$if){
                    $e = $task->setIFactor($if);
                    if(is_string($e)){
                        $DEFAULT_ERROR[] = $e;
                    }
                }
            }else{
                $DEFAULT_ERROR[] = "ERROR: Only the assignee can change the ifactor of task# $taskId";
            }
        }
    }
    foreach($_REQUEST['tasks'] ?? [] as $taskId => $dates){
        if ($dates['dcomp'] === '' && $dates['due_date'] === '') {
            continue;
        }
        $task = new tldTask($taskId);

        if($task->getAssignee() !== $user->getID()){
            $DEFAULT_ERROR[] = "ERROR: Only the assignee can change the Completion Date of task# $taskId";
            continue;
        }

        if ($dates['due_date'] !== '' && $task->itsHeader['due_date'] !== $dates['due_date']) {
            $e = $task->reschedule($dates['due_date']);
            if(is_string($e)){
                $DEFAULT_ERROR[] = $e;
            }
        }

        if ($dates['dcomp'] !== '' && $task->getDComp() !== $dates['dcomp']) {
            $e = $task->setDComp($dates['dcomp']);
            if(is_string($e)){
                $DEFAULT_ERROR[] = $e;
            }
        }
    }
break;
}

if(isset($_REQUEST['orderby'])){
    $sess['calendar']['gantt']['orderby'] = $_REQUEST['orderby'];
}
if(isset($_REQUEST['id'])){
    $sess['calendar']['gantt']['id'] = $_REQUEST['id'];
}
if(isset($_REQUEST['mode'])){
    //only allowed modes
    if(in_array($_REQUEST['mode'], array("tts","user"))
            || empty($_REQUEST['mode'])){
        //if we are changing mode then also reset to home position
        $sess['calendar']['gantt']['mode'] = $_REQUEST['mode'];
    }
}
if(in_array($_REQUEST['shift'], array("h", "b", "f"))){
    switch($_REQUEST['shift']){
    case 'b':
        $sess['calendar']['gantt']['range_start'] =
            $sess['calendar']['gantt']['range_start'] - 7;
    break;
    case 'f':
        $sess['calendar']['gantt']['range_start'] =
            $sess['calendar']['gantt']['range_start'] + 7;
    break;
    default:
        $sess['calendar']['gantt']['range_start'] = -7;
    }
}
if(empty($a)){
	$a = 'DESC';
}

if(isset($_REQUEST['orderby'])
		&& in_array($_REQUEST['orderby'],
				array("module","parent_id","cat", "dcomp", "est"))){
		if($_REQUEST['sort'] == 'ASC'){
			$a = 'DESC';
		}else{
			$a = 'ASC';
		}
}

if(empty($ORDERBY)){
	$ORDERBY = "cat DESC";
}

switch($sess['calendar']['gantt']['orderby']){
case 'module':
    $ORDERBY = "t1.module $a";
break;
    case 'IFactor':
        $ORDERBY = " t1.ifactor $a";
        break;
case 'cat':
    $ORDERBY = " t1.cat $a";
break;
case 'dcomp':
    $ORDERBY = "dcomp $a";
break;
case 'parent_id':
	$ORDERBY = "t1.parent_id $a";
break;
case 'est':
    $ORDERBY = " t1.hours $a";
break;
default:
    $ORDERBY = "t1.due_date ASC";
}
ob_start();

//by default, set to sess var
$id = $sess['calendar']['gantt']['id'];

switch($sess['calendar']['gantt']['mode']){
case 'tts':
    if(empty($id)){
        $DEFAULT_ERROR[] = "ERROR: no TTS project id given...";
        return;
    }
    $WHERE = " t1.module='TTS' AND t1.parent_id=$id";
    $DEFAULT_TITLE .= "\\TTS#$id";
break;
case 'module':
    if(empty($id)){
        $DEFAULT_ERROR[] = "ERROR: no module name given...";
        return;
    }
    $WHERE = "  t1.status<>'CLOSED' AND t1.module='$id'";
    $DEFAULT_TITLE .= "\\Module $id";
break;
default:
    $listUser = new tldUser($id);
    if(empty($id)){
        $id = $user->getID();
    }elseif($id <> $user->getID() && $listUser->getSupervisor() <> $user->getID() && !in_array($id,$user->getAllSubordinatesID($user->getID()))){
        $DEFAULT_ERROR[] = "ERROR: Only the supervisor or owner of these tasks can view the list...";
        return;
    }
    $WHERE = " t1.status<>'CLOSED' AND t1.assignee=$id";
    $myuser = new tldUser($id);
    $DEFAULT_TITLE .= "\\".$myuser->getFullname();
}
$RANGE = 37;
if(!isset($sess['calendar']['gantt']['range_start'])
        || $sess['calendar']['gantt']['range_start'] < -1000
        || $sess['calendar']['gantt']['range_start'] > 1000){
    $range_start = -7;
}else{
    $range_start = $sess['calendar']['gantt']['range_start'];
}
$range_end = $range_start + $RANGE;

$mod_links = tldUtils::getModLinks();
$query =<<<EOF
select
	t1.*,
    CASE WHEN t1.dcomp='0000-00-00' AND due_date > NOW() THEN due_date WHEN t1.dcomp='0000-00-00' THEN NULL ELSE t1.dcomp END AS dcomp,
    t2.lastname AS assignor_lastname,
    CONCAT(t2.lastname,', ',t2.firstname) AS assignor_fullname,
    t3.lastname AS assignee_lastname,
    CONCAT(t3.lastname,', ',t3.firstname) AS assignee_fullname,
	to_days(t1.date) as open_days,
	to_days(t1.date)-to_days(now()) as open_days_rel,
	to_days(t1.due_date) as due_days,
	to_days(t1.due_date)-to_days(now()) as due_days_rel,
	to_days(if(t1.dcomp='0000-00-00', due_date, t1.dcomp)) as dcomp_days,
	to_days(if(t1.dcomp='0000-00-00', due_date, t1.dcomp))-to_days(now()) as dcomp_days_rel,
    (SELECT
        GROUP_CONCAT(concat(date,'\n-',comment)
        ORDER BY tasks_comments.id DESC SEPARATOR "\n----------------------------------------\n")
     FROM tasks_comments
     WHERE tasks_comments.parent_id=t1.id
    ) AS log_list,
     if((SELECT
     count(*)
     FROM mis_tts
     WHERE t1.parent_id=mis_tts.id)>0,
     (SELECT
     mis_tts.problem
     FROM mis_tts
     WHERE t1.parent_id=mis_tts.id), CONCAT(t1.module,'#',t1.parent_id)) AS category
FROM tasks as t1
    LEFT JOIN people AS t2 ON t1.assignor=t2.id
    LEFT JOIN people AS t3 ON t1.assignee=t3.id
where
    $WHERE
ORDER BY
    $ORDERBY
EOF;
$rows = tldUtils::getSqlToAssocArray($query);
if(count($rows) == 0){
    $DEFAULT_ERROR[] = "WARNING: no tasks found";
    return;
}

if($_REQUEST['mode'] == 'ical'){
    $cal = new tldCAL(
        "TasksList",
        "taskslist".$user->getID(),
        "TLD Tasks List"
    );
    foreach($rows as $row){
        $task = new tldTask($row['id']);
        $cal->addEvent($task->asVTODO());
    }
    $cal->out();
    exit;
}

//cache the tasks for browsing
$sess["calendar"]["tasks"] = $rows;
//get all related comments
$query=<<<EOF
SELECT
    t1.id,
    GROUP_CONCAT(tasks_comments.comment SEPARATOR "\n-----------------------\n") AS log_list,
    to_days(tasks_comments.date)-to_days(NOW()) AS date_days_rel
FROM tasks AS t1
    join tasks_comments on t1.id=tasks_comments.parent_id
WHERE
    $WHERE
GROUP BY t1.id, date_days_rel
EOF;
$task_comments = tldUtils::getSqlToAssocArray($query);
$logs = [];
if(count($task_comments)){
    foreach($task_comments as $task_comment){
        $logs[$task_comment['id']][$task_comment['date_days_rel']] = $task_comment['log_list'];
    }
}

$editMode = $m[0] ?? '' === 'edit';
$superuser = $user->isInGroup('superuser');
?>
<style type="text/css" media="all">
tr{
border:3px solid #FFFFFF;
}
</style>
<a href="<?= $php_self?>?shift=b">
    <img src="/shared/bluesphere/32x32/actions/1leftarrow.png" title="Back">
</a>&nbsp;&nbsp;&nbsp;
<a href="<?= $php_self?>?shift=h">
    <img src="/shared/bluesphere/32x32/actions/gohome.png" title="Today">
</a>&nbsp;&nbsp;&nbsp;
<a href="<?= $php_self?>?shift=f">
    <img src="/shared/bluesphere/32x32/actions/1rightarrow.png" title="Forwards">
</a>
Relative position in Days <?= $sess['calendar']['gantt']['range_start'] + 7?>
<?php if($editMode):?>
    <form action="<?=$php_self?>">
        <input type="hidden" name="m[0]" value="sav">
<?php endif;?>

<table style="border-collapse: collapse; width: 100%">
    <tr bgcolor="#CCCCCC">
        <td title="Order by due date">
            <a href="<?= $php_self?>?orderby=">ID</a></td>
        <td title="Status">
            <img src="/shared/bluesphere/16x16/actions/idea.png"></td>
        <td title="Order by module type">
            <a href="<?= $php_self?>?orderby=module&sort=<?= $a?>">Mod</a></td>
        <td title="Linked to document id">
        	<a href="<?= $php_self?>?orderby=parent_id&sort=<?= $a?>">Ref</a></td>
        <td title="Personal task IF">
            <a href="<?= $php_self?>?orderby=IFactor&sort=<?= $a?>">IF</a></td>
        <?php if(!$editMode):?>
        <td title="Personal task Category">
            <a href="<?= $php_self?>?orderby=cat&sort=<?= $a?>">Cat</a></td>
        <?php endif;?>
        <td title="Personal task completion date">
            <a href="<?= $php_self?>?orderby=dcomp&sort=<?= $a?>">Comp</a></td>
        <?php if($editMode && $superuser):?>
        <td title="Due date"><a href="<?= $php_self?>?orderby=due_date&sort=<?= $a?>">Due date</a></td>
        <?php endif; ?>
        <?php if(!$editMode):?>
        <td title="Estimation time (in Hours)">
            <a href="<?= $php_self?>?orderby=est&sort=<?= $a?>">Est</a></td>
        <td title="View full task log">
            <img src="/shared/bluesphere/16x16/actions/toggle_log.png"></td>
        <td title="Add Comment">
            <img src="/shared/bluesphere/16x16/actions/mail_generic.png"></td>
        <td title="Reschedule this task">
            <img src="/shared/bluesphere/16x16/actions/1day.png"></td>
        <td title="Transfer this task">
            <img src="/shared/bluesphere/16x16/actions/mail_forward.png"></td>
        <td title="Close this task">
            <img src="/shared/bluesphere/16x16/actions/no.png"></td>
        <?php endif;?>
        <td title="Assignor">
            <img src="/shared/bluesphere/16x16/actions/launch.png"></td>
        <td title="Assignee">
            <img src="/shared/bluesphere/16x16/apps/personal.png"></td>
        <?php for ($i=$range_start; $i<$range_end; $i++):?>
            <td width="5" style="<?php  if(in_array($i, array(7, 14, 30))):?>border: 1px solid #0000FF<?php endif;?>" bgcolor="<?= $i==0 ? "#66CCFF" : ($i%7==0 ? "#FF000" : "");?>" title="<?= $i?> Days">
                &nbsp
            </td>
        <?php  endfor;?>
        <td title="Problem Description">Description</td>
    </tr>
<?php  foreach($rows as $rownum=>$row):?>
    <tr bgcolor="<?= $rownum%2==0 ? "#FFFFFF" : "#CCCCCC";?>">
        <td onMouseOver="javascript:overlib(
        		'<?= htmlentities(TldDatabase::escape(nl2br($row['task']))) ?>',
				WIDTH, 350, OFFSETX, 50, VAUTO, FGCOLOR, '#eeeeee', BGCOLOR, 'gray', CAPCOLOR, '#dedede');"
			onMouseOut="javascript:nd();">
            <a href="<?= $php_self?>?m[0]=tasks&m[1]=task&&m[2]=view&id=<?= $row['id'];?>">
            <?= $row['id'];?></a>
        </td>
        <td title="<?= $row['status']."\n"?><?= $row['due_days_rel'] < -30 ? "WARNING: Due date is out of range.\n" : "";?>
<?= $row['due_days_rel'] < 0 ?"WARNING: Task is late by ".(-1*$row['due_days_rel'])." days\n":"";?>
Opened:<?= $row['date']."\n";?> Due Date:<?= $row['due_date'];?>"
            bgcolor="<?= ($row['status']=='CLOSED') ? "#FF0000" : "";?>">
            <img src="/shared/bluesphere/16x16/actions/idea.png">
        </td>
        <td><?= $row['module'];?></td>
        <td onMouseOver="javascript:overlib(
        		'<?= htmlentities(TldDatabase::escape(nl2br($row['category']))) ?>',
				WIDTH, 350, OFFSETX, 50, VAUTO, FGCOLOR, '#eeeeee', BGCOLOR, 'gray', CAPCOLOR, '#dedede');"
			onMouseOut="javascript:nd();"><a href="<?= $mod_links[$row['module']].$row['parent_id'];?>">
            <?= $row['parent_id']?></a></td>
        <?php if($editMode):?>
            <td title="Task iFactor">
            <select name="ifactor[<?=$row['id']?>]" style="font-size: 10px">
                <option value="1" <?= $row['ifactor']==1 ? "selected='1'" : ''?>>1</option>
                <option value="10" <?= $row['ifactor']==10 ? "selected='10'" : ''?>>10</option>
                <option value="100" <?= $row['ifactor']==100 ? "selected='100'" : ''?>>100</option>
                <option value="1000" <?= $row['ifactor']==1000 ? "selected='1000'" : ''?>>1000</option>
            </select>
            </td>
            <td title="Task completion date">
                <input type="text" name="tasks[<?=$row['id']?>][dcomp]" value="<?= $row['dcomp']?>" style="font-size: 10px" size="10">
            </td>
        <?php if ($superuser): ?>
            <td title="Task Due date">
                <input type="text" name="tasks[<?=$row['id']?>][due_date]" value="<?= $row['due_date']?>" style="font-size: 10px" size="10">
            </td>
        <?php endif; ?>
        <?php else:?>
            <td title="Task iFactor">
                <?= $row['ifactor'] ?>
            </td>
            <td title="Task Category"><?= $row['cat']?></td>
            <td title="Estimated task completion date"><?= $row['dcomp']?></td>
            <td title="Estimation time (in Hours)"><?= $row['hours']?></td>
        <td title="<?= htmlentities($row['log_list'] ?? '')?>">
            <img src="/shared/bluesphere/16x16/actions/toggle_log.png"></td>
        <td title="Add comment to Task#<?= $row['id']?>">
            <a href="<?=$php_self?>?m[0]=tasks&m[1]=task&m[2]=comment&id=<?=$row['id']?>">
            <img src="/shared/bluesphere/16x16/actions/mail_generic.png"></a>
        </td>
        <td title="Reschedule Task#<?= $row['id']?>">
            <a href="<?=$php_self?>?m[0]=tasks&m[1]=task&m[2]=reschedule&id=<?=$row['id']?>">
            <img src="/shared/bluesphere/16x16/actions/1day.png"></a>
        </td>
        <td title="Transfer to Task#<?= $row['id']?>">
            <a href="<?=$php_self?>?m[0]=tasks&m[1]=task&m[2]=transfer&id=<?=$row['id']?>">
            <img src="/shared/bluesphere/16x16/actions/mail_forward.png"></a>
        </td>
        <td title="Close Task#<?= $row['id']?>">
            <a href="<?=$php_self?>?m[0]=tasks&m[1]=task&m[2]=closeConfirm&id=<?=$row['id']?>">
            <img src="/shared/bluesphere/16x16/actions/no.png"></a>
        </td>
        <?php endif;?>
        <td title="<?= $row['assignor_fullname']?>">
            <a href="/en/private/directory/index.php?m[0]=people&m[1]=view&id=<?= $row['assignor']?>&n[1]=<?= $row['assignor_lastname']?>">
       <?= $row['assignor_lastname']?></a></td>
        <td title="<?= $row['assignee_fullname']?>">
            <a href="/en/private/directory/index.php?m[0]=people&m[1]=view&id=<?= $row['assignee']?>&n[1]=<?= $row['assignee_lastname']?>">
        <?= $row['assignee_lastname']?></td>

        <?php for ($i=$range_start; $i<$range_end; $i++):?>
            <?php
            $mytitle = htmlentities($logs[$row['id']][$i] ?? '');
            $mycell = empty($mytitle) ? "&nbsp;" : '<img src="/shared/bluesphere/16x16/actions/toggle_log.png">';
            if($row['dcomp_days_rel'] == $i){
                $style = "border: 1px solid #0000FF;";
            }else{
                $style="";
            }
            ?>
            <?php if($row['due_days_rel']==$i): ?>
                <td width="5" style="<?=$style?>" bgcolor="#00FF00" title="<?= "Due date: ".$row['due_date']."\n"?> <?= $mytitle?>">
                <?=$mycell?></td>
            <?php elseif($row['open_days_rel']==$i): ?>
                <td width="5" style="<?=$style?>" bgcolor="#FF6600" title="<?= "Date opened: ".$row['date']."\n"?> <?= $mytitle?>">
                <?=$mycell?></td>
            <?php elseif($i > $row['open_days_rel'] && $i < $row['due_days_rel'] && $i<>0): ?>
                <td width="5" style="<?=$style?>" bgcolor="#FFCC00" title="<?= "$i Days\n"?> <?= $mytitle?>">
                <?=$mycell?></td>
            <?php else:?>
                <td width="5" style="<?=$style?>" bgcolor="<?= $i==0 ? "#66CCFF" : ($i%7==0 ? "#FF000" : "");?>" title="<?= "$i Days\n"?> <?= $mytitle?>">
                <?=$mycell?></td>
            <?php endif;?>
        <?php endfor;?>

        <?php
        $title_problem = empty($row['task']) ? "No description..." : htmlentities(TldDatabase::escape($row['task']));
        if(strlen($row['task'])>80){
	        $p = explode(" ", $row['task'], substr_count(substr($row['task'],0,80)," "));
	        if(count(explode(" ", $row['task'])) > substr_count(substr($row['task'],0,80)," ")){
	            $num = substr_count(substr($row['task'],0,80)," ")-1;
	        	$p[$num] = "...";
	        }
        }else{
        	$p = explode(" ", $row['task'], 15);
        }
//         $pa = implode(" ", $p);
//         if(explode("/n", $pa, 1)){
//         	$p = explode("/n", $pa, 1);
//         	$p[1] = "...";
//         }
        ?>
        <td onMouseOver="javascript:overlib(
        		'<?= htmlentities(TldDatabase::escape(nl2br($row['task']))) ?>',
				WIDTH, 350, OFFSETX, 50, VAUTO, FGCOLOR, '#eeeeee', BGCOLOR, 'gray', CAPCOLOR, '#dedede');"
			onMouseOut="javascript:nd();">
            <?= strip_tags(implode(" ", $p))?>
        </td>
    </tr>
<?php   endforeach;?>
</table>
<?php if($editMode):?>
        <input type="submit" name="Submit">
    </form>
<?php endif;?>
<?php
$BUFFER = ob_get_contents();
ob_end_clean();
return $BUFFER;
?>

