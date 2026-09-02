
{foreach name=lines item=row from=$rows}
<p class="xxsmalltext" align="center">
		{$row.serial|upper}<br>
		{$row.man_serial|upper}<br>
	<img src="/en/private/mis/inv/barcode.php?text={$row.id}">
	</p><!-- NEW PAGE -->
{/foreach}
