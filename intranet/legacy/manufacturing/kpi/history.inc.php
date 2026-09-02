<?php

//initialize the array of option that will be use in the graphs
$options=array(
    "graphTitle"   =>"",
    "graphStyle"   =>"bar",
    "GPTargetVal"  =>"",
    "showMarkers"  =>"false"
);

$ds = $sess['mfg']['kpi']['period']['ds'];
$de = $sess['mfg']['kpi']['period']['de'];
// check the month difference between the start date and end date
// if difference superior to 12 month then setup the legend of the x axis to be vertical
list($de_y, $de_m) = explode("-", $de);
list($ds_y, $ds_m) = explode("-", $ds);
$d2=date(mktime(0, 0, 0, $de_m, 1, (int)$de_y));
$d1=date(mktime(0, 0, 0, $ds_m, 1, (int)$ds_y));
if((floor(($d2-$d1)/2628000))>12) $options["axisXAngle"]='vertical';

switch($m[1]){
    case 'CRABAvgPerTypeGTUnits':
        //initialize the array of option that will be use in this graph
        $options["graphTitle"]="Monthly average CRABS count, per type, for all GT units (on First GT date) for {$m[2]} from $ds to $de, ".$location['location'];
        $options["GPTargetVal"]=$help["CRAB Average Per Type For All GT Units Group Target"];
        $options["showMarkers"]="true";
        $options['customColor']="true";

        $C1 = $C2 = "";
        if($BUID !== "ALL"){
            $C1 = "AND s.man_location='".$location["location"]."'";
            $C2 = "AND s2.man_location='".$location["location"]."'";
        }
        $query=<<<EOF
SELECT
	c.opno AS zval,
	DATE_FORMAT(s.dgt_com, '%Y-%m') AS xval,
	ROUND(
		COUNT(*) /
		(
			SELECT
				COUNT(*)
			FROM
				service s2
			WHERE
				PERIOD_DIFF(DATE_FORMAT(NOW(), '%Y%m'), DATE_FORMAT(s2.dgt_com, '%Y%m')) = PERIOD_DIFF(DATE_FORMAT(NOW(), '%Y%m'), DATE_FORMAT(s.dgt_com, '%Y%m'))
				$C2
		)
	,1) AS yval
FROM
	service s
	LEFT JOIN crabs c ON c.erid=s.id
WHERE
DATE_FORMAT(s.dgt_com, '%Y%m') BETWEEN '$ds' AND '$de' AND
	c.opno<>''
	$C1
GROUP BY
	zval, xval
ORDER BY CASE
WHEN zval = 'QA'
THEN 1
WHEN zval = 'Test'
THEN 2
WHEN zval = 'Assy'
THEN 3
WHEN zval = 'PDI'
THEN 4
WHEN zval = 'PDI-SOL'
THEN 5
WHEN zval = 'PDI-CSC'
THEN 6
END, xval
EOF;
        // Ensure color will always be displayed in the same way
        $rows  = [];
        foreach (['QA', 'Test', 'Assy', 'PDI', 'PDI-SOL', 'PDI-CSC'] as $zval) {
            $rows[] = [
                'zval' => $zval,
                'xval' => $ds,
                'yval' => 0
            ];
        }

        $rows = array_merge($rows, tldUtils::getSqlToAssocArray($query));
        $Graph=_doBarGraph($rows,$options);
        break;
    case 'ITR':
        //initialize the array of option that will be use in this graph

        if($BUID !== "ALL"){
            $AND = "AND buid=$BUID";
        }

        $query=<<<EOF
SELECT
    locs.location as zval,
    ROUND((T1.erp_net_inv_val-T1.erp_net_wip_val)/AVG((SELECT sum(kpis.erp_tot_sls)
    FROM fin_kpi AS kpis
    WHERE kpis.buid=T1.buid
    AND PERIOD_DIFF(
    DATE_FORMAT(CONCAT(T1.ynam,'-',T1.mnam,'-01'), '%Y%m'),
    DATE_FORMAT(CONCAT(kpis.ynam,'-',kpis.mnam,'-01'), '%Y%m')
    ) between 0 and 2
    )
    ), 2)
    as yval,
    DATE_FORMAT(CONCAT(ynam,'-',mnam,'-01'), '%Y-%m') AS xval
FROM fin_kpi AS T1 LEFT JOIN locations AS locs ON T1.buid=locs.id
WHERE PERIOD_DIFF(DATE_FORMAT(NOW(), '%Y%m'), 
    DATE_FORMAT(CONCAT(ynam,'-',mnam,'-01'), '%Y%m') 
      ) <= 12 
$AND
GROUP BY zval, xval
EOF;
        $rows = tldUtils::getSqlToAssocArray($query);
        $Graph=_doBarGraph($rows,$options);
break;
case 'otdpVendors':
    $ds = "$ds-01";
    $end_date = new DateTime("$de-01");
    $end_date->modify('last day');
    $de = $end_date->format('Y-m-d');
    // Get KPI data
    $data = array();
    switch($m[2]){
    case 'reliability':
        $options["GPTargetVal"]=$help["OTDP Vendors {$m[2]} Target"];
        switch($m[3]){
        case 'byVendor':
            $options["graphTitle"]="Vendor OTDP {$m[2]} from $ds to $de with vendor#$suno";
            $options["showMarkers"]="true";
            $suno = TldDatabase::escape($_GET['suno']);
            if($BUID === "ALL"){
                foreach($FACTORIES as $erp=>$factory){
                    $data[$erp] = tldERPVendor::getOTDPByPeriodBySuno($erp,$ds,$de,$suno);
                }
            }else{
                $data[$erp] = tldERPVendor::getOTDPByPeriodBySuno($erp,$ds,$de,$suno);
            }
        break;
        case 'byPart':
            $options["graphTitle"]="Vendor OTDP {$m[2]} from $ds to $de for PN#$pn";
            $options["showMarkers"]="true";
            $pn = TldDatabase::escape($_GET['pn']);
            if($BUID === "ALL"){
                foreach($FACTORIES as $erp=>$factory){
                    $data[$erp] = tldERPVendor::getOTDPByPeriodByPart($erp,$ds,$de,$pn);
                }
            }else{
                $data[$erp] = tldERPVendor::getOTDPByPeriodByPart($erp,$ds,$de,$pn);
            }
        break;
        case 'byBuyer':
            $options["graphTitle"]="Vendor OTDP {$m[2]} from $ds to $de for $email";
            $options["showMarkers"]="true";
            $email = TldDatabase::escape($_GET['email']);
            if($BUID === "ALL"){
                foreach($FACTORIES as $erp=>$factory){
                    $data[$erp] = tldERPVendor::getOTDPByPeriodByBuyerEmail($erp,$ds,$de,$email);
                }
            }else{
                $data[$erp] = tldERPVendor::getOTDPByPeriodByBuyerEmail($erp,$ds,$de,$email);
            }
        break;
        default:
            $options["graphTitle"]="OTDP Vendor {$m[2]} from $ds to $de";
            $options["showMarkers"]="true";
            if($BUID === "ALL"){
                foreach($FACTORIES as $erp=>$factory){
                    $data[$erp] = tldERPVendor::getOTDPByPeriodByConstraints($erp,$ds,$de);
                }
            }else{
                $data[$erp] = tldERPVendor::getOTDPByPeriodByConstraints($erp,$ds,$de);
            }
        break;
        }
    break;
    }
    // Rearrange KPI data
    $rows = array();
    switch ($m[2] ?? null) {
        case 'reliability':
            foreach ($data as $erp => $kpidata) {
                foreach ($kpidata as $kpi) {
                    $rows[] = [
                        "xval" => $kpi['period'],
                        "yval" => $kpi[$m[2]],
                        "zval" => $FACTORIES[$erp],
                    ];
                }
            }
            break;
    }
    $Graph=_doBarGraph($rows,$options);
break;
case 'otdpPast12Months':
    //initialize the array of option that will be use in this graph
    $options["GPTargetVal"]=$help["OTDP Factory Group Target"];
    if($ds && $de){
        $WHERE = "WHERE DATE_FORMAT(t1.dgt_com,'%Y-%m') BETWEEN '$ds' AND '$de'";
        $options["graphTitle"]=str_replace(
    		array('%DS%','%DE%'),
    		array($ds,$de),
    		$translate->getDictionary('OTDP Factory on GT promise date from %DS% to %DE% (Percentage)')
    	);
    }else{
        $WHERE = "AND PERIOD_DIFF(DATE_FORMAT(NOW(),'%Y%m'),DATE_FORMAT(t1.dgt_com,'%Y%m')) <= 12";
        $options["graphTitle"]=str_replace(
    		array('%BU%'),
    		array($location['location']),
    		$translate->getDictionary('OTDP Factory on GT promise date Previous 12 Months %BU% (Percentage)')
    	);
    }

    $AND = "";
    if($BUID !== "ALL"){
        $AND = "AND t1.man_location='".$location["location"]."'";
    }

    $query=<<<EOF
SELECT
    t1.man_location as factories,
    DATE_FORMAT(t1.dgt_com, '%Y-%m') AS xval,
    ROUND(
        count(
            DATEDIFF(t1.dgt_com, t2.ddel_est1) <=0 or null
        )*100/count(*)
    ) as yval
FROM
    service as t1,
    sor_units as t2
    $WHERE
    $AND
    AND t1.sor_uid=t2.id
    AND t1.man_location<>''
GROUP BY
    factories, xval
EOF;
    $rows = tldUtils::getSqlToAssocArray($query);
    $Graph=_doLineGraph($rows,$options);
break;
case 'otdpAvgDaysLatePast12Months':
    //initialize the array of option that will be use in this graph
    $options["GPTargetVal"]=$help["OTDP Factory AVG days late Group Target"];

    if($ds && $de){
        $WHERE = "WHERE DATE_FORMAT(t1.dgt_com,'%Y-%m') BETWEEN '$ds' AND '$de'";
        $options["graphTitle"]=str_replace(
    		array('%DS%','%DE%'),
    		array($ds,$de),
    		$translate->getDictionary('OTDP Factory AVG Days Late from %DS% to %DE% (Days)')
    	);
    }else{
        $WHERE = "AND PERIOD_DIFF(DATE_FORMAT(NOW(),'%Y%m'),DATE_FORMAT(t1.dgt_com,'%Y%m')) <= 12";
        $options["graphTitle"]=str_replace(
    		array('%BU%'),
    		array($location['location']),
    		$translate->getDictionary('OTDP Factory AVG Days Late for Previous 12 Months %BU% (Days)')
    	);
    }

    $AND = "";
    if($BUID !== "ALL"){
        $AND = "AND t1.man_location='".$location["location"]."'";
    }

    $query=<<<EOF
SELECT
    t1.man_location as factories,
    DATE_FORMAT(t1.dgt_com, '%Y-%m') AS xval,
    ROUND(
        avg(
        CASE
            WHEN t1.dgt_com<>'0000-00-00' THEN
                if(DATEDIFF(t1.dgt_com, t2.ddel_est1) > 0,
                DATEDIFF(t1.dgt_com, t2.ddel_est1),
                0)
            ELSE
                DATEDIFF(NOW(), t2.ddel_est1)
        END
        )
    ) as yval
FROM
    service as t1,
    sor_units as t2
    $WHERE
    $AND
    AND t1.sor_uid=t2.id
    AND t1.man_location<>''
GROUP BY
    factories, xval
EOF;
    $rows = tldUtils::getSqlToAssocArray($query);
    $Graph = _doLineGraph($rows,$options);
break;
case 'cycleCountQuantityPerformancePast12Months':
    //initialize the array of option that will be use in this graph
    $options["GPTargetVal"]=$help["Cycle Count Quantity Group Target"];
    $options["showMarkers"]="true";

    if($ds && $de){
        $WHERE = "WHERE DATE_FORMAT(CONCAT(ynam,'-',mnam,'-01'), '%Y-%m') BETWEEN '$ds' AND '$de'";
        $options["graphTitle"]="Cycle Count Quantity absolute variance from $ds to $de (Percentage)";
    }else{
        $WHERE = "WHERE PERIOD_DIFF(DATE_FORMAT(NOW(), '%Y-%m'),
                DATE_FORMAT(CONCAT(ynam,'-',mnam,'-01'), '%Y-%m')) <= 12";
        $options["graphTitle"]="Cycle Count Quantity absolute variance for Previous 12 Months (Percentage)";
    }

    $AND = "";
    if($BUID !== "ALL"){
        $AND = "AND buid=$BUID";
    }

    $query = <<<EOF
        SELECT (SELECT location FROM locations AS T2 WHERE T2.id = T1.buid) as factories, ROUND(if(cc_qua=0, 0, cc_quv*100/cc_qua),2) as yval,
            DATE_FORMAT(CONCAT(ynam,'-',mnam,'-01'), '%Y-%m') AS xval
        FROM fin_kpi AS T1
         $WHERE
         $AND
        GROUP BY factories, xval
