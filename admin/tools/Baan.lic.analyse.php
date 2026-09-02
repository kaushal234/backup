<?php
$form = new HTML_QuickForm('frmBaan.lic.analyse', 'post');
$form->addElement(	'hidden', 'm[0]', 'Baan.lic.analyse');
$form->addElement(	'header', 'title', "Set options below");
$form->addElement(	'date', 'date_from', 'From date',
        array(	"format"=>"Ymd", "minYear"=>date("Y")-2, "maxYear"=>date("Y")+2));
$form->addElement(	'date', 'date_to', 'To date',
        array(	"format"=>"Ymd", "minYear"=>date("Y")-2, "maxYear"=>date("Y")+2));

$form->addRule('date_from', 'Required','required');
$form->addRule('date_to', 'Required','required');

$form->setDefaults(array("date_from"=>date('Y-m-d')));
$form->setDefaults(array("date_to"=>date('Y-m-d')));

$form->addElement(	'submit', 'btnSubmit', 'Generate');

if(!$form->validate()){
    $body .= $form->toHTML();
    return;
}

$vars = tldUtils::cleanupFormInput($form->exportValues());
$vars['from']=implode('-',$vars['date_from']). "-00-00-00";
$vars['to']=implode('-',$vars['date_from']). "-99-99-99";

$body .= $form->toHTML();
$body .= "<p><strong>Baan licenses analyse</strong></p>";

// Dates list
$query = "select distinct dt from erp_licences where dt >= '" . $vars['from'] . "' and dt <= '" . $vars['to'] . "'";
$rowsTmp1 = tldUtils::getSqlToAssocArray($query);

// Departments
$query = "select erp_licences.baan_id as baan_id, erp_licences.dt as dt, people.div_id as div_id, people.bu_id as bu_id, people.dpt_id as dpt_id, tld_departments.dpt as dpt from erp_licences, people, tld_departments where dt >= '" . $vars['from'] . "' and dt <= '" . $vars['to'] . "' and erp_licences.baan_id=people.baan_id and people.dpt_id=tld_departments.id";
$rowsTmp2 = tldUtils::getSqlToAssocArray($query);

// Baan companies
$query = "select t_user, t_comp from tttaad200000";
$rowsTmp3 = tldUtils::getSqlToAssocArray($query, 'odbc', array("src"=>"baan"));

// Add Baan company to $rowsTmp2
foreach ($rowsTmp2 as $key2 => $value2) {
	foreach ($rowsTmp3 as $key3 => $value3) {
		if (trim($rowsTmp2[$key2]['baan_id'])==trim($rowsTmp3[$key3]['t_user'])) {
			$rowsTmp2[$key2]['baan_comp']=$rowsTmp3[$key3]['t_comp'];
		}
	}
}

$html .= "<table cellpadding='3'>";
$html .= "<thead bgcolor=2971A8>";
$html .= "<tr>";
$html .= "<th><font color=white>Date Time</font></th>";
$html .= "<th><font color=white>TOT Baan</font></th>";
$html .= "<th><font color=white>TOT N/A</font></th>";
$html .= "<th bgcolor=white><font color=white>&nbsp;</font></th>";
$html .= "<th><font color=white>TOT N.AM.</font></th>";
$html .= "<th><font color=white>TOT EUR</font></th>";
$html .= "<th><font color=white>TOT ASI</font></th>";
$html .= "<th bgcolor=white><font color=white>&nbsp;</font></th>";

$html .= "<th><font color=white>TLD AME 300</font></th>";
$html .= "<th><font color=white>TLD WIN 400</font></th>";
$html .= "<th><font color=white>TLD SHE 420</font></th>";
$html .= "<th><font color=white>TLD MTL 500</font></th>";
$html .= "<th><font color=white>TLD DTV 510</font></th>";
$html .= "<th><font color=white>TLD STL 520</font></th>";
$html .= "<th><font color=white>TLD EUR 540</font></th>";
$html .= "<th><font color=white>TLD MEAI 560</font></th>";
$html .= "<th><font color=white>TLD ASI 600</font></th>";
$html .= "<th><font color=white>TLD GST 620</font></th>";
$html .= "<th><font color=white>TLD SHA 640</font></th>";
$html .= "<th><font color=white>TLD WUX 660</font></th>";
$html .= "<th><font color=white>TLD CHI 680</font></th>";
$html .= "<th bgcolor=white><font color=white>&nbsp;</font></th>";

