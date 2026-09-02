<?php
//if(!$user->isInGroup(array("gg_ADMIN","gg_ACCT"))){
//	echo "You do not have permissions for this page..";
//	exit;
//}
$DEFAULT_TITLE .= "\KPI";
$DEFAULT_MENU .=<<<EOF
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<a href="$php_self?m[0]=kpi">Home</a>
&nbsp;|&nbsp;<a href="kpi/kpi_admin.php">KPI Admin</a>
EOF;

if($buid){
	$loc = new tldLocation($buid);
	$location = $loc->getHeader();

	$body.=<<<EOF
	<h3>Manufacturing KPI - ${location['location']}</h3>
	<h4>OTDP for Previous 12 Months (Percentage)</h4>
<img src="/en/private/product_support/odp/reports/graphs.php?m[0]=otdpPast12Months&buid=$buid"><br>
	<h4>Factory Average Days Late for Previous 12 Months (days)</h4>
<img src="/en/private/product_support/odp/reports/graphs.php?m[0]=otdpAvgDaysLatePast12Months&buid=$buid"><br>
	<h4>Cycle Count Quantity Performance for Previous 12 Months (Percentage)</h4>
<img src="kpi/graphs.php?m[0]=cycleCountQuantityPerformancePast12Months&buid=$buid"><br>
	<h4>Cycle Count Value Performance for Previous 12 Months (Percentage)</h4>
<img src="kpi/graphs.php?m[0]=cycleCountValuePerformancePast12Months&buid=$buid">
	<h4>Inventory Value (days)</h4>
<img src="kpi/graphs.php?m[0]=inventoryValuePast12Months&buid=$buid">
	<h4>Work In Progress Value (days)</h4>
<img src="kpi/graphs.php?m[0]=wipValuePast12Months&buid=$buid">
	<h4>Factory Standard Efficiency (FSE) for Previous 12 Months (Percentage)</h4>
<img src="kpi/graphs.php?m[0]=factoryStandardEfficiencyPast12Months&buid=$buid"><br>
	<h4>Factory Productive Hour Ratio (PHR) (Percentage)</h4>
<img src="kpi/graphs.php?m[0]=factoryProductiveHourRatioPast12Months&buid=$buid"><br>
	<h4>Factory Productivity (FSE x PHR) (Percentage)</h4>
<img src="kpi/graphs.php?m[0]=factoryProductivityPast12Months&buid=$buid"><br>
	<h4>Internal Customer Satisfaction</h4>
<img src="kpi/graphs.php?m[0]=internalCustomerSatisfactionPast12Months&buid=$buid"><br>
	<h4>Warranty Claim Counts for Previous 12 Months</h4>
<img src="/en/private/product_support/wc/reports/graphs.php?m[0]=countCurrentYearByFactoryType&man_location=${location['man_location']}"><br>
	<h4>PDC Focus Weight for Previous 12 Months</h4>
<img src="/en/private/product_support/pdc/reports/graphs.php?m[0]=historyByFactory&erp=${location['erp']}"><br>
	<h4>Number of PDC in Progress for Previous 12 Months</h4>
<img src="kpi/graphs.php?m[0]=pdcInProgressCountPast12Months&buid=$buid"><br>
	<h4>Number of PDC Opened During the Month for Previous 12 Months</h4>
<img src="/en/private/product_support/pdc/reports/graphs.php?m[0]=openedPerMonth&buid=$buid"><br>
EOF;
}else{
	$form = new tldHTMLList(
			tldUtils::getSqlToAssocArray("SELECT id,location FROM locations WHERE factory='Y' AND erp<>'' ORDER BY location"),
						 array(	"key"=>array("buid"=>"id"),
								"value"=>array("location")),
						 "$php_self?m[0]=kpi",
						 array("title"=>"Please select Factory")
						 );
	$body = $form->fetch();
}
