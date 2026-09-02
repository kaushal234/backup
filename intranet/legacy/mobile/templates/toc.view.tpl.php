<form class="form-horizontal" role="form" enctype="multipart/form-data" method="post" action="<?= "$PHPSELF?page=toc&action=update" ?>">

	<input type="hidden" name="toc_id" value="<?php echo $header['id'] ?>">
	
	<div class="jumbotld">
		<table class="table table-striped tablebleble">
			<thead>
		      <tr>
		        <th class="primary">Last Log</th>
		        <th class="primary">TLD Main File</th>
		      </tr>
		    </thead>
		    <tbody>
		        <td>
		        	<?php if($logs[0]['comment']): ?>
		   			<?php echo $logs[0]['date'] ?> - <?php echo $logs[0]['poster_fullname'] ?></b> : <?php echo $logs[0]['comment'] ?>
		   			<?php endif;?>
				</td>
		        <td>
		        	<?php echo $fileHTML ?>
		        </td>
		      </tr>
		    </tbody>
		</table>
		<table class="table table-striped table-bordered table-view" data-toggle="table">
		    <tr>
				<td>
		            TOC#
	   	   		</td>
	   	   		<td>
		            <?php echo $header['id'] ?>
				</td>
		   	</tr>
		   	<tr>
				<td>
		            Date
		   		</td>
		   		<td>
		   			<?php echo $header['dt'] ?>
		   		</td>
		   	</tr>
		   	<tr>
				<td>
		            Poster
		   		</td>
		   		<td>
		   			<?php echo $header['post_fullname'] ?>
		   		</td>
		   	</tr>
		   	<tr>
				<td>
		            Status
		   		</td>
		   		<td>
		   			<?php echo $header['status'] ?>
		   		</td>
		   	</tr>
		   	<tr>
				<td>
		            Assignee
		   		</td>
		   		<td>
		   			<?php echo $header['ass_fullname'] ?>
		   		</td>
		   	</tr>
		   	<tr>
				<td>
		            SSO
		   		</td>
		   		<td>
		   			<?php echo $header['sso_fullname'] ?>
		   		</td>
		   	</tr>
		   	<tr>
				<td>
		            Service Technician
		   		</td>
		   		<td>
		   			<?php echo $header['tec_fullname'] ?>
		   		</td>
		   	</tr>
		   	<tr>
				<td>
		            Customer Name
		   		</td>
		   		<td>
		   			<?php echo $header['customer_name'] ?>
		   		</td>
		   	</tr>
		   	<tr>
				<td>
		            Customer Contact
		   		</td>
		   		<td>
		   			<?php echo $header['con_fullname'] ?>
		   		</td>
		   	</tr>
		   	<tr>
				<td>
		            Short Problem Description
		   		</td>
		   		<td>
		   			<?php echo $header['short_desc'] ?>
		   		</td>
		   	</tr>
		   	<tr>
				<td>
		            Customer Problem Description
		   		</td>
		   		<td>
		   			<?php echo $header['prob_dsca'] ?>
		   		</td>
		   	</tr>
		   	<tr>
				<td>
		            Equipment Hours
		   		</td>
		   		<td>
		   			<?php echo $header['hours'] ?>
		   		</td>
		   	</tr>
		   	<tr>
				<td>
		           	Equipment Airport Code
		   		</td>
		   		<td>
		   			<?php echo $header['apc'] ?>
		   		</td>
		   	</tr>
		   	<tr>
				<td>
		            Activity Type
		   		</td>
		   		<td>
		   			<?php echo $header['activity_type'] ?>
		   		</td>
		   	</tr>
		   	<tr>
				<td>
		            Unit Operational Status
		   		</td>
		   		<td>
		   			<?php echo $header['unit_operation_status_full'] ?>
		   		</td>
		   	</tr>
		   	<tr>
				<td>
		            Dispatch Tech?
		   		</td>
		   		<td>
		   			<?php echo $header['disp_tec'] ?>
		   		</td>
		   	</tr>
		   	<tr>
				<td>
		            Importance Factor
		   		</td>
		   		<td>
		   			<?php echo $header['ifactor'] ?>
		   		</td>
		   	</tr>
		   	<tr>
				<td>
		            Weight Factor
		   		</td>
		   		<td>
		   			<?php echo $header['wf'] ?>
		   		</td>
		   	</tr>
		   	<tr>
				<td>
		            Estimated Hours
		   		</td>
		   		<td>
		   			<?php echo $header['est_hours'] ?>
		   		</td>
		   	</tr>
		   	<tr>
				<td>
		            Last Log
		   		</td>
		   		<td>
		   			<?php if($logs[0]['comment']): ?>
		   			<?php echo $logs[0]['date'] ?> - <?php echo $logs[0]['poster_fullname'] ?></b> : <?php echo $logs[0]['comment'] ?>
		   			<?php endif;?>
		   		</td>
		   	</tr>
		</table>
		<div class="form-control">
			<div class="col-xs-12">
				<button type="submit" class="btn btn-primary btn-lg btn-block">Update TOC</button>
			</div>
		</div>
	</div>
</form>	