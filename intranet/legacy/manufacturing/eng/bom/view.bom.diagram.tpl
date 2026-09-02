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
        <param name="filename" value="{$smarty.server.SCRIPT_NAME}?m[0]=bom&m[1]=view&m[2]=diagram&m[3]=jpg&erp={$bom->itsERP}&pn={$bom->itsID}&date={$bom->itsDate}">

        <!-- Background color goes here. -->
        <param name="background" value="dark gray">

        <!-- Maximum zoom multiple goes here. -->
        <param name="maxzoom" value="4">

        <!-- HTML code for non Java visitors goes here. -->
        <IMG SRC="{$smarty.server.SCRIPT_NAME}?m[0]=bom&m[1]=view&m[2]=diagram&m[3]=jpg&erp={$bom->itsERP}&pn={$bom->itsID}&date={$bom->itsDate}">
</applet>
