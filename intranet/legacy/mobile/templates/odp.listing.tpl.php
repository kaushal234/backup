<div class="container">
  	<div id="TabContainer">
		 <table class="table table-striped tablebleble scrollable">
		    <thead>
		      <tr>
		        <th class="primary">Factory</th>
		        <th class="primary">Buyer</th>
		       	<th class="primary">Equipment Type</th>
		       	<th class="primary">Unit SN</th>
		       	<th class="primary">Customer EXW Request</th>
				<th class="primary">Factory EXW Promise</th>
				<th class="primary">Estimated GT Date</th>
				<th class="primary">Actual GT Date</th>
		      </tr>
		    </thead>
		    <tbody>
		    <?php foreach($odpListing as $odp): ?>
		      <tr class='clickable-row-odp' data-url='<?php echo $odp['id'] ?>'>
		        <td><?php echo $odp['erp_fullname'] ?></td>
		        <td><?php echo $odp['er_buyer_customer_display'] ?></td>
		        <td><?php echo $odp['model'] ?></td>
		        <td><?php echo $odp['sn'] ?></td>
		        <td><?php echo $odp['del_dat'] ?></td>
		        <td><?php echo $odp['ddel_est1'] ?></td>
		        <td><?php echo $odp['dgt_rev'] ?></td>
		        <td><?php echo $odp['dgt_act'] ?></td>
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
		<form method="post" action="<?= "$PHPSELF?page=odp&action=view" ?>">
    		<input type="hidden" name="er_id" class="hiddenId" value=""/>
			<button type="submit" class="btn btn-primary btn-lg btn-block">View</button>
		</form>
      </div>
    </div>
  </div>
</div>
