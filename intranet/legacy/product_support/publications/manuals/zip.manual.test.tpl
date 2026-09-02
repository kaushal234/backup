<html>
<head>
<title>{$title}</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<link href="tld-gse.css" rel="stylesheet" type="text/css">
</head>
<body>
<a href="menu.html">Home</a>
<!-- outer table-->
<table align="center" width="100%">
	<tr>
	<td>
<h3>Manual ID# {$id}</h3>
{* Include the common template for text for header *}
{include file=sales_service/publications/manuals/manual.header.tpl}
<p>Index
{foreach item=cat from=$categories}
	&nbsp;|&nbsp;<a href="{$cat}.html">{$cat}</a>
{/foreach}
</p>
<h5>{$category}</h5>
{if $documents ne ""}
	<table width="100%" border="1" cellspacing="0" cellpadding="1" class="smalltext" bordercolor="#FFFFFF">
	<tr>
	{* Include the common template for detail header *}
	{include file=sales_service/publications/manuals/manual.detail.header.tpl}
	</tr>
	{foreach name=docs item=lineItem from=$documents}
	<tr bgcolor="{cycle values="#eeeeee,#d0d0d0"}">
		{* Include the common template for detail header *}
		{include file=sales_service/publications/manuals/manual.detail.tpl}
		<td>
		{if $lineItem.id eq ""}
			&nbsp;
		{else}
			<a href="{$lineItem.id}.html">
			View
			</a>
		{/if}
		</td>
	</tr>
	{/foreach}
	</table>
{/if}
{if $schematics[0]<>""}
	<ul>
	{foreach item=item from=$schematics}
		<li><a href="{$item.file}">{$item.label}</a></li>
	{/foreach}
	</ul>
{/if}
	</td>
	</tr>
</table>
</body>
</html>
