<?php
include_once("sales_service.inc.php");
require_once 'HTML/QuickForm/advmultiselect.php';
$DEFAULT_TITLE .= "\Models";

$DEFAULT_MENU .=<<<EOF
<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<a href="$php_self?m[0]=models">Home</a>
EOF;
if($user->isInGroup("superuser","gg_quality")){
	$DEFAULT_MENU .=<<<EOF
	&nbsp;|&nbsp;<a href="models/models_admin.php">Models Admin</a>
EOF;
}
switch($m[1]){
case "form":
	switch($m[2]){
	case "newLink":
		$DEFAULT_MENU="";
		$DEFAULT_TITLE .= "\New Link";
		//check module
                $module = strtoupper($module);
		$modules = tldTask::getModuleList();
		if(!in_array($module, $modules)){
			$DEFAULT_ERROR[] = "ERROR: Module '$module' is not valid";
			break;
		}
		//check parent_id
		if(empty($parent_id)){
			$DEFAULT_ERROR[] = "ERROR: parent_id not set";
			break;
		}
		$form = new HTML_QuickForm('frmNew', 'post');
		$form->addElement(	'header', 'title', "Submit new $module Link");
		$form->addElement(	'hidden', 'm[0]', 'links');
		$form->addElement(	'hidden', 'm[1]', 'form');
		$form->addElement(	'hidden', 'm[2]', 'newLink');
		$form->addElement(	'hidden', 'module', $module);
		$form->addElement(	'hidden', 'parent_id', $parent_id);
		$form->addElement(	'select', 'type', 'Ref Type', $modules);
		$form->addElement(	'text', 'item', 'Ref#');
		$form->addElement(	'submit', 'btnSubmit', 'Submit');
		$form->addRule("type","This is a required field.","required");
		$form->addRule("item","This is a required field.","required");
		if ($form->validate()){
			# If the form validates then freeze the data
			$form->freeze();
			$vals = $form->exportValues();
			$error = tldModLink::insert($vals['module'], $vals['parent_id'], $modules[$vals['type']], $vals['item']);
			if(!is_numeric($error)){
				$DEFAULT_ERROR[] = "ERROR: There was an error adding the new link...$error";
			}
			$url = tldModLink::getURL($vals['module'], $vals['parent_id']);
			$body =<<<EOF
			<a href="$url">Click here to go back to $module #$parent_id</a>
EOF;
		}else{
			$body = $form->toHTML();
		}
	break;
	}
break;
case 'view':
	$link = new tldModLink($id);
	$url = tldModLink::getURL($link->getType(), $link->getItem());
case 'bounce':
	$body =<<<EOF
	<meta http-equiv="refresh" content="0;URL=$url">
	<a href="$url">If the screen does not refresh automatically, click here.</a>
EOF;
break;
case 'del':
	if($user->isInGroup("role_QAM") || $user->isInGroup("superuser") || $user->isInGroup("GG_QUALITY")){
		$model = new tldModModel($id);
		$e = $model->delete($id);
		if(is_string($e)){
			$DEFAULT_ERROR[] = "ERROR: Can not delete model, reason: $e";
			break;
		}
		$url = "/en/private/manufacturing/qa/dev.php?m[0]=ncr&m[1]=view&m[2]=models&id=".$model->getParentID();
		$body =<<<EOF
	<meta http-equiv="refresh" content="0;URL=$url">
	<a href="$url">If the screen does not refresh automatically, click here.</a>
EOF;
	}else{
		$DEFAULT_ERROR[] = "ERROR: You do not have permissions to delete this file.";
	}
break;
case 'new':
	switch($module){
		case 'NCR':
			$body = "This page has been migrated and should not be displayed anymore.";

			break;
	}
break;
default:
	$body =<<<EOF
	<a href="$url">Click here to go back to $module #$id</a>
EOF;
break;
}

?>
