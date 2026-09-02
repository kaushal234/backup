	<td>{$lineItem.item|default:"&nbsp;"}</td>
	<td>{$lineItem.pn|default:"&nbsp;"}
	{if $lineItem.vendor_pn <> ""}
		<br><b>Vendor PN:</b>&nbsp;{$lineItem.vendor_pn}
	{/if}
	{if $lineItem.ocm_pn <> ""}
		<br><b>OCM PN:</b>&nbsp;{$lineItem.ocm_pn}
	{/if}
	</td>
	<td>{$lineItem.qty|default:"&nbsp;"}</td>
	<td>{$lineItem.um|default:"&nbsp;"}</td>
	<td>{$lineItem.en|default:"&nbsp;"}
	{if $lineItem.fr <> ""}
		<br><b>Alt Lang:</b>&nbsp;{$lineItem.fr}
	{/if}
	{if $lineItem.note <> ""}
		<br><b>Note:</b>&nbsp;{$lineItem.note}
	{/if}
	</td>
	<td>
	{if $lineItem.pn<>""}
		<a href="/en/private/parts/parts.php?m[0]=inv&m[1]=view&id={$lineItem.pn}" title="View Part Dashboard">Dash</a>
	{else}
		&nbsp;
	{/if}
	</td>