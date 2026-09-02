<?php
require_once('HTML/QuickForm/advmultiselect.php');
include_once("graphs.common.inc.php");
include_once("sales_service.inc.php");
include_once 'PHPExcel.php';
include_once 'PHPExcel/Writer/Excel2007.php';

use AppBundle\Chart\ChartBuilder;
use AppBundle\Chart\ChartBuilderFactory;
use Symfony\Component\HttpClient\Exception\ClientException;
use ApiBundle\Client;

global $kernel;
$chartBuilderFactory = $kernel->getContainer()->get(ChartBuilderFactory::class);

$PATH .= "/kpi";
$DEFAULT_TITLE .= "\KPI";
$DEFAULT_MENU .= <<<EOF
	<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
	<a href="$php_self?m[0]=kpi">Home</a>
	&nbsp;|&nbsp;<a href="$php_self?m[0]=kpi&m[1]=help">Help</a>
EOF;

$JS_INCLUDE=["/shared/javascript/overlib/overlib.js"];
$smarty->assign("js_includes", $JS_INCLUDE);

// array to define the list of the different SPH with targets values and ERP#
$whse = [
    "DVT" => "SPH Windsor (SPH WIN)",
    "DCA" => "SPH Salinas (SPH SAL)",
    "HK1" => "SPH Hong-Kong (SPH HKG)",
    "SP1" => "SPH Shanghai (SPH SHA)",
    //"unknown1"=>"SPH MontLouis (SPH MTL) - NOT AVAILABLE",
    //"unknown2"=>"SPH Dubai (SPH DUB) - NOT AVAILABLE"
];

