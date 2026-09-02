<?php
$DEFAULT_TITLE .= "\SRM";
$DEFAULT_MENU .=<<<EOF
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<a href="$php_self?m[0]=srm">Home</a>
&nbsp;|&nbsp;<a href="$php_self?m[0]=srm&m[1]=forms&m[2]=newSRM" title="Create a SRM">New SRM</a>
&nbsp;|&nbsp;<a href="$php_self?m[0]=srm&m[1]=forms&m[2]=report" title="Report">Report</a>
&nbsp;|&nbsp;<a href="$php_self?m[0]=srm&m[1]=admin">Admin</a>
EOF;

switch($m[1]){
case 'admin':
	$DEFAULT_MENU .=<<<EOF
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<a href="/en/private/calendar/srm/srm_admin.php">SRM Admin</a>
&nbsp;|&nbsp;<a href="/en/private/calendar/srm/srm_item_admin.php">Resource Item Admin</a>
EOF;
break;
case 'forms':
	switch($m[2]){
	case 'newSRM':
		$form = new HTML_QuickForm('frmNewTask', 'post');
		$form->addElement(	'hidden', 'm[0]', 'srm');
		$form->addElement(	'hidden', 'm[1]', 'forms');
		$form->addElement(	'hidden', 'm[2]', 'newSRM');
		$form->addElement(	'header', 'title', "Submit new shared resource booking");
		$bu_id = array(""=>"")+tldLocation::getLocationList("smartyOptions");
		$form->addElement(	'select', 'bu_id', 'Where', $bu_id);
		$form->addElement('text', 'date_start', 'Start');
		$form->addElement('text', 'date_end', 'End');
		$user = tldDirectory::getUserList("smartyOptions");
		
		$form->addElement(	'textarea', 'description', 'Description');
		$form->addElement(	'submit', 'btnSubmit', 'Submit');
		$body = $form->toHTML();
	break;
	case 'report':
	
	break;
	default:
	break;
}
break;
default:
	$DEFAULT_ERROR[]="Module in BETA Version, some features are not available!";
break;
}