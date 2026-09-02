<?php
use ApiBundle\Client;
include_once("calendar.inc.php");

if (!$user->isInGroup(["gg_ADMIN", "gg_PARTS", "gg_SALES", "gg_SERVICE", "role_PSM", "role_PSE", "role_PSA", "gg_ACCT"])) {
    $DEFAULT_ERROR[] = "ERROR: You do not have permissions";
    return;
}

$DEFAULT_TITLE .= "/Spare Parts Request";
$DEFAULT_MENU .=<<<EOF
	<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
	<a href="$php_self?m[0]=spr">Home</a>&nbsp;|&nbsp;
	<a href="$php_self?m[0]=spr&m[1]=form&m[2]=byNum" title="Get SPR by number">By number</a>&nbsp;|&nbsp;
	<a href="$php_self?m[0]=spr&m[1]=listing&m[2]=search" title="Search for SPR">Search</a>&nbsp;|&nbsp;
	<a href="$php_self?m[0]=spr&m[1]=reports">Reports</a>
EOF;

// Generic SPH email list linked to SPH location
$_SPH = tldSPR::getSparePartsHubEmails();

switch($m[1]){
case 'listing':
	switch($m[2]){
	case 'search':
		$DEFAULT_TITLE .= "\Search";
		$form = new HTML_QuickForm('frmSPRSearch', 'post');
		$form->addElement(	'hidden', 'm[0]', 'spr');
		$form->addElement(	'hidden', 'm[1]', 'listing');
		$form->addElement(	'hidden', 'm[2]', 'search');
		$form->addElement(	'header', 'title','SPR Search');
		$form->addElement(	'text',   'target', 'Look for', array("size"=>"20"));
		$form->addElement(	'submit', 'btnSubmit', 'Submit');
		$form->addRule('target', 'This is required', 'required');
		if($form->validate()){
			$vars = tldUtils::cleanupFormInput($form->exportValues());
            $rows = tldSPR::search("%{$vars['target']}%");
            $caption = "Search result for '{$vars['target']}'";
		}
		else $body = $form->toHTML();
	break;
    case 'bySSOStatus':
   		$rows = tldSPR::bySSOStatus($y, $x);
		$caption = "SPR with '$x' status for $y SSO";
    break;
    case 'bySPHStatus':
   		$rows = tldSPR::bySPHStatus($y, $x);
		$caption = "SPR with '$x' status for $y SPH";
    break;
    case 'byWCstatus':
        $rows = tldSPQ::byWCstatus($x, $y);
        $title = "WC part request $x, for $y";
        $report = new tldReportColumnar(
            $rows,
            [
                "xItems" => [
                    "id" => "WC#",
                    "part_number" => "Part Number",
                    "part_description" => "Part Description",
                    "part_note" => "WC part note",
                    "date" => "Request Date",
                    "comment" => "Request comment",
                ],
                "title" => $title,
                "links" => [
                    "id" => "/en/private/product_support/index.ps.php?m[0]=wc&m[1]=view&id=",
                ],
            ]
        );
        $body .= $report->fetch();
        break;
	}
	if(isset($rows,$caption)) $body .= _getListing($rows,$caption);
break;
case 'reports':
	switch($m[2]){
	default:
		$body = $smarty->fetch("$PATH/spr/reports/homepage.reports.tpl");
	break;
	}
break;
case 'form':
	switch($m[2]) {
		case 'byNum':
			$form = new HTML_QuickForm('frmByNum', 'post');
			$form->addElement('hidden', 'm[0]', 'spr');
			$form->addElement('hidden', 'm[1]', 'view');
			$form->addElement('header', 'title', 'SPR by number');
			$form->addElement('text', 'id', 'SPR#');
			$form->addElement('submit', 'btnSubmit', 'Submit');
			$body = $form->toHTML();
			break;
		case 'new':
			$body .= "SPR can't be created from this module anymore. Please refer to the module documentation.";
			break;
	}
break;
case 'view':
	if(empty($id)){
		$DEFAULT_ERROR[]=  "ERROR: no line id set...";
		return;
	}

	global $kernel;
	$container = $kernel->getContainer();
	$router = $container->get('router');
	$client = $container->get(Client::class);

	try {
		$apiSpr = $client->findOneBy('parts/spare_parts_requests', ['legacyId' => $id]);
	} catch (\Exception $exception) {
		$DEFAULT_ERROR[] = "ERROR: SPR#$id (legacy) not found";
		break;
	}
	$url = $router->generate('spare_parts_request_show', ['id' => $apiSpr->getIriId()]);
	header("Location: $url");
	exit;
default:
	$body .= $smarty->fetch("$PATH/spr/homepage.spr.tpl");
	$dashboard2 = new tldMatrix(
	    tldSPR::countBySSOStatus(),
		"status", "sso_fullname", "num",
		"$php_self?m[0]=spr&m[1]=listing&m[2]=bySSOStatus",
		"SPR Count by Status, SSO",
		array("xItems"=>tldSPR::getStatusList())
	);
	$dashboard1 = new tldMatrix(
	    tldSPR::countBySPHStatus(),
		"status", "sph_fullname", "num",
		"$php_self?m[0]=spr&m[1]=listing&m[2]=bySPHStatus",
		"SPR Count by Status, SPH",
		array("xItems"=>tldSPR::getStatusList())
	);
	$body .='<table width="100%"><tr><td width="50%">'.$dashboard1->fetch().'</td><td width="50%">'.$dashboard2->fetch().'</td></tr></table>';
	$body .= _getListing(tldSPR::byLatest(),"Latest SPR");
}


