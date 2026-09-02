<?php

// POPUP definitions

$help=array("Metrics Help Page"=>"<p> Metrics definitions </p>",

// BAAN STATS --->

	"Orders Statistic by Customer type"=>"<p><b>What it does measure exactly?</b><br>The total amount of orders by customer type</p>
	<b>How it is generated?</b><ul><li>Generated directly from BAAN</li></ul>",

	"Shipments Statistic by Customer type"=>"<p><b>What it does measure exactly?</b><br>The total amount of shipments by customer type</p>
	<b>How it is generated?</b><ul><li>Generated directly from BAAN</li></ul>",

// SFR STATS --->

	"Ordered SFR"=>"<p><b>What it does measure exactly?</b><br>Amongst all the SFR closed during the period, percentage of ORDERED ones</p>
	<b>How it is generated?</b><ul><li>Generated directly from SFR system</li></ul>",

	"Lost SFR"=>"<p><b>What it does measure exactly?</b><br>Amongst all the SFR closed during the period, percentage of LOST ones</p>
	<b>How it is generated?</b><ul><li>Generated directly from SFR system</li></ul>",

	"Average closure of SFR"=>"<p><b>What it does measure exactly?</b><br>Average number of months SFR stay opened (calculation made on total closed during the period)</p>
	<b>How it is generated?</b><ul><li>Generated directly from SFR system</li></ul>",

// TOC STATS --->

	"NTO"=>"<br/><b>What it does measure exactly?</b>
	<p>Measure amount of TOC that have been opened each month to the service department</p>
	<br/><b>How it is generated?</b><p>SUM all OPEN TOC during each month</p>",
    "nto_target"=>"",

	"NTS"=>"<br/><b>What it does measure exactly?</b>
	<p>Measure amount of TOC that the service department have solved each month</p>
	<br/><b>How it is generated?</b><p>SUM all SOLVED TOC during each month</p>",
    "nts_target"=>"",

	"TIR"=>"<br/><b>What it does measure exactly?</b>
	<p>Measure every month each Service department reactivity in service</p>
	<br/><b>How it is generated?</b><p>SUM all TOC solved in less than the expected hours during
	the month DIVIDED by the NTS of the same month MULTIPLIED by 100</p>
	<br/><b>Expected Hours per Zone</b><p>
	<ul>
		<li>Green: 48 hours</li>
		<li>Blue: 72 hours</li>
		<li>Yellow: 120 hours</li>
		<li>Red: 168 hours</li>
	</ul></p>",
    "tir_target"=>"",

	"TAT"=>"<br/><b>What it does measure exactly?</b>
	<p>Measure Service department efficiency through the average time to SOLVE TOC.</p>
	<br/><b>How it is generated?</b><p>For a particular month, SUM the time in days of
	all SOLVED TOC (From OPEN to SOLVED time - SUSPENDED time) during that month DIVIDED
	by the NTS of that particular month</p>",
	"tat_target"=>"",

	"TOL"=>"<br/><b>What it does measure exactly?</b>
	<p>Alerts about the oldest customer support issue not solved yet</p>
	<br/><b>How it is generated?</b><p>Get the list of TOC NOT SOLVED before the
	specified month AND take the bigger number of days opened until the end of this
	specified month</p>",
	"tol_target"=>"",

    'Average Days Open for TOC' => '<br/><b>What it does measure exactly?</b>
  <p>Sum of days open for all TOC considered in the period (status Open & In Progress only) divided by the number of TOC for the same period.</p>
  <br/><b>How it is generated?</b>
  <p>Calculation:
    <ul>
        <li>1). This Month for the period going from the first day of the month \'m\' until to date</li>
        <li>2). Last Month for the whole \'m-1\' month</li>
    </ul>
    </p>',
    'avg_toc_open_target' => '',

// CSR Stats --->

    "Dispatch AST & Activity" => "<br/><b>What it does measure exactly?</b>
	<p>Measure our ability to dispatch technician rapidly.</p>
	<br/><b>How it is generated?</b>
	<p>Calculation:
    <ul>
        <li>FDA: First Dispatch Ability: Date difference between Scheduled date and TOC opening date, in number of days. Averaged each month for each SSO.</li>
        <li>DPA: Dispatch Planning Accuracy: Date difference between Work date and Scheduled date, in number of days. Averaged each month for each SSO.</li>
        <li>DPA%: (Nb of times when DPA = 0) / (Nb of CSR or WC where Dispatch Tech is set to Y for the calendar month) * 100. For each SSO.</li>
    </ul>
    </p>",
    "dispatch_ast_target" => "",

