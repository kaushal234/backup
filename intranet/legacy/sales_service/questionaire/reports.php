<?php
include_once("common.inc.php");
$mySmarty = tldUtils::getSmarty("intranet");
$menu = <<<EOF
	<a href="/en/private/sales/questionaire/reports.htm">Home</a>
EOF;
$mySmarty->assign("menu",$menu);

switch($m){
case stats:
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
	$body = $mySmarty->fetch("sales/questionaire/reports.question.stats.tpl");
break;
default:
	$body = $mySmarty->fetch("sales/questionaire/admin/reports/default.tpl");
}

$mySmarty->assign("body",$body);
$mySmarty->display("intranet.tpl");
?>
