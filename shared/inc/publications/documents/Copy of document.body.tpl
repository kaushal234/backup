<table width="100%" border="0" align="center">
  <tr>
	<td><h1>Document #{$header.id}</h1></td>
	<td><img src="/home/www/www.tld-gse.com/shared/icons/tld-icon.gif" width="72" height="46" align="right">
	</td>
  </tr>
</table>
<br><br><br><br><br><br><br>
<a name="{$header.id}"></a>
<h2>Factory Doc#: </b>{$header.factory_num}</h2>
<h2>Revision: </b>{$header.rev}</h2>
<h2>Category: </b>{$header.category}</h2>
<h2>Doc Type: </b>{$header.doc_type}</h2>

	{if $header.endescription<>""}
		<h2>Description</h2>
		<p>{$header.endescription|nl2br}</p>
	{/if}
	{if $header.ennotes<>""}
		<h2>Notes</h2>
		<p>{$header.ennotes|nl2br}</p>
	{/if}
	{if $header.frdescription<>""}
		<h2>French Description</h2>
		<p>{$header.frdescription|nl2br}</p>
	{/if}
	{if $header.frnotes<>""}
		<h2>French Notes</h2>
		<p>{$header.frnotes|nl2br}</p>
	{/if}

{if count($detail)==0 AND $header.diagram_filename<>""}
	<img src="{$diagram_path}/{$header.diagram_filename}" width="{$pic_width}" height="{$pic_height}">
{elseif count($detail)}
	{foreach name=outer item=lineItem from=$detail}
		{* PRINT THE REPEATING TABLE HEADER *}
		{if $smarty.foreach.outer.iteration%$MAX_LINES_PER_PAGE==1}
			{if $header.diagram_filename ne ""}
				<!-- NEW PAGE --> 
				<img src="{$diagram_path}/{$header.diagram_filename}" width="{$pic_width}" height="{$pic_height}">
			{/if}
			
			<!-- NEW PAGE --> 
			<table width="100%" border="1" cellspacing="0" cellpadding="1" class="smalltext" bordercolor="#FFFFFF">
			<tr>
				<th><b>Item</b></th>
				<th><b>PN</b></th>
				<th><b>QTY</b></th>
				<th><b>UM</b></th>
				<th><b>Details</b></th>
			</tr>
			<tr>
		{/if}
		<!--tr bgcolor="{cycle values="#eeeeee,#d0d0d0"}"-->
		<tr>
			<td>{$lineItem.item|default:"&nbsp;"}</td>
			<td>{$lineItem.pn|default:"&nbsp;"}
			{if $lineItem.vendor_pn <> ""}
				<br><b>Vendor PN:</b>&nbsp;{$lineItem.vendor_pn}
			{/if}
			{if $lineItem.ocm_pn <> ""}
				<br><b>OCM PN:</b>&nbsp;{$lineItem.ocm_pn}
			{/if}
			</td>
			<td>{$lineItem.qty|default:"&nbsp;"}</td>
			<td>{$lineItem.um|default:"&nbsp;"}</td>
			<td>{$lineItem.en|default:"&nbsp;"}
			{if $lineItem.fr <> ""}
				<br><b>French:</b>&nbsp;{$lineItem.fr}
			{/if}
			{if $lineItem.note <> ""}
				<br><b>Note:</b>&nbsp;{$lineItem.note}
			{/if}
			</td>
		</tr>
		{* PRINT THE REPEATING TABLE FOOTER *}
		{if $smarty.foreach.outer.iteration%$MAX_LINES_PER_PAGE==0}
			</table>
		{/if}
	{/foreach}
	</table>
	{* Add blank page*}
	{if ($header.diagram_filename<>"" AND count($detail)<>0)
		 OR ($header.diagram_filename=="" AND $smarty.foreach.outer.iteration%$MAX_LINES_PER_PAGE==0)
		 OR ($header.diagram_filename=="" AND count($detail)==0)}
		<!-- NEW PAGE -->
		<br><br><br><br><br><br><br><br><br><br><br><br><br><br>
		<p align="center">Page Intentionally Left Blank</p>		
	{/if}
{/if}
		<!-- NEW PAGE -->