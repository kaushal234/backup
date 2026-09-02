<h3>Request For Quotations</h3>
{if count($rfqNums )> 0}
	<table border="1">
	<tr class="table_title"><td>RFQ Number</td></tr>
	{foreach name=poList item=rfqNum from=$rfqNums}
		<tr><td><a href="{$smarty.server.SCRIPT_NAME}?m[0]=rfq&m[1]=view&erp={$erp}&id={$rfqNum.qono}">{$rfqNum.qono}</a></td>
		</tr>
	{/foreach}
	</table>
{else}
	<p class="alert">No request for quotations found</p>
{/if}