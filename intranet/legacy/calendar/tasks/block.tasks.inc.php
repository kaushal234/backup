<?php
include_once("calendar.inc.php");
include_once("forms_and_reports.inc.php");
?>

<!--START block_tasks-->
<table width="100%" border="0" cellspacing="0" cellpadding="0">
  <tr class="smalltext">
    <td class="table_title">
      <div align="center">Your Tasks</div>
    </td>
  </tr>
  <tr class="smalltext">
    <td>
<?php
	$report = new tldReportColumnar(
	    tldTask::countByWeeksOverdue($user->getID()),
		array(
			"xItems"=>array(
				"wk"=>"Weeks Late",
				"cnt"=>"Count"
		    ),
			"title"=>"Weeks Overdue",
			"links"=>array("wk"=>"/en/private/calendar/calendar.php?m[0]=tasks&m[1]=listing&m[2]=byWeeksLate&assignee=".$user->getID()."&wk="),
			"sortable"=>"no"
		)
	);
	echo $report->fetch();

	$sess["calendar"]["tasks"] = tldTask::byAssignee($user->getId());
	$rows = $sess["calendar"]["tasks"];
	$_BODY="<table><tr><th>ID#</th><th>Mod</th><th>Doc#</th><th>Due</th><th>Assignor</th></tr>";
	foreach($rows as $row){
		if($row["overdue"]){
			$_BODY .= "<tr class=\"alert\">";
		}else{
			$_BODY .= "<tr>";
		}
		$_BODY .= "<td><a href=\"/en/private/calendar/calendar.php?m[0]=tasks&m[1]=task&m[2]=view&id=${row['id']}\" title=\"".
		htmlentities($row['task'])."\">${row['id']}</td>";
		$_BODY .= "<td>${row['module']}</td><td>${row['parent_id']}</td><td>";
		$_BODY .= $row['due_date']."</td><td>${row['assignor_fullname']}</td></tr>";
	}
	$_BODY .= "</table>";
	echo $_BODY;

?>
	<a href="/en/private/calendar/calendar.php?m[0]=tasks&m[1]=taskList">More...</a>
    </td>
  </tr>
</table>
<!--END block_tasks-->
