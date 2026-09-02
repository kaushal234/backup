<h3>{$title}</h3>
<form action="{$smarty.server.SCRIPT_NAME}" method="get" style="margin-left:20px;">   
	<input type="hidden" name="m[0]" value="customer_account">
	<input type="hidden" name="m[1]" value="step1">
	<table>
		<tr>
			<th>Spare Parts Hub :</th>
			<td>
				<select name="sph" class="smalltext">
					<option value="300">TLD AME - Salinas</option>
					<option value="300">TLD AME - Windsor</option>
					<option value="540">TLD EUR - Montlouis</option>
					<option value="600">TLD ASI - Hong Kong</option>
					<option value="680">TLD ASI - Shanghai</option>
				</select>
				<input type="submit" value="Ok" class="smalltext">
			</td>
		</tr>
	</table>
</form>
