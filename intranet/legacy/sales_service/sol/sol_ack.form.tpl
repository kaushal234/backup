<input id="globaldate" type="text" name="Global date" value="{$er[0].ddel_asm}" size="10" maxlength="10" class="datepicker"/>
<input id="changedate" type="button" value="Copy to all date fields" />
<form name="editSOLAck" method="post" action="{$nextURL}">
<table class="sortable" cellpadding="3"">
		<thead>
			<tr>
				<th>#</th>
				<th>Short Description</th>
				<th>Requested<br/>Delivery Location</th>
				<th>Requested<br/>Delivery Date</th>
				<th>Early<br/>delivery<br/>ok?</th>				
				<th>Factory Promised<br/>Delivery Date</th>
				<th>SN#</th>
				<th>Model</th>
                <th>ASM Promised<br/>Delivery Date</th>
			</tr>
		</thead>
		<tbody>
			{foreach name=er item=er from=$er}
			<tr bgcolor="{cycle values="#eeeeee,#d0d0d0"}">
				<td>{$smarty.foreach.er.iteration}</td>
				<td>{$er.short_desc}</td>
				<td>{$er.del_location}</td>
                <td>{$er.del_dat}</td>
				<td>{$er.del_early}</td>
				<td>{$er.ddel_est1}</td>
				<td>{$er.sn}</td>
				<td>{$er.model}</td>
                <td><input type="text" name="units[{$er.id}][ddel_asm]" value="{$er.ddel_asm}" size="10" maxlength="10" class="datepicker"/></td>
			</tr>
			{/foreach}
		</tbody>
	</table>
	<br/>
	<input type="submit" value="Save Dates and generate PDF" /> <input type="reset" value="Reset" />
</form>

<script type="text/javascript">
	{literal}
	
    $("#changedate").click(function () {
        $("input[name*='ddel_asm']").val($("#globaldate").val());
    });

	{/literal}
</script>