<?php
include_once('Image/Graph.php');
include_once("pChart/pChart/pChart.class");
$JS_INCLUDE=array("/shared/javascript/overlib/overlib.js");
$smarty->assign("js_includes",$JS_INCLUDE);

switch($m[2]){
case 'inventoryKPIValue':
    if(empty($factory)){
        $DEFAULT_ERROR[] = "ERROR: Date or Factory missing from Step 1";
        break;
    }
    //Check and transform date received from Form
    $date_query = date("Y");

    $query2 = <<<EOF

SELECT DATE_FORMAT(CONCAT(T1.ynam,'-',T1.mnam,'-01'), '%Y%m') AS nam_period, erp_net_wip_val AS net_wip_val
FROM fin_kpi AS T1
WHERE PERIOD_DIFF(
		DATE_FORMAT(NOW(), '%Y%m'),
		DATE_FORMAT(CONCAT(ynam,'-',mnam,'-01'), '%Y%m')
	) <= 12
AND buid = $factory
GROUP BY nam_period
EOF;

    $query3 = <<<EOF

SELECT DATE_FORMAT(CONCAT(T1.ynam,'-',T1.mnam,'-01'), '%Y%m') AS nam_period, erp_net_inv_val AS net_inv_val
FROM fin_kpi AS T1
WHERE PERIOD_DIFF(
		DATE_FORMAT(NOW(), '%Y%m'),
		DATE_FORMAT(CONCAT(T1.ynam,'-',T1.mnam,'-01'), '%Y%m')
	) <= 12
AND buid = $factory
GROUP BY nam_period
EOF;

    $query4 = <<<EOF

SELECT DATE_FORMAT(CONCAT(T1.ynam,'-',T1.mnam,'-01'), '%Y%m') AS nam_period, erp_net_raw_material_val AS net_raw_material_val
FROM fin_kpi AS T1
WHERE PERIOD_DIFF(
		DATE_FORMAT(NOW(), '%Y%m'),
		DATE_FORMAT(CONCAT(T1.ynam,'-',T1.mnam,'-01'), '%Y%m')
	) <= 12
AND buid = $factory
GROUP BY nam_period
EOF;

    $query5 = <<<EOF

SELECT DATE_FORMAT(CONCAT(T1.ynam,'-',T1.mnam,'-01'), '%Y%m') AS nam_period, (erp_net_inv_val-erp_net_raw_material_val-erp_net_wip_val) AS finish_goods_val
FROM fin_kpi AS T1
WHERE PERIOD_DIFF(
		DATE_FORMAT(NOW(), '%Y%m'),
		DATE_FORMAT(CONCAT(T1.ynam,'-',T1.mnam,'-01'), '%Y%m')
	) <= 12
AND buid = $factory
GROUP BY nam_period
EOF;

    if(in_array($factory, array(9,21))){$unit= "(in K, EUR)";}
    if(in_array($factory, array(4,32))){$unit= "(in K, RMB)";}
    $rows2 = tldUtils::getSqlToAssocArray($query2);
    $rows3 = tldUtils::getSqlToAssocArray($query3);
    $rows4 = tldUtils::getSqlToAssocArray($query4);
    $rows5 = tldUtils::getSqlToAssocArray($query5);
    $dataDescription["Values"][] = "Finish Goods Value";
    $dataDescription["Description"]["Finish Goods Value"] = "Finish Goods Value ";
    $dataDescription["Values"][] = "WIP Value";
    $dataDescription["Description"]["WIP Value"] = "WIP Value ";
    $dataDescription["Values"][] = "Raw Material Value";
    $dataDescription["Description"]["Raw Material Value"] = "Raw Material Value ";
    $dataDescription["Values"][] = "Inventory Value";
    $dataDescription["Description"]["Inventory Value"] = "Inventory Value ";
    $cur=1;
    //Cumulative sum of GT per day
    foreach($rows5 as $row){
        $ydm = $row['nam_period'];
        $d[$ydm] = array("Name" => $ydm);
        $x[$ydm] = array("Name" => $ydm);
        $finish_goods_val = round($row['finish_goods_val'] ,0);
        $d[$row['nam_period']]["Finish Goods Value"] = $finish_goods_val;
        $x[$row['nam_period']]["Finish Goods Value"] = $finish_goods_val;
        foreach($d as $key=>$name){
            if($key > $row['nam_period']){
                $d[$key]["Finish Goods Value"] = $finish_goods_val;
            }
        }
    }
    foreach($rows4 as $row){

        $net_raw_material_val = round($row['net_raw_material_val'] ,0);
        $d[$row['nam_period']]["Raw Material Value"] = $net_raw_material_val;
        $x[$row['nam_period']]["Raw Material Value"] = $net_raw_material_val;
        foreach($d as $key=>$name){
            if($key > $row['nam_period']){
                $d[$key]["Raw Material Value"] = $net_raw_material_val;
            }
        }
    }
    foreach($rows3 as $row){

        $net_inv_val = round($row['net_inv_val'] ,0);
        $d[$row['nam_period']]["Inventory Value"] = $net_inv_val;
        $x[$row['nam_period']]["Inventory Value"] = $net_inv_val;
        foreach($d as $key=>$name){
            if($key > $row['nam_period']){
                $d[$key]["Inventory Value"] = $net_inv_val;
            }
        }
    }
    foreach($rows2 as $row){

        $net_wip_val = round($row['net_wip_val'] ,0);
        $d[$row['nam_period']]["WIP Value"] = $net_wip_val;
        $x[$row['nam_period']]["WIP Value"] = $net_wip_val;
        foreach($d as $key=>$name){
            if($key > $row['nam_period']){
                $d[$key]["WIP Value"] = $net_wip_val;
            }
        }
    }

    $chart_width = 1000;
    $chart_height = 500;
    $chart_legend_width = 100;
    $chart = new pChart($chart_width, $chart_height);
    $rgb = array_values(tldUtils::getColorNamesTPGTGraphs());
    foreach ($dataDescription['Values'] as $k => $v) {
        //filter out very light colors
        if (array_product($rgb[$k]) < 200 * 200 * 200) {
            $chart->setColorPalette(
                $k,
                $rgb[$k][0],
                $rgb[$k][1],
                $rgb[$k][2]
            );
        }
    }
    $data = array_values($d);
    $dataValue = array_values($x);
    $dataDescription["Position"] = "Name";
    $chart->setFontProperties("$SHARED_LIB_PATH/src/pChart/Fonts/tahoma.ttf", 8);
    $chart->setGraphArea(65, 40, $chart_width - $chart_legend_width-20, $chart_height * 0.9);
    $chart->drawGraphArea(255, 255, 255, TRUE);
    $chart->drawScale(
        $data,
        $dataDescription,
        SCALE_START0,
        150, 150, 150, TRUE, 0, 2, TRUE
    );
    $chart->drawGrid(4, TRUE, 230, 230, 230, 50);

    // Draw the 0 line
    $chart->setFontProperties("$SHARED_LIB_PATH/src/pChart/Fonts/tahoma.ttf", 6);
    $chart->drawTreshold(0, 143, 55, 72, TRUE, TRUE);
    $chart->writeValues($dataValue, $dataDescription, $dataDescription["Values"]);
    // Draw the bar graph
    $chart->drawLineGraph(
        $data,
        $dataDescription,
        TRUE
    );

    $chart->setFontProperties("$SHARED_LIB_PATH/src/pChart/Fonts/tahoma.ttf", 8);
    $chart->drawLegend(
        $chart_width - $chart_legend_width-20, 0,
        $dataDescription,
        255, 255, 255
    );
    $chart->setFontProperties("$SHARED_LIB_PATH/src/pChart/Fonts/tahoma.ttf", 12);
    $chart->drawTitle(
        20, 22,
        "Graph (Value) for Pervious 12 Months  - ".tldLocation::getLocationByID($factory)." ".$unit,
        "", "", ""
    );

    $chart->Stroke();
exit;

    break;

}


?>
