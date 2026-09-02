<?php
$DEFAULT_TITLE .= "\Cash Forecast";

// Check permissions
if (!$user->isInGroup(["role_EVP", "role_COO", "role_CMO", "role_CEO", "role_FC", "role_CFO", "role_SA", "role_PSM", "role_PSE", "role_PSA", "gg_ACCT"])) {
    $DEFAULT_ERROR[] = "ERROR: You do not have permissions to access this page";
    return;
}
// For gg_ACCT, only GROUP can access
if($user->isInGroup(array("gg_ACCT"))
    && !in_array(900,(array)$user->isInGroup("gg_ACCT"))
    && !$user->isInGroup(array("role_FC","role_CFO"))
    ){
	$DEFAULT_ERROR[] = "ERROR: You do not have permissions to access this page";
	return;
}

// Get 6 months cash period
$months["0000-00-00"]="0000-00";
$today = date('Ym');
for($i=0;$i<8;$i++){
	$cd = strtotime($today);
	$dt = date('Y-m', mktime(0,0,0,date('m',$cd)+$i,1,date('Y',$cd)));
	$months["$dt-01"] = $dt;
}

$DEFAULT_MENU .=<<<EOF
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<a href="$php_self?m[0]=sfr&m[1]=cash" title="SFR cash forecast homepage">Home</a>&nbsp;|&nbsp;
<a href="$php_self?m[0]=sfr&m[1]=cash&m[2]=quickEdit" title="Quick Edit">Quick edit</a>
EOF;

