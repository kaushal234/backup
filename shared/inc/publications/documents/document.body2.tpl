{* Coverpage *}
<table width="100%" border="0" align="center">
  <tr>
	<td><h1>Document #{$header.id}</h1></td>
	<td><img src="/home/www/www.tld-gse.com/shared/icons/tld-icon.gif" width="72" height="46" align="right">
	</td>
  </tr>
</table>
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
	<h2></h2>

{* Picture page *}
{if $header.diagram_filename<>""}
	<!-- NEW PAGE -->
	<img src="{$diagram_path}/{$header.diagram_filename}" width="{$pic_width|default:"600"}" height="{$pic_height}" align="center">
{/if}

{* Start of table *}
{if count($pages)}
	{foreach name=outer item=page from=$pages}
		{* PRINT THE REPEATING TABLE HEADER *}
			<table width="100%" border="1" cellspacing="0" cellpadding="0" class="smalltext" bordercolor="#FFFFFF">
			<tr>
				<th><b>Item</b></th>
				<th><b>PN</b></th>
				<th><b>QTY</b></th>
				<th><b>Details</b></th>
				<th><b>P</b></th>
				<th><b>M</b></th>
				<th><b>O</b></th>
				<th><b>C</b></th>
			</tr>
		{foreach name=outer item=lineItem from=$page}
			<tr valign="top">
				<td><font size='-1'>&nbsp;{$lineItem.item|default:"&nbsp;"}</font></td>
				<td><font size='-1'>&nbsp;{$lineItem.pn|default:"&nbsp;"}
				<br>&nbsp;
				{if $lineItem.vendor_pn <> ""}
					<b>Vendor PN:</b>&nbsp;{$lineItem.vendor_pn}
				{/if}
				<br>&nbsp;
				{if $lineItem.ocm_pn <> ""}
					<b>OCM PN:</b>&nbsp;{$lineItem.ocm_pn}
				{/if}
				</font></td>
				<td>
					<font size='-1'>&nbsp;{$lineItem.qty|default:"&nbsp;"}
					&nbsp;{$lineItem.um|default:"&nbsp;"}</font>
				</td>
				<td width="50%">
					<font size='-1'>
					&nbsp;{$lineItem.en|default:"&nbsp;"}
					{if $lineItem.fr <> ""}
						&nbsp;/&nbsp;{$lineItem.fr}
					{/if}
					<br>&nbsp;
					{if $lineItem.note <> ""}
						<b>Note:</b>&nbsp;{$lineItem.note}
					{/if}
				</font>
				</td>
				<td><font size='-1'>
				{if $lineItem.group_p<>0}
					&nbsp;{$lineItem.group_p}
				{else}
					&nbsp;
				{/if}
				</font></td>
				<td><font size='-1'>
				{if $lineItem.group_m<>0}
					&nbsp;{$lineItem.group_m}
				{else}
					&nbsp;
				{/if}
				</font></td>
				<td><font size='-1'>
				{if $lineItem.group_o<>0}
					&nbsp;{$lineItem.group_o}
				{else}
					&nbsp;
				{/if}</font>
				</td>
				<td><font size='-1'>
				{if $lineItem.group_c<>0}
					&nbsp;{$lineItem.group_c}
				{else}
					&nbsp;
				{/if}</font>
				</td>
			</tr>
		{/foreach}
		</table>
	{/foreach}
	{* Add blank page*}
	{if ($header.diagram_filename=="" && count($pages)%2==0)
		OR ($header.diagram_filename<>"" && count($pages)%2<>0)}
		<!-- NEW PAGE -->
		<br><br><br><br><br><br><br><br><br><br>
		<p align="center">Page Intentionally Left Blank</p>		
	{/if}
{/if}
<!-- NEW PAGE -->