EOF;
    $rows = tldUtils::getSqlToAssocArray($query);
    $Graph=_doLineGraph($rows,$options);
break;
case 'cycleCountValuePerformancePast12Months':
    //initialize the array of option that will be use in this graph
    $options["GPTargetVal"]=$help["Cycle Count Value Group Target"];
    $options["showMarkers"]="true";

    if($ds && $de){
        $WHERE = "WHERE DATE_FORMAT(CONCAT(ynam,'-',mnam,'-01'), '%Y-%m') BETWEEN '$ds' AND '$de'";
        $options["graphTitle"]="Cycle Count Value variance from $ds to $de (Percentage)";
    }else{
        $WHERE = "WHERE PERIOD_DIFF(DATE_FORMAT(NOW(), '%Y-%m'),
                DATE_FORMAT(CONCAT(ynam,'-',mnam,'-01'), '%Y-%m')) <= 12";
        $options["graphTitle"]="Cycle Count Value variance for Previous 12 Months (Percentage)";
    }

    $AND = "";
    if($BUID !== "ALL"){
        $AND = "AND buid=$BUID";
    }

    $query = <<<EOF
SELECT
    (SELECT location
    FROM locations AS T2
    WHERE T2.id = T1.buid
    ) as factories,
    ROUND(if(cc_pric=0, 0, cc_priv*100/cc_pric),2) as yval,
    DATE_FORMAT(CONCAT(ynam,'-',mnam,'-01'), '%Y-%m') AS xval
