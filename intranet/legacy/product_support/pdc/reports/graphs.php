<?php
include_once 'common.inc.php';
include_once 'product_support.inc.php';
include_once 'Image/Graph.php';

$COLOURS = [
    'white', 'red', 'aqua', 'blue', 'lime', 'fuchsia', 'gray', 'silver', 'yellow',
    'green', 'navy', 'olive', 'purple', 'teal', 'maroon', 'black',
];

// array definition of the KPI Targets
$KPITargets = [
    'GPTarget' => [
        'targetLineColor' => 'blue',
        'historyAllFactories' => '',
        'historyByFactory' => '',
        'historyByFactoryByWeek' => '',
        'openedPerMonth' => '',
    ],
];

switch ($m[0]) {
    case 'historyAllFactories':
        $Graph =& Image_Graph::factory('graph', [980, 500]);
        // add font
        $Font =& $Graph->addNew('ttf_font', 'DejaVuLGCSans-Bold.ttf');
        // set the font size to 11 pixels
        $Font->setSize(10);
        $Graph->setFont($Font);
        $Plotarea = Image_Graph::factory('plotarea');
        $Legend = Image_Graph::factory('legend');

        $Graph->add(
            Image_Graph::vertical(
                Image_Graph::factory('title', ['PDC Fweight for ALL Factories for previous 12 MONTHS', 12]),
                Image_Graph::horizontal(
                    $Plotarea,
                    $Legend,
                    85
                ),
                10
            )
        );

        // create a grid and assign it to the secondary Y axis
        $GridY2 =& $Plotarea->addNew('line_grid', IMAGE_GRAPH_AXIS_Y);

        $Legend->setPlotarea($Plotarea);
        $Legend->setShowMarker = true;
//	$erps = array("400"=>"Windsor","420"=>"Sherbrooke","500"=>"Montlouis","520"=>"St Lin","640"=>"Shanghai","620"=>"Taiwan");
        $erps = tldLocation::getFactoryList('smartyOptions');
        $FillArray =& Image_Graph::factory('Image_Graph_Fill_Array');
        //if specific erp is not set then do all erps
        $i = 0;
        foreach ($erps as $erp => $name) {
            $Dataset =& Image_Graph::factory('dataset');
            $Dataset->setName($name);
            $rows = tldPDC::getDemeritHistory('factory', $erp);
            foreach ($rows as $row) {
                $Dataset->addPoint($row['month_name'], $row['fweight'], $erp);
            }
            $i++;
            $FillArray->addColor($COLOURS[$i], $erp);
            $datasets[$erp] = $Dataset;
        }
        $Plot =& $Plotarea->addNew('bar', [$datasets, 'stacked']);
        $Plot->setFillStyle($FillArray);
        $Plot->setTitle('PDC Fweight');

        // setup a data processor to process numbers and convert them to english format (eg: 1,000,000)
        $DataPreprocessor =& Image_Graph::factory('Image_Graph_DataPreprocessor_Function', '_largeNumberConvert');

        // format the labels of the Y axis in the Engish number format (eg: 1,000,000)
        $AxisY =& $Plotarea->getAxis(IMAGE_GRAPH_AXIS_Y);
        $AxisY->setDataPreprocessor($DataPreprocessor);

        // add Group target blue line
        $groupTarget =& $Plotarea->addNew('Image_Graph_Axis_Marker_Line', null, IMAGE_GRAPH_AXIS_Y);
        $groupTarget->setLineColor($KPITargets['GPTarget']['targetLineColor']);
        $groupTarget->setValue($KPITargets['GPTarget'][$m[0]]);

        // add marker (value) in the bar
        $Marker =& Image_Graph::factory('value_marker', IMAGE_GRAPH_VALUE_Y);
        // format the labels of the marker in the Engish number format (eg: 1,000,000)
        $Marker->setDataPreprocessor($DataPreprocessor);

        // shift markers 10 pix down
        $marker_pointing =& $Graph->addNew('Image_Graph_Marker_Pointing', [0, 10, & $Marker]);
        $marker_pointing->setLineColor('000000@0.0');
        $Plot->setMarker($marker_pointing);

        break;
    case 'historyByFactory':
        $erps = array_keys(tldLocation::getFactoryList('smartyOptions'));
        if (!in_array($erp, $erps)) {
            exit;
        }
        $Graph =& Image_Graph::factory('graph', [980, 500]);

        // add font
        $Font =& $Graph->addNew('ttf_font', 'DejaVuLGCSans-Bold.ttf');
        // set the font size to 11 pixels
        $Font->setSize(10);
        $Graph->setFont($Font);
        $Plotarea = Image_Graph::factory('plotarea');
        $Legend = Image_Graph::factory('legend');

        $Graph->add(
            Image_Graph::vertical(
                Image_Graph::factory('title', ['Average Monthly Fweight over Time', 12]),
                Image_Graph::horizontal(
                    $Plotarea,
                    $Legend,
                    85
                ),
                10
            )
        );
        $Legend->setPlotarea($Plotarea);
        $Dataset =& Image_Graph::factory('dataset');

        // create a grid and assign it to the secondary Y axis
        $GridY2 =& $Plotarea->addNew('line_grid', IMAGE_GRAPH_AXIS_Y);

        $rows = tldPDC::getDemeritHistory('factory', $erp);

        foreach ($rows as $row) {
            $Dataset->addPoint($row['month_name'], $row['fweight']);
        }

        $Plot =& $Plotarea->addNew('bar', [&$Dataset]);
        $Plot->setFillColor('#FF000');
        $Plot->setTitle('AVG Fweight');

        // setup a data processor to process numbers and convert them to english format (eg: 1,000,000)
        $DataPreprocessor =& Image_Graph::factory('Image_Graph_DataPreprocessor_Function', '_largeNumberConvert');

        // format the labels of the Y axis in the Engish number format (eg: 1,000,000)
        $AxisY =& $Plotarea->getAxis(IMAGE_GRAPH_AXIS_Y);
        $AxisY->setDataPreprocessor($DataPreprocessor);

        // add Group target blue line
        $groupTarget =& $Plotarea->addNew('Image_Graph_Axis_Marker_Line', null, IMAGE_GRAPH_AXIS_Y);
        $groupTarget->setLineColor($KPITargets['GPTarget']['targetLineColor']);
        $groupTarget->setValue($KPITargets['GPTarget'][$m[0]]);

        // add marker (value) in the bar
        $Marker =& $Plot->addNew('Image_Graph_Marker_Value', IMAGE_GRAPH_VALUE_Y);
        // format the labels of the marker in the Engish number format (eg: 1,000,000)
        $Marker->setDataPreprocessor($DataPreprocessor);

        // shift markers 10 pix down
        $marker_pointing =& $Graph->addNew('Image_Graph_Marker_Pointing', [0, 10, & $Marker]);
        $marker_pointing->setLineColor('000000@0.0');
        $Plot->setMarker($marker_pointing);

        break;
    case 'historyByFactoryByWeek':
        $erps = array_keys(tldLocation::getFactoryList('smartyOptions'));
        if (!in_array($erp, $erps)) {
            exit;
        }
        $Graph =& Image_Graph::factory('graph', [1000, 300]);

        // add font
        $Font =& $Graph->addNew('ttf_font', 'DejaVuLGCSans-Bold.ttf');
        // set the font size to 11 pixels
        $Font->setSize(10);
        $Graph->setFont($Font);
        $Plotarea = Image_Graph::factory('plotarea');
        $Legend = Image_Graph::factory('legend');

        $Graph->add(
            Image_Graph::vertical(
                Image_Graph::factory('title', ['Average Weekly Fweight over Time', 12]),
                Image_Graph::horizontal(
                    $Plotarea,
                    $Legend,
                    85
                ),
                10
            )
        );
        $Legend->setPlotarea($Plotarea);
        $Dataset =& Image_Graph::factory('dataset');

        // create a grid and assign it to the secondary Y axis
        $GridY2 =& $Plotarea->addNew('line_grid', IMAGE_GRAPH_AXIS_Y);

        $rows = tldPDC::getDemeritHistory('factoryByWeek', $erp);

        foreach ($rows as $row) {
            $Dataset->addPoint($row['week_name'], $row['fweight']);
        }
        $Plot =& $Plotarea->addNew('line', [&$Dataset]);
        $Plot->setFillColor('#FF000');
        $Plot->setTitle('AVG Week FW');

        // setup a data processor to process numbers and convert them to english format (eg: 1,000,000)
        $DataPreprocessor =& Image_Graph::factory('Image_Graph_DataPreprocessor_Function', '_largeNumberConvert');

        // format the labels of the Y axis in the Engish number format (eg: 1,000,000)
        $AxisY =& $Plotarea->getAxis(IMAGE_GRAPH_AXIS_Y);
        $AxisY->setDataPreprocessor($DataPreprocessor);

        break;
    case 'openedPerMonth':
        $Graph =& Image_Graph::factory('graph', [980, 500]);

        // add font
        $Font =& $Graph->addNew('ttf_font', 'DejaVuLGCSans-Bold.ttf');
        // set the font size to 11 pixels
        $Font->setSize(10);
        $Graph->setFont($Font);
        $Plotarea = Image_Graph::factory('plotarea');
        $Legend = Image_Graph::factory('legend');

        $Graph->add(
            Image_Graph::vertical(
                Image_Graph::factory('title', ['Count of PDC opened by Month for last 12 Months', 12]),
                Image_Graph::horizontal(
                    $Plotarea,
                    $Legend,
                    85
                ),
                10
            )
        );
        $Legend->setPlotarea($Plotarea);
        $Legend->setShowMarker = true;
        // create a grid and assign it to the secondary Y axis
        $GridY2 =& $Plotarea->addNew('line_grid', IMAGE_GRAPH_AXIS_Y);

        $WHERE = '';
        if ($buid) {
            $WHERE = " AND t1.factory=$buid";
        }
        $query = <<<EOF
		SELECT t2.location AS factory, date_format(date, '%Y-%m') AS dispMonth,
		  COUNT(*) AS opened
		FROM demerit AS t1 LEFT JOIN locations AS t2 ON t1.factory=t2.id
		WHERE PERIOD_DIFF(DATE_FORMAT(NOW(), '%Y%m'), DATE_FORMAT(t1.date, '%Y%m')) < 12
		$WHERE
		GROUP BY factory, dispMonth
		ORDER BY dispMonth ASC
EOF;
        $rows = tldUtils::getSqlToAssocArray($query);
        foreach ($rows as $row) {
            $erps[$row['factory']][] = $row;
        }
        $FillArray =& Image_Graph::factory('Image_Graph_Fill_Array');
        //if specific erp is not set then do all erps
        $i = 0;
        foreach ($erps as $factory => $rows) {
            $datasets[$factory] =& Image_Graph::factory('dataset');
            $datasets[$factory]->setName($factory);
            foreach ($rows as $row) {
                $datasets[$factory]->addPoint($row['dispMonth'], $row['opened'], $factory);
            }
            $i++;
            $FillArray->addColor($COLOURS[$i], $factory);
        }
        $Plot =& $Plotarea->addNew('bar', [$datasets, 'stacked']);
        $Plot->setFillStyle($FillArray);
        $Plot->setTitle('PDC opened Month');

        // add Group target blue line
        $groupTarget =& $Plotarea->addNew('Image_Graph_Axis_Marker_Line', null, IMAGE_GRAPH_AXIS_Y);
        $groupTarget->setLineColor($KPITargets['GPTarget']['targetLineColor']);
        $groupTarget->setValue($KPITargets['GPTarget'][$m[0]]);

        // add marker (value) in the bar
        $Marker =& Image_Graph::factory('value_marker', IMAGE_GRAPH_VALUE_Y);
        // shift markers 10 pix down
        $marker_pointing =& $Graph->addNew('Image_Graph_Marker_Pointing', [0, 10, & $Marker]);
        $marker_pointing->setLineColor('000000@0.0');
        $Plot->setMarker($marker_pointing);

        break;
}

$Graph->done();

// function used by the $DataPreprocessor to convert number in english format (eg: 1,000,000)
function _largeNumberConvert($num)
{
    return number_format($num);
}
