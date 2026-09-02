<?php
$DEFAULT_TITLE .= "\Std Cost";

if(!$user->isInGroup(array("acl_stdcost","gg_PUR","role_QAM","role_ASM","gg_PARTS","gg_ADMIN","gg_ENG", "gg_BOOST", "gg_STOC"))){
	$ACL_ERROR = true;
}

switch($m[1]){
case 'view':
	if(empty($pn)){
		$DEFAULT_ERROR[] = "ERROR: Part number empty!";
		break;
	}
	$parts = tldERP::searchItemDataByPN(trim($pn), array("fuzzySearch"=>true));
	if(empty($parts)){
		$DEFAULT_ERROR[] = "ERROR: No items founded for PN '$pn'";
		break;
	}
	$smarty->assign("parts", $parts);
	$body = $smarty->fetch("$PATH/stdcost/list.stdcost.tpl");
break;
default:
	$body = $smarty->fetch("$PATH/stdcost/homepage.stdcost.tpl");
	$form = new HTML_QuickForm('frmSearchPN', 'post');
	$form->addElement(	'hidden', 'm[0]', 'stdcost');
	$form->addElement(	'hidden', 'm[1]', 'view');
	$form->addElement(	'hidden', 'm[2]', 'search');
	$form->addElement(	'header', 'title','Search for PN Std Cost');
	$form->addElement(	'text', 'pn', 'Part#');
	$form->addElement(	'submit', 'btnSubmit', 'Lookup');
	$form->addRule(	'pn', 'Required', 'required');
	$body .= $form->toHTML();
}
?>