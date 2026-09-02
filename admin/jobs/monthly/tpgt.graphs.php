<?php
include_once("common.inc.php");
include_once("sales_service.inc.php");

$dt_begin = date("Y-m-d h:i:s A");
error_log("RUNNING $PHP_SELF - $dt_begin");

// Get actual year month period
$today = new DateTime(date('Y-m-d'));
$Ym = $today->format('Ym');


// Get Listing
$factoryList = tldLocation::getFactoryList("smartyOptionsLocationLocation");

//Foreach Factory (factory='Y' from locations)
foreach($factoryList as $factory){
// Get data
$queryGT = <<<EOF
		
SELECT DATE_FORMAT( service.dgt_rev, '%Y%m%d' ) AS nam_period, service.man_location AS factory, count(*) AS est_gt
FROM service
LEFT JOIN sor_units ON service.sor_uid = sor_units.id
WHERE DATE_FORMAT( service.dgt_rev, '%Y%m' ) LIKE '$Ym' AND DATE_FORMAT( sor_units.ddel_est1, '%Y%m' ) <= '$Ym' AND service.man_location LIKE '$factory'
GROUP BY nam_period
EOF;

$queryTP = <<<EOF

SELECT DATE_FORMAT( service.dgt_rev, '%Y%m%d' ) AS nam_period, service.man_location AS factory, SUM(sor_opts.pric) AS est_tp, sor_opts.pric_cur AS currency
FROM service
LEFT JOIN sor_units ON service.sor_uid = sor_units.id
LEFT JOIN sor_opts ON sor_units.parent_id = sor_opts.parent_id
WHERE DATE_FORMAT( service.dgt_rev, '%Y%m' ) LIKE '$Ym' AND DATE_FORMAT( sor_units.ddel_est1, '%Y%m' ) <= '$Ym' AND service.man_location LIKE '$factory' AND sor_opts.pric_cur IS NOT NULL
GROUP BY nam_period
EOF;

$rowsGT = tldUtils::getSqlToAssocArray($queryGT);
$rowsTP = tldUtils::getSqlToAssocArray($queryTP);
    // Insert GT count for each day of the month
    foreach($rowsGT as $row){
        // Insert into mod_graphs
        $e = tldModKPI::insert(
            array(
            	"module"=>"GT",
            	"key1"=>$row['factory'],
            	"key2"=>$row['nam_period'],
            	"val"=>$row['est_gt']
            )
        );
        if(is_string($e)){
            error_log("tldModKPI::insert error: $e");
        }
    }
    // Insert TP value for each day of the month
    foreach($rowsTP as $row){
    	// Insert into mod_graphs
    	$e = tldModKPI::insert(
    			array(
    					"module"=>"TP",
    					"key1"=>$row['factory'],
    					"key2"=>$row['nam_period'],
    					"val"=>$row['est_tp'],
    					"key3"=>$row['currency']    				
    			)
    	);
    	if(is_string($e)){
    		error_log("tldModKPI::insert error: $e");
    	}
    }

}
$dt_end = date("Y-m-d h:i:s A");
error_log("END OF $PHP_SELF - $dt_end");

?>
