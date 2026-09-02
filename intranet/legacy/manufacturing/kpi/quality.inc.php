<?php
$kpireview_add=('past12'==$graphType)?'class="kpireview"':'';
if($buid=="ALL"){
	$body.=<<<EOF
<br><br><img src="kpi/graphs.php?m[0]=$graphType&m[1]=internalCustomerSatisfactionPast12Months&buid=ame" $kpireview_add><br>
EOF;
		$_TITLE="Internal Customer Satisfaction Definition";
		// setup the popup for the KPI Graph
		$groupTarget="<b>Group Target:</b>  ".$help["Internal Customer Satisfaction Group Target"];
		$popupDef = new tldOverlib($help[$_TITLE].$groupTarget, array("CAPTION"=>$_TITLE,"WIDTH"=>"500","linkName"=>$_TITLE));
		$body.=$popupDef->fetch();
//-------------------------------------------------------------------//
	$body.=<<<EOF
<br><br><hr><img src="kpi/graphs.php?m[0]=$graphType&m[1]=internalCustomerSatisfactionPast12Months&buid=asi" $kpireview_add><br>
EOF;
		$_TITLE="Internal Customer Satisfaction Definition";
		// setup the popup for the KPI Graph
		$groupTarget="<b>Group Target:</b>  ".$help["Internal Customer Satisfaction Group Target"];
		$popupDef = new tldOverlib($help[$_TITLE].$groupTarget, array("CAPTION"=>$_TITLE,"WIDTH"=>"500","linkName"=>$_TITLE));
		$body.=$popupDef->fetch();
//-------------------------------------------------------------------//
	$body.=<<<EOF
<br><br><hr><img src="kpi/graphs.php?m[0]=$graphType&m[1]=internalCustomerSatisfactionPast12Months&buid=eur" $kpireview_add><br>
EOF;
		$_TITLE="Internal Customer Satisfaction Definition";
		// setup the popup for the KPI Graph
		$groupTarget="<b>Group Target:</b>  ".$help["Internal Customer Satisfaction Group Target"];
		$popupDef = new tldOverlib($help[$_TITLE].$groupTarget, array("CAPTION"=>$_TITLE,"WIDTH"=>"500","linkName"=>$_TITLE));
		$body.=$popupDef->fetch();
}else{
	$body.=<<<EOF
<br><br><img src="kpi/graphs.php?m[0]=$graphType&m[1]=internalCustomerSatisfactionPast12Months&buid=$buid" $kpireview_add><br>
EOF;
		$_TITLE="Internal Customer Satisfaction Definition";
		// setup the popup for the KPI Graph
		$groupTarget="<b>Group Target:</b>  ".$help["Internal Customer Satisfaction Group Target"];
		$popupDef = new tldOverlib($help[$_TITLE].$groupTarget, array("CAPTION"=>$_TITLE,"WIDTH"=>"500","linkName"=>$_TITLE));
		$body.=$popupDef->fetch();
//-------------------------------------------------------------------//
	$body.=<<<EOF
<br><br><hr><img src="kpi/graphs.php?m[0]=$graphType&m[1]=CRABCountPerTypeGTUnits&buid=$buid" $kpireview_add><br>
EOF;
		$_TITLE="CRAB Count Per Type For All GT Units Definition";
		// setup the popup for the KPI Graph
		$groupTarget="<b>Group Target:</b>  ".$help["CRAB Count Per Type For All GT Units Group Target"];
		$popupDef = new tldOverlib($help[$_TITLE].$groupTarget, array("CAPTION"=>$_TITLE,"WIDTH"=>"500","linkName"=>$_TITLE));
		$body.=$popupDef->fetch();
    $body.=<<<EOF
<br><br><hr><img src="kpi/graphs.php?m[0]=$graphType&m[1]=CRABAvgPerTypeGTUnits&buid=$buid" $kpireview_add><br>
EOF;
    $_TITLE="CRAB Average Per Type For All GT Units Definition";
    // setup the popup for the KPI Graph
    $groupTarget="<b>Group Target:</b>  ".$help["CRAB Average Per Type For All GT Units Group Target"];
    $popupDef = new tldOverlib($help[$_TITLE].$groupTarget, array("CAPTION"=>$_TITLE,"WIDTH"=>"500","linkName"=>$_TITLE));
    $body.=$popupDef->fetch();
}

