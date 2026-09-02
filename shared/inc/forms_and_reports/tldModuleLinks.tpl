<h3>Links</h3>
{if count($links)>0}
<table width="100%" border="1" cellspacing="0" cellpadding="1" class="smalltext" bordercolor="#FFFFFF">
<tr>
	<th>Module</th>
	<th>Number</th>
</tr>
{foreach name=outer item=link from=$links}
<tr bgcolor="{cycle values="#eeeeee,#d0d0d0"}">
	<td>{$link.type}</td>
	<td>
	{if $link.item<>"" && $urls[$link.type]<>""}
		<a href="{$urls[$link.type]}{$link.item}">{$link.item}</a>
	{/if}
	</td>
</tr>
{/foreach}
</table>
{else}
	<p class="alert">No Links</p>
{/if}