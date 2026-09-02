<?php

use ApiBundle\Client;
use Symfony\Component\HttpClient\Exception\ClientException;

include_once("sales_service.inc.php");
include_once("product_support.inc.php");
// Get default dashboard vars
$ID_DASH = $user->getID();
$TYPE_DASH = NULL;

$DEFAULT_TITLE.="\My Dashboard";
$DEFAULT_MENU .=<<<EOF
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<a href="$php_self?m[0]=home">My Dashboard</a>
EOF;
if($user->isInGroup(array("role_EVP","gg_ADMIN"))){
	$DEFAULT_MENU .=<<<EOF
&nbsp;|&nbsp;<a href="$php_self?m[0]=home&m[1]=selectASM">Dashboard by ASM</a>
EOF;
}

$body.= <<<EOF
<p>Welcome to the Sales and Service Module</p>

EOF;
switch($m[1]){
	case 'EVP':
		$TYPE_DASH = "EVP";
	break;
	case 'selectASM':
		if(!$user->isInGroup(array("role_EVP","role_SA","gg_ADMIN"))){
			$DEFAULT_ERROR[]="ERROR: You do not have permissions...";
			break;
		}
		$ASMGrp = new tldGroup("role_ASM");
		$SalesAgentGrp = new tldGroup("gg_SALES_AGENTS");
		$ASMList = array_column($ASMGrp->getUserlistBySSO($DEFAULT_BUID), 'fullname', 'id');
		$SalesAgentList = array_column($SalesAgentGrp->getUserlistBySSO($DEFAULT_BUID), 'fullname', 'id');
		if(empty($ASMList)){
			$DEFAULT_ERROR[]="ERROR: No ASM found for SSO#$DEFAULT_BUID";
			break;
		}
		$form = new HTML_QuickForm('frmSelectUser', 'post');
		$form->addElement(  'header', 'title', 'Select ASM');
		$form->addElement(  'hidden', 'm[0]', 'home');
		$form->addElement(  'hidden', 'm[1]', 'selectASM');
		$form->addElement(  'select', 'uid', 'ASM', array(""=>"")+$ASMList+$SalesAgentList);
		$form->addElement(  'submit', 'btnSubmit', 'Submit');
		$form->addRule('uid', 'This is required', 'required');

		if(!$form->validate()){
			$body = $form->toHTML();
			return;
		}

		$vars = tldUtils::cleanupFormInput($form->exportValues());
		$ID_DASH = $vars['uid'];
		$TYPE_DASH = "ASM";
		if($TYPE_DASH = "ASM" && $user->isInGroup(array("role_EVP","role_SA","gg_ADMIN"))){
			$DEFAULT_MENU .=<<<EOF
&nbsp;|&nbsp;<a href="$php_self?m[0]=home&m[1]=EVP">Back to EVP Dashboard</a>
EOF;
		}
break;
default:
	// Check if ASM or EVP
	if($user->isInGroup(array("role_EVP","role_SA","gg_ADMIN"))){
		$TYPE_DASH = "EVP";
	}elseif($user->isInGroup("role_ASM")){
		$TYPE_DASH = "ASM";
	}
break;
}

$FLAG_DISPLAY_DASH = TRUE;
// Check type of dashboard
if(empty($TYPE_DASH)){
	$DEFAULT_ERROR[]="WARNING: Could not get info to define the dashboard type...";
	$FLAG_DISPLAY_DASH = FALSE;
}
// Check user to display dashboard
$USER_DASH = new tldUser($ID_DASH);
if(empty($USER_DASH->itsDetails)){
	$DEFAULT_ERROR[]="WARNING: Could not get dashboard, User#$ID_DASH not found...";
	$FLAG_DISPLAY_DASH = FALSE;
}
// Check BU ERP
if(empty($DEFAULT_BUID)){
	$DEFAULT_ERROR[]="WARNING: Could not get dashboard, ERP BU not defined...";
	$DEFAULT_ERROR[]="Reason: Your account is not configured correctly";
	$DEFAULT_ERROR[]="Or you have not selected the BU from Sales/Change company section";
	$FLAG_DISPLAY_DASH = FALSE;
}

////////////////////////////////////////////////////////
//              BEGIN - DASHBOARD & REPORTS           //
////////////////////////////////////////////////////////

