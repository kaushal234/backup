<h3>Revisions Detail for PN#{$part_number}</h3>

<table width="100%" border="1" cellspacing="0" cellpadding="1" class="smalltext" bordercolor="#FFFFFF">
<tr>
	<th>Part Number</th>
	<th>Revision</th>
	<th>Start Date</th>
	<th>End Date</th>
	<th>Download</th>
</tr>

{foreach item=revision from=$revisions_detail}
<tr bgcolor="{cycle values="#eeeeee,#d0d0d0"}">
	<td>{$part_number}</td>
	<td>{$revision.revision}</td>
	<td>{$revision.effectiveDate|date_format:"Y-m-d"}</td>
	{if $revision.expiryDate === "1970-01-01T00:00:00Z"}
		<td>Current Revision</td>
	{else}
		<td>{$revision.expiryDate|date_format:"Y-m-d"}</td>
	{/if}
	<td>
		<a target="_blank" href="{$php_self}?m[0]=getfile&m[1]=drawing&erp={$erp}&item={$part_number}&date={$revision.effectiveDate}"><?= _("Download") ?>Download</a>
	</td>
</tr>
{/foreach}
</table>
