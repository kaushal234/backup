<?php
ob_start();
$ERP = $LOCATION["erp"];
?>

<h3><?= _("Help homepage") ?></h3>

<p><?= _("Welcome to the help homepage") ?></p>

<p><a href="help/training/TLD Historique presentation.pdf">
    <?= _("TLD History presentation") ?></a>
</p>

<p><a href="help/training/TLD SHOPFLOOR  BOM presentation.pdf">
    <?= _("TLD SHOPFLOOR BOM presentation") ?></a>
</p>

<p><a href="help/training/TLD SHOPFLOOR  CBOM presentation.pdf">
    <?= _("TLD SHOPFLOOR CBOM presentation") ?></a>
</p>

<p><a href="help/training/TLD SHOPFLOOR  CRAB presentation.pdf">
    <?= _("TLD SHOPFLOOR CRAB presentation") ?></a>
</p>

<p><a href="help/training/TLD SHOPFLOOR  DESSIN presentation.pdf">
    <?= _("TLD SHOPFLOOR DRAWINGN presentation") ?></a>
</p>

<p><a href="<?= $SHOPFLOOR_URL ?>/index.php?m[0]=dms&id=556">
    <?= _("TLD SHOPFLOOR NCR presentation") ?></a>
</p>

<p><a href="help/training/TLD SHOPFLOOR  EAP presentation.pdf">
    <?= _("TLD SHOPFLOOR EAP presentation") ?></a>
</p>

<p><a href="help/training/TLD SHOPFLOOR  ER presentation.pdf">
    <?= _("TLD SHOPFLOOR ER presentation") ?></a>
</p>

<p><a href="help/training/TLD SHOPFLOOR  ITEM presentation.pdf">
    <?= _("TLD SHOPFLOOR ITEM presentation") ?></a>
</p>

<?php if($ERP == 520 || $ERP == 510 || $ERP == 500): ?>
<p><a href="help/training/TLD SHOPFLOOR  KELIO presentation.pdf">
    <?= _("TLD SHOPFLOOR KELIO presentation"); ?></a>
</p>

<p><a href="help/training/DMSpresentation.pdf">
    <?= _("TLD SHOPFLOOR DMS presentation"); ?></a>
</p>

<p><a href="help/training/TLD Survey public SHOPFLOOR presentation.pdf">
    <?= _("TLD Survey public SHOPFLOOR presentation") ?></a>
</p>
<?php endif; ?>

<?php
return ob_get_clean();
?>
