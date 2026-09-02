<h3>Get AVAILABILITY</h3>
<form action="{$smarty.server.SCRIPT_NAME}" method="get">
	<input name="m[0]" type="hidden" value="avail">
	<input name="m[1]" type="hidden" value="view">
	<select name=erp>
	   {html_options options=$locations}
	</select>
	<input type="text" name="item" value="PART NUMBER" onfocus="this.value=''">
	<input type="submit" name="submit" value="Show Availability">
</form>