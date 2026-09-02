<?php
include_once("sales_service.inc.php");
$DEFAULT_TITLE .= "\SB";
// Get MOO ID
$moo_id = tldModule::getMOOIDByModule("sb");
$DEFAULT_MENU .=<<<EOF
	<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
	<a href="$php_self?m[0]=sbs">Home</a>
	&nbsp;|&nbsp;<a href="$php_self?m[0]=sbs&m[1]=form&m[2]=byNum">By Num</a>
	&nbsp;|&nbsp;<a href="$php_self?m[0]=sbs&m[1]=form&m[2]=search">Search</a>
	&nbsp;|&nbsp;<a href="$php_self?m[0]=sbs&m[1]=reports">Reports</a>
	&nbsp;|&nbsp;<a href="/en/private/directory/index.php?m[0]=people&m[1]=view&id=$moo_id" title="Module Owner">Owner</a>
	&nbsp;|&nbsp;<a href="$php_self?m[0]=sbs&m[1]=help">Help</a>
EOF;

if($user->isInGroup(array("sbs","gg_SUPPORT","gg_ADMIN"))){
	$DEFAULT_MENU .=<<<EOF
	&nbsp;|&nbsp;<a href="/en/private/product_support/sbs/sbs_admin.php">Maintain SB Records</a>
EOF;
}

$DEFAULT_ERROR[]="WARNING: This module is only available for SB in IMPLEMENTATION";
$DEFAULT_ERROR[]="Please use SB3 for new SB, LOCKED & CLOSED SB are left for reference";
$DEFAULT_ERROR[]="<br>";

