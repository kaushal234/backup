{if $smarty.request.m[1]=='search'}
<h3>Search Tools</h3>
<ul>
	<li><a href="{$smarty.server.SCRIPT_NAME}?m[0]=reports&m[1]=listing&m[2]=SearchItem">Search tool: by item</a></li>
</ul>
<ul>
	<li><a href="{$smarty.server.SCRIPT_NAME}?m[0]=reports&m[1]=listing&m[2]=SearchWO">Search tool: where used (in work orders)</a></li>
</ul>
<ul>
	<li><a href="{$smarty.server.SCRIPT_NAME}?m[0]=reports&m[1]=listing&m[2]=SearchOutbound">Search tool: where used (in outbounded items)</a></li>
</ul>
<ul>
	<li><a href="{$smarty.server.SCRIPT_NAME}?m[0]=reports&m[1]=listing&m[2]=SearchBOMMono">Search tool: where used (in monolevel BOM)</a></li>
</ul>
<ul>
	<li><a href="{$smarty.server.SCRIPT_NAME}?m[0]=reports&m[1]=listing&m[2]=SearchBOMMulti">Search tool: where used (in multilevel BOM)</a></li>
</ul>
<ul>
	<li><a href="{$smarty.server.SCRIPT_NAME}?m[0]=reports&m[1]=listing&m[2]=SearchMagic">Search tool: Magic search on Item, Description and Xref</a></li>
</ul>
{else}
<h3>Engineering Reports</h3>
<h4>Columnar Reports</h4>
<ul>
	<li><a href="{$smarty.server.SCRIPT_NAME}?m[0]=reports&m[1]=listing&m[2]=BoxesReport">Boxes reports</a></li>
</ul>
<ul>
	<li><a href="{$smarty.server.SCRIPT_NAME}?m[0]=reports&m[1]=listing&m[2]=EdmItmRevi">Item revision discrepancies between EDM & ITM</a></li>
</ul>
<ul>
	<li><a href="{$smarty.server.SCRIPT_NAME}?m[0]=reports&m[1]=listing&m[2]=ItmWODrawing">Item list with missing drawing</a></li>
</ul>
<ul>
    <li><a href="{$smarty.server.SCRIPT_NAME}?m[0]=reports&m[1]=listing&m[2]=MeapTaskList">MEAP Tasks List</a></li>
</ul>
<h4>Other</h4>
<ul>
	<li><a href="file://///bakura/interdept/desc_40_EUREKA/desi40.pdf">EUREKA ITEM MASTER REFERENCE</a></li>
</ul>
{/if}
