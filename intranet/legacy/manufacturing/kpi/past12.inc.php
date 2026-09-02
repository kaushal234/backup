<?php

use ApiBundle\Client;
use Symfony\Component\HttpClient\Exception\ClientException;

$options=array(
    "graphTitle"   =>"",
    "graphStyle"   =>"bar",
    "GPTargetVal"  =>"",
    "showMarkers"  =>"false"
);

switch($m[1]){
    case 'dmsKPI':
        // Calculate dates for previous 12 months
        $date = date('Y-m') . "-01";
        $start = new DateTime($date);
        $start->sub(new DateInterval('P12M'));
        $ds = $start->format('Y-m-d');
        $de = date('Y-m-d');
        $data = [];
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
        $factory = tldLocation::getLocationByID($buid);
        // Foreach months, calculate KPI
        foreach ($graph_period as $period) {
            $period_Ym = $period['xval'];
            $period_Ymd = substr($period['xval'], 0, 4) . "-" . substr($period['xval'], -2, 2) . "-01";
            $date = new DateTime($period_Ymd);
            $period = $date->format('Y-m-d');
            $query = <<<EOF

SELECT count(*) as val
FROM (
SELECT *
FROM mod_logs
WHERE module = 'DMS' and DATEDIFF(LAST_DAY('$period'),date)>=0
GROUP BY `parent_id`
ORDER BY `date` DESC
) AS b
inner JOIN dms AS c ON b.parent_id = c.id
inner JOIN people ON people.id=c.owner_id
WHERE c.status LIKE 'EXPIRED'
AND people.bu_id=$buid AND DATEDIFF(LAST_DAY('$period'),DATE_ADD(c.dt_act, INTERVAL c.periodicity MONTH))>=0
EOF;

            $row = tldUtils::getSqlRowToAssocArray($query);
            $data[] = [
                "xval" => substr($period, 0, -3),
                "yval" => $row['val'],
                "zval" => $factory,
            ];
        }
        // Get KPI data
        $options["GPTargetVal"] = $help["Monthly expired documents"];
        $options["graphTitle"] = "DMS - Monthly count of expired documents - $factory";
        $Graph = _doBarGraph($data, $options);
        break;
    case 'dmsAVGKPI':
        // Calculate dates for previous 12 months
        $date = date('Y-m') . "-01";
        $start = new DateTime($date);
        $start->sub(new DateInterval('P12M'));
        $ds = $start->format('Y-m-d');
        $de = date('Y-m-d');
        $data = [];
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
        $factory = tldLocation::getLocationByID($buid);
        // Foreach months, calculate KPI
        foreach ($graph_period as $period) {
            $period_Ym = $period['xval'];
            $period_Ymd = substr($period['xval'], 0, 4) . "-" . substr($period['xval'], -2, 2) . "-01";
            $date = new DateTime($period_Ymd);
            $period = $date->format('Y-m-d');
            $factory = tldLocation::getLocationByID($buid);
            $options["GPTargetVal"] = $help["Monthly average days in revision"];
            $options["graphTitle"] = "DMS - Monthly count of average days in revision status - $factory";
            $query = <<<EOF
SELECT IF(ROUND(AVG(
        CASE
            WHEN c.status ='REVISION' THEN
                DATEDIFF('$period',b.date)
            END
            )
        ) is NULL,0, ROUND(AVG(
        CASE
            WHEN c.status ='REVISION' THEN
                DATEDIFF('$period',b.date)
            END
            )
        )) as val
FROM (

SELECT *
FROM (

SELECT *
FROM mod_logs
WHERE module = 'DMS'
ORDER BY `date` DESC
) AS a
GROUP BY `parent_id`
ORDER BY `date` DESC
) AS b
LEFT JOIN dms AS c ON b.parent_id = c.id
LEFT JOIN people ON people.id=c.owner_id
WHERE (c.status LIKE 'REVISION'
AND DATEDIFF(LAST_DAY('$period'),b.date)>=0) AND people.bu_id =$buid

EOF;

            $row = tldUtils::getSqlRowToAssocArray($query);
            $data[] = [
                "xval" => substr($period, 0, -3),
                "yval" => $row['val'],
                "zval" => $factory,
            ];
        }
        $Graph = _doBarGraph($data, $options);
        break;
    case 'otdpVendors':
    // Calculate dates for previous 12 months
    $date = date('Y-m')."-01";
    $start = new DateTime($date);
    $start->sub(new DateInterval('P12M'));
    $ds = $start->format('Y-m-d');
    $de = date('Y-m-d');
    // Get KPI data
    $data = array();
    switch($m[2]){
    case 'reliability':
        $options["GPTargetVal"]=$help["OTDP Vendors {$m[2]} Target"];
        switch($m[3]){
        case 'byVendor':
            $options["graphTitle"]="Vendor OTDP {$m[2]} for Previous 12 Months with vendor#$suno";
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
            $options["graphTitle"]="Vendor OTDP {$m[2]} for Previous 12 Months for PN#$pn";
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
            $options["graphTitle"]="Vendor OTDP {$m[2]} for previous 12 Months for $email";
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
            $options["graphTitle"]="OTDP Vendor {$m[2]} for Previous 12 Months (Percentage)";
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
        switch ($m[2]) {
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
    $options["graphTitle"]=str_replace(
		array('%BU%'),
		array($location['location']),
		$translate->getDictionary('OTDP Factory on GT promise date Previous 12 Months %BU% (Percentage)')
	);
    $options["GPTargetVal"]=$help["OTDP Factory Group Target"];
    $options["showMarkers"]="true";

    // By default TLD DTV is excluded
    $WHERE = "AND t1.man_location!='TLD DTV'";
    if($BUID !== "ALL"){
        $WHERE = "t1.man_location='".$location["location"]."'";
    }

    $query=<<<EOF
SELECT
    t1.man_location as zval,
    DATE_FORMAT(t1.dgt_com, '%Y-%m') AS xval,
    ROUND(
        COUNT(DATEDIFF(t1.dgt_com, t2.ddel_est1)<=4 OR NULL)*100/COUNT(*)
    ) as yval
FROM service as t1, sor_units as t2
WHERE
    $WHERE AND t1.sor_uid=t2.id
    AND PERIOD_DIFF(DATE_FORMAT(NOW(),'%Y%m'),DATE_FORMAT(t1.dgt_com,'%Y%m')) BETWEEN 0 AND 12
GROUP BY
    zval, xval
EOF;
    $rows = tldUtils::getSqlToAssocArray($query);
    $Graph=_doBarGraph($rows,$options);
break;
case 'gt28Past12Months':
    $options["graphTitle"]=str_replace(
		array('%BU%'),
		array($location['location']),
		$translate->getDictionary('End of Month GT in last 3 days of the month %BU% (Percentage)')
	);
    $options["GPTargetVal"]=10;
    $options["showMarkers"]="true";

    // By default TLD DTV is excluded
    $WHERE = "AND t1.man_location!='TLD DTV'";
    if($BUID !== "ALL") {
        $WHERE = "AND t1.man_location='".$location["location"]."'";
    }

    $query=<<<EOF
SELECT
    man_location as zval,
    date_format(t1.dgt_com, "%Y-%m") AS xval,
    ROUND(
        (select count(*) from service AS t2
        WHERE YEAR(t2.dgt_com)=YEAR(t1.dgt_com)
        AND MONTH(t2.dgt_com)=MONTH(t1.dgt_com)
        AND t2.man_location=t1.man_location
        AND DAY(t2.dgt_com) > DAY(DATE_ADD(LAST_DAY(t2.dgt_com), INTERVAL -3 DAY))
        )*100/COUNT(*)
    ) AS yval
FROM
    service as t1
WHERE
    PERIOD_DIFF(DATE_FORMAT(NOW(), '%Y%m'), DATE_FORMAT(dgt_com, '%Y%m')) BETWEEN 0 AND 12
    $WHERE
GROUP BY
    zval, xval
EOF;
    $rows = tldUtils::getSqlToAssocArray($query);
    $Graph=_doBarGraph($rows,$options);
break;
case 'gt20Past12Months':
    $options["graphTitle"]=str_replace(
		array('%BU%'),
		array($location['location']),
		$translate->getDictionary('End of Month GT in last 10 days of the month %BU% (Percentage)')
	);
    $options["GPTargetVal"]=33;
    $options["showMarkers"]="true";

    // By default TLD DTV is excluded
    $WHERE = "AND t1.man_location!='TLD DTV'";
    if($BUID !== "ALL"){
        $WHERE = "AND t1.man_location='".$location["location"]."'";
    }

    $query=<<<EOF
SELECT
    man_location as zval,
    date_format(t1.dgt_com, "%Y-%m") AS xval,
    ROUND(
        (select count(*) from service AS t2
        WHERE YEAR(t2.dgt_com)=YEAR(t1.dgt_com)
        AND MONTH(t2.dgt_com)=MONTH(t1.dgt_com)
        AND t2.man_location=t1.man_location
        AND DAY(t2.dgt_com) > DAY(DATE_ADD(LAST_DAY(t2.dgt_com), INTERVAL -10 DAY))
        )*100/COUNT(*)
    ) AS yval
FROM
    service as t1
WHERE
    PERIOD_DIFF(DATE_FORMAT(NOW(), '%Y%m'), DATE_FORMAT(dgt_com, '%Y%m')) BETWEEN 0 AND 12
    $WHERE
GROUP BY
    zval, xval
EOF;
    $rows = tldUtils::getSqlToAssocArray($query);
    $Graph=_doBarGraph($rows,$options);
break;
case 'wcCountByStatusByFactory':
    // Options
    $options["graphTitle"]="Warranty count, per type";
    $options["GPTargetVal"]="NA";
    $months = 12;
    // Constraints
    switch($status){
    case 'PAC':
        $WHERE.=" AND warranty_status IN('PENDING','ACCEPTED ','CONDITIONAL')";
        $options["graphTitle"].=", in status PENDING, ACCEPTED & CONDITIONAL";
        $months = 24;
    break;
    case 'RS':
        $WHERE.=" AND warranty_status IN('REJECTED','SALES CONCESSION')";
        $options["graphTitle"].=", in status REJECTED & SALES CONCESSION";
    break;
    }
    if($BUID !== "ALL"){
        $WHERE.=" AND warranty.man_location='{$location['location']}'";
        $options["graphTitle"].=", ".$location['location'];
    }
    // Query Count
    $query = <<<EOF
SELECT
    warranty.type as zval,
    DATE_FORMAT(claim_date, '%Y-%m') AS xval,
    COUNT(*) AS yval
FROM
    warranty
LEFT JOIN toc ON toc.warranty_id = warranty.id
LEFT JOIN service ON service.id = warranty.parent_id
WHERE
    service.light = 0
    AND PERIOD_DIFF(
        DATE_FORMAT(NOW(), '%Y%m'),
        DATE_FORMAT(claim_date, '%Y%m')
    ) BETWEEN 0 AND $months
$WHERE
AND (toc.activity_type != 'Commissioning' OR toc.id IS NULL)
GROUP BY zval, xval
ORDER BY xval ASC
EOF;
    $start = (new \DateTime('first day of this month'))
        ->modify("-{$months} months");

    $end = new \DateTime('first day of next month');

    $period = new \DatePeriod(
        $start,
        new \DateInterval('P1M'),
        $end
    );

    $rows = tldUtils::getSqlToAssocArray($query);
    $Graph=_doBarGraph($rows,$options);
break;
case 'wcCountByStatusByFactoryLightEr':
    // Options
    $options["graphTitle"]="Warranty count, per type";
    $options["GPTargetVal"]="NA";
    // Constraints
    switch($status){
        case 'PAC':
            $WHERE.=" AND warranty_status IN('PENDING','ACCEPTED ','CONDITIONAL')";
            $options["graphTitle"].=", in status PENDING, ACCEPTED & CONDITIONAL";
            break;
        case 'RS':
            $WHERE.=" AND warranty_status IN('REJECTED','SALES CONCESSION')";
            $options["graphTitle"].=", in status REJECTED & SALES CONCESSION";
            break;
    }
    if($BUID !== "ALL"){
        $WHERE.=" AND warranty.man_location='{$location['location']}'";
        $options["graphTitle"].=", ".$location['location'];
    }
    // Query Count
    $query = <<<EOF
SELECT
    warranty.type as zval,
    DATE_FORMAT(claim_date, '%Y-%m') AS xval,
    COUNT(*) AS yval
FROM
    warranty
LEFT JOIN toc ON toc.warranty_id = warranty.id
LEFT JOIN service ON service.id = warranty.parent_id
WHERE
    service.light = 1
    AND PERIOD_DIFF(
        DATE_FORMAT(NOW(), '%Y%m'),
        DATE_FORMAT(claim_date, '%Y%m')
    ) BETWEEN 0 AND 12
$WHERE
AND (toc.activity_type != 'Commissioning' OR toc.id IS NULL)
GROUP BY zval, xval
EOF;
    $start = (new \DateTime('midnight first day of this month last year'));
    $oneMonthInterval = new \DateInterval('P1M');
    $period = new \DatePeriod($start, $oneMonthInterval, new \DateTime('first day of this month'));

    $rows = tldUtils::getSqlToAssocArray($query);
    $Graph=_doBarGraph($rows,$options);
    break;
case 'countCurrentYearByFactory':
    //initialize the array of option that will be use in this graph
    $options["GPTargetVal"]=$help["Warranty Claim Counts Group Target"];
    $options["showMarkers"]="true";

    if($BUID !== "ALL"){
        $options["graphTitle"]="Monthly Warranty Count, per type, for Previous 12 Months ".$location['location'];
        $WHERE = "AND man_location='".$location['location']."'";

        $query = <<<EOF
        SELECT type as zval,
            DATE_FORMAT(claim_date, '%Y-%m') AS xval,
            COUNT(*) AS yval
        FROM warranty
        WHERE PERIOD_DIFF(DATE_FORMAT(NOW(), '%Y%m'), DATE_FORMAT(claim_date, '%Y%m')) BETWEEN 0 AND 12
        $WHERE
        GROUP BY zval, xval
EOF;
    }else{
        $options["graphTitle"]="Monthly Warranty Count, all types, for Previous 12 Months ".$location['location'];
        $query = <<<EOF
        SELECT man_location as zval,
            DATE_FORMAT(claim_date, '%Y-%m') AS xval,
            COUNT(*) AS yval
        FROM warranty
        WHERE PERIOD_DIFF(DATE_FORMAT(NOW(), '%Y%m'), DATE_FORMAT(claim_date, '%Y%m')) BETWEEN 0 AND 12
        GROUP BY zval, xval
EOF;
    }
    $rows = tldUtils::getSqlToAssocArray($query);
    $Graph=_doBarGraph($rows,$options);
break;
case 'MTBF_byFactory':
    $options["GPTargetVal"]=$help["MTBF target"];
    $options["showMarkers"]="true";
    $options["graphTitle"]="WC - Mean time between failures, shipped < 2 years, for Previous 12 Months";
    // Create conditions
	if($byType){
		$options["graphTitle"].=" - Per Type";
		$options["GPTargetVal"] = "NA";
	}
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
WHERE period_diff(date_format(NOW(), '%Y%m'), p.nam_period) BETWEEN 1 AND 12
ORDER BY nam_period
EOF;
    $graph_period = tldUtils::getSqlToAssocArray($query);
    // Foreach months, calculate KPI
    foreach($graph_period as $period){
        $period_Ym = $period['xval'];
        $period_Ymd = substr($period['xval'],0,4)."-".substr($period['xval'],-2,2)."-01";

        if($buid !== "ALL"){
            $WHERE = "er.man_location='{$bu->itsDetails['location']}'";
            $rows = tldWC::getMTBFPeriodByConstraints($period_Ymd,$WHERE,$byType);
            if(!$byType)$rows = array($rows);
            foreach($rows as $row){
	            $data[]=array(
	                "xval"=>$row['period'],
	                "yval"=>$row['val'],
	                "zval"=>($byType)?$row['type']:$bu->itsDetails['location']
	            );
            }
        } else {
            $FACTORIES = array_filter($FACTORIES, function($factory) {
                return $factory !== "TLD DTV";
            });
            foreach($FACTORIES as $erp=>$location){
                $WHERE = "er.man_location='$location'";
                $rows = tldWC::getMTBFPeriodByConstraints($period_Ymd,$WHERE,$byType);
                if(!$byType)$rows = array($rows);
                foreach($rows as $row){
	                $data[]=array(
	                    "xval"=>$row['period'],
	                    "yval"=>$row['val'],
	                    "zval"=>($byType)?$row['type']:$location
	                );
                }
            }
        }
    }
    $Graph=_doBarGraph($data,$options);
break;
case 'AVGTMY_byFactory':
    $options["GPTargetVal"]=$help["AVGTMY target"];
    $options["showMarkers"]="true";
    $options["graphTitle"]="WC - Average of Time of WC per Machine per Year, shipped < 2 years, for Previous 12 Months";
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
WHERE period_diff(date_format(NOW(), '%Y%m'), p.nam_period) BETWEEN 1 AND 12
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
            foreach($FACTORIES as $erp=>$location){
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
    $options["showMarkers"]="true";
    $options["graphTitle"]="WC - Average Time at First Failures, shipped < 2 years, for Previous 12 Months";
    // Create conditions
	if($byType){
		$options["graphTitle"].=" - Per Type";
		$options["GPTargetVal"] = "NA";
	}
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
WHERE period_diff(date_format(NOW(), '%Y%m'), p.nam_period) BETWEEN 1 AND 12
ORDER BY nam_period
EOF;
    $graph_period = tldUtils::getSqlToAssocArray($query);
    // Foreach months, calculate KPI
    foreach($graph_period as $period){
        $period_Ym = $period['xval'];
        $period_Ymd = substr($period['xval'],0,4)."-".substr($period['xval'],-2,2)."-01";

        if($buid !== "ALL"){
            $WHERE = "er.man_location='{$bu->itsDetails['location']}'";
            $rows = tldWC::getATFFPeriodByConstraints($period_Ymd,$WHERE,$byType);
            if(!$byType)$rows = array($rows);
			foreach($rows as $row){
				$data[]=array(
						"xval"=>$row['period'],
						"yval"=>$row['val'],
						"zval"=>($byType)?$row['type']:$bu->itsDetails['location']
				);
			}
        }else{
            $FACTORIES = array_filter($FACTORIES, function($factory) {
                return $factory !== "TLD DTV";
            });
            foreach($FACTORIES as $erp=>$location){
                $WHERE = "er.man_location='$location'";
                $rows = tldWC::getATFFPeriodByConstraints($period_Ymd,$WHERE,$byType);
                if(!$byType)$rows = array($rows);
				foreach($rows as $row){
					$data[]=array(
							"xval"=>$row['period'],
							"yval"=>$row['val'],
							"zval"=>($byType)?$row['type']:$location
					);
				}
            }
        }
    }
    $Graph=_doBarGraph($data,$options);
break;
case 'AVGWC_byFactory':
	$graphArray = array(
			'title'=>'WC - Average number of WC per Machine, shipped < 2 years, for Previous 12 Months',
			'yScaleUnit'=>'WC',
			'xScaleUnit'=>'Date'
	);
	// Create conditions
	if($byType){
		$graphArray['title'] .=" - Per Type";
	}else{
		$target = $help["AVGWC target"];
	}
	if($buid !== "ALL" && is_numeric($buid)){
		$bu = new tldLocation($buid);
		$graphArray['title'].=" - {$bu->itsDetails['location']}";
	}
	// Initialise kpi data
	$graph = new tldGraphLine($graphArray);
	$data = array();
	// Get 12 months windows
	$query = <<<EOF
     SELECT nam_period AS xval
     FROM fin_periods AS p
     WHERE period_diff(date_format(NOW(), '%Y%m'), p.nam_period) BETWEEN 1 AND 12
     ORDER BY nam_period
EOF;

	$graph_period = tldUtils::getSqlToAssocArray($query);
	// Foreach months, calculate KPI
	foreach($graph_period as $period){
		$period_Ym = $period['xval'];
		$period_Ymd = substr($period['xval'],0,4)."-".substr($period['xval'],-2,2)."-01";

		if($buid !== "ALL"){
			$WHERE = "er.man_location='{$bu->itsDetails['location']}'";
			$rows = tldWC::getAVGWCPeriodByConstraints($period_Ymd,$WHERE,$byType);
			if(!$byType)$rows = array($rows);
			foreach($rows as $row){
				$data[($byType)?$row['type']:$bu->itsDetails['location']][$row['period']] = $row['val'];
			}
		}else{
			foreach($FACTORIES as $erp=>$location){
                if ($location === 'TLD DTV') {
                    continue;
                }
				$WHERE = "er.man_location='$location'";
				$rows = tldWC::getAVGWCPeriodByConstraints($period_Ymd,$WHERE,$byType);
				if(!$byType)$rows = array($rows);
				foreach($rows as $row){
					$data[($byType)?$row['type']:$location][$row['period']] = $row['val'];
				}
			}
		}
	}
	$i = 1;
	foreach($data as $key=>$dat){
		if ($i == 1 && !empty($target)){
			$add_target = array('groupTarget'=>$target);
		}else{
			$add_target = array();
		}
		$graph->addLine(
				$dat,
				array(
						'name'=>$key,
						'color'=>$COLOURS[$i]
				)+$add_target
		);
		$add_target = array();
		$i++;
	}
	$graph->fetch();

break;
case 'MNOWC3_byFactory':
    $options["GPTargetVal"]=$help["MNOWC3 target"];
    $options["showMarkers"]="true";
    $options["graphTitle"]="WC - % of Machines with NO WC in the first 3 months, shipped < 2 years, for Previous 12 Months";
    // Create conditions
	if($byType){
		$options["graphTitle"].=" - Per Type";
		$options["GPTargetVal"] = "NA";
	}
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
WHERE period_diff(date_format(NOW(), '%Y%m'), p.nam_period) BETWEEN 1 AND 12
ORDER BY nam_period
EOF;
    $graph_period = tldUtils::getSqlToAssocArray($query);
    // Foreach months, calculate KPI
    foreach($graph_period as $period){
        $period_Ym = $period['xval'];
        $period_Ymd = substr($period['xval'],0,4)."-".substr($period['xval'],-2,2)."-01";

        if($buid !== "ALL"){
            $WHERE = "er.man_location='{$bu->itsDetails['location']}'";
            $rows = tldWC::getMNOWC3PeriodByConstraints($period_Ymd,$WHERE,$byType);
			if(!$byType)$rows = array($rows);
			foreach($rows as $row){
				$data[]=array(
						"xval"=>$row['period'],
						"yval"=>$row['val'],
						"zval"=>($byType)?$row['type']:$bu->itsDetails['location']
				);
			}
        }else{
            $FACTORIES = array_filter($FACTORIES, function($factory) {
                return $factory !== "TLD DTV";
            });
            foreach($FACTORIES as $erp=>$location){
                $WHERE = "er.man_location='$location'";
                $rows = tldWC::getMNOWC3PeriodByConstraints($period_Ymd,$WHERE,$byType);
				if(!$byType)$rows = array($rows);
				foreach($rows as $row){
					$data[]=array(
							"xval"=>$row['period'],
							"yval"=>$row['val'],
							"zval"=>($byType)?$row['type']:$location
					);
				}
            }
        }
    }
    $Graph=_doBarGraph($data,$options);
break;
case 'CRABAvgPerTypeGTUnits':
    //initialize the array of option that will be use in this graph
    $options["graphTitle"]="Monthly average CRABS count, per type, for all GT units (on First GT date) for previous 12 months, ".$location['location'];
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
	PERIOD_DIFF(DATE_FORMAT(NOW(), '%Y%m'), DATE_FORMAT(s.dgt_com, '%Y%m')) BETWEEN 0 AND 12
    AND c.opno <> ''
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
    $start = (new \DateTime('midnight first day of this month last year'));
    $rows  = [];
    foreach (['QA', 'Test', 'Assy', 'PDI', 'PDI-SOL', 'PDI-CSC'] as $zval) {
        $rows[] = [
            'zval' => $zval,
            'xval' => $start->format('Y-m'),
            'yval' => 0,
        ];
    }

    $oneMonthInterval = new \DateInterval('P1M');
    $period = new \DatePeriod($start, $oneMonthInterval, new \DateTime('first day of this month'));
    foreach ($period as $month) {
        $rows[] = [
            'zval' => 'QA',
            'xval' => $month->format('Y-m'),
            'yval' => 0,
        ];
    }

    $rows = array_merge($rows, tldUtils::getSqlToAssocArray($query));

    $Graph=_doBarGraph($rows,$options);
break;
    case 'CRABCountPerTypeGTUnits':
        //initialize the array of option that will be use in this graph
        $options["graphTitle"]="Monthly PDI CRABS count, per type, for all GT units (on First GT date) for previous 12 months, ".$location['location'];
        $options["GPTargetVal"]=$help["CRAB Count Per Type For All GT Units Group Target"];
        $options["showMarkers"]="true";
        $options['customColor']="true";

        $C1 = "";
        if($BUID !== "ALL"){
            $C1 = "AND s.man_location='".$location["location"]."'";
        }
        $query=<<<EOF
SELECT
	c.opno AS zval,
	DATE_FORMAT(s.dgt_com, '%Y-%m') AS xval,
	COUNT(*)  AS yval
FROM
	service s
	LEFT JOIN crabs c ON c.erid=s.id
WHERE
	PERIOD_DIFF(DATE_FORMAT(NOW(), '%Y%m'), DATE_FORMAT(s.dgt_com, '%Y%m')) <= 12 AND
	c.opno LIKE 'PDI%'
	$C1
GROUP BY
	zval, xval
ORDER BY CASE
WHEN zval = 'PDI'
THEN 1
WHEN zval = 'PDI-SOL'
THEN 2
WHEN zval = 'PDI-CSC'
THEN 3
END, xval
EOF;
        // Ensure color will always be displayed in the same way
        $start = (new \DateTime('midnight first day of this month last year'));
        $rows  = [];
        foreach (['PDI', 'PDI-SOL', 'PDI-CSC'] as $zval) {
            $rows[] = [
                'zval' => $zval,
                'xval' => $start->format('Y-m'),
                'yval' => 0
            ];
        }
        $oneMonthInterval = new \DateInterval('P1M');
        $period = new \DatePeriod($start, $oneMonthInterval, new \DateTime('first day of this month'));
        foreach ($period as $month) {
            $rows[] = [
                'zval' => 'PDI',
                'xval' => $month->format('Y-m'),
                'yval' => 0,
            ];
        }

        $rows = array_merge($rows, tldUtils::getSqlToAssocArray($query));

        $Graph=_doBarGraph($rows,$options);
        break;
case 'otdpAvgDaysLatePast12Months':
    $options["graphTitle"]=str_replace(
		array('%BU%'),
		array($location['location']),
		$translate->getDictionary('OTDP Factory AVG Days Late for Previous 12 Months %BU% (Days)')
	);
    $options["GPTargetVal"]=$help["OTDP Factory AVG days late Group Target"];
    $options["showMarkers"]="true";

    // By default TLD DTV is excluded
    $WHERE = "AND t1.man_location!='TLD DTV'";
    if($BUID !== "ALL"){
        $WHERE = "AND t1.man_location='".$location["location"]."'";
    }

    $query=<<<EOF
    select t1.man_location as zval,
    DATE_FORMAT(t1.dgt_com, '%Y-%m') AS xval,
    ROUND(avg(
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

    from service as t1,
     sor_units as t2
    where t1.sor_uid=t2.id
     $WHERE
      AND PERIOD_DIFF(DATE_FORMAT(NOW(),'%Y%m'),DATE_FORMAT(t1.dgt_com,'%Y%m')) <= 12
    group by zval, xval
EOF;
    $rows = tldUtils::getSqlToAssocArray($query);
    $Graph=_doBarGraph($rows,$options);
break;
case 'cycleCountQuantityPerformancePast12Months':

    //initialize the array of option that will be use in this graph
    $options["graphTitle"]="Cycle Count Quantity absolute variance for Previous 12 Months (Percentage)";
    $options["GPTargetVal"]=$help["Cycle Count Quantity Group Target"];
    $options["showMarkers"]="true";

    $WHERE = "";
    if($BUID !== "ALL"){
        $WHERE = "AND buid=$BUID";
    }

    $query = <<<EOF
        SELECT
            (SELECT location FROM locations AS T2
            WHERE T2.id = T1.buid
            ) as zval,
            ROUND(if(cc_qua=0, 0, cc_quv*100/cc_qua),2
            ) as yval,
            DATE_FORMAT(CONCAT(ynam,'-',mnam,'-01'), '%Y-%m') AS xval
        FROM fin_kpi AS T1
        WHERE PERIOD_DIFF(DATE_FORMAT(NOW(), '%Y%m'),
                        DATE_FORMAT(CONCAT(ynam,'-',mnam,'-01'), '%Y%m')
              ) <= 12
         $WHERE
        GROUP BY zval, xval
EOF;
    $rows = tldUtils::getSqlToAssocArray($query);
    $Graph=_doBarGraph($rows,$options);
break;
case 'numberItemCountedPast12Months':
    //initialize the array of option that will be use in this graph
    $options["graphTitle"]="Number of item counted for Previous 12 Months (Percentage)";
    $options["showMarkers"]="true";

    $WHERE = "";
    if($BUID !== "ALL"){
        $WHERE = "AND buid=$BUID";
    }

    $query = <<<EOF
        SELECT
            (SELECT location FROM locations AS T2
            WHERE T2.id = T1.buid
            ) as zval,
            T1.cc_qua as yval,
            DATE_FORMAT(CONCAT(ynam,'-',mnam,'-01'), '%Y-%m') AS xval
        FROM fin_kpi AS T1
        WHERE PERIOD_DIFF(DATE_FORMAT(NOW(), '%Y%m'),
                        DATE_FORMAT(CONCAT(ynam,'-',mnam,'-01'), '%Y%m')
              ) <= 12
         $WHERE
        GROUP BY zval, xval
EOF;
    $rows = tldUtils::getSqlToAssocArray($query);
    $Graph=_doBarGraph($rows,$options);
break;
case 'sumValueItemCounterPast12Months':
    //initialize the array of option that will be use in this graph
    $options["graphTitle"]="Sum of value item counted for Previous 12 Months (Percentage)";
    $options["showMarkers"]="true";

    $WHERE = "";
    if($BUID !== "ALL"){
        $WHERE = "AND buid=$BUID";
    }

    $query = <<<EOF
        SELECT
            (SELECT location FROM locations AS T2
            WHERE T2.id = T1.buid
            ) as zval,
            T1.cc_pric as yval,
            DATE_FORMAT(CONCAT(ynam,'-',mnam,'-01'), '%Y-%m') AS xval
        FROM fin_kpi AS T1
        WHERE PERIOD_DIFF(DATE_FORMAT(NOW(), '%Y%m'),
                        DATE_FORMAT(CONCAT(ynam,'-',mnam,'-01'), '%Y%m')
              ) <= 12
         $WHERE
        GROUP BY zval, xval
EOF;
    $rows = tldUtils::getSqlToAssocArray($query);
    $Graph=_doBarGraph($rows,$options);
break;
case 'cycleCountValuePerformancePast12Months':
    //initialize the array of option that will be use in this graph
    $options["graphTitle"]="Cycle Count Value variance for Previous 12 Months (Percentage)";
    $options["GPTargetVal"]=$help["Cycle Count Value Group Target"];
    $options["showMarkers"]="false";

    $WHERE = "";
    if($BUID !== "ALL"){
        $WHERE = "AND buid=$BUID";
    }

    $query = <<<EOF
SELECT
    (SELECT location FROM locations AS T2 WHERE T2.id = T1.buid
    ) as zval,
    ROUND(if(cc_pric=0, 0, cc_priv*100/cc_pric),2
    ) as yval,
    DATE_FORMAT(CONCAT(ynam,'-',mnam,'-01'), '%Y-%m') AS xval
FROM fin_kpi AS T1
WHERE PERIOD_DIFF(DATE_FORMAT(NOW(), '%Y%m'),
                DATE_FORMAT(CONCAT(ynam,'-',mnam,'-01'), '%Y%m')
      ) <= 12
 $WHERE
GROUP BY zval, xval
EOF;
    $rows = tldUtils::getSqlToAssocArray($query);
    $Graph=_doBarGraph($rows,$options);
break;
case 'factoryProductiveHourRatioPast12Months':
    //initialize the array of option that will be use in this graph
    $options["graphTitle"]="Factory Productive Hour Ratio (PHR) for Previous 12 Months (Percentage)";
    $options["GPTargetVal"]=$help["Factory Productive Hour Ratio Group Target"];
    $options["showMarkers"]="true";

    $WHERE = "";
    if($BUID !== "ALL"){
        $WHERE = "AND buid=$BUID";
    }

    $query = <<<EOF
SELECT
    (SELECT location FROM locations AS T2 WHERE T2.id = T1.buid) as zval,
    ROUND(if(phr_poth=0, 0, phr_proh*100/phr_poth),2) as yval,
    DATE_FORMAT(CONCAT(ynam,'-',mnam,'-01'), '%Y-%m') AS xval
FROM fin_kpi AS T1
WHERE PERIOD_DIFF(DATE_FORMAT(NOW(), '%Y%m'),
        DATE_FORMAT(CONCAT(ynam,'-',mnam,'-01'), '%Y%m')
      ) <= 12
 $WHERE
GROUP BY zval, xval
EOF;
    $rows = tldUtils::getSqlToAssocArray($query);
    $Graph=_doBarGraph($rows,$options);
break;
case 'factoryStandardEfficiencyPast12Months':

    //initialize the array of option that will be use in this graph
    $options["graphTitle"]="Factory Standard Efficiency (FSE) for Previous 12 Months (Percentage)";
    $options["GPTargetVal"]=$help["Factory Standard Efficiency Group Target"];
    $options["showMarkers"]="true";

    $WHERE = "";
    if($BUID !== "ALL"){
        $WHERE = "AND buid=$BUID";
    }

    $query = <<<EOF
SELECT
    (SELECT location FROM locations AS T2 WHERE T2.id = T1.buid) as zval,
    ROUND(if(fse_acth=0, 0, fse_stdh*100/fse_acth),2) as yval,
    DATE_FORMAT(CONCAT(ynam,'-',mnam,'-01'), '%Y-%m') AS xval
FROM fin_kpi AS T1
WHERE PERIOD_DIFF(DATE_FORMAT(NOW(), '%Y%m'),
    DATE_FORMAT(CONCAT(ynam,'-',mnam,'-01'), '%Y%m')
      ) <= 12
 $WHERE
GROUP BY zval, xval
EOF;
    $rows = tldUtils::getSqlToAssocArray($query);
    $Graph=_doBarGraph($rows,$options);
break;
case 'factoryProductivityPast12Months':
    //initialize the array of option that will be use in this graph
    $options["graphTitle"]="Factory Productivity (FSE x PHR) for Previous 12 Months (Percentage)";
    $options["GPTargetVal"]=$help["Factory Productivity Group Target"];
    $options["showMarkers"]="true";

    $WHERE = "";
    if($BUID !== "ALL"){
        $WHERE = "AND buid=$BUID";
    }

    $query = <<<EOF
SELECT
    (SELECT location FROM locations AS T2 WHERE T2.id = T1.buid) as zval,
    ROUND(if(fse_stdh=0 OR phr_poth=0, 0, 100*fse_stdh/fse_acth * phr_proh/phr_poth),2) as yval,
    DATE_FORMAT(CONCAT(ynam,'-',mnam,'-01'), '%Y-%m') AS xval
FROM fin_kpi AS T1
WHERE PERIOD_DIFF(DATE_FORMAT(NOW(), '%Y%m'),
    DATE_FORMAT(CONCAT(ynam,'-',mnam,'-01'), '%Y%m')
  ) <= 12
 $WHERE
GROUP BY zval, xval
EOF;
    $rows = tldUtils::getSqlToAssocArray($query);
    $Graph=_doBarGraph($rows,$options);
break;
case 'inventoryValuePast12Months':
    //initialize the array of option that will be use in this graph
    $options["graphTitle"]="Inventory Value for Previous 12 Months (Days of sales)";
    $options["GPTargetVal"]=$help["Inventory Value Group Target"];
    $options["showMarkers"]="true";

    // By default TLD DTV is excluded buid=37
    $WHERE = "AND buid!=37";
    if($BUID !== "ALL"){
        $WHERE = "AND buid=$BUID";
    }

    $query = <<<EOF
SELECT
    locs.location as zval,
    ROUND(T1.erp_net_inv_val/((SELECT sum(kpis.erp_tot_sls)
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
FROM fin_kpi AS T1 LEFT JOIN locations AS locs ON T1.buid=locs.id
WHERE PERIOD_DIFF(DATE_FORMAT(NOW(), '%Y%m'),
    DATE_FORMAT(CONCAT(ynam,'-',mnam,'-01'), '%Y%m')
      ) <= 12
$WHERE
GROUP BY zval, xval
EOF;
    $rows = tldUtils::getSqlToAssocArray($query);
    $Graph=_doBarGraph($rows,$options);
break;
case 'wipValuePast12Months':
    //initialize the array of option that will be use in this graph
    $options["graphTitle"]="Work in Progress Value for Previous 12 Months (in days of sales)";
    $options["GPTargetVal"]=$help["Work in Progress Value Group Target"];
    $options["showMarkers"]="true";

    // By default TLD DTV is excluded buid=37
    $WHERE = "AND buid!=37";
    if($BUID !== "ALL"){
        $WHERE = "AND buid=$BUID";
    }

    $query = <<<EOF
SELECT
    locs.location as zval,
    ROUND(T1.erp_net_wip_val/((SELECT sum(kpis.erp_tot_sls)
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
FROM fin_kpi AS T1 LEFT JOIN locations AS locs ON T1.buid=locs.id
WHERE PERIOD_DIFF(DATE_FORMAT(NOW(), '%Y%m'),
                DATE_FORMAT(CONCAT(ynam,'-',mnam,'-01'), '%Y%m')
      ) <= 12
$WHERE
GROUP BY zval, xval
EOF;
    $rows = tldUtils::getSqlToAssocArray($query);
    $Graph=_doBarGraph($rows,$options);
break;
case 'internalCustomerSatisfactionPast12Months':
    $WHERE = "WHERE PERIOD_DIFF(DATE_FORMAT(NOW(), '%Y%m'),
            DATE_FORMAT(CONCAT(ynam,'-',mnam,'-01'), '%Y%m')) <= 12";
    $options["graphTitle"]="Internal Customer Satisfaction for Previous 12 Months by SSO ($BUID)";

    if($BUID === "ame" || $BUID === "asi" || $BUID === "eur" || $BUID === "nam"){
        //initialize the array of option that will be use in this graph
        $options["showMarkers"]="true";
        $options["graphStyle"]="bar";

        $query = <<<EOF
SELECT
    (SELECT location FROM locations AS T2 WHERE T2.id = T1.buid) as zval,
    ics_$BUID as yval,
    DATE_FORMAT(CONCAT(ynam,'-',mnam,'-01'), '%Y-%m') AS xval
FROM fin_kpi AS T1
 $WHERE
ORDER BY zval, xval
EOF;

        $rows = tldUtils::getSqlToAssocArray($query);

        $Graph=_doBarGraph($rows,$options);

    }elseif($BUID !== "ALL"){

		if($json){
			$Graph=array();
		}else{
	    	$Graph =& Image_Graph::factory('graph', array(980, 500));
            $Plotarea = Image_Graph::factory('plotarea');
            $Legend = Image_Graph::factory('legend');

	        $Graph->add(
	            Image_Graph::vertical(
	                Image_Graph::factory('title', array($options["graphTitle"], 12)),
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

	        // add font
	        $Font =& $Graph->addNew('ttf_font', 'DejaVuLGCSans-Bold.ttf');
	        // set the font size to 11 pixels
	        $Font->setSize(10);
	        $Graph->setFont($Font);

	        // create a grid and assign it to the secondary Y axis
	        $GridY2 =& $Plotarea->addNew('line_grid', IMAGE_GRAPH_AXIS_Y);
		}
        $AND = "";
        if($BUID !== "ALL"){
            $AND = "AND buid=$BUID";
        }

        $query = <<<EOF
SELECT *,
    DATE_FORMAT(CONCAT(ynam,'-',mnam,'-01'), '%Y-%m') AS xval
FROM fin_kpi AS T1
 $WHERE
 $AND
ORDER BY ynam, mnam
EOF;

        $rows = tldUtils::getSqlToAssocArray($query);

		if($json){
			$Graph['options']=$options;
			$Graph['rows']=array();
			foreach($rows as $row){
				foreach(['ame' => 'TLD America', 'asi' => 'TLD Asia', 'eur' => 'TLD ECAT', 'prc' => 'TLD China', 'meai' => 'TLD IMEA', 'lac' => 'LAC','nam'=>'TLD NAM', 'aero'=> 'AERO SSO'] as $loc=>$nam){
					$Graph['rows'][]=array(
						'xval'=>$row["xval"],
						'yval'=>$row["ics_$loc"],
						'zval'=>$nam
					);
				}
			}
			break;
		}

        // create a fill array
        $FillArray =& Image_Graph::factory('Image_Graph_Fill_Array');

        $ameDataset =& Image_Graph::factory('dataset');
        $ameDataset->setName('TLD America');
        $FillArray->addColor($COLOURS[0], 'ame');

        $asiDataset =& Image_Graph::factory('dataset');
        $asiDataset->setName('TLD Asia');
        $FillArray->addColor($COLOURS[1], 'asi');

        $eurDataset =& Image_Graph::factory('dataset');
        $eurDataset->setName('TLD ECAT');
        $FillArray->addColor($COLOURS[2], 'eur');

        $prcDataset =& Image_Graph::factory('dataset');
        $prcDataset->setName('TLD China');
        $FillArray->addColor($COLOURS[3], 'prc');

        $meaiDataset =& Image_Graph::factory('dataset');
        $meaiDataset->setName('TLD IMEA');
        $FillArray->addColor($COLOURS[4], 'meai');

        $laajDataset =& Image_Graph::factory('dataset');
        $laajDataset->setName('TLD LAC');
        $FillArray->addColor($COLOURS[5], 'lac');

        $namDataset =& Image_Graph::factory('dataset');
        $namDataset->setName('TLD NAM');
        $FillArray->addColor($COLOURS[6], 'nam');

        $aeroDataset =& Image_Graph::factory('dataset');
        $aeroDataset->setName('AERO SSO');
        $FillArray->addColor($COLOURS[7], 'aero');

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
			$prcDataset->addPoint(
            	$row["xval"],
				$row["ics_prc"],
				'prc'
			);
			$meaiDataset->addPoint(
            	$row["xval"],
				$row["ics_meai"],
				'meai'
			);
			$laajDataset->addPoint(
				$row["xval"],
				$row["ics_laaj"],
				'laaj'
			);
            $namDataset->addPoint(
                $row["xval"],
                $row["ics_nam"],
                'nam'
            );
            $aeroDataset->addPoint(
                $row["xval"],
                $row["ics_aero"],
                'aero'
            );
        }
        $datasets["ame"] = &$ameDataset;
        $datasets["asi"] = &$asiDataset;
        $datasets["eur"] = &$eurDataset;
        $datasets["prc"] = &$prcDataset;
        $datasets["meai"] = &$meaiDataset;
        $datasets["laaj"] = &$laajDataset;
        $datasets["nam"] = &$namDataset;
        $datasets["aero"] = &$aeroDataset;



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
    }
break;
case 'pdcOpenedPast12Months':
    //initialize the array of option that will be use in this graph
    $options["GPTargetVal"]=$help["PDC Opened During the Month Group Target"];
    $options["showMarkers"]="true";
    $options["graphTitle"]="Count of PDC opened by Year, Month for Previous 12 Months";

    $WHERE = "";
    if($BUID !== "ALL"){
        $WHERE = " AND T1.factory='$BUID'";
    }
    $query1 =<<<EOF
SELECT
    'OPENED'  AS zval,
    date_format(T1.date, '%Y-%m') AS xval,
    COUNT(*) AS yval
FROM
    demerit AS T1
    LEFT JOIN locations AS T2 ON T1.factory=T2.id
WHERE
    PERIOD_DIFF(DATE_FORMAT(NOW(), '%Y%m'), DATE_FORMAT(T1.date, '%Y%m')) BETWEEN 0 AND 12
$WHERE
GROUP BY
    xval, zval
EOF;
    $rows1 = tldUtils::getSqlToAssocArray($query1);

    $query2 =<<<EOF
SELECT
    'CLOSED'  AS zval,
    date_format(T1.date_closed, '%Y-%m') AS xval,
    COUNT(*) AS yval
FROM
    demerit AS T1 LEFT JOIN locations AS T2 ON T1.factory=T2.id
WHERE
    PERIOD_DIFF(DATE_FORMAT(NOW(), '%Y%m'), DATE_FORMAT(T1.date_closed, '%Y%m')) between 0 AND 12
$WHERE
GROUP BY xval, zval
EOF;
    $start = (new \DateTime('midnight first day of this month last year'));
    $oneMonthInterval = new \DateInterval('P1M');
    $period = new \DatePeriod($start, $oneMonthInterval, new \DateTime('first day of this month'));

    $rows  = [];
    foreach ($period as $month) {
        $rows[] = [
            'zval' => 'CLOSED',
            'xval' => $month->format('Y-m'),
            'yval' => 0,
        ];
    }

    $rows2 = tldUtils::getSqlToAssocArray($query2);
    $Graph = _doBarGraph(array_merge($rows, $rows1, $rows2),$options);
break;
case 'historyByFactory':
    //initialize the array of option that will be use in this graph
    $options["GPTargetVal"]=$help["PDC Focus Weight Group Target"];
    $options["showMarkers"]="true";
    $options["graphTitle"]="Average Monthly Fweight over Time Previous 12 Months";

    $WHERE = "";
    if($BUID !== "ALL"){
        $WHERE = " AND locations.erp=$erp";
    }

    $query =<<<EOF
SELECT
    locations.location as zval,
    DATE_FORMAT(T1.date, '%Y-%m') AS xval,
    ROUND(SUM(fweight)/COUNT(DISTINCT T1.date),0) as yval
FROM
    demerit_history AS T1 LEFT JOIN demerit ON demerit.id=T1.parent_id
    LEFT JOIN locations ON locations.id=demerit.factory
WHERE PERIOD_DIFF(DATE_FORMAT(NOW(), '%Y%m'), DATE_FORMAT(T1.date, '%Y%m')) < 12
$WHERE
GROUP BY zval, xval
EOF;
    $rows = tldUtils::getSqlToAssocArray($query);
    $Graph = _doBarGraph($rows,$options);
break;
case 'pdcInProgressCountPast12Months':

    //initialize the array of option that will be use in this graph
    $options["graphTitle"]="In Progress PDC for Previous 12 Months, as of 15th of each month";
    $options["GPTargetVal"]=$help["PDC in Progress Group Target"];
    $options["showMarkers"]="true";

    $WHERE = "";
    if($BUID !== "ALL"){
        $WHERE = "AND list_key=$BUID";
    }
    // new query to get the PDC in Progress as of the 15 of each month and for the TABLE MOD_LISTS



    $query =<<<EOF
SELECT
    t2.location AS zval,
    list_key2 as xval, value as yval
FROM
    mod_lists AS t1 LEFT JOIN locations AS t2 ON t1.list_key=t2.id
WHERE
    PERIOD_DIFF(DATE_FORMAT(NOW(), '%Y%m'),DATE_FORMAT(str_to_date(t1.list_key2,"%Y-%m"), '%Y%m')) < 12
    AND module ='PDC'
    $WHERE
    AND list_name='INPROGRESS'
GROUP BY zval, xval
EOF;
    $rows = tldUtils::getSqlToAssocArray($query);
    $Graph = _doBarGraph($rows, $options);
break;
case 'DOPOPast12Months':

    //initialize the array of option that will be use in this graph
    $options["graphTitle"]="Days of Payables Out for Previous 12 Months";
    $options["GPTargetVal"]=$help["Days of Payables Out Group Target"];
    $options["showMarkers"]="true";

    $WHERE = "";
    if($BUID !== "ALL"){
        $WHERE = "AND buid=$BUID";
    }

    $query = <<<EOF
SELECT
	locs.location AS zval,
	ROUND(
		T1.erp_tot_ap /
		(
			(
				SELECT
					SUM(kpis.erp_tot_sls)
				FROM
					fin_kpi AS kpis
				WHERE
					kpis.buid=T1.buid AND
					PERIOD_DIFF(
						DATE_FORMAT(CONCAT(T1.ynam,'-',T1.mnam,'-01'), '%Y%m'),
						DATE_FORMAT(CONCAT(kpis.ynam,'-',kpis.mnam,'-01'), '%Y%m')
					) BETWEEN 0 AND 2
			)
			/ 90
		),
		2
	) AS yval,
	DATE_FORMAT(CONCAT(ynam,'-',mnam,'-01'), '%Y-%m') AS xval
FROM
	fin_kpi AS T1
	LEFT JOIN locations AS locs ON T1.buid=locs.id
WHERE
	PERIOD_DIFF(
		DATE_FORMAT(NOW(), '%Y%m'),
		DATE_FORMAT(CONCAT(ynam,'-',mnam,'-01'), '%Y%m')
	) <= 12
	$WHERE
GROUP BY
	zval,
	xval
EOF;
    $rows = tldUtils::getSqlToAssocArray($query);
    $Graph =_doBarGraph($rows,$options);
break;
case 'VWCClosedRate':
	error_reporting(0); // for dev
    //initialize the array of option that will be use in this graph
    $options["graphTitle"]="VWC Closed Rate % for Previous 12 Months";
    $options["GPTargetVal"]=$help["VWC Closed Rate Group Target"];
    $options["showMarkers"]="true";

    global $kernel;
    $client = $kernel->getContainer()->get(Client::class);
    $locationIri = 'ALL';
    try {
        $location = $client->findOneBy('locations', ['legacyId' => $BUID]);
        $locationIri = $location->getIri();
    } catch (\RangeException $e) {
        // do nothing
    }

    try {
        $data = $client->get("reports/resource=/purchasing/vendor_warranty_claims;x=vwc_closed_rate;y=$locationIri");
    } catch (Exception $e) {
        $DEFAULT_ERROR[] = 'ERROR: Could not get API client. Reason: ' . $e->getMessage();
        return;
    }

    $rows = [];
    foreach ($data['rows'] as $months => $locations) {
        foreach ($locations as $values) {
            $rows[] = [
                'zval' => $values['y'],
                'xval' => $values['x'],
                'yval' => $values['value'],
            ];
        }
    }

    $Graph =_doBarGraph($rows,$options);
break;
case 'VWCResolvedRate':
	error_reporting(0); // for dev
    //initialize the array of option that will be use in this graph
    $options["graphTitle"]="VWC Resolved Rate % for Previous 12 Months";
    $options["GPTargetVal"]=$help["VWC Resolved Rate Group Target"];
    $options["showMarkers"]="true";

    global $kernel;
    $client = $kernel->getContainer()->get(Client::class);
    $locationIri = 'ALL';
    try {
        $location = $client->findOneBy('locations', ['legacyId' => $BUID]);
        $locationIri = $location->getIri();
    } catch (\RangeException $e) {
        // do nothing
    }

    try {
        $data = $client->get("reports/resource=/purchasing/vendor_warranty_claims;x=vwc_resolved_rate;y=$locationIri");
    } catch (Exception $e) {
        $DEFAULT_ERROR[] = 'ERROR: Could not get API client. Reason: ' . $e->getMessage();
        return;
    }

    $rows = [];
    foreach ($data['rows'] as $months => $locations) {
        foreach ($locations as $values) {
            $rows[] = [
                'zval' => $values['y'],
                'xval' => $values['x'],
                'yval' => $values['value'],
            ];
        }
    }

    $Graph =_doBarGraph($rows,$options);
break;
case 'VWCResolvedRateByBuyer':
	error_reporting(0); // for dev
    //initialize the array of option that will be use in this graph
    $options["graphTitle"]="VWC Resolved Rate % for Previous 12 Months By Buyer".($BUID !== "ALL"?" In ".tldLocation::getLocationByID($BUID):"");
    $options["GPTargetVal"]=$help["VWC Resolved Rate Group Target"];
    $options["showMarkers"]="true";
    $WHERE = "";
    if($BUID !== "ALL"){
        $WHERE = "AND v.erp=$erp";
    }
	$query=<<<EOF
	SELECT
		DATE_FORMAT(v.date, '%Y-%m') AS xval,
		v.status,
		v.erp,
		v.suno
	FROM
		vwc v
	WHERE
		PERIOD_DIFF(DATE_FORMAT(NOW(), '%Y%m'), DATE_FORMAT(v.date, '%Y%m')) <= 12 AND
		v.status LIKE 'CLOSED%'
		$WHERE
	ORDER BY
		v.date
EOF;
    $rows = tldUtils::getSqlToAssocArray($query);
    $dataset = $buyers = array();
    foreach($rows AS $row){
    	if(!isset($buyers[$row['erp']][$row['suno']])){
			$buyer = NULL;
			if($row['suno'] AND $row['erp']){
				$sup = new tldERPVendor($row['suno'], $row['erp']);
				$u = new tldUser($sup->getBuyerEmail());
				$buyer = $u->getFullname();
			}
			$buyers[$row['erp']][$row['suno']] = (!empty($buyer)) ? $buyer : 'MLM';
    	}
    	$dataset[$row['xval']][$buyers[$row['erp']][$row['suno']]]['CNT_CLOSED'] += 1;
    	if('CLOSED_RESOLVED' === $row['status']) $dataset[$row['xval']][$buyers[$row['erp']][$row['suno']]]['CNT_CLOSED_RESOLVED'] += 1;
    	$dataset[$row['xval']][$row['erp']];
    }
    $rows = array();
    foreach($dataset AS $xval => $row){
    	foreach($row AS $zval => $values){
    		$rows[] = array(
    			'xval' => $xval,
    			'zval' => $zval,
    			'yval' => round(($values['CNT_CLOSED_RESOLVED'] / $values['CNT_CLOSED']) * 100) . '%'
    		);
    	}
    }
    $Graph =_doBarGraph($rows,$options);
break;
case 'VWCAvgResolvedTime':
	error_reporting(0); // for dev
    //initialize the array of option that will be use in this graph
    $options["graphTitle"]="VWC Average Resolved Time (in days) for Previous 12 Months";
    $options["GPTargetVal"]=$help["VWC Resolved Rate Group Target"];
    $options["showMarkers"]="true";

    global $kernel;
    $client = $kernel->getContainer()->get(Client::class);

    $parameters['location'] = 'ALL';
    if($buid !== "ALL"){
        try {
            $location = $client->findOneBy('locations', ['legacyId' => $buid]);
            $parameters['location']= $location->getIri();
            $options["graphTitle"] .= " in ".tldLocation::getLocationByID($buid);
        } catch (\RangeException $e) {
            // do nothing
        }
    }

    try {
        $data = $client->get(
            'reports/resource=/purchasing/vendor_warranty_claims;x=resolved_time;y=location',
            ['query' => ['options' => $parameters]]
        );
    } catch (Exception $e) {
        $DEFAULT_ERROR[] = 'ERROR: Could not get API client. Reason: ' . $e->getMessage();
        return;
    }

    $rows = [];
    foreach ($data['rows'] as $months => $locations) {
        foreach ($locations as $values) {
            $rows[] = [
                'zval' => $values['y'],
                'xval' => $values['x'],
                'yval' => $values['value'],
            ];
        }
    }

    $Graph =_doBarGraph($rows,$options);
break;
case 'evendorusage':
	$options["graphStyle"]="bar";
	$options["showMarkers"]="true";
	$options["graphTitle"]="Evendor system usage for Previous 12 Months(in Percentage of 100% Vendors)";
	// Create conditions
	$WHERE = "";
	if($buid !== "ALL" && is_numeric($buid)){
		$bu = new tldLocation($buid);
		$options["graphTitle"].=" - {$bu->itsDetails['location']}";
		$erp = $bu->itsDetails["erp"];
		$WHERE = "AND T4.erp=$erp ";
	}
	// Initialise kpi data
	$data = array();
	switch($erp){
		case '300':
		case '400':
		case '410':
		case '600':
		case '620':
		case '640':
		case '660':
		case '680':
		case '420':
		case '500':
		case '500':
		case '520':
		case '540':
        case '570':
			$ip ="T1.ip_address NOT LIKE '172.17.%' AND T1.ip_address NOT LIKE '192.1%' AND T1.ip_address NOT LIKE '172.16.%' AND T1.ip_address NOT LIKE '10.0.%'";
		break;
		default:
			$ip ="T1.ip_address NOT LIKE '172.17.%' AND T1.ip_address NOT LIKE '192.1%' AND T1.ip_address NOT LIKE '172.16.%' AND T1.ip_address NOT LIKE '10.0.%'";
		break;
	}
	$query = <<<EOF
 SELECT locs.location AS zval, T4.erp,
 	DATE_FORMAT( T1.dt, '%Y-%m' ) AS xval, ROUND( count(distinct user_id) , 2 ) AS yval
	FROM vendors_login_logs AS T1
	LEFT JOIN vendors AS T2 ON T2.id = T1.user_id
	LEFT JOIN vendors_suno AS T4 ON T2.id = T4.parent_id
	LEFT JOIN locations AS locs ON T4.erp=locs.erp
	WHERE PERIOD_DIFF( DATE_FORMAT( NOW( ) , '%Y%m' ) , DATE_FORMAT( T1.dt, '%Y%m' ) ) <=12
	$WHERE AND $ip AND T4.erp in (620,640,520,500,420,400,410,660,570)
	GROUP BY zval, xval, T4.erp
EOF;
	$rows = tldUtils::getSqlToAssocArray($query);
	$query1 = <<<EOF
		SELECT count(*) AS num
		FROM vendors AS T1, vendors_suno AS T4
		WHERE T1.id=T4.parent_id $WHERE and T1.enable='Y'
EOF;
	$results = tldUtils::getSqlRowToAssocArray($query1);
	$dataset =array();
	foreach($rows AS $row){

		$dataset[] = array(
				'xval' => $row['xval'],
				'zval' => $row['zval'],
				'yval' => round($row['yval']*100/$results['num'])
		);

	}
	if($buid === "ALL"){
		$dataset =array();
		foreach($rows AS $row){
		$query1 = <<<EOF
		SELECT count(*) AS num
		FROM vendors AS T1, vendors_suno AS T4
		WHERE T1.id=T4.parent_id and T1.enable='Y' and T4.erp={$row['erp']}
EOF;
		$results = tldUtils::getSqlRowToAssocArray($query1);
		$dataset[] = array(
				'xval' => $row['xval'],
				'zval' => $row['zval'],
				'yval' => round($row['yval']*100/$results['num'])
				);

		}
	}
	$Graph =_doBarGraph($dataset,$options);
break;
case 'evendorusage2':
	$options["graphStyle"]="bar";
	$options["showMarkers"]="true";
	$options["graphTitle"]="Evendor system usage for Previous 12 Months(in Percentage of 100% Active eVendors only)";
	// Create conditions
	$WHERE = "";
	if($buid !== "ALL" && is_numeric($buid)){
		$bu = new tldLocation($buid);
		$options["graphTitle"].=" - {$bu->itsDetails['location']}";
		$erp = $bu->itsDetails["erp"];
		$WHERE = "AND T4.erp=$erp ";
	}
	// Initialise kpi data
	$data = array();
	switch($erp){
		case '300':
		case '400':
		case '410':
		case '600':
		case '620':
		case '640':
		case '660':
		case '680':
		case '420':
		case '500':
		case '500':
		case '520':
		case '540':
        case '570':
			$ip ="T1.ip_address NOT LIKE '172.17.%' AND T1.ip_address NOT LIKE '192.1%' AND T1.ip_address NOT LIKE '172.1%' AND T1.ip_address NOT LIKE '10.0.%'";
		break;
		default:
			$ip ="T1.ip_address NOT LIKE '172.17.%' AND T1.ip_address NOT LIKE '192.1%' AND T1.ip_address NOT LIKE '172.1%' AND T1.ip_address NOT LIKE '10.0.%'";
		break;
	}
	$query = <<<EOF
 SELECT locs.location AS zval, T4.erp,DATE_FORMAT( T1.dt, '%Y-%m' ) AS xval, ROUND( count(distinct T4.t_suno) , 2 ) AS yval
	FROM vendors_login_logs AS T1
	LEFT JOIN vendors AS T2 ON T2.id = T1.user_id
	LEFT JOIN vendors_suno AS T4 ON T2.id = T4.parent_id
	LEFT JOIN locations AS locs ON T4.erp=locs.erp
	WHERE PERIOD_DIFF( DATE_FORMAT( NOW( ) , '%Y%m' ) , DATE_FORMAT( T1.dt, '%Y%m' ) ) <=12
	$WHERE AND $ip AND T4.erp in (620,640,520,500,420,400,410,660,570)
	GROUP BY zval, xval, T4.erp
EOF;

	$rows = tldUtils::getSqlToAssocArray($query);
	$dataset = array();
	foreach($rows AS $row){
        $erp = $row['erp'];
	    $date = $row['xval'];
	    $query1 = <<<EOF
		SELECT DISTINCT(T4.t_suno) AS t_suno
		FROM vendors AS T1, vendors_suno AS T4
		WHERE T1.id=T4.parent_id and T1.enable='Y' and T4.erp=$erp
EOF;
	    $total_sunos = tldUtils::getSqlToAssocArray($query1);
        $query2 =<<<EOF
SELECT
    distinct(LTRIM(RTRIM(T1.t_suno))) as active_suno
FROM
    dbo.ttdpur040$erp AS T1
WHERE
    SUBSTRING(convert(varchar,T1.t_odat,120), 0, 8) = '$date'
EOF;
        $opt1="odbc";
        $opt2=array("src"=>"baan");
	    $active_sunos = tldUtils::getSqlToAssocArray($query2, $opt1, $opt2);
	    $active_sunos = array_column($active_sunos,"active_suno");
	    foreach($total_sunos as $key=>$total_suno){
	        if(!in_array($total_suno['t_suno'],$active_sunos)) unset($total_sunos[$key]);
	    }

        $dataset[] = array(
	       'xval' => $row['xval'],
	       'zval' => $row['zval'],
	       'yval' => round($row['yval']*100/count($total_sunos))
        );
	}
	$Graph =_doBarGraph($dataset,$options);
	break;
case 'VWCAvgResolvedTimeByBuyer':
	error_reporting(0); // for dev
    //initialize the array of option that will be use in this graph
    $options["graphTitle"]="VWC Average Resolved Time (in days) for Previous 12 Months By Buyer".($BUID !== "ALL"?" In ".tldLocation::getLocationByID($BUID):"");
    $options["GPTargetVal"]=$help["VWC Resolved Rate Group Target"];
    $options["showMarkers"]="true";
    $WHERE = "";
    if($BUID !== "ALL"){
        $WHERE = "AND v.erp=$erp";
    }
	$query=<<<EOF
	SELECT
		DATE_FORMAT(v.date, '%Y-%m') AS xval,
		v.status,
		v.erp,
		v.suno,
		DATEDIFF
		(
			(
				SELECT
					date
				FROM
					mod_logs m
				WHERE
					m.module='VWC' AND
					m.parent_id=v.id AND
					m.comment LIKE 'Status CLOSED_RESOLVED%'
				ORDER BY
					m.date DESC
				LIMIT 1
			),
			v.date
		) AS days_open
	FROM
		vwc v
	WHERE
		PERIOD_DIFF(DATE_FORMAT(NOW(), '%Y%m'), DATE_FORMAT(v.date, '%Y%m')) <= 12 AND
		v.status='CLOSED_RESOLVED'
		$WHERE
	ORDER BY
		v.date
EOF;
    $rows = tldUtils::getSqlToAssocArray($query);
    $dataset = $buyers = array();
    foreach($rows AS $row){
    	if(!isset($buyers[$row['erp']][$row['suno']])){
			$buyer = NULL;
			if($row['suno'] AND $row['erp']){
				$sup = new tldERPVendor($row['suno'], $row['erp']);
				$u = new tldUser($sup->getBuyerEmail());
				$buyer = $u->getFullname();
			}
			$buyers[$row['erp']][$row['suno']] = (!empty($buyer)) ? $buyer : 'MLM';
    	}
    	$dataset[$row['xval']][$buyers[$row['erp']][$row['suno']]][] = $row['days_open'];
    }
    $rows = array();
    foreach($dataset AS $xval => $row){
    	foreach($row AS $zval => $values){
    		$rows[] = array(
    			'xval' => $xval,
    			'zval' => $zval,
    			'yval' => round(array_sum($values) / count($values))
    		);
    	}
    }
    $Graph =_doBarGraph($rows,$options);
break;
case 'ConfirmPOPast12Months':
	$title = "POL Confirmation % For Previous 12 Months";
	/*
	 * X = Date
	 * Y = Confirmation %
	 * #Z = TLD Rep (buyer)
	 * Z = ERP
	 */

	// Get locations
	$WHERE = '';
	if(is_numeric($BUID)){
		$WHERE = "AND id=".intval($BUID);
	}elseif($erp){
		$WHERE = "AND erp=$erp";
	}

	// Get reps
	$WHERE = '';
	if(!empty($rep)){
		$buyer = new tldUser($rep);
		$title .= " From Buyer {$buyer->getFullname()}";
		$WHERE .= " AND tld_rep_id=".intval($rep);
	}
	if(!empty($sup)){
		$title .= " From Vendor $sup";
		$WHERE .= " AND t_suno='".TldDatabase::escape($sup)."'";
	}

		$query=<<<EOF
SELECT
	t_suno
FROM
	vendors_suno
LEFT JOIN vendors on vendors.id = vendors_suno.parent_id  
WHERE
	erp=$erp AND enable = 'Y' AND
	t_suno!=''
	$WHERE
EOF;
		$rows = tldUtils::getSqlToAssocArray($query);

	// Get POLs
	$from = mktime(0,0,0,date('n'),1,date('Y')-1); // A year ago
	$dataset = [];
	foreach($rows AS $loc => $suno){
        $polines = tldPOL::byVendorERP($erp, $suno['t_suno'], ['sortField'=>'t_odat','sortOrder'=>'DESC']);
        if(!count($polines)) continue; // No lines
        foreach($polines AS $pol){
            $odat = strtotime($pol['t_odat']);
            // Check if in 12 month range
            if($odat < $from) break;
            $ym = date('Ym', $odat);
            $dataset[$erp][$ym]['total'] += 1;
            $query=<<<EOF
SELECT
	COUNT(*) AS cnt
FROM
	erp_po_ddat
WHERE
	erp=$erp AND
	t_orno={$pol['t_orno']} AND
	t_pono={$pol['t_pono']} AND
	t_ddat<>'0000-00-00'
EOF;
            $cnt = tldUtils::getSqlRowToAssocArray($query);
            if($cnt['cnt'] > 0){
                $dataset[$erp][$ym]['confirmed'] += 1;
            }
        }
	}

	// Set data
	$rows = [];
	foreach($dataset AS $loc => $array){
		foreach($array AS $date => $value){
			$rows[] = array(
				'xval' => $date,
				'yval' => round(($value['confirmed'] / $value['total']) * 100),
				'zval' => $loc
			);
		}
	}

	$rows = array_reverse($rows);

	// Get graph
    $options["graphTitle"]=$title;
    $options["GPTargetVal"]=$help["Vendor PO Confirm Group Target"];
    $options["showMarkers"]="true";
    $Graph =_doBarGraph($rows,$options);
break;
case 'eapQtyPast12Months':
    //initialize the array of option that will be use in this graph
    $options["graphTitle"]="EAP Qty Remaining Open per month for Previous 12 Months";
    $options["GPTargetVal"]=$help["EAP Qty per month Group Target"];
    $options["showMarkers"]="true";

    $WHERE = $INNER_WHERE = '';
    if($BUID !== "ALL"){
        $WHERE = "AND t1.id=$BUID";
    }

    if ($eapCategory) {
        $eapCategory = TldDatabase::escape($eapCategory);
        $INNER_WHERE = " AND eap.category = '$eapCategory' ";
    }
    $eaps = [];
    if ($meaps) {
        foreach (explode(',', $meaps) as $meap) {
            $meap = new tldMEAP(TldDatabase::escape(trim($meap)));
            if (!$meap->isEmpty()) {
                $eaps[] = implode(',', array_column(array_filter($meap->getFamilyTree(true,true), static function (array $module) { return 'EAP' === $module['module'];}), 'id'));
            }
        }
    }

    if ($eaps) {
        $param = implode(', ', $eaps);
        $INNER_WHERE = " AND eap.id IN ($param) ";
    }

    $query = <<<EOF
SELECT
	t1.location AS zval,
	p.nam_period AS xval,
    (SELECT COUNT(*) FROM eap
    	WHERE eap.status<>'REJECTED'
		AND eap.factory=t1.id
		$INNER_WHERE
        AND PERIOD_DIFF(date_format(dt_opened, '%Y%m'), nam_period) <= 0
		AND (
			PERIOD_DIFF(date_format(dt_closed, '%Y%m'), nam_period) > 0
			OR dt_closed LIKE '0000-00-00%'
		)
    ) AS yval
FROM
	fin_periods AS p,
	locations as t1
WHERE
	period_diff(p.nam_period, date_format(NOW(), '%Y%m')) BETWEEN -12 AND 0
	AND t1.factory='Y'
	$WHERE
EOF;
    $rows = tldUtils::getSqlToAssocArray($query);
    $Graph =_doBarGraph($rows,$options);
break;
case 'eapQtyClosedPast12Months':
    //initialize the array of option that will be use in this graph
    $options["graphTitle"]="EAP Qty CLOSED per month for Previous 12 Months";
    $options["GPTargetVal"]=$help["EAP Qty CLOSED per month Group Target"];
    $options["showMarkers"]="true";

    $WHERE = "";
    if($BUID !== "ALL"){
        $WHERE .= " AND bu.id=$BUID";
    }
    if ($eapCategory) {
        $eapCategory = TldDatabase::escape($eapCategory);
        $WHERE .= " AND eap.category = '$eapCategory' ";
    }

    $eaps = [];
    if ($meaps) {
        foreach (explode(',', $meaps) as $meap) {
            $meap = new tldMEAP(TldDatabase::escape(trim($meap)));
            if (!$meap->isEmpty()) {
                $eaps[] = implode(',', array_column(array_filter($meap->getFamilyTree(true,true), static function (array $module) { return 'EAP' === $module['module'];}), 'id'));
            }
        }
    }

    if ($eaps) {
        $param = implode(', ', $eaps);
        $WHERE .= " AND eap.id IN ($param) ";
    }

    $query = <<<EOF
SELECT
	bu.location AS zval,
	p.nam_period AS xval,
	count(*) AS yval
FROM
	fin_periods as p
	LEFT JOIN eap ON date_format(eap.dt_closed, '%Y%m')=p.nam_period
	LEFT JOIN locations AS bu ON eap.factory=bu.id
WHERE
	period_diff(p.nam_period, date_format(NOW(), '%Y%m')) BETWEEN -12 AND 0
	AND status='CLOSED'
    $WHERE
GROUP BY
	zval, xval
EOF;
    $rows = tldUtils::getSqlToAssocArray($query);
    $Graph =_doBarGraph($rows,$options);
break;
case 'eapByTypeQtyClosedPast12Months':
	error_reporting(0);
	//initialize the array of option that will be use in this graph
	$options["graphTitle"]="EAP Qty CLOSED per month by Type for Previous 12 Months";
	$options["GPTargetVal"]=$help["EAP Qty CLOSED per month Group Target"];
	$options["showMarkers"]="true";

	$WHERE = "";
	if($BUID !== "ALL"){
		$WHERE .= " AND bu.id=$BUID";
	}

    if ($eapCategory) {
        $eapCategory = TldDatabase::escape($eapCategory);
        $WHERE .= " AND eap.category = '$eapCategory' ";
    }

    $eaps = [];
    if ($meaps) {
        foreach (explode(',', $meaps) as $meap) {
            $meap = new tldMEAP(TldDatabase::escape(trim($meap)));
            if (!$meap->isEmpty()) {
                $eaps[] = implode(',', array_column(array_filter($meap->getFamilyTree(true,true), static function (array $module) { return 'EAP' === $module['module'];}), 'id'));
            }
        }
    }

    if ($eaps) {
        $param = implode(', ', $eaps);
        $WHERE .= " AND eap.id IN ($param) ";
    }

	$query = <<<EOF
SELECT
	(SELECT IF(mods.type is null, 'NO_TYPE', mods.type)
		FROM mod_models AS mods
		WHERE mods.parent_id=eap.id AND mods.module='EAP'
		LIMIT 1
	) AS zval,
	date_format(eap.dt_closed, '%Y-%m') AS xval,
	COUNT(DISTINCT(eap.id)) AS yval
FROM
	fin_periods as p
	LEFT JOIN eap ON date_format(eap.dt_closed, '%Y%m')=p.nam_period
	LEFT JOIN locations AS bu ON eap.factory=bu.id
WHERE
	period_diff(p.nam_period, date_format(NOW(), '%Y%m')) BETWEEN -12 AND 0
	AND status='CLOSED'
    $WHERE
GROUP BY
	zval, xval
ORDER BY
	xval
EOF;
	$rows = tldUtils::getSqlToAssocArray($query);
	$Graph =_doBarGraph($rows,$options);
break;
case 'eapQtyInProgressByBU':
    //initialize the array of option that will be use in this graph

    $options["graphTitle"]="EAP Qty In Progress Late(Months) in ".tldLocation::getLocationByID($BUID);
    $options["GPTargetVal"]=$help["EAP In Progress Late Report"];
    $options["showMarkers"]="true";

    $WHERE = "";
    if($BUID !== "ALL"){
        $WHERE = " AND bu.id=$BUID";
    }
    $query = <<<EOF
  SELECT (

SELECT IF(mods.type is null, 'NO_TYPE', mods.type)
FROM mod_models AS mods
WHERE mods.parent_id = eap.id
AND mods.module = 'EAP'
LIMIT 1
) AS zval, period_diff(date_format( NOW( ) , '%Y%m' ), date_format( eap.dt_opened, '%Y%m' ))  AS

xval,
COUNT( DISTINCT (eap.id) ) AS yval
FROM eap
LEFT JOIN locations AS bu ON eap.factory = bu.id
WHERE period_diff(date_format( NOW( ) , '%Y%m' ), date_format( eap.dt_opened, '%Y%m' ))
BETWEEN $ed+1
AND $sd
AND STATUS = 'IN PROGRESS'
$WHERE
GROUP BY zval, xval

EOF;
    $rows = tldUtils::getSqlToAssocArray($query);
    $Graph =_doBarGraph($rows,$options,$wd=76);
break;
case 'eapClosedIn72Hrs':
	error_reporting(0); // for dev
    //initialize the array of option that will be use in this graph
    $options["graphTitle"]="EAP % CLOSED In 72 Hours for Previous 12 Months";
    $options["GPTargetVal"]=$help["EAP % CLOSED In 72 Hours Group Target"];
    $options["showMarkers"]="true";
    $WHERE = "";
    if($BUID !== "ALL"){
        $WHERE = "AND eap.factory='$BUID'";
    }

    if ($eapCategory) {
        $eapCategory = TldDatabase::escape($eapCategory);
        $WHERE .= " AND eap.category = '$eapCategory' ";
    }

    $eaps = [];
    if ($meaps) {
        foreach (explode(',', $meaps) as $meap) {
            $meap = new tldMEAP(TldDatabase::escape(trim($meap)));
            if (!$meap->isEmpty()) {
                $eaps[] = implode(',', array_column(array_filter($meap->getFamilyTree(true,true), static function (array $module) { return 'EAP' === $module['module'];}), 'id'));
            }
        }
    }

    if ($eaps) {
        $param = implode(', ', $eaps);
        $WHERE .= " AND eap.id IN ($param) ";
    }

	// Get EAPs
	$query = <<<EOF
	SELECT
		eap.dt_opened,
		eap.dt_closed,
		eap.status,
		locs.location
	FROM
		eap
		LEFT JOIN locations AS locs ON eap.factory=locs.id
	WHERE
		PERIOD_DIFF(DATE_FORMAT(NOW(), '%Y%m'), DATE_FORMAT(eap.dt_opened, '%Y%m')) <= 12
		$WHERE
	ORDER BY
		eap.dt_opened
EOF;
	$eaps = tldUtils::getSqlToAssocArray($query);

	// Get dataset
	$dataset = array();
	foreach($eaps AS $eap){
		$xval = substr($eap['dt_opened'], 0, 7);
		$zval = $eap['location'];
		// Count all per month
		$dataset[$zval][$xval]['opened']++;
		// Count closed in 72 hrs
		if($eap['dt_closed'] !== '0000-00-00 00:00:00' && $eap['status'] === 'CLOSED' && (strtotime($eap['dt_closed']) - strtotime($eap['dt_opened'])) <= (60*60*72)){
			$dataset[$zval][$xval]['closed']++;
		}
	}
	// Get rows
    $rows = array();
    foreach($dataset AS $zval => $data){
    	foreach($data AS $xval => $row)
    	$rows[] = array(
    		'xval' => $xval,
    		'zval' => $zval,
    		// Calculate
    		'yval' => round(($row['closed'] / $row['opened']) * 100)
    	);
    }
    $Graph =_doBarGraph($rows,$options);
break;
case 'eapAvgDaysOpen2':
    error_reporting(0); // for dev
    //initialize the array of option that will be use in this graph
    $options["graphTitle"]="EAP Average Number of Days Open for Previous 12 Months";
    $options["GPTargetVal"]=$help["EAP Average Number Of Days Open Group Target"];
    $options["showMarkers"]="true";
    $WHERE = $INNER_WHERE = '';
    if($BUID !== "ALL"){
        $WHERE = "AND t1.id='$BUID'";
    }

    if ($eapCategory) {
        $eapCategory = TldDatabase::escape($eapCategory);
        $INNER_WHERE = " AND eap.category = '$eapCategory' ";
    }


    $eaps = [];
    if ($meaps) {
        foreach (explode(',', $meaps) as $meap) {
            $meap = new tldMEAP(TldDatabase::escape(trim($meap)));
            if (!$meap->isEmpty()) {
                $eaps[] = implode(',', array_column(array_filter($meap->getFamilyTree(true,true), static function (array $module) { return 'EAP' === $module['module'];}), 'id'));
            }
        }
    }

    if ($eaps) {
        $param = implode(', ', $eaps);
        $INNER_WHERE .= " AND eap.id IN ($param) ";
    }

    $query = <<<EOF
SELECT
	t1.location AS zval,
	p.nam_period AS xval,
    (SELECT 
        ROUND(SUM(
            TO_DAYS(LAST_DAY(DATE_FORMAT(CONCAT(p.nam_period,'01'),'%Y%m%d')))-TO_DAYS(eap.dt_opened)
		) / COUNT(*), 2)
    FROM eap
    WHERE 
        eap.factory=t1.id
        AND PERIOD_DIFF(DATE_FORMAT(eap.dt_opened, '%Y%m'), p.nam_period) < 0
        AND (eap.dt_closed LIKE '0000-00-00%' OR PERIOD_DIFF(DATE_FORMAT(eap.dt_closed, '%Y%m'), nam_period) > 0)
        $INNER_WHERE
    ) AS yval
FROM
	fin_periods AS p,
	locations as t1
WHERE
	period_diff(p.nam_period, date_format(NOW(), '%Y%m')) BETWEEN -12 AND 0
	AND t1.factory='Y'
	$WHERE
EOF;
    $rows = tldUtils::getSqlToAssocArray($query);
    $Graph =_doBarGraph($rows,$options);
break;
case 'eapAvgDaysOpen':
	error_reporting(0); // for dev
    //initialize the array of option that will be use in this graph
    $options["graphTitle"]="EAP Average Number of Days To Close for Previous 12 Months";
    $options["GPTargetVal"]=$help["EAP Average Number Of Days To Close Group Target"];
    $options["showMarkers"]="true";
    $WHERE = '';
    if($BUID !== "ALL"){
        $WHERE = "AND eap.factory='$BUID'";
    }

    if ($eapCategory) {
        $eapCategory = TldDatabase::escape($eapCategory);
        $WHERE .= " AND eap.category = '$eapCategory' ";
    }
    $eaps = [];
    if ($meaps) {
        foreach (explode(',', $meaps) as $meap) {
            $meap = new tldMEAP(TldDatabase::escape(trim($meap)));
            if (!$meap->isEmpty()) {
                $eaps[] = implode(',', array_column(array_filter($meap->getFamilyTree(true,true), static function (array $module) { return 'EAP' === $module['module'];}), 'id'));
            }
        }
    }

    if ($eaps) {
        $param = implode(', ', $eaps);
        $WHERE .= " AND eap.id IN ($param) ";
    }

	$query = <<<EOF
	SELECT
		locs.location AS zval,
		ROUND(SUM(
			TO_DAYS(eap.dt_closed)-TO_DAYS(eap.dt_opened)
		) / COUNT(*), 2) AS yval,
		DATE_FORMAT(eap.dt_closed, '%Y-%m') AS xval
	FROM
		eap
		LEFT JOIN locations AS locs ON eap.factory=locs.id
	WHERE
		PERIOD_DIFF(DATE_FORMAT(NOW(), '%Y%m'), DATE_FORMAT(eap.dt_closed, '%Y%m')) <= 12
		AND eap.status = 'CLOSED'
		$WHERE
	GROUP BY
		zval,
		xval
	ORDER BY
		eap.dt_closed
EOF;
	$rows = tldUtils::getSqlToAssocArray($query);
    $Graph =_doBarGraph($rows,$options);
break;
case 'vwcRequestRecoveryValuePast12Months':
	$options["graphTitle"]="VWC Requested Recovery Value Past 12 Months";
	$options["GPTargetVal"]=$help["VWC Requested Recovery Value Past 12 Months"];
	$options["showMarkers"]="true";

	$WHERE = "";
	if($buid !== "ALL"){
		$WHERE = " AND T2.id=$buid AND T1.status <> 'PENDING'";
	}
	$query = <<<EOF
SELECT (

SELECT CASE WHEN T1.status LIKE 'CLOSED%'
		THEN 'Closed and cost paid by supplier'
		WHEN T1.status = 'VENDOR_TO_RESPOND'
		THEN 'Waiting Supplier Credit'
		WHEN T1.status = 'ISSUE_CREDIT_NOTE'
		THEN 'Supplier Credit Accepted'
		WHEN T1.status NOT LIKE 'CLOSED%'
		AND T1.status <> 'PENDING'
		THEN 'VWC In Progress'
		END
		) AS zval,
		ROUND( SUM(T1.req_crd_amt), 2 ) AS yval,
		DATE_FORMAT( T1.date, '%Y-%m' ) AS xval
FROM vwc AS T1
	LEFT JOIN locations AS T2 ON T1.erp = T2.erp
WHERE PERIOD_DIFF( DATE_FORMAT( NOW( ) , '%Y%m' ) , DATE_FORMAT( T1.date, '%Y%m' ) ) <=12
    $WHERE

GROUP BY

	zval, xval

EOF;
	$rows = tldUtils::getSqlToAssocArray($query);
	$Graph =_doBarGraph($rows,$options);
break;
case 'vwcSupplierRecoveryCostPast12Months':
	$options["graphTitle"]="VWC Supplier Recovery Cost Past 12 Months";
	$options["GPTargetVal"]=$help["VWC Requested Recovery Value Past 12 Months"];
	$options["showMarkers"]="true";

    global $kernel;
    $client = $kernel->getContainer()->get(Client::class);

    $parameters['location'] = 'ALL';
    if($buid !== "ALL"){
        try {
            $location = $client->findOneBy('locations', ['legacyId' => $buid]);
            $parameters['location']= $location->getIri();
            $options["graphTitle"] .= " in ".tldLocation::getLocationByID($buid);
        } catch (\RangeException $e) {
            // do nothing
        }
    }
    if(!empty($suno)){
        $parameters['supplierNumber'] = $suno;
        $options["graphTitle"] .= " For Supplier {$suno}";
    }

    try {
        $data = $client->get(
            'reports/resource=/purchasing/vendor_warranty_claims;x=supplier_recovery_cost;y=report',
            ['query' => ['options' => $parameters]]
        );
    } catch (Exception $e) {
        $DEFAULT_ERROR[] = 'ERROR: Could not get API client. Reason: ' . $e->getMessage();
        return;
    }

    $rows = [];
    foreach ($data['rows'] as $months => $locations) {
        foreach ($locations as $values) {
            $rows[] = [
                'zval' => $values['y'],
                'xval' => $values['x'],
                'yval' => $values['value'],
            ];
        }
    }
    $Graph =_doBarGraph($rows,$options);
    break;
case 'NCRStatPast12Months':
    $options["graphTitle"]="NCR Open & Close Past 12 Months";
    $options["showStack"]=true;
    $options["showMarkers"]=true;

    global $kernel;
    $client = $kernel->getContainer()->get(Client::class);
    $factoryIri = 'ALL';
    try {
        $factory = $client->findOneBy('locations', ['legacyId' => $buid]);
        $factoryIri = $factory->getIri();
    } catch (\RangeException $e) {
        // do nothing
    }
    try {
        $data = $client->get("reports/resource=/quality/non_conformities;x=ncr_opened;y=$factoryIri");
    } catch (Exception $e) {
        $DEFAULT_ERROR[] = 'ERROR: Could not get API client. Reason: ' . $e->getMessage();
        return;
    }

    $rows = [];
    foreach ($data['rows'] as $months => $statuses) {
        foreach ($statuses as $values) {
            $rows[] = [
                'zval' => $values['y'],
                'xval' => $values['x'],
                'yval' => $values['value'],
            ];
        }
    }

    $Graph =_doBarGraph($rows,$options);
break;
case 'NCRResponsibilityPast12Months':
    $options["graphTitle"]="NCR Responsibility Past 12 Months";
    $options["showStack"]= true;
    $options["showMarkers"]=false;

    global $kernel;
    $client = $kernel->getContainer()->get(Client::class);
    $factoryIri = 'ALL';
    try {
        $factory = $client->findOneBy('locations', ['legacyId' => $buid]);
        $factoryIri = $factory->getIri();
    } catch (\RangeException $e) {
        // do nothing
    }
    try {
        $data = $client->get("reports/resource=/quality/non_conformities;x=ncr_by_responsible;y=$factoryIri");
    } catch (Exception $e) {
        $DEFAULT_ERROR[] = 'ERROR: Could not get API client. Reason: ' . $e->getMessage();
        return;
    }

    $rows = [];
    foreach ($data['rows'] as $months => $statuses) {
        foreach ($statuses as $values) {
            $rows[] = [
                'zval' => $values['y'],
                'xval' => $values['x'],
                'yval' => $values['value'],
            ];
        }
    }

    $Graph =_doBarGraph($rows,$options);
break;

case 'NCRProcessPast12Months':
    $options["graphTitle"]="NCR Process Past 12 Months";
    $options["showStack"]= true;
    $options["showMarkers"]=false;

    global $kernel;
    $client = $kernel->getContainer()->get(Client::class);
    $factoryIri = 'ALL';
    try {
        $factory = $client->findOneBy('locations', ['legacyId' => $buid]);
        $factoryIri = $factory->getIri();
    } catch (\RangeException $e) {
        // do nothing
    }
    try {
        $data = $client->get("reports/resource=/quality/non_conformities;x=ncr_by_process;y=$factoryIri");
    } catch (Exception $e) {
        $DEFAULT_ERROR[] = 'ERROR: Could not get API client. Reason: ' . $e->getMessage();
        return;
    }

    $rows = [];
    foreach ($data['rows'] as $months => $statuses) {
        foreach ($statuses as $values) {
            $rows[] = [
                'zval' => $values['y'],
                'xval' => $values['x'],
                'yval' => $values['value'],
            ];
        }
    }

    $Graph =_doBarGraph($rows,$options);
break;
}
