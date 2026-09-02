<table cellpadding="3">
	{foreach name=levels from=$data key=priority item=group}
		<tr>
			<td colspan="4">
				<h2>
				{if $priority==1}
				TOP PRIORITY
				{elseif $priority==2}
				ALL GSE
				{else}
				NON GSE
				{/if}
				</h2>
			</td>
		</tr>
		{foreach name=families from=$group key=family item=rows}
		<tr>
			<td colspan="4">
				<h3>{$family|escape:'html'}</h3>
			</td>
		</tr>
		<tr>
			<td style="text-align:center;font-weight:bold;background-color:#2971a8;color:white;">Status</td>
			<td style="text-align:center;font-weight:bold;background-color:#2971a8;color:white;">Location</td>
			<td style="font-weight:bold;background-color:#2971a8;color:white;">Unit Number</td>
			<td style="font-weight:bold;background-color:#2971a8;color:white;">Comment</td>
		</tr>
			{foreach name=ers from=$rows item=er}
			<tr style="background-color:{cycle values="#eeeeee,#d0d0d0"};">
				<td style="text-align:center;font-weight:bold;
				{if $er.transaction_status=='FMC'}background-color:green;color:white;
				{elseif $er.transaction_status=='NMC'}background-color:red;color:white;
				{elseif $er.transaction_status=='PMC'}background-color:yellow;
				{else}background-color:#ccc:{/if}">{$er.transaction_status|escape:'html'}</td>
				<td style="text-align:center;">{$er.location_short|escape:'html'}</td>
				<td>
					{if is_array($options) && array_key_exists('er_link', $options) && $options.er_link}
					<a href="{$options.er_link}{$er.id}">{$er.cust_asset_num|escape:'html'}</a>
					{else}
					{$er.cust_asset_num|escape:'html'}
					{/if}
				</td>
				<td>{$er.transaction_last_log|escape:'html'}</td>
			</tr>
			{/foreach}
		{/foreach}
		{if !$smarty.foreach.levels.last}
		<tr>
			<td colspan="4">
				&nbsp;
			</td>
		</tr>
		{/if}
	{/foreach}
</table>