function _getGeneralTab(){
	global $spr,$header, $DEFAULT_MENU, $id;
	$DEFAULT_MENU.=<<<EOF
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<a href="/en/private/parts/parts.php?m[0]=cart&m[1]=import&m[2]=bySPR&id=$id">Transfer to Cart</a>
EOF;
	$lines = tldCourier::parseMultiple($header["ship_tnum"]);
	if(!empty($lines)){
    	foreach($lines as $line){
    		$form = new tldCourier($line['courier']);
    		$result .= $form->fetch($line['trackNum']);
    	}
	}
	$header["ship_tnum"]=$result;
	$report = new tldAssocTable($header, [
		'id'						=> 'SPR#',
		'status'					=> 'Status',
		'dt'						=> 'Date/Time Opened',
		'sso_fullname'				=> 'SSO',
		'entered_by_fullname'		=> 'Entered by',
		'assignor_fullname'			=> 'Requestor',
		'sph_fullname'				=> 'SPH',
		'cust_nama'					=> 'Customer Name',
		'cust_cona'					=> 'Customer Contact',
		'cust_tela'					=> 'Customer Contact Tel',
		'cust_emla'					=> 'Customer Email',
		'ship_to'					=> 'Ship To Address',
		'dt_ship'					=> 'Ship date',
		'estimated_shipping_date'	=> 'Estimated shipping date',
		'ship_tnum'					=> 'Tracking Numbers',
		'nota'						=> 'Notes',
	],
				array("title"=>"General")
	);
	$body = $report->fetch();
	$report = new tldReportColumnar($spr->getLines(), array(
					"xItems"=>array(
                        "item"	=>"PN",
                        "dsca"	=>"Description",
                        "um"	=>"UM",
                        "oqua"	=>"Qty"),
					"title"=>"Line Items"
			)
	);
	$body .= $report->fetch();
	return $body;
}

function _getListing($rows,$title){
	global $php_self;
	$report = new tldReportColumnar(
	    $rows,
	    array(
    		"xItems"=>array(
    			"id"			=>"SPR#",
    			"dt"			=>"Date/Time Opened",
    			"status"		=>"Status",
    			"sso_fullname"	=>"SSO",
    			"sph_fullname"	=>"SPH",
    			"cust_nama"		=>"Customer Name",
    			"er_type"		=>"ER type Linked",
    			"sb_id"			=>"SB Linked"),
    		"title"=>$title,
    		"links"=>array(
				"id"	=>"$php_self?m[0]=spr&m[1]=view&id=",
				"sb_id"	=>"/en/private/product_support/index.ps.php?m[0]=sb&m[1]=view&id="
    		)
        )
	);
	return $report->fetch();
}
?>
