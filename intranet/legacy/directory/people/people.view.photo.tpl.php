<?php
ob_start();
?>

<h4>Actual Picture</h4>

<p>
<?php
$photoFilename = $people->getPhotoFilename();
if(empty($photoFilename)): ?>
    <img src="/shared/no_photo.jpg" width="150">
<?php  else: ?>
    <a href="/en/private/directory/index.php?m[0]=people&m[1]=view&m[2]=photo&m[3]=out&width=512&id=<?= $people->getID(); ?>">
        <img src="/en/private/directory/index.php?m[0]=people&m[1]=view&m[2]=photo&m[3]=out&width=128&id=<?= $people->getID(); ?>">
    </a>
<?php  endif; ?>
</p>

<?php
return ob_get_clean();
