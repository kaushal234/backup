<?php
ob_start();
?>

<script type="text/javascript" src="/shared/javascript/jquery/jquery-1.9.1.min.js"></script>
<script type="text/javascript" src="/shared/javascript/jquery/jquery-ui-1.10.1.custom.min.js"></script>
<link rel="stylesheet" href="/shared/javascript/jquery/css/smoothness/jquery-ui-1.10.1.custom.min.css">

<br><br><br><br><br><br><br><br>

<form action="/shop/autoselect.php" method="post" name="frmIdent" id="frmIdent">
<div>
<input name="m[0]" type="hidden" value="login" />

<table width=100% height=50% border=0>
	<tr width=100% height=100%>
		<td width=30% height=100%>
			&nbsp;
		</td>
		<td width=60% height=100%>
			<table border=0>
				<tr>
					<td bgcolor=lightgrey colspan=2 style='font-size: <?= $_SESSION['pi_font_size'] ?? null ?>;' height=25px><b><?= _('Identification') ?></b></td>
				</tr>
				<tr>
					<td style='font-size: <?= $_SESSION['pi_font_size'] ?? null ?>;' height=25px>
						<br><b><?= $translator->trans('security.login.username') ?>:</b>
					</td>
					<td><br><input name="pi_user_input" type=text size=25 value=<?= $_SESSION['pi_user_input'] ?? null ?>><br><br></td>
				</tr>
				<tr>
					<td style='font-size: <?= $_SESSION['pi_font_size'] ?? null ?>;' height=25px>
						<b><?= $translator->trans('security.login.password') ?>:</b>
					</td>
					<td><input name="pi_pwd_input" type=password size=25><br><br></td>
				</tr>
				<tr>
					<td><input name="btnSubmit" value="<?= _('Submit') ?>" type="submit" /></td>
				</tr>
			</table>
		</td>
	</tr>
</table>
</div>
</form>

<span style='font-size: <?= $_SESSION['pi_font_size'] ?? null ?>;'><b><?= $_SESSION['pi_user_ko'] ?? null ?></b></span>
<span style='font-size: <?= $_SESSION['pi_font_size'] ?? null ?>;'><b><?= $a['error'] ?? null ?></b></span>
<?php $_SESSION['pi_user_ko']=''; ?>


<?php
return ob_get_clean();
?>