///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    $body.=<<<EOF
<br><br><hr><img id="warranty-count" src="kpi/graphs.php?m[0]=$graphType&m[1]=wcCountByStatusByFactory&status=PAC&buid=$buid" $kpireview_add><br>
EOF;
    $_TITLE="Warranty Claim Counts for status PENDING, ACCEPTED or CONDITIONAL";
    $popupDef = new tldOverlib($help["Warranty Claim Counts Definition"], array("CAPTION"=>$_TITLE,"WIDTH"=>"500","linkName"=>$_TITLE));
    $body.=$popupDef->fetch();
//-------------------------------------------------------------------//
    $body.=<<<EOF
<br><br><hr><img src="kpi/graphs.php?m[0]=$graphType&m[1]=wcCountByStatusByFactory&status=RS&buid=$buid" $kpireview_add><br>
EOF;
    $_TITLE="Warranty Claim Counts for status REJECTED & SALES CONCESSION";
    $popupDef = new tldOverlib($help["Warranty Claim Counts Definition"], array("CAPTION"=>$_TITLE,"WIDTH"=>"500","linkName"=>$_TITLE));
    $body.=$popupDef->fetch();
//-------------------------------------------------------------------//
    $body.=<<<EOF
<br><br><hr><img src="kpi/graphs.php?m[0]=$graphType&m[1]=wcCountByStatusByFactoryLightEr&status=PAC&buid=$buid" $kpireview_add><br>
EOF;
    $_TITLE="Warranty Claim with ER light Counts for status PENDING, ACCEPTED or CONDITIONAL";
    $popupDef = new tldOverlib($help["Warranty Claim Counts Definition ER light"], array("CAPTION"=>$_TITLE,"WIDTH"=>"500","linkName"=>$_TITLE));
    $body.=$popupDef->fetch();
//-------------------------------------------------------------------//
    $body.=<<<EOF
<br><br><hr><img src="kpi/graphs.php?m[0]=$graphType&m[1]=wcCountByStatusByFactoryLightEr&status=RS&buid=$buid" $kpireview_add><br>
EOF;
    $_TITLE="Warranty Claim with ER light Counts for status REJECTED & SALES CONCESSION";
    $popupDef = new tldOverlib($help["Warranty Claim Counts Definition ER light"], array("CAPTION"=>$_TITLE,"WIDTH"=>"500","linkName"=>$_TITLE));
    $body.=$popupDef->fetch();
//-------------------------------------------------------------------//
    $body.=<<<EOF
<br><br><hr><img src="kpi/graphs.php?m[0]=$graphType&m[1]=MTBF_byFactory&buid=$buid" $kpireview_add><br>
EOF;
	$_TITLE="WC - Mean time between failures";
	// setup the popup for the KPI Graph
	$groupTarget="<b>Group Target:</b>  ".$help["MTBF target"];
	$popupDef = new tldOverlib($help["MTBF"].$groupTarget, array("CAPTION"=>$_TITLE,"WIDTH"=>"500","linkName"=>$_TITLE));
	$body.=$popupDef->fetch();
//-------------------------------------------------------------------//
    $body.=<<<EOF
<br><br><hr><img src="kpi/graphs.php?m[0]=$graphType&m[1]=MTBF_byFactory&buid=$buid&byType=1" $kpireview_add><br>
EOF;
	$_TITLE="WC - Mean time between failures (Per Type)";
	// setup the popup for the KPI Graph
	$groupTarget="<b>Group Target:</b>  ".$help["MTBF target"];
	$popupDef = new tldOverlib($help["MTBF"].$groupTarget, array("CAPTION"=>$_TITLE,"WIDTH"=>"500","linkName"=>$_TITLE));
	$body.=$popupDef->fetch();
//-------------------------------------------------------------------//