switch($m[1]){
case 'help':
	switch($m[2]){
	case 'intro':
		$body .= $smarty->fetch("$PATH/sbs/help/sbs.help.tpl");
	break;
	default:
	    $smarty->assign('DMS_URL',$DMS_URL);
		$body .= $smarty->fetch("$PATH/sbs/help/homepage.help.tpl");
	}
break;
case 'form':
	switch($m[2]){
	case 'byNum':
		$DEFAULT_TITLE .= "\SBs by Number";
		$form = new HTML_QuickForm('frmByNum', 'post');
		$form->addElement(	'hidden', 'm[0]', 'sbs');
		$form->addElement(	'hidden', 'm[1]', 'view');
		$form->addElement(	'header', 'title', "SB by Number");
		$form->addElement(	'text', 'id', 'SB#');
		$form->addElement(	'submit', 'btnSubmit', 'Submit');
		$body .= $form->toHTML();
	break;
	case 'search':
		$DEFAULT_TITLE .= "\Search SBs";
		$form = new HTML_QuickForm('frmSearch', 'post');
		$form->addElement(	'hidden', 'm[0]', 'sbs');
		$form->addElement(	'hidden', 'm[1]', 'listing');
		$form->addElement(	'hidden', 'm[2]', 'search');
		$form->addElement(	'header', 'title', "Search SBs");
		$form->addElement(	'text', 'x', 'Search for...');
		$form->addElement(	'submit', 'btnSubmit', 'Submit');
		$body = $form->toHTML();
	break;
	case 'byCustomerName':
		$form = new HTML_QuickForm('frmbyCustomerName');
		$form->addElement(	'header', 'title', 'Compulsory SBs by Customer');
		$form->addElement(	'hidden', 'm[0]', 'sbs');
		$form->addElement(	'hidden', 'm[1]', 'listing');
		$form->addElement(	'hidden', 'm[2]', 'byCustomerName');
		$form->addElement(	'select', 'x', 'Customer', tldCustomer::getList("smartyOptions"));
		$form->addElement(	'submit', 'btnSubmit', 'Submit');
		$body .= $form->toHTML();
	break;
	case 'byCustomerNameAirportCode':
		$form = new HTML_QuickForm('frmbyCustomerNameAirportCode', 'post');
		$form->addElement(	'header', 'title', 'SBs by Customer and/or Location');
		$form->addElement(	'hidden', 'm[0]', 'sbs');
		$form->addElement(	'hidden', 'm[1]', 'listing');
		$form->addElement(	'hidden', 'm[2]', 'byCustomerNameAirportCode');
		$form->addElement(	'select', 'x', 'Customer', array(""=>"")+tldCustomer::getList("smartyOptions"));
		$form->addElement(	'select', 'y', 'Airport Code', tldUtils::getSqlToAssocArray(
			"SELECT distinct airport_code FROM service ORDER BY airport_code","smartyOptions", 'airport_code')
		);
		$form->addElement(	'submit', 'btnSubmit', 'Submit');
		$body .= $form->toHTML();
	break;
	case 'sbsStatsByFactory':
		$form = new HTML_QuickForm('frmsbsStatsByFactory');
		$form->addElement(	'header', 'title', 'SBs stats by Factory');
		$form->addElement(	'hidden', 'm[0]', 'sbs');
		$form->addElement(	'hidden', 'm[1]', 'listing');
		$form->addElement(	'hidden', 'm[2]', 'sbsStats');
		$form->addElement(	'select', 'x', 'Factory', array(""=>"")+tldLocation::getFactoryList("smartyOptionsLocationLocation"));
		$form->addElement(	'submit', 'btnSubmit', 'Submit');
		$body .= $form->toHTML();
	break;
	}
break;
case 'view':
    include_once("view.inc.php");
break;
case "reports":
	switch($m[2]){
    case 'bySSOStatus':
        $form = new tldMatrix(
            tldSB::countBySSOStatus(),
            "vstatus", "sales_org", "num",
            "$php_self?m[0]=sbs&m[1]=listing&m[2]=bySSOStatus",
            "ER Affected Count by SSO, SB Status (Old Dashboard)"
        );
        $body .= $form->fetch();
    break;
	case "sbStatsByFactorySB":
        $form = new tldReportColumnar(
            tldSB::getSBSStats(),
			array(
                "xItems"=>array(
                    "id"=>"SB#",
					"entered_date"=>"Date",
					"factory"=>"factory",
					"title"=>"Title",
					"numAffected"=>"Affected",
					"numDone"=>"Done"
				),
    			"title"=>"OPEN Compulsory SB Stats",
    			"links"=>array("id"=>"/en/private/product_support/sbs/sbs_admin.php?mode=record_view&form_type=main_tpl&id=")
			)
		);
		$body .= $form->fetch();
	break;
	default:
		$body .= $smarty->fetch("$PATH/sbs/reports/homepage.reports.tpl");
	}
break;
case 'listing':
    $levels = array("factory","urgency");
    $links = array();
	$xItems = array(
		"id"=>"SB#",
		"vstatus"=>"Status",
		"entered_date"=>"Date",
		"urgency"=>"Urgency",
		"sb_type"=>"Type",
		"title"=>"Title"
	);
	switch($m[2]){
    case 'byERBySSO':
        $levels = array("apc");
        $status = TldDatabase::escape($status);
        $sso = TldDatabase::escape($sso);
        // Create constraints
        $a = "sb.status LIKE '$status' AND sso LIKE '$sso'";
        // Additional constraints
        if(!empty($urgency)){
            $urgency = TldDatabase::escape($urgency);
            $a.= " AND sb.urgency LIKE '$urgency'";
        }
        // Setting columns
        $xItems = array(
            "id"=>"SB#",
            "entered_date"=>"Date",
            "status"=>"Status",
            "sb_type"=>"Type",
            "urgency"=>"Severity",
            "title"=>"Title",
            "factory"=>"Factory",
            "sn"=>"ER SN",
            "model"=>"Model",
            "apc"=>"APC",
            "sso"=>"SSO",
            "user_customer_display"=>"USER Customer",
            "isTBD"=>"TBD?",
            "isDone"=>"Done?",
        );
        $xItemsCSV = $xItems;

        switch($m[3]){
        case 'not_done':
            $er_status = "NOT DONE";
            $a.=" AND isTBD='Y' AND isDone='N'";
        break;
        }

        $title = "List of ER '$er_status' in '$status' for SSO '$sso'";
        $rows = tldSB::getEquipmentByConstraints($a);
    break;
	case 'byCustomerNameAirportCode':
		if(!empty($x)){
			$x = TldDatabase::escape($x);
			$cu = new tldCustomer($x);
			$cu_name = $cu->getCustomerName();
			$vars['customer_name']=$cu_name;
		}
		if(!empty($y)){
			$vars['airport_code'] = TldDatabase::escape($y);
		}
		if(!empty($vars['customer_name']) && !empty($vars['airport_code']))
			$constraint = " customer_name LIKE '".$vars['customer_name']."' AND airport_code LIKE '".$vars['airport_code']."' ";
		elseif(!empty($vars['customer_name']))
			$constraint = " customer_name LIKE '".$vars['customer_name']."' ";
		elseif(!empty($vars['airport_code']))
			$constraint = " airport_code LIKE '".$vars['airport_code']."' ";
		$title = "SB by Customer $cu_name - Airport Code $airport_code";
		$rows = tldSB::createList($constraint);
	break;
	case 'byCustomerName':
		$cu = new tldCustomer(TldDatabase::escape($x));
		$cu_nama = $cu->getCustomerName();
		$title = "SB by Customer $cu_name";
		$rows = tldSB::createList(" customer_name='$cu_nama'");
	break;
	case 'sbsStats':
		if(!empty($x)){
			$opt = array("mode"=>"byFactory","factory"=>TldDatabase::escape($x));
			$rows = tldSB::getSBSStats($opt);
		}
		else $rows = tldSB::getSBSStats();
		$xItems['num_tbd'] = "TDB Responses";
		$xItems['numAffected'] = "Affected";
		$xItems['numDone'] = "Done";
		$title = "SB Stats";
	break;
	case 'search':
		$rows = tldSB::search("%".TldDatabase::escape($x)."%");
		$title = "Search results for $x";
	break;
	case 'byERPStatus':
       $rows = tldSB::byERPStatus($y, $x);
       $title = "SB Records for Factory $y - Status $x";
	break;
    case 'bySSOStatus':
        $rows = tldSB::bySSOStatus($y, $x);
        $title = "ER with SB associated for SSO $y - Status $x";
        $xItems = array(
            "id"=>"SB#",
            "er_sn"=>"ER SN#",
            "vstatus"=>"Status",
            "entered_date"=>"Date",
            "urgency"=>"Urgency",
            "sb_type"=>"Type",
            "title"=>"Title"
        );
        $links = array(
            "er_sn"=>array(
                "url"=>"$php_self?m[0]=equipment&m[1]=view",
                "params"=>array("id"=>"er_id")
            )
        );
        $xItemsCSV = $xItems;
	break;
	}

// RESULTS ----->

	switch($out){
	case "csv":
        $xItemsCSV = $sess['sb']['csvCol'];
        if(empty($xItemsCSV)){
            $xItemsCSV = array(
                "id"=>"SB#",
                "vstatus"=>"Status",
                "numAffected"=>"Num Affected",
                "numDone"=>"Num Done",
                "num_tbd"=>"Num TBD",
                "entered_date"=>"Date",
                "urgency"=>"Urgency",
                "sb_type"=>"Type",
                "title"=>"Title"
            );
        }
        $report = new tldCSV(
            $sess['sb']['listing'],
            array("xItems"=>$xItemsCSV, "showTitles"=>true)
        );
		$report->out();
		exit;
	break;
    default:
        $DEFAULT_MENU.=<<<EOF
        <br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
        <a href="$php_self?m[0]=sbs&m[1]=listing&out=csv" title="Download CSV version">Download CSV</a>
EOF;
        // Keep in session
        $sess['sb']['listing'] = $rows;
        $sess['sb']['csvCol'] = $xItemsCSV;
        // Display online
        $report = new tldReportMultiLevel(
            $rows,
            $levels,
            $xItems,
            array(
                "title"=>$title,
                "passField"=>"id",
                "url"=>"$php_self?m[0]=sbs&m[1]=view&id=",
                "links"=>$links
            )
        );
        $body .= $report->fetch();
        if(empty($rows)){
            $body .= "No results...";
        }
    break;
	}
break;
case 'charts':
	switch($m[2]){
	case 'historyAllFactories':
		$body =<<<EOF
		<h3>Count of SBS opened by Year, Month for Previous 12 Months</h3>
        <img src="sbs/reports/graphs.php?m[0]=historyAllFactories"><br>
EOF;
	break;
	case 'statsByCompulsoryFactory12Months':
		$body .=<<<EOF
		<h3>Compulsory SB Stats for All Factories, Previous 12 Months</h3>
<img src="sbs/reports/graphs.php?m[0]=SBSStats&m[1]=statsByCompulsoryFactory12Months"><br>
EOF;
	break;
	case 'statsByRecommendedFactory12Months':
		$body .=<<<EOF
		<h3>Recommended SB Stats for All Factories, Previous 12 Months</h3>
<img src="sbs/reports/graphs.php?m[0]=SBSStats&m[1]=statsByRecommendedFactory12Months"><br>
EOF;
	break;
	}
break;
default:
	$body = $smarty->fetch("$PATH/sbs/homepage.sbs.tpl");
    // SB by Factory
    $form = new tldMatrix(
        tldSB::countByERPStatus(),
        "vstatus", "factory", "num",
        "$php_self?m[0]=sbs&m[1]=listing&m[2]=byERPStatus",
        "SB Count by Factory, Status",
		array(
            "xItems"=>array(
                "LOCKED",
                "IMPLEMENTATION",
                "CLOSED"
            )
		)
    );
    $body .= $form->fetch();
    // ER stats implementation by SSO
    $body.=include('sb.matrix.stats.er.tpl.php');
    // Latest
	$report = new tldReportColumnar(
        tldSB::getLatest(),
		array(
			"xItems"=>array(
                "id"=>"SB#",
				"vstatus"=>"Status",
				"urgency"=>"Severity",
				"sb_type"=>"Type",
				"numAffected"=>"Affected#",
				"num_tbd"=>"TBD",
				"numDone"=>"Done",
				"title"=>"Title"
			),
		"title"=>"Recently Added SBs",
		"links"=>array("id"=>"$php_self?m[0]=sbs&m[1]=view&id="))
	);
	$body .= $report->fetch();
}


