<?php
include_once("sales_service.inc.php");

if(!$user->isInGroup(array("gg_MIS"))){
	$DEFAULT_ERROR[] = "ERROR: You do not have permission to access this module";
	return;
}
$DEFAULT_TITLE .= "\Datasheet Upload";
$DEFAULT_MENU .= <<<EOF
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<a href="$php_self?m[0]=datasheet_upload">Home</a>&nbsp;|&nbsp;
<a href="$php_self?m[0]=datasheet_upload&m[1]=log">Logs</a>&nbsp;
EOF;

switch($m[1]){
	case 'log':
		$logs = tldModLog::byConstraints(array("module"=>"datasheet"));
		$report = new tldReportColumnar(
				$logs,
				array(
						"xItems"=>array(
								"id"				=>"ID#",
								"date"				=>"Date",
								"poster_fullname"	=>"Poster",
								"comment"			=>"Comment"
						)
				)
		);
		$body = $report->fetch();
	break;
}

$langList = array("de"=>"German","en"=>"English","es"=>"Spanish","fr"=>"French","pt"=>"Portuguese","ru"=>"Russian","zh"=>"Chinese");
$modelList = tldModel::getList();
$typeList = array("datasheet"=>"Datasheet","presentation"=>"Presentation");

$form = new HTML_QuickForm('frmNewTask', 'post');
$form->addElement(	'hidden', 	'm[0]', 		'datasheet_upload');
$form->addElement(	'header', 	'title', 		'Datasheet Upload');
$form->addElement(	'select', 	'language', 	'Language',		array(""=>"")+$langList);
$form->addElement(	'select', 	'model', 		'Model',		array(""=>"")+$modelList);
$form->addElement(	'select', 	'type', 		'Type',			array(""=>"")+$typeList);
$form->addElement(  'file',   	'file',			'Datasheet');
$form->addRule('language', 'Required','required');
$form->addRule('model', 'Required','required');
$form->addRule('type', 'Required','required');
$form->addRule('file', 'Required','required');
$form->addElement('submit', 'btnSubmit', 'Submit');


if(!$form->validate()){
	$body .= $form->toHTML();
	return;
}

$cataloguePath = tldDatasheet::CATALOGUE_FILE_PATH;
$vars = tldUtils::cleanupFormInput($form->exportValues());
$langFolder = $vars['language'];
$file = $form->getElement('file');
$file_array = $file->getValue();
$ext = strtolower(pathinfo($file_array['name'], PATHINFO_EXTENSION));
$filename = $vars['model'].'_'.$vars['type'].'.'.$ext;
if($file_array['tmp_name']!=""){
	if(file_exists("$cataloguePath/$langFolder/$filename")){
		unlink("$cataloguePath/$langFolder/$filename");
		$DEFAULT_ERROR[] = ".../$langFolder/$filename exists. Unlinking!";
	}
	$f = $file->moveUploadedFile("$cataloguePath/$langFolder",$filename);
	if(!$f){
		$DEFAULT_ERROR[] = "ERROR: Problem uploading file...<br/> $f";
	}else{
		tldModLog::insert(array("parent_id"=>0,"module"=>"datasheet","poster"=>$user->getID(),"comment"=>".../$langFolder/$filename uploaded successfully!"));
		$body .= ".../$langFolder/$filename uploaded successfully!";
	}
}

?>