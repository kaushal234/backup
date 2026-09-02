{assign var="title" value="Manual Search"}
{if $count==0}
	<h1>No results found...</h1>
{else}
	<table border="1" cellspacing="0" cellpadding="1" class="smalltext" bordercolor="#FFFFFF">
	<tr>
{* Include the common template for detail header *}
{include file=private/manuals/manual.list.detail.header.tpl}
	</tr>
	{foreach name=outer key=key item=lineItem from=$rows}
	<tr bgcolor="{cycle values="#eeeeee,#d0d0d0"}">
{* Include the common template for detail *}
{include file=private/manuals/manual.list.detail.tpl}
		<td>
			<a href="{$smarty.server.SCRIPT_NAME}?m[0]=view&m[1]=manual&id={$lineItem.id}">View</a>
		</td>
	</tr>
	{/foreach}
	
	</table>
{/if}