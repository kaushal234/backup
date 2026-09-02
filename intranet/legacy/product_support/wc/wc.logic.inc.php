<?php
include_once("erp.inc.php");
include_once("sales_service.inc.php");
require_once 'HTML/QuickForm/advmultiselect.php';

use ApiBundle\Client;
use Symfony\Component\HttpClient\Exception\ClientException;
use AppBundle\Chart\ChartBuilderFactory;

global $kernel;
$chartBuilderFactory = $kernel->getContainer()->get(ChartBuilderFactory::class);


$DEFAULT_TITLE .= "\WC";
// Get MOO ID
$moo_id = tldModule::getMOOIDByModule("wc");
$DEFAULT_MENU .=<<<EOF
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<a href="$php_self?m[0]=wc">Home</a>
&nbsp;|&nbsp;<a href="$php_self?m[0]=wc&m[1]=form&m[2]=byNum">By Number</a>
&nbsp;|&nbsp;<a href="$php_self?m[0]=wc&m[1]=listing&m[2]=advsearch">Search</a>
EOF;
if(!$user->isAgent() || ($user->isAgent() && $user->isInGroup("gg_SALES_AGENTS")) || ($user->isAgent() && $user->isInGroup("gg_PARTS_AGENTS") && !$user->isInGroup("gg_SERVICE_AGENTS"))){
$DEFAULT_MENU .=<<<EOF
&nbsp;|&nbsp;<a href="$php_self?m[0]=wc&m[1]=reports">Reports</a>
EOF;
}
$DEFAULT_MENU .=<<<EOF
&nbsp;|&nbsp;<a href="$php_self?m[0]=wc&m[1]=listing&m[2]=byCurrentUser">Warranties I opened</a>
&nbsp;|&nbsp;<a href="/en/private/directory/index.php?m[0]=people&m[1]=view&id=$moo_id">Owner</a>
EOF;
if(!$user->isAgent() || ($user->isAgent() && $user->isInGroup("gg_SALES_AGENTS")) || ($user->isAgent() && $user->isInGroup("gg_PARTS_AGENTS") && !$user->isInGroup("gg_SERVICE_AGENTS"))){
$DEFAULT_MENU .=<<<EOF
&nbsp;|&nbsp;<a href="/en/private/product_support/wc/wc_admin.php">Maintain Warranties</a>
EOF;
}
$DEFAULT_MENU .=<<<EOF
&nbsp;|&nbsp;<a href="/en/private/mis/mis.php?m[0]=help&m[1]=dms&id=864">Help page</a>
EOF;

global $kernel;
$request = $kernel->getContainer()->get('request_stack')->getCurrentRequest();
$clientIp = $request->getClientIp();

// Always reset the WC list in session when not in view mode.
if(!empty($sess["wc"]["list"]) && $m[1]!="view" && $m[1]!="listing") {
    $sess["wc"]["list"] = null;
}

