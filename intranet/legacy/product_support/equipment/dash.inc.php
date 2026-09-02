<?php
$DEFAULT_TITLE .= "\Dashboards";
$DEFAULT_MENU .=<<<EOF
	<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
	<a href="$php_self?m[0]=equipment&m[1]=listing&m[2]=${m[2]}">Online</a>&nbsp;|&nbsp;
	<a href="$php_self?m[0]=equipment&m[1]=listing&m[2]=${m[2]}&m[3]=csv">CSV</a>
EOF;

switch($m[2]){
case 'byFactory':
	$DEFAULT_TITLE .= " - By Factory";
	$form = new HTML_QuickForm('frmByFactgory', 'post');
	$form->addElement(	'header', 'title', "Show equipment records by Factory");
	$form->addElement(	'hidden', 'm[0]', 'equipment');
	$form->addElement(	'hidden', 'm[1]', 'listing');
	$form->addElement(	'hidden', 'm[2]', 'byFactory');
	$form->addElement(	'select', 'factory', 'Factory', array(""=>"")+
		tldLocation::getFactoryList("smartyOptionsLocationLocation"));
	$form->addElement(	'submit', 'btnSubmit', 'Submit');

	if($form->validate()){
		$_title = "Equipment Records by Factory";
		$rows = tldEquipment::byConstraints(array("man_location"=>$factory));
	}
	else $body = $form->toHTML();
break;
default:
break;
}
?>