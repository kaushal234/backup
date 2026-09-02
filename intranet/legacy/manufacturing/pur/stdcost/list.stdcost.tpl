<h3>Standard Costs results</h3>

<table width="100%">
	<tr bgcolor="#D0D0D0">
		<td>ERP</td>
		<td>Part Number<br>Description</td>
		<td>Price</td>
		<td>Availability</td>
	</tr>
	{foreach name=lines item=part from=$parts}
	<tr bgcolor="{cycle values="#eeeeee,#d0d0d0"}">
		<td>{$part.ERP}</td>
		<td>{$part.ITEM}<br>{$part.DESCRIPTION}</td>
		<td>MIP:&nbsp;{$part.CURRENCY}&nbsp;{$part.MIP}<br>
		Std Cost:&nbsp;{$part.STDCCUR}&nbsp;{$part.STDCOST}</td>
		<td><a href="/en/private/manufacturing/eng/dev.php?m[0]=avail&m[1]=view&erp={$part.ERP}&item={$part.ITEM}">Check</a></td>
	</tr>
	{/foreach}
</table>