FROM
    fin_kpi AS T1
    $WHERE
    $AND
GROUP BY
    factories, xval
EOF;
    $rows = tldUtils::getSqlToAssocArray($query);
    $Graph=_doLineGraph($rows,$options);
break;
case 'factoryProductiveHourRatioPast12Months':
    //initialize the array of option that will be use in this graph
    $options["GPTargetVal"]=$help["Factory Productive Hour Ratio Group Target"];
    $options["showMarkers"]="true";

    if($ds && $de){
        $WHERE = "WHERE DATE_FORMAT(CONCAT(ynam,'-',mnam,'-01'), '%Y-%m') BETWEEN '$ds' AND '$de'";
        $options["graphTitle"]="Factory Productive Hour Ratio (PHR) from $ds to $de (Percentage)";
    }else{
        $WHERE = "WHERE PERIOD_DIFF(DATE_FORMAT(NOW(), '%Y-%m'),
                DATE_FORMAT(CONCAT(ynam,'-',mnam,'-01'), '%Y-%m')) <= 12";
        $options["graphTitle"]="Factory Productive Hour Ratio (PHR) for Previous 12 Months (Percentage)";
    }

    $AND = "";
    if($BUID !== "ALL"){
        $AND = "AND buid=$BUID";
    }

    $query = <<<EOF
SELECT
    (SELECT location
    FROM locations AS T2
    WHERE T2.id = T1.buid
    ) as factories,
    ROUND(if(phr_poth=0, 0, phr_proh*100/phr_poth), 2) as yval,
    DATE_FORMAT(CONCAT(ynam,'-',mnam,'-01'), '%Y-%m') AS xval
FROM
    fin_kpi AS T1
    $WHERE
    $AND
GROUP BY
    factories, xval
EOF;
    $rows = tldUtils::getSqlToAssocArray($query);
    $Graph=_doLineGraph($rows,$options);
