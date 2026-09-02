<?php
include_once('erp.others.inc.php');
include_once('erp.inc.php');
include_once("common.inc.php");
include_once('forms_and_reports.inc.php');
require_once("HTML/QuickForm.php");
$JS_INCLUDE=array("/shared/javascript/overlib/overlib.js");

$smarty = tldUtils::getSmarty("intranet");

switch($m[2]){

 	case 'inactivesBaanUsers':

 		$xItems = array(
 				"t_user"=>"Baan User",
 				"t_uusr"=>"Win User",
 				"t_name"=>"User Name",
 				"t_utyp"=>"User Type",
 				"t_comp"=>"Default Company",
 				"t_clan"=>"Language",
 				"t_last"=>"Last Login"
 		);
 		// Get listing
 		$erpList = tldLocation::getERPList("smartyOptions");

 		// Get form
 		$userType = array("All", "SuperUser", "NormalUser");
 		$form = new HTML_QuickForm('frminactivesBaanUsers', 'get', "", "", "", true);
 		$form->addElement(	'hidden', 'm[0]', 'user');
 		$form->addElement(	'hidden', 'm[1]', 'reports');
 		$form->addElement(	'hidden', 'm[2]', 'inactivesBaanUsers');
 		$form->addElement(	'header', 'title','Select:');
 		$form->addElement(	'select', 'Ut', 'User Type', array_combine($userType, $userType));
 		$form->addElement(	'date', 'x', 'From date', array("format"=>"Y-m-d","minYear"=>2012,"maxYear"=>date('Y')));
 		$form->addElement(	'submit', 'btnSubmit', 'Submit');
 		$form->setDefaults(array("x"=>"2012-12-27"));

 		if($form->validate()){
 			$vars = tldUtils::cleanupFormInput($form->exportValues());
 			$vars['from']=implode('-',$vars['x']);
 			$query="select * from tttaad200000 where 1=1 ";
			if($vars['Ut']=='SuperUser') { $query.='and t_utyp=1'; }
			if($vars['Ut']=='NormalUser') { $query.='and t_utyp=2'; }
 			$rows = tldUtils::getSqlToAssocArray($query, 'odbc', array("src"=>"baan"));

			// Add last login date
 			foreach ($rows as $key1 => $value1) {
 				if ($rows[$key1]['t_utyp']=='1') { $rows[$key1]['t_utyp']='S'; };
 				if ($rows[$key1]['t_utyp']=='2') { $rows[$key1]['t_utyp']='N'; };
 				if ($rows[$key1]['t_clan']=='2') { $rows[$key1]['t_clan']='EN'; };
 				if ($rows[$key1]['t_clan']=='4') { $rows[$key1]['t_clan']='FR'; };
 				$query="select max(dt) as dt from erp_licences where baan_id='".$rows[$key1]['t_user']."' and dt>='".$vars['from']."'";
 				$rows2 = tldUtils::getSqlToAssocArray($query);
 				$rows[$key1]['t_last']=$rows2[0]['dt'];
 				//$rows[$key1]['t_last']=$query;

 			}


 			$caption = "Last login per Baan user (since 2012-12-27)";

 		} //else {
 		$body = $form->toHTML();

 		$report = new tldReportColumnar($rows,
 				array(
 						"xItems"=>$xItems,
 						"title"=>$caption,
 						"links"=>$links
 				)
 		);
 		$body .= $report->fetch();
 		//}

 	break;

 	case 'PerfTestBaanDB':
 	    $xItems = array(
 	    "t_cprj"=>"Project",
 	    "t_pdno"=>"Prod order",
 	    "t_pono"=>"Position",
 	    "t_sitm"=>"Item",
 	    "t_cwar"=>"Warehouse"
		);
		// Get listing
		$erpList = tldLocation::getERPList("smartyOptions");

		// Get form
		$form = new HTML_QuickForm('frmPerfTestBaanDB', 'get', "", "", "", true);
		$form->addElement(	'hidden', 'm[0]', 'user');
		$form->addElement(	'hidden', 'm[1]', 'reports');
		$form->addElement(	'hidden', 'm[2]', 'PerfTestBaanDB');
		$form->addElement(	'header', 'title','Select:');
		$form->addElement(	'text', 'L', 'Results');
		$form->addElement(	'submit', 'btnSubmit', 'Submit');
		$form->setDefaults(array("L"=>"50"));

		if($form->validate()){
			$vars = tldUtils::cleanupFormInput($form->exportValues());
			$query="select top ".$vars['L']." t_cprj, t_pdno, t_pono, t_sitm, t_cwar from tticst001500 where t_aldt >= '01/01/2014' and t_aldt <= '12/31/2014' ";
			echo "Start: ".date('Y-m-d H:i:s')."<br>";
			$rows = tldUtils::getSqlToAssocArray($query, 'odbc', array("src"=>"baan"));
			echo "Stop: ".date('Y-m-d H:i:s')."<br>";
			$caption = "BaanDB perf test<br>";
			echo($query);

		} //else {
		$body = $form->toHTML();

		$report = new tldReportColumnar($rows,
			array(
				"xItems"=>$xItems,
				"title"=>$caption,
				"links"=>$links
			)
		);
		$body .= $report->fetch();
		//}

	break;

	case 'PerfTestWebDB':
		$xItems = array(
			"sn"=>"Serial",
			"model"=>"Model",
			"type"=>"Type",
			"customer_name"=>"Customer",
			"t_prno"=>"Project"
		);
		// Get listing
		$erpList = tldLocation::getERPList("smartyOptions");

		// Get form
		$form = new HTML_QuickForm('frmPerfTestWebDB', 'get', "", "", "", true);
		$form->addElement(	'hidden', 'm[0]', 'user');
		$form->addElement(	'hidden', 'm[1]', 'reports');
		$form->addElement(	'hidden', 'm[2]', 'PerfTestWebDB');
		$form->addElement(	'header', 'title','Select:');
		$form->addElement(	'text', 'L', 'Results');
		$form->addElement(	'submit', 'btnSubmit', 'Submit');
		$form->setDefaults(array("L"=>"50"));

		if($form->validate()){
			$vars = tldUtils::cleanupFormInput($form->exportValues());
			$query="select sn, model, type, customer_name, t_prno FROM `service` where date_entered like '2014%' and man_location='TLD MTL' and t_prno<>'' limit ".$vars['L'];
			echo "Start: ".date('Y-m-d H:i:s')."<br>";
			$rows = tldUtils::getSqlToAssocArray($query);
			echo "Stop: ".date('Y-m-d H:i:s')."<br>";

			$caption = "WebDB perf test<br>";
			echo($query);

		} //else {
		$body = $form->toHTML();

		$report = new tldReportColumnar($rows,
			array(
				"xItems"=>$xItems,
				"title"=>$caption,
				"links"=>$links
			)
		);
		$body .= $report->fetch();
		//}

	break;

	case 'PerfTestBothDB':
		$xItems = array(
			"sn"=>"Serial",
			"model"=>"Model",
			"type"=>"Type",
			"customer_name"=>"Customer",
			"t_prno"=>"Project",
			"nb"=>"Lines"
		);
		// Get listing
		$erpList = tldLocation::getERPList("smartyOptions");

		// Get form
		$form = new HTML_QuickForm('frmPerfTestBothDB', 'get', "", "", "", true);
		$form->addElement(	'hidden', 'm[0]', 'user');
		$form->addElement(	'hidden', 'm[1]', 'reports');
		$form->addElement(	'hidden', 'm[2]', 'PerfTestBothDB');
		$form->addElement(	'header', 'title','Select:');
		$form->addElement(	'text', 'L', 'Results');
		$form->addElement(	'submit', 'btnSubmit', 'Submit');
		$form->setDefaults(array("L"=>"50"));

		if($form->validate()){
			$vars = tldUtils::cleanupFormInput($form->exportValues());
			$query="select sn, model, type, customer_name, t_prno FROM `service` where date_entered like '2014%' and man_location='TLD MTL' and t_prno<>'' limit ".$vars['L'];
			echo "Start: ".date('Y-m-d H:i:s')."<br>";
			$rows = tldUtils::getSqlToAssocArray($query);
			foreach($rows as $key1=>$value1) {
				$queryBaan="select count(*) as nb from tticst001500 where t_cprj=".$rows[$key1]['t_prno'];
				$rowsBaan = tldUtils::getSqlToAssocArray($queryBaan, "odbc", array("src"=>"baan"));
				$rows[$key1]['nb']=$rowsBaan[0]['nb'];
			}
			echo "Stop: ".date('Y-m-d H:i:s')."<br>";

			$caption = "WebDB & BaanDB join perf test<br>";

		} //else {
		$body = $form->toHTML();

		$report = new tldReportColumnar($rows,
			array(
				"xItems"=>$xItems,
				"title"=>$caption,
				"links"=>$links
			)
		);
		$body .= $report->fetch();
		//}

	break;
	default:
		//echo("$PATH/user/reports/homepage.reports.tpl");
		$body = $smarty->fetch("$PATH/user/reports/homepage.reports.tpl");
	break;
}

?>