switch($m[1]){
	case "tracking":
		$arr=explode('@',$tracking_no);
		$couriercode=trim($arr[0]);
		$trackingno=trim($arr[1]);
		header("Location:https://www.tld-gse.com/shared/redirect_courier.php?&courier={$couriercode}&trno={$trackingno}");
		exit;

	break;

case 'form':
    switch($m[2]){
    case 'byNum':
        $DEFAULT_TITLE .= "\WC by Number";
        $form = new HTML_QuickForm('frmByNum', 'post');
        $form->addElement(  'hidden', 'm[0]', 'wc');
        $form->addElement(  'hidden', 'm[1]', 'view');
        $form->addElement(  'header', 'title', "WC by Number");
        $form->addElement(  'text', 'id', 'WC#');
        $form->addElement(  'submit', 'btnSubmit', 'Submit');
        $body .= $form->toHTML();
    break;
    }
break;
case 'view':
    include('wc.view.inc.php');
break;
case "matrix":
    switch($m[2]){
    case "byFactoryStatus":
        $form = new tldMatrix(
            tldWC::countByFactoryStatus(),
            "warranty_status", "man_location", "num",
            "$php_self?m[0]=wc&m[1]=listing&m[2]=byFactoryStatus",
            "Warranty Count by Status, Factory"
        );
        $body .= $form->fetch();
    break;
    case "bySSOStatus":
        $form = new tldMatrix(
            tldWC::countBySsoBystatus(),
            "warranty_status", "sales_org", "num",
            "$php_self?m[0]=wc&m[1]=listing&m[2]=bySSOStatus",
            "Warranty Count by Status, Factory"
        );
        $body .= $form->fetch();
    break;
    case "bySalesOrgYear":
        $form = new tldMatrix(
            tldWC::countBySalesOrgYear(),
            "claim_year", "sales_org", "num",
            "",
            "Warranty Count by Sales Org, Year"
        );
        $body .= $form->fetch();
    break;
    }
break;
case 'reports':
    switch($m[2]){
    case 'WCStatus':
    	switch($m[3]){
    		case 'csv':
    			if(!$user->isInGroup(['warranty','gg_PARTS','gg_SERVICE','gg_SUPPORT','gg_ADMIN','gg_ACCT'])){
    				$DEFAULT_ERROR[] = 'ERROR: You do not have the permission to access this page';
    				break;
    			}
    			if(!isset($_SESSION['data_report'])){
    				$DEFAULT_ERROR[] = 'ERROR: Session not set, data is missing!';
    				break;
    			}

    			$xItemCSV = [
                    'id'					=> 'ID#',
                    'sales_org'				=> 'SSO',
                    'man_location'			=> 'Factory',
                    'warranty_status'		=> 'Status',
                    'prod_man_accept_date'	=> 'WC Accept Date',
                    'part_number'			=> 'Parts P/N',
                    'part_description'		=> 'Parts Description',
                    'quantity'				=> 'Parts quantity',
                    'parts_order_ref'		=> 'Parts Order Ref',
                    'location'              => 'Location',
                    'packing_slip'          => 'Packing Slip#',
                    'so_no'                 => 'SO#',
                    'tracking_no'           => 'Tracking#',
    			];
    			$report = new tldCSV(
    					$_SESSION['data_report'],
    					[
                            'xItems' => $xItemCSV,
                            'showTitles' => true
    					]
    			);
    			$report->out();
    			unset($_SESSION['data_report']);
    			exit;
    			break;
    	}

        //Listing
    	$SSO_List = tldLocation::getSalesOrgList('smartyOptionsLocationLocation');
    	$BU_List = tldLocation::getFactoryList('smartyOptionsLocationLocation');
    	$status = ['ACCEPTED'=>'ACCEPTED','CONDITIONAL'=>'CONDITIONAL','REJECTED'=>'REJECTED','SALES CONCESSION'=>'SALES CONCESSION'];

    	// Get form
    	$form = new HTML_QuickForm('formWCStatus', 'post');
    	$form->addElement(  'header', 'title', 'Warranty Status Report');
    	$form->addElement(  'hidden', 'm[0]',   'wc');
    	$form->addElement(  'hidden', 'm[1]',   'reports');
    	$form->addElement(  'hidden', 'm[2]',   'WCStatus');
    	$form->addElement(  'select', 'sso',    'SSO', $SSO_List);
    	$form->addElement(  'date',   'start',  'Start Date', ['format' => 'Y-m-d', 'addEmptyOption' => FALSE, 'minYear' => date('Y')-2, 'maxYear' => date('Y')+1]);
    	$form->addElement(  'date',   'end', 	'End Date', ['format'=>'Y-m-d', 'addEmptyOption' => FALSE, 'minYear' => date('Y')-2, 'maxYear' => date('Y')+1]);
    	$form->addElement(  'select', 'bu', 'Manufacturing Location', [''=>''] + $BU_List);
    	$form->addElement(  'select', 'status',  'Status', [''=>''] + $status);
    	$form->addElement(  'submit', 'btnSubmit','Submit');
    	// Set Required
    	$form->addRule('sso', 'Required', 'required');
    	$form->addRule('startn', 'Required', 'required');
    	$form->addRule('end', 'Required', 'required');
    	// Set Default
    	$form->setDefaults([
    			'start' => date('Y').'-01-01',
    			'end' => date('Y-m-d'),
            ]
    	);
    	if(!$form->validate()){
    		$body = $form->toHTML();
    		break;
    	}
    	# If the form validates then freeze the data
    	$form->freeze();
    	$vars = tldUtils::cleanupFormInput($form->exportValues());
    	$sso = $vars['sso'];
    	$start = vsprintf('%1$04d-%2$02d-%3$02d', $vars['start']);
    	$end = vsprintf('%1$04d-%2$02d-%3$02d', $vars['end']);
    	$bu = $vars['bu'];
        $status = $vars['status'];

        if(!empty($bu)) {
            $bu_title = "- Factory $bu";
        }
    	if(!empty($status)) {
            $status_title = "- Status $status";
        }
    	$data = tldWC::WCStatusReport($sso,$start,$end,$bu,$status);
    	if(isset($data)){
            $_SESSION['data_report'] = $data;
            $report = new tldReportColumnar(
                $data,
                [
                    'xItems' => [
                        'id'					=> 'ID#',
                        'sales_org'				=> 'SSO',
                        'man_location'			=> 'Factory',
                        'warranty_status'		=> 'Status',
                        'prod_man_accept_date'	=> 'WC Accept Date',
                        'customer_name'	        => 'Customer Name',
                        'part_number'			=> 'Parts P/N',
                        'part_description'		=> 'Parts Description',
                        'quantity'				=> 'Parts quantity',
                        'parts_order_ref'		=> 'Parts Order Ref',
                        'location'              => 'Sales Organization',
                        'packing_slip'          => 'Packing Slip#',
                        'so_no'                 => 'SO#',
                        'tracking_no'           => 'Tracking#',
                    ],
                    'title'=>"Warranty Status Report - SSO $sso - Between $start and $end $bu_title $status_title",
                    'links' => [
                        'id'=>"$php_self?m[0]=wc&m[1]=view&id=",
                        'tracking_no' => [
                            'url' => "$php_self?m[0]=wc&m[1]=tracking&tracking_no=",
                            'params' => [
                                'tracking_no' => 'tracking_no'
                            ],
                            'target' => '_blank'
                        ],
                        'packing_slip' => [
                            'url' => 'https://www.tld-gse.com/en/private/finance/finance.php?m[0]=ps&m[1]=view',
                            'params' => [
                                'erp' => 'erp',
                                'id' => 'packing_slip'
                            ]
                        ]
                    ]
                ]
            );
            if($user->isInGroup(['warranty','gg_PARTS','gg_SERVICE','gg_SUPPORT','gg_ADMIN','gg_ACCT'])){
                $DEFAULT_MENU.=<<<EOF
                <br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<a href="$php_self?m[0]=wc&m[1]=reports&m[2]=WCStatus&m[3]=csv">Download to CSV</a>
EOF;
            }
            $body .= $report->fetch();
    	}
    break;
        case "WCProcessTime":
            // Listing
            $factoryList = tldLocation::getFactoryList("smartyOptionsLocationLocation");

            $form = new HTML_QuickForm('formWCProcessTime', 'post');
            $form->addElement(  'header', 'title', "Time to process WC after creation chart");
            $form->addElement(  'hidden', 'm[0]',   'wc');
            $form->addElement(  'hidden', 'm[1]',   'reports');
            $form->addElement(  'hidden', 'm[2]',   'WCProcessTime');
            $form->addElement(  'date',   'start',  'Start Date', ['format'=>'Y-m-d', 'addEmptyOption'=>FALSE, 'minYear'=>date('Y')-5, 'maxYear'=>date('Y')]);
            $form->addElement(  'date',   'end', 	'End Date', ['format'=>'Y-m-d', 'addEmptyOption'=>FALSE, 'minYear'=>date('Y')-5, 'maxYear'=>date('Y')]);
            $form->addElement('select', 'factory', 'Factory', ['' => ''] + $factoryList);
            $form->addElement(  'submit', 'btnSubmit','Submit');
            // Set Required
            $form->addRule('start', 'Required', 'required');
            $form->addRule('end', 'Required', 'required');
            $form->addRule('factory', 'Required', 'required');
            // Set Default
            $form->setDefaults([
                    "start" => date("Y")."-01-01",
                    "end" => date("Y-m-d")]
            );
            if(!$form->validate()){
                $body = $form->toHTML();
                break;
            }
            # If the form validates then freeze the data
            $form->freeze();
            $vars = tldUtils::cleanupFormInput($form->exportValues());
            $vars['start'] = implode('-', $vars['start']);
            $vars['end'] = implode('-', $vars['end']);

            $data = tldWC::WCProcessTimeAfterCreation($vars['start'], $vars['end'], $vars['factory']);
            $graph = new tldGraph();
            $graph->setTitle("Time to process WC after initial creation");
            $graph->setYAxisTitle('Time (in days)');
            // Case of multiple series
            $xAxis = $dataSerie = [];
            // Get data & prepare it
            foreach ($data as $row)
            {
                $xAxis[] = $row['week'];
                $dataSerie[] = (int)$row['days_to_process'];
            }
            $graph->setXAxisCategories($xAxis);
            $graph->addBar($dataSerie,['name'=>$vars['factory']]);
            // Display
            $body.= $graph->fetch();

            break;
    case "WCPNPareto":
        switch($m[3]){
            case 'csv':
                if(!isset($_SESSION['data_report'])){
                    $DEFAULT_ERROR[] = "ERROR: Session not set, data is missing!";
                    break;
                }

                $xItemCSV = [
                    "part_number"			=>"Parts P/N",
                    "part_description"      =>"PN Description",
                    "id"					=>"ID#",
                    "claim_date"	        =>"Claim Date",
                    "man_location"			=>"Factory",
                    "sales_org"				=>"SSO",
                    "warranty_status"		=>"Status",
                    "customer_name"		    =>"Customer",
                    "type"		            =>"Equipment Type",
                    "model"		            =>"Equipment Model",
                    "sn"				    =>"Serial Number",
                    "hours"			        =>"Hourmeter",
                    "problem_desc"	        =>"Problem Description",
                    "actual_gt_date"		=>"Actual GT",
                    "date_shipped"		    =>"Date Shipped"
                ];
                $report = new tldCSV(
                    $_SESSION['data_report'],
                    [
                        "xItems"=>$xItemCSV,
                        "showTitles"=>true
                    ]
                );
                $report->out();
                unset($_SESSION['data_report']);
                exit;
                break;
        }
        $factoryList = tldUtils::optionsByKeyValue(
            tldLocation::byConstraints(['disable' => 0, 'factory' => 'Y', 'hidden' => 0]),
            'location',
            'location'
        );

        $queryModels = "select service.model
from service 
  join models on service.model=models.model 
where service.model != '' 
      and service.model not like ' %' 
      and service.model not like '_' 
      and man_location != '' 
  and hide != 1 
GROUP BY service.model";
        $models = [];
        foreach (tldUtils::getSqlToAssocArray($queryModels) as $mod) {
            $models[$mod['model']] = $mod['model'];
        }

        $queryTypes = "select service.type
from service 
where  man_location != '' and service.type != '' and service.type != ' '
GROUP BY service.type ORDER BY service.type ASC";
        $types = [];
        foreach (tldUtils::getSqlToAssocArray($queryTypes) as $type) {
            $types[$type['type']] = $type['type'];
        }
        $filterList = ['ALL' => 'ALL', 'Model' => 'Model', 'Type' => 'Type'];
        $topList = [10 => 10, 20 => 20, 30 => 30];
        // Get form
        $form = new HTML_QuickForm('formWCPNPareto', 'post');
        $form->addElement(  'header', 'title', "WC PN pareto filtering report");
        $form->addElement(  'hidden', 'm[0]',   'wc');
        $form->addElement(  'hidden', 'm[1]',   'reports');
        $form->addElement(  'hidden', 'm[2]',   'WCPNPareto');
        $form->addElement('select', 'top', 'Top', $topList, ['id'=>'top']);
        $form->addElement(  'date',   'start',  'Start Date', ['format'=>'Y-m-d', 'addEmptyOption'=>FALSE, 'minYear'=>date('Y')-5, 'maxYear'=>date('Y')]);
        $form->addElement(  'date',   'end', 	'End Date', ['format'=>'Y-m-d', 'addEmptyOption'=>FALSE, 'minYear'=>date('Y')-5, 'maxYear'=>date('Y')]);
        $form->addElement('advmultiselect', 'factories', 'Factory', ['ALL' => 'ALL'] + $factoryList, ['id'=>'factories', 'size' => 15, 'style' => 'width:150px;']);
        $form->addElement('select', 'filerting', 'Filter By', $filterList, ['id'=>'filter_by']);

        $js = <<<JS
$(document).ready(function() {
    function changeSelectGraph() {
        $("select[id^='models']").closest( "tbody" ).closest("tr").hide();
        $("select[id^='types']").closest( "tbody" ).closest("tr").hide();
        
        switch($("#filter_by").val()) {
          case 'Model':
            $("select[id='models-f']").closest( "tbody" ).closest("tr").show();
            break;
          case 'Type':
            $("select[id='types-f']").closest( "tbody" ).closest("tr").show();
            break;
        }
    }
    $("#filter_by").change(changeSelectGraph);
    changeSelectGraph();
});
JS;
        $smarty->assign('html_head', '<script type="text/javascript">' . $js . '</script>');
        $form->addElement('advmultiselect', 'models', 'Models', ['ALL' => 'ALL'] + $models, ['id'=>'models', 'size' => 15, 'class' => 'pool', 'style' => 'width:382px;']);
        $form->addElement('advmultiselect', 'types', 'Types', ['ALL' => 'ALL'] + $types, ['id'=>'types', 'size' => 15, 'class' => 'pool', 'style' => 'width:382px;']);
        $form->addElement(  'submit', 'btnSubmit','Submit');
        // Set Required
        $form->addRule('start', 'Required', 'required');
        $form->addRule('end', 'Required', 'required');
        $form->addRule('factories', 'Required', 'required');
        $form->addRule('filter_by', 'Required', 'required');
        $form->addRule('top', 'Required', 'required');
        // Set Default
        $form->setDefaults(array(
                'start'=>date('Y').'-01-01',
                'end'=>date('Y-m-d'))
        );
        if(!$form->validate()){
            $body = $form->toHTML();
            break;
        }

        $vars = tldUtils::cleanupFormInput($form->exportValues());
        $vars['start'] = $start = implode('-', $vars['start']);
        $vars['end'] = $end = implode('-', $vars['end']);

        foreach (['models', 'types', 'factories'] as $key) {
            if (isset($vars[$key]) && \in_array('ALL', $vars[$key], true)) {
                $vars[$key] = 'ALL';
            }
        }

        $count = tldWC::byConstraintsCount($vars);
        $graphData = tldWC::byConstraintsTopPNCount($vars);
        $vars['partNumbers'] = array_column($graphData, 'part_number');
        $warrantiesReport = tldWC::byConstraintsTopPN($vars + ['groupBy' => 'warranty.id']);

        try {
            global $kernel;
            $client = $kernel->getContainer()->get(Client::class);
            $supplierCorrectiveActionRequests = $client->get('quality/supplier_corrective_action_requests', [
                'query' => [
                    'factory.name' => $vars['factories'],
                    'parts.partNumber' => $vars['partNumbers'],
                    'normalizationGroups' => ['supplier_corrective_action_request:detail', 'part']
                ]
            ]);
        } catch (ClientException $exception) {
            $DEFAULT_ERROR[] = 'Something went wrong and the SCARs linked could not be fetched';
        }

        $cumulatePercentage = 0;
        foreach ($graphData as &$pn) {
            $cumulatePercentage += $pn['pn_count'] / $count['count'] * 100;
            $pn['percent'] = round($cumulatePercentage, 2);
        }


        if (isset($vars['models']) && is_array($vars['models'])) {
            $catalogueFilter = implode('/', $vars['models']);
        } elseif (isset($vars['types']) && is_array($vars['types'])) {
            $catalogueFilter = implode('/', $vars['types']);
        } else {
            $catalogueFilter = 'ALL MODELS';
        }

        $reportTitle = sprintf('Top %d WC Part Numbers - %s - %s - Between %s and %s', $vars['top'],
            is_array($vars['factories']) ? implode('/', $vars['factories']) : 'ALL FACTORIES',
            $catalogueFilter,
            $start,
            $end);

        // Generate KPIs
        $chart = $chartBuilderFactory
            ->getColumnChartBuilder()
            ->setTitle($reportTitle)
            ->shareTooltip()
            ->addYAxis()
            ->addYAxis(
                null,
                [
                    'opposite' => true,
                    'labels' => [
                        'format' => "{value}%",
                    ]
                ]
            )
        ;

        foreach ($graphData as $row) {
            $chart->addPlot('Part Numbers Used', sprintf('%s - %s', $row['part_number'], mb_convert_encoding($row['part_description'], 'UTF-8', mb_list_encodings())), (int) $row['pn_count']);
            $chart->addPlot('Percent', (string) $row['part_number'], $row['percent'], ['options' => ['yAxis' => 1, 'type' => 'spline']]);
        }

        $chart = json_encode($chart->buildConfig(false), JSON_THROW_ON_ERROR);

        $body .= <<<EOF
<br/><br/>
<div id="container" style="width:100%; height:400px;"></div>
<script>
	$(document).ready(function() {
	  $('#container').highcharts($chart)
    })
</script>
EOF;
        $_SESSION['data_report'] = $warrantiesReport;

        $supplierCorrectiveActionRequestReport = [];
        foreach ($supplierCorrectiveActionRequests['hydra:member'] as $supplierCorrectiveActionRequest) {
            $parts = [];
            foreach ($supplierCorrectiveActionRequest['parts'] as $part) {
                if (!\in_array($part['partNumber'], $vars['partNumbers'])) {
                    continue;
                }
                $parts[] = $part['partNumber'];
            }

            $id = $supplierCorrectiveActionRequest['id'];
            $router = $kernel->getContainer()->get('router');
            $route = $router->generate('supplier_corrective_action_request_show', ['id' => $id]);

            $supplierCorrectiveActionRequestReport[] = [
                'id' => "<a href='$route'>$id</a>",
                'partNumbers' => implode(', ', $parts),
                'status' => $supplierCorrectiveActionRequest['status'],
                'shortDescription' => $supplierCorrectiveActionRequest['shortDescription']
            ];
        }

        $scarReport = new tldReportColumnar(
            $supplierCorrectiveActionRequestReport,
            [
                "xItems" => [
                    "id"			    =>"ID#",
                    "partNumbers"       =>"PN",
                    "status"			=>"Status",
                    "shortDescription"	=>"Short Description",
                ],
                "title" => "SCARs Linked to PN"
            ]
        );

        $report = new tldReportColumnar(
            $warrantiesReport,
            [
                "xItems" => [
                    "part_number"			=>"Parts P/N",
                    "part_description"      =>"PN Description",
                    "id"					=>"ID#",
                    "claim_date"	        =>"Claim Date",
                    "man_location"			=>"Factory",
                    "sales_org"				=>"SSO",
                    "warranty_status"		=>"Status",
                    "customer_name"		    =>"Customer",
                    "type"		            =>"Equipment Type",
                    "model"		            =>"Equipment Model",
                    "sn"				    =>"Serial Number",
                    "hours"			        =>"Hourmeter",
                    "problem_desc"	        =>"Problem Description",
                    "actual_gt_date"		=>"Actual GT",
                    "date_shipped"		    =>"Date Shipped"
                ],
                "links" => ["id" => "$php_self?m[0]=wc&m[1]=view&id="],
                "title" => "WC",
            ]
        );

        // $partNumbers keep order from the previous graph
        $partNumbers = array_values(array_unique($vars['partNumbers']));

        $constraints = 'pn IN ("'. implode('", "', $partNumbers).'")';
        $rows = tldModParts::byConstraintsWithModuleData($constraints, 'PDC', ['id', 'status']);

        $pdcLinksAndStatusData = array_fill_keys($partNumbers, []);
        foreach ($rows as $row) {
            $partNumber = trim((string) $row['pn']);
            $pdcLinksAndStatusData[$partNumber][] = [
                'id' => sprintf('<a href="/en/private/product_support/index.ps.php?m[0]=pdc&m[1]=view&id=%s" target="_blank">%s</a>', $row['pdc_id'], $row['pdc_id']),
                'status' => $row['pdc_status'] ?? null,
            ];
        }

        $body .= buildModuleTable($pdcLinksAndStatusData, 'PDC');

        // SCAR links and statuses by part number
        $scarLinksAndStatusData = array_fill_keys($partNumbers, []);
        foreach ($supplierCorrectiveActionRequestReport as $scar) {
            $scarParts = array_map('trim', explode(',', $scar['partNumbers']));
            foreach ($scarParts as $partNumber) {
                $scarLinksAndStatusData[$partNumber][] = [
                    'id' => $scar['id'],
                    'status' => $scar['status'],
                ];
            }
        }

        $body .= buildModuleTable($scarLinksAndStatusData, 'SCAR');

        $DEFAULT_MENU.=<<<EOF
    		<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<a href="$php_self?m[0]=wc&m[1]=reports&m[2]=WCPNPareto&m[3]=csv">Download to CSV</a>
EOF;
        $body .= $scarReport->fetch()."<br>";
        $body .= $report->fetch();
        break;
    case "WCParts":
    	switch($m[3]){
    		case 'csv':
    			if(!$user->isInGroup(array("warranty","gg_PARTS","gg_SERVICE","gg_SUPPORT","gg_ADMIN","gg_ACCT"))){
    				$DEFAULT_ERROR[] = "ERROR: You do not have the permission to access this page";
    				break;
    			}
    			if(!isset($_SESSION['data_report'])){
    				$DEFAULT_ERROR[] = "ERROR: Session not set, data is missing!";
    				break;
    			}

    			$xItemCSV = array(
    				"id"					=>"ID#",
    				"sales_org"				=>"SSO",
    				"man_location"			=>"Factory",
    				"warranty_status"		=>"Status",
    				"prod_man_accept_date"	=>"WC Accept Date",
    				"part_number"			=>"Parts P/N",
    				"pn_description"		=>"Parts Description",
    				"quantity"				=>"Parts quantity",
    				"total_value"			=>"Parts MIP",
    				"currency"				=>"Currency",
    				"parts_order_ref"		=>"Parts Order Ref"
    			);
    			$report = new tldCSV(
    					$_SESSION['data_report'],
    					array(
    							"xItems"=>$xItemCSV,
    							"showTitles"=>true
    					)
    			);
    			$report->out();
    			unset($_SESSION['data_report']);
    			exit;
    			break;
    	}
    	// Listing
    	$SSO_List = tldLocation::getSalesOrgList("smartyOptionsLocationLocation");
    	$BU_List = tldLocation::getFactoryList("smartyOptionsLocationLocation");
    	$status = array("ACCEPTED"=>"ACCEPTED","CONDITIONAL"=>"CONDITIONAL","REJECTED"=>"REJECTED","SALES CONCESSION"=>"SALES CONCESSION");

    	// Get form
    	$form = new HTML_QuickForm('frmNewWCPartValue', 'post');
    	$form->addElement(  'header', 'title', "WC Parts value calculation (Calculation only made from Parts that have a MIP)");
    	$form->addElement(  'hidden', 'm[0]',   'wc');
    	$form->addElement(  'hidden', 'm[1]',   'reports');
    	$form->addElement(  'hidden', 'm[2]',   'WCParts');
    	$form->addElement(  'select', 'sso',    'SSO', $SSO_List);
    	$form->addElement(  'date',   'start',  'Start Date', 	array("format"=>"Y-m-d", 'addEmptyOption'=>FALSE, "minYear"=>date("Y")-2, "maxYear"=>date("Y")+1));
    	$form->addElement(  'date',   'end', 	'End Date', 	array("format"=>"Y-m-d", 'addEmptyOption'=>FALSE, "minYear"=>date("Y")-2, "maxYear"=>date("Y")+1));
    	$form->addElement(  'select', 'bu', 'Manufacting Location', array(""=>"")+$BU_List);
    	$form->addElement(  'select', 'status',  'Status', array(""=>"")+$status);
    	$form->addElement(  'select', 'currency','Currency', array("USD"=>"USD","EUR"=>"EUR","CNY"=>"CNY"));
    	$form->addElement(  'submit', 'btnSubmit','Submit');
    	// Set Required
    	$form->addRule('sso', 'Required', 'required');
    	$form->addRule('startn', 'Required', 'required');
    	$form->addRule('end', 'Required', 'required');
    	// Set Default
    	$form->setDefaults(array(
    			"start"=>date("Y")."-01-01",
    			"end"=>date("Y-m-d"))
    	);
    	if(!$form->validate()){
    		$body = $form->toHTML();
    		break;
    	}
    	# If the form validates then freeze the data
    	$form->freeze();
    	$vars = tldUtils::cleanupFormInput($form->exportValues());
    	$sso = $vars['sso'];
    	$currency = $vars['currency'];
    	$year_cur = vsprintf('%1$04d', $vars['start']);
    	$month_cur = vsprintf('%2$02d', $vars['start']);
    	$start = vsprintf('%1$04d-%2$02d-%3$02d', $vars['start']);
    	$end = vsprintf('%1$04d-%2$02d-%3$02d', $vars['end']);
    	$bu = $vars['bu'];
    	if(!empty($bu)) {
            $bu_title = "- Factory $bu";
        }
    	$status = $vars['status'];
    	if(!empty($status)) {
            $status_title = "- Status $status";
        }
    	$data = tldWC::WCPartCalculation($sso,$start,$end,$bu,$status,'');
    	if(isset($data)){
    	// Form part number array for Baan query
    	foreach($data as $dat){
    		$pn[] = $dat['part_number'];
    	}
    	$sso_code = tldLocation::getERPByLocation($sso);
    	$e = new tldBaanERP($sso_code);
    	$prices = $e->getItemData($pn);
    	// Store prices linked with their PNs
    	foreach($prices as $price){
    		$item = trim($price['ITEM']);
    		$temp[$item]['MIP'] = $price['MIP'];
    		$temp[$item]['CURRENCY'] = $price['CURRENCY'];
    		$temp[$item]['DESCRIPTION'] = $price['DESCRIPTION'];
    	}
    	// Calculate price (X quantity, X currency)
    	foreach($data as $dat){
    		$cur = tldForex::getRate($temp[$dat['part_number']]['CURRENCY'],$currency,$year_cur,$month_cur);
    		$total = round($temp[$dat['part_number']]['MIP']*$dat['quantity']*$cur,2);
    		$temp[$dat['id']][$dat['part_number']]['total'] = $total;
    		$temp[$dat['id']][$dat['part_number']]['currency'] = $currency;
    		$temp[$dat['id']][$dat['part_number']]['pn_description'] = $temp[$dat['part_number']]['DESCRIPTION'];
    	}
    	$data_total = tldWC::WCPartCalculation($sso,$start,$end,$bu,$status,'');
    	foreach($data_total as &$dat_total){
    		if(isset($temp[$dat_total['id']][$dat_total['part_number']]['total'])){
    			$dat_total['total_value'] = $temp[$dat_total['id']][$dat_total['part_number']]['total'];
    			$dat_total['currency'] = $temp[$dat_total['id']][$dat_total['part_number']]['currency'];
    		}
    		$dat_total['pn_description'] = $temp[$dat_total['id']][$dat_total['part_number']]['pn_description'];
    	}
    	if(isset($_SESSION['data_report'])) {
            unset($_SESSION['data_report']);
        }
    		$_SESSION['data_report']=$data_total;

    		$report = new tldReportColumnar(
    				$data_total,
    				array(
    						"xItems"=>array(
    	"id"					=>"ID#",
    	"sales_org"				=>"SSO",
    	"man_location"			=>"Factory",
    	"warranty_status"		=>"Status",
    	"prod_man_accept_date"	=>"WC Accept Date",
    	"part_number"			=>"Parts P/N",
    	"pn_description"		=>"Parts Description",
    	"quantity"				=>"Parts quantity",
    	"total_value"			=>"Parts MIP",
    	"currency"				=>"Currency",
    	"parts_order_ref"		=>"Parts Order Ref"
    	),
    	"title"=>"WC Parts - SSO $sso - Between $start and $end $bu_title $status_title",
    	"links"=>array("id"=>"$php_self?m[0]=wc&m[1]=view&id=")
    	)
    	);
    	if($user->isInGroup(array("warranty","gg_PARTS","gg_SERVICE","gg_SUPPORT","gg_ADMIN","gg_ACCT"))){
    		$DEFAULT_MENU.=<<<EOF
    		<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<a href="$php_self?m[0]=wc&m[1]=reports&m[2]=WCParts&m[3]=csv">Download to CSV</a>
EOF;
    	}
    	$body .= "Note: Parts MIP value = Parts Quantity * MIP";
		$body .= $report->fetch();
    	}
    break;
    case "WCPartsValue":
    	switch($m[3]){
    		case 'csv':
    			if(!$user->isInGroup(array("warranty","gg_PARTS","gg_SERVICE","gg_SUPPORT","gg_ADMIN","gg_ACCT"))){
    				$DEFAULT_ERROR[] = "ERROR: You do not have the permission to access this page";
    				break;
    			}
    			if(!isset($_SESSION['data_report'])){
    				$DEFAULT_ERROR[] = "ERROR: Session not set, data is missing!";
    				break;
    			}

    			$xItemCSV = array(
    					"id"					=>"ID#",
    					"sales_org"				=>"SSO",
    					"man_location"			=>"Factory",
    					"warranty_status"		=>"Status",
    					"prod_man_accept_date"	=>"WC Accept Date",
    					"type"					=>"Type",
    					"model"					=>"Model",
    					"total_value"			=>"Total listed Parts Value (@MIP-20%)",
    					"currency"				=>"Currency",
    					"technician_cost_te"	=>"Travel Expenses",
    					"technician_cost_labour"=>"Labor Costs",
    					"parts_cost"			=>"Parts Costs",
    					"note_cost"				=>"Cost Note",
    					"parts_order_ref"		=>"Parts Order Ref"
    			);
    			$report = new tldCSV(
    					$_SESSION['data_report'],
    					array(
    							"xItems"=>$xItemCSV,
    							"showTitles"=>true
    					)
    			);
    			$report->out();
    			unset($_SESSION['data_report']);
    			exit;
    			break;
    	}
    	// Listing
    	$SSO_List = tldLocation::getSalesOrgList("smartyOptionsLocationLocation");
    	$BU_List = tldLocation::getFactoryList("smartyOptionsLocationLocation");
    	$status = array("ACCEPTED"=>"ACCEPTED","CONDITIONAL"=>"CONDITIONAL","REJECTED"=>"REJECTED","SALES CONCESSION"=>"SALES CONCESSION");

    	// Get form
    	$form = new HTML_QuickForm('frmNewWCPartValue', 'post');
    	$form->addElement(  'header', 'title', "WC Parts value calculation (Calculation only made from Parts that have a MIP)");
    	$form->addElement(  'hidden', 'm[0]',   'wc');
    	$form->addElement(  'hidden', 'm[1]',   'reports');
    	$form->addElement(  'hidden', 'm[2]',   'WCPartsValue');
    	$form->addElement(  'select', 'sso',    'SSO', $SSO_List);
    	$form->addElement(  'date',   'start',  'Start Date', 	array("format"=>"Y-m-d", 'addEmptyOption'=>FALSE, "minYear"=>date("Y")-2, "maxYear"=>date("Y")+1));
    	$form->addElement(  'date',   'end', 	'End Date', 	array("format"=>"Y-m-d", 'addEmptyOption'=>FALSE, "minYear"=>date("Y")-2, "maxYear"=>date("Y")+1));
    	$form->addElement(  'select', 'bu', 'Manufacting Location', array(""=>"")+$BU_List);
    	$form->addElement(  'select', 'status',  'Status', array(""=>"")+$status);
    	$form->addElement(  'select', 'currency','Currency', array("USD"=>"USD","EUR"=>"EUR","CNY"=>"CNY"));
    	$form->addElement(  'submit', 'btnSubmit','Submit');
    	// Set Required
    	$form->addRule('sso', 'Required', 'required');
    	$form->addRule('startn', 'Required', 'required');
    	$form->addRule('end', 'Required', 'required');
    	// Set Default
    	$form->setDefaults(array(
    			"start"=>date("Y")."-01-01",
    			"end"=>date("Y-m-d"))
    	);
    	if(!$form->validate()){
    		$body = $form->toHTML();
    		break;
    	}
    	# If the form validates then freeze the data
    	$form->freeze();
    	$vars = tldUtils::cleanupFormInput($form->exportValues());
		$sso = $vars['sso'];
		$currency = $vars['currency'];
		$year_cur = vsprintf('%1$04d', $vars['start']);
		$month_cur = vsprintf('%2$02d', $vars['start']);
		$start = vsprintf('%1$04d-%2$02d-%3$02d', $vars['start']);
		$end = vsprintf('%1$04d-%2$02d-%3$02d', $vars['end']);
		$bu = $vars['bu'];
		if(!empty($bu)) {
            $bu_title = "- Factory $bu";
        }
		$status = $vars['status'];
		if(!empty($status)) {
            $status_title = "- Status $status";
        }
    	$data = tldWC::WCPartCalculation($sso,$start,$end,$bu,$status,'');
    	if(isset($data)){
    		// Form part number array for Baan query
    		foreach($data as $dat){
    			$pn[] = $dat['part_number'];
    		}
    		$sso_code = tldLocation::getERPByLocation($sso);
    		$e = new tldBaanERP($sso_code);
    		$prices = $e->getItemData($pn);
    		// Store prices linked with their PNs
    		foreach($prices as $price){
    			$item = trim($price['ITEM']);
    			$temp[$item]['MIP'] = $price['MIP'];
    			$temp[$item]['CURRENCY'] = $price['CURRENCY'];
    		}
    		// Calculate price (X quantity, -20%, X currency)
    		foreach($data as $dat){
    			$cur = tldForex::getRate($temp[$dat['part_number']]['CURRENCY'],$currency,$year_cur,$month_cur);
    			$total = round(($temp[$dat['part_number']]['MIP']-(($temp[$dat['part_number']]['MIP']*20)/100))*$dat['quantity']*$cur,2);
    			$temp[$dat['id']]['total'] = $total + $temp[$dat['id']]['total'];
    			$temp[$dat['id']]['currency'] = $currency;
    		}
    		$option = "WC Part Calculation";
    		$data_total = tldWC::WCPartCalculation($sso,$start,$end,$bu,$status,$option);
    		foreach($data_total as &$dat_total){
				if(isset($temp[$dat_total['id']]['total'])){
    				$dat_total['total_value'] = $temp[$dat_total['id']]['total'];
    				$dat_total['currency'] = $temp[$dat_total['id']]['currency'];
				}
    		}
    		if(isset($_SESSION['data_report'])) {
                unset($_SESSION['data_report']);
            }
    		$_SESSION['data_report']=$data_total;

    		$report = new tldReportColumnar(
    			$data_total,
    			array(
    					"xItems"=>array(
    								"id"					=>"ID#",
    								"sales_org"				=>"SSO",
    								"man_location"			=>"Factory",
    								"warranty_status"		=>"Status",
    								"prod_man_accept_date"	=>"WC Accept Date",
    								"type"					=>"Type",
    								"model"					=>"Model",
    								"total_value"			=>"Total listed Parts Value (@MIP-20%)",
    								"currency"				=>"Currency",
    								"technician_cost_te"	=>"Travel Expenses",
    								"technician_cost_labour"=>"Labor Costs",
    								"parts_cost"			=>"Parts Costs",
    								"note_cost"				=>"Cost Note",
    								"parts_order_ref"		=>"Parts Order Ref"
    					),
    					"title"=>"WC Parts Calculation - SSO $sso - Between $start and $end $bu_title $status_title",
    					"links"=>array("id"=>"$php_self?m[0]=wc&m[1]=view&id=")
    			)
    		);
    		if($user->isInGroup(array("warranty","gg_PARTS","gg_SERVICE","gg_SUPPORT","gg_ADMIN","gg_ACCT"))){
    			$DEFAULT_MENU.=<<<EOF
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<a href="$php_self?m[0]=wc&m[1]=reports&m[2]=WCPartsValue&m[3]=csv">Download to CSV</a>
EOF;
    		}
    		$body .= "Note: Total value is based on the Parts MIP value. Parts that do not have a MIP are <b>NOT</b> taken into account in the calculation. The formula is MIP-20% x Parts QTY";
    		$body .= $report->fetch();
    	}
    break;
    case "wcByFactoryPeriod":
        $data = tldWC::getFactoryList();
        foreach($data as $location) {
            $ListManLocation[$location['man_location']] = $location['man_location'];
        }
        // Get form
        $form = new HTML_QuickForm('frmNewDemerit', 'post');
        $form->addElement(  'header', 'title', "Select date range for $man_location warranties");
        $form->addElement(  'hidden', 'm[0]', 'wc');
        $form->addElement(  'hidden', 'm[1]', 'reports');
        $form->addElement(  'hidden', 'm[2]', 'wcByFactoryPeriod');
        $form->addElement(  'select', 'man_location', 'Manufacting Location', $ListManLocation);
        $form->addElement(  'text', 'start', 'Start Date');
        $form->addElement(  'text', 'end', 'End Date');
        $form->addElement(  'submit', 'btnSubmit', 'Submit');
        // Set default
        $form->setDefaults(array(
            "start"=>date("Y")."-01-01",
            "end"=>date("Y-m-d"))
        );

        if($form->validate()){
            # If the form validates then freeze the data
            $form->freeze();
            $man_location = TldDatabase::escape($man_location);
            $start = TldDatabase::escape($start);
            $end = TldDatabase::escape($end);
            $options = array("t1.man_location"=>$man_location);
            $data = tldWC::byPeriod($start, $end, $options);
            $report = new tldCSV($data, array(
                "xItems"=>array(
                    "id","warranty_status","serial_number","date_shipped","hours","entered_by","customer_name","claim_date","type",
                    "model","sales_org","est_man_hours","problem_desc","prod_man_comments","technician_cost_te",
                    "technician_cost_labour","parts_cost","note_cost","month_age","prod_man_accept_date"),
                "showTitles"=>true)
            );
            $report->out("wcByFactoryPeriod.csv");
            $DEFAULT_TEMPLATE="NO_TEMPLATE";
        }
        else {
            $body = $form->toHTML();
        }
    break;
    case 'OpenTaskByFactory':
        // Listing
        $factoryList = tldLocation::getFactoryList('smartyOptionsIDLocation');
        // Form
        $form = new HTML_QuickForm('frm', 'post');
        $form->addElement(  'header', 'title', "Get OPEN task by Factory");
        $form->addElement(  'hidden', 'm[0]', 'wc');
        $form->addElement(  'hidden', 'm[1]', 'reports');
        $form->addElement(  'hidden', 'm[2]', 'OpenTaskByFactory');
        $form->addElement(  'select', 'bu_id', 'Factory', $factoryList);
        $form->addElement(  'submit', 'btnSubmit', 'Submit');
        $form->setDefaults(array('bu_id'=>$user->getBUID()));

        if(!$form->validate()){
            $body = $form->toHTML();
            break;
        }

        $vars = tldUtils::cleanupFormInput($form->exportValues());
        $rows = tldWC::getOpenTaskByFactoryID($vars['bu_id']);
        // Display report
        $form = new tldReportColumnar(
            $rows,
            array(
                "xItems"=>array(
                    "id"                =>"Task#",
                    "parent_id"         =>"WC#",
                    "status"            =>"Status",
                    "date"              =>"Date Opened",
                    "due_date"          =>"Due Date",
                    "assignor_fullname" =>"Assignor",
                    "assignee_fullname" =>"Assignee",
                    "task"              =>"Task"
                ),
                "title"=>"Open WC task",
                "links"=>array(
                    "id"=>"/en/private/calendar/calendar.php?m[0]=tasks&m[1]=task&&m[2]=view&id=",
                    "parent_id"=>"$php_self?m[0]=wc&m[1]=view&id="
                )
            )
        );
        $body.= $form->fetch();
    break;
    default:
        $body = $smarty->fetch("$PATH/wc/reports/homepage.reports.tpl");
    break;
    }
break;
case 'help':
    $body = $smarty->fetch("$PATH/wc/help.tpl");
break;
case 'charts':
    switch($m[2]){
    case "factoryCharts":
        if($man_location){
            $smarty->assign("man_location", $man_location);
            $body = $smarty->fetch("$PATH/wc/reports/report.byFactory.tpl");
            break;
        }
        $form = new tldHTMLList(
            tldWC::getFactoryList(),
            array(
                "key"=>"man_location",
                "value"=>"man_location"
            ),
            "$php_self?m[0]=wc&m[1]=charts&m[2]=factoryCharts&man_location="
        );
        $body = $form->fetch();
    break;
    }
break;
case 'listing':
    $xItems = [
		'id' => 'WC#',
    	'claim_date' => 'Date',
        'entered_by' => 'Entered By',
        'man_location' => 'Factory',
        'sales_org' => 'SSO',
    	'prod_man_sign_date' => 'PSM Signed Date',
        'warranty_status' => 'Status',
    	'customer_name' => 'Customer',
        'type' => 'Type',
        'model' => 'Model',
        'serial_number' => 'Serial Number',
        'hours' => 'Hourmeter',
        'part_failing' => 'Critical PN failing',
        'problem_desc' => 'Problem Description',
        'part_number' => 'Part Number',
        'part_description' => 'Part Name',
        'part_quantity' => 'Quantity requested',
        'category' => 'Category',
    ];
    $xItems1 = array(
    		"id"                =>"WC#",
    		"prod_man_sign_date"=>"PSM Signed Date",
    		"man_location"      =>"Factory",
    		"warranty_status"   =>"Status",
    		"claim_date"        =>"Date",
    		"entered_by"        =>"Entered By",
    		"type"              =>"Type",
    		"model"             =>"Model",
    		"serial_number"     =>"Serial Number",
    		"hours"             =>"Hourmeter",
    		"problem_desc"      =>"Problem Description",
    		"customer_name"		=>"Customer Name"
    );
    $xItemsUnreturned = array(
		"part_number"    		=>"Part Number",
		"id"                	=>"WC#",
    	"part_description"   	=>"Part Name",
		"part_quantity" 		=>"Quantity requested",
		"model"             	=>"Unit Model",
    	"man_location"      	=>"Factory",
		"claim_date"        	=>"WC Claimed Date",
    	"warranty_status"   	=>"WC Status",
		"hours"             	=>"Hourmeter",
		"customer_name"     	=>"Customer Name",
    	"poster_fullname"    	=>"WC Poster",
    	"airport_code"		 	=>"Airport Code"
    );
    //straight list of warranties
    switch($m[2]){
    case 'byFactoryFilteringFlag':
    	$statuses = [
    		'ALL' => 'ALL',
    		'PENDING' => 'PENDING',
			'ACCEPTED' => 'ACCEPTED',
			'REJECTED' => 'REJECTED',
			'CONDITIONAL' => 'CONDITIONAL',
		];
        $form = new HTML_QuickForm('frmModelFiltering', 'get');
        $form->addElement('header', 'title', 'Additionally filter on a Model');
        $form->addElement('hidden', 'm[0]', 'wc');
        $form->addElement('hidden', 'm[1]', 'listing');
        $form->addElement('hidden', 'm[2]', 'byFactoryFilteringFlag');
        $form->addElement('hidden', 'x', $x);
        $form->addElement('hidden', 'y', $y);
        $form->addElement('select', 'z', 'Model', tldModel::getModels('', 'smartyOptions'));
        $form->addElement('select', 'status', 'Status', $statuses);
        $form->addElement('submit', 'btnSubmit', 'Submit');

        $vars = tldUtils::cleanupFormInput($form->exportValues());

        $filterFlag = TldDatabase::escape($vars['x']);
        $factory = TldDatabase::escape($vars['y']);
        $model = ' ALL_MODELS';
        $status = 'ALL';

		if (isset($vars['status'])) {
			$status = TldDatabase::escape($vars['status']);
		}

        if (isset($vars['z'])) {
            $model = TldDatabase::escape($vars['z']);
        }
        $body .= $form->toHTML();

        $rows = tldWC::byFactoryFilteringFlag($factory, $filterFlag, $model, $status);
        $_title = "WC for factory '$factory', filtering status '$filterFlag', model '$model'";
    break;
    case 'WCSumBySubParts':
    	// Get erp# from IP
    	$bus = tldLocation::byOutsideNetworkAddress($clientIp);
    	if(count($bus)==1) {
            $erp_default = $bus[0]['erp'];
        }
    	// Get form
    	$form = new HTML_QuickForm('frmByNum', 'get');
    	$form->addElement(	'hidden', 'm[0]', 'wc');
    	$form->addElement(	'hidden', 'm[1]', 'listing');
    	$form->addElement(	'hidden', 'm[2]', 'WCSumBySubParts');
    	$form->addElement(	'header', 'title', 'Get WC count by PN');
    	$form->addElement(	'select', 'erp', 'Factory',
    			array(""=>"",540=>"TLD EUR")+tldLocation::getFactoryList("smartyOptions"));
    	$form->addElement(	'text',   'pn', 'Part Number');
    	$form->addElement(	'text',   'date', 'Date');
    	$form->addRule('pn', 'This is required', 'required');
    	$form->setDefaults(array("erp"=>$erp_default,"date"=>date("Y-m-d")));
    	$form->addElement(	'submit',	'btnSubmit','Submit');

    	if(!$form->validate()){
    		$body = $form->toHTML();
    		break;
    	}

    	header("Location: /en/private/manufacturing/eng/dev.php?m[0]=bom&m[1]=view&m[2]=WCSumBySubParts&erp=$erp&pn=$pn&date=$date");

    break;
    case 'MTBFbyBuByPeriod':
        // Listing
        $factoryList = tldLocation::getFactoryList("smartyOptionsLocationLocation");
    	// Form to choose which Factory to list from
    	$form = new HTML_QuickForm('frmfactory', 'post');
    	$form->addElement(	'hidden',	'm[0]',		'wc');
    	$form->addElement(	'hidden',	'm[1]',		'listing');
    	$form->addElement(	'hidden',	'm[2]',		$m[2]);
    	$form->addElement(	'header',	'title',	'MTBF by Bu By Period - Denominator');
    	$form->addElement(  'select',	'factory',	'Factory',	array(""=>"")+$factoryList);
        $form->addElement(	'date', 	'period',	'Period', array(
        	"format"=>"Y-m", "minYear"=>date('Y')-2, "maxYear"=>date('Y'),'addEmptyOption'=>TRUE));
    	$form->addRule(		'factory',	'Required',	'required');
    	$form->addRule(		'period',	'Required',	'required');
    	$form->setDefaults(array('period'=>array('Y'=>date('Y'),'m'=>date('m'))));
    	$form->addElement(	'submit',	'btnSubmit','Submit');

    	if(!$form->validate()){
    	    $body = $form->toHTML();
    	    break;
    	}

    	$vars = tldUtils::cleanupFormInput($form->exportValues());
    	$period = implode('-',$vars['period']).'-01';
    	$rows = tldWC::byMTBFPeriodByConstraints($period, array("er.man_location"=>$vars['factory']));
    	$xItems = array(
    		'period'=>'Period',
    	    'id'=>'WC#',
            'claim_date'=>'Date',
            'warranty_status'=>'Status',
    		'er_id'=>'ER#',
            'sn'=>'sn',
    	    'type'=>'Type',
            'model'=>'Model',
            'man_location'=>'Factory',
            'date_shipped'=>'Ship date',
            'days_shipped_end_period'=>'NB days since shipped<br>End of period',
    	);
	break;
	case 'MTBFbyBuByPeriodNumerator':
		// Listing
		$factoryList = tldLocation::getFactoryList("smartyOptionsLocationLocation");
		// Form to choose which Factory to list from
		$form = new HTML_QuickForm('frmfactory', 'post');
		$form->addElement(	'hidden',	'm[0]',		'wc');
		$form->addElement(	'hidden',	'm[1]',		'listing');
		$form->addElement(	'hidden',	'm[2]',		$m[2]);
		$form->addElement(	'header',	'title',	'MTBF by Bu By Period - Numerator');
		$form->addElement(  'select',	'factory',	'Factory',	array(""=>"")+$factoryList);
		$form->addElement(	'date', 	'period',	'Period', array(
				"format"=>"Y-m", "minYear"=>date('Y')-2, "maxYear"=>date('Y'),'addEmptyOption'=>TRUE));
		$form->addRule(		'factory',	'Required',	'required');
		$form->addRule(		'period',	'Required',	'required');
		$form->setDefaults(array('period'=>array('Y'=>date('Y'),'m'=>date('m'))));
		$form->addElement(	'submit',	'btnSubmit','Submit');

		if(!$form->validate()){
			$body = $form->toHTML();
			break;
		}

		$vars = tldUtils::cleanupFormInput($form->exportValues());
		$period = implode('-',$vars['period']).'-01';
		$rows = tldWC::byMTBFPeriod($period, array("er.man_location"=>$vars['factory']));
		$xItems = array(
				'period'=>'Period',
				'sn'=>'sn',
				'wcno'=>'WC#',
				'wcqty'=>'WC Count',
				'life'=>'NB days since shipped<br>End of period',
				'id'=>'First WC#',
				'claim_date'=>'Date',
				'warranty_status'=>'Status',
				'date_shipped'=>'Ship date',
				'hours'=>'Hours',
				'model'=>'Model',
				'type'=>'Type',
				'man_location'=>'Factory',
				'er_id'=>'ER#',
				'customer_name'=>'Customer',
		);
	break;
    case 'ATFFbyBuByPeriod':
        // Listing
        $factoryList = tldLocation::getFactoryList("smartyOptionsLocationLocation");
    	// Form to choose which Factory to list from
    	$form = new HTML_QuickForm('frmfactory', 'post');
    	$form->addElement(	'hidden',	'm[0]',		'wc');
    	$form->addElement(	'hidden',	'm[1]',		'listing');
    	$form->addElement(	'hidden',	'm[2]',		$m[2]);
    	$form->addElement(	'header',	'title',	'ATFF by Bu By Period');
    	$form->addElement(  'select',	'factory',	'Factory',	array(""=>"")+$factoryList);
        $form->addElement(	'date', 	'period',	'Period', array(
        	"format"=>"Y-m", "minYear"=>date('Y')-2, "maxYear"=>date('Y'),'addEmptyOption'=>TRUE));
    	$form->addRule(		'factory',	'Required',	'required');
    	$form->addRule(		'period',	'Required',	'required');
    	$form->setDefaults(array('period'=>array('Y'=>date('Y'),'m'=>date('m'))));
    	$form->addElement(	'submit',	'btnSubmit','Submit');

    	if(!$form->validate()){
    	    $body = $form->toHTML();
    	    break;
    	}

    	$vars = tldUtils::cleanupFormInput($form->exportValues());
    	$period = implode('-',$vars['period']).'-01';
    	$rows = tldWC::byATFFByPeriodByConstraints($period, array("er.man_location"=>$vars['factory']));
    	$xItems = array(
    		'period'=>'Period',
    	    'id'=>'WC#',
            'claim_date'=>'Date',
            'warranty_status'=>'Status',
    		'er_id'=>'ER#',
            'sn'=>'sn',
            'model'=>'Model',
            'man_location'=>'Factory',
            'date_shipped'=>'Ship date',
            'wc_diff_er_shipped'=>'Nb days<br>first failure',
    	);
    break;
    case 'byUnreturnedParts':
    	$ssoList = tldLocation::getSalesOrgList("smartyOptionsLocationLocation");
    	// Form to choose which SSO to list from
    	$form = new HTML_QuickForm('frmSSO', 'post');
    	$form->addElement(	'hidden',	'm[0]',		'wc');
    	$form->addElement(	'hidden',	'm[1]',		'listing');
    	$form->addElement(	'hidden',	'm[2]',		'byUnreturnedParts');
    	$form->addElement(	'header',	'title',	'Choose an SSO');
		$form->addElement(  'select',	'sales_org',	'SSO',	array(""=>"")+$ssoList);
		$form->addElement(  'checkbox', 'xls', 'Format', '.xls');
    	$form->addRule(		'sales_org','Required',	'required');
    	$form->addElement(	'submit',	'btnSubmit','Submit');
    	if ($form->validate()){
    	# If the form validates then freeze the data
    	$vars = tldUtils::cleanupFormInput($form->exportValues());
    	$_title = 'Parts Warranties Unreturned by SSO ' . $vars['sales_org'];
    	$a = 'sales_org like \'' . $vars['sales_org'] . '\' AND parts.d_in like \'0000-00-00\' AND parts.qty_in > 0';
    	$rows = tldWC::getPartsListByConstraints($a);
    	if(count($rows)==0){
        	$DEFAULT_ERROR[] = 'ERROR: No Parts Warranties Unreturned found for SSO ' . $vars['sales_org'] . '...';
   		}
    	}else{
    		$body = $form->toHTML();
    	}
    	$flag = 1;
    break;
    case 'returnedPartsByFactory':
//     	$ssoList = tldLocation::getSalesOrgList("smartyOptionsLocationLocation");
    	$factoryList = tldLocation::getFactoryList("smartyOptionsLocationLocation");
    	// Form to choose which Factory to list from
    	$form = new HTML_QuickForm('frmfactory', 'post');
    	$form->addElement(	'hidden',	'm[0]',		'wc');
    	$form->addElement(	'hidden',	'm[1]',		'listing');
    	$form->addElement(	'hidden',	'm[2]',		'returnedPartsByFactory');
    	$form->addElement(	'header',	'title',	'Choose an Factory');
    	$form->addElement(  'select',	'factory','Factory',	array(""=>"")+$factoryList);
    	$form->addElement(  'checkbox', 'xls', 'Format', '.xls');
    	$form->addRule(		'factory','Required',	'required');
    	$form->addElement(	'submit',	'btnSubmit','Submit');
    	if ($form->validate()){
    		# If the form validates then freeze the data
    		$vars = tldUtils::cleanupFormInput($form->exportValues());
    		$_title = 'Parts Warranties Returned by factory ' . $vars['factory'];
    		$a = 'man_location like \'' . $vars['factory'] . '\' AND  parts.d_in >0 AND parts.qty_in  not like 0';
    		$rows = tldWC::getPartsListByConstraints($a);
    		if(count($rows)==0){
    			$DEFAULT_ERROR[] = 'ERROR: No Parts Warranties returned found for factory ' . $vars['factory'] . '...';
    		}
    	}else{
    		$body = $form->toHTML();
    	}
    	$flag = 1;
    break;
        case 'WCFiltering':
            // Listing
            $data = tldWC::getFactoryList();
            foreach ($data as $location) {
                $ListManLocation[$location['man_location']] = $location['man_location'];
            }
            // Form to choose which Factory to list from
            $form = new HTML_QuickForm('frmwcfiltering', 'post');
            $form->addElement('hidden', 'm[0]', 'wc');
            $form->addElement('hidden', 'm[1]', 'listing');
            $form->addElement('hidden', 'm[2]', $m[2]);
            $form->addElement('header', 'title', 'WC filtering by Bu By Period');
            $form->addElement('select', 'man_location', 'Factory', ["" => ""] + $ListManLocation);
            $form->addElement('select', 'category', 'Category', tldEquipment::getWarrantyCategories());
            $form->addElement('text', 'dt_from', 'Date from', ["class" => "datepicker"]);
            $form->addElement('text', 'dt_to', 'Date to', ["class" => "datepicker"]);
            $form->addRule('man_location', 'Required', 'required');
            $form->addRule("dt_to", "Required...", 'required');
            $form->addRule("dt_from", "Required...", 'required');
            $form->setDefaults(["dt_to" => date("Y-m-d")]);
            $form->addElement('submit', 'btnSubmit', 'Submit');

            if (!$form->validate()) {
                $body = $form->toHTML();
                break;
            }

            $vars = tldUtils::cleanupFormInput($form->exportValues());
            $man_location = TldDatabase::escape($man_location);
            $options = ["t1.man_location" => $man_location];
            if ('' !== (string) $vars['category']) {
                $options["t1.category"] = TldDatabase::escape($vars['category']);
            }
            $rows = tldWC::byWCFiltering($vars['dt_from'], $vars['dt_to'], $options);
            $_title = "Warranty Claims filtering report - $man_location - between {$vars['dt_from']} to {$vars['dt_to']}";
            $xItems = [
                'id' => 'WC#',
                'claim_date' => 'Date',
                'hours' => 'Hours',
                'sn' => 'TLD SN',
                'model' => 'Model',
                'customer_name' => 'Customer',
                'man_location' => 'Factory',
                'category' => 'Category',
                'month_age' => 'Age in month',
                'dgt_act' => 'GT date',
                'date_shipped' => 'Ship date',
                'warranty_status' => 'Status',
                'prod_man_comments' => 'Support Comments',
                'comment' => 'Filtering Comments',
                'module' => 'Filtering Link Module',
                'item' => 'Filtering Module Reference#'
            ];
            break;
        case "byFactoryStatus":
        $erp = TldDatabase::escape($y);
        $status = TldDatabase::escape($x);
        $rows = tldWC::byERPStatus($erp, $status, "customer_name");
        $_title = "Warranty Claims by Factory $y and Status $x";
    break;
    case "bySSOStatus":
    	$sso = TldDatabase::escape($y);
    	$status = TldDatabase::escape($x);
    	$rows = tldWC::bySSOStatus($sso, $status);
    	$_title = "Warranty Claims by SSO $y - Status $x";
    	break;
    case "byPN":
    	$pn = TldDatabase::escape($pn);
    	$rows = tldWC::byPartNumberByConstraints($pn, "warranty_status NOT IN ('REJECTED','SALES CONCESSION')");
    	$_title = "Warranty Claims for PN $pn - Status ACCEPTED/PENDING";
    break;
    case "byPNWithin12Month":
        $pn = TldDatabase::escape($pn);
        $rows = tldWC::byPartNumberByConstraints($pn, "warranty_status NOT IN ('REJECTED','SALES CONCESSION')  AND (PERIOD_DIFF(DATE_FORMAT(NOW(),'%Y%m'),DATE_FORMAT(warranty.claim_date,'%Y%m')) BETWEEN 0 AND 12)");
        $_title = "Warranty Claims for PN $pn - Status ACCEPTED/PENDING - Last 12 Months";
    break;
    case "byERPStatusByASM":
    	$factory = TldDatabase::escape($x);
    	$status = TldDatabase::escape($y);
    	$rows = tldWC::byERPStatusByASM($factory, $status, $id, $sso);
    	if(empty($sso)){
    		$_title = "Warranty Claims by Factory $x - Status $y for ASM# $id - Last 12 Months";
    	}else{
    		$_title = "Warranty Claims by Factory $x - Status $y for SSO# $sso - Last 12 Months";
    	}
    	break;
    case 'byCurrentUser':
        $DEFAULT_TITLE .= "\Warranties I opened";
        $rows = tldWC::byInitiator($user->getEmail());
    break;
	case 'advsearch':
		// listing
		$userList = tldDirectory::getUserlist("smartyOptions");
		$modelList = tldModel::getList();
		$categoryList = array("ALL_TYPES"=>"ALL_TYPE")+tldType::getTypes("en","smartyOptions");
		$factoryList = tldLocation::getFactoryList("smartyOptionsIDLocation");
		$factoryQualityList = tldLocation::getFactoryList("smartyOptionsLocationLocation");
		$statusList = tldWC::getStatusList();
		$statusList = array_combine($statusList,$statusList);
		$filteringFlagList = tldWC::getFilteringFlagList();
		$filteringFlagList = array_combine($filteringFlagList,$filteringFlagList);
        $customerList = tldCustomer::getList("smartyOptions");
        $categories = tldEquipment::getWarrantyCategories();
        // Report columns
		$xItems['month_age']="Age in Months";
		$xItems['gt_date']="Actual GT Date";
		$xItems['ship_date']="Shipped Date";
		$xItems1['month_age']="Age in Months";
		$xItems1['gt_date']="Actual GT Date";
		$xItems1['ship_date']="Shipped Date";
		$xItems1['service_comments']="Service Comments";
		$xItems1['prod_man_comments']="Product Support Comments";
		// Form
		$form = new HTML_QuickForm('frmSearch', 'post');
		$form->addElement(  'hidden', 'm[0]', 'wc');
		$form->addElement(  'hidden', 'm[1]', 'listing');
		$form->addElement(  'hidden', 'm[2]', 'advsearch');
		$form->addElement(  'header', 'title', 'Search warranties');
		$form->addElement(  'text', 'target', 'Look for', array("size"=>"20"));
		$form->addElement(	'header', 'title', 'Filters');
		$form->addElement(	'select', 'entered_by', 'Initiator', array(""=>"")+$userList);
		$form->addElement(	'select', 'man_location', 'Location', array(""=>"")+$factoryList);
		$form->addElement(	'select', 'warranty_status', 'Status', array(""=>"")+$statusList);
		$form->addElement(	'select', 'type', 'Product Type', array(""=>"")+$categoryList);
		$form->addElement(	'select', 'model', 'Product Model', array(""=>"")+$modelList);
		$form->addElement(	'select', 'customerEndUser', 'Customer End User', array(""=>"")+$customerList);
		$form->addElement(	'select', 'customerBuyer', 'Customer Buyer', array(""=>"")+$customerList);
		$form->addElement(  'date', 'open_start', 'OPEN date from',
			array("format"=>"Ymd", 'addEmptyOption'=>TRUE, "minYear"=>date("Y")-2, "maxYear"=>date("Y")));
		$form->addElement(  'date', 'open_end', 'OPEN date to',
			array("format"=>"Ymd", 'addEmptyOption'=>TRUE, "minYear"=>date("Y")-2, "maxYear"=>date("Y")));
		$form->addElement(	'select', 'filtering_flag', 'Flag status', array(""=>"")+$filteringFlagList);
        $form->addElement('select', 'category', 'Category', $categories);
		$form->addElement(	'text', 'part_failing', 'Critical part failing');
		$form->addElement(	'text', 'pn', 'By Part Number');
        $form->addElement(  'text', 'pdc', 'PDC number');
        $form->addElement(  'checkbox', 'csv', 'Format', '.csv');
		$form->addElement(	'header', 'title', 'Quality dashboard / Warranties linked to PDC/SCAR/SB');
		$form->addElement(  'date', 'period_start', 'Period from',
			[
				'format' => 'Ymd',
				'addEmptyOption' => true,
				'minYear' => date('Y')-2,
				'maxYear' => date('Y'),
			]);
		$form->addElement(  'date', 'period_end', 'Period to',
			[
				'format' => 'Ymd',
				'addEmptyOption' => true,
				'minYear' => date('Y')-2,
				'maxYear' => date('Y'),
			]);
		$form->addElement(	'select', 'quality_factory', 'Factory', array(""=>"")+$factoryQualityList);
		$form->addElement(  'checkbox', 'csvQuality', 'Format', '.csv');
		$form->addElement(  'checkbox', 'wc_by_pdc', 'WC By PDC');
		$form->addElement(  'submit', 'btnSubmit', 'Submit');

		if(!$form->validate() && $output !== 'csv'){
			$body = $form->toHTML();
			break;
		}

		$vars = tldUtils::cleanupFormInput($form->exportValues());
		$target = trim($vars['target']);
		$a = array();
		// keyword
		if(!empty($target)){
			$a[] = <<<EOF
(
	warranty.claimant_details like '%$target%'
    OR warranty.customer_name like '%$target%'
    OR warranty.sales_org like '%$target%'
    OR warranty.serial_number like '%$target%'
    OR warranty.equipment_location like '%$target%'
    OR warranty.problem_desc like '%$target%'
    OR warranty.extranet_prob_desc like '%$target%'
    OR warranty.service_comments like '%$target%'
    OR warranty.parts_order_ref like '%$target%'
    OR parts.part_number like '%$target%'
    OR parts.part_description like '%$target%'
    OR parts.sn like '%$target%'
    OR warranty.parts_courier like '%$target%'
    OR warranty.prod_man_comments like '%$target%'
    OR warranty.part_failing like '%$target%'
)
EOF;
		}

		$periodStart = $vars['period_start'];
		$periodEnd = $vars['period_end'];
		$factory = $vars['quality_factory'];

		if(!empty($periodStart['Y']) && !empty($periodStart['m']) && !empty($periodStart['d'])){
			$startDate = date(implode("-", $periodStart));
			if(!empty($periodEnd['Y']) && !empty($periodEnd['m']) & !empty($periodEnd['d'])) {
				$endDate = date(implode("-", $periodEnd));
			} else {
				$DEFAULT_ERROR[] = "ERROR: Not enough constraints selected";
				$body.= $form->toHTML();
				break;
			}

			$AND_WHERE = '';
			if ('' !== $factory) {
				$AND_WHERE = " AND warranty.man_location = '$factory'";
			}
			$PDC = " demerit.id as pdc,";
            $GROUPBY = " GROUP BY models.model, demerit.id";
            if ($vars['wc_by_pdc']) {
                $GROUPBY = " GROUP BY demerit.id";
            }

            $queryPDCFromWC = <<< SQL
SELECT models.model,
       COUNT(DISTINCT warranty.id) AS qty,
       demerit.short_desc as issue,
       demerit.status as pdc_status,
       IFNULL((SELECT GROUP_CONCAT(DISTINCT(warranty.man_location)) from warranty
        LEFT JOIN mod_links ON mod_links.parent_id = warranty.id
        where warranty.warranty_status = 'ACCEPTED' AND mod_links.module = 'WC'
          AND mod_links.type = 'PDC' AND mod_links.item = demerit.id
          AND warranty.man_location != locations.location), 'N') as sister_factory,
       demerit.ifactor as ifactor,
       demerit.id as pdc,
       CASE WHEN demerit.date BETWEEN "$startDate" AND "$endDate" THEN 'New issue'
        WHEN DATEDIFF(demerit.date, "$startDate") < 0 THEN 'Known issue'
        ELSE ''
        END as issues
FROM warranty
         LEFT JOIN mod_links AS wc_links_from_wc ON wc_links_from_wc.parent_id = warranty.id
         LEFT JOIN demerit ON demerit.id = wc_links_from_wc.item
LEFT JOIN models ON warranty.model = models.model
LEFT JOIN locations ON demerit.factory = locations.id
WHERE wc_links_from_wc.module = 'WC'
  AND wc_links_from_wc.type = 'PDC'
  AND warranty.claim_date BETWEEN "$startDate" AND "$endDate"
  $AND_WHERE
$GROUPBY
SQL;

            $queryWCFromPDC = <<< SQL
SELECT models.model,
       COUNT(DISTINCT warranty.id) AS qty,
       demerit.short_desc as issue,
       CASE WHEN demerit.date BETWEEN "$startDate" AND "$endDate" THEN 'New issue'
        WHEN DATEDIFF(demerit.date, "$startDate") < 0 THEN 'Known issue'
        ELSE ''
        END as issues,
       demerit.status as pdc_status,
       IFNULL((SELECT GROUP_CONCAT(DISTINCT(warranty.man_location)) from warranty
        LEFT JOIN mod_links ON mod_links.parent_id = warranty.id
        where warranty.warranty_status = 'ACCEPTED' AND mod_links.module = 'PDC'
          AND mod_links.type = 'WC' AND mod_links.item = demerit.id
          AND warranty.man_location != locations.location), 'N') as sister_factory,
       demerit.id as pdc
FROM warranty
         LEFT JOIN mod_links AS wc_links_from_pdc ON wc_links_from_pdc.item = warranty.id
         LEFT JOIN demerit ON demerit.id = wc_links_from_pdc.parent_id
LEFT JOIN models ON warranty.model = models.model
LEFT JOIN locations ON demerit.factory = locations.id
WHERE wc_links_from_pdc.module = 'PDC'
  AND wc_links_from_pdc.type = 'WC'
  AND warranty.claim_date BETWEEN "$startDate" AND "$endDate"
  $AND_WHERE
$GROUPBY
SQL;

            $qualityResults = array_merge(tldUtils::getSqlToAssocArray($queryPDCFromWC), tldUtils::getSqlToAssocArray($queryWCFromPDC));

            $qualityResultsFormatted = [];
            foreach ($qualityResults as $result) {
                $key = sprintf('%s-%s', $result['model'], $result['pdc']);
                if (isset($vars['wc_by_pdc']) && $vars['wc_by_pdc'] !== false) {
                    $key = $result['pdc'];
                }

                if (isset($qualityResultsFormatted[$key])) {
                    $qualityResultsFormatted[$key]['qty'] = (int)$qualityResultsFormatted[$key]['qty'] + (int)$result['qty'];
                } else {
                    $qualityResultsFormatted[$key] = $result;
                }
            }

            foreach ($qualityResultsFormatted as &$result){
                $scars = [];
                $sbs = [];
                if (($pdcId = $result['pdc']) === null || $result['pdc'] === '') {
                    continue;
                }
                $pdc = new tldPDC($pdcId);
                $scars[] = array_column($pdc->getLinksFromHere('SCAR'), 'dsca');
                $scars[] = array_column($pdc->getLinksToHere('SCAR'), 'dsca');
                $result['scar'] = '<p> '.implode('</p> <p>', array_merge(...array_unique($scars))).'</p>';

                $sbs[] = array_column($pdc->getLinksFromHere('SB3'), 'dsca');
                $sbs[] = array_column($pdc->getLinksToHere('SB3'), 'dsca');
                $result['sb'] = '<p> '.implode('</p> <p>', array_merge(...array_unique($sbs))).'</p>';
            }

            ksort($qualityResultsFormatted);
			if(count($qualityResultsFormatted)){
				$rows = $qualityResultsFormatted;
                $xItems = [
                    'model' => 'Model',
                    'qty' => 'Qty',
                    'issue' => 'Issue',
                    'pdc' => 'PDC',
                    'ifactor' => 'IFactor',
                    'pdc_status' => 'PDC Status',
                    'sister_factory' => 'Sister Factory',
                    'scar' => 'SCAR',
                    'sb' => 'SB',
                    'issues' => 'Issue status',
                ];
                $_links = ['pdc' => '/en/private/product_support/index.ps.php?m[0]=pdc&m[1]=view&id='];
			}
			$smarty->assign('width', 1280);
			break;
		}
        $acl_form_fields = array(
    		"pn","entered_by","man_location","warranty_status","type","model","problem_desc","open_start","open_end","part_failing","filtering_flag", 'customerEndUser', 'customerBuyer', 'pdc', 'category'
        );
        foreach($vars as $key=>$raw){
          	if(!in_array($key, $acl_form_fields, true) || empty($raw)) {
                continue;
            }
            if($raw === "%") {
                continue;
            }
        	// Construct constraint query
            switch($key){
            case "pn":
                $a[] = " '$raw' IN (SELECT part_number FROM warranty_parts WHERE parent_id=warranty.id) ";
            break;
        	case "open_start";
                try{ $date = new DateTime(implode("-",$raw));}
                catch(Exception $e){continue 2;}
                $date = $date->format('Y-m-d');
                $a[] = " DATEDIFF('$date',claim_date)<=0 ";
        	break;
        	case "open_end";
                try{ $date = new DateTime(implode("-",$raw));}
                catch(Exception $e){continue 2;}
                $date = $date->format('Y-m-d');
                $a[] = " DATEDIFF('$date',claim_date)>=0 ";
        	break;
        	case "entered_by";
        		$tld_user = new tldUser($raw);
        		$user = $tld_user->getEmail();
        		$a[] = " $key LIKE '$user' ";
        	break;
        	case "man_location";
            	$loc = tldLocation::getLocationByID($raw);
            	$a[] = " warranty.$key LIKE '$loc' ";
        	break;
            case 'customerEndUser':
                $a[] = " er.customer_id = $raw ";
            break;
            case 'customerBuyer':
                $a[] = " er.buyer_customer_id = $raw ";
            break;
        	case "part_failing":
        	case "warranty_status";
			case 'type':
			case 'filtering_flag':
			case 'model':
				$a[] = " warranty.$key LIKE '$raw' ";
			break;
            case 'pdc':
                $warrantiesIds = tldWC::getIdsByPDC($raw);
                $strWarrantiesIds = implode(',', $warrantiesIds);
                $a[] = "warranty.id IN ($strWarrantiesIds)";
            break;
            case 'category':
                $a[] = " warranty.category = '$raw' ";
            break;
        	default:
        		if(is_numeric($raw)){
        			$a[] = " $key=$raw ";
        		}elseif(is_string($raw)){
        			$a[] = " $key LIKE '%$raw%' ";
        		}
        	break;
            }
        }
		if(count($a)<1){
        	$DEFAULT_ERROR[] = "ERROR: Not enough constraints selected";
        	$body.= $form->toHTML();
        	break;
        }

        $constraints = implode(" AND ",$a);
        $rows = tldWC::advSearch($constraints);

        $warrantyIds = [];
        foreach($rows as $row) {
            if (\in_array($row['id'], $warrantyIds)) {
                continue;
            }

            $warrantyIds[] = $row['id'];
        }

        $_title = sprintf('Warranty search results (%d WCs)', count($warrantyIds));
    break;
    case 'bySignedDate':
        $form = new HTML_QuickForm('frmBySignedDate');
        $form->addElement(  'header', 'title', 'List WC by Signed Date Range');
        $form->addElement(  'hidden', 'm[0]', 'wc');
        $form->addElement(  'hidden', 'm[1]', 'listing');
        $form->addElement(  'hidden', 'm[2]', 'bySignedDate');
        $form->addElement(  'date', 'start', 'Start Date', array("format"=>"Y-m-d"));
        $form->addElement(  'date', 'end', 'End Date', array("format"=>"Y-m-d"));
        $form->addElement(  'checkbox', 'csv', 'Format', '.csv');
        $form->addElement(  'submit', 'btnSubmit', 'Submit');
        $form->setDefaults(array('start'=>date("Y-01-01"),
                                'end'=>date("Y-m-d")));
        if ($form->validate()){
            # If the form validates then freeze the data
//          $form->freeze();
            $var = $form->exportValues();
            $start = implode('-', $var['start']);
            $end = implode('-', $var['end']);
            $rows = tldWC::byPeriod($start, $end,
                    array("compareDate"=>"prod_man_accept_date")
                );
            $_title = "WC Signed between $start and $end";
        }else{
            $body = $form->toHTML();
        }
    break;
    case 'byCustomerID':
        $cust = new tldCustomer(TldDatabase::escape($id));
        $rows = tldWC::byQuery(
            array(
                "customer_name"=>$cust->getCustomerName()
            )
        );
        $_title = "Warranty Claims for ".$cust->getCustomerName();
    break;
    case 'byCustomer':
        $form = new HTML_QuickForm('frmSearch', 'post');
        $form->addElement(  'header', 'title', 'WC by customer');
        $form->addElement(  'hidden', 'm[0]', 'wc');
        $form->addElement(  'hidden', 'm[1]', 'listing');
        $form->addElement(  'hidden', 'm[2]', 'byCustomer');
        $customers = tldCustomer::getList("smartyOptions");
        $form->addElement(  'select', 'customer', 'Customer', $customers);
        $form->addElement(  'select', 'option', 'Find by', ['wcCustomer'=>'WC Customer', 'buyer'=>'Buyer', 'end_user'=>'End User']);
        $form->addElement(  'checkbox', 'csv', 'Format', '.csv');
        $form->addElement(  'submit', 'btnSubmit', 'Submit');
        $form->addRule('target', 'This is required', 'required');
        if ($form->validate()){
            # If the form validates then freeze the data
            $form->freeze();
            $customer = new tldCustomer(TldDatabase::escape($customer));
            $_title = "Warranty Claims for {$customer->getCustomerName()}.";
            $rows = tldWC::byCustomerWC($customer, $option);
            if(count($rows)==0){
                $smarty->assign("error", "No warranties search target '$target'.");
            }
        }else{
            $body = $form->toHTML();
        }
    break;
    }

    //Special report if $flag=1 (byUnreturnedParts)
    if($flag == 1){
        if(count($rows ?? []) == 0) {
            break;
        }
        $sess["wc"]["list"] = $rows;
        if($xls == true){
        	$report = new tldXLS(
        		$rows,
                array(
                    "xItems"=>$xItemsUnreturned,
                    "showTitles"=>true
                )
            );
        	$report->out("wc_unreturned_list.xls");
        	exit;
        }
        // finaly display the listing report
        $form = new tldReportMultiLevel(
    		$rows,
        	array("customer_name"),
    		array(
    			"part_number"       =>"Part Number",
    			"id"                =>"WC#",
        		"part_description"  =>"Part Name",
    			"part_quantity"     =>"Quantity requested",
    			"d_in"	 			=>"Date received",
    			"model"             =>"Unit Model",
        		"man_location"      =>"Factory",
        		"claim_date"        =>"WC Claim Date",
        		"warranty_status"   =>"WC Status",
    			"hours"             =>"Hourmeter",
    			"customer_name"     =>"Customer Name",
        		"poster_fullname"   =>"WC Poster",
    			"airport_code"		=>"Airport Code"
        	),
        	array(
        		"passField"=>"id",
        		"title"=>$_title,
        		"url"=>"$php_self?m[0]=wc&m[1]=view&m[2]=parts&id=",
        		"showNumberOfRows"=>true,
        		"showGrandTotalRows"=>true
        	)
        );
        $body .= $form->fetch();
        $smarty->assign("width", 1280);
        break;
    }

    // Save the rows into the session
    if(is_array($rows) && count($rows)){
        $sess["wc"]["list"] = $rows;
       	$sess["wc"]["xItems"] = $xItems1;
    }
    // Specific csv request from forms field
    if($csv == true || $csvQuality == true) {
    	$output = 'csv';
	};

    switch($output){
    case 'csv':
        $report = new tldCSV(
            $sess["wc"]["list"],
            array(
                "xItems"=>$sess["wc"]["xItems"],
                "showTitles"=>true
            )
        );
        $report->out("wc_list.csv");
        exit;
    break;
    case 'multilevel':
        $DEFAULT_MENU.= <<<EOF
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<a href="$php_self?m[0]=wc&m[1]=listing&output=csv">CSV</a>
EOF;
        $form = new tldReportMultiLevel(
        $rows,
        array(
        "sales_org",
        "customer_name"
            ),
            $xItems,
            array(
            "passField"=>"id",
            "title"=>$_title,
            "url"=>"$php_self?m[0]=wc&m[1]=view&id=",
            "showNumberOfRows"=>true,
            "showGrandTotalRows"=>true
            )
        );
        $body .= $form->fetch();
        $smarty->assign("width", 1280);
        break;
	break;
    default:
    case 'columnar':
        $DEFAULT_MENU.= <<<EOF
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<a href="$php_self?m[0]=wc&m[1]=listing&output=csv">CSV</a>
EOF;
        // record data in session
        $sess["wc"]["list"] = $rows;
       	$sess["wc"]["xItems"] = $xItems;
       	// display report

		if (!isset($_links)) {
			$_links = [
				'id' => "$php_self?m[0]=wc&m[1]=view&id=",
			];
		}

        $form = new tldReportColumnar(
            $rows,
            array(
                "xItems"=>$xItems,
                "title"=>$_title,
                "links"=>$_links,
            "showNumberOfRows"=>TRUE
            )
        );
        $body.= $form->fetch();
        $smarty->assign("width", 1280);
    break;
    }
break;
case 'csv':
    switch($m[2]){
    case 'erShippedLast2yearsByFactoryPeriod':
        $form = new HTML_QuickForm('frmRtnPartStatus', 'post');
        $form->addElement(  'header', 'title', "Select date range");
        $form->addElement(  'hidden', 'm[0]', 'wc');
        $form->addElement(  'hidden', 'm[1]', 'csv');
        $form->addElement(  'hidden', 'm[2]', 'erShippedLast2yearsByFactoryPeriod');
        $form->addElement(  'select', 'factory', 'Factory',
            tldLocation::getFactoryList("smartyOptionsLocationLocation"));
        $form->addElement(  'text', 'period', 'Period');
        $form->addElement(  'submit', 'btnSubmit', 'Submit');
        $form->setDefaults(array("period"=>date("Y-m-d")));

        if(!$form->validate()){
            $body = $form->toHTML();
            break;
        }

        $vars = tldUtils::cleanupFormInput($form->exportValues());
        // Check period
        try{
            $periodObj = new DateTime($vars['period']);
        }catch(Exception $e){
            $DEFAULT_ERROR[]="ERROR: Period date set invalid: ".$e->getMessage();
            break;
        }
        $today = new DateTime(date('Y-m-d'));
        if($periodObj > $today){
            $DEFAULT_ERROR[]="ERROR: Period date in future, not possible.";
            break;
        }
        $period = $periodObj->format("Y-m-d");
        // Prepare constraints
        $a = <<<EOF
wc.man_location LIKE '{$vars['factory']}'
AND PERIOD_DIFF(
    DATE_FORMAT(LAST_DAY('$period'),'%Y%m'),
    DATE_FORMAT(er.date_shipped, '%Y%m')
) BETWEEN 0 AND 24
AND wc.claim_date > er.date_shipped
EOF;
        // Prepare options
        $opt = array(
            "select"=>"LAST_DAY('$period') AS period"
        );
        $rows = tldWC::getEquipmentsByConstraints($a, $opt);
        $filename = "WC_ER_$period.csv";
        $_options['xItems'] = array(
            "id"                    =>"WC#",
            "warranty_status"       =>"Status",
            "sales_org"             =>"SSO",
            "customer_name"         =>"Customer",
            "claim_date"            =>"Date",
            "man_location"          =>"Factory",
        	"type"					=>"Type",
            "model"                 =>"Model",
            "serial_number"         =>"Serial Number",
            "date_shipped"          =>"Shipped Date",
            "period"                =>"Period"
        );
        $_options['showTitles']=true;
    break;
    case 'byCurrentUser':
        $rows = tldWC::byInitiator($user->getEmail());
        $filename = "MyWc_".date('dMy').".csv";
        $_options['xItems'] = array(
            "id"                    =>"WC#",
            "warranty_status"       =>"Status",
            "sales_org"             =>"SSO",
            "customer_name"         =>"Customer",
            "man_location"          =>"Factory",
            "claim_date"            =>"Date",
            "entered_by"            =>"Entered By",
            "model"                 =>"Model",
            "serial_number"         =>"Serial Number",
            "equipment_location"    =>"Location",
            "part_return_date"      =>"Part Return Date",
            "parts_order_ref"       =>"Parts Order Ref",
            "problem_desc"          =>"Problem Description",
            "technician_cost_te"    =>"the cost for the T&E",
            "technician_cost_labour"=>"Labor cost",
            "parts_cost"            =>"Parts cost",
            "return_parts"          =>"Parts to be return"
        );
        $_options['showTitles']=true;
    break;
    case 'returnPartStatus':
        // Get form
        $form = new HTML_QuickForm('frmRtnPartStatus', 'post');
        $form->addElement(  'header', 'title', "Select date range");
        $form->addElement(  'hidden', 'm[0]', 'wc');
        $form->addElement(  'hidden', 'm[1]', 'csv');
        $form->addElement(  'hidden', 'm[2]', 'returnPartStatus');
        $form->addElement(  'text', 'start', 'Start Date YYYY-MM-DD');
        $form->addElement(  'text', 'end', 'End Date YYYY-MM-DD');
        $form->addElement(  'submit', 'btnSubmit', 'Submit');
        // Set default
        $form->setDefaults(array(
            "start"=>date("Y")."-01-01",
            "end"=>date("Y-m-d"))
        );

        if(!$form->validate()){
        	$body = $form->toHTML();
        	break;
        }
        $form->freeze();
        $start = TldDatabase::escape($start);
        if($start){
        	$WHERE.=" AND claim_date >= '$start'";
        }
        $end = TldDatabase::escape($end);
        if($end){
        	$WHERE.=" AND claim_date <= '$end'";
        }
    	$query=<<<EOF
        SELECT warranty.id,claim_date,warranty_status,customer_name,serial_number,man_location,sales_org,prod_man_accept_user,
        problem_desc,b.part_number,b.part_description,return_parts,part_return_date
        FROM warranty,warranty_parts AS b
        WHERE warranty.id=b.parent_id $WHERE
        ORDER by warranty.id
EOF;
        $filename = "${m[2]}.csv";
        $_options['xItems'] = array(
            "id"=>"WC#",
            "claim_date"=>"Claim Date",
            "prod_man_sign_date"=>"PSM Signed Date",
            "warranty_status"=>"Status",
            "customer_name"=>"Customer Name",
            "serial_number"=>"Serial Number",
        	"man_location"=>"Factory",
            "sales_org"=>"Sales Org",
            "prod_man_accept_user"=>"PSM Acceptance",
            "problem_desc"=>"Problem Description",
            "part_number"=>"Part Number",
            "part_description"=>"Description",
            "return_parts"=>"Return Parts?",
            "part_return_date"=>"Part Return Date"
        );
        $rows = tldUtils::getSqlToAssocArray($query);
    break;
    case 'claimedParts':
        $query=<<<EOF
            SELECT warranty.id,claim_date,part_number,part_description,warranty_parts.brand,
                quantity,failure_type,failure_system
            FROM warranty_parts,warranty
            WHERE warranty.id=warranty_parts.parent_id
            ORDER by part_number
EOF;
            $filename = "${m[2]}.csv";
            $_options['xItems'] = array(
                "id"=>"WC#",
                "claim_date"=>"Claim Date",
                "part_number"=>"Part Nubmer",
                "brand"=>"Part Brand",
                "quantity"=>"Quantity",
                "failure_type"=>"Failure Type",
                "failure_system"=>"Failure Systemn"
            );
            $_options['showTitles']=true;
            $rows = tldUtils::getSqlToAssocArray($query);
    break;
    case 'countGroupByPart':
        $form = new HTML_QuickForm('frm', 'post');
        $form->addElement(  'header', 'title', 'Count of Parts');
        $form->addElement(  'hidden', 'm[0]', 'wc');
        $form->addElement(  'hidden', 'm[1]', 'csv');
        $form->addElement(  'hidden', 'm[2]', 'countGroupByPart');
        $form->addElement(  'text', 'start', 'Start date');
        $form->addElement(  'text', 'end', 'End date');
        $man_locations = tldLocation::getFactoryList("smartyOptionsLocationLocation");
        $form->addElement(  'select', 'factory', 'Factory',
            array(""=>"ALL")+$man_locations);
        $form->addElement(  'submit', 'btnSubmit', 'Submit');
        $form->addRule('start', 'This is required', 'required');
        $form->addRule('end', 'This is required', 'required');
        $form->setDefaults(array('start'=>'0000-00-00',
                                'end'=>date("Y-m-d")));
        if ($form->validate()){
            # If the form validates then freeze the data
            $form->freeze();
            $query=<<<EOF
            SELECT part_number,warranty_parts.brand,part_description,failure_type,
                failure_system, count(*) as pn_count , group_concat(DISTINCT man_location) as mans
            FROM warranty,warranty_parts
            WHERE warranty.id=warranty_parts.parent_id AND (claim_date BETWEEN '$start' AND '$end')
            GROUP BY part_number
            ORDER BY pn_count DESC
EOF;
            $filename = "${m[2]}-$start-$end.csv";
            $_options['xItems'] = array(
                "part_number"=>"PN",
                "brand"=>"Brand",
                "part_description"=>"Description",
                "pn_count"=>"Count"
            );
            $_options['showTitles']=true;
            $rows = tldUtils::getSqlToAssocArray($query);
            if(empty($factory)){
	            foreach($rows AS &$row){
	            	$mans = explode(',',$row['mans']);
	            	foreach($man_locations AS $man){
	            		if(!in_array($man, $mans)) {
                            continue;
                        }
	            		$row[$man] = 'x';
	            		if(isset($_options['xItems'][$man])) {
                            continue;
                        }
	            		$_options['xItems'][$man]=$man;
	            	}
	            }
            }else{
            	$tmp = array();
	            foreach($rows AS $row){
	            	if (strpos($row['mans'], $factory) !== false){
	            		$tmp[] = $row;
	            	}
	            }
	            $rows=$tmp;
            }
        }else{
            $body = $form->toHTML();
        }
    break;
    case 'partsByFactoryPeriod':
        $form = new HTML_QuickForm('PartsWC', 'post');
        $form->addElement(  'header', 'title', "Get WC parts by factory and period");
        $form->addElement(  'hidden', 'm[0]', 'wc');
        $form->addElement(  'hidden', 'm[1]', 'csv');
        $form->addElement(  'hidden', 'm[2]', 'partsByFactoryPeriod');
        $form->addElement(  'select', 'man_location', 'Factory',
            tldLocation::getFactoryList("smartyOptionsLocationLocation"));
        $form->addElement(  'text', 'start', 'Start Date');
        $form->addElement(  'text', 'end', 'End Date');
        $form->addElement(  'submit', 'btnSubmit', 'Submit');
        $form->setDefaults(array("start"=>date("Y")."-01-01","end"=>date("Y-m-d")));

        if(!$form->validate()){
            $body = $form->toHTML();
            break 2;
        }

        $vars = tldUtils::cleanupFormInput($form->exportValues());
        $bu = $vars['man_location'];
        $start = $vars['start'];
        $end = $vars['end'];
        $a = "man_location='$bu' AND claim_date BETWEEN '$start' AND '$end'";
        $filename = "WC-$bu-$start-$end.csv";
        $rows = tldWC::getPartsListByConstraints($a);
        $_options['xItems'] = array(
            "id"                    =>"WC#",
            "warranty_status"       =>"Status",
            "serial_number"         =>"ER SN#",
            "entered_by"            =>"Entered By",
            "customer_name"         =>"Customer",
            "claim_date"            =>"Claim Date",
            "ship_date"             =>"Ship date",
            "type"                  =>"Type",
            "model"                 =>"Model",
            "failure_type"          =>"Failure Type",
            "failure_system"        =>"Failure System",
            "sales_org"             =>"SSO",
            "problem_desc"          =>"Problem Description",
            "prod_man_comments"     =>"Factory comments",
            "num_of_days"           =>"Nb days since shipped",
            "hours"                 =>"Hourmeter",
            "month_age"             =>"WC age (in month)",
            "parts_number"          =>"Part Number",
            "parts_description"     =>"Part description",
            "brand"                 =>"Part Brand",
            "quantity"              =>"Qty Shipped",
            "qty_in"                =>"Qty Returned",
            "d_in"                  =>"Date returned",
            "prod_man_accept_date"  =>"WC Accept Date",
            "filtering_flag"        =>"Filtering status",
        );
        $_options['showTitles']=true;
    break;
    }
	if(isset($rows)){
	    if(!count($rows)){
    	    $DEFAULT_ERROR[]="No rows found...";
        	break;
	    }
    	$report = new tldCSV($rows,$_options);
	    $report->out($filename);
    	exit;
	}
break;
default:
    $body = $smarty->fetch("$PATH/wc/homepage.wc.tpl");
    $cells = array();
    $form = new tldMatrix(
        tldWC::countByFactoryStatus(),
        "warranty_status", "man_location", "num",
        "$php_self?m[0]=wc&m[1]=listing&m[2]=byFactoryStatus",
        "Warranty Count by Status, Factory",
        [
            "doNotShowYTotals"=>true,
            "xItems" => [
                "PENDING",
                "CONDITIONAL",
                "ACCEPTED",
                "REJECTED",
                "SALES CONCESSION",
            ]
        ]
    );
    $cells[] = $form->fetch();
    $form = new tldMatrix(
        tldWC::countBySsoBystatus(),
        "warranty_status", "sales_org", "num",
        "$php_self?m[0]=wc&m[1]=listing&m[2]=bySSOStatus",
        "Warranty Count by Status, SSO",
        [
            "doNotShowYTotals"=>true,
            "xItems" => [
                "PENDING",
                "CONDITIONAL",
                "REJECTED",
                "SALES CONCESSION",
            ]
        ]
    );
    $cells[] = $form->fetch();
    $form = new tldMatrix(
        tldWC::countToBeFilteredByFactory(),
        "filtering_flag", "man_location", "num",
        "$php_self?m[0]=wc&m[1]=listing&m[2]=byFactoryFilteringFlag",
        "WC TO BE FILTERED by Factory",
        array(
        	"doNotShowXTotals"=>TRUE,
        	"doNotShowYTotals"=>TRUE
        )
    );
    $cells[] = $form->fetch();
    // Display
    $report = new tldHTMLTable(
    	$cells,
		array(
			"cols"=>2,
			"attribs"=>array(
				"table"=>" width='100%'",
				"tr"=>" bgcolor='#FFFFFF'"
			)
		)
	);
	$body.= $report->fetch();
    // Latest
    $body.= _getListing(tldWC::byLatest(),"Recently Added Warranties");
}


