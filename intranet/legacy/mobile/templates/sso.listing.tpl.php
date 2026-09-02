<div class="container">
	<div class="list-group">
		<?php foreach($ssoList as $sso): ?>
			<form method="post" class="clickable-link" action="<?= "$PHPSELF?page=customers&action=SSOView" ?>">
                <input type="hidden" name="sso_name" value="<?= $sso['name'] ?>">
                <input type="hidden" name="sso_id" value="<?= $sso['id'] ?>">
                <input type="hidden" name="sso_legacy_id" value="<?= $sso['legacyId'] ?>">
    			<a href="#" class="list-group-item submit-form">
  					<span class="float-end glyphicon glyphicon-arrow-right"></span><?= $sso['name'] ?>
  				</a>
			</form>
  		<?php endforeach; ?>
	</div>
</div>
