<?php
// initialze the start and end date for the History case
$ds_calculate = TldDatabase::escape($_GET['ds']);
$de_calculate = TldDatabase::escape($_GET['de']);
//initialize the array of option that will be use in the graphs
$options = array(
    "graphTitle"   =>"",
    "graphStyle"   =>"bar",
    "GPTargetVal"  =>"NA",
    "showMarkers"  =>"false"
);
// check the month difference between the start date and end date
// if difference superior to 12 month then setup the legend of the x axis to be vertical
list($de_y, $de_m) = explode("-", $de_calculate);
list($ds_y, $ds_m) = explode("-", $ds_calculate);
$d2=date(mktime(0, 0, 0, $de_m, 1, $de_y));
$d1=date(mktime(0, 0, 0, $ds_m, 1, $ds_y));      
if((floor(($d2-$d1)/2628000))>12) $options["axisXAngle"]='vertical';


switch($m[1]){
case 'toc':
    $tocGraphName = strtoupper($m[2]);
    $options = array(
        "graphTitle"   =>"TOC KPI - $tocGraphName",
        "graphStyle"   =>"bar",
        "GPTargetVal"  =>"NA",
        "showMarkers"  =>"true"
    );
    // Common toc constraints for all kpi
    switch($m[3]){
    case 'byCustomerIDSSOID':
    	if(empty($id) || !is_numeric($id) || empty($id2) || !is_numeric($id2) || empty($ds) || empty($de)){
    		exit;
    	}
    	$cu = new tldCustomer($id);
    	$bu = new tldLocation($id2);
    	$legend = $cu->getCustomerName()." - ".$bu->getShortName();
    	$WHERE = "AND cuid=$id AND ssoid=$id2";
    	$ds_array = explode('-', $ds);
    	$de_array = explode('-', $de);
    	$ds = vsprintf('%1$04d%2$02d', $ds_array);
    	$de = vsprintf('%1$04d%2$02d', $de_array);
    	$WHERE_PERIOD = "p.nam_period BETWEEN '$ds' AND '$de'";
    break;    
    case 'bySSOID':
        if(empty($id) || !is_numeric($id) || empty($ds) || empty($de)){
            exit;
        }
        $location = new tldLocation($id);
        $legend = $location->itsDetails['location'];
        $WHERE = "AND ssoid=$id";
        $WHERE_PERIOD = "p.nam_period BETWEEN '$ds' AND '$de'";
    break;
    case 'byTecID':
        if(empty($id) || !is_numeric($id) || empty($ds) || empty($de)){
            exit;
        }
        $tecUser = new tldUser($id);
        $legend = $tecUser->getFullname();
        $WHERE = "AND tecid=$id";
        $WHERE_PERIOD = "p.nam_period BETWEEN '$ds' AND '$de'";
    break;
    }
    $options['graphTitle'].=" - $legend";
    // Do kpi queries
    switch($m[2]){
    case 'nto':
        $query=<<<EOF
SELECT
    '$legend' AS factories,
    nam_period AS xval,
    ROUND(
    (SELECT COUNT(*) FROM toc
        WHERE DATE_FORMAT(dt, '%Y%m') = nam_period $WHERE
    ),1) AS yval
FROM
    fin_periods AS p
WHERE
    $WHERE_PERIOD
ORDER BY
    nam_period
EOF;
        $rows = tldUtils::getSqlToAssocArray($query);
        if(empty($rows)) $rows=array();
        $Graph=_doBarGraph($rows,$options);
    break;
    case 'nts':
        $query=<<<EOF
SELECT
    '$legend' AS factories,
    nam_period AS xval,
    ROUND(
    (SELECT COUNT(*) FROM toc
        WHERE DATE_FORMAT(dt_closed, '%Y%m')=nam_period $WHERE
    ),1) AS yval
FROM
    fin_periods AS p
WHERE
    $WHERE_PERIOD
ORDER BY
    nam_period
EOF;
        $rows = tldUtils::getSqlToAssocArray($query);

        if(empty($rows)) $rows=array();
        $Graph=_doBarGraph($rows,$options);
    break;
    case 'tir':
        $query=<<<EOF
SELECT
    '$legend' AS factories,
    nam_period AS xval,
    ROUND(
        (SELECT 
        	SUM(
				CASE
					WHEN toc_zones.zone = 'G' AND TIMESTAMPDIFF(HOUR,dt,dt_closed)<=48 THEN 1
					WHEN toc_zones.zone = 'G' AND TIMESTAMPDIFF(HOUR,dt,dt_closed)>48 THEN 0
					WHEN toc_zones.zone = 'B' AND TIMESTAMPDIFF(HOUR,dt,dt_closed)<=72 THEN 1
					WHEN toc_zones.zone = 'B' AND TIMESTAMPDIFF(HOUR,dt,dt_closed)>72 THEN 0
					WHEN toc_zones.zone = 'Y' AND TIMESTAMPDIFF(HOUR,dt,dt_closed)<=120 THEN 1
					WHEN toc_zones.zone = 'Y' AND TIMESTAMPDIFF(HOUR,dt,dt_closed)>120 THEN 0
					WHEN toc_zones.zone = 'R' AND TIMESTAMPDIFF(HOUR,dt,dt_closed)<=168 THEN 1
					WHEN toc_zones.zone = 'R' AND TIMESTAMPDIFF(HOUR,dt,dt_closed)>168 THEN 0
				END
			)
        FROM toc
        LEFT JOIN airport_codes AS apc ON apc.airport_code = toc.apc AND apc.type LIKE 'Airport'
        LEFT JOIN airport_codes as apc2 ON apc2.airport_code = toc.apc AND apc2.type LIKE "Airport" AND apc2.city_name > apc.city_name
		LEFT JOIN countries ON countries.iso_code_2 = apc.ctry_code_2
		LEFT JOIN toc_zones ON toc_zones.parent_id = countries.id
        WHERE DATE_FORMAT(dt_closed, '%Y%m')=nam_period AND apc2.id IS NULL
        $WHERE
        )
        /
        (SELECT COUNT(*) FROM toc
            WHERE DATE_FORMAT(dt_closed, '%Y%m')=nam_period
            $WHERE
        ),1
    )*100 AS yval
FROM
    fin_periods AS p
WHERE
    $WHERE_PERIOD
ORDER BY
    nam_period
EOF;
        $rows = tldUtils::getSqlToAssocArray($query);

        if(empty($rows)) $rows=array();
        $Graph=_doBarGraph($rows,$options);
    break;
    case 'tat':
        $query=<<<EOF
SELECT
    '$legend' AS factories,
    nam_period AS xval,
    ROUND(
    (SELECT
            SUM(
                TIMESTAMPDIFF(DAY,dt,dt_closed)
                -(SELECT fct_tld_mod_toc_getDaysSuspendedByID(toc.id))
            )/COUNT(*)
        FROM toc
        WHERE
            DATE_FORMAT(dt_closed, '%Y%m')=nam_period
            $WHERE
    ),1) AS yval
FROM
    fin_periods AS p
WHERE
    $WHERE_PERIOD
ORDER BY
    nam_period
EOF;
        $rows = tldUtils::getSqlToAssocArray($query);

        if(empty($rows)) $rows=array();
        $Graph=_doBarGraph($rows,$options);
    break;
    case 'tol':
        $query=<<<EOF
SELECT
    '$legend' AS factories,
    nam_period AS xval,
    ROUND(
    (SELECT 
        MAX(
            TIMESTAMPDIFF(
                DAY,
                DATE_FORMAT(dt,'%Y-%m-%d'),
                LAST_DAY(CONCAT(SUBSTRING(p.nam_period,1,4), '-', SUBSTRING(p.nam_period,5,2), '-01'))
            )
        )
        FROM toc
        WHERE
            PERIOD_DIFF(DATE_FORMAT(dt, '%Y%m'), p.nam_period) <= 0
            AND (
              PERIOD_DIFF(DATE_FORMAT(dt_closed, '%Y%m'), p.nam_period) > 0
              OR TO_DAYS(dt_closed) IS NULL
            )
            $WHERE
    ),1) AS yval
FROM
    fin_periods AS p
WHERE
    $WHERE_PERIOD
ORDER BY
    nam_period
EOF;
        $rows = tldUtils::getSqlToAssocArray($query);

        if(empty($rows)) $rows=array();
        $Graph=_doBarGraph($rows,$options);
    break;
    }
break;
}
