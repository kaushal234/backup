<?php
include_once("common.inc.php");
include_once("erp.inc.php");
include_once('product_support.inc.php');
include_once('sales_service.inc.php');
include_once('Image/Graph.php');
include_once("graphs.common.inc.php");

$ERP_LIST = tldLocation::getERPList("smartyOptions");
$COLOURS = array(
    "white","red","aqua","blue","lime","fuchsia","teal","navy",
	"yellow","green","olive","purple","gray","maroon","silver"
);

if($_REQUEST['war']){
	$war = TldDatabase::escape($_REQUEST['war']);
}

switch($m[0]){
case 'past12':
    include('past12.inc.php');
break;
case 'history':
	include('history.inc.php');
break;
}

$Graph->done();

function _doBarGraph($rows,$options){
	global $COLOURS;
	global $BUID;

	$Graph =& Image_Graph::factory('graph', array(900, 500));

	// add font
	$Font =& $Graph->addNew('ttf_font', 'DejaVuLGCSans-Bold.ttf');
	// set the font size
	$Font->setSize(10);
	$Graph->setFont($Font);
	$Plotarea = Image_Graph::factory('plotarea');
	$Legend = Image_Graph::factory('legend');

	$Graph->add(
		Image_Graph::vertical(
			Image_Graph::factory('title', array($options["graphTitle"], 14)),
			Image_Graph::horizontal(
	      		$Plotarea,
				$Legend,
	      		85
	    	),
	    	10
		)
	);

	// link the legend with the plotares
	$Legend->setPlotarea($Plotarea);
	$Legend->setShowMarker = true;

	// create a grid and assign it to the secondary Y axis
	$GridY2 =& $Plotarea->addNew('line_grid', IMAGE_GRAPH_AXIS_Y);

	if(empty($rows)) $rows=array(array("factories"=>"NA","xval"=>0,"yval"=>0));

	foreach($rows as $row){
		$erps[$row['factories']][] = $row;
	}

	$FillArray =& Image_Graph::factory('Image_Graph_Fill_Array');
	//if specific erp is not set then do all erps
	$i = 1;
	foreach($erps as $factories=>$rows){
		$datasets[$factories] =& Image_Graph::factory('dataset');
		if($BUID <> "ALL" && $options["GPTargetVal"] <> "NA") $GPTarget =& Image_Graph::factory('dataset');
		$datasets[$factories]->setName($factories);
		foreach($rows as $row){
			$datasets[$factories]->addPoint($row["xval"], $row["yval"], $factories);
		    if($BUID <> "ALL" && $options["GPTargetVal"] <> "NA") $GPTarget->addPoint($row["xval"], $options["GPTargetVal"]);
		}
		$FillArray->addColor($COLOURS[$i], $factories);
		$i++;
	}

	$Plot =& $Plotarea->addNew($options["graphStyle"], array($datasets, "stacked"));
	$Plot->setFillStyle($FillArray);

	//change the angle of the X axis legend to vertical if option is enabled
	if($options["axisXAngle"]){
        $AxisX =& $Plotarea->getAxis(IMAGE_GRAPH_AXIS_X);
        $AxisX->setFontAngle($options["axisXAngle"]);
        $AxisX->setLabelOption('offset', 35);
	}

	if($BUID <> "ALL" && $options["GPTargetVal"] <> "NA"){
    	$Target =& $Plotarea->addNew("Image_Graph_Plot_Fit_Line", array(&$GPTarget));
    	$Target->setLineColor('blue');
    	$Target->setTitle("Group Target");
	}

	if($options["showMarkers"]=="true"){

		// create a Y data value marker
		$Marker =& $Plot->addNew('Image_Graph_Marker_Value', IMAGE_GRAPH_VALUE_Y);
		// and use the marker on the 1st plot
		$Plot->setMarker($Marker);
		$Plot->setDataSelector(Image_Graph::factory('Image_Graph_DataSelector_NoZeros'));

	}
	return $Graph;
}

function _doLineGraph($rows,$options){
	global $COLOURS;
	global $BUID;

	$Graph =& Image_Graph::factory('graph', array(760, 500));

	// add font
	$Font =& $Graph->addNew('ttf_font', 'DejaVuLGCSans-Bold.ttf');
	// set the font size to 10 pixels
	$Font->setSize(10);
	$Graph->setFont($Font);
	$Plotarea = Image_Graph::factory('plotarea');
	$Legend = Image_Graph::factory('legend');

	$Graph->add(
		Image_Graph::vertical(
			Image_Graph::factory('title', array($options["graphTitle"], 14)),
			Image_Graph::horizontal(
	      		$Plotarea,
				$Legend,
	      		85
	    	),
	    	10
		)
	);

	// link the legend with the plotares
	$Legend->setPlotarea($Plotarea);
	$Legend->setShowMarker = true;

	// create a grid and assign it to the secondary Y axis
	$GridY2 =& $Plotarea->addNew('line_grid', IMAGE_GRAPH_AXIS_Y);

	if(empty($rows)) $rows=array(array("factories"=>"NA","xval"=>0,"yval"=>0));

	foreach($rows as $row){
		$erps[$row['factories']][] = $row;
	}

	$FillArray =& Image_Graph::factory('Image_Graph_Fill_Array');
	//if specific erp is not set then do all erps
	$i = 1;
	foreach($erps as $factories=>$rows){
		$datasets[$factories] =& Image_Graph::factory('dataset');
		if($BUID <> "ALL" && $options["GPTargetVal"] <> "NA") $GPTarget =& Image_Graph::factory('dataset');
		$datasets[$factories]->setName($factories);
		foreach($rows as $row){
			$datasets[$factories]->addPoint($row["xval"], $row["yval"], $factories);
		    if($BUID <> "ALL" && $options["GPTargetVal"] <> "NA") $GPTarget->addPoint($row["xval"], $options["GPTargetVal"]);
		}
		$i++;
		$FillArray->addColor($COLOURS[$i], $factories);
	}

    $j=1;
	foreach($datasets as $factory){
	    ${"Plot{$j}"} =& $Plotarea->addNew($options["graphStyle"], array($factory));
	    $LineStyle =& Image_Graph::factory('Image_Graph_Line_Solid', array($COLOURS[$j]));
        $LineStyle->setThickness( 4 );
	    ${"Plot{$j}"}->setLineStyle($LineStyle);

        $j++;
	}

	//change the angle of the X axis legend to vertical if option is enabled
	if($options["axisXAngle"]){
        $AxisX =& $Plotarea->getAxis(IMAGE_GRAPH_AXIS_X);
        $AxisX->setFontAngle($options["axisXAngle"]);
        $AxisX->setLabelOption('offset', 35);
	}

	//add target line if not in benchmark mode
	if($BUID <> "ALL" && $options["GPTargetVal"] <> "NA"){
    	$Target =& $Plotarea->addNew("Image_Graph_Plot_Fit_Line", array(&$GPTarget));
    	$Target->setLineColor('blue');
    	$Target->setTitle("Group Target");
	}

	return $Graph;
}

?>
