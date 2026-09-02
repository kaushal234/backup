<h3>Filter by PO#, SO#, Packing Slip, Customer P/N or TLD P/N :</h3>
<form action="{$smarty.server.SCRIPT_NAME}" method="post" style="margin-left:20px;">   
	<input type="hidden" name="m[0]" value="customer_account">
	<input type="hidden" name="m[1]" value="view">
	<input type="hidden" name="m[2]" value="shipped_line_items">
	<input type="hidden" name="sph" value="{$sph}">
	<input type="hidden" name="id" value="{$id}">
	<input name="target" type="text" class="smalltext">
	<input name="Submit" type="submit" value="Find" class="smalltext">
</form>