<h3>{$title}</h3>
{if count($header)}
	<table width="100%">
	<tr bgcolor="#D0D0D0">
		<th>ID#</th>
		<th>Parent</th>
		<th>Date</th>
		<th>Qty</th>
		<th>Type<br>
		Description</th>
		<th>Serial<br>
		Man. SN</th>
	</tr>
		<tr bgcolor="{cycle values="#eeeeee,#d0d0d0"}">
			<td>{$header.id}</td>
			<td>{$header.parent_id}</td>
			<td>{$header.date}</td>
			<td>{$header.qty}</td>
			<td>{$header.type}<br>
			{$header.description}</td>
			<td>{$header.serial}<br>
			{$header.man_serial}</td>
		</tr>
	</table>
{/if}
{if count($rows)}
	<h4>Child Records</h4>
	<table width="100%">
	<tr bgcolor="#D0D0D0">
		<th>ID#</th>
		<th>Parent</th>
		<th>Date</th>
		<th>Qty</th>
		<th>Type<br>
		Description</th>
		<th>Serial<br>
		Man. SN</th>
		<th>&nbsp;</th>
	</tr>
	{foreach name=lines item=row from=$rows}
		<tr bgcolor="{cycle values="#eeeeee,#d0d0d0"}">
			<td><a href="{$smarty.server.SCRIPT_NAME}?m[0]=inv&m[1]=browse&id={$row.id}">{$row.id}</a></td>
			<td>{$row.parent_id}</td>
			<td>{$row.date}</td>
			<td>{$row.qty}</td>
			<td><b>{$row.type}</b><br>
			{$row.description}</td>
			<td>{$row.serial}<br>
			{$row.man_serial}</td>
			<td><a href="{$smarty.server.SCRIPT_NAME}?m[0]=inv&m[1]=reports&m[2]=tree&id={$row.id}">Inv Tree</a></td>
		</tr>
	{/foreach}
	</table>
{else}
	<p class="alert">No related rows found.</p>
{/if}