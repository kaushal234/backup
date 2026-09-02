<?php
include_once("common.inc.php");
include_once("user.inc.php");
include_once("forms_and_reports.inc.php");

session_start();
if(!isset($_SESSION['sess'])) {
	$_SESSION['sess'] = null;
}
$sess =& $_SESSION['sess'];

$user = new tldUser($GLOBALS["PHP_AUTH_USER"]);

$smarty = tldUtils::getSmarty("intranet");

$php_self = $_SERVER['PHP_SELF'];
$DEFAULT_MENU =<<<EOF
<a href="$php_self">Home</a>
&nbsp;|&nbsp;<a href="$php_self?m[0]=reports">Reports</a>
EOF;

$PATH = dirname($_SERVER["SCRIPT_FILENAME"])."/users_and_groups";
$DEFAULT_TEMPLATE = "intranet.tpl";
$DEFAULT_TITLE = "Users and Groups";

switch($m[0] ?? null){
case 'reports':
	$DEFAULT_TITLE .= "\Reports";
	switch($m[1] ?? null){
	case 'groupByLocation':
		// 1 - COMMON ROLES
		$LOCATIONS = tldLocation::getERPList("smartyOptions");
		$GROUPS = array('role_CEO','role_CFO','role_CIO','role_COO');
		$a = implode("','",$GROUPS);
		$query =<<<EOF
SELECT
	count(people.id) as num,
	(SELECT location FROM locations WHERE locations.erp=people_groups.level) as level,
	CONCAT(people_groups.group_name,"<br>",
		(SELECT description FROM people_groups_select
		WHERE people_groups_select.group_name=people_groups.group_name)
	) AS group_name
FROM people
	LEFT JOIN people_groups ON people.id = people_groups.parent_id
WHERE
	people_groups.group_name IN('$a')
GROUP BY
	people_groups.group_name,
	people_groups.level
EOF;
		$rows = tldUtils::getSqlToAssocArray($query);
		$body .= _getMatrixGroupLocation($rows,"Common Roles by Location");

		// 2 - SSO ROLES

		$LOCATIONS = tldLocation::getSalesOrgList("smartyOptions");
		$GROUPS = array('role_AP','role_CSM','role_EVP','role_FC','role_ICS','role_SA','role_SPM','role_TM','role_SPM');
		$a = implode("','",$GROUPS);
		$b = implode("','",array_keys($LOCATIONS));
		$query =<<<EOF
SELECT
	count(people.id) as num,
	(SELECT location FROM locations WHERE locations.erp=people_groups.level) as level,
	CONCAT(people_groups.group_name,"<br>",
		(SELECT description FROM people_groups_select
		WHERE people_groups_select.group_name=people_groups.group_name)
	) AS group_name
FROM people
	LEFT JOIN people_groups ON people.id = people_groups.parent_id
WHERE
	people_groups.group_name IN('$a')
	AND people_groups.level IN('$b')
GROUP BY
	people_groups.group_name,
	people_groups.level
EOF;
		$rows = tldUtils::getSqlToAssocArray($query);
		$body .= _getMatrixGroupLocation($rows,"SSO Roles by Location");

		// 3 - ERP ROLES

		$LOCATIONS = tldLocation::getFactoryList("smartyOptions");
		$GROUPS = array('role_AP','role_EM','role_FC','role_MLM','role_PM','role_PSM','role_QAM');
		$a = implode("','",$GROUPS);
		$b = implode("','",array_keys($LOCATIONS));
		$query =<<<EOF
SELECT
	count(people.id) as num,
	(SELECT location FROM locations WHERE locations.erp=people_groups.level) as level,
	CONCAT(people_groups.group_name,"<br>",
		(SELECT description FROM people_groups_select
		WHERE people_groups_select.group_name=people_groups.group_name)
	) AS group_name
FROM people
	LEFT JOIN people_groups ON people.id = people_groups.parent_id
WHERE
	people_groups.group_name IN('$a')
	AND people_groups.level IN('$b')
GROUP BY
	people_groups.group_name,
	people_groups.level
EOF;
		$rows = tldUtils::getSqlToAssocArray($query);
		$body .= _getMatrixGroupLocation($rows,"Factory Roles by Location");
	break;
	default:
		$body = $smarty->fetch("$PATH/homepage.users_and_groups.tpl");
	break;
	}
break;
default:
	$body = $smarty->fetch("$PATH/homepage.users_and_groups.tpl");
break;
}

$smarty->assign("menu",$DEFAULT_MENU.(isset($menu) ? $menu : ''));
$smarty->assign("body",$body);
if(empty($title)) {
	$title = $DEFAULT_TITLE;
}
$smarty->assign("title",$title);

if(empty($template)) {
	$template = $DEFAULT_TEMPLATE;
}
if($template<>"NO_TEMPLATE") {
	$smarty->display($template);
}


function _getMatrixGroupLocation($rows,$title){
	$form = new tldMatrix($rows,
    	"level", "group_name", "num",
    	NULL,
    	$title,
    	array(
    		"doNotShowYTotals"=>TRUE,
    		"doNotShowXTotals"=>TRUE
    	)
    );
	return $form->fetch();
}