function getAffectedPage(){
	GLOBAL $sb;
	GLOBAL $report;
	$fields = array(
//	"id"=>"Equipment ID#",
						"sn"=>"Equipment SN#",
						"customer_name"=>"Customer",
						"airport_code"=>"Airport Code",
						"model"=>"Model",
						"done"=>"Done?"
					);

	if($sb->getUrgency() === "SB:RECOMMENDED"){
		$fields["tbd"] = "TBD";
	}
	$report = new tldReportMultiLevel($sb->getAffectedEquipment(),
				array("sales_org"),
				$fields,
					array(
					"passField"=>"id",
					"title"=>"Affected Equipment",
					"url"=>"$php_self?m[0]=equipment&m[1]=view&id=",
					"showNumberOfRows"=>true
					)
				);
	$body .= $report->fetch();
	return $body;
}

function getSummaryPage(){
	GLOBAL $sb;
	GLOBAL $report;
	$fields = array(
        "airport_code"=>"Airport Code",
        "done"=>"Done?",
        "log_id"=>"Log#",
        "log_dt"=>"Log date",
        "spr_id"=>"SPR#",
        "spr_status"=>"SPR Status",
        "csr_id"=>"CSR#",
        "csr_status"=>"CSR Status"
    );

	$report = new tldReportMultiLevel(
	    $sb->getSummary(),
		array("sales_org","customer_name","sn"),
		$fields,
		array(
			"passField"=>"id",
			"title"=>"Summary",
			"url"=>"$php_self?m[0]=equipment&m[1]=view&id=",
			"showNumberOfRowsByLevel"=>array(1),
			"links"=>array(
                "log_id"=>"/en/private/common/index.php?m[0]=logs&m[1]=view&id=",
                "csr_id"=>"/en/private/sales_service/service.php?m[0]=csr&m[1]=view&id=",
                "spr_id"=>"/en/private/parts/parts.php?m[0]=spr&m[1]=view&id="
            )
		)
	);
	$body .= $report->fetch();
	return $body;
}
