<html>
<head>
	<title>{$title}</title>
	<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
	<link href="tld-gse.css" rel="stylesheet" type="text/css">
</head>
<body>
<a href="MANUAL INDEX.html">Home</a>&nbsp;|&nbsp;<a href="javascript:history.go(-1)">Back</a>
<!-- outer table-->
<table align="center">
	<tr>
	<td>
		<!-- inner table-->
		<table width="{$width|default:"650"}" border="0" align="center">
		  <tr>
			<td><img src="tld-icon.gif" width="72" height="46" align="right">
			<h1>{$title}</h1></td>
		  </tr>
		  <tr>
			<td>
			<!-- HEADER BLOCK -->
			<h3>Doc#: {$header.id}</h3>
			<table width="100%"cellspacing="0" cellpadding="5" class="smalltext">
			<tr>
				<td><p>
					<b>Factory Doc#: </b>{$header.factory_num}<br>
					<b>Revision: </b>{$header.rev}<br>
					<b>Category: </b>{$header.category}<br>
					<b>Doc Type: </b>{$header.doc_type}<br>
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
				<td>
				{* Picture *}
					<p>
					{if $header.diagram_filename_ext == "pdf"}
						<a href="{$header.diagram_filename}" target="_blank">
						<img src="pdf-icon.gif">Click to see PDF.</a>
					{elseif $header.diagram_filename_ext == "jpg"}
						<a href="{$header.diagram_filename}" target="blank">
							<img src="{$header.diagram_filename}" height="150">
						</a><br>
						Click to enlarge
					{else}
						No diagram available: {$header.diagram_filename}
					{/if}
					</p>
			</td>
			</tr>
			</table>
			<!-- HEADER BLOCK -->
			
			{if $detail[1] ne ""}
				<table width="100%" border="1" cellspacing="0" cellpadding="1" class="smalltext" bordercolor="#FFFFFF">
				<tr>
					<th><b>Item</b></th>
					<th><b>PN</b></th>
					<th><b>QTY</b></th>
					<th><b>UM</b></th>
					<th><b>Details</b></th>
				</tr>
				{foreach name=outer item=lineItem from=$detail}
				<tr bgcolor="{cycle values="#eeeeee,#d0d0d0"}">
				<!-- LINE BLOCK -->
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
				<!-- LINE BLOCK -->
				</tr>
				{/foreach}
				</table>
			{/if}
			</td>
		  </tr>
		</table>
	</td>
	</tr>
</table>
</body>
</html>
