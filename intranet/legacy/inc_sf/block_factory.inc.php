<?php
include_once("common.inc.php");
$camera = tldWebCam::getRandomPic();
global $kernel;
$authorizationChecker = $kernel->getContainer()->get('security.authorization_checker.legacy');
$granted = $authorizationChecker->isGranted('INTRANET_ACCESS');
?>
<?php if ($granted): ?><a href="/en/private/manufacturing/index.php"><?php endif; ?>
    <img src="<?= $camera['file'] ?>"><br><strong><?= strtoupper($camera["location"])?></strong>, <?= $camera["title"] ?>
<?php if ($granted): ?></a><?php endif; ?>
