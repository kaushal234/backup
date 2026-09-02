<h3>Reports</h3>

<p>Welcome to the Manufacturing Reports section</p>

<h2>Columnar Reports</h2>

<ul>
	<li><a href="/en/private/calendar/calendar.php?m[0]=tasks&m[1]=listing&m[2]=byTplStatus&x=ALL&y=mfg.pur.slow_moving_inventory">Slow moving inventory sequences</a></li>
	<li><a href="/en/private/parts/parts.php?m[0]=reports&amp;m[1]=listing&amp;m[2]=InventoryForecastTool2">Inventory Forecast Tool</a></li>
	<li><a href="{$smarty.server.SCRIPT_NAME}?m[0]=reports&m[1]=PurItemsToControl">Purchased Items to Control</a></li>
	<li><a href="{$smarty.server.SCRIPT_NAME}?m[0]=reports&m[1]=MRPOrders">MRP Orders</a></li>
	<li><a href="{$smarty.server.SCRIPT_NAME}?m[0]=mrp&m[1]=listing&m[2]=byLate">Late MRP by Factory</a></li>
	<li><a href="{$smarty.server.SCRIPT_NAME}?m[0]=reports&m[1]=CriticalcomponentsStatus">Critical components status</a></li>
	<li><a href="{$smarty.server.SCRIPT_NAME}?m[0]=reports&m[1]=PipoItems">Phase In Phase Out follow up</a></li>
	<li><a href="{$smarty.server.SCRIPT_NAME}?m[0]=reports&m[1]=benchmark&m[2]=supplier">Benchmark Supplier</a></li>
	<li><a href="{$smarty.server.SCRIPT_NAME}?m[0]=reports&m[1]=ReceiptLabels">Receipt labels for suppliers</a></li>
	<li><a href="{$smarty.server.SCRIPT_NAME}?m[0]=reports&m[1]=POSummary&m[2]=byERP">PO Summary By Supplier By Period By Buyer By ERP</a></li>
	<li><a href="{$smarty.server.SCRIPT_NAME}?m[0]=reports&m[1]=ItemIssue">Item Issue by Period</a></li>
	<li><a href="{$smarty.server.SCRIPT_NAME}?m[0]=reports&m[1]=ItemBySupplierByBU">Item By Supplier By ERP</a></li>
	<li><a href="{$smarty.server.SCRIPT_NAME}?m[0]=reports&m[1]=PNNotReceived">Open Purchase Orders with 0 Standard Cost</a></li>
    <li><a href="{$smarty.server.SCRIPT_NAME}?m[0]=reports&m[1]=ExportItemData">Full Item data exportation file with issued in the past</a></li>
	<li><a href="{$smarty.server.SCRIPT_NAME}?m[0]=reports&m[1]=vendorinformation">Suppliers information extraction from BAAN ttccom2101m00</a></li>
</ul>
<h2>Inventory reports</h2>
<h3>Inventory Turnover Ratio Charts</h3>
<ul>
    <li><a href="{$smarty.server.SCRIPT_NAME}?m[0]=reports&m[1]=ITR&m[2]=byBUID">Inventory Turnover Ratio By Company</a></li>
</ul>
<h3>Inventory Charts</h3>
<ul>
    <li><a href="{$smarty.server.SCRIPT_NAME}?m[0]=reports&m[1]=inventorywipraw&m[2]=byBUID">Inventory Value By Company</a></li>
</ul>
<h2>Supplier reports</h2>

<h3>Supplier activities</h3>

<ul>
	<li><a href="{$smarty.server.SCRIPT_NAME}?m[0]=reports&m[1]=activityByYearByBU&m[2]=byERP">Supplier Activity summary by year by BU (from receipt table tdpur045)</a></li>
	<li><a href="{$smarty.server.SCRIPT_NAME}?m[0]=reports&m[1]=supplier&m[2]=revenue&m[3]=byERP">Supplier Revenues by BU (from invoice table tdpur046)</a></li>
</ul>

<h3>Vendor OTDP Charts</h3>

<ul>
	<li><a href="{$smarty.server.SCRIPT_NAME}?m[0]=reports&m[1]=vendorOTDP&m[2]=byBUID">Vendor ODTP by Company</a></li>
	<li><a href="{$smarty.server.SCRIPT_NAME}?m[0]=reports&m[1]=vendorOTDP&m[2]=bySuno">Vendor ODTP by Vendor</a></li>
	<li><a href="{$smarty.server.SCRIPT_NAME}?m[0]=reports&m[1]=vendorOTDP&m[2]=byPart">Vendor ODTP by Part</a></li>
	<li><a href="{$smarty.server.SCRIPT_NAME}?m[0]=reports&m[1]=vendorOTDP&m[2]=byBuyer">Vendor ODTP by Buyer</a></li>
	<li><a href="{$smarty.server.SCRIPT_NAME}?m[0]=reports&m[1]=OTDPbyVBC">OTDP by vendor/buyer/company</a></li>
</ul>

<h2>PO Confirm Charts</h2>

<ul>
	<li><a href="{$smarty.server.SCRIPT_NAME}?m[0]=reports&m[1]=vendorPOConfirm&m[2]=byBUID">PO Confirm by Company</a></li>
	<li><a href="{$smarty.server.SCRIPT_NAME}?m[0]=reports&m[1]=vendorPOConfirm&m[2]=bySunoBuyer">PO Confirm by Vendor / Buyer</a></li>
</ul>

<h2>AERO Report</h2>

<ul>
	<li><a href="{$smarty.server.SCRIPT_NAME}?m[0]=reports&m[1]=aeroShippingReport">AERO Shipping Report</a></li>
	<li><a href="{$smarty.server.SCRIPT_NAME}?m[0]=reports&m[1]=aeroPurReport">AERO Purchasing Report</a></li>
	<li><a href="{$smarty.server.SCRIPT_NAME}?m[0]=reports&m[1]=aeroBOMReport">AERO Engineering Report</a></li>
</ul>
