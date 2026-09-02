<?php
ob_start();
?>

<script type="text/javascript" src="/shared/javascript/jquery/jquery-1.9.1.min.js"></script>
<script type="text/javascript" src="/shared/javascript/jquery/jquery-ui-1.10.1.custom.min.js"></script>
<link rel="stylesheet" href="/shared/javascript/jquery/css/smoothness/jquery-ui-1.10.1.custom.min.css">

<?php
$body = include("$PATH/header.pi.tpl.php");
?>

<script type="text/javascript">
$( ".TblInstructions" ).css( "background-color" , "#EEEEEE" );
$( ".TblInstructions" ).css( "border" , "2px solid #000000" );
</script>

<script type="text/javascript">
$(document).ready(function(){
    resizeContent();

    $(window).resize(function() {
        resizeContent();
    });
});

function resizeContent() {
    $height = $(window).height() - 46;
    $('body div#content').height($height);
}
</script>


<a href="/shop/pi/45075-Work Instructions.pdf" target="_blank">Global drawing</a>


<style>
  html, body { height: 100% }
</style>

<table border=1 width=100% height=100%>
	<tr width=100% height=100%>
		<td width=100% height=100%>
			<object width="100%" height="100%" type="application/pdf" data="/shop/pi/45075-Work Instructions.pdf?#zoom=85&scrollbar=1&toolbar=1&navpanes=1" id="pdf_content"></object>
		</td>
	</tr>
</table>


<?php
return ob_get_clean();
?>
