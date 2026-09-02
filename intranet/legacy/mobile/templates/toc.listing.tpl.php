<div class="container">
  	<div id="TabContainer">
		 <table class="table table-striped tablebleble">
		    <thead>
		      <tr>
		        <th class="primary">TOC#</th>
		        <th class="primary">Status</th>
		       	<th class="primary">Unit Status</th>
		       	<th class="primary">Model</th>
		       	<th class="primary">Airport Code</th>
				<th class="primary">Short Problem Description</th>
		      </tr>
		    </thead>
		    <tbody>
		    <?php foreach($tocListing as $toc): ?>
		      <tr class='clickable-row-toc' data-url='<?php echo $toc['id'] ?>'>
		        <td><?php echo $toc['id'] ?></td>
		        <td><?php echo $toc['status'] ?></td>
		        <td><?php echo $toc['unit_operation_status'] ?></td>
		        <td><?php echo $toc['model'] ?></td>
		        <td><?php echo $toc['apc'] ?></td>
		        <td><?php echo $toc['short_desc'] ?></td>
		      </tr>
			<?php endforeach; ?>
		    </tbody>
		  </table>
		</div>
</div>
<!-- Modal -->
<div class="modal fade bottom-left" id="myModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title" id="myModalLabel"></h4>
      </div>
      <div class="modal-body">
		<form method="post" action="<?= "$PHPSELF?page=toc&action=view" ?>">
    		<input type="hidden" name="toc_id" class="hiddenId" value=""/>
			<button type="submit" class="btn btn-primary btn-lg btn-block">View</button>
		</form>
		<br>
		<form method="post" action="<?= "$PHPSELF?page=toc&action=update" ?>">
    		<input type="hidden" name="toc_id" class="hiddenId" value=""/>
			<button type="submit" class="btn btn-primary btn-lg btn-block">Update</button>
		</form>
      </div>
    </div>
  </div>
</div>