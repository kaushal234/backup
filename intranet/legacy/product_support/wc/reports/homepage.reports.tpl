<h3>Warranty Reports</h3>

<h4>Lists</h4>

<ol>
	<li><a href="{$smarty.server.SCRIPT_NAME}?m[0]=wc&m[1]=listing&m[2]=byCurrentUser">Show Warranties I opened</a></li>
	<li><a href="{$smarty.server.SCRIPT_NAME}?m[0]=wc&m[1]=listing&m[2]=byUnreturnedParts">Show Parts Warranties Unreturned by SSO</a></li>
	<li><a href="{$smarty.server.SCRIPT_NAME}?m[0]=wc&m[1]=listing&m[2]=byCustomer">Show Warranties by Customer</a></li>
	<li><a href="{$smarty.server.SCRIPT_NAME}?m[0]=wc&m[1]=reports&m[2]=wcByFactoryPeriod">WC by Factory and Period</a></li>
	<li><a href="{$smarty.server.SCRIPT_NAME}?m[0]=wc&m[1]=listing&m[2]=bySignedDate">WC by Signed Date Period</a></li>
	<li><a href="{$smarty.server.SCRIPT_NAME}?m[0]=wc&m[1]=reports&m[2]=WCPartsValue">WC Parts Calculation by SSO, Period</a></li>
	<li><a href="{$smarty.server.SCRIPT_NAME}?m[0]=wc&m[1]=reports&m[2]=WCParts">WC Parts by SSO, Period</a></li>
	<li><a href="{$smarty.server.SCRIPT_NAME}?m[0]=wc&m[1]=reports&m[2]=WCStatus">Warranty Status Report</a></li>
	<li><a href="{$smarty.server.SCRIPT_NAME}?m[0]=wc&m[1]=listing&m[2]=returnedPartsByFactory">Parts Warranties Returned by factory</a></li>
	<li><a href="{$smarty.server.SCRIPT_NAME}?m[0]=wc&m[1]=reports&m[2]=OpenTaskByFactory">Open WC Task by Factory</a></li>
	<li><a href="{$smarty.server.SCRIPT_NAME}?m[0]=wc&m[1]=listing&m[2]=WCSumBySubParts">WC Count per PN and its sub-parts</a></li>
	<li><a href="{$smarty.server.SCRIPT_NAME}?m[0]=wc&m[1]=listing&m[2]=WCFiltering">WC Filtering Report</a></li>
	<li><a href="{$smarty.server.SCRIPT_NAME}?m[0]=wc&m[1]=reports&m[2]=WCPNPareto">WC PN pareto filtering report</a></li>
	<li><a href="{$smarty.server.SCRIPT_NAME}?m[0]=wc&m[1]=reports&m[2]=WCProcessTime">Time to process WC after creation</a></li>
</ol>

<h4>Matrix</h4>

<ol>
	<li><a href="{$smarty.server.SCRIPT_NAME}?m[0]=wc&m[1]=matrix&m[2]=byFactoryStatus">Count By Status and Manufacturer Location</a></li>
	<li><a href="{$smarty.server.SCRIPT_NAME}?m[0]=wc&m[1]=matrix&m[2]=bySSOStatus">Count By Status and SSO</a></li>
	<li><a href="{$smarty.server.SCRIPT_NAME}?m[0]=wc&m[1]=matrix&m[2]=bySalesOrgYear">Count By Sales Org and Claim Year</a></li>
</ol>

<h4>Charts</h4>

<ol>
	<li>WC Stats by Factory(Move to MFG/KPI NEW VERSION)</li>
</ol>

<h4>KPI details</h4>

<ul>
	<li>Average number of WC per machine by BU by Period</li>
	<li><a href="{$smarty.server.SCRIPT_NAME}?m[0]=wc&m[1]=listing&m[2]=MTBFbyBuByPeriodNumerator">Mean time between Failures by BU by Period - Numerator</a></li>
	<li><a href="{$smarty.server.SCRIPT_NAME}?m[0]=wc&m[1]=listing&m[2]=MTBFbyBuByPeriod">Mean time between Failures by BU by Period - Denominator</a></li>
	<li><a href="{$smarty.server.SCRIPT_NAME}?m[0]=wc&m[1]=listing&m[2]=ATFFbyBuByPeriod">Average Time at First Failure by BU by Period</a></li>
	<li>Percent of Machines with NO WC in the first 3 months</li>
</ul>

<h4>Downloads</h4>

<ol>
	<li><a href="{$smarty.server.SCRIPT_NAME}?m[0]=wc&m[1]=csv&m[2]=countGroupByPart">Download COUNT part_number GROUP BY count SORT BY part_number by Period</a></li>
	<li><a href="{$smarty.server.SCRIPT_NAME}?m[0]=wc&m[1]=csv&m[2]=claimedParts">Download part_number SORT BY part_number</a></li>
	<li><a href="{$smarty.server.SCRIPT_NAME}?m[0]=wc&m[1]=csv&m[2]=returnPartStatus">Download warranty return part status SORT BY warranty id</a></li>
	<li><a href="{$smarty.server.SCRIPT_NAME}?m[0]=wc&m[1]=csv&m[2]=partsByFactoryPeriod">Download Warranty Parts BY factory and period</a></li>
	<li><a href="{$smarty.server.SCRIPT_NAME}?m[0]=wc&m[1]=csv&m[2]=erShippedLast2yearsByFactoryPeriod">Download WC and ER by Factory by Period (past 2 year shipped ER - wc kpi)</a></li>
</ol>
