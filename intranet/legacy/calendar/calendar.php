<?php
include_once("common.inc.php");
include_once("calendar.inc.php");
include_once("forms_and_reports.inc.php");
require_once("HTML/QuickForm.php");
require_once('HTML/QuickForm/advmultiselect.php');

session_start();
if(!isset($_SESSION['sess'])) $_SESSION['sess'] = null;
$sess =& $_SESSION['sess'];

$body = '';
$user = new tldUser($GLOBALS["PHP_AUTH_USER"]);

$smarty = tldUtils::getSmarty("intranet");
$php_self = $_SERVER['PHP_SELF'];
$PATH = "calendar";
$DEFAULT_TEMPLATE = isset($sess['template']) ? $sess['template'] : "intranet.tpl";
$DEFAULT_TITLE = "Calendar";
$DEFAULT_ERROR = array();
$DEFAULT_MENU =<<<EOF
<a href="$php_self" title="Calendar Homepage">Home</a>&nbsp;|&nbsp;
<a href="$php_self?m[0]=sub" title="Team Tasks">Team</a>&nbsp;|&nbsp;
<a href="$php_self?m[0]=tasks&m[1]=taskList" title="Tasks">Tasks (old version)</a>&nbsp;|&nbsp;
<a href="$php_self?m[0]=st&m[1]=STList" title="Scheduled Tasks">ST</a>
EOF;

if (!$user->isAgent() || strpos($user->getEmail(),'@sageparts.com')!==FALSE)
{
    $DEFAULT_MENU .=<<<EOF
&nbsp;|&nbsp;<a href="$php_self?m[0]=gwf" title="Group Work Flow">GWF</a>&nbsp;|&nbsp;
<a href="$php_self?m[0]=seq" title="Sequences">SEQ</a>&nbsp;|&nbsp;
<a href="$php_self?m[0]=bp" title="Business Processes">BP</a>&nbsp;|&nbsp;
<a href="$php_self?m[0]=events" title="Events">Events</a>
EOF;
}

if ($user->isInGroup(array("gg_MIS")))
{
    $DEFAULT_MENU .=<<<EOF
&nbsp;|&nbsp;<a href="$php_self?m[0]=srm" title="SRM">Shared Resource Module</a>
EOF;
}

switch($m[0]){
case "sub":
case "todo":
case "tasks":
case "st":
case "gwf":
case "seq":
case "bp":
case "events":
case "srm":
    include($m[0]."/logic.".$m[0].".inc.php");
break;
case "dashboard2":
    include("mis.dashboard.inc.php");
break;
case "dashboard":
	$DEFAULT_MENU .=<<<EOF
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<a href="$php_self?mode=&id=$user->itsID" title="Calendar Homepage">My List</a>
&nbsp;|&nbsp;<a href="$php_self?m[0]=dashboard">My Dashboard</a>
&nbsp;|&nbsp;<a href="$php_self?mode=ical&id=$user->itsID" title="Download all tasks as ical">Download</a>
&nbsp;|&nbsp;<a href="$php_self?m[0]=edit&id=$user->itsID" title="Prioritize my tasks">Maintain MyTasks</a>
EOF;
	$cells = array();
	$uid = $user->getID();
	$bu = new tldLocation($user->getBUID());
	$erp = $bu->getERP();
	$USER_BODY = <<<EOF
	<h3>{$user->getFullname()} Dashboard</h3>
	<p>
		<a href="/en/private/calendar/calendar.php?mode=&id={$uid}">Task Scheduler</a>
		&nbsp;|&nbsp; <a href="/en/private/calendar/calendar.php?m[0]=tasks&m[1]=listing&m[2]=byUserByClosedPeriod&uid={$uid}">Recently Closed</a>
		&nbsp;|&nbsp; <a href="/en/private/calendar/calendar.php?m[0]=tasks&m[1]=taskList">Task List</a>
	</p>
	<img width="100" src="/en/private/uploads/{$user->getPhotoFilename()}" />
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
	WHERE status<>'CLOSED' AND assignee={$uid}
EOF;
	$tasks = tldUtils::getSqlToAssocArray($query);
	$tcnt = array();
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

	$sequenceTemplate = $m[2] === 'eng.newpartnbrevision' ? 78 : 22;
	$query = <<<SQL
    SELECT '_USER_' AS location,
    CONCAT('Step ',T1.cur_step) AS step,
    COUNT(*) AS num
    FROM tasks AS T1
    WHERE T1.tplno=$sequenceTemplate AND T1.status <> 'CLOSED' AND T1.assignor={$uid}
    GROUP BY location, step
SQL;
    $seqr = array_merge(tldTask::countByNewItemSEQ(['erp' => $bu->getERP()]), tldUtils::getSqlToAssocArray($query));
    $form = new tldMatrix(
			$seqr,
			"location", "step", "num",
			"/en/private/manufacturing/eng/dev.php?m[0]=reports&m[1]=listing&m[2]=SEQReport",
			"SEQ New Item Count",
			array('doNotShowYTotals'=>true)
	);
	// manipulate username link
	$html = $form->fetch();
	$html = str_replace("&x=_USER_","&x=UID{$user->getID()}",$html);
	$html = str_replace("_USER_",$user->getFullname(),$html);
	$cells[] = $html;

	if (!empty($modules = tldModule::byConstraints("oid = {$user->getID()}"))) {
		$mooLinks = [
			'All my tasks as MOO' => '/en/private/calendar/calendar.php?m[0]=tasks&m[1]=taskList&m[2]=myModules',
		];

		foreach ($modules as $module) {
			$mooLinks[sprintf('%s Tasks', $module['module'])] = '/en/private/calendar/calendar.php?m[0]=tasks&m[1]=taskList&m[2]=byIntranetModule&module='.$module['module'];
		}

		$mooCell = '<h3>MOO Links</h3>';
		$mooCell.= '<ul>';
		foreach ($mooLinks as $title => $link) {
			$mooCell.= sprintf('<li><a href="%s">%s</a>', $link, $title);
			$mooCell.= sprintf('<a href="%s&includeClosed">%s</a></li>', $link, ' (include CLOSED)');
		}
		$mooCell.= '</ul>';

		$cells[]= $mooCell;
	}

	$report = new tldHTMLTable(
			$cells,
			array(
					"cols"=>2,
					"attribs"=>array("table"=>" width='100%'","tr"=>" bgcolor='#FFFFFF'")
			)
	);
	$body .= $report->fetch();
break;
default:
    $body = $smarty->fetch("$PATH/homepage.$PATH.tpl");
    $body .= include("gantt.inc.php");
break;
}

if (isset($JS_INCLUDE)) {
	$smarty->assign("js_includes",$JS_INCLUDE);
}
$smarty->assign("menu",$DEFAULT_MENU.(isset($menu) ? $menu : ''));
$smarty->assign("body",$body);
$DEFAULT_ERROR = array_map(function ($errorMsg) {
    return !empty($errorMsg) ? '<span class="default-error-msg">' . $errorMsg . '</span>' : $errorMsg;
}, $DEFAULT_ERROR);
$smarty->assign("error",implode("<br>", $DEFAULT_ERROR));
if(empty($title)) $title = $DEFAULT_TITLE;
$smarty->assign("title",$title);

if(empty($template)) $template = $DEFAULT_TEMPLATE;
if($template<>"NO_TEMPLATE")
	$smarty->display($template);

?>
