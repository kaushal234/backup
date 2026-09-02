<?php
include_once("questionaire.inc.php");
$currentQuestion = &$sess['questionaire']['currentQuestion'];
$questionaire = &$sess['questionaire']['questionaire'];


$DEFAULT_TITLE .="\Questionaire";
$DEFAULT_MENU .= <<<EOF
	<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
	<a href="$self?m[0]=questionaire">Home</a>&nbsp;|&nbsp;
	<a href="$self?m[0]=questionaire&m[1]=reports">Reports</a>
EOF;

if($user->isInGroup("questionaire")){
	$menu .= <<<EOF
	&nbsp;|&nbsp;<a href="questionaire/admin/index.htm">Questionaire Adminstration</a>
EOF;
}

switch($m[1]){
	case "check":
		$myQuestion = new question($currentQuestion);
		$smarty->assign("single", $single);
		$smarty->assign("question", $myQuestion->getItsDetails());
		$smarty->assign("answeredCorrectly", $myQuestion->checkAnswer($response,$GLOBALS["PHP_AUTH_USER"]));
		$smarty->assign("response", $response);
		$smarty->assign("numQuestions", count($questionaire["questionsLeft"]));
		$body = $smarty->fetch("sales_service/questionaire/view.answer.tpl");
	break;
	case "start":
		$questionaire = array();
		$questionaire["questionsLeft"] = array();
		$questionaire["question_cat"] = $question_cat;
		$questionaire["equip_cat"] = $equip_cat;
		switch($m[2]){
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
				$smarty->assign("single", "1");
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
	case 'question':
		if(count($questionaire["questionsLeft"]) == 0)
			exit;
		$myQuestion = new question(array_pop($questionaire["questionsLeft"]));
		$currentQuestion = $myQuestion->getId();
		$smarty->assign("question",$myQuestion->getItsDetails());
		$smarty->assign("next_step","check");
		$body = $smarty->fetch("sales_service/questionaire/view.question.tpl");

	break;
	case 'reports':
		switch($m[2]){
		case 'questionStats':
			$smarty->assign("title","Questionaire Statistics");
			$smarty->assign("width","0");

			$query = <<<EOF
				SELECT questionaire.*,
					count(*) AS total_responses,
					sum(result) AS total_correct,
					sum(result)/count(*)*100 AS total_correct_percentage
				FROM questionaire,questionaire_results
				WHERE questionaire.id=questionaire_results.parent_id
				group by questionaire.id
EOF;
			$smarty->assign("results",tldUtils::getSqlToAssocArray($query));
			$body = $smarty->fetch("sales_service/questionaire/reports.question.stats.tpl");
		break;
		case 'psmStats':
			$smarty->assign("title","Questionaire Statistics");
			$smarty->assign("width","0");

			$query = <<<EOF
				SELECT author, count(  *  )  AS questionCount
				FROM  `questionaire`
				GROUP  BY author
				ORDER  BY author
EOF;
			$smarty->assign("results",tldUtils::getSqlToAssocArray($query));
			$body = $smarty->fetch("sales_service/questionaire/reports.psm.stats.tpl");
		break;
		default:
			$body = $smarty->fetch("sales_service/questionaire/reports.homepage.tpl");
		}
	break;
	default:
		$smarty->assign("question_cats",tldList::optionsByListNameAsListItemListItem('questionaire_cat'));
		$smarty->assign("equip_cats",tldList::optionsByListNameAsListItemListItem('equip_cat'));

		$body = $smarty->fetch("sales_service/questionaire/index.tpl");
}
