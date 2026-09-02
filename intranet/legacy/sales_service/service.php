<?php

require_once __DIR__.'/../legacy_autoload.php';
require_once 'autoload_shared.php';

include_once("common.inc.php");
include_once("product_support.inc.php");
include_once("sales_service.inc.php");
include_once("forms_and_reports.inc.php");

session_start();
if(!isset($_SESSION['sess'])) $_SESSION['sess'] = null;
$sess =& $_SESSION['sess'];
$body = '';
$user = new tldUser($GLOBALS["PHP_AUTH_USER"]);
$userId = $user->getID();

$smarty = tldUtils::getSmarty("intranet");
$smarty->assign("m",$m);

$php_self = $_SERVER['PHP_SELF'];

// LOCATION SWITCH
$locations = tldLocation::getLocationList("smartyOptions");
// Case of request change
if($m[0]=='changeBU' && in_array($_REQUEST['buid'], array_keys($locations))){
    $sess['service']['default_buid'] = $_REQUEST['buid'];
    unset($m[0]);
}
// Get default value if not set
if(!isset($sess['service']['default_buid'])){
    // Get BU from account
    $DEFAULT_BUID = $user->getBUID();
    // Try from SERVICE permissions
    if(empty($DEFAULT_BUID)){
        $erp = $user->isInGroup("gg_SERVICE");
        if(is_array($erp)) $erp = $erp[0];
        $DEFAULT_BUID = tldLocation::getIDByERP((int)$erp);
    }
    // If nothing found, default to TLD EUR
    if(!in_array($DEFAULT_BUID, array_keys($locations))){
        $DEFAULT_BUID = 7;
    }
    $sess['service']['default_buid'] = $DEFAULT_BUID;
}
$DEFAULT_BUID = TldDatabase::escape($sess['service']['default_buid']);
$DEFAULT_BU = new tldLocation($DEFAULT_BUID);

$PATH = "sales_service";
$DEFAULT_TEMPLATE = "intranet.tpl";
$DEFAULT_ERROR = [];
$DEFAULT_SUCCESS = [];

$DEFAULT_TITLE = "Service Module\\{$DEFAULT_BU->getShortName()}";
$DEFAULT_MENU = <<<EOF
<a href="$php_self">Home</a>
&nbsp;|&nbsp;<a href="$php_self?m[0]=changeBU" title="Change default location">Change location</a>
&nbsp;|&nbsp;<a href="$php_self?m[0]=toc" title="TLD On Call">TOC</a>
&nbsp;|&nbsp;<a href="$php_self?m[0]=csr" title="Customer Service Request">CSR</a>
&nbsp;|&nbsp;<a href="$php_self?m[0]=srvo" title="Service Order">Service Order</a>
EOF;
if(!$user->isAgent() || ($user->isAgent() && $user->isInGroup("gg_SALES_AGENTS")) || ($user->isAgent() && $user->isInGroup("gg_PARTS_AGENTS"))){
$DEFAULT_MENU .=<<<EOF
&nbsp;|&nbsp;<a href="$php_self?m[0]=scm" title="Service Contract Module">SCM</a>
&nbsp;|&nbsp;<a href="/en/private/sales_service/sales.php?m[0]=kpi">KPI</a>
&nbsp;|&nbsp;<a href="$php_self?m[0]=toc&m[1]=reports&m[2]=myTOCsParts">My TOC's parts</a>
EOF;
}

