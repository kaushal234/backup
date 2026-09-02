<h3>Manual ID# {$id}</h3>
<p>
<a href="/en/private/sales_service/publications/zipped.php?id={$id}">CDROM Manual</a>
&nbsp;|&nbsp;Chapter 4 PDF&nbsp;
<a href="{$smarty.server.SCRIPT_NAME}?m[0]=manuals&m[1]=pdf&id={$id}">English</a>&nbsp;|&nbsp;
<a href="{$smarty.server.SCRIPT_NAME}?m[0]=manuals&m[1]=pdf&id={$id}&lang=fr">French</a>
</p>
{* Include the common template for text for header *}
{include file=sales_service/publications/manuals/manual.header.tpl}
{if $documents ne ""}
	<h3>Operation and Parts Manual</h3>
	{foreach name=doc_types key=doc_type item=doc_types from=$documents}
	<h4>{$doc_type}</h4>
		{foreach name=categories key=category item=docs from=$doc_types}
			<h5>{$category}</h5>
			<table width="100%" border="1" cellspacing="0" cellpadding="1" class="smalltext" bordercolor="#FFFFFF">
			<tr>
			{* Include the common template for detail header *}
			{include file=sales_service/publications/manuals/manual.detail.header.tpl}
			</tr>
			{foreach name=docs item=lineItem from=$docs}
			<tr bgcolor="{cycle values="#eeeeee,#d0d0d0"}">
				{* Include the common template for detail header *}
				{include file=sales_service/publications/manuals/manual.detail.tpl}
				<td>
				{if $lineItem.id eq ""}
					&nbsp;
				{else}
					<a href="{$smarty.server.SCRIPT_NAME}?m[0]=documents&m[1]=view&id={$lineItem.id}">
					View
					</a>
				{/if}
				</td>
			</tr>
			{/foreach}
			</table>
		{/foreach}
	{/foreach}
{/if}