function _getGeneralTab(){
    global $wc;
    $server = $_SERVER['SERVER_NAME'];
    $cells = array();
    // WC
    $general = new tldAssocTable(
        $wc->itsHeader,
        array(
            "id"                =>"WC#",
            "warranty_status"   =>"Status",
            "entered_by"        =>"Entered By",
            "claimant_details"  =>"Claimant Details",
            "warranty_details"  =>"Warranty Details",
            "customer_name"     =>"Customer Name",
            "claim_date"        =>"Claim Date",
            "type"              =>"Type",
            "model"             =>"Model",
            "man_location"      =>"Factory",
            "sales_org"         =>"SSO",
            "serial_number"     =>"ER SN",
            "equipment_location"=>"ER Location",
            "hours"             =>"Hourmeter",
            "er_operation_status"=>"ER operation status",
            "part_failing"      =>"Critical PN failing",
            "part_return_date"  =>"Part Return Date",
            "parts_order_ref"   =>"Parts Order Ref",
            "category"          =>"Category",
        ),
        array(
        	"title"=>"General",
		    "links"=>array("id"=>"http://$server/en/private/product_support/index.ps.php?m[0]=wc&m[1]=view&id=")
        )
    );
    $cells[] = $general->fetch();
    // ER
    $er =  new tldEquipment($wc->getParentID());
    $report = new tldAssocTable(
    	$er->getHeader(),
        array(
            "id"=>"ER#",
			"sn"=>"Equipment SN#",
			"status"=>"Status",
			"cust_asset_num"=>"Customer Asset#",
			"type"=>"Type",
			"model"=>"Model",
			"er_batch_qty"=>"Batch Qty",
			"man_location"=>"Factory",
			"apc_fullname"=>"Airport",
			"sales_org"=>"Sales Organization",
            "dgt_act"=>"Actual GT",
            "dt_commissioned"=>"Commissioning Date",
			"date_shipped"=>"Actual Ship Date"
		),
		array(
			"title"=>"Unit Details",
			"links"=>array("id"=>"http://$server/en/private/product_support/index.ps.php?m[0]=equipment&m[1]=view&id=")
		)
	);
    $cells[] = $report->fetch();
    // FAQ
    global $kernel;

    $client = $kernel->getContainer()->get(Client::class);

    try {
        $response = $client->findBy(\AppBundle\Controller\Quality\FirstArticleQualification\FirstArticleQualificationController::RESOURCE_URL,
            ['equipmentRecords.serialNumber' => $er->getSN()],
            ['createdAt' => 'desc'],
        );
    } catch (\Symfony\Component\HttpClient\Exception\ClientException $e) {
        $response = [];
    }
    $router = $kernel->getContainer()->get('router');

    $faqs = array_reduce($response->getSimpleArrayCopy(), function ($memo, $faq) use ($router) {
        $partNumberList = '';
        foreach ($faq['partNumbers'] as $partNumber) {
            $partNumberList .= $partNumber['number'].', ';
        }
        $memo[] = [
            'link' => sprintf('<a href="%s">#%s</a>', $router->generate('first_article_qualifications_show', ['id' => $faq['id']]), $faq['id']),
            'factory' => $faq['location']['name'],
            'createdAt' => (new DateTime($faq['createdAt']))->format('Y-m-d'),
            'partNumberList' => $partNumberList,
        ];
        return $memo;
    }, []);

    $faqsReport = new tldReportColumnar($faqs,
        [
            'xItems' => [
                'link' => 'ID',
                'factory' => 'Factory',
                'createdAt'=> 'Created At',
                'partNumberList' => 'List of Part Number',
            ],
            'title' => 'FAQ list',
        ]
    );
    $cells[1] .= $faqsReport->fetch();
    // Dashboard - WC Related Data
    $report = new tldReportColumnar(
    		$wc->getRelatedData(),
    		array(
            	"xItems"=>array(
    				"desc"=>"WC Related Data",
    				"count"=>"Count"
    			),
    		"title"=>"WC Related Data",
    		"links"=>array(
					"count"=>array(
						"url"=>"/en/private/product_support/index.ps.php?m[0]=wc&m[1]=view&id={$wc->getID()}",
						"params"=>array(
							""=>"link"
						)
					)
				),
    		)
    );
    $cells[] = $report->fetch();
    // Display all
    $report = new tldHTMLTable(
    	$cells,
		array(
			"cols"=>3,
			"attribs"=>array(
				"table"=>" width='100%'",
				"tr"=>" bgcolor='#FFFFFF'"
			)
		)
	);
	return $report->fetch();
}

