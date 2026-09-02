<h3>Manual ID# {$id}</h3>
{* Include the common template for text for header *}
{include file=product_support/publications/manuals/manual.header.tpl}
{if $documents ne ""}
	<h3>Operation and Parts Manual</h3>
	{foreach name=doc_types key=doc_type item=doc_types from=$documents}
	<h4>{$doc_type}</h4>
		{foreach name=categories key=category item=docs from=$doc_types}
			{if $category=='Chapter 0'}<h5>Chapter 0: Introduction</h5>
			{elseif $category=='Chapter 1'}<h5>Chapter 1: General Information &amp; Operating Instructions</h5>
			{elseif $category=='Chapter 2'}<h5>Chapter 2: Maintenance</h5>
			{elseif $category=='Chapter 3'}<h5>Chapter 3: Overhaul / Major Repair</h5>
			{elseif $category=='Chapter 4'}<h5>Chapter 4: Illustrated Parts List</h5>
			{elseif $category=='Chapter 5'}<h5>Chapter 5: Manufacturers Appendices</h5>
			{else}<h5>{$category}</h5>{/if}
			<table width="100%" border="1" cellspacing="0" cellpadding="1" class="smalltext" bordercolor="#FFFFFF">
			<tr>
			{* Include the common template for detail header *}
			{include file=product_support/publications/manuals/manual.detail.header.tpl}
			</tr>
			{foreach name=docs item=lineItem from=$docs}
			<tr bgcolor="{cycle values="#eeeeee,#d0d0d0"}">
				{* Include the common template for detail header *}
				{include file=product_support/publications/manuals/manual.detail.tpl}
				<td>
				{if $lineItem.id eq ""}
					&nbsp;
				{else}
					<a href="{$smarty.server.SCRIPT_NAME}?m[0]=publications&m[1]=documents&m[2]=view&lang={$header.lang}&id={$lineItem.id}">
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
