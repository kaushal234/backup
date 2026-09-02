<?php
ob_start();
?>
<h3><?= _("MATERIAL NON CONFORMANCE REPORT") ?></h3>

<h3>NCR# <?= $id.' - '.$ncr['status'] ?></h3>

<?php if(strtoupper(null !== $ncr['mainFile'] ? substr($ncr['mainFile']['filePath'], -3) : '') == "JPG"): ?>
<img src="<?= $php_self ?>?m[0]=ncr&m[1]=file&m[2]=getPhoto&id=<?= $id ?>" align="right" width="250">
<?php elseif(!empty($ncr['mainFile'])): ?>
<a href="<?= $php_self ?>?m[0]=ncr&m[1]=view&m[2]=outPhoto&id=<?= $id ?>" target="_blank">
  <img src="/shared/bluesphere/64x64/mimetypes/document.png" align="right" alt="<?= _("Download Attachment") ?>">
</a>
<?php endif; ?>

<?php
return ob_get_clean();
?>
