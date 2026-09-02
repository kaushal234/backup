<?php
include_once("common.inc.php");
include_once("erp.inc.php");
include_once('product_support.inc.php');
include_once('Image/Graph.php');
include_once("forms_and_reports.inc.php");

// include the KPI common file where are the KPI descriptions, values  ..
include_once("kpi.common.inc.php");

session_start();
if (!isset($_SESSION['sess'])) {
    $_SESSION['sess'] = null;
}
$sess =& $_SESSION['sess'];

$COLOURS = [
    "white", "red", "aqua", "blue", "lime", "fuchsia", "teal", "navy", "yellow",
    "green", "olive", "purple", "gray", "maroon", "silver",
];
$factoryList = tldLocation::byConstraints("erp<>'' AND factory='Y' AND hidden<>1 and disable<>1");
$FACTORIES = array_column($factoryList, 'location', 'erp');

if ($_REQUEST['buid']) {
    $BUID = TldDatabase::escape($_REQUEST['buid']);
    if ($BUID !== "ALL" && $BUID !== "ame" && $BUID !== "asi" && $BUID !== "eur") {
        $loc = new tldLocation($BUID);
        $location = $loc->getHeader();
        $erp = $location['erp'];
    }
}

// Translations
switch ($erp) {
    case 500:
    case 510:
    case 520:
        $translate = new tldTranslate('FR', 'ISO-8859-1');
        break;
    default:
        $translate = new tldTranslate('EN', 'ISO-8859-1');
        break;
}

if ('history' === ($m[0] ?? null)) {
    include("history.inc.php");
} else {
    include("past12.inc.php");
}

if (null !== $Graph) {
    if ($json) {
        if (is_array($Graph) && !is_object($Graph)) {
            $array = ['options' => $Graph['options'], 'rows' => []];
            foreach ((array)$Graph['rows'] as $row) {
                $array['rows'][$row['xval']][$row['zval']][] = $row['yval'];
            }
            echo json_encode($array, JSON_FORCE_OBJECT);
        }
        exit;
    }

    $Graph->done();
}

function _doBarGraph($rows, $options, $wd = 85)
{
    global $COLOURS;
    global $BUID;
    global $json;

    if ($json) {
        return [
            'rows' => $rows,
            'options' => $options,
        ];
    }

    $COLOURS = [
        "white", "gray", "aqua", "blue", "lime", "fuchsia", "teal",
        "olive", "purple", "maroon", "silver", "navy", "yellow",
    ];

    $Graph =& Image_Graph::factory('graph', [1300, 500]);

    // add font
    $Font =& $Graph->addNew('ttf_font', 'DejaVuLGCSans-Bold.ttf');
    // set the font size to 11 pixels
    $Font->setSize(8);
    $Graph->setFont($Font);

    $Plotarea = Image_Graph::factory('plotarea');
    $Legend = Image_Graph::factory('legend');
    $Graph->add(
        Image_Graph::vertical(
            Image_Graph::factory(
                'title',
                [
                    $options["graphTitle"],
                    10,
                ]
            ),
            Image_Graph::horizontal(
                $Plotarea,
                $Legend,
                $wd
            ),
            10
        )
    );

    $Plotarea->setBackgroundColor('white');
    $Plotarea->setBorderColor('white');

    // link the legend with the plotares
    $Legend->setPlotarea($Plotarea);
    $Legend->setShowMarker = true;

    // create a grid and assign it to the secondary Y axis
    $GridY2 =& $Plotarea->addNew('line_grid', IMAGE_GRAPH_AXIS_Y);

    if (empty($rows)) {
        $rows = [
            [
                "zval" => "NA",
                "xval" => 0,
                "yval" => 0,
            ],
        ];
    }

    foreach ($rows as $row) {
        $erps[$row['zval']][] = $row;
    }

    $FillArray =& Image_Graph::factory('Image_Graph_Fill_Array');
    //if specific erp is not set then do all erps
    $i = 1;
    foreach ($erps as $zval => $rows) {
        $datasets[$zval] =& Image_Graph::factory('dataset');
        if ($BUID !== "ALL" && $options["GPTargetVal"] !== "NA") {
            $GPTarget =& Image_Graph::factory('dataset');
        }
        $name = $zval;
        if (empty($zval)) {
            $name = "Unknow";
        }
        $datasets[$zval]->setName($name);
        foreach ($rows as $row) {
            $datasets[$zval]->addPoint($row["xval"], $row["yval"], $zval);
            if ($BUID !== "ALL" && $options["GPTargetVal"] !== "NA") {
                $GPTarget->addPoint($row["xval"], $options["GPTargetVal"]);
            }
        }
        $FillArray->addColor($COLOURS[$i], $zval);
        $i++;
    }

    $Plot =& $Plotarea->addNew($options["graphStyle"], ($options["showStack"]) ? [$datasets] : [$datasets, "stacked"]);
    $Plot->setFillStyle($FillArray);
    //change the angle of the X axis legend to vertical if option is enabled

    if (isset($options["axisXAngle"])) {
        $AxisX =& $Plotarea->getAxis(IMAGE_GRAPH_AXIS_X);
        $AxisX->setFontAngle($options["axisXAngle"]);
        $AxisX->setLabelOption('offset', 35);
    }

    if ($BUID !== "ALL" && $options["GPTargetVal"] !== "NA") {
        $Target =& $Plotarea->addNew(
            "Image_Graph_Plot_Fit_Line",
            [&$GPTarget]
        );
        $Target->setLineColor('blue');
        $Target->setTitle("Group Target");
    }

    if ($options["showMarkers"] == "true") {
        // create a Y data value marker
        $Marker =& $Plot->addNew('Image_Graph_Marker_Value', IMAGE_GRAPH_VALUE_Y);
        // and use the marker on the 1st plot
        $Plot->setMarker($Marker);
        $Plot->setDataSelector(Image_Graph::factory('Image_Graph_DataSelector_NoZeros'));
    }
    return $Graph;
}

