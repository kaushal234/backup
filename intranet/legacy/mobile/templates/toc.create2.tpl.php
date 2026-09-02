<form class="form-horizontal" role="form" enctype="multipart/form-data" method="post" action="<?= "$PHPSELF?page=toc&action=create2" ?>">

  <input type="hidden" name="submit" value="1">

  <div class="jumbotld">

	<div class="jumbotld-title">
      <h3>Contacts and TOC Notifications for Customer <?php echo $cust->getCustomerName(); ?></h3>
    </div>

    <div class="form-control <?php if(isset($missingFields['conid'])) echo "has-error"; ?>">
	  	<div class="col-xs-12">
	  	    <label class="control-label" for="conid"></label>
	  	    <?php if(!empty($extranetUserList)): ?>
	  		<select name="conid" id="conid" class="form-control">
	  			<option value="" disabled selected>Main Customer Contact</option>
		   		<?php foreach($extranetUserList as $id=>$extranetUser): ?>
		   		<option value="<?= $id ?>" <?php if($_POST['conid']==$id) echo "selected";?>><?= $extranetUser ?></option>
		   		<?php endforeach; ?>
	  		</select>
	  		<?php else: ?>
	  		<select disabled name="conid" id="conid" class="form-control">
	  			<option value="" disabled selected>No contact found</option>
	  		</select>
	  		<?php endif;?>
		</div>
	</div>
	<div class="form-control <?php if(isset($missingFields['lang'])) echo "has-error"; ?>">
	  	<div class="col-xs-12">
	  	    <label class="control-label" for="lang"></label>
	  	    <?php if(!empty($extranetUserList)): ?>
	  		<select name="lang" id="lang" class="form-control">
	  			<option value="" disabled selected>Overwrite Language? (optional)</option>
		   		<?php foreach($langList as $lang): ?>
		   		<option value="<?= $lang ?>" <?php if($_POST['lang']==$lang) echo "selected";?>><?= $lang ?></option>
		   		<?php endforeach; ?>
	  		</select>
	  		<?php else: ?>
	  		<select disabled name="lang" id="lang" class="form-control">
	  			<option value="" disabled selected>N/A</option>
	  		</select>
	  		<?php endif;?>
		</div>
	</div>

  </div>

  <div class="jumbotld">

	<div class="jumbotld-title">
      <h3>New Main Contact (if not listed above)</h3>
    </div>

 	<div class="form-control">
	  	<div class="col-xs-12">
	  	    <label class="control-label" for="crtid"></label>
	  		<select name="con[crtid]" id="crtid" class="form-control">
	  			<option value="" disabled selected>Select CRT</option>
		   		<?php foreach($crtList as $id=>$crt): ?>
		   		<option value="<?= $id ?>" <?php if($_POST['con']['crtid']==$id) echo "selected";?>><?= $crt ?></option>
		   		<?php endforeach; ?>
	  		</select>
		</div>
	</div>
	<div class="form-control <?php if(isset($missingFields['con']['lastname'])) echo "has-error"; ?>">
	  	<div class="col-xs-12">
	  		<input name="con[lastname]" type="text" <?php if($_POST['con']['lastname']) echo "value=\"{$_POST['con']['lastname']}\" ";?> class="form-control" id="lastname" placeholder="Lastname">
	  	</div>
	</div>
	<div class="form-control <?php if(isset($missingFields['con']['firstname'])) echo "has-error"; ?>">
	  	<div class="col-xs-12">
	  		<input name="con[firstname]" type="text" <?php if($_POST['con']['firstname']) echo "value=\"{$_POST['con']['firstname']}\" ";?> class="form-control" id="firstname" placeholder="Firstname">
	  	</div>
	</div>
	<div class="form-control <?php if(isset($missingFields['con']['email'])) echo "has-error"; ?>">
	  	<div class="col-xs-12">
	  		<input name="con[email]" type="text" size="40" <?php if($_POST['con']['email']) echo "value=\"{$_POST['con']['email']}\" ";?> class="form-control" id="email" placeholder="Email">
	  	</div>
	</div>
	<div class="form-control <?php if(isset($missingFields['con']['phone'])) echo "has-error"; ?>">
	  	<div class="col-xs-12">
	  		<input name="con[phone]" type="text" <?php if($_POST['con']['phone']) echo "value=\"{$_POST['con']['phone']}\" ";?> class="form-control" id="phone" placeholder="Phone">
	  	</div>
	</div>
    <div class="form-control <?php if(isset($missingFields['con']['lang'])) echo "has-error"; ?>">
	  	<div class="col-xs-12">
	  	    <label class="control-label" for="con[lang]"></label>
	  		<select name="con[lang]" id="con[lang]" class="form-control">
	  			<option value="" disabled selected>Language</option>
		   		<?php foreach($langList as $lang): ?>
		   		<option value="<?= $lang ?>" <?php if($_POST['con']['lang']==$lang) echo "selected";?>><?= $lang ?></option>
		   		<?php endforeach; ?>
	  		</select>
		</div>
    </div>

  </div>

  <div class="jumbotld">

	<div class="jumbotld-title">
      <h3>Additional Contacts (optional)</h3>
    </div>

    <div class="form-control">
	  	<div class="col-xs-12">
	  		<select multiple="multiple" id="multi-select" name="contacts[]">
	  			<?php foreach($extranetUserList as $id=>$extranetUser): ?>
		   		<option value="<?= $id ?>"
		   		<?php foreach($_POST['contacts'] as $contactid): if($contactid==$id) echo "selected"; endforeach;?>><?= $extranetUser ?></option>
		   		<?php endforeach; ?>
	  		</select>

    	</div>
	</div>

  </div>

  <div class="jumbotld">

	<div class="jumbotld-title">
      <h3>TOC Notification Option</h3>
    </div>

	<div class="form-control">
    	<div class="col-xs-12">
    	    <?php if($_POST['disable_not_value'] == "Y"): ?>
				<input type="hidden" name="disable_not_value" value="Y" id="toc-disable-not">
    			<button type="button" id="toc-btn-not" class="btn btn-block btn-danger glyphicon glyphicon-eye-close"> Unset Disabled TOC Notification</button>
			<?php else:?>
				<input type="hidden" name="disable_not_value" value="N" id="toc-disable-not">
    			<button type="button" id="toc-btn-not" class="btn btn-block btn-primary glyphicon glyphicon-eye-open"> Set Disabled TOC Notification</button>
            <?php endif;?>
    	</div>
    </div>
	<div class="form-control <?php if(isset($missingFields['disable_reason'])) echo "has-error"; ?>">
	  	<div class="col-xs-12">
	  		<textarea name="disable_reason" class="form-control" rows="2" id="disable_reason" placeholder="Please justify (only if disabled)"><?php if($_POST['disable_reason']) echo $_POST['disable_reason']; ?></textarea>
	  	</div>
	</div>

  </div>

  <div class="jumbotld">

	<div class="jumbotld-title">
	  <h3>TOC Main File (picture prefered)</h3>
    </div>

	<div class="form-control">
	  	<div class="col-xs-12">
	  		<input name="attachment_dsc" type="text" <?php if($_POST['attachment_dsc']) echo "value=\"{$_POST['attachment_dsc']}\" ";?> class="form-control" id="attached" placeholder="Quick file description">
	  	</div>
	</div>
	<div class="form-control">
		<div class="col-xs-12">
	     	<input type="file" name="attachment" id="file" />
     	</div>
	</div>
	<div class="form-control">
		<div class="col-xs-12">
			<button type="submit" class="btn btn-primary btn-block">Submit</button>
		</div>
	</div>

  </div>

</form>
