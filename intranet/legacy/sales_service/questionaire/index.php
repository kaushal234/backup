<?php
session_start();
if(!isset($_SESSION['questionaire'])) $_SESSION['questionaire'] = null;
$questionaire =& $_SESSION['questionaire'];
if(!isset($_SESSION['currentQuestion'])) $_SESSION['currentQuestion'] = null;
$questionaire =& $_SESSION['currentQuestion'];

include_once("common.inc.php");
include_once("questionaire.inc.php");

$mySmarty = tldUtils::getSmarty("intranet");
$mySmarty->assign("title","Questionaire");

$self = $_SERVER['PHP_SELF'];
$menu = <<<EOF
	<a href="$self">Home</a>&nbsp;|&nbsp;
	<a href="$self?m[0]=reports">Reports</a>
EOF;

$myUser = new tldUser($GLOBALS["PHP_AUTH_USER"]);
if($myUser->isInGroup(array("questionaire","gg_SUPPORT","gg_ADMIN"))){
	$menu .= <<<EOF
	&nbsp;|&nbsp;<a href="admin/index.htm">Questionaire Adminstration</a>
EOF;
}

$mySmarty->assign("menu",$menu);

switch($m[0]){
	case "check":
		$myQuestion = new question($currentQuestion);
		$mySmarty->assign("single", $single);
		$mySmarty->assign("question", $myQuestion->getItsDetails());
		$mySmarty->assign("answeredCorrectly", $myQuestion->checkAnswer($response,$GLOBALS["PHP_AUTH_USER"]));
		$mySmarty->assign("response", $response);
		$mySmarty->assign("numQuestions", count($questionaire["questionsLeft"]));
		$body = $mySmarty->fetch("sales_service/questionaire/view.answer.tpl");
	break;
	case "start":
		$questionaire = array();
		$questionaire["questionsLeft"] = array();
		$questionaire["question_cat"] = $question_cat;
		$questionaire["equip_cat"] = $equip_cat;
		switch($m[1]){
		case "latest":
			$query = "SELECT * FROM questionaire ORDER BY id DESC LIMIT ".TldDatabase::escape($n);
			$rows = tldUtils::getSqlToAssocArray($query);
			foreach($rows as $row){
				$questionaire["questionsLeft"][] = $row["id"];
			}
		break;
		case "specific":
			$questionaire["questionsLeft"][] = $id;
		break;
		default:
			if($single){
				$mySmarty->assign("single", "1");
				$n = 1;
			}
			while($n >0){
				$myQuestion = new question("", $questionaire["equip_cat"], $questionaire["question_cat"]);
				$id = $myQuestion->getId();
				if(!in_array($id, $questionaire["questionsLeft"])){
					$n--;
					$questionaire["questionsLeft"][] = $id;
				}
			}
		}
	case question:
		if(count($questionaire["questionsLeft"]) == 0)
			exit;
		$myQuestion = new question(array_pop($questionaire["questionsLeft"]));
		$currentQuestion = $myQuestion->getId();
		$mySmarty->assign("question",$myQuestion->getItsDetails());
		$mySmarty->assign("next_step","check");
		$body = $mySmarty->fetch("sales_service/questionaire/view.question.tpl");

	break;
	case reports:
		switch($m[1]){
		case questionStats:
			$mySmarty->assign("title","Questionaire Statistics");
			$mySmarty->assign("width","0");

			$query = <<<EOF
				SELECT questionaire.*,
					count(*) AS total_responses,
					sum(result) AS total_correct,
					sum(result)/count(*)*100 AS total_correct_percentage
				FROM questionaire,questionaire_results
				WHERE questionaire.id=questionaire_results.parent_id
				group by questionaire.id
EOF;
			$mySmarty->assign("results",tldUtils::getSqlToAssocArray($query));
			$body = $mySmarty->fetch("sales_service/questionaire/reports.question.stats.tpl");
		break;
		case psmStats:
			$mySmarty->assign("title","Questionaire Statistics");
			$mySmarty->assign("width","0");

			$query = <<<EOF
				SELECT author, count(  *  )  AS questionCount
				FROM  `questionaire`
				GROUP  BY author
				ORDER  BY author
EOF;
			$mySmarty->assign("results",tldUtils::getSqlToAssocArray($query));
			$body = $mySmarty->fetch("sales_service/questionaire/reports.psm.stats.tpl");
		break;
		default:
			$body = $mySmarty->fetch("sales_service/questionaire/reports.homepage.tpl");
		}
	break;
	default:
		$mySmarty->assign("question_cats",tldList::optionsByListNameAsListItemListItem('questionaire_cat'));
		$mySmarty->assign("equip_cats",tldList::optionsByListNameAsListItemListItem('equip_cat'));

		$body = $mySmarty->fetch("sales_service/questionaire/index.tpl");
}

$mySmarty->assign("body",$body);
$mySmarty->display("intranet.tpl");
?>
