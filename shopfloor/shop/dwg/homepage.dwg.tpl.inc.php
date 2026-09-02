<?php
ob_start();
?>

<h3><?= _("Drawing Revision Detail") ?></h3>

<form action="<?= $php_self ?>" method="get">
  <input type="hidden" name="m[0]" value="dwg">
  <input type="hidden" name="m[1]" value="ls">
  <input type="hidden" name="erp" value="<?= $ERP ?>">
  <input type="text" name="id" value="<?= _("PART NUMBER") ?>" onfocus="this.value=''" id="inputpn">
  <input type="submit" name="submit" value="<?= _("Show Drawings") ?>">
</form>
<script>
    document.getElementById('inputpn').focus();</script>
<?php
return ob_get_clean();
?>