$html .= "<th><font color=white>Material control & purchasing</font></th>";
$html .= "<th><font color=white>Spare parts</font></th>";
$html .= "<th><font color=white>Finance & accounting</font></th>";
$html .= "<th><font color=white>Engineering</font></th>";
$html .= "<th><font color=white>Production</font></th>";
$html .= "<th><font color=white>Product Support</font></th>";
$html .= "<th><font color=white>Quality assurance</font></th>";
$html .= "<th><font color=white>Sales & service</font></th>";
$html .= "<th><font color=white>Service</font></th>";
$html .= "<th><font color=white>MIS</font></th>";
$html .= "<th><font color=white>Sales admin</font></th>";
$html .= "<th><font color=white>General Management</font></th>";
$html .= "<th><font color=white>Human Ressources</font></th>";



$html .= "</tr>";
$html .= "</thead>";

$html .= "<tbody>";

$bgcolor="#eeeeee";
$Lig=0;
$AVG_Baan=0;
$AVG_NA_DPT=0;
$AVG_C300=0;
$AVG_C400=0;
$AVG_C420=0;
$AVG_C500=0;
$AVG_C510=0;
$AVG_C520=0;
$AVG_C540=0;
$AVG_C560=0;
$AVG_C600=0;
$AVG_C620=0;
$AVG_C640=0;
$AVG_C660=0;
$AVG_C680=0;

$AVG_NAM=0;
$AVG_EUR=0;
$AVG_ASI=0;

$AVG_ENG=0;
$AVG_FIN=0;
$AVG_GEN=0;
$AVG_HR=0;
$AVG_MIS=0;
$AVG_MC=0;
$AVG_PRS=0;
$AVG_PRO=0;
$AVG_QA=0;
$AVG_SS=0;
$AVG_SAD=0;
$AVG_SRV=0;
$AVG_SPR=0;