switch($m[0]){
case 'toc':
case 'csr':
case 'scm':
case 'srvo':
    include($m[0].'/'.$m[0].'.logic.inc.php');
break;
case 'changeBU':
    $DEFAULT_TITLE .= "\Change location";
	$form = new HTML_QuickForm('frm', 'post');
	$form->addElement(	'hidden', 'm[0]', 'changeBU');
	$form->addElement(	'header', 'title', "Switch location");
	$form->addElement(	'select', 'buid', 'Location', array(""=>"")+$locations);
	$form->addElement('submit', 'btnSubmit', 'Submit');
	$form->addRule(	'buid', 'Required', 'required');
	$body .= $form->toHTML();
break;
default:
    global $kernel;
    $smarty->assign('spq_link', $kernel->getContainer()->get('router')->generate('spq_quotations_home'));
    $body = $smarty->fetch("$PATH/homepage.service.tpl");
    if ( $user->isInGroup(['ROLE_CSM', 'ROLE_AST', 'ROLE_CSA'])){
        if ($user->isInGroup( ['ROLE_CSM', 'ROLE_CSA'])){
            $grpCSM = new tldGroup('ROLE_CSM');
            $grpAST = new tldGroup('ROLE_AST');
            $ast = $grpCSM->getUserlist(array("smartyOptions"=>true))+$grpAST->getUserlist(array("smartyOptions"=>true));
            asort($ast);

            $astForm = new HTML_QuickForm('astList', 'post');
            $astForm->addElement('header', 'title', "AST Dashboard");
            $astForm->addElement('select', 'id', 'Technician', array(""=>"")+$ast);
            $astForm->addElement('submit', 'btnSubmit', 'Submit');
            $body .= $astForm->toHTML();

            if ($astForm->isSubmitted()) {
                $vars = tldUtils::cleanupFormInput($astForm->exportValues());
                $userId = $vars['id'];
            }
        }

        $dashboardAST = [];

        $taskResults = tldCSR::dashboardCountTasksForAST($userId);
        if (false !== ($result = current($taskResults)) && ($total = $result['IN PROGRESS'] + $result['late']) > 0) {
            $dashboardTasks[] = $result + [
                'work_type' => 'Tasks',
                'totals' => $total,
                'tech_id' => $userId,
            ];
        }

        foreach (tldCSR::dashboardSeparatedCountTOCForAST($userId) as $row) {
            $dashboardAST[]= $row + [
                    'work_type' => 'TOC - IF ' . $row['ifactor'],
                    'totals' => $row['IN PROGRESS'] + $row['late'] + $row['SUSPENDED'],
                    'tech_id' => $userId,
                    'module' => 'toc'
                ];
        }
        $reportASTDashboard = new tldReportColumnar(
            $dashboardAST,
            [
                "xItems"=> [
                    'work_type' =>'Work Type',
                    'PENDING' => 'Pending',
                    'IN PROGRESS' => 'In Progress',
                    'late' => 'Late',
                    'SUSPENDED' => 'Suspended',
                    'totals'=> 'Totals'
                ],
                "title"=>"AST TOC Dashboard (for all BUs)",
                "links"=> [
                    "totals"=> [
                        "url"=>"/en/private/sales_service/service.php?m[1]=listing&m[2]=search&status=ALL",
                        "params" => ["x" => "work_type", "y" => "tech_id", 'm[0]' => 'module'],
                        "target"=>"_blank",
                    ],"PENDING"=> [
                        "url"=>"/en/private/sales_service/service.php?m[1]=listing&m[2]=search&status=PENDING",
                        "params" => ["x" => "work_type", "y" => "tech_id", 'm[0]' => 'module'],
                        "target"=>"_blank",
                    ],"IN PROGRESS"=> [
                        "url"=>"/en/private/sales_service/service.php?m[1]=listing&m[2]=search&status=IN+PROGRESS",
                        "params" => ["x" => "work_type", "y" => "tech_id", 'm[0]' => 'module', 'state' => 'IN PROGRESS'],
                        "target"=>"_blank",
                    ],"late"=> [
                        "url"=>"/en/private/sales_service/service.php?m[1]=listing&m[2]=search&status=IN+PROGRESS",
                        "params" => ["x" => "work_type", "y" => "tech_id", 'm[0]' => 'module', 'late' => 'late'],
                        "target"=>"_blank",
                    ],"SUSPENDED"=> [
                        "url"=>"/en/private/sales_service/service.php?m[1]=listing&m[2]=search&status=SUSPENDED",
                        "params" => ["x" => "work_type", "y" => "tech_id", 'm[0]' => 'module'],
                        "target"=>"_blank",
                    ],
                ],
            ]
        );
        $body .= '<a href="/en/private/sales_service/service.php?m[0]=csr" title="Customer Service Request">CSR LINK</a>';
        $body .= $reportASTDashboard->fetch();

        $reportASTTasksDashboard = new tldReportColumnar(
        $dashboardTasks,
            [
                "xItems" => [
                    'work_type' => 'Work Type',
                    'IN PROGRESS' => 'Open - In Progress',
                    'late' => 'Late',
                    'totals' => 'Totals'
                ],
                "title" => "AST Tasks Dashboard (for all BUs)",
                "links" => [
                    "totals" => [
                        "url" => "/en/private/calendar/calendar.php?m[0]=tasks&m[1]=taskList&m[2]=byDueByMod&x=ALL&y=ALL",
                        "params" => ["userid" => "tech_id"],
                        "target" => "_blank",
                    ], "IN PROGRESS" => [
                        "url" => "/en/private/calendar/calendar.php?m[0]=tasks&m[1]=taskList&m[2]=byDueByMod&x=Due&y=ALL",
                        "params" => ["userid" => "tech_id"],
                        "target" => "_blank",
                    ], "late" => [
                        "url" => "/en/private/calendar/calendar.php?m[0]=tasks&m[1]=taskList&m[2]=byDueByMod&x=Late&y=ALL",
                        "params" => ["userid" => "tech_id"],
                        "target" => "_blank",
                    ],
                ],
            ]
        );
        $body .= $reportASTTasksDashboard->fetch();
    }

    $report = new tldReportColumnar(
        tldSB3::byLatest(),
        array(
            "xItems"=>array(
                "id"=>"SB#",
                "dt"=>"Date",
                "poster_fullname"=>"Poster",
                "factory"=>"Factory",
                "status"=>"Status",
                "category"=>"Category",
                "confidential"=>"Confidential?",
                "ifactor"=>"IF",
                "title"=>"Title",
            ),
            "title"=>"Recently Added SBs",
            "links"=>array(
                "id"=>"/en/private/product_support/index.ps.php?m[0]=sb&m[1]=view&id="
            )
        )
    );
    $body .= $report->fetch();
break;
}
if (isset($JS_INCLUDE)) {
    $smarty->assign("js_includes",$JS_INCLUDE);
}
$smarty->assign("menu",$DEFAULT_MENU.(isset($menu) ? $menu : ''));
$smarty->assign("body",$body);
if(empty($title)) {
    $title = $DEFAULT_TITLE;
}
$smarty->assign("title",$title);
$smarty->assign("error",implode("<br>", $DEFAULT_ERROR));
$smarty->assign("success",implode("<br>", $DEFAULT_SUCCESS));

if(empty($template))   {
    $template = $DEFAULT_TEMPLATE;
}

if($template !== "NO_TEMPLATE") {
    $smarty->display($template);
}
