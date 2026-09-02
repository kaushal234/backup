<?php
$kpireview_add=('past12'==$graphType)?'class="kpireview"':'';
//-------------------------------------------------------------------//
	$body.=<<<EOF
<br><br><img src="kpi/graphs.php?m[0]=$graphType&m[1]=gt28Past12Months&buid=$buid" $kpireview_add><br>
EOF;
		$_TITLE="End of Month GT 28+ Definition";
		// setup the popup for the KPI Graph
		$groupTarget="<b>Group Target:</b>  ".$help["End of Month GT 28+ Group Target"];
		$popupDef = new tldOverlib($help[$_TITLE].$groupTarget, array("CAPTION"=>$_TITLE,"WIDTH"=>"500","linkName"=>$_TITLE));
		$body.=$popupDef->fetch();
//-------------------------------------------------------------------//
	$body.=<<<EOF
<br><br><hr><img src="kpi/graphs.php?m[0]=$graphType&m[1]=gt20Past12Months&buid=$buid" $kpireview_add><br>
EOF;
		$_TITLE="End of Month GT 20+ Definition";
		// setup the popup for the KPI Graph
		$groupTarget="<b>Group Target:</b>  ".$help["End of Month GT 20+ Group Target"];
		$popupDef = new tldOverlib($help[$_TITLE].$groupTarget, array("CAPTION"=>$_TITLE,"WIDTH"=>"500","linkName"=>$_TITLE));
		$body.=$popupDef->fetch();
//-------------------------------------------------------------------//
        $body.=<<<EOF
<br><br><hr><img src="kpi/graphs.php?m[0]=$graphType&m[1]=otdpPast12Months&buid=$buid" $kpireview_add><br>
EOF;
		$_TITLE="OTDP Factory Definition";
		// setup the popup for the KPI Graph
		$groupTarget="<b>Group Target:</b>  ".$help["OTDP Factory Group Target"];
		$popupDef = new tldOverlib($help[$_TITLE].$groupTarget, array("CAPTION"=>$_TITLE,"WIDTH"=>"500","linkName"=>$_TITLE));
		$body.=$popupDef->fetch();
//-------------------------------------------------------------------//
	$body.=<<<EOF
<br><br><hr><img src="kpi/graphs.php?m[0]=$graphType&m[1]=otdpAvgDaysLatePast12Months&buid=$buid" $kpireview_add><br>
EOF;
		$_TITLE="OTDP Factory AVG days late Definition";
		// setup the popup for the KPI Graph
		$groupTarget="<b>Group Target:</b>  ".$help["OTDP Factory AVG days late Group Target"];
		$popupDef = new tldOverlib($help[$_TITLE].$groupTarget, array("CAPTION"=>$_TITLE,"WIDTH"=>"500","linkName"=>$_TITLE));
		$body.=$popupDef->fetch();

?>
