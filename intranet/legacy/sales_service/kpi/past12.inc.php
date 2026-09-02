<?php

//initialize the array of option that will be use in the graphs
$options = [
    "graphTitle" => "",
    "graphStyle" => "bar",
    "GPTargetVal" => "NA",
    "showMarkers" => "false",
];

switch ($m[1]) {
    case 'orderStatsByCustType':
        $ERP = TldDatabase::escape($erp);
        $type = TldDatabase::escape($type);

        $options["graphTitle"] = "Order statistic by $type customer type";
        $options["showMarkers"] = "true";

        // Get home currency of the ERP
        $query = "SELECT t1.t_ccur FROM tttaad100000 AS t1 WHERE t1.t_comp=$ERP";
        $CUR = tldUtils::getSqlRowToAssocArray($query, 'odbc', ['src' => 'baan']);
        if ($CUR['t_ccur'] === 'ECU') {
            $CUR['t_ccur'] = 'EUR';
        }
        $options["graphTitle"] .= " in K ".$CUR['t_ccur']." for ".$ERP_LIST[$ERP];

        // Look which field should be used
        switch ($ERP) {
            case 500:
            case 520:
            case 540:
            case 600:
            case 610:
            case 640:
            case 660:
            case 700:
                $field = "t_cbrn";  // line of buisness
                break;
            default:
                $field = "t_cfcg";  // customer group
                break;
        }

        if ($type !== 'ALL') {
            // Check if multiple code for a type of customer for this $ERP
            if (is_array($X_REF_CUST_TYPE[$ERP][$type])) {
                $i = 0;
                foreach ($X_REF_CUST_TYPE[$ERP][$type] as $code) {
                    if ($i == 0) {
                        $WHERE = " AND ( t3.$field = '$code'";
                    } else {
                        $WHERE .= " OR t3.$field = '$code'";
                    }
                    $i++;
                }
                $WHERE .= ")";
            } else {
                $WHERE = " AND t3.$field = '{$X_REF_CUST_TYPE[$ERP][$type]}'";
            }
        }

        $query = <<<EOF
SELECT
    '$ERP_LIST[$ERP]' AS factories,
    SUBSTRING(convert(varchar, t2.t_odat, 120), 0 , 8) AS xval,
    CONVERT(VARCHAR(100),CAST(SUM(t1.t_amnt*t1.t_rats)/1000 AS DECIMAL(15,1))) AS yval
FROM
    ttdsls045$ERP AS t1
    LEFT JOIN ttdsls040$ERP AS t2 ON t2.t_orno = t1.t_orno
    LEFT JOIN ttccom010$ERP AS t3 ON t3.t_cuno = t1.t_cuno
WHERE
    DATEDIFF(month, t2.t_odat, GETDATE()) <= 12
    $WHERE
GROUP BY SUBSTRING(convert(varchar, t2.t_odat, 120), 0 , 8)
ORDER BY SUBSTRING(convert(varchar, t2.t_odat, 120), 0 , 8)
EOF;

        $rows = tldUtils::getSqlToAssocArray($query, 'odbc', ['src' => 'baan']);

        if (empty($rows)) {
            $rows = [];
        }
        $Graph = _doBarGraph($rows, $options);
        break;
    case 'shipStatsByCustType':
        $ERP = TldDatabase::escape($erp);
        $type = TldDatabase::escape($type);

        $options["graphTitle"] = "Shipments statistic by $type customer type";
        $options["showMarkers"] = "true";

        // Get home currency of the ERP
        $query = "SELECT t1.t_ccur FROM tttaad100000 AS t1 WHERE t1.t_comp=$ERP";
        $CUR = tldUtils::getSqlRowToAssocArray($query, 'odbc', ['src' => 'baan']);
        if ($CUR['t_ccur'] === 'ECU') {
            $CUR['t_ccur'] = 'EUR';
        }
        $options["graphTitle"] .= " in K ".$CUR['t_ccur']." for ".$ERP_LIST[$ERP];

        // Look which field should be used
        switch ($ERP) {
            case 500:
            case 520:
            case 540:
            case 600:
            case 610:
            case 640:
            case 660:
            case 700:
                $field = "t_cbrn";  // line of buisness
                break;
            default:
                $field = "t_cfcg";  // customer group
                break;
        }

        if ($type !== 'ALL') {
            // Check if multiple code for a type of customer for this $ERP
            if (is_array($X_REF_CUST_TYPE[$ERP][$type])) {
                $i = 0;
                foreach ($X_REF_CUST_TYPE[$ERP][$type] as $code) {
                    if ($i == 0) {
                        $WHERE = " AND ( t3.$field = '$code'";
                    } else {
                        $WHERE .= " OR t3.$field = '$code'";
                    }
                    $i++;
                }
                $WHERE .= ")";
            } else {
                $WHERE = " AND t3.$field = '{$X_REF_CUST_TYPE[$ERP][$type]}'";
            }
        }

        $query = <<<EOF
SELECT
    '$ERP_LIST[$ERP]' AS factories,
    SUBSTRING(convert(varchar, t1.t_invd, 120), 0 , 8) AS xval,
	CONVERT(VARCHAR(100),CAST(SUM(t1.t_amnt*t1.t_rats)/1000 AS DECIMAL(15,1))) AS yval
FROM
    ttdsls045$ERP AS t1
    LEFT JOIN ttdsls040$ERP AS t2 ON t2.t_orno = t1.t_orno
    LEFT JOIN ttccom010$ERP AS t3 ON t3.t_cuno = t1.t_cuno
WHERE
    DATEDIFF(month, t1.t_invd, GETDATE()) <= 12
    $WHERE
GROUP BY SUBSTRING(convert(varchar, t1.t_invd, 120), 0 , 8)
ORDER BY SUBSTRING(convert(varchar, t1.t_invd, 120), 0 , 8)
EOF;

        $rows = tldUtils::getSqlToAssocArray($query, 'odbc', ['src' => 'baan']);

        if (empty($rows)) {
            $rows = [];
        }
        $Graph = _doBarGraph($rows, $options);
        break;
    case "sfrStatsOrdered":
        if (!empty($erp) && is_numeric($erp)) {
            $WHERE = "AND sfr.erp_id=$erp ";
            $id = $erp;
        }
        if (!empty($sso) && is_numeric($sso)) {
            $WHERE = "AND sfr.sso_id=$sso ";
            $id = $sso;
        }
        $location = new tldLocation($id);
        if (empty($location->itsDetails['location'])) {
            $location->itsDetails['location'] = 'ALL';
        }
        $options = [
            "graphTitle" => "Ordered SFR statistic - ".$location->itsDetails['location'],
            "graphStyle" => "bar",
            "GPTargetVal" => "NA",
            "showMarkers" => "true",
        ];
        $CLOSED_STATUS = "'".implode("','", array_keys(tldSFR::getCloseStatuses()))."'";
        $query = <<<EOF
SELECT
    'SFR in %' AS factories,
    SUBSTRING(sfr.dt_closed,1,7) AS xval,
    ROUND(COUNT(sfr2.id)*100/COUNT(sfr.id),1) AS yval
FROM
    sfr LEFT JOIN sfr as sfr2 ON sfr2.id=sfr.id AND sfr2.status='ORDERED'
WHERE
    period_diff( date_format( NOW(),'%Y%m') , date_format(sfr.dt_closed,'%Y%m') ) <= 12 
    AND sfr.status IN ($CLOSED_STATUS)
    $WHERE
GROUP BY SUBSTRING(sfr.dt_closed,1,7)
ORDER BY SUBSTRING(sfr.dt_closed,1,7)
EOF;
        $rows = tldUtils::getSqlToAssocArray($query);

        if (empty($rows)) {
            $rows = [];
        }
        $Graph = _doBarGraph($rows, $options);
        break;
    case "sfrStatsLost":
        if (!empty($erp) && is_numeric($erp)) {
            $WHERE = "AND sfr.erp_id=$erp ";
            $id = $erp;
        }
        if (!empty($sso) && is_numeric($sso)) {
            $WHERE = "AND sfr.sso_id=$sso ";
            $id = $sso;
        }
        $location = new tldLocation($id);
        if (empty($location->itsDetails['location'])) {
            $location->itsDetails['location'] = 'ALL';
        }
        $options = [
            "graphTitle" => "Lost SFR statistic - ".$location->itsDetails['location'],
            "graphStyle" => "bar",
            "GPTargetVal" => "NA",
            "showMarkers" => "true",
        ];
        $CLOSED_STATUS = "'".implode("','", array_keys(tldSFR::getCloseStatuses()))."'";
        $query = <<<EOF
SELECT
    'SFR in %' AS factories,
    SUBSTRING(sfr.dt_closed,1,7) AS xval,
    ROUND(COUNT(sfr2.id)*100/COUNT(sfr.id),1) AS yval
FROM
    sfr LEFT JOIN sfr as sfr2 ON sfr2.id=sfr.id AND sfr2.status='LOST'
WHERE
    period_diff( date_format( NOW(),'%Y%m') , date_format(sfr.dt_closed,'%Y%m') ) <= 12 
    AND sfr.status IN ($CLOSED_STATUS)
    $WHERE
GROUP BY SUBSTRING(sfr.dt_closed,1,7)
ORDER BY SUBSTRING(sfr.dt_closed,1,7)
EOF;
        $rows = tldUtils::getSqlToAssocArray($query);

        if (empty($rows)) {
            $rows = [];
        }
        $Graph = _doBarGraph($rows, $options);
        break;
    case "sfrCloseAverage":
        if (!empty($erp) && is_numeric($erp)) {
            $WHERE = "AND sfr.erp_id=$erp ";
            $id = $erp;
        }
        if (!empty($sso) && is_numeric($sso)) {
            $WHERE = "AND sfr.sso_id=$sso ";
            $id = $sso;
        }
        $location = new tldLocation($id);
        if (empty($location->itsDetails['location'])) {
            $location->itsDetails['location'] = 'ALL';
        }
        $options = [
            "graphTitle" => "Average closure of SFR - ".$location->itsDetails['location'],
            "graphStyle" => "bar",
            "GPTargetVal" => "NA",
            "showMarkers" => "true",
        ];
        $CLOSED_STATUS = "'".implode("','", array_keys(tldSFR::getCloseStatuses()))."'";
        $query = <<<EOF
SELECT
    'Average in Months' AS factories,
    SUBSTRING(sfr.dt_closed,1,7) AS xval,
    ROUND(SUM(period_diff(date_format(sfr.dt_closed,'%Y%m') , date_format( sfr.dt,'%Y%m')))/COUNT(sfr.id),1) AS yval
FROM
    sfr
WHERE
    period_diff( date_format( NOW(),'%Y%m') , date_format(sfr.dt_closed,'%Y%m') ) <= 12 
    AND sfr.status IN ($CLOSED_STATUS)
    $WHERE
GROUP BY SUBSTRING(sfr.dt_closed,1,7)
ORDER BY SUBSTRING(sfr.dt_closed,1,7)
EOF;
        $rows = tldUtils::getSqlToAssocArray($query);

        if (empty($rows)) {
            $rows = [];
        }
        $Graph = _doBarGraph($rows, $options);
        break;
    case "DaysOfSalesOut":
        if (!empty($erp) && is_numeric($erp)) {
            $WHERE = "AND sfr.erp_id=$erp ";
            $id = $erp;
        }
        if (!empty($sso) && is_numeric($sso)) {
            $WHERE = "AND sfr.sso_id=$sso ";
            $id = $sso;
        }

        // get location
        $query = "SELECT location FROM locations WHERE id=$sso";
        $loc = tldUtils::getSqlRowToAssocArray($query);

        $location = new tldLocation($id);
        if (empty($location->itsDetails['location'])) {
            $location->itsDetails['location'] = 'ALL';
        }
        $options = [
            "graphTitle" => "Days of Sales Out for Previous 12 month - ".$location->itsDetails['location'],
            "graphStyle" => "bar",
            "GPTargetVal" => "NA",
            "showMarkers" => "true",
        ];
        $query = <<<EOF
SELECT 
    locs.location as factories, 
    ROUND(T1.sso_tot_ar/( 
        (SELECT sum(kpis.sso_tot_sls) 
        FROM locations_kpi_sso AS kpis 
        WHERE kpis.parent_id=T1.parent_id 
        AND PERIOD_DIFF( 
            DATE_FORMAT(CONCAT(T1.ynam,'-',T1.mnam,'-01'), '%Y%m'), 
            DATE_FORMAT(CONCAT(kpis.ynam,'-',kpis.mnam,'-01'), '%Y%m') 
        ) between 0 and 
        CASE locs.location WHEN 'TLD AME' THEN 0 WHEN 'TLD EUR' THEN 1 WHEN 'TLD ASI' THEN 2 END 
        )/CASE locs.location WHEN 'TLD AME' THEN 30 WHEN 'TLD EUR' THEN 60 WHEN 'TLD ASI' THEN 90 END 
        ), 1) 
    as yval, 
    DATE_FORMAT(CONCAT(ynam,'-',mnam,'-01'), '%Y-%m') AS xval 
FROM 
    locations_kpi_sso AS T1 LEFT JOIN locations AS locs ON T1.parent_id=locs.id 
WHERE
    T1.parent_id = $sso and 
    DATE_FORMAT(CONCAT(ynam,'-',mnam,'-01'), '%Y-%m-%d') >= DATE_SUB(now(), INTERVAL 12 MONTH)   
GROUP BY 
    factories, xval 
EOF;
        $rows = tldUtils::getSqlToAssocArray($query);

        if (empty($rows)) {
            $rows = [];
        }
        $Graph = _doBarGraph($rows, $options);
        break;
    case "DaysOfSalesOutFG":
        if (!empty($erp) && is_numeric($erp)) {
            $WHERE = "AND sfr.erp_id=$erp ";
            $id = $erp;
        }
        if (!empty($sso) && is_numeric($sso)) {
            $WHERE = "AND sfr.sso_id=$sso ";
            $id = $sso;
        }

        // get location
        $query = "SELECT location FROM locations WHERE id=$sso";
        $loc = tldUtils::getSqlRowToAssocArray($query);

        $location = new tldLocation($id);
        if (empty($location->itsDetails['location'])) {
            $location->itsDetails['location'] = 'ALL';
        }
        $options = [
            "graphTitle" => "Days of Sales Out for Previous 12 month - ".$location->itsDetails['location'],
            "graphStyle" => "bar",
            "GPTargetVal" => "NA",
            "showMarkers" => "true",
        ];
        $query = <<<EOF
SELECT  
    locs.location as factories, 
    ROUND((T1.sso_tot_ar + T1.sso_fin_gds)/ 
    ((SELECT 
        sum(kpis.sso_tot_sls) 
    FROM 
        locations_kpi_sso AS kpis 
    WHERE 
        kpis.parent_id=T1.parent_id AND  
        PERIOD_DIFF( 
        DATE_FORMAT(CONCAT(T1.ynam,'-',T1.mnam,'-01'), '%Y%m'), 
        DATE_FORMAT(CONCAT(kpis.ynam,'-',kpis.mnam,'-01'), '%Y%m') 
        ) between 0 and 
        CASE locs.location WHEN 'TLD AME' THEN 0 WHEN 'TLD EUR' THEN 1 WHEN 'TLD ASI' THEN 2 END 
        )/CASE locs.location WHEN 'TLD AME' THEN 30 WHEN 'TLD EUR' THEN 60 WHEN 'TLD ASI' THEN 90 END 
        ), 1) as yval, 
    DATE_FORMAT(CONCAT(ynam,'-',mnam,'-01'), '%Y-%m') AS xval 
FROM 
    locations_kpi_sso AS T1 LEFT JOIN locations AS locs ON T1.parent_id=locs.id
WHERE 
    T1.parent_id = $sso and 
    DATE_FORMAT(CONCAT(ynam,'-',mnam,'-01'), '%Y-%m-%d') >= DATE_SUB(now(), INTERVAL 12 MONTH)                   
GROUP BY 
    factories, xval 
EOF;
        $rows = tldUtils::getSqlToAssocArray($query);

        if (empty($rows)) {
            $rows = [];
        }
        $Graph = _doBarGraph($rows, $options);
        break;
    case 'toc':
        $tocGraphName = strtoupper($m[2]);
        $options = [
            "graphTitle" => "TOC KPI - $tocGraphName",
            "graphStyle" => "bar",
            "GPTargetVal" => "NA",
            "showMarkers" => "true",
        ];
        // Common toc constraints for all kpi
        switch ($m[3]) {
            case 'byCustomerIDSSOID':
                if (empty($id) || !is_numeric($id) || empty($id2) || !is_numeric($id2)) {
                    exit;
                }
                $cu = new tldCustomer($id);
                $bu = new tldLocation($id2);
                $legend = $cu->getCustomerName()." - ".$bu->getShortName();
                $WHERE = "AND cuid=$id AND ssoid=$id2";
                break;
            case 'bySSOID':
                if (empty($id) || !is_numeric($id)) {
                    exit;
                }
                $location = new tldLocation($id);
                $legend = $location->itsDetails['location'];
                $WHERE = "AND ssoid=$id";
                break;
            case 'byTecID':
                if (empty($id) || !is_numeric($id)) {
                    exit;
                }
                $tecUser = new tldUser($id);
                $legend = $tecUser->getFullname();
                $WHERE = "AND tecid=$id";
                break;
            case 'byActivityType':
                if (empty($id) || !is_numeric($id) || null === $activityType) {
                    exit;
                }
                $bu = new tldLocation($id);
                $legend = sprintf('%s - %s', $bu->getShortName(), $activityType === '' ? 'Other' : $activityType);
                $WHERE = sprintf("AND ssoid=%s AND activity_type='%s'", $id, $activityType);
                break;
        }
        $options['graphTitle'] .= " - $legend";
        // Do kpi queries
        switch ($m[2]) {
            case 'nto':
                $query = <<<EOF
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
    PERIOD_DIFF(DATE_FORMAT(NOW(), '%Y%m'), p.nam_period) BETWEEN 1 AND 12
ORDER BY
    nam_period
EOF;
                $rows = tldUtils::getSqlToAssocArray($query);
                if (empty($rows)) {
                    $rows = [];
                }
                $Graph = _doBarGraph($rows, $options);
                break;
            case 'nts':
                $query = <<<EOF
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
    PERIOD_DIFF(DATE_FORMAT(NOW(), '%Y%m'), p.nam_period) BETWEEN 1 AND 12
ORDER BY
    nam_period
EOF;
                $rows = tldUtils::getSqlToAssocArray($query);

                if (empty($rows)) {
                    $rows = [];
                }
                $Graph = _doBarGraph($rows, $options);
                break;
            case 'tir':
                $query = <<<EOF
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
        ),2
    )*100 AS yval
