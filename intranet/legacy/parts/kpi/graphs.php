<?php
include_once("common.inc.php");
include_once("erp.inc.php");
include_once('product_support.inc.php');
include_once('Image/Graph.php');
$COLOURS = array_keys(tldUtils::getColorNames());

if($_REQUEST['war']){
	$war = TldDatabase::escape($_REQUEST['war']);
}

// array to define the list of the diffent SPH with targets values and ERP#
$whse=array("SPH_WIN"=>array("erp"=>"300",
							"SPHName"=>"SPH Windsor (SPH WIN)",
							"SPHLoc"=>"TLD AME - Parts",
                            "WHSECode"=>"DVT",
							"IFR Target"=>75,
							"AVT Target"=>3,
							"WFR Target"=>90),
			"SPH_SAL"=>array("erp"=>"300",
							"SPHName"=>"SPH Salinas (SPH SAL)",
							"SPHLoc"=>"TLD America - Salinas",
							"WHSECode"=>"DCA",
							"IFR Target"=>75,
							"AVT Target"=>3,
							"WFR Target"=>90),
			"SPH_HKG"=>array("erp"=>"600",
							"SPHName"=>"SPH Hong-Kong (SPH HKG)",
							"SPHLoc"=>"TLD CHI",
							"WHSECode"=>"HK1",
							"IFR Target"=>75,
							"AVT Target"=>3,
							"WFR Target"=>90),
#			"SPH_SHA"=>array("erp"=>"640",
#							"SPHName"=>"SPH Shanghai (SPH SHA)",
#							"SPHLoc"=>"TLD SHA",
#							"WHSECode"=>"SP1",
#							"IFR Target"=>75,
#							"AVT Target"=>3,
#							"WFR Target"=>90),
			"SPH_SHA"=>array("erp"=>"680",
							"SPHName"=>"SPH Shanghai (SPH SHA)",
							"SPHLoc"=>"CHINA SSO",
							"WHSECode"=>"SP1",
							"IFR Target"=>75,
							"AVT Target"=>3,
							"WFR Target"=>90),
			"SPH_MTL"=>array("erp"=>"540",
							"SPHName"=>"SPH MontLouis (SPH MTL)",
							"SPHLoc"=>"Montlouis - Parts",
							"WHSECode"=>"SP1",
							"IFR Target"=>75,
							"AVT Target"=>3,
							"WFR Target"=>90),
			"SPH_DUB"=>array("erp"=>"540",
							"SPHName"=>"SPH Dubai (SPH DUB)",
							"SPHLoc"=>"TLD DUB",
							"WHSECode"=>"SP2",
							"IFR Target"=>75,
							"AVT Target"=>3,
							"WFR Target"=>90),
			"targetLineColor"=>"blue");