foreach ($rowsTmp1 as $key1 => $value1) {
	$Lig += 1;
	if ($bgcolor=="#eeeeee") {
		$bgcolor="#d0d0d0";
	} else {
		$bgcolor="#eeeeee";
	}

	$html .= "<tr bgcolor=" . $bgcolor . ">";
	$html .= "<td>" . $rowsTmp1[$key1]['dt'] . "</td>";

	// TOT Baan
	$query = "select count(*) as nb from erp_licences where dt = '" . $rowsTmp1[$key1]['dt'] . "'";
	$rowsTmp4 = tldUtils::getSqlToAssocArray($query);
	$AVG_Baan += $rowsTmp4[0]['nb'];

	// Add companies data
	//-------------------
	$C300=0;
	$C400=0;
	$C420=0;
	$C500=0;
	$C510=0;
	$C520=0;
	$C540=0;
	$C560=0;
	$C600=0;
	$C620=0;
	$C640=0;
	$C660=0;
	$C680=0;

	$NAM=0;
	$EUR=0;
	$ASI=0;

	foreach ($rowsTmp2 as $key2 => $value2) {
		if ($rowsTmp2[$key2]['dt']==$rowsTmp1[$key1]['dt']) {
			if ($rowsTmp2[$key2]['baan_comp']=="300") { $C300 += 1; $NAM += 1; $AVG_C300 += 1; $AVG_NAM += 1; }
			if ($rowsTmp2[$key2]['baan_comp']=="400") { $C400 += 1; $NAM += 1; $AVG_C400 += 1; $AVG_NAM += 1; }
			if ($rowsTmp2[$key2]['baan_comp']=="420") { $C420 += 1; $NAM += 1; $AVG_C420 += 1; $AVG_NAM += 1; }
			if ($rowsTmp2[$key2]['baan_comp']=="500") { $C500 += 1; $EUR += 1; $AVG_C500 += 1; $AVG_EUR += 1; }
			if ($rowsTmp2[$key2]['baan_comp']=="510") { $C510 += 1; $EUR += 1; $AVG_C510 += 1; $AVG_EUR += 1; }
			if ($rowsTmp2[$key2]['baan_comp']=="520") { $C520 += 1; $EUR += 1; $AVG_C520 += 1; $AVG_EUR += 1; }
			if ($rowsTmp2[$key2]['baan_comp']=="540") { $C540 += 1; $EUR += 1; $AVG_C540 += 1; $AVG_EUR += 1; }
			if ($rowsTmp2[$key2]['baan_comp']=="560") { $C560 += 1; $EUR += 1; $AVG_C560 += 1; $AVG_EUR += 1; }
			if ($rowsTmp2[$key2]['baan_comp']=="600") { $C600 += 1; $ASI += 1; $AVG_C600 += 1; $AVG_ASI += 1; }
			if ($rowsTmp2[$key2]['baan_comp']=="620") { $C620 += 1; $ASI += 1; $AVG_C620 += 1; $AVG_ASI += 1; }
			if ($rowsTmp2[$key2]['baan_comp']=="640") { $C640 += 1; $ASI += 1; $AVG_C640 += 1; $AVG_ASI += 1; }
			if ($rowsTmp2[$key2]['baan_comp']=="660") { $C660 += 1; $ASI += 1; $AVG_C660 += 1; $AVG_ASI += 1; }
			if ($rowsTmp2[$key2]['baan_comp']=="680") { $C680 += 1; $ASI += 1; $AVG_C680 += 1; $AVG_ASI += 1; }
		}
	}

	// Add department data
	//--------------------

	$ENG=0;
	$FIN=0;
	$GEN=0;
	$HR=0;
	$MIS=0;
	$MC=0;
	$PRS=0;
	$PRO=0;
	$QA=0;
	$SS=0;
	$SAD=0;
	$SRV=0;
	$SPR=0;

	foreach ($rowsTmp2 as $key2 => $value2) {
		if ($rowsTmp2[$key2]['dt']==$rowsTmp1[$key1]['dt']) {
			if ($rowsTmp2[$key2]['dpt']=="Engineering") 						{ $ENG += 1; $AVG_ENG +=1; }
			if ($rowsTmp2[$key2]['dpt']=="Finance & Accounting") 				{ $FIN += 1; $AVG_FIN +=1; }
			if ($rowsTmp2[$key2]['dpt']=="General Management") 					{ $GEN += 1; $AVG_GEN +=1; }
			if ($rowsTmp2[$key2]['dpt']=="Human Ressources") 					{ $HR += 1;  $AVG_HR  +=1; }
			if ($rowsTmp2[$key2]['dpt']=="Management of Information System") 	{ $MIS += 1; $AVG_MIS +=1; }
			if ($rowsTmp2[$key2]['dpt']=="Material control & Purchasing") 		{ $MC += 1;  $AVG_MC  +=1; }
			if ($rowsTmp2[$key2]['dpt']=="Product Support") 					{ $PRS += 1; $AVG_PRS +=1; }
			if ($rowsTmp2[$key2]['dpt']=="Production") 							{ $PRO += 1; $AVG_PRO +=1; }
			if ($rowsTmp2[$key2]['dpt']=="Quality Assurance") 					{ $QA += 1;  $AVG_QA  +=1; }
			if ($rowsTmp2[$key2]['dpt']=="Sales & Service") 					{ $SS += 1;  $AVG_SS  +=1; }
			if ($rowsTmp2[$key2]['dpt']=="Sales Administration") 				{ $SAD += 1; $AVG_SAD +=1; }
			if ($rowsTmp2[$key2]['dpt']=="Service") 							{ $SRV += 1; $AVG_SRV +=1; }
			if ($rowsTmp2[$key2]['dpt']=="Spare Parts") 						{ $SPR += 1; $AVG_SPR +=1; }
		}
	}

	$NA_DPT = $rowsTmp4[0]['nb']-$ENG-$FIN-$GEN-$HR-$MIS-$MC-$PRS-$PRO-$QA-$SS-$SAD-$SRV-$SPR;
	$AVG_NA_DPT += $NA_DPT;

	$html .= "<td>" . $rowsTmp4[0]['nb'] . "</td>";	// Total Baan licences
	$html .= "<td>" . $NA_DPT . "</td>";			// Admin
	$html .= "<td bgcolor=white>&nbsp;</td>";
	$html .= "<td>" . $NAM . "</td>";				// North America
	$html .= "<td>" . $EUR . "</td>";				// Europe
	$html .= "<td>" . $ASI . "</td>";				// Asia
	$html .= "<td bgcolor=white>&nbsp;</td>";

	$html .= "<td>" . $C300 . "</td>";
	$html .= "<td>" . $C400 . "</td>";
	$html .= "<td>" . $C420 . "</td>";
	$html .= "<td>" . $C500 . "</td>";
	$html .= "<td>" . $C510 . "</td>";
	$html .= "<td>" . $C520 . "</td>";
	$html .= "<td>" . $C540 . "</td>";
	$html .= "<td>" . $C560 . "</td>";
	$html .= "<td>" . $C600 . "</td>";
	$html .= "<td>" . $C620 . "</td>";
	$html .= "<td>" . $C640 . "</td>";
	$html .= "<td>" . $C660 . "</td>";
	$html .= "<td>" . $C680 . "</td>";
	$html .= "<td bgcolor=white>&nbsp;</td>";

	$html .= "<td>" . $MC . "</td>";	// Material control & Puchasing
	$html .= "<td>" . $SPR . "</td>";	// 	Spare Parts
	$html .= "<td>" . $FIN . "</td>";	// Finance & Accounting
	$html .= "<td>" . $ENG . "</td>";	// Engineering
	$html .= "<td>" . $PRO . "</td>";	// Production
	$html .= "<td>" . $PRS . "</td>";	// Product Support
	$html .= "<td>" . $QA . "</td>";	// Quality Assurance
	$html .= "<td>" . $SS . "</td>";	// Sales & Service
	$html .= "<td>" . $SRV . "</td>";	// Service
	$html .= "<td>" . $MIS . "</td>";	// Management of Information System
	$html .= "<td>" . $SAD . "</td>";	// Sales Administration
	$html .= "<td>" . $GEN . "</td>";	// General Management
	$html .= "<td>" . $HR . "</td>";	// Human Ressources
	$html .= "</tr>";
}
$html .= "</tbody>";
$html .= "<tfoot bgcolor=2971A8>";

