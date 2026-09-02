<form name="editWCParts" method="post" action="{$nextURL}">
	<table class="sortable" cellpadding="3"">
		<thead>
			<tr>
				<th>ID#</th>
				<th>Track(Y/N)</th>
				<th>Note</th>
				<th>Part Brand</th>
				<th>Part Number</th>
				<th>Part Description</th>
				<th>SN</th>
				<th>Failure Type</th>
				<th>System</th>
				<th>UM</th>
				<th>Qty Shipped</th>
				<th>Qty Returned</th>
				<th>Date Returned</th>
			</tr>
		</thead>
		<tbody>
			{foreach name=wcpart item=wcpart from=$wcpart}
			<tr bgcolor="{cycle values="#eeeeee,#d0d0d0"}">
				<td>{$wcpart.wcpart_id}</td>
				<td><select name="parts[{$wcpart.wcpart_id}][supply_it]">
				<option selected="selected" value="{$wcpart.supply_it}">{$wcpart.supply_it}</option>
				<option value="YES">YES</option>
				<option value="NO">NO</option>
				</select></td>
				<td><input type="text" name="parts[{$wcpart.wcpart_id}][notes]" value="{$wcpart.notes}" "size="9" /></td>
				<td><input type="text" name="parts[{$wcpart.wcpart_id}][brand]" value="{$wcpart.brand}" "size="9" /></td>
				<td><input type="text" name="parts[{$wcpart.wcpart_id}][part_number]" value="{$wcpart.part_number}" size="9" /></td>
				<td><input type="text" name="parts[{$wcpart.wcpart_id}][part_description]" value="{$wcpart.part_description}" size="10" /></td>
				<td><input type="text" name="parts[{$wcpart.wcpart_id}][sn]" value="{$wcpart.sn}" size="9" /></td>
				<td><select name="parts[{$wcpart.wcpart_id}][failure_type]">
				{foreach from=$failureTypeList key=key item=name}
					{if $key eq $wcpart.failure_type}
						<option label="{$key}" value="{$key}" selected="selected">{$name}</option>
					{else}
						<option label="{$key}" value="{$key}">{$name}</option>
					{/if}
				{/foreach}
				</select></td>
				<td><select name="parts[{$wcpart.wcpart_id}][failure_system]">
				{foreach from=$failureSystemList key=key item=name}
					{if $key eq $wcpart.failure_system}
						<option label="{$key}" value="{$key}" selected="selected">{$name}</option>
					{else}
						<option label="{$key}" value="{$key}">{$name}</option>
					{/if}
				{/foreach}
				</select></td>
				<td><input type="text" name="parts[{$wcpart.wcpart_id}][um]" value="{$wcpart.um}" size="5" /></td>
				<td><input type="text" name="parts[{$wcpart.wcpart_id}][quantity]" value="{$wcpart.quantity}" size="5" /></td>
				<td><input type="text" name="parts[{$wcpart.wcpart_id}][qty_in]" value="{$wcpart.qty_in}" size="5" /></td>
				<td><input type="text" name="parts[{$wcpart.wcpart_id}][d_in]" value="{$wcpart.d_in}" size="9" /></td>
				</tr>
			{/foreach}
		</tbody>
	</table>
	<br/>
	<input type="submit" value="Update Lines" /> <input type="reset" value="Reset" />
</form>