switch($m[2]){
case 'quickEdit':
    if(!$user->isInGroup(array("role_EVP","role_COO"))){
    	$DEFAULT_ERROR[] = "ERROR: You do not have permissions to edit the SFR cash forecast";
    	break;
    }
    // Check type of cash forecast and get BU associated of the user
    if($user->isInGroup("role_EVP")){
    	$TYPE="SSO";
    	$ssos = (array)$user->isInGroup("role_EVP");
    }elseif($user->isInGroup("role_COO")){
    	$TYPE="ERP";
    	$erps = (array)$user->isInGroup("role_COO");
    }
    $DEFAULT_TITLE .= " - $TYPE";

    // Get list of open SFR not cashed already before actual month
    switch($TYPE){
    case 'SSO':
    	if(empty($ssos)){
    		$DEFAULT_ERROR[]="ERROR: No SSO# configured to your EVP permissions";
    		return;
    	}
    	$a=" ssolist.erp IN(";
    	foreach($ssos as $sso) $a.="'$sso',";
    	$a=substr($a, 0, -1).") ";
    	$rows = tldSFR::byOpenStatusSSOCashForecastConstraints(
    		$a, array('orderBy'=>"tot_succ_pc DESC,sfr.cust_nama", 'useWhereCondition' => true)
    	);
    break;
    case 'ERP':
    	if(empty($erps)){
    		$DEFAULT_ERROR[]="ERROR: No ERP# configured to your COO permissions";
    		return;
    	}
    	$a=" erplist.erp IN(";
    	foreach($erps as $erp) $a.="'$erp',";
    	$a=substr($a, 0, -1).") ";
    	$rows = tldSFR::byOpenStatusERPCashForecastConstraints(
    		$a, array('orderBy'=>"tot_succ_pc DESC,sfr.cust_nama", 'useWhereCondition' => true)
    	);
    break;
    }

    // Check if rows
    if(count($rows)<1){
    	$DEFAULT_ERROR[] = "ERROR: No $TYPE SFR cash forecast record to update";
    	return;
    }

    switch($m[3]){
    case 'update':
        foreach($rows as $row){
    		$sfrID = $row['id'];
    		$dt_cf = $_POST[$TYPE][$sfrID];
    		// Check if a cash forecast date have been submitted
    		if(empty($dt_cf)) continue;
    		// Check the month submitted in the 6 months range
    		if(!in_array($dt_cf,array_keys($months))){
    			$DEFAULT_ERROR[]="ERROR: Cash forecast month '$dt_cf' not valid for SFR#$sfrID";
    			continue;
    		}
    		// Get the SFR
    		$sfr = new tldSFR($sfrID);
    		// Update the date regarding the $TYPE
    		if($TYPE=='SSO'){
    			// Check SFR cash forecast date if different
    			if($dt_cf==$row['dt_cfsso']) continue;
    			$e = $sfr->evpUpdate(['dt_cfsso' => $dt_cf]);
    		}
    		if($TYPE=='ERP'){
    			// Check SFR cash forecast date if different
    			if($dt_cf==$row['dt_cferp']) continue;
    			$e = $sfr->cooUpdate(['dt_cferp' => $dt_cf]);
    		}
    		// Check error
    		if(is_string($e)){
    			$DEFAULT_ERROR[]="ERROR: SFR#$sfrID $TYPE cash forecast date not updated<br/>Reason: $e";
    			continue;
    		}
    		$body.="<br/>SFR#$sfrID $TYPE cash forecast date updated to {$months[$dt_cf]}";
    	}
    	$body.="<br/><br/>SFR cash forecast date update process successfully terminated";
    break;
    default:
        $smarty->assign("url","$php_self?m[0]=sfr&m[1]=$m[1]&m[2]=quickEdit&m[3]=update");
    	$smarty->assign("typeCashForecast",$TYPE);
    	$smarty->assign("months", $months);
    	$smarty->assign("mode", "form");
    	$smarty->assign("today", date("Y-m"));
    	$body.=_quickCashListing($rows);
    break;
    }
break;
case 'listing':
    switch($m[3]){
    case 'bySSOERP':
        $a = array();
		if($y!="ALL") $a['ssolist.location'] = TldDatabase::escape($y);
    	if($x!="ALL") $a['erplist.location'] = TldDatabase::escape($x);
        $rows = tldSFR::byCashForecastConstraints(
            $a, array('orderBy'=>"tot_succ_pc DESC,sfr.cust_nama")
        );
        $TITLE = "SFR cash forecast by $y SSO, $x Factory";
    break;
    }

    $DEFAULT_MENU.=<<<EOF
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<a href="$php_self?m[0]=sfr&m[1]=$m[1]&m[2]=$m[2]&m[3]=$m[3]&w=$w&x=$x&y=$y&m[4]=csv">CSV</a>
EOF;

    switch($m[4]){
    case 'csv':
        $report = new tldCSV(
		    $rows,
		    array(
				"xItems"=>array(
        			"id"=>"SFR#",
                    "status"=>"Status",
                    "year_id"=>"Year",
                    "month_id"=>"Month",
                    "buyer_display"=>"Customer BUYER",
                    "user_display"=>"Customer USER",
                    "cust_ctry"=>"Customer Country",
        			"apc"=>"Airport Code",
                    "erp_fullname"=>"Factory",
                    "sso_fullname"=>"SSO",
                    "asm_fullname"=>"ASM / Sales Agent",
					"price"=>"Price",
                    "model"=>"Model",
                    "qty"=>"Qty",
                    "cust_pur_pc"=>"Cust Pur %",
                    "tld_succ_pc"=>"TLD Success %",
                    "tot_succ_pc"=>"Total Success %",
                    "Ym_cfsso"=>"SSO cash forecast",
                    "Ym_cferp"=>"ERP cash forecast",
                    "last_asm_log_datetime"=>"Last log Date",
                    "last_log"=>"Last log",
		        ),
				"showTitles"=>true
			)
		);
		$report->out();
		exit;
    break;
    default:
		$overlib = $smarty->fetch('overlib.inc.js.tpl');
		$overlib .= '<script type="text/javascript">$(function(){$("[data-body]").overlib()});</script>';
		$smarty->assign("html_head", $overlib);
        $smarty->assign("title",$TITLE);
    	$smarty->assign("months", $months);
        $smarty->assign("today", date("Y-m"));
    	$body.=_quickCashListing($rows);
    break;
    }
break;
default:
	$form = new tldMatrix(
    	tldSFR::countBySSOERPCashForecastConstraints(),
		"erp_fullname", "sso_fullname", "num",
		"$php_self?m[0]=sfr&m[1]=cash&m[2]=listing&m[3]=bySSOERP",
		'SFR cash forecast by SSO, Factory'
	);
    $body .= $form->fetch();
break;
}


function _quickCashListing($rows){
	global $smarty;
	// Get logs
    foreach($rows as $row){
    	// Asm log
        $log = tldModLog::byParent($row['id'], 'SFR');
        if(count($log)){
            $logs[$row['id']] = $log;
        }
    	// Other log
        $log1 = tldModLog::byParent($row['id'], 'SFR', 1);
        if(count($log)){
            $logs1[$row['id']] = $log1;
        }
    }
    $smarty->assign("logs", $logs);
    $smarty->assign("logs1", $logs1);
    $smarty->assign("rows", $rows);
	return $smarty->fetch("sales_service/sfr/listing.cash.edit.tpl");
}
?>
