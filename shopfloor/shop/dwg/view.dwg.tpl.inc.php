<?php
ob_start();
?>

<h3><?= _("Drawing Revision Detail") ?></h3>

<table width="100%" border="1" cellspacing="0" cellpadding="1" class="smalltext" bordercolor="#FFFFFF">
<tr>
	<th><?= _("Part Number") ?></th>
	<th><?= _("Revision") ?></th>
	<th><?= _("Start Date") ?></th>
	<th><?= _("End Date") ?></th>
	<th><?= _("Filename") ?></th>
</tr>
<?php  foreach($engineeringRevisions['revisions'] as $i => $revision): ?>
<tr bgcolor="<?= ($i%2==0)?"#eeeeee":"#d0d0d0"; ?>">
	<td><?= $engineeringRevisions['item'] ?></td>
	<td><?= $revision['revision'] ?></td>
	<td><?= (new \DateTime($revision['effectiveDate']))->format('Y-m-d'); ?></td>
	<td><?= (new \DateTime($revision['expiryDate']))->format('Y-m-d'); ?></td>
	<td>
        <a target="_blank" href="<?= $php_self ?>?m[0]=getfile&m[1]=drawing&erp=<?= $erp ?>&item=<?= $engineeringRevisions['item'] ?>&date=<?php echo $revision['effectiveDate'] ?>"><?= _("Download") ?></a>
	</td>
</tr>
<?php  endforeach; ?>
</table>

<?php
return ob_get_clean();
?>
