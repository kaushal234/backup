<p>
<a href="/en/private/manufacturing/pur/dev.php?m[0]=vwc&m[1]=transfer&id={$header.id}">Transfer to VWC</a>
</p>
<h3>Warranty Claim {$header.id}</h3>
<table>
<tr><th>Status</th><td>{$header.warranty_status}</td></tr>
<tr><th>Claim Date</th><td>{$header.claim_date}</td></tr>
<tr><th>Entered by</th><td>{$header.entered_by}</td></tr>
<tr><th>Location</th><td>{$header.equipment_location}</td></tr>
<tr><th>Hours</th><td>{$header.hours}</td></tr>
<tr><th>Problem Description</th><td>{$header.problem_desc}</td></tr>
<tr><th>Service Comments</th><td>{$header.service_comments}</td></tr>

{if $detail[0]<>""}
	<table>
	<tr class="table_title">
		<th>PN</th>
		<th>Desc</th>
		<th>Brand</th>
		<th>Qty</th>
		<th>UM</th>
	</tr>
	{foreach name=lines item=line from=$detail}
		<tr>
		<td>{$line.part_number}</td>
		<td>{$line.part_description}</td>
		<td>{$line.brand}</td>
		<td>{$line.quantity}</td>
		<td>{$line.um}</td>
	{/foreach}
	</table>
{/if}