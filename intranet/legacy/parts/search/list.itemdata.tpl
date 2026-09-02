{if count($parts)==0}
	<h2>No results found in ERP...</h2>
{else}
	<h2>Results from ERP</h2>
	<form method="post" action="{$smarty.server.SCRTIP_NAME}?m[0]=cart&m[1]=addItems">
	<table border="1" cellspacing="0" cellpadding="1" class="smalltext" bordercolor="#FFFFFF" width="100%">
	<tr bgcolor="#D0D0D0">
		<th>Item</th>
		<th>ERP</th>
		<th>PN<br>
		Description</th>
		{if $options.showPricing<>""}
		<th>MIP<br>
		STDCOST</th>
		{/if}
		<th>WT</th>
		<th>Last Pur</th>
		<th>Lead</th>
		<th>&nbsp;</th>
	</tr>
	{foreach name=outer key=key item=part from=$parts}
	<tr bgcolor="{cycle values="#eeeeee,#d0d0d0"}">
		<td>{$key+1}</td>
		<td>{$part.ERP}<br>
		<td>{$part.ITEM}<br>
		{$part.DESCRIPTION|nl2br}</td>
		{if $options.showPricing<>""}
		<td>{$part.CURRENCY}&nbsp;{$part.MIP}<br>
		{$part.CURRENCY}&nbsp;{$part.STDCOST}</td>
		{/if}
		<td>{$part.WT}</td>
		<td>{$part.LSTDT}</td>
		<td>{$part.LEAD}</td>
		<td><input type="text" name="qty[{$key}]" size="3"></td>
	</tr>
	{/foreach}
	</table>
	<input type="submit" name="submit" value="Submit">
	</form>
{/if}
