<?php
session_start();
if(!isset($_SESSION['questionaire'])) $_SESSION['questionaire'] = null;
$questionaire =& $_SESSION['questionaire'];

include_once("common.inc.php");
include_once("questionaire.inc.php");

$mySmarty = tldUtils::getSmarty("intranet");
$mySmarty->assign("title","Questionaire");
$self = $_SERVER['PHP_SELF'];
$menu = <<<EOF
	<a href="questionaire.php">Home</a>
EOF;
$mySmarty->assign("menu",$menu);

$myQuestion = new question($id);
$mySmarty->assign("question",$myQuestion->getItsDetails());

switch($m){
	case 'check':
		$questionaire["questionsLeft"] = $questionaire["questionsLeft"] -1;
		$mySmarty->assign("answeredCorrectly",$myQuestion->checkAnswer($response,$GLOBALS["PHP_AUTH_USER"]));
		$mySmarty->assign("response",$response);
		$mySmarty->assign("numQuestions",$questionaire["questionsLeft"]);
		$body = $mySmarty->fetch("sales/questionaire/view.answer.tpl");
	break;
	default:
		if($n) $questionaire["questionsLeft"] = $n;
		$mySmarty->assign("next_step","check");
		$body = $mySmarty->fetch("sales/questionaire/view.question.tpl");
}

$mySmarty->assign("body",$body);

$mySmarty->display("intranet.tpl");
