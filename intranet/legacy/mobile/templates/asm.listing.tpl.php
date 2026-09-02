<div class="container">
	<div class="list-group">
		<?php foreach($asmList as $sm): ?>
			<form method="post" class="clickable-link" action="<?= "$PHPSELF?page=customers&action=myASMView" ?>">
    			<input type="hidden" name="asm_id" value="<?= $sm['id'] ?>">
    			<a href="#" class="list-group-item submit-form">
  					<span class="float-end glyphicon glyphicon-arrow-right"></span><?= $sm['lastname'] ?>, <?= $sm['firstname'] ?>
  				</a>
			</form>
  		<?php endforeach; ?>
	</div>
</div>
