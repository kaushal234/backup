{if $pic_path<>"."}
	{assign var=pic_path value="/en/private/uploads/manuals_diagrams"}
{/if}
<h3>Document#: {$header.id}</h3>
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
			<a href="{$pic_path}/{$header.diagram_filename}" target="_blank">
			<img src="/shared/icons/pdf-icon.gif">Click to see PDF.</a>
		{elseif $header.diagram_filename_ext == "jpg"}
			{if $pic_path == "."}
				<a href="{$pic_path}/{$header.diagram_filename}" target="blank">
			{else}
				<a href="javascript:void(0)" onclick="javascript:window.open('{$smarty.server.SCRIPT_NAME}?m[0]=documents&m[1]=viewImage&id={$header.id}','Closeup', 'scrollbars=yes,status=no,toolbar=no,location=no,menubar=no,resizable=yes,width=1024,height=768')">
			{/if}
			<img src="{$pic_path}/{$header.diagram_filename}" height="150">
			</a><br>Click to enlarge
		{else}
			No diagram available: {$header.diagram_filename}
		{/if}
		</p>
</td>
</tr>
</table>
