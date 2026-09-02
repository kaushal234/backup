{assign var=MAX_LINES_PER_PAGE value=38}
<html>
<head>
<title>{$title}</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<link href="/tld-gse.css" rel="stylesheet" type="text/css">
</head>
<body>
<table width="100%">
	<tr>
	<td>
	<h1>{$title}</h1>
	</td>
	<td>
		<img src="/home/www/www.tld-gse.com/shared/icons/tld-icon.gif" width="72" height="46" align="right">
	</td>
	</tr>
</table>
<table width="100%"cellspacing="0" cellpadding="5" class="smalltext">
<tr>
	<td><p>
		<b>Factory Doc#: </b>{$header.factory_num}<br>
		<b>Revision: </b>{$header.rev}<br>
		<b>Category: </b>{$header.category}<br>
		<b>Doc Type: </b>{$header.doc_type}
		</p>
	</td>
	<td>
	{if $header.endescription<>""}
		<b>Description</b><br>
		{$header.endescription|default:"&nbsp;"|nl2br}<br>
	{/if}
	{if $header.ennotes<>""}
		<b>Notes</b><br>
		{$header.ennotes|default:"&nbsp;"|nl2br}<br>
	{/if}
	{if $header.frdescription<>""}
		<b>French Description</b><br>
		{$header.frdescription|default:"&nbsp;"|nl2br}<br>
	{/if}
	{if $header.frnotes<>""}
		<b>French Notes</b><br>
		{$header.frnotes|default:"&nbsp;"|nl2br}
	{/if}
	&nbsp;
	</td>
</tr>
</table>

<!-- NEW PAGE --> 
{if $header.diagram_filename ne ""}
	<img src="{$diagram_path}/{$header.diagram_filename}" width="700">
{else}
	No diagram available
{/if}

<!-- NEW PAGE --> 
{if $detail ne ""}
	{foreach name=outer item=lineItem from=$detail}
		{if $smarty.foreach.outer.iteration%$MAX_LINES_PER_PAGE==1}
			<table width="100%" border="1" cellspacing="0" cellpadding="1" class="smalltext" bordercolor="#FFFFFF">
			<tr>
			{* Include the common template for document detail header *}
			{include file=product_support/publications/documents/document.detail.header.tpl}
			</tr>
			<tr>
		{/if}
		<!--tr bgcolor="{cycle values="#eeeeee,#d0d0d0"}"-->
		<tr>
		{* Include the common template for document detail *}
		{include file=product_support/publications/documents/document.detail.tpl}
		</tr>
		{if $smarty.foreach.outer.iteration%$MAX_LINES_PER_PAGE==0}
			</table>
			<!-- NEW PAGE -->
		{/if}
	{/foreach}
	</table>
	{* Add blank page if *}
	{if $smarty.foreach.outer.iteration%$MAX_LINES_PER_PAGE%2==0}
			<!-- NEW PAGE -->
			<br><br><br><br><br><br><br><br><br><br><br><br><br><br>
			<p>Page Intentionally Left Blank</p>		
	{/if}
{/if}

</body>
</html>