// Average
	$AVG_Baan = round($AVG_Baan / $Lig,0);
	$AVG_NA_DPT = round($AVG_NA_DPT / $Lig,0);
	$LIC_OK = $AVG_Baan - $AVG_NA_DPT;
	$AVG_NAM = round($AVG_NAM / $Lig,0);
	$AVG_EUR = round($AVG_EUR / $Lig,0);
	$AVG_ASI = round($AVG_ASI / $Lig,0);
	$AVG_C300 = round($AVG_C300 / $Lig,0);
	$AVG_C400 = round($AVG_C400 / $Lig,0);
	$AVG_C420 = round($AVG_C420 / $Lig,0);
	$AVG_C500 = round($AVG_C500 / $Lig,0);
	$AVG_C510 = round($AVG_C510 / $Lig,0);
	$AVG_C520 = round($AVG_C520 / $Lig,0);
	$AVG_C540 = round($AVG_C540 / $Lig,0);
	$AVG_C560 = round($AVG_C560 / $Lig,0);
	$AVG_C600 = round($AVG_C600 / $Lig,0);
	$AVG_C620 = round($AVG_C620 / $Lig,0);
	$AVG_C640 = round($AVG_C640 / $Lig,0);
	$AVG_C660 = round($AVG_C660 / $Lig,0);
	$AVG_C680 = round($AVG_C680 / $Lig,0);
	$AVG_MC =   round($AVG_MC   / $Lig,0);
	$AVG_SPR =  round($AVG_SPR  / $Lig,0);
	$AVG_FIN =  round($AVG_FIN  / $Lig,0);
	$AVG_ENG =  round($AVG_ENG  / $Lig,0);
	$AVG_GEN =  round($AVG_GEN  / $Lig,0);
	$AVG_HR =   round($AVG_HR   / $Lig,0);
	$AVG_MIS =  round($AVG_MIS  / $Lig,0);
	$AVG_PRS =  round($AVG_PRS  / $Lig,0);
	$AVG_PRO =  round($AVG_PRO  / $Lig,0);
	$AVG_QA =   round($AVG_QA   / $Lig,0);
	$AVG_SS =   round($AVG_SS   / $Lig,0);
	$AVG_SAD =  round($AVG_SAD  / $Lig,0);
	$AVG_SRV =  round($AVG_SRV  / $Lig,0);

	$html .= "<tr>";
	$html .= "<td><font color=white>AVG</td>";
	$html .= "<td><font color=white>" . $AVG_Baan . "</font></td>";		// Total Baan licences
	$html .= "<td><font color=white>" . $AVG_NA_DPT . "</font></td>";			// Admin
	$html .= "<td bgcolor=white>&nbsp;</td>";
	$html .= "<td><font color=white>" . $AVG_NAM . "</font></td>";				// North America
	$html .= "<td><font color=white>" . $AVG_EUR . "</font></td>";				// Europe
	$html .= "<td><font color=white>" . $AVG_ASI . "</font></td>";				// Asia
	$html .= "<td bgcolor=white>&nbsp;</td>";

	$html .= "<td><font color=white>" . $AVG_C300 . "</font></td>";
	$html .= "<td><font color=white>" . $AVG_C400 . "</font></td>";
	$html .= "<td><font color=white>" . $AVG_C420 . "</font></td>";
	$html .= "<td><font color=white>" . $AVG_C500 . "</font></td>";
	$html .= "<td><font color=white>" . $AVG_C510 . "</font></td>";
	$html .= "<td><font color=white>" . $AVG_C520 . "</font></td>";
	$html .= "<td><font color=white>" . $AVG_C540 . "</font></td>";
	$html .= "<td><font color=white>" . $AVG_C560 . "</font></td>";
	$html .= "<td><font color=white>" . $AVG_C600 . "</font></td>";
	$html .= "<td><font color=white>" . $AVG_C620 . "</font></td>";
	$html .= "<td><font color=white>" . $AVG_C640 . "</font></td>";
	$html .= "<td><font color=white>" . $AVG_C660 . "</font></td>";
	$html .= "<td><font color=white>" . $AVG_C680 . "</font></td>";
	$html .= "<td bgcolor=white>&nbsp;</td>";

	$html .= "<td><font color=white>" . $AVG_MC . "</font></td>";	// Material control & Puchasing
	$html .= "<td><font color=white>" . $AVG_SPR . "</font></td>";	// 	Spare Parts
	$html .= "<td><font color=white>" . $AVG_FIN . "</font></td>";	// Finance & Accounting
	$html .= "<td><font color=white>" . $AVG_ENG . "</font></td>";	// Engineering
	$html .= "<td><font color=white>" . $AVG_PRO . "</font></td>";	// Production
	$html .= "<td><font color=white>" . $AVG_PRS . "</font></td>";	// Product Support
	$html .= "<td><font color=white>" . $AVG_QA . "</font></td>";	// Quality Assurance
	$html .= "<td><font color=white>" . $AVG_SS . "</font></td>";	// Sales & Service
	$html .= "<td><font color=white>" . $AVG_SRV . "</font></td>";	// Service
	$html .= "<td><font color=white>" . $AVG_MIS . "</font></td>";	// Management of Information System
	$html .= "<td><font color=white>" . $AVG_SAD . "</font></td>";	// Sales Administration
	$html .= "<td><font color=white>" . $AVG_GEN . "</font></td>";	// General Management
	$html .= "<td><font color=white>" . $AVG_HR . "</font></td>";	// Human Ressources
	$html .= "</tr>";


