<?php
include_once("common.inc.php");
$PATH = "sales_service/questionaire/admin/reports";
$mySmarty = tldUtils::getSmarty("intranet");
$menu = <<<EOF
	<a href="/en/private/sales_service/questionaire/admin/index.htm">Home</a>
EOF;
$mySmarty->assign("menu",$menu);

switch($m ?? null){
case 'expired':
	$mySmarty->assign("title","Expired Questions");
	$mySmarty->assign("width","0");

	$query = <<<EOF
		SELECT questionaire.*,
			count(*) AS total_responses,
			sum(result) AS total_correct,
			sum(result)/count(*)*100 AS total_correct_percentage
		FROM questionaire,questionaire_results
		WHERE questionaire.id=questionaire_results.parent_id
			AND now() > questionaire.expiration
		group by questionaire.id
EOF;
	$mySmarty->assign("results",tldUtils::getSqlToAssocArray($query));
	$body = $mySmarty->fetch("$PATH/question.stats.tpl");
break;
case 'stats':
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
	$body = $mySmarty->fetch("$PATH/question.stats.tpl");
break;
case 'userStats':
	$mySmarty->assign("title","User Statistics");
	$mySmarty->assign("width","0");

	$query = <<<EOF
		SELECT distinct user,
			count(*) AS total_responses,
			sum(result) AS total_correct,
			sum(result)/count(*)*100 AS total_correct_percentage
		FROM questionaire,questionaire_results
		WHERE questionaire.id=questionaire_results.parent_id
		group by user
EOF;
	$mySmarty->assign("results",tldUtils::getSqlToAssocArray($query));
	$body = $mySmarty->fetch("$PATH/users.stats.tpl");
break;
default:
	$body = $mySmarty->fetch("$PATH/default.tpl");
}

$mySmarty->assign("body",$body);
$mySmarty->display("intranet.tpl");
