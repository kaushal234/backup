<?php declare(strict_types=1);
ob_start();
$i = 1;
$statusDefinitions = [
    'REVISION' => _('DMS page created, document under construction or revision'),
    'APPROVAL' => _('Document revision under discution and approval. Once approved, concerned users are notified'),
    'ACTIVE' => _('Last document revision released for use'),
    'EXPIRED' => _('Last document expired waiting for owner action'),
    'ARCHIVE' => _('DMS page and its documents revision left for reference'),
];
?>

<h3><?php echo _('Status definition and workflow'); ?></h3>

<img src="/img/status.png" alt="Status workflow" height="250" width="628" usemap="#worflow475a4eb" border="0">

<map name="worflow475a4eb">
    <area shape="rect" coords="0,105,95,150" href="#" title="REVISION: <?php echo $statusDefinitions['REVISION']; ?>">
    <area shape="rect" coords="240,106,335,150" href="#" title="APPROVAL: <?php echo $statusDefinitions['APPROVAL']; ?>">
    <area shape="rect" coords="410,106,510,150" href="#" title="ACTIVE: <?php echo $statusDefinitions['ACTIVE']; ?>">
    <area shape="rect" coords="535,0,623,50" href="#" title="ARCHIVE: <?php echo $statusDefinitions['ARCHIVE']; ?>">
    <area shape="rect" coords="530,200,623,248" href="#" title="EXPIRED: <?php echo $statusDefinitions['EXPIRED']; ?>">
</map>

<p><em><?php echo _('Hover your mouse over status above to have more details'); ?>...</em><p>

<?php
$_BUFF = ob_get_contents();
ob_end_clean();

return $_BUFF;
?>