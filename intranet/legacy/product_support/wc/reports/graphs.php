<?php
include_once("common.inc.php");
include_once("product_support.inc.php");
include_once('Image/Graph.php');
include_once("pChart/pChart/pChart.class");
//$COLOURS= array("white","aqua","blue","lime","fuchsia","red","gray","silver","yellow",
//	"green","navy","olive","purple","teal","maroon","black");
$COLOURS = array_keys(tldUtils::getColorNames());
switch($m[0]){
//need man_location
case 'countCurrentYearByFactory':
	$man_location = TldDatabase::escape($man_location);

	$query = <<<EOF
SELECT IF(model='', 'NO_MODEL', model) AS model,
	DATE_FORMAT(claim_date, '%Y-%m') AS display_date,
	COUNT(*) AS cnt
FROM warranty
WHERE PERIOD_DIFF(DATE_FORMAT(NOW(), '%Y%m'), DATE_FORMAT(claim_date, '%Y%m')) < 12
    AND PERIOD_DIFF(DATE_FORMAT(NOW(), '%Y%m'), DATE_FORMAT(claim_date, '%Y%m')) > 0
    AND man_location = '$man_location'
GROUP BY display_date, model
EOF;
//ORDER BY claim_date, model
	$rows = tldUtils::getSqlToAssocArray($query);
    $yearMonth = date("Y")*12+date("m");
    for($i=($yearMonth-12); $i<$yearMonth-1; $i++){
        $ym = sprintf("%04d-%02d", floor($i/12), ($i%12)+1);
        $d[$ym] = array("Name"=>$ym);
        }
    $dataDescription["Values"] = array();
	foreach($rows as $row){
		$d[$row['display_date']][$row['model']] = $row['cnt'];
        if(!in_array($row['model'], $dataDescription["Values"])){
            $dataDescription["Values"][] = $row['model'];
            $dataDescription["Description"][$row['model']] = $row['model'];
        }
	}
    $chart = new pChart(1000,800);
    $rgb = array_values(tldUtils::getColorNames());
    foreach($dataDescription['Values'] as $k=>$v){
        //filter out very light colors
        if(array_product($rgb[$k]) < 200*200*200){
            $chart->setColorPalette(
                $k,
                $rgb[$k][0],
                $rgb[$k][1],
                $rgb[$k][2]
            );
        }
    }
    $data = array_values($d);
    $dataDescription["Position"] = "Name";
    // Display Serie1 when calling graphs functions

    // Set the description of Serie1 to "Year 2007"
     // Initialise the graph
     $chart->setFontProperties("$SHARED_PHP_PATH/pChart/Fonts/tahoma.ttf",8);
     $chart->setGraphArea(40,40,880,780);
     $chart->drawGraphArea(255,255,255,TRUE);
     $chart->drawScale($data,
             $dataDescription,
             SCALE_ADDALLSTART0,
             150,150,150,TRUE,0,2,TRUE);
     $chart->drawGrid(4,TRUE,230,230,230,50);

     // Draw the 0 line
     $chart->setFontProperties("$SHARED_PHP_PATH/pChart/Fonts/tahoma.ttf",6);
     $chart->drawTreshold(0,143,55,72,TRUE,TRUE);

     // Draw the bar graph
     $chart->drawStackedBarGraph($data,
             $dataDescription,
             TRUE);

     // Finish the graph
     $chart->setFontProperties("$SHARED_PHP_PATH/pChart/Fonts/tahoma.ttf",8);
     $chart->drawLegend(
         900,0,
         $dataDescription,
         255,255,255);
     $chart->setFontProperties("$SHARED_PHP_PATH/pChart/Fonts/tahoma.ttf",12);
     $chart->drawTitle(50,22,
             "Monthly Warranty Count for Previous 12 Months by Model",
             50,50,50,585);
     $chart->Stroke();
break;
case 'countCurrentYearByFactoryBAK':
	$Graph =& Image_Graph::factory('graph', array(760, 500));

	// add font
	$Font =& $Graph->addNew('ttf_font', 'DejaVuLGCSans-Bold.ttf');
	// set the font size to 11 pixels
	$Font->setSize(6);
	$Graph->setFont($Font);
	$Plotarea = Image_Graph::factory('plotarea');
	$Legend = Image_Graph::factory('legend');

	$Graph->add(
		Image_Graph::vertical(
			Image_Graph::factory('title', array("Monthly Warranty Count for Previous 12 Months by Model", 12)),
			Image_Graph::horizontal(
				$Plotarea,
				$Legend,
				80
			),
			10
		)
	);
	$Legend->setPlotarea($Plotarea);
	$Legend->setShowMarker = true;
	// create a grid and assign it to the secondary Y axis
	$GridY2 =& $Plotarea->addNew('line_grid', IMAGE_GRAPH_AXIS_Y);

	$man_location = TldDatabase::escape($man_location);

	$query = <<<EOF
SELECT model,
	DATE_FORMAT(claim_date, '%Y-%m') AS display_date,
	COUNT(*) AS cnt
FROM warranty
WHERE PERIOD_DIFF(DATE_FORMAT(NOW(), '%Y%m'), DATE_FORMAT(claim_date, '%Y%m')) < 12
	AND man_location = '$man_location'
GROUP BY display_date, model
EOF;
//ORDER BY claim_date, model

	$FillArray =& Image_Graph::factory('Image_Graph_Fill_Array');
	$rows = tldUtils::getSqlToAssocArray($query);
	foreach($rows as $row){
		//divide into arrays by model
		$as[$row['model']][] = $row;
	}
	$i=0;
	// create a fill array
	$myColours = array_rand($COLOURS, count($as));

	foreach($as as $model=>$a){
		$FillArray->addColor($COLOURS[$myColours[$i]], $model);
		$i++;
		$Dataset =& Image_Graph::factory('dataset');
		$Dataset->setName($model);
//		$FillArray->addColor($COLOURS[$iColour].$opacity, $model);
//		$i++;
		foreach($a as $row){
			$Dataset->addPoint(		$row["display_date"],
									$row["cnt"],
									$model);
		}

		$datasets["$model"] = &$Dataset;
	}

	$Plot =& $Plotarea->addNew("bar", array($datasets, 'stacked'));
	// set a line color
	$Plot->setLineColor('gray');

	// set a standard fill style
	$Plot->setFillStyle($FillArray);

	// create a Y data value marker
	$Marker =& $Plot->addNew('Image_Graph_Marker_Value', IMAGE_GRAPH_VALUE_Y);
	// and use the marker on the 1st plot
	$Plot->setMarker($Marker);
	$Plot->setDataSelector(Image_Graph::factory('Image_Graph_DataSelector_NoZeros'));
    $Graph->done();
break;
case 'countCurrentYearByFactoryType':
	$man_location = TldDatabase::escape($man_location);

	$query = <<<EOF
SELECT type,
	DATE_FORMAT(claim_date, '%Y-%m') AS display_date,
	COUNT(*) AS cnt
FROM warranty
WHERE PERIOD_DIFF(DATE_FORMAT(NOW(), '%Y%m'), DATE_FORMAT(claim_date, '%Y%m')) < 12
    AND PERIOD_DIFF(DATE_FORMAT(NOW(), '%Y%m'), DATE_FORMAT(claim_date, '%Y%m')) > 0
	AND man_location = '$man_location'
GROUP BY display_date, type
EOF;
//ORDER BY claim_date, model
	$rows = tldUtils::getSqlToAssocArray($query);
    $yearMonth = date("Y")*12+date("m");
    for($i=($yearMonth-12); $i<$yearMonth-1; $i++){
        $ym = sprintf("%04d-%02d", floor($i/12), ($i%12)+1);
        $d[$ym] = array("Name"=>$ym);
        }
    $dataDescription["Values"] = array();
	foreach($rows as $row){
		$d[$row['display_date']][$row['type']] = $row['cnt'];
        if(!in_array($row['type'], $dataDescription["Values"])){
            $dataDescription["Values"][] = $row['type'];
            $dataDescription["Description"][$row['type']] = $row['type'];
        }
	}
    $chart = new pChart(1000,500);
    $step = 16;
    $rgb = array_values(tldUtils::getColorNames());
    foreach($dataDescription['Values'] as $k=>$v){
        //filter out very light colors
        if(array_product($rgb[$k]) < 200*200*200){
            $chart->setColorPalette(
                $k,
                $rgb[$k][0],
                $rgb[$k][1],
                $rgb[$k][2]
            );
        }
    }
    $data = array_values($d);
    $dataDescription["Position"] = "Name";
    // Display Serie1 when calling graphs functions

    // Set the description of Serie1 to "Year 2007"
     // Initialise the graph
     $chart->setFontProperties("$SHARED_PHP_PATH/pChart/Fonts/tahoma.ttf",8);
     $chart->setGraphArea(40,40,880,480);
     $chart->drawGraphArea(255,255,255,TRUE);
     $chart->drawScale($data,
             $dataDescription,
             SCALE_ADDALLSTART0,
             150,150,150,TRUE,0,2,TRUE);
     $chart->drawGrid(4,TRUE,230,230,230,50);

     // Draw the 0 line
	$chart->setFontProperties("$SHARED_PHP_PATH/pChart/Fonts/tahoma.ttf",6);
	$chart->drawTreshold(0,143,55,72,TRUE,TRUE);

     // Draw the bar graph
     $chart->drawStackedBarGraph($data,
             $dataDescription,
             TRUE);

     // Finish the graph
     $chart->setFontProperties("$SHARED_PHP_PATH/pChart/Fonts/tahoma.ttf",8);
     $chart->drawLegend(900,40,$dataDescription,255,255,255,FALSE);
     $chart->setFontProperties("$SHARED_PHP_PATH/pChart/Fonts/tahoma.ttf",12);
     $chart->drawTitle(50,22,
             "Monthly Warranty Count for Previous 12 Months by Type",
             50,50,50,585);
     $chart->Stroke();
break;
case 'countCurrentYearByFactoryTypeBAK':
	$Graph =& Image_Graph::factory('graph', array(760, 500));

	// add font
	$Font =& $Graph->addNew('ttf_font', 'DejaVuLGCSans-Bold.ttf');
	// set the font size to 11 pixels
	$Font->setSize(6);
	$Graph->setFont($Font);
	$Plotarea = Image_Graph::factory('plotarea');
	$Legend = Image_Graph::factory('legend');

	$Graph->add(
		Image_Graph::vertical(
			Image_Graph::factory('title', array("Monthly Warranty Count for Previous 12 Months by Type", 12)),
			Image_Graph::horizontal(
				$Plotarea,
				$Legend,
				80
			),
			10
		)
	);
	$Legend->setPlotarea($Plotarea);
	$Legend->setShowMarker = true;
	// create a grid and assign it to the secondary Y axis
	$GridY2 =& $Plotarea->addNew('line_grid', IMAGE_GRAPH_AXIS_Y);

	$man_location = TldDatabase::escape($man_location);

	$query = <<<EOF
SELECT type,
	DATE_FORMAT(claim_date, '%Y-%m') AS display_date,
	COUNT(*) AS cnt
FROM warranty
WHERE PERIOD_DIFF(DATE_FORMAT(NOW(), '%Y%m'), DATE_FORMAT(claim_date, '%Y%m')) < 12
	AND man_location = '$man_location'
GROUP BY display_date, type
EOF;
//ORDER BY claim_date, model
	$rows = tldUtils::getSqlToAssocArray($query);
	foreach($rows as $row){
		//divide into arrays by model
		$as[$row['type']][] = $row;
	}
	// create a fill array
	$FillArray =& Image_Graph::factory('Image_Graph_Fill_Array');
	$i=0;

	foreach($as as $key=>$a){
		$Dataset =& Image_Graph::factory('dataset');
		$Dataset->setName($key);
		$FillArray->addColor($COLOURS[$i], $key);
		$i++;
		foreach($a as $row){
			$Dataset->addPoint(		$row["display_date"],
									$row["cnt"],
									$key);
		}

		$datasets["$key"] = &$Dataset;
	}

	$Plot =& $Plotarea->addNew("bar", array($datasets, 'stacked'));
	// set a line color
	$Plot->setLineColor('gray');

	// set a standard fill style
	$Plot->setFillStyle($FillArray);

	// create a Y data value marker
	$Marker =& $Plot->addNew('Image_Graph_Marker_Value', IMAGE_GRAPH_VALUE_Y);
	// and use the marker on the 1st plot
	$Plot->setMarker($Marker);
	$Plot->setDataSelector(Image_Graph::factory('Image_Graph_DataSelector_NoZeros'));
    $Graph->done();
break;
case 'countPast5YearsByFactory':
	$Graph =& Image_Graph::factory('graph', array(900, 300));
	$TITLE = "Yearly Warranty Count for $man_location, Last 5 Years";
	$Plotarea = Image_Graph::factory('plotarea');
	$Legend = Image_Graph::factory('legend');
	// setup the graph layout, 10% for the title, the rest for the graph
	$Graph->add(
		Image_Graph::vertical(
			Image_Graph::factory('title', array($TITLE, 10)),
			Image_Graph::horizontal(
				$Plotarea,
				$Legend,
				90
			),
			10
		)
	);
	$Legend->setPlotarea($Plotarea);
	$Legend->setShowMarker = true;

	// add font
	$Font =& $Graph->addNew('ttf_font', 'DejaVuLGCSans-Bold.ttf');
	// set the font size to 11 pixels
	$Font->setSize(6);
	$Graph->setFont($Font);

	// create a grid and assign it to the secondary Y axis
	$GridY2 =& $Plotarea->addNew('line_grid', IMAGE_GRAPH_AXIS_Y);

	$man_location = TldDatabase::escape($man_location);

	$Dataset =& Image_Graph::factory('dataset');
	$query = <<<EOF
		SELECT count( * ) AS cnt,
			YEAR(claim_date) AS display_date
		FROM warranty
		WHERE YEAR(claim_date) > YEAR(NOW())-5
		AND man_location = '$man_location'
		GROUP BY display_date
EOF;
	$rows = tldUtils::getSqlToAssocArray($query);
	foreach($rows as $row){
		$Dataset->addPoint($row["display_date"], $row["cnt"]);
	}
	$Dataset->setName($man_location);
	$Plot =& $Plotarea->addNew("bar", array(&$Dataset));
	$Plot->setFillColor("#FF000");
    $Graph->done();
break;
default:
}

?>
