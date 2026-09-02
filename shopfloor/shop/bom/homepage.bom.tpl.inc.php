<?php
ob_start();
?>

<h3><?= _("Get BOM") ?></h3>

<form action="<?= $php_self ?>" method="get">
	<input name="m[0]" type="hidden" value="bom">
	<input name="m[1]" type="hidden" value="view">
	<input name="reset" type="hidden" value="Y">
	<input type="text" name="pn" value="<?= _("PART NUMBER") ?>" onfocus="this.value=''">
	<input type="text" name="date" value="<?= date("Y-m-d") ?>">
	<input type="submit" name="submit" value="<?= _("Show BOM") ?>">
</form>

<?php
return ob_get_clean();
?>
