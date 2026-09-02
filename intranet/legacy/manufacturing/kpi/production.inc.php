<?php
$kpireview_add=('past12'==$graphType)?'class="kpireview"':'';
//-------------------------------------------------------------------//
	$body.=<<<EOF
<br><br><img src="kpi/graphs.php?m[0]=$graphType&m[1]=factoryStandardEfficiencyPast12Months&buid=$buid" $kpireview_add><br>
EOF;
		$_TITLE="Factory Standard Efficiency Definition";
		// setup the popup for the KPI Graph
		$groupTarget="<b>Group Target:</b>  ".$help["Factory Standard Efficiency Group Target"];
		$popupDef = new tldOverlib($help[$_TITLE].$groupTarget, array("CAPTION"=>$_TITLE,"WIDTH"=>"500","linkName"=>$_TITLE));
		$body.=$popupDef->fetch();
//-------------------------------------------------------------------//
	$body.=<<<EOF
<br><br><hr><img src="kpi/graphs.php?m[0]=$graphType&m[1]=factoryProductiveHourRatioPast12Months&buid=$buid" $kpireview_add><br>
EOF;
		$_TITLE="Factory Productive Hour Ratio Definition";
		// setup the popup for the KPI Graph
		$groupTarget="<b>Group Target:</b>  ".$help["Factory Productive Hour Ratio Group Target"];
		$popupDef = new tldOverlib($help[$_TITLE].$groupTarget, array("CAPTION"=>$_TITLE,"WIDTH"=>"500","linkName"=>$_TITLE));
		$body.=$popupDef->fetch();
//-------------------------------------------------------------------//
	$body.=<<<EOF
<br><br><hr><img src="kpi/graphs.php?m[0]=$graphType&m[1]=factoryProductivityPast12Months&buid=$buid" $kpireview_add><br>
EOF;
		$_TITLE="Factory Productivity Definition";
		// setup the popup for the KPI Graph
		$groupTarget="<b>Group Target:</b>  ".$help["Factory Productivity Group Target"];
		$popupDef = new tldOverlib($help[$_TITLE].$groupTarget, array("CAPTION"=>$_TITLE,"WIDTH"=>"500","linkName"=>$_TITLE));
		$body.=$popupDef->fetch();


?>
