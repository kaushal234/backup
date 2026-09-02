	<h3>{$title|default:"Breakdown PER UNIT"}</h3>
	<table cellpadding="3">
		<tr bgcolor="#DDDDDD">
			<th>#</th>
			<th>Category</th>
			<th>Description</th>
			<th colspan="2"><div align="center">Cost</div></th>
			<th colspan="2"><div align="center">Sales Price</div></th>
		{if !empty($options.edit)}
			<th><div align="center">Edit</div></th>{/if}
		{if !empty($options.delete)}
			<th><div align="center">Delete</div></th>{/if}
		</tr>

	{foreach name=lines key=key item=line from=$lines}
		<tr bgcolor="#eeeeee">
		<td>{$smarty.foreach.lines.iteration}</td>
		<td>{$line.caty}</td>
		<td>{$line.dsca}</td>
		<td>{if empty($line.pric) OR $line.pric==0}
			{else}<b>{$line.pric_cur}</b>&nbsp;{$line.pric}{/if}</td>
		<td bgcolor="#CCFF00">{if empty($line.pric_in_dcur) OR $line.pric_in_dcur==0}
			{else}<b>{$line.dcur}</b>&nbsp;{$line.pric_in_dcur}{/if}</td>
		<td>{if empty($line.pris) OR $line.pris==0}&nbsp;
			{else}<b>{$line.pris_cur}</b>&nbsp;{$line.pris}{/if}</td>
		<td bgcolor="#CCFF00">{if empty($line.pris_in_dcur) OR $line.pris_in_dcur==0}&nbsp;
			{else}<b>{$line.dcur}</b>&nbsp;{$line.pris_in_dcur}{/if}</td>
	{if !empty($options.edit)}
		<td><div align="center">
			<a href="/en/private/sales_service/sales.php?m[0]=sol&m[1]=view&m[2]=breakdown&m[3]=FormExternal&action=update&id={$id}&optid={$line.id}{if $options.edit == 'partial'}&partial{/if}" title="Click to edit">
			<img src="/shared/icons/miscellaneous/edit.png" alt="Edit"/></a></div></td>{/if}
	{if !empty($options.delete)}
		<td><div align="center">
			<a href="/en/private/sales_service/sales.php?m[0]=sol&m[1]=view&m[2]=breakdown&m[3]=del&id={$id}&optid={$line.id}" 
			onClick="javascript:return confirm('Are you sure to delete option #{$smarty.foreach.lines.iteration} ?');" 
			title="Click to delete">
			<img src="/shared/icons/miscellaneous/delete.png" alt="Delete"/></div></td>{/if}
		</tr>
	{/foreach}
	<tr>
		<td>&nbsp;</td>
		<td>&nbsp;</td>
		<td>&nbsp;</td>
		<td>&nbsp;</td>
		<td bgcolor="#CCFF00"><b>{$dcur}</b>&nbsp;{$totals.pric_tot_in_dcur}</td>
		<td>&nbsp;</td>
		<td bgcolor="#CCFF00"><b>{$dcur}</b>&nbsp;{$totals.pris_tot_in_dcur}</td>
	{if !empty($options.edit)}
		<td>&nbsp;</td>{/if}
	{if !empty($options.delete)}
		<td>&nbsp;</td>{/if}
	</tr>	
</table>