function getLinkedTOCLastLog(){
    global $wc;
    $links = $wc->getLinksToHere();
    foreach($links as $link) {
        if($link['module'] === 'TOC') {
            $toc = new tldToc($link['parent_id']);
            break;
        }
    }
    $content = "";
    if (!empty($toc)) {
        $logs = $toc->getFullLog();
		$tocId = $toc->getID();
        $content = <<<EOF
<h3 style="display:inline">Last log From TOC#{$tocId}</h3>&nbsp;-&nbsp;<a href="/en/private/sales_service/service.php?m[0]=toc&m[1]=view&m[2]=log&id={$tocId}">See full Logs</a>

<p><b>{$logs[0]['date']} - {$logs[0]['poster_fullname']}</b> : {$logs[0]['comment']}</p>
EOF;
    }
    return $content;
}

function _getListing($rows,$title){
    global $php_self;
    $form = new tldReportColumnar(
        $rows,
        array(
            "xItems"=>array(
                "id"=>"WC#",
    			"claim_date"=>"Date",
        		"entered_by"=>"Entered By",
                "warranty_status"=>"Status",
                "customer_name"=>"Customer",
                "model"=>"Model",
                "serial_number"=>"Serial Number",
                "part_failing"=>"Critical PN failing",
                "problem_desc"=>"Problem Description",
                "category"=>"Category"
            ),
            "title"=>$title,
            "links"=>array(
                "id"=>"$php_self?m[0]=wc&m[1]=view&id="
            )
        )
    );
    return $form->fetch();
}

function buildModuleTable(array $data, string $label): string
{
    $html = '<div class="columnar">';
    $html .= '<table class="bordered" cellpadding="3" style="width:100%;">';

    $headerStyle = 'background: #2971a8; color: white;';

    $html .= '<tr><th style="' . $headerStyle . '">PN</th>';
    foreach ($data as $partNumber => $rows) {
        $html .= '<th style="' . $headerStyle . '">' . htmlspecialchars((string) $partNumber) . '</th>';
    }
    $html .= '</tr>';

    $html .= '<tr><td><strong>' . htmlspecialchars($label) . '</strong></td>';
    foreach ($data as $rows) {
        $html .= '<td>';
        foreach ($rows as $row) {
            $html .= '<div>' . $row['id'] . '</div>';
        }
        $html .= '</td>';
    }
    $html .= '</tr>';

    $html .= '<tr><td><strong>Status</strong></td>';
    foreach ($data as $rows) {
        $html .= '<td>';
        foreach ($rows as $row) {
            $html .= '<div>' . htmlspecialchars((string) ($row['status'] ?? '')) . '</div>';
        }
        $html .= '</td>';
    }
    $html .= '</tr>';

    $html .= '</table></div><br><br><br>';

    return $html;
}
