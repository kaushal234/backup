<h3>{$title}</h3>
<form action="{$smarty.server.SCRIPT_NAME}" method="get" style="margin-left:20px;">   
	<input type="hidden" name="m[0]" value="customer_account">
	<input type="hidden" name="m[1]" value="step2">
	<input type="hidden" name="sph" value="{$sph}">
	<table>
		<tr>
			<th>Customer Id :</th>
			<td><input name="t_cuno" type="text" class="xsmalltext" style="width:100%"></td>
			<td>
				<input name="btnSubmitCustomerIdOpenLineItems" type="submit" value="Open Line Items" class="xsmalltext">
				<input name="btnSubmitCustomerIdShippedLineItems" type="submit" value="Shipped Line Items" class="xsmalltext">
			</td>
		</tr>
		<tr>
			<th>Customer name :</th>
			<td>
				<select name="t_nama" class="xsmalltext">
					{foreach key=key item=item from=$customers}
						<option value="{$item.t_cuno}">{$item.t_nama} ({$item.t_cuno})</option>
					{/foreach}
				</select>
			</td>
			<td>
				<input name="btnSubmitCustomerNameOpenLineItems" type="submit" value="Open Line Items" class="xsmalltext">
				<input name="btnSubmitCustomerNameShippedLineItems" type="submit" value="Shipped Line Items" class="xsmalltext">
			</td>
		</tr>
	</table>
</form>