// Average%
	$AVG_NA_DPT = round(($AVG_NA_DPT / $AVG_Baan) * 100,0) . "%";
	$AVG_Baan = "100%";
	$AVG_NAM = round(($AVG_NAM / $LIC_OK) * 100,0) . "%";
	$AVG_EUR = round(($AVG_EUR / $LIC_OK) * 100,0) . "%";
	$AVG_ASI = round(($AVG_ASI / $LIC_OK) * 100,0) . "%";
	$AVG_C300 = round(($AVG_C300 / $LIC_OK) * 100,0) . "%";
	$AVG_C400 = round(($AVG_C400 / $LIC_OK) * 100,0) . "%";
	$AVG_C420 = round(($AVG_C420 / $LIC_OK) * 100,0) . "%";
	$AVG_C500 = round(($AVG_C500 / $LIC_OK) * 100,0) . "%";
	$AVG_C510 = round(($AVG_C510 / $LIC_OK) * 100,0) . "%";
	$AVG_C520 = round(($AVG_C520 / $LIC_OK) * 100,0) . "%";
	$AVG_C540 = round(($AVG_C540 / $LIC_OK) * 100,0) . "%";
	$AVG_C560 = round(($AVG_C560 / $LIC_OK) * 100,0) . "%";
	$AVG_C600 = round(($AVG_C600 / $LIC_OK) * 100,0) . "%";
	$AVG_C620 = round(($AVG_C620 / $LIC_OK) * 100,0) . "%";
	$AVG_C640 = round(($AVG_C640 / $LIC_OK) * 100,0) . "%";
	$AVG_C660 = round(($AVG_C660 / $LIC_OK) * 100,0) . "%";
	$AVG_C680 = round(($AVG_C680 / $LIC_OK) * 100,0) . "%";
	$AVG_MC =   round(($AVG_MC   / $LIC_OK) * 100,0) . "%";
	$AVG_SPR =  round(($AVG_SPR  / $LIC_OK) * 100,0) . "%";
	$AVG_FIN =  round(($AVG_FIN  / $LIC_OK) * 100,0) . "%";
	$AVG_ENG =  round(($AVG_ENG  / $LIC_OK) * 100,0) . "%";
	$AVG_GEN =  round(($AVG_GEN  / $LIC_OK) * 100,0) . "%";
	$AVG_HR =   round(($AVG_HR   / $LIC_OK) * 100,0) . "%";
	$AVG_MIS =  round(($AVG_MIS  / $LIC_OK) * 100,0) . "%";
	$AVG_PRS =  round(($AVG_PRS  / $LIC_OK) * 100,0) . "%";
	$AVG_PRO =  round(($AVG_PRO  / $LIC_OK) * 100,0) . "%";
	$AVG_QA =   round(($AVG_QA   / $LIC_OK) * 100,0) . "%";
	$AVG_SS =   round(($AVG_SS   / $LIC_OK) * 100,0) . "%";
	$AVG_SAD =  round(($AVG_SAD  / $LIC_OK) * 100,0) . "%";
	$AVG_SRV =  round(($AVG_SRV  / $LIC_OK) * 100,0) . "%";

	$html .= "<tr>";
	$html .= "<td><font color=white>AVG%</td>";
	$html .= "<td><font color=white>" . $AVG_Baan . "</font></td>";		// Total Baan licences
	$html .= "<td><font color=white>" . $AVG_NA_DPT . "</font></td>";			// Admin
	$html .= "<td bgcolor=white>&nbsp;</td>";
	$html .= "<td><font color=white>" . $AVG_NAM . "</font></td>";				// North America
	$html .= "<td><font color=white>" . $AVG_EUR . "</font></td>";				// Europe
	$html .= "<td><font color=white>" . $AVG_ASI . "</font></td>";				// Asia
	$html .= "<td bgcolor=white>&nbsp;</td>";

	$html .= "<td><font color=white>" . $AVG_C300 . "</font></td>";
	$html .= "<td><font color=white>" . $AVG_C400 . "</font></td>";
	$html .= "<td><font color=white>" . $AVG_C420 . "</font></td>";
	$html .= "<td><font color=white>" . $AVG_C500 . "</font></td>";
	$html .= "<td><font color=white>" . $AVG_C510 . "</font></td>";
	$html .= "<td><font color=white>" . $AVG_C520 . "</font></td>";
	$html .= "<td><font color=white>" . $AVG_C540 . "</font></td>";
	$html .= "<td><font color=white>" . $AVG_C560 . "</font></td>";
	$html .= "<td><font color=white>" . $AVG_C600 . "</font></td>";
	$html .= "<td><font color=white>" . $AVG_C620 . "</font></td>";
	$html .= "<td><font color=white>" . $AVG_C640 . "</font></td>";
	$html .= "<td><font color=white>" . $AVG_C660 . "</font></td>";
	$html .= "<td><font color=white>" . $AVG_C680 . "</font></td>";
	$html .= "<td bgcolor=white>&nbsp;</td>";

	$html .= "<td><font color=white>" . $AVG_MC . "</font></td>";	// Material control & Puchasing
	$html .= "<td><font color=white>" . $AVG_SPR . "</font></td>";	// 	Spare Parts
	$html .= "<td><font color=white>" . $AVG_FIN . "</font></td>";	// Finance & Accounting
	$html .= "<td><font color=white>" . $AVG_ENG . "</font></td>";	// Engineering
	$html .= "<td><font color=white>" . $AVG_PRO . "</font></td>";	// Production
	$html .= "<td><font color=white>" . $AVG_PRS . "</font></td>";	// Product Support
	$html .= "<td><font color=white>" . $AVG_QA . "</font></td>";	// Quality Assurance
	$html .= "<td><font color=white>" . $AVG_SS . "</font></td>";	// Sales & Service
	$html .= "<td><font color=white>" . $AVG_SRV . "</font></td>";	// Service
	$html .= "<td><font color=white>" . $AVG_MIS . "</font></td>";	// Management of Information System
	$html .= "<td><font color=white>" . $AVG_SAD . "</font></td>";	// Sales Administration
	$html .= "<td><font color=white>" . $AVG_GEN . "</font></td>";	// General Management
	$html .= "<td><font color=white>" . $AVG_HR . "</font></td>";	// Human Ressources
	$html .= "</tr>";

$html .= "</tfoot>";
$html .= "</table>";
$html .= "</div>";
$body .= $html;

EOF;


?>
