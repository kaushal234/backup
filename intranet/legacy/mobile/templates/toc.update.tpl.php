<form class="form-horizontal" role="form" enctype="multipart/form-data" method="post" action="<?= "$PHPSELF?page=toc&action=update" ?>">

  <input type="hidden" name="submit" value="1">
  <input type="hidden" name="toc_id" value="<?php echo $header['id'] ?>">
  
  
  <div class="jumbotld">
  
	<div class="jumbotld-title">
      <h3>Internal Log & Communication</h3>
    </div>	
    

	<div class="form-control <?php if(isset($missingFields['log'])) echo "has-error"; ?>">
	  	<div class="col-xs-12">
	  		<textarea name="log" class="form-control" rows="3" id="log" placeholder="Comment"></textarea>
	  		<?php echo $titleLog; ?>
	  	</div>
	</div>
  
  </div>
  
  <div class="jumbotld">  
  
	<div class="jumbotld-title">
      <h3>Notification Email to Customer [<?php  if($toc->isNotificationEnable()){ echo 'ENABLE'; }else{ echo 'DISABLED';} ?>]</h3>
    </div>	
    
	<div class="form-control <?php if(isset($missingFields['not[other_recipient]'])) echo "has-error"; ?>">
	  	<div class="col-xs-12">
	  		<?php if($toc->isNotificationEnable()): ?>
	  			<textarea name="not[other_recipient]" class="form-control" rows="3" name="not[other_recipient]" placeholder="Other recipients
(separated by commas)"></textarea>
	  			<?php echo $titleNot; ?>
	  		<?php else: ?>
	  			<textarea disabled name="not[other_recipient]" class="form-control" rows="3" name="not[other_recipient]" placeholder="Other recipients
(separated by commas)"></textarea>
	  		<?php endif;?>
	  	</div>
	</div>
	
	<div class="form-control <?php if(isset($missingFields['not[comment]'])) echo "has-error"; ?>">
	  	<div class="col-xs-12">
	  		<?php if($toc->isNotificationEnable()): ?>
	  			<textarea name="not[comment]" class="form-control" rows="3" name="not[comment]" placeholder="Comment"></textarea>
	  		<?php else: ?>
	  			<textarea disabled name="not[comment]" class="form-control" rows="3" name="not[comment]" placeholder="Comment"></textarea>
	  		<?php endif;?>
	  	</div>
	</div>
  
	<div class="form-control">
		<div class="col-xs-12">
			<button type="submit" class="btn btn-primary btn-block">Submit</button>
		</div>
	</div>	
    
  </div>

</form>
