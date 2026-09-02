<?php
ob_start();
?>
<br/>
<?php  if(count($rows)>0): ?>
<table class="sortable" cellpadding="3">
  <tr style="background:#2971a8; color:white; cursor:pointer;">
    <td>#</td>
    <td><?= _("PN") ?></td>
    <td><?= _("Code") ?></td>
    <td><?= _("Description (en)") ?></td>
    <td><?= _("Description (alt)") ?></td>
    <td><?= _("Qty") ?></td>
    <td><?= _("UM") ?></td>
    <td><?= _("Rev") ?></td>
    <td><?= _("File") ?></td>
  </tr>
  <?php  foreach($rows as $i=>$row): ?>
  <tr bgcolor="<?= (($i-1)%2==0)? "#eeeeee" : "#d0d0d0" ?>">
	<td><?= $i ?></td>
    <td><?= $row['t_sitm'] ?></td>
    <td><?= $row['t_csig_edm'] ?></td>
	<td><?= $row['t_dsca'] ?></td>
    <td>
      <?= ($erp >= '600' && $row['altdsca'] !== null) ? mb_convert_encoding($row['altdsca'], 'HTML-ENTITIES', 'UTF-8') : $row['altdsca'] ?>
    </td>
    <td><?= $row['t_qana'] ?></td>
    <td><?= $row['t_cuni'] ?></td>
    <td><?= $row['t_revi'] ?></td>
      <!-- revision in the link is used only to invalidate the browser cache when the drawing revision changes. -->
    <td><a target="_blank" href="<?= $php_self ?>?m[0]=getfile&m[1]=drawing&item=<?= $row['t_sitm'] ?>&revision=<?= $row['t_revi'] ?? '' ?>">
    	<img src="/shared/bluesphere/16x16/actions/filesaveas.png" alt="<?php  _("Download") ?>" /></a>
    </td>
  </tr>
  <?php  endforeach; ?>
</table>
<?php  else:
echo "<p>"._("No records found...")."<p>";
endif;

return ob_get_clean();
?>
