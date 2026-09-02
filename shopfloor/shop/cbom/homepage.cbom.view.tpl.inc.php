<?php
ob_start();
?>

<table width="100%">
  <tr align="center">
    <td width="12%">
      <a href="<?= $php_self ?>?m[0]=cbom&m[1]=view&sn=<?= $sn ?>&date=<?= $date ?>" title="Click to go to this CBOM homepage">
      <img src="/shared/bluesphere/48x48/apps/tux_config.png" /><br/>
      CBOM #<?= $sn ?><br/><?= _("Home page") ?></a>
    </td>
    <td width="12%">
      <a href="<?= $php_self ?>?m[0]=cbom&m[1]=view&m[2]=schematics&sn=<?= $sn ?>&date=<?= $date ?>" title="Click to get Schematics">
      <img src="/shared/bluesphere/48x48/apps/snavigator.png" /><br/>
      <?= _("Get Schematics") ?></a>
    </td>
    <td width="12%">
      <a href="<?= $php_self ?>?m[0]=cbom&m[1]=view&m[2]=options&sn=<?= $sn ?>&date=<?= $date ?>" title="Click to get Options">
      <img src="/shared/bluesphere/48x48/apps/snavigator.png" /><br/>
      <?= _("Get Options") ?></a>
    </td>
    <td width="12%">
      <a href="<?= $php_self ?>?m[0]=cbom&m[1]=view&m[2]=ai&sn=<?= $sn ?>&date=<?= $date ?>" title="Click to get Assembly Instructions">
      <img src="/shared/bluesphere/48x48/apps/snavigator.png" /><br/>
      <?= _("Get Assembly Instructions") ?></a>
    </td>
    <td width="12%">
      <a href="<?= $php_self ?>?m[0]=cbom&m[1]=view&m[2]=docList&date=<?= $date ?>&sn=<?= $sn ?>" title="Click to get full document list">
      <img src="/shared/bluesphere/48x48/apps/snavigator.png" /><br/>
      <?= _("Document List") ?></a>
    </td>
  <td width="12%">
      <a href="<?= $php_self ?>?m[0]=cbom&m[1]=view&m[2]=materialByCode&date=<?= $date ?>&sn=<?= $sn ?>" title="Click to get material list by signal code">
          <img src="/shared/bluesphere/48x48/apps/snavigator.png" /><br/>
          <?= _("Material By Code") ?></a>
  </td>
    <td width="12%">
      <a href="<?= $php_self ?>?m[0]=cbom&m[1]=view&m[2]=chapters&date=<?= $date ?>&sn=<?= $sn ?>" title="Click to get chapters 0, 1, 2, 3, 5">
      <img src="/shared/bluesphere/48x48/apps/kdict.png" /><br/>
      <?= _("Chapters") ?> 0,1,2,3,5</a>
    </td>
    <td width="12%">
      <a href="<?= $php_self ?>?m[0]=er&m[1]=form&m[2]=submitComponentSN&prno=<?= $sn ?>" title="Click to add Component Serial Number">
      <img src="/shared/bluesphere/48x48/apps/klipper.png" /><br/>
      <?= _("Add Component SN") ?></a>
    </td>
  </tr>
</table>

<?php
return ob_get_clean();
?>
