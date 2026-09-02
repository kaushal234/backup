<?php
include_once("common.inc.php");
include_once("erp.inc.php");
include_once('product_support.inc.php');
include_once('Image/Graph.php');
$COLOURS = array_keys(tldUtils::getColorNames());

switch($m[0]){
case "otdpPart":		
		$options=array("part"=>$part);
		$Graph =& Image_Graph::factory('graph', array(760, 250)); 
		$Plotarea =& $Graph->addNew('plotarea'); 
		$Graph->add(Image_Graph::factory('title', array("On Time Delivery Performance for ERP ".$erp." and Part# ".$options['part']." For previous 12 months (Percentage)",24)));
		$rows = tldERPVendor::getOTDPByERP($erp,$options,$mode);
		$Dataset =& Image_Graph::factory('dataset');
		foreach($rows as $row){
			$Dataset->addPoint($row["xval"], $row["yval"]); 
		}
		$Plot =& $Plotarea->addNew("bar", array(&$Dataset));
		$Plot->setFillColor("#FF000");	
break;
}

$Graph->done(); 

?>