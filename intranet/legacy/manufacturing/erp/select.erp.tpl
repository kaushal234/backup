<h3>Select ERP</h3>
<ul>
{foreach name=lines key=erp item=location from=$locations}
	<li><a href="{$NEXT_STEP}{$erp}">{$location}</a></li>
{/foreach}
</ul>