// SALES STATS --->
	"Days of Sales Out for Previous 12 month"=>"<p><b>What does it measure exactly?</b>
	<br>It measures the Trade Account Receivables in days of sales</p>
	<b>How is it generated?</b>
	<ul><li>- one number entered monthly by BU controller</li>
		<li>- equal to'Trade Accounts Receivables (minus customer downpayments)'
			  divided by (Total Sales of past X months)/Y, where
		<ol type='1'>
			<li>for America, X=1, Y=30</li>
			<li>for Europe, X=2, Y=60</li>
			<li>for Asia, X=3, Y=90</li>
			</ol>
		</li>
	</ul>",

	"Days of Sales Out with Finished Goods for Previous 12 month"=>"<p><b>What it does measure exactly?</b>
	<br>It measures the Trade Account Receivables and the Finish Goods inventory in days of sales</p>
	<b>How is it generated?</b>
	<ul><li>- one number entered monthly by BU controller</li>
		<li>- equal to'Trade Accounts Receivables (minus customer downpayments)
		      + Net Finish Goods Inventory value'
			  divided by (Total Sales of past X months)/Y, where
		<ol type='1'>
			<li>for America, X=1, Y=30</li>
			<li>for Europe, X=2, Y=60</li>
			<li>for Asia, X=3, Y=90</li>
			</ol>
		</li>
	</ul>",


//	"GPTarget"=>array("targetLineColor"=>"blue")
);

// CROSS REFERENCE Table for customer type

// TLD EUR ----------------------->

$X_REF_CUST_TYPE[500] = array(
	"Ground Handler"	=>"0HDL",
	"Freightliner"		=>"0CAA",
	"Caterer"			=>"0CAT",
	"Agent"				=>"0AGE",
	"Airline"			=>"0AIR",
	"Leaser"			=>"0LEA",
	"Airport Authorities & Concessionaires"	=>"0APA",
	"Military"			=>"0MIL",
	"Other"				=>"0OTH"
);
$X_REF_CUST_TYPE[520] = $X_REF_CUST_TYPE[500];
$X_REF_CUST_TYPE[540] = $X_REF_CUST_TYPE[500];

// TLD AME ----------------------->

$X_REF_CUST_TYPE[300] = array(
	"Ground Handler"	=>"004",
	"Freightliner"		=>"003",
	"Caterer"			=>"",	// Not used
	"Agent"				=>"",	// Not used
	"Airline"			=>"002",
	"Leaser"			=>"",	// Not used
	"Airport Authorities & Concessionaires"	=>"005",
	"Military"			=>"006",
	"Other"				=>"001"
);

$X_REF_CUST_TYPE[400] = array(
	"Ground Handler"	=>"",
	"Freightliner"		=>"",
	"Caterer"			=>"",
	"Agent"				=>"",
	"Airline"			=>"",
	"Leaser"			=>"",
	"Airport Authorities & Concessionaires"	=>"",
	"Military"			=>"",
	"Other"				=>array("100","200","250","300","500","550","900")
);

$X_REF_CUST_TYPE[420] = array(
	"Ground Handler"	=>"004",
	"Freightliner"		=>"003",
	"Caterer"			=>"",	// Not used
	"Agent"				=>"",	// Not used
	"Airline"			=>"",	// Not used
	"Leaser"			=>"",	// Not used
	"Airport Authorities & Concessionaires"	=>"",	// Not used
	"Military"			=>"006",
	"Other"				=>""	// Not used
);

// TLD ASIA ----------------------->

$X_REF_CUST_TYPE[600] = array(
	"Ground Handler"	=>"004",
	"Freightliner"		=>"003",
	"Caterer"			=>"009",
	"Agent"				=>array("011","012","013","014"),
	"Airline"			=>"002",
	"Leaser"			=>"015",
	"Airport Authorities & Concessionaires"	=>"005",
	"Military"			=>"006",
	"Other"				=>array("001","007","008","010","020")
);

$X_REF_CUST_TYPE[610] = $X_REF_CUST_TYPE[600];
$X_REF_CUST_TYPE[700] = $X_REF_CUST_TYPE[600];

$X_REF_CUST_TYPE[640] = array(
	"Ground Handler"	=>"GRO",
	"Freightliner"		=>"CAR",
	"Caterer"			=>"CAT",
	"Agent"				=>"AGT",
	"Airline"			=>"AIR",
	"Leaser"			=>"LEA",
	"Airport Authorities & Concessionaires"	=>"POR",
	"Military"			=>"MIL",
	"Other"				=>array("OTR","MAI")
);

$X_REF_CUST_TYPE[660] = array(
	"Ground Handler"	=>"GRO",
	"Freightliner"		=>"CAR",
	"Caterer"			=>"CAT",
	"Agent"				=>"AGT",
	"Airline"			=>"AIR",
	"Leaser"			=>"LEA",
	"Airport Authorities & Concessionaires"	=>"POR",
	"Military"			=>"MIL",
	"Other"				=>"OTR"
);

?>
