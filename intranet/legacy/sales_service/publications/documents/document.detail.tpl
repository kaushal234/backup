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
		<br><b>French:</b>&nbsp;{$lineItem.fr}
	{/if}
	{if $lineItem.note <> ""}
		<br><b>Note:</b>&nbsp;{$lineItem.note}
	{/if}
	</td>
	<td>
	{if $lineItem.item<>""}
		<a href="/en/private/sales_service/parts/dev.php?m[0]=inv&m[1]=byPN&id={$lineItem.pn}&docid={$header.id}">Buy</a>
	{else}
		&nbsp;
	{/if}
	</td>