<h3>Reports</h3>

<h4>Customer Cleanup</h4>

<ul>
	<li><a href="{$smarty.server.SCRIPT_NAME}?m[0]=equipment&m[1]=customerCleanUp&m[2]=matrix">Matrix ER by Factory SSO, without customer Buyer/User</a></li>
</ul>

<h4>Columnar Reports</h4>

<ol>
    <li><a href="{$smarty.server.SCRIPT_NAME}?m[0]=equipment&m[1]=reports&m[2]=byBuByComponents">Equipment with components info by factory</a></li>
    <li><a href="{$smarty.server.SCRIPT_NAME}?m[0]=equipment&m[1]=listing&m[2]=byProblemRecords">Equipment Records with Problems</a></li>
    <li><a href="{$smarty.server.SCRIPT_NAME}?m[0]=equipment&m[1]=listing&m[2]=byUnassigned">Equipment Records not assigned to a Sales Order yet</a></li>
    <li><a href="{$smarty.server.SCRIPT_NAME}?m[0]=equipment&m[1]=listing&m[2]=byUnshipped">Unshipped Equipment</a></li>
    <li><a href="{$smarty.server.SCRIPT_NAME}?m[0]=equipment&m[1]=listing&m[2]=byManualPN">Equipment Records by manual PN#</a></li>
    <li><a href="{$smarty.server.SCRIPT_NAME}?m[0]=equipment&m[1]=listing&m[2]=byState">Equipment Records by State: Retired</a></li>
	<li><a href="{$smarty.server.SCRIPT_NAME}?m[0]=equipment&m[1]=listing&m[2]=marketShareAnalysis">Market Share Analysis</a></li>
	<li><a href="{$smarty.server.SCRIPT_NAME}?m[0]=equipment&m[1]=listing&m[2]=linkFMS">SIM card ER reports</a></li>
	<li><a href="{$smarty.server.SCRIPT_NAME}?m[0]=equipment&m[1]=listing&m[2]=linkFMSSold">Units sold with Link by sales date</a></li>
</ol>

<h4>Multi Level Reports</h4>

<ol>
	<li><a href="{$smarty.server.SCRIPT_NAME}?m[0]=equipment&m[1]=multiLevel&m[2]=shippedWithoutManual">Equipment Shipped Without Online Manual (previous and current year)</a></li>
</ol>

<h4>Matrix Reports</h4>

<ol>
	<li><a href="{$smarty.server.SCRIPT_NAME}?m[0]=equipment&m[1]=3dims">Equipment by 3 dimensions</a> </li>
	<li><a href="{$smarty.server.SCRIPT_NAME}?m[0]=equipment&m[1]=tldLinkBySSO">FMS Equipments by SSO</a> </li>
	<li><a href="{$smarty.server.SCRIPT_NAME}?m[0]=equipment&m[1]=tldLinkByFactory">Link Equipments by Factory</a> </li>
</ol>

<h4>Charts</h4>

<ol>
	<li><a href="{$smarty.server.SCRIPT_NAME}?m[0]=equipment&m[1]=charts&m[2]=equipmentCountByType" target="_blank">Equipment Count by Type</a> </li>
</ol>

<h4>Dashboards</h4>

<ol>
	<li><a href="{$smarty.server.SCRIPT_NAME}?m[0]=equipment&m[1]=dash&m[2]=byFactory">Dashboard by Factory</a> </li>
</ol>