break;
case 'factoryStandardEfficiencyPast12Months':
    //initialize the array of option that will be use in this graph
    $options["GPTargetVal"]=$help["Factory Standard Efficiency Group Target"];
    $options["showMarkers"]="true";

    if($ds && $de){
        $WHERE = "WHERE DATE_FORMAT(CONCAT(ynam,'-',mnam,'-01'), '%Y-%m') BETWEEN '$ds' AND '$de'";
        $options["graphTitle"]="Factory Standard Efficiency (FSE) from $ds to $de (Percentage)";
    }else{
        $WHERE = "WHERE PERIOD_DIFF(DATE_FORMAT(NOW(), '%Y-%m'),
                DATE_FORMAT(CONCAT(ynam,'-',mnam,'-01'), '%Y-%m')) <= 12";
        $options["graphTitle"]="Factory Standard Efficiency (FSE) for Previous 12 Months (Percentage)";
    }

    $AND = "";
    if($BUID !== "ALL"){
        $AND = "AND buid=$BUID";
    }

    $query = <<<EOF
SELECT
    (SELECT location
    FROM locations AS T2
    WHERE T2.id = T1.buid
    ) as factories,
    ROUND(if(fse_acth=0, 0, fse_stdh*100/fse_acth),2) as yval,
    DATE_FORMAT(CONCAT(ynam,'-',mnam,'-01'), '%Y-%m') AS xval
FROM
    fin_kpi AS T1
    $WHERE
    $AND
GROUP BY
    factories, xval
EOF;
    $rows = tldUtils::getSqlToAssocArray($query);
    $Graph=_doLineGraph($rows,$options);
break;
case 'factoryProductivityPast12Months':
    //initialize the array of option that will be use in this graph
    $options["GPTargetVal"]=$help["Factory Productivity Group Target"];
    $options["showMarkers"]="true";

    if($ds && $de){
        $WHERE = "WHERE DATE_FORMAT(CONCAT(ynam,'-',mnam,'-01'), '%Y-%m') BETWEEN '$ds' AND '$de'";
        $options["graphTitle"]="Factory Productivity (FSE x PHR) from $ds to $de (Percentage)";
    }else{
        $WHERE = "WHERE PERIOD_DIFF(DATE_FORMAT(NOW(), '%Y-%m'),
                DATE_FORMAT(CONCAT(ynam,'-',mnam,'-01'), '%Y-%m')) <= 12";
        $options["graphTitle"]="Factory Productivity (FSE x PHR) for Previous 12 Months (Percentage)";
    }

    $AND = "";
    if($BUID !== "ALL"){
        $AND = "AND buid=$BUID";
    }

    $query = <<<EOF
SELECT
    (SELECT location FROM locations AS T2 WHERE T2.id = T1.buid) as factories,
    ROUND(
        if(fse_stdh=0 OR phr_poth=0, 0, 100*fse_stdh/fse_acth * phr_proh/phr_poth),
        2
    ) as yval,
    DATE_FORMAT(CONCAT(ynam,'-',mnam,'-01'), '%Y-%m') AS xval
FROM
    fin_kpi AS T1
    $WHERE
    $AND
GROUP BY
    factories, xval
EOF;
    $rows = tldUtils::getSqlToAssocArray($query);
    $Graph=_doLineGraph($rows,$options);
break;
case 'inventoryValuePast12Months':
    //initialize the array of option that will be use in this graph
    $options["GPTargetVal"]=$help["Inventory Value Group Target"];
    $options["showMarkers"]="true";

    if($ds && $de){
        $WHERE = "WHERE DATE_FORMAT(CONCAT(ynam,'-',mnam,'-01'), '%Y-%m') BETWEEN '$ds' AND '$de'";
        $options["graphTitle"]="Inventory Value from $ds to $de (Days of sales)";
    }else{
        $WHERE = "WHERE PERIOD_DIFF(DATE_FORMAT(NOW(), '%Y-%m'),
                DATE_FORMAT(CONCAT(ynam,'-',mnam,'-01'), '%Y-%m')) <= 12";
        $options["graphTitle"]="Inventory Value for Previous 12 Months (Days of sales)";
    }

    $AND = "";
    if($BUID !== "ALL"){
        $AND = "AND buid=$BUID";
    }

    $query = <<<EOF
SELECT
    locs.location as factories,
    ROUND(
        T1.erp_net_inv_val/
        (
        (SELECT sum(kpis.erp_tot_sls)
        FROM fin_kpi AS kpis
        WHERE kpis.buid=T1.buid
            AND PERIOD_DIFF(
        DATE_FORMAT(CONCAT(T1.ynam,'-',T1.mnam,'-01'), '%Y%m'),
        DATE_FORMAT(CONCAT(kpis.ynam,'-',kpis.mnam,'-01'), '%Y%m')
        ) between 0 and 2
        )/90
    ), 2)
    as yval,
    DATE_FORMAT(CONCAT(ynam,'-',mnam,'-01'), '%Y-%m') AS xval
FROM
    fin_kpi AS T1
    LEFT JOIN locations AS locs ON T1.buid=locs.id
    $WHERE
    $AND
GROUP BY
    factories, xval
EOF;
    $rows = tldUtils::getSqlToAssocArray($query);
    $Graph=_doLineGraph($rows,$options);
break;
case 'wipValuePast12Months':
    //initialize the array of option that will be use in this graph
    $options["GPTargetVal"]=$help["Work in Progress Value Group Target"];
    $options["showMarkers"]="true";

    if($ds && $de){
        $WHERE = "WHERE DATE_FORMAT(CONCAT(ynam,'-',mnam,'-01'), '%Y-%m') BETWEEN '$ds' AND '$de'";
        $options["graphTitle"]="Work in Progress Value from $ds to $de (in days of sales)";
    }else{
        $WHERE = "WHERE PERIOD_DIFF(DATE_FORMAT(NOW(), '%Y-%m'),
                DATE_FORMAT(CONCAT(ynam,'-',mnam,'-01'), '%Y-%m')) <= 12";
        $options["graphTitle"]="Work in Progress Value for Previous 12 Months (in days of sales)";
    }

    $AND = "";
    if($BUID !== "ALL"){
        $AND = "AND buid=$BUID";
    }

    $query = <<<EOF
