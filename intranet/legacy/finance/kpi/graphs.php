<?php
include_once("common.inc.php");
include_once("erp.inc.php");
include_once('product_support.inc.php');
include_once('Image/Graph.php');
$COLOURS = array_keys(tldUtils::getColorNames());

//$BUID = 17;
if($_REQUEST['buid']){
	$BUID = TldDatabase::escape($_REQUEST['buid']);
	$loc = new tldLocation($BUID);
	$location = $loc->getHeader();
	$erp = $location['erp'];
}

switch($m[0] ?? null){
case 'cycleCountQuantityPerformancePast12Months':
	$Graph =& Image_Graph::factory('graph', array(760, 250));
	$Plotarea =& $Graph->addNew('plotarea');
	$Graph->add(Image_Graph::factory('title', array("Cycle Count Quantity Performance for Previous 12 Months",24)));

	$query = <<<EOF
SELECT if(cc_qua=0, 0, cc_quv*100/cc_qua) as yval,
	DATE_FORMAT(CONCAT(ynam,'-',mnam,'-01'), '%Y-%m') AS xval
FROM fin_kpi
WHERE PERIOD_DIFF(DATE_FORMAT(NOW(), '%Y%m'),
                DATE_FORMAT(CONCAT(ynam,'-',mnam,'-01'), '%Y%m')
      ) <= 12
 AND buid=$BUID
ORDER BY ynam, mnam
EOF;
	$rows = tldUtils::getSqlToAssocArray($query);

	$Dataset =& Image_Graph::factory('dataset');
	foreach($rows as $row){
		$Dataset->addPoint($row["xval"], $row["yval"]);
	}
	$Plot =& $Plotarea->addNew("bar", array(&$Dataset));
	$Plot->setFillColor("#FF000");
break;
case 'cycleCountValuePerformancePast12Months':
	$Graph =& Image_Graph::factory('graph', array(760, 250));
	$Plotarea =& $Graph->addNew('plotarea');
	$Graph->add(Image_Graph::factory('title', array("Cycle Count Value Performance for Previous 12 Months",24)));

	$query = <<<EOF
SELECT if(cc_pric=0, 0, cc_priv*100/cc_pric) as yval,
	DATE_FORMAT(CONCAT(ynam,'-',mnam,'-01'), '%Y-%m') AS xval
FROM fin_kpi
WHERE PERIOD_DIFF(DATE_FORMAT(NOW(), '%Y%m'),
                DATE_FORMAT(CONCAT(ynam,'-',mnam,'-01'), '%Y%m')
      ) <= 12
 AND buid=$BUID
ORDER BY ynam, mnam
EOF;

	$rows = tldUtils::getSqlToAssocArray($query);
	$Dataset =& Image_Graph::factory('dataset');
	foreach($rows as $row){
		$Dataset->addPoint($row["xval"], $row["yval"]);
	}
	$Plot =& $Plotarea->addNew("bar", array(&$Dataset));
	$Plot->setFillColor("#FF000");
break;
case 'factoryProductiveHourRatioPast12Months':
	$Graph =& Image_Graph::factory('graph', array(760, 250));
	$Plotarea =& $Graph->addNew('plotarea');
	$Graph->add(Image_Graph::factory('title', array("Factory Productive Hour Ratio for Previous 12 Months",24)));

	$query = <<<EOF
SELECT if(phr_poth=0, 0, phr_proh*100/phr_poth) as yval,
	DATE_FORMAT(CONCAT(ynam,'-',mnam,'-01'), '%Y-%m') AS xval
FROM fin_kpi
WHERE PERIOD_DIFF(DATE_FORMAT(NOW(), '%Y%m'),
                DATE_FORMAT(CONCAT(ynam,'-',mnam,'-01'), '%Y%m')
      ) <= 12
 AND buid=$BUID
ORDER BY ynam, mnam
EOF;

	$rows = tldUtils::getSqlToAssocArray($query);
	$Dataset =& Image_Graph::factory('dataset');
	foreach($rows as $row){
		$Dataset->addPoint($row["xval"], $row["yval"]);
	}
	$Plot =& $Plotarea->addNew("bar", array(&$Dataset));
	$Plot->setFillColor("#FF000");
break;
case 'factoryStandardEfficiencyPast12Months':
	$Graph =& Image_Graph::factory('graph', array(760, 250));
	$Plotarea =& $Graph->addNew('plotarea');
	$Graph->add(Image_Graph::factory('title', array("Factory Standard Efficiency for Previous 12 Months",24)));

	$query = <<<EOF
SELECT if(fse_stdh=0, 0, fse_acth*100/fse_stdh) as yval,
	DATE_FORMAT(CONCAT(ynam,'-',mnam,'-01'), '%Y-%m') AS xval
FROM fin_kpi
WHERE PERIOD_DIFF(DATE_FORMAT(NOW(), '%Y%m'),
                DATE_FORMAT(CONCAT(ynam,'-',mnam,'-01'), '%Y%m')
      ) <= 12
 AND buid=$BUID
ORDER BY ynam, mnam
EOF;

	$rows = tldUtils::getSqlToAssocArray($query);
	$Dataset =& Image_Graph::factory('dataset');
	foreach($rows as $row){
		$Dataset->addPoint($row["xval"], $row["yval"]);
	}
	$Plot =& $Plotarea->addNew("bar", array(&$Dataset));
	$Plot->setFillColor("#FF000");
break;
case 'factoryProductivityPast12Months':
	$Graph =& Image_Graph::factory('graph', array(760, 250));
	$Plotarea =& $Graph->addNew('plotarea');
	$Graph->add(Image_Graph::factory('title', array("Factory Productivity for Previous 12 Months",24)));

	$query = <<<EOF
SELECT if(fse_stdh=0 OR phr_poth=0, 0, 100*fse_acth/fse_stdh * phr_proh/phr_poth) as yval,
	DATE_FORMAT(CONCAT(ynam,'-',mnam,'-01'), '%Y-%m') AS xval
FROM fin_kpi
WHERE PERIOD_DIFF(DATE_FORMAT(NOW(), '%Y%m'),
                DATE_FORMAT(CONCAT(ynam,'-',mnam,'-01'), '%Y%m')
      ) <= 12
 AND buid=$BUID
ORDER BY ynam, mnam
EOF;

	$rows = tldUtils::getSqlToAssocArray($query);
	$Dataset =& Image_Graph::factory('dataset');
	foreach($rows as $row){
		$Dataset->addPoint($row["xval"], $row["yval"]);
	}
	$Plot =& $Plotarea->addNew("bar", array(&$Dataset));
	$Plot->setFillColor("#FF000");
break;
case 'inventoryValuePast12Months':
	$Graph =& Image_Graph::factory('graph', array(760, 250));
	$Plotarea =& $Graph->addNew('plotarea');
	$Graph->add(Image_Graph::factory('title', array("Inventory Value (Days) for Previous 12 Months",24)));

	$query = <<<EOF
SELECT inv_vald as yval,
	DATE_FORMAT(CONCAT(ynam,'-',mnam,'-01'), '%Y-%m') AS xval
FROM fin_kpi
WHERE PERIOD_DIFF(DATE_FORMAT(NOW(), '%Y%m'),
                DATE_FORMAT(CONCAT(ynam,'-',mnam,'-01'), '%Y%m')
      ) <= 12
 AND buid=$BUID
ORDER BY ynam, mnam
EOF;

	$rows = tldUtils::getSqlToAssocArray($query);
	$Dataset =& Image_Graph::factory('dataset');
	foreach($rows as $row){
		$Dataset->addPoint($row["xval"], $row["yval"]);
	}
	$Plot =& $Plotarea->addNew("bar", array(&$Dataset));
	$Plot->setFillColor("#FF000");
break;
case 'wipValuePast12Months':
	$Graph =& Image_Graph::factory('graph', array(760, 250));
	$Plotarea =& $Graph->addNew('plotarea');
	$Graph->add(Image_Graph::factory('title', array("Work in Progress Value (Days) for Previous 12 Months",24)));

	$query = <<<EOF
SELECT wip_vald as yval,
	DATE_FORMAT(CONCAT(ynam,'-',mnam,'-01'), '%Y-%m') AS xval
FROM fin_kpi
WHERE PERIOD_DIFF(DATE_FORMAT(NOW(), '%Y%m'),
                DATE_FORMAT(CONCAT(ynam,'-',mnam,'-01'), '%Y%m')
      ) <= 12
 AND buid=$BUID
ORDER BY ynam, mnam
EOF;

	$rows = tldUtils::getSqlToAssocArray($query);
	$Dataset =& Image_Graph::factory('dataset');
	foreach($rows as $row){
		$Dataset->addPoint($row["xval"], $row["yval"]);
	}
	$Plot =& $Plotarea->addNew("bar", array(&$Dataset));
	$Plot->setFillColor("#FF000");
break;
case 'internalCustomerSatisfactionPast12Months':
	$Graph =& Image_Graph::factory('graph', array(760, 250));
	$Plotarea = Image_Graph::factory('plotarea');
	$Legend = Image_Graph::factory('legend');
	$Graph->add(
		Image_Graph::vertical(
			Image_Graph::factory('title', array("Internal Customer Satisfaction for Previous 12 Months by SSO", 12)),
			Image_Graph::horizontal(
				$Plotarea,
				$Legend,
				80
			),
			5
		)
	);
	$Legend->setPlotarea($Plotarea);
	$Legend->setShowMarker = true;

	$query = <<<EOF
SELECT *,
	DATE_FORMAT(CONCAT(ynam,'-',mnam,'-01'), '%Y-%m') AS xval
FROM fin_kpi
WHERE PERIOD_DIFF(DATE_FORMAT(NOW(), '%Y%m'),
                DATE_FORMAT(CONCAT(ynam,'-',mnam,'-01'), '%Y%m')
      ) <= 12
 AND buid=$BUID
ORDER BY ynam, mnam
EOF;

	$rows = tldUtils::getSqlToAssocArray($query);
	// create a fill array
	$FillArray =& Image_Graph::factory('Image_Graph_Fill_Array');

	$ameDataset =& Image_Graph::factory('dataset');
	$ameDataset->setName('TLD America');
	$FillArray->addColor($COLOURS[0], 'ame');

	$asiDataset =& Image_Graph::factory('dataset');
	$asiDataset->setName('TLD Asia');
	$FillArray->addColor($COLOURS[1], 'asi');

	$eurDataset =& Image_Graph::factory('dataset');
	$eurDataset->setName('TLD Europe');
	$FillArray->addColor($COLOURS[2], 'eur');

	foreach($rows as $row){
		$ameDataset->addPoint(		$row["xval"],
								$row["ics_ame"],
								'ame');
		$asiDataset->addPoint(		$row["xval"],
								$row["ics_asi"],
								'asi');
		$eurDataset->addPoint(		$row["xval"],
								$row["ics_eur"],
								'eur');
	}
	$datasets["ame"] = &$ameDataset;
	$datasets["asi"] = &$asiDataset;
	$datasets["eur"] = &$eurDataset;

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
break;
case 'pdcOpenedCountPast12Months':
	$Graph =& Image_Graph::factory('graph', array(760, 250));
	$Plotarea =& $Graph->addNew('plotarea');
	$Graph->add(Image_Graph::factory('title', array("Opened PDC for Previous 12 Months",24)));

	$man_location = TldDatabase::escape($BU ?? null);


	$query = <<<EOF
		SELECT COUNT(*) as yval,
		DATE_FORMAT(date, '%Y-%m') AS xval
		FROM demerit
		WHERE PERIOD_DIFF(DATE_FORMAT(NOW(),'%Y%m'),
							DATE_FORMAT(date, '%Y%m')
							) <= 12
			AND factory=$BUID
			Group by xval
			ORDER BY xval
EOF;

	$rows = tldUtils::getSqlToAssocArray($query);
	$Dataset =& Image_Graph::factory('dataset');
	foreach($rows as $row){
		$Dataset->addPoint($row["xval"], $row["yval"]);
	}
	$Plot =& $Plotarea->addNew("bar", array(&$Dataset));
	$Plot->setFillColor("#FF000");
break;
case 'pdcInProgressCountPast12Months':
	$Graph =& Image_Graph::factory('graph', array(760, 250));
	$Plotarea =& $Graph->addNew('plotarea');
	$Graph->add(Image_Graph::factory('title', array("In Progress PDC for Previous 12 Months, as of 15th of each month", 24)));

	$query =<<<EOF
		SELECT DATE_FORMAT(t1.date, '%Y-%m')  AS xval,
			(select count(*)
			from demerit as t2
			where ((CONCAT(xval,'-15') BETWEEN t2.date AND t2.date_closed )
			OR (CONCAT(xval,'-15') > t2.date AND t2.date_closed='0000-00-00'))
			AND t2.factory=t1.factory
			) as yval
            FROM demerit AS t1
            WHERE PERIOD_DIFF(DATE_FORMAT(NOW(), '%Y%m'),DATE_FORMAT(t1.date, '%Y%m')) < 12
	    	AND factory=$BUID
            GROUP BY xval
	    	order by xval
EOF;
	$rows = tldUtils::getSqlToAssocArray($query);
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
