<?php
include_once("common.inc.php");
include_once("quality.inc.php");
include_once('Image/Graph.php');
$COLOURS= array("white","red","aqua","blue","lime","fuchsia","gray","silver","yellow",
	"green","navy","olive","purple","teal","maroon","black");

switch($m[0]){
case 'cumulativeByMonth':
	if($m[1] == "previous"){
		if(date("m") == 1) {
			$t = mktime(0, 0, 0, 12, 1, date("Y")-1);
		} else {
			$t = mktime(0, 0, 0, date("m")-1, 1, date("Y"));
		}
		$erps = tldGT::getCumulativeCountByMonth(date("Y", $t), date("m", $t), $t);
	}else{
		$t = time();
		$erps = tldGT::getCumulativeCountByMonth();
	}

	$Graph =& Image_Graph::factory('graph', array(960, 300));
	$month = date("F", $t);
	$Plotarea = Image_Graph::factory('plotarea');
	$Legend = Image_Graph::factory('legend');
	$Graph->add(
		Image_Graph::vertical(
			Image_Graph::factory('title', array("Cumulative Count of GT on $month by Day and Factory Stacked", 12)),
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
	//if specific erp is not set then do all erps
	$i = 0;
	foreach($erps as $factory=>$rows){
		$datasets[$factory] =& Image_Graph::factory('dataset');
		$datasets[$factory]->setName($factory);
		foreach($rows as $date => $row){
			$datasets[$factory]->addPoint($date, $row, $factory);
		}
		$i++;
		$FillArray->addColor($COLOURS[$i], $factory);
	}
	$Plot =& $Plotarea->addNew("bar", array($datasets, "stacked"));
	$Plot->setFillStyle($FillArray);

break;
}

$Graph->done();
?>
