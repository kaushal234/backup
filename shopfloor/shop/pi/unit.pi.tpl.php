<?php
include_once("common.inc.php");
ob_start();
?>

<script type="text/javascript" src="/shared/javascript/jquery/jquery-1.9.1.min.js"></script>
<script type="text/javascript" src="/shared/javascript/jquery/jquery-ui-1.10.1.custom.min.js"></script>
<link rel="stylesheet" href="/shared/javascript/jquery/css/smoothness/jquery-ui-1.10.1.custom.min.css">
<?php if($error) { ?>
<p class="error">
    <?= $error ?>
<br>
</p>
<?php } ?>
<br><br><br><br><br><br><br><br>

<form action="/shop/autoselect.php?m[0]=pi&m[1]=form&m[2]=operation" method="post" name="frmIdent" id="frmIdent">
<div>
<input name="m[0]" type="hidden" value="pi" />
<input name="m[1]" type="hidden" value="form" />
<input name="m[2]" type="hidden" value="unit" />
<table width=100% height=50% border=0>
	<tr width=100% height=100%>
		<td width=30% height=10>
		</td>
		<td width=60% height=100%>
			<table border=0>
				<tr width=100%>
					<td bgcolor=lightgrey colspan=2 style='font-size: <?= $_SESSION['pi_font_size'] ?>;' height=25px><b><?= _('Select ER') ?></b></td>
				</tr>
				<tr width=100%>
					<td style='font-size: <?= $_SESSION['pi_font_size'] ?>;' height=25px>
						<b><?= $translator->trans('form_er.er', [], 'pio') ?>:</b>
					</td>
					<td><input name="pi_er_input" type=text size=25 value=<?= $_SESSION['pi_er_input'] ?>><br><br></td>
				</tr>
				<tr width=100%>
					<td><input id="erSubmit" name="btnSubmit" value="<?= $translator->trans('form_er.submit', [], 'pio') ?>" type="submit" /></td>
					<td style="text-align: right;"><a href="<?= $php_self ?>"><?= $translator->trans('form_er.go_to_shopfloor', [], 'pio') ?></a></td>
				</tr>
			</table>
		</td>
	</tr>
</table>

</div>
</form>

<script type="text/javascript">
$( ".TblUnit" ).css( "background-color" , "#EEEEEE" );
$( ".TblUnit" ).css( "border" , "2px solid #000000" );
$("#erSubmit").on("click", function(){
    $(this).hide().after('<button disabled>"Please wait"</button>');
    $(this).parents('form:first').submit();
});
</script>

<?php
return ob_get_clean();
?>
