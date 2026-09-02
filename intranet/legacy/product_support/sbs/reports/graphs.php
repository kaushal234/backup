<?php
include_once("common.inc.php");
include_once("product_support.inc.php");
include_once('Image/Graph.php');
$COLOURS= array("white","aqua","blue","lime","fuchsia","red","gray","silver","yellow",
	"green","navy","olive","purple","teal","maroon","black");

switch($m[0]){
case 'SBSStats':
	switch($m[1]){
	case 'statsByCompulsoryFactory12Months':
		$rows = tldSB::getSBSStats(array("mode"=>"byCompulsoryFactoryPast12Months",
									"factory"=>$erp)
							);
		$TITLE = "Compulsory SB Stats for Past 12 Months";
	break;
	case 'statsByRecommendedFactory12Months':
		$rows = tldSB::getSBSStats(array("mode"=>"byRecommendedFactoryPast12Months",
									"factory"=>$erp)
							);
		$TITLE = "Recommended SB Stats for Past 12 Months";
	break;
	}
	if(count($rows)==0) break;

	$Graph =& Image_Graph::factory('graph', array(750, 600));
	$Plotarea = Image_Graph::factory('plotarea');
	$Legend = Image_Graph::factory('legend');
	$Graph->add(
		Image_Graph::vertical(
			Image_Graph::factory('title', array($TITLE, 12)),
			Image_Graph::horizontal(
				$Plotarea,
				$Legend,
				85
			),
			5
		)
	);
	$Legend->setPlotarea($Plotarea);
	$Legend->setShowMarker = true;
	$FillArray =& Image_Graph::factory('Image_Graph_Fill_Array');
	$i = 0;
	$factories[] = array();

	$t = time();
	$months = array();
	$factories[] = '';

	for($m=11;$m>=0;$m--) {
		$time = strtotime('-'.$m.' months');
		$month[] = date("Ym",$time);
	}

	foreach($rows as $row){
		if(!in_array($row['factory'], $factories)){
			$factories[] = $row['factory'];
			$datasets[$row['factory']] =& Image_Graph::factory('dataset');
			$datasets[$row['factory']]->setName($row['factory']);
			$FillArray->addColor($COLOURS[$i], $row['factory']);
			$i++;
		}
		while(count($month) && $month[0] < $row["ym"]) {
			$datasets[$row['factory']]->addPoint(
				array_shift($month),
				null,
				$row['factory']
			);
		}

		if($month[0] == $row["ym"]) array_shift($month);

		if($row["numAffected"] != 0) {
			$datasets[$row['factory']]->addPoint(
				$row["ym"],
				$row["numAffected"],
				$row['factory']
			);
		}
	}
	$Plot =& $Plotarea->addNew("bar", array($datasets, "stacked"));
	$Plot->setFillStyle($FillArray);
	$Plot->setLineColor('gray');
	// create a Y data value marker
	$Marker =& $Plot->addNew('Image_Graph_Marker_Value', IMAGE_GRAPH_VALUE_Y);
	// and use the marker on the 1st plot
	$Plot->setMarker($Marker);
break;
case 'historyAllFactories':
	$Graph =& Image_Graph::factory('graph', array(750, 300));
	$Plotarea = Image_Graph::factory('plotarea');
	$Legend = Image_Graph::factory('legend');
	$Graph->add(
		Image_Graph::vertical(
			Image_Graph::factory('title', array("Count of SBS opened by Year, Month for Previous 12 Months", 12)),
			Image_Graph::horizontal(
				$Plotarea,
				$Legend,
				85
			),
			5
		)
	);
	$Legend->setPlotarea($Plotarea);
	$Legend->setShowMarker = true;
	$query =<<<EOF
		SELECT factory, date_format(entered_date, '%Y-%m') AS dispMonth,
		  COUNT(*) AS opened
		FROM sbs AS t1
		WHERE PERIOD_DIFF(DATE_FORMAT(NOW(), '%Y%m'), DATE_FORMAT(t1.entered_date, '%Y%m')) < 12
		GROUP BY factory, dispMonth
EOF;
	$rows = tldUtils::getSqlToAssocArray($query);
	foreach($rows as $row){
		$erps[$row['factory']][] = $row;
	}
	$FillArray =& Image_Graph::factory('Image_Graph_Fill_Array');
	//if specific erp is not set then do all erps
	$i = 0;
	foreach($erps as $factory=>$rows){
		$Dataset =& Image_Graph::factory('dataset');
		$Dataset->setName($factory);
		foreach($rows as $row){
			$Dataset->addPoint($row["dispMonth"], $row["opened"], $factory);
		}
		$i++;
		$FillArray->addColor($COLOURS[$i], $factory);
		$datasets[$factory] = $Dataset;
	}
	$Plot =& $Plotarea->addNew("bar", array($datasets, "stacked"));
	$Plot->setFillStyle($FillArray);
break;
case 'historyByFactoryByWeek':
	$erps = array("400","420","500","520","640","620");
	if(!in_array($erp, $erps))
		exit;
	$Graph =& Image_Graph::factory('graph', array(1000, 300));

	$Graph->add(
		Image_Graph::vertical(
			Image_Graph::factory('title', array("Average Weekly Fweight over Time for Company $erp", 18)),
			$Plotarea = Image_Graph::factory('plotarea'),
			5
		)
	);
	$Dataset =& Image_Graph::factory('dataset');
	$query =<<<EOF
		SELECT date_format(entered_date, '%Y-%m') AS dispMonth,
	  (SELECT count(*)
	  FROM sbs AS t2
	  WHERE date_format(t2.entered_date, '%Y-%m')=date_format(t1.entered_date, '%Y-%m')
	  ) AS opened
	FROM sbs AS t1
	GROUP BY dispMonth
EOF;
	$rows = tldUtils::getSqlToAssocArray($query);
	foreach($rows as $row){
		$Dataset->addPoint($row["displMonth"], $row["cou"]);
	}
	$Plot =& $Plotarea->addNew("line", array(&$Dataset));
	$Plot->setFillColor("#FF000");
break;
}

$Graph->done();
?>
