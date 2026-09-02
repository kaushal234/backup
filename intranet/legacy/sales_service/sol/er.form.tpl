<form name="editODPListing" method="post" action="{$nextURL}">
	<select id="airports" style="display:none;">
		{foreach from=$apcList key=apc item=name}
			<option value="{$apc}" label="{$apc}">{$name}</option>
		{/foreach}
	</select>
	<table class="sortable" cellpadding="3"">
		<thead>
			<tr>
				<th>#</th>
				<th>Short Description</th>
				<th>Requested<br/>Delivery Location</th>
				<th>Requested<br/>Delivery Date</th>
				<th>Early<br/>delivery<br/>ok?</th>
				<th>Factory Promised<br/>Delivery Date</th>
				<th>SN#</th>
				<th>Project#</th>
				<th>Model</th>
				<th>Final destination</th>
				<th>ESR#</th>
				<th>GT Date</th>
				<th>Ship Date</th>
				{if $sor.bu eq 320}
					<th>DPAS Rating</th>
				{/if}
				<th>Batch Qty</th>
				<th>Commissioning</th>
				<th>Sleeping Commission</th>
				<th>Customer Asset#</th>
				{if $unit eq "y"}
				<th>Delete</th>
				{/if}
			</tr>
		</thead>
		<tbody>
			{foreach name=er item=er from=$er}
			<tr bgcolor="{cycle values="#eeeeee,#d0d0d0"}">
				<td>{$smarty.foreach.er.iteration}</td>
				{if $unit eq "y"}
					<td><input type="text" name="units[{$er.id}][short_desc]" value="{$er.short_desc}" size="20" /></td>
					<td><input type="text" name="units[{$er.id}][del_location]" value="{$er.del_location}" size="25" /></td>
				{else}
					<td>{$er.short_desc}</td>
					<td>{$er.del_location}</td>
				{/if}
				{if $sale eq "y"}
					<td><input type="text" name="units[{$er.id}][del_dat]" value="{$er.del_dat}" size="5" maxlength="10"  class="datepicker"/></td>
				{else}
					<td>{$er.del_dat}</td>
				{/if}
				{if $early eq "y"}
					<td><select name="units[{$er.id}][del_early]">
					<option selected="selected" value="{$er.del_early}">{$er.del_early}</option>
					<option value="Y">Y</option>
					<option value="N">N</option>
					</select></td>
				{else}
					<td>{$er.del_early}</td>
				{/if}
				{if $factory eq "y"}
					<td><input type="text" name="units[{$er.id}][ddel_est1]" value="{$er.ddel_est1}" size="5" maxlength="10" class="datepicker"/></td>
				{else}
					<td>{$er.ddel_est1}</td>
				{/if}
				<td>{$er.sn}</td>
				{if $factory eq "y"}
					<td><input type="text" name="units[{$er.id}][t_prno]" value="{$er.t_prno}" size="3"/></td>
				{else}
					<td>{$er.t_prno}</td>
				{/if}
				<td>{$er.model}</td>
				{if $er.sn neq ""}
					<td>
					  <select name="units[{$er.id}][airport_code]" width="200" style="width:200px">
							<option label="{$er.airport_code}" value="{$er.airport_code}">{$apcList[$er.airport_code]}</option>
					  </select>
					</td>
				{else}
					<td>{$er.airport_code}</td>
				{/if}
				<td>{$er.esrid}</td>
				<td>{$er.dgt_act}</td>
				<td>{$er.date_shipped}</td>
				{if $sor.bu eq 320}
					<td><input type="text" name="units[{$er.id}][dpas_rating]" value="{$er.dpas_rating}" size="10" /></td>
				{/if}
				{if $unit eq "y"}
					<td><input type="text" name="units[{$er.id}][batch_qty]" value="{$er.batch_qty}" size="2" {if $fl_trailersDollies neq "y"}disabled="disabled"{/if}/></td>
				{else}
					<td>{$er.batch_qty}</td>
				{/if}
				
				<td>
					<select name="units[{$er.id}][commissioning]">
						<option label="Yes" value="1" {if $er.commissioning eq 1}selected="selected"{/if} >Yes</option>
						<option label="No" value="0" {if $er.commissioning eq 0}selected="selected"{/if}>No</option>
					</select>
				</td>
				{if $sale eq "y"}
					<td>
						<input name="units[{$er.id}][sleep_com]" value="{$er.sleep_com}" style="width:150px!important;"/>
						<select name="units[{$er.id}][sleep_com_sso_id]"  style="width:100%!important">
							{foreach from=$ssoList key=sso item=name}
								<option value="{$sso}" {if $sso eq $er.sleep_com_sso_id}selected="selected"{/if}>{$name}</option>
							{/foreach}
						</select>
					</td>
				{else}
					<td>{$er.sleep_com}</td>
				{/if}
				{if $factory eq "y" or $sale eq 'y'}
					<td><input type="text" name="units[{$er.id}][cust_asset_num]" value="{$er.cust_asset_num}" size="3"/></td>
				{else}
					<td>{$er.cust_asset_num}</td>
				{/if}
				{if $unit eq "y"}
					<td><div align="center">
						<a href="/en/private/sales_service/sales.php?m[0]=sol&m[1]=view&m[2]=er&m[3]=editUnits&m[4]=del&id={$id}&uid={$er.id}"
						onClick="javascript:return confirm('Are you sure to delete unit#{$smarty.foreach.er.iteration} ?');"
						title="Click to delete">
						<img src="/shared/icons/miscellaneous/delete.png" alt="Delete"/></div>
					</td>
				{/if}
			</tr>
			{/foreach}
			<tr>
				<td></td>
				{if $unit eq "y"}
					<td>
						<input class="js-form-selector" type="text" name="short_desc" size="20" />
						<button class="js-button-selector" type="button">Copy</button>
					</td>
					<td>
						<input class="js-form-selector" type="text" name="del_location" size="25" />
						<button class="js-button-selector" type="button">Copy</button>
					</td>
				{else}
					<td></td>
					<td></td>
				{/if}
				<td></td>
				<td></td>
				<td></td>
				<td></td>
				<td></td>
				<td></td>
				<td>
					<select class="js-form-selector" name="airport_code" width="200" style="width:200px"></select>
					<button class="js-button-selector" type="button">Copy</button>
				</td>
				<td></td>
				<td></td>
				<td></td>
				{if $sor.bu eq 320}<td></td>{/if}
				<td></td>
				<td></td>
				<td></td>
				<td></td>
				{if $unit eq "y"}<td></td>{/if}
			</tr>
		</tbody>
	</table>
	<br/>
	<input type="submit" value="Update Units" /> <input type="reset" value="Reset" />
</form>
