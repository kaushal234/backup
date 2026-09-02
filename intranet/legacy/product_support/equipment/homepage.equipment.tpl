<h3>Equipment Records Homepage</h3>
<form action="{$smarty.server.SCRIPT_NAME}" method="get" style="display:inline-block;">
	Search by serial number.<br>
	<input type="hidden" name="m[0]" value="equipment">
	<input type="hidden" name="m[1]" value="search">
	<input type="hidden" name="m[2]" value="bySN">
	<input name="sn" type="text" value="Equip SN" onfocus="this.value=''">
	<input name="Submit" type="submit" value="Find">
</form>

<form action="{$smarty.server.SCRIPT_NAME}" method="post" id="frmSearch" name="frmSearch" style="display:inline-block; margin-left: 100px">
	Basic Search<br>
	<input type="hidden" name="m[0]" value="equipment">
	<input type="hidden" name="m[1]" value="listing">
	<input type="hidden" name="m[2]" value="search">
	<input name="target" type="text" value="">
	<input name="btnSubmit" type="submit" value="Submit">
</form>
