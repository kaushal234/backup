<h3>Manuals</h3>
<form action="{$smarty.server.SCRIPT_NAME}">
<input type="hidden" name="m[0]" value="publications">
<input type="hidden" name="m[1]" value="manuals">
<input type="hidden" name="m[2]" value="view">
<input type="text" name="id" value="Manual Number" onfocus="this.value=''">
<input type="submit" name="submit" value="submit">
</form>

<h4>Searches</h4>
<ul>
<li><a href="{$smarty.server.SCRIPT_NAME}?m[0]=publications&m[1]=manuals&m[2]=search&m[3]=byBrandModel">Click here to start searching by Brand and Model...</a></li>
<li><a href="{$smarty.server.SCRIPT_NAME}?m[0]=publications&m[1]=manuals&m[2]=search&m[3]=byCustomerPN">Click here to start searching by Customer Name and Part Number...</a></li>
<li><a href="{$smarty.server.SCRIPT_NAME}?m[0]=publications&m[1]=manuals&m[2]=countByModelLang">Number of manuals per model per language</a></li>
<li><a href="{$smarty.server.SCRIPT_NAME}?m[0]=publications&m[1]=manuals&m[2]=countByModelPublishable">Number of manuals Publishable from CBOM per model</a></li>
</ul>


