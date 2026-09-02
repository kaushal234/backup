<?php
$DEFAULT_MENU .=<<<EOF
	<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
	<a href="$php_self?m[0]=kpi">Home</a>
	&nbsp;|&nbsp;<a href="$php_self?m[0]=kpi&m[1]=help">Help</a>
	EOF;

if ($user->isInGroup(['role_CSD'])) {
	$DEFAULT_MENU .=<<<EOF
		&nbsp;|&nbsp;<a href="$php_self?m[0]=kpi&m[1]=uploadKPI">Upload KPI</a>
	EOF;
}

switch ($m[1]) {
	case 'uploadKPI':
		if (!$user->isInGroup(['role_CSD'])) {
			$DEFAULT_ERROR[] = "ERROR: You do not have permissions";
			return;
		}

		$TITLE .= "\Upload Monthly KPIs";
		$form = new HTML_QuickForm('frm', 'post');
		$form->addElement('header','title', 'Upload Monthly KPIs');
		$form->addElement('hidden', 'm[0]', 'kpi');
		$form->addElement('hidden', 'm[1]', 'uploadKPI');
		$form->addElement('file', 'file', 'File (PDF only)');
		$form->addElement('submit', 'btnSubmit', 'Submit');
		$form->addRule('file', 'Required', 'required');

		if ($form->validate()) {
			$file = ($form->getElement('file')->getValue());
			$uploadFile = new basicFile($file['tmp_name']);
			$e = tldModFile::insert(
				[
					'module' => 'SPH',
					'parent_id' => 0,
					'description' => 'SPH Monthly KPIs',
					'filename' => 'SPR_KPI_results.pdf',
					'poster' => $user->getID(),
				],
				$file
			);

			if ($file['type'] !== 'application/pdf') {
				$DEFAULT_ERROR[] = 'ERROR: Only PDF files are allowed';
			} elseif(is_string($e)){
				$DEFAULT_ERROR[] = 'ERROR: File upload error';
			} else {
				$DEFAULT_SUCCESS[] = 'SUCCESS: File Uploaded';
			}
		}
		$body .= $form->toHTML();
		break;
	default:
		if(!$user->isInGroup(array("gg_ADMIN","gg_PARTS","gg_SALES", "gg_HR", "role_CSD"))){
			$DEFAULT_ERROR[] = "ERROR: You do not have permissions";
			return;
		}

		// Add overlib library for this section
		$JS_INCLUDE=array("/shared/javascript/overlib/overlib.js");
		$smarty->assign("js_includes",$JS_INCLUDE);

		// array to define the list of the diffent SPH with targets values and ERP#
		$whse=array(
			"SPH_WIN"=>"SPH Windsor (SPH WIN)",
			"SPH_SAL"=>"SPH Salinas (SPH SAL)",
			"SPH_HKG"=>"SPH Hong-Kong (SPH HKG)",
			"SPH_SHA"=>"SPH Shanghai (SPH SHA)",
			"SPH_MTL"=>"SPH MontLouis (SPH MTL)",
			"SPH_DUB"=>"SPH Dubai (SPH DUB)"
		);

		// setup of the definition array that will be used to automatically generate the Help Page and the Graph definition popups.
		$help=array("SPH KPI Help Page"=>"<b>Assumptions:</b><p>All day shall be considered as calendar days i.e. 365 days per annum.<br>
				An order received before 3:00pm must recorded same day.<br>
				Consider part quantity and not line items.<br>
				Calculation shall exclude ot consider the customer with down payment
				(payment in advance) as we are waiting for payment before shipment.<br>
				The calculation period is one month starting 1st of the month.</p>",
			"TDP Definition"=>"Number of parts shipped",
			"IFR Definition"=>"<b>Objectives:</b> Mesure our capability to react to a customer request within the same day<br>
							<b>Unit:</b> Pourcentage<br>
							<b>Calculation Method:</b><br>
							<ul><li>Order date record = packing slip realease date then YES</li>
							<li>Count number of YES versus Qty of parts shipped during the month</li></ul>
							<b>Group Target:</b> 75%",
			"AVT Definition"=>"<b>Objectives:</b> Measure our average response time to send a part<br>
							<b>Unit:</b> Days<br>
							<b>Calculation Method:</b><br>
							<ul><li>Sum of lead-time taken for each single part separtly</li>
							<li>over the total number of parts shipped during the month</li></ul>
							<b>Group Target:</b> 3 Days",
			"WFR Definition"=>"<b>Objectives:</b> Mesure our capability to react to a customer request within one week<br>
							<b>Unit:</b> Pourcentage<br>
							<b>Calculation Method:</b><br>
							<ul><li>Packing slip realease date superior or equal order date record+7days then YES</li>
							<li>Count number of YES versus Qty of parts shipped during the month</li></ul>
							<b>Group Target:</b> 90%",
			"Inventory in Days of Sales Definition"=>"<b>Objectives:</b> Mesure the spare parts inventory value in days of sales<br>
							<b>Unit:</b> Value<br>
							<b>Calculation Method:</b><br>
							<ul><li>'Net Spare Parts Inventory Value' divided by </li>
							<li>(Total Spare Parts Sales of past 3 month) / 90</li></ul>
							<b>Group Target:</b> "
		);
		
		$DEFAULT_TITLE .= "\KPI";
		$montlyKpiFiles = tldModFile::byConstraints(['module' => 'SPH', 'description' => 'SPH Monthly KPIs']);
		$lastMontlyKpiFile = array_pop($montlyKpiFiles);
		$body .= <<<EOF
	<a href="/en/private/common/index.php?m[0]=files&m[1]=view&m[2]=out&id={$lastMontlyKpiFile['id']}">Click here to see Official PDF Version</a>
EOF;

		if($war){
			$body.=<<<EOF
			<h2>Online Parts KPI - $whse[$war] - (BETA)</h2>
		
			<br><br><br><br><br><p><img src="kpi/graphs.php?m[0]=tdp&war=$war"></p><br>
		EOF;
			// setup the popup for TDP Graph
			$popupDef = new tldOverlib($help["TDP Definition"], array("CAPTION"=>"TDP","WIDTH"=>"500","linkName"=>"TDP Definition"));
			$body.=$popupDef->fetch();

			$body.=<<<EOF
			<br><br><br><br><br><p><img src="kpi/graphs.php?m[0]=ifr&war=$war"></p><br>
		EOF;
			// setup the popup for IFR Graph
			$popupDef = new tldOverlib($help["IFR Definition"], array("CAPTION"=>"Immediate Fill Rate (IFR)","WIDTH"=>"500","linkName"=>"IFR Definition"));
			$body.=$popupDef->fetch();

			$body.=<<<EOF
			<br><br><br><br><br><p><img src="kpi/graphs.php?m[0]=avt&war=$war"></p><br>
		EOF;
			// setup the popup for AVT Graph
			$popupDef = new tldOverlib($help["AVT Definition"], array("CAPTION"=>"Average Lead-Time (AVT)","WIDTH"=>"500","linkName"=>"AVT Definition"));
			$body.=$popupDef->fetch();

			$body.=<<<EOF
			<br><br><br><br><br><p><img src="kpi/graphs.php?m[0]=wfr&war=$war"></p><br>
		EOF;
			// setup the popup for WFR Graph
			$popupDef = new tldOverlib($help["WFR Definition"], array("CAPTION"=>"Week Fill rate (WFR)","WIDTH"=>"500","linkName"=>"WFR Definition"));
			$body.=$popupDef->fetch();

		// debut bbl
			$body.=<<<EOF
			<br><br><br><br><br><p><img src="kpi/graphs.php?m[0]=InventoryValue&war=$war"></p><br>
		EOF;
			// setup the popup for InventoryValue Graph
			$popupDef = new tldOverlib($help["Inventory in Days of Sales Definition"], array("CAPTION"=>"Inventory in Days of Sales","WIDTH"=>"500","linkName"=>"Inventory in Days of Sales Definition"));
			$body.=$popupDef->fetch();
		// fin bbl

		}else{

			$form = new tldHTMLList(
				$whse,
								 "war",
								 "$php_self?m[0]=kpi&war=$war",
								 array("title"=>"Please select SPH - (BETA)")
								 );
			$body .= $form->fetch();

			global $kernel;
			$router = $kernel->getContainer()->get('router');
		}
		switch($m[1]){
			case 'help':
				$DEFAULT_TITLE .= "\Help";
				foreach($help as $helpTitle=>$helpText){
				$helpBody .= <<<EOF
					<h3>$helpTitle</h3>
					<p>$helpText</p>
		EOF;

				}
				$body = $helpBody;
			break;
		}
}
?>
