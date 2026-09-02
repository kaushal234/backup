<?php
$DEFAULT_TITLE .= "/Module";

$DEFAULT_MENU .= <<<EOF
    <tld:include src="mis/groups/_menu.html.twig" />
EOF;

switch($m[1]){

default:
	$body = $smarty->fetch("$PATH/grdesc/homepage.grdesc.tpl");
	$body.= _getListing(tldGroupRole::getList(),"Group list");
break;
}

function _getListing($rows,$title){
	global $php_self;
	$report = new tldReportColumnar(
		$rows,
		array(
			"xItems"=>array(
				"id"			=>"ID#",
				"group_name"	=>"Group name",
				"description"	=>"Group and role description"
			),
			"title"=>$title,
			""
		)
	);
	return $report->fetch();
}
?>