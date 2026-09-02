<table>
<tr class="table_title"><td>Item#</td>
<td>Part#</td><td>Order Qty</td>
<td>Description</td><td>UM</td>
<td>Del Date</td>
</tr>
{foreach name=lines item=line from=$detail}
	<tr><td>{$line.pono}</td><td>{$line.item}</td><td>{$line.oqua}</td>
	<td>{$line.dsca}</td><td>{$line.cuqp}</td>
	<td>{$line.ddat}</td>
{/foreach}
</table>