FROM
    fin_periods AS p
WHERE
    PERIOD_DIFF(DATE_FORMAT(NOW(), '%Y%m'), p.nam_period) BETWEEN 1 AND 12
ORDER BY
    nam_period
EOF;
                $rows = tldUtils::getSqlToAssocArray($query);

                if (empty($rows)) {
                    $rows = [];
                }
                $Graph = _doBarGraph($rows, $options);
                break;
            case 'tat':
                $query = <<<EOF
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
    PERIOD_DIFF(DATE_FORMAT(NOW(), '%Y%m'), p.nam_period) BETWEEN 1 AND 12
ORDER BY
    nam_period
EOF;
                $rows = tldUtils::getSqlToAssocArray($query);

                if (empty($rows)) {
                    $rows = [];
                }
                $Graph = _doBarGraph($rows, $options);
                break;
            case 'tol':
                $query = <<<EOF
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
            status NOT IN ('SOLVED', 'CLOSED')
            AND
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
    PERIOD_DIFF(DATE_FORMAT(NOW(), '%Y%m'), p.nam_period) BETWEEN 1 AND 12
ORDER BY
    nam_period
EOF;
                $rows = tldUtils::getSqlToAssocArray($query);

                if (empty($rows)) {
                    $rows = [];
                }
                $Graph = _doBarGraph($rows, $options);
                break;
        }
        break;
}