function _doLineGraph($rows, $options)
{
    global $COLOURS;
    global $BUID;
    global $json;

    if ($json) {
        return [
            'rows' => $rows,
            'options' => $options,
        ];
    }

    $Graph =& Image_Graph::factory(
        'graph',
        [980, 500]
    );

    // add font
    $Font =& $Graph->addNew('ttf_font', 'DejaVuLGCSans-Bold.ttf');
    // set the font size to 11 pixels
    $Font->setSize(10);
    $Graph->setFont($Font);
    $Plotarea = Image_Graph::factory('plotarea');
    $Legend = Image_Graph::factory('legend');

    $Graph->add(
        Image_Graph::vertical(
            Image_Graph::factory(
                'title',
                [
                    $options["graphTitle"],
                    12,
                ]
            ),
            Image_Graph::horizontal(
                $Plotarea,
                $Legend,
                85
            ),
            10
        )
    );

    $Plotarea->setBackgroundColor('white');
    $Plotarea->setBorderColor('white');

    // link the legend with the plotares
    $Legend->setPlotarea($Plotarea);
    $Legend->setShowMarker = true;

    // create a grid and assign it to the secondary Y axis
    $GridY2 =& $Plotarea->addNew('line_grid', IMAGE_GRAPH_AXIS_Y);

    if (empty($rows)) {
        $rows = [
            [
                "zval" => "NA",
                "xval" => 0,
                "yval" => 0,
            ],
        ];
    }

    foreach ($rows as $row) {
        $erps[$row['zval']][] = $row;
    }

    $FillArray =& Image_Graph::factory('Image_Graph_Fill_Array');
    //if specific erp is not set then do all erps
    $i = 1;
    foreach ($erps as $zval => $rows) {
        $datasets[$zval] =& Image_Graph::factory('dataset');
        if ($BUID !== "ALL" && $options["GPTargetVal"] !== "NA") {
            $GPTarget =& Image_Graph::factory('dataset');
        }
        $datasets[$zval]->setName($zval);
        foreach ($rows as $row) {
            $datasets[$zval]->addPoint($row["xval"], $row["yval"], $zval);
            if ($BUID !== "ALL" && $options["GPTargetVal"] !== "NA") {
                $GPTarget->addPoint($row["xval"], $options["GPTargetVal"]);
            }
        }
        $i++;
        $FillArray->addColor($COLOURS[$i], $zval);
    }

    $j = 1;
    foreach ($datasets as $z) {
        ${"Plot{$j}"} =& $Plotarea->addNew($options["graphStyle"], [$z]);
        $LineStyle =& Image_Graph::factory('Image_Graph_Line_Solid', [$COLOURS[$j]]);
        $LineStyle->setThickness(4);
        ${"Plot{$j}"}->setLineStyle($LineStyle);

        $j++;
    }

    //change the angle of the X axis legend to vertical if option is enabled
    if (isset($options["axisXAngle"])) {
        $AxisX =& $Plotarea->getAxis(IMAGE_GRAPH_AXIS_X);
        $AxisX->setFontAngle($options["axisXAngle"]);
        $AxisX->setLabelOption('offset', 35);
    }

    //add target line if not in benchmark mode
    if ($BUID !== "ALL" && $options["GPTargetVal"] !== "NA") {
        $Target =& $Plotarea->addNew("Image_Graph_Plot_Fit_Line", [&$GPTarget]);
        $Target->setLineColor('blue');
        $Target->setTitle("Group Target");
    }
    return $Graph;
}

