{if $pic_path<>"."}
	{assign var=pic_path value="/en/private/uploads/manuals_diagrams"}
{/if}
<script language="Javascript">
<!--
window.moveTo((screen.width/2)-400,(screen.height/2)-300)
//window.moveTo(0,0)
window.resizeTo(785, 585)
//window.resizeTo(screen.width,screen.height)
//window.resizeTo(750,500)
//-->
</script>
<applet code="imageviewer.class" name="imageviewer" width="750" height="500"> 

        <!-- The filename of the script goes here. -->
        <param name="filename" value="{$smarty.server.SCRIPT_NAME}?m[0]=publications&m[1]=file&m[2]=diagramImg&id={$header.id}">

        <!-- Background color goes here. -->
        <param name="background" value="dark gray">

        <!-- Maximum zoom multiple goes here. -->
        <param name="maxzoom" value="4">

        <!-- HTML code for non Java visitors goes here. -->
        <IMG SRC="{$smarty.server.SCRIPT_NAME}?m[0]=publications&m[1]=file&m[2]=diagramImg&id={$header.id}">
</applet>
