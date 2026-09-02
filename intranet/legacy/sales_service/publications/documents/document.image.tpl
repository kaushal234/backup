{if $pic_path<>"."}
	{assign var=pic_path value="/en/private/uploads/manuals_diagrams"}
{/if}
        <script language="Javascript">
<!--
//window.moveTo((screen.width/2)-400,(screen.height/2)-300)
window.moveTo(0,0)
window.resizeTo(screen.width,screen.height)
//window.resizeTo(750,500)
//-->
</script>
<table width="100%">
<tr>
<td width="333">
	<a href="javascript:window.close()">Close Window</a>
</td>
<td width="334" align="center">
	<h3>Image# {$header.id}</h3>
</td>
<td width="333">
	<ul>
		<li>Click to Zoom IN.</li>
		<li>Shift Click Zoom OUT</li>
		<li>Move curser to picture edge to pan</li>
	</ul>
</td>
</tr>
<tr>
<td colspan="3" align="center">
	<applet code="ImageZoom2.class" width="1000" height="580">
			<param name="IMAGE" value="https://www.tld-gse.com{$smarty.server.SCRIPT_NAME}?m[0]=file&m[1]=diagramImg&id={$header.id}">
			<param name="Preload" value="ON">
	</applet>
</td>
</tr>
</table>
