<?php

$kpireview_add = ('past12'==$graphType) ? 'class="kpireview"' : '';

// Currency selection
$form = new HTML_QuickForm('frmEngineeringKPIFilter', 'get');
$form->addElement(  'hidden', 'm[0]', 'kpi');
$form->addElement(  'hidden', 'm[1]', 'buid');
$form->addElement(  'hidden', 'm[2]', 'engineering');
$form->addElement(  'hidden', 'buid', $buid);
$form->addElement(  'text', 'meaps', 'MEAP number');
$form->addElement(  'select', 'eapCategory', 'EAP Category', ['' => ''] + tldList::optionsByListNameAsListItemListItem('list.eap.category'));
$form->addElement(  'submit', 'btnSubmit', 'Submit');
$body .= $form->toHTML();

$body.=<<<EOF
<br><br><img src="kpi/graphs.php?m[0]=$graphType&m[1]=eapQtyPast12Months&buid=$buid&eapCategory=$eapCategory&meaps=$meaps" $kpireview_add><br>
EOF;
$_TITLE="EAP Qty per month for Previous 12 Months Definition";
$groupTarget="<b>Group Target:</b>  ".$help["EAP Qty per month Group Target"];
$popupDef = new tldOverlib($help[$_TITLE].$groupTarget, array("CAPTION"=>$_TITLE,"WIDTH"=>"500","linkName"=>$_TITLE));
$body.=$popupDef->fetch();

//-------------------------------------------------------------------//

$body.=<<<EOF
<br><br><hr><img src="kpi/graphs.php?m[0]=$graphType&m[1]=eapQtyClosedPast12Months&buid=$buid&eapCategory=$eapCategory&meaps=$meaps" $kpireview_add><br>
EOF;
$_TITLE="EAP Qty CLOSED per month for Previous 12 Months Definition";
$groupTarget="<b>Group Target:</b>  ".$help["EAP Qty CLOSED per month Group Target"];
$popupDef = new tldOverlib($help[$_TITLE].$groupTarget, array("CAPTION"=>$_TITLE,"WIDTH"=>"500","linkName"=>$_TITLE));
$body.=$popupDef->fetch();

//-------------------------------------------------------------------//

$body.=<<<EOF
<br><br><hr><img src="kpi/graphs.php?m[0]=$graphType&m[1]=eapByTypeQtyClosedPast12Months&buid=$buid&eapCategory=$eapCategory&meaps=$meaps" $kpireview_add><br>
EOF;
$_TITLE="EAP Qty CLOSED per month by Type for Previous 12 Months Definition";
$groupTarget="<b>Group Target:</b>  ".$help["EAP Qty CLOSED per month Group Target"];
$popupDef = new tldOverlib($help[$_TITLE].$groupTarget, array("CAPTION"=>$_TITLE,"WIDTH"=>"500","linkName"=>$_TITLE));
$body.=$popupDef->fetch();

//-------------------------------------------------------------------//
$body.=<<<EOF
<br><br><hr><img src="kpi/graphs.php?m[0]=$graphType&m[1]=eapClosedIn72Hrs&buid=$buid&eapCategory=$eapCategory&meaps=$meaps" $kpireview_add><br>
EOF;
$_TITLE="EAP % CLOSED In 72 Hours for Previous 12 Months Definition";
$groupTarget="<b>Group Target:</b>  ".$help["EAP % CLOSED In 72 Hours Group Target"];
$popupDef = new tldOverlib($help[$_TITLE].$groupTarget, array("CAPTION"=>$_TITLE,"WIDTH"=>"500","linkName"=>$_TITLE));
$body.=$popupDef->fetch();

//-------------------------------------------------------------------//

$body.=<<<EOF
<br><br><hr><img src="kpi/graphs.php?m[0]=$graphType&m[1]=eapAvgDaysOpen&buid=$buid&eapCategory=$eapCategory&meaps=$meaps" $kpireview_add><br>
EOF;
$_TITLE="EAP Average Number Of Days To Close for Previous 12 Months Definition";
$groupTarget="<b>Group Target:</b>  ".$help["EAP Average Number Of Days To Close Group Target"];
$popupDef = new tldOverlib($help[$_TITLE].$groupTarget, array("CAPTION"=>$_TITLE,"WIDTH"=>"500","linkName"=>$_TITLE));
$body.=$popupDef->fetch();

//-------------------------------------------------------------------//

$body.=<<<EOF
<br><br><hr><img src="kpi/graphs.php?m[0]=$graphType&m[1]=eapAvgDaysOpen2&buid=$buid&eapCategory=$eapCategory&meaps=$meaps" $kpireview_add><br>
EOF;
$_TITLE="EAP Average Number Of Days Open for Previous 12 Months Definition";
$groupTarget="<b>Group Target:</b>  ".$help["EAP Average Number Of Days Open Group Target"];
$popupDef = new tldOverlib($help[$_TITLE].$groupTarget, array("CAPTION"=>$_TITLE,"WIDTH"=>"500","linkName"=>$_TITLE));
$body.=$popupDef->fetch();

