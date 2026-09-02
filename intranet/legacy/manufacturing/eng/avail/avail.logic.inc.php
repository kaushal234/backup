<?php
$DEFAULT_TITLE .= "\Availability";

switch($m[1]){
case 'view':
    $cells = array();
    $TXTA = array();
    $invs = array(300, 400, 410, 420, 500, 510, 520, 540, 570, 600, 620, 640, 660, 680);
    foreach($invs as $inv){
    	$myerp = tldERP::getERPOb($inv);
		$header = $myerp->getItemData($item);
		if(!empty($header)){

			// Get inventory data
            $rows = $myerp->getInvData($item);
            if(count($rows)){
                foreach($rows as $row){
                    $row['wh_sfst'] = $row['sfst'];
                    $lines[] = array_merge($header,$row);
                }
            }else{
                $lines[] = $header;
            }
        }
    }
    $xitems = array(
        "ERP"           =>"ERP",
        "ITEM"          =>"Part Number",
        "DESCRIPTION"   =>"Description",
    	"LEAD"          =>"Leadtime",
        "t_csig"        =>"Sigal Code",
        "WT"            =>"Weight",
    	"UM"			=>"Unit Measure",
        "t_cwar"        =>"Warehouse Code",
        "cwar_fullname" =>"Warehouse",
        "reop"          =>"Warehouse Reop",
		"econ"			=>"Economic Stock",
    	"allo"          =>"Allocated",
        "stoc"          =>"On Hand",
        "ordr"          =>"On Order"
    );


    // Display the report
    $report = new tldReportColumnar(
        $lines,
        array(
            "xItems"=>$xitems
        )
    );
    $body .= $report->fetch();

break;
default:
	$body = $smarty->fetch("$PATH/avail/homepage.avail.tpl");
}

?>
