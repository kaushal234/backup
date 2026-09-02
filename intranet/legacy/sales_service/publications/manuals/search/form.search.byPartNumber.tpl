<h4>Search Documents by Part Number</h4>
<form action="{$smarty.server.SCRIPT_NAME}">
	<input type="hidden" name="m[0]" value="documents">
	<input type="hidden" name="m[1]" value="search">
	<input type="hidden" name="m[2]" value="byPartNumber">
	Please enter part number to search for:<br><input type="text" name="id" value="">
	<input type="submit" name="submit" value="submit">
</form>