SELECT
    locs.location as factories,
    ROUND(T1.erp_net_wip_val/
        (
            (SELECT sum(kpis.erp_tot_sls)
            FROM fin_kpi AS kpis
            WHERE kpis.buid=T1.buid
            AND PERIOD_DIFF(
                DATE_FORMAT(CONCAT(T1.ynam,'-',T1.mnam,'-01'), '%Y%m'),
                DATE_FORMAT(CONCAT(kpis.ynam,'-',kpis.mnam,'-01'), '%Y%m')
                ) between 0 and 2
            )/90
        ),
    2) as yval,
    DATE_FORMAT(CONCAT(ynam,'-',mnam,'-01'), '%Y-%m') AS xval
FROM
    fin_kpi AS T1
    LEFT JOIN locations AS locs ON T1.buid=locs.id
$WHERE
    $AND
GROUP BY
    factories, xval
EOF;
    $rows = tldUtils::getSqlToAssocArray($query);
    $Graph=_doLineGraph($rows,$options);
break;
case 'internalCustomerSatisfactionPast12Months':
    if($ds && $de){
        $WHERE = "WHERE DATE_FORMAT(CONCAT(ynam,'-',mnam,'-01'), '%Y-%m') BETWEEN '$ds' AND '$de'";
        $options["graphTitle"]="Internal Customer Satisfaction from $ds to $de, by SSO($BUID)";
    }else{
        $WHERE = "WHERE PERIOD_DIFF(DATE_FORMAT(NOW(), '%Y%m'),
                DATE_FORMAT(CONCAT(ynam,'-',mnam,'-01'), '%Y%m')) <= 12";
        $options["graphTitle"]="Internal Customer Satisfaction for Previous 12 Months by SSO($BUID)";
    }

    if($BUID === "ame" || $BUID === "asi" || $BUID === "eur"){
        //initialize the array of option that will be use in this graph
        $options["showMarkers"]="true";
        $options["graphStyle"]="bar";

        $query = <<<EOF
SELECT
    (SELECT location FROM locations AS T2 WHERE T2.id = T1.buid
    ) as factories,
    ics_$BUID as yval,
    DATE_FORMAT(CONCAT(ynam,'-',mnam,'-01'), '%Y-%m') AS xval
FROM
    fin_kpi AS T1
$WHERE
ORDER BY
    factories, xval
EOF;
        $rows = tldUtils::getSqlToAssocArray($query);
        $Graph=_doBarGraph($rows,$options);
    }elseif($BUID !== "ALL"){
        $Graph =& Image_Graph::factory('graph', array(760, 500));
        $Plotarea = Image_Graph::factory('plotarea');
        $Legend = Image_Graph::factory('legend');

        $Graph->add(
            Image_Graph::vertical(
                Image_Graph::factory('title', array($options["graphTitle"], 12)),
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

        // add font
        $Font =& $Graph->addNew('ttf_font', 'DejaVuLGCSans-Bold.ttf');
        // set the font size to 11 pixels
        $Font->setSize(6);
        $Graph->setFont($Font);

        // create a grid and assign it to the secondary Y axis
        $GridY2 =& $Plotarea->addNew('line_grid', IMAGE_GRAPH_AXIS_Y);

        $AND = "";
        if($BUID !== "ALL"){
            $AND = "AND buid=$BUID";
        }

        $query = <<<EOF
SELECT
    *,
    DATE_FORMAT(CONCAT(ynam,'-',mnam,'-01'), '%Y-%m') AS xval
FROM
    fin_kpi AS T1
$WHERE
    $AND
ORDER BY
    ynam, mnam
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
            $ameDataset->addPoint(
                $row["xval"],
                $row["ics_ame"],
                'ame'
            );
            $asiDataset->addPoint(
                $row["xval"],
                $row["ics_asi"],
                'asi'
            );
            $eurDataset->addPoint(
                $row["xval"],
                $row["ics_eur"],
                'eur'
            );
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
        $Plot->setDataSelector(
            Image_Graph::factory('Image_Graph_DataSelector_NoZeros')
        );

        if($options["axisXAngle"]){
            $AxisX =& $Plotarea->getAxis(IMAGE_GRAPH_AXIS_X);
            $AxisX->setFontAngle($options["axisXAngle"]);
            $AxisX->setLabelOption('offset', 35);
        }
    }
break;
case 'countCurrentYearByFactory':
    //initialize the array of option that will be use in this graph
    $options["GPTargetVal"]=$help["Warranty Claim Counts Group Target"];
    $options["showMarkers"]="true";
    $options["graphStyle"]="bar";

    if($ds && $de){
        $WHERE = "WHERE DATE_FORMAT(claim_date, '%Y-%m') BETWEEN '$ds' AND '$de'";
        $options["graphTitle"]="Monthly Warranty Count from $ds to $de";
    }else{
        $WHERE = "WHERE PERIOD_DIFF(DATE_FORMAT(NOW(), '%Y%m'), DATE_FORMAT(claim_date, '%Y%m')) < 12";
        $options["graphTitle"]="Monthly Warranty Count for Previous 12 Months";
    }

    if($BUID !== "ALL"){
        $AND = "AND man_location='".$location['location']."'";
        $query = <<<EOF
SELECT
    type as factories,
    DATE_FORMAT(claim_date, '%Y-%m') AS xval,
    COUNT(*) AS yval
FROM
    warranty
$WHERE
    $AND
GROUP BY
    factories , xval
EOF;
    }else{
        $query = <<<EOF
SELECT
    man_location as factories,
    DATE_FORMAT(claim_date, '%Y-%m') AS xval,
    COUNT(*) AS yval
FROM
    warranty
$WHERE
GROUP BY
    factories, xval
EOF;
    }
    $rows = tldUtils::getSqlToAssocArray($query);
    $Graph=_doBarGraph($rows,$options);
break;
case 'pdcOpenedPast12Months':
    //initialize the array of option that will be use in this graph
    $options["GPTargetVal"]=$help["PDC Opened During the Month Group Target"];
    $options["showMarkers"]="true";

    if($ds && $de){
        $WHERE = "WHERE DATE_FORMAT(T1.date, '%Y-%m') BETWEEN '$ds' AND '$de'";
        $options["graphTitle"]="Opened PDC from $ds to $de";
    }else{
        $WHERE = "WHERE PERIOD_DIFF(DATE_FORMAT(NOW(), '%Y%m'), DATE_FORMAT(T1.date, '%Y%m')) < 12";
        $options["graphTitle"]="Opened PDC for Previous 12 Months";
    }

    $AND = "";
    if($BUID !== "ALL"){
        $AND = " AND T1.factory='$BUID'";
    }

    $query =<<<EOF
SELECT
    T2.location AS factories,
    date_format(date, '%Y-%m') AS xval,
    COUNT(*) AS yval
FROM
    demerit AS T1
    LEFT JOIN locations AS T2 ON T1.factory=T2.id
$WHERE
    $AND
GROUP BY
    factories, xval
EOF;
    $rows = tldUtils::getSqlToAssocArray($query);
    $Graph = _doLineGraph($rows,$options);
break;
case 'historyByFactory':
    //initialize the array of option that will be use in this graph
    //$options["GPTargetVal"]=$help["PDC Opened During the Month Group Target"];
    $options["showMarkers"]="true";

    if($ds && $de){
        $WHERE = "WHERE DATE_FORMAT(T1.date, '%Y-%m') BETWEEN '$ds' AND '$de'";
        $options["graphTitle"]="Average Monthly Fweight over Time from $ds to $de";
    }else{
        $WHERE = "WHERE PERIOD_DIFF(DATE_FORMAT(NOW(), '%Y%m'), DATE_FORMAT(T1.date, '%Y%m')) < 12";
        $options["graphTitle"]="Average Monthly Fweight over Time Previous 12 Months";
    }

    $AND = "";
    if($BUID !== "ALL"){
        $AND = " AND locations.erp=$erp";
    }

    $query =<<<EOF
SELECT
    locations.location as factories,
    DATE_FORMAT(T1.date, '%Y-%m') AS xval,
    ROUND(SUM(fweight)/COUNT(DISTINCT T1.date),0) as yval
FROM
    demerit_history AS T1
    LEFT JOIN demerit ON demerit.id=T1.parent_id
    LEFT JOIN locations ON locations.id=demerit.factory
$WHERE
    $AND
GROUP BY
    factories, xval
EOF;
    $rows = tldUtils::getSqlToAssocArray($query);
    $Graph = _doLineGraph($rows,$options);
break;
case 'pdcInProgressCountPast12Months':
    //initialize the array of option that will be use in this graph
    $options["GPTargetVal"]=$help["PDC in Progress Group Target"];
    $options["showMarkers"]="true";

    if($ds && $de){
        $WHERE = "WHERE t1.list_key2 BETWEEN '$ds' AND '$de'";
        $options["graphTitle"]="In Progress PDC from $ds to $de, as of 15th of each month";
    }else{
        $WHERE = "WHERE PERIOD_DIFF(DATE_FORMAT(NOW(), '%Y%m'),DATE_FORMAT(str_to_date(t1.list_key2,'%Y-%m'), '%Y%m')) < 12";
        $options["graphTitle"]="In Progress PDC for Previous 12 Months, as of 15th of each month";
    }

    $AND = "";
    if($BUID !== "ALL"){
        $AND = "AND list_key=$BUID";
    }
    // new query to get the PDC in Progress as of the 15 of each month and for the TABLE MOD_LISTS
    $query =<<<EOF
SELECT
    t2.location AS factories,
    list_key2 as xval,
    value as yval
FROM
    mod_lists AS t1
    LEFT JOIN locations AS t2 ON t1.list_key=t2.id
$WHERE
    AND module ='PDC'
    $AND
    AND list_name='INPROGRESS'
GROUP BY
    factories, xval
EOF;
    $rows = tldUtils::getSqlToAssocArray($query);
    $Graph = _doLineGraph($rows,$options);
break;
case 'DOPOPast12Months':
    //initialize the array of option that will be use in this graph
    $options["GPTargetVal"]=$help["Days of Purchase Out Group Target"];
    $options["showMarkers"]="true";

    if($ds && $de){
        $WHERE = "WHERE DATE_FORMAT(CONCAT(ynam,'-',mnam,'-01'), '%Y-%m') BETWEEN '$ds' AND '$de'";
        $options["graphTitle"]="Days of Purchase Out from $ds to $de";
    }else{
        $WHERE = "WHERE PERIOD_DIFF(DATE_FORMAT(NOW(), '%Y-%m'),
                DATE_FORMAT(CONCAT(ynam,'-',mnam,'-01'), '%Y-%m')) <= 12";
        $options["graphTitle"]="Days of Purchase Out for Previous 12 Months";
    }

    $AND = "";
    if($BUID !== "ALL"){
        $AND = "AND buid=$BUID";
    }

    $query = <<<EOF
SELECT
    locs.location as factories,
    ROUND(
        T1.erp_tot_ap/
        (
            (SELECT sum(kpis.erp_tot_pur)
            FROM fin_kpi AS kpis
            WHERE kpis.buid=T1.buid
            AND PERIOD_DIFF(
                DATE_FORMAT(CONCAT(T1.ynam,'-',T1.mnam,'-01'), '%Y%m'),
                DATE_FORMAT(CONCAT(kpis.ynam,'-',kpis.mnam,'-01'), '%Y%m')
                ) between 0 and 2
            )/90
        ),
        2
    )as yval,
    DATE_FORMAT(CONCAT(ynam,'-',mnam,'-01'), '%Y-%m') AS xval
FROM
    fin_kpi AS T1
    LEFT JOIN locations AS locs ON T1.buid=locs.id
$WHERE
    $AND
GROUP BY
    factories, xval
EOF;
    $rows = tldUtils::getSqlToAssocArray($query);
    $Graph=_doLineGraph($rows,$options);
break;
case 'MTBF_byFactory':
    $options["GPTargetVal"]=$help["MTBF target"];
    $options["graphStyle"]="bar";
    $options["showMarkers"]="true";
    $options["graphTitle"]="WC - Mean time between failures, shipped < 2 years, history KPI";
    // Create conditions
    if($buid !== "ALL" && is_numeric($buid)){
        $bu = new tldLocation($buid);
        $options["graphTitle"].=" - {$bu->itsDetails['location']}";
    }
    // Initialise kpi data
    $data = array();
    // Get window
    $query = <<<EOF
SELECT nam_period AS xval
FROM fin_periods AS p
WHERE
    PERIOD_DIFF( DATE_FORMAT(CONCAT('$de','-01'),'%Y%m'), p.nam_period )>=0
    AND PERIOD_DIFF( DATE_FORMAT(CONCAT('$ds','-01'),'%Y%m'), p.nam_period )<=0
ORDER BY nam_period
EOF;
    $graph_period = tldUtils::getSqlToAssocArray($query);
    // Foreach months, calculate KPI
    foreach($graph_period as $period){
        $period_Ym = $period['xval'];
        $period_Ymd = substr($period['xval'],0,4)."-".substr($period['xval'],-2,2)."-01";

        if($buid !== "ALL"){
            $WHERE = "er.man_location='{$bu->itsDetails['location']}'";
            $rows = tldWC::getMTBFPeriodByConstraints($period_Ymd,$WHERE);
            $data[]=array(
                "xval"=>$rows['period'],
                "yval"=>$rows['val'],
                "zval"=>$bu->itsDetails['location']
            );
        }else{
            foreach(tldLocation::getERPList("smartyOptionsIDLocation") as $lid=>$location){
                $WHERE = "er.man_location='$location'";
                $rows = tldWC::getMTBFPeriodByConstraints($period_Ymd,$WHERE);
                $data[]=array(
                    "xval"=>$rows['period'],
                    "yval"=>$rows['val'],
                    "zval"=>$location
                );
            }
        }
    }
    $Graph=_doBarGraph($data,$options);
break;
case 'AVGTMY_byFactory':
    $options["GPTargetVal"]=$help["AVGTMY target"];
    $options["graphStyle"]="bar";
    $options["showMarkers"]="true";
    $options["graphTitle"]="WC - Average of Time of WC per Machine per Year, shipped < 2 years, history KPI";
    // Create conditions
    if($buid !== "ALL" && is_numeric($buid)){
        $bu = new tldLocation($buid);
        $options["graphTitle"].=" - {$bu->itsDetails['location']}";
    }
    // Initialise kpi data
    $data = array();
    // Get window
    $query = <<<EOF
SELECT nam_period AS xval
FROM fin_periods AS p
WHERE
    PERIOD_DIFF( DATE_FORMAT(CONCAT('$de','-01'),'%Y%m'), p.nam_period )>=0
    AND PERIOD_DIFF( DATE_FORMAT(CONCAT('$ds','-01'),'%Y%m'), p.nam_period )<=0
ORDER BY nam_period
EOF;
    $graph_period = tldUtils::getSqlToAssocArray($query);
    // Foreach months, calculate KPI
    foreach($graph_period as $period){
        $period_Ym = $period['xval'];
        $period_Ymd = substr($period['xval'],0,4)."-".substr($period['xval'],-2,2)."-01";

        if($buid !== "ALL"){
            $WHERE = "er.man_location='{$bu->itsDetails['location']}'";
            $rows = tldWC::getAVGTMYPeriodByConstraints($period_Ymd,$WHERE);
            $data[]=array(
                "xval"=>$rows['period'],
                "yval"=>$rows['val'],
                "zval"=>$bu->itsDetails['location']
            );
        }else{
            foreach(tldLocation::getERPList("smartyOptionsIDLocation") as $lid=>$location){
                $WHERE = "er.man_location='$location'";
                $rows = tldWC::getAVGTMYPeriodByConstraints($period_Ymd,$WHERE);
                $data[]=array(
                    "xval"=>$rows['period'],
                    "yval"=>$rows['val'],
                    "zval"=>$location
                );
            }
        }
    }
    $Graph=_doBarGraph($data,$options);
break;
case 'ATFF_byFactory':
    $options["graphStyle"]="bar";
    $options["showMarkers"]="true";
    $options["graphTitle"]="WC - Average Time at First Failure, shipped < 2 years, history KPI";
    // Create conditions
    if($buid !== "ALL" && is_numeric($buid)){
        $bu = new tldLocation($buid);
        $options["graphTitle"].=" - {$bu->itsDetails['location']}";
    }
    // Initialise kpi data
    $data = array();
    // Get window
    $query = <<<EOF
SELECT nam_period AS xval
FROM fin_periods AS p
WHERE
    PERIOD_DIFF( DATE_FORMAT(CONCAT('$de','-01'),'%Y%m'), p.nam_period )>=0
    AND PERIOD_DIFF( DATE_FORMAT(CONCAT('$ds','-01'),'%Y%m'), p.nam_period )<=0
ORDER BY nam_period
EOF;
    $graph_period = tldUtils::getSqlToAssocArray($query);
    // Foreach months, calculate KPI
    foreach($graph_period as $period){
        $period_Ym = $period['xval'];
        $period_Ymd = substr($period['xval'],0,4)."-".substr($period['xval'],-2,2)."-01";

        if($buid !== "ALL"){
            $WHERE = "er.man_location='{$bu->itsDetails['location']}'";
            $rows = tldWC::getATFFPeriodByConstraints($period_Ymd,$WHERE);
            $data[]=array(
                "xval"=>$rows['period'],
                "yval"=>$rows['val'],
                "zval"=>$bu->itsDetails['location']
            );
        }else{
            foreach(tldLocation::getERPList("smartyOptionsIDLocation") as $lid=>$location){
                $WHERE = "er.man_location='$location'";
                $rows = tldWC::getATFFPeriodByConstraints($period_Ymd,$WHERE);
                $data[]=array(
                    "xval"=>$rows['period'],
                    "yval"=>$rows['val'],
                    "zval"=>$location
                );
            }
        }
    }
    $Graph=_doBarGraph($data,$options);
break;
case 'AVGWC_byFactory':
	$options["GPTargetVal"]=$help["AVGWC target"];
	$options["graphStyle"]="bar";
	$options["showMarkers"]="true";
	$options["graphTitle"]="WC - Average number of WC per Machine, shipped < 2 years, for Previous 12 Months";
	// Create conditions
	if($buid !== "ALL" && is_numeric($buid)){
		$bu = new tldLocation($buid);
		$options["graphTitle"].=" - {$bu->itsDetails['location']}";
	}
	// Initialise kpi data
	$data = array();
	// Get 12 months windows
    $query = <<<EOF
SELECT nam_period AS xval
FROM fin_periods AS p
WHERE
    PERIOD_DIFF( DATE_FORMAT(CONCAT('$de','-01'),'%Y%m'), p.nam_period )>=0
    AND PERIOD_DIFF( DATE_FORMAT(CONCAT('$ds','-01'),'%Y%m'), p.nam_period )<=0
ORDER BY nam_period
EOF;
	$graph_period = tldUtils::getSqlToAssocArray($query);
	// Foreach months, calculate KPI
	foreach($graph_period as $period){
		$period_Ym = $period['xval'];
		$period_Ymd = substr($period['xval'],0,4)."-".substr($period['xval'],-2,2)."-01";

		if($buid !== "ALL"){
			$WHERE = "er.man_location='{$bu->itsDetails['location']}'";
			$rows = tldWC::getAVGWCPeriodByConstraints($period_Ymd,$WHERE);
			$data[]=array(
					"xval"=>$rows['period'],
					"yval"=>$rows['val'],
					"zval"=>$bu->itsDetails['location']
			);
		}else{
			foreach($FACTORIES as $erp=>$location){
                if ($location === 'TLD DTV') {
                    continue;
                }
				$WHERE = "er.man_location='$location'";
				$rows = tldWC::getAVGWCPeriodByConstraints($period_Ymd,$WHERE);
				$data[]=array(
						"xval"=>$rows['period'],
						"yval"=>$rows['val'],
						"zval"=>$location
				);
			}
		}
	}
	$Graph=_doBarGraph($data,$options);
break;
case 'MNOWC3_byFactory':
    $options["GPTargetVal"]=$help["MNOWC3 target"];
    $options["graphStyle"]="bar";
    $options["showMarkers"]="true";
    $options["graphTitle"]="WC - % of Machines with NO WC in the first 3 months, shipped < 2 years, for Previous 12 Months";
    // Create conditions
    if($buid !== "ALL" && is_numeric($buid)){
        $bu = new tldLocation($buid);
        $options["graphTitle"].=" - {$bu->itsDetails['location']}";
    }
    // Initialise kpi data
    $data = array();
    // Get window
    $query = <<<EOF
SELECT nam_period AS xval
FROM fin_periods AS p
WHERE
    PERIOD_DIFF( DATE_FORMAT(CONCAT('$de','-01'),'%Y%m'), p.nam_period )>=0
    AND PERIOD_DIFF( DATE_FORMAT(CONCAT('$ds','-01'),'%Y%m'), p.nam_period )<=0
ORDER BY nam_period
EOF;
    $graph_period = tldUtils::getSqlToAssocArray($query);
    // Foreach months, calculate KPI
    foreach($graph_period as $period){
        $period_Ym = $period['xval'];
        $period_Ymd = substr($period['xval'],0,4)."-".substr($period['xval'],-2,2)."-01";

        if($buid !== "ALL"){
            $WHERE = "er.man_location='{$bu->itsDetails['location']}'";
            $rows = tldWC::getMNOWC3PeriodByConstraints($period_Ymd,$WHERE);
            $data[]=array(
                "xval"=>$rows['period'],
                "yval"=>$rows['val'],
                "zval"=>$bu->itsDetails['location']
            );
        }else{
            foreach(tldLocation::getERPList("smartyOptionsIDLocation") as $lid=>$location){
                $WHERE = "er.man_location='$location'";
                $rows = tldWC::getMNOWC3PeriodByConstraints($period_Ymd,$WHERE);
                $data[]=array(
                    "xval"=>$rows['period'],
                    "yval"=>$rows['val'],
                    "zval"=>$location
                );
            }
        }
    }
    $Graph=_doBarGraph($data,$options);
break;

}
