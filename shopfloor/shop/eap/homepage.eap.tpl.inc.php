<?php
ob_start();
?>
<h3>EAP <?= _("Homepage") ?></h3>

<p><?= _("Welcome to the EAP homepage") ?>.</p>
<?php
return ob_get_clean();
?>
