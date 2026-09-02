{literal}
<script type="text/javascript">
$(document).ready(function(){
    $('.check:button').toggle(function(){
        $('input:checkbox').attr('checked','checked');
        $(this).val('Uncheck all')
    },function(){
        $('input:checkbox').removeAttr('checked');
        $(this).val('Check all');        
    })
})
</script>
{/literal}


<form name="addESRL" method="post" action="{$nextURL}">
	<table class="sortable" cellpadding="3"">
		<thead>
			<tr>
				<th>#</th>
				<th>Short Description</th>
				<th>Requested<br/>Delivery Date</th>
				<th>Early<br/>delivery<br/>ok?</th>				
				<th>Factory Promised<br/>Delivery Date</th>
				<th>SN#</th>
				<th>Model</th>
				<th>Final destination</th>
				<th>ESR#</th>
				<th>GT Date</th>
				<th>Ship Date</th>
				<th>Batch Qty</th>
				<th>Add to ESR?</th>
			</tr>
		</thead>
		<tbody>
			{foreach name=er item=er from=$er}
			<tr bgcolor="{cycle values="#eeeeee,#d0d0d0"}">
				<td>{$smarty.foreach.er.iteration}</td>
				<td>{$er.short_desc}</td>
				<td>{$er.del_dat}</td>
				<td>{$er.del_early}</td>
				<td>{$er.ddel_est1}</td>
				<td>{$er.sn}</td>
				<input type="hidden" name="units[{$er.id}][sn]" value="{$er.sn}" />
				<td>{$er.model}</td>
				<td>{$er.airport_code}</td>
				<td>{$er.esrid}</td>
				<td>{$er.dgt_act}</td>
				<td>{$er.date_shipped}</td>
				<input type="hidden" name="units[{$er.id}][date_shipped]" value="{$er.date_shipped}" />
				<td>{$er.batch_qty}</td>
				<td align="center">
        			<input type="checkbox" name="units[{$er.id}][checked]" class="cb-element" value="checked"/>
       			</td>
			</tr>
			{/foreach}
				<tr>
				<td> </td>
				<td> </td>
				<td> </td>
				<td> </td>
				<td> </td>
				<td> </td>
				<td> </td>
				<td> </td>
				<td> </td>
				<td> </td>
				<td> </td>
				<td> </td>
				<td>
				<input type="button" class="check" value="Check all" />
				</td>
				</tr>
		</tbody>
		<tr><th>ESR ID# - Leave blank if new one</th></tr>
		<tr><td><input type="text" name="esr_id" value="" size="11"/></td></tr>
	<tr>
		<td><input hidden type="number" name="sol_id" value="{$id}" size="11"/></td>
	</tr>
	</table>
	<br/>
	<input type="submit" value="Create ESR/Add ESRL" /> <input type="reset" value="Reset" />
</form>
