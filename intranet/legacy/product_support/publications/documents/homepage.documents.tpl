<h3>Documents</h3>
<form action="{$smarty.server.SCRIPT_NAME}">
<input type="hidden" name="m[0]" value="publications">
<input type="hidden" name="m[1]" value="documents">
<input type="hidden" name="m[2]" value="view">
<input type="text" name="id" value="Document Number" onfocus="this.value=''">
<input type="submit" name="submit" value="submit">
</form>

<h4>Searches</h4>
<ul>
<li><a href="{$smarty.server.SCRIPT_NAME}?m[0]=publications&m[1]=documents&m[2]=search&m[3]=byPartNumber">Click here to start searching by Part Number...</a></li>
</ul>