/*    $body.=<<<EOF
<br><br><hr><img src="kpi/graphs.php?m[0]=$graphType&m[1]=AVGTMY_byFactory&buid=$buid" $kpireview_add><br>
EOF;
    $_TITLE="WC - Average Time of WC per Machine per Year";
    // setup the popup for the KPI Graph
    $groupTarget="<b>Group Target:</b>  ".$help["AVGTMY target"];
    $popupDef = new tldOverlib($help["AVGTMY"].$groupTarget, array("CAPTION"=>$_TITLE,"WIDTH"=>"500","linkName"=>$_TITLE));
    $body.=$popupDef->fetch();
*/
//-------------------------------------------------------------------//
    $body.=<<<EOF
<br><br><hr><img src="kpi/graphs.php?m[0]=$graphType&m[1]=ATFF_byFactory&buid=$buid" $kpireview_add><br>
EOF;
    $_TITLE="WC - Average Time at First Failure";
    // setup the popup for the KPI Graph
    $groupTarget="<b>Group Target:</b>  ".$help["ATFF target"];
    $popupDef = new tldOverlib($help["ATFF"].$groupTarget, array("CAPTION"=>$_TITLE,"WIDTH"=>"500","linkName"=>$_TITLE));
    $body.=$popupDef->fetch();
//-------------------------------------------------------------------//
    $body.=<<<EOF
<br><br><hr><img src="kpi/graphs.php?m[0]=$graphType&m[1]=ATFF_byFactory&buid=$buid&byType=1" $kpireview_add><br>
EOF;
    $_TITLE="WC - Average Time at First Failure (Per Type)";
    // setup the popup for the KPI Graph
    $groupTarget="<b>Group Target:</b>  ".$help["ATFF target"];
    $popupDef = new tldOverlib($help["ATFF"].$groupTarget, array("CAPTION"=>$_TITLE,"WIDTH"=>"500","linkName"=>$_TITLE));
    $body.=$popupDef->fetch();
//-------------------------------------------------------------------//
    $body.=<<<EOF
<br><br><hr><img src="kpi/graphs.php?m[0]=$graphType&m[1]=MNOWC3_byFactory&buid=$buid" $kpireview_add><br>
EOF;
    $_TITLE="WC - % of Machines with NO WC in the first 3 months";
    // setup the popup for the KPI Graph
    $groupTarget="<b>Group Target:</b>  ".$help["MNOWC3 target"];
    $popupDef = new tldOverlib($help["MNOWC3"].$groupTarget, array("CAPTION"=>$_TITLE,"WIDTH"=>"500","linkName"=>$_TITLE));
    $body.=$popupDef->fetch();
//-------------------------------------------------------------------//
    $body.=<<<EOF
<br><br><hr><img src="kpi/graphs.php?m[0]=$graphType&m[1]=MNOWC3_byFactory&buid=$buid&byType=1" $kpireview_add><br>
EOF;
    $_TITLE="WC - % of Machines with NO WC in the first 3 months (Per Type)";
    // setup the popup for the KPI Graph
    $groupTarget="<b>Group Target:</b>  ".$help["MNOWC3 target"];
    $popupDef = new tldOverlib($help["MNOWC3"].$groupTarget, array("CAPTION"=>$_TITLE,"WIDTH"=>"500","linkName"=>$_TITLE));
    $body.=$popupDef->fetch();
//-------------------------------------------------------------------//
	if(is_numeric($buid)):
    $body.=<<<EOF
<br><br><hr><div id="wc_targetlist"></div>
<script type="text/javascript">
jQuery(function(){
	jQuery.post('kpi/charts.php?m[0]=$graphType&m[1]=WCTargetList_byFactory&buid=$buid', '', function(data){
		jQuery('#wc_targetlist').html(data);
	}, 'html');
});
</script>
EOF;
	endif;
//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	$body.=<<<EOF
<br><br><hr><img src="kpi/graphs.php?m[0]=$graphType&m[1]=historyByFactory&buid=$buid" $kpireview_add><br>
EOF;
		$_TITLE="PDC Focus Weight Definition";
		// setup the popup for the KPI Graph
		$groupTarget="<b>Group Target:</b>  ".$help["PDC Focus Weight Group Target"];
		$popupDef = new tldOverlib($help[$_TITLE].$groupTarget, array("CAPTION"=>$_TITLE,"WIDTH"=>"500","linkName"=>$_TITLE));
		$body.=$popupDef->fetch();
