<?php
$kpireview_add=('past12'==$graphType)?'class="kpireview"':'';
//-------------------------------------------------------------------//
	$body.=<<<EOF
<br><br><img src="kpi/graphs.php?m[0]=$graphType&m[1]=cycleCountQuantityPerformancePast12Months&buid=$buid" $kpireview_add><br>
EOF;
		$_TITLE="Cycle Count Quantity  Definition";
		// setup the popup for the KPI Graph
		$groupTarget="<b>Group Target:</b>  ".$help["Cycle Count Quantity Group Target"];
		$popupDef = new tldOverlib($help[$_TITLE].$groupTarget, array("CAPTION"=>$_TITLE,"WIDTH"=>"500","linkName"=>$_TITLE));
		$body.=$popupDef->fetch();
//-------------------------------------------------------------------//
	$body.=<<<EOF
<br><br><hr><img src="kpi/graphs.php?m[0]=$graphType&m[1]=numberItemCountedPast12Months&buid=$buid" $kpireview_add><br>
EOF;
		$_TITLE = 'Number of item counted definition';
		// setup the popup for the KPI Graph
		$popupDef = new tldOverlib($help[$_TITLE].$groupTarget, ['CAPTION' => $_TITLE, 'WIDTH' => '500', 'linkName' => $_TITLE]);
		$body .= $popupDef->fetch();
//-------------------------------------------------------------------//
	$body.=<<<EOF
<br><br><hr><img src="kpi/graphs.php?m[0]=$graphType&m[1]=sumValueItemCounterPast12Months&buid=$buid" $kpireview_add><br>
EOF;
		$_TITLE = 'Sum of the value of the item counted definition';
		// setup the popup for the KPI Graph
		$popupDef = new tldOverlib($help[$_TITLE].$groupTarget, ['CAPTION' => $_TITLE, 'WIDTH' => '500', 'linkName' => $_TITLE]);
		$body .= $popupDef->fetch();
//-------------------------------------------------------------------//
	$body.=<<<EOF
<br><br><hr><img src="kpi/graphs.php?m[0]=$graphType&m[1]=cycleCountValuePerformancePast12Months&buid=$buid" $kpireview_add><br>
EOF;
		$_TITLE="Cycle Count Value Definition";
		// setup the popup for the KPI Graph
		$groupTarget="<b>Group Target:</b>  ".$help["Cycle Count Value Group Target"];
		$popupDef = new tldOverlib($help[$_TITLE].$groupTarget, array("CAPTION"=>$_TITLE,"WIDTH"=>"500","linkName"=>$_TITLE));
		$body.=$popupDef->fetch();
//-------------------------------------------------------------------//
	$body.=<<<EOF
<br><br><hr><img src="kpi/graphs.php?m[0]=$graphType&m[1]=inventoryValuePast12Months&buid=$buid" $kpireview_add><br>
EOF;
		$_TITLE="Inventory Value Definition";
		// setup the popup for the KPI Graph
		$groupTarget="<b>Group Target:</b>  ".$help["Inventory Value Group Target"];
		$popupDef = new tldOverlib($help[$_TITLE].$groupTarget, array("CAPTION"=>$_TITLE,"WIDTH"=>"500","linkName"=>$_TITLE));
		$body.=$popupDef->fetch();
//-------------------------------------------------------------------//
	$body.=<<<EOF
<br><br><hr><img src="kpi/graphs.php?m[0]=$graphType&m[1]=wipValuePast12Months&buid=$buid" $kpireview_add><br>
EOF;
		$_TITLE="Work in Progress Value Definition";
		// setup the popup for the KPI Graph
		$groupTarget="<b>Group Target:</b>  ".$help["Work in Progress Value Group Target"];
		$popupDef = new tldOverlib($help[$_TITLE].$groupTarget, array("CAPTION"=>$_TITLE,"WIDTH"=>"500","linkName"=>$_TITLE));
		$body.=$popupDef->fetch();

?>
