<html>
<head>
<title>{$title}</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<link href="tld-gse.css" rel="stylesheet" type="text/css">
</head>
<body>
<!-- outer table-->
<table align="center">
	<tr>
	<td>
<h3>Manual ID# {$id}</h3>
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
					<a href="{$lineItem.id}.html">
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
