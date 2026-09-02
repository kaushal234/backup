<?php
ob_start();
?>
<h3>NCR <?= _("Homepage") ?></h3>

<p><?= _("Welcome to the NCR homepage") ?>.</p>
<?php
return ob_get_clean();
?>