//-------------------------------------------------------------------//
	$body.=<<<EOF
<br><br><hr><img id="pdc12months" src="kpi/graphs.php?m[0]=$graphType&m[1]=pdcInProgressCountPast12Months&buid=$buid" $kpireview_add><br>
EOF;
		$_TITLE="PDC in Progress Definition";
		// setup the popup for the KPI Graph
		$groupTarget="<b>Group Target:</b>  ".$help["PDC in Progress Group Target"];
		$popupDef = new tldOverlib($help[$_TITLE].$groupTarget, array("CAPTION"=>$_TITLE,"WIDTH"=>"500","linkName"=>$_TITLE));
		$body.=$popupDef->fetch();
//-------------------------------------------------------------------//
	$body.=<<<EOF
<br><br><hr><img src="kpi/graphs.php?m[0]=$graphType&m[1]=pdcOpenedPast12Months&buid=$buid" $kpireview_add><br>
EOF;
		$_TITLE="PDC Opened During the Month Definition";
		// setup the popup for the KPI Graph
		$groupTarget="<b>Group Target:</b>  ".$help["PDC Opened During the Month Group Target"];
		$popupDef = new tldOverlib($help[$_TITLE].$groupTarget, array("CAPTION"=>$_TITLE,"WIDTH"=>"500","linkName"=>$_TITLE));
		$body.=$popupDef->fetch();
/////////////////////////////////////////////////////////////////////////////////
		/*
		$body.=<<<EOF
<br><br><hr><img src="kpi/graphs.php?m[0]=$graphType&m[1]=vwcRequestRecoveryValuePast12Months&buid=$buid" $kpireview_add><br>
EOF;
		$_TITLE="VWC - Requested Recovery Value Past 12 Months";
		// setup the popup for the KPI Graph
// 		$groupTarget="<b>Group Target:</b>  ".$help["PDC Opened During the Month Group Target"];
		$popupDef = new tldOverlib($help[$_TITLE].$groupTarget, array("CAPTION"=>$_TITLE,"WIDTH"=>"500","linkName"=>$_TITLE));
		$body.=$popupDef->fetch();
		*/
//--------NCR Closed and Opened-------------------------------------------------//
		$body.=<<<EOF
<br><br><hr><img id="ncr-open-close" src="kpi/graphs.php?m[0]=$graphType&m[1]=NCRStatPast12Months&buid=$buid" $kpireview_add><br>
EOF;
		$_TITLE="NCR Opened/Closed Past 12 Months";
		// setup the popup for the KPI Graph
		$groupTarget="";
		$popupDef = new tldOverlib($help[$_TITLE].$groupTarget, array("CAPTION"=>$_TITLE,"WIDTH"=>"500","linkName"=>$_TITLE));
		$body.=$popupDef->fetch();

// -----  NCR Responsibility past 12 month -----------//
$body.=<<<EOF
<br><br><hr><img src="kpi/graphs.php?m[0]=$graphType&m[1]=NCRResponsibilityPast12Months&buid=$buid" $kpireview_add><br>
EOF;
		$_TITLE = 'NCRResponsibilityPast12Months';
        // setup the popup for the KPI Graph
        $groupTarget="";
        $popupDef = new tldOverlib($help[$_TITLE].$groupTarget, array("CAPTION"=>$_TITLE,"WIDTH"=>"500","linkName"=>$_TITLE));
        $body.=$popupDef->fetch();

// -----  NCR Important Factor past 12 month -----------//
        $body.=<<<EOF
        <br><br><hr><img src="kpi/graphs.php?m[0]=$graphType&m[1]=NCRProcessPast12Months&buid=$buid" $kpireview_add><br>
EOF;
        $_TITLE = 'NCRProcessPast12Months';
        // setup the popup for the KPI Graph
        $groupTarget="";
        $popupDef = new tldOverlib($help[$_TITLE].$groupTarget, array("CAPTION"=>$_TITLE,"WIDTH"=>"500","linkName"=>$_TITLE));
        $body.=$popupDef->fetch();
