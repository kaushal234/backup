<?php
ini_set('max_execution_time', 0);
include_once("common.inc.php");
include_once("Mail.php");
include_once("Mail/mime.php");
include_once("calendar.inc.php");
include_once("forms_and_reports.inc.php");
?>
<!DOCTYPE html>
<html>
  <head>
	<title>ST</title>
	<link href="/tld-gse.css" rel="stylesheet" type="text/css">
	<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
  </head>
  <body>
<?php


$form = new HTML_QuickForm('runST', 'post');
$form->addElement(	'date', 'setdate', 'Date of ST task to create:',
	array(	"format"=>"Ymd", "minYear"=>date("Y")-10, "maxYear"=>date("Y")+10));
$grp[] =& $form->createElement('checkbox', null, 1, '');
$form->addGroup($grp, 'test', 'Check for test run:');
$form->addElement(	'submit','btnSubmit','send');
$form->addElement(	'reset','btnReset','reset');
$form->setDefaults(array(
	'setdate'=>array(
		'Y'=>date('Y'),
		'm'=>date('m'),
		'd'=>date('d')
	),
	'test'=>1
));
$form->addRule('setdate', 'Required', 'required');
echo $form->toHTML();

if($form->validate()){
	$vars = $form->exportValues();
	$setdate = sprintf("%d-%02d-%02d",$vars["setdate"]["Y"],$vars["setdate"]['m'],$vars["setdate"]['d']);
	if(!checkdate(substr($setdate,5,2),substr($setdate,8,2),substr($setdate,0,4))){
		echo "Date '$setdate' is not formatted correctly: ";
		exit;
	}
	if($vars['test']){
		// Show run list only
		$report = new tldReportColumnar(
			tldST::getSTsDueForTask($setdate),
			array(
				"xItems"=>array(
					"id"=>"ST#",
					"type"=>"Type",
					"bu_fullname"=>"BU",
					"assignor_fullname"=>"Assigner",
					"assignee_fullname"=>"Assignee",
					"date_start"=>"Start",
		            "date_end"=>"End",
					"description"=>"Description",
					"repd_val"=>"Repetition Value",
		            "repd_unit"=>"Repetition Unit",
					"leadtime_value"=>"Leadtime Value",
					"leadtime_unit"=>"Leadtime Unit"),
				"title"	=> "ST Assigned To Run:"
			)
		);
		echo $report->fetch();
	}else{
		// Run list
		tldST::doTasks($setdate);
		echo "Completed Opening Tasks!";
	}
}

?>
  </body>
</html>
