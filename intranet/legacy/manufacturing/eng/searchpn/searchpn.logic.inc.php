<?php
$DEFAULT_TITLE .= "\Search new Part Number Sequence";
$DEFAULT_MENU .=<<<EOF
	<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
	<a href="$php_self?m[0]=searchpn&m[1]=search">Search</a>
EOF;
switch($m[1]){
case 'search':
	$form = new HTML_QuickForm('frmByNum', 'post');
	$form->addElement(	'hidden', 'm[0]', 'searchpn');
	$form->addElement(	'hidden', 'm[1]', 'viewByPN');
	$form->addElement(	'header', 'title', "Search new Sequence by Part Number");
	$form->addElement(	'text', 'pn', 'PN#');
	$form->addElement(	'submit', 'btnSubmit', 'Submit');
	if(!$form->validate()){
		$body = $form->toHTML();
		break;
	}
break;
case 'viewByPN':
	$rows = tldTask::byPN($pn, 20);
	$_TITLE = "New SEQ with #PN".$pn;
	$xItems = array(
			"id"				=>"Task/SEQ#",
			"module"            =>"Module",
			"parent_id"         =>"Ref#",
			"pn"				=>"PN#",
			"status"			=>"Status",
			"date"				=>"Date Opened",
			"dt_closed"			=>"Date Closed",
			"due_date"			=>"Due Date",
			"assignor_fullname"	=>"Assignor",
			"assignee_fullname"	=>"Assignee",
			"task"          	=>"Task"
	);

	$report = new tldReportColumnar(
			$rows,
			array(
					"xItems"=>$xItems,
					"title"=>$_TITLE,
					"links"=>array("id"=>"/en/private/calendar/calendar.php?m[0]=tasks&m[1]=task&m[2]=view&id=")
			)
	);
	$body .= $report->fetch();
break;
}

?>



