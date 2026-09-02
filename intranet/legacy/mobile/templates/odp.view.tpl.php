	<div class="jumbotld">
		<table class="table table-striped table-bordered table-view" data-toggle="table">
		    <tr>
				<td>
		            ER#
	   	   		</td>
	   	   		<td>
		            <?= $odpListing[0]['id'] ?>
				</td>
		   	</tr>
		   	<tr>
				<td>
		            Late
		   		</td>
		   		<td>
		   			<?= $odpListing[0]['del_late'] ?>
		   		</td>
		   	</tr>
		   	<tr>
				<td>
		            Factory
		   		</td>
		   		<td>
		   			<?= $odpListing[0]['erp_fullname'] ?>
		   		</td>
		   	</tr>
		   	<tr>
				<td>
		            TLD Sales Org
		   		</td>
		   		<td>
		   			<?= $odpListing[0]['sso_fullname'] ?>
		   		</td>
		   	</tr>
		   	<tr>
				<td>
		            SOR Customer BUYER
		   		</td>
		   		<td>
		   			<?= $odpListing[0]['sor_buyer_customer_display'] ?>
		   		</td>
		   	</tr>
		   	<tr>
				<td>
		            ER Customer USER
		   		</td>
		   		<td>
		   			<?= $odpListing[0]['er_user_customer_display'] ?>
		   		</td>
		   	</tr>
		   	<tr>
				<td>
		            Equipment Type
		   		</td>
		   		<td>
		   			<?= $odpListing[0]['model'] ?>
		   		</td>
		   	</tr>
		   	<tr>
				<td>
		            Unit SN
		   		</td>
		   		<td>
		   			<?= $odpListing[0]['sn'] ?>
		   		</td>
		   	</tr>
		   	<tr>
				<td>
		            Project#
		   		</td>
		   		<td>
		   			<?= $odpListing[0]['t_prno'] ?>
		   		</td>
		   	</tr>
		   	<tr>
				<td>
		            SOL#
		   		</td>
		   		<td>
		   			<?= $odpListing[0]['solid'] ?>
		   		</td>
		   	</tr>
		   	<tr>
				<td>
		            SOR#
		   		</td>
		   		<td>
		   			<?= $odpListing[0]['sorid'] ?>
		   		</td>
		   	</tr>
		   	<tr>
				<td>
		            SSO SO#
		   		</td>
		   		<td>
		   			<?= $odpListing[0]['orno'] ?>
		   		</td>
		   	</tr>
		   	<tr>
				<td>
		           	SSO PO#
		   		</td>
		   		<td>
		   			<?= $odpListing[0]['sso_po'] ?>
		   		</td>
		   	</tr>
		   	<tr>
				<td>
		            SSO Invoice#
		   		</td>
		   		<td>
		   			<?= $odpListing[0]['direct_sso_invoice'] ?>
		   		</td>
		   	</tr>
            <tr>
                <td>
                    Factory Invoice#
                </td>
                <td>
                    <?= $odpListing[0]['direct_erp_invoice'] ?>
                </td>
            </tr>
		   	<tr>
				<td>
		            Customer PO#
		   		</td>
		   		<td>
		   			<?= $odpListing[0]['cu_orno'] ?>
		   		</td>
		   	</tr>
		   	<tr>
				<td>
		            PO Accept Date
		   		</td>
		   		<td>
		   			<?= $odpListing[0]['dpo_ack'] ?>
		   		</td>
		   	</tr>
		   	<tr>
				<td>
		            Customer EXW Request
		   		</td>
		   		<td>
		   			<?= $odpListing[0]['del_dat'] ?>
		   		</td>
		   	</tr>
		   	<tr>
				<td>
		            Factory EXW Promise
		   		</td>
		   		<td>
		   			<?= $odpListing[0]['ddel_est1'] ?>
		   		</td>
		   	</tr>
		   	<tr>
				<td>
		            Pre-delivery Inspection?
		   		</td>
		   		<td>
		   			<?= $odpListing[0]['conf_cis'] ?>
		   		</td>
		   	</tr>
		   	<tr>
				<td>
		            EXW Ship Date
		   		</td>
		   		<td>
		   			<?= $odpListing[0]['date_shipped'] ?>
		   		</td>
		   	</tr>
		   	<tr>
				<td>
		            Estimated GT Date
		   		</td>
		   		<td>
		   			<?= $odpListing[0]['dgt_rev'] ?>
		   		</td>
		   	</tr>
		   	<tr>
				<td>
		            First GT Date
		   		</td>
		   		<td>
		   			<?= $odpListing[0]['dgt_com'] ?>
		   		</td>
		   	</tr>
		   	<tr>
				<td>
		            Actual GT Date
		   		</td>
		   		<td>
		   			<?= $odpListing[0]['dgt_act'] ?>
		   		</td>
		   	</tr>
		   	<tr>
				<td>
		            YT Date
		   		</td>
		   		<td>
		   			<?= $odpListing[0]['dyt'] ?>
		   		</td>
		   	</tr>
		   	<tr>
				<td>
		            ODP Comments
		   		</td>
		   		<td>
		   			<?= $odpListing[0]['odp_note'] ?>
		   		</td>
		   	</tr>
		   	<tr>
				<td>
		            Delivery Penalty Accepted by Sales Org?
		   		</td>
		   		<td>
		   			<?= $odpListing[0]['conf_sls'] ?>
		   		</td>
		   	</tr>
		   	<tr>
				<td>
		            Inco Terms
		   		</td>
		   		<td>
		   			<?= $odpListing[0]['inco'] ?>
		   		</td>
		   	</tr>
		   	<tr>
				<td>
		            Inco Location
		   		</td>
		   		<td>
		   			<?= $odpListing[0]['inco_loc'] ?>
		   		</td>
		   	</tr>
		   	<tr>
				<td>
		            Length (mm)
		   		</td>
		   		<td>
		   			<?= $odpListing[0]['diml'] ?>
		   		</td>
		   	</tr>
		   	<tr>
				<td>
		            Width (mm)
		   		</td>
		   		<td>
		   			<?= $odpListing[0]['dimw'] ?>
		   		</td>
		   	</tr>
		   	<tr>
				<td>
		            Height (mm)
		   		</td>
		   		<td>
		   			<?= $odpListing[0]['dimh'] ?>
		   		</td>
		   	</tr>
		   	<tr>
				<td>
		            Weight (kg)
		   		</td>
		   		<td>
		   			<?= $odpListing[0]['dimk'] ?>
		   		</td>
		   	</tr>
		   	<tr>
				<td>
		            Parts Inc?
		   		</td>
		   		<td>
		   			<?= $odpListing[0]['parts_inc'] ?>
		   		</td>
		   	</tr>
		   	<tr>
				<td>
		            Nb Crabs
		   		</td>
		   		<td>
		   			<?= $odpListing[0]['nb_crabs'] ?>
		   		</td>
		   	</tr>
		   	<tr>
				<td>
		            Payment Terms
		   		</td>
		   		<td>
		   			<?= $odpListing[0]['payment_terms'] ?>
		   		</td>
		   	</tr>
		   	<tr>
				<td>
		            Transaction Total
		   		</td>
		   		<td>
		   			<?= $odpListing[0]['trans_total'] ?>
		   		</td>
		   	</tr>
		   	<tr>
				<td>
					Vessel loading date
		   		</td>
		   		<td>
		   			<?= $odpListing[0]['esr_dt_shipped'] ?>
		   		</td>
		   	</tr>
		   	<tr>
				<td>
		            Estimated Date of Arrival
		   		</td>
		   		<td>
		   			<?= $odpListing[0]['esr_dt_estimated'] ?>
		   		</td>
		   	</tr>
		   	<tr>
				<td>
		            Actual Date of Arrival
		   		</td>
		   		<td>
		   			<?= $odpListing[0]['esr_dt_arrived'] ?>
		   		</td>
		   	</tr>
		</table>
	</div>
