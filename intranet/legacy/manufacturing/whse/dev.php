<?php
include_once("eng.inc.php");
include_once("erp.inc.php");
include_once("common.inc.php");
include_once("vendor.inc.php");
include_once("forms_and_reports.inc.php");
include_once('publications.inc.php');
include_once('vault.inc.php');
include_once('erp.others.inc.php');
include_once('erp.inc.php');
include_once('quality.inc.php');
include_once ("HTML/QuickForm.php");

session_start();
if(!isset($_SESSION['sess'])) $_SESSION['sess'] = null;
$sess =& $_SESSION['sess'];

$user = new tldUser($GLOBALS["PHP_AUTH_USER"]);
if(!isset($erp)){
    $erp = tldLocation::getERPByID($user->getBUID());
}

$smarty = tldUtils::getSmarty("intranet");
$smarty->assign("locations", tldLocation::getFactoryList("smartyOptions"));

$php_self = $_SERVER['PHP_SELF'];

$DEFAULT_MENU = <<<EOF
    <tld:include src="materials/warehouse/_menu_legacy.html.twig" />
EOF;

$PATH = "manufacturing/whse";
$DEFAULT_TEMPLATE = "intranet.tpl";
$DEFAULT_TITLE = "Manufacturing\Warehouse";
$DEFAULT_ERROR = array();
$form = new HTML_QuickForm('frmInventory', 'get');
$form->addElement(	'hidden', 'm[0]', 'inv');
$form->addElement(	'hidden', 'm[1]', 'byPNPlanned');
$form->addElement(	'header', 'title', "Inventory/PN Information");
$form->addElement(	'text',   'id',    'Part Number', array("size"=>12));
$form->addElement(	'select', 'erp',   'Company', tldLocation::getWarehouseList("smartyOptions")+array("510"=>"TLD DTV"));
$form->setDefaults(array("erp"=>$erp));
$form->addElement(	'submit', 'btnSubmit', 'Submit');
$cell = $form->toHTML();

$cells[] = $cell;

$form = new HTML_QuickForm('frmInventory', 'post');
$form->addElement(	'hidden', 'm[0]', 'inv');
$form->addElement(	'hidden', 'm[1]', 'byPNTransaction');
// 		$form->addElement(	'hidden', 'm[1]', 'view');
$form->addElement('header', 'title', "<div style='background: lightcoral; padding: 0 5px 0 5px;'><div>Planned inventory Movement by item (BAAN)</div><div style='font-style: italic;font-size: 10px'>in Infor LN 'Inventory 360'</div></div>");

$form->addElement(	'text',   'id',    'Part Number', array("size"=>12));
$form->addElement(	'select', 'erp',   'Company', tldLocation::getWarehouseList("smartyOptions")+array("510"=>"TLD DTV"));
$form->setDefaults(array("erp"=>$erp));
$form->addElement(	'submit', 'btnSubmit', 'Submit');

$cell = $form->toHTML();
if($erp === 900){
	$erp = 500;
}
 $warehouseList = array_column((array) tldCWAR::byERP($erp), 't_cwar', 't_cwar');
 $form = new HTML_QuickForm('frmInventory', 'post');
 $form->addElement(	'hidden', 'm[0]', 		'inv');
 $form->addElement(	'hidden', 'm[1]', 		'byLocationTransaction');
 $form->addElement('header', 'title', "<div style='background: lightcoral; padding: 0 5px 0 5px;'><div>Past inventory transaction by location (BAAN)</div><div style='font-size: 12px'>(maximum 1000 records)</div><div style='font-style: italic;font-size: 10px'>Historical data only from BAAN</div></div>");
 $form->addElement(	'text',   'id',    		'Part Number',   ['size' => 12]);
 $form->addElement(	'select', 'warehouse',  'Warehouse', 	 ['' => ''] + $warehouseList);
 $form->addElement(	'text',   'order',  	'Order#',		 ['size' => 12]);
 $form->addElement(	'select', 'range',  	'Date Range', 	 ['1'=>'ALL', '0'=>'Past 12 months']);
 $form->addElement(	'submit', 'btnSubmit', 'Submit');

$cell .= $form->toHTML();

$cells[] = $cell;
$report = new tldHTMLTable(
		$cells,
		array(
				"cols"=>2,
				"attribs"=>array(
						"table"=>"width='100%'",
						"tr"=>" bgcolor='#FFFFFF'"
				)
		)
);
$body = $report->fetch();


tldUtils::logUserAccess();
switch($m[0]){
	case 'reports':
		include("{$m[0]}/{$m[0]}.logic.inc.php");
	break;
	case 'inv':
		include("{$m[0]}/{$m[0]}.logic.inc.php");
	break;
    case 'invseq':

        $sequencesLink[] = [
            "link" => "/en/private/calendar/calendar.php?m[0]=seq&m[1]=new&m[2]=acct.invadjust",
            "desc" => "Start an Inventory Adjustment Sequence(For SPH)",
        ];

        $sequencesLink[] = [
            "link" => "/en/private/calendar/calendar.php?m[0]=seq&m[1]=new&m[2]=acct.positiveadj",
            "desc" => "Start an Positive Inventory Adjustment Sequence(BETA)",
        ];
        $sequencesLink[] = [
            "link" => "/en/private/calendar/calendar.php?m[0]=seq&m[1]=new&m[2]=acct.negativeadj",
            "desc" => "Start an Negative Inventory Adjustment Sequence(BETA)",
        ];
        $sequencesLink[] = [
            "link" => "/en/private/calendar/calendar.php?m[0]=seq&m[1]=new&m[2]=acct.loanpart",
            "desc" => "Start a Loan Parts Request Sequence(BETA)",
        ];

        if (count($sequencesLink) > 0) {
            $body = "<h3>Warehouse Inventory Sequences</h3>";
            $body .= "<ul>";
            foreach ($sequencesLink as $link) {
                $body .= "<li><a href=\"{$link['link']}\">{$link['desc']}</a></li>";
            }
            $body .= "</ul>";
        }
    break;
}
if (isset($JS_INCLUDE)) {
    $smarty->assign("js_includes",$JS_INCLUDE);
}
$smarty->assign("menu", $DEFAULT_MENU.(isset($menu) ? $menu : ''));
if(empty($title)) $title = $DEFAULT_TITLE;
$smarty->assign("title", $title);
$smarty->assign("body", $body);
$smarty->assign("error", implode("<br>", $DEFAULT_ERROR));
// $smarty->assign("menu", $DEFAULT_MENU);
$smarty->assign("body", $body);



if(empty($template)) $template = $DEFAULT_TEMPLATE;
if($template<>"NO_TEMPLATE") $smarty->display($template);