switch($m[0]){
case 'tdp':

	$Graph =& Image_Graph::factory('graph', array(760, 250));

	// add font
	$Font =& $Graph->addNew('ttf_font', 'DejaVuLGCSans-Bold.ttf');
	// set the font size to 11 pixels
	$Font->setSize(6);
	$Graph->setFont($Font);
	$Plotarea = Image_Graph::factory('plotarea');
	$Legend = Image_Graph::factory('legend');
	// setup the graph layout, 10% for the title, the rest for the graph
	$Graph->add(
	  Image_Graph::vertical(
	    Image_Graph::factory('title', array("TDP , Previous 12 Months", 12)),
			Image_Graph::horizontal(
	      		$Plotarea,
				$Legend,
	      		88
	    	),10
		)
	);

	// link the legend with the plotares
	$Legend->setPlotarea($Plotarea);

	// create a grid and assign it to the secondary Y axis
	$GridY2 =& $Plotarea->addNew('line_grid', IMAGE_GRAPH_AXIS_Y);

	$rows = tldBaanERP::getSalesPartsKPI($whse[$war]['WHSECode'],$whse[$war]['erp']);

	$Dataset =& Image_Graph::factory('dataset');
	foreach($rows as $row){
		$Dataset->addPoint($row["xval"], $row["TDP"]);
	}
	$Plot =& $Plotarea->addNew("bar", array(&$Dataset));
	$Plot->setFillColor("#FF000");
	$Plot->setTitle('TDP');

	// add marker (value) in the bar
	$Marker =& Image_Graph::factory('value_marker', IMAGE_GRAPH_VALUE_Y);
	// shift markers 10 pix down
	$marker_pointing =& $Graph->addNew('Image_Graph_Marker_Pointing', array(0,10,& $Marker));
	$marker_pointing->setLineColor('000000@0.0');
	$Plot->setMarker($marker_pointing);

break;
case 'ifr':
	$Graph =& Image_Graph::factory('graph', array(760, 250));

	// add font
	$Font =& $Graph->addNew('ttf_font', 'DejaVuLGCSans-Bold.ttf');
	// set the font size to 11 pixels
	$Font->setSize(6);
	$Graph->setFont($Font);

	$Plotarea = Image_Graph::factory('plotarea');
	$Legend = Image_Graph::factory('legend');
	// setup the graph layout, 10% for the title, the rest for the graph
	$Graph->add(
	  Image_Graph::vertical(
	    Image_Graph::factory('title', array("IFR , Previous 12 Months", 12)),
			Image_Graph::horizontal(
	      		$Plotarea,
				$Legend,
	      		88
	    	),10
		)
	);

	// link the legend with the plotares
	$Legend->setPlotarea($Plotarea);

	// create a grid and assign it to the secondary Y axis
	$GridY2 =& $Plotarea->addNew('line_grid', IMAGE_GRAPH_AXIS_Y);

	// get the data for the x and y axis
	$rows = tldBaanERP::getSalesPartsKPI($whse[$war]["WHSECode"],$whse[$war]["erp"]);

	$Dataset =& Image_Graph::factory('dataset');
	$GPTarget =& Image_Graph::factory('dataset');
	foreach($rows as $row){
		$Dataset->addPoint($row["xval"], $row["IFR"]);
		$GPTarget->addPoint($row["xval"], $whse[$war]["IFR Target"]);
	}

	$Plot =& $Plotarea->addNew("bar", array(&$Dataset));
	$Plot->setFillColor("#FF000");
	$Plot->setTitle('IFR');

	// force the Y axis to 100%
	$axisY =& $Plotarea->getAxis('y');
    $axisY->forceMaximum(100);


	// add target line
	$Target =& $Plotarea->addNew("Image_Graph_Plot_Fit_Line", array(&$GPTarget));
	$Target->setLineColor($whse["targetLineColor"]);
	$Target->setTitle('IFR Target');


	// add marker (value) in the bar
	$Marker =& Image_Graph::factory('value_marker', IMAGE_GRAPH_VALUE_Y);
	// shift markers 10 pix down
	$marker_pointing =& $Graph->addNew('Image_Graph_Marker_Pointing', array(0,10,& $Marker));
	$marker_pointing->setLineColor('000000@0.0');
	$Plot->setMarker($marker_pointing);


break;
case 'avt':
	$Graph =& Image_Graph::factory('graph', array(760, 250));

	// add a TrueType font
	$Font =& $Graph->addNew('ttf_font', 'DejaVuLGCSans-Bold.ttf');
	// set the font size to 11 pixels
	$Font->setSize(6);
	$Graph->setFont($Font);
	$Plotarea = Image_Graph::factory('plotarea');
	$Legend = Image_Graph::factory('legend');
	// setup the graph layout, 10% for the title, the rest for the graph
	$Graph->add(
	  Image_Graph::vertical(
	    Image_Graph::factory('title', array("AVT , Previous 12 Months", 12)),
			Image_Graph::horizontal(
	      		$Plotarea,
				$Legend,
	      		88
	    	),10
		)
	);

	// link the legend with the plotares
	$Legend->setPlotarea($Plotarea);

	// create a grid and assign it to the secondary Y axis
	$GridY2 =& $Plotarea->addNew('line_grid', IMAGE_GRAPH_AXIS_Y);

	// get the data for the x and y axis
	$rows = tldBaanERP::getSalesPartsKPI($whse[$war]["WHSECode"],$whse[$war]["erp"]);

	$Dataset =& Image_Graph::factory('dataset');
	$GPTarget =& Image_Graph::factory('dataset');
	foreach($rows as $row){
		$Dataset->addPoint($row["xval"], $row["AVT"]);
		$GPTarget->addPoint($row["xval"], $whse[$war]["AVT Target"]);
	}

	$Plot =& $Plotarea->addNew("bar", array(&$Dataset));
	$Plot->setFillColor("#FF000");
	$Plot->setTitle('AVT');

	// add target line
	$Target =& $Plotarea->addNew("Image_Graph_Plot_Fit_Line", array(&$GPTarget));
	$Target->setLineColor($whse["targetLineColor"]);
	$Target->setTitle('AVT Target');

	// add marker (value) in the bar
	$Marker =& Image_Graph::factory('value_marker', IMAGE_GRAPH_VALUE_Y);
	// shift markers 10 pix down
	$marker_pointing =& $Graph->addNew('Image_Graph_Marker_Pointing', array(0,10,& $Marker));
	$marker_pointing->setLineColor('000000@0.0');
	$Plot->setMarker($marker_pointing);

break;
case 'wfr':
	$Graph =& Image_Graph::factory('graph', array(760, 250));

	// add a TrueType font
	$Font =& $Graph->addNew('ttf_font', 'DejaVuLGCSans-Bold.ttf');
	// set the font size to 11 pixels
	$Font->setSize(6);
	$Graph->setFont($Font);
	$Plotarea = Image_Graph::factory('plotarea');
	$Legend = Image_Graph::factory('legend');
	// setup the graph layout, 10% for the title, the rest for the graph
	$Graph->add(
	  Image_Graph::vertical(
	    Image_Graph::factory('title', array("WFR , Previous 12 Months", 12)),
			Image_Graph::horizontal(
	      		$Plotarea,
				$Legend,
	      		88
	    	),10
		)
	);

	// link the legend with the plotares
	$Legend->setPlotarea($Plotarea);

	// create a grid and assign it to the secondary Y axis
	$GridY2 =& $Plotarea->addNew('line_grid', IMAGE_GRAPH_AXIS_Y);

	// get the data for the x and y axis
	$rows = tldBaanERP::getSalesPartsKPI($whse[$war]["WHSECode"],$whse[$war]["erp"]);

	$Dataset =& Image_Graph::factory('dataset');
	$GPTarget =& Image_Graph::factory('dataset');
	foreach($rows as $row){
		$Dataset->addPoint($row["xval"], $row["WFR"]);
		$GPTarget->addPoint($row["xval"], $whse[$war]["WFR Target"]);
	}

	$Plot =& $Plotarea->addNew("bar", array(&$Dataset));
	$Plot->setFillColor("red");
	$Plot->setTitle('WFR');

	// force the Y axis to 100%
	$axisY =& $Plotarea->getAxis('y');
    $axisY->forceMaximum(100);

	// add target line
	$Target =& $Plotarea->addNew("Image_Graph_Plot_Fit_Line", array(&$GPTarget));
	$Target->setLineColor($whse["targetLineColor"]);
	$Target->setTitle('WFR Target');

	// add marker (value) in the bar
	$Marker =& Image_Graph::factory('value_marker', IMAGE_GRAPH_VALUE_Y);
	// shift markers 10 pix down
	$marker_pointing =& $Graph->addNew('Image_Graph_Marker_Pointing', array(0,10,& $Marker));
	$marker_pointing->setLineColor('000000@0.0');
	$Plot->setMarker($marker_pointing);
break;

// debut bbl
case 'InventoryValue':
	$Graph =& Image_Graph::factory('graph', array(760, 250));

	// add a TrueType font
	$Font =& $Graph->addNew('ttf_font', 'DejaVuLGCSans-Bold.ttf');
	// set the font size to 11 pixels
	$Font->setSize(6);
	$Graph->setFont($Font);
	$Plotarea = Image_Graph::factory('plotarea');
	$Legend = Image_Graph::factory('legend');
	// setup the graph layout, 10% for the title, the rest for the graph
	$Graph->add(
	  Image_Graph::vertical(
	    Image_Graph::factory('title', array("Inventory in Days of Sales , Previous 12 Months", 12)),
			Image_Graph::horizontal(
	      		$Plotarea,
				$Legend,
	      		88
	    	),10
		)
	);

	// link the legend with the plotares
	$Legend->setPlotarea($Plotarea);

	// create a grid and assign it to the secondary Y axis
	$GridY2 =& $Plotarea->addNew('line_grid', IMAGE_GRAPH_AXIS_Y);

	// get the data for the x and y axis
	$rows = tldBaanERP::getPartsInventoryValue($whse[$war]["SPHLoc"]);
	$Dataset =& Image_Graph::factory('dataset');
	$GPTarget =& Image_Graph::factory('dataset');
	$MaxY=0;
	foreach($rows as $row){
		$Dataset->addPoint($row["xval"], $row["yval"]);
		$GPTarget->addPoint($row["xval"], 0);
		if ($MaxY<$row["yval"]) {$MaxY=$row["yval"]; }
	}

	$Plot =& $Plotarea->addNew("bar", array(&$Dataset));
	$Plot->setFillColor("red");
	$Plot->setTitle('Inv value');

	// force the Y axis to 100%
	$axisY =& $Plotarea->getAxis('y');
    $axisY->forceMaximum($MaxY);

	// add target line
	$Target =& $Plotarea->addNew("Image_Graph_Plot_Fit_Line", array(&$GPTarget));
	$Target->setLineColor($whse["targetLineColor"]);
	$Target->setTitle('Inv value Target');

	// add marker (value) in the bar
	$Marker =& Image_Graph::factory('value_marker', IMAGE_GRAPH_VALUE_Y);
	// shift markers 10 pix down
	$marker_pointing =& $Graph->addNew('Image_Graph_Marker_Pointing', array(0,10,& $Marker));
	$marker_pointing->setLineColor('000000@0.0');
	$Plot->setMarker($marker_pointing);
break;

// fin bbl


}

$Graph->done();

?>

