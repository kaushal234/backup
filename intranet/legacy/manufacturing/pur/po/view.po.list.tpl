<h3>Purchase Orders</h3>
{if count($poNums) > 0}
	<table border="1">
	<tr class="table_title"><td>Purchase Order Number</td></tr>
	{foreach name=poList item=poNum from=$poNums}
		<tr><td><a href="{$smarty.server.SCRIPT_NAME}?m[0]=po&m[1]=view&id={$poNum.orno}">{$poNum.orno}</a></td>
		</tr>
	{/foreach}
	</table>
{else}
	<p class="alert">No purchase orders found</p>
{/if}