switch ($m[1]) {
    case "statsByCustType":
        // Listing
        $custType = tldList::optionsByListNameAsListItemListItem('list.sales.customer.types');
        $ssoList = tldLocation::getSalesOrgList();
        $factoryList = tldLocation::getFactoryList();
        $data = array_merge($ssoList, $factoryList);
        $erpList = [];
        foreach ($data as $location) {
            $erpList[$location['erp']] = "{$location['location']} ({$location['erp']})";
        }
        // Form
        $form = new HTML_QuickForm('frmByBuyer', 'post');
        $form->addElement('hidden', 'm[0]', 'kpi');
        $form->addElement('hidden', 'm[1]', 'statsByCustType');
        $form->addElement('header', 'title', "Order and Shipment Statistic by cutomer type");
        $form->addElement('select', 'type', 'Customer type', ["" => "", "ALL" => "ALL"] + $custType);
        $boxes =& $form->addElement('advmultiselect', 'erp', null, $erpList,
            ['size' => 6, 'class' => 'pool', 'style' => 'width:200px;']
        );
        $boxes->setLabel(['Select ERP(s)', 'ERP list', 'ERP to display metrics']);
        $boxes->setButtonAttributes('add', ['value' => '-->>', 'class' => 'inputCommand']);
        $boxes->setButtonAttributes('remove', ['value' => '<<--', 'class' => 'inputCommand']);
        $form->addElement('submit', 'btnSubmit', 'Submit');
        $form->addRule("type", "Required", "required");
        $form->addRule("erp", "Required", "required");

        if ($form->validate()) {
            $body = <<<EOF
			<br/><a href="$php_self?m[0]=kpi&m[1]=statsByCustType"><- Go back to the form</a>
EOF;
            $vars = tldUtils::cleanupFormInput($form->exportValues());
            foreach ($vars['erp'] as $ERP) {
                // Check ERP and $type exists
                $type = $vars['type'];
                if (empty($X_REF_CUST_TYPE[$ERP][$type]) && $type != 'ALL') {
                    $DEFAULT_ERROR[] = "ERROR: The type '$type' do not exists for the ERP '$ERP'";
                    continue;
                }
                // Get Metrics
                $body .= <<<EOF
<br><br><img src="/en/private/sales_service/kpi/graphs.php?m[0]=past12&m[1]=orderStatsByCustType&erp=$ERP&type=$type"><br/>
EOF;
                $_TITLE = "Orders Statistic by Customer type";
                $popupDef = new tldOverlib($help[$_TITLE].$groupTarget, ["CAPTION" => $_TITLE, "WIDTH" => "500", "linkName" => $_TITLE]);
                $body .= $popupDef->fetch();

                $body .= <<<EOF
<br><br><img src="/en/private/sales_service/kpi/graphs.php?m[0]=past12&m[1]=shipStatsByCustType&erp=$ERP&type=$type"><br/>
EOF;
                $_TITLE = "Shipments Statistic by Customer type";
                $popupDef = new tldOverlib($help[$_TITLE].$groupTarget, ["CAPTION" => $_TITLE, "WIDTH" => "500", "linkName" => $_TITLE]);
                $body .= $popupDef->fetch();
            }
        } else {
            $body .= $form->toHTML();
        }
        break;
    case "sfrStatsOrdered":
    case "sfrStatsLost":
    case "sfrCloseAverage":
        $erpList = tldLocation::getFactoryList("smartyOptionsIDLocation");
        $ssoList = tldLocation::getSalesOrgList("smartyOptionsIDLocation");
        $form = new HTML_QuickForm('frmSfrStatsOrdered', 'post');
        $form->addElement('hidden', 'm[0]', 'kpi');
        $form->addElement('hidden', 'm[1]', $m[1]);
        $form->addElement('header', 'title', "SFR Statistics by");
        $form->addElement('select', 'sso', 'SSO', ["" => "", "ALL" => "ALL SSO"] + $ssoList);
        $form->addElement('select', 'erp', 'or ERP', ["" => "", "ALL" => "ALL ERP"] + $erpList);
        $form->addElement('submit', 'btnSubmit', 'Submit');

        if ($form->validate()) {
            $vars = tldUtils::cleanupFormInput($form->exportValues());
            if (!empty($vars['erp']) && !empty($vars['sso'])) {
                $DEFAULT_ERROR[] = "ERROR: Please select a Factory OR a SSO only!";
                $body = $form->toHTML();
                break;
            }
            if (!empty($vars['erp'])) {
                $endurl = "&erp=".$vars['erp'];
            }
            if (!empty($vars['sso'])) {
                $endurl = "&sso=".$vars['sso'];
            }
            // Display metrics
            $body .= <<<EOF
			<br><br><img src="/en/private/sales_service/kpi/graphs.php?m[0]=past12&m[1]={$m[1]}$endurl"><br/>
EOF;
            switch ($m[1]) {
                case "sfrStatsOrdered":
                    $_TITLE = "Ordered SFR";
                    break;
                case "sfrStatsLost":
                    $_TITLE = "Lost SFR";
                    break;
                case "sfrCloseAverage":
                    $_TITLE = "Average closure of SFR";
                    break;
            }
            $popupDef = new tldOverlib($help[$_TITLE].$groupTarget, ["CAPTION" => $_TITLE, "WIDTH" => "500", "linkName" => $_TITLE]);
            $body .= $popupDef->fetch();
        } else {
            $body = $form->toHTML();
        }
        break;
    case 'csr':
        $DEFAULT_TITLE .= "\CSR";

        // Define a date interval
        $begin = new \DateTime('first day of 12 months ago');
        $end = new \DateTime('last day of last month');
        $interval = new DateInterval('P1M');
        $dateInterval = new DatePeriod($begin, $interval, $end);
        foreach ($dateInterval as $period) {
            $datePeriod[] = $period->format('Y-m');
        }

        // Listing
        $ssoList = tldLocation::getSalesOrgList("smartyOptionsLocationLocation");
        $factoryList = tldLocation::getFactoryList("smartyOptionsLocationLocation");
        $typeList = tldType::getList("smartyOptions_Name");
        $asmList = tldCustomer::getAsmList(['smartyOptionsTech_name' => 1]);

        // Get form
        $form = new HTML_QuickForm('frmKpiCSR', 'post');
        $form->addElement('hidden', 'm[0]', 'kpi');
        $form->addElement('hidden', 'm[1]', 'csr');
        $form->addElement('hidden', 'm[2]', $m[2]);
        $form->addElement('header', 'title', "CSR KPI");
        switch ($m[2]) {
            case 'bySSO':
                $form->addElement('select', 'sso', 'SSO', ["" => ""] + $ssoList);
                $form->addElement('select', 'type', 'Unit Type', ["" => "", "ALL" => "All"] + $typeList);
                $form->addRule('sso', 'Required', 'required');
                $form->addRule('type', 'Required', 'required');
                $form->setDefaults(["type" => "ALL"]);
                break;
            case 'byFactory':
                if (isset($_GET['factory'])) {
                    $form->_submitValues = $_GET;
                    $form->_flagSubmitted = true;
                    break;
                }
                $form->addElement('select', 'factory', 'Factory', ["" => ""] + $factoryList);
                $form->addRule('factory', 'Required', 'required');
                break;
            case 'dispatchBySSO':
                $form->addElement('select', 'sso', 'SSO', ['ALL SSO' => 'ALL SSO'] + $ssoList);
                $form->addElement('select', 'start', 'From', array_combine($datePeriod, $datePeriod));
                $form->addElement('select', 'end', 'To', array_combine($datePeriod, $datePeriod));
                $form->setDefaults(['end' => end($datePeriod), 'sso' => 'ALL SSO']);
                break;
            case 'BySSOByASM':
                $form->addElement('select', 'sso', 'SSO', ['' => ''] + $ssoList);
                $form->addElement('select', 'asm', 'ASM', ['' => ''] + $asmList);
                $form->addRule('sso', 'Required', 'required');
                break;
            case 'ByFactoryByASM':
                $form->addElement('select', 'factory', 'Factory', ['' => ''] + $factoryList);
                $form->addElement('select', 'asm', 'ASM', ['' => ''] + $asmList);
                $form->addRule('factory', 'Required', 'required');
                break;
        }

        $form->addElement('submit', 'btnSubmit', 'Submit');

        if (!$form->validate()) {
            $body = $form->toHtml();
            break;
        }

        // cq = Commissioning Quality, acr = Average CSR Ratio
        $KPI_TYPE = ['cq', 'acr'];
        $vars = tldUtils::cleanupFormInput($form->exportValues());
        if (isset($_GET['factory'])) {
            $vars['factory'] = $_GET['factory'];
        }
        // Construct conditions & links param
        $a = null;
        $link = null;
        $option = null;
        switch ($m[2]) {
            case 'bySSO':
                $a = ['service.sales_org' => $vars['sso']];
                $legend = ' by SSO: '.$vars['sso'];
                $option = ['type' => $m[2]];
                if ('ALL' !== $vars['type']) {
                    $a = array_merge($a, ['service.type' => $vars['type']]);
                    $legend .= ' - '.$vars['type'];
                }
                break;
            case 'byFactory':
                $a = ["service.man_location" => $vars['factory']];
                $legend = " by Factory: ".$vars['factory'];
                break;
            case 'dispatchBySSO':
                $a = 'ALL SSO' !== $vars['sso'] ? ['service.sales_org' => $vars['sso']] : '';
                $legend = ' for - '.$vars['sso'];
                $from = $vars['start'];
                $until = $vars['end'];
                break;
            case 'BySSOByASM':
                $a = [
                    'service.sales_org' => $vars['sso'],
                ];
                if (!empty($vars['asm'])) {
                    $a['service.sales_rep'] = $vars['asm'];
                }
                $legend = ' for - '.$vars['asm'].' in '.$vars['sso'];
                break;
            case 'ByFactoryByASM':
                $a = [
                    'service.man_location' => $vars['factory'],
                ];
                if (!empty($vars['asm'])) {
                    $a['service.sales_rep'] = $vars['asm'];
                }
                $legend = ' for - '.$vars['asm'].' in '.$vars['factory'];
                break;
        }

        switch ($m[2]) {
            case 'bySSO':
            case 'byFactory':
                //Get KPI Data
                $rows = tldCSR::getKPIDataByConstraints($a, $option);
                $cqs = $rows['cq'];
                $acrs = $rows['acr'];
                $DATA_CQ_SUM = [];
                $DATA_ACR_SUM = [];

                // Get xItems
                $xItems = ["kpi_type" => "KPI"];
                foreach ($cqs as $line) {
                    $xItems[$line['xval']] = $line['xval'];
                }
                // Rearrange data for Summary
                $DATA_ACR_SUM['acr'] = ["kpi_type" => "ACR"];
                $DATA_ACR_SUM['csr'] = ["kpi_type" => "CSR Count"];
                $DATA_CQ_SUM['aspect'] = ["kpi_type" => "Aspect"];
                $DATA_CQ_SUM['conformity'] = ["kpi_type" => "Conformity"];
                $DATA_CQ_SUM['operational'] = ["kpi_type" => "Operational"];
                $DATA_CQ_SUM['csr'] = ["kpi_type" => "CSR Count"];
                foreach ($xItems as $date) {
                    foreach ($cqs as $cq) {
                        if ($cq['xval'] == $date) {
                            $DATA_CQ_SUM[$cq['component']][$date] = $cq['val'];
                            $DATA_CQ_SUM['csr'][$date] = $cq['csr_count'];
                        }
                    }
                    foreach ($acrs as $acr) {
                        if ($acr['xval'] == $date) {
                            $DATA_ACR_SUM['acr'][$date] = $acr['val'];
                            $DATA_ACR_SUM['csr'][$date] = $acr['csr_count'];
                        }
                    }
                }

                // *********************** CQ GRAPH ***********************
                // Rearrange data for Graphs
                $DATA_CQ = $graph_periods = $xAxis = [];
                foreach ($cqs as $cq) {
                    $date = new DateTime();
                    $date->setDate(substr($cq['xval'], 0, 4), substr($cq['xval'], 4, 6), 1);
                    $graph_periods[$cq['xval']] = $date->format('M');
                    $DATA_CQ[$cq['component']][] = $cq['val'];
                    $DATA_CQ['csr'][$cq['xval']] = $cq['csr_count'];
                }
                // Graph periods
                foreach ($graph_periods as $period) {
                    $xAxis[] = $period;
                }
                // CQ Graph
                $graphCQ = new tldGraph();
                $graphCQ->setTitle("Commissioning Quality $legend");
                $graphCQ->setMultipleYAxisTitle("Component Points", ["max" => (int)15]);
                $graphCQ->setMultipleYAxisTitle("Number of CSR", ["opposite" => true]);
                $graphCQ->setXAxisCategories($xAxis);
                // Get data & prepare it
                $dataSerieCSR = [];
                foreach ($DATA_CQ as $key => $CQS) {
                    $dataSerie = [];
                    foreach ($CQS as $key2 => $CQ) {
                        if ('csr' === $key) {
                            $dataSerieCSR[] = (int)$CQ;
                        } else {
                            $dataSerie[] = (float)$CQ;
                        }
                    }
                    if (!empty($dataSerie)) {
                        $graphCQ->addBar($dataSerie, ['name' => ucfirst($key),
                            'stacking' => 'normal',
                            'dataLabels' => ['enabled' => false],
                            'enableMouseTracking' => true]);
                    }
                }
                $graphCQ->addScatter($dataSerieCSR, ['name' => 'Quantity CSR', 'yAxis' => 1]);
                // Display
                $body .= $graphCQ->fetch();

                // Display KPI CQ summary
                $report = new tldReportColumnar(
                    $DATA_CQ_SUM,
                    [
                        "xItems" => $xItems,
                        "title" => "Data Summary",
                    ]
                );
                $body .= $report->fetch();
                $body .= "<br><br>";

                // *********************** ACR GRAPH ***********************
                // Rearrange data for Graphs
                $DATA_ACR = $graph_periods = $xAxis = [];
                foreach ($acrs as $acr) {
                    $date = new DateTime();
                    $date->setDate(substr($acr['xval'], 0, 4), substr($acr['xval'], 4, 6), 1);
                    $graph_periods[$acr['xval']] = $date->format('M');
                    $DATA_ACR['acr'][] = $acr['val'];
                    $DATA_ACR['csr'][] = $acr['csr_count'];
                }
                // Graph periods
                foreach ($graph_periods as $period) {
                    $xAxis[] = $period;
                }

                // ACR Graph
                $graphACR = new tldGraph();
                $graphACR->setTitle("Average CSR Ratio $legend");
                $graphACR->setMultipleYAxisTitle("Ratio", ["max" => (int)100, 'labels' => ['format' => '{value} %']]);
                $graphACR->setMultipleYAxisTitle("Number of CSR", ["opposite" => true]);
                $graphACR->setXAxisCategories($xAxis);
                // Get data & prepare it
                $dataSerieCSR = [];
                foreach ($DATA_ACR as $key => $ACRS) {
                    $dataSerie = [];
                    foreach ($ACRS as $key2 => $ACR) {
                        if ('csr' === $key) {
                            $dataSerieCSR[] = (int)$ACR;
                        } else {
                            $dataSerie[] = (float)$ACR;
                        }
                    }
                    if (!empty($dataSerie)) {
                        $graphACR->addLine($dataSerie, ['name' => 'Average CSR Ratio',
                            'dataLabels' => ['enabled' => false],
                            'enableMouseTracking' => true]);
                    }
                }
                $graphACR->addScatter($dataSerieCSR, ['name' => 'Quantity CSR', 'yAxis' => 1,
                    'dataLabels' => ['enabled' => true]]);
                // Display
                $body .= $graphACR->fetch();

                // Display KPI ACR summary
                $report = new tldReportColumnar(
                    $DATA_ACR_SUM,
                    [
                        "xItems" => $xItems,
                        "title" => "Data Summary",
                    ]
                );
                $body .= $report->fetch();

                break;
            case 'BySSOByASM':
            case 'ByFactoryByASM':
                //Get KPI Data
                $rows = tldCSR::getKPIDataForSOLIncomplete($a);

                // Get xItems
                $xItems = ["kpi_type" => "KPI"];
                foreach ($rows as $line) {
                    $xItems[$line['xval']] = $line['xval'];
                }
                // Rearrange data for Summary
                $summaryData = [
                    'sol_incomplete' => [
                        "kpi_type" => "SOL Incomplete Ratio",
                    ],
                    'csr' => [
                        "kpi_type" => "CSR Count",
                    ],
                ];
                foreach ($xItems as $date) {
                    foreach ($rows as $row) {
                        if ($row['xval'] == $date) {
                            $summaryData['sol_incomplete'][$date] = $row['val'];
                            $summaryData['csr'][$date] = $row['csr_count'];
                        }
                    }
                }

                // *********************** GRAPH ***********************
                // Rearrange data for Graphs
                $data = $graph_periods = $xAxis = [];
                $maxValue = 0;
                foreach ($rows as $row) {
                    $date = new DateTime();
                    $date->setDate(substr($row['xval'], 0, 4), substr($row['xval'], 4, 6), 1);
                    $graph_periods[$row['xval']] = $date->format('M');
                    $data['sol_incomplete'][] = $row['val'];
                    $data['csr'][] = $row['csr_count'];

                    if ($row['val'] > $maxValue) {
                        $maxValue = $row['val'];
                    }
                }
                // Graph periods
                foreach ($graph_periods as $period) {
                    $xAxis[] = $period;
                }
                // Graph
                $graph = new tldGraph();
                $graph->setTitle("CSR Conformity Note Affected by SOL Incomplete $legend");
                $graph->setMultipleYAxisTitle("Ratio", ['min' => 0, 'max' => (int)$maxValue, 'labels' => ['format' => '{value} %']]);
                $graph->setMultipleYAxisTitle("Number of CSR", ["opposite" => true]);
                $graph->setXAxisCategories($xAxis);
                // Get data & prepare it
                $dataSerieCSR = [];
                foreach ($data as $key => $values) {
                    $dataSerie = [];
                    foreach ($values as $key2 => $value) {
                        if ('csr' === $key) {
                            $dataSerieCSR[] = (int)$value;
                        } else {
                            $dataSerie[] = (float)$value;
                        }
                    }
                    if (!empty($dataSerie)) {
                        $graph->addLine($dataSerie, ['name' => 'Average CSR Ratio',
                            'dataLabels' => ['enabled' => false],
                            'enableMouseTracking' => true]);
                    }
                }
                $graph->addScatter($dataSerieCSR, ['name' => 'Quantity CSR', 'yAxis' => 1,
                    'dataLabels' => ['enabled' => true]]);
                // Display
                $body .= $graph->fetch();

                // Display KPI summary
                $report = new tldReportColumnar(
                    $summaryData,
                    [
                        "xItems" => $xItems,
                        "title" => "Data Summary",
                    ]
                );
                $body .= $report->fetch();
                $body .= "<br><br>";
                break;
            case 'dispatchBySSO':
                //Get KPI Data
                $rows = tldCSR::getKPIDataFDAAndDPA($from, $until, $a);
                $dataSum = [];
                $xValues = array_column($rows, 'xValue');
                $DPA = array_column($rows, 'DPA');
                $FDA = array_column($rows, 'FDA');
                $percentDPA = array_column($rows, 'percentDPA');

                // Get xItems
                $xItems = ["typeKPI" => "KPI"] + array_combine($xValues, $xValues);

                // Rearrange data for Summary
                $dataSum['DPA'] = ["typeKPI" => "DPA"] + array_combine($xValues, $DPA);
                $dataSum['FDA'] = ["typeKPI" => "FDA"] + array_combine($xValues, $FDA);
                $dataSum['DPA%'] = ["typeKPI" => "DPA%"] + array_combine($xValues, $percentDPA);

                // Rearrange data for Graph
                $xAxis = [];
                foreach ($xValues as $value) {
                    $date = new DateTime($value);
                    $xAxis[] = $date->format('M');
                }

                $dataGraph = [];
                $dataGraph['DPA'] = $DPA;
                $dataGraph['FDA'] = $FDA;
                $dataGraph['DPA%'] = $percentDPA;

                // Graph
                $graph = new tldGraph();
                $graph->setTitle("AST Dispatch & Reactivity $legend");
                $graph->setMultipleYAxisTitle("Number of days", ['min' => 0]);
                $graph->setMultipleYAxisTitle("Percentage for DPA%", ['min' => 0, 'max' => 100, 'labels' => ['format' => '{value} %'], "opposite" => true]);
                $graph->setXAxisCategories($xAxis);

                // Get data & prepare it
                foreach ($dataGraph as $title => $data) {
                    $dataSerie = [];
                    foreach ($data as $subData) {
                        $dataSerie[] = (float)$subData;
                    }
                    $color = '';
                    if ($title === 'FDA') {
                        $color = 'blue';
                    } else {
                        $color = 'orange';
                    }

                    if (!empty($dataSerie)) {
                        if ($title !== 'DPA%') {
                            $graph->addBar($dataSerie, [
                                'name' => ucfirst($title),
                                'stacking' => 'normal',
                                'dataLabels' => ['enabled' => false],
                                'enableMouseTracking' => true,
                                'color' => $color,
                            ]);
                        } else {
                            $graph->addLine($dataSerie, [
                                'name' => ucfirst($title),
                                'yAxis' => 1,
                                'dataLabels' => ['enabled' => false],
                                'enableMouseTracking' => true,
                                'color' => 'grey',
                            ]);
                        }
                    }
                }
                // Display
                $body .= $graph->fetch();
                // Display KPI summary
                $report = new tldReportColumnar(
                    $dataSum,
                    [
                        'xItems' => $xItems,
                        'title' => 'Data Summary',
                    ]
                );
                $body .= $report->fetch();

                // Display description and calculation rules
                $_TITLE = "Dispatch AST & Activity";
                $popupDef = new tldOverlib($help[$_TITLE], ["CAPTION" => $_TITLE, "WIDTH" => "500", "linkName" => $_TITLE]);
                $body .= $popupDef->fetch();
                break;
        }
        break;
    case 'toc':
        $DEFAULT_TITLE .= "\TOC";
        // Listing
        $ssoList = tldTOC::getSSOList();
        switch ($m[2]) {
            case 'averageDaysOpenInProgress':
                $startPreviousMonth = new \DateTime('first day of previous month');
                $endPreviousMonth = new \DateTime('last day of previous month');
                $startActualMonth = new \DateTime('first day of this month');
                $endActualMonth = new \DateTime('last day of this month');

                $rowsPreviousMonth = tldTOC::getAverageDaysOpenInProgressKPIBySSO($startPreviousMonth, $endPreviousMonth, array_keys($ssoList));
                $rowsActualMonth = tldTOC::getAverageDaysOpenInProgressKPIBySSO($startActualMonth, $endActualMonth, array_keys($ssoList));
                $rowsCountSolved = tldTOC::countSolvedByPeriod($startActualMonth, $endActualMonth, array_keys($ssoList));
                $tocStatus = ['OPEN', 'IN PROGRESS'];
                $previousMonthRes = [];
                $actualMonthRes = [];
                $reducer = '';

                foreach ($rowsPreviousMonth as $result) {
                    $reducer = static function ($memo, $result) {
                        $memo[$result['status']][$result['sso_name']] = [
                            'val' => (int)$result['avg'],
                        ];
                        return $memo;
                    };
                }
                $previousMonthRes = array_reduce($rowsPreviousMonth, $reducer, ['OPEN' => [], 'IN PROGRESS' => []]);

                foreach ($rowsActualMonth as $result) {
                    $reducer = function ($memo, $result) {
                        $memo[$result['status']][$result['sso_name']] = [
                            'val' => (int)$result['avg'],
                        ];
                        return $memo;
                    };
                }
                $actualMonthRes = array_reduce($rowsActualMonth, $reducer, ['OPEN' => [], 'IN PROGRESS' => []]);

                foreach ($ssoList as $value) {
                    foreach ($tocStatus as $status) {
                        if (empty($previousMonthRes[$status][$value])) {
                            $previousMonthRes[$status][$value] = [
                                'val' => null,
                            ];
                        }
                        if (empty($actualMonthRes[$status][$value])) {
                            $actualMonthRes[$status][$value] = [
                                'val' => null,
                            ];
                        }
                    }
                }
                sort($ssoList);
                ksort($previousMonthRes['OPEN']);
                ksort($previousMonthRes['IN PROGRESS']);
                ksort($actualMonthRes['OPEN']);
                ksort($actualMonthRes['IN PROGRESS']);

                // Graph 1 - Previous Month
                $graphPreviousMonth = new tldGraph();
                $graphPreviousMonth->setTitle('Average days Open for a TOC by SSO | M-1');
                $graphPreviousMonth->setXAxisTitle('SSO');
                $graphPreviousMonth->setXAxisCategories($ssoList);
                $graphPreviousMonth->setYAxisTitle('Average Days');
                $graphPreviousMonth->addBar(array_column($previousMonthRes['OPEN'], 'val'), ['name' => 'OPEN']);
                $graphPreviousMonth->addBar(array_column($previousMonthRes['IN PROGRESS'], 'val'), ['name' => 'IN PROGRESS']);

                // Graph 2 - Actual Month
                $graphActualMonth = new tldGraph();
                $graphActualMonth->setTitle('Average days Open for a TOC by SSO | Actual Month');
                $graphActualMonth->setXAxisTitle('SSO');
                $graphActualMonth->setXAxisCategories($ssoList);
                $graphActualMonth->setYAxisTitle('Average Days');
                $graphActualMonth->addBar(array_column($actualMonthRes['OPEN'], 'val'), ['name' => 'OPEN']);
                $graphActualMonth->addBar(array_column($actualMonthRes['IN PROGRESS'], 'val'), ['name' => 'IN PROGRESS']);

                // Report for count SOLVED by SSO - Actual month
                $countReport = new tldReportColumnar(
                    $rowsCountSolved,
                    [
                        'xItems' => [
                            'toc_count' => 'Amount',
                            'sso_name' => 'SSO',
                        ],
                        'title' => 'Count TOC Solved by SSO | Actual Month',
                    ]
                );

                // Display description, calculation rules, graphs and SOLVED report
                $_TITLE = 'Average Days Open for TOC';
                $popupDef = new tldOverlib($help[$_TITLE], ['CAPTION' => $_TITLE, 'WIDTH' => '500', 'linkName' => $_TITLE]);
                $body .= $graphPreviousMonth->fetch();
                $body .= $graphActualMonth->fetch();
                $body .= $popupDef->fetch();
                $body .= $countReport->fetch();
                break 2;
                case 'byFactorySupportRequiredGraph':
                    $factoryId = TldDatabase::escape($location);
                    $factoryName = (new tldLocation($factoryId))->getBuName();
                    $rows = tldTOC::byFactorySupportRequired('ALL', $factoryName);

                    $results = [];
                    foreach ($rows as $toc) {
                        $date = substr($toc['dt'], 0, -3);

                        if (!\array_key_exists($date, $results)) {
                            $results[$date] = ['xval' => $date, 'yval' => 0, 'zval' => $factoryName];
                        }

                        $results[$date]['yval']++;
                    }

                    $chart = $chartBuilderFactory
                        ->getLineChartBuilder()
                        ->setTitle('Active TOC with Factory Support Required (by open date)')
                        ->addYAxis('Count')
                    ;

                    foreach ($results as $key => $row) {
                        $chart->addPlot(
                            $row['zval'],
                            $row['xval'],
                            (int)$row['yval']
                        );
                    }

                    $chart = json_encode($chart->buildConfig());

                    $body .= <<<EOF
<br/><br/>
<div id="container-$type" style="width:100%; height:400px;"></div>
<script>
	$(document).ready(function() {
	  $('#container-$type').highcharts($chart);
});
</script>
EOF;
                break 2;
                case 'byLengthOfClosure':
                    $ssoList = tldLocation::getSalesOrgList("smartyOptionsIDLocation");
                    $form = new HTML_QuickForm('byLengthOfClosure', 'post');
                    $form->addElement('hidden', 'm[0]', 'kpi');
                    $form->addElement('hidden', 'm[1]', $m[1]);
                    $form->addElement('hidden', 'm[2]', 'byLengthOfClosure');
                    $form->addElement('header', 'title', 'Distribution of TOCs by length of closure');
                    $form->addElement('select', 'sso', 'SSO', $ssoList);
                    $form->addElement('submit', 'btnSubmit', 'Submit');

                    if ($form->validate()) {
                        $vars = tldUtils::cleanupFormInput($form->exportValues());
                        if (empty($vars['sso'])) {
                            $DEFAULT_ERROR[] = "ERROR: Please select a SSO!";
                            $body = $form->toHTML();
                            break;
                        }
                        $oneYearAgo = (new DateTime('midnight first day of this month last year'))->format('Y-m-d');
                        $oneYearsResults = tldTOC::getTocsLengthOfClosureKPI($vars['sso'], $oneYearAgo);

                        $today = (new DateTime())->format('Y-m');
                        $rows = [
                            ['solvedIn' => 'Under 7 days', 'total' => 0, 'YearTotal' => 0, 'YearRemote' => 0],
                            ['solvedIn' => '7 to 14 days', 'total' => 0, 'YearTotal' => 0, 'YearRemote' => 0],
                            ['solvedIn' => '14 to 21 days', 'total' => 0, 'YearTotal' => 0, 'YearRemote' => 0],
                            ['solvedIn' => 'More than 21 days', 'total' => 0, 'YearTotal' => 0, 'YearRemote' => 0],
                        ];

                        $thirtyDaysAgo = (new DateTime('1 month ago'))->format('Y-m-d');
                        $thirtyDaysResult = tldTOC::getTocsLengthOfClosureKPI($vars['sso'], $thirtyDaysAgo);

                        // calculate total of TOC for the current month
                        foreach ($thirtyDaysResult as $result) {
                            foreach ($rows as &$row) {
                                if ($result['solvedIn'] === $row['solvedIn']) {
                                    $row['total'] += $result['total'];
                                    if ($result['remote']) {
                                        $row['remote'] = $result['total'];
                                    }
                                    continue 2;
                                }
                            }
                        }
                        foreach ($rows as &$row) {
                            if (null !== $row['remote'] ?? null && $row['total'] !== 0) {
                                $row['resolveRate'] = round($row['remote'] / $row['total'] * 100);
                                continue;
                            }
                            $row['resolveRate'] = 0;
                        }

                        // calculate total of TOC for the past year
                        foreach ($oneYearsResults as $result) {
                            foreach ($rows as &$row) {
                                if ($result['solvedIn'] === $row['solvedIn']) {
                                    $row['YearTotal'] += $result['total'];
                                    if ($result['remote']) {
                                        $row['YearRemote'] += $result['total'];
                                    }
                                    continue 2;
                                }
                            }
                        }
                        foreach ($rows as &$row) {
                            if ($row['YearTotal'] !== 0) {
                                $row['delta'] = $row['resolveRate'] - round($row['YearRemote'] / $row['YearTotal'] * 100);
                                continue;
                            }
                            $row['delta'] = 0;
                        }
                        
                        $report = new tldReportColumnar(
                            $rows,
                            [
                                'xItems' => [
                                    'solvedIn' => 'Solved in',
                                    'total' => 'Numbers of TOCs',
                                    'resolveRate' => '% Solved Remote',
                                    'delta' => 'Delta versus 12 month moving average',
                                ],
                                'title' => sprintf('Distribution of TOCs by length of closure for %s from %s', (new tldLocation($vars['sso']))->getBuName(), $thirtyDaysAgo),
                            ]
                        );
                        $body = $report->fetch();
                    } else {
                        $body = $form->toHTML();
                    }
                    break 2;
        }
        // Get form
        $form = new HTML_QuickForm('frmSfrStatsOrdered', 'post');
        $form->addElement('hidden', 'm[0]', 'kpi');
        $form->addElement('hidden', 'm[1]', 'toc');
        $form->addElement('hidden', 'm[2]', $m[2]);
        $form->addElement('header', 'title', "TOC KPI");
        switch ($m[2]) {
            case 'byCustomerIDSSOID':
                $form->addElement('select', 'cuid', 'Customer', ["" => ""] + tldCustomer::getList("smartyOptions"));
                $form->addElement('select', 'ssoid', 'SSO', ["" => ""] + $ssoList);
                $form->addElement('date', 'start', 'Start Date', ["format" => "Ym", 'addEmptyOption' => false, "minYear" => date("Y") - 2, "maxYear" => date("Y")]);
                $form->addElement('date', 'end', 'End Date', ["format" => "Ym", 'addEmptyOption' => false, "minYear" => date("Y") - 2, "maxYear" => date("Y")]);
                $form->setDefaults(['start' => date("Y-01"), 'end' => date("Y-m")]);
                $form->addRule('ssoid', 'Required', 'required');
                $form->addRule('cuid', 'Required', 'required');
                break;
            case 'byTecID':
                $grp = new tldGroup("gg_SERVICE");
                $selectPeople =& $form->addElement(
                    'advmultiselect', 'tecid', null,
                    $grp->getUserlist('smartyOptions'),
                    [
                        'size' => 10,
                        'class' => 'pool',
                        'style' => 'width:200px;',
                    ]
                );
                $selectPeople->setLabel(['Technicians (max 10)']);
                $selectPeople->setButtonAttributes('add', ['value' => '-->>', 'class' => 'inputCommand']);
                $selectPeople->setButtonAttributes('remove', ['value' => '<<--', 'class' => 'inputCommand']);
                $form->addRule('tecid', 'Required', 'required');
                break;
            case 'bySSOID':
                $form->addElement('select', 'ssoid', 'SSO', ["" => ""] +
                    tldLocation::getSalesOrgList("smartyOptionsIDLocation"));
                $form->addRule('ssoid', 'Required', 'required');
                break;
            case 'byActivityType':
                $form->addElement('select', 'ssoid', 'SSO', ['' => ''] + tldLocation::getSalesOrgList("smartyOptionsIDLocation"));
                $form->addRule('ssoid', 'Required', 'required');
                $form->addElement('select', 'activity_type', 'Activity Type', ['other' => 'Other'] + tldTOC::getActivityTypeList());
                $form->addRule('activity_type', 'Required', 'required');
                break;
        }
        $form->addElement('submit', 'btnSubmit', 'Submit');

        if (!$form->validate()) {
            $body = $form->toHTML();
            break;
        }

        $KPI_TYPE = ['nto', 'nts', 'tir', 'tat', 'tol'];
        $vars = tldUtils::cleanupFormInput($form->exportValues());

        if (isset($vars['tecid']) && count($vars['tecid']) > 10) {
            $DEFAULT_ERROR[] = "ERROR: Cannot select more than 10 people!";
            $body = $form->toHTML();
            break;
        }

        // Construct conditions & links param
        $a = null;
        $link = null;
        $technicianIds = [];
        switch ($m[2]) {
            case 'byCustomerIDSSOID':
                $a = ["cuid" => $vars['cuid'], "ssoid" => $vars['ssoid']];
                $cu = new tldCustomer($vars['cuid']);
                $bu = new tldLocation($vars['ssoid']);
                $ds = implode('-', $vars['start']);
                $de = implode('-', $vars['end']);
                $start = vsprintf('%1$04d%2$02d', $vars['start']);
                $end = vsprintf('%1$04d%2$02d', $vars['end']);
                $opt['periodConstraints'] = "p.nam_period BETWEEN '$start' AND '$end'";
                $link = "&id=".$vars['cuid']."&id2=".$vars['ssoid']."&ds=".$ds."&de=".$de;
                $legend = $cu->getCustomerName()." - ".$bu->getShortName();
                $flag_period = 1;
                break;
            case 'byTecID':
                $technicianIds = $vars['tecid'];
                if (count($technicianIds) === 1) {
                    $vars['tecid'] = reset($technicianIds);
                    $a = ['tecid' => $vars['tecid']];
                    $link = '&id='.$vars['tecid'];
                    $tec = new tldUser($vars['tecid']);
                    $legend = $tec->getFullname();
                }
                break;
            case 'bySSOID':
                $a = ["ssoid" => $vars['ssoid']];
                $link = "&id=".$vars['ssoid'];
                $bu = new tldLocation($vars['ssoid']);
                $legend = $bu->getShortName();
                break;
            case 'byActivityType':
                $activityType = $vars['activity_type'] !== 'other' ? $vars['activity_type'] : '';
                $ssoid = $vars['ssoid'];
                $bu = new tldLocation($ssoid);
                $a = ['activity_type' => $activityType, 'ssoid' => $ssoid];
                $link = sprintf('&activityType=%s&id=%s', $activityType, $ssoid);
                $legend = sprintf('%s - %s', $bu->getShortName(), $activityType === '' ? 'Other' : $activityType);
                break;
        }

        // For the technician KPI: generate a report when only one technician is selected
        if (count($technicianIds) === 1) {
            // Rearange data
            // Get KPI data
            if ((int)$flag_period === 1) {
                $rows = tldTOC::getKPIPast12MonthByConstraints($a, $opt);
            } else {
                $rows = tldTOC::getKPIPast12MonthByConstraints($a);
            }
            // Get xItems
            $xItems = ['kpi_type' => 'KPI'];
            foreach ($rows as $line) {
                $xItems[$line['xval']] = $line['xval'];
            }
            $DATA = [];
            foreach ($KPI_TYPE as $type) {
                $DATA[$type] = ['kpi_type' => strtoupper($type)];
                foreach ($xItems as $date) {
                    foreach ($rows as $row) {
                        if ($row['xval'] == $date) {
                            $DATA[$type][$date] = $row[$type];
                        }
                    }
                }
            }

            // Display KPI summary
            $report = new tldReportColumnar(
                $DATA,
                [
                    "xItems" => $xItems,
                    "title" => "TOC KPI summary - $legend",
                ]
            );
            $body .= $report->fetch();
        }

        if ($m[2] !== 'byTecID') {
            // Display ALL KPI graphs
            foreach ($KPI_TYPE as $type) {
                if ($flag_period == 1) {
                    $body .= <<<EOF
<br><br><img src="/en/private/sales_service/kpi/graphs.php?m[0]=history&m[1]=toc&m[2]=$type&m[3]={$m[2]}$link"><br/>
EOF;
                } else {
                    $body .= <<<EOF
<br><br><img src="/en/private/sales_service/kpi/graphs.php?m[0]=past12&m[1]=toc&m[2]=$type&m[3]={$m[2]}$link"><br/>
EOF;
                }
                $_TITLE = strtoupper($type);
                $popupDef = new tldOverlib($help[$_TITLE].$groupTarget, ["CAPTION" => $_TITLE, "WIDTH" => "500", "linkName" => $_TITLE]);
                $body .= $popupDef->fetch();
                // Add link to get listing
                $body .= " | <a href=\"/en/private/sales_service/service.php?m[0]=toc&m[1]=listing&m[2]=kpiByPeriod&m[3]=$type&m[4]={$m[2]}$link\">Get $_TITLE Listing by month</a>";
            }
            break;
        }

        // Get Date interval to fill empty KPI results from query
        $endingDate = new \DateTime('midnight first day of this month');
        $startingDate = new \DateTime('midnight first day of this month last year');
        $period = new \DatePeriod($startingDate, new \DateInterval('P1M'), $endingDate);
        $technicianIdsImploded = implode("','", $technicianIds);

        // Get data for Highcharts KPIs
        foreach ($KPI_TYPE as $type) {
            $rows = [];
            switch ($type) {
                case 'nto':
                    $graphTitle = 'NTO by technician';
                    $query = <<<EOF
SELECT DATE_FORMAT(dt, '%Y-%m') as xval, COUNT(*) yval, CONCAT(people.firstname, ' ', people.lastname) as zval
FROM toc
LEFT JOIN people ON toc.tecid=people.id
WHERE PERIOD_DIFF(DATE_FORMAT(NOW(), '%Y%m'), DATE_FORMAT(dt, '%Y%m')) BETWEEN 1 AND 12 
AND tecid in ('$technicianIdsImploded')
GROUP BY xval, zval
EOF;

                    $rows = tldUtils::getSqlToAssocArray($query);
                    break;
                case 'nts':
                    $graphTitle = 'NTS by technician';
                    $query = <<<EOF
SELECT DATE_FORMAT(dt_closed, '%Y-%m') as xval, COUNT(*) yval, CONCAT(people.firstname, ' ', people.lastname) as zval
FROM toc
LEFT JOIN people ON toc.tecid=people.id
WHERE PERIOD_DIFF(DATE_FORMAT(NOW(), '%Y%m'), DATE_FORMAT(dt_closed, '%Y%m')) BETWEEN 1 AND 12 
AND tecid in ('$technicianIdsImploded')
GROUP BY xval, zval
EOF;
                    $rows = tldUtils::getSqlToAssocArray($query);
                    break;
                case 'tir':
                    $graphTitle = 'TIR by technician';
                    $query = <<<EOF
SELECT DATE_FORMAT(dt_closed, '%Y-%m') as xval ,CONCAT(people.firstname, ' ', people.lastname) zval, ROUND(SUM(
    CASE
       WHEN toc_zones.zone = 'G' AND TIMESTAMPDIFF(HOUR, dt, dt_closed) <= 48 THEN 1
       WHEN toc_zones.zone = 'G' AND TIMESTAMPDIFF(HOUR, dt, dt_closed) > 48 THEN 0
       WHEN toc_zones.zone = 'B' AND TIMESTAMPDIFF(HOUR, dt, dt_closed) <= 72 THEN 1
       WHEN toc_zones.zone = 'B' AND TIMESTAMPDIFF(HOUR, dt, dt_closed) > 72 THEN 0
       WHEN toc_zones.zone = 'Y' AND TIMESTAMPDIFF(HOUR, dt, dt_closed) <= 120 THEN 1
       WHEN toc_zones.zone = 'Y' AND TIMESTAMPDIFF(HOUR, dt, dt_closed) > 120 THEN 0
       WHEN toc_zones.zone = 'R' AND TIMESTAMPDIFF(HOUR, dt, dt_closed) <= 168 THEN 1
       WHEN toc_zones.zone = 'R' AND TIMESTAMPDIFF(HOUR, dt, dt_closed) > 168 THEN 0
       END
           ) / COUNT(*) * 100) as yval
    FROM toc
    LEFT JOIN airport_codes AS apc ON apc.airport_code = toc.apc AND apc.type LIKE 'Airport'
    LEFT JOIN airport_codes as apc2 ON apc2.airport_code = toc.apc AND apc2.type LIKE 'Airport' AND apc2.city_name > apc.city_name
    LEFT JOIN countries ON countries.iso_code_2 = apc.ctry_code_2
    LEFT JOIN toc_zones ON toc_zones.parent_id = countries.id
    LEFT JOIN people ON people.id = toc.tecid
WHERE PERIOD_DIFF(DATE_FORMAT(NOW(), '%Y%m'), DATE_FORMAT(dt_closed, '%Y%m')) BETWEEN 1 AND 12
AND apc2.id IS NULL
AND tecid in ('$technicianIdsImploded')
GROUP BY zval, xval
EOF;
                    $rows = tldUtils::getSqlToAssocArray($query);
                    break;
                case 'tat':
                    $graphTitle = 'TAT by technician';
                    $query = <<<EOF
SELECT DATE_FORMAT(dt_closed, '%Y-%m') as xval ,CONCAT(people.firstname, ' ', people.lastname) zval,
ROUND((SELECT SUM(TIMESTAMPDIFF(DAY, dt, dt_closed)) - (SELECT fct_tld_mod_toc_getDaysSuspendedByID(toc.id))) / COUNT(*)) as yval
FROM toc
LEFT JOIN people ON people.id = toc.tecid
WHERE PERIOD_DIFF(DATE_FORMAT(NOW(), '%Y%m'), DATE_FORMAT(dt_closed, '%Y%m')) BETWEEN 1 AND 12
AND tecid in ('$technicianIdsImploded')
GROUP BY zval, xval
EOF;
                    $rows = tldUtils::getSqlToAssocArray($query);
                    break;
                case 'tol':
                    $graphTitle = 'TOL by technician';
                    foreach ($technicianIds as $id) {
                        $tecUser = new tldUser($id);
                        $legend = $tecUser->getFullname();
                        $query = <<<EOF
SELECT
    '$legend' AS zval,
    CONCAT(SUBSTRING(nam_period,1,4), '-', SUBSTRING(nam_period,5,2)) AS xval,
    ROUND(
            (SELECT
                 MAX(
                         TIMESTAMPDIFF(
                                 DAY,
                                 DATE_FORMAT(dt,'%Y-%m-%d'),
                                 LAST_DAY(CONCAT(SUBSTRING(p.nam_period,1,4), '-', SUBSTRING(p.nam_period,5,2), '-01'))
                             )
                     )
             FROM toc
             WHERE PERIOD_DIFF(DATE_FORMAT(dt, '%Y%m'), p.nam_period) <= 0
             AND (PERIOD_DIFF(DATE_FORMAT(dt_closed, '%Y%m'), p.nam_period) > 0 OR TO_DAYS(dt_closed) IS NULL)
             AND tecid=$id
            ),1) AS yval
FROM
    fin_periods AS p
WHERE
    PERIOD_DIFF(DATE_FORMAT(NOW(), '%Y%m'), p.nam_period) BETWEEN 1 AND 12
ORDER BY
    nam_period
EOF;
                        $rows = array_merge($rows, tldUtils::getSqlToAssocArray($query));
                    }
                    break;
            }

            // Generate KPIs
            $chart = $chartBuilderFactory
                ->getLineChartBuilder()
                ->setTitle($graphTitle)
                ->addYAxis(strtoupper($kpi), ['min' => 0]);

            foreach ($rows as $key => $row) {
                $chart->addPlot(
                    $row['zval'],
                    $row['xval'],
                    (int)$row['yval']
                );
            }

            $chart->reMapXValues(array_map([ChartBuilder::class, 'formatMonth'], iterator_to_array($period)), 0);

            $chart = json_encode($chart->buildConfig());

            $body .= <<<EOF
<br/><br/>
<div id="container-$type" style="width:100%; height:400px;"></div>
<script>
	$(document).ready(function() {
	  $('#container-$type').highcharts($chart);
});
</script>
EOF;
            $_TITLE = strtoupper($type);
            $popupDef = new tldOverlib($help[$_TITLE].$groupTarget, ["CAPTION" => $_TITLE, "WIDTH" => "500", "linkName" => $_TITLE]);
            $body .= $popupDef->fetch();
            // Add link to get listing
            $body .= " | <a href=\"/en/private/sales_service/service.php?m[0]=toc&m[1]=listing&m[2]=kpiByPeriod&m[3]=$type&m[4]={$m[2]}$link\">Get $_TITLE Listing by month</a>";
        }

        break;
    case 'toc_survey':
        $DEFAULT_TITLE .= "\TOC Survey KPI";
        // Listing
        $ssoList = tldLocation::getSalesOrgList("smartyOptionsIDLocation");
        // Form
        // Get form
        $form = new HTML_QuickForm('frmSfrStatsOrdered', 'post');
        $form->addElement('hidden', 'm[0]', 'kpi');
        $form->addElement('hidden', 'm[1]', 'toc_survey');
        $form->addElement('header', 'title', "TOC survey KPI");
        $form->addElement('select', 'ssoid', 'SSO', ["" => ""] + $ssoList);
        $form->addElement('header', 'title', "KPI period");
        $form->addElement('text', 'from', "Period from (yyyymm)", ['class' => 'datepicker', 'data-dateformat' => 'yymm']);
        $form->addElement('text', 'to', "Period to (yyyymm)", ['class' => 'datepicker', 'data-dateformat' => 'yymm']);
        $form->addRule('from', 'Required', 'required');
        $form->addRule('to', 'Required', 'required');
        $form->setDefaults([
            'from' => date('Y').'01',
            'to' => date('Y').'12',
        ]);
        $form->addElement('submit', 'btnSubmit', 'Submit');

        if (!$form->validate()) {
            $body = $form->toHTML();
            break;
        }

        $vars = tldUtils::cleanupFormInput($form->exportValues());
        $kpiLineTypes = [
            'average' => 'Average',
            'work' => 'Work execution',
            'responsiveness' => 'Responsiveness',
            'communication' => 'Communication',
            'attitude' => 'Attitude',
        ];
        foreach ($kpiLineTypes as $type => $typeName) {
            $body .= <<<EOF
<p><img src="/en/private/sales_service/service.php?m[0]=toc&m[1]=kpi&m[2]=survey&m[3]=bySSOID&ssoid={$vars['ssoid']}&type=$type&from={$vars['from']}&to={$vars['to']}" /></p>
EOF;
        }
        break;
    case 'toc_official':
        $montlyKpiFiles = tldModFile::byConstraints(['module' => 'TOC', 'description' => 'TOC Monthly KPIs']);
        $lastMontlyKpiFile = array_pop($montlyKpiFiles);
        $DEFAULT_MENU .= <<<EOF
    <br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
    <a href="/en/private/common/index.php?m[0]=files&m[1]=view&m[2]=out&id={$lastMontlyKpiFile['id']}">PDF version</a>
    &nbsp;|&nbsp;<a href="$php_self?m[0]=kpi&m[1]=toc_official&m[2]=xls">XLS version</a>
EOF;

        $form = new HTML_QuickForm('test', 'get');
        $form->addElement('hidden', 'm[0]', 'kpi');
        $form->addElement('hidden', 'm[1]', 'toc_official');
        $form->addElement('hidden', 'm[2]', $m[2] ?: '');
        $form->addElement('select', 'activity_type', 'Activity Type', ['ALL' => 'ALL', 'other' => 'Other'] + tldTOC::getActivityTypeList());
        $form->addRule('activity_type', 'Required', 'required');
        $form->addElement('submit', 'btnSubmit', 'Submit');
        if (!$form->validate()) {
            $body = $form->toHTML();
            break;
        }

        $vars = tldUtils::cleanupFormInput($form->exportValues());
        $activityType = $vars['activity_type'];
        $AND = "AND key2='$activityType'";
        if ($activityType === 'ALL') {
            $AND = "AND key2=''";
        }

        $KPI_TYPE = [
            'nto' => 'Number TOC Open',
            'nts' => 'Number TOC Solved',
            'tir' => 'TOC Immediate Ratio',
            'tat' => 'TOC Average Time to Solve',
            'tol' => 'TOC Oldest',
        ];
        $locations = tldTOC::getSSOList();
        // Get info to have last 12 months period
        $today = new DateTime(date('Y-m-d'));
        $today->sub(new DateInterval('P1M'));
        $end = $today->format('Ym');
        $today->sub(new DateInterval('P11M'));
        $start = $today->format('Ym');
        $periodList = tldModKPI::getPeriodList($start, $end);
        // Prepare xItems
        $xItems = [
            "sso" => "SSO",
            "kpi_desc" => "KPI",
            "kpi_code" => "",
            "avg" => "AVG",
            "target" => "Target",
        ];
        foreach ($periodList as $period) {
            $xItems[$period['period']] = $period['period'];
        }


        // Get KPI for each sso by kpi type by period
        $DATA = [];
        foreach ($locations as $buid => $location) {
            $temp = $xItems;
            $temp['sso'] = $location;
            $_NTO = [];
            $_NTS = [];
            // Get cumul from past
            $row = [];
            $start_period = $periodList[0]['period'];

            $start_period_sql = (DateTime::createFromFormat('Ym', $start_period))->modify('first day of this month')->format("Y-m-d");
            $sql = <<<EOF
SELECT (SELECT COUNT(*) FROM toc WHERE dt < '$start_period_sql' and ssoid = '$buid') -
       (SELECT COUNT(*) FROM toc WHERE dt_closed < '$start_period_sql' AND dt_closed != '0000-00-00 00:00:00' and ssoid = '$buid') AS total_cumul;

EOF;
            $row = tldUtils::getSqlRowToAssocArray($sql);
            $_CUMUL = $row['total_cumul'];
            foreach ($KPI_TYPE as $type => $desc) {
                $temp['kpi_desc'] = $desc;
                $temp['kpi_code'] = strtoupper($type);
                $temp['avg'] = 0;
                foreach ($periodList as $period) {
                    $a = "module='TOC' AND key1=$buid AND name='$type' AND period='{$period['period']}' $AND";
                    $rows = tldModKPI::byConstraints($a);
                    if (!empty($rows[0]['val'])) {
                        $temp[$period['period']] = $rows[0]['val'];
                        $temp['avg'] += $rows[0]['val'];
                    } else {
                        $temp[$period['period']] = null;
                    }
                    // Prepare cumul
                    if ($type === 'nto') {
                        $_NTO[$period['period']] = $rows[0]['val'];
                    } elseif ($type === 'nts') {
                        $_NTS[$period['period']] = $rows[0]['val'];
                    }
                }
                $temp['avg'] = round($temp['avg'] / count($periodList), 2);
                $temp['target'] = $help[$type."_target"];
                $DATA[] = $temp;

                // Check to calculate extra data
                if ($type === 'nts') {
                    // Backlog Number TOC
                    $temp['avg'] = 0;
                    foreach ($periodList as $period) {
                        $temp[$period['period']] = $_NTO[$period['period']] - $_NTS[$period['period']];
                        $temp['avg'] += $temp[$period['period']];
                    }
                    $temp['avg'] = round($temp['avg'] / count($periodList), 2);
                    $temp['kpi_desc'] = "Backlog #TOC (GAP)";
                    $temp['kpi_code'] = "";
                    $DATA[] = $temp;
                    // Backlog Number TOC Cumul
                    $temp['avg'] = 0;
                    $last_cumul = $_CUMUL;
                    $temp['kpi_desc'] = "Backlog #TOC (TOTAL)";
                    foreach ($periodList as $k => $period) {
                        $temp[$period['period']] = ($_NTO[$period['period']] - $_NTS[$period['period']]) + $last_cumul;
                        $temp['avg'] += $temp[$period['period']];
                        $last_cumul = $temp[$period['period']];
                    }
                    $temp['avg'] = round($temp['avg'] / count($periodList), 2);
                    $temp['kpi_code'] = "";
                    $DATA[] = $temp;
                }
            }
        }

        switch ($m[2]) {
            case 'xls':
                $report = new tldXLS(
                    $DATA,
                    [
                        "xItems" => $xItems,
                        "showTitles" => true,
                    ]
                );
                $report->out();
                exit;
                break;
            default:
                // Display KPI summary
                $report = new tldReportMultiLevel(
                    $DATA,
                    ["sso"],
                    $xItems,
                    [
                        "title" => sprintf("Official Monthly TOC %s", sprintf('- Activity Type: %s', $activityType)),
                    ]
                );
                $body .= $report->fetch();
                break;
        }
        break;
    case 'DaysOfSalesOut':
        $erpList = tldLocation::getFactoryList("smartyOptionsIDLocation");
        $ssoList = tldLocation::getSalesOrgList("smartyOptionsIDLocation");
        $form = new HTML_QuickForm('frmDaysOfSalesOut', 'post');
        $form->addElement('hidden', 'm[0]', 'kpi');
        $form->addElement('hidden', 'm[1]', $m[1]);
        $form->addElement('header', 'title', "Days of Sales Out by");
        $form->addElement('select', 'sso', 'SSO', ["" => "", "ALL" => "ALL SSO"] + $ssoList);
        //$form->addElement(	'select', 'erp', 'or ERP', array(""=>"","ALL"=>"ALL ERP")+$erpList);
        $form->addElement('submit', 'btnSubmit', 'Submit');

        if ($form->validate()) {
            $vars = tldUtils::cleanupFormInput($form->exportValues());
            if (empty($vars['sso'])) {
                $DEFAULT_ERROR[] = "ERROR: Please select a SSO!";
                $body = $form->toHTML();
                break;
            }
            if (!empty($vars['sso'])) {
                $endurl = "&sso=".$vars['sso'];
            }
            // Display metrics
            $body .= <<<EOF
			<br><br><img src="/en/private/sales_service/kpi/graphs.php?m[0]=past12&m[1]={$m[1]}$endurl"><br/>
EOF;

            switch ($m[1]) {
                case "sfrStatsOrdered":
                    $_TITLE = "Ordered SFR";
                    break;
                case "sfrStatsLost":
                    $_TITLE = "Lost SFR";
                    break;
                case "DaysOfSalesOut":
                    $_TITLE = "Days of Sales Out for Previous 12 month";
                    break;
            }
            $popupDef = new tldOverlib($help[$_TITLE].$groupTarget, ["CAPTION" => $_TITLE, "WIDTH" => "500", "linkName" => $_TITLE]);
            $body .= $popupDef->fetch();
        } else {
            $body = $form->toHTML();
        }
        break;
    case 'DaysOfSalesOutFG':
        $erpList = tldLocation::getFactoryList("smartyOptionsIDLocation");
        $ssoList = tldLocation::getSalesOrgList("smartyOptionsIDLocation");
        $form = new HTML_QuickForm('frmDaysOfSalesOutFG', 'post');
        $form->addElement('hidden', 'm[0]', 'kpi');
        $form->addElement('hidden', 'm[1]', $m[1]);
        $form->addElement('header', 'title', "Days of Sales Out with Finish Goods by");
        $form->addElement('select', 'sso', 'SSO', ["" => "", "ALL" => "ALL SSO"] + $ssoList);
        //$form->addElement(	'select', 'erp', 'or ERP', array(""=>"","ALL"=>"ALL ERP")+$erpList);
        $form->addElement('submit', 'btnSubmit', 'Submit');

        if ($form->validate()) {
            $vars = tldUtils::cleanupFormInput($form->exportValues());
            if (empty($vars['sso'])) {
                $DEFAULT_ERROR[] = "ERROR: Please select a SSO!";
                $body = $form->toHTML();
                break;
            }
            if (!empty($vars['sso'])) {
                $endurl = "&sso=".$vars['sso'];
            }
            // Display metrics
            $body .= <<<EOF
			<br><br><img src="/en/private/sales_service/kpi/graphs.php?m[0]=past12&m[1]={$m[1]}$endurl"><br/>
EOF;

            $_TITLE = "Days of Sales Out with Finished Goods for Previous 12 month";

            $popupDef = new tldOverlib($help[$_TITLE].$groupTarget, ["CAPTION" => $_TITLE, "WIDTH" => "500", "linkName" => $_TITLE]);
            $body .= $popupDef->fetch();
        } else {
            $body = $form->toHTML();
        }
        break;
    case 'help':
        $DEFAULT_TITLE .= "\Help";
        foreach ($help as $helpTitle => $helpText) {
            $helpBody .= "<h3>$helpTitle</h3><p>$helpText</p>";
        }
        $body = $helpBody;
        break;
    case 'dms' :
        switch ($m[2]) {
            case 'activeSalesMaterial':
                $DEFAULT_TITLE .= "\DMS";

                $query = <<<EOF
SELECT
    dms.status as dms_status,
    COUNT(DISTINCT dms.id) AS dms_count
FROM dms
WHERE
    dms.type_id = 9 and dms.status != 'ARCHIVE'
GROUP BY 
    dms_status
EOF;

                $countAllDmsByType = tldUtils::getSqlToAssocArray($query);

                $totalDmsAllType = array_sum(array_column($countAllDmsByType, 'dms_count'));

                foreach ($countAllDmsByType as $key => $countDmsByType){
                    $countAllDmsByType[$key]['dms_percent'] = 'DMS (%)';
                    $countAllDmsByType[$key]['dms'] = 'DMS';
                    $countAllDmsByType[$key]['percent_count'] = $countDmsByType['dms_count'] * 100 / $totalDmsAllType;
                }

                $report = new tldMatrix(
                    $countAllDmsByType,
                    'dms_status', 'dms_percent', 'percent_count',
                    "",
                    '% of Sales material DMS / (All - Archive)',
                    [
                        array_column($countAllDmsByType, 'dms_status'),
                        'doNotShowXTotals' => true
                    ],
                );

                $report2 = new tldMatrix(
                    $countAllDmsByType,
                    'dms_status', 'dms', 'dms_count',
                    "",
                    'Nbr of Sales material DMS / (All - Archive)',

                    [
                        array_column($countAllDmsByType, 'dms_status'),
                        'doNotShowXTotals' => true
                    ],
                );
                $body .= $report->fetch();
                $body .= $report2->fetch();

                $client = $kernel->getContainer()->get(Client::class);
                $factoryId = TldDatabase::escape($location);
                $factoryName = (new tldLocation($factoryId))->getBuName();
                try {
                    $reports = $client->findBy('report_snapshots', [
                        'resource' => '/dms',
                        'x' => 'owner.businessUnit.name',
                        'y' => 'status',
                        'createdAt' => ['after' => (new DateTime('12 month ago'))->format('y-m-d')]
                    ]);
                } catch (ClientException $e) {
                    $reports = [];
                }

                $rows = [];
                foreach ($reports as $key => $report) {
                    if (null !== ($report['options']['type'] ?? null) && $report['options']['type'] !== 'Sales Material') {
                        unset($reports[$key]);
                        continue;
                    }
                    $results[] = [
                        'xval' => substr($report['createdAt'], 0, 7),
                        'yval' => 0 !== (int)$report['rows'][$factoryName]['ACTIVE']['value'] && 0 != (int)$report['xTotals'][$factoryName] ? round(($report['rows'][$factoryName]['ACTIVE']['value']/$report['xTotals'][$factoryName])*100) : 0,
                        'zval' => $factoryName
                    ];
                }

                $chart = $chartBuilderFactory
                    ->getLineChartBuilder()
                    ->addYAxis('% ', ['min' => 0, 'max' => 100])
                    ->setTitle('% Active DMS Sales Material for '.$factoryName)
                ;

                foreach ($results as $key => $row) {
                    $chart->addPlot(
                        $row['zval'],
                        $row['xval'],
                        (int)$row['yval']
                    );
                }

                $chart = json_encode($chart->buildConfig());

                $body .= <<<EOF
<br/><br/>
<div id="container" style="width:100%; height:400px;"></div>
<script>
	$(document).ready(function() {
	  $('#container').highcharts($chart);
});
</script>
EOF;

                $query = <<<EOF
SELECT
    dms.status as dms_status,
    COUNT(DISTINCT dms.id) AS dms_count,
    locations.location AS owner_bu_name
FROM dms
    LEFT JOIN people ON dms.owner_id=people.id
    LEFT JOIN locations ON people.bu_id=locations.id
WHERE
    dms.type_id = 9 and dms.status != 'ARCHIVE'
GROUP BY 
    dms_status, owner_bu_name
EOF;
                $countAllDmsByTypeByBU = tldUtils::getSqlToAssocArray($query);

                $report = new tldMatrix(
                    $countAllDmsByTypeByBU,
                    'dms_status', 'owner_bu_name', 'dms_count',
                    "/en/private/manufacturing/qa/dev.php?m[0]=reports&m[1]=listing&m[2]=dmsStatusByBUForSalesMaterial",
                    'DMS quantity of Type sales material, per factory',
                    [
                        array_column($countAllDmsByTypeByBU, 'owner_bu_name'),
                    ],
                );

                $body .= $report->fetch();

                break 2;
        }
    case 'sol':
        switch ($m[2]) {
            case 'XLS':
                if (isset($_SESSION['data_report'])) {
                    try {
                        $data = $_SESSION['data_report'];
                        $objPHPExcel = new PHPExcel();
                        $objPHPExcel->setActiveSheetIndex(0);
                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(0, 1, "SOL");
                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(1, 1, "Year");
                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(2, 1, "Month");
                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(3, 1, "Number of days");

                        $rowNumber = 2;
                        foreach ($data as $month => $val) {
                            $val["sol"] = str_replace(' ', '', $val["sol"]);
                            $val["sol"] = explode("</br>", $val["sol"]);
                            foreach ($val["sol"] as $key => $row) {
                                $row = str_replace(['days', '<b>', '</b>'], '', $row);
                                $row = explode(":", $row);
                                if (!empty($row[0])) {
                                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(0, $rowNumber, $row[0]);
                                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(1, $rowNumber, $val["year"]);
                                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(2, $rowNumber, $month);
                                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(3, $rowNumber, $row[1]);
                                    $rowNumber++;
                                }
                            }
                        }
                        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
                        header('Content-Disposition: attachment;filename="KPI_SOL-process-time.xlsx"');
                        header('Cache-Control: max-age=0');
                        $objWriter = new PHPExcel_Writer_Excel2007($objPHPExcel);
                        $objWriter->save('php://output');
                    } catch (Exception $e) {
                        $DEFAULT_ERROR[] = "ERROR: ".$e->getMessage();
                    }
                } else {
                    $DEFAULT_ERROR[] = "ERROR: No report loaded.";
                }
                exit;
                break;
            case 'XLS-modified':
                if (isset($_SESSION['modified_sol_report'])) {
                    $data = $_SESSION['modified_sol_report'];
                    try {
                        $objPHPExcel = new PHPExcel();
                        $objPHPExcel->setActiveSheetIndex(0);
                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(0, 1, "SOL");
                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(1, 1, "Number of modification");
                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(2, 1, "Year");
                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(3, 1, "Month");

                        $rowNumber = 2;
                        foreach ($data as $val) {
                            $year = substr($val['date'], 0, 4);
                            $month = substr($val['date'], 5, 2);

                            $sols = $val['sols'];
                            foreach ($sols as $sol) {
                                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(0, $rowNumber, $sol['id']);
                                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(1, $rowNumber, $sol['num']);
                                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(2, $rowNumber, $year);
                                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(3, $rowNumber, $month);
                                $rowNumber++;
                            }
                        }
                        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
                        header('Content-Disposition: attachment;filename="KPI_SOL-modified.xlsx"');
                        header('Cache-Control: max-age=0');
                        $objWriter = new PHPExcel_Writer_Excel2007($objPHPExcel);
                        $objWriter->save('php://output');
                    } catch (Exception $e) {
                        $DEFAULT_ERROR[] = "ERROR: ".$e->getMessage();
                    }
                } else {
                    $DEFAULT_ERROR[] = "ERROR: No report loaded.";
                }
                exit;
                break;
        }

        $ssoList = tldLocation::getSalesOrgList("smartyOptionsIDLocation");
        $factoryList = tldLocation::getFactoryList("smartyOptionsIDLocation");
        $kpiToolForm = new HTML_QuickForm('kpiToolfrm');
        $kpiToolForm->addElement('hidden', 'm[0]', 'kpi');
        $kpiToolForm->addElement('hidden', 'm[1]', 'sol');
        $kpiToolForm->addElement('header', 'title', "Graph configuration");
        $groupForm[] = $kpiToolForm->createElement('select', 'type_graph', 'TYPE_GRAPH', ["sso" => "SSO", "factory" => "Factory"]);
        $groupForm[] = $kpiToolForm->createElement('select', 'graph_sso', 'GRAPH_SSO', $ssoList);
        $groupForm[] = $kpiToolForm->createElement('select', 'graph_factory', 'GRAPH_FACTORY', $factoryList);
        $groupForm[] = $kpiToolForm->createElement(
            'text',
            'dt_from',
            'Start',
            ['class' => 'datepicker', 'data-dateformat' => 'yy-mm', 'size' => 7]
        );
        $groupForm[] = $kpiToolForm->createElement(
            'text',
            'dt_to',
            'End',
            ['class' => 'datepicker', 'data-dateformat' => 'yy-mm', 'size' => 7]
        );
        $groupForm[] = $kpiToolForm->createElement('submit', 'btnSubmit', 'Submit');
        $kpiToolForm->addGroup($groupForm);
        $defaults = [
            'dt_from' => (new DateTime())->modify("-11 months")->format("Y-m"),
            'dt_to' => date('Y-m'),
        ];
        $kpiToolForm->setDefaults($defaults);
        $body = $kpiToolForm->toHtml();
        $js = <<<JS
$(document).ready(function() {
    function changeSelectGraph() {
        $("select[name^='graph_']").hide();
        $("select[name='graph_" + $("select[name='type_graph']").val() + "']").show();
    }
    $("select[name='type_graph']").change(changeSelectGraph);
    changeSelectGraph();
});
JS;
        $smarty->assign("html_head", '<script type="text/javascript">'.$js."</script>");
        if ($kpiToolForm->validate() || (isset($_GET['type_graph']) && isset($_GET['location']))) {
            $vars = $kpiToolForm->exportValues();
            if (isset($_GET['type_graph']) && isset($_GET['location'])) {
                $vars['type_graph'] = $_GET['type_graph'];
                $vars['graph_factory'] = $_GET['location'];
            }
            if (empty($vars["dt_from"]) || empty($vars["dt_to"])) {
                $DEFAULT_ERROR[] = "ERROR: You need a start period and an end period";
                return;
            }
            // Check start & end date
            $start = new DateTime($vars["dt_from"]);
            $end = new DateTime($vars["dt_to"]);

            if ($end < $start) {
                $DEFAULT_ERROR[] = "ERROR: Start period can not be greater than the end period";
            }
            // Check period <= 1 year
            $interval = $start->diff($end);
            $nbDays = $interval->format('%a');
            if ($nbDays > 365) {
                $DEFAULT_ERROR[] = "ERROR: KPI period should not be bigger than a year (Period selected is {$interval->format('%R%a')} days)";
            }

            // if there is any error stop the script

            if (count($DEFAULT_ERROR ?? [])) {
                return;
            }
            include("kpi.graph.sol.inc.php");
            break;
        }
        break;
    default:
        $body = $smarty->fetch("$PATH/kpi.homepage.tpl");
        break;
}
