<h3>Report: All open PO line items</h3>
<p>Legend<br>
<img src="/shared/bluesphere/16x16/actions/idea.png"> Possible drawing change since PO issue. Pls contact your TLD Rep<br>
<img src="/shared/bluesphere/16x16/actions/cancel.png"> This line item is late</p>
<table>
<tr>
{foreach name=titles key=field item=caption from=$fields}
	<td><a href="{$smarty.server.SCRIPT_NAME}?m[0]=report&m[1]=po&field={$field}&order=
	{if $order=="ASC"}
		DESC
	{else}
		ASC
	{/if}
	">{$caption}</a></td>
{/foreach}
<td>&nbsp;</td>
</tr>

{foreach name=lines item=line from=$pos}
	<tr>
	{foreach name=titles key=field item=caption from=$fields}
		<td>
		{if $field=="orno"}
			<a href="{$smarty.server.SCRIPT_NAME}?m[0]=po&m[1]=view&id={$line.$field}">{$line.$field}</a>
		{elseif $field=="item"}
			<a href="{$smarty.server.SCRIPT_NAME}?m[0]=bom&m[1]=multi&item={$line.$field}&odat={$line.odat}">{$line.$field}</a>
		{else}
			{$line.$field}
		{/if}
		</td>
	{/foreach}
	<td>
	{if $line.bexdt<>"0000-00-00"}
		<img src="/shared/bluesphere/16x16/actions/idea.png" alt="Possible drawing change since PO issue. Pls contact your TLD Rep">
	{/if}
	{if $line.late==1}
		<img src="/shared/bluesphere/16x16/actions/cancel.png" alt="This line item is late">
	{/if}
	</td>	
	</tr>
{/foreach}
</table>