
<div class="row" id="home">
	  
    <div class="col-xs-6 col-md-4">
    	<a href="<?= "$PHPSELF?page=customers&action=listing&legacyId=$ASM_ID" ?>" class="homepage-btn"><span class="glyphicon glyphicon-briefcase"></span><br>My Customers</a>
    </div>
    
    <div class="col-xs-6 col-md-4">
    	<a href="<?= "$PHPSELF?page=mim&action=create" ?>" class="homepage-btn"><span class="glyphicon glyphicon-bullhorn"></span><br>Create MIM</a>
    </div>
    
    <div class="col-xs-6 col-md-4">
    	<a href="<?= "$PHPSELF?page=sfr&action=create" ?>" class="homepage-btn"><span class="glyphicon glyphicon-usd"></span><br>Create SFR</a>
    </div>
    
    <div class="col-xs-6 col-md-4">
    	<a class="homepage-btn" data-toggle="modal" data-target="#myModalSFR"><span class="glyphicon glyphicon-edit"></span><br>SFR Update</a>
    </div>
    
    <div class="col-xs-6 col-md-4">
    	<a class="homepage-btn" data-toggle="modal" data-target="#myModalTOC"><span class="glyphicon glyphicon-edit"></span><br>Create TOC</a>
    </div>
    <div class="col-xs-6 col-md-4">
    	<a href="<?= "$PHPSELF?page=odp&action=listing" ?>" class="homepage-btn"><span class="glyphicon glyphicon-road"></span><br>My Deliveries</a>
    </div>

</div>


<div class="modal fade" id="myModalSFR" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <form method="post" id="sfr_search" action="">
        <div class="modal-header">
		  <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
          <h4 class="modal-title" id="myModalLabel">Search SFR by:</h4>
        </div>
        <div class="modal-body">
    	  <div class="form-control">
    	    <input type="text" class="form-control" name="sfr_id" id="sfr_id" placeholder="SFR ID#">
          </div>
          <div class="form-control">
    	    <select name="sfr_by_customer" id="sfr_by_customer" class="form-control">
				<option value="" disabled selected>Or select customer</option>
    		    <?php foreach($asmCustomerList as $id=>$customer): ?>
		   		<option value="<?= $id ?>"><?= $customer ?></option>
		   	  	<?php endforeach; ?>
			</select>
      	  </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
          <button type="submit" class="btn btn-primary" id="sfr_search_go">Go</button>
        </div>
      </form>
    </div>
  </div>
</div>

<div class="modal fade" id="myModalTOC" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <form method="post" id="toc_create" action="<?= "$PHPSELF?page=toc&action=create1" ?>">
        <div class="modal-header">
		  <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
          <h4 class="modal-title" id="myModalLabel">Create TOC - Please provide ER Serial Number (optional):</h4>
        </div>
        <div class="modal-body">
    	  <div class="form-control">
    	    <input type="text" class="form-control" name="er_sn" id="er_sn" placeholder="ER SN#">
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
          <button type="submit" class="btn btn-primary">Go</button>
        </div>
      </form>
    </div>
  </div>
</div>