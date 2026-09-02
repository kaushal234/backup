<?php

// Setup of the definition description for Help Page and Graph definition popups

$help=array(

// GENERAL CONF ---->

	"KPI Help Page"=>"<p> kpi definitions </p>",
	"GPTarget"=>array("targetLineColor"=>"blue"),

// SUPPORT KPI ---->

	"End of Month GT 28+ Definition"=>"<p><b>What it does measure exactly?</b><br>On all the units First green tagged during one month, this is the percentage of the units that have been First green tagged after the 28th day of the month.</p>
	<b>How it is generated?</b><ul><li>It is generated automatically out of the QA/GT module every month.</li></ul>",
	"End of Month GT 28+ Group Target"=>10,

	"End of Month GT 20+ Definition"=>"<p><b>What it does measure exactly?</b><br>On all the units First green tagged during one month, this is the percentage of the units that have been First green tagged after the 20th day of the month.</p>
	<b>How it is generated?</b><ul><li>It is generated automatically out of the QA/GT module every month.</li></ul>",
	"End of Month GT 20+ Group Target"=>33,

	"OTDP Factory Definition"=>"<p><b>What it does measure exactly?</b><br>On all units delivered this month in this factory, this is the percentage of units First Green Tagged at max 4 days after the factory promised date</p>
	<b>How it is generated?</b><ul><li>Generated directly out of the ODP online every month</li></ul>",
	"OTDP Factory Group Target"=>95,

	"OTDP Factory AVG days late Definition"=>"<p><b>What it does measure exactly?</b><br>On all units delivered this month in this factory, you make the sum of days late for only the units late and divide it by the number of units First GT during the month</p>
	<b>How it is generated?</b><ul><li>Generated directly out of the ODP online every month</li></ul>",
	"OTDP Factory AVG days late Group Target"=>2,


// VENDOR OTDP ---->

    "OTDP Vendors reliability Target"=>95,

	"OTDP Vendors reliability Definition"=>"<b>What it does measure exactly?</b><br><p>SUPPLIER OTDP RELIABILITY is based on the suppliers actual delivery vs the supplier initial confirmation in percent of line items received within -15 and +5 days of the PO</p>
	<b>How it is generated?</b><ul><li>Generated directly and automatically from BAAN</li></ul>",

	"OTDP Vendors reliability by Parts Definition"=>"<b>What it does measure exactly?</b><br><p>SUPPLIER OTDP RELIABILITY is based on the suppliers actual delivery vs the supplier initial confirmation in percent of a specified part received within -15 and +5 days of the PO</p>
	<b>How it is generated?</b><ul><li>Generated directly and automatically from BAAN</li></ul>",

	"OTDP Vendors reliability by Buyer Definition"=>"<b>What it does measure exactly?</b><br><p>SUPPLIER OTDP RELIABILITY is based on the suppliers actual delivery vs the supplier initial confirmation in percent of line items received by the buyer, within -15 and +5 days of the PO</p>
	<b>How it is generated?</b><ul><li>Generated directly and automatically from BAAN</li></ul>",

	"OTDP Vendors reliability by Vendor Definition"=>"<b>What it does measure exactly?</b><br><p>SUPPLIER OTDP RELIABILITY is based on the suppliers actual delivery vs the supplier initial confirmation in percent of line items received for a specified vendor, within -15 and +5 days of the PO</p>
	<b>How it is generated?</b><ul><li>Generated directly and automatically from BAAN</li></ul>",

	"Days of Payables Out Definition"=>"<p><b>What it does measure exactly?</b><br>It measures the Account Payables in days of sales (i.e. the time we take to pay our suppliers in average)</p>
    <b>How it is generated?</b><ul><li>Trade Accounts Payable divided by ((Sales of past 3 months) / 90)</li></ul>
    <p><i>Trade Accounts Payable and Sales are entered in the KPI Admin</i></p>",
    "Days of Payables Out Group Target"=>60,

	"VWC Resolved Rate Definition"=>"<p><b>What it does measure exactly?</b><br>It measures the VWC Closed as Resolved Percentage</p>
    <b>How it is generated?</b><ul><li>Calculated as the ratio of \"CLOSED_RESOLVED\" vs \"total number of closed\" (%)</li></ul>",
    "VWC Resolved Rate Group Target"=>"NA",

	"VWC Closed Rate Definition"=>"<p><b>What it does measure exactly?</b><br>It measures the VWC Closed to open</p>
    <b>How it is generated?</b><ul><li>Calculated as the ratio of \"total number closed\" vs \"total number of open\" (%)</li></ul>",
    "VWC Closed Rate Group Target"=>"NA",

	"VWC Average Resolved Time Definition"=>"<p><b>What it does measure exactly?</b><br>It averages the amount of days opened for VWC Closed as Resolved</p>",
    "VWC Average Resolved Time Group Target"=>"NA",

	"Evendor system usage(Percentage)Definition"=>"<p><b>What it does measure exactly?</b><br>It shows activity of active eVendor accounts and their weight in total figure of vendors  for each ERP# disregarding Shadow connections</p>
	<b>How it is generated?</b><ul><li> Per ERP# [Number of Vendors (through 1 or N eVendor valid contacts i.e. enable) who logged-in at least once during the month] / [total number of unique Vendors in ERP # ]</li></ul>",
	"Evendor system usage(Percentage)Definition Group Target"=>"NA",

	"Evendor system usage(Percentage) Definition"=>"<p><b>What it does measure exactly?</b><br>It shows activity of eVendors in eVendor portal towards activity of eVendors in terms of orders in the month<br>% can exceed 100% as some vendors are connected to manage VWC, SCAR, Forecast, etc.. ie connected even if no order opened during the month
    </p>
	<b>How it is generated?</b><ul><li>Per ERP# [Number of Vendors who logged-in at least once during the month (count limited to one per SUNO in case several contacts)] / [Total number of Vendors/SUNO which placed at least one order in the considered month]</li></ul>",
	"Evendor system usage(Percentage) Definition Group Target"=>"NA",
// PURCHASING ---->

	"Vendor PO Confirm Definition"=>"<p><b>What it does measure exactly?</b><br>It measures the percentage of POL confirmed dates from vendors</p>
    <b>How it is generated?</b><ul><li>Percent calculated from POL from Baan to confirmed date submitted from eVendor (date by PO opened date)</li></ul>",
    "Vendor PO Confirm Group Target"=>98,

// <------

	"Cycle Count Quantity  Definition"=>"<p><b>What it does measure exactly?</b><br>Measures the percentage of line items cycle counted during the month on which a variance has been found</p>
	<b>How it is generated?</b><ul>Cost accounting to input 2 numbers:
								<ul><li>the number of items they counted during the month</li>
								<li>the number of items on which a variance was found</li></ul>
								The system calculates and display the ratio between these two numbers in percentage (2 digits)</ul>",
	"Cycle Count Quantity Group Target"=>2,

	"Number of item counted definition"=>"<p><b>Number of item counted in the finance KPI</b></p>",

	"Sum of the value of the item counted definition"=>"<p><b>Sum of the value of the item counted in the finance KPI</b></p>",

	"Cycle Count Value Definition"=>"<p><b>What it does measure exactly?</b><br>Measures the percentage on inventory valuation variance found during the cycle count of the month</p>
	<b>How it is generated?</b><ul>Cost accounting to input 2 numbers:
								<ul><li>the $ value of items they counted during the month</li>
								<li>the sum of the value of the variances found</li></ul>
								The system calculates and display the ratio between these two numbers in percentage (2 digits)</ul>",
	"Cycle Count Value Group Target"=>0.5,

	"Inventory Value Definition"=>"<p><b>What it does measure exactly?</b><br>Measures the inventory value in days of Sales </p>
	<b>How it is generated?</b><ul><li>One number to enter monthly by BU accounting people</li>
									<li>equal to <<i>Net Inventory Value</i> divided by (Total Sales of past 3 months) / 90</li></ul>",
	"Inventory Value Group Target"=>45,

    "Inventory KPI Definition"=>"<p><b>What it does measure exactly?</b><br>Inventory data in past 12 months</p>
	<b>How it is generated?</b><ul><li>Finish good value</li>
									<li>equal to <<i>Inventory value</i>> minus <<i>Raw material value</i>> minus <<i>WIP value</i>> </li></ul>",
    "Inventory Target"=>"NA",

	"Work in Progress Value Definition"=>"<p><b>What it does measure exactly?</b><br>Measures the WIP value in days of Sales </p>
	<b>How it is generated?</b><ul><li>One number to enter monthly by BU accounting people</li>
									<li>equal to <i>Net WIP Inventory Value</i> divided by (Total Sales of past 3 months) / 90</li></ul>",
	"Work in Progress Value Group Target"=>30,

	"Factory Standard Efficiency Definition"=>"<p><b>What it does measure exactly?</b><br>Measures the ratio between Standard Hours allocated on units and Actual hours allocated on units.</p>
	<b>How it is generated?</b><ul>Cost accounting to input 2 numbers:
								<ul><li>the standard hours allocated to units shipped in the month</li>
								<li>the actual hours spent on units shipped in the month</li></ul>
								The system calculates and display the ratio between these two numbers in percentage format</ul>",
	"Factory Standard Efficiency Group Target"=>'NA',

	"Factory Productive Hour Ratio Definition"=>"<p><b>What it does measure exactly?</b><br>Measures the ratio between productive hours a allocated on work orders during the month and potential hours of the factory during the same month.</p>
	<b>How it is generated?</b><ul>Cost accounting to input 2 numbers:
								<ul><li>the productive hours allocated on work orders during the month
								<li>the potential hours of the factory during the same month</ul>
								The system calculates and display the ratio between these two number in percentage</ul>",
	"Factory Productive Hour Ratio Group Target"=>85,

	"Factory Productivity Definition"=>"<p><b>What it does measure exactly?</b><br>Measures the real productivity of the factory </p>
	<b>How it is generated?</b><ul><li>Direct result of FSE x PHR and must be displayed in percentage</li></ul>",
	"Factory Productivity Group Target"=>'NA',

    "ITR Definition"=>"<p><b>What it does measure exactly?</b><br> Inventory Turnover Ratio = Net Sales (average 3 months) / Inventory at Cost (raw materials) </p>",
	"ITR Target"=>'NA',

// QUALITY KPI ---->

    "Internal Customer Satisfaction Definition"=>
        "<p><b>What it does measure exactly?</b><br>Measure the SSO satisfaction for this factory</p>
        <b>How it is generated?</b><ul><li>Input by the PSM of one number (0 to 4, 2 digits) for each SSO</li></ul>",
    "Internal Customer Satisfaction Group Target"=>3.5,

	"Warranty Claim Counts Definition"=>
        "<p><b>What it does measure exactly?</b><br>Measures the number of WC for this factory during the month (excluded ER light)</p>
        <b>How it is generated?</b><ul><li>Automatically generated by the Warranty module</li></ul>",

	"Warranty Claim Counts Definition ER light"=>
        "<p><b>What it does measure exactly?</b><br>Measures the number of WC for this factory during the month (only ER light)</p>
        <b>How it is generated?</b><ul><li>Automatically generated by the Warranty module</li></ul>",
	"Warranty Claim Counts Group Target"=>'NA',

	"AVGWC"=>
		"<p><b>What it does measure exactly?</b><br>Measures the average number of WC per Machine per Year.
		(Number of WC not 'SALES CONCESSION' or 'REJECTED') / (SUM of shipped Machines)</p>
		<b>How it is generated?</b><ul><li>Automatically generated by WC and ER module</li></ul>
		<b>Targets ER Types</b><table border=1>
								   <tr><td>Conventional Aircraft Tractors	</td><td> 0.5</td></tr>
								   <tr><td>Towbarless Aircraft Tractors		</td><td> 2</td></tr>
								   <tr><td>Catering Trucks					</td><td> 2</td></tr>
								   <tr><td>Maintenance Platforms			</td><td> 1</td></tr>
								   <tr><td>Trailers and Dollies				</td><td> 0.25</td></tr>
								   <tr><td>Loaders							</td><td> 2</td></tr>
								   <tr><td>Baggage Tractors					</td><td> 0.5</td></tr>
								   <tr><td>Belt Loaders						</td><td> 0.5</td></tr>
								   <tr><td>Passenger Steps					</td><td> 0.5</td></tr>
								   <tr><td>Transporters						</td><td> 2</td></tr>
								   <tr><td>Air Conditioners					</td><td> 0.5</td></tr>
								   <tr><td>Air Starters						</td><td> 0.25</td></tr>
								   <tr><td>Ground Power Units				</td><td> 0.25</td></tr>
								   <tr><td>Lav and Water Trucks				</td><td> 1</td></tr>
								   <tr><td>Military Loaders					</td><td> 2</td></tr>
								   <tr><td>Military Coolers					</td><td> 0.5</td></tr>
								</table>",
	"AVGWC target"=>'0.75',

	"MTBF"=>
	   "<p><b>What it does measure exactly?</b><br>Measures (SUM of NB days between [last day of month] and ER shipped date) / 
	   (Number of WC not 'SALES CONCESSION' or 'REJECTED') for all ER (excluded ER light) that have been shipped 24 months before the last day of the period</p>
        <b>How it is generated?</b><ul><li>Automatically generated by WC and ER module</li></ul>
		<b>Targets ER Types</b><table border=1>
								   <tr><td>Conventional Aircraft Tractors	</td><td> 500</td></tr>
								   <tr><td>Towbarless Aircraft Tractors		</td><td> 250</td></tr>
								   <tr><td>Catering Trucks					</td><td> 200</td></tr>
								   <tr><td>Maintenance Platforms			</td><td> 200</td></tr>
								   <tr><td>Trailers and Dollies				</td><td> 1000</td></tr>
								   <tr><td>Loaders							</td><td> 250</td></tr>
								   <tr><td>Baggage Tractors					</td><td> 500</td></tr>
								   <tr><td>Belt Loaders						</td><td> 500</td></tr>
								   <tr><td>Passenger Steps					</td><td> 500</td></tr>
								   <tr><td>Transporters						</td><td> 300</td></tr>
								   <tr><td>Air Conditioners					</td><td> 500</td></tr>
								   <tr><td>Air Starters						</td><td> 700</td></tr>
								   <tr><td>Ground Power Units				</td><td> 1000</td></tr>
								   <tr><td>Lav and Water Trucks				</td><td> 400</td></tr>
								   <tr><td>Military Loaders					</td><td> 250</td></tr>
								   <tr><td>Military Coolers					</td><td> 500</td></tr>
								</table>",
	"MTBF target"=>"365",
/*
    "AVGTMY"=>
        "<p><b>What it does measure exactly?</b><br>Measures the Average of Time of WC per Machine per Year.
        Takes: (Number of WC not 'SALES CONCESSION' or 'REJECTED') / (SUM of Time between [last day of month] and ER shipped date) / The Number of ER
        for all ER that have been shipped 24 months before the last day of the period</p>
        <b>How it is generated?</b><ul><li>Automatically generated by WC and ER module</li></ul>",
    "AVGTMY target"=>"NA",
*/
	"ATFF"=>
        "<p><b>What it does measure exactly?</b><br>Measures the Average Time (in days) between the Equipment shipping date and its <b>first valid and accepted Warranty Claim</b>.
        It is calculated as: (SUM of days between first valid WC and shipping date) / (Number of Equipments with at least one valid WC)
        for all Equipments (excluding ER light) shipped within 24 months before the end of the period.</p>
        <b>What is excluded?</b><ul>
            <li>Warranty Claims with status 'SALES CONCESSION' or 'REJECTED'.</li>
            <li>Interventions of type 'Commissioning'.</li>
            <li>Equipments with Serial Numbers not starting with 'T' or 'L'.</li>
            <li>'ER Light' machines.</li>
        </ul>
        <b>How it is generated?</b><ul><li>Automatically generated by WC and ER module</li></ul>",
	"ATFF target"=>"None",

    "MNOWC3"=>
        "<p><b>What it does measure exactly?</b><br>Measures (SUM machines WHEN first WC date - ship date > 90 days) / (SUM of shipped machines)
        for all ER (excluded ER light) that have been shipped 24 months before the last day of the period</p>
        <b>How it is generated?</b><ul><li>Automatically generated by WC and ER module</li></ul>",
	"MNOWC3 target"=>"95",

	"PDC Focus Weight Definition"=>
        "<p><b>What it does measure exactly?</b><br>Measure the FW of the factory during that month</p>
        <b>How it is generated?</b><ul><li>Automatically generated by the Support module</li></ul>",
	"PDC Focus Weight Group Target"=>'NA',

	"PDC in Progress Definition"=>
        "<p><b>What it does measure exactly?</b><br>Measures the number of PDC in progress</p>
        <b>How it is generated?</b><ul><li>Automatically generated by the Support module</li></ul>",
    "PDC in Progress Group Target"=>25,

	"PDC Opened During the Month Definition"=>
        "<p><b>What it does measure exactly?</b><br>Measures the number of PDC opened during that month</p>
        <b>How it is generated?</b><ul><li>Automatically generated by the Support module</li></ul>",
	"PDC Opened During the Month Group Target"=>'NA',

    "CRAB Average Per Type For All GT Units Definition"=>
        "<p><b>What it does measure exactly?</b><br>Measures the Average of CRABS on GT Units per Type.
        Takes: (SUM of CRABs by type (Assy, QA, Test) per unit First GT date of given month) / (SUM of units First GT date of given month)</p>
        <b>How it is generated?</b><ul><li>Automatically generated by CRAB and ER module</li></ul>",
    "CRAB Average Per Type For All GT Units Group Target"=>"NA",

    "CRAB Count Per Type For All GT Units Definition"=>
        "<p><b>What it does measure exactly?</b><br>Measures the Count of CRABS on GT Units per Type.
        Takes: SUM of PDI CRABs by type per unit First GT date of given month</p>
        <b>How it is generated?</b><ul><li>Automatically generated by CRAB and ER module</li></ul>",
    "CRAB Count Per Type For All GT Units Group Target"=>"NA",

    "VWC Supplier Recovery Cost Past 12 Months"=>
        "<p><b>What it does measure exactly?</b><br>Sum of the supplier recovery cost.</p>
        <b>How it is generated?</b><ul><li>Automatically generated by VWC module</li></ul>",
    "VWC Supplier Recovery Cost Group Target"=>"NA",

    "NCR Opened/Closed Past 12 Months"=>
    "<p><b>What it does measure exactly?</b><br>Sum of the opened NCRs versus the sum of the closed NCRs.</p>
        <b>How it is generated?</b><ul><li>Automatically generated by NCR module</li></ul>",
            "NCR Opened/Closed Target"=>"NA",

	"NCRResponsibilityPast12Months"=>
		"<p><b>What it does measure exactly?</b><br>Sum of the NCRs Responsibility Figures.</p>
        <b>How it is generated?</b><ul><li>Automatically generated by NCR module</li></ul>",
	"NCR Responsibility Target"=>"NA",

	"NCRProcessPast12Months"=>
		"<p><b>What it does measure exactly?</b><br>Sum of the NCRs Process Figures.</p>
        <b>How it is generated?</b><ul><li>Automatically generated by NCR module</li></ul>",
	"NCR Responsibility Target"=>"NA",

// ENGINEERING KPI ---->

    "EAP Qty per month for Previous 12 Months Definition"=>
    	"<p><b>What it does measure exactly?</b><br>Measures the number of EAP remaining during that month</p>
		<b>How it is generated?</b><ul><li>Automatically generated from EAP module</li>
		<li>SUM of EAP, not REJECTED, still remaining for the specified month</li></ul>",
    "EAP Qty per month Group Target"=>"200",

	"EAP Qty CLOSED per month for Previous 12 Months Definition"=>
		"<p><b>What it does measure exactly?</b><br>Measures the number of PDC closed during that month</p>
		<b>How it is generated?</b><ul><li>Automatically generated from EAP module</li></ul>",
    "EAP Qty CLOSED per month Group Target"=>"100",

	"EAP Qty CLOSED per month by Type for Previous 12 Months Definition"=>
		"<p><b>What it does measure exactly?</b><br>Measures the number of EAP closed during that month by model type</p>
		<b>How it is generated?</b><ul><li>Automatically generated from EAP module</li></ul>",

	"EAP In Progress Late Report"=>"<p><b>What it does measure exactly?</b><br>It provides the quantity of EAP under In Progress status by Model Type, according to the duration since EAP has been submit</p>
		<b>How it is generated?</b><ul><li>Automatically generated from EAP module</li><p>
		<li>Sum of EAP, in PENDING, sort by duration </li></ul>",

    "EAP % CLOSED In 72 Hours for Previous 12 Months Definition"=>
        "<p><b>What it does measure exactly?</b><br>Measures the percent of EAPs CLOSED in 72 hours from time opened.
        Takes: (SUM of EAPs CLOSED / SUM of EAPs OPENED) * 100</p>
        <b>How it is generated?</b><ul><li>Automatically generated by EAP module</li></ul>",
    "EAP % CLOSED In 72 Hours Group Target"=>"15",

    "EAP Average Number Of Days To Close for Previous 12 Months Definition"=>
        "<p><b>What it does measure exactly?</b><br>Measures the Average Number Of Days To Close An EAP.
        Takes: Sum of days to close the EAP for all the EAP closed this month / number of EAP closed this month</p>
        <b>How it is generated?</b><ul><li>Automatically generated by EAP module</li></ul>",
    "EAP Average Number Of Days To Close Group Target"=>"90",

    "EAP Average Number Of Days Open for Previous 12 Months Definition"=>
    "<p><b>What it does measure exactly?</b><br>Measures the Average Number Of Days An EAP is Open.
        Takes: Sum of opened days of all EAP at the given month [Looking at full EAP backlog] / number of EAP still opened at the given month</p>
        <b>How it is generated?</b><ul><li>Automatically generated by EAP module</li></ul>",
    "EAP Average Number Of Days Open Group Target"=>"NA",

	"DMS Monthly average days in revision past 12 month"=>"<p><b>For all exisiting DMS of the considered BU In Revision, for the month considered, make an average of the days accumulated in between 
		</b><br> 
		<ul><li>If last status in Revision (last day of the considered month minus last revision date)</li>
		<li>for all other status: nothing is measured the purpose of the KPI</li></ul>",
	"Monthly average days in revision past 12 month target"=>"NA",

	"DMS Monthly expired past 12 month"=>"<p><b>Increase count by 1 if DMS :</b><br>
		<ul><li>Has reached expired status during that period (and expired is still end of that month status) or</li>
			<li>The last known status is expired (ie not Active, Revision, approval, Archive) even if reached during previous period</li></ul>",
	"Monthly expired past 12 month target"=>"NA",


);


?>
