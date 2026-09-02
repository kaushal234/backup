<?php
include_once("common.inc.php");
include_once("erp.inc.php");
include_once('product_support.inc.php');
include_once('Image/Graph.php');
$PATH = "manufacturing";

session_start();
if(!isset($_SESSION['sess'])) $_SESSION['sess'] = null;
$sess =& $_SESSION['sess'];

$smarty = tldUtils::getSmarty("intranet");

$factoryList = tldLocation::byConstraints("erp<>'' AND factory='Y' AND hidden<>1 and disable<>1");
$FACTORIES = array_column($factoryList, 'location', 'erp');

if($_REQUEST['buid']){
	$BUID = TldDatabase::escape($_REQUEST['buid']);
	if(is_numeric($BUID)){
		$loc = new tldLocation($BUID);
		$location = $loc->getHeader();
		$erp = $location['erp'];
	}
}

// Do report
switch($m[0]){
case 'past12':
	switch($m[1]){
	case 'WCTargetList_byFactory':
		if(!is_object($loc) OR $loc->isEmpty()) exit;

        // Target goals:
        $targets = array(
            $location['location'] => [
                'AVGWC' => null,
                'MTBF' => null,
                'ATFF' => null,
                'MNOWC3' => 95
            ],
            'Conventional Aircraft Tractors' => [
                'AVGWC' => 0.5,
                'MTBF' => 750,
                'ATFF' => null,
                'MNOWC3' => 95
            ],
            'Towbarless Aircraft Tractors' => [
                'AVGWC' => 1.5,
                'MTBF' => 250,
                'ATFF' => null,
                'MNOWC3' => 75
            ],
            'Catering Trucks and Derivatives' => [
                'AVGWC' => 2,
                'MTBF' => 200,
                'ATFF' => null,
                'MNOWC3' => 95
            ],
            'Maintenance Platforms' => [
                'AVGWC' => 0.5,
                'MTBF' => 750,
                'ATFF' => null,
                'MNOWC3' => 95
            ],
            'Trailers and Dollies' => [
                'AVGWC' => 0.1,
                'MTBF' => 3500,
                'ATFF' => null,
                'MNOWC3' => 98
            ],
            'Loaders' => [
                'AVGWC' => 1.5,
                'MTBF' => 250,
                'ATFF' => null,
                'MNOWC3' => 95
            ],
            'Baggage Tractors' => [
                'AVGWC' => 0.5,
                'MTBF' => 750,
                'ATFF' => null,
                'MNOWC3' => 95
            ],
            'Belt Loaders' => [
                'AVGWC' => 0.4,
                'MTBF' => 1000,
                'ATFF' => null,
                'MNOWC3' => 95
            ],
            'Passenger Steps' => [
                'AVGWC' => 0.5,
                'MTBF' => 750,
                'ATFF' => null,
                'MNOWC3' => 95
            ],
            'Transporters' => [
                'AVGWC' => 1.5,
                'MTBF' => 250,
                'ATFF' => null,
                'MNOWC3' => 95
            ],
            'Air Conditioners' => [
                'AVGWC' => 0.4,
                'MTBF' => 1000,
                'ATFF' => null,
                'MNOWC3' => 95
            ],
            'Fixed Air Conditioning systems' => [
                'AVGWC' => 0.4,
                'MTBF' => 1000,
                'ATFF' => null,
                'MNOWC3' => 95
            ],
            'Air Starters' => [
                'AVGWC' => 0.25,
                'MTBF' => 1500,
                'ATFF' => null,
                'MNOWC3' => 95
            ],
            'Ground Power Units' => [
                'AVGWC' => 0.25,
                'MTBF' => 1500,
                'ATFF' => null,
                'MNOWC3' => 95
            ],
            'Lavatory and Water Trucks' => [
                'AVGWC' => 0.5,
                'MTBF' => 750,
                'ATFF' => 800,
                'MNOWC3' => 95
            ],
            'Military' => [
                'AVGWC' => 1.5,
                'MTBF' => 250,
                'ATFF' => null,
                'MNOWC3' => 95
            ],
            'Miscellaneous' => [
                'AVGWC' => 0.5,
                'MTBF' => null,
                'ATFF' => null,
                'MNOWC3' => null
            ],
            'Power Conversion' => [
                'AVGWC' => 0.75,
                'MTBF' => null,
                'ATFF' => null,
                'MNOWC3' => null
            ],
            'Distribution Systems' => [
                'AVGWC' => 0.5,
                'MTBF' => null,
                'ATFF' => null,
                'MNOWC3' => null
            ],
            'Hydraulic Power Units' => [
                'AVGWC' => 0.5,
                'MTBF' => 750,
                'ATFF' => null,
                'MNOWC3' => 95
            ],
            'Oxygen and Nitrogen Service' => [
                'AVGWC' => 0.05,
                'MTBF' => null,
                'ATFF' => null,
                'MNOWC3' => 99.8
            ],
            'Autonomous' => [
                'AVGWC' => 2,
                'MTBF' => 250,
                'ATFF' => null,
                'MNOWC3' => 95
            ]
        );

        $productTypes = [
            'Conventional Aircraft Tractors',
            'Catering Trucks and Derivatives',
            'Towbarless Aircraft Tractors',
            'Maintenance Platforms',
            'Trailers and Dollies',
            'Loaders',
            'Baggage Tractors',
            'Belt Loaders',
            'Passenger Steps',
            'Transporters',
            'Air Conditioners',
            'Fixed Air Conditioning systems',
            'Air Starters',
            'Ground Power Units',
            'Lavatory and Water Trucks',
            'Military',
            'Miscellaneous',
            'Power Conversion',
            'Distribution Systems',
            'Hydraulic Power Units',
            'Oxygen and Nitrogen Service',
            'Autonomous',
        ];
		$erTypeList = array_keys($targets);

		// Report settings
		$settings = array(
			'AVGWC' => array(
				'title' => 'AVG # WC per Machine',
				'threshhold' => '+'
			),
			'MTBF' => array(
				'title' => 'MTBF',
				'threshhold' => '-'
			),
            'WC' => array(
                'title' => "Total WC's",
                'threshhold' => '-'
            ),
            'ER' => array(
                'title' => 'Total Units',
                'threshhold' => '-'
            ),
			'ATFF' => array(
				'title' => 'AVG Time at 1st failure',
				'threshhold' => ''
			),
			'MNOWC3' => array(
				'title' => '% Machines with NO failure in 1st 3 months',
				'threshhold' => '-'
			)
		);

		$query = <<<EOF
		SELECT
			nam_period,
			CONCAT(SUBSTR(nam_period,1,4),'-',SUBSTR(nam_period,-2,2),'-01') AS Ymd,
			CONCAT(SUBSTR(nam_period,1,4),'-',SUBSTR(nam_period,-2,2)) AS Ym
		FROM fin_periods
		WHERE PERIOD_DIFF(DATE_FORMAT(NOW(), '%Y%m'), nam_period) BETWEEN 1 AND 24
		ORDER BY nam_period
EOF;
		$periods = tldUtils::getSqlToAssocArray($query);

		// Prepare constrains
		$constraints = [];
		$constraints[] = "er.man_location='%s'";
		// Special cases
		if ($location['location'] === 'TLD MTL') {
            $constraints[] = "er.type NOT LIKE 'Ground Power Units'";
        }
		// Complete constraints
		$constraints = implode(' AND ',$constraints);

		$data = array();
		foreach($periods as $period){
			// AVGWC
            $constraintsAVGWC = $constraints;

            // TTS#17470
            if ($location['location'] === 'AERO Specialties') {
                $constraintsAVGWC .= <<<SQL
                    AND (er.type LIKE 'Ground Power Units'
                    OR er.type LIKE 'Hydraulic Power Units'
                    OR er.type LIKE 'Lavatory and Water Trucks'
                    OR er.type LIKE 'Oxygen and Nitrogen Service')
                SQL;
            }

			$a = tldWC::getAVGWCPeriodByConstraints($period['Ymd'], sprintf($constraintsAVGWC, $location['location']));
			$a['type'] = $location['location'];
			$b = tldWC::getAVGWCPeriodByConstraints($period['Ymd'], sprintf($constraintsAVGWC, $location['location']), true);
			$grp = array_merge(array($a), $b);
			foreach($grp as $g){
			    if (!in_array($g['type'], $erTypeList)) continue;
				$data[$g['type']?$g['type']:'Unknown']['AVGWC'][$g['period']] = $g['val'];
			}

            // WC count
            $a = tldWC::getWCCountByConstraints($period['Ymd'], sprintf($constraints, $location['location']));
            $a['type'] = $location['location'];
            $b = tldWC::getWCCountByConstraints($period['Ymd'], sprintf($constraints, $location['location']), true);
            $grp = array_merge(array($a), $b);
            foreach($grp as $g){
                if (!in_array($g['type'], $erTypeList)) continue;
                $data[$g['type']?$g['type']:'Unknown']['WC'][$g['period']] = $g['val'];
            }

            // ER count
            $a = tldWC::getERCountByConstraints($period['Ymd'], sprintf($constraints, $location['location']));
            $a['type'] = $location['location'];
            $b = tldWC::getERCountByConstraints($period['Ymd'], sprintf($constraints, $location['location']), true);
            $grp = array_merge(array($a), $b);
            foreach($grp as $g){
                if (!in_array($g['type'], $erTypeList)) continue;
                $data[$g['type']?$g['type']:'Unknown']['ER'][$g['period']] = $g['val'];
            }

            // MTBF
			$a = tldWC::getMTBFPeriodByConstraints($period['Ymd'], sprintf($constraints, $location['location']));
			$a['type'] = $location['location'];
			$b = tldWC::getMTBFPeriodByConstraints($period['Ymd'], sprintf($constraints, $location['location']), true);
			$grp = array_merge(array($a), $b);
			foreach($grp as $g){
			    if (!in_array($g['type'], $erTypeList)) continue;
                // TTS#17470
                if (in_array($g['type'], ['Lavatory and Water Trucks', 'Miscellaneous'])) continue;
				$data[$g['type']?$g['type']:'Unknown']['MTBF'][$g['period']] = $g['val'];
			}

			// ATFF
			$a = tldWC::getATFFPeriodByConstraints($period['Ymd'], sprintf($constraints, $location['location']));
			$a['type'] = $location['location'];
			$b = tldWC::getATFFPeriodByConstraints($period['Ymd'], sprintf($constraints, $location['location']), true);
			$grp = array_merge(array($a), $b);
			foreach($grp as $g){
			    if (!in_array($g['type'], $erTypeList)) continue;
                // TTS#17470
                if (in_array($g['type'], ['Lavatory and Water Trucks'])) continue;
				$data[$g['type']?$g['type']:'Unknown']['ATFF'][$g['period']] = $g['val'];
			}

			// MNOWC3
			$a = tldWC::getMNOWC3PeriodByConstraints($period['Ymd'], sprintf($constraints, $location['location']));
			$a['type'] = $location['location'];
			$b = tldWC::getMNOWC3PeriodByConstraints($period['Ymd'], sprintf($constraints, $location['location']), true);
			$grp = array_merge(array($a), $b);
			foreach($grp as $g){
			    if(!in_array($g['type'],$erTypeList)) continue;
				$data[$g['type']?$g['type']:'Unknown']['MNOWC3'][$g['period']] = $g['val'];
			}
		}

		$smarty->assign('types', $productTypes);
		$smarty->assign('settings', $settings);
		$smarty->assign('targets', $targets);
		$smarty->assign('periods', $periods);
		$smarty->assign('data', $data);
		$smarty->assign('manufacturerLocation', $BUID);
		echo $smarty->fetch("$PATH/kpi/kpi.quality.wc.targetlist.tpl");
	break;
	}
break;
}
