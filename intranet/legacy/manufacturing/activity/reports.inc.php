<?php
include_once("sales_service.inc.php");
$DEFAULT_TITLE .= "\Reports";

switch($m[2]) {
case 'varianceReport':
    if (!$user->isInGroup(["role_CFO", "gg_ADMIN", "role_COO", "role_CMO", "role_PSM", "role_PSE", "role_PSA", "role_CEO"])) {
        $DEFAULT_ERROR[] = "ERROR: You do not have permissions to view this record...";
        break;
    }
    switch($m[3]){
		case 'fullCSV':
			$data = $_SESSION['data_report'];
			$report = new tldCSV(
					$data,
					array(
							"xItems"=>array(
								"id"					=>"ID#",
								"er_sn"					=>"ER SN",
								"sol_id"				=>"SOL#",
								"erp_fullname"			=>"Factory",
								"sso_fullname"			=>"SSO",
								"buyer"					=>"Customer",
								"model"					=>"Model",
								"act_margin"			=>"Actual Factory Margin (%)",
								"std_margin"			=>"Standard Factory Margin (%)",
								"proj_margin"			=>"Projected Factory Margin (%)"
							),
							"showTitles"=>true
					)
			);
			$report->out("factory_margins_variance_report.csv");
			exit;
			break;
		case 'xls':
			$data = $_SESSION['data_report'];
			$report = new tldXLS(
					$data,
					array(
							"xItems"=>array(
								"id"					=>"ID#",
								"er_sn"					=>"ER SN",
								"sol_id"				=>"SOL#",
								"erp_fullname"			=>"Factory",
								"sso_fullname"			=>"SSO",
								"buyer"					=>"Customer",
								"model"					=>"Model",
								"act_margin"			=>"Actual Factory Margin (%)",
								"std_margin"			=>"Standard Factory Margin (%)",
								"proj_margin"			=>"Projected Factory Margin (%)"
							),
							"showTitles"=>true
					)
			);
			$report->out("factory_margins_variance_report.xls");
			exit;
			break;
	}

	$form = new HTML_QuickForm('frmReport', 'post');
	$form->addElement(	'hidden', 	'm[0]', 	'activity');
	$form->addElement(	'hidden', 	'm[1]', 	'reports');
	$form->addElement(	'hidden', 	'm[2]', 	'varianceReport');
	$form->addElement(	'header', 	'title', 	'Factory Margins Variance Report (more than 2% variance between Factory Margins) - by Period, Factory');
	$form->addElement(  'date',   	'start', 	'Start', 		array("format"=>"Y-m", 'addEmptyOption'=>FALSE, "minYear"=>date("Y")-3, "maxYear"=>date("Y")));
	$form->addElement(  'date',   	'end', 		'End', 			array("format"=>"Y-m", 'addEmptyOption'=>FALSE, "minYear"=>date("Y")-3, "maxYear"=>date("Y")));
	$form->addElement(	'select', 	'erp_id', 	'Factory',		[""=>"", "28"=>"TLD JST"] + tldLocation::getFactoryList("smartyOptionsIDLocation"));
	$form->addRule('start', 'Required', 'required');
	$form->addRule('end', 'Required', 'required');
	$form->setDefaults(array("start"=>date("Y-m",strtotime('first day of last month')),"end"=>date("Y-m")));
	$form->addElement('submit', 'btnSubmit', 'Submit');

	if(!$form->validate()){
		$body = $form->toHTML();
		break;
	}
	$vars = tldUtils::cleanupFormInput($form->exportValues());
	$start = vsprintf('%1$04d-%2$02d', $vars['start']);
	$end = vsprintf('%1$04d-%2$02d', $vars['end']);
	$a = "date BETWEEN '$start' AND '$end'";
	$erp_id = $vars['erp_id'];
	if($erp_id){
		$a .= "AND erp_id = $erp_id";
		$factoryName = " - ". tldLocation::getLocationByID($erp_id);
	}
	$data = tldMargin::getVariancesByPeriod($a);
	if(isset($_SESSION['data_report']))
		unset($_SESSION['data_report']);
	$_SESSION['data_report']=$data;
	if($data){
		$report = new tldReportColumnar(
				$data,
				array(
						"xItems"=>array(
								"id"					=>"ID#",
								"er_sn"					=>"ER SN",
								"sol_id"				=>"SOL#",
								"erp_fullname"			=>"Factory",
								"sso_fullname"			=>"SSO",
								"buyer"					=>"Customer",
								"model"					=>"Model",
								"act_margin"			=>"Actual Factory Margin (%)",
								"std_margin"			=>"Standard Factory Margin (%)",
								"proj_margin"			=>"Projected Factory Margin (%)"
						),

						"title"	=> "Factory Margin Variance Report (more than 2% variance between Factory Margins) between $start and $end $factoryName",
						"links"	=> array(
								"er_sn"=>"/en/private/product_support/index.ps.php?m[0]=equipment&m[1]=search&m[2]=bySN&sn=",
								"sol_id"=>"/en/private/sales_service/sales.php?m[0]=sol&m[1]=view&id=")
				)
		);
		$DEFAULT_MENU .=<<<EOF
        <br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
        <a href="$php_self?m[0]=activity&m[1]=reports&m[2]=varianceReport&m[3]=xls">Download XLS</a>&nbsp;|&nbsp;
        <a href="$php_self?m[0]=activity&m[1]=reports&m[2]=varianceReport&m[3]=fullCSV">Download CSV</a>
EOF;

		$body = $report->fetch();
	}else{
		$body = $form->toHTML();
		$body .= "<br>No records...";
	}
break;
    case 'projectedMargin':
        if (!$user->isInGroup(["role_CFO", "gg_ADMIN", "role_COO", "role_CMO", "role_PSM", "role_PSE", "role_PSA", "role_CEO", "role_FC"])) {
            $DEFAULT_ERROR[] = "ERROR: You do not have permissions to view this record...";
            break;
        }
        switch($m[3]){
		case 'fullCSV':
			$data = $_SESSION['data_report'];
			$report = new tldCSV(
					$data,
					array(
							"xItems"=>array(
								"sn"						=>"ER SN",
								"model"						=>"Model",
								"dt_psm_approval"			=>"SOL PSM approval Date",
								"sol_id"					=>"SOL#",
								"sso_fullname"				=>"SSO",
								"buyer_customer_display"	=>"Customer",
								"pris_tp_in_dcur"			=>"Negotiated TP",
								"dcur"						=>"Currency",
								"discc_pc"					=>"Customer Discount (%)",
								"discf_pc"					=>"Factory Discount (%)",
								"projected_margin"			=>"Projected Margin (%)"
						),
							"showTitles"=>true
					)
			);
			$report->out("projected_margins_report.csv");
			exit;
			break;
		case 'xls':
			$data = $_SESSION['data_report'];
			$report = new tldXLS(
					$data,
					array(
							"xItems"=>array(
								"sn"						=>"ER SN",
								"model"						=>"Model",
								"dt_psm_approval"			=>"SOL PSM approval Date",
								"sol_id"					=>"SOL#",
								"sso_fullname"				=>"SSO",
								"buyer_customer_display"	=>"Customer",
								"pris_tp_in_dcur"			=>"Negotiated TP",
								"dcur"						=>"Currency",
								"discc_pc"					=>"Customer Discount (%)",
								"discf_pc"					=>"Factory Discount (%)",
								"projected_margin"			=>"Projected Margin (%)"
						),
							"showTitles"=>true
					)
			);
			$report->out("projected_margins_report.xls");
			exit;
			break;
	}

	$form = new HTML_QuickForm('frmReport', 'post');
	$form->addElement(	'hidden', 	'm[0]', 	'activity');
	$form->addElement(	'hidden', 	'm[1]', 	'reports');
	$form->addElement(	'hidden', 	'm[2]', 	'projectedMargin');
	$form->addElement(	'header', 	'title', 	'Projected Margins - by Factory');
	$form->addElement(	'select', 	'erp_id', 	'Factory',		[""=>"", "28"=>"TLD JST"] + tldLocation::getFactoryList("smartyOptionsIDLocation"));
	$form->addRule('erp_id', 'Required', 'required');
	$form->addElement('submit', 'btnSubmit', 'Submit');

	if(!$form->validate()){
		$body = $form->toHTML();
		break;
	}
	$vars = tldUtils::cleanupFormInput($form->exportValues());
	$erp_id = $vars['erp_id'];
	$factoryName = tldLocation::getLocationByID($vars['erp_id']);
	$data = tldEquipment::getProjectedMarginsNoGT($erp_id);
	if(isset($_SESSION['data_report']))
		unset($_SESSION['data_report']);
	$_SESSION['data_report']=$data;
	if($data){
		$report = new tldReportColumnar(
				$data,
				array(
						"xItems"=>array(
								"sn"						=>"ER SN",
								"model"						=>"Model",
								"dt_psm_approval"			=>"SOL PSM approval Date",
								"sol_id"					=>"SOL#",
								"sso_fullname"				=>"SSO",
								"buyer_customer_display"	=>"Customer",
								"pris_tp_in_dcur"			=>"Negotiated TP",
								"dcur"						=>"Currency",
								"discc_pc"					=>"Customer Discount (%)",
								"discf_pc"					=>"Factory Discount (%)",
								"projected_margin"			=>"Projected Margin (%)"
						),

						"title"=>"Projected Margins for $factoryName",
						"links"=>array(
								"sol_id"=>"/en/private/sales_service/sales.php?m[0]=sol&m[1]=view&id=",
								"sn"=>"/en/private/product_support/index.ps.php?m[0]=equipment&m[1]=search&m[2]=bySN&sn=")
				)
		);
		$DEFAULT_MENU .=<<<EOF
        <br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
        <a href="$php_self?m[0]=activity&m[1]=reports&m[2]=projectedMargin&m[3]=xls">Download XLS</a>&nbsp;|&nbsp;
        <a href="$php_self?m[0]=activity&m[1]=reports&m[2]=projectedMargin&m[3]=fullCSV">Download CSV</a>
EOF;

		$body = $report->fetch();
	}else{
		$body = $form->toHTML();
		$body .= "<br>No records...";
	}
break;
case 'factoryMargin':
    if (!$user->isInGroup(["role_CFO", "gg_ADMIN", "role_COO", "role_CEO", "role_CMO", "role_FC", "role_PSM", "role_PSE", "role_PSA"])) {
        $DEFAULT_ERROR[] = "ERROR: You do not have permissions to view this record...";
        break;
	}
	switch($m[3]){
		case 'fullCSV':
			$data = $_SESSION['data_report'];
			$report = new tldCSV(
					$data,
					array(
							"xItems"=>array(
								"id"					=>"ID#",
								"year"					=>"Year",
								"month"					=>"Month",
								"er_sn"					=>"ER SN",
								"sol_id"				=>"SOL#",
								"sso_fullname"			=>"SSO",
								"erp_fullname"			=>"Factory",
								"buyer"					=>"Customer",
								"country"				=>"Country",
								"model"					=>"Model",
								"cur"					=>"Currency",
								"factory_rev"			=>"Transfer Price",
								"discf_pc"				=>"Fact. Discount (%)",
								"dir_margin_per"		=>"Actual Direct Margin (%)",
								"est_dir_margin_per"	=>"Proj. Direct Margin (%)",
								"std_dir_margin_per"	=>"Standard Direct Margin (%)",
								"act_hour"				=>"Actual Hours",
								"std_hour"				=>"Target Hours",
								"std_lab_cost"			=>"Standard Labour Costs",
								"act_lab_cost"			=>"Actual Labour Costs",
								"std_mat"				=>"Standard Material",
								"act_mat"				=>"Actual Material",
								"std_other_mat"			=>"Standard Other Material",
								"act_other_mat"			=>"Actual Other Material",
								"std_other_dir_cost"	=>"Standard Other Direct Costs",
								"act_other_dir_cost"	=>"Actual Other Direct Costs",
								"comment"				=>"Comments"
						),
							"showTitles"=>true
					)
			);
			$report->out("factory_margins_report.csv");
			exit;
			break;
		case 'xls':
			$data = $_SESSION['data_report'];
			$report = new tldXLS(
					$data,
					array(
							"xItems"=>array(
								"id"					=>"ID#",
								"year"					=>"Year",
								"month"					=>"Month",
								"er_sn"					=>"ER SN",
								"sol_id"				=>"SOL#",
								"sso_fullname"			=>"SSO",
								"erp_fullname"			=>"Factory",
								"buyer"					=>"Customer",
								"country"				=>"Country",
								"model"					=>"Model",
								"cur"					=>"Currency",
								"factory_rev"			=>"Transfer Price",
								"discf_pc"				=>"Fact. Discount (%)",
								"dir_margin_per"		=>"Actual Direct Margin (%)",
								"est_dir_margin_per"	=>"Proj. Direct Margin (%)",
								"std_dir_margin_per"	=>"Standard Direct Margin (%)",
								"act_hour"				=>"Actual Hours",
								"std_hour"				=>"Target Hours",
								"std_lab_cost"			=>"Standard Labour Costs",
								"act_lab_cost"			=>"Actual Labour Costs",
								"std_mat"				=>"Standard Material",
								"act_mat"				=>"Actual Material",
								"std_other_mat"			=>"Standard Other Material",
								"act_other_mat"			=>"Actual Other Material",
								"std_other_dir_cost"	=>"Standard Other Direct Costs",
								"act_other_dir_cost"	=>"Actual Other Direct Costs",
								"comment"				=>"Comments"
						),
							"showTitles"=>true
					)
			);
			$report->out("factory_margins_report.xls");
			exit;
			break;
	}
    
	$form = new HTML_QuickForm('frmReport', 'post');
	$form->addElement(	'hidden', 	'm[0]', 	'activity');
	$form->addElement(	'hidden', 	'm[1]', 	'reports');
	$form->addElement(	'hidden', 	'm[2]', 	'factoryMargin');
	$form->addElement(	'header', 	'title', 	'Factory Margins - by Period, Factory');
	$form->addElement(  'date',   	'start', 	'Start', 		array("format"=>"Y-m", 'addEmptyOption'=>FALSE, "minYear"=>date("Y")-3, "maxYear"=>date("Y")));
	$form->addElement(  'date',   	'end', 		'End', 			array("format"=>"Y-m", 'addEmptyOption'=>FALSE, "minYear"=>date("Y")-3, "maxYear"=>date("Y")));
	$form->addElement(	'select', 	'erp_id', 	'Factory',		[""=>"", "28"=>"TLD JST"] + tldLocation::getFactoryList("smartyOptionsIDLocation"));
	$form->addRule('start', 'Required', 'required');
	$form->addRule('end', 'Required', 'required');
	$form->setDefaults(array("start"=>date("Y-m",strtotime('first day of last month')),"end"=>date("Y-m")));
	$form->addElement('submit', 'btnSubmit', 'Submit');

	if(!$form->validate()){
		$body = $form->toHTML();
		break;
	}
	$vars = tldUtils::cleanupFormInput($form->exportValues());
	// Security check for role_FC and role_COO based on Factory
    $factory_grps = $user->isInGroup(["role_COO", "role_CEO", "role_FC", "role_PSM", "role_PSE", "role_PSA"], ["return_rows"=>true]);
	$level = array();
	if(count($factory_grps)){
	    foreach($factory_grps as $factory_grp){
	        $level[] = $factory_grp['level'];
	    }
		$factoryID = $vars['erp_id'];
    	$BuID = $user->getBUID();
		if (!in_array($BuID, [5, 59]) && !in_array(tldLocation::getERPByID($factoryID), $level)){
			$DEFAULT_ERROR[] = "ERROR: You are not allowed to check the data for this Factory...";
			break;
		}
	}
	// Year and Month calculation
	$start = vsprintf('%1$04d-%2$02d', $vars['start']);
	$end = vsprintf('%1$04d-%2$02d', $vars['end']);
	$erp_id = $vars['erp_id'];
	$a = "date BETWEEN '$start' AND '$end'";
	if($vars['erp_id']){
		$a .= "AND erp_id = $erp_id";
		$factoryName = " - ". tldLocation::getLocationByID($vars['erp_id']);
	}
	$data = tldMargin::getMarginsByPeriod($a);
	if(isset($_SESSION['data_report']))
		unset($_SESSION['data_report']);
	$_SESSION['data_report']=$data;
	if($data){
		$report = new tldReportColumnar(
				$data,
				array(
						"xItems"=>array(
								"id"					=>"ID#",
								"date"					=>"Factory Revenue Recognition Date",
								"er_sn"					=>"ER SN",
								"sol_id"				=>"SOL#",
								"sso_fullname"			=>"SSO",
								"buyer"					=>"Customer",
								"model"					=>"Model",
								"cur"					=>"Currency",
								"factory_rev"			=>"Transfer Price",
								"discf_pc"				=>"Fact. Discount (%)",
								"dir_margin_per"		=>"Actual Direct Margin (%)",
								"est_dir_margin_per"	=>"Proj. Direct Margin (%)",
								"std_dir_margin_per"	=>"Standard Direct Margin (%)",
								"act_hour"				=>"Actual Hours",
								"std_hour"				=>"Target Hours",
								"comment"				=>"Comments"
						),

						"title"=>"Factory Margins between $start and $end $factoryName",
						"links"=>array("id"=>"/en/private/finance/finance.php?m[0]=mfg_margins&m[1]=view&id=",
								"sol_id"=>"/en/private/sales_service/sales.php?m[0]=sol&m[1]=view&id=",
								"er_sn"=>"/en/private/product_support/index.ps.php?m[0]=equipment&m[1]=search&m[2]=bySN&sn=")
				)
		);
		$DEFAULT_MENU .=<<<EOF
        <br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
        <a href="$php_self?m[0]=activity&m[1]=reports&m[2]=factoryMargin&m[3]=xls">Download XLS</a>&nbsp;|&nbsp;
        <a href="$php_self?m[0]=activity&m[1]=reports&m[2]=factoryMargin&m[3]=fullCSV">Download CSV</a>
EOF;

		$body = $report->fetch();
	}else{
		$body = $form->toHTML();
		$body .= "<br>No records...";
	}
break;
default:
	$body = $smarty->fetch("$PATH/activity/reports/homepage.reports.tpl");
break;
}
?>