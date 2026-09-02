{if $header.doc_type<>"MANUAL:SECTION"}
	<a href="{$smarty.server.SCRIPT_NAME}?m[0]=documents&m[1]=pdf&id={$header.id}">PDF</a>
{/if}
{include file=sales_service/publications/documents/document.header.tpl}

{if $detail[1] ne ""}
	<table width="100%" border="1" cellspacing="0" cellpadding="1" class="smalltext" bordercolor="#FFFFFF">
	<tr>
	{* Include the common template for document detail header *}
	{include file=sales_service/publications/documents/document.detail.header.tpl}
	</tr>
	{foreach name=outer item=lineItem from=$detail}
	<tr bgcolor="{cycle values="#eeeeee,#d0d0d0"}">
	{* Include the common template for document detail *}
	{include file=sales_service/publications/documents/document.detail.tpl}
	</tr>
	{/foreach}
	</table>
{/if}
