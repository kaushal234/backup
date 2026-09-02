<form class="form-horizontal" role="form" enctype="multipart/form-data" method="post" action="<?= "$PHPSELF?page=toc&action=create1" ?>">

  <input type="hidden" name="submit" value="1">
  <?php if($_ER_FLAG): ?>
    <input type="hidden" name="er_sn" value="<?php echo $_ERSN; ?>">
	<input type="hidden" name="erid" value="<?php echo $_ERID; ?>">
  <?php endif;?>
  
  <div class="jumbotld">
  
	<div class="jumbotld-title">
      <h3>TOC General Information</h3>
    </div>	
    
    <div class="form-control <?php if(isset($missingFields['cuid'])) echo "has-error"; ?>">
	  	<div class="col-xs-12">
	  	    <label class="control-label" for="cuid"></label>
	  		<select name="cuid" id="cuid" class="form-control">
	  			<option value="" disabled selected>End User Customer</option>
	  			<?php if($_POST['cuid']): ?>
		   		<?php foreach($customerList as $id=>$customer): ?>
		   		<option value="<?= $id ?>" <?php if($_POST['cuid']==$id) echo "selected";?>><?= $customer ?></option>
		   		<?php endforeach; ?>
		   		<?php else: ?>
		   		<?php foreach($customerList as $id=>$customer): ?>
		   		<option value="<?= $id ?>" <?php if($_ER_FLAG && $er->getUserCustomerID()==$id) echo "selected";?>><?= $customer ?></option>
		   		<?php endforeach; ?>
		   		<?php endif;?>
	  		</select>
		</div>
	</div>    
	<div class="form-control <?php if(isset($missingFields['ssoid'])) echo "has-error"; ?>">
	  	<div class="col-xs-12">
	  	    <label class="control-label" for="ssoid"></label>
	  		<select name="ssoid" id="ssoid" class="form-control">
	  			<option value="" disabled selected>SSO</option>
	  			<?php if($_POST['ssoid']): ?>
		   		<?php foreach($ssoList as $id=>$sso): ?>
		   		<option value="<?= $id ?>" <?php if($_POST['ssoid']==$id) echo "selected";?>><?= $sso ?></option>
		   		<?php endforeach; ?>
		   		<?php else: ?>
		   		<?php foreach($ssoList as $id=>$sso): ?>
		   		<option value="<?= $id ?>" <?php if($user->getBUID()==$id) echo "selected";?>><?= $sso ?></option>
		   		<?php endforeach; ?>
		   		<?php endif;?>
	  		</select>
		</div>
	</div>	
	<div class="form-control">
	  	<div class="col-xs-12">
	  	    <label class="control-label" for="tecid"></label>
	  		<select name="tecid" id="tecid" class="form-control">
	  			<option value="" disabled selected>Technician</option>
	  			<?php if($_POST['tecid']): ?>
		   		<?php foreach($servicePeopleList as $id=>$servicePeople): ?>
		   		<option value="<?= $id ?>" <?php if($_POST['tecid']==$id) echo "selected";?>><?= $servicePeople ?></option>
		   		<?php endforeach; ?>
		   		<?php else: ?>
		   		<?php foreach($servicePeopleList as $id=>$servicePeople): ?>
		   		<option value="<?= $id ?>" <?php if($user->getID()==$id) echo "selected";?>><?= $servicePeople ?></option>
		   		<?php endforeach; ?>
		   		<?php endif;?>
	  		</select>
		</div>
	</div>	
	<div class="form-control">
	  	<div class="col-xs-12">
	  	    <label class="control-label" for="assid"></label>
	  		<select name="assid" id="assid" class="form-control">
	  			<option value="" disabled selected>Assignee</option>
		   		<?php foreach($peopleList as $id=>$people): ?>
		   		<option value="<?= $id ?>" <?php if($_POST['assid']==$id) echo "selected";?>><?= $people ?></option>
		   		<?php endforeach; ?>
	  		</select>
		</div>
	</div>
	<div class="form-control <?php if(isset($missingFields['short_desc'])) echo "has-error"; ?>">
	  	<div class="col-xs-12">
	  		<input name="short_desc" type="text" <?php if($_POST['short_desc']) echo "value=\"{$_POST['short_desc']}\" ";?> class="form-control" id="short_desc" placeholder="Problem Description (short)">
	  	</div>
	</div>
	<div class="form-control">
	  	<div class="col-xs-12">
	  		<textarea name="prob_dsca" class="form-control" rows="2" id="prob_dsca" placeholder="Problem Description (long)"><?php if($_POST['prob_dsca']) echo $_POST['prob_dsca']; ?></textarea>
	  	</div>
	</div>
	<div class="form-control <?php if(isset($missingFields['unit_operation_status'])) echo "has-error"; ?>">
	  	<div class="col-xs-12">
	  	    <label class="control-label" for="unit_operation_status"></label>
	  		<select name="unit_operation_status" id="unit_operation_status" class="form-control">
	  			<option value="" disabled selected>Unit Operational Status</option>
		   		<?php foreach($unitOperationStatusList as $unitOperationStatus): ?>
		   		<option value="<?= $unitOperationStatus ?>" <?php if($_POST['unit_operation_status']==$unitOperationStatus) echo "selected";?>><?= $unitOperationStatus ?></option>
		   		<?php endforeach; ?>
	  		</select>
		</div>
	</div>
	<div class="form-control <?php if(isset($missingFields['ifactor'])) echo "has-error"; ?>">
	  	<div class="col-xs-12">
	  	    <label class="control-label" for="ifactor"></label>
	  		<select name="ifactor" id="ifactor" class="form-control">
	  			<option value="" disabled selected>Importance Factor</option>
		   		<?php foreach($ifList as $if): ?>
		   		<option value="<?= $if ?>" <?php if($_POST['ifactor']==$if) echo "selected";?>><?= $if ?></option>
		   		<?php endforeach; ?>
	  		</select>
		</div>
	</div>
	<div class="form-control">
	  	<div class="col-xs-12">
	  		<input name="est_hours" type="text" <?php if($_POST['est_hours']) echo "value=\"{$_POST['est_hours']}\" ";?>class="form-control" id="est_hours" placeholder="Estimated Hours">
	  	</div>
	</div>	
  
  </div>
  
  <div class="jumbotld">  
  
	<div class="jumbotld-title">
      <h3>Equipment Information - SN: <?php echo $titleER; ?></h3>
    </div>	
    
	<div class="form-control <?php if(isset($missingFields['hours'])) echo "has-error"; ?>">
	  	<div class="col-xs-12">
	  		<input name="hours" type="text" <?php if($_POST['hours']) echo "value=\"{$_POST['hours']}\" ";?>class="form-control" id="hours" placeholder="Hours">
	  		Last Hours: <?php echo $titleHours; ?>
	  	</div>
	</div>
	<div class="form-control <?php if(isset($missingFields['apc'])) echo "has-error"; ?>">
	  	<div class="col-xs-12">
	  	    <label class="control-label" for="apc"></label>
	  		<select name="apc" id="apc" class="form-control">
	  			<option value="" disabled selected>Airport Code</option>
	  			<?php if($_POST['apc']): ?>
		   		<?php foreach($apcList as $apc): ?>
		   		<option value="<?= $apc ?>" <?php if($_POST['apc']==$apc) echo "selected";?>><?= $apc ?></option>
		   		<?php endforeach; ?>
		   		<?php else: ?>
		   		<?php foreach($apcList as $apc): ?>
		   		<option value="<?= $apc ?>" <?php if($_ER_FLAG && $er->getAPC()==$apc) echo "selected";?>><?= $apc ?></option>
		   		<?php endforeach; ?>
		   		<?php endif;?>
	  		</select>
	  		Last APC: <?php echo $titleAPC; ?>
		</div>
	</div>
	<div class="form-control">
		<div class="col-xs-12">
			<button type="submit" class="btn btn-primary btn-block">Submit</button>
		</div>
	</div>	
  
  </div>  

</form>