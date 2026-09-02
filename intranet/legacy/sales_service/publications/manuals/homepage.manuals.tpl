<h3>Manuals</h3>
<form action="{$smarty.server.SCRIPT_NAME}">
<input type="hidden" name="m[0]" value="manuals">
<input type="hidden" name="m[1]" value="view">
<input type="text" name="id" value="Manual Number">
<input type="submit" name="submit" value="submit">
</form>

<h4>Searches</h4>
<ul>
<li><a href="{$smarty.server.SCRIPT_NAME}?m[0]=manuals&m[1]=search&m[2]=byBrandModel">Click here to start searching by Brand and Model...</a></li>
</ul>
