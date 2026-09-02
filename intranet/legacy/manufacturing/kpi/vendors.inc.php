<?php
$kpireview_add=('past12'==$graphType)?'class="kpireview"':'';
//-------------------------------------------------------------------//
	$body.=<<<EOF
<br><br><img src="kpi/graphs.php?m[0]=$graphType&m[1]=otdpVendors&m[2]=reliability&buid=$buid" $kpireview_add><br>
EOF;
		$_TITLE="OTDP Vendors reliability Definition";
		// setup the popup for the KPI Graph
		$groupTarget="<b>Group Target:</b> ".$help["OTDP Vendors reliability Target"];
		$popupDef = new tldOverlib($help[$_TITLE].$groupTarget, array("CAPTION"=>$_TITLE,"WIDTH"=>"500","linkName"=>$_TITLE));
		$body.=$popupDef->fetch();

	$body.=<<<EOF
<br><br><hr><img src="kpi/graphs.php?m[0]=$graphType&m[1]=DOPOPast12Months&buid=$buid" $kpireview_add><br>
EOF;
		$_TITLE="Days of Payables Out Definition";
		// setup the popup for the KPI Graph
		$groupTarget="<b>Group Target:</b>  ".$help["Days of Payables Out Group Target"];
		$popupDef = new tldOverlib($help[$_TITLE].$groupTarget, array("CAPTION"=>$_TITLE,"WIDTH"=>"500","linkName"=>$_TITLE));
		$body.=$popupDef->fetch();
//-------------------------------------------------------------------//
	$body.=<<<EOF
<br><br><hr><img src="kpi/graphs.php?m[0]=$graphType&m[1]=VWCClosedRate&buid=$buid" $kpireview_add><br>
EOF;
		$_TITLE="VWC Closed Rate Definition";
		// setup the popup for the KPI Graph
		$popupDef = new tldOverlib($help[$_TITLE], array("CAPTION"=>$_TITLE,"WIDTH"=>"500","linkName"=>$_TITLE));
		$body.=$popupDef->fetch();
//-------------------------------------------------------------------//
	$body.=<<<EOF
<br><br><hr><img src="kpi/graphs.php?m[0]=$graphType&m[1]=VWCResolvedRate&buid=$buid" $kpireview_add><br>
EOF;
		$_TITLE="VWC Resolved Rate Definition";
		// setup the popup for the KPI Graph
		$popupDef = new tldOverlib($help[$_TITLE], array("CAPTION"=>$_TITLE,"WIDTH"=>"500","linkName"=>$_TITLE));
		$body.=$popupDef->fetch();
//-------------------------------------------------------------------//
$body.=<<<EOF
<br><br><hr><img src="kpi/graphs.php?m[0]=$graphType&m[1]=vwcSupplierRecoveryCostPast12Months&buid=$buid" $kpireview_add><br>
EOF;
		$_TITLE="VWC Supplier Recovery Cost Past 12 Months";
		// setup the popup for the KPI Graph
		$groupTarget="<b>Group Target:</b>  ".$help["VWC Supplier Recovery Cost Group Target"];
		$popupDef = new tldOverlib($help[$_TITLE].$groupTarget, array("CAPTION"=>$_TITLE,"WIDTH"=>"500","linkName"=>$_TITLE));
		$body.=$popupDef->fetch();
//-------------------------------------------------------------------//
	$body.=<<<EOF
<br><br><hr><img src="kpi/graphs.php?m[0]=$graphType&m[1]=VWCAvgResolvedTime&buid=$buid" $kpireview_add><br>
EOF;
		$_TITLE="VWC Average Resolved Time Definition";
		// setup the popup for the KPI Graph
		$popupDef = new tldOverlib($help[$_TITLE], array("CAPTION"=>$_TITLE,"WIDTH"=>"500","linkName"=>$_TITLE));
		$body.=$popupDef->fetch();
		//-------------------------------------------------------------------//
		$body.=<<<EOF
<br><br><hr><img src="kpi/graphs.php?m[0]=$graphType&m[1]=evendorusage&buid=$buid" $kpireview_add><br>
EOF;
		$_TITLE="Evendor system usage(Percentage)Definition";
		// setup the popup for the KPI Graph
		$popupDef = new tldOverlib($help[$_TITLE], array("CAPTION"=>$_TITLE,"WIDTH"=>"500","linkName"=>$_TITLE));
		$body.=$popupDef->fetch();
		//-----------------------------------------------------------------------//
		$body.=<<<EOF
<br><br><hr><img src="kpi/graphs.php?m[0]=$graphType&m[1]=evendorusage2&buid=$buid" $kpireview_add><br>
EOF;
		$_TITLE="Evendor system usage(Percentage) Definition";
		// setup the popup for the KPI Graph
		$popupDef = new tldOverlib($help[$_TITLE], array("CAPTION"=>$_TITLE,"WIDTH"=>"500","linkName"=>$_TITLE));
		$body.=$popupDef->fetch();
?>
