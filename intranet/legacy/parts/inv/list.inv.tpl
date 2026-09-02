
{if count($parts)==0}
	<h2>No results found in ERP...</h2>
{else}
	<p class="alert">WARNING: Pls contact the originating location for parts with ZERO stock or pricing discrepancy.</p>
	<table border="1" cellspacing="0" cellpadding="1" class="smalltext" bordercolor="#FFFFFF" width="100%">
	<tr bgcolor="#D0D0D0">
		<th>Item</th>
		<th>ERP Location<br>
		Whse Code</th>
		<th>PN<br>
		Description</th>
		<th>Lead<br>Wt</th>
		<th>Avail=OH-AL<br>
		OH/OO/AL</th>
		{if $options.showPricing<>""}
			<th>MIP<br>Std Cost</th>
		{/if}
		<th>EDM</th>
		<th>BOM</th>
		{if $showBUY==true}
			<th>Buy</th>
		{/if}
	</tr>
	{assign var=i value=1}
	{foreach name=outer key=key item=part from=$parts}
	<tr bgcolor="{cycle values="#eeeeee,#d0d0d0"}">
		<td>{$i}
		{assign var=i value=$i+1}</td>
		<td title=""><b>{$part.ERP}</b><br>
		{$part.cwar_fullname} ({$part.t_cwar})</td>
		<td><b>{$part.ITEM}</b><br>
		{$part.DESCRIPTION}
		{if $part.FR<>""}
		/<i>{$part.FR}</i>
		{/if}
		</td>
		<td>{$part.LEAD}<br>
		{$part.WT}
		</td>
		<td><b>{if $part.stoc-$part.allo < 0}0{else}{$part.stoc-$part.allo}{/if}</b><br>
		{$part.stoc}/{$part.ordr}/{$part.allo}</td>
		{if $options.showPricing<>""}
		<td><b>{$part.CURRENCY}&nbsp;{$part.MIP}/{$part.UM}</b><br>
		{$part.STDCUR}
		&nbsp;{$part.STDCOST}/{$part.UM}</td>
		{/if}
		<td><a href="/en/private/manufacturing/eng/dev.php?m[0]=edm&m[1]=view&erp={$part.ERP}&item={$part.ITEM}">EDM</a></td>
		<td><a href="/en/private/manufacturing/eng/dev.php?m[0]=bom&m[1]=view&erp={$part.ERP}&pn={$part.ITEM}">BOM</a></td>
		{if $showBUY==true}
		<td><a href="{$smarty.server.SCRIPT_NAME}?m[0]=cart&m[1]=addItem&id={$part.ITEM}">Buy</a></td>
		{/if}
	</tr>
	{/foreach}
	</table>
{/if}
