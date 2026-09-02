<?php
ob_start();
?>

<h3><?= _("Files") ?></h3>

<?php  if(count($files)): ?>
  <table width="100%">
  <?php
  foreach($files as $k=>$file):
    if($file['filePath']=="") continue;
    $color = ($k%2==1) ? "#eeeeee" : "#d0d0d0";
  ?>
    <tr bgcolor="<?= $color ?>">
      <td><b><?= $file['createdAt'] ?></b><br><?= $file['description'] ?></td>
      <td>
        <a href="<?= "$php_self?m[0]=ncr&m[1]=view&m[2]=files&m[3]=out&id=$id&fid={$file['id']}" ?>">
        <img src="/shared/bluesphere/16x16/actions/filesaveas.png" alt="<?= $file['filePath'] ?>"></a>
      </td>
    </tr>
  <?php  endforeach; ?>
  </table>
<?php  else: ?>
  <p><?= _('No additional files posted') ?></p>
<?php  endif; ?>

<?php
return ob_get_clean();
?>