if($FLAG_DISPLAY_DASH){

    $STATS = array(
        "sol"=>NULL,
        "wc"=>NULL,
        "toc"=>NULL,
        "odp"=>NULL
    );

	switch($TYPE_DASH){
		case 'ASM':
			global $kernel;

			$client = $kernel->getContainer()->get(Client::class);
			$templating = $kernel->getContainer()->get('twig.legacy');

			$asm = $client->findOneBy('people', ['legacyId' => $ID_DASH]);

			$TITLE_DASH = "ASM Dashboard - ".$USER_DASH->getFullname();

			// Get SOL stats
			$STATS['sol'] = tldSOL::countByERPCustomerbyASM($ID_DASH,"");
			// Get WC stats
			$STATS['wc'] = tldWC::countByERPStatusbyASM($ID_DASH,"");
			// Get TOC stats
			$STATS['toc'] = tldTOC::countBySSOStatusByASM($ID_DASH,"");
			// Get ODP stats
			$STATS['odp'] = tldODP::countByERPStatusByASM($ID_DASH,"");
			// Get CRT stats
			$STATS['cust'] = tldCustomer::countByCRTByASM($ID_DASH,"");

			// Title of Dashboard --------------------------------->
			$body .= "<h2>$TITLE_DASH</h2>";

			try {
				$salesForecastsByASMAndFactory = $client->get('reports/resource=/sales/sales_forecasts;x=asm.id;y=factory.name', [
					'query' => [
						'options' => [
							'asm' => $asm['@id'],
						],
					],
				]);
			} catch (ClientException $e) {
				$salesForecastsByASMAndFactory = [];
			}

			try {
				$salesForecastsByASMAndFactoryDelinquent = $client->get('reports/resource=/sales/sales_forecasts;x=asm.id;y=factory.name', [
					'query' => [
						'options' => [
							'delinquent' => true,
							'asm' => $asm['@id'],
						],
					],
				]);
			} catch (ClientException $e) {
				$salesForecastsByASMAndFactoryDelinquent = [];
			}

			$date28DaysAgo = (new DateTime('28 days ago'))->format('Y-m-d');

			try {
				$salesForecastsByASMAndFactoryRecentlyClosed = $client->get('reports/resource=/sales/sales_forecasts;x=asm.id;y=factory.name', [
					'query' => [
						'options' => [
							'closedAt-after' => $date28DaysAgo,
							'asm' => $asm['@id'],
						],
					],
				]);
			} catch (ClientException $e) {
				$salesForecastsByASMAndFactoryRecentlyClosed = [];
			}

			$body.= '<h3>Open Status SFR Count by Factory, ASM</h3>';

			$body.= !empty($salesForecastsByASMAndFactory) ? $templating->render('helper/_matrix_table.html.twig', [
				'data' => $salesForecastsByASMAndFactory,
				'link' => [
					'route' => 'sales_forecasts_list_gantt',
					'xParam' => 'asm',
					'yParam' => 'factory'
				],
				'showZero' => false,
				'hideTotals' => 'y',
			]) : '<p>No results...</p>';

			$body.= '<h3>Open Status Delinquent SFR Count by Factory, ASM</h3>';

			$body.= !empty($salesForecastsByASMAndFactoryDelinquent) ? $templating->render('helper/_matrix_table.html.twig', [
				'data' => $salesForecastsByASMAndFactoryDelinquent,
				'link' => [
					'route' => 'sales_forecasts_list_gantt',
					'xParam' => 'asm',
					'yParam' => 'factory',
					'additionalParams' => ['delinquent' => true],
				],
				'showZero' => false,
				'hideTotals' => 'y',
			]) : '<p>No results...</p>';

			$body.= '<h3>Recently Closed (<28 days) SFR Count by Factory, ASM</h3>';

			$body.= !empty($salesForecastsByASMAndFactoryRecentlyClosed) ? $templating->render('helper/_matrix_table.html.twig', [
				'data' => $salesForecastsByASMAndFactoryRecentlyClosed,
				'link' => [
					'route' => 'sales_forecasts_list_gantt',
					'xParam' => 'asm',
					'yParam' => 'factory',
					'additionalParams' => ['closedAt-after' => $date28DaysAgo]
				],
				'showZero' => false,
				'hideTotals' => 'y',
			]) : '<p>No results...</p>';

			// SOL --------------------------------->
			$reportSOL = new tldMatrix(
					$STATS['sol'],
					"erp_fullname", "customer", "num",
					"$php_self?m[0]=sol&m[1]=listing&m[2]=byERPCustomerByASM&id=$ID_DASH",
					"SOL Count by Factory, Customer - Last 12 Months"
			);
			$body .= $reportSOL->fetch();

			// WC --------------------------------->
			$reportWC = new tldMatrix(
					$STATS['wc'],
					"man_location", "warranty_status", "num",
					"/en/private/product_support/index.ps.php?m[0]=wc&m[1]=listing&m[2]=byERPStatusByASM&id=$ID_DASH",
					"<br><br>WC Count by Factory, Status - Last 12 Months"
			);

			$body .= $reportWC->fetch();

			// TOC --------------------------------->
			$reportTOC = new tldMatrix(
                $STATS['toc'],
                "sso_fullname", "status", "num",
                "/en/private/sales_service/service.php?m[0]=toc&m[1]=listing&m[2]=bySSOStatusByASM&id=$ID_DASH",
                "<br><br>TOC Count by SSO, Status - Last 12 Months",
                [
                    "yItems" => ['OPEN', 'IN PROGRESS', 'SUSPENDED', 'SOLVED', 'CLOSED'],
                ]
            );

			$body .= $reportTOC->fetch();

            // ODP --------------------------------->
            $reportODP = new tldMatrix(
                $STATS['odp'],
                "erp_fullname", "delivery", "num",
                "/en/private/product_support/index.ps.php?m[0]=odp&m[1]=listing&m[2]=byERPStatusByASM&id=$ID_DASH",
                "<br><br>ODP Count by ERP, Status"
			);

			$body .= $reportODP->fetch();

			// eCustomers --------------------------------->
			$reportCust = new tldMatrix(
					$STATS['cust'],
					"erp_fullname", "type", "num",
					"/en/private/sales_service/sales.php?m[0]=customers&m[1]=list&m[2]=noCRTByASM&id=$ID_DASH",
					"<br><br>eCustomer w/o CRT Count by ERP, Type"
			);

			$body .= $reportCust->fetch();

		break;
		case 'EVP':

			// Check BU ERP
			if(!in_array($DEFAULT_BUID,array(34,1,7,11,40,2,41,42,45,46,37,98))){
				$DEFAULT_ERROR[]="WARNING: Could not get dashboard, ERP BU not defined...";
				$DEFAULT_ERROR[]="Reason: Your account is not configured correctly";
				$DEFAULT_ERROR[]="Or you have not selected the BU from Sales/Change company section";
				$FLAG_DISPLAY_DASH = FALSE;
				break;
			}

			$TITLE_DASH = "EVP Dashboard - ".$DEFAULT_BU->getShortName();

			// Get SOL stats
			$STATS['sol'] = tldSOL::countByERPCustomerbyASM($ID_DASH,$DEFAULT_BUID);
			// Get WC stats
			$STATS['wc'] = tldWC::countByERPStatusbyASM($ID_DASH,$DEFAULT_BUID);
			// Get TOC stats
			$STATS['toc'] = tldTOC::countBySSOStatusByASM($ID_DASH,$DEFAULT_BUID);
			// Get ODP stats
			$STATS['odp'] = tldODP::countByERPStatusByASM($ID_DASH,$DEFAULT_BUID);
			// Get CRT stats
			$STATS['cust'] = tldCustomer::countByCRTByASM($ID_DASH,$DEFAULT_BUID);

			// Title of Dashboard --------------------------------->
			$body .= "<h2>$TITLE_DASH</h2>";

			// SFR --------------------------------->

			$body.= $matrixStyle;

			global $kernel;

			$templating = $kernel->getContainer()->get('twig.legacy');
			$client = $kernel->getContainer()->get(Client::class);

			try {
				$salesForecastsByASMAndFactory = $client->get('reports/resource=/sales/sales_forecasts;x=asm.id;y=factory.name');
			} catch (ClientException $e) {
				$salesForecastsByASMAndFactory = [];
			}

			try {
				$salesForecastsByASMAndFactoryDelinquent = $client->get('reports/resource=/sales/sales_forecasts;x=asm.id;y=factory.name', [
					'query' => [
						'options' => [
							'delinquent' => true,
						],
					],
				]);
			} catch (ClientException $e) {
				$salesForecastsByASMAndFactoryDelinquent = [];
			}

			$date28DaysAgo = (new DateTime('28 days ago'))->format('Y-m-d');

			try {
				$salesForecastsByASMAndFactoryRecentlyClosed = $client->get('reports/resource=/sales/sales_forecasts;x=asm.id;y=factory.name', [
					'query' => [
						'options' => [
							'closedAt-after' => $date28DaysAgo,
						],
					],
				]);
			} catch (ClientException $e) {
				$salesForecastsByASMAndFactoryRecentlyClosed = [];
			}

			$body.= '<h3>Open Status SFR Count by Factory, ASM</h3>';

			$body.= !empty($salesForecastsByASMAndFactory) ? $templating->render('helper/_matrix_table.html.twig', [
				'data' => $salesForecastsByASMAndFactory,
				'link' => [
					'route' => 'sales_forecasts_list_gantt',
					'xParam' => 'asm',
					'yParam' => 'factory'
				],
				'showZero' => false,
			]) : '<p>No results...</p>';

			$body.= '<h3>Open Status Delinquent SFR Count by Factory, ASM</h3>';

			$body.= !empty($salesForecastsByASMAndFactoryDelinquent) ? $templating->render('helper/_matrix_table.html.twig', [
				'data' => $salesForecastsByASMAndFactoryDelinquent,
				'link' => [
					'route' => 'sales_forecasts_list_gantt',
					'xParam' => 'asm',
					'yParam' => 'factory',
					'additionalParams' => ['delinquent' => true],
				],
				'showZero' => false,
			]) : '<p>No results...</p>';

			$body.= '<h3>Recently Closed (<28 days) SFR Count by Factory, ASM</h3>';

			$body.= !empty($salesForecastsByASMAndFactoryRecentlyClosed) ? $templating->render('helper/_matrix_table.html.twig', [
				'data' => $salesForecastsByASMAndFactoryRecentlyClosed,
				'link' => [
					'route' => 'sales_forecasts_list_gantt',
					'xParam' => 'asm',
					'yParam' => 'factory',
					'additionalParams' => ['closedAt-after' => $date28DaysAgo]
				],
				'showZero' => false,
			]) : '<p>No results...</p>';

			// SOL --------------------------------->
			$reportSOL = new tldMatrix(
					$STATS['sol'],
					"erp_fullname", "customer", "num",
					"$php_self?m[0]=sol&m[1]=listing&m[2]=byERPCustomerByASM&sso=$DEFAULT_BUID",
					"SOL Count by Factory, Customer - Last 12 Months"
			);
			$body .= $reportSOL->fetch();

			// WC --------------------------------->
			$reportWC = new tldMatrix(
					$STATS['wc'],
					"man_location", "warranty_status", "num",
					"/en/private/product_support/index.ps.php?m[0]=wc&m[1]=listing&m[2]=byERPStatusByASM&sso=$DEFAULT_BUID",
					"<br><br>WC Count by Factory, Status - Last 12 Months"
			);

			$body .= $reportWC->fetch();

			// TOC --------------------------------->
			$reportTOC = new tldMatrix(
					$STATS['toc'],
					"sso_fullname", "status", "num",
					"/en/private/sales_service/service.php?m[0]=toc&m[1]=listing&m[2]=bySSOStatusByASM&sso_asm=$DEFAULT_BUID",
					"<br><br>TOC Count by SSO, Status - Last 12 Months",
                [
                    "yItems" => ['OPEN','IN PROGRESS','SUSPENDED','SOLVED','CLOSED'],
                ]
			);

			$body .= $reportTOC->fetch();

			// ODP --------------------------------->
			$reportODP = new tldMatrix(
					$STATS['odp'],
					"erp_fullname", "delivery", "num",
					"/en/private/product_support/index.ps.php?m[0]=odp&m[1]=listing&m[2]=byERPStatusByASM&sso=$DEFAULT_BUID",
					"<br><br>ODP Count by ERP, Status"
			);

			$body .= $reportODP->fetch();

			// eCustomers --------------------------------->
			$reportCust = new tldMatrix(
					$STATS['cust'],
					"erp_fullname", "type", "num",
					"/en/private/sales_service/sales.php?m[0]=customers&m[1]=list&m[2]=noCRTByASM&sso=$DEFAULT_BUID",
					"<br><br>eCustomer w/o CRT Count by ERP, Type"
			);

			$body .= $reportCust->fetch();

		break;
	}



}
?>
