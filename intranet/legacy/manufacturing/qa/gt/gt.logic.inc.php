<?php
include_once("quality.inc.php");

$DEFAULT_TITLE .= "\GT";
$DEFAULT_MENU .=<<<EOF
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<a href="$php_self?m[0]=gt">GT Home</a>
&nbsp;|&nbsp;<a href="$php_self?m[0]=gt&m[1]=dash">Dashboard</a>
&nbsp;|&nbsp;<a href="$php_self?m[0]=gt&m[1]=reports">Reports</a>
&nbsp;|&nbsp;<a href="/en/private/mis/mis.php?m[0]=help&m[1]=dms&id=21">Help</a>
EOF;

switch($m[1]){
case 'dash':
	$body .= $smarty->fetch("$PATH/gt/homepage.dash.tpl");
break;
case 'reports':
	switch($m[2]){
    case 'gtByERPSSO':
        $query =<<<EOF
SELECT
	ers.id,
	ers.sn,
	ers.model,
	ers.man_location,
	ssos.location,
	ers.customer_name,
	ers.airport_code,
	ers.dgt_com,
	ers.dgt_act,
	ers.date_shipped
	ers.promised_cbom_date
	ers.last_cbom_update_date
 FROM
     service as ers
     left join sor_units as sous on ers.sor_uid=sous.id
     left join sor_lines as sols on sous.parent_id=sols.id
     left join sor as sors on sols.parent_id=sors.id
     left join locations as ssos on sors.bu=ssos.erp
WHERE
	YEAR(ers.dgt_act) > YEAR(NOW())-4
ORDER BY
	ers.id DESC
EOF;
        $rows = tldUtils::getSqlToAssocArray($query);
        $report = new tldXLS(
            $rows,
            array(
                "xItems"=>array(
                    "id"=>"ID#",
            		"sn"=>"SN#",
            		"model"=>"Model",
            		"man_location"=>"Factory",
            		"location"=>"SSO",
            		"customer_name"=>"Customer",
            		"airport_code"=>"Airport Code",
                    "dgt_com"=>"First GT Date",
                    "dgt_act"=>"GT Date",
            		"date_shipped"=>"Ship Date",
					"promised_cbom_date" => "Promised CBOM Date",
					"last_cbom_update_date" => "Last CBOM Update Date"
                ),
                "showTitles"=>true
            )
        );
        $report->out();
        exit;
    break;
	case 'GTByFactoryPeriod':
	if($man_location){
		$form = new HTML_QuickForm('frmByPeriod', 'post');
		$form->addElement(	'header', 'title', "Select date range for $man_location GT");
		$form->addElement(	'hidden', 'm[0]', 'gt');
		$form->addElement(	'hidden', 'm[1]', 'reports');
		$form->addElement(	'hidden', 'm[2]', 'GTByFactoryPeriod');
		$form->addElement(	'hidden', 'man_location', $man_location);
		$form->addElement(	'text', 'start', 'Start Date');
		$form->addElement(	'text', 'end', 'End Date');
		$form->addElement(	'submit',
							'btnSubmit',
							'Submit');
		if ($form->validate()){
			# If the form validates then freeze the data
			$form->freeze();
			$man_location = TldDatabase::escape($man_location);
			$start = TldDatabase::escape($start);
			$end = TldDatabase::escape($end);
			$options = array("man_location"=>$man_location);
			$data = tldGT::byPeriod($start,$end,$options );
            if(count($data)){
                $DEFAULT_MENU .=<<<EOF
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<a href="$php_self?m[0]=gt&m[1]=listing&m[2]=GTByFactoryPeriod&start=$start&end=$end&man_location=$man_location&out=csv&btnSubmit=submit">CSV version</a>
EOF;
                $sess['gt']['listing'] = $data;

            }
			// Display
			$body.= _getListing($data,"GT'd Equipment between ".$start." and ".$end);
		}else{
			$form->setDefaults(
                array(
                    "start"=>date("Y")."-01-01",
					"end"=>date("Y-m-d")
				)
			);
			$body = $form->toHTML();

		}
	}else{
		$form = new tldHTMLList(
            tldWC::getFactoryList(),
			 array("key"=>"man_location", "value"=>"man_location"),
			 "$php_self?m[0]=gt&m[1]=reports&m[2]=GTByFactoryPeriod&man_location="
		 );
		$body = $form->fetch();
	}
		break;
		default:
			$body = $smarty->fetch("$PATH/gt/reports/homepage.reports.tpl");
		}
break;
case 'listing':
	$xItems = array(
        "id"=>"ID#",
        "sn"=>"SN#",
        "model"=>"Model",
        "man_location"=>"Factory",
        "customer_name"=>"Customer",
        "airport_code"=>"Airport Code",
        "dgt_com"=>"First GT Date",
        "dgt_act"=>"GT Date",
        "date_shipped"=>"Ship Date",
		"promised_cbom_date" => "Promised CBOM Date",
		"last_cbom_update-Date" => "Last CBOM Update Date"
	);
	switch($m[2]){
	case 'GTByFactoryPeriod':
        switch($out){
            case 'csv':
                $report = new tldCSV(
                    $sess['gt']['listing'],
                    [
                        "xItems" => $xItems,
                        "showTitles" => true
                    ]
                );
                $report->out();
                exit;
                break;
        }

	break;
	case 'byERP_YM':
	    $x = TldDatabase::escape($x);
	    $y = TldDatabase::escape($y);
		$rows = tldGT::byERPYearMonth($x, $y);
		$_title = "GT'd Equipment";
		if(count($rows)){
			$DEFAULT_MENU .=<<<EOF
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<a href="$php_self?m[0]=gt&m[1]=listing&m[2]=byERP_YM&x=$x&y=$y&out=csv">CSV version</a>
EOF;
			$sess['gt']['list'] = $rows;
			$sess['gt']['xItems'] = $xItems;


			switch($out){
				case 'csv':
					$report = new tldCSV(
					$sess['gt']['list'],
					array(
					"xItems"=>$sess['gt']['xItems'],
					"showTitles"=>true
					)
					);
					$report->out();
					exit;
					break;
			}
		}
	break;
	}
	$body.= _getListing($rows,$_title);
break;
default:
	$rows = tldGT::countERPMonthPast12Months();
	if(count($rows)){
		$sess["gt"]["list"] = $rows;
		$form = new tldMatrix(
			$rows,
			"x", "y", "num",
			"$php_self?m[0]=gt&m[1]=listing&m[2]=byERP_YM",
			"GT Count by Year-Month, Factory"
		);
		$body .= $form->fetch();
	}
	// Latest
	$body.= _getListing(
	    tldGT::byLatest(10, array("mode"=>"shipped")),
	    "Recently GT'd Equipment"
    );
}


function _getListing($rows,$title){
    $report = new tldReportColumnar(
        $rows,
        array(
            "xItems"=>array(
            	"id"=>"ID#",
        		"sn"=>"SN#",
                "model"=>"Model",
        		"man_location"=>"Factory",
        		"customer_name"=>"Customer",
        		"airport_code"=>"Airport Code",
                "dgt_com"=>"First GT Date",
                "dgt_act"=>"GT Date",
        		"date_shipped"=>"Ship Date",
				"promised_cbom_date" => "Promised CBOM Date",
				"last_cbom_update_date" => "Last CBOM Update Date"
        	),
        	"title"=>"$title",
        	"links"=>array(
                "id"=>"/en/private/product_support/index.ps.php?m[0]=equipment&m[1]=view&id="
        	)
    	)
    );
	return $report->fetch();
}

?>
