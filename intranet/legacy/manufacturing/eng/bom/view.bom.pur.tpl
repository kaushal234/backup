<h2>BOM for {$bom->itsID} from company {$bom->itsERP} as of  {if $smarty.now|date_format:"%Y-%m-%d" > $bom->itsDate}<span style="color:#f00">{$bom->itsDate}</span>{else}{$bom->itsDate}{/if}</h2>

<table>
	<tr>
		<th>Part Number</th>
		<th>Position</th>
		<th>Description</th>
		<th>Qty</th>
		<th>PBOM Qty</th>
		<th>UM</th>
		<th>Status</th>
		<th>Drawing</th>
		<th>Rev</th>
		<th>Effective Date</th>
		<th>Supply Source</th>
		<th>Supplier #</th>
		<th>Supplier Name</th>
		<th>Lead Time</th>
		<th>Lead Time Unit</th>
		<th>On Hand</th>
		<th>On Order</th>
		<th>Allocated</th>
		<th>Availability</th>
	</tr>
{foreach name=bom key=key item=line from=$bom->itsBOMAsArray}
	<tr>
		<td style="white-space: nowrap">
			{section name=indent start=0 loop=$line.level}
				.
			{/section}
			<a href="{$smarty.server.SCRIPT_NAME}?m[0]=bom&m[1]=view&erp={$bom->itsERP}&pn={$line.t_sitm}&date={$bom->itsDate}">
				{$line.t_sitm|escape:"htmlall"}
			</a>
		</td>
		<td>
			{section name=indent start=0 loop=$line.level}
				&nbsp;
			{/section}
			<img src="/shared/branch.gif">{$line.t_pono}
		</td>
    	<td>{$line.t_dsca}</td>
		<td>{$line.t_qana}</td>
		<td>{$line.productQuantity}</td>
		<td>{$line.t_cuni}</td>
		<td>
			{if !$line.expired}
				OK
			{else}
				This item was changed {$line.t_exdt}
			{/if}
		</td>
		<td>
			<a target="_blank" href="{$smarty.server.SCRIPT_NAME}?m[0]=getfile&m[1]=drawing&erp={$bom->itsERP}&item={$line.t_sitm}&date={$bom->itsDate}">
				<img src="/shared/bluesphere/16x16/actions/filesaveas.png" alt="Save file  to your hard disk"></a>
		</td>
		<td>{$line.t_revi}</td>
		<td>{$line.t_indt}</td>
		<td>{$line.supplySource}</td>
		<td>{$line.supplier}</td>
		<td>{$line.supplierName}</td>
		<td>{$line.leadTime}</td>
		<td>{$line.leadTimeUnit}</td>
		<td>{$line.inventoryOnHand}</td>
		<td>{$line.inventoryOnOrder}</td>
		<td>{$line.allocated}</td>
		<td>
    		<a href="/en/private/parts/dashboard/{$line.t_sitm}">
    		<img src="/shared/bluesphere/16x16/actions/viewmag.png" alt="Show availability"></a>
    	</td>
	</tr>
{/foreach}
</table>