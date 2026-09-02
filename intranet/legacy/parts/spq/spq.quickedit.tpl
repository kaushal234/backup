<form name="editSPQ" method="post" action="{$nextURL}">
	<table class="sortable" cellpadding="3"">
		<thead>
			<tr>
				<th>SPQ#</th>
				<th>SPH</th>
				<th>Owner</th>
				<th>Customer</th>
				<th>Request Received Date</th>
				<th>Qono#</th>
				<th>Quote Value</th>
				<th>RFQ#</th>
				<th>Status</th>
			</tr>
		</thead>
		<tbody>
			{foreach name=spq item=spq from=$spqList}
			<tr bgcolor="{cycle values="#eeeeee,#d0d0d0"}">
				<td>{$spq.id}</td>
				<td>{$spq.sph_fullname}</td>
				<td>{$spq.poster_fullname}</td>
				<td>{$spq.customer_name}</td>
				<td>{$spq.dt_received}</td>
				<td>{$spq.qono}</td>
				<td>{$spq.qono_val}</td>
				<td>{$spq.rfq}</td>
				<td>
				  <select name="spq[{$spq.id}][status]" >
				  	{foreach from=$statusList key=key item=status}
						{if $status eq $spq.status}
							<option label="{$status}" value="{$status}" selected="selected">{$status}</option>
						{else}
							<option label="{$status}" value="{$status}">{$status}</option>
						{/if}
					{/foreach}
				  </select>
				</td>
				</tr>
			{/foreach}
		</tbody>
	</table>
	<br/>
	<input type="submit" value="Update SPQs" /> <input type="reset" value="Reset